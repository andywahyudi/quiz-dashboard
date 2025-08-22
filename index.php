<?php
require_once 'config.php';
require_once 'includes/Database.php';
require_once 'includes/EmailService.php';

$error = '';
$success = '';

if ($_POST && isset($_POST['email'])) {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        $error = 'Please enter a valid email address.';
    } else {
        $db = new Database();
        
        try {
            // Check if email is in the approved list
            $approvedStmt = $db->prepare("SELECT id FROM approved_emails WHERE email = ?");
            $approvedStmt->execute([$email]);
            
            if ($approvedStmt->rowCount() > 0) {
                // Email is pre-approved, create/update user and mark as verified
                $userStmt = $db->prepare("INSERT INTO users (email, is_verified) VALUES (?, TRUE) ON DUPLICATE KEY UPDATE is_verified = TRUE, verification_code = NULL, code_expires_at = NULL");
                $userStmt->execute([$email]);
                
                // Get user ID
                $getUserStmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $getUserStmt->execute([$email]);
                $user = $getUserStmt->fetch();
                
                // Set session variables and redirect to quizzes
                $_SESSION['email'] = $email;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['verified'] = true;
                header('Location: quizzes.php');
                exit;
            } else {
                // Email not in approved list, proceed with normal verification
                $emailService = new EmailService();
                
                // Generate 6-digit verification code
                $verificationCode = sprintf('%06d', mt_rand(0, 999999));
                $expiresAt = date('Y-m-d H:i:s', time() + VERIFICATION_CODE_EXPIRY);
                
                // Insert or update user
                $stmt = $db->prepare("INSERT INTO users (email, verification_code, code_expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE verification_code = ?, code_expires_at = ?, is_verified = FALSE");
                $stmt->execute([$email, $verificationCode, $expiresAt, $verificationCode, $expiresAt]);
                
                // Send email
                if ($emailService->sendVerificationCode($email, $verificationCode)) {
                    $_SESSION['email'] = $email;
                    header('Location: verify.php');
                    exit;
                } else {
                    $error = 'Failed to send verification email. Please try again.';
                }
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
    <title>Quiz Access - Email Verification</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Quiz Access</h1>
            <p>Enter your Arbiter email address</p>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Enter your email address" required>
                </div>
                <button type="submit" class="btn-primary">Verify Email</button>
            </form>
            
            <!-- <div class="info-note">
                <p><small>Pre-approved participants will be redirected directly to quizzes. Others will receive a verification code via email.</small></p>
            </div> -->
        </div>
    </div>
</body>
</html>