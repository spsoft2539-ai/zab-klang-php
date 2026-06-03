<?php
require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $rows = db()->query('SELECT * FROM restaurant_tables ORDER BY id')->fetch_all(MYSQLI_ASSOC);
    json_out(array_map('format_table', $rows));
}

json_out(['error' => 'Method not allowed'], 405);
