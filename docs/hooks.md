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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 284](classes/class-helper.php#L284-L291)

## Filters

### `downloadlist_register_iconset`

*Register a single iconset through adding it to the list.*

The iconset must be an object extending Iconset_Base and implement Iconset.

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$list` | `array` | The list of iconsets.

**Changelog**

Version | Description
------- | -----------
`3.0.0` | Available since 3.0.0.

Source: [./classes/class-iconsets.php](classes/class-iconsets.php), [line 63](classes/class-iconsets.php#L63-L72)

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
`$dashicons` | `array` | List of the icons.

**Changelog**

Version | Description
------- | -----------
`3.0.0` | Available since 3.0.0

Source: [./classes/iconsets/class-dashicons.php](classes/iconsets/class-dashicons.php), [line 167](classes/iconsets/class-dashicons.php#L167-L182)

### `downloadlist_dashicons_files`

*Filter the files used for dashicons.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$files` | `array` | List of the files.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./classes/iconsets/class-dashicons.php](classes/iconsets/class-dashicons.php), [line 250](classes/iconsets/class-dashicons.php#L250-L256)

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
`$font_awesome_icons` | `array` | List of the icons.

**Changelog**

Version | Description
------- | -----------
`3.0.0` | Available since 3.0.0

Source: [./classes/iconsets/class-fontawesome.php](classes/iconsets/class-fontawesome.php), [line 212](classes/iconsets/class-fontawesome.php#L212-L227)

### `downloadlist_fontawesome_files`

*Filter the files used for fontawesome.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$files` | `array` | List of the files.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./classes/iconsets/class-fontawesome.php](classes/iconsets/class-fontawesome.php), [line 244](classes/iconsets/class-fontawesome.php#L244-L250)

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
`$bootstrapicons` | `array` | List of the icons.

**Changelog**

Version | Description
------- | -----------
`3.0.0` | Available since 3.0.0

Source: [./classes/iconsets/class-bootstrap.php](classes/iconsets/class-bootstrap.php), [line 160](classes/iconsets/class-bootstrap.php#L160-L175)

### `downloadlist_bootstrap_files`

*Filter the files used for bootstrap.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$files` | `array` | List of the files.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./classes/iconsets/class-bootstrap.php](classes/iconsets/class-bootstrap.php), [line 244](classes/iconsets/class-bootstrap.php#L244-L250)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 37](classes/class-helper.php#L37-L43)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 54](classes/class-helper.php#L54-L61)

### `downloadlist_css_path`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$path` |  | 

Source: [./classes/class-helper.php](classes/class-helper.php), [line 81](classes/class-helper.php#L81-L81)

### `downloadlist_css_url`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` |  | 

Source: [./classes/class-helper.php](classes/class-helper.php), [line 101](classes/class-helper.php#L101-L101)

### `downloadlist_prevent_css_generation`

*Prevent generation of new CSS-files.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$false` | `array` | Set to true to prevent the generation.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./classes/class-helper.php](classes/class-helper.php), [line 136](classes/class-helper.php#L136-L142)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 272](classes/class-helper.php#L272-L279)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 318](classes/class-helper.php#L318-L325)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 327](classes/class-helper.php#L327-L334)

### `downloadlist_prevent_icon_generation`

*Prevent generation of icons used by iconsets of this plugin.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$false` | `array` | Set to true to prevent the generation.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./classes/class-helper.php](classes/class-helper.php), [line 347](classes/class-helper.php#L347-L353)

### `downloadlist_prevent_icon_generation`

*Set suffix for generated filename.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$suffix` | `array` | The suffix to use.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [./classes/class-helper.php](classes/class-helper.php), [line 390](classes/class-helper.php#L390-L396)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 420](classes/class-helper.php#L420-L427)

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

Source: [./classes/class-helper.php](classes/class-helper.php), [line 561](classes/class-helper.php#L561-L569)


<p align="center"><a href="https://github.com/pronamic/wp-documentor"><img src="https://cdn.jsdelivr.net/gh/pronamic/wp-documentor@main/logos/pronamic-wp-documentor.svgo-min.svg" alt="Pronamic WordPress Documentor" width="32" height="32"></a><br><em>Generated by <a href="https://github.com/pronamic/wp-documentor">Pronamic WordPress Documentor</a> <code>1.2.0</code></em><p>

