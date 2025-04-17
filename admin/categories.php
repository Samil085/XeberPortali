<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Kategori ekleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = $_POST['name'];
    $slug = strtolower(str_replace(' ', '-', $name));
    
    try {
        $stmt = $db->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
        $success = "Kateqoriya uğurla əlavə edildi!";
    } catch (PDOException $e) {
        $error = "Xəta baş verdi: " . $e->getMessage();
    }
}

// Kategorileri al
$stmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

// Kategori silme işlemi
if (isset($_POST['delete'])) {
    $category_id = $_POST['category_id'];
    
    try {
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        
        header("Location: categories.php");
        exit();
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
    <title>Admin Panel - Kateqoriyalar</title>
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
                    <h2>Kateqoriyalar</h2>
                    <a href="add_category.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Yeni Kateqoriya
                    </a>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ad</th>
                                <th>Yaradılma tarixi</th>
                                <th>Əməliyyatlar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td><?php echo $category['name']; ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($category['created_at'])); ?></td>
                                <td>
                                    <form method="POST" action="" class="d-inline" onsubmit="return confirm('Bu kateqoriyanı silmək istədiyinizə əminsiniz?');">
                                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 