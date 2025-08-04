// Additional JavaScript functionality for Wordle Clone

// Toast notification system
class Toast {
    static show(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'toastSlideOut 0.3s ease-in';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, duration);
    }
}

// Enhanced game statistics
class GameStats {
    constructor() {
        this.stats = this.loadStats();
    }
    
    loadStats() {
        const saved = localStorage.getItem('wordle_stats');
        return saved ? JSON.parse(saved) : {
            gamesPlayed: 0,
            gamesWon: 0,
            currentStreak: 0,
            maxStreak: 0,
            guessDistribution: {
                1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0
            }
        };
    }
    
    saveStats() {
        localStorage.setItem('wordle_stats', JSON.stringify(this.stats));
    }
    
    updateStats(won, attempts) {
        this.stats.gamesPlayed++;
        
        if (won) {
            this.stats.gamesWon++;
            this.stats.currentStreak++;
            this.stats.maxStreak = Math.max(this.stats.maxStreak, this.stats.currentStreak);
            this.stats.guessDistribution[attempts]++;
        } else {
            this.stats.currentStreak = 0;
        }
        
        this.saveStats();
    }
    
    getWinRate() {
        return this.stats.gamesPlayed > 0 
            ? Math.round((this.stats.gamesWon / this.stats.gamesPlayed) * 100)
            : 0;
    }
}

// Keyboard shortcuts and accessibility
class KeyboardManager {
    constructor() {
        this.setupKeyboardShortcuts();
        this.setupAccessibility();
    }
    
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Escape to close modals
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
            
            // Ctrl/Cmd + N for new game
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                const newGameBtn = document.getElementById('new-game-btn');
                if (newGameBtn) newGameBtn.click();
            }
            
            // Ctrl/Cmd + L for leaderboard
            if ((e.ctrlKey || e.metaKey) && e.key === 'l') {
                e.preventDefault();
                window.location.href = 'leaderboard.php';
            }
        });
    }
    
    setupAccessibility() {
        // Add ARIA labels to interactive elements
        const tiles = document.querySelectorAll('.tile');
        tiles.forEach((tile, index) => {
            const row = Math.floor(index / 5) + 1;
            const col = (index % 5) + 1;
            tile.setAttribute('aria-label', `Tile ${row}-${col}`);
        });
        
        // Add keyboard navigation to virtual keyboard
        const keys = document.querySelectorAll('.key');
        keys.forEach((key, index) => {
            key.setAttribute('tabindex', '0');
            key.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    key.click();
                }
            });
        });
    }
    
    closeAllModals() {
        const modals = document.querySelectorAll('#instructions-modal, #result-modal');
        modals.forEach(modal => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
    }
}

// Theme management
class ThemeManager {
    constructor() {
        this.currentTheme = localStorage.getItem('wordle_theme') || 'light';
        this.applyTheme();
        this.setupThemeToggle();
    }
    
    applyTheme() {
        document.documentElement.setAttribute('data-theme', this.currentTheme);
        document.body.classList.toggle('dark', this.currentTheme === 'dark');
    }
    
    toggleTheme() {
        this.currentTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        localStorage.setItem('wordle_theme', this.currentTheme);
        this.applyTheme();
    }
    
    setupThemeToggle() {
        // Add theme toggle button to navigation if needed
        const nav = document.querySelector('nav');
        if (nav) {
            const themeBtn = document.createElement('button');
            themeBtn.innerHTML = this.currentTheme === 'light' ? '🌙' : '☀️';
            themeBtn.className = 'ml-4 p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700';
            themeBtn.setAttribute('aria-label', 'Toggle theme');
            themeBtn.addEventListener('click', () => this.toggleTheme());
            
            const navItems = nav.querySelector('.flex.items-center.space-x-4');
            if (navItems) {
                navItems.appendChild(themeBtn);
            }
        }
    }
}

// Game sharing functionality
class ShareManager {
    static shareResult(guesses, won, word) {
        const emojiMap = {
            'correct': '🟩',
            'present': '🟨',
            'absent': '⬜'
        };
        
        let shareText = `Wordle Clone ${won ? '✅' : '❌'}\n`;
        
        guesses.forEach(guess => {
            const feedback = guess.feedback || [];
            const emojiRow = feedback.map(f => emojiMap[f] || '⬜').join('');
            shareText += emojiRow + '\n';
        });
        
        shareText += `\nPlay at: ${window.location.origin}`;
        
        if (navigator.share) {
            navigator.share({
                title: 'Wordle Clone Result',
                text: shareText
            });
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(shareText).then(() => {
                Toast.show('Result copied to clipboard!', 'success');
            });
        }
    }
}

// Performance monitoring
class PerformanceMonitor {
    static measureGameTime() {
        const startTime = performance.now();
        
        return {
            end: () => {
                const endTime = performance.now();
                const duration = Math.round(endTime - startTime);
                console.log(`Game completed in ${duration}ms`);
                return duration;
            }
        };
    }
    
    static logGameStats(stats) {
        console.log('Game Statistics:', stats);
    }
}

// Initialize additional features when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize managers
    new KeyboardManager();
    new ThemeManager();
    
    // Global game stats
    window.gameStats = new GameStats();
    
    // Add loading states to buttons
    const buttons = document.querySelectorAll('button');
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            if (button.classList.contains('loading')) return;
            
            button.classList.add('loading');
            button.disabled = true;
            
            // Re-enable after a short delay
            setTimeout(() => {
                button.classList.remove('loading');
                button.disabled = false;
            }, 1000);
        });
    });
    
    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add service worker for offline support (optional)
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('SW registered: ', registration);
            })
            .catch(registrationError => {
                console.log('SW registration failed: ', registrationError);
            });
    }
});

// Export classes for use in main game
window.Toast = Toast;
window.GameStats = GameStats;
window.ShareManager = ShareManager;
window.PerformanceMonitor = PerformanceMonitor; 