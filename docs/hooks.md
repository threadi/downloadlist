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

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 326](app/Plugin/Helper.php#L326-L333)

## Filters

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

Source: [./app/Plugin/Taxonomies.php](app/Plugin/Taxonomies.php), [line 189](app/Plugin/Taxonomies.php#L189-L195)

### `downloadlist_api_return_file_data`

*Filter the resulting file data before we return them.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$file_data` | `array<int,mixed>` | The response as array.
`$request` | `\WP_REST_Request` | The request.

**Changelog**

Version | Description
------- | -----------
`3.7.0` | Available since 3.7.0.

Source: [./app/Plugin/Rest.php](app/Plugin/Rest.php), [line 124](app/Plugin/Rest.php#L124-L131)

### `downloadlist_rest_api_filetypes`

*Filter the resulting list of iconsets before we return them.*

@3.7.0 Available since 3.7.0.

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$iconsets` | `array` | List of iconsets.
`$request` | `\WP_REST_Request` | The request.

Source: [./app/Plugin/Rest.php](app/Plugin/Rest.php), [line 169](app/Plugin/Rest.php#L169-L176)

### `downloadlist_link_download_attribute`

*Filter the download attribute for the link.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$download_link_attribute` | `string` | The value.
`$file` | `array` | The attributes for single file.

**Changelog**

Version | Description
------- | -----------
`3.6.0` | Available since 3.6.0.

Source: [./app/Plugin/Init.php](app/Plugin/Init.php), [line 572](app/Plugin/Init.php#L572-L579)

### `downloadlist_link_target_attribute`

*Filter the target attribute for the link.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$link_target` | `string` | The value.
`$file` | `array` | The attributes for single file.

**Changelog**

Version | Description
------- | -----------
`3.6.0` | Available since 3.6.0.

Source: [./app/Plugin/Init.php](app/Plugin/Init.php), [line 593](app/Plugin/Init.php#L593-L600)

### `downloadlist_rel_attribute`

*Filter the rel-attribute.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$rel_attribute` | `string` | The rel-value.
`$file` | `array` | The attributes for single file.

**Changelog**

Version | Description
------- | -----------
`3.5.0` | Available since 3.5.0

Source: [./app/Plugin/Init.php](app/Plugin/Init.php), [line 608](app/Plugin/Init.php#L608-L615)

### `downloadlist_download_button_download_attribute`

*Filter the download attribute for the download button.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$download_button` | `string` | The value.
`$file` | `array` | The attributes for single file.

**Changelog**

Version | Description
------- | -----------
`3.6.0` | Available since 3.6.0.

Source: [./app/Plugin/Init.php](app/Plugin/Init.php), [line 628](app/Plugin/Init.php#L628-L635)

### `downloadlist_download_button_target_attribute`

*Filter the target attribute for the download button.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$download_target_attribute` | `string` | The value.
`$file` | `array` | The attributes for single file.

**Changelog**

Version | Description
------- | -----------
`3.6.0` | Available since 3.6.0.

Source: [./app/Plugin/Init.php](app/Plugin/Init.php), [line 649](app/Plugin/Init.php#L649-L656)

### `downloadlist_mime_labels`

*Filter the list of possible mime labels.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$list` | `array<string,string>` | List of possible mime labels.

**Changelog**

Version | Description
------- | -----------
`4.0.0` | Available since 4.0.0.

Source: [./app/Plugin/Init.php](app/Plugin/Init.php), [line 806](app/Plugin/Init.php#L806-L812)

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

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 42](app/Plugin/Helper.php#L42-L48)

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

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 59](app/Plugin/Helper.php#L59-L66)

### `downloadlist_css_path`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$path` |  | 

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 86](app/Plugin/Helper.php#L86-L86)

### `downloadlist_css_url`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` |  | 

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 106](app/Plugin/Helper.php#L106-L106)

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

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 154](app/Plugin/Helper.php#L154-L160)

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

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 314](app/Plugin/Helper.php#L314-L321)

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

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 360](app/Plugin/Helper.php#L360-L367)

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

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 369](app/Plugin/Helper.php#L369-L376)

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

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 389](app/Plugin/Helper.php#L389-L395)

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

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 447](app/Plugin/Helper.php#L447-L453)

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

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 482](app/Plugin/Helper.php#L482-L489)

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

Source: [./app/Plugin/Helper.php](app/Plugin/Helper.php), [line 631](app/Plugin/Helper.php#L631-L639)

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

Source: [./app/Plugin/Admin/Admin.php](app/Plugin/Admin/Admin.php), [line 190](app/Plugin/Admin/Admin.php#L190-L196)

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

Source: [./app/Iconsets/Iconsets.php](app/Iconsets/Iconsets.php), [line 116](app/Iconsets/Iconsets.php#L116-L125)

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

