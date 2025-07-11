<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_SESSION['office_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT office_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($office_id);
    if ($stmt->fetch()) {
        $_SESSION['office_id'] = $office_id;
    }
    $stmt->close();
}

$user_name = '';
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($fullname);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Report Template Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css" />
    <link rel="stylesheet" href="css/templates.css" />
</head>

<body>
    <?php include 'includes/sidebar.php' ?>
    <div class="main">
        <?php include 'includes/topbar.php' ?>
        <?php include 'alerts/templates_alert.php' ?>
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h5 class="mb-0">Create Report Template</h5>

                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <!-- Text Formatting Buttons -->
                                    <div class="toolbar btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleCommand(this, 'bold')">B</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleCommand(this, 'italic')">I</button>
                                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="toggleCommand(this, 'underline')"><u>U</u></button>
                                    </div>

                                    <!-- Font Size Dropdown -->
                                    <select class="form-select form-select-sm" style="width: auto;" onchange="setFontSize(this.value)">
                                        <option value="">Font Size</option>
                                        <option value="12px">12px</option>
                                        <option value="14px">14px</option>
                                        <option value="16px">16px</option>
                                        <option value="18px">18px</option>
                                        <option value="24px">24px</option>
                                        <option value="32px">32px</option>
                                    </select>

                                    <!-- Font Family Dropdown -->
                                    <select class="form-select form-select-sm" style="width: auto;" onchange="setFontFamily(this.value)">
                                        <option value="">Font Family</option>
                                        <option value="Arial">Arial</option>
                                        <option value="Georgia">Georgia</option>
                                        <option value="Tahoma">Tahoma</option>
                                        <option value="Times New Roman">Times New Roman</option>
                                        <option value="Verdana">Verdana</option>
                                    </select>

                                    <!-- Special Inserts -->
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-dark" onclick="insertSpecial('[blank]')">Blank</button>
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="insertSpecial('$dynamic_year')">Year</button>
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="insertSpecial('$dynamic_month')">Month</button>
                                    </div>

                                    <!-- Trigger Upload Modal -->
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadFormatModal">
                                        <i class="bi bi-upload"></i> Upload Template
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="templateForm" method="POST" enctype="multipart/form-data" action="save_template.php">
                                <!-- Hidden inputs for fonts and sizes -->
                                <input type="hidden" name="header_font_family" id="header_font_family">
                                <input type="hidden" name="header_font_size" id="header_font_size">

                                <input type="hidden" name="subheader_font_family" id="subheader_font_family">
                                <input type="hidden" name="subheader_font_size" id="subheader_font_size">

                                <input type="hidden" name="footer_font_family" id="footer_font_family">
                                <input type="hidden" name="footer_font_size" id="footer_font_size">

                                <div class="mb-3">
                                    <label class="form-label">Template Name</label>
                                    <input type="text" name="template_name" class="form-control" required />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Upload Left Logo</label>
                                    <input type="file" name="left_logo" class="form-control" accept="image/*" onchange="previewImage(event, 'leftLogoBox')" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Upload Right Logo</label>
                                    <input type="file" name="right_logo" class="form-control" accept="image/*" onchange="previewImage(event, 'rightLogoBox')" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Header</label>
                                    <div id="header" name="header_content" class="rich-input" contenteditable="true" oninput="updatePreview()"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Subheader</label>
                                    <div class="btn-group mb-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAlignment('subheader', 'left')"><i class="bi bi-align-start"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAlignment('subheader', 'center')"><i class="bi bi-align-center"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAlignment('subheader', 'right')"><i class="bi bi-align-end"></i></button>
                                    </div>'
                                    <!-- Add Table Controls -->
                                    <div class="btn-group mb-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="insertTable('subheader')">Insert Table</button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow('subheader')">Add Row</button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addColumn('subheader')">Add Column</button>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="toggleTableBorders('subheader')">
                                        Toggle Borders
                                    </button>
                                    <div id="subheader" name="subheader_content" class="rich-input" contenteditable="true" oninput="updatePreview()"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Footer</label>
                                    <div class="btn-group mb-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAlignment('footer', 'left')"><i class="bi bi-align-start"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAlignment('footer', 'center')"><i class="bi bi-align-center"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAlignment('footer', 'right')"><i class="bi bi-align-end"></i></button>
                                    </div>
                                    <!-- Add Table Controls -->
                                    <div class="btn-group mb-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="insertTable('footer')">Insert Table</button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow('footer')">Add Row</button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addColumn('footer')">Add Column</button>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="toggleTableBorders('footer')">
                                        Toggle Borders
                                    </button>
                                    <div id="footer" name="footer_content" class="rich-input" contenteditable="true" oninput="updatePreview()"></div>
                                </div>
                                <input type="hidden" name="header_content" id="header_hidden">
                                <input type="hidden" name="subheader_content" id="subheader_hidden">
                                <input type="hidden" name="footer_content" id="footer_hidden">
                                <button type="submit" class="btn submit-btn"><i class="bi bi-save"></i> Save Template</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Live Preview</h5>
                        </div>
                        <div class="card-body preview-box" id="livePreview">
                            <div class="row">
                                <div class="col-3 text-start" id="leftLogoBox"></div>
                                <div class="col-6 text-center">
                                    <div id="headerPreview"></div>
                                </div>
                                <div class="col-3 text-end" id="rightLogoBox"></div>
                                <div class="col-12 mt-2">
                                    <div id="subheaderPreview" class="text-muted"></div>
                                </div>
                            </div>
                            <hr />
                            <div class="mt-3" id="footerPreview"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'template_saved_list.php'; ?>
        </div>
    </div>
    <?php include 'modals/upload_template_modal.php'; ?>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/templates.js"></script>

</body>

</html>