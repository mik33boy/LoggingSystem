<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'communication_logger');

// Try to connect to MySQL
try {
    $db = new PDO("mysql:host=".DB_HOST, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to MySQL successfully!<br>";
    
    // Create database if it doesn't exist
    $db->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "Database '" . DB_NAME . "' created or already exists.<br>";
    
    // Select the database
    $db->exec("USE " . DB_NAME);
    
    // Create users table
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table 'users' created or already exists.<br>";
    
    // Create communication_logs table
    $db->exec("CREATE TABLE IF NOT EXISTS communication_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        direction ENUM('incoming', 'outgoing') NOT NULL,
        type ENUM('memo', 'fax', 'email', 'letter', 'phone', 'other') NOT NULL,
        subject VARCHAR(255) NOT NULL,
        content TEXT,
        sender VARCHAR(255),
        recipient VARCHAR(255),
        timestamp TIMESTAMP NOT NULL,
        confidentiality_level ENUM('public', 'confidential', 'secret') DEFAULT 'public',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    echo "Table 'communication_logs' created or already exists.<br>";
    
    // Create log_access table
    $db->exec("CREATE TABLE IF NOT EXISTS log_access (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        log_id INT NOT NULL,
        action ENUM('view', 'create', 'edit', 'delete') NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (log_id) REFERENCES communication_logs(id) ON DELETE CASCADE
    )");
    echo "Table 'log_access' created or already exists.<br>";
    
    // First delete any existing test user
    try {
        $stmt = $db->prepare("DELETE FROM users WHERE username = 'samnario'");
        $stmt->execute();
        echo "Removed existing test user if present.<br>";
    } catch (PDOException $e) {
        echo "Could not remove existing user: " . $e->getMessage() . "<br>";
    }

    // Create test user with known password
    $hashedPassword = password_hash('123456', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute(['samnario', $hashedPassword, 'user']);
    echo "Test user created. Username: 'samnario', Password: '123456'<br>";
    
    // Create admin user
    $adminHashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute(['admin', $adminHashedPassword, 'admin']);
    echo "Admin user created. Username: 'admin', Password: 'admin123'<br>";
    
    echo "Database setup completed successfully!";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?> 