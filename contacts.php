<?php
session_start();
include '../includes/config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$start = ($page > 1) ? ($page * $per_page) - $per_page : 0;

$total = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$pages = ceil($total / $per_page);

// Get messages
$stmt = $pdo->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT ?, ?");
$stmt->bindValue(1, $start, PDO::PARAM_INT);
$stmt->bindValue(2, $per_page, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark as read
if (isset($_GET['mark_read'])) {
    $id = (int)$_GET['mark_read'];
    $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$id]);
    header("Location: contacts.php");
    exit();
}

// Delete message
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$id]);
    header("Location: contacts.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contacts | TravelEase Admin</title>
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2 class="h3">Manage Contacts</h2>
                </div>
                
                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Subject</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($messages as $message): ?>
                                    <tr class="<?= $message['is_read'] ? '' : 'table-primary' ?>">
                                        <td><?= $message['id'] ?></td>
                                        <td><?= htmlspecialchars($message['name']) ?></td>
                                        <td><?= htmlspecialchars($message['email']) ?></td>
                                        <td><?= htmlspecialchars($message['subject']) ?></td>
                                        <td><?= date('M d, Y H:i', strtotime($message['created_at'])) ?></td>
                                        <td>
                                            <?php if ($message['is_read']): ?>
                                                <span class="badge bg-success">Read</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Unread</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="view_message.php?id=<?= $message['id'] ?>" class="btn btn-sm btn-info">View</a>
                                            <a href="contacts.php?mark_read=<?= $message['id'] ?>" class="btn btn-sm btn-secondary">Mark Read</a>
                                            <a href="contacts.php?delete=<?= $message['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?>">Previous</a></li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $pages; $i++): ?>
                                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $pages): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?>">Next</a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>