=== Download List with Icons ===
Contributors: threadi
Tags: list, download, icons, block
Requires at least: 5.8
Tested up to: 6.2
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Stable tag: 2.1.1

== Description ==

This plugin provides a Gutenberg Block to manage a download list with file type specific icons. No configuration is necessary. The Block can be used immediately after installation.

= Features =

- Choose files from media library
- Output chosen files as list with download-link, file-title, file-size and file-description
- Show an icon in front of each file in list
- Drag & Drop sorting for the list
- Remove files from list

= Supports =

- Display of file size, description and icon can be switched on and off
- Choose what link should be published: direct link or attachment page
- sort list by title or filesize with one click
- the Block can be used in Widgets

== Screenshots ==

1. After adding the Block you have to choose the files.
2. After adding files to the Block they will be listed.
3. The files will be listed in frontend.

== Installation ==

1. Upload "download-list-block-with-icons" to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Add the Download List Block to the post or page where you want to show the downloadlist. Choose the file(s) you wish to present.

== Upgrade Notice ==

There is nothing else to consider when updating from this plugin.

== Frequently Asked Questions ==

= Where do I enter the description of a file? =

The description stored at the file in the Media Library itself is used as the description. You can edit it in 2 ways:

- Open the file in the Media Library for editing. There you will find the field "Description". Enter the desired description here.
- Or open this editing mask via the pencil at the file in the block you have created.

= Is there a limit to the number of files? =

No, there are no limitations.

= Can I use the block in my theme? =

The block can basically be used in any theme that supports Gutenberg blocks. If this is the case for your theme, you have to ask your theme developer.

= Can I change the file icons? =

The plugin provides a small number of icons for output in the web page. If you want to change or add them, you can do it with custom CSS.

Example for another general icon:
`ul.downloadlist-list li:before { content:"\f497"; }`

Example of a specific icon for PDF files:
`ul.downloadlist-list li.file_pdf:before { content:"\f190"; }`

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
