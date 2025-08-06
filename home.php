<?php
require_once 'config.php';
requireLogin();

$userStats = getUserStats($_SESSION['user_id']);
$incompleteGames = getIncompleteGames($_SESSION['user_id']);
$leaderboard = getTopPlayers(10);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Wordle Clone - Home</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
    
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    }
    
    .game-card {
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      border-radius: 12px;
      background: white;
    }
    
    .game-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
    }
    
    .btn-secondary {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      transition: all 0.3s ease;
    }
    
    .btn-secondary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
    }
    
    .leaderboard-item {
      transition: all 0.3s ease;
    }
    
    .leaderboard-item:hover {
      transform: translateX(5px);
    }
    
    .progress-bar {
      height: 8px;
      border-radius: 4px;
      background: #e5e7eb;
    }
    
    .progress-fill {
      height: 100%;
      border-radius: 4px;
      background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%);
    }
  </style>
</head>

<body class="min-h-screen">
  <!-- Navigation -->
  <nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-800">Wordle Clone</h1>
      <div class="flex items-center gap-4 text-sm">
        <span class="text-gray-600">Games: <?= $userStats['total_games']; ?></span>
        <span class="text-gray-600">Wins: <?= $userStats['wins']; ?></span>
        <span class="text-gray-600">Points: <?= $userStats['total_points']; ?></span>
        <span class="text-gray-600">Welcome, <?= htmlspecialchars($_SESSION['username']); ?></span>
        <a href="logout.php" class="text-red-600 hover:underline">Logout</a>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="max-w-7xl mx-auto py-8 px-4">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl p-8 mb-8 text-white">
      <h1 class="text-4xl font-bold mb-4">Welcome to Wordle Clone!</h1>
      <p class="text-xl mb-6">Guess the hidden 5-letter word in 6 tries. Each guess must be a valid word.</p>
      <div class="flex gap-4">
        <a href="index.php" class="btn-primary text-white px-6 py-3 rounded-lg font-bold">New Game</a>
        <a href="#leaderboard" class="bg-white text-indigo-600 px-6 py-3 rounded-lg font-bold hover:bg-gray-100">View Leaderboard</a>
      </div>
    </div>

    <!-- Incomplete Games -->
    <?php if (!empty($incompleteGames)): ?>
    <div class="mb-12">
      <h2 class="text-2xl font-bold mb-4 text-gray-800">Continue Your Games</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($incompleteGames as $game): ?>
        <div class="game-card p-6">
          <h3 class="font-bold text-lg mb-2">Game #<?= $game['id'] ?></h3>
          <div class="flex items-center mb-4">
            <span class="text-sm text-gray-600">Progress:</span>
            <div class="progress-bar w-full ml-2">
              <div class="progress-fill" style="width: <?= ($game['guess_count'] / 6) * 100 ?>%"></div>
            </div>
          </div>
          <p class="text-sm text-gray-600 mb-4">Started: <?= date('M j, g:i a', strtotime($game['started_at'])) ?></p>
          <div class="flex gap-2">
            <a href="index.php?continue=<?= $game['id'] ?>" class="btn-primary text-white px-4 py-2 rounded-lg text-sm font-bold">Continue</a>
            <button onclick="confirmAbandon(<?= $game['id'] ?>)" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-300">Abandon</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Quick Stats -->
    <div class="mb-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Your Stats</h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="game-card p-6 text-center">
          <div class="text-4xl font-bold text-indigo-600 mb-2"><?= $userStats['total_games'] ?></div>
          <div class="text-gray-600">Games Played</div>
        </div>
        <div class="game-card p-6 text-center">
          <div class="text-4xl font-bold text-green-600 mb-2"><?= $userStats['wins'] ?></div>
          <div class="text-gray-600">Games Won</div>
        </div>
        <div class="game-card p-6 text-center">
          <div class="text-4xl font-bold text-yellow-500 mb-2"><?= $userStats['win_percentage'] ?>%</div>
          <div class="text-gray-600">Win Rate</div>
        </div>
        <div class="game-card p-6 text-center">
          <div class="text-4xl font-bold text-blue-600 mb-2"><?= $userStats['total_points'] ?></div>
          <div class="text-gray-600">Total Points</div>
        </div>
      </div>
    </div>

    <!-- Leaderboard -->
    <div id="leaderboard" class="mb-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Leaderboard</h2>
      <div class="game-card overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-100">
            <tr>
              <th class="py-3 px-4 text-left">Rank</th>
              <th class="py-3 px-4 text-left">Player</th>
              <th class="py-3 px-4 text-left">Games</th>
              <th class="py-3 px-4 text-left">Wins</th>
              <th class="py-3 px-4 text-left">Win Rate</th>
              <th class="py-3 px-4 text-left">Points</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($leaderboard as $index => $player): ?>
            <tr class="leaderboard-item border-t border-gray-200 <?= $player['username'] === $_SESSION['username'] ? 'bg-blue-50' : 'hover:bg-gray-50' ?>">
              <td class="py-3 px-4 font-bold"><?= $index + 1 ?></td>
              <td class="py-3 px-4">
                <div class="flex items-center">
                  <?php if ($player['username'] === $_SESSION['username']): ?>
                    <span class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full mr-2">You</span>
                  <?php endif; ?>
                  <?= htmlspecialchars($player['username']) ?>
                </div>
              </td>
              <td class="py-3 px-4"><?= $player['total_games'] ?></td>
              <td class="py-3 px-4"><?= $player['wins'] ?></td>
              <td class="py-3 px-4"><?= $player['win_percentage'] ?>%</td>
              <td class="py-3 px-4 font-bold"><?= $player['total_points'] ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="mt-4 text-center">
        <a href="leaderboard.php" class="btn-secondary text-white px-6 py-2 rounded-lg font-bold inline-block">View Full Leaderboard</a>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-white border-t border-gray-200 py-6">
    <div class="max-w-7xl mx-auto px-4 text-center text-gray-600 text-sm">
      <p>Wordle Clone &copy; <?= date('Y') ?> - All rights reserved</p>
    </div>
  </footer>

  <script>
    function confirmAbandon(gameId) {
      if (confirm('Are you sure you want to abandon this game? This cannot be undone.')) {
        window.location.href = `game.php?abandon=${gameId}`;
      }
    }
  </script>
</body>

</html>