<?php
require_once '../connect.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Placeholder parsing and HTML decoding
function parseTemplateContent($html) {
    $decoded = html_entity_decode($html); // Decode DB-escaped HTML

    $placeholders = [
        '$dynamic_month' => date('F'),
        '$dynamic_year'  => date('Y'),
        '[blank]' => '<span style="display:inline-block; border-bottom:1px solid #000; min-width:100px;">&nbsp;</span>',
    ];

    foreach ($placeholders as $key => $value) {
        $decoded = str_ireplace($key, $value, $decoded); // Case-insensitive
    }

    return $decoded;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['selected_assets']) && !empty($_POST['template_id'])) {
    $ids = array_map('intval', $_POST['selected_assets']);
    $id_list = implode(',', $ids);
    $template_id = (int)$_POST['template_id'];

    // Fetch template
    $template_stmt = $conn->prepare("SELECT * FROM report_templates WHERE id = ?");
    $template_stmt->bind_param("i", $template_id);
    $template_stmt->execute();
    $template_result = $template_stmt->get_result();
    $template = $template_result->fetch_assoc();
    $template_stmt->close();

    if (!$template) {
        die("Invalid report template selected.");
    }

    // Fetch asset data
    $stmt = $conn->query("
        SELECT a.*, c.category_name
        FROM assets a
        JOIN categories c ON a.category = c.id
        WHERE a.id IN ($id_list)
    ");

    // Prepare logo images
    $leftLogoBase64 = '';
    if (!empty($template['left_logo_path'])) {
        $leftLogoPath = '../uploads/' . $template['left_logo_path'];
        if (file_exists($leftLogoPath)) {
            $leftLogoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($leftLogoPath));
        }
    }

    $rightLogoBase64 = '';
    if (!empty($template['right_logo_path'])) {
        $rightLogoPath = '../uploads/' . $template['right_logo_path'];
        if (file_exists($rightLogoPath)) {
            $rightLogoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($rightLogoPath));
        }
    }

    // Parse and format template content
    $headerHtml    = parseTemplateContent($template['header_html']);
    $subHeaderHtml = parseTemplateContent($template['subheader_html']);
    $footerHtml    = parseTemplateContent($template['footer_html']);

    // Report metadata
    $reportDate = date('F Y');
    $reportFilename = 'Inventory_Report_' . date('Ymd_His') . '.pdf';

    // Start building HTML
    $html = '
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header-row {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .header-col {
            display: table-cell;
            vertical-align: top;
        }
        .left-col, .right-col {
            width: 20%;
        }
        .center-col {
            width: 60%;
            text-align: center;
        }
        .logo {
            height: 80px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 30px;
            font-size: 11px;
            text-align: center;
        }
    </style>

    <div class="header-row">
        <div class="header-col left-col">' . ($leftLogoBase64 ? '<img src="' . $leftLogoBase64 . '" class="logo">' : '') . '</div>
        <div class="header-col center-col" style="text-align: center;">
            ' . $headerHtml . '
            ' . $subHeaderHtml . '
        </div>
        <div class="header-col right-col" style="text-align: right;">' . ($rightLogoBase64 ? '<img src="' . $rightLogoBase64 . '" class="logo">' : '') . '</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Asset Name</th>
                <th>Category</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Status</th>
                <th>Value</th>
                <th>Acquired</th>
                <th>Updated</th>
            </tr>
        </thead>
        <tbody>';

    while ($row = $stmt->fetch_assoc()) {
        $html .= '<tr>
            <td>' . htmlspecialchars($row['asset_name']) . '</td>
            <td>' . htmlspecialchars($row['category_name']) . '</td>
            <td>' . htmlspecialchars($row['description']) . '</td>
            <td>' . $row['quantity'] . '</td>
            <td>' . htmlspecialchars($row['unit']) . '</td>
            <td>' . ucfirst(htmlspecialchars($row['status'])) . '</td>
            <td>&#8369; ' . number_format($row['value'], 2) . '</td>
            <td>' . date('M d, Y', strtotime($row['acquisition_date'])) . '</td>
            <td>' . date('M d, Y', strtotime($row['last_updated'])) . '</td>
        </tr>';
    }

    $html .= '</tbody></table>';

    // Footer section
    if (!empty($footerHtml)) {
        $html .= '<div class="footer">' . $footerHtml . '</div>';
    }

    // Generate PDF with Dompdf
    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    // Save to server
    $pdfOutput = $dompdf->output();
    $savePath = '../generated_reports/' . $reportFilename;
    file_put_contents($savePath, $pdfOutput);

    // Log into database
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId) {
        $logStmt = $conn->prepare("INSERT INTO generated_reports (user_id, filename, template_id, generated_at) VALUES (?, ?, ?, NOW())");
        $logStmt->bind_param("isi", $userId, $reportFilename, $template_id);
        $logStmt->execute();
        $logStmt->close();
    }

    // Output PDF in browser
    $dompdf->stream($reportFilename, ['Attachment' => false]);
    exit;

} else {
    // Redirect if invalid request
    $office_id = $_GET['office'] ?? ($_POST['office'] ?? '');
    $redirectUrl = "inventory.php?report=none";
    if (!empty($office_id)) {
        $redirectUrl .= "&office=" . urlencode($office_id);
    }
    header("Location: $redirectUrl");
    exit;
}
