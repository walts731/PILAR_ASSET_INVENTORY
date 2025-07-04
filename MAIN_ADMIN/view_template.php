<?php
require_once '../connect.php';

if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM report_templates WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo '<div class="container-fluid">';
    echo '<div class="row mb-3">';
    if ($row['left_logo_path']) {
        echo '<div class="col-3 text-start"><img src="' . htmlspecialchars($row['left_logo_path']) . '" style="height:60px;"></div>';
    }
    echo '<div class="col-6 text-center">';
    echo '<div>' . $row['header_html'] . '</div>';
    echo '<div class="text-muted mt-2">' . $row['subheader_html'] . '</div>';
    echo '</div>';
    if ($row['right_logo_path']) {
        echo '<div class="col-3 text-end"><img src="' . htmlspecialchars($row['right_logo_path']) . '" style="height:60px;"></div>';
    }
    echo '</div>';
    echo '<hr>';
    echo '<div class="text-end">' . $row['footer_html'] . '</div>';
    echo '</div>';
} else {
    echo "Template not found.";
}
$stmt->close();
