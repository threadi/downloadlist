=== Download List Block with Icons ===
Contributors: threadi
Tags: list, download, icons, block
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Stable tag: @@VersionNumber@@

Use a Gutenberg Block to manage a download list with file type specific icons. No configuration is necessary.

== Description ==

Use a Gutenberg Block to manage a download list with file type specific icons. No configuration is necessary. The Block can be used immediately after installation.

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
- Usage of [multiple hooks](https://github.com/threadi/downloadlist/tree/master/docs/hooks.md) to change or add icons to shipped iconsets
- Sort list by title or filesize with one click
- The Block can be used in widgets
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

You can also add a custom title and description per file used only by the Block of this plugin.

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

= 3.6.2 =

- Added GitHub actions for plugin releases and documentation
- Move changelog to GitHub

[older changes](https://github.com/threadi/downloadlist/blob/master/changelog.md)
