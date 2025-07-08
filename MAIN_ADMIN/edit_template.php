<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$template_id = $_GET['id'] ?? null;
if (!$template_id) die("Invalid template ID.");

$stmt = $conn->prepare("SELECT * FROM report_templates WHERE id = ?");
$stmt->bind_param("i", $template_id);
$stmt->execute();
$result = $stmt->get_result();
$template = $result->fetch_assoc();
$stmt->close();

if (!$template) die("Template not found.");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css" />
    <link rel="stylesheet" href="css/templates.css" />

    <style>
        .rich-input {
            border: 1px solid #ccc;
            padding: 10px;
            min-height: 80px;
            background: #fff;
        }

        .preview-box {
            border: 1px solid #ccc;
            padding: 20px;
            background-color: #fff;
            height: 100%;
        }

        .blank {
            display: inline-block;
            border-bottom: 1px solid #000;
            width: 100px;
        }

        .preview-logo img {
            height: 60px;
        }

        .submit-btn {
            background-color: rgb(44, 110, 215);
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 20px;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .submit-btn:hover {
            background-color: rgb(9, 96, 184);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>

<body><?php include 'includes/sidebar.php' ?>
    <div class="main">
        <?php include 'includes/topbar.php' ?>
        <div class="container py-4">
            <h4 class="mb-3">Edit Template <?= htmlspecialchars($template['template_name']) ?></h4>

            <!-- Toolbar -->
            <div class="mb-3 d-flex flex-wrap gap-2">
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="toggleCommand(this, 'bold')">B</button>
                    <button class="btn btn-outline-secondary" onclick="toggleCommand(this, 'italic')">I</button>
                    <button class="btn btn-outline-warning" onclick="toggleCommand(this, 'underline')"><u>U</u></button>
                </div>
                <select class="form-select form-select-sm" style="width: auto;" onchange="setFontSize(this.value)">
                    <option value="">Font Size</option>
                    <option value="12px">12px</option>
                    <option value="14px">14px</option>
                    <option value="16px">16px</option>
                    <option value="18px">18px</option>
                    <option value="24px">24px</option>
                    <option value="32px">32px</option>
                </select>
                <select class="form-select form-select-sm" style="width: auto;" onchange="setFontFamily(this.value)">
                    <option value="">Font Family</option>
                    <option value="Arial">Arial</option>
                    <option value="Georgia">Georgia</option>
                    <option value="Tahoma">Tahoma</option>
                    <option value="Times New Roman">Times New Roman</option>
                    <option value="Verdana">Verdana</option>
                </select>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-dark" onclick="insertSpecial('[blank]')">Blank</button>
                    <button class="btn btn-outline-info" onclick="insertSpecial('$dynamic_year')">Year</button>
                    <button class="btn btn-outline-info" onclick="insertSpecial('$dynamic_month')">Month</button>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data" action="update_template.php">
                <input type="hidden" name="template_id" value="<?= $template['id'] ?>">
                <input type="hidden" name="remove_left_logo" id="remove_left_logo" value="0">
                <input type="hidden" name="remove_right_logo" id="remove_right_logo" value="0">

                <div class="row">
                    <!-- Editor -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Template Name</label>
                            <input type="text" name="template_name" class="form-control" value="<?= htmlspecialchars($template['template_name']) ?>" required>
                        </div>

                        <!-- Left Logo -->
                        <div class="mb-3">
                            <label class="form-label">Left Logo</label>
                            <input type="file" name="left_logo" class="form-control" accept="image/*" onchange="previewImage(event, 'leftLogoBox')">
                            <div class="mt-2" id="leftLogoEditor">
                                <?php if (!empty($template['left_logo_path'])): ?>
                                    <img src="<?= $template['left_logo_path'] ?>" style="height: 60px;">
                                    <button type="button" class="btn btn-sm btn-danger mt-1" onclick="removeLogo('leftLogoBox', 'leftLogoEditor')">Remove</button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Right Logo -->
                        <div class="mb-3">
                            <label class="form-label">Right Logo</label>
                            <input type="file" name="right_logo" class="form-control" accept="image/*" onchange="previewImage(event, 'rightLogoBox')">
                            <div class="mt-2 text-end" id="rightLogoEditor">
                                <?php if (!empty($template['right_logo_path'])): ?>
                                    <img src="<?= $template['right_logo_path'] ?>" style="height: 60px;">
                                    <button type="button" class="btn btn-sm btn-danger mt-1" onclick="removeLogo('rightLogoBox', 'rightLogoEditor')">Remove</button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Header, Subheader, Footer -->
                        <div class="mb-3">
                            <label class="form-label">Header</label>
                            <div id="header" class="rich-input" contenteditable="true" oninput="updatePreview()"><?= $template['header_html'] ?></div>
                            <input type="hidden" name="header_html" id="header_hidden">
                        </div>

                        <!-- Alignment for Subheader -->
                        <div class="btn-group btn-group-sm">
                            <span class="text-secondary small me-1">Subheader Align:</span>
                            <button type="button" class="btn btn-outline-secondary" onclick="setAlignment('subheader', 'left')">Left</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="setAlignment('subheader', 'center')">Center</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="setAlignment('subheader', 'right')">Right</button>
                        </div>


                        <div class="mb-3">
                            <label class="form-label">Subheader</label>
                            <div id="subheader" class="rich-input" contenteditable="true" oninput="updatePreview()"><?= $template['subheader_html'] ?></div>
                            <input type="hidden" name="subheader_html" id="subheader_hidden">
                        </div>

                        <!-- Alignment for Footer -->
                        <div class="btn-group btn-group-sm">
                            <span class="text-secondary small me-1">Footer Align:</span>
                            <button type="button" class="btn btn-outline-secondary" onclick="setAlignment('footer', 'left')">Left</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="setAlignment('footer', 'center')">Center</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="setAlignment('footer', 'right')">Right</button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Footer</label>
                            <div id="footer" class="rich-input" contenteditable="true" oninput="updatePreview()"><?= $template['footer_html'] ?></div>
                            <input type="hidden" name="footer_html" id="footer_hidden">
                        </div>

                        <button type="submit" class="btn submit-btn"><i class="bi bi-save"></i> Update Template</button>
                    </div>

                    <!-- Live Preview -->
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header"><strong>Live Preview</strong></div>
                            <div class="card-body preview-box">
                                <div class="row">
                                    <!-- Left Logo -->
                                    <div class="col-3 text-start preview-logo" id="leftLogoBox">
                                        <?php if (!empty($template['left_logo_path'])): ?>
                                            <img src="<?= $template['left_logo_path'] ?>" alt="Left Logo">
                                        <?php endif; ?>
                                    </div>

                                    <!-- Header -->
                                    <div class="col-6 text-center">
                                        <div id="headerPreview"></div>
                                    </div>

                                    <!-- Right Logo -->
                                    <div class="col-3 text-end preview-logo" id="rightLogoBox">
                                        <?php if (!empty($template['right_logo_path'])): ?>
                                            <img src="<?= $template['right_logo_path'] ?>" alt="Right Logo">
                                        <?php endif; ?>
                                    </div>

                                    <!-- Subheader -->
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
            </form>
        </div>

        <script>
            let activeEditable = null;

            function setAlignment(targetId, alignment) {
                const target = document.getElementById(targetId);
                if (target) {
                    target.style.textAlign = alignment;
                    updatePreview();
                }
            }

            document.addEventListener("focusin", function(e) {
                if (e.target.classList.contains("rich-input")) {
                    activeEditable = e.target;
                }
            });

            function toggleCommand(button, command) {
                document.execCommand(command, false, null);
                button.classList.toggle("active");
                updatePreview();
            }

            function setFontSize(size) {
                if (activeEditable) {
                    activeEditable.style.fontSize = size;
                    updatePreview();
                }
            }

            function setFontFamily(family) {
                if (activeEditable) {
                    activeEditable.style.fontFamily = family;
                    updatePreview();
                }
            }

            function insertSpecial(text) {
                const sel = window.getSelection();
                if (!sel.rangeCount) return;
                const range = sel.getRangeAt(0);
                range.deleteContents();
                range.insertNode(document.createTextNode(text));
                updatePreview();
            }

            function removeLogo(previewId, editorId) {
                document.getElementById(previewId).innerHTML = "";
                document.getElementById(editorId).innerHTML = "";

                if (previewId === 'leftLogoBox') {
                    document.getElementById('remove_left_logo').value = '1';
                }
                if (previewId === 'rightLogoBox') {
                    document.getElementById('remove_right_logo').value = '1';
                }

                updatePreview();
            }

            function previewImage(event, previewId) {
                const reader = new FileReader();
                reader.onload = function() {
                    document.getElementById(previewId).innerHTML = `<img src="${reader.result}" style="height: 60px;">`;
                    updatePreview();
                }
                reader.readAsDataURL(event.target.files[0]);
            }

            function updatePreview() {
                const header = document.getElementById('header');
                const subheader = document.getElementById('subheader');
                const footer = document.getElementById('footer');

                // Use styled wrapper for previews too
                document.getElementById('headerPreview').innerHTML = parseSpecial(wrapWithStyle(header));
                document.getElementById('subheaderPreview').innerHTML = parseSpecial(wrapWithStyle(subheader));
                document.getElementById('footerPreview').innerHTML = parseSpecial(wrapWithStyle(footer));

                // Save to hidden inputs for form submission
                document.getElementById('header_hidden').value = wrapWithStyle(header);
                document.getElementById('subheader_hidden').value = wrapWithStyle(subheader);
                document.getElementById('footer_hidden').value = wrapWithStyle(footer);
            }

            function wrapWithStyle(element) {
                const style = element.getAttribute("style") || "";
                return `<div style="${style}">${element.innerHTML}</div>`;
            }

            function parseSpecial(html) {
                return html
                    .replace(/\$dynamic_year/g, new Date().getFullYear())
                    .replace(/\$dynamic_month/g, new Date().toLocaleString('default', {
                        month: 'long'
                    }))
                    .replace(/\[blank\]/g, '<span class="blank">&nbsp;</span>');
            }

            window.onload = updatePreview;
        </script>


        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="js/dashboard.js"></script>
</body>

</html>