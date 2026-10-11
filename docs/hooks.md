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

Source: [app/Plugin/Helper.php](Plugin/Helper.php), [line 295](Plugin/Helper.php#L295-L302)

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

Source: [app/Plugin/Taxonomies.php](Plugin/Taxonomies.php), [line 191](Plugin/Taxonomies.php#L191-L197)

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

Source: [app/Plugin/Helper.php](Plugin/Helper.php), [line 44](Plugin/Helper.php#L44-L50)

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

Source: [app/Plugin/Helper.php](Plugin/Helper.php), [line 61](Plugin/Helper.php#L61-L68)

### `downloadlist_css_path`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$path` |  | 

Source: [app/Plugin/Helper.php](Plugin/Helper.php), [line 88](Plugin/Helper.php#L88-L88)

### `downloadlist_css_url`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` |  | 

Source: [app/Plugin/Helper.php](Plugin/Helper.php), [line 108](Plugin/Helper.php#L108-L108)

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

Source: [app/Plugin/Helper.php](Plugin/Helper.php), [line 123](Plugin/Helper.php#L123-L129)

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

Source: [app/Plugin/Helper.php](Plugin/Helper.php), [line 268](Plugin/Helper.php#L268-L275)

### `downloadlist_preview_image_size`

*Filter the image size used for preview images.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$size` | `string` | The image size.
`$attachment_id` | `int` | The ID of the attachment.
`$width` | `int` | The width of the preview.

**Changelog**

Version | Description
------- | -----------
`5.0.0` | Available since 5.0.0.

Source: [app/Plugin/Helper.php](Plugin/Helper.php), [line 374](Plugin/Helper.php#L374-L382)

### `downloadlist_folder_taxonomies`

*Filter the list of taxonomies which could be used as folders for download lists.*

The key is the name of the taxonomy for attachments, the value its label in the block editor.

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$taxonomies` | `array<string,string>` | List of taxonomies.

**Changelog**

Version | Description
------- | -----------
`5.0.0` | Available since 5.0.0.

Source: [app/Plugin/Helper.php](Plugin/Helper.php), [line 583](Plugin/Helper.php#L583-L591)

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

Source: [app/Plugin/Helper.php](Plugin/Helper.php), [line 811](Plugin/Helper.php#L811-L818)

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

Source: [app/Plugin/Helper.php](Plugin/Helper.php), [line 820](Plugin/Helper.php#L820-L827)

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

Source: [app/Plugin/Helper.php](Plugin/Helper.php), [line 840](Plugin/Helper.php#L840-L846)

### `downloadlist_icon_filename_suffix`

*Set suffix for generated filename.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$suffix` | `string` | The suffix to use.

**Changelog**

Version | Description
------- | -----------
`3.4.0` | Available since 3.4.0

Source: [app/Plugin/Helper.php](Plugin/Helper.php), [line 898](Plugin/Helper.php#L898-L904)

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

Source: [app/Plugin/Helper.php](Plugin/Helper.php), [line 933](Plugin/Helper.php#L933-L940)

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

Source: [app/Plugin/Helper.php](Plugin/Helper.php), [line 1103](Plugin/Helper.php#L1103-L1111)

### `downloadlist_link_download_attribute`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$download_link_attribute` |  | 
`$file` |  | 

Source: [app/Plugin/Init.php](Plugin/Init.php), [line 652](Plugin/Init.php#L652-L652)

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

Source: [app/Plugin/Init.php](Plugin/Init.php), [line 666](Plugin/Init.php#L666-L673)

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

Source: [app/Plugin/Init.php](Plugin/Init.php), [line 681](Plugin/Init.php#L681-L688)

### `downloadlist_download_button_download_attribute`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$download_button_attribute` |  | 
`$file` |  | 

Source: [app/Plugin/Init.php](Plugin/Init.php), [line 713](Plugin/Init.php#L713-L713)

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

Source: [app/Plugin/Init.php](Plugin/Init.php), [line 727](Plugin/Init.php#L727-L734)

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

Source: [app/Plugin/Init.php](Plugin/Init.php), [line 940](Plugin/Init.php#L940-L946)

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

Source: [app/Plugin/Rest.php](Plugin/Rest.php), [line 229](Plugin/Rest.php#L229-L236)

### `downloadlist_rest_api_filetypes`

*Filter the resulting list of iconsets before we return them.*

@3.7.0 Available since 3.7.0.

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$iconsets` | `array` | List of iconsets.
`$request` | `\WP_REST_Request` | The request.

Source: [app/Plugin/Rest.php](Plugin/Rest.php), [line 274](Plugin/Rest.php#L274-L281)

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

Source: [app/Plugin/Admin/Admin.php](Plugin/Admin/Admin.php), [line 221](Plugin/Admin/Admin.php#L221-L227)

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

Source: [app/Plugin/Admin/Help_System.php](Plugin/Admin/Help_System.php), [line 104](Plugin/Admin/Help_System.php#L104-L110)

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

Source: [app/Plugin/Admin/Help_System.php](Plugin/Admin/Help_System.php), [line 124](Plugin/Admin/Help_System.php#L124-L130)

### `downloadlist_register_iconset`

*Register a single iconset through adding it to the list.*

The iconset must be an object extending "Iconset_Base" and implement Iconset.

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$list` | `array<int,\DownloadListWithIcons\Iconsets\Iconset_Base>` | The list of iconsets.

**Changelog**

Version | Description
------- | -----------
`3.0.0` | Available since 3.0.0.

Source: [app/Iconsets/Iconsets.php](Iconsets/Iconsets.php), [line 122](Iconsets/Iconsets.php#L122-L131)

### `downloadlist_enqueue_all_iconsets`

*Load the styles of all iconsets on every page in frontend, e.g. if download lists are loaded via AJAX.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$false` | `bool` | Set to true to load the styles of all iconsets.

**Changelog**

Version | Description
------- | -----------
`5.0.0` | Available since 5.0.0.

Source: [app/Iconsets/Iconsets.php](Iconsets/Iconsets.php), [line 271](Iconsets/Iconsets.php#L271-L277)

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

Source: [app/Iconsets/Iconsets/Fontawesome.php](Iconsets/Iconsets/Fontawesome.php), [line 230](Iconsets/Iconsets/Fontawesome.php#L230-L245)

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

Source: [app/Iconsets/Iconsets/Fontawesome.php](Iconsets/Iconsets/Fontawesome.php), [line 262](Iconsets/Iconsets/Fontawesome.php#L262-L268)

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

Source: [app/Iconsets/Iconsets/Dashicons.php](Iconsets/Iconsets/Dashicons.php), [line 185](Iconsets/Iconsets/Dashicons.php#L185-L200)

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

Source: [app/Iconsets/Iconsets/Dashicons.php](Iconsets/Iconsets/Dashicons.php), [line 268](Iconsets/Iconsets/Dashicons.php#L268-L274)

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

Source: [app/Iconsets/Iconsets/Bootstrap.php](Iconsets/Iconsets/Bootstrap.php), [line 178](Iconsets/Iconsets/Bootstrap.php#L178-L193)

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

Source: [app/Iconsets/Iconsets/Bootstrap.php](Iconsets/Iconsets/Bootstrap.php), [line 262](Iconsets/Iconsets/Bootstrap.php#L262-L268)

### `downloadlist_themify_icons`

*Filter the list of themify icons. This list is an array with the not optimized
mime type as index and the themify-unicode as value.*

Example:
```
add_filter( 'downloadlist_themify_icons', function( $list ) {
 $list['application/example'] = '\f42';
 return $list;
});
```

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$themify` | `array<string,string>` | List of the icons.

**Changelog**

Version | Description
------- | -----------
`4.1.0` | Available since 4.1.0

Source: [app/Iconsets/Iconsets/Themify.php](Iconsets/Iconsets/Themify.php), [line 180](Iconsets/Iconsets/Themify.php#L180-L195)

### `downloadlist_themify_files`

*Filter the files used for themify.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$files` | `array<int,array<string,mixed>>` | List of the files.

**Changelog**

Version | Description
------- | -----------
`4.1.0` | Available since 4.1.0

Source: [app/Iconsets/Iconsets/Themify.php](Iconsets/Iconsets/Themify.php), [line 264](Iconsets/Iconsets/Themify.php#L264-L270)


<p align="center"><a href="https://github.com/pronamic/wp-documentor"><img src="https://cdn.jsdelivr.net/gh/pronamic/wp-documentor@main/logos/pronamic-wp-documentor.svgo-min.svg" alt="Pronamic WordPress Documentor" width="32" height="32"></a><br><em>Generated by <a href="https://github.com/pronamic/wp-documentor">Pronamic WordPress Documentor</a> <code>1.2.0</code></em><p>

