<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'aani_market_system');

echo "=== CUSTOMERS ===\n";
$result = $conn->query('SELECT id, user_id, first_name, last_name, phone FROM customers LIMIT 10');
while($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}, User ID: {$row['user_id']}, Name: {$row['first_name']} {$row['last_name']}\n";
}

echo "\n=== ORDERS WITH CUSTOMER CHECK ===\n";
$result = $conn->query('
SELECT o.id, o.order_reference, o.customer_id, c.id as cid, c.first_name
FROM orders o
LEFT JOIN customers c ON o.customer_id = c.id
LIMIT 10
');
while($row = $result->fetch_assoc()) {
    $found = $row['cid'] ? 'YES' : 'NO';
    echo "Order ID: {$row['id']}, Ref: {$row['order_reference']}, Customer ID: {$row['customer_id']}, Customer Found: {$found}\n";
}

echo "\n=== VENDOR 2 ORDERS (should be Orders 1 and 5) ===\n";
$result = $conn->query('
SELECT DISTINCT o.id, o.order_reference
FROM orders o
JOIN order_items oi ON o.id = oi.order_id
WHERE oi.vendor_id = 2
ORDER BY o.id
');
while($row = $result->fetch_assoc()) {
    echo "Order ID: {$row['id']}, Ref: {$row['order_reference']}\n";
}

$conn->close();
