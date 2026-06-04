<?php
// ─── Routing ─────────────────────────────────────────────
// GET    ?menu_id=X            — list all options grouped
// POST   body{menuId,groupName,optName,price,required}   — add option
// PATCH  ?id=X    body{...}    — update single option
// PATCH  ?menu_id=X&group=Y   body{newGroupName,required} — rename / toggle group
// DELETE ?id=X                — delete single option
// DELETE ?menu_id=X&group=Y   — delete entire group
require_once __DIR__ . '/../db.php';

$method  = $_SERVER['REQUEST_METHOD'];
$id      = trim($_GET['id']      ?? '');
$menuId  = trim($_GET['menu_id'] ?? '');
$group   = $_GET['group']        ?? null;   // null = not a group operation

/* ── GET ──────────────────────────────────────────────── */
if ($method === 'GET') {
    if (!$menuId) json_out(['error' => 'menu_id required'], 400);
    json_out(get_menu_options($menuId));
}

/* ── POST  (add single option) ────────────────────────── */
if ($method === 'POST') {
    $body     = json_body();
    $mId      = trim($body['menuId']    ?? '');
    $grp      = trim($body['groupName'] ?? '');
    $opt      = trim($body['optName']   ?? '');
    $price    = (float)($body['price']    ?? 0);
    $required = (int)($body['required']  ?? 0);
    $sort     = (int)($body['sortOrder'] ?? 0);

    if (!$mId || !$grp || !$opt) json_out(['error' => 'menuId, groupName, optName required'], 400);

    $newId = 'opt-' . now_ms();
    $stmt  = db()->prepare(
        'INSERT INTO menu_options (id,menu_id,group_name,opt_name,price,required,sort_order)
         VALUES (?,?,?,?,?,?,?)'
    );
    $stmt->bind_param('ssssdii', $newId, $mId, $grp, $opt, $price, $required, $sort);
    $stmt->execute();
    json_out(['id'=>$newId,'menuId'=>$mId,'groupName'=>$grp,'optName'=>$opt,'price'=>$price,'required'=>$required], 201);
}

/* ── PATCH ────────────────────────────────────────────── */
if ($method === 'PATCH') {
    $body = json_body();

    // --- PATCH group metadata (rename / required) ---
    if ($menuId && $group !== null) {
        $fields = []; $types = ''; $params = [];
        if (array_key_exists('newGroupName', $body)) {
            $fields[] = 'group_name=?'; $types .= 's'; $params[] = trim($body['newGroupName']);
        }
        if (array_key_exists('required', $body)) {
            $fields[] = 'required=?'; $types .= 'i'; $params[] = (int)$body['required'];
        }
        if (!$fields) json_out(['error' => 'nothing to update'], 400);
        $params[] = $menuId; $params[] = $group; $types .= 'ss';
        $stmt = db()->prepare('UPDATE menu_options SET '.implode(',',$fields).' WHERE menu_id=? AND group_name=?');
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        json_out(['ok' => true]);
    }

    // --- PATCH single option ---
    if (!$id) json_out(['error' => 'id required'], 400);
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

/* ── DELETE ───────────────────────────────────────────── */
if ($method === 'DELETE') {

    // --- DELETE entire group ---
    if ($menuId && $group !== null) {
        $stmt = db()->prepare('DELETE FROM menu_options WHERE menu_id=? AND group_name=?');
        $stmt->bind_param('ss', $menuId, $group);
        $stmt->execute();
        json_out(['ok' => true, 'deleted' => $stmt->affected_rows]);
    }

    // --- DELETE single option ---
    if (!$id) json_out(['error' => 'id or (menu_id+group) required'], 400);
    $stmt = db()->prepare('DELETE FROM menu_options WHERE id=?');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    json_out(['ok' => true]);
}

json_out(['error' => 'Method not allowed'], 405);
