<?php
require_once 'config.php';
requireLogin();

$conn = getDBConnection();

// Get leaderboard data
$stmt = $conn->prepare("
    SELECT 
        u.username,
        COUNT(g.id) AS games_played,
        SUM(CASE WHEN g.status = 'won' THEN 1 ELSE 0 END) AS games_won,
        SUM(g.points) AS total_points
    FROM users u
    LEFT JOIN game_sessions g ON u.id = g.user_id
    GROUP BY u.id, u.username
    ORDER BY total_points DESC, games_won DESC, games_played ASC
    LIMIT 50
");

$stmt->execute();
$result = $stmt->get_result();
$leaderboard = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Wordle Clone</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-800">Wordle Clone</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-600 hover:text-gray-900">Game</a>
                    <a href="leaderboard.php" class="text-blue-600 font-medium">Leaderboard</a>
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="text-red-600 hover:text-red-800">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800">Leaderboard</h2>
                <p class="text-gray-600 mt-1">Top players ranked by total points</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rank
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Player
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Points
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Wins
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Games
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Win Rate
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($leaderboard)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No games played yet. Be the first to play!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($leaderboard as $index => $player): ?>
                                <tr class="<?php echo $index < 3 ? 'bg-yellow-50' : ''; ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php 
                                        if ($index === 0) echo '🥇';
                                        elseif ($index === 1) echo '🥈';
                                        elseif ($index === 2) echo '🥉';
                                        else echo ($index + 1);
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($player['username']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                        <?php echo number_format($player['total_points'] ?? 0); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $player['games_won'] ?? 0; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $player['games_played'] ?? 0; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php 
                                        $gamesPlayed = $player['games_played'] ?? 0;
                                        $gamesWon = $player['games_won'] ?? 0;
                                        if ($gamesPlayed > 0) {
                                            echo round(($gamesWon / $gamesPlayed) * 100, 1) . '%';
                                        } else {
                                            echo '0%';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <a href="index.php" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Play Game
            </a>
        </div>
    </div>
</body>
</html> 