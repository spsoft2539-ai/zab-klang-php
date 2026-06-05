<?php
// GET    /api/expenses.php?from=DATE&to=DATE  — list
// POST   /api/expenses.php                   — create
// PATCH  /api/expenses.php?id=xxx            — update
// DELETE /api/expenses.php?id=xxx            — delete
require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = trim($_GET['id'] ?? '');

/* ── GET ─────────────────────────────────────── */
if ($method === 'GET') {
    $from = $_GET['from'] ?? null;   // DATE string yyyy-mm-dd
    $to   = $_GET['to']   ?? null;

    if ($from && $to) {
        $stmt = db()->prepare(
            'SELECT * FROM expenses WHERE date BETWEEN ? AND ? ORDER BY date DESC, created_at DESC'
        );
        $stmt->bind_param('ss', $from, $to);
    } else {
        $stmt = db()->prepare('SELECT * FROM expenses ORDER BY date DESC, created_at DESC');
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    json_out(array_map(fn($r) => [
        'id'          => $r['id'],
        'date'        => $r['date'],
        'category'    => $r['category'],
        'description' => $r['description'],
        'amount'      => (float)$r['amount'],
        'note'        => $r['note'],
        'created_at'  => (int)$r['created_at'],
    ], $rows));
}

/* ── POST (create) ───────────────────────────── */
if ($method === 'POST') {
    $body = json_body();
    $date = trim($body['date'] ?? date('Y-m-d'));
    $cat  = trim($body['category'] ?? '');
    $desc = trim($body['description'] ?? '');
    $amt  = (float)($body['amount'] ?? 0);
    $note = trim($body['note'] ?? '') ?: null;

    if (!$cat || !$desc || $amt <= 0) json_out(['error' => 'category, description, amount required'], 400);

    $newId = 'exp-' . now_ms();
    $now   = now_ms();
    $stmt  = db()->prepare(
        'INSERT INTO expenses (id,date,category,description,amount,note,created_at) VALUES (?,?,?,?,?,?,?)'
    );
    $stmt->bind_param('ssssdsi', $newId, $date, $cat, $desc, $amt, $note, $now);
    $stmt->execute();
    json_out(['id' => $newId, 'date' => $date, 'category' => $cat,
              'description' => $desc, 'amount' => $amt, 'note' => $note], 201);
}

/* ── PATCH (update) ──────────────────────────── */
if ($method === 'PATCH') {
    if (!$id) json_out(['error' => 'id required'], 400);
    $body = json_body();

    $s = db()->prepare('SELECT * FROM expenses WHERE id=?');
    $s->bind_param('s', $id); $s->execute();
    $row = $s->get_result()->fetch_assoc();
    if (!$row) json_out(['error' => 'not found'], 404);

    $date = $body['date']        ?? $row['date'];
    $cat  = $body['category']    ?? $row['category'];
    $desc = $body['description'] ?? $row['description'];
    $amt  = isset($body['amount']) ? (float)$body['amount'] : (float)$row['amount'];
    $note = array_key_exists('note', $body) ? ($body['note'] ?: null) : $row['note'];

    $stmt = db()->prepare(
        'UPDATE expenses SET date=?,category=?,description=?,amount=?,note=? WHERE id=?'
    );
    $stmt->bind_param('sssdss', $date, $cat, $desc, $amt, $note, $id);
    $stmt->execute();
    json_out(['ok' => true]);
}

/* ── DELETE ──────────────────────────────────── */
if ($method === 'DELETE') {
    if (!$id) json_out(['error' => 'id required'], 400);
    $stmt = db()->prepare('DELETE FROM expenses WHERE id=?');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    if ($stmt->affected_rows === 0) json_out(['error' => 'not found'], 404);
    json_out(['ok' => true]);
}

json_out(['error' => 'Method not allowed'], 405);
