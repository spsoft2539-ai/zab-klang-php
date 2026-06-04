<?php
// GET    /api/users.php             — list all users (owner/manager)
// POST   /api/users.php             — create new user (owner only)
// PATCH  /api/users.php?id=USER_ID  — update user (owner only)
// DELETE /api/users.php?id=USER_ID  — delete user (owner only)
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';

requireAuth();
$method = $_SERVER['REQUEST_METHOD'];

/* ── GET ──────────────────────────────────────────────────── */
if ($method === 'GET') {
    requireRole(['owner', 'manager']);
    json_out(getAllUsers());
}

/* ── POST (create new user) ───────────────────────────────── */
if ($method === 'POST') {
    requireRole('owner');

    $body = json_body();
    $username = trim($body['username'] ?? '');
    $password = trim($body['password'] ?? '');
    $name = trim($body['name'] ?? '');
    $role = trim($body['role'] ?? 'staff');

    if (!$username || !$password || !$name) {
        json_out(['error' => 'username, password, name required'], 400);
    }

    $result = createUser($username, $password, $name, $role);
    if (isset($result['error'])) {
        json_out($result, 400);
    }

    json_out(['id' => $result['id'], 'username' => $username, 'name' => $name, 'role' => $role], 201);
}

/* ── PATCH (update user) ──────────────────────────────────── */
if ($method === 'PATCH') {
    requireRole('owner');

    $id = trim($_GET['id'] ?? '');
    if (!$id) json_out(['error' => 'id required'], 400);

    $body = json_body();
    $result = updateUser(
        $id,
        $body['name'] ?? null,
        $body['role'] ?? null,
        $body['is_active'] ?? null
    );

    if (isset($result['error'])) {
        json_out($result, 400);
    }

    json_out($result);
}

/* ── DELETE (delete user) ─────────────────────────────────── */
if ($method === 'DELETE') {
    requireRole('owner');

    $id = trim($_GET['id'] ?? '');
    if (!$id) json_out(['error' => 'id required'], 400);

    $user = getCurrentUser();
    if ($user['id'] === $id) {
        json_out(['error' => 'Cannot delete your own account'], 400);
    }

    $result = deleteUser($id);
    if (isset($result['error'])) {
        json_out($result, 400);
    }

    json_out($result);
}

json_out(['error' => 'Method not allowed'], 405);
