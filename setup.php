<?php
// Database setup script for Wordle Clone
// Run this file once to create the database and tables

echo "Wordle Clone - Database Setup\n";
echo "=============================\n\n";

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'wordle_clone';

try {
    // Connect to MySQL server
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error . "\n");
    }
    
    echo "✓ Connected to MySQL server\n";
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS `$dbname`";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Database '$dbname' created or already exists\n";
    } else {
        echo "✗ Error creating database: " . $conn->error . "\n";
        exit(1);
    }
    
    // Select the database
    $conn->select_db($dbname);
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) UNIQUE NOT NULL,
        `password_hash` TEXT NOT NULL
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Users table created or already exists\n";
    } else {
        echo "✗ Error creating users table: " . $conn->error . "\n";
        exit(1);
    }
    
    // Create dictionary table
    $sql = "CREATE TABLE IF NOT EXISTS `dictionary` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `word` VARCHAR(5) UNIQUE NOT NULL
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Dictionary table created or already exists\n";
    } else {
        echo "✗ Error creating dictionary table: " . $conn->error . "\n";
        exit(1);
    }
    
    // Create game_sessions table
    $sql = "CREATE TABLE IF NOT EXISTS `game_sessions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT,
        `word_id` INT,
        `guesses` TEXT,
        `guess_count` INT,
        `points` INT DEFAULT 0,
        `status` ENUM('playing', 'won', 'lost'),
        `started_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `completed_at` DATETIME,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`word_id`) REFERENCES `dictionary`(`id`) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Game sessions table created or already exists\n";
    } else {
        echo "✗ Error creating game_sessions table: " . $conn->error . "\n";
        exit(1);
    }
    
    // Check if dictionary has words
    $result = $conn->query("SELECT COUNT(*) as count FROM dictionary");
    $row = $result->fetch_assoc();
    $wordCount = $row['count'];
    
    if ($wordCount == 0) {
        echo "\n⚠ Dictionary is empty. Please run import_words.php to add words.\n";
    } else {
        echo "✓ Dictionary contains $wordCount words\n";
    }
    
    echo "\n🎉 Database setup completed successfully!\n";
    echo "\nNext steps:\n";
    echo "1. If dictionary is empty, run: http://localhost/wordle-clone/import_words.php\n";
    echo "2. Access the game: http://localhost/wordle-clone/\n";
    echo "3. Register a new account and start playing!\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?> 