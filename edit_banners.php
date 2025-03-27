<?php
require 'db_connection.php';
require 'session_manager.php';

// Check if user is admin
$user_id = $_SESSION['user_id'];
$activeCount = $conn->query("SELECT COUNT(*) AS total FROM banners WHERE is_active = 1")->fetch_assoc()['total'];
$sql = "SELECT Status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || $user['Status'] !== 'admin') {
    die("Access denied! Only administrators can edit banners.");
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['banners'])) {
    $uploadDir = 'banners/';
    foreach ($_FILES['banners']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['banners']['error'][$key] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES['banners']['name'][$key]);
            $targetPath = $uploadDir . time() . '_' . $fileName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $stmt = $conn->prepare("INSERT INTO banners (image_path, is_active) VALUES (?, 0)");
                $stmt->bind_param("s", $targetPath);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

// Handle delete image
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Get image path
    $result = $conn->query("SELECT image_path FROM banners WHERE id = $id");
    $row = $result->fetch_assoc();
    if ($row) {
        unlink($row['image_path']); // delete file
        $conn->query("DELETE FROM banners WHERE id = $id"); // delete from DB
    }
    header("Location: edit_banners.php");
    exit;
}

// Handle toggle active/inactive
// Handle toggle active/inactive with limit
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];

    // Check current status of this banner
    $result = $conn->query("SELECT is_active FROM banners WHERE id = $id");
    $banner = $result->fetch_assoc();

    if ($banner) {
        if ($banner['is_active'] == 0) {
            // If trying to activate -> check how many are active
            $activeCount = $conn->query("SELECT COUNT(*) AS total FROM banners WHERE is_active = 1")->fetch_assoc()['total'];
            if ($activeCount >= 5) {
                echo "<script>alert('You can only have up to 5 active banners at a time.'); window.location='edit_banners.php';</script>";
                exit;
            }
        }
        // Toggle the banner's active status
        $conn->query("UPDATE banners SET is_active = NOT is_active WHERE id = $id");
    }

    header("Location: edit_banners.php");
    exit;
}


// Get all banners
$result = $conn->query("SELECT id, image_path, created_at, is_active FROM banners ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Banners - JSCinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <a href="analysis.php" class="btn btn-outline-light">Back to Analysis</a>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="text-center">Manage Banner Images</h2>

    <!-- Upload form -->
    <form method="POST" enctype="multipart/form-data" class="mb-4">
        <label for="banners" class="form-label">Upload new images:</label>
        <input type="file" name="banners[]" id="banners" class="form-control mb-2" multiple required>
        <button type="submit" class="btn btn-success">Upload</button>
    </form>

    <!-- Banner list -->
    <h4>Current Banners</h4>
    <p class="mb-3">
        <strong>Active banners:</strong> <?php echo $activeCount; ?> / 5
    </p>

    <div class="row">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card banner-card text-center">
                <img src="<?php echo htmlspecialchars($row['image_path']); ?>" class="banner-preview card-img-top" alt="Banner">
                    <div class="card-body">
                        <p class="card-text small text-muted"><?php echo $row['created_at']; ?></p>
                        <p>Status:
                            <span class="badge bg-<?php echo $row['is_active'] ? 'success' : 'secondary'; ?> status-badge">
                    <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                </span>
                        </p>
                        <div class="d-flex justify-content-center gap-2 mt-2 flex-wrap">
                            <a href="?toggle=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <?php echo $row['is_active'] ? 'Deactivate' : 'Activate'; ?>
                            </a>
                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this image?');">
                                Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>


        <?php endwhile; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
