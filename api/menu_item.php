<?php
// PATCH/DELETE /api/menu_item.php?id=xxx
require_once __DIR__ . '/../db.php';

$id     = trim($_GET['id'] ?? '');
$method = $_SERVER['REQUEST_METHOD'];

if (!$id) json_out(['error' => 'id required'], 400);

if ($method === 'PATCH') {
    $body  = json_body();
    $stmt0 = db()->prepare('SELECT * FROM menu_items WHERE id=?');
    $stmt0->bind_param('s', $id);
    $stmt0->execute();
    $item = $stmt0->get_result()->fetch_assoc();
    if (!$item) json_out(['error' => 'not found'], 404);

    $name  = $body['name']        ?? $item['name'];
    $desc  = $body['description'] ?? $item['description'];
    $price = isset($body['price']) ? (float)$body['price'] : (float)$item['price'];
    $cat   = $body['category']    ?? $item['category'];
    $tag   = array_key_exists('tag', $body) ? $body['tag'] : $item['tag'];
    $img   = $body['image']       ?? $item['image'];
    $avail = array_key_exists('is_available', $body) ? (int)(bool)$body['is_available'] : (int)($item['is_available'] ?? 1);

    $stmt = db()->prepare(
        'UPDATE menu_items SET name=?,description=?,price=?,category=?,tag=?,image=?,is_available=? WHERE id=?'
    );
    $stmt->bind_param('ssdsssis', $name, $desc, $price, $cat, $tag, $img, $avail, $id);
    $stmt->execute();
    json_out(['id'=>$id,'name'=>$name,'description'=>$desc,'price'=>$price,'category'=>$cat,'tag'=>$tag,'image'=>$img,'is_available'=>(bool)$avail]);
}

if ($method === 'DELETE') {
    $stmt = db()->prepare('DELETE FROM menu_items WHERE id=?');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    json_out(['ok' => true]);
}

json_out(['error' => 'Method not allowed'], 405);
