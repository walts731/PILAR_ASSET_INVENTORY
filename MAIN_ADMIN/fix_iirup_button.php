<?php
// Script to properly replace the IIRUP button section

$file_path = 'view_asset_details.php';
$content = file_get_contents($file_path);

// More specific pattern to match the exact IIRUP button section
$search = '<?php if (strtolower($asset[\'status\'] ?? \'\') === \'serviceable\'): ?>
    <a href="forms.php?id=7&asset_id=<?= $asset[\'id\'] ?>"
        class="btn btn-outline-warning" target="_blank">
        <i class="bi bi-exclamation-triangle me-2"></i>Create IIRUP
    </a>
<?php endif; ?>';

$replace = '<button class="btn btn-outline-warning add-to-iirup-btn"
                                        data-asset-id="<?= $asset[\'id\'] ?>">
                                        <i class="bi bi-exclamation-triangle me-2"></i>Add to IIRUP
                                    </button>';

$content = str_replace($search, $replace, $content);

// Add JavaScript for the button functionality
$js_search = '$(document).ready(function() {
            loadLifecycleEvents();';

$js_replace = '$(document).ready(function() {
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
            });';

$content = str_replace($js_search, $js_replace, $content);

// Write the modified content back to the file
file_put_contents($file_path, $content);

echo "IIRUP button successfully replaced!\n";
echo "The button now:\n";
echo "1. Adds asset to temp_iirup_items table\n";
echo "2. Redirects to IIRUP form without pre-populating\n";
?>
