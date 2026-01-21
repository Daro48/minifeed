<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Nur POST-Requests erlaubt');
}

session_start();
require_once __DIR__ . '/../src/db.php';

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

if (!isset($_FILES['media'])) {
    die('Keine Datei hochgeladen');
}

$file = $_FILES['media'];
$mime = $file['type'];

$baseDir = __DIR__ . '/../public/uploads';
$imageDir = $baseDir . '/images';
$videoDir = $baseDir . '/videos';
$origDir  = $baseDir . '/original';

@mkdir($imageDir, 0777, true);
@mkdir($videoDir, 0777, true);
@mkdir($origDir, 0777, true);

$filename = uniqid() . '.mp4';

if (str_starts_with($mime, 'image/')) {

    move_uploaded_file($file['tmp_name'], "$imageDir/$filename");

    $stmt = $pdo->prepare(
        "INSERT INTO posts (user_id, file_path, file_type) VALUES (?,?,?)"
    );
    $stmt->execute([$_SESSION['user_id'], $filename, 'image']);

    header('Location: /homepage.php');
    exit;
}

if ($mime === 'video/mp4') {

    $origPath = "$origDir/$filename";
    $webPath  = "$videoDir/$filename";

    move_uploaded_file($file['tmp_name'], $origPath);

    $cmd = "ffmpeg -y -i \"$origPath\" "
        . "-map 0:v:0 -map 0:a:0 "
        . "-c:v libx264 -profile:v baseline -level 3.0 "
        . "-pix_fmt yuv420p -movflags +faststart "
        . "-g 30 -keyint_min 30 -sc_threshold 0 "
        . "-c:a aac -ac 2 -ar 44100 -b:a 128k "
        . "\"$webPath\"";



    exec($cmd . " 2>&1", $out, $code);

    if ($code !== 0 || !file_exists($webPath)) {
        echo "<pre>";
        print_r($out);
        echo "</pre>";
        exit('FFmpeg Fehler');
    }

    $stmt = $pdo->prepare(
        "INSERT INTO posts (user_id, file_path, file_type) VALUES (?,?,?)"
    );
    $stmt->execute([$_SESSION['user_id'], $filename, 'video']);

    header('Location: /homepage.php');
    exit;
}

die('Dateityp nicht erlaubt');
