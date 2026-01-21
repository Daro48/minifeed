<?php
session_start();
require_once __DIR__ . '/../src/db.php';

// Feed laden
$stmt = $pdo->query("
    SELECT posts.*, users.name 
    FROM posts
    JOIN users ON posts.user_id = users.id
    ORDER BY posts.created_at DESC
");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Feed</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            background: #fafafa;
            font-family: Arial, sans-serif;
        }

        .topbar {
            position: sticky;
            top: 0;
            background: white;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }

        .feed {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px 10px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .feed::-webkit-scrollbar {
            width: 8px;
        }

        .feed::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .feed::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .feed::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .post {
            background: white;
            margin-bottom: 25px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .post-header {
            padding: 10px;
            font-weight: bold;
        }

        .post img,
        .post video {
            width: 100%;
            display: block;
        }

        .post video {
            max-height: 500px;
            min-height: 200px;
            object-fit: contain;
            background: #000;
        }

        .post-footer {
            padding: 10px;
            font-size: 12px;
            color: #666;
        }

        .upload-box {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .upload-box input,
        .upload-box button {
            width: 100%;
            margin-top: 10px;
        }
    </style>
    <script>
        function setFeedHeight() {
            const feed = document.querySelector('.feed');
            const topbar = document.querySelector('.topbar');
            const uploadBox = document.querySelector('.upload-box');

            const windowHeight = window.innerHeight;
            const topbarHeight = topbar.offsetHeight;
            const uploadBoxHeight = uploadBox.offsetHeight;
            const margin = 20; // Zusätzlicher Abstand

            const feedHeight = windowHeight - topbarHeight - uploadBoxHeight - margin;
            feed.style.height = feedHeight + 'px';
        }

        window.addEventListener('load', setFeedHeight);
        window.addEventListener('resize', setFeedHeight);

        // Video-Management: Nur ein Video gleichzeitig
        document.addEventListener('DOMContentLoaded', function() {
            const videos = document.querySelectorAll('.post-video');

            videos.forEach(function(video) {
                // Wenn ein Video startet, stoppe alle anderen
                video.addEventListener('play', function() {
                    videos.forEach(function(otherVideo) {
                        if (otherVideo !== video && !otherVideo.paused) {
                            otherVideo.pause();
                            otherVideo.currentTime = 0; // Zurück zum Anfang
                        }
                    });
                });

                // Optional: Video automatisch starten, wenn es sichtbar wird
                // (Intersection Observer für Auto-Play beim Scrollen)
                const observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            // Video ist sichtbar - optional auto-play
                            // entry.target.play().catch(() => {}); // Auskommentiert, da User es manuell starten soll
                        } else {
                            // Video ist nicht sichtbar - stoppe es
                            if (!entry.target.paused) {
                                entry.target.pause();
                                entry.target.currentTime = 0;
                            }
                        }
                    });
                }, {
                    threshold: 0.5 // Video muss zu 50% sichtbar sein
                });

                observer.observe(video);
            });
        });
    </script>
</head>

<body>

    <div class="topbar">
        <div>📸 MiniFeed</div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="index.php">Login</a>
        <?php endif; ?>
    </div>


    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="upload-box">
            <form action="/upload.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="media" accept="image/*,video/mp4" required>
                <button type="submit">Hochladen</button>
            </form>
        </div>
    <?php else: ?>
        <div class="upload-box" style="text-align:center;">
            <p>
                <a href="login.php">Einloggen</a> oder
                <a href="reigster.php">Registrieren</a>,
                um Inhalte hochzuladen.
            </p>
        </div>
    <?php endif; ?>


    <div class="feed">

        <?php if (count($posts) === 0): ?>
            <p>Noch keine Beiträge.</p>
        <?php endif; ?>

        <?php foreach ($posts as $post): ?>
            <div class="post">
                <div class="post-header">
                    <?= htmlspecialchars($post['name']) ?>
                </div>

                <?php if ($post['file_type'] === 'image'): ?>
                    <img src="/image.php?file=<?= urlencode($post['file_path']) ?>"
                        alt="Bild von <?= htmlspecialchars($post['name']) ?>"
                        loading="lazy"
                        decoding="async"
                        style="width: 100%; height: auto; display: block;">
                <?php elseif ($post['file_type'] === 'video'): ?>
                    <?php
                    $videoPath = "/uploads/videos/" . htmlspecialchars($post['file_path']);
                    ?>
                    <video class="post-video" controls preload="metadata" playsinline loop
                        style="width:100%; height:auto; background:#000;">
                        <source src="<?= $videoPath ?>" type="video/mp4">
                        Dein Browser unterstützt das Video-Tag nicht.
                    </video>
                <?php endif; ?>

                <div class="post-footer">
                    <?= date('d.m.Y H:i', strtotime($post['created_at'])) ?>
                </div>
            </div>
        <?php endforeach; ?>

    </div>

</body>

</html>