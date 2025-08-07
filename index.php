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
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f0fff3ff 0%, #e0f2fe 100%);
    }

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
  <nav class="bg-gradient-to-r from-gray-900 to-black shadow-xl">
    <div class="max-w-7xl mx-auto px-6 py-3 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-teal-400 tracking-tight">Wordle Clone</h1>
      <div class="flex items-center gap-6">
        <div
          class="flex items-center gap-4 bg-gray-800/50 rounded-full px-4 py-2 backdrop-blur-sm border border-gray-700">
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
          <span class="text-sm font-medium text-gray-300">Welcome,
            <?= htmlspecialchars($_SESSION['username']); ?></span>
          <a href="logout.php"
            class="text-sm font-medium text-white bg-teal-600 hover:bg-teal-500 px-4 py-1.5 rounded-full transition-all duration-200 shadow-md hover:shadow-teal-500/20">Logout</a>
        </div>
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
      <button id="exit-game-btn" class="bg-teal-600 text-white px-6 py-2 rounded hover:bg-teal-700">Exit</button>
      <button id="instructions-btn" class="bg-gray-600 text-white px-6 py-2 rounded ml-2 hover:bg-gray-700">How to
        Play</button>
    </div>

    <!-- Keyboard -->
    <div class="text-center mb-6 space-y-2">
      <?php
      // QWERTY keyboard rows
      $rows = [
        ['q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p'],
        ['a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l'],
        ['enter', 'z', 'x', 'c', 'v', 'b', 'n', 'm', 'backspace']
      ];

      foreach ($rows as $rowIndex => $row) {
        $indent = match ($rowIndex) {
          1 => 'ml-3',   // second row indentation
          default => ''
        };

        echo "<div class='flex justify-center gap-1 $indent'>";
        foreach ($row as $key) {
          $label = $key === 'backspace' ? '⌫' : ($key === 'enter' ? 'Enter' : strtoupper($key));
          $wClass = in_array($key, ['enter', 'backspace']) ? 'w-16' : 'w-10';

          // Apply custom colors for Enter and Backspace
          $bgColor = match ($key) {
            'enter' => 'bg-blue-400 hover:bg-blue-500',
            'backspace' => 'bg-red-400 hover:bg-red-500',
            default => 'bg-gray-300 hover:bg-gray-400'
          };

          echo "<div class='key $wClass h-12 $bgColor rounded flex items-center justify-center cursor-pointer text-sm font-medium select-none' data-key=\"$key\">$label</div>";
        }
        echo "</div>";
      }
      ?>
    </div>


  </div>

  <!-- Flashing Status Toast -->
  <div id="status-toast"
    class="fixed top-5 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-4 py-2 rounded shadow z-50 opacity-0 transition-opacity duration-300 pointer-events-none">
  </div>

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
        document.querySelectorAll('.key').forEach(key => {
          const k = key.dataset.key;
          let baseClass = 'key';
          if (k === 'enter') {
            baseClass += ' bg-blue-500 hover:bg-blue-700 text-white';
          } else if (k === 'backspace') {
            baseClass += ' bg-red-500 hover:bg-red-700';
          } else {
            baseClass += ' bg-gray-300 hover:bg-gray-400';
          }
          key.className = baseClass;
        });

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

      showStatus(message, duration = 1500) {
        const toast = document.getElementById('status-toast');
        toast.textContent = message;
        toast.classList.remove('opacity-0');

        clearTimeout(this.statusTimeout);
        this.statusTimeout = setTimeout(() => {
          toast.classList.add('opacity-0');
        }, duration);
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
          this.cleanupBound = () => this.cleanupGame();
          window.addEventListener('beforeunload', this.cleanupBound);


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
      async cleanupGame() {
        if (!this.gameOver && this.gameId !== null) {
          navigator.sendBeacon('game.php', new URLSearchParams({
            action: 'cancel_if_empty',
            game_id: this.gameId
          }));
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