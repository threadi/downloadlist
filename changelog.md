# Changelog

## [5.0.0] - 11.10.2026

### Security

- Only users who can see the notices of this plugin can dismiss them (the request checked only the nonce)
- REST API endpoint for file data returns only files the user is allowed to read
- File types of custom icons are checked against the list of known file types
- Values in the generated CSS file are escaped
- Download attributes from hooks are escaped as complete attribute

### Added

- Optional preview images for images, PDF files and audio or video files with cover image, with the width as setting per block and as default in the plugin settings
- Optional border for icons and preview images with frame width, color, radius, inner spacing and background color under Styles in the block, also as default in the plugin settings
- Folders of folder plugins for the media library (e.g., Folderly, HappyFiles, Folders) could be used as source of a download list block, optionally with their subfolders; can be disabled in the new tab "Settings"
- Abilities for the Abilities API of WordPress (since WordPress 6.9), e.g. for AI agents: find where a file is shown, get the usage of download lists, get download lists, folders and their files, check for missing preview images; optionally also assign files to download lists, add download lists to posts, inherit the settings and regenerate the styles
- Settings for the abilities (enabled, mode "Read only" or "All") with a check whether they could be used in the project
- The descriptions of the abilities, which change content ask AI agents to confirm the change with the user first
- New tab "Usage" in the plugin settings, which lists all posts, pages, patterns and templates with a download list, with the number of lists and files and links to edit them
- Hints in the block editor for files without preview image and their reason
- Lists with the iconset of the plugin "File-Preview for Download List Block with Icons" show the preview images now without this plugin
- Own setting for the browser target of the download button (it used the setting of the link before)
- Confirmation for the WP CLI command `reset_plugin` (use `--yes` to skip it)
- Support for network activation and uninstallation in multisites
- Hook `downloadlist_enqueue_all_iconsets` to load the styles of all iconsets on every page (e.g. for download lists loaded via AJAX)

### Changed

- Icon fonts are only loaded in frontend for the iconsets used on the page
- The unicode of custom icons accepts only hexadecimal characters (e.g. `f101`)
- Inheriting settings also updates blocks in groups, columns, custom post types, synced patterns and templates
- Reset of the plugin keeps the download lists
- Uninstallation also removes the download lists, the meta data of the iconsets and the generated icon files
- `Helper::generate_css()` always generates the styles for all iconsets, its parameter is deprecated
- The hook `downloadlist_prevent_icon_generation` is not used for the file name suffix anymore, use `downloadlist_icon_filename_suffix`
- The file of Font Awesome is now named `fontawesome.css`
- GitHub workflows use pinned actions, minimal permissions, a smoke test in Playground and an SBOM
- Now requires WordPress 6.6 or newer
- The tab "General Settings" is now named "Block settings", the frame settings have their own section "Styles"
- Templates `list-start.php`, `list-item.php`, `list-item-not-linked.php` and `button-download.php` changed (version 5.0.0)

### Fixed

- Blocks using a download list showed nothing if their files were not saved in the block
- Download lists could show a PHP warning for files, which do not exist in the uploads directory
- Fixed fatal error during uninstallation (missing autoloader)
- Fixed backslashes in posts being removed after inheriting the settings to the blocks or after adding the block via settings
- Fixed migration tasks of updates not running on websites without development mode
- Fixed the image of custom icons not being saved
- Fixed styles of all other iconsets getting lost after saving an icon or iconset
- Fixed custom iconsets using icons and file types of all custom iconsets
- Fixed custom iconsets using only 10 icons
- Fixed duplicate generic icon entries on each activation or reset (existing duplicates are removed during update)
- Fixed default iconset losing its marker on quick-edit or REST API updates
- Fixed font URLs with query strings and fields of iconsets, which could not be emptied
- Fixed PHP warnings for file format labels of not listed file types
- Fixed hook `downloadlist_download_button_download_attribute` not working
- Fixed templates in embeds
- Fixed image sizes of iconsets not being registered
- Fixed possible fatal error in frontend if `get_plugin_data()` is not loaded
- Fixed block editor errors after transforming a file block and on dropping a file outside the list
- Fixed sorting of files in block editor before their data are loaded
- Fixed file dates in block editor for users without access to the site settings
- Fixed progress dialog for inheriting settings if a request fails
- Fixed "How to use" link for German websites
- Fixed icons with font codes being invisible if no font size is set at the iconset
- Fixed editors without administrator rights not seeing iconsets and download lists in the block
- Fixed chosen default iconset being reset on activation
- Removed shipped translation files, translations are provided by translate.wordpress.org

## [4.1.4] - 03.10.2026

### Changed

- Switched to dataview for plugin settings
- Updated dependencies

## [4.1.3] - 09.08.2026

### Changed

- Compatible with WordPress 7.1
- Now requires PHP 8.2 or newer
- Updated dialog lib to 2.0.0
- Updated settings lib to 3.0.0
- Updated dependencies for the block

## [4.1.2] - 20.06.2026

### Changed

- Optimized capability checks
- Optimized sanitization of entered data
- Updated dependencies

### Fixed

- Fixed error if custom icon is added without a selected file type

### Removed

- Removed now unsued function to dismiss a notices, now handled by easy-transients-for-wordpress

## [4.1.1] - 26.04.2026

### Changed

- Updated dependencies

## [4.1.0] - 29.03.2026

### Added

- Added "Themify Icons" as new iconset

### Changed

- Updated settings library to new main version for optimized handling
- Set compatibility with WordPress 7.0
- Optimized some texts

## [4.0.2] - 28.01.2026

### Added

- Added SBOM generation for the plugin
- Added PHP Unit Tests

### Changed

- Optimized iconset handling
- Optimized style loading of iconsets
- Optimized spaces around the title of downloads without link
- Updated Block API version to 3
- Updated dependencies

### Fixed

- Fixed missing check for direct requests on template files

## [4.0.1] - 12.11.2025

### Changed

- Compatibility With WordPress 6.9

### Fixed

- New created blocks get a false default iconset

## [4.0.0] - 28.09.2025

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
