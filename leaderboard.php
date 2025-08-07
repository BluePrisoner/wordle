<?php
require_once 'config.php';
requireLogin();

$leaderboard = getTopPlayers(50);
$userRank = getUserRank($_SESSION['user_id']);
$userRank = (int) $userRank;
$userRankIndex = $userRank - 1;

// If rank is invalid or not in top 50, prevent crash
$playerStats = ($userRankIndex >= 0 && isset($leaderboard[$userRankIndex]))
  ? $leaderboard[$userRankIndex]
  : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Wordle Clone - Leaderboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    }

    .leaderboard-card {
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      border-radius: 12px;
      background: white;
    }

    .leaderboard-item {
      transition: all 0.3s ease;
    }

    .leaderboard-item:hover {
      transform: translateX(5px);
    }

    .rank-1 {
      background: linear-gradient(135deg, #fde047 0%, #f59e0b 100%);
    }

    .rank-2 {
      background: linear-gradient(135deg, #e5e7eb 0%, #9ca3af 100%);
    }

    .rank-3 {
      background: linear-gradient(135deg, #d4b08d 0%, #a16207 100%);
    }

    .user-rank {
      box-shadow: 0 0 0 3px #3b82f6;
    }
  </style>
</head>

<body class="min-h-screen">
  <!-- Navigation -->
  <nav class="bg-gradient-to-r from-gray-900 to-black shadow-xl">
    <div class="max-w-7xl mx-auto px-6 py-3 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-teal-400 tracking-tight">Wordle Clone</h1>
      <div class="flex items-center gap-6">
        <div class="flex items-center gap-6">
          <a href="home.php"
            class="text-sm font-medium text-gray-300 hover:text-teal-400 transition-colors duration-200">Home</a>
          <a href="index.php"
            class="text-sm font-medium text-gray-300 hover:text-teal-400 transition-colors duration-200">New Game</a>
        </div>
        <div class="flex items-center gap-4">
          <span class="text-sm font-medium text-gray-300">Welcome,
            <?= htmlspecialchars($_SESSION['username']); ?></span>
          <div class="h-5 w-px bg-gray-600"></div>
          <a href="logout.php"
            class="text-sm font-medium text-white bg-teal-600 hover:bg-teal-500 px-4 py-1.5 rounded-full transition-all duration-200 shadow-md hover:shadow-teal-500/20">Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="max-w-7xl mx-auto py-8 px-4">
    <!-- Leaderboard Header -->
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-gray-800">Leaderboard</h1>
      <div class="bg-white rounded-lg px-4 py-2 shadow">
        <span class="text-gray-600">Your Rank: </span>
        <span class="font-bold"><?= $userRank == 0 ? "N/A" : $userRank ?></span>
      </div>
    </div>

    <!-- Leaderboard -->
    <div class="leaderboard-card overflow-hidden mb-8">
      <table class="w-full">
        <thead class="bg-gray-100">
          <tr>
            <th class="py-4 px-6 text-left">Rank</th>
            <th class="py-4 px-6 text-left">Player</th>
            <th class="py-4 px-6 text-left">Games</th>
            <th class="py-4 px-6 text-left">Wins</th>
            <th class="py-4 px-6 text-left">Win Rate</th>
            <th class="py-4 px-6 text-left">Points</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($leaderboard as $index => $player): ?>
            <tr class="leaderboard-item border-t border-gray-200 
              <?= $index < 3 ? 'rank-' . ($index + 1) : '' ?>
              <?= $player['username'] === $_SESSION['username'] ? 'user-rank' : 'hover:bg-gray-50' ?>">
              <td class="py-4 px-6 font-bold">
                <?php if ($index === 0): ?>
                  <span class="text-2xl">🥇</span>
                <?php elseif ($index === 1): ?>
                  <span class="text-2xl">🥈</span>
                <?php elseif ($index === 2): ?>
                  <span class="text-2xl">🥉</span>
                <?php else: ?>
                  <?= $index + 1 ?>
                <?php endif; ?>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <?php if ($player['username'] === $_SESSION['username']): ?>
                    <span class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full mr-2">You</span>
                  <?php endif; ?>
                  <?= htmlspecialchars($player['username']) ?>
                </div>
              </td>
              <td class="py-4 px-6"><?= $player['total_games'] ?></td>
              <td class="py-4 px-6"><?= $player['total_games'] != 0 ? $player['wins'] : "N/A" ?></td>
              <td class="py-4 px-6"><?= $player['total_games'] != 0 ? $player['win_percentage'] . "%" : "N/A" ?></td>
              <td class="py-4 px-6 font-bold"><?= $player['total_games'] != 0 ? $player['total_points'] : "N/A" ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Your Stats -->
    <div class="leaderboard-card p-6 mb-8">
      <h2 class="text-xl font-bold mb-4">Your Stats</h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gray-100 p-4 rounded-lg">
          <div class="text-sm text-gray-600 mb-1">Current Rank</div>
          <div class="text-2xl font-bold"><?= $userRank == 0 ? "N/A" : $userRank ?></div>
        </div>
        <div class="bg-gray-100 p-4 rounded-lg">
          <div class="text-sm text-gray-600 mb-1">Total Points</div>
          <div class="text-2xl font-bold"><?= $playerStats ? $playerStats['total_points'] : "N/A" ?></div>
        </div>
        <div class="bg-gray-100 p-4 rounded-lg">
          <div class="text-sm text-gray-600 mb-1">Win Rate</div>
          <div class="text-2xl font-bold"><?= $playerStats ? $playerStats['win_percentage'] . "%" : "N/A" ?></div>
        </div>
        <div class="bg-gray-100 p-4 rounded-lg">
          <div class="text-sm text-gray-600 mb-1">Games Played</div>
          <div class="text-2xl font-bold"><?= $playerStats ? $playerStats['total_games'] : "N/A" ?></div>
        </div>
      </div>
    </div>

    <!-- Back to Home -->
    <div class="text-center">
      <a href="home.php"
        class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-blue-700">Back to Home</a>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-white border-t border-gray-200 py-6">
    <div class="max-w-7xl mx-auto px-4 text-center text-gray-600 text-sm">
      <p>Wordle Clone &copy; <?= date('Y') ?> - All rights reserved</p>
    </div>
  </footer>
</body>

</html>