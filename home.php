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
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
      color: #e2e8f0;
    }

    .game-card {
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
      border-radius: 12px;
      background: #1e293b;
      border: 1px solid #334155;
    }

    .game-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.3);
      border-color: #475569;
    }

    .btn-primary {
      background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);
      transition: all 0.3s ease;
      color: white;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 6px rgba(14, 165, 233, 0.3);
    }

    .btn-secondary {
      background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
      transition: all 0.3s ease;
      color: white;
    }

    .btn-secondary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 6px rgba(20, 184, 166, 0.3);
    }

    .leaderboard-item {
      transition: all 0.3s ease;
    }

    .leaderboard-item:hover {
      background-color: #334155 !important;
      transform: translateX(5px);
    }

    .progress-bar {
      height: 8px;
      border-radius: 4px;
      background: #334155;
    }

    .progress-fill {
      height: 100%;
      border-radius: 4px;
      background: linear-gradient(90deg, #0ea5e9 0%, #0369a1 100%);
    }

    table {
      border-collapse: separate;
      border-spacing: 0;
    }

    th {
      background-color: #1e293b;
      color: #94a3b8;
    }

    td, th {
      border-bottom: 1px solid #334155;
    }

    tr:last-child td {
      border-bottom: none;
    }
  </style>
</head>

<body class="min-h-screen">
  <!-- Navigation -->
  <nav class="bg-gradient-to-r from-gray-900 to-black shadow-xl">
    <div class="max-w-7xl mx-auto px-6 py-3 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-teal-400 tracking-tight">Wordle Clone</h1>
      <div class="flex items-center gap-6">
        <div class="flex items-center gap-4 bg-gray-800/50 rounded-full px-4 py-2 backdrop-blur-sm border border-gray-700">
          <div class="flex flex-col items-center">
            <span class="text-xs font-medium text-teal-300/80">Games</span>
            <span class="text-sm font-semibold text-white"><?= $userStats['total_games']; ?></span>
          </div>
          <div class="h-5 w-px bg-gray-600"></div>
          <div class="flex flex-col items-center">
            <span class="text-xs font-medium text-teal-300/80">Wins</span>
            <span class="text-sm font-semibold text-teal-400"><?= $userStats['wins']; ?></span>
          </div>
          <div class="h-5 w-px bg-gray-600"></div>
          <div class="flex flex-col items-center">
            <span class="text-xs font-medium text-teal-300/80">Points</span>
            <span class="text-sm font-semibold text-amber-300"><?= $userStats['total_points']; ?></span>
          </div>
        </div>
        <div class="flex items-center gap-4">
          <span class="text-sm font-medium text-gray-300">Welcome, <?= htmlspecialchars($_SESSION['username']); ?></span>
          <a href="logout.php" class="text-sm font-medium text-white bg-teal-600 hover:bg-teal-500 px-4 py-1.5 rounded-full transition-all duration-200 shadow-md hover:shadow-teal-500/20">Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="max-w-7xl mx-auto py-8 px-4">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-slate-800 to-slate-700 rounded-xl p-8 mb-8 text-white border border-slate-600">
      <h1 class="text-4xl font-bold mb-4 text-teal-400">Welcome to Wordle Clone!</h1>
      <p class="text-xl mb-6 text-slate-300">Guess the hidden 5-letter word in 6 tries. Each guess must be a valid word.</p>
      <div class="flex gap-4">
        <a href="index.php" class="btn-primary px-6 py-3 rounded-lg font-bold">New Game</a>
        <a href="#leaderboard" class="bg-slate-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-slate-500 transition-colors">View Leaderboard</a>
      </div>
    </div>

    <!-- Incomplete Games -->
    <?php if (!empty($incompleteGames)): ?>
      <div class="mb-12">
        <h2 class="text-2xl font-bold mb-4 text-teal-400">Continue Your Games</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($incompleteGames as $game): ?>
            <div class="game-card p-6">
              <h3 class="font-bold text-lg mb-2 text-white">Game #<?= $game['id'] ?></h3>
              <div class="flex items-center mb-4">
                <span class="text-sm text-slate-400">Progress:</span>
                <div class="progress-bar w-full ml-2">
                  <div class="progress-fill" style="width: <?= ($game['guess_count'] / 6) * 100 ?>%"></div>
                </div>
              </div>
              <p class="text-sm text-slate-400 mb-4">Started: <?= date('M j, g:i a', strtotime($game['started_at'])) ?></p>
              <div class="flex gap-2">
                <a href="index.php?continue=<?= $game['id'] ?>" class="btn-primary px-4 py-2 rounded-lg text-sm font-bold">Continue</a>
                <button onclick="confirmAbandon(<?= $game['id'] ?>)" class="bg-slate-700 text-slate-300 px-4 py-2 rounded-lg text-sm font-bold hover:bg-slate-600 transition-colors">Abandon</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Quick Stats -->
    <div class="mb-12">
      <h2 class="text-2xl font-bold mb-6 text-teal-400">Your Stats</h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="game-card p-6 text-center">
          <div class="text-4xl font-bold text-sky-400 mb-2"><?= $userStats['total_games'] ?></div>
          <div class="text-slate-400">Games Played</div>
        </div>
        <div class="game-card p-6 text-center">
          <div class="text-4xl font-bold text-teal-400 mb-2">
            <?= $userStats['total_games'] != 0 ? $userStats['wins'] : "N/A" ?>
          </div>
          <div class="text-slate-400">Games Won</div>
        </div>
        <div class="game-card p-6 text-center">
          <div class="text-4xl font-bold text-amber-400 mb-2">
            <?= $userStats['total_games'] != 0 ? $userStats['win_percentage'] . "%" : "N/A" ?>
          </div>
          <div class="text-slate-400">Win Rate</div>
        </div>
        <div class="game-card p-6 text-center">
          <div class="text-4xl font-bold text-purple-400 mb-2">
            <?= $userStats['total_games'] != 0 ? $userStats['total_points'] : "N/A" ?>
          </div>
          <div class="text-slate-400">Total Points</div>
        </div>
      </div>
    </div>

    <!-- Leaderboard -->
    <div id="leaderboard" class="mb-12">
      <h2 class="text-2xl font-bold mb-6 text-teal-400">Leaderboard</h2>
      <div class="game-card overflow-hidden">
        <table class="w-full">
          <thead>
            <tr>
              <th class="py-3 px-4 text-left text-slate-400">Rank</th>
              <th class="py-3 px-4 text-left text-slate-400">Player</th>
              <th class="py-3 px-4 text-left text-slate-400">Games</th>
              <th class="py-3 px-4 text-left text-slate-400">Wins</th>
              <th class="py-3 px-4 text-left text-slate-400">Win Rate</th>
              <th class="py-3 px-4 text-left text-slate-400">Points</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($leaderboard as $index => $player): ?>
              <tr class="leaderboard-item <?= $player['username'] === $_SESSION['username'] ? 'bg-slate-800' : 'bg-slate-700' ?>">
                <td class="py-3 px-4 font-bold text-white"><?= $index + 1 ?></td>
                <td class="py-3 px-4">
                  <div class="flex items-center">
                    <?php if ($player['username'] === $_SESSION['username']): ?>
                      <span class="bg-teal-600 text-white text-xs px-2 py-1 rounded-full mr-2">You</span>
                    <?php endif; ?>
                    <span class="text-white"><?= htmlspecialchars($player['username']) ?></span>
                  </div>
                </td>
                <td class="py-3 px-4 text-white"><?= $player['total_games'] ?></td>
                <td class="py-3 px-4 text-white"><?= $player['total_games'] != 0 ? $player['wins'] : "N/A" ?></td>
                <td class="py-3 px-4 text-white"><?= $player['total_games'] != 0 ? $player['win_percentage'] . "%" : "N/A" ?></td>
                <td class="py-3 px-4 font-bold text-white"><?= $player['total_games'] != 0 ? $player['total_points'] : "N/A" ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="mt-4 text-center">
        <a href="leaderboard.php" class="btn-secondary px-6 py-2 rounded-lg font-bold inline-block">View Full Leaderboard</a>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-slate-800 border-t border-slate-700 py-6">
    <div class="max-w-7xl mx-auto px-4 text-center text-slate-400 text-sm">
      <p>Wordle Clone &copy; <?= date('Y') ?> - All rights reserved</p>
    </div>
  </footer>

  <script>
    function confirmAbandon(gameId) {
      if (confirm('Are you sure you want to abandon this game? This cannot be undone.')) {
        fetch('game.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=abandon_game&game_id=${gameId}`
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              location.reload();
            } else {
              alert(data.message);
            }
          });
      }
    }
  </script>
</body>
</html>