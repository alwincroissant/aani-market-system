<?php
// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Get database connection
$conn = new mysqli('127.0.0.1', 'root', '', 'aani_market_system');

echo "=== USERS ===\n";
$result = $conn->query('SELECT id, email, role, is_active FROM users LIMIT 10');
while($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}, Email: {$row['email']}, Role: {$row['role']}, Active: {$row['is_active']}\n";
}

echo "\n=== VENDORS ===\n";
$result = $conn->query('SELECT id, user_id, business_name FROM vendors LIMIT 10');
while($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}, User ID: {$row['user_id']}, Business: {$row['business_name']}\n";
}

echo "\n=== PRODUCTS (first 10) ===\n";
$result = $conn->query('SELECT id, vendor_id, product_name, deleted_at FROM products LIMIT 10');
while($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}, Vendor ID: {$row['vendor_id']}, Product: {$row['product_name']}, Deleted: {$row['deleted_at']}\n";
}

echo "\n=== ORDERS ===\n";
$result = $conn->query('SELECT id, order_reference, customer_id, order_status, created_at FROM orders LIMIT 10');
while($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}, Ref: {$row['order_reference']}, Customer: {$row['customer_id']}, Status: {$row['order_status']}, Created: {$row['created_at']}\n";
}

echo "\n=== ORDER ITEMS ===\n";
$result = $conn->query('SELECT id, order_id, vendor_id, product_id, item_status FROM order_items LIMIT 15');
while($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}, Order: {$row['order_id']}, Vendor: {$row['vendor_id']}, Product: {$row['product_id']}, Status: {$row['item_status']}\n";
}

$conn->close();
