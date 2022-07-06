=== Download List with Icons ===
Contributors: threadi
Tags: list, download, icons
Requires at least: 5.8
Tested up to: 6.0
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Stable tag: 1.0.6

== Description ==

This plugins provides a Gutenberg block to manage a download list with file type specific icons.

#### Features:

- Choose files from media library
- Output chosen files as list with download-link, file-title, file-size and (optional) file-description
- Drag & Drop sorting for the list
- Remove files from list

---

== Installation ==

1. Upload "downloadlist" to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Add the Download List Block to the post or page where you want to show the downloadlist. Choose the file(s) you wish to present.

== Screenshots ==

1. After adding the Block you have to choose the files.
2. After adding files to the Block they will be listed.
3. The files will be listed in frontend.

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
* Updated compatibility-flag for Wordpress 6.0

= 1.0.5 =
* replace serialize_block in favor of render_block for better compatibility with other blocks

= 1.0.6 =
* Add support for inner blocks
* Fixed usage of foreign shortcodes
* changed used sortable-library
