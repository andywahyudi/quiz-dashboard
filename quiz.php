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

// Function to convert Google Form URL to embeddable format
function getEmbeddableFormUrl($url) {
    // Extract form ID from various Google Form URL formats
    if (preg_match('/\/forms\/d\/e\/([a-zA-Z0-9-_]+)\//', $url, $matches)) {
        $formId = $matches[1];
        return "https://docs.google.com/forms/d/e/{$formId}/viewform?embedded=true";
    } elseif (preg_match('/\/forms\/d\/([a-zA-Z0-9-_]+)\//', $url, $matches)) {
        $formId = $matches[1];
        return "https://docs.google.com/forms/d/{$formId}/viewform?embedded=true";
    }
    
    // If already has embedded=true, return as is
    if (strpos($url, 'embedded=true') !== false) {
        return $url;
    }
    
    // Add embedded=true parameter
    $separator = strpos($url, '?') !== false ? '&' : '?';
    return $url . $separator . 'embedded=true';
}

$embeddableUrl = getEmbeddableFormUrl($quiz['google_form_url']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .quiz-iframe-container {
            position: relative;
            width: 100%;
            height: 700px;
            border: none;
            overflow: hidden;
        }
        
        .quiz-iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: white;
        }
        
        .loading-message {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #666;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="quiz-header">
            <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
            <p><?php echo htmlspecialchars($quiz['description']); ?></p>
        </div>
        
        <div class="quiz-content">
            <div class="quiz-iframe-container">
                <div class="loading-message" id="loadingMessage">Loading quiz...</div>
                <iframe 
                    id="quizIframe"
                    class="quiz-iframe"
                    src="<?php echo htmlspecialchars($embeddableUrl); ?>"
                    allowfullscreen
                    allow="autoplay; camera; microphone; clipboard-read; clipboard-write;"
                    onload="document.getElementById('loadingMessage').style.display='none';"
                    onerror="document.getElementById('loadingMessage').innerHTML='Error loading quiz. Please refresh the page.';"
                    sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-presentation">
                </iframe>
            </div>
        </div>
        
        <div class="quiz-actions">
            <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="action" value="complete">
                <button type="submit" class="btn-success" onclick="return confirm('Are you sure you have completed the quiz?')">Done</button>
            </form>
            <a href="quizzes.php" class="btn-secondary">Back</a>
        </div>
    </div>

    <script>
        // Additional iframe loading handling
        document.addEventListener('DOMContentLoaded', function() {
            const iframe = document.getElementById('quizIframe');
            const loadingMessage = document.getElementById('loadingMessage');
            
            // Set a timeout to show error message if iframe doesn't load
            setTimeout(function() {
                if (loadingMessage.style.display !== 'none') {
                    loadingMessage.innerHTML = 'Quiz is taking longer to load. Please check your internet connection or try refreshing the page.';
                }
            }, 10000); // 10 seconds
            
            // Handle iframe load errors
            iframe.addEventListener('error', function() {
                loadingMessage.innerHTML = 'Error loading quiz. Please refresh the page or contact support.';
                loadingMessage.style.display = 'block';
            });
        });
    </script>
</body>
</html>