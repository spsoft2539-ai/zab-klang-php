<?php
// PATCH /api/order_printed.php?id=ORD-xxx
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') json_out(['error' => 'Method not allowed'], 405);

$id = trim($_GET['id'] ?? '');
if (!$id) json_out(['error' => 'id required'], 400);

$stmt = db()->prepare('UPDATE orders SET printed=1 WHERE id=?');
$stmt->bind_param('s', $id);
$stmt->execute();
json_out(['ok' => true]);
