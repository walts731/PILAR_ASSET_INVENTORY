<?php
/**
 * Integration Script for Tag Format System
 * This script helps integrate the new tag format system with existing forms
 */

require_once 'connect.php';
require_once 'includes/tag_format_helper.php';

// Function to update ITR form processing
function updateITRFormProcessing() {
    $filePath = 'MAIN_ADMIN/save_itr_items.php';
    
    if (!file_exists($filePath)) {
        return "File not found: $filePath";
    }
    
    $content = file_get_contents($filePath);
    
    // Add helper include at the top
    if (strpos($content, "tag_format_helper.php") === false) {
        $content = str_replace(
            "require_once '../connect.php';",
            "require_once '../connect.php';\nrequire_once '../includes/tag_format_helper.php';",
            $content
        );
    }
    
    // Replace manual ITR number with automatic generation
    $content = preg_replace(
        '/\$itr_no\s*=\s*\$_POST\[\'itr_no\'\];?/',
        '$itr_no = generateTag(\'itr_no\');',
        $content
    );
    
    // Remove ITR number from required fields validation
    $content = str_replace(
        "empty(\$itr_no)",
        "false", // Always false since it's auto-generated
        $content
    );
    
    file_put_contents($filePath, $content);
    return "Updated ITR form processing";
}

// Function to update PAR form processing
function updatePARFormProcessing() {
    $filePath = 'MAIN_ADMIN/save_par_items.php';
    
    if (!file_exists($filePath)) {
        return "File not found: $filePath";
    }
    
    $content = file_get_contents($filePath);
    
    // Add helper include
    if (strpos($content, "tag_format_helper.php") === false) {
        $content = str_replace(
            "require_once '../connect.php';",
            "require_once '../connect.php';\nrequire_once '../includes/tag_format_helper.php';",
            $content
        );
    }
    
    // Replace manual PAR number with automatic generation
    $content = preg_replace(
        '/\$par_no\s*=\s*\$_POST\[\'par_no\'\];?/',
        '$par_no = generateTag(\'par_no\');',
        $content
    );
    
    file_put_contents($filePath, $content);
    return "Updated PAR form processing";
}

// Function to update ICS form processing
function updateICSFormProcessing() {
    $filePath = 'MAIN_ADMIN/save_ics_items.php';
    
    if (!file_exists($filePath)) {
        return "File not found: $filePath";
    }
    
    $content = file_get_contents($filePath);
    
    // Add helper include
    if (strpos($content, "tag_format_helper.php") === false) {
        $content = str_replace(
            "require_once '../connect.php';",
            "require_once '../connect.php';\nrequire_once '../includes/tag_format_helper.php';",
            $content
        );
    }
    
    // Replace manual ICS number with automatic generation
    $content = preg_replace(
        '/\$ics_no\s*=\s*\$_POST\[\'ics_no\'\];?/',
        '$ics_no = generateTag(\'ics_no\');',
        $content
    );
    
    file_put_contents($filePath, $content);
    return "Updated ICS form processing";
}

// Function to update RIS form processing
function updateRISFormProcessing() {
    $filePath = 'MAIN_ADMIN/save_ris_items.php';
    
    if (!file_exists($filePath)) {
        return "File not found: $filePath";
    }
    
    $content = file_get_contents($filePath);
    
    // Add helper include
    if (strpos($content, "tag_format_helper.php") === false) {
        $content = str_replace(
            "require_once '../connect.php';",
            "require_once '../connect.php';\nrequire_once '../includes/tag_format_helper.php';",
            $content
        );
    }
    
    // Replace manual RIS number with automatic generation
    $content = preg_replace(
        '/\$ris_no\s*=\s*\$_POST\[\'ris_no\'\];?/',
        '$ris_no = generateTag(\'ris_no\');',
        $content
    );
    
    file_put_contents($filePath, $content);
    return "Updated RIS form processing";
}

// Function to update form HTML files
function updateFormHTML($formFile, $tagType, $fieldName) {
    if (!file_exists($formFile)) {
        return "File not found: $formFile";
    }
    
    $content = file_get_contents($formFile);
    
    // Add helper include at the top of PHP section
    if (strpos($content, "tag_format_helper.php") === false) {
        $content = preg_replace(
            '/<\?php\s*\n/',
            "<?php\nrequire_once '../includes/tag_format_helper.php';\n",
            $content,
            1
        );
    }
    
    // Replace manual input field with auto-generated display
    $oldPattern = '/<input[^>]*name=["\']' . $fieldName . '["\'][^>]*>/i';
    $newField = '
    <div class="form-group">
        <label class="form-label">' . strtoupper($fieldName) . ' (Auto-generated)</label>
        <div class="input-group">
            <input type="text" class="form-control" value="<?= previewTag(\'' . $tagType . '\') ?>" readonly>
            <span class="input-group-text">
                <i class="bi bi-magic" title="Auto-generated"></i>
            </span>
        </div>
        <small class="text-muted">This number will be automatically assigned when you save the form.</small>
    </div>';
    
    $content = preg_replace($oldPattern, $newField, $content);
    
    file_put_contents($formFile, $content);
    return "Updated form HTML: $formFile";
}

// Main integration function
function integrateTagSystem() {
    $results = [];
    
    // Update form processing files
    $results[] = updateITRFormProcessing();
    $results[] = updatePARFormProcessing();
    $results[] = updateICSFormProcessing();
    $results[] = updateRISFormProcessing();
    
    // Update form HTML files
    $results[] = updateFormHTML('MAIN_ADMIN/itr_form.php', 'itr_no', 'itr_no');
    $results[] = updateFormHTML('MAIN_ADMIN/par_form.php', 'par_no', 'par_no');
    $results[] = updateFormHTML('MAIN_ADMIN/ics_form.php', 'ics_no', 'ics_no');
    $results[] = updateFormHTML('MAIN_ADMIN/ris_form.php', 'ris_no', 'ris_no');
    
    return $results;
}

// Test tag generation
function testTagGeneration() {
    $results = [];
    
    $tagTypes = ['red_tag', 'ics_no', 'itr_no', 'par_no', 'ris_no', 'inventory_tag'];
    
    foreach ($tagTypes as $tagType) {
        $preview = previewTag($tagType);
        $results[$tagType] = [
            'preview' => $preview,
            'status' => $preview ? 'OK' : 'ERROR'
        ];
    }
    
    return $results;
}

// Run integration if requested
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'integrate':
            $results = integrateTagSystem();
            echo "<h3>Integration Results:</h3>";
            foreach ($results as $result) {
                echo "<p>✓ $result</p>";
            }
            break;
            
        case 'test':
            $results = testTagGeneration();
            echo "<h3>Tag Generation Test:</h3>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Tag Type</th><th>Preview</th><th>Status</th></tr>";
            foreach ($results as $tagType => $data) {
                $status = $data['status'] == 'OK' ? '✓' : '✗';
                echo "<tr><td>$tagType</td><td>{$data['preview']}</td><td>$status {$data['status']}</td></tr>";
            }
            echo "</table>";
            break;
            
        case 'backup':
            // Create backup of original files
            $filesToBackup = [
                'MAIN_ADMIN/save_itr_items.php',
                'MAIN_ADMIN/save_par_items.php',
                'MAIN_ADMIN/save_ics_items.php',
                'MAIN_ADMIN/save_ris_items.php',
                'MAIN_ADMIN/itr_form.php',
                'MAIN_ADMIN/par_form.php',
                'MAIN_ADMIN/ics_form.php',
                'MAIN_ADMIN/ris_form.php'
            ];
            
            $backupDir = 'backups/tag_integration_' . date('Y-m-d_H-i-s');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            foreach ($filesToBackup as $file) {
                if (file_exists($file)) {
                    copy($file, $backupDir . '/' . basename($file));
                    echo "<p>✓ Backed up: $file</p>";
                }
            }
            echo "<p><strong>Backup completed in: $backupDir</strong></p>";
            break;
            
        default:
            echo "<p>Unknown action</p>";
    }
} else {
    // Show integration options
    echo "<h2>Tag Format System Integration</h2>";
    echo "<p>Choose an action:</p>";
    echo "<ul>";
    echo "<li><a href='?action=backup'>Create Backup of Original Files</a></li>";
    echo "<li><a href='?action=test'>Test Tag Generation</a></li>";
    echo "<li><a href='?action=integrate'>Run Integration (Modify Files)</a></li>";
    echo "</ul>";
    
    echo "<h3>Manual Integration Steps:</h3>";
    echo "<ol>";
    echo "<li>Run the database setup script: <code>create_tag_formats_table.sql</code></li>";
    echo "<li>Create backup of your files (click backup link above)</li>";
    echo "<li>Test tag generation (click test link above)</li>";
    echo "<li>Run integration to modify files (click integrate link above)</li>";
    echo "<li>Test each form manually to ensure proper functionality</li>";
    echo "</ol>";
    
    echo "<h3>Files that will be modified:</h3>";
    echo "<ul>";
    echo "<li>MAIN_ADMIN/save_itr_items.php - ITR processing</li>";
    echo "<li>MAIN_ADMIN/save_par_items.php - PAR processing</li>";
    echo "<li>MAIN_ADMIN/save_ics_items.php - ICS processing</li>";
    echo "<li>MAIN_ADMIN/save_ris_items.php - RIS processing</li>";
    echo "<li>MAIN_ADMIN/itr_form.php - ITR form HTML</li>";
    echo "<li>MAIN_ADMIN/par_form.php - PAR form HTML</li>";
    echo "<li>MAIN_ADMIN/ics_form.php - ICS form HTML</li>";
    echo "<li>MAIN_ADMIN/ris_form.php - RIS form HTML</li>";
    echo "</ul>";
}
?>
