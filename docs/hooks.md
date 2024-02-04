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

Source: [classes/class-helper.php](class-helper.php), [line 285](class-helper.php#L285-L292)

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

Source: [classes/class-iconsets.php](class-iconsets.php), [line 65](class-iconsets.php#L65-L74)

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

Source: [classes/iconsets/class-dashicons.php](iconsets/class-dashicons.php), [line 169](iconsets/class-dashicons.php#L169-L184)

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

Source: [classes/iconsets/class-dashicons.php](iconsets/class-dashicons.php), [line 249](iconsets/class-dashicons.php#L249-L255)

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

Source: [classes/iconsets/class-fontawesome.php](iconsets/class-fontawesome.php), [line 212](iconsets/class-fontawesome.php#L212-L227)

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

Source: [classes/iconsets/class-fontawesome.php](iconsets/class-fontawesome.php), [line 244](iconsets/class-fontawesome.php#L244-L250)

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

Source: [classes/iconsets/class-bootstrap.php](iconsets/class-bootstrap.php), [line 162](iconsets/class-bootstrap.php#L162-L177)

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

Source: [classes/iconsets/class-bootstrap.php](iconsets/class-bootstrap.php), [line 244](iconsets/class-bootstrap.php#L244-L250)

### `downloadlist_fontawesome_files`

*Filter the list of possible mimetypes.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$mime_types` | `array` | List of the mime types.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [classes/class-helper.php](class-helper.php), [line 39](class-helper.php#L39-L45)

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

Source: [classes/class-helper.php](class-helper.php), [line 56](class-helper.php#L56-L63)

### `downloadlist_css_path`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$path` |  | 

Source: [classes/class-helper.php](class-helper.php), [line 83](class-helper.php#L83-L83)

### `downloadlist_css_url`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` |  | 

Source: [classes/class-helper.php](class-helper.php), [line 103](class-helper.php#L103-L103)

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

Source: [classes/class-helper.php](class-helper.php), [line 137](class-helper.php#L137-L143)

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

Source: [classes/class-helper.php](class-helper.php), [line 273](class-helper.php#L273-L280)

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

Source: [classes/class-helper.php](class-helper.php), [line 319](class-helper.php#L319-L326)

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

Source: [classes/class-helper.php](class-helper.php), [line 328](class-helper.php#L328-L335)

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

Source: [classes/class-helper.php](class-helper.php), [line 347](class-helper.php#L347-L353)

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

Source: [classes/class-helper.php](class-helper.php), [line 390](class-helper.php#L390-L396)

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

Source: [classes/class-helper.php](class-helper.php), [line 420](class-helper.php#L420-L427)


<p align="center"><a href="https://github.com/pronamic/wp-documentor"><img src="https://cdn.jsdelivr.net/gh/pronamic/wp-documentor@main/logos/pronamic-wp-documentor.svgo-min.svg" alt="Pronamic WordPress Documentor" width="32" height="32"></a><br><em>Generated by <a href="https://github.com/pronamic/wp-documentor">Pronamic WordPress Documentor</a> <code>1.2.0</code></em><p>

