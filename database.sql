CREATE DATABASE IF NOT EXISTS quizarbiter;
USE quizarbiter;

-- Users table for storing email verification data
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    verification_code VARCHAR(6),
    code_expires_at DATETIME,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Quizzes table
CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    google_form_url TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Quiz attempts table for logging
CREATE TABLE quiz_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    quiz_id INT,
    started_at DATETIME,
    completed_at DATETIME NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
);

-- Insert sample quizzes
INSERT INTO quizzes (title, description, google_form_url) VALUES 
('General Knowledge Quiz', 'Test your general knowledge with this comprehensive quiz covering various topics.', 'https://docs.google.com/forms/d/e/your-form-id-1/viewform'),
('Science & Technology Quiz', 'Challenge yourself with questions about science, technology, and innovation.', 'https://docs.google.com/forms/d/e/your-form-id-2/viewform');


-- Add this to your existing database.sql file
-- Pre-approved emails table
CREATE TABLE approved_emails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample approved emails (replace with your actual list)
INSERT INTO approved_emails (email) VALUES 
('admin@company.com'),
('manager@company.com'),
('participant1@company.com'),
('participant2@company.com');