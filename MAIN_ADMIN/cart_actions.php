<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['borrow_cart'])) {
    $_SESSION['borrow_cart'] = [];
}

header('Content-Type: application/json');
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $asset_id = intval($_POST['asset_id']);
        $quantity = intval($_POST['quantity']);
        $asset_name = filter_var($_POST['asset_name'], FILTER_SANITIZE_STRING);
        $max_quantity = intval($_POST['max_quantity']);

        if ($quantity <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Quantity must be positive.']);
            exit();
        }

        if ($quantity > $max_quantity) {
            echo json_encode(['status' => 'error', 'message' => 'Quantity exceeds available stock.']);
            exit();
        }

        // Add or update item in cart
        $_SESSION['borrow_cart'][$asset_id] = [
            'quantity' => $quantity,
            'asset_name' => $asset_name,
            'max_quantity' => $max_quantity
        ];

        echo json_encode(['status' => 'success', 'message' => "'$asset_name' added to cart.", 'cart_count' => count($_SESSION['borrow_cart'])]);
        break;

    case 'remove':
        $asset_id = intval($_POST['asset_id']);
        if (isset($_SESSION['borrow_cart'][$asset_id])) {
            unset($_SESSION['borrow_cart'][$asset_id]);
            echo json_encode(['status' => 'success', 'message' => 'Item removed from cart.', 'cart_count' => count($_SESSION['borrow_cart'])]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Item not found in cart.']);
        }
        break;

    case 'update':
        $asset_id = intval($_POST['asset_id']);
        $quantity = intval($_POST['quantity']);

        if (!isset($_SESSION['borrow_cart'][$asset_id])) {
            echo json_encode(['status' => 'error', 'message' => 'Item not found in cart.']);
            exit();
        }

        $max_quantity = $_SESSION['borrow_cart'][$asset_id]['max_quantity'];

        if ($quantity <= 0) {
            // If quantity is zero or less, remove the item
            unset($_SESSION['borrow_cart'][$asset_id]);
            echo json_encode(['status' => 'success', 'message' => 'Item removed from cart.', 'cart_count' => count($_SESSION['borrow_cart']), 'removed' => true]);
        } elseif ($quantity > $max_quantity) {
            echo json_encode(['status' => 'error', 'message' => 'Quantity exceeds available stock.']);
        } else {
            $_SESSION['borrow_cart'][$asset_id]['quantity'] = $quantity;
            echo json_encode(['status' => 'success', 'message' => 'Cart updated.', 'cart_count' => count($_SESSION['borrow_cart'])]);
        }
        break;

    case 'clear':
        $_SESSION['borrow_cart'] = [];
        echo json_encode(['status' => 'success', 'message' => 'Cart cleared.', 'cart_count' => 0]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        break;
}
?>
