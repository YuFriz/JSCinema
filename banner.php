<?php
require "db_connection.php";

// Pobierz tylko aktywne obrazki banera
$result = $conn->query("SELECT image_path FROM banners WHERE is_active = 1 ORDER BY created_at DESC");

$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = $row['image_path'];
}
$conn->close();
?>

<div class="banner-container">
    <button class="banner-btn prev">&#10094;</button>

    <div class="banner">
        <?php foreach ($images as $index => $path): ?>
            <img src="<?= htmlspecialchars($path) ?>" class="<?= $index === 0 ? 'active' : '' ?>" alt="Banner">
        <?php endforeach; ?>
    </div>

    <button class="banner-btn next">&#10095;</button>

    <div class="dots text-center mt-2">
        <?php foreach ($images as $index => $path): ?>
            <span class="dot <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>"></span>
        <?php endforeach; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const images = document.querySelectorAll('.banner img');
        const dots = document.querySelectorAll('.dot');
        const prevBtn = document.querySelector('.banner-btn.prev');
        const nextBtn = document.querySelector('.banner-btn.next');
        let currentIndex = 0;
        let interval;

        function showImage(index) {
            images.forEach(img => img.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            images[index].classList.add('active');
            dots[index].classList.add('active');
            currentIndex = index;
        }

        function nextImage() {
            let nextIndex = (currentIndex + 1) % images.length;
            showImage(nextIndex);
        }

        function prevImage() {
            let prevIndex = (currentIndex - 1 + images.length) % images.length;
            showImage(prevIndex);
        }

        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                showImage(parseInt(dot.dataset.index));
                resetInterval();
            });
        });

        nextBtn.addEventListener('click', () => {
            nextImage();
            resetInterval();
        });

        prevBtn.addEventListener('click', () => {
            prevImage();
            resetInterval();
        });

        function startInterval() {
            interval = setInterval(nextImage, 10000);
        }

        function resetInterval() {
            clearInterval(interval);
            startInterval();
        }

        startInterval();
    });
</script>
