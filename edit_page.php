<?php
session_start();
include '../includes/config.php';
include 'includes/upload.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

$page_types = ['terms', 'privacy', 'about']; // Add more as needed
$page_name = $_GET['page'] ?? '';

if (!in_array($page_name, $page_types)) {
    header("Location: index.php");
    exit();
}

// Get current content
$stmt = $pdo->prepare("SELECT * FROM site_pages WHERE page_name = ?");
$stmt->execute([$page_name]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    try {
        if ($page) {
            // Update existing page
            $stmt = $pdo->prepare("UPDATE site_pages SET title = ?, content = ? WHERE page_name = ?");
            $stmt->execute([$title, $content, $page_name]);
        } else {
            // Create new page
            $stmt = $pdo->prepare("INSERT INTO site_pages (page_name, title, content) VALUES (?, ?, ?)");
            $stmt->execute([$page_name, $title, $content]);
        }
        
        $_SESSION['success'] = "Page updated successfully";
        header("Location: edit_page.php?page=$page_name");
        exit();
    } catch (PDOException $e) {
        $error = "Error saving page: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car | TravelEase Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        .image-preview {
            max-height: 200px;
            width: auto;
            display: block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2>Edit <?= ucfirst($page_name) ?> Page</h2>
        
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label">Page Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                            value="<?= htmlspecialchars($page['title'] ?? ucfirst($page_name)) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="10"><?= htmlspecialchars($page['content'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#content').summernote({
            height: 300,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });
    </script>
</body>
</html>