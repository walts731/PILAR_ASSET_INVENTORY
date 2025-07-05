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

function renderContent($html) {
    $currentYear = date("Y");
    $currentMonth = date("F"); // Full month name

    return str_replace(
        ['[blank]', '$dynamic_year', '$dynamic_month'],
        [
            '<span style="display:inline-block; min-width:100px; border-bottom:1px solid #000;">&nbsp;</span>',
            $currentYear,
            $currentMonth
        ],
        $html
    );
}

if ($row = $result->fetch_assoc()) {
    echo '<div class="container-fluid">';
    echo '<div class="row mb-3">';

    // Left logo
    if ($row['left_logo_path']) {
        echo '<div class="col-3 text-start"><img src="' . htmlspecialchars($row['left_logo_path']) . '" style="height:60px;"></div>';
    } else {
        echo '<div class="col-3"></div>';
    }

    // Header center
    echo '<div class="col-6 text-center">';
    echo '<div>' . renderContent($row['header_html']) . '</div>';
    echo '</div>';

    // Right logo
    if ($row['right_logo_path']) {
        echo '<div class="col-3 text-end"><img src="' . htmlspecialchars($row['right_logo_path']) . '" style="height:60px;"></div>';
    } else {
        echo '<div class="col-3"></div>';
    }

    echo '</div>'; // end of row

    // Subheader - full width
    echo '<div class="row mb-2">';
    echo '<div class="col-md-12 text-muted">' . renderContent($row['subheader_html']) . '</div>';
    echo '</div>';

    echo '<hr>';

    // Footer
    echo '<div class="text-end">' . renderContent($row['footer_html']) . '</div>';
    echo '</div>';
} else {
    echo "Template not found.";
}

$stmt->close();
