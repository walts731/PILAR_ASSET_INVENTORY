    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <?php if (file_exists(__DIR__ . '/../js/' . basename($_SERVER['PHP_SELF'], '.php') . '.js')): ?>
        <script src="/PILAR_ASSET_INVENTORY/js/<?php echo basename($_SERVER['PHP_SELF'], '.php'); ?>.js"></script>
    <?php endif; ?>
    
    <script>
    // Function to handle dark mode toggle
    function handleDarkModeToggle() {
        fetch('/PILAR_ASSET_INVENTORY/includes/dark_mode_helper.php', {
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
            document.documentElement.classList.toggle('dark-mode', data.dark_mode);
            
            // Update the icon
            const icon = document.querySelector('#darkModeToggle i');
            if (data.dark_mode) {
                icon.classList.remove('bi-moon');
                icon.classList.add('bi-sun');
                icon.parentElement.title = 'Switch to Light Mode';
            } else {
                icon.classList.remove('bi-sun');
                icon.classList.add('bi-moon');
                icon.parentElement.title = 'Switch to Dark Mode';
            }
        });
    }
    
    // Add event listener to dark mode toggle button
    document.addEventListener('DOMContentLoaded', function() {
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', handleDarkModeToggle);
        }
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    </script>
</body>
</html>
