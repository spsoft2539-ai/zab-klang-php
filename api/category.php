<?php
// PATCH/DELETE /api/category.php?name=xxx
require_once __DIR__ . '/../db.php';

$name   = urldecode($_GET['name'] ?? '');
$method = $_SERVER['REQUEST_METHOD'];

if (!$name) json_out(['error' => 'name required'], 400);

if ($method === 'PATCH') {
    $body    = json_body();
    $newName = trim($body['newName'] ?? '');
    if (!$newName) json_out(['error' => 'newName required'], 400);

    // Check duplicate
    $ck = db()->prepare('SELECT name FROM menu_categories WHERE name=?');
    $ck->bind_param('s', $newName);
    $ck->execute();
    if ($ck->get_result()->num_rows > 0) json_out(['error' => 'name exists'], 409);

    // Rename in categories
    $stmt = db()->prepare('UPDATE menu_categories SET name=? WHERE name=?');
    $stmt->bind_param('ss', $newName, $name);
    $stmt->execute();

    // Rename in menu_items
    $stmt2 = db()->prepare('UPDATE menu_items SET category=? WHERE category=?');
    $stmt2->bind_param('ss', $newName, $name);
    $stmt2->execute();

    json_out(['ok' => true]);
}

if ($method === 'DELETE') {
    // Check if any menu items still use this category
    $ck = db()->prepare('SELECT COUNT(*) as cnt FROM menu_items WHERE category=?');
    $ck->bind_param('s', $name);
    $ck->execute();
    $cnt = $ck->get_result()->fetch_assoc()['cnt'];
    if ($cnt > 0) json_out(['error' => 'category in use'], 409);

    $stmt = db()->prepare('DELETE FROM menu_categories WHERE name=?');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    json_out(['ok' => true]);
}

json_out(['error' => 'Method not allowed'], 405);
