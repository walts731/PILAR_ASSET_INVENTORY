<?php
require_once '../connect.php';
require_once '../includes/tag_format_helper.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$tagHelper = new TagFormatHelper($conn);
$tagFormats = $tagHelper->getAllTagFormats();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_format') {
        $tagType = $_POST['tag_type'];
        $data = [
            'format_template' => $_POST['format_template'],
            'prefix' => $_POST['prefix'],
            'suffix' => $_POST['suffix'] ?? '',
            'increment_digits' => (int)$_POST['increment_digits'],
            'date_format' => $_POST['date_format']
        ];
        
        if ($tagHelper->updateTagFormat($tagType, $data)) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Tag format updated successfully!'
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Failed to update tag format.'
            ];
        }
        
        header("Location: manage_tag_format.php");
        exit();
    }
}

// Get flash message
$flash = null;
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tag Format - PILAR Asset Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .format-preview {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 0.5rem;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #0d6efd;
        }
        .tag-type-card {
            transition: transform 0.2s ease-in-out;
        }
        .tag-type-card:hover {
            transform: translateY(-2px);
        }
        .placeholder-help {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .current-format {
            background: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="container-fluid py-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0"><i class="bi bi-tags"></i> Manage Tag Format</h4>
                    <p class="text-muted mb-0">Configure automatic tag generation for all forms</p>
                </div>
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#helpModal">
                    <i class="bi bi-question-circle"></i> Help
                </button>
            </div>

            <!-- Flash Messages -->
            <?php if ($flash): ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Tag Format Cards -->
            <div class="row">
                <?php foreach ($tagFormats as $format): ?>
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="card tag-type-card shadow-sm h-100">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-tag"></i> 
                                    <?= strtoupper(str_replace('_', ' ', $format['tag_type'])) ?>
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Current Format Display -->
                                <div class="current-format">
                                    <small class="text-muted d-block">Current Format:</small>
                                    <div class="format-preview"><?= htmlspecialchars($format['format_template']) ?></div>
                                    <small class="text-muted d-block mt-1">
                                        Next: <strong><?= htmlspecialchars($tagHelper->previewNextTag($format['tag_type'])) ?></strong>
                                    </small>
                                </div>

                                <!-- Edit Form -->
                                <form method="POST" class="tag-format-form">
                                    <input type="hidden" name="action" value="update_format">
                                    <input type="hidden" name="tag_type" value="<?= htmlspecialchars($format['tag_type']) ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Format Template</label>
                                        <input type="text" name="format_template" class="form-control" 
                                               value="<?= htmlspecialchars($format['format_template']) ?>" 
                                               placeholder="e.g., PAR-{####}" required>
                                        <div class="placeholder-help mt-1">
                                            Use: {####} = Auto-increment (recommended format: PREFIX-{####})
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-8">
                                            <label class="form-label">Prefix</label>
                                            <input type="text" name="prefix" class="form-control" 
                                                   value="<?= htmlspecialchars($format['prefix']) ?>" 
                                                   placeholder="e.g., PAR-">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Digits</label>
                                            <select name="increment_digits" class="form-select">
                                                <option value="3" <?= $format['increment_digits'] == 3 ? 'selected' : '' ?>>3 (001)</option>
                                                <option value="4" <?= $format['increment_digits'] == 4 ? 'selected' : '' ?>>4 (0001)</option>
                                                <option value="5" <?= $format['increment_digits'] == 5 ? 'selected' : '' ?>>5 (00001)</option>
                                                <option value="6" <?= $format['increment_digits'] == 6 ? 'selected' : '' ?>>6 (000001)</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Date Format</label>
                                            <select name="date_format" class="form-select">
                                                <option value="YYYY" <?= $format['date_format'] == 'YYYY' ? 'selected' : '' ?>>YYYY (2025)</option>
                                                <option value="YY" <?= $format['date_format'] == 'YY' ? 'selected' : '' ?>>YY (25)</option>
                                                <option value="YYYYMM" <?= $format['date_format'] == 'YYYYMM' ? 'selected' : '' ?>>YYYYMM (202501)</option>
                                                <option value="YYYYMMDD" <?= $format['date_format'] == 'YYYYMMDD' ? 'selected' : '' ?>>YYYYMMDD (20250103)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Suffix (Optional)</label>
                                            <input type="text" name="suffix" class="form-control" 
                                                   value="<?= htmlspecialchars($format['suffix']) ?>" 
                                                   placeholder="e.g., -FINAL">
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 pt-3 border-top">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-save"></i> Update Format
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm ms-2" 
                                                onclick="previewFormat(this.form)">
                                            <i class="bi bi-eye"></i> Preview
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Statistics -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Tag Usage Statistics</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tag Type</th>
                                            <th>Current Count</th>
                                            <th>Next Number</th>
                                            <th>Last Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($tagFormats as $format):
                                            $stmt = $conn->prepare("SELECT current_count, updated_at FROM tag_counters WHERE tag_type = ? AND year_period = 'global' ORDER BY updated_at DESC LIMIT 1");
                                            $stmt->bind_param("s", $format['tag_type']);
                                            $stmt->execute();
                                            $counter = $stmt->get_result()->fetch_assoc();
                                            $stmt->close();
                                        ?>
                                            <tr>
                                                <td><strong><?= strtoupper(str_replace('_', ' ', $format['tag_type'])) ?></strong></td>
                                                <td><?= $counter ? $counter['current_count'] : 0 ?></td>
                                                <td><code><?= htmlspecialchars($tagHelper->previewNextTag($format['tag_type'])) ?></code></td>
                                                <td><?= $counter && $counter['updated_at'] ? date('M d, Y H:i', strtotime($counter['updated_at'])) : 'Never' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-question-circle"></i> Tag Format Help</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>Available Placeholders:</h6>
                    <ul>
                        <li><code>{####}</code> - Auto-increment number (padded with zeros)</li>
                        <li><code>{###}</code> - 3-digit increment (001, 002, 003...)</li>
                        <li><code>{#####}</code> - 5-digit increment (00001, 00002...)</li>
                    </ul>
                    
                    <h6 class="mt-3">Examples:</h6>
                    <ul>
                        <li><code>PAR-{####}</code> → PAR-0001, PAR-0002, PAR-0003...</li>
                        <li><code>ICS-{###}</code> → ICS-001, ICS-002, ICS-003...</li>
                        <li><code>RT-{#####}</code> → RT-00001, RT-00002, RT-00003...</li>
                    </ul>
                    
                    <h6 class="mt-3">Important Notes:</h6>
                    <ul>
                        <li>Changing the prefix will reset the counter to start from 1</li>
                        <li>Counters are tracked separately for each tag type</li>
                        <li>The system automatically generates tags when creating new records</li>
                        <li>Simple format: PREFIX-{####} (no year separation)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewFormat(form) {
            const template = form.format_template.value;
            const digits = parseInt(form.increment_digits.value);
            
            // Simple preview generation (client-side)
            let preview = template;
            
            // Replace increment placeholder
            const incrementPattern = new RegExp('\\{#{' + digits + '}\\}', 'g');
            preview = preview.replace(incrementPattern, '1'.padStart(digits, '0'));
            
            // Show preview
            alert('Preview: ' + preview);
        }

        // Auto-update format template when other fields change
        document.querySelectorAll('.tag-format-form').forEach(form => {
            const templateInput = form.querySelector('input[name="format_template"]');
            const prefixInput = form.querySelector('input[name="prefix"]');
            const digitsSelect = form.querySelector('select[name="increment_digits"]');
            const dateSelect = form.querySelector('select[name="date_format"]');
            const suffixInput = form.querySelector('input[name="suffix"]');
            
            function updateTemplate() {
                const prefix = prefixInput.value;
                const digits = '#'.repeat(parseInt(digitsSelect.value));
                const suffix = suffixInput.value;
                
                let template = prefix + '{' + digits + '}' + suffix;
                templateInput.value = template;
            }
            
            [prefixInput, digitsSelect, dateSelect, suffixInput].forEach(input => {
                input.addEventListener('change', updateTemplate);
                input.addEventListener('input', updateTemplate);
            });
        });
    </script>
</body>
</html>
