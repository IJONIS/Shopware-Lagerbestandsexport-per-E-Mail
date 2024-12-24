# Lagerbestandsexport Plugin für Shopware 6

Dieses Plugin ermöglicht den automatischen Export von Lagerbeständen per E-Mail in Shopware 6.

## Systemanforderungen

* Shopware 6.6.5.1 oder höher
* PHP 8.1 oder höher

## Features

- Automatischer Export von Produkten und deren Lagerbeständen als CSV
- Export enthält:
  - Artikelnummer (SKU)
  - EAN
  - Produktname
  - Lagerbestand
- Konfigurierbare E-Mail-Adressen für den Versand
- Test-Funktion für die E-Mail-Konfiguration
- Lokale Speicherung der Export-Dateien
- Mehrsprachige Unterstützung (Deutsch/Englisch)
- Geplante Tasks für automatische Exports
- Kommandozeilen-Interface für manuelle Exports

## Installation

### Über den Plugin Manager (empfohlen)

1. Laden Sie die ZIP-Datei des Plugins in den Plugin Manager Ihrer Shopware 6 Installation hoch
2. Installieren Sie das Plugin
3. Aktivieren Sie das Plugin
4. Leeren Sie den Cache
5. Konfigurieren Sie die E-Mail-Einstellungen

### Manuelle Installation

1. Laden Sie das Plugin in das Verzeichnis `custom/plugins/Lagerbestandsexport` Ihrer Shopware 6 Installation
2. Öffnen Sie die Kommandozeile und navigieren Sie zum Shopware-Hauptverzeichnis
3. Führen Sie folgende Befehle aus:
   ```bash
   bin/console plugin:refresh
   bin/console plugin:install --activate Lagerbestandsexport
   bin/console cache:clear
   ```
4. Konfigurieren Sie die E-Mail-Einstellungen

## Konfiguration

### Plugin-Konfiguration

Die Konfiguration erfolgt in der Shopware Administration unter **Einstellungen > System > Plugins > Lagerbestandsexport**:

- **Export Interval:** Wählen Sie das gewünschte Exportintervall (täglich/wöchentlich/monatlich)
- **Export Email Addresses:** Geben Sie die E-Mail-Adressen für den Versand an (durch Komma getrennt)
- **Sender Email Address:** Absender-E-Mail-Adresse
- **Email Subject:** Betreff der Export-E-Mail (unterstützt `%date%` Platzhalter)

### E-Mail-Konfiguration

Das Plugin nutzt die Standard-SMTP-Konfiguration von Shopware. Stellen Sie sicher, dass diese korrekt eingerichtet ist unter:
**Einstellungen > System > Mailer-Konfiguration**

## Verwendung

### Administration

1. Navigieren Sie zu **Einstellungen > System > Plugins > Lagerbestandsexport**
2. Konfigurieren Sie die E-Mail-Einstellungen
3. Nutzen Sie den "Test-E-Mail senden" Button für einen manuellen Test-Export
4. Die Export-Dateien werden im Verzeichnis `files/export` gespeichert
5. Die konfigurierten E-Mail-Empfänger erhalten den Export als CSV-Anhang

### Kommandozeile

Sie können den Export auch manuell über die Kommandozeile ausführen:

```bash
bin/console lagerbestandsexport:export
```

### Automatischer Export

Der Export wird automatisch gemäß dem konfigurierten Intervall ausgeführt (Standard: täglich).

## Debugging

Bei Problemen mit dem E-Mail-Versand:

1. Überprüfen Sie die SMTP-Konfiguration in Shopware
2. Testen Sie den E-Mail-Versand mit dem Test-Button
3. Prüfen Sie die Logs unter `var/log/prod-{date}.log`

### Bekannte Probleme und Lösungen

1. **E-Mails kommen nicht an:**
   - Überprüfen Sie die SMTP-Konfiguration in Shopware
   - Stellen Sie sicher, dass die E-Mail-Adressen korrekt formatiert sind
   - Prüfen Sie die Firewall-Einstellungen für SMTP-Ports

2. **Test-Button funktioniert nicht:**
   - Leeren Sie den Browser-Cache
   - Kompilieren Sie die Administration neu:
     ```bash
     bin/console assets:install
     bin/console cache:clear
     ```

## Support

Bei Fragen oder Problemen:
- Erstellen Sie ein Issue auf GitHub
- Kontaktieren Sie uns unter support@ijonis.com
- Besuchen Sie unsere Support-Seite: https://www.ijonis.com/support

## Changelog

Eine detaillierte Liste der Änderungen finden Sie in der [CHANGELOG.md](CHANGELOG.md).

## Lizenz

Dieses Plugin ist unter der MIT-Lizenz lizenziert. Weitere Informationen finden Sie in der [LICENSE.md](LICENSE.md).
