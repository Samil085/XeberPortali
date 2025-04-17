<?php
require_once 'config/db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$news_id = $_GET['id'];

try {
    $stmt = $db->prepare("SELECT n.*, c.name as category_name 
                         FROM news n 
                         JOIN categories c ON n.category_id = c.id 
                         WHERE n.id = ?");
    $stmt->execute([$news_id]);
    $news = $stmt->fetch();

    if (!$news) {
        header("Location: index.php");
        exit();
    }

    // Görüntülenme sayısını artır
    $stmt = $db->prepare("INSERT INTO news_views (news_id, view_date) VALUES (?, NOW())");
    $stmt->execute([$news_id]);

    // Benzer haberler
    $stmt = $db->prepare("SELECT n.*, c.name as category_name,
                         (SELECT COUNT(*) FROM news_views WHERE news_id = n.id) as view_count
                         FROM news n 
                         JOIN categories c ON n.category_id = c.id 
                         WHERE n.category_id = ? AND n.id != ?
                         ORDER BY n.created_at DESC 
                         LIMIT 5");
    $stmt->execute([$news['category_id'], $news_id]);
    $related_news = $stmt->fetchAll();

    // En çok okunan haberler
    $stmt = $db->query("SELECT n.*, c.name as category_name,
                       (SELECT COUNT(*) FROM news_views WHERE news_id = n.id) as view_count
                       FROM news n 
                       JOIN categories c ON n.category_id = c.id 
                       ORDER BY view_count DESC 
                       LIMIT 5");
    $popular_news = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Xəta baş verdi: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $news['title']; ?> - Xəbər Portalı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #fed525;
            --secondary-color: #222222;
        }
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .top-bar {
            background: var(--secondary-color);
            padding: 10px 0;
        }
        .logo {
            height: 40px;
        }
        .nav-main {
            background: var(--secondary-color);
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .nav-main .nav-link {
            color: #fff !important;
            padding: 15px 20px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 14px;
        }
        .nav-main .nav-link:hover {
            color: var(--primary-color) !important;
        }
        .news-content {
            background: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .news-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .news-meta {
            margin: 20px 0;
            padding: 15px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }
        .news-meta span {
            margin-right: 20px;
            color: #666;
            font-size: 14px;
        }
        .category-badge {
            background: var(--primary-color);
            color: var(--secondary-color);
            padding: 5px 15px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
        }
        .news-text {
            font-size: 16px;
            line-height: 1.8;
            color: #333;
        }
        .news-text p {
            margin-bottom: 20px;
        }
        .popular-news {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .popular-news h3 {
            color: var(--secondary-color);
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
        }
        .popular-news-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .popular-news-item:last-child {
            border-bottom: none;
        }
        .popular-news-item img {
            width: 100px;
            height: 70px;
            object-fit: cover;
            margin-right: 15px;
            border-radius: 3px;
        }
        .popular-news-item h6 {
            margin: 0 0 5px 0;
            font-size: 14px;
            line-height: 1.4;
        }
        .popular-news-item .views {
            font-size: 12px;
            color: #666;
        }
        .weather-widget {
            background: var(--primary-color);
            color: var(--secondary-color);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="index.php">
                    <img src="assets/images/logo.png" alt="Logo" class="logo">
                </a>
                <div class="d-flex align-items-center">
                    <div class="weather-widget me-3">
                        <i class="bi bi-thermometer-half"></i> Bakı: 14°C
                    </div>
                    <div class="social-links">
                        <a href="#" class="text-white me-2"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-2"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-telegram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <nav class="nav-main">
        <div class="container">
            <ul class="nav">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">ANA SƏHİFƏ</a>
                </li>
                <?php
                $stmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
                $categories = $stmt->fetchAll();
                foreach ($categories as $category):
                ?>
                <li class="nav-item">
                    <a class="nav-link" href="category.php?id=<?php echo $category['id']; ?>">
                        <?php echo strtoupper($category['name']); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-8">
                <div class="news-content">
                    <a href="category.php?id=<?php echo $news['category_id']; ?>" class="category-badge mb-3">
                        <?php echo $news['category_name']; ?>
                    </a>
                    <h1 class="mt-3 mb-4"><?php echo $news['title']; ?></h1>
                    
                    <div class="news-meta">
                        <span><i class="bi bi-clock"></i> <?php echo date('d.m.Y H:i', strtotime($news['created_at'])); ?></span>
                        <span><i class="bi bi-eye"></i> <?php echo $news['view_count']; ?> baxış</span>
                    </div>

                    <img src="uploads/<?php echo $news['image']; ?>" class="news-image" alt="<?php echo $news['title']; ?>">
                    
                    <?php if ($news['video']): ?>
                    <div class="video-container mb-4">
                        <video controls class="w-100">
                            <source src="uploads/videos/<?php echo $news['video']; ?>" type="video/mp4">
                            Tarayıcınız video etiketini desteklemiyor.
                        </video>
                    </div>
                    <?php endif; ?>
                    
                    <div class="news-text">
                        <?php echo nl2br($news['content']); ?>
                    </div>
                </div>

                <?php if (!empty($related_news)): ?>
                <div class="popular-news mt-4">
                    <h3>Oxşar xəbərlər</h3>
                    <div class="row">
                        <?php foreach ($related_news as $related): ?>
                        <div class="col-md-6">
                            <div class="popular-news-item">
                                <img src="uploads/<?php echo $related['image']; ?>" alt="<?php echo $related['title']; ?>">
                                <div>
                                    <h6><a href="news.php?id=<?php echo $related['id']; ?>" class="text-dark text-decoration-none">
                                        <?php echo $related['title']; ?>
                                    </a></h6>
                                    <span class="views">
                                        <i class="bi bi-eye"></i> <?php echo $related['view_count']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="popular-news">
                    <h3>İndi oxuyurlar</h3>
                    <?php foreach ($popular_news as $popular): ?>
                    <div class="popular-news-item">
                        <img src="uploads/<?php echo $popular['image']; ?>" alt="<?php echo $popular['title']; ?>">
                        <div>
                            <h6><a href="news.php?id=<?php echo $popular['id']; ?>" class="text-dark text-decoration-none">
                                <?php echo $popular['title']; ?>
                            </a></h6>
                            <span class="views">
                                <i class="bi bi-eye"></i> <?php echo $popular['view_count']; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 