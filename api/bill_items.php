<?php
// POST   /api/bill_items.php?bill_id=xxx    — add item to bill
// PATCH  /api/bill_items.php?id=xxx         — edit item
// DELETE /api/bill_items.php?id=xxx         — delete item
require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = trim($_GET['id']      ?? '');
$billId = trim($_GET['bill_id'] ?? '');

/* ── POST (add item) ─────────────────────────── */
if ($method === 'POST') {
    if (!$billId) json_out(['error' => 'bill_id required'], 400);

    // ตรวจว่าบิลมีอยู่
    $bs = db()->prepare('SELECT id,vat_rate FROM bills WHERE id=?');
    $bs->bind_param('s', $billId); $bs->execute();
    $bill = $bs->get_result()->fetch_assoc();
    if (!$bill) json_out(['error' => 'bill not found'], 404);

    $body   = json_body();
    $name   = trim($body['name']   ?? '');
    $price  = (float)($body['price']    ?? 0);
    $qty    = (int)($body['quantity']   ?? 1);
    $note   = trim($body['note']   ?? '') ?: null;
    $menuId = trim($body['menu_id']?? '') ?: null;

    if (!$name || $price <= 0) json_out(['error' => 'name and price required'], 400);

    $stmt = db()->prepare('INSERT INTO bill_items (bill_id,menu_id,name,price,quantity,note) VALUES (?,?,?,?,?,?)');
    $stmt->bind_param('sssdis', $billId, $menuId, $name, $price, $qty, $note);
    $stmt->execute();
    $newId = db()->insert_id;

    recalc_bill($billId);
    json_out(['id' => $newId, 'name' => $name, 'price' => $price, 'quantity' => $qty], 201);
}

/* ── PATCH (edit item) ───────────────────────── */
if ($method === 'PATCH') {
    if (!$id) json_out(['error' => 'id required'], 400);

    $s = db()->prepare('SELECT * FROM bill_items WHERE id=?');
    $s->bind_param('i', $id); $s->execute();
    $item = $s->get_result()->fetch_assoc();
    if (!$item) json_out(['error' => 'not found'], 404);

    $body  = json_body();
    $name  = $body['name']     ?? $item['name'];
    $price = isset($body['price'])    ? (float)$body['price']    : (float)$item['price'];
    $qty   = isset($body['quantity']) ? (int)$body['quantity']   : (int)$item['quantity'];
    $note  = array_key_exists('note', $body) ? ($body['note'] ?: null) : $item['note'];

    $stmt = db()->prepare('UPDATE bill_items SET name=?,price=?,quantity=?,note=? WHERE id=?');
    $stmt->bind_param('ssdsi', $name, $price, $qty, $note, $id);
    $stmt->execute();

    recalc_bill($item['bill_id']);
    json_out(['ok' => true]);
}

/* ── DELETE (remove item) ────────────────────── */
if ($method === 'DELETE') {
    if (!$id) json_out(['error' => 'id required'], 400);

    $s = db()->prepare('SELECT bill_id FROM bill_items WHERE id=?');
    $s->bind_param('i', $id); $s->execute();
    $row = $s->get_result()->fetch_assoc();
    if (!$row) json_out(['error' => 'not found'], 404);

    $stmt = db()->prepare('DELETE FROM bill_items WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    recalc_bill($row['bill_id']);
    json_out(['ok' => true]);
}

json_out(['error' => 'Method not allowed'], 405);

/* ── Helper: คำนวณยอดบิลใหม่ ─────────────────── */
function recalc_bill(string $billId): void {
    $bs = db()->prepare('SELECT vat_rate FROM bills WHERE id=?');
    $bs->bind_param('s', $billId); $bs->execute();
    $bill = $bs->get_result()->fetch_assoc();
    $vatRate = (float)($bill['vat_rate'] ?? 7);

    $is = db()->prepare('SELECT SUM(price*quantity) sub FROM bill_items WHERE bill_id=?');
    $is->bind_param('s', $billId); $is->execute();
    $sub = (float)($is->get_result()->fetch_assoc()['sub'] ?? 0);

    $vat   = round($sub * $vatRate / 100, 2);
    $total = $sub + $vat;
    $db    = db();
    $db->query("UPDATE bills SET subtotal=$sub, vat=$vat, total=$total WHERE id='".$db->real_escape_string($billId)."'");
}
