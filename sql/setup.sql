-- Database Setup for Judge Scoreboard Application
-- File: sql/setup.sql

-- Create tables
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('judge', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS participants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    identifier VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS scores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    judge_id INT NOT NULL,
    participant_id INT NOT NULL,
    score DECIMAL(5,2) NOT NULL CHECK (score >= 0 AND score <= 100),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (judge_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_judge_participant (judge_id, participant_id)
);

-- Create indexes for better performance
CREATE INDEX idx_scores_participant ON scores(participant_id);
CREATE INDEX idx_scores_judge ON scores(judge_id);
CREATE INDEX idx_scores_submitted ON scores(submitted_at);

-- Insert default users (passwords are hashed versions of simple passwords)
INSERT INTO users (username, password, role) VALUES 
('judge1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'judge'),
('judge2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'judge'),
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample participants
INSERT INTO participants (name, identifier) VALUES 
('John Doe', 'john_doe'),
('Jane Smith', 'jane_smith'),
('Mike Johnson', 'mike_johnson'),
('Sarah Wilson', 'sarah_wilson'),
('David Brown', 'david_brown');

-- Insert some sample scores for demonstration
INSERT INTO scores (judge_id, participant_id, score) VALUES 
(1, 1, 85.50),
(1, 2, 92.00),
(1, 3, 78.75),
(2, 1, 88.25),
(2, 2, 90.50),
(2, 4, 85.00);

-- Create a view for easy scoreboard queries
CREATE VIEW scoreboard_view AS
SELECT 
    p.id,
    p.name,
    p.identifier,
    COALESCE(AVG(s.score), 0) as average_score,
    COUNT(s.score) as judge_count,
    COALESCE(SUM(s.score), 0) as total_score,
    MAX(s.submitted_at) as last_updated
FROM participants p
LEFT JOIN scores s ON p.id = s.participant_id
GROUP BY p.id, p.name, p.identifier
ORDER BY average_score DESC, total_score DESC;