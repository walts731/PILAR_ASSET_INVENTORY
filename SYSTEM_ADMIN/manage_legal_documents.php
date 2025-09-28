<?php
session_start();
require_once "../connect.php";

// Check if user is logged in and is super admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
    header("Location: ../index.php");
    exit();
}

// Fetch current legal documents
$privacy_policy = null;
$terms_of_service = null;

$stmt = $conn->prepare("SELECT * FROM legal_documents WHERE document_type = ? AND is_active = 1 ORDER BY created_at DESC LIMIT 1");

// Get Privacy Policy
$stmt->bind_param("s", $type);
$type = 'privacy_policy';
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $privacy_policy = $result->fetch_assoc();
}

// Get Terms of Service
$type = 'terms_of_service';
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $terms_of_service = $result->fetch_assoc();
}

$stmt->close();

// Fetch system settings for branding
$system = [
    'logo' => 'default-logo.png',
    'system_title' => 'Inventory System'
];
$result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
if ($result && $result->num_rows > 0) {
    $system = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Legal Documents - <?= htmlspecialchars($system['system_title']) ?></title>
    
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/dashboard.css">
    
    <!-- Quill Rich Text Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    
    <style>
        .document-card {
            border: 1px solid #e0e6ed;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: #fff;
        }
        .document-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .document-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 1.5rem;
        }
        .document-status {
            font-size: 0.875rem;
            opacity: 0.9;
        }
        .editor-container {
            min-height: 400px;
        }
        .version-badge {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .save-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .save-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        .preview-btn {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .preview-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <?php include 'includes/topbar.php'; ?>
        <div class="content">
            <div class="container-fluid">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-file-text me-2"></i>Manage Legal Documents
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <div id="alertContainer"></div>

                <!-- Privacy Policy Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="document-card">
                            <div class="document-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-1">
                                            <i class="bi bi-shield-lock me-2"></i>Privacy Policy
                                        </h4>
                                        <div class="document-status">
                                            Last updated: <?= $privacy_policy ? date('F j, Y g:i A', strtotime($privacy_policy['last_updated'])) : 'Never' ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="version-badge">
                                            Version <?= $privacy_policy ? htmlspecialchars($privacy_policy['version']) : '1.0' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <form id="privacyForm">
                                    <input type="hidden" name="document_type" value="privacy_policy">
                                    <input type="hidden" name="current_version" value="<?= $privacy_policy ? htmlspecialchars($privacy_policy['version']) : '1.0' ?>">
                                    
                                    <div class="mb-3">
                                        <label for="privacy_title" class="form-label fw-bold">Document Title</label>
                                        <input type="text" class="form-control" id="privacy_title" name="title" 
                                               value="<?= $privacy_policy ? htmlspecialchars($privacy_policy['title']) : 'Privacy Policy' ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="privacy_version" class="form-label fw-bold">Version</label>
                                        <input type="text" class="form-control" id="privacy_version" name="version" 
                                               value="<?= $privacy_policy ? htmlspecialchars($privacy_policy['version']) : '1.0' ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="privacy_effective_date" class="form-label fw-bold">Effective Date</label>
                                        <input type="date" class="form-control" id="privacy_effective_date" name="effective_date" 
                                               value="<?= $privacy_policy ? date('Y-m-d', strtotime($privacy_policy['effective_date'])) : date('Y-m-d') ?>" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="privacy_content" class="form-label fw-bold">Content</label>
                                        <div class="editor-container">
                                            <div id="privacy_editor" style="height: 400px;"></div>
                                            <textarea id="privacy_content" name="content" style="display: none;"><?= $privacy_policy ? htmlspecialchars($privacy_policy['content']) : '' ?></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-success save-btn">
                                            <i class="bi bi-check-lg me-2"></i>Save Privacy Policy
                                        </button>
                                        <button type="button" class="btn btn-info preview-btn" onclick="previewDocument('privacy_policy')">
                                            <i class="bi bi-eye me-2"></i>Preview
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms of Service Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="document-card">
                            <div class="document-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-1">
                                            <i class="bi bi-file-earmark-text me-2"></i>Terms of Service
                                        </h4>
                                        <div class="document-status">
                                            Last updated: <?= $terms_of_service ? date('F j, Y g:i A', strtotime($terms_of_service['last_updated'])) : 'Never' ?>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="version-badge">
                                            Version <?= $terms_of_service ? htmlspecialchars($terms_of_service['version']) : '1.0' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <form id="termsForm">
                                    <input type="hidden" name="document_type" value="terms_of_service">
                                    <input type="hidden" name="current_version" value="<?= $terms_of_service ? htmlspecialchars($terms_of_service['version']) : '1.0' ?>">
                                    
                                    <div class="mb-3">
                                        <label for="terms_title" class="form-label fw-bold">Document Title</label>
                                        <input type="text" class="form-control" id="terms_title" name="title" 
                                               value="<?= $terms_of_service ? htmlspecialchars($terms_of_service['title']) : 'Terms of Service' ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="terms_version" class="form-label fw-bold">Version</label>
                                        <input type="text" class="form-control" id="terms_version" name="version" 
                                               value="<?= $terms_of_service ? htmlspecialchars($terms_of_service['version']) : '1.0' ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="terms_effective_date" class="form-label fw-bold">Effective Date</label>
                                        <input type="date" class="form-control" id="terms_effective_date" name="effective_date" 
                                               value="<?= $terms_of_service ? date('Y-m-d', strtotime($terms_of_service['effective_date'])) : date('Y-m-d') ?>" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="terms_content" class="form-label fw-bold">Content</label>
                                        <div class="editor-container">
                                            <div id="terms_editor" style="height: 400px;"></div>
                                            <textarea id="terms_content" name="content" style="display: none;"><?= $terms_of_service ? htmlspecialchars($terms_of_service['content']) : '' ?></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-success save-btn">
                                            <i class="bi bi-check-lg me-2"></i>Save Terms of Service
                                        </button>
                                        <button type="button" class="btn btn-info preview-btn" onclick="previewDocument('terms_of_service')">
                                            <i class="bi bi-eye me-2"></i>Preview
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">
                        <i class="bi bi-eye me-2"></i>Document Preview
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="previewContent">
                    <!-- Preview content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize Quill editors
        let privacyQuill, termsQuill;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Privacy Policy Editor
            privacyQuill = new Quill('#privacy_editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        [{ 'align': [] }],
                        ['link', 'blockquote', 'code-block'],
                        ['clean']
                    ]
                }
            });
            
            // Terms of Service Editor
            termsQuill = new Quill('#terms_editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        [{ 'align': [] }],
                        ['link', 'blockquote', 'code-block'],
                        ['clean']
                    ]
                }
            });
            
            // Load existing content
            const privacyContent = document.getElementById('privacy_content').value;
            if (privacyContent) {
                privacyQuill.root.innerHTML = privacyContent;
            }
            
            const termsContent = document.getElementById('terms_content').value;
            if (termsContent) {
                termsQuill.root.innerHTML = termsContent;
            }
        });

        // Handle form submissions
        document.getElementById('privacyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveDocument(this, 'Privacy Policy');
        });

        document.getElementById('termsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveDocument(this, 'Terms of Service');
        });

        function saveDocument(form, documentName) {
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
            submitBtn.disabled = true;
            
            // Get content from Quill
            const contentField = form.querySelector('textarea[name="content"]');
            const editorId = contentField.id;
            let quillContent = '';
            
            if (editorId === 'privacy_content') {
                quillContent = privacyQuill.root.innerHTML;
            } else if (editorId === 'terms_content') {
                quillContent = termsQuill.root.innerHTML;
            }
            
            formData.set('content', quillContent);
            
            fetch('save_legal_document.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(`${documentName} saved successfully!`, 'success');
                    // Reload page after 2 seconds to show updated information
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert(data.message || `Failed to save ${documentName}`, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert(`An error occurred while saving ${documentName}`, 'danger');
            })
            .finally(() => {
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }

        function previewDocument(documentType) {
            const form = documentType === 'privacy_policy' ? 
                document.getElementById('privacyForm') : 
                document.getElementById('termsForm');
            
            const title = form.querySelector('input[name="title"]').value;
            const version = form.querySelector('input[name="version"]').value;
            const effectiveDate = form.querySelector('input[name="effective_date"]').value;
            const editorId = form.querySelector('textarea[name="content"]').id;
            let content = '';
            
            if (editorId === 'privacy_content') {
                content = privacyQuill.root.innerHTML;
            } else if (editorId === 'terms_content') {
                content = termsQuill.root.innerHTML;
            }
            
            const previewContent = `
                <div class="document-preview">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">${title}</h2>
                        <p class="text-muted">
                            <strong>Version:</strong> ${version}<br>
                            <strong>Effective Date:</strong> ${new Date(effectiveDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
                        </p>
                    </div>
                    <div class="content-body">
                        ${content}
                    </div>
                </div>
            `;
            
            document.getElementById('previewContent').innerHTML = previewContent;
            document.getElementById('previewModalLabel').innerHTML = `<i class="bi bi-eye me-2"></i>${title} Preview`;
            
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
        }

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            alertContainer.appendChild(alert);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>
