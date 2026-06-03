<?php
// GET /api/categories.php
// POST /api/categories.php  body: {name}
require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $rows = db()->query('SELECT name FROM menu_categories ORDER BY sort_order, name')->fetch_all(MYSQLI_ASSOC);
    json_out(array_column($rows, 'name'));
}

if ($method === 'POST') {
    $body = json_body();
    $name = trim($body['name'] ?? '');
    if (!$name) json_out(['error' => 'name required'], 400);

    $stmt = db()->prepare('INSERT IGNORE INTO menu_categories (name, sort_order) VALUES (?, 99)');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    json_out(['ok' => true]);
}

json_out(['error' => 'Method not allowed'], 405);
