<?php
// GET /api/menu.php
// POST /api/menu.php  body: {name,description,price,category,tag,image}
require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $rows = db()->query('SELECT * FROM menu_items ORDER BY category, name')->fetch_all(MYSQLI_ASSOC);
    json_out(array_map(fn($r) => [
        'id'          => $r['id'],
        'name'        => $r['name'],
        'description' => $r['description'] ?? '',
        'price'       => (float)$r['price'],
        'category'    => $r['category'],
        'tag'         => $r['tag'],
        'image'       => $r['image'] ?? '',
    ], $rows));
}

if ($method === 'POST') {
    $body = json_body();
    $id   = 'menu-' . now_ms();
    $name = trim($body['name'] ?? '');
    $desc = trim($body['description'] ?? '');
    $price = (float)($body['price'] ?? 0);
    $cat   = trim($body['category'] ?? '');
    $tag   = $body['tag'] ?? null;
    $img   = $body['image'] ?? '';

    if (!$name || !$cat || $price <= 0) json_out(['error' => 'invalid item'], 400);

    $stmt = db()->prepare(
        'INSERT INTO menu_items (id,name,description,price,category,tag,image) VALUES (?,?,?,?,?,?,?)'
    );
    $stmt->bind_param('sssdsss', $id, $name, $desc, $price, $cat, $tag, $img);
    $stmt->execute();

    // Auto-register category
    $cs = db()->prepare('INSERT IGNORE INTO menu_categories (name, sort_order) VALUES (?, 99)');
    $cs->bind_param('s', $cat);
    $cs->execute();

    json_out([
        'id' => $id, 'name' => $name, 'description' => $desc,
        'price' => $price, 'category' => $cat, 'tag' => $tag, 'image' => $img,
    ], 201);
}

json_out(['error' => 'Method not allowed'], 405);
