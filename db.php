<?php
// ─── Database Configuration ──────────────────────────────────
// แก้ค่าด้านล่างให้ตรงกับ MySQL ของคุณ
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'zab');
define('DB_PORT', 3306);

// ─── Connection Singleton ────────────────────────────────────
function db(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        $conn->set_charset('utf8mb4');
        if ($conn->connect_error) {
            http_response_code(500);
            die(json_encode(['error' => 'DB connection failed: ' . $conn->connect_error]));
        }
    }
    return $conn;
}

// ─── Helpers ─────────────────────────────────────────────────

/** Return current time as "HH:MM" in Asia/Bangkok */
function thai_time(): string {
    $dt = new DateTime('now', new DateTimeZone('Asia/Bangkok'));
    return $dt->format('H:i');
}

/** Return current unix timestamp in milliseconds */
function now_ms(): int {
    return (int)(microtime(true) * 1000);
}

/** Send JSON response and exit */
function json_out(mixed $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Get JSON body from request */
function json_body(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

/** Get a setting value */
function get_setting(string $key, string $default = ''): string {
    $stmt = db()->prepare('SELECT value FROM app_settings WHERE key_name = ?');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return $res ? $res['value'] : $default;
}

/** Fetch all settings as associative array */
function get_all_settings(): array {
    $rows = db()->query('SELECT key_name, value FROM app_settings')->fetch_all(MYSQLI_ASSOC);
    $out = [];
    foreach ($rows as $r) $out[$r['key_name']] = $r['value'];
    return [
        'restaurantName'  => $out['restaurantName']  ?? 'แซ่บกลางซอย',
        'cuisine'         => $out['cuisine']          ?? 'อีสาน · ซีฟู้ด · หมูกระทะ',
        'openTime'        => $out['openTime']         ?? '11:00',
        'closeTime'       => $out['closeTime']        ?? '22:00',
        'vatRate'         => (float)($out['vatRate']  ?? 7),
        'serviceCharge'   => (float)($out['serviceCharge'] ?? 0),
    ];
}

/** Fetch a table row as array */
function get_table(string $id): ?array {
    $stmt = db()->prepare('SELECT * FROM restaurant_tables WHERE id = ?');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
}

/** Fetch all order items for an order */
function get_order_items(string $order_id): array {
    $stmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $stmt->bind_param('s', $order_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    return array_map(fn($r) => [
        'menuId'   => $r['menu_id'],
        'name'     => $r['name'],
        'price'    => (float)$r['price'],
        'quantity' => (int)$r['quantity'],
        'note'     => $r['note'],
    ], $rows);
}

/** Format a table row as API object */
function format_table(array $r): array {
    return [
        'id'       => $r['id'],
        'zone'     => $r['zone'],
        'seats'    => (int)$r['seats'],
        'status'   => $r['status'],
        'openedAt' => $r['opened_at'],
        'guests'   => $r['guests'] !== null ? (int)$r['guests'] : null,
    ];
}

/** Format an order row as API object */
function format_order(array $r): array {
    return [
        'id'        => $r['id'],
        'tableId'   => $r['table_id'],
        'items'     => get_order_items($r['id']),
        'orderedAt' => $r['ordered_at'],
        'createdAt' => (int)$r['created_at'],
        'printed'   => (bool)$r['printed'],
    ];
}

/** Close a table: create bill, clear orders, reset table */
function close_table(string $id, ?string $payment_method, ?float $cash_received): bool {
    $table = get_table($id);
    if (!$table) return false;

    // Collect all items from all orders
    $stmt = db()->prepare(
        'SELECT oi.* FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.table_id = ?'
    );
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $all_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (!empty($all_items)) {
        $settings = get_all_settings();
        $subtotal = array_sum(array_map(fn($i) => (float)$i['price'] * (int)$i['quantity'], $all_items));
        $vat_rate = (float)$settings['vatRate'];
        $svc_rate = (float)$settings['serviceCharge'];
        $vat      = round($subtotal * ($vat_rate / 100));
        $svc_amt  = round($subtotal * ($svc_rate / 100));
        $total    = $subtotal + $vat + $svc_amt;
        $now_ms   = now_ms();
        $bill_id  = 'BILL-' . $now_ms;
        $closed   = thai_time();
        $guests   = $table['guests'];

        $change = null;
        if ($payment_method === 'cash' && $cash_received !== null) {
            $change = max(0, $cash_received - $total);
        }

        // Insert bill
        $stmt2 = db()->prepare(
            'INSERT INTO bills (id,table_id,closed_at,closed_at_ms,subtotal,vat_rate,vat,service_charge,service_amt,total,guests,payment_method,cash_received,change_amt)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt2->bind_param('sssiidddddisdd',
            $bill_id, $id, $closed, $now_ms,
            $subtotal, $vat_rate, $vat, $svc_rate, $svc_amt, $total,
            $guests, $payment_method, $cash_received, $change
        );
        $stmt2->execute();

        // Insert bill items
        $istmt = db()->prepare(
            'INSERT INTO bill_items (bill_id,menu_id,name,price,quantity,note) VALUES (?,?,?,?,?,?)'
        );
        foreach ($all_items as $item) {
            $istmt->bind_param('sssdis',
                $bill_id, $item['menu_id'], $item['name'],
                $item['price'], $item['quantity'], $item['note']
            );
            $istmt->execute();
        }
    }

    // Delete orders for this table
    $d = db()->prepare('DELETE FROM orders WHERE table_id = ?');
    $d->bind_param('s', $id);
    $d->execute();

    // Reset table to available
    $u = db()->prepare(
        "UPDATE restaurant_tables SET status='available', opened_at=NULL, guests=NULL WHERE id=?"
    );
    $u->bind_param('s', $id);
    $u->execute();

    return true;
}
