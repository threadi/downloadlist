# Download List with Icons
Contributors: threadi
Tags: list, download, icons
Requires at least: 5.8
Tested up to: 6.0
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Stable tag: 1.0.5

## Description

This plugins provides a Gutenberg block to manage a download list with file type specific icons.

#### Features:

- Choose files from media library
- Output chosen files as list with download-link, file-title, file-size and (optional) file-description
- Drag & Drop sorting for the list
- Remove files from list

### Deutsch

Dieses Plugin bietet einen Gutenberg-Block für die Erfassung einer Download-Liste mit Dateityp-spezifischen Symbolen.

#### Features

- Dateien aus der Medieathek auswählen
- Ausgabe der ausgewählten Dateien als Liste mit Download-Link, Datei-Titel, Dateigröße und (optional) Dateibeschreibung
- Drag & Drop Sortierung für die Liste
- Dateien aus der Liste entfernen

---

## Installation

### English

1. Upload "downloadlist" to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Add the Download List Block to the post or page where you want to show the downloadlist. Choose the file(s) you wish to present.

### Deutsch

1. Lade "downloadlist" in das Verzeichnis "/wp-content/plugins/\" hoch.
2. Aktivieren Sie das Plugin über das Menü "Plugins" in WordPress.
3. Fügen Sie den Download List Block zu dem Beitrag oder der Seite hinzu, auf der Sie die Downloadliste anzeigen möchten. Wählen Sie die Datei(en) aus, die Sie bereitstellen möchten.

## Screenshots

### English

1. After adding the Block you have to choose the files.
2. After adding files to the Block they will be listed.
3. The files will be listed in frontend.

### Deutsch

1. Nach dem Hinzufügen des Blocks kann man die Dateien auswählen.
2. Nach dem Hinzufügen der Dateien werden diese angezeigt.
3. Im Frontend sieht man die ausgewählten Dateien.

## Changelog

### 1.0
* Initial commit

### 1.0.1
* Fixed issue with 3rd-party dependency
* Updated format for Changelog

### 1.0.2
* Updated dependencies

### 1.0.3
* Updated dependencies

### 1.0.4
* Updated compatibility-flag for Wordpress 6.0

= 1.0.5 =
* replace serialize_block in favor of render_block for better compatibility with other blocks
