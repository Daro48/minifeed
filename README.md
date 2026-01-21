# MiniFeed

Eine moderne Media-Sharing-Webanwendung, mit der Nutzer Bilder und Videos hochladen und in einem scrollbaren Feed teilen können. Ähnlich einem vereinfachten Instagram-Feed, gebaut mit PHP, Docker und MySQL.

## Features

- **Benutzerauthentifizierung** - Registrierung und Login mit Benutzername und Passwort
- **Bild-Upload** - Unterstützung für JPEG, PNG, GIF, WebP
- **Video-Upload** - MP4-Videos mit automatischer Verarbeitung
- **Responsive Design** - Funktioniert auf Desktop und mobilen Geräten
- **Video-Looping** - Videos laufen automatisch in einer Endlosschleife
- **Scrollbarer Feed** - Alle Posts in einem übersichtlichen, scrollbaren Container
- **Modernes UI** - Professionelles Design mit Gradienten und Animationen
- **Docker-Setup** - Einfache Installation mit Docker Compose
- **phpMyAdmin** - Integrierte Datenbankverwaltung

## Tech Stack

- **Backend:** PHP 8.2
- **Webserver:** Apache
- **Datenbank:** MySQL 8.0
- **Container:** Docker & Docker Compose
- **Video-Processing:** FFmpeg
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)

## Voraussetzungen

- Docker Desktop (oder Docker Engine + Docker Compose)
- Git
- Mindestens 2GB freier RAM
- Ports 80 und 8080 müssen frei sein

## Installation

### 1. Repository klonen

```bash
git clone https://github.com/Daro48/minifeed.git
cd minifeed
```

### 2. Docker Container starten

```bash
docker-compose up --build
```

Beim ersten Start werden alle Container erstellt und die Datenbank initialisiert. Dies kann einige Minuten dauern.

### 3. Datenbank einrichten

Die Datenbank wird automatisch erstellt. Du kannst die Tabellen manuell in phpMyAdmin erstellen oder das Projekt verwendet die automatische Tabellenerstellung.

**Tabellenstruktur:**

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type ENUM('image', 'video') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 4. Anwendung öffnen

- **Webanwendung:** http://localhost
- **phpMyAdmin:** http://localhost:8080
  - Server: `db`
  - Benutzername: `root`
  - Passwort: `rootpassword`

## Verwendung

### Registrierung

1. Öffne http://localhost
2. Klicke auf "Jetzt registrieren"
3. Wähle einen Benutzernamen und Passwort
4. Nach erfolgreicher Registrierung wirst du zum Login weitergeleitet

### Login

1. Gib deinen Benutzernamen und Passwort ein
2. Nach erfolgreichem Login gelangst du zum Feed

### Medien hochladen

1. Klicke auf "Datei auswählen" im Upload-Bereich
2. Wähle ein Bild (JPEG, PNG, GIF, WebP) oder Video (MP4)
3. Klicke auf "Hochladen"
4. Dein Post erscheint im Feed

### Feed durchsuchen

- Scroll durch den Feed, um alle Posts zu sehen
- Videos können mit den Standard-Controls abgespielt werden
- Nur ein Video kann gleichzeitig abgespielt werden
- Videos laufen automatisch in einer Endlosschleife

## Projektstruktur

```
minifeed/
├── docker/
│   └── apache/
│       └── 000-default.conf      # Apache VirtualHost Konfiguration
├── public/
│   ├── index.php                 # Login-Seite (Hauptseite)
│   ├── reigster.php              # Registrierungsseite
│   ├── homepage.php              # Feed-Seite
│   ├── upload.php                # Upload-Handler
│   ├── logout.php                # Logout
│   ├── image.php                 # Bild-Auslieferung mit korrekten Headers
│   └── uploads/                  # Hochgeladene Medien
│       ├── images/               # Bilder
│       ├── videos/               # Verarbeitete Videos
│       └── original/             # Original-Videos (optional)
├── src/
│   ├── db.php                    # Datenbankverbindung
│   └── upload_handler.php        # Upload-Verarbeitung
├── Dockerfile                    # PHP/Apache Container Definition
├── docker-compose.yml            # Docker Compose Konfiguration
└── README.md                     # Diese Datei
```

## Konfiguration

### Datenbank-Einstellungen

Die Datenbank-Konfiguration kann in `docker-compose.yml` angepasst werden:

```yaml
environment:
  DB_HOST: db
  DB_NAME: users_db
  DB_USER: root
  DB_PASSWORD: rootpassword
```

### PHP Upload-Limits

Die Upload-Limits sind im `Dockerfile` konfiguriert:

- `upload_max_filesize = 100M`
- `post_max_size = 100M`
- `max_execution_time = 300`
- `memory_limit = 256M`

### Apache-Konfiguration

Die Apache-Konfiguration befindet sich in `docker/apache/000-default.conf` und kann bei Bedarf angepasst werden.

## Entwicklung

### Container neu starten

```bash
docker-compose restart
```

### Container stoppen

```bash
docker-compose down
```

### Container mit Daten löschen

```bash
docker-compose down -v
```

**Wichtig:** Dies löscht alle Daten, einschließlich der Datenbank!

### Logs anzeigen

```bash
docker-compose logs -f php
docker-compose logs -f db
```

### In Container einsteigen

```bash
docker exec -it php-app bash
```

## Troubleshooting

### Bilder werden nicht angezeigt

- Prüfe, ob die Dateien in `public/uploads/images/` existieren
- Prüfe die Dateiberechtigungen: `chmod -R 755 public/uploads`
- Prüfe die Browser-Konsole (F12) auf Fehler

### Videos werden nicht abgespielt

- Stelle sicher, dass die Videos im MP4-Format sind
- Prüfe, ob FFmpeg korrekt installiert ist: `docker exec php-app ffmpeg -version`
- Prüfe die Browser-Konsole auf Fehler

### Datenbank-Verbindungsfehler

- Stelle sicher, dass der MySQL-Container läuft: `docker ps`
- Prüfe die Logs: `docker-compose logs db`
- Warte einige Sekunden nach dem Start, bis MySQL bereit ist

### Port bereits belegt

Wenn Port 80 oder 8080 bereits belegt ist, ändere die Ports in `docker-compose.yml`:

```yaml
ports:
  - "8080:80"  # Statt 80:80
  - "8081:80"  # Statt 8080:80 für phpMyAdmin
```

## Sicherheit

**Wichtig:** Dieses Projekt ist für Entwicklungs- und Lernzwecke gedacht. Für den Produktionseinsatz sollten folgende Sicherheitsmaßnahmen implementiert werden:

- HTTPS/SSL-Zertifikate
- Stärkere Passwort-Anforderungen
- Rate Limiting für Uploads
- Dateityp-Validierung (nicht nur MIME-Type)
- CSRF-Schutz
- SQL-Injection-Schutz (bereits mit Prepared Statements implementiert)
- XSS-Schutz (bereits mit htmlspecialchars implementiert)
- Umgebungsvariablen für sensible Daten (.env)

## License

Dieses Projekt steht unter der MIT License.

## Autor

**Dein Name**

- GitHub: [@Daro48](https://github.com/Daro48)

## Support

Bei Fragen oder Problemen öffne bitte ein Issue auf GitHub.
