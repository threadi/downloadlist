# hooks

## downloadlist_bootstrap_icons

Allows to modify the bootstrap iconset.

Usage:
```
add_filter( 'downloadlist_bootstrap_icons', function( $list ) {
 $list['application/example'] = '\f42';
 return $list;
})
```

## downloadlist_dashicons_icons

Allows to modify the dashicons iconset.

Usage:
```
add_filter( 'downloadlist_dashicons_icons', function( $list ) {
 $list['application/example'] = '\f42';
 return $list;
})
```

## downloadlist_fontawesome_icons

Allows to modify the fontawesome iconset.

Usage:
```
add_filter( 'downloadlist_fontawesome_icons', function( $list ) {
 $list['application/example'] = '\f42';
 return $list;
})
```

## downloadlist_register_iconset

Allows the addition of a custom iconset. See [iconset_en.md](iconset_en.md).
