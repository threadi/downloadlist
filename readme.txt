=== Download List with Icons ===
Contributors: threadi
Tags: list, download, icons, block
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Stable tag: 3.2.1

== Description ==

This plugin provides a Gutenberg Block to manage a download list with file type specific icons. No configuration is necessary. The Block can be used immediately after installation.

= Features =

- Choose files from media library
- Output chosen files as list with file-type-specific icon, download-link, title, size and description from media library
- Ships with multiple iconsets (Bootstrap-Icons, Dashicons, FontAweSome-Icons)
- Drag & Drop sorting for the list
- Remove files from list

= Supports =

- Display of file size, description and icon can be switched on and off
- Choose what link should be published: direct link or attachment page
- Choose an iconset for each Block; manage custom icons in unlimited lists
- Usage of [multiple hooks](https://github.com/threadi/downloadlist/tree/master/docs/hooks.md) to change or add icons to generated iconsets
- Sort list by title or filesize with one click
- The Block can be used in classic widgets
- Set colors, typography and borders for each Block
- Use [External files in media library](https://wordpress.org/plugins/external-files-in-media-library/) if you want to link to external files

The development repository is on [GitHub](https://github.com/threadi/downloadlist).

== Screenshots ==

1. After adding the Block you have to choose the files.
2. After adding files to the Block they will be listed.
3. The files will be listed in frontend.

== Installation ==

1. Upload "download-list-block-with-icons" to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Add the Download List Block to the post or page where you want to show the downloadlist. Choose the file(s) you wish to present.

== Upgrade Notice ==

There is nothing else to consider when updating this plugin.

== Frequently Asked Questions ==

= Where do I enter the description of a file? =

The description stored at the file in the Media Library itself is used as the description. You can edit it in 2 ways:

- Open the file in the Media Library for editing. There you will find the field "Description". Enter the desired description here.
- Or open this editing mask via the pencil at the file in the block you have created.

= Is there a limit to the number of files? =

No, there are no limitations.

= Can I use the block in my theme? =

The block can basically be used in any theme that supports Gutenberg blocks. If this is the case for your theme, you have to ask your theme developer.

= Can I add my own icons? =

Yes, you can manage your custom iconsets in WordPress-backend.

= No icon is displayed at a file. Why? =

The file will probably have a file type that does not comply with the WordPress standard and is therefore not included in our plugin. You have 2 options:

* Use the iconset specific hook downloadlist_*_icons to add the list of icons. Check beforehand with the developer of your chosen icon set if the icon you want is included there.
* Use another icon set which supports the file type if necessary.

= Does the plugin also support Elementor or other PageBuilders? =

No. The plugin is intended solely for the Gutenberg editor and will not be extended to other PageBuilders.

== Changelog ==

= 1.0.0 =
* Initial commit

= 1.0.1 =
* Fixed issue with 3rd-party dependency
* Updated format for Changelog

= 1.0.2 =
* Updated dependencies

= 1.0.3 =
* Updated dependencies

= 1.0.4 =
* Updated compatibility-flag for WordPress 6.0

= 1.0.5 =
* replace serialize_block in favor of render_block for better compatibility with other blocks

= 1.0.6 =
* Add support for inner blocks
* Fixed usage of foreign shortcodes
* changed used sortable-library

= 2.0.0 =
* Added option to edit each file in a Block in media library with one click
* Added control-option to show or hide the filesize of all files in a Block (default: show)
* Added control-option to show or hide the description of all files in a Block (default: show)
* Added control-option to show or hide the icon of all files in a Block (default: show)
* Added toolbar-option to sort the files in a Block by their titles with one click
* Added toolbar-option to sort the files in a Block by their filesize with one click
* Added control-option to set the link target for all files in a Block to
  "direct link" (e.g. /wp-content/uploads/file.pdf) or "attachment page" (e.g. /file/)
* Added support for HTML-output in file description
* Added support for using this Block as Widget
* Changed loading of file-data in Block in Gutenberg: they are now loaded live from Media Library as the front page
* Fixed icon-visibility (only default icon was visible)
* Updated compatibility-flag for WordPress 6.0.1

= 2.0.1 =
* Fixed release-upload problem

= 2.0.2 =
* Fixed widget-handling: all other widgets were not visible

= 2.0.3 =
* Fixed limit of entries per list - it's now unlimited

= 2.0.4 =
* Add some german translations
* Updated compatibility-flag for WordPress 6.1

= 2.1.0 =
* Added support for templates via theme
* Updated dependencies
* Updated compatibility-flag for WordPress 6.2

= 2.1.1 =
* Fixed output of classic block if this plugin is used

= 3.0.0 =
* Added possibility to manage different iconsets and assign them to each Block
* Added styling (e.g. color, typography ..) for Blocks using WordPress-standards
* Compatible with WordPress Coding Standards
* Minimum PHP-compatibility set to 8.0
* Only compatible with WordPress 6.0 or newer
* Compatibility with WordPress 6.3
* Compatible with theme Blockify and much more FSE-themes

= 3.0.1 =
* Fixed initialization of iconsets during plugin activation

= 3.1.0 =
* Added option to disable forcing download on click on file-link
* Added option to show download-button on each file entry
* Optimized styling for files with description
* Optimized iconset-style-generation
* Fixed usage of unique function names

= 3.2.0 =
* Added custom title and description for files in downloadlist
* Added icon for our own icon-post-type in wp-admin
* Optimized rest of code regarding WordPress Coding Standard
* Updated dependencies

= 3.2.1 =
* Compatible with WordPress Coding Standards 3.0
* Prevented WPML from translating our (only internal used) custom post type and taxonomy for icons
* Compatibility with WordPress 6.4
* Updated dependencies
* Fixed possible error during adding files
