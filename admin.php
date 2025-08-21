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

// Get quiz attempts with user and quiz information
$stmt = $db->prepare("
    SELECT 
        qa.id,
        u.email,
        q.title as quiz_title,
        qa.started_at,
        qa.completed_at,
        qa.ip_address,
        CASE 
            WHEN qa.completed_at IS NOT NULL THEN 'Completed'
            ELSE 'In Progress'
        END as status
    FROM quiz_attempts qa
    LEFT JOIN users u ON qa.user_id = u.id
    LEFT JOIN quizzes q ON qa.quiz_id = q.id
    ORDER BY qa.started_at DESC
    LIMIT 100
");
$stmt->execute();
$quizAttempts = $stmt->fetchAll();

// Get quiz attempts statistics
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_attempts,
        COUNT(CASE WHEN completed_at IS NOT NULL THEN 1 END) as completed_attempts,
        COUNT(CASE WHEN completed_at IS NULL THEN 1 END) as incomplete_attempts
    FROM quiz_attempts
");
$stmt->execute();
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            color: #6c757d;
            margin-top: 5px;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .admin-table th,
        .admin-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .admin-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .status-completed {
            color: #28a745;
            font-weight: bold;
        }
        .status-progress {
            color: #ffc107;
            font-weight: bold;
        }
        .admin-section {
            margin-bottom: 40px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard</h1>
        
        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <!-- Quiz Attempts Statistics -->
        <div class="admin-section">
            <h2>Quiz Attempts Overview</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_attempts']; ?></div>
                    <div class="stat-label">Total Attempts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['completed_attempts']; ?></div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['incomplete_attempts']; ?></div>
                    <div class="stat-label">In Progress</div>
                </div>
            </div>
        </div>

        <!-- Quiz Attempts Table -->
        <div class="admin-section">
            <h2>Recent Quiz Attempts (Last 100)</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Quiz</th>
                        <th>Started At</th>
                        <th>Completed At</th>
                        <th>Status</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quizAttempts as $attempt): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($attempt['email'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($attempt['quiz_title'] ?? 'Unknown Quiz'); ?></td>
                            <td><?php echo htmlspecialchars($attempt['started_at']); ?></td>
                            <td><?php echo $attempt['completed_at'] ? htmlspecialchars($attempt['completed_at']) : '-'; ?></td>
                            <td>
                                <span class="<?php echo $attempt['status'] === 'Completed' ? 'status-completed' : 'status-progress'; ?>">
                                    <?php echo htmlspecialchars($attempt['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($attempt['ip_address']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($quizAttempts)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #6c757d;">No quiz attempts found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
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