<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit();
}

$category_id = $_GET['id'];

// Kategori bilgilerini al
$stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: categories.php");
    exit();
}

// Kategori güncelleme
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $slug = strtolower(str_replace(' ', '-', $name));
    
    try {
        $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $category_id]);
        $success = "Kateqoriya uğurla yeniləndi!";
        
        // Güncel kategori bilgilerini al
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Xəta baş verdi: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Kateqoriya Redaktə</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
        }
        .content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <h3 class="mb-4">Admin Panel</h3>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2">
                        <a href="index.php" class="nav-link">
                            <i class="bi bi-newspaper"></i> Xəbərlər
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="add_news.php" class="nav-link">
                            <i class="bi bi-plus-circle"></i> Yeni xəbər
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="categories.php" class="nav-link active">
                            <i class="bi bi-tags"></i> Kateqoriyalar
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="logout.php" class="nav-link">
                            <i class="bi bi-box-arrow-right"></i> Çıxış
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Kateqoriya Redaktə</h2>
                    <a href="categories.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Geri
                    </a>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Kateqoriya Adı</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($category['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" class="form-control" value="<?php echo $category['slug']; ?>" readonly>
                                <small class="text-muted">Slug avtomatik olaraq kateqoriya adından yaradılır.</small>
                            </div>
                            <button type="submit" class="btn btn-primary">Yadda saxla</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 