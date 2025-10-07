<?php
// Final script to properly modify the IIRUP button

$file_path = 'view_asset_details.php';
$lines = file($file_path, FILE_IGNORE_NEW_LINES);

$output = [];
$i = 0;
$count = count($lines);

while ($i < $count) {
    $line = $lines[$i];
    
    // Look for the specific IIRUP button section
    if (strpos($line, '<!-- Create IIRUP Button') !== false) {
        // Replace the comment
        $output[] = '                                    <!-- Add to IIRUP Button -->';
        $i++; // Skip the original comment line
        
        // Skip the PHP if condition and the entire old button block
        while ($i < $count && strpos($lines[$i], '<?php endif; ?>') === false) {
            $i++;
        }
        $i++; // Skip the endif line
        
        // Add the new button
        $output[] = '                                    <button class="btn btn-outline-warning add-to-iirup-btn"';
        $output[] = '                                        data-asset-id="<?= $asset[\'id\'] ?>">';
        $output[] = '                                        <i class="bi bi-exclamation-triangle me-2"></i>Add to IIRUP';
        $output[] = '                                    </button>';
    } else {
        $output[] = $line;
        $i++;
    }
}

// Write the modified content
file_put_contents($file_path, implode("\n", $output));

// Now add the JavaScript functionality
$content = file_get_contents($file_path);

// Find the document ready function and add our handler
$js_pattern = '/(\$\(document\)\.ready\(function\(\) \{\s*loadLifecycleEvents\(\);)/';
$js_replacement = '$1

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

$content = preg_replace($js_pattern, $js_replacement, $content);
file_put_contents($file_path, $content);

echo "Successfully modified view_asset_details.php!\n";
echo "Changes made:\n";
echo "1. Replaced conditional IIRUP button with simple Add to IIRUP button\n";
echo "2. Added JavaScript handler for AJAX functionality\n";
echo "3. Button now inserts asset into temp_iirup_items table\n";
echo "4. Redirects to IIRUP form without pre-populating\n";
?>
