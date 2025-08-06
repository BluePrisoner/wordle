<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost:3307');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'wordle_clone');

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Helper function to redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Helper function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Helper function to get user data
function getUserData($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $user;
}

// // Helper function to get user stats
// function getUserStats($userId) {
//     $conn = getDBConnection();
//     $stmt = $conn->prepare("
//         SELECT 
//             COUNT(*) as total_games,
//             SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as wins,
//             SUM(points) as total_points
//         FROM game_sessions 
//         WHERE user_id = ?
//     ");
//     $stmt->bind_param("i", $userId);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     $stats = $result->fetch_assoc();
//     $stmt->close();
//     $conn->close();
    
//     return [
//         'total_games' => $stats['total_games'] ?? 0,
//         'wins' => $stats['wins'] ?? 0,
//         'total_points' => $stats['total_points'] ?? 0
//     ];
// }


function getUserStats($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_games,
            SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as wins,
            SUM(points) as total_points,
            ROUND(SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) / COUNT(*) * 100, 1) as win_percentage
        FROM game_sessions
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Default values if no games played
    $stats = [
        'total_games' => 0,
        'wins' => 0,
        'total_points' => 0,
        'win_percentage' => 0
    ];
    
    if ($result->num_rows > 0) {
        $stats = $result->fetch_assoc();
    }
    
    return $stats;
}


function getIncompleteGames($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT id, guess_count, started_at 
        FROM game_sessions 
        WHERE user_id = ? AND status = 'playing'
        ORDER BY started_at DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get top players for leaderboard
 */
function getTopPlayers($limit = 10) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        SELECT 
            u.username,
            COUNT(gs.id) as total_games,
            SUM(CASE WHEN gs.status = 'won' THEN 1 ELSE 0 END) as wins,
            ROUND(SUM(CASE WHEN gs.status = 'won' THEN 1 ELSE 0 END) / COUNT(gs.id) * 100, 1) as win_percentage,
            SUM(gs.points) as total_points
        FROM users u
        LEFT JOIN game_sessions gs ON u.id = gs.user_id
        GROUP BY u.id
        HAVING total_games > 0
        ORDER BY total_points DESC, win_percentage DESC
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get user's rank in leaderboard
 */
function getUserRank($userId) {
    $conn = getDBConnection();
        
    
    // Debug: Check connection first
    if (!$conn) {
        die("Database connection not established");
    }

    $sql = "
        SELECT rank FROM (
            SELECT 
                u.id,
                RANK() OVER (ORDER BY SUM(gs.points) DESC, 
                ROUND(SUM(CASE WHEN gs.status = 'won' THEN 1 ELSE 0 END) / COUNT(gs.id) * 100, 1) DESC) as rank
            FROM users u
            LEFT JOIN game_sessions gs ON u.id = gs.user_id
            GROUP BY u.id
            HAVING COUNT(gs.id) > 0
        ) as ranked_users
        WHERE id = ?
    ";
    
    $stmt = $conn->prepare($sql);
    
    // Add error handling for prepare
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error . " | SQL: " . $sql);
    }
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $row = $result->fetch_assoc();
    return $row ? $row['rank'] : 'N/A';
}
?> 