<?php
require "db_connection.php";

// Pobierz tylko aktywne obrazki banera
$result = $conn->query("SELECT image_path FROM banners WHERE is_active = 1 ORDER BY created_at DESC");

echo '<div class="banner">';

$isFirst = true;
while ($row = $result->fetch_assoc()) {
    $class = $isFirst ? 'active' : '';
    echo '<img src="' . htmlspecialchars($row['image_path']) . '" class="' . $class . '" alt="Banner">';
    $isFirst = false;
}

echo '</div>';

$conn->close();
?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const images = document.querySelectorAll('.banner img');
        let currentIndex = 0;

        function changeImage() {
            images[currentIndex].classList.remove('active');
            currentIndex = (currentIndex + 1) % images.length;
            images[currentIndex].classList.add('active');
        }

        setInterval(changeImage, 3000);
    });
</script>
