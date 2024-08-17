# Download List with Icons

This repository is the database for the plugin _Download List with Icons_. This provides a Gutenberg block to manage a download list with file type specific icons.

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

I recommend to use [PoEdit](https://poedit.net/) to translate texts for this plugin.

### generate pot-file

Run in main directory:

`wp i18n make-pot . languages/download-list-block-with-icons.pot --exclude=docs,css,src,svn`

### update translation-file

1. Open .po-file of the language in PoEdit.
2. Go to "Translate" > "Update from POT-file".
3. After this the new entries are added to the language-file.

### export translation-file

1. Open .po-file of the language in PoEdit.
2. Go to File > Save.
3. Upload the generated .mo-file and the .po-file to the plugin-folder languages/

### generate json-translation-files

Run in main directory:

`wp i18n make-json languages --no-purge`

OR use ant in build/-directory: `ant json-translations`

## Build blocks

### Requirements

`npm install`

### Run for development

`npm start`

### Run for release

`npm run build`

Hint: will be called by ant-command mentioned above.

## Check for WordPress Coding Standards

### Initialize

`composer install`

### Run

`vendor/bin/phpcs --standard=ruleset.xml file`

### Repair

`vendor/bin/phpcbf --standard=ruleset.xml file`

### Generate documentation

`vendor/bin/wp-documentor parse classes --format=markdown --output=docs/hooks.md --prefix=downloadlist_`
