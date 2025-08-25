<?php
require_once '../vendor/autoload.php';
require_once '../connect.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Validate Item ID
$item_id = isset($_GET['item_id']) ? $_GET['item_id'] : null;

if ($item_id) {
    // Fetch MR details from the database
    $stmt = $conn->prepare("SELECT * FROM mr_details WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $mr_details = $result->fetch_assoc();
    $stmt->close();

    if (!$mr_details) {
        die("MR record not found.");
    }

    // Prepare the HTML for the Property Sticker
    $html = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { text-align: center; }
            .header { font-size: 18px; font-weight: bold; }
            .content { margin-top: 20px; }
            img { max-width: 100%; height: auto; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <img src='" . realpath('../img/' . $mr_details['logo']) . "' alt='Municipal Logo' style='height: 50px;'><br>
                <h2>Government Property</h2>
            </div>
            <div class='content'>
                <p><strong>Item ID:</strong> {$mr_details['item_id']}</p>
                <p><strong>Description:</strong> {$mr_details['description']}</p>
                <p><strong>Model:</strong> {$mr_details['model_no']}</p>
                <p><strong>Serial No:</strong> {$mr_details['serial_no']}</p>
                <p><strong>Location:</strong> {$mr_details['office_location']}</p>
                <p><strong>Unit Quantity:</strong> {$mr_details['unit_quantity']} {$mr_details['unit']}</p>
                <p><strong>Acquisition Date:</strong> {$mr_details['acquisition_date']}</p>
                <p><strong>Acquisition Cost:</strong> PHP {$mr_details['acquisition_cost']}</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Instantiate Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);  // Enable HTML5 parsing
    $options->set('isPhpEnabled', true);  // Allow PHP code within HTML
    $dompdf = new Dompdf($options);
    
    // Load HTML content
    $dompdf->loadHtml($html);
    
    // (Optional) Set paper size
    $dompdf->setPaper('A4', 'portrait');
    
    // Render the PDF (first pass)
    $dompdf->render();
    
    // Output the generated PDF
    $dompdf->stream("property_sticker_{$item_id}.pdf", array("Attachment" => 0));  // 0 for inline, 1 for download
} else {
    echo "Item ID not provided.";
}
?>
