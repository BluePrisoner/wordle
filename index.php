<?php
require_once 'config.php';
requireLogin();

$userStats = getUserStats($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Wordle Clone - Game</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .tile {
      width: 3rem;
      height: 3rem;
      border: 2px solid #d1d5db;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 1.5rem;
      text-transform: uppercase;
      transition: all 0.3s ease;
    }

    .tile.filled {
      border-color: #6b7280;
    }

    .tile.correct {
      background-color: #22c55e;
      border-color: #22c55e;
      color: #fff;
    }

    .tile.present {
      background-color: #eab308;
      border-color: #eab308;
      color: #fff;
    }

    .tile.absent {
      background-color: #6b7280;
      border-color: #6b7280;
      color: #fff;
    }

    .key {
      padding: 0.5rem 0.75rem;
      margin: 0.25rem;
      font-weight: bold;
      border-radius: 0.375rem;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }

    .key.correct {
      background-color: #22c55e;
      color: white;
    }

    .key.present {
      background-color: #eab308;
      color: white;
    }

    .key.absent {
      background-color: #6b7280;
      color: white;
    }
  </style>
</head>

<body class="bg-gray-100 min-h-screen">
  <!-- Navigation -->
  <nav class="bg-white shadow">
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

  <!-- Main Game Container -->
  <div class="max-w-md mx-auto py-8 px-4">
    <!-- Game Board -->
    <div id="game-board" class="grid gap-1 mb-8">
      <?php for ($row = 0; $row < 6; $row++): ?>
        <div class="grid grid-cols-5 gap-1">
          <?php for ($col = 0; $col < 5; $col++): ?>
            <div class="tile" data-row="<?= $row ?>" data-col="<?= $col ?>"></div>
          <?php endfor; ?>
        </div>
      <?php endfor; ?>
    </div>

    <!-- Controls -->
    <div class="text-center mb-6">
      <button id="exit-game-btn" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Exit</button>
      <button id="instructions-btn" class="bg-gray-600 text-white px-6 py-2 rounded ml-2 hover:bg-gray-700">How to
        Play</button>
    </div>

    <!-- Keyboard -->
    <div class="text-center mb-6">
      <div class="flex justify-center flex-wrap max-w-md mx-auto">
        <?php
        $keyboard = ['q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', 'a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'enter', 'z', 'x', 'c', 'v', 'b', 'n', 'm', 'backspace'];
        foreach ($keyboard as $key) {
          $label = $key === 'backspace' ? '⌫' : strtoupper($key);
          echo "<div class='key bg-gray-300' data-key=\"$key\">$label</div>";
        }
        ?>
      </div>
    </div>

    <!-- Status Message -->
    <div id="status-message" class="text-center text-lg font-semibold text-gray-700"></div>
  </div>

  <!-- Modals -->
  <div id="instructions-modal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-md max-w-md w-full">
      <h2 class="text-xl font-bold mb-2">How to Play</h2>
      <p>Guess the 5-letter word in 6 tries. Each guess must be a valid word.</p>
      <ul class="list-disc list-inside mt-2 text-sm text-gray-700">
        <li><strong>Green</strong>: correct letter, correct position</li>
        <li><strong>Yellow</strong>: letter is in the word but wrong position</li>
        <li><strong>Gray</strong>: letter not in the word</li>
        <li>Points are based on how fast you guess the word!</li>
      </ul>
      <button id="close-instructions" class="mt-4 w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Got
        it!</button>
    </div>
  </div>

  <!-- Result Modal -->
  <div id="result-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-md max-w-md w-full text-center">
      <h2 id="result-title" class="text-2xl font-bold mb-2"></h2>
      <p id="result-message" class="mb-4"></p>
      <div class="flex justify-center gap-4">
        <button id="play-again" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Play Again</button>
        <button id="go-home" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-gray-700">Home</button>
      </div>
    </div>
  </div>

  <!-- Game Logic Script -->
  <script>
    class WordleGame {
      constructor() {
        this.reset();
        this.initEvents();

        // Check if we're continuing a game from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const continueGameId = urlParams.get('continue');

        if (continueGameId) {
          this.loadGame(continueGameId);
        } else {
          this.startNewGame();
        }
      }

      reset() {
        this.currentRow = 0;
        this.currentCol = 0;
        this.gameId = null;
        this.gameOver = false;
        this.currentGuess = '';
        document.querySelectorAll('.tile').forEach(tile => {
          tile.textContent = '';
          tile.className = 'tile';
        });
        document.querySelectorAll('.key').forEach(key => key.className = 'key bg-gray-300');
      }

      initEvents() {
        document.getElementById('exit-game-btn').onclick = () => {
          window.location.href = 'home.php';
        };
        document.getElementById('go-home').onclick = () => {
          window.location.href = 'home.php';
        };
        document.getElementById('instructions-btn').onclick = () => this.toggleModal('instructions-modal', true);
        document.getElementById('close-instructions').onclick = () => this.toggleModal('instructions-modal', false);
        document.getElementById('play-again').onclick = () => {
          this.toggleModal('result-modal', false);
          this.startNewGame();
        };

        document.querySelectorAll('.key').forEach(key => {
          key.onclick = () => {
            const k = key.dataset.key;
            if (this.gameOver) return;
            if (k === 'backspace') this.backspace();
            else if (k === 'enter') this.submitGuess();
            else this.typeLetter(k);
          };
        });

        document.addEventListener('keydown', e => {
          if (this.gameOver) return;
          const key = e.key.toLowerCase();
          if (/^[a-z]$/.test(key)) this.typeLetter(key);
          else if (key === 'enter') this.submitGuess();
          else if (key === 'backspace') this.backspace();
        });
      }

      toggleModal(id, show) {
        const modal = document.getElementById(id);
        modal.classList.toggle('hidden', !show);
        modal.classList.toggle('flex', show);
      }

      showStatus(msg) {
        document.getElementById('status-message').textContent = msg;
      }

      typeLetter(letter) {
        if (this.currentCol < 5) {
          const tile = document.querySelector(`[data-row="${this.currentRow}"][data-col="${this.currentCol}"]`);
          tile.textContent = letter.toUpperCase();
          tile.classList.add('filled');
          this.currentGuess += letter;
          this.currentCol++;
        }
      }

      backspace() {
        if (this.currentCol > 0) {
          this.currentCol--;
          this.currentGuess = this.currentGuess.slice(0, -1);
          const tile = document.querySelector(`[data-row="${this.currentRow}"][data-col="${this.currentCol}"]`);
          tile.textContent = '';
          tile.classList.remove('filled');
        }
      }

      async startNewGame() {
        this.reset();
        const res = await fetch('game.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'action=new_game'
        });
        const data = await res.json();
        if (data.success) {
          // Redirect to same page with ?continue=<game_id>
          window.location.href = `index.php?continue=${data.game_id}`;
        } else {
          this.showStatus("Error: " + data.message);
        }

      }

      async loadGame(gameId) {
        const res = await fetch('game.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=continue_game&game_id=${gameId}`
        });

        const data = await res.json();
        if (data.success) {
          this.gameId = data.game_id;

          // Restore existing guesses and their feedback if any
          if (data.existing_guesses && data.existing_guesses.length > 0) {
            for (let i = 0; i < data.existing_guesses.length; i++) {
              const guess = data.existing_guesses[i];
              const feedback = data.feedback[i];

              for (let j = 0; j < guess.length; j++) {
                const tile = document.querySelector(`[data-row="${i}"][data-col="${j}"]`);
                tile.textContent = guess[j].toUpperCase();
                tile.classList.add('filled');

                // Add feedback color class
                const feedbackClass = {
                  green: 'correct',
                  yellow: 'present',
                  gray: 'absent'
                }[feedback[j]];
                tile.classList.add(feedbackClass);

                // Update keyboard colors
                const key = document.querySelector(`.key[data-key="${guess[j]}"]`);
                if (key) {
                  // Only update if not already marked correct (correct takes precedence)
                  if (!key.classList.contains('correct')) {
                    key.classList.add(feedbackClass);
                  }
                }
              }
              this.currentRow = i + 1;
            }
            this.showStatus("Game loaded successfully!");
          }
        } else {
          this.showStatus("Error: " + data.message);
          this.startNewGame(); // Fallback to new game if loading fails
        }
      }
      async submitGuess() {
        if (this.currentGuess.length !== 5) return this.showStatus("Word must be 5 letters");

        const res = await fetch('game.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=submit_guess&game_id=${this.gameId}&guess=${this.currentGuess}`
        });

        const data = await res.json();
        if (!data.success) return this.showStatus(data.message);

        for (let i = 0; i < 5; i++) {
          const tile = document.querySelector(`[data-row="${this.currentRow}"][data-col="${i}"]`);
          const feedbackClass = {
            green: 'correct',
            yellow: 'present',
            gray: 'absent'
          }[data.feedback[i]];

          tile.classList.add(feedbackClass);

          const k = document.querySelector(`.key[data-key="${this.currentGuess[i]}"]`);
          if (k) k.classList.add(feedbackClass);

        }

        if (data.game_over) {
          this.gameOver = true;
          document.getElementById('result-title').textContent = data.won ? "🎉 You Won!" : "😢 You Lost!";
          document.getElementById('result-message').textContent = data.message;
          this.toggleModal('result-modal', true);
        } else {
          this.currentRow++;
          this.currentCol = 0;
          this.currentGuess = '';
          this.showStatus(data.message);
        }
      }

    }

    document.addEventListener('DOMContentLoaded', () => new WordleGame());
  </script>
</body>

</html>