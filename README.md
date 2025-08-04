# Wordle Clone

A complete Wordle game web application built with PHP, MySQL, and Tailwind CSS.

## Features

- **User Authentication**: Registration, login, and logout functionality
- **Wordle Game Logic**: 6 attempts to guess a 5-letter word
- **Visual Feedback**: Green (correct position), Yellow (correct letter, wrong position), Gray (not in word)
- **Scoring System**: Points based on number of attempts (100, 80, 60, 40, 20, 10, 0)
- **Leaderboard**: Rank users by total points, wins, and games played
- **Responsive Design**: Modern UI with Tailwind CSS
- **Virtual Keyboard**: On-screen keyboard with visual feedback
- **Game Statistics**: Track total games, wins, and points per user

## Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Tailwind CSS
- **Server**: XAMPP (Apache + MySQL)

## Installation & Setup

### Prerequisites

1. Install XAMPP (https://www.apachefriends.org/)
2. Start Apache and MySQL services in XAMPP Control Panel

### Database Setup

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `wordle_clone`
3. Import the SQL schema:

```sql
-- Users table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password_hash TEXT NOT NULL
);

-- Dictionary of valid 5-letter words
CREATE TABLE dictionary (
  id INT AUTO_INCREMENT PRIMARY KEY,
  word VARCHAR(5) UNIQUE NOT NULL
);

-- Game sessions
CREATE TABLE game_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  word_id INT,
  guesses TEXT, -- JSON string of 6 guesses
  guess_count INT,
  points INT DEFAULT 0,
  status ENUM('won', 'lost'),
  started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (word_id) REFERENCES dictionary(id) ON DELETE CASCADE
);
```

### Application Setup

1. Clone or download this project to your XAMPP `htdocs` folder
2. Navigate to the project directory
3. Update database configuration in `config.php` if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'wordle_clone');
   ```

4. Import words into the dictionary:
   - Visit `http://localhost/wordle-clone/import_words.php` in your browser
   - This will populate the dictionary with 5-letter words
   - Delete `import_words.php` after successful import for security

5. Access the application:
   - Go to `http://localhost/wordle-clone/`
   - Register a new account or login

## File Structure

```
wordle-clone/
├── index.php              # Main game interface
├── login.php              # User login page
├── register.php           # User registration page
├── logout.php             # Logout functionality
├── game.php               # Game logic API
├── leaderboard.php        # Leaderboard page
├── config.php             # Database configuration
├── import_words.php       # Word import script
├── assets/
│   ├── css/              # CSS files
│   └── js/               # JavaScript files
└── README.md             # This file
```

## Game Rules

1. **Objective**: Guess the 5-letter word in 6 attempts
2. **Feedback**:
   - 🟩 Green: Letter is in the correct position
   - 🟨 Yellow: Letter is in the word but wrong position
   - ⬜ Gray: Letter is not in the word
3. **Scoring**:
   - 1st try: 100 points
   - 2nd try: 80 points
   - 3rd try: 60 points
   - 4th try: 40 points
   - 5th try: 20 points
   - 6th try: 10 points
   - Failure: 0 points

## Security Features

- Password hashing using `password_hash()` and `password_verify()`
- Prepared statements to prevent SQL injection
- Input sanitization
- Session management
- Login required for game access

## How to Play

1. **Register/Login**: Create an account or login to start playing
2. **Start Game**: Click "New Game" to begin
3. **Make Guesses**: Type 5-letter words and press Enter
4. **View Feedback**: See color-coded feedback for each guess
5. **Track Progress**: View your stats and leaderboard position
6. **Play Again**: Start a new game anytime

## Customization

### Adding More Words

Edit `import_words.php` and add more 5-letter words to the `$words` array.

### Changing Scoring

Modify the scoring logic in `game.php` around line 80-90.

### Styling

The application uses Tailwind CSS. You can customize colors and styling by modifying the CSS classes in the HTML files.

## Troubleshooting

### Common Issues

1. **Database Connection Error**:
   - Ensure MySQL is running in XAMPP
   - Check database credentials in `config.php`
   - Verify database `wordle_clone` exists

2. **Words Not Loading**:
   - Run `import_words.php` to populate the dictionary
   - Check for any PHP errors in the browser console

3. **Game Not Working**:
   - Ensure all files are in the correct directory
   - Check that Apache is running
   - Verify file permissions

### Error Logs

Check XAMPP error logs:
- Apache: `xampp/apache/logs/error.log`
- PHP: `xampp/php/logs/php_error_log`

## Contributing

Feel free to submit issues and enhancement requests!

## License

This project is open source and available under the MIT License. 