# Iconsets

Iconsets wurden mit Version 3.0 dieses Plugins eingeführt. Sie ermöglichen es pro Download-Listen-Block eines von mehreren Iconsets auszuwählen die dann für die Ausgabe der Icons an den Dateien verwendet werden.

Das Plugin liefert selbst bereits folgende Iconsets mit:

* Dashicons = von WordPress selbst bereitgestellte Icons
* FontAweSome = die beliebte Schriftart deren Symbole als Icons verwendet werden
* Custom Icons = ermöglicht es eigene Grafiken als Icons zu verwenden

## Hinweise

FontAweSome wird in der kostenfreien Version 6 in diesem Plugin mitgeliefert.

Das Plugin unterstützt alle von WordPress standardmäßig unterstützten Dateitypen. Abhängig davon, welche Dateitypen die Iconsets unterstützen, wird die Auswahl der möglichen Dateitypen am Download-Listen-Block entsprechend beschränkt. Wenn ein Custom Icon Set z.B. nur PDFs als Icons bereitgestellt, kann man nur PDFs für die betreffende Liste auswählen.

## Eigenes Iconset ergänzen

Es ist möglich eigene Iconsets zu ergänzen, z.B. wenn man selbst eine individuelle Schriftart hierfür entwickelt hat. Dafür ist es notwendig in PHP eine Klasse zu schreiben, die [wie hier beschrieben](Iconset_de.md) aufgebaut ist. Danach kann man per Hook "downloadlist_register_iconset" das eigene Iconset am Plugin anmelden.

## Icons verändern

Über folgende Hooks kann man die Icons beeinflussen:

* downloadlist_fontawesome_icons
* downloadlist_dashicons_icons
