<?php
// GET /api/bills.php?today=1&since=0
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_out(['error' => 'Method not allowed'], 405);

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
