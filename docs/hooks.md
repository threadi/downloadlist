# Hooks

- [Actions](#actions)
- [Filters](#filters)

## Actions

### `downloadlist_generate_css`

*Run additional tasks after generating of the CSS-file.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$styles` | `string` | The CSS-code.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 316](app/Plugin/Helper.php#L316-L323)

## Filters

### `downloadlist_plugin_row_meta`

*Filter the links in row meta of our plugin in plugin list.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$row_meta` | `array` | List of links.

**Changelog**

Version | Description
------- | -----------
`3.8.0` | Available since 3.8.0.

Source: [./inc/admin.php](inc/admin.php), [line 781](inc/admin.php#L781-L787)

### `downloadlist_taxonomies`

*Filter the taxonomies this plugin is supporting.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$taxonomies` | `array<string,mixed>` | List of taxonomies.

**Changelog**

Version | Description
------- | -----------
`4.0.0` | Available since 4.0.0.

Source: [./app/Plugin/Taxonomies.php](app/Plugin/Taxonomies.php), [line 178](app/Plugin/Taxonomies.php#L178-L184)

### `downloadlist_mime_types`

*Filter the list of possible mimetypes.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$mime_types` | `array` | List of the mime types.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 41](app/Plugin/Helper.php#L41-L47)

### `downloadlist_style_filename`

*Set the filename for the style.css which will be saved in upload-directory.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$filename` | `string` | The list of iconsets.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0.

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 58](app/Plugin/Helper.php#L58-L65)

### `downloadlist_css_path`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$path` |  | 

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 85](app/Plugin/Helper.php#L85-L85)

### `downloadlist_css_url`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` |  | 

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 105](app/Plugin/Helper.php#L105-L105)

### `downloadlist_prevent_css_generation`

*Prevent generation of new CSS-files.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$false` | `bool` | Set to true to prevent the generation.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 153](app/Plugin/Helper.php#L153-L159)

### `downloadlist_generate_css`

*Filter the CSS-code just before it is saved.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$styles` | `string` | The CSS-code.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 304](app/Plugin/Helper.php#L304-L311)

### `downloadlist_generate_classname`

*Filter the string name of a mime type.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$type` | `string` | The name of the mime type.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 350](app/Plugin/Helper.php#L350-L357)

### `downloadlist_generate_classname`

*Filter the string name of a mime type.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subtype` |  | 

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 359](app/Plugin/Helper.php#L359-L366)

### `downloadlist_prevent_icon_generation`

*Prevent generation of icons used by iconsets of this plugin.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$false` | `bool` | Set to true to prevent the generation.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 379](app/Plugin/Helper.php#L379-L385)

### `downloadlist_prevent_icon_generation`

*Set suffix for generated filename.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$suffix` | `string` | The suffix to use.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 437](app/Plugin/Helper.php#L437-L443)

### `downloadlist_prevent_icon_generation`

*Prevent generation of specific icon.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$false` | `bool` | Set to true to prevent generation.
`$post_id` | `int` | The ID of the attachment.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 472](app/Plugin/Helper.php#L472-L479)

### `downloadlist_file_version`

*Filter the used file version (for JS- and CSS-files which get enqueued).*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$plugin_version` | `string` | The plugin-version.
`$filepath` | `string` | The absolute path to the requested file.

**Changelog**

Version | Description
------- | -----------
`3.6.0` | Available since 3.6.0.

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 621](app/Plugin/Helper.php#L621-L629)

### `downloadlist_help_sidebar_content`

*Filter the sidebar content.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$sidebar_content` | `string` | The content.

**Changelog**

Version | Description
------- | -----------
`3.8.0` | Available since 3.8.0.

Source: [./app/Plugin/Admin/Help_System.php](app/Plugin/Admin/Help_System.php), [line 104](app/Plugin/Admin/Help_System.php#L104-L110)

### `downloadlist_light_help_tabs`

*Filter the list of help tabs with its contents.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$list` | `array<int,array<string,mixed>>` | List of help tabs.

**Changelog**

Version | Description
------- | -----------
`3.8.0` | Available since 3.8.0.

Source: [./app/Plugin/Admin/Help_System.php](app/Plugin/Admin/Help_System.php), [line 124](app/Plugin/Admin/Help_System.php#L124-L130)

### `downloadlist_register_iconset`

*Register a single iconset through adding it to the list.*

The iconset must be an object extending Iconset_Base and implement Iconset.

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$list` | `array<int,\DownloadListWithIcons\Iconsets\Iconset_Base>` | The list of iconsets.

**Changelog**

Version | Description
------- | -----------
`3.0.0` | Available since 3.0.0.

Source: [./app/Iconsets/Iconsets.php](app/Iconsets/Iconsets.php), [line 65](app/Iconsets/Iconsets.php#L65-L74)

### `downloadlist_fontawesome_icons`

*Filter the list of fontawesome icons. This list is an array with the not optimized
mime type as index and the bootstrap-unicode as value.*

Example:
```
add_filter( 'downloadlist_fontawesome_icons', function( $list ) {
 $list['application/example'] = '\f42';
 return $list;
});
```

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$font_awesome_icons` | `array<string,string>` | List of the icons.

**Changelog**

Version | Description
------- | -----------
`3.0.0` | Available since 3.0.0

Source: [./app/Iconsets/Iconsets/Fontawesome.php](app/Iconsets/Iconsets/Fontawesome.php), [line 230](app/Iconsets/Iconsets/Fontawesome.php#L230-L245)

### `downloadlist_fontawesome_files`

*Filter the files used for fontawesome.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$files` | `array<int,array<string,mixed>>` | List of the files.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./app/Iconsets/Iconsets/Fontawesome.php](app/Iconsets/Iconsets/Fontawesome.php), [line 262](app/Iconsets/Iconsets/Fontawesome.php#L262-L268)

### `downloadlist_bootstrap_icons`

*Filter the list of bootstrap icons. This list is an array with the not optimized
mime type as index and the bootstrap-unicode as value.*

Example:
```
add_filter( 'downloadlist_bootstrap_icons', function( $list ) {
 $list['application/example'] = '\f42';
 return $list;
});
```

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$bootstrapicons` | `array<string,string>` | List of the icons.

**Changelog**

Version | Description
------- | -----------
`3.0.0` | Available since 3.0.0

Source: [./app/Iconsets/Iconsets/Bootstrap.php](app/Iconsets/Iconsets/Bootstrap.php), [line 178](app/Iconsets/Iconsets/Bootstrap.php#L178-L193)

### `downloadlist_bootstrap_files`

*Filter the files used for bootstrap.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$files` | `array<int,array<string,mixed>>` | List of the files.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./app/Iconsets/Iconsets/Bootstrap.php](app/Iconsets/Iconsets/Bootstrap.php), [line 262](app/Iconsets/Iconsets/Bootstrap.php#L262-L268)

### `downloadlist_dashicons_icons`

*Filter the list of dashicons. This list is an array with the not optimized
mime type as index and the bootstrap-unicode as value.*

Example:
```
add_filter( 'downloadlist_dashicons_icons', function( $list ) {
 $list['application/example'] = '\f42';
 return $list;
});
```

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$dashicons` | `array<string,string>` | List of the icons.

**Changelog**

Version | Description
------- | -----------
`3.0.0` | Available since 3.0.0

Source: [./app/Iconsets/Iconsets/Dashicons.php](app/Iconsets/Iconsets/Dashicons.php), [line 185](app/Iconsets/Iconsets/Dashicons.php#L185-L200)

### `downloadlist_dashicons_files`

*Filter the files used for dashicons.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$files` | `array<int,array<string,mixed>>` | List of the files.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./app/Iconsets/Iconsets/Dashicons.php](app/Iconsets/Iconsets/Dashicons.php), [line 268](app/Iconsets/Iconsets/Dashicons.php#L268-L274)


<p align="center"><a href="https://github.com/pronamic/wp-documentor"><img src="https://cdn.jsdelivr.net/gh/pronamic/wp-documentor@main/logos/pronamic-wp-documentor.svgo-min.svg" alt="Pronamic WordPress Documentor" width="32" height="32"></a><br><em>Generated by <a href="https://github.com/pronamic/wp-documentor">Pronamic WordPress Documentor</a> <code>1.2.0</code></em><p>

