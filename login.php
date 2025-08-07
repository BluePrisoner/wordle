<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $conn = getDBConnection();

        $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Redirect to game
                header('Location: home.php');
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Wordle Clone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }

        .login-card {
            background: #1e293b;
            border: 1px solid #334155;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .input-field {
            background: #334155;
            border-color: #475569;
            color: #f8fafc;
            transition: all 0.2s ease;
        }

        .input-field:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.2);
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4">
    <div class="login-card p-8 rounded-xl w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-teal-400">Wordle Clone</h1>
            <p class="text-slate-400 mt-2">Welcome back! Please login to continue</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-rose-900/50 border border-rose-700 text-rose-200 px-4 py-3 rounded-lg mb-6">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-slate-300 mb-2">Username</label>
                <input type="text" id="username" name="username" required
                    class="w-full px-4 py-3 input-field rounded-lg focus:outline-none focus:ring-0"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 input-field rounded-lg focus:outline-none focus:ring-0">
            </div>

            <button type="submit"
                class="w-full bg-teal-600 text-white py-3 px-4 rounded-lg hover:bg-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-800 transition duration-200 font-medium">
                Login
            </button>
        </form>

        <div class="text-center mt-6">
            <p class="text-slate-400">
                Don't have an account?
                <a href="register.php" class="text-teal-400 hover:text-teal-300 transition-colors">Register here</a>
            </p>
        </div>
    </div>
</body>

</html>