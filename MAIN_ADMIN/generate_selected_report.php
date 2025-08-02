<?php
require_once '../connect.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['selected_assets'])) {
    $ids = array_map('intval', $_POST['selected_assets']);
    $id_list = implode(',', $ids);

    $stmt = $conn->query("
        SELECT a.*, c.category_name
        FROM assets a
        JOIN categories c ON a.category = c.id
        WHERE a.id IN ($id_list)
    ");

    $reportDate = date('F Y');
    $reportFilename = 'Inventory_Report_' . date('Ymd_His') . '.pdf';
    $logoPath = '../img/PILAR LOGO TRANSPARENT.png';
    $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

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
        .left-col {
            width: 20%;
            text-align: left;
        }
        .center-col {
            width: 60%;
            text-align: center;
        }
        .right-col {
            width: 20%;
        }
        .logo {
            height: 80px;
        }
        .center-col h4, .center-col h2, .center-col p {
            margin: 2px 0;
            line-height: 1.3;
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
    </style>

    <div class="header-row">
        <div class="header-col left-col">';

    if ($logoBase64) {
        $html .= '<img src="' . $logoBase64 . '" class="logo" alt="Logo">';
    }

    $html .= '</div>
        <div class="header-col center-col">
            <p>Republic of the Philippines</p>
            <h4>Municipality of Pilar</h4>
            <p>Province of Sorsogon</p>
            <h2>INVENTORY REPORT</h2>
            <p><em>As of ' . $reportDate . '</em></p>
        </div>
        <div class="header-col right-col"></div>
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

    // Generate PDF
    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    // Save the PDF to a directory
    $pdfOutput = $dompdf->output();
    $savePath = '../generated_reports/' . $reportFilename;
    file_put_contents($savePath, $pdfOutput);

    // Optional: Insert log into reports table
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId) {
        $insert = $conn->prepare("INSERT INTO generated_reports (user_id, filename, generated_at) VALUES (?, ?, NOW())");
        $insert->bind_param("is", $userId, $reportFilename);
        $insert->execute();
    }

    // Output PDF to browser
    $dompdf->stream($reportFilename, ['Attachment' => false]);
    exit;
} else {
    echo "No assets selected.";
}
?>
