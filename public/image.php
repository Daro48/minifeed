<?php
// Bild-Auslieferung mit korrekten Headern für mobile Geräte
$file = $_GET['file'] ?? '';

if (empty($file)) {
    http_response_code(404);
    die('Datei nicht gefunden');
}

// Sicherheit: Nur Dateien aus dem images-Ordner
$file = basename($file);
$imagePath = __DIR__ . '/uploads/images/' . $file;

if (!file_exists($imagePath)) {
    http_response_code(404);
    die('Datei nicht gefunden');
}

// MIME-Type basierend auf Dateiendung bestimmen
$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$mimeTypes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp'
];

$mimeType = $mimeTypes[$extension] ?? 'image/jpeg';

// Headers setzen
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($imagePath));
header('Cache-Control: public, max-age=31536000');
header('Access-Control-Allow-Origin: *');

// Bild ausgeben
readfile($imagePath);
exit;