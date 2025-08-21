<?php
require_once 'config.php';
require_once 'includes/Database.php';

if (!isset($_SESSION['verified']) || !$_SESSION['verified']) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$stmt = $db->prepare("SELECT * FROM quizzes WHERE is_active = TRUE ORDER BY id");
$stmt->execute();
$quizzes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Quizzes</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Available Quizzes</h1>
        <p>Choose a quiz to get started:</p>
        
        <div class="quiz-grid">
            <?php foreach ($quizzes as $quiz): ?>
                <div class="quiz-card">
                    <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                    <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                    <a href="quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-primary">Start Quiz</a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>