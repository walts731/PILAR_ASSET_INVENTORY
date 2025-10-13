<?php
// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sample_import_consumables.csv"');

// Output CSV content
echo "description,quantity,unit,unit_price,office_name,acquisition_date\n";
echo "Printer Paper A4,10,ream,250.00,IT Department,2025-01-15\n";
echo "Ballpen Black,50,piece,15.00,IT Department,2025-01-15\n";
echo "Stapler,2,piece,350.00,IT Department,2025-01-15\n";
echo "Staples,5,box,25.00,IT Department,2025-01-15\n";
echo "Notebook,20,piece,45.00,IT Department,2025-01-15\n";

exit;