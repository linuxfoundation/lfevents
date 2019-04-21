[![CircleCI](https://circleci.com/gh/LF-Engineering/lfevents.svg?style=shield&circle-token=97ff5f114ec48d9c1595975ac16ee11d7f87014a)](https://circleci.com/gh/LF-Engineering/lfevents)
[![Dashboard lfevents](https://img.shields.io/badge/dashboard-lfeventsci-yellow.svg)](https://dashboard.pantheon.io/sites/f74d847c-e689-4631-a91b-24b7f897139b#dev/code)
[![Dev Site lfevents](https://img.shields.io/badge/site-lfeventsci-blue.svg)](http://dev-lfeventsci.pantheonsite.io/)

# LFEvents Developer Instructions

LFEvents uses a Continuous Integration (CI) infrastructure via github, CircleCI and Pantheon.  These instructions help you get a local instance up and running and explain how to run the various tests.

All these tests are run by CircleCI on each commit to the master branch, whenver a PR is created on a branch, and on each commit to a branch that has a PR open.  Such branches will have a multidev env automatically created for them by CircleCI to facilitate showing to stakeholders.  Once the PR is merged, the env will be automatically deleted.  

-----

## Install Local Instance

### Requirements

* Install [Lando](https://docs.devwithlando.io/) (a Docker Compose utility / abstraction layer). On a Mac using brew, the command is `brew cask install lando`.

* Install [Terminus](https://pantheon.io/docs/terminus/install/) (CLI for interaction with Pantheon).  Follow all the instructions on that page to setup a [machine token](https://pantheon.io/docs/terminus/install/#machine-token) and [SSH Authentication](https://pantheon.io/docs/terminus/install/#ssh-authentication).

* You need a GitHub [personal access token](https://help.github.com/en/articles/creating-a-personal-access-token-for-the-command-line) to use in place of a password for performing Git operations over HTTPS.  It will be used in step 2 below.

* [Node](https://nodejs.org/)/[NPM](https://www.npmjs.com/) (for theme development)

### Lando Setup
(these steps were derived from [instructions provided by Pantheon](https://github.com/pantheon-systems/example-wordpress-composer#working-locally-with-lando))

1. Clone this repository with HTTPS (not SSH): `git clone https://github.com/LF-Engineering/lfevents.git`
  * Note that the repo does not contain all of WordPress, 3rd-party themes and plugins. They will be pulled in via [composer](https://getcomposer.org/) in step 4.

2. Run `lando init` and use the following values when prompted:
  * `From where should we get your app's codebase?` > `current working directory`
  * `What recipe do you want to use?` > `pantheon`
  * `Enter a Pantheon machine token` > `[enter the GitHub token you got above]`
  * `Which site?` > `lfeventsci`

3. Run `lando start` and note the local site URL provided at the end of the process

4. Run `lando composer install --no-ansi --no-interaction --optimize-autoloader --no-progress` to download dependencies

5. Run `lando pull --code=none` and follow the prompts to download the media files and database from Pantheon:
  * `Pull database from?` >  `dev`
  * `Pull files from?` >  `dev`

6. Visit the local site URL saved from above

### Notes

* You can stop Lando with `lando stop` and start it again with `lando start`

* Composer, Terminus and wp-cli commands should be run in Lando rather than on the host machine. This is done by prefixing the desired command with `lando`. For example, after a change to composer.json, run `lando composer update` rather than `composer update`.

* Run `lando pull --code=none` at any time to pull down a fresh copy of the database and files from Pantheon

-----

## Theme Development

LFEvents uses a fork of the [FoundationPress](https://github.com/olefredrik/foundationpress) theme, which **requires [Node.js](http://nodejs.org)**.

* Go to the theme directory: `cd web/wp-content/themes/FoundationPress-master`

* Install the Node.js dependencies: `npm install` (git ignores the `node_modules/` directory)

* To optionally use Browsersync, copy `config-default.yml` to `config.yml` (git ignores this file) and change the Browsersync URL (line 4) to `https://lfeventsci.lndo.site/`

* Run `npm start` to watch changes theme changes and compile CSS and JS to `dist/` (git ignores this directory). When deployed, `dist/` files are compiled and minified with `npm run build` through CircleCI.

-----

## Wordhat Tests

The CircleCI job runs [Wordhat](https://wordhat.info/) tests after each commit.  They interact with the site through a chrome headless browser.  The tests are stored in tests/behat/. Here's a [quick intro](https://wordhat.info/getting-started/behat-intro.html) on how to write tests.

Create a behat-local.yml like this:


```
cp tests/behat/behat-pantheon.yml behat-local.yml
```


Edit behat-local.yml to have the bottom half of the file like this. You'll need to fill in your own admin password and update the `base_url` and `site_url` params:


```
 extensions:
   Behat\MinkExtension:
     base_url: https://lfeventsci.lndo.site
     browser_name: chrome
     sessions:
       default:
         chrome:
           api_url: "http://localhost:9222"
           validate_certificate: false

   PaulGibbs\WordpressBehatExtension:
     users:
       -
         roles:
           - administrator
         username: admin
         password: xxx
     default_driver: wpcli
     site_url: https://lfeventsci.lndo.site/wp
     path: web/wp


   DMore\ChromeExtension\Behat\ServiceContainer\ChromeExtension: ~
```


You need to have chrome running in headless mode in order for the tests to run.  I accomplished that on macos like this:


```
alias chrome="/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome"
chrome --headless --remote-debugging-port=9222 https://www.chromestatus.com&
```


Then to run the tests:


```
vendor/bin/behat --config=behat-local.yml
```

-----

## Wraith Tests

[Wraith](https://github.com/BBC-News/wraith) performs visual regression tests by comparing two versions of the site.  It is a great way to spot unintended render issues across the site.  

Install wraith system-wide using [the instructions here](http://bbc-news.github.io/wraith/index.html).

Then setup your own config file:


```
cd wraith
cp configs/capture-local.yaml.template configs/capture.yaml
```


Edit configs/capture.yaml to update the new domain to point to your local instance.

Run wraith: `wraith capture configs/capture.yaml`

View the diff gallery: `open shots/gallery.html`

-----

## Code Sniffs

The CircleCI process has a job to sniff the code to make sure it complies with WordPress coding standards.  All Linux Foundation code should comply with [these guidelines](https://docs.google.com/document/d/1TYqCwG874i6PdJDf5UX9gnCZaarvf121G1GdNH7Vl5k/edit#heading=h.dz20heii56uf).

phpcs and the [WordPress Coding Standards for PHP_CodeSniffer](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards) come as part of the repo and are installed in the vendor directory by composer.  phpcs can be run on the command line like this:

```
./vendor/bin/phpcs --standard=WordPress ./web/wp-content
```

It's even more convenient to [install into your text editor](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards#using-phpcs-and-wpcs-from-within-your-ide).  

Since the lfeventsci repo includes phpcs via composer, it will use that version of the binary even though you may have phpcs installed system-wide.  So in the root of the repo you'll need to run the following so that it can find the WordPress standards from within your code editor:

```
./vendor/bin/phpcs --config-set installed_paths ~/Sites/lfeventsci/vendor/wp-coding-standards/wpcs
```

-----

## Upgrading WordPress core, themes and plugins

Dependencies of this project are managed by [Composer](https://getcomposer.org/). All dependencies of the project are set in [composer.json](https://github.com/LF-Engineering/lfevents/blob/master/composer.json) and are pulled in at deploy time according to what is set in [composer.lock](https://github.com/LF-Engineering/lfevents/blob/master/composer.lock).  

composer.lock is generated from composer.json only when explicitly calling the `composer update` function. Any additional themes or plugins can be added first to composer.json and then `composer update package` is run to update composer.lock and pull in the new files.  Dependencies are pegged to a version according to the composer [versioning rules](https://getcomposer.org/doc/articles/versions.md).

It's good practice to keep WordPress and all plugins set at their latest releases to inherit any security patches and upgraded functionality.  Upgrading to a new version, however, sometimes has unintended consequences so it's critical to run all tests before deploying live.  

To upgrade the version of a dependency, follow these steps:

1. Edit [composer.json](https://github.com/LF-Engineering/lfevents/blob/master/composer.json) to set the new version rule

2. Run `lando composer update [package]` to update [composer.lock](https://github.com/LF-Engineering/lfevents/blob/master/composer.lock) for just that package or run `lando composer update` to upgrade all packages to the latest versions which satisfy the constraints set in composer.json

3. Test the site locally

4. Check in to github and allow the tests to run

5. Test the dev instance to make sure all looks good

6. Deploy live
