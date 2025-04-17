<?php
require_once 'config/db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$category_id = $_GET['id'];

// Kateqoriyanı əldə et
$stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: index.php");
    exit();
}

// Səhifələmə
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;

// Kateqoriyaya aid xəbərləri əldə et
$stmt = $db->prepare("SELECT n.*, c.name as category_name 
                      FROM news n 
                      JOIN categories c ON n.category_id = c.id 
                      WHERE n.category_id = ? AND n.status = 'active' 
                      ORDER BY n.created_at DESC 
                      LIMIT ? OFFSET ?");
$stmt->execute([$category_id, $per_page, $offset]);
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ümumi xəbər sayını əldə et
$stmt = $db->prepare("SELECT COUNT(*) FROM news WHERE category_id = ? AND status = 'active'");
$stmt->execute([$category_id]);
$total_news = $stmt->fetchColumn();
$total_pages = ceil($total_news / $per_page);
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category['name']; ?> - Xəbər Portalı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .news-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .news-card:hover {
            transform: translateY(-5px);
        }
        .news-image {
            height: 200px;
            object-fit: cover;
        }
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Xəbər Portalı</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Ana Səhifə</a>
                    </li>
                    <?php
                    $stmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($categories as $cat):
                    ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $cat['id'] == $category_id ? 'active' : ''; ?>" 
                           href="category.php?id=<?php echo $cat['id']; ?>">
                            <?php echo $cat['name']; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="admin/login.php" class="btn btn-outline-light">Admin Panel</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-primary text-white py-5">
        <div class="container">
            <h1 class="display-4"><?php echo $category['name']; ?></h1>
            <p class="lead"><?php echo $total_news; ?> xəbər</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row">
            <?php foreach ($news as $item): ?>
            <div class="col-md-4">
                <div class="card news-card">
                    <img src="uploads/<?php echo $item['image']; ?>" class="card-img-top news-image" alt="<?php echo $item['title']; ?>">
                    <span class="badge bg-primary category-badge"><?php echo $item['category_name']; ?></span>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $item['title']; ?></h5>
                        <p class="card-text"><?php echo substr(strip_tags($item['content']), 0, 100) . '...'; ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><?php echo date('d.m.Y', strtotime($item['created_at'])); ?></small>
                            <a href="news.php?id=<?php echo $item['id']; ?>" class="btn btn-primary">Ətraflı</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?id=<?php echo $category_id; ?>&page=<?php echo $page - 1; ?>">Əvvəlki</a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?id=<?php echo $category_id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?id=<?php echo $category_id; ?>&page=<?php echo $page + 1; ?>">Sonrakı</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Xəbər Portalı</h5>
                    <p>Ən son xəbərlər və yeniliklər</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>&copy; <?php echo date('Y'); ?> Bütün hüquqlar qorunur.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 