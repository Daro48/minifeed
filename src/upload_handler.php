<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

// Prüfe ob Datei hochgeladen wurde
if (!isset($_FILES['media']) || $_FILES['media']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'Die Datei ist zu groß (upload_max_filesize überschritten)',
        UPLOAD_ERR_FORM_SIZE => 'Die Datei ist zu groß (MAX_FILE_SIZE überschritten)',
        UPLOAD_ERR_PARTIAL => 'Die Datei wurde nur teilweise hochgeladen',
        UPLOAD_ERR_NO_FILE => 'Keine Datei wurde hochgeladen',
        UPLOAD_ERR_NO_TMP_DIR => 'Temporäres Verzeichnis fehlt',
        UPLOAD_ERR_CANT_WRITE => 'Fehler beim Schreiben der Datei',
        UPLOAD_ERR_EXTENSION => 'Eine PHP-Erweiterung hat den Upload gestoppt'
    ];
    $errorCode = $_FILES['media']['error'] ?? 'unbekannt';
    $errorMsg = $errorMessages[$errorCode] ?? 'Unbekannter Upload-Fehler: ' . $errorCode;
    error_log("Upload-Fehler: " . $errorMsg);
    die("Upload-Fehler: " . $errorMsg);
}

$file = $_FILES['media'];

$images = ['image/jpeg', 'image/png'];
$videos = ['video/mp4', 'video/x-m4v', 'video/quicktime', 'video/x-msvideo', 'video/webm'];

// Dateierweiterung extrahieren
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$mimeType = $file['type'];

// Absoluter Pfad verwenden
$basePath = '/var/www/html/public/uploads/';

$type = null;
$dir = null;

// Prüfe MIME-Type oder Dateierweiterung
if (in_array($mimeType, $images) || in_array($fileExtension, ['jpg', 'jpeg', 'png'])) {
    $type = 'image';
    $dir = $basePath . 'images/';
} elseif (in_array($mimeType, $videos) || in_array($fileExtension, ['mp4', 'm4v', 'mov', 'avi', 'webm'])) {
    $type = 'video';
    $dir = $basePath . 'videos/';
} else {
    // Debug-Informationen ausgeben
    error_log("Unbekannter Dateityp - MIME: " . $mimeType . ", Extension: " . $fileExtension);
    die("Dateityp nicht unterstützt. MIME-Type: " . $mimeType . ", Erweiterung: " . $fileExtension . ". Erlaubt: JPEG, PNG, MP4, MOV, AVI, WebM");
}

// Verzeichnis erstellen falls es nicht existiert (mit Fehlerbehandlung)
if (!is_dir($dir)) {
    if (!mkdir($dir, 0755, true)) {
        error_log("Konnte Verzeichnis nicht erstellen: " . $dir);
        die("Upload-Verzeichnis konnte nicht erstellt werden. Bitte manuell erstellen: " . $dir);
    }
}

// Prüfe ob Verzeichnis beschreibbar ist
if (!is_writable($dir)) {
    error_log("Verzeichnis ist nicht beschreibbar: " . $dir);
    die("Verzeichnis ist nicht beschreibbar: " . $dir);
}

$filename = uniqid() . '_' . basename($file['name']);
$targetPath = $dir . $filename;

// Prüfe ob temporäre Datei existiert
if (!file_exists($file['tmp_name'])) {
    error_log("Temporäre Datei existiert nicht: " . $file['tmp_name']);
    die("Temporäre Datei existiert nicht");
}

// Versuche Datei zu verschieben
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    $lastError = error_get_last();
    error_log("Upload fehlgeschlagen. Quelle: " . $file['tmp_name'] . ", Ziel: " . $targetPath);
    error_log("PHP Error: " . ($lastError ? $lastError['message'] : 'Kein Fehler gefunden'));
    error_log("Verzeichnis existiert: " . (is_dir($dir) ? 'ja' : 'nein'));
    error_log("Verzeichnis beschreibbar: " . (is_writable($dir) ? 'ja' : 'nein'));
    die("Upload fehlgeschlagen. Bitte Logs prüfen.");
}

$stmt = $pdo->prepare(
    "INSERT INTO posts (user_id, file_path, file_type) VALUES (?, ?, ?)"
);
$stmt->execute([
    $_SESSION['user_id'],
    $filename,
    $type
]);

header("Location: /homepage.php");
exit();