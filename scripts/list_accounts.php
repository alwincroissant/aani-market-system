<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$db = $app->make('db');

$users = [];
$vendors = [];

try {
    $users = $db->table('users')->get();
} catch (Exception $e) {
    echo "Error querying users: " . $e->getMessage() . "\n";
}

try {
    $vendors = $db->table('vendors')->get();
} catch (Exception $e) {
    echo "Error querying vendors: " . $e->getMessage() . "\n";
}

echo "USERS:\n";
if (is_object($users) && method_exists($users, 'toJson')) {
    echo $users->toJson(JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo json_encode($users, JSON_PRETTY_PRINT) . "\n\n";
}

echo "VENDORS:\n";
if (is_object($vendors) && method_exists($vendors, 'toJson')) {
    echo $vendors->toJson(JSON_PRETTY_PRINT) . "\n";
} else {
    echo json_encode($vendors, JSON_PRETTY_PRINT) . "\n";
}
