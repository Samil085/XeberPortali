<?php
require_once 'config/db.php';
session_start();

// Manşet haberler (son 5 haber)
$stmt = $db->query("SELECT n.*, c.name as category_name, 
                    (SELECT COUNT(*) FROM news_views WHERE news_id = n.id) as view_count 
                    FROM news n 
                    JOIN categories c ON n.category_id = c.id 
                    ORDER BY n.created_at DESC 
                    LIMIT 5");
$featured_news = $stmt->fetchAll();

// En çok okunan haberler
$stmt = $db->query("SELECT n.*, c.name as category_name, 
                    (SELECT COUNT(*) FROM news_views WHERE news_id = n.id) as view_count 
                    FROM news n 
                    JOIN categories c ON n.category_id = c.id 
                    ORDER BY view_count DESC 
                    LIMIT 6");
$popular_news = $stmt->fetchAll();

// Son dakika haberleri
$stmt = $db->query("SELECT n.*, c.name as category_name 
                    FROM news n 
                    JOIN categories c ON n.category_id = c.id 
                    WHERE n.is_breaking = 1 
                    ORDER BY n.created_at DESC 
                    LIMIT 5");
$breaking_news = $stmt->fetchAll();

// Kategorileri al
$stmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

// Kategorilere göre haberler
$category_news = [];
foreach ($categories as $category) {
    $stmt = $db->prepare("SELECT n.*, 
                         (SELECT COUNT(*) FROM news_views WHERE news_id = n.id) as view_count 
                         FROM news n 
                         WHERE n.category_id = ? 
                         ORDER BY n.created_at DESC 
                         LIMIT 4");
    $stmt->execute([$category['id']]);
    $category_news[$category['id']] = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xəbər Portalı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <style>
        :root {
            --primary-color: #fed525;
            --secondary-color: #222222;
            --accent-color: #ff4444;
            --transition: all 0.3s ease;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .top-bar {
            background: var(--secondary-color);
            padding: 8px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .main-content {
            margin-top: 100px;
        }
        .logo {
            height: 32px;
        }
        .nav-main {
            background: var(--secondary-color);
            position: fixed;
            width: 100%;
            top: 48px;
            z-index: 999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .nav-main .container {
            display: flex;
            align-items: center;
            height: 52px;
        }
        .nav-main .navbar-nav {
            display: flex;
            align-items: center;
            flex-direction: row;
            margin: 0;
            padding: 0;
            gap: 5px;
            height: 100%;
        }
        .nav-main .nav-item {
            position: relative;
            height: 100%;
            display: flex;
            align-items: center;
        }
        .nav-main .nav-link {
            color: #fff;
            padding: 0 20px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 13px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            height: 100%;
            position: relative;
            text-decoration: none;
            white-space: nowrap;
        }
        .nav-main .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        .nav-main .nav-link:hover::after,
        .nav-main .nav-link.active::after {
            width: 100%;
        }
        .nav-main .nav-link:hover,
        .nav-main .nav-link.active {
            color: var(--primary-color);
        }
        .nav-main .dropdown-menu {
            background: var(--secondary-color);
            border: none;
            border-radius: 8px;
            margin: 0;
            padding: 8px 0;
            min-width: 180px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            display: none;
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .nav-main .nav-item:hover .dropdown-menu {
            display: block;
            opacity: 1;
            visibility: visible;
            animation: fadeInDown 0.3s ease;
        }
        .nav-main .dropdown-item {
            color: #fff;
            padding: 8px 20px;
            font-size: 13px;
            transition: all 0.3s ease;
            display: block;
            text-decoration: none;
            text-align: left;
            position: relative;
        }
        .nav-main .dropdown-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 0;
            background: var(--primary-color);
            transition: all 0.3s ease;
        }
        .nav-main .dropdown-item:hover::before {
            width: 3px;
        }
        .nav-main .dropdown-item:hover,
        .nav-main .dropdown-item.active {
            background: rgba(255,255,255,0.05);
            color: var(--primary-color);
            padding-left: 25px;
        }
        .nav-main .dropdown-toggle::after {
            content: '\F282';
            font-family: 'Bootstrap Icons';
            border: none;
            vertical-align: middle;
            margin-left: 5px;
            font-size: 12px;
        }
        @media (max-width: 991px) {
            .nav-main .dropdown-menu {
                position: static;
                box-shadow: none;
                background: rgba(255,255,255,0.05);
                border-radius: 0;
            }
            .nav-main .nav-link {
                padding: 15px;
            }
        }
        .breaking-news {
            background: var(--accent-color);
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        .breaking-news .container {
            overflow: hidden;
            position: relative;
        }
        .breaking-news-label {
            background: var(--secondary-color);
            color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: bold;
            margin-right: 15px;
            display: inline-block;
            position: relative;
            z-index: 2;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .breaking-news-container {
            display: inline-flex;
            align-items: center;
            position: relative;
            white-space: nowrap;
            padding-left: 100%;
        }
        .breaking-news-item {
            display: inline-block;
            margin-right: 50px;
            font-weight: 500;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        .breaking-news-item:hover {
            color: var(--primary-color);
            transform: translateY(-1px);
        }
        @keyframes scroll-left {
            0% { transform: translateX(0); }
            100% { transform: translateX(-200%); }
        }
        .featured-news {
            position: relative;
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .featured-news img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .featured-news:hover img {
            transform: scale(1.05);
        }
        .featured-news-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 30px;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
            border-radius: 0 0 10px 10px;
            color: white;
        }
        .news-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            height: 100%;
            transition: var(--transition);
            position: relative;
        }
        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .news-card .image-wrapper {
            position: relative;
            overflow: hidden;
            padding-top: 65%;
        }
        .news-card img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }
        .news-card:hover img {
            transform: scale(1.05);
        }
        .news-card .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, rgba(0,0,0,0.4) 0%, transparent 40%, transparent 60%, rgba(0,0,0,0.8) 100%);
            z-index: 1;
        }
        .news-card .category-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--primary-color);
            color: var(--secondary-color);
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            display: inline-block;
        }
        .news-card .category-badge:hover {
            background: white;
            color: var(--accent-color);
            transform: translateY(-2px);
        }
        .news-card .time-badge {
            background: rgba(255,255,255,0.9);
            color: var(--secondary-color);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .news-card .time-badge i {
            color: var(--accent-color);
        }
        .news-card .card-body {
            padding: 20px;
            position: relative;
        }
        .news-card .news-title {
            font-size: 1.1rem;
            font-weight: 600;
            line-height: 1.4;
            margin: 0;
            color: var(--secondary-color);
            transition: var(--transition);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .news-card:hover .news-title {
            color: var(--accent-color);
        }
        .news-card .views-badge {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: rgba(255,255,255,0.9);
            color: var(--secondary-color);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
            z-index: 2;
        }
        .category-badge {
            background: var(--primary-color);
            color: var(--secondary-color);
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            margin-bottom: 15px;
            transition: var(--transition);
        }
        .category-badge:hover {
            background: white;
            color: var(--secondary-color);
            transform: translateY(-2px);
        }
        .views-badge {
            background: var(--secondary-color);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .views-badge i {
            font-size: 14px;
        }
        .popular-news {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
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
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }
        .popular-news-item:hover {
            transform: translateX(5px);
        }
        .popular-news-item img {
            width: 100px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }
        .popular-news-item h6 {
            margin: 0 0 8px 0;
            font-size: 14px;
            line-height: 1.4;
            color: var(--secondary-color);
        }
        .weather-widget {
            background: rgba(255,255,255,0.1);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 13px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .social-links a {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            margin-left: 8px;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        .social-links a:hover {
            background: var(--primary-color);
            color: var(--secondary-color) !important;
            transform: translateY(-3px);
        }
        .category-section {
            margin-bottom: 40px;
        }
        .section-title {
            position: relative;
            margin-bottom: 30px;
            padding-bottom: 10px;
        }
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary-color);
        }
        .main-slider {
            position: relative;
            margin-bottom: 40px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .main-slider .swiper {
            width: 100%;
            height: 600px;
        }
        .main-slider .swiper-slide {
            position: relative;
            overflow: hidden;
        }
        .main-slider img {
            width: 100%;
            height: 600px;
            object-fit: cover;
            transition: var(--transition);
        }
        .main-slider .swiper-slide:hover img {
            transform: scale(1.05);
        }
        .main-slider .content-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 50px;
            background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.5) 50%, transparent 100%);
            color: white;
            transform: translateY(0);
            transition: var(--transition);
        }
        .main-slider .swiper-slide:hover .content-overlay {
            transform: translateY(-10px);
        }
        .main-slider .news-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .main-slider .news-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 1rem;
            opacity: 0.9;
        }
        .main-slider .swiper-button-next,
        .main-slider .swiper-button-prev {
            color: var(--primary-color);
            background: rgba(0,0,0,0.5);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            transition: var(--transition);
        }
        .main-slider .swiper-button-next:hover,
        .main-slider .swiper-button-prev:hover {
            background: var(--secondary-color);
        }
        .main-slider .swiper-button-next:after,
        .main-slider .swiper-button-prev:after {
            font-size: 20px;
        }
        .main-slider .swiper-pagination-bullet {
            width: 12px;
            height: 12px;
            background: var(--primary-color);
            opacity: 0.5;
            transition: var(--transition);
        }
        .main-slider .swiper-pagination-bullet-active {
            opacity: 1;
            width: 30px;
            border-radius: 6px;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
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
                        <small class="ms-2">Günəşli</small>
                    </div>
                    <div class="social-links">
                        <a href="#" class="text-white"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-telegram"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <nav class="nav-main">
        <div class="container">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo !isset($_GET['category']) ? 'active' : ''; ?>" href="index.php">
                        ANA SƏHİFƏ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="all-news.php">XƏBƏRLƏR</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button">
                        KATEQORİYALAR
                    </a>
                    <ul class="dropdown-menu">
                        <?php foreach ($categories as $category): ?>
                        <li>
                            <a class="dropdown-item <?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'active' : ''; ?>" 
                               href="category.php?id=<?php echo $category['id']; ?>">
                                <?php echo strtoupper($category['name']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">ƏLAQƏ</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="main-content">
        <div class="breaking-news">
            <div class="container">
                <div class="d-flex align-items-center">
                    <span class="breaking-news-label">SON DƏQİQƏ</span>
                    <div class="breaking-news-container">
                        <?php foreach ($breaking_news as $news): ?>
                            <a href="news.php?id=<?= $news['id'] ?>" class="breaking-news-item">
                                <?= htmlspecialchars($news['title']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Ana Slider -->
                    <div class="main-slider">
                        <div class="swiper">
                            <div class="swiper-wrapper">
                                <?php foreach ($featured_news as $news): ?>
                                <div class="swiper-slide">
                                    <img src="uploads/images/<?php echo $news['image']; ?>" alt="<?php echo $news['title']; ?>">
                                    <div class="content-overlay">
                                        <a href="category.php?id=<?php echo $news['category_id']; ?>" class="category-badge">
                                            <?php echo $news['category_name']; ?>
                                        </a>
                                        <h2 class="news-title">
                                            <a href="news.php?id=<?php echo $news['id']; ?>" class="text-white text-decoration-none">
                                                <?php echo $news['title']; ?>
                                            </a>
                                        </h2>
                                        <div class="news-meta">
                                            <span><i class="bi bi-clock"></i> <?php echo date('d.m.Y H:i', strtotime($news['created_at'])); ?></span>
                                            <span><i class="bi bi-eye"></i> <?php echo $news['view_count']; ?> baxış</span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-pagination"></div>
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-button-next"></div>
                        </div>
                    </div>

                    <!-- Kategoriler -->
                    <?php foreach ($categories as $category): ?>
                    <?php if (!empty($category_news[$category['id']])): ?>
                    <div class="category-section" data-aos="fade-up">
                        <h3 class="section-title"><?php echo $category['name']; ?></h3>
                        <div class="row">
                            <?php foreach ($category_news[$category['id']] as $news): ?>
                            <div class="col-md-6 mb-4">
                                <div class="news-card">
                                    <div class="image-wrapper">
                                        <img src="uploads/images/<?php echo $news['image']; ?>" alt="<?php echo $news['title']; ?>">
                                        <div class="image-overlay"></div>
                                        <a href="category.php?id=<?php echo $news['category_id']; ?>" class="category-badge">
                                            <?php echo $news['category_name']; ?>
                                        </a>
                                        <div class="time-badge">
                                            <i class="bi bi-clock"></i>
                                            <?php 
                                                $date = new DateTime($news['created_at']);
                                                echo $date->format('H:i');
                                            ?>
                                            <span style="margin-left: 5px;">
                                                <?php echo $date->format('d.m.Y'); ?>
                                            </span>
                                        </div>
                                        <div class="views-badge">
                                            <i class="bi bi-eye"></i>
                                            <span><?php echo number_format($news['view_count']); ?></span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <a href="news.php?id=<?php echo $news['id']; ?>" class="text-decoration-none">
                                            <h3 class="news-title"><?php echo $news['title']; ?></h3>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="col-lg-4">
                    <!-- En Çok Okunanlar -->
                    <div class="popular-news mb-4" data-aos="fade-left">
                        <h3>İndi oxuyurlar</h3>
                        <?php foreach ($popular_news as $news): ?>
                        <div class="popular-news-item">
                            <img src="uploads/images/<?php echo $news['image']; ?>" alt="<?php echo $news['title']; ?>">
                            <div>
                                <span class="category-badge mb-2 d-inline-block"><?php echo $news['category_name']; ?></span>
                                <h6><a href="news.php?id=<?php echo $news['id']; ?>" class="text-dark text-decoration-none">
                                    <?php echo $news['title']; ?>
                                </a></h6>
                                <small class="text-muted">
                                    <i class="bi bi-eye"></i> <?php echo $news['view_count']; ?> baxış
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Hava Durumu Widget -->
                    <div class="weather-widget" data-aos="fade-left" data-aos-delay="100">
                        <h5 class="mb-3">Hava Proqnozu</h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-sun fs-1"></i>
                                <div class="mt-2">
                                    <div class="fs-4 fw-bold">14°C</div>
                                    <div>Bakı</div>
                                </div>
                            </div>
                            <div>
                                <div class="mb-2">
                                    <small>Rütubət: 65%</small>
                                </div>
                                <div>
                                    <small>Külək: 15 km/s</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Döviz Kurları -->
                    <div class="currency-widget bg-white p-3 rounded-3 shadow-sm" data-aos="fade-left" data-aos-delay="200">
                        <h5 class="mb-3">Valyuta Məzənnələri</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>USD</span>
                            <span class="fw-bold">1.70 AZN</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>EUR</span>
                            <span class="fw-bold">1.85 AZN</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>TRY</span>
                            <span class="fw-bold">0.062 AZN</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // AOS Animasyon
            AOS.init({
                duration: 800,
                once: true
            });

            // Son Dakika Slider - Yeni Versiyon
            function initBreakingNews() {
                const container = $('.breaking-news-container');
                const items = $('.breaking-news-item');
                const totalWidth = Array.from(items).reduce((acc, item) => acc + $(item).outerWidth(true), 0);
                
                // Container genişliğini ayarla
                container.css('width', totalWidth + 'px');
                
                // Animasyonu sıfırla ve yeniden başlat
                function resetAnimation() {
                    container.css('transform', 'translateX(100%)');
                    container.css('animation', 'none');
                    container.outerHeight(); // Force reflow
                    container.css('animation', 'scroll-left 20s linear infinite');
                }

                // İlk animasyonu başlat
                resetAnimation();

                // Animasyon bittiğinde yeniden başlat
                container.on('animationend', resetAnimation);
            }

            // Son dakika haberlerini başlat
            initBreakingNews();

            // Pencere boyutu değiştiğinde yeniden başlat
            $(window).on('resize', function() {
                initBreakingNews();
            });

            // Ana Slider
            const mainSlider = new Swiper('.main-slider .swiper', {
                slidesPerView: 1,
                spaceBetween: 0,
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                }
            });
        });
    </script>
</body>
</html> 