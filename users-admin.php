<?php
global $conn;
require 'session_manager.php';
require 'db_connection.php';


// Sprawdzenie uprawnień administratora
$user_id = $_SESSION['user_id'];
$sql = "SELECT Status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['Status'] !== 'admin') {
    die("Access denied! Only admins can add screenings.");
}
$stmt->close();


// Pobranie wszystkich użytkowników
$users_query = $conn->query("SELECT id, imie, nazwisko, email, Status FROM users");

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Users List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Nawigacja -->
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <a href="profile.php" class="btn btn-outline-light">Back to Admin Panel</a>
    </div>
</nav>

<!-- Główna zawartość -->
<div class="container mt-4">
    <h2 class="text-center mb-4">User Management</h2>

    <!-- Wyszukiwanie użytkowników -->
    <div class="mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="Search users by name or email...">
    </div>

    <!-- Tabela użytkowników -->
    <div class="table-responsive">
        <table class="table table-striped text-center align-middle" id="usersTable">
            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Surname</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $users_query->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['imie']); ?></td>
                    <td><?php echo htmlspecialchars($row['nazwisko']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><span class="badge bg-<?php echo $row['Status'] === 'admin' ? 'danger' : 'success'; ?>">
                            <?php echo ucfirst($row['Status']); ?>
                        </span></td>
                    <td>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="edit_user_admin.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete_user_admin.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Funkcja do filtrowania użytkowników w tabeli
    document.getElementById("searchInput").addEventListener("keyup", function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll("#usersTable tbody tr");

        rows.forEach(row => {
            let name = row.cells[1].innerText.toLowerCase();
            let email = row.cells[3].innerText.toLowerCase();

            if (name.includes(filter) || email.includes(filter)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
</script>
</body>
</html>

<?php
$conn->close();
?>
