<?php
session_start();
require_once '../connect.php';

// Set up test guest session
$_SESSION['is_guest'] = true;
$_SESSION['guest_email'] = 'guest@pilar.gov.ph';

// Add some test assets to cart for demonstration
$_SESSION['borrow_cart'] = [
    [
        'asset_id' => 1,
        'description' => 'Dell Laptop XPS 15',
        'inventory_tag' => 'DELL-001',
        'property_no' => 'PROP-001',
        'category_name' => 'Computer Equipment'
    ],
    [
        'asset_id' => 2,
        'description' => 'HP LaserJet Printer',
        'inventory_tag' => 'HP-002',
        'property_no' => 'PROP-002',
        'category_name' => 'Office Equipment'
    ]
];

// Redirect to borrow.php to test the form
header("Location: borrow.php");
exit();
?>
