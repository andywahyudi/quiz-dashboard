<?php
require_once 'config.php';
require_once 'includes/Database.php';

if (!isset($_SESSION['verified']) || !$_SESSION['verified']) {
    header('Location: index.php');
    exit;
}

$quizId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$quizId) {
    header('Location: quizzes.php');
    exit;
}

$db = new Database();

// Get quiz details
$stmt = $db->prepare("SELECT * FROM quizzes WHERE id = ? AND is_active = TRUE");
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz) {
    header('Location: quizzes.php');
    exit;
}

// Handle quiz completion
if ($_POST && isset($_POST['action'])) {
    if ($_POST['action'] === 'complete') {
        $stmt = $db->prepare("UPDATE quiz_attempts SET completed_at = NOW() WHERE user_id = ? AND quiz_id = ? AND completed_at IS NULL");
        $stmt->execute([$_SESSION['user_id'], $quizId]);
        
        $_SESSION['quiz_completed'] = true;
        header('Location: quizzes.php?completed=1');
        exit;
    }
}

// Log quiz start if not already logged
if (!isset($_SESSION['quiz_started_' . $quizId])) {
    $stmt = $db->prepare("INSERT INTO quiz_attempts (user_id, quiz_id, started_at, ip_address, user_agent) VALUES (?, ?, NOW(), ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $quizId,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
    $_SESSION['quiz_started_' . $quizId] = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="quiz-header">
            <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
            <p><?php echo htmlspecialchars($quiz['description']); ?></p>
        </div>
        
        <div class="quiz-content">
            <iframe src="<?php echo htmlspecialchars($quiz['google_form_url']); ?>" 
                    width="100%" 
                    height="600" 
                    frameborder="0" 
                    marginheight="0" 
                    marginwidth="0">
                Loading...
            </iframe>
        </div>
        
        <div class="quiz-actions">
            <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="action" value="complete">
                <button type="submit" class="btn-success">Done</button>
            </form>
            <a href="quizzes.php" class="btn-secondary">Back</a>
        </div>
    </div>
</body>
</html>