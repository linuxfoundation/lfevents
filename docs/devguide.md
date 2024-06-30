# LFEvents Developer Guide

LFEvents uses a Continuous Integration (CI) infrastructure via GitHub Actions and Pantheon.  These instructions help you get a local instance up and running and explain how to run the various tests.

Git branches will have a Pantheon multidev env automatically created for them to facilitate testing.  Once the PR is merged, the env will be automatically deleted.

For instructions on how to configure [the resulting site](https://events.linuxfoundation.org) to host events, please see the [Admin Instructions](https://docs.google.com/document/d/1mvIuw-R9k_gbnZn_iV04qNTjG33u_lXwFlN7s-lgJ1Y/edit?usp=sharing).

-----

## Install Local Instance

### Requirements

* Install [Lando](https://github.com/lando/lando/releases) (a Docker Compose utility / abstraction layer). Using Homebrew for installation is not recommended. [Lando Docs](https://docs.devwithlando.io/). Lando includes it's own versions of PHP, Node (14.19.0), NPM.

* When setting up Lando with the Pantheon recipe it will automatically download [Terminus](https://pantheon.io/docs/terminus/install/) (CLI for interaction with Pantheon).  Follow all the instructions on that page to setup a [machine token](https://pantheon.io/docs/terminus/install/#machine-token) and [SSH Authentication](https://pantheon.io/docs/terminus/install/#ssh-authentication).  Save the machine token for use in step 2 below.

* Get a GitHub [personal access token](https://help.github.com/en/articles/creating-a-personal-access-token-for-the-command-line) to use in place of a password for performing Git operations over HTTPS.

### Lando Setup
(these steps were derived from [instructions provided by Pantheon](https://github.com/pantheon-systems/example-wordpress-composer#working-locally-with-lando))

1. Clone this repository with HTTPS (not SSH): `git clone https://github.com/linuxfoundation/lfevents.git`
  * Note that the repo does not contain all of WordPress, 3rd-party themes and plugins. They will be pulled in via [composer](https://getcomposer.org/) in step 4.

2. Run `lando init` and use the following values when prompted:
  * `From where should we get your app's codebase?` > `current working directory`
  * `What recipe do you want to use?` > `pantheon`
  * `Enter a Pantheon machine token` > `[enter the Pantheon token you got above]`
  * `Which site?` > `lfeventsci`

3. Open the .lando.yml file and add the following to the file.

```yml
keys:
  - pantheon_rsa
excludes:
  - vendor
  - /app/web/wp-content/themes/lfevents/node_modules
services:
  node:
    type: 'node:14'
  appserver:
    run:
      - /app/vendor/bin/phpcs -i
tooling:
  npm:
    service: node
  node:
    service: node
  npx:
    service: node
  sniff:
    service: appserver
    description: "Run the recommended code sniffs"
    cmd: "/app/vendor/bin/phpcs -ns"
  warnings:
    service: appserver
    description: "Show code sniff warnings"
    cmd: "/app/vendor/bin/phpcs -s"
  fix:
    service: appserver
    description: "Run the recommended code sniffs and fix them"
    cmd: "/app/vendor/bin/phpcbf -s"
  paths:
    service: appserver
    description: "See code sniff paths"
    cmd: "/app/vendor/bin/phpcs -i"
  debug:
    service: appserver
    description: "Monitor WordPress debug log output"
    cmd: "tail -f /app/web/wp-content/debug.log"
```

4. Run `lando start` and note the local site URL provided at the end of the process

5. Run `lando composer install --no-ansi --no-interaction --optimize-autoloader --no-progress` to download dependencies

6. Run `lando pull --code=none --files=none` and follow the prompts to download the media files and database from Pantheon:
  * `Pull database from?` >  `dev`

7. Run this command to activate/deactivate multiple plugings that can help with local dev or are not needed for local dev. The Load Media Files from Production plugin will load media from the production server instead of needing to download them locally:

```
lando wp plugin activate debug-bar && lando wp plugin activate query-monitor && lando wp plugin deactivate shortpixel-image-optimiser && lando wp plugin deactivate pantheon-advanced-page-cache && lando wp plugin activate load-media-from-production
```

8. You will need to compile the theme css/js before the site will render correctly:
   1. Go to the theme directory: `cd web/wp-content/themes/lfevents`
   2. Install the Node.js dependencies: `lando npm install`
   3. Compile the files: `lando npm run build`

9. Visit the local site URL saved from above.  To find it again run `lando info`.

10. In the admin you will need to edit the [Search & Filter](https://lfeventsci.lndo.site/wp/wp-admin/edit.php?post_type=search-filter-widget) settings.  The full url to the result pages are hardcoded in the "Display Results" of each filter.  These will need to be set to the correpsonding local instance url.

11. Get your browser to trust the Lando SSL certificate by following [these instructions](https://docs.lando.dev/config/security.html#trusting-the-ca).  This step isn't essential but will stop you having to keep bypassing the privacy warning in your browser.

### Notes

* You can stop Lando with `lando stop` and start it again with `lando start`. You can turn it off completely with `lando poweroff`

* Composer, Terminus, npm and wp-cli commands should be run in Lando rather than on the host machine. This is done by prefixing the desired command with `lando`. For example, after a change to composer.json, run `lando composer update` rather than `composer update`.

* Repeat steps 6 and 7 above to download a fresh copy of the database.

-----

## Theme Development

LFEvents uses a fork of the [FoundationPress](https://github.com/olefredrik/foundationpress) theme.  Run `lando npm start` to compile CSS and JS to `dist/` (git ignores this directory) as changes are made to the source files. When deployed, `dist/` files are compiled and minified by the CI process.

Custom plugins have their css/js compiled separately and it is stored in the repo. If you make edits to the plugin source files, you need to rebuild them. First you'll need to run `lando npm run-script install-plugins` to install the necessary files then `lando npm run-script build-plugins` to build the plugins. You can do this for each plugin individually as well.

-----

## Code Sniffs

The CI process will sniff the code to make sure it complies with WordPress coding standards.  All Linux Foundation code should comply with [these guidelines](https://docs.google.com/document/d/1TYqCwG874i6PdJDf5UX9gnCZaarvf121G1GdNH7Vl5k/edit#heading=h.dz20heii56uf).

phpcs and the [WordPress Coding Standards for PHP_CodeSniffer](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards) come as part of the Lando install and are installed in the vendor directory by Composer.

You can get a report of required fixes on your code by running `lando sniff` and you can automatically fix some required changes by running `lando fix`. You can see warnings by running `lando warnings`.

Both commands are setup to use WordPress Coding Standards and to run on the `wp-content/themes/` directory as well as on custom plugins. This is controlled by the phpcs.xml file.

If you want to run your own phpcs/cbf commands, this can be done within Lando using `lando php ./vendor/bin/phpcs ?` where `?` is your arguments to pass.

It's even more convenient to [install into your IDE](https://github.com/WordPress/WordPress-Coding-Standards/wiki).

Since the lfeventsci repo includes phpcs via Composer, your IDE should use that version of the binary even though you may have phpcs installed system-wide.

-----

## Upgrading WordPress core, themes and plugins

Dependencies of this project are managed by [Composer](https://getcomposer.org/). All dependencies of the project are set in [composer.json](https://github.com/linuxfoundation/lfevents/blob/main/composer.json) and are pulled in at deploy time according to what is set in [composer.lock](https://github.com/linuxfoundation/lfevents/blob/main/composer.lock).

composer.lock is generated from composer.json only when explicitly calling the `lando composer update` function. Any additional themes or plugins can be added first to composer.json and then `lando composer update` is run to update composer.lock and pull in the new files.  Dependencies are pegged to a version according to the composer [versioning rules](https://getcomposer.org/doc/articles/versions.md).

It's good practice to keep WordPress and all plugins set at their latest releases to inherit any security patches and upgraded functionality.  Upgrading to a new version, however, sometimes has unintended consequences so it's critical to run all tests before deploying live.

To upgrade the version of a dependency, follow these steps:

1. Edit [composer.json](https://github.com/linuxfoundation/lfevents/blob/main/composer.json) to set the new version rule

2. Run `lando composer update [package]` to update [composer.lock](https://github.com/linuxfoundation/lfevents/blob/main/composer.lock) for just that package or run `lando composer update` to upgrade all packages to the latest versions which satisfy the constraints set in composer.json

3. Test the site locally

4. Check in to github and allow the tests to run

5. Test the dev instance to make sure all looks good

6. Deploy live
