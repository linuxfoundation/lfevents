# LFEvents Developer Guide

LFEvents uses a Continuous Integration (CI) infrastructure via GitHub Actions and Pantheon.  These instructions help you get a local instance up and running and explain how to run the various tests.

Git PRs will have a Pantheon multidev env automatically created for them to facilitate testing.  Once the PR is merged, the env will be automatically deleted.

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

7. Run this command to activate/deactivate multiple plugins that can help with local dev or are not needed for local dev. The Load Media Files from Production plugin will load media from the production server instead of needing to download them locally:

```
lando wp plugin activate debug-bar && lando wp plugin activate query-monitor && lando wp plugin deactivate shortpixel-image-optimiser && lando wp plugin deactivate pantheon-advanced-page-cache && lando wp plugin activate load-media-from-production
```

8. You will need to compile the theme css/js before the site will render correctly:
   1. Go to the theme directory: `cd web/wp-content/themes/lfevents`
   2. Install the Node.js dependencies: `lando npm install`
   3. Compile the files: `lando npm run build`

9. Visit the local site URL saved from above.  To find it again run `lando info`.

10. In the admin you will need to edit the [Search & Filter](https://lfeventsci.lndo.site/wp/wp-admin/edit.php?post_type=search-filter-widget) settings.  The full url to the result pages are hardcoded in the "Display Results" of each filter.  These will need to be set to the corresponding local instance url.

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

## Sessionize Blocks

The `sessionize-blocks` plugin provides two blocks — Sessionize Schedule and Sessionize Speakers — that display event data from [Sessionize](https://sessionize.com). Each block takes an **API code** attribute, which is the event identifier in Sessionize's URLs.

### How the data flows

Event data is fetched **on the server**, not in the browser. This means the full schedule is present in the HTML source, so search engines and AI agents can read it, and the page keeps working when sessionize.com is slow or down.

1. `Sessionize_Client` fetches `https://sessionize.com/api/v2/{apiCode}/view/All` and `.../view/GridSmart`.
2. `Sessionize_Normalizer` sanitizes every session description (a PHP port of the block's markdown-lite renderer) and slims the grid payload.
3. `Sessionize_Store` saves the result gzipped into a non-autoloaded option keyed by the API code.
4. `render.php` reads the cache and renders real session cards, speaker cards, JSON-LD, and an inline `<script type="application/json">` island.
5. `view.js` hydrates from that inline island instead of making a network request, then takes over filtering, search, grid view, modals and favorites as before.

The cache is **stale-while-revalidate**. A page render never blocks on sessionize.com except on a true cold start (no cached copy at all), and that one fetch is behind a lock so concurrent requests don't stampede. If a refresh fails, the last known good copy is kept and the error is recorded — bad data never overwrites good data.

### Refreshing

Data refreshes automatically via WP-Cron (`sessionize_refresh_all`). Note that Pantheon fires wp-cron roughly hourly regardless of the declared interval; the stale-while-revalidate behaviour covers the gap.

To force a refresh:

- **Admin UI**: Tools → Sessionize Data. Shows last sync, session/speaker counts, cached size and last error per API code, with a "Refresh now" button.
- **WP-CLI**: `lando wp sessionize refresh` (all events) or `lando wp sessionize refresh --code=abc12345`. `lando wp sessionize status` prints the same table as the admin screen.

Both paths purge the Pantheon edge cache for the affected pages afterwards, so visitors aren't served stale HTML.

### Cache TTL

The default TTL is 15 minutes. Override it with the `sessionize_cache_ttl` filter (values below 60 seconds are ignored):

```php
add_filter( 'sessionize_cache_ttl', function() { return 300; } );
```

### Rebuilding the block assets

From `web/wp-content/plugins/sessionize-blocks/`, run `npm install` then `npm run build`. Commit the regenerated `build/` directories.

### Gotcha: two renderers

Sessions are now rendered in **both** PHP (`render.php` and `includes/data.php`) and JavaScript (`src/view.js`). If you change how a session card looks or how a field is derived, change both — otherwise the card will visibly shift when the page hydrates. The riskiest areas are date/time formatting and the category colour hash, which are deliberately byte-for-byte ports of the JS versions.

-----

## Code Sniffs

The CI process will sniff the code to make sure it complies with WordPress coding standards.  All Linux Foundation code should comply with [these guidelines](https://docs.google.com/document/d/1TYqCwG874i6PdJDf5UX9gnCZaarvf121G1GdNH7Vl5k/edit#heading=h.dz20heii56uf).

phpcs and the [WordPress Coding Standards for PHP_CodeSniffer](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards) come as part of the Lando install and are installed in the vendor directory by Composer.

You can get a report of required fixes on your code by running `lando sniff` and you can automatically fix some required changes by running `lando fix`. You can see warnings by running `lando warnings`.

The commands are setup to use WordPress Coding Standards and to run on the `wp-content/themes/` directory as well as on custom plugins. This is controlled by the phpcs.xml file.

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
