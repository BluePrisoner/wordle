<?php
require_once 'config.php';
requireLogin();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = $_SESSION['user_id'];
    $conn = getDBConnection();

    switch ($action) {
        case 'new_game':
            $stmt = $conn->prepare("SELECT id, word FROM dictionary ORDER BY RAND() LIMIT 1");
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $wordData = $result->fetch_assoc();
                $stmt = $conn->prepare("INSERT INTO game_sessions (user_id, word_id, guesses, guess_count, status) VALUES (?, ?, '[]', 0, 'playing')");
                $stmt->bind_param("ii", $userId, $wordData['id']);
                if ($stmt->execute()) {
                    $response = [
                        'success' => true,
                        'game_id' => $conn->insert_id,
                        'message' => 'New game started'
                    ];
                } else {
                    $response['message'] = 'Failed to create game session';
                }
            } else {
                $response['message'] = 'No words found in dictionary';
            }
            break;

        case 'submit_guess':
            $gameId = (int) ($_POST['game_id'] ?? 0);
            $guess = strtolower(trim($_POST['guess'] ?? ''));

            if (strlen($guess) !== 5 || !ctype_alpha($guess)) {
                $response['message'] = 'Guess must be exactly 5 letters';
                break;
            }

            $stmt = $conn->prepare("SELECT gs.*, d.word as target_word FROM game_sessions gs JOIN dictionary d ON gs.word_id = d.id WHERE gs.id = ? AND gs.user_id = ? AND gs.status = 'playing'");
            $stmt->bind_param("ii", $gameId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $response['message'] = 'Game not found or already completed';
                break;
            }

            $game = $result->fetch_assoc();
            $guesses = json_decode($game['guesses'], true) ?: [];

            if (count($guesses) >= 6) {
                $response['message'] = 'Maximum guesses reached';
                break;
            }

            $stmt = $conn->prepare("SELECT id FROM dictionary WHERE word = ?");
            $stmt->bind_param("s", $guess);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                $response['message'] = 'Word not in dictionary';
                break;
            }

            $guesses[] = $guess;
            $newGuessCount = count($guesses);
            $won = ($guess === $game['target_word']);
            $gameOver = $won || $newGuessCount >= 6;

            $points = $won ? match ($newGuessCount) {
                1 => 100,
                2 => 80,
                3 => 60,
                4 => 40,
                5 => 20,
                default => 10,
            } : 0;

            $status = $won ? 'won' : ($gameOver ? 'lost' : 'playing');
            $completedAt = $gameOver ? date('Y-m-d H:i:s') : null;

            $stmt = $conn->prepare("UPDATE game_sessions SET guesses = ?, guess_count = ?, points = ?, status = ?, completed_at = ? WHERE id = ?");
            $guessesJson = json_encode($guesses);
            $stmt->bind_param("siissi", $guessesJson, $newGuessCount, $points, $status, $completedAt, $gameId);
            $stmt->execute();

            $feedback = calculateLetterFeedback($guess, $game['target_word']);

            $response = [
                'success' => true,
                'guess' => $guess,
                'feedback' => $feedback,
                'won' => $won,
                'game_over' => $gameOver,
                'points' => $points,
                'attempts' => $newGuessCount,
                'message' => $won ? '🎉 Correct!' : ($gameOver ? '❌ Game Over. Word was: ' . strtoupper($game['target_word']) : 'Guess submitted')
            ];
            break;

        case 'get_game_state':
            $gameId = (int) ($_POST['game_id'] ?? 0);
            $stmt = $conn->prepare("SELECT gs.*, d.word as target_word FROM game_sessions gs JOIN dictionary d ON gs.word_id = d.id WHERE gs.id = ? AND gs.user_id = ?");
            $stmt->bind_param("ii", $gameId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $game = $result->fetch_assoc();
                $guesses = json_decode($game['guesses'], true) ?: [];

                $response = [
                    'success' => true,
                    'game_id' => $game['id'],
                    'guesses' => $guesses,
                    'attempts' => $game['guess_count'],
                    'status' => $game['status'],
                    'points' => $game['points'],
                    'target_word' => $game['target_word']
                ];
            } else {
                $response['message'] = 'Game not found';
            }
            break;

        case 'abandon_game':
            $gameId = (int) ($_POST['game_id'] ?? 0);
            $stmt = $conn->prepare("DELETE FROM game_sessions WHERE id = ? AND user_id = ? AND status = 'playing'");
            $stmt->bind_param("ii", $gameId, $userId);
            if ($stmt->execute()) {
                $response = [
                    'success' => true,
                    'message' => 'Game abandoned successfully'
                ];
            } else {
                $response['message'] = 'Failed to abandon game';
            }
            break;

        default:
            $response['message'] = 'Invalid action';
    }

    $conn->close();
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);

// Helper function to calculate letter feedback
function calculateLetterFeedback(string $guess, string $target): array
{
    $feedback = array_fill(0, 5, 'gray');
    $targetLetters = str_split($target);
    $guessLetters = str_split($guess);

    // First pass: greens
    for ($i = 0; $i < 5; $i++) {
        if ($guessLetters[$i] === $targetLetters[$i]) {
            $feedback[$i] = 'green';
            $targetLetters[$i] = null;
            $guessLetters[$i] = null;
        }
    }

    // Second pass: yellows
    for ($i = 0; $i < 5; $i++) {
        if ($guessLetters[$i] !== null) {
            $index = array_search($guessLetters[$i], $targetLetters, true);
            if ($index !== false) {
                $feedback[$i] = 'yellow';
                $targetLetters[$index] = null;
            }
        }
    }

    return $feedback;
}
?>