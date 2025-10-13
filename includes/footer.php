    <!-- Custom JS -->
    <?php if (file_exists(__DIR__ . '/../js/' . basename($_SERVER['PHP_SELF'], '.php') . '.js')): ?>
        <script src="/PILAR_ASSET_INVENTORY/js/<?php echo basename($_SERVER['PHP_SELF'], '.php'); ?>.js"></script>
    <?php endif; ?>
    
    <script>
    // Initialize Bootstrap components
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Handle tab changes
        var tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabEls.forEach(function(tabEl) {
            tabEl.addEventListener('shown.bs.tab', function (event) {
                // This ensures that any content that needs to be updated after tab change can be handled here
                if (typeof updateContentOnTabChange === 'function') {
                    updateContentOnTabChange(event);
                }
            });
        });
    });
    </script>
</body>
</html>
