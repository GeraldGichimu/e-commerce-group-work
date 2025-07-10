<?php
session_start();
include '../includes/config.php';
include 'includes/upload.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM admin_users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("User not found");
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: manage-admins.php");
    exit();
}

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
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
        $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
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
    
    if (!in_array($role, ['admin', 'user'])) {
        $errors['role'] = 'Invalid role selected';
    }
    
    // If no errors, update user
    if (empty($errors)) {
        try {
            // Prepare base query
            $query = "UPDATE admin_users SET name = ?, email = ?, role = ?";
            $params = [$name, $email, $role];
            
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hotel | TravelEase Admin</title>
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
                    <h2 class="h3">Edit Admin #<?php echo $user_id; ?></h2>
                    <a href="manage-admins.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Admins
                    </a>
                </div>
                
                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                User updated successfully! <a href="admin_users.php">View all admin_users</a>
                            </div>
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
                            
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>" 
                                        id="role" name="role">
                                    <option value="user" <?= ($_POST['role'] ?? $user['role']) === 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="admin" <?= ($_POST['role'] ?? $user['role']) === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <?php if (isset($errors['role'])): ?>
                                    <div class="invalid-feedback"><?= $errors['role'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update User</button>
                            <a href="manage-admins.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>