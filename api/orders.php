<?php
// GET /api/orders.php?since=0&tableId=A1
// POST /api/orders.php  body: {tableId, items:[{menuId,name,price,quantity,note}]}
require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $since   = (int)($_GET['since']   ?? 0);
    $tableId = $_GET['tableId'] ?? null;

    $sql    = 'SELECT * FROM orders WHERE 1=1';
    $params = [];
    $types  = '';

    if ($since > 0) { $sql .= ' AND created_at > ?'; $types .= 'i'; $params[] = $since; }
    if ($tableId)   { $sql .= ' AND table_id = ?';   $types .= 's'; $params[] = $tableId; }
    $sql .= ' ORDER BY created_at ASC';

    $stmt = db()->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    json_out(array_map('format_order', $rows));
}

if ($method === 'POST') {
    $body    = json_body();
    $tableId = $body['tableId'] ?? '';
    $items   = $body['items']   ?? [];

    if (!$tableId || !is_array($items) || empty($items)) {
        json_out(['error' => 'invalid order'], 400);
    }

    $now_ms = now_ms();
    $time   = thai_time();
    $oid    = 'ORD-' . $now_ms;

    // Insert order
    $stmt = db()->prepare(
        'INSERT INTO orders (id, table_id, ordered_at, created_at, printed) VALUES (?,?,?,?,0)'
    );
    $stmt->bind_param('sssi', $oid, $tableId, $time, $now_ms);
    $stmt->execute();

    // Insert order items
    $istmt = db()->prepare(
        'INSERT INTO order_items (order_id, menu_id, name, price, quantity, note) VALUES (?,?,?,?,?,?)'
    );
    foreach ($items as $item) {
        $menuId = $item['menuId'] ?? null;
        $name   = $item['name']   ?? '';
        $price  = (float)($item['price']    ?? 0);
        $qty    = (int)($item['quantity']   ?? 1);
        $note   = $item['note'] ?? null;
        $istmt->bind_param('sssdis', $oid, $menuId, $name, $price, $qty, $note);
        $istmt->execute();
    }

    // Auto-promote table status
    $t = get_table($tableId);
    if ($t && in_array($t['status'], ['active', 'available'])) {
        $ot  = $t['opened_at'] ?? $time;
        $gst = $t['guests']    ?? 1;
        $stmt2 = db()->prepare(
            "UPDATE restaurant_tables SET status='preparing', opened_at=?, guests=? WHERE id=?"
        );
        $stmt2->bind_param('sis', $ot, $gst, $tableId);
        $stmt2->execute();
    }

    $stmt3 = db()->prepare('SELECT * FROM orders WHERE id=?');
    $stmt3->bind_param('s', $oid);
    $stmt3->execute();
    $row = $stmt3->get_result()->fetch_assoc();
    json_out(format_order($row), 201);
}

json_out(['error' => 'Method not allowed'], 405);
