<?php
class EmailService {
    private $apiKey;
    private $domain;
    private $fromEmail;
    
    public function __construct() {
        $this->apiKey = MAILGUN_API_KEY;
        $this->domain = MAILGUN_DOMAIN;
        $this->fromEmail = MAILGUN_FROM_EMAIL;
    }
    
    public function sendVerificationCode($email, $code) {
        $subject = 'Quiz Verification Code';
        $message = "Your verification code is: <strong>$code</strong><br><br>This code will expire in 10 minutes.";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.mailgun.net/v3/{$this->domain}/messages");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "api:{$this->apiKey}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'from' => $this->fromEmail,
            'to' => $email,
            'subject' => $subject,
            'html' => $message
        ]);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode == 200;
    }
}
?>