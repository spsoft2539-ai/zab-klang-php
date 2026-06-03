<?php
// GET /api/settings.php
// PATCH /api/settings.php  body: partial settings object
require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    json_out(get_all_settings());
}

if ($method === 'PATCH') {
    $body = json_body();
    $allowed = ['restaurantName','cuisine','openTime','closeTime','vatRate','serviceCharge'];
    foreach ($body as $k => $v) {
        if (!in_array($k, $allowed)) continue;
        $stmt = db()->prepare('INSERT INTO app_settings (key_name,value) VALUES (?,?) ON DUPLICATE KEY UPDATE value=?');
        $val = (string)$v;
        $stmt->bind_param('sss', $k, $val, $val);
        $stmt->execute();
    }
    json_out(get_all_settings());
}

json_out(['error' => 'Method not allowed'], 405);
