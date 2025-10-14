<?php
session_start();
require_once '../connect.php';

// Simple test for borrow cart functionality
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Cart Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Borrow Cart Test</h2>

        <div class="row">
            <div class="col-md-6">
                <h4>Cart Actions</h4>
                <button class="btn btn-primary mb-2" onclick="addAsset(1)">Add Asset ID 1</button>
                <button class="btn btn-primary mb-2" onclick="addAsset(2)">Add Asset ID 2</button>
                <button class="btn btn-warning mb-2" onclick="getCart()">Get Cart</button>
                <button class="btn btn-danger mb-2" onclick="clearCart()">Clear Cart</button>
                <button class="btn btn-info mb-2" onclick="getCount()">Get Count</button>
            </div>

            <div class="col-md-6">
                <h4>Response</h4>
                <pre id="response" class="bg-light p-3 rounded"></pre>
            </div>
        </div>

        <div class="mt-4">
            <a href="borrow.php" class="btn btn-success">Go to Borrow Form</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        function addAsset(assetId) {
            $.post('borrow_cart_manager.php', {
                action: 'add',
                asset_id: assetId
            })
            .done(function(response) {
                $('#response').text(JSON.stringify(response, null, 2));
            })
            .fail(function(xhr, status, error) {
                $('#response').text('Error: ' + error);
            });
        }

        function getCart() {
            $.post('borrow_cart_manager.php', {
                action: 'get_cart'
            })
            .done(function(response) {
                $('#response').text(JSON.stringify(response, null, 2));
            })
            .fail(function(xhr, status, error) {
                $('#response').text('Error: ' + error);
            });
        }

        function clearCart() {
            $.post('borrow_cart_manager.php', {
                action: 'clear'
            })
            .done(function(response) {
                $('#response').text(JSON.stringify(response, null, 2));
            })
            .fail(function(xhr, status, error) {
                $('#response').text('Error: ' + error);
            });
        }

        function getCount() {
            $.post('borrow_cart_manager.php', {
                action: 'get_count'
            })
            .done(function(response) {
                $('#response').text(JSON.stringify(response, null, 2));
            })
            .fail(function(xhr, status, error) {
                $('#response').text('Error: ' + error);
            });
        }
    </script>
</body>
</html>
