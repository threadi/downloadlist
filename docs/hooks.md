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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 315](classes/class-helper.php#L315-L322)

## Filters

### `downloadlist_register_iconset`

*Register a single iconset through adding it to the list.*

The iconset must be an object extending Iconset_Base and implement Iconset.

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$list` | `array<int,\downloadlist\Iconset_Base>` | The list of iconsets.

**Changelog**

Version | Description
------- | -----------
`3.0.0` | Available since 3.0.0.

Source: [./classes/class-iconsets.php](classes/class-iconsets.php), [line 65](classes/class-iconsets.php#L65-L74)

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

Source: [./classes/iconsets/class-dashicons.php](classes/iconsets/class-dashicons.php), [line 185](classes/iconsets/class-dashicons.php#L185-L200)

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

Source: [./classes/iconsets/class-dashicons.php](classes/iconsets/class-dashicons.php), [line 268](classes/iconsets/class-dashicons.php#L268-L274)

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

Source: [./classes/iconsets/class-fontawesome.php](classes/iconsets/class-fontawesome.php), [line 230](classes/iconsets/class-fontawesome.php#L230-L245)

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

Source: [./classes/iconsets/class-fontawesome.php](classes/iconsets/class-fontawesome.php), [line 262](classes/iconsets/class-fontawesome.php#L262-L268)

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

Source: [./classes/iconsets/class-bootstrap.php](classes/iconsets/class-bootstrap.php), [line 178](classes/iconsets/class-bootstrap.php#L178-L193)

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

Source: [./classes/iconsets/class-bootstrap.php](classes/iconsets/class-bootstrap.php), [line 262](classes/iconsets/class-bootstrap.php#L262-L268)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 40](classes/class-helper.php#L40-L46)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 57](classes/class-helper.php#L57-L64)

### `downloadlist_css_path`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$path` |  | 

Source: [./classes/class-helper.php](classes/class-helper.php), [line 84](classes/class-helper.php#L84-L84)

### `downloadlist_css_url`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` |  | 

Source: [./classes/class-helper.php](classes/class-helper.php), [line 104](classes/class-helper.php#L104-L104)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 152](classes/class-helper.php#L152-L158)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 303](classes/class-helper.php#L303-L310)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 349](classes/class-helper.php#L349-L356)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 358](classes/class-helper.php#L358-L365)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 378](classes/class-helper.php#L378-L384)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 436](classes/class-helper.php#L436-L442)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 471](classes/class-helper.php#L471-L478)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 620](classes/class-helper.php#L620-L628)

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

Source: [./classes/class-help-system.php](classes/class-help-system.php), [line 103](classes/class-help-system.php#L103-L109)

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

Source: [./classes/class-help-system.php](classes/class-help-system.php), [line 123](classes/class-help-system.php#L123-L129)

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

Source: [./inc/admin.php](inc/admin.php), [line 798](inc/admin.php#L798-L804)


<p align="center"><a href="https://github.com/pronamic/wp-documentor"><img src="https://cdn.jsdelivr.net/gh/pronamic/wp-documentor@main/logos/pronamic-wp-documentor.svgo-min.svg" alt="Pronamic WordPress Documentor" width="32" height="32"></a><br><em>Generated by <a href="https://github.com/pronamic/wp-documentor">Pronamic WordPress Documentor</a> <code>1.2.0</code></em><p>

