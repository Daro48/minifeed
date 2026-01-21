<?php
session_start();
require_once __DIR__ . '/../src/db.php';

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
    <link rel="stylesheet" href="assets/style.css">

    <script>
        function setFeedHeight() {
            const feed = document.querySelector('.feed');
            const topbar = document.querySelector('.topbar');
            const uploadBox = document.querySelector('.upload-box');

            const windowHeight = window.innerHeight;
            const topbarHeight = topbar.offsetHeight;
            const margin = 20;

            let uploadBoxHeight = 0;
            if (uploadBox && uploadBox.classList.contains('show')) {
                uploadBoxHeight = uploadBox.offsetHeight;
            }

            const feedHeight = windowHeight - topbarHeight - uploadBoxHeight - margin;
            feed.style.height = feedHeight + 'px';
        }

        window.addEventListener('load', setFeedHeight);
        window.addEventListener('resize', setFeedHeight);

        document.addEventListener('DOMContentLoaded', function() {
            const addButton = document.querySelector('.add_button button');
            const uploadBox = document.querySelector('.upload-box');

            if (addButton && uploadBox) {
                addButton.addEventListener('click', function() {
                    uploadBox.classList.toggle('show');
                    setFeedHeight();
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const videos = document.querySelectorAll('.post-video');

            videos.forEach(function(video) {
                video.addEventListener('play', function() {
                    videos.forEach(function(otherVideo) {
                        if (otherVideo !== video && !otherVideo.paused) {
                            otherVideo.pause();
                            otherVideo.currentTime = 0;
                        }
                    });
                });

                const observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (!entry.isIntersecting) {
                            if (!entry.target.paused) {
                                entry.target.pause();
                                entry.target.currentTime = 0;
                            }
                        }
                    });
                }, {
                    threshold: 0.5
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

    <div class="add_button">
        <button>+</button>
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
                        decoding="async">
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