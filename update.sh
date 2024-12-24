#!/bin/bash

# Aktuellen Branch speichern
current_branch=$(git rev-parse --abbrev-ref HEAD)

# Änderungen stagen
git stash

# Auf main Branch wechseln
git checkout main

# Änderungen von GitHub holen
git pull origin main

# Composer Update ausführen
composer update

# Cache leeren
bin/console cache:clear

# Plugin aktualisieren
bin/console plugin:update Lagerbestandsexport

# Zurück zum ursprünglichen Branch
git checkout $current_branch

# Gestagete Änderungen wiederherstellen
git stash pop

echo "Update abgeschlossen!"
