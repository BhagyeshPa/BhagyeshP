<div class="header-logo">
    <img src="assets/logo.png" alt="Kopran Logo">
</div>

<div class="user-profile">
    <button id="darkModeToggle" class="btn btn-outline-secondary btn-sm me-3" title="Toggle Dark Mode">
        <i class="fas fa-moon"></i>
    </button>
    <div class="user-info">
        <div class="user-name">
            <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Guest'); ?>
        </div>
        <div class="user-role">
            <?php echo ucfirst(htmlspecialchars($_SESSION['role'] ?? 'User')); ?>
        </div>
    </div>
    <div class="logout-section">
        <a href="logout.php" class="btn btn-outline-danger btn-sm" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
        </a>
        <div class="logout-text">Logout</div>
    </div>
</div>


<style>
    .header-logo img {
        height: 60px;
        margin-left: 5px;
        margin-top: 5px;
    }
    
    #darkModeToggle {
        border-radius: 8px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    #darkModeToggle:hover {
        background-color: var(--bg-color);
    }
</style>

<script>
    // Dark Mode Toggle Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const darkModeToggle = document.getElementById('darkModeToggle');
        const html = document.documentElement;
        
        // Check for saved dark mode preference
        const darkMode = localStorage.getItem('darkMode');
        if (darkMode === 'enabled') {
            html.classList.add('dark-mode');
            updateIcon(true);
        }
        
        darkModeToggle.addEventListener('click', function() {
            // Add transition class
            document.body.classList.add('theme-transitioning');
            
            html.classList.toggle('dark-mode');
            
            if (html.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
                updateIcon(true);
            } else {
                localStorage.setItem('darkMode', 'disabled');
                updateIcon(false);
            }
            
            // Remove transition class after animation completes
            setTimeout(() => {
                document.body.classList.remove('theme-transitioning');
            }, 1000);
        });
        
        function updateIcon(isDark) {
            const icon = darkModeToggle.querySelector('i');
            if (isDark) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        }
    });
</script>
</header>