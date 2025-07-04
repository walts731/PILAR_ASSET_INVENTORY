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
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Create Report Template</h5>
                            <div class="toolbar">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="format('bold')">Bold</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="format('italic')">Italic</button>
                                <button type="button" class="btn btn-sm btn-outline-dark" onclick="insertSpecial('[blank]')">Add Blank</button>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="insertSpecial('$dynamic_year')">Year</button>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="insertSpecial('$dynamic_month')">Month</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="templateForm" method="POST" enctype="multipart/form-data" action="save_template.php">
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
                                <div class="col-12 mt-2">
                                    <div id="subheaderPreview" class="text-muted"></div>
                                </div>
                                <div class="col-3 text-end" id="rightLogoBox"></div>
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
            document.getElementById('headerPreview').innerHTML = parseSpecial(document.getElementById('header').innerHTML);

            const subheader = document.getElementById('subheader');
            document.getElementById('subheaderPreview').innerHTML = parseSpecial(subheader.innerHTML);
            document.getElementById('subheaderPreview').style.textAlign = subheader.style.textAlign;

            const footer = document.getElementById('footer');
            document.getElementById('footerPreview').innerHTML = parseSpecial(footer.innerHTML);
            document.getElementById('footerPreview').style.textAlign = footer.style.textAlign;

            document.getElementById('header_hidden').value = document.getElementById('header').innerHTML;
            document.getElementById('subheader_hidden').value = subheader.innerHTML;
            document.getElementById('footer_hidden').value = footer.innerHTML;
        }

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
    </script>
</body>

</html>