<?php
require_once 'config.php';
require_once 'includes/Database.php';

if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$email = $_SESSION['email'];

if ($_POST && isset($_POST['code'])) {
    $code = trim($_POST['code']);
    
    if (strlen($code) !== 6 || !ctype_digit($code)) {
        $error = 'Please enter a valid 6-digit code.';
    } else {
        $db = new Database();
        
        try {
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND verification_code = ? AND code_expires_at > NOW()");
            $stmt->execute([$email, $code]);
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                
                // Mark as verified
                $updateStmt = $db->prepare("UPDATE users SET is_verified = TRUE, verification_code = NULL, code_expires_at = NULL WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['verified'] = true;
                header('Location: quizzes.php');
                exit;
            } else {
                $error = 'Invalid or expired verification code.';
            }
        } catch (Exception $e) {
            $error = 'An error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Verification Code</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Email Verification</h1>
            <p>We've sent a 6-digit verification code to:<br><strong><?php echo htmlspecialchars($email); ?></strong></p>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" name="code" placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}" required>
                </div>
                <button type="submit" class="btn-primary">Verify Code</button>
            </form>
            
            <p><a href="index.php">← Back to email entry</a></p>
        </div>
    </div>
</body>
</html>