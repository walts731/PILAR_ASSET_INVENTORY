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
    $currentMonth = date("F");

    $html = str_replace(
        ['[blank]', '$dynamic_year', '$dynamic_month'],
        [
            '<span style="display:inline-block; min-width:100px; border-bottom:1px solid #000;">&nbsp;</span>',
            $currentYear,
            $currentMonth
        ],
        $html
    );

    return $html; // Styles remain untouched
}

?>

<?php if ($row = $result->fetch_assoc()): ?>
    <style>
        .bordered-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 2px solid #000;
            margin-top: 30px;
        }

        .bordered-table th,
        .bordered-table td {
            border-right: 1px solid #000;
            padding: 10px;
            height: 40px;
        }

        .bordered-table th:last-child,
        .bordered-table td:last-child {
            border-right: none;
        }

        .bordered-table thead th {
            border-bottom: 1px solid #000;
            text-align: left;
            background: #f8f9fa;
        }

        .bordered-table tr:not(:last-child) td {
            border-bottom: none;
        }
    </style>

    <div class="container-fluid">
        <div class="row mb-3">
            <!-- Left logo -->
            <div class="col-3 text-start">
                <?php if ($row['left_logo_path']): ?>
                    <img src="<?= htmlspecialchars($row['left_logo_path']) ?>" style="height:60px;">
                <?php endif; ?>
            </div>

            <!-- Header -->
            <div class="col-6 text-center">
                <div><?= renderContent($row['header_html']) ?></div>
            </div>

            <!-- Right logo -->
            <div class="col-3 text-end">
                <?php if ($row['right_logo_path']): ?>
                    <img src="<?= htmlspecialchars($row['right_logo_path']) ?>" style="height:60px;">
                <?php endif; ?>
            </div>
        </div>

        <!-- Subheader full-width -->
        <div class="row mb-2">
            <div class="col-md-12 text-muted">
                <?= renderContent($row['subheader_html']) ?>
            </div>
        </div>

        <hr>

        <!-- Sample table with outer and vertical borders -->
        <div class="table-responsive mb-4">
            <table class="bordered-table">
                <thead>
                    <tr>
                        <th style="width:5%;">#</th>
                        <th>Description</th>
                        <th style="width:15%;">Quantity</th>
                        <th style="width:15%;">Unit</th>
                        <th style="width:20%;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="mt-4">
            <?= renderContent($row['footer_html']) ?>
        </div>
    </div>
<?php else: ?>
    <p>Template not found.</p>
<?php endif;

$stmt->close();
?>
