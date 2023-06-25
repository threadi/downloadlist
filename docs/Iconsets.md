# Iconsets

Iconsets were introduced with version 3.0 of this plugin. They allow to select one of several icon sets per download list block which are then used for the output of the icons on the files.

The plugin itself already provides the following icon sets:

* Dashicons = icons provided by WordPress itself
* FontAweSome = the popular font whose icons are used as icons
* Custom Icons = allows you to use your own graphics as icons

## Notes

FontAweSome is included in the free version 6 in this plugin.

The plugin supports all file types supported by WordPress by default. Depending on which file types the icon sets support, the selection of possible file types at the download list block will be limited accordingly. For example, if a Custom Icon Set only provides PDFs as icons, you can only select PDFs for that list.

## Add your own icon set

It is possible to add own icon sets, e.g. if you have developed an individual font for it yourself. For this it is necessary to write a class in PHP, which is built [as described here](Iconset.md). After that you can register your own iconset with the plugin using the hook "downloadlist_register_iconset":

```
/**
* Register the custom iconset.
*
* @param $list
* @return array
*/
function your_custom_function( $list ): array {
  $list[] = My_Custom_Iconset::get_instance();
  return $list;
}
add_filter( 'downloadlist_register_iconset', 'your_custom_function', 10, 1 );
```

## Change icons

With the following hooks you can influence the icons:

* downloadlist_fontawesome_icons
* downloadlist_dashicons_icons
