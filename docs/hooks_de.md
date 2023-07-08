# Hooks

## downloadlist_bootstrap_icons

Erlaubt die Veränderung des Bootstrap-Iconset.

Verwendung:
```
add_filter( 'downloadlist_bootstrap_icons', function( $list ) {
 $list['application/example'] = '\f42';
 return $list;
})
```

## downloadlist_dashicons_icons

Erlaubt die Veränderung des Bootstrap-Iconset.

Verwendung:
```
add_filter( 'downloadlist_dashicons_icons', function( $list ) {
 $list['application/example'] = '\f42';
 return $list;
})
```

## downloadlist_fontawesome_icons

Erlaubt die Veränderung des Bootstrap-Iconset.

Verwendung:
```
add_filter( 'downloadlist_fontawesome_icons', function( $list ) {
 $list['application/example'] = '\f42';
 return $list;
})
```

## downloadlist_register_iconset

Ermöglicht die Ergänzung eines eigenen Iconsets. Siehe [iconset_de.md](iconset_de.md).
