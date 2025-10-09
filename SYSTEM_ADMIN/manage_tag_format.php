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

// Fetch categories for asset code functionality
$categories = [];
$res_cats = $conn->query("SELECT id, category_name, category_code FROM categories ORDER BY category_name");
if ($res_cats && $res_cats->num_rows > 0) {
    while ($cr = $res_cats->fetch_assoc()) {
        $categories[] = $cr;
    }
}

// Get code format for JavaScript
$code_format = '';
foreach ($tagFormats as $format) {
    if ($format['tag_type'] === 'asset_code') {
        $code_format = $format['format_template'];
        break;
    }
}

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
            // Special message for asset_code updates
            if ($tagType === 'asset_code') {
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Asset Code format updated successfully! The new format will be used in create_mr.php for new assets.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Tag format updated successfully!'
                ];
            }
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
        .asset-code-demo {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border-radius: 10px;
            padding: 1.5rem;
            color: white;
        }
        .code-output {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            font-size: 1.1em;
        }
        .format-example-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .format-example-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
                                        <?php if ($format['tag_type'] === 'asset_code'): ?>
                                        <div class="alert alert-info mt-2 mb-0">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <strong>Asset Code Format:</strong> Must include <code>{CODE}</code> placeholder for category codes. 
                                            This format is used in <strong>create_mr.php</strong> for asset creation.
                                        </div>
                                        <?php endif; ?>
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
                                                <option value="" <?= empty($format['date_format']) ? 'selected' : '' ?>>No Date (disabled)</option>
                                                <option value="YYYY" <?= $format['date_format'] == 'YYYY' ? 'selected' : '' ?>>YYYY (<?= date('Y') ?>)</option>
                                                <option value="YY" <?= $format['date_format'] == 'YY' ? 'selected' : '' ?>>YY (<?= date('y') ?>)</option>
                                                <option value="YYYYMM" <?= $format['date_format'] == 'YYYYMM' ? 'selected' : '' ?>>YYYYMM (<?= date('Ym') ?>)</option>
                                                <option value="YYYYMMDD" <?= $format['date_format'] == 'YYYYMMDD' ? 'selected' : '' ?>>YYYYMMDD (<?= date('Ymd') ?>)</option>
                                            </select>
                                            <small class="text-muted">Date will be added as prefix to the format</small>
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

            <!-- Asset Code Testing Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-upc-scan"></i> Asset Code Generator Testing
                                <span class="badge bg-light text-dark ms-2">Asset Code</span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Test the asset code generation functionality used in create_mr.php</p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="testCategory" class="form-label fw-semibold">Select Category</label>
                                    <select id="testCategory" class="form-select">
                                        <option value="">Choose a category...</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= (int)$cat['id'] ?>" data-code="<?= htmlspecialchars($cat['category_code'] ?? '') ?>">
                                                <?= htmlspecialchars($cat['category_name']) ?> 
                                                <?php if (!empty($cat['category_code'])): ?>
                                                    (<?= htmlspecialchars($cat['category_code']) ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="testAssetCode" class="form-label fw-semibold">Generated Asset Code</label>
                                    <div class="input-group">
                                        <input type="text" id="testAssetCode" class="form-control code-output" readonly 
                                               placeholder="Select a category to generate code">
                                        <button type="button" class="btn btn-outline-primary" onclick="copyToClipboard('testAssetCode')" title="Copy to clipboard">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success" onclick="generateNewCode()" title="Generate with new sequence">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">This shows how asset codes are generated in create_mr.php</small>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <strong>Current Asset Code Format:</strong> 
                                        <code><?= htmlspecialchars($code_format ?: 'YYYY-CODE-XXXX (default)') ?></code>
                                        <br>
                                        <small>
                                            • YYYY = Current Year<br>
                                            • CODE = Category Code<br>
                                            • XXXX = Sequential Number (0001, 0002, etc.)
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Format Examples -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="bi bi-lightbulb"></i> Format Examples
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="card border-primary format-example-card" style="cursor: pointer;" onclick="applyFormatExample('YYYY-CODE-{####}')">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">Standard Format</h6>
                                                    <code class="d-block mb-2">YYYY-CODE-{####}</code>
                                                    <small class="text-muted">2025-COMP-0001</small>
                                                    <div class="mt-2"><small class="text-primary">Click to test</small></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border-success format-example-card" style="cursor: pointer;" onclick="applyFormatExample('{YYYY}{CODE}{####}')">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">Compact Format</h6>
                                                    <code class="d-block mb-2">{YYYY}{CODE}{####}</code>
                                                    <small class="text-muted">2025COMP0001</small>
                                                    <div class="mt-2"><small class="text-success">Click to test</small></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card border-warning format-example-card" style="cursor: pointer;" onclick="applyFormatExample('{CODE}-{YYYY}-{###}')">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">Custom Format</h6>
                                                    <code class="d-block mb-2">{CODE}-{YYYY}-{###}</code>
                                                    <small class="text-muted">COMP-2025-001</small>
                                                    <div class="mt-2"><small class="text-warning">Click to test</small></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Date Format Examples -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h6 class="fw-semibold mb-3">
                                                <i class="bi bi-calendar"></i> Date Format Examples
                                            </h6>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card border-info format-example-card" style="cursor: pointer;" onclick="testTagFormat('{YYYY}-PAR-{####}')">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">With Year</h6>
                                                    <code class="d-block mb-2">{YYYY}-PAR-{####}</code>
                                                    <small class="text-muted"><?= date('Y') ?>-PAR-0001</small>
                                                    <div class="mt-2"><small class="text-info">Click to test</small></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card border-secondary format-example-card" style="cursor: pointer;" onclick="testTagFormat('ICS-{YYYYMM}-{###}')">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">Year-Month</h6>
                                                    <code class="d-block mb-2">ICS-{YYYYMM}-{###}</code>
                                                    <small class="text-muted">ICS-<?= date('Ym') ?>-001</small>
                                                    <div class="mt-2"><small class="text-secondary">Click to test</small></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card border-dark format-example-card" style="cursor: pointer;" onclick="testTagFormat('{YYYYMMDD}-RT-{#####}')">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">Full Date</h6>
                                                    <code class="d-block mb-2">{YYYYMMDD}-RT-{#####}</code>
                                                    <small class="text-muted"><?= date('Ymd') ?>-RT-00001</small>
                                                    <div class="mt-2"><small class="text-dark">Click to test</small></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card border-danger format-example-card" style="cursor: pointer;" onclick="testTagFormat('ITR{YY}{MM}-{####}')">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title">Short Date</h6>
                                                    <code class="d-block mb-2">ITR{YY}{MM}-{####}</code>
                                                    <small class="text-muted">ITR<?= date('ym') ?>-0001</small>
                                                    <div class="mt-2"><small class="text-danger">Click to test</small></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                        <li><code>{#}</code> - Single digit increment (1, 2, 3...)</li>
                        <li><code>{##}</code> - 2-digit increment (01, 02, 03...)</li>
                        <li><code>{###}</code> - 3-digit increment (001, 002, 003...)</li>
                        <li><code>{####}</code> - 4-digit increment (0001, 0002, 0003...)</li>
                        <li><code>{#####}</code> - 5-digit increment (00001, 00002...)</li>
                        <li><code>{######}</code> - 6-digit increment (000001, 000002...)</li>
                        <li><em>And so on... Use any number of # symbols for custom digit lengths!</em></li>
                    </ul>
                    
                    <h6 class="mt-3">Date Placeholders:</h6>
                    <ul>
                        <li><code>{YYYY}</code> - Full year (<?= date('Y') ?>)</li>
                        <li><code>{YY}</code> - Short year (<?= date('y') ?>)</li>
                        <li><code>{MM}</code> - Month (<?= date('m') ?>)</li>
                        <li><code>{DD}</code> - Day (<?= date('d') ?>)</li>
                        <li><code>{YYYYMM}</code> - Year-Month (<?= date('Ym') ?>)</li>
                        <li><code>{YYYYMMDD}</code> - Year-Month-Day (<?= date('Ymd') ?>)</li>
                    </ul>
                    
                    <h6 class="mt-3">Examples:</h6>
                    <ul>
                        <li><code>PAR-{####}</code> → PAR-0001, PAR-0002, PAR-0003...</li>
                        <li><code>{YYYY}-PAR-{####}</code> → <?= date('Y') ?>-PAR-0001, <?= date('Y') ?>-PAR-0002...</li>
                        <li><code>ICS-{YYYYMM}-{###}</code> → ICS-<?= date('Ym') ?>-001, ICS-<?= date('Ym') ?>-002...</li>
                        <li><code>RT-{#####}</code> → RT-00001, RT-00002, RT-00003...</li>
                        <li><code>SN-{##}-{##}</code> → SN-01-01, SN-01-02, SN-02-01...</li>
                        <li><code>ITEM-{#######}</code> → ITEM-0000001, ITEM-0000002...</li>
                    </ul>
                    
                    <h6 class="mt-3">Asset Code Placeholders:</h6>
                    <ul>
                        <li><code>{YYYY}</code> or <code>YYYY</code> - Current year (2025)</li>
                        <li><code>{CODE}</code> or <code>CODE</code> - Category code from categories table</li>
                        <li><code>{XXXX}</code> or <code>XXXX</code> - Sequential number (0001, 0002, etc.)</li>
                    </ul>
                    
                    <h6 class="mt-3">Asset Code Examples:</h6>
                    <ul>
                        <li><code>YYYY-CODE-{####}</code> → 2025-COMP-0001 (for Computer category)</li>
                        <li><code>{CODE}-{YYYY}-{###}</code> → FURN-2025-001 (for Furniture category)</li>
                        <li><code>{YYYY}{CODE}{####}</code> → 2025VEHI0001 (for Vehicle category)</li>
                    </ul>
                    
                    <h6 class="mt-3">Important Notes:</h6>
                    <ul>
                        <li>Changing the prefix will reset the counter to start from 1</li>
                        <li>Counters are tracked separately for each tag type</li>
                        <li>The system automatically generates tags when creating new records</li>
                        <li>Asset codes use category codes from the categories table</li>
                        <li>Simple format: PREFIX-{####} (no year separation)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Asset Code Generation (from create_mr.php)
        const codeFormatTemplate = <?= json_encode($code_format ?? '') ?>;
        
        // Date placeholder replacement function
        function replaceDatePlaceholders(template, dateFormat = '') {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            
            const replacements = {
                '{YYYY}': year.toString(),
                '{YY}': year.toString().slice(-2),
                '{MM}': month,
                '{DD}': day,
                '{MMDD}': month + day,
                '{YYYYMM}': year.toString() + month,
                '{YYYYMMDD}': year.toString() + month + day,
                'YYYY': year.toString(),
                'YY': year.toString().slice(-2),
                'MM': month,
                'DD': day,
                'MMDD': month + day,
                'YYYYMM': year.toString() + month,
                'YYYYMMDD': year.toString() + month + day
            };
            
            let result = template;
            for (const [placeholder, value] of Object.entries(replacements)) {
                result = result.replace(new RegExp(placeholder, 'g'), value);
            }
            
            return result;
        }
        
        function buildCodeFromCategory(catCode) {
            if (!catCode) return '';
            const year = new Date().getFullYear().toString();
            const seq = '0001'; // Default sequence placeholder

            let template = (codeFormatTemplate || '').trim();
            
            // Ensure {CODE} placeholder is always present in template
            if (template && !template.includes('{CODE}') && !template.includes('CODE')) {
                // If template doesn't have CODE placeholder, add it
                template = template.replace(/(\{####\}|\{###\}|\{#####\})/, '{CODE}-$1');
            }
            
            // If no template, use default with CODE
            if (!template) {
                template = '{YYYY}-{CODE}-{####}';
            }

            let output = template;
            
            // Replace date placeholders
            const now = new Date();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            
            output = output.replace(/\{YYYY\}|YYYY/g, year);
            output = output.replace(/\{YY\}|YY/g, year.slice(-2));
            output = output.replace(/\{MM\}|MM/g, month);
            output = output.replace(/\{DD\}|DD/g, day);
            output = output.replace(/\{YYYYMM\}|YYYYMM/g, year + month);
            output = output.replace(/\{YYYYMMDD\}|YYYYMMDD/g, year + month + day);
            
            // Replace category code placeholder
            output = output.replace(/\{CODE\}|CODE/g, catCode);
            
            // Enhanced flexible digit replacement - supports any number of # symbols
            output = output.replace(/\{(#+)\}/g, function(match, hashes) {
                const digitCount = hashes.length;
                return '0'.repeat(Math.max(1, digitCount - 1)) + '1';
            });
            
            // Legacy support for XXXX format
            output = output.replace(/\{XXXX\}|XXXX/g, seq);
            
            return output;
        }
        
        // Asset Code Testing functionality
        document.addEventListener('DOMContentLoaded', function() {
            const testCategorySelect = document.getElementById('testCategory');
            const testAssetCodeInput = document.getElementById('testAssetCode');
            
            if (testCategorySelect && testAssetCodeInput) {
                testCategorySelect.addEventListener('change', function() {
                    const selected = this.options[this.selectedIndex];
                    const catCode = selected ? (selected.getAttribute('data-code') || '') : '';
                    if (catCode) {
                        testAssetCodeInput.value = buildCodeFromCategory(catCode);
                    } else {
                        testAssetCodeInput.value = '';
                    }
                });
            }
        });
        
        // Update asset code testing when format changes
        function updateAssetCodeTesting() {
            const assetCodeForm = document.querySelector('form input[name="tag_type"][value="asset_code"]');
            if (assetCodeForm) {
                const form = assetCodeForm.closest('form');
                const templateInput = form.querySelector('input[name="format_template"]');
                if (templateInput) {
                    // Update the global format template
                    window.codeFormatTemplate = templateInput.value;
                    
                    // Update current format display in testing section
                    const currentFormatDisplay = document.querySelector('.alert-info code');
                    if (currentFormatDisplay) {
                        currentFormatDisplay.textContent = templateInput.value || '{YYYY}-{CODE}-{####} (default)';
                    }
                    
                    // Regenerate test code if category is selected
                    const testCategorySelect = document.getElementById('testCategory');
                    const testAssetCodeInput = document.getElementById('testAssetCode');
                    if (testCategorySelect && testAssetCodeInput && testCategorySelect.value) {
                        const selected = testCategorySelect.options[testCategorySelect.selectedIndex];
                        const catCode = selected ? (selected.getAttribute('data-code') || '') : '';
                        if (catCode) {
                            testAssetCodeInput.value = buildCodeFromCategory(catCode);
                        }
                    }
                }
            }
        }
        
        // Apply format example to test
        function applyFormatExample(format) {
            // Update the global format template
            window.codeFormatTemplate = format;
            
            // Trigger code generation if category is selected
            const testCategorySelect = document.getElementById('testCategory');
            const testAssetCodeInput = document.getElementById('testAssetCode');
            
            if (testCategorySelect && testAssetCodeInput && testCategorySelect.value) {
                const selected = testCategorySelect.options[testCategorySelect.selectedIndex];
                const catCode = selected ? (selected.getAttribute('data-code') || '') : '';
                if (catCode) {
                    // Use the new format
                    const year = new Date().getFullYear().toString();
                    const seq = '0001';
                    let output = '';
                    
                    const hasBarePlaceholders = format.includes('YYYY') || format.includes('CODE') || format.includes('XXXX');
                    const hasCurlyPlaceholders = format.includes('{YYYY}') || format.includes('{CODE}') || format.includes('{XXXX}');
                    if (hasBarePlaceholders || hasCurlyPlaceholders) {
                        output = format
                            .replace(/\{YYYY\}|YYYY/g, year)
                            .replace(/\{CODE\}|CODE/g, catCode)
                            .replace(/\{XXXX\}|XXXX/g, seq)
                            .replace(/\{###\}/g, '001')
                            .replace(/\{####\}/g, '0001')
                            .replace(/\{#####\}/g, '00001');
                    } else {
                        output = `${year}-${catCode}-${seq}`;
                    }
                    
                    testAssetCodeInput.value = output;
                    
                    // Show animation
                    testAssetCodeInput.style.background = '#fff3cd';
                    setTimeout(() => {
                        testAssetCodeInput.style.background = '';
                    }, 1500);
                }
            }
            
            // Show notification
            const notification = document.createElement('div');
            notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                <strong>Format Applied!</strong> Now using: <code>${format}</code>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(notification);
            
            // Auto-remove notification
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 4000);
        }
        
        // Test tag format with date placeholders
        function testTagFormat(template) {
            // Replace date placeholders
            let preview = replaceDatePlaceholders(template);
            
            // Replace increment placeholders
            preview = preview.replace(/\{####\}/g, '0001');
            preview = preview.replace(/\{###\}/g, '001');
            preview = preview.replace(/\{#####\}/g, '00001');
            
            // Show preview in a modal
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title"><i class="bi bi-calendar-check"></i> Date Format Preview</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <h4>Template:</h4>
                            <div class="alert alert-secondary">
                                <code style="font-size: 1.2em;">${template}</code>
                            </div>
                            <h4>Generated Result:</h4>
                            <div class="alert alert-info">
                                <code style="font-size: 1.5em; font-weight: bold; color: #0d6efd;">${preview}</code>
                            </div>
                            <small class="text-muted">This shows how the tag would look with current date</small>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="copyToClipboard('temp-preview')" data-bs-dismiss="modal">
                                <i class="bi bi-clipboard"></i> Copy Template
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            // Add hidden input for copying
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.id = 'temp-preview';
            hiddenInput.value = template;
            modal.appendChild(hiddenInput);
            
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            // Remove modal after it's hidden
            modal.addEventListener('hidden.bs.modal', () => {
                document.body.removeChild(modal);
            });
        }
        
        // Generate new code with different sequence
        function generateNewCode() {
            const testCategorySelect = document.getElementById('testCategory');
            const testAssetCodeInput = document.getElementById('testAssetCode');
            
            if (testCategorySelect && testAssetCodeInput) {
                const selected = testCategorySelect.options[testCategorySelect.selectedIndex];
                const catCode = selected ? (selected.getAttribute('data-code') || '') : '';
                if (catCode) {
                    // Generate with random sequence for demonstration
                    const randomSeq = Math.floor(Math.random() * 9999) + 1;
                    const paddedSeq = randomSeq.toString().padStart(4, '0');
                    
                    let template = (codeFormatTemplate || '').trim();
                    const year = new Date().getFullYear().toString();
                    let output = '';
                    
                    const hasBarePlaceholders = template.includes('YYYY') || template.includes('CODE') || template.includes('XXXX');
                    const hasCurlyPlaceholders = template.includes('{YYYY}') || template.includes('{CODE}') || template.includes('{XXXX}');
                    if (hasBarePlaceholders || hasCurlyPlaceholders) {
                        output = template
                            .replace(/\{YYYY\}|YYYY/g, year)
                            .replace(/\{CODE\}|CODE/g, catCode)
                            .replace(/\{XXXX\}|XXXX/g, paddedSeq);
                    } else if (template.length > 0) {
                        output = `${year}-${template}-${catCode}-${paddedSeq}`;
                    } else {
                        output = `${year}-${catCode}-${paddedSeq}`;
                    }
                    
                    testAssetCodeInput.value = output;
                    
                    // Show animation
                    testAssetCodeInput.style.background = '#d4edda';
                    setTimeout(() => {
                        testAssetCodeInput.style.background = '';
                    }, 1000);
                } else {
                    alert('Please select a category first');
                }
            }
        }
        
        // Copy to clipboard function
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            if (element && element.value) {
                navigator.clipboard.writeText(element.value).then(function() {
                    // Show success feedback
                    const button = element.nextElementSibling;
                    const originalHTML = button.innerHTML;
                    button.innerHTML = '<i class="bi bi-check"></i>';
                    button.classList.add('btn-success');
                    button.classList.remove('btn-outline-secondary');
                    
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.classList.remove('btn-success');
                        button.classList.add('btn-outline-secondary');
                    }, 2000);
                });
            }
        }

        // Enhanced preview format functionality with flexible digit support
        function previewFormat(templateOrForm) {
            // Support calling with either a template string or a form element (this.form)
            let template = '';
            if (typeof templateOrForm === 'string') {
                template = templateOrForm;
            } else if (templateOrForm && typeof templateOrForm.querySelector === 'function') {
                const ti = templateOrForm.querySelector('input[name="format_template"]');
                template = ti ? String(ti.value || '') : '';
                // If template is empty, derive from form fields like updateTemplate()
                if (!template) {
                    const prefixInput = templateOrForm.querySelector('input[name="prefix"]');
                    const digitsSelect = templateOrForm.querySelector('select[name="increment_digits"]');
                    const dateSelect = templateOrForm.querySelector('select[name="date_format"]');
                    const suffixInput = templateOrForm.querySelector('input[name="suffix"]');
                    const tagTypeInput = templateOrForm.querySelector('input[name="tag_type"]');
                    const isAssetCode = tagTypeInput && tagTypeInput.value === 'asset_code';

                    const prefix = prefixInput ? prefixInput.value : '';
                    const digits = digitsSelect ? '#'.repeat(parseInt(digitsSelect.value || '4')) : '####';
                    const dateFormat = dateSelect ? dateSelect.value : '';
                    const suffix = suffixInput ? suffixInput.value : '';

                    let tmp = '';
                    if (dateFormat) {
                        if (dateFormat === 'YYYY') tmp += '{YYYY}';
                        else if (dateFormat === 'YY') tmp += '{YY}';
                        else if (dateFormat === 'YYYYMM') tmp += '{YYYYMM}';
                        else if (dateFormat === 'YYYYMMDD') tmp += '{YYYYMMDD}';
                        tmp += '-';
                    }
                    if (isAssetCode) tmp += '{CODE}-';
                    if (prefix) {
                        tmp += prefix;
                        if (!tmp.endsWith('-')) tmp += '-';
                    }
                    if (digits) tmp += '{' + digits + '}';
                    if (suffix) tmp += suffix;
                    template = tmp.replace(/-+/g, '-');
                }
            } else {
                template = '';
            }

            // Replace placeholders with sample values
            let preview = template;
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            
            // Replace date placeholders
            preview = preview.replace(/\{YYYY\}|YYYY/g, year);
            preview = preview.replace(/\{YY\}|YY/g, year.toString().slice(-2));
            preview = preview.replace(/\{MM\}|MM/g, month);
            preview = preview.replace(/\{DD\}|DD/g, day);
            preview = preview.replace(/\{YYYYMM\}|YYYYMM/g, year + month);
            preview = preview.replace(/\{YYYYMMDD\}|YYYYMMDD/g, year + month + day);
            
            // Enhanced flexible digit replacement - supports any number of # symbols
            preview = preview.replace(/\{(#+)\}/g, function(match, hashes) {
                const digitCount = hashes.length;
                return '0'.repeat(Math.max(1, digitCount - 1)) + '1';
            });
            
            // Legacy support for specific patterns
            preview = preview.replace(/\{####\}/g, '0001');
            preview = preview.replace(/\{###\}/g, '001');
            preview = preview.replace(/\{#####\}/g, '00001');
            preview = preview.replace(/\{######\}/g, '000001');
            preview = preview.replace(/\{##\}/g, '01');
            preview = preview.replace(/\{#\}/g, '1');
            
            // Replace asset code placeholder (both with and without braces)
            preview = preview.replace(/\{CODE\}|CODE/g, 'COMP');
            
            // Also render inline preview near the form (visible without modal)
            if (templateOrForm && typeof templateOrForm.querySelector === 'function') {
                let wrap = templateOrForm.querySelector('.inline-preview-wrap');
                if (!wrap) {
                    wrap = document.createElement('div');
                    wrap.className = 'inline-preview-wrap mt-3';
                    wrap.innerHTML = `
                        <div class="small text-muted mb-1"><i class="bi bi-eye"></i> Preview Result</div>
                        <div class="format-preview" style="font-size:1.1em;"><code class="d-block" style="font-size:1.2em; font-weight:bold; color:#0d6efd;"></code></div>
                    `;
                    // append at end of form
                    templateOrForm.appendChild(wrap);
                }
                const codeEl = wrap.querySelector('code');
                if (codeEl) codeEl.textContent = preview;
            }

            // Show preview modal
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="bi bi-eye"></i> Format Preview</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <h4>Preview Result:</h4>
                            <div class="alert alert-info">
                                <code style="font-size: 1.5em; font-weight: bold;">${preview}</code>
                            </div>
                            <small class="text-muted">This shows how the next generated tag will look</small>
                            <hr>
                            <div class="text-start">
                                <h6>Supported Patterns:</h6>
                                <ul class="list-unstyled small">
                                    <li><code>{#}</code> → Single digit (1, 2, 3...)</li>
                                    <li><code>{##}</code> → Two digits (01, 02, 03...)</li>
                                    <li><code>{###}</code> → Three digits (001, 002, 003...)</li>
                                    <li><code>{####}</code> → Four digits (0001, 0002, 0003...)</li>
                                    <li><code>{#####}</code> → Five digits (00001, 00002...)</li>
                                    <li><code>{######}</code> → Six digits (000001, 000002...)</li>
                                    <li><em>And so on... Use any number of # symbols!</em></li>
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            // Remove modal after it's hidden
            modal.addEventListener('hidden.bs.modal', () => {
                document.body.removeChild(modal);
            });
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
                const dateFormat = dateSelect.value;
                const suffix = suffixInput.value;
                
                // Check if this is an asset_code format
                const tagTypeInput = form.querySelector('input[name="tag_type"]');
                const isAssetCode = tagTypeInput && tagTypeInput.value === 'asset_code';
                
                let template = '';
                
                // Build template based on components
                if (dateFormat && dateFormat !== '') {
                    // Add date format to template
                    if (dateFormat === 'YYYY') {
                        template += '{YYYY}';
                    } else if (dateFormat === 'YY') {
                        template += '{YY}';
                    } else if (dateFormat === 'YYYYMM') {
                        template += '{YYYYMM}';
                    } else if (dateFormat === 'YYYYMMDD') {
                        template += '{YYYYMMDD}';
                    }
                    
                    // Add separator after date
                    template += '-';
                }
                
                // For asset_code, always include {CODE} placeholder
                if (isAssetCode) {
                    template += '{CODE}-';
                }
                
                // Add prefix
                if (prefix) {
                    template += prefix;
                    if (!template.endsWith('-')) {
                        template += '-';
                    }
                }
                
                // Add increment digits - support flexible digit patterns
                if (digits) {
                    template += '{' + digits + '}';
                }
                
                // Add suffix
                if (suffix) {
                    template += suffix;
                }
                
                // Clean up multiple dashes
                template = template.replace(/-+/g, '-');
                
                templateInput.value = template;
            }
            
            [prefixInput, digitsSelect, dateSelect, suffixInput].forEach(input => {
                input.addEventListener('change', updateTemplate);
                input.addEventListener('input', updateTemplate);
            });
            
            // Add listener for template input changes (for asset_code)
            if (templateInput) {
                templateInput.addEventListener('input', function() {
                    // Update asset code testing if this is asset_code format
                    const tagTypeInput = form.querySelector('input[name="tag_type"]');
                    if (tagTypeInput && tagTypeInput.value === 'asset_code') {
                        updateAssetCodeTesting();
                    }
                });
            }
        });
    </script>
</body>
</html>
