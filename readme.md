# Download List Block with Icons

This repository is the database for the plugin _Download List Block with Icons_. This provides a Gutenberg block to manage a download list with file type specific icons.

## Usage

After checkout go through the following steps:

1. copy _build/build.properties.dist_ to _build/build.properties_.
2. modify the build/build.properties file - note the comments in the file.
3. execute the command in _build/_: `ant init`
4. after that the plugin can be activated in WordPress

## Test

Run `npm start` to compile the Block Editor-Scripts for tests.

## Release

1. increase the version number in _build/build.properties_.
2. execute the following command in _build/_: `ant build`
3. after that you will finde in the release directory a zip file which could be used in WordPress to install it.

## Translations

The translations are managed on [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/download-list-block-with-icons/). The repository contains no language files.

## Build blocks

### Requirements

`npm ci`

### Run for development

`npm start`

### Run for release

`npm run build`

This also copies the icon fonts of Font Awesome and Bootstrap Icons from node_modules to css/.

Hint: will be called by ant-command mentioned above.

## Check for WordPress Coding Standards

### Initialize

`composer install`

### Run

`vendor/bin/phpcs .`

### Repair

`vendor/bin/phpcbf .`

## Check for WordPress VIP Coding Standards

Hint: this check runs against the VIP-GO-platform which is not our target for this plugin. Many warnings can be ignored.

### Run

`vendor/bin/phpcs --extensions=php --ignore=*/vendor/*,*/node_modules/*,*/block/*,*/svn/*,*/src/* --standard=WordPress-VIP-Go .`

## Check PHP compatibility

`vendor/bin/phpcs -p app --standard=PHPCompatibilityWP --runtime-set testVersion 8.2-`

### Generate documentation

`vendor/bin/wp-documentor parse app --format=markdown --output=docs/hooks.md --prefix=downloadlist_ --exclude=Transients.php`

The GitHub workflow "Hook documentation" does this automatically on pull requests.

## Check with the plugin "Plugin Check"

This runs the plugin check as the plugin check in the WordPress repository does on every plugin update. It should result in no errors.

Hint: run this not in the development environment, it would also check all dependencies that is unnecessary.
Use a normal WordPress installation with an installed PCP plugin.

`wp plugin check --error-severity=7 --warning-severity=6 --include-low-severity-errors --categories=plugin_repo --format=json --slug=download-list-block-with-icons .`

## PHP Unit tests

### Preparation

Be sure to have run `composer install` or `composer update` before.

Then set the password of the database user `root` and run the installation:

`WP_TESTS_DB_PASSWORD='your-password' composer test-install`

### Run

`composer test`

## Release via GitHub

Push a tag with the version number (e.g. `4.2.0`). The workflow "Build release zip" builds the zip, checks it with Plugin Check, runs a smoke test in WordPress Playground and creates the release with the notes from changelog.md. The changelog needs a section for this version.

