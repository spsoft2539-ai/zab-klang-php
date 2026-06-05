<?php
// GET    /api/bills.php            — list bills
// POST   /api/bills.php            — create manual bill
// PATCH  /api/bills.php?id=xxx     — edit bill metadata
// DELETE /api/bills.php?id=xxx     — delete bill
require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = trim($_GET['id'] ?? '');

/* ── POST (create manual bill) ───────────────── */
if ($method === 'POST') {
    $body    = json_body();
    $tableId = trim($body['table_id'] ?? 'manual');
    $pm      = trim($body['payment_method'] ?? 'cash');
    $guests  = (int)($body['guests'] ?? 1);
    $cr      = isset($body['cash_received']) ? (float)$body['cash_received'] : null;
    $items   = $body['items'] ?? [];
    $vatRate = 7.0;

    if (empty($items)) json_out(['error' => 'กรุณาเพิ่มรายการอาหารอย่างน้อย 1 รายการ'], 400);

    // คำนวณยอด
    $subtotal = array_reduce($items, fn($s, $i) => $s + ((float)($i['price']??0) * (int)($i['quantity']??1)), 0.0);
    $vat      = round($subtotal * $vatRate / 100, 2);
    $total    = $subtotal + $vat;
    $change   = ($pm === 'cash' && $cr !== null) ? max(0, $cr - $total) : null;
    if ($pm === 'transfer') { $cr = null; $change = null; }

    $now    = now_ms();
    $billId = 'BILL-' . $now;
    $time   = thai_time();
    $db     = db();

    $crStr     = $cr     !== null ? $cr     : 'NULL';
    $changeStr = $change !== null ? $change : 'NULL';

    $db->query("INSERT INTO bills
        (id,table_id,closed_at,closed_at_ms,subtotal,vat_rate,vat,service_charge,service_amt,total,guests,payment_method,cash_received,change_amt)
        VALUES (
          '".$db->real_escape_string($billId)."',
          '".$db->real_escape_string($tableId)."',
          '".$db->real_escape_string($time)."',
          $now, $subtotal, $vatRate, $vat, 0, 0, $total, $guests,
          '".$db->real_escape_string($pm)."',
          $crStr, $changeStr
        )");

    if ($db->affected_rows === 0) json_out(['error' => 'บันทึกบิลไม่สำเร็จ: '.$db->error], 500);

    // Insert items
    foreach ($items as $item) {
        $name   = trim($item['name'] ?? '');
        $price  = (float)($item['price'] ?? 0);
        $qty    = (int)($item['quantity'] ?? 1);
        $note   = trim($item['note'] ?? '') ?: null;
        $menuId = trim($item['menu_id'] ?? '') ?: null;
        if (!$name || $price <= 0) continue;
        $is = $db->prepare('INSERT INTO bill_items (bill_id,menu_id,name,price,quantity,note) VALUES (?,?,?,?,?,?)');
        $is->bind_param('sssdis', $billId, $menuId, $name, $price, $qty, $note);
        $is->execute();
    }

    json_out(['id' => $billId, 'total' => $total], 201);
}

/* ── DELETE ──────────────────────────────────── */
if ($method === 'DELETE') {
    if (!$id) json_out(['error' => 'id required'], 400);
    $stmt = db()->prepare('DELETE FROM bills WHERE id=?');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    if ($stmt->affected_rows === 0) json_out(['error' => 'not found'], 404);
    json_out(['ok' => true]);
}

/* ── PATCH (edit metadata) ────────────────────── */
if ($method === 'PATCH') {
    if (!$id) json_out(['error' => 'id required'], 400);
    $body = json_body();

    $s = db()->prepare('SELECT * FROM bills WHERE id=?');
    $s->bind_param('s', $id); $s->execute();
    $bill = $s->get_result()->fetch_assoc();
    if (!$bill) json_out(['error' => 'not found'], 404);

    $pm      = $body['payment_method'] ?? $bill['payment_method'];
    $tableId = $body['table_id']       ?? $bill['table_id'];
    $cr      = isset($body['cash_received']) ? (float)$body['cash_received'] : (float)$bill['cash_received'];
    $change  = isset($body['change_amt'])    ? (float)$body['change_amt']    : (float)$bill['change_amt'];
    $guests  = isset($body['guests'])        ? (int)$body['guests']          : (int)$bill['guests'];

    if ($pm === 'cash' && $cr) {
        $change = max(0, $cr - (float)$bill['total']);
    }
    if ($pm === 'transfer') { $cr = null; $change = null; }

    // recalculate if items changed
    if (isset($body['recalc']) && $body['recalc']) {
        $is2 = db()->prepare('SELECT SUM(price*quantity) subtotal FROM bill_items WHERE bill_id=?');
        $is2->bind_param('s', $id); $is2->execute();
        $row = $is2->get_result()->fetch_assoc();
        $newSub = (float)($row['subtotal'] ?? 0);
        $newVat = round($newSub * (float)$bill['vat_rate'] / 100, 2);
        $newTotal = $newSub + $newVat;
        $change = ($pm==='cash' && $cr) ? max(0, $cr - $newTotal) : null;
        $db2 = db();
        $db2->query("UPDATE bills SET subtotal=$newSub, vat=$newVat, total=$newTotal WHERE id='".$db2->real_escape_string($id)."'");
    }

    $stmt = db()->prepare('UPDATE bills SET payment_method=?,table_id=?,cash_received=?,change_amt=?,guests=? WHERE id=?');
    $stmt->bind_param('ssddis', $pm, $tableId, $cr, $change, $guests, $id);
    $stmt->execute();
    json_out(['ok' => true]);
}

if ($method !== 'GET') json_out(['error' => 'Method not allowed'], 405);

/* ── GET ─────────────────────────────────────── */
$today = !empty($_GET['today']);
$since = (int)($_GET['since'] ?? 0);
$sql   = 'SELECT * FROM bills WHERE 1=1';
$params = []; $types = '';

if ($today) {
    $startOfDay = strtotime('today midnight') * 1000;
    $sql .= ' AND closed_at_ms >= ?'; $types .= 'i'; $params[] = $startOfDay;
} elseif ($since > 0) {
    $sql .= ' AND closed_at_ms > ?'; $types .= 'i'; $params[] = $since;
}
$sql .= ' ORDER BY closed_at_ms DESC';

$stmt = db()->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$bills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$result = [];
foreach ($bills as $bill) {
    $istmt = db()->prepare('SELECT * FROM bill_items WHERE bill_id=? ORDER BY id');
    $istmt->bind_param('s', $bill['id']); $istmt->execute();
    $items = $istmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $result[] = [
        'id'            => $bill['id'],
        'tableId'       => $bill['table_id'],
        'closedAt'      => $bill['closed_at'],
        'closedAtMs'    => (int)$bill['closed_at_ms'],
        'items'         => array_map(fn($i) => [
            'itemId'   => (int)$i['id'],
            'menuId'   => $i['menu_id'],
            'name'     => $i['name'],
            'price'    => (float)$i['price'],
            'quantity' => (int)$i['quantity'],
            'note'     => $i['note'],
        ], $items),
        'subtotal'      => (float)$bill['subtotal'],
        'vatRate'       => (float)$bill['vat_rate'],
        'vat'           => (float)$bill['vat'],
        'serviceCharge' => (float)$bill['service_charge'],
        'serviceAmt'    => (float)$bill['service_amt'],
        'total'         => (float)$bill['total'],
        'guests'        => $bill['guests'] !== null ? (int)$bill['guests'] : null,
        'paymentMethod' => $bill['payment_method'],
        'cashReceived'  => $bill['cash_received'] !== null ? (float)$bill['cash_received'] : null,
        'change'        => $bill['change_amt']    !== null ? (float)$bill['change_amt']    : null,
    ];
}
json_out($result);
