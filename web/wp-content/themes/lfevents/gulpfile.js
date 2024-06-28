'use strict';

const gulp = require('gulp');
const terser = require('gulp-terser');
const yargs = require('yargs');
const browser = require('browser-sync').create();
const rimraf = require('rimraf');
const yaml = require('js-yaml');
const fs = require('fs');
const webpackStream = require('webpack-stream');
const webpack2 = require('webpack');
const named = require('vinyl-named');
const log = require('fancy-log');
const colors = require('ansi-colors');
const rename = require('gulp-rename');
const dartSass = require('sass');
const sass = require('gulp-sass')(dartSass);
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const sourcemaps = require('gulp-sourcemaps');
const through = require('through2');

// Load settings from config.yml or fallback to config-default.yml.
function loadConfig() {
    try {
        const ymlFile = fs.readFileSync(fs.existsSync('config.yml') ? 'config.yml' : 'config-default.yml', 'utf8');
        return yaml.load(ymlFile);
    } catch (err) {
        log(' Error loading config file: ', err);
        process.exit(1);
    }
}

const { BROWSERSYNC, PATHS } = loadConfig();
const PRODUCTION = !!(yargs.argv.production);

// Delete the "dist" folder. This happens every time a build starts.
function clean(done) {
    rimraf(PATHS.dist, done);
}

// Compile minify and autoprefix CSS.
function compileCSS() {
    const plugins = [
        autoprefixer(),
        cssnano({
            preset: 'default'
        })
    ];
    return gulp.src('src/scss/*.scss')
        .pipe(sourcemaps.init())
        .pipe(sass({
            includePaths: PATHS.sass,
            quietDeps: true
        }).on('error', sass.logError))
        // First, output the unminified CSS file.
        .pipe(postcss([autoprefixer()]))
        .pipe(gulp.dest(PATHS.dist + '/css'))
        // Then, process and output the minified CSS file.
        .pipe(postcss([cssnano()]))
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(PATHS.dist + '/css'))
        .pipe(browser.reload({
            stream: true
        }));
}

// Start BrowserSync to preview the site in browser.
function server(done) {
    browser.init({
        proxy: BROWSERSYNC.url,
        ui: {
            port: 8080
        },
    });
    done();
}

// Reload the browser with BrowserSync.
function reload(done) {
    browser.reload();
    done();
}

// Watch for changes to static assets, pages, Sass, and JavaScript
function watch() {
    gulp.watch('src/scss/**/*.scss', compileCSS)
        .on('change', path => log('File ' + colors.bold(colors.magenta(path)) + ' changed.'))
        .on('unlink', path => log('File ' + colors.bold(colors.magenta(path)) + ' was removed.'));
    gulp.watch('**/*.php', reload)
        .on('change', path => log('File ' + colors.bold(colors.magenta(path)) + ' changed.'))
        .on('unlink', path => log('File ' + colors.bold(colors.magenta(path)) + ' was removed.'));
    gulp.watch('src/images/**/*', gulp.series(reload));
}

const webpack = {
  config: {
    mode: PRODUCTION ? 'production' : 'development',
    module: {
        rules: [{
            test: /.js$/,
            exclude: /node_modules(?![\\\/]foundation-sites)/,
            use: {
                loader: 'babel-loader',
                options: {
                    presets: ['@babel/preset-env']
                }
            }
        }, ],
    },
    externals: {
        jquery: 'jQuery',
    },
  },
  changeHandler(err, stats) {
      log('[webpack]', stats.toString({
          colors: true,
      }));

      browser.reload();
  },
  build() {
    return gulp.src(PATHS.entries)
        .pipe(named())
        .pipe(webpackStream(webpack.config, webpack2))
        .pipe(terser().on('error', e => {
          if (!PRODUCTION) {
              log.error('[Terser Error]', e);
          }
      }))
        .pipe(gulp.dest(PATHS.dist + '/js'));
  },
  watch() {
      const watchConfig = Object.assign(webpack.config, {
          watch: true,
          devtool: 'inline-source-map',
      });

      return gulp.src(PATHS.entries)
          .pipe(named())
          .pipe(webpackStream(watchConfig, webpack2, webpack.changeHandler)
              .on('error', (err) => {
                  log('[webpack:error]', err.toString({
                      colors: true,
                  }));
              }),
          )
          .pipe(gulp.dest(PATHS.dist + '/js'));
  },
};

gulp.task('webpack:build', webpack.build);
gulp.task('webpack:watch', webpack.watch);
gulp.task('build', gulp.series(
  clean,
  compileCSS,
  'webpack:build',
));

// Default task
gulp.task('default', gulp.series(
  'build',
  server,
  'webpack:watch',
  watch
));
