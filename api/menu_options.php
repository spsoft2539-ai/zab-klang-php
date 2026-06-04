<?php
// GET    /api/menu_options.php?menu_id=xxx  — list options for one item
// POST   /api/menu_options.php              — add option  body:{menuId,groupName,optName,price,required,sortOrder}
// DELETE /api/menu_options.php?id=opt-xxx  — remove option
require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $menuId = trim($_GET['menu_id'] ?? '');
    if (!$menuId) json_out(['error' => 'menu_id required'], 400);
    json_out(get_menu_options($menuId));
}

if ($method === 'POST') {
    $body     = json_body();
    $menuId   = trim($body['menuId']    ?? '');
    $group    = trim($body['groupName'] ?? '');
    $opt      = trim($body['optName']   ?? '');
    $price    = (float)($body['price']     ?? 0);
    $required = (int)($body['required']   ?? 0);
    $sort     = (int)($body['sortOrder']  ?? 0);

    if (!$menuId || !$group || !$opt) json_out(['error' => 'menuId, groupName, optName required'], 400);

    $id   = 'opt-' . now_ms();
    $stmt = db()->prepare(
        'INSERT INTO menu_options (id,menu_id,group_name,opt_name,price,required,sort_order)
         VALUES (?,?,?,?,?,?,?)'
    );
    $stmt->bind_param('ssssdii', $id, $menuId, $group, $opt, $price, $required, $sort);
    $stmt->execute();
    json_out(['id' => $id, 'menuId' => $menuId, 'groupName' => $group,
              'optName' => $opt, 'price' => $price, 'required' => $required], 201);
}

if ($method === 'PATCH') {
    $id   = trim($_GET['id'] ?? '');
    if (!$id) json_out(['error' => 'id required'], 400);
    $body   = json_body();
    $fields = []; $types = ''; $params = [];
    if (array_key_exists('groupName', $body)) { $fields[]='group_name=?'; $types.='s'; $params[]=trim($body['groupName']); }
    if (array_key_exists('optName',   $body)) { $fields[]='opt_name=?';   $types.='s'; $params[]=trim($body['optName']); }
    if (array_key_exists('price',     $body)) { $fields[]='price=?';      $types.='d'; $params[]=(float)$body['price']; }
    if (array_key_exists('required',  $body)) { $fields[]='required=?';   $types.='i'; $params[]=(int)$body['required']; }
    if (!$fields) json_out(['error' => 'nothing to update'], 400);
    $params[] = $id; $types .= 's';
    $stmt = db()->prepare('UPDATE menu_options SET '.implode(',',$fields).' WHERE id=?');
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    json_out(['ok' => true]);
}

if ($method === 'DELETE') {
    $id = trim($_GET['id'] ?? '');
    if (!$id) json_out(['error' => 'id required'], 400);
    $stmt = db()->prepare('DELETE FROM menu_options WHERE id = ?');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    json_out(['ok' => true]);
}

json_out(['error' => 'Method not allowed'], 405);
