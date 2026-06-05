<?php
// GET    /api/bills.php            — list bills
// PATCH  /api/bills.php?id=xxx     — edit bill
// DELETE /api/bills.php?id=xxx     — delete bill
require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = trim($_GET['id'] ?? '');

/* ── DELETE ──────────────────────────────────── */
if ($method === 'DELETE') {
    if (!$id) json_out(['error' => 'id required'], 400);
    $stmt = db()->prepare('DELETE FROM bills WHERE id=?');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    if ($stmt->affected_rows === 0) json_out(['error' => 'not found'], 404);
    json_out(['ok' => true]);
}

/* ── PATCH (edit) ────────────────────────────── */
if ($method === 'PATCH') {
    if (!$id) json_out(['error' => 'id required'], 400);
    $body = json_body();

    // ดึงบิลเดิม
    $s = db()->prepare('SELECT * FROM bills WHERE id=?');
    $s->bind_param('s', $id);
    $s->execute();
    $bill = $s->get_result()->fetch_assoc();
    if (!$bill) json_out(['error' => 'not found'], 404);

    $pm       = $body['payment_method'] ?? $bill['payment_method'];
    $tableId  = $body['table_id']       ?? $bill['table_id'];
    $cr       = isset($body['cash_received']) ? (float)$body['cash_received'] : $bill['cash_received'];
    $change   = isset($body['change_amt'])    ? (float)$body['change_amt']    : $bill['change_amt'];
    $guests   = isset($body['guests'])        ? (int)$body['guests']          : $bill['guests'];

    // คำนวณเงินทอนใหม่ถ้าเป็นเงินสด
    if ($pm === 'cash' && $cr !== null) {
        $change = $cr - (float)$bill['total'];
        if ($change < 0) $change = 0;
    }
    if ($pm === 'transfer') { $cr = null; $change = null; }

    $stmt = db()->prepare(
        'UPDATE bills SET payment_method=?, table_id=?, cash_received=?, change_amt=?, guests=? WHERE id=?'
    );
    $stmt->bind_param('ssddis', $pm, $tableId, $cr, $change, $guests, $id);
    $stmt->execute();
    json_out(['ok' => true]);
}

if ($method !== 'GET') json_out(['error' => 'Method not allowed'], 405);

$today = !empty($_GET['today']);
$since = (int)($_GET['since'] ?? 0);

$sql    = 'SELECT * FROM bills WHERE 1=1';
$params = [];
$types  = '';

if ($today) {
    $startOfDay = strtotime('today midnight') * 1000;
    $sql    .= ' AND closed_at_ms >= ?';
    $types  .= 'i';
    $params[] = $startOfDay;
} elseif ($since > 0) {
    $sql    .= ' AND closed_at_ms > ?';
    $types  .= 'i';
    $params[] = $since;
}
$sql .= ' ORDER BY closed_at_ms DESC';

$stmt = db()->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$bills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Load bill items
$result = [];
foreach ($bills as $bill) {
    $istmt = db()->prepare('SELECT * FROM bill_items WHERE bill_id=?');
    $istmt->bind_param('s', $bill['id']);
    $istmt->execute();
    $items = $istmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $result[] = [
        'id'            => $bill['id'],
        'tableId'       => $bill['table_id'],
        'closedAt'      => $bill['closed_at'],
        'closedAtMs'    => (int)$bill['closed_at_ms'],
        'items'         => array_map(fn($i) => [
            'menuId'   => $i['menu_id'],
            'name'     => $i['name'],
            'price'    => (float)$i['price'],
            'quantity' => (int)$i['quantity'],
            'note'     => $i['note'],
        ], $items),
        'subtotal'       => (float)$bill['subtotal'],
        'vatRate'        => (float)$bill['vat_rate'],
        'vat'            => (float)$bill['vat'],
        'serviceCharge'  => (float)$bill['service_charge'],
        'serviceAmt'     => (float)$bill['service_amt'],
        'total'          => (float)$bill['total'],
        'guests'         => $bill['guests'] !== null ? (int)$bill['guests'] : null,
        'paymentMethod'  => $bill['payment_method'],
        'cashReceived'   => $bill['cash_received'] !== null ? (float)$bill['cash_received'] : null,
        'change'         => $bill['change_amt'] !== null ? (float)$bill['change_amt'] : null,
    ];
}

json_out($result);
