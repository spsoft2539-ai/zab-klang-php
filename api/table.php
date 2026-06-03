<?php
// GET/PATCH /api/table.php?id=A1
require_once __DIR__ . '/../db.php';

$id     = trim($_GET['id'] ?? '');
$method = $_SERVER['REQUEST_METHOD'];

if (!$id) json_out(['error' => 'id required'], 400);

if ($method === 'GET') {
    $t = get_table($id);
    if (!$t) json_out(['error' => 'not found'], 404);
    json_out(format_table($t));
}

if ($method === 'PATCH') {
    $body   = json_body();
    $action = $body['action'] ?? null;

    // Open table
    if ($action === 'open') {
        $t = get_table($id);
        if (!$t || $t['status'] !== 'available') json_out(['error' => 'cannot open'], 400);
        $guests = (int)($body['guests'] ?? 1);
        $time   = thai_time();
        $stmt   = db()->prepare(
            "UPDATE restaurant_tables SET status='active', opened_at=?, guests=? WHERE id=?"
        );
        $stmt->bind_param('sis', $time, $guests, $id);
        $stmt->execute();
        json_out(format_table(get_table($id)));
    }

    // Close table (generates bill)
    if ($action === 'close') {
        $pm  = $body['paymentMethod'] ?? null;
        $cr  = isset($body['cashReceived']) ? (float)$body['cashReceived'] : null;
        close_table($id, $pm, $cr);
        json_out(['ok' => true]);
    }

    // Change status
    if (isset($body['status'])) {
        $valid = ['available', 'active', 'preparing', 'billing'];
        $st    = $body['status'];
        if (!in_array($st, $valid)) json_out(['error' => 'invalid status'], 400);
        $stmt = db()->prepare('UPDATE restaurant_tables SET status=? WHERE id=?');
        $stmt->bind_param('ss', $st, $id);
        $stmt->execute();
        $t = get_table($id);
        if (!$t) json_out(['error' => 'not found'], 404);
        json_out(format_table($t));
    }

    json_out(['error' => 'invalid body'], 400);
}

// Settings: add table
if ($method === 'POST') {
    $body  = json_body();
    $newId = strtoupper(trim($body['id'] ?? ''));
    $zone  = strtoupper(trim($body['zone'] ?? ''));
    $seats = (int)($body['seats'] ?? 2);
    if (!$newId || !$zone || $seats < 1) json_out(['error' => 'invalid'], 400);

    $existing = get_table($newId);
    if ($existing) json_out(['error' => 'duplicate id'], 409);

    $stmt = db()->prepare('INSERT INTO restaurant_tables (id,zone,seats,status) VALUES (?,?,?,\'available\')');
    $stmt->bind_param('ssi', $newId, $zone, $seats);
    $stmt->execute();
    json_out(format_table(get_table($newId)), 201);
}

// Settings: update table config
if ($method === 'PUT') {
    $body  = json_body();
    $seats = isset($body['seats']) ? (int)$body['seats'] : null;
    $zone  = $body['zone'] ?? null;

    $t = get_table($id);
    if (!$t) json_out(['error' => 'not found'], 404);

    $nz = $zone ?? $t['zone'];
    $ns = $seats ?? (int)$t['seats'];
    $stmt = db()->prepare('UPDATE restaurant_tables SET zone=?, seats=? WHERE id=?');
    $stmt->bind_param('sis', $nz, $ns, $id);
    $stmt->execute();
    json_out(format_table(get_table($id)));
}

// Settings: delete table
if ($method === 'DELETE') {
    $t = get_table($id);
    if (!$t) json_out(['error' => 'not found'], 404);
    if ($t['status'] !== 'available') json_out(['error' => 'table is in use'], 409);
    $stmt = db()->prepare('DELETE FROM restaurant_tables WHERE id=?');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    json_out(['ok' => true]);
}

json_out(['error' => 'Method not allowed'], 405);
