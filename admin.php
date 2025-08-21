<?php
require_once 'config.php';
require_once 'includes/Database.php';

// Simple password protection (you should implement proper admin authentication)
$admin_password = 'DoqImt9lX89eOrsG49tQ0opX'; // Change this!

if ($_POST && isset($_POST['admin_password'])) {
    if ($_POST['admin_password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = 'Invalid password';
    }
}

if (!isset($_SESSION['admin_logged_in'])) {
    // Show login form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Login</title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
        <div class="container">
            <div class="form-container">
                <h1>Admin Login</h1>
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <input type="password" name="admin_password" placeholder="Admin Password" required>
                    </div>
                    <button type="submit" class="btn-primary">Login</button>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$db = new Database();
$message = '';

// Handle adding new email
if ($_POST && isset($_POST['add_email'])) {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    if ($email) {
        try {
            $stmt = $db->prepare("INSERT INTO approved_emails (email) VALUES (?)");
            $stmt->execute([$email]);
            $message = 'Email added successfully!';
        } catch (Exception $e) {
            $message = 'Email already exists or error occurred.';
        }
    }
}

// Handle removing email
if ($_GET && isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    $stmt = $db->prepare("DELETE FROM approved_emails WHERE id = ?");
    $stmt->execute([$id]);
    $message = 'Email removed successfully!';
}

// Get all approved emails
$stmt = $db->prepare("SELECT * FROM approved_emails ORDER BY email");
$stmt->execute();
$approvedEmails = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Approved Emails</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Manage Approved Emails</h1>
        
        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <div class="admin-section">
            <h2>Add New Email</h2>
            <form method="POST">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Enter email address" required>
                </div>
                <button type="submit" name="add_email" class="btn-primary">Add Email</button>
            </form>
        </div>
        
        <div class="admin-section">
            <h2>Current Approved Emails</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Added Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($approvedEmails as $approvedEmail): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($approvedEmail['email']); ?></td>
                            <td><?php echo htmlspecialchars($approvedEmail['created_at']); ?></td>
                            <td>
                                <a href="?remove=<?php echo $approvedEmail['id']; ?>" 
                                   onclick="return confirm('Are you sure?')" 
                                   class="btn-danger">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="admin-section">
            <a href="index.php" class="btn-secondary">Back to Quiz</a>
            <a href="?logout=1" class="btn-secondary">Logout</a>
        </div>
    </div>
</body>
</html>

<?php
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    header('Location: admin.php');
    exit;
}
?>