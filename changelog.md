# Changelog

## [Unreleased]

### Added

- Added possibility to categorize media files in lists which could be used for listing of files in frontend
- Added option to show file dates for each file
- Added option to sort the list by file dates
- Added option to show a label for the file format for each file
- Added possibility to add custom font file for iconsets using unicode icons
- Added new hooks
- Added hint which page and view in backend is added or extended by this plugin
- Added global settings for our block which can also be inherited to all blocks
- Added task in settings to add the block to chosen pages or posts
- Added option to reset the plugin in backend settings (in preparation for Cyber Resilience Act)
- Added option to reset plugin styles with one click

### Changed

- Using composer autoloader for each PHP-object for better performance
- Using transient composer package for internal actions and admin notices
- Updated Font Awesome Icons from version 6 to 7
- Updated dependencies

### Fixed

- Fixed missing translations for bulk message editing of icons

## [3.8.0] - 15.05.2025

### Added

- Added link to support forum in plugin list
- Added new hooks
- Added help box in icon edit screen
- Added check with PHPStan to reduce potential PHP-errors

### Changed

- Optimization on code for better speed and to prevent potential errors
- Updated dependencies

### Fixed

- Fixed formatting in plugin list

## [3.7.0] - 16.02.2025

### Added

- Added version numbers for each template file
- Added hint for user in backend if customized template files should be updated to new version
- Added hooks for any returning lists on REST endpoints of this plugin

### Changed

- Admin notices of this plugin can now be hidden for X days
- Optimized styling on editing a custom icon

## [3.6.5] - 30.01.2025

### Fixed

- Fixed less favorable index for file types of custom iconsets

## [3.6.4] - 11.01.2025

### Changed

- Use natural sort for alphabetic sorting for files in the list
- Files without title will now be visible with their filename
- Optimized internal object spelling

## [3.6.3] - 02.01.2025

### Fixed

- Fixed wrong path for templates in child-theme
- Fixed wrong hook usage for "downloadlist_fontawesome_files" in Helper object
- Fixed wrong plugin slug path in GitHub action during building the release

## [3.6.2] - 21.12.2024

### Added

- Added GitHub actions for plugin releases and documentation

### Changed

- Moved changelog to GitHub
- Small code optimizations
- Updated dependencies

### Fixed

- Fixed missing WPML-configuration in release file

### Removed

- Removed unused test with auto-release via build.xml

## [3.6.1] - 30.11.2024

### Changed

- Updated compatibility with other plugins like Block Visibility
- Optimized some variable names
- Optimized uninstall-tasks if they are run via WP CLI
- Updated dependencies

## [3.6.0] - 22.11.2024

### Added

- Added template for download button
- Added new options to chose target-attribut value for link and download-button
- Added option to hide the link and show just the file title instead
- Added new versioning for styles
- Added some new hooks

### Changed

- Re-arranged the settings in block in multiple sections for better overview
- Changed internal transient names for better compatibility with other plugins
- Optimized code

### Fixed

- Fixed possible double enqueuing of styles in frontend

## [3.5.5] - 06.11.2024

### Fixed

- Fixed missing style optimizations from last release

## [3.5.4] - 06.11.2024

### Added

- Added warning if PHP 8.0 or older is used

### Changed

- Compatibility with WordPress 6.7
- Updated some styles in editor
- Updated dependencies

## [3.5.3] - 01.09.2024

### Changed

- Load our own styles on frontend only if our block is used in actual page
- Load only actually used iconset(s) styles in frontend
- Optimized Font Awesome styles regarding font-weight
- Updated dependencies

## [3.5.2] - 17.08.2024

### Changed

- Prevent to use WP/scripts >= 28.x for better compatibility with WordPress < 6.6
- Updated dependencies
- Updated fontawesome and bootstrap icon libraries
- Remove custom attachment title und description on uninstall (solves #92)

## [3.5.1] - 08.06.2024

### Changed

- Updated dependencies
- Compatibility with WordPress 6.6

### Fixed

- Fixed visibility of Dashicons in frontend if user is not logged in

## [3.5.0] - 05.05.2024

### Added

- Added SEO-relevant rel-attribute to optionally prevent bots to follow download-links
- Added new hook for rel-attribute

### Changed

- Compatibility with WordPress 6.5.3
- Updated dependencies
- Updated fontawesome and bootstrap icon libraries

### Fixed

- Fixed potential PHP warning for files without sizes

## [3.4.2] - 29.03.2024

### Changed

- Optimized output of file size in list
- Updated dependencies

### Fixed

- Fixed choosing or uploading images button
- Fixed generating of styles for custom iconsets

## [3.4.1] - 24.02.2024

### Changed

- Prevent uninstall with PHP older than 8.0 to prevent errors
- Hide "Mine" in icon list in backend
- Updated dependencies
- Compatibility with WordPress 6.5

### Fixed

- Fixed possible notice in transient-handler
- Fixed removing of our own iconset taxonomy on uninstall

## [3.4.0] - 04.02.2024

### Added

- Added possibility to use multiple custom iconsets
- Added automatic documentation of hooks in this plugin

### Changed

- Now generating valid class names from mime types (thanks @samedwards)
- Rename handles of enqueued styles to prevent conflicts with other plugins
- Compatibility with WordPress 6.4.3
- Optimized style for editing custom icons
- Optimized performance
- Updated dependencies
- Plugin now won't be usable with PHP older than 8.0

### Fixed

- Fixed missing translations
- Fixed error on search for iconsets during editing your own icon
- Fixed visibility of custom icons in backend
- Fixed problem with duplicate entries for each iconset

## [3.3.2] - 14.01.2024

### Fixed

- Fixed loading of translation scripts

## [3.3.1] - 13.01.2024

### Fixed

- Fixed error in creating new Downloadlist-Block
- Fixed typos in descriptions

## [3.3.0] - 13.01.2024

### Added

- Added possibility to convert File-, Audio- and Video-Block to Downloadlist-Block

### Changed

- Optimized sort-button: now sorting on every click in the opposite direction
- Disable sort-buttons if list contains less or equal than 1 file
- Releases now MUST fulfill all WordPress Coding Standard rules before creating release files
- Updated dependencies

### Removed

- Removed AJAX requests for preview as list to request is empty for preview
- Removed all language files from plugin directory

## [3.2.4] - 23.11.2023

### Changed

- Better check for uploads-directory existence for compatibility with playground-preview

## [3.2.3] - 22.11.2023

### Changed

- Check if attachment page is enabled for WordPress 6.4 or newer before generating link to it
- Updated dependencies
- Some style-optimizations

## [3.2.2] - 21.10.2023

### Changed

- Changed text domain to plugin slug to match WordPress-Repository requirements
- Removed language-files from plugin (except the json-files for Block Editor)
- Added missing translations

## [3.2.1] - 21.10.2023

### Changed

- Compatible with WordPress Coding Standards 3.0
- Prevented WPML from translating our (only internal used) custom post type and taxonomy for icons
- Compatibility with WordPress 6.4
- Updated dependencies

### Fixed

- Fixed possible error during adding files

## [3.2.0] - 23.08.2023

### Added

- Added custom title and description for files in downloadlist
- Added icon for our own icon-post-type in wp-admin

### Changed

- Optimized rest of code regarding WordPress Coding Standard
- Updated dependencies

## [3.1.0] - 16.07.2023

## Added

- Added option to disable forcing download on click on file-link
- Added option to show download-button on each file entry

### Changed

- Optimized styling for files with description
- Optimized iconset-style-generation

### Fixed

- Fixed usage of unique function names

## [3.0.1] - 08.07.2023

### Fixed

- Fixed initialization of iconsets during plugin activation

## [3.0.0] - 08.07.2023

### Added

- Added possibility to manage different iconsets and assign them to each Block
- Added styling (e.g. color, typography ...) for Blocks using WordPress-standards

### Changed

- Compatible with WordPress Coding Standards
- Minimum PHP-compatibility set to 8.0
- Only compatible with WordPress 6.0 or newer
- Compatibility with WordPress 6.3
- Compatible with theme Blockify and much more FSE-themes

## [2.1.1] - 12.04.2023

### Fixed

- Fixed output of classic block if this plugin is used

## [2.1.0] - 19.03.2023

### Added

- Added support for templates via theme

### Changed

- Updated dependencies
- Updated compatibility-flag for WordPress 6.2

## [2.0.4] - 17.10.2022

### Added

- Add some german translations

### Changed

- Updated compatibility-flag for WordPress 6.1

## [2.0.3] - 01.08.2022

### Fixed

- Fixed limit of entries per list - it's now unlimited

## [2.0.2] - 01.08.2022

### Fixed

- Fixed widget-handling: all other widgets were not visible

## [2.0.1] - 10.07.2022

### Fixed

- Fixed release-upload problem

## [2.0.0] - 10.07.2022

### Added

- Added option to edit each file in a Block in media library with one click
- Added control-option to show or hide the filesize of all files in a Block (default: show)
- Added control-option to show or hide the description of all files in a Block (default: show)
- Added control-option to show or hide the icon of all files in a Block (default: show)
- Added toolbar-option to sort the files in a Block by their titles with one click
- Added toolbar-option to sort the files in a Block by their filesize with one click
- Added control-option to set the link target for all files in a Block to
  "direct link" (e.g. /wp-content/uploads/file.pdf) or "attachment page" (e.g. /file/)
- Added support for HTML-output in file description
- Added support for using this Block as Widget

### Changed

- Changed loading of file-data in Block in Gutenberg: they are now loaded live from Media Library as the front page
- Updated compatibility-flag for WordPress 6.0.1

### Fixed

- Fixed icon-visibility (only default icon was visible)

## [1.0.6] - 06.07.2022

### Added

- Add support for inner blocks

### Changed

- changed used sortable-library

### Fixed

- Fixed usage of foreign shortcodes

## [1.0.5] - 06.07.2022

### Changed

- replace serialize_block in favor of render_block for better compatibility with other blocks

## [1.0.4] - 21.05.2022

### Changed

- Updated compatibility-flag for WordPress 6.0

## [1.0.3] - 15.04.2022

### Changed

- Updated dependencies

## [1.0.2] - 09.02.2022

### Changed

- Updated dependencies

## [1.0.1] - 09.02.2022

### Changed

- Updated format for Changelog

### Fixed

- Fixed issue with 3rd-party dependency

## [1.0.0] - 08.01.2022

### Added

- Initial commit
