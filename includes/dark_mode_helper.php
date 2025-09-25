<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize dark mode from session or default to light
if (!isset($_SESSION['dark_mode'])) {
    $_SESSION['dark_mode'] = false;
}

/**
 * Get the current dark mode state
 * @return bool True if dark mode is enabled, false otherwise
 */
function isDarkMode() {
    return $_SESSION['dark_mode'] ?? false;
}

/**
 * Toggle dark mode state
 */
function toggleDarkMode() {
    $_SESSION['dark_mode'] = !$_SESSION['dark_mode'];
    return $_SESSION['dark_mode'];
}

// Handle dark mode toggle request
if (isset($_POST['toggle_dark_mode'])) {
    header('Content-Type: application/json');
    echo json_encode(['dark_mode' => toggleDarkMode()]);
    exit;
}

// Add dark mode class to body if enabled
function getDarkModeClass() {
    return isDarkMode() ? 'dark-mode' : '';
}

// Generate the dark mode toggle button HTML
function getDarkModeToggle() {
    $isDark = isDarkMode();
    $icon = $isDark ? 'sun' : 'moon';
    $title = $isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
    
    return <<<HTML
    <button id="darkModeToggle" class="btn btn-link text-decoration-none" title="$title">
        <i class="bi bi-$icon"></i>
    </button>
    <script>
    // Add click handler for dark mode toggle
    document.getElementById('darkModeToggle').addEventListener('click', function() {
        fetch('includes/dark_mode_helper.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'toggle_dark_mode=1'
        })
        .then(response => response.json())
        .then(data => {
            // Toggle the dark mode class on the body
            document.body.classList.toggle('dark-mode', data.dark_mode);
            // Update the icon
            const icon = this.querySelector('i');
            if (data.dark_mode) {
                icon.classList.remove('bi-moon');
                icon.classList.add('bi-sun');
                this.title = 'Switch to Light Mode';
            } else {
                icon.classList.remove('bi-sun');
                icon.classList.add('bi-moon');
                this.title = 'Switch to Dark Mode';
            }
        });
    });
    </script>
    HTML;
}
?>
