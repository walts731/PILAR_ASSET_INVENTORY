<?php
// Add JavaScript handler for the IIRUP button

$file_path = 'view_asset_details.php';
$content = file_get_contents($file_path);

// Find the document ready function and add our handler
$search = '$(document).ready(function() {
            loadLifecycleEvents();

            $(document).on(\'click\', \'.transfer-asset\', function(e) {';

$replace = '$(document).ready(function() {
            loadLifecycleEvents();

            // Add to IIRUP button click handler
            $(document).on(\'click\', \'.add-to-iirup-btn\', function(e) {
                e.preventDefault();
                const button = $(this);
                const assetId = button.data(\'asset-id\');
                
                // Show loading state
                button.prop(\'disabled\', true);
                button.html(\'<i class="bi bi-hourglass-split me-2"></i>Adding...\');
                
                // Send AJAX request to add asset to temp table
                $.post(\'insert_iirup_button.php\', {
                    asset_id: assetId
                })
                .done(function(response) {
                    const data = typeof response === \'string\' ? JSON.parse(response) : response;
                    
                    if (data.success) {
                        // Show success state briefly
                        button.removeClass(\'btn-outline-warning\').addClass(\'btn-success\');
                        button.html(\'<i class="bi bi-check-circle me-2"></i>Added!\');
                        
                        // Redirect to IIRUP form after short delay
                        setTimeout(function() {
                            window.location.href = data.redirect || \'forms.php?id=7\';
                        }, 1000);
                    } else {
                        alert(data.message || \'Failed to add asset to IIRUP list\');
                        // Reset button
                        button.prop(\'disabled\', false);
                        button.html(\'<i class="bi bi-exclamation-triangle me-2"></i>Add to IIRUP\');
                    }
                })
                .fail(function() {
                    alert(\'An error occurred while adding asset to IIRUP list\');
                    // Reset button
                    button.prop(\'disabled\', false);
                    button.html(\'<i class="bi bi-exclamation-triangle me-2"></i>Add to IIRUP\');
                });
            });

            $(document).on(\'click\', \'.transfer-asset\', function(e) {';

$content = str_replace($search, $replace, $content);
file_put_contents($file_path, $content);

echo "JavaScript handler added successfully!\n";
echo "The IIRUP button now has full AJAX functionality.\n";
?>
