<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category_id = $_POST['category'];
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    $image = $_FILES['image'];
    
    // Video yükleme işlemi
    $video = null;
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $video = $_FILES['video'];
        $video_name = time() . '_' . basename($video["name"]);
        $video_target = "../uploads/videos/" . $video_name;
        
        // Video klasörünü oluştur
        if (!file_exists("../uploads/videos")) {
            mkdir("../uploads/videos", 0777, true);
        }
        
        // Video yükle
        if (move_uploaded_file($video["tmp_name"], $video_target)) {
            $video_path = $video_name;
        }
    }
    
    // Resim yükleme
    $target_dir = "../uploads/images/";
    
    // Resim klasörünü oluştur
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            $error = "Şəkil qovluğu yaradıla bilmədi!";
        }
    }
    
    // Resim kontrolü
    if (!isset($image) || $image['error'] !== UPLOAD_ERR_OK) {
        $error = "Şəkil yüklənərkən xəta baş verdi! Xəta kodu: " . $image['error'];
    } else {
        // Resim türü kontrolü
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($image['type'], $allowed_types)) {
            $error = "Yalnız JPG, PNG və GIF formatlı şəkillər yüklənə bilər!";
        } else {
            // Resim boyutu kontrolü (5MB)
            if ($image['size'] > 5 * 1024 * 1024) {
                $error = "Şəkil ölçüsü 5MB-dan böyük ola bilməz!";
            } else {
                $image_name = time() . '_' . basename($image["name"]);
                $target_file = $target_dir . $image_name;
                
                try {
                    if (move_uploaded_file($image["tmp_name"], $target_file)) {
                        $stmt = $db->prepare("INSERT INTO news (title, content, category_id, image, video, is_breaking) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$title, $content, $category_id, $image_name, $video_path ?? null, $is_breaking]);
                        $success = "Xəbər uğurla əlavə edildi!";
                        header("Location: index.php");
                        exit();
                    } else {
                        $error = "Şəkil yüklənərkən xəta baş verdi! Qovluq icazələrini yoxlayın.";
                    }
                } catch (PDOException $e) {
                    $error = "Xəbər əlavə edilərkən xəta baş verdi: " . $e->getMessage();
                }
            }
        }
    }
}

// Kateqoriyaları əldə et
$stmt = $db->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Yeni Xəbər</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
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
        .note-video-clip {
            max-width: 100%;
        }
        .video-preview {
            max-width: 300px;
            margin-top: 10px;
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
                        <a href="add_news.php" class="nav-link active">
                            <i class="bi bi-plus-circle"></i> Yeni xəbər
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="categories.php" class="nav-link">
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
                <h2 class="mb-4">Yeni Xəbər</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Başlıq</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Kateqoriya</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Kateqoriya seçin</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Şəkil</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                    </div>

                    <div class="mb-3">
                        <label for="video" class="form-label">Video (istəyə bağlı)</label>
                        <input type="file" class="form-control" id="video" name="video" accept="video/*">
                        <div id="videoPreview" class="video-preview"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Məzmun</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_breaking" name="is_breaking">
                        <label class="form-check-label" for="is_breaking">Son Dakika Haberi</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Yadda saxla</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#content').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        for (let i = 0; i < files.length; i++) {
                            uploadImage(files[i]);
                        }
                    }
                }
            });

            // Video önizleme
            $('#video').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#videoPreview').html(`
                            <video controls class="mt-2" style="max-width: 100%;">
                                <source src="${e.target.result}" type="${file.type}">
                                Tarayıcınız video etiketini desteklemiyor.
                            </video>
                        `);
                    }
                    reader.readAsDataURL(file);
                }
            });
        });

        // Summernote editörüne resim yükleme
        function uploadImage(file) {
            const formData = new FormData();
            formData.append('image', file);
            
            $.ajax({
                url: 'upload_image.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(url) {
                    $('#content').summernote('insertImage', url);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error(textStatus + ": " + errorThrown);
                }
            });
        }
    </script>
</body>
</html> 