<?php
// PATCH /api/order_item.php  body:{orderId,menuId,quantity}  — update qty (0=delete item)
// DELETE /api/order_item.php  body:{orderId,menuId}           — remove item
require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$body   = json_body();

$orderId = trim($body['orderId'] ?? '');
$menuId  = trim($body['menuId']  ?? '');

if (!$orderId || !$menuId) json_out(['error' => 'orderId and menuId required'], 400);

// Verify order exists
$stmt = db()->prepare('SELECT id FROM orders WHERE id = ?');
$stmt->bind_param('s', $orderId);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) json_out(['error' => 'order not found'], 404);

if ($method === 'PATCH') {
    $qty = (int)($body['quantity'] ?? 0);
    if ($qty <= 0) {
        $s = db()->prepare('DELETE FROM order_items WHERE order_id = ? AND menu_id = ?');
        $s->bind_param('ss', $orderId, $menuId);
        $s->execute();
    } else {
        $s = db()->prepare('UPDATE order_items SET quantity = ? WHERE order_id = ? AND menu_id = ?');
        $s->bind_param('iss', $qty, $orderId, $menuId);
        $s->execute();
    }
    // Clean up order if now empty
    $c = db()->prepare('SELECT COUNT(*) cnt FROM order_items WHERE order_id = ?');
    $c->bind_param('s', $orderId);
    $c->execute();
    if ((int)$c->get_result()->fetch_assoc()['cnt'] === 0) {
        $d = db()->prepare('DELETE FROM orders WHERE id = ?');
        $d->bind_param('s', $orderId);
        $d->execute();
    }
    json_out(['ok' => true]);
}

if ($method === 'DELETE') {
    $s = db()->prepare('DELETE FROM order_items WHERE order_id = ? AND menu_id = ?');
    $s->bind_param('ss', $orderId, $menuId);
    $s->execute();
    // Clean up order if now empty
    $c = db()->prepare('SELECT COUNT(*) cnt FROM order_items WHERE order_id = ?');
    $c->bind_param('s', $orderId);
    $c->execute();
    if ((int)$c->get_result()->fetch_assoc()['cnt'] === 0) {
        $d = db()->prepare('DELETE FROM orders WHERE id = ?');
        $d->bind_param('s', $orderId);
        $d->execute();
    }
    json_out(['ok' => true]);
}

json_out(['error' => 'Method not allowed'], 405);
