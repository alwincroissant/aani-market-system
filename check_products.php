<?php
$conn = new PDO('sqlite:database.sqlite');
$stmt = $conn->query('SELECT id, product_name, product_image_url FROM products ORDER BY id DESC LIMIT 10');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['id'] . ' | ' . $row['product_name'] . ' | ' . ($row['product_image_url'] ?? 'NULL') . PHP_EOL;
}
