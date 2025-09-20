<?php
require_once '../connect.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Collect selected IDs from both sources
$selectedAssets = isset($_POST['selected_assets']) ? array_map('intval', (array)$_POST['selected_assets']) : [];
$selectedAssetsNew = isset($_POST['selected_assets_new']) ? array_map('intval', (array)$_POST['selected_assets_new']) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!empty($selectedAssets) || !empty($selectedAssetsNew))) {
    // Build a unified rows array with normalized columns
    $rows = [];

    // Pull from assets
    if (!empty($selectedAssets)) {
        $id_list = implode(',', $selectedAssets);
        $q = $conn->query("
            SELECT a.*, c.category_name
            FROM assets a
            LEFT JOIN categories c ON a.category = c.id
            WHERE a.id IN ($id_list)
        ");
        while ($r = $q->fetch_assoc()) {
            $qty = (int)($r['quantity'] ?? 0);
            $val = (float)($r['value'] ?? 0);
            $rows[] = [
                'description' => $r['description'] ?? '',
                'category_name' => $r['category_name'] ?? 'Uncategorized',
                'quantity' => $qty,
                'unit' => $r['unit'] ?? '',
                'status' => $r['status'] ?? '',
                'unit_cost' => $val,
                'total_value' => $val * $qty,
                'acquired' => $r['acquisition_date'] ?? '',
                'updated' => $r['last_updated'] ?? ''
            ];
        }
    }

    // Pull from assets_new
    if (!empty($selectedAssetsNew)) {
        $id_list_new = implode(',', $selectedAssetsNew);
        $q2 = $conn->query("
            SELECT 
              an.*,
              COALESCE(
                (
                  SELECT c.category_name 
                  FROM assets a 
                  LEFT JOIN categories c ON a.category = c.id 
                  WHERE a.asset_new_id = an.id 
                  ORDER BY a.id ASC 
                  LIMIT 1
                ), 'Uncategorized'
              ) AS category_name
            FROM assets_new an
            WHERE an.id IN ($id_list_new)
        ");
        while ($r2 = $q2->fetch_assoc()) {
            $qty = (int)($r2['quantity'] ?? 0);
            $val = (float)($r2['unit_cost'] ?? 0);
            $rows[] = [
                'description' => $r2['description'] ?? '',
                'category_name' => $r2['category_name'] ?? 'Uncategorized',
                'quantity' => $qty,
                'unit' => $r2['unit'] ?? '',
                'status' => 'available', // assets_new has no status column
                'unit_cost' => $val,
                'total_value' => $val * $qty,
                'acquired' => $r2['date_created'] ?? '',
                'updated' => $r2['date_created'] ?? ''
            ];
        }
    }

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
                <th>Description</th>
                <th>Category</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Status</th>
                <th>Unit Cost</th>
                <th>Total Value</th>
                <th>Acquired</th>
                <th>Updated</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($rows as $row) {
        $acq = !empty($row['acquired']) ? date('M d, Y', strtotime($row['acquired'])) : '';
        $upd = !empty($row['updated']) ? date('M d, Y', strtotime($row['updated'])) : '';
        $html .= '<tr>
            <td>' . htmlspecialchars($row['description']) . '</td>
            <td>' . htmlspecialchars($row['category_name']) . '</td>
            <td>' . (int)$row['quantity'] . '</td>
            <td>' . htmlspecialchars($row['unit']) . '</td>
            <td>' . htmlspecialchars(ucfirst($row['status'])) . '</td>
            <td>&#8369; ' . number_format((float)$row['unit_cost'], 2) . '</td>
            <td>&#8369; ' . number_format((float)$row['total_value'], 2) . '</td>
            <td>' . $acq . '</td>
            <td>' . $upd . '</td>
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

    // Insert log into reports table with office_id
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userId = $_SESSION['user_id'] ?? null;

    if ($userId) {
        // Get the user's office_id
        $officeQuery = $conn->prepare("SELECT office_id FROM users WHERE id = ?");
        $officeQuery->bind_param("i", $userId);
        $officeQuery->execute();
        $officeResult = $officeQuery->get_result();
        $officeRow = $officeResult->fetch_assoc();
        $officeId = $officeRow['office_id'] ?? null;

        $insert = $conn->prepare("INSERT INTO generated_reports (user_id, office_id, filename, generated_at) VALUES (?, ?, ?, NOW())");
        $insert->bind_param("iis", $userId, $officeId, $reportFilename);
        $insert->execute();
    }

    // Output PDF to browser
    $dompdf->stream($reportFilename, ['Attachment' => false]);
    exit;
} else {
    echo "No assets selected.";
}
