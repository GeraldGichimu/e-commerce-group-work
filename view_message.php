<?php
session_start();
include '../includes/config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

// Get message ID from URL
$message_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch message details
try {
    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$message_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$message) {
        throw new Exception("Message not found");
    }
    
    // Mark as read if not already
    if (!$message['is_read']) {
        $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$message_id]);
        $message['is_read'] = 1;
    }
    
    // Mark as responded if form submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['respond'])) {
        $response = trim($_POST['response'] ?? '');
        
        if (empty($response)) {
            $error = "Response message is required";
        } else {
            // Save response to database
            $stmt = $pdo->prepare("UPDATE contact_messages SET responded = 1, response = ?, response_date = NOW() WHERE id = ?");
            $stmt->execute([$response, $message_id]);
                
            $success = "Response sent successfully";
            $message['responded'] = 1; // Update local copy
        }
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: contacts.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins | TravelEase Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .message-content {
            white-space: pre-wrap;
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            border-left: 4px solid #4C1B24;
        }
        .response-content {
            white-space: pre-wrap;
            background-color: #e8f4fd;
            padding: 1rem;
            border-radius: 5px;
            border-left: 4px solid #0d6efd;
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
                    <h2 class="h3">Manage Admins</h2>
                    <a href="add-admin.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add New Admin
                    </a>
                </div>
                
                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3>Message Details</h3>
                        <div>
                            <a href="contacts.php" class="btn btn-sm btn-secondary">Back to Messages</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <h5>From:</h5>
                            <p><?= htmlspecialchars($message['name']) ?> &lt;<?= htmlspecialchars($message['email']) ?>&gt;</p>
                            
                            <h5>Subject:</h5>
                            <p><?= htmlspecialchars($message['subject']) ?></p>
                            
                            <h5>Received:</h5>
                            <p><?= date('F j, Y \a\t g:i a', strtotime($message['created_at'])) ?></p>
                            
                            <h5>Status:</h5>
                            <p>
                                <span class="badge bg-<?= $message['is_read'] ? 'success' : 'warning' ?>">
                                    <?= $message['is_read'] ? 'Read' : 'Unread' ?>
                                </span>
                                <?php if ($message['responded']): ?>
                                    <span class="badge bg-primary ms-2">Responded</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <h5>Message:</h5>
                            <div class="message-content">
                                <?= htmlspecialchars($message['message']) ?>
                            </div>
                        </div>
                        
                        <?php if ($message['responded']): ?>
                            <div class="mb-4">
                                <h5>Your Response:</h5>
                                <p><small>Sent on <?= date('F j, Y \a\t g:i a', strtotime($message['response_date'])) ?></small></p>
                                <div class="response-content">
                                    <?= htmlspecialchars($message['response']) ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mb-4">
                                <h5>Respond to Message</h5>
                                <form method="post">
                                    <div class="mb-3">
                                        <label for="response" class="form-label">Your Response</label>
                                        <textarea class="form-control" id="response" name="response" rows="6" required></textarea>
                                    </div>
                                    <button type="submit" name="respond" class="btn btn-primary">Send Response</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>