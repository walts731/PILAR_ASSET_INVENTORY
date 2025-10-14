<?php
session_start();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        addToBorrowCart();
        break;
    case 'remove':
        removeFromBorrowCart();
        break;
    case 'clear':
        clearBorrowCart();
        break;
    case 'get_cart':
        getBorrowCart();
        break;
    case 'get_count':
        getBorrowCartCount();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function addToBorrowCart() {
    $asset_id = intval($_POST['asset_id'] ?? 0);
    
    if ($asset_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid asset ID']);
        return;
    }
    
    // Check if asset already exists in cart
    if (isset($_SESSION['borrow_cart'])) {
        foreach ($_SESSION['borrow_cart'] as $item) {
            if ($item['asset_id'] == $asset_id) {
                echo json_encode(['success' => false, 'message' => 'Asset already in borrow cart']);
                return;
            }
        }
    }
    
    // Fetch asset details
    require_once '../connect.php';
    
    $sql = "SELECT a.id, a.description, a.inventory_tag, a.property_no, a.category, c.category_name 
            FROM assets a 
            LEFT JOIN categories c ON a.category = c.id 
            WHERE a.id = ? AND a.status != 'disposed'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $asset_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Asset not found or not available']);
        return;
    }
    
    $asset = $result->fetch_assoc();
    $stmt->close();
    
    // Add to cart
    if (!isset($_SESSION['borrow_cart'])) {
        $_SESSION['borrow_cart'] = [];
    }
    
    $_SESSION['borrow_cart'][] = [
        'asset_id' => $asset['id'],
        'description' => $asset['description'],
        'inventory_tag' => $asset['inventory_tag'],
        'property_no' => $asset['property_no'],
        'category_name' => $asset['category_name']
    ];
    
    echo json_encode([
        'success' => true, 
        'message' => 'Asset added to borrow cart',
        'count' => count($_SESSION['borrow_cart'])
    ]);
}

function removeFromBorrowCart() {
    $asset_id = intval($_POST['asset_id'] ?? 0);
    
    if (!isset($_SESSION['borrow_cart'])) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        return;
    }
    
    foreach ($_SESSION['borrow_cart'] as $key => $item) {
        if ($item['asset_id'] == $asset_id) {
            unset($_SESSION['borrow_cart'][$key]);
            $_SESSION['borrow_cart'] = array_values($_SESSION['borrow_cart']); // Reindex array
            echo json_encode([
                'success' => true, 
                'message' => 'Asset removed from borrow cart',
                'count' => count($_SESSION['borrow_cart'])
            ]);
            return;
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Asset not found in cart']);
}

function clearBorrowCart() {
    $_SESSION['borrow_cart'] = [];
    echo json_encode(['success' => true, 'message' => 'Borrow cart cleared']);
}

function getBorrowCart() {
    $cart = $_SESSION['borrow_cart'] ?? [];
    echo json_encode(['success' => true, 'cart' => $cart, 'count' => count($cart)]);
}

function getBorrowCartCount() {
    $count = isset($_SESSION['borrow_cart']) ? count($_SESSION['borrow_cart']) : 0;
    echo json_encode(['success' => true, 'count' => $count]);
}
?>
