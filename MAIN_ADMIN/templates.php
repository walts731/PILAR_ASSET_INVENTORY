<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_SESSION['office_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT office_id FROM users WHERE user_id = ?");
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
    <style>
        .preview-box {
            border: 1px solid #ccc;
            padding: 20px;
            background-color: #fff;
            height: 100%;
        }

        .toolbar button {
            margin-right: 5px;
        }

        .rich-input {
            border: 1px solid #ccc;
            padding: 10px;
            min-height: 100px;
            background: #fff;
        }

        .blank {
            display: inline-block;
            min-width: 100px;
            border-bottom: 1px solid #000;
        }

        .toolbar .btn.active {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php' ?>
    <div class="main">
        <?php include 'includes/topbar.php' ?>
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
                                    <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#uploadFormatModal">
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
                                    </div>
                                    <div id="subheader" name="subheader_content" class="rich-input" contenteditable="true" oninput="updatePreview()"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Footer</label>
                                    <div class="btn-group mb-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAlignment('footer', 'left')"><i class="bi bi-align-start"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAlignment('footer', 'center')"><i class="bi bi-align-center"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAlignment('footer', 'right')"><i class="bi bi-align-end"></i></button>
                                    </div>
                                    <div id="footer" name="footer_content" class="rich-input" contenteditable="true" oninput="updatePreview()"></div>
                                </div>
                                <input type="hidden" name="header_content" id="header_hidden">
                                <input type="hidden" name="subheader_content" id="subheader_hidden">
                                <input type="hidden" name="footer_content" id="footer_hidden">
                                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Save Template</button>
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
    <!-- Upload Format Modal -->
    <div class="modal fade" id="uploadFormatModal" tabindex="-1" aria-labelledby="uploadFormatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="uploadFormatModalLabel">Template Format Guide</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Before uploading, please ensure your file includes the following comment blocks:</p>
                    <ul class="list-unstyled">
                        <li><code>&lt;!-- HEADER_START --&gt;</code> ... <code>&lt;!-- HEADER_END --&gt;</code></li>
                        <li><code>&lt;!-- SUBHEADER_START --&gt;</code> ... <code>&lt;!-- SUBHEADER_END --&gt;</code></li>
                        <li><code>&lt;!-- FOOTER_START --&gt;</code> ... <code>&lt;!-- FOOTER_END --&gt;</code></li>
                    </ul>
                    <form id="uploadTemplateForm" action="upload_template.php" method="POST" enctype="multipart/form-data">
                        <input type="file" name="template_file" id="templateFileInput" class="form-control mb-2" accept=".docx,.txt,.html" hidden onchange="document.getElementById('uploadTemplateForm').submit();">
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success" onclick="document.getElementById('templateFileInput').click();">Continue</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- jQuery, Bootstrap JS, and DataTables JS (before </body>) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        function format(command) {
            document.execCommand(command, false, null);
            updatePreview();
        }

        function insertSpecial(text) {
            const sel = window.getSelection();
            if (!sel.rangeCount) return;
            const range = sel.getRangeAt(0);
            range.deleteContents();
            range.insertNode(document.createTextNode(text));
            updatePreview();
        }

        function setAlignment(section, alignment) {
            const el = document.getElementById(section);
            if (el) {
                el.style.textAlign = alignment;
                updatePreview();
            }
        }

        function updatePreview() {
            const header = document.getElementById('header');
            const subheader = document.getElementById('subheader');
            const footer = document.getElementById('footer');

            const headerPreview = document.getElementById('headerPreview');
            const subheaderPreview = document.getElementById('subheaderPreview');
            const footerPreview = document.getElementById('footerPreview');

            // Set previews
            headerPreview.innerHTML = parseSpecial(header.innerHTML);
            subheaderPreview.innerHTML = parseSpecial(subheader.innerHTML);
            footerPreview.innerHTML = parseSpecial(footer.innerHTML);

            headerPreview.style.cssText = header.style.cssText;
            subheaderPreview.style.cssText = subheader.style.cssText;
            footerPreview.style.cssText = footer.style.cssText;

            // Set hidden HTML content
            // Wrap with inline style
            document.getElementById('header_hidden').value =
                `<div style="font-family:${header.style.fontFamily}; font-size:${header.style.fontSize}; text-align:${header.style.textAlign};">${header.innerHTML}</div>`;

            document.getElementById('subheader_hidden').value =
                `<div style="font-family:${subheader.style.fontFamily}; font-size:${subheader.style.fontSize}; text-align:${subheader.style.textAlign};">${subheader.innerHTML}</div>`;

            document.getElementById('footer_hidden').value =
                `<div style="font-family:${footer.style.fontFamily}; font-size:${footer.style.fontSize}; text-align:${footer.style.textAlign};">${footer.innerHTML}</div>`;

            // Set font family and size hidden inputs
            document.getElementById('header_font_family').value = header.style.fontFamily || '';
            document.getElementById('header_font_size').value = header.style.fontSize || '';

            document.getElementById('subheader_font_family').value = subheader.style.fontFamily || '';
            document.getElementById('subheader_font_size').value = subheader.style.fontSize || '';

            document.getElementById('footer_font_family').value = footer.style.fontFamily || '';
            document.getElementById('footer_font_size').value = footer.style.fontSize || '';
        }

        updatePreview();


        function parseSpecial(html) {
            return html
                .replace(/\$dynamic_year/g, new Date().getFullYear())
                .replace(/\$dynamic_month/g, new Date().toLocaleString('default', {
                    month: 'long'
                }))
                .replace(/\[blank\]/g, '<span class="blank">&nbsp;</span>');
        }

        function previewImage(event, targetId) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById(targetId).innerHTML = `<img src="${reader.result}" style="height: 60px;">`;
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        updatePreview();

        function toggleCommand(button, command) {
            document.execCommand(command, false, null);
            button.classList.toggle("active"); // Toggle highlight
            updatePreview();
        }

        function setFontSize(size) {
            const activeSection = getActiveSection();
            if (activeSection) {
                activeSection.style.fontSize = size;
                updatePreview();
            }
        }

        function setFontFamily(family) {
            const activeSection = getActiveSection();
            if (activeSection) {
                activeSection.style.fontFamily = family;
                updatePreview();
            }
        }

        // Detect which section the user is editing
        let activeEditable = null;
        document.addEventListener("focusin", function(e) {
            if (e.target.classList.contains("rich-input")) {
                activeEditable = e.target;
            }
        });

        function getActiveSection() {
            return activeEditable;
        }
    </script>
</body>

</html>