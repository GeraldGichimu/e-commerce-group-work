<?php
session_start();
include '../includes/config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT id, name, email, phone FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("User not found");
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: users.php");
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = $_POST['phone'] ?? 'user';
    
    // Validate inputs
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } else {
        // Check if email exists (excluding current user)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Email already in use';
        }
    }
    
    if (!empty($password)) {
        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        } elseif ($password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
    }
    
    // If no errors, update user
    if (empty($errors)) {
        try {
            // Prepare base query
            $query = "UPDATE users SET name = ?, email = ?, phone = ?";
            $params = [$name, $email, $phone];
            
            // Add password to query if provided
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query .= ", password = ?";
                $params[] = $hashed_password;
            }
            
            $query .= " WHERE id = ?";
            $params[] = $user_id;
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            
            $success = true;
        } catch (PDOException $e) {
            $errors['database'] = 'Error updating user: ' . $e->getMessage();
        }
    }
}
if ($success) {
    $_SESSION['success'] = "User updated successfully";
    header("Location: users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User | TravelEase Admin</title>
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2 class="h3">Add New User</h2>
                    <a href="users.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Users
                    </a>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Edit User: <?= htmlspecialchars($user['name']) ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                User updated successfully! <a href="users.php">View all users</a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($errors['database'])): ?>
                            <div class="alert alert-danger"><?= $errors['database'] ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($errors['database'])): ?>
                            <div class="alert alert-danger"><?= $errors['database'] ?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                       id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? $user['name']) ?>">
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                       id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>">
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="number" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                       id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? $user['phone']) ?>">
                                <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                       id="password" name="password">
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback"><?= $errors['password'] ?></div>
                                <?php endif; ?>
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                       id="confirm_password" name="confirm_password">
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update User</button>
                            <a href="users.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>