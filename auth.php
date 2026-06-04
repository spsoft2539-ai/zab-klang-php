<?php
// ─── User Authentication & Authorization ────────────────────
session_start();

/**
 * Get current user from session
 * @return array|null {id, username, name, role}
 */
function getCurrentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

/**
 * Require authentication — redirect to login if not logged in
 */
function requireAuth(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Require specific role(s) — 403 if not authorized
 * @param string|array $allowedRoles Single role or array of roles
 */
function requireRole($allowedRoles): void {
    requireAuth();
    $user = getCurrentUser();
    $allowed = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];

    if (!in_array($user['role'], $allowed, true)) {
        http_response_code(403);
        die('<h1>❌ Access Denied</h1><p>You do not have permission to access this page.</p>');
    }
}

/**
 * Set user session after successful login
 */
function setUserSession(array $user): void {
    $_SESSION['user'] = [
        'id'       => $user['id'],
        'username' => $user['username'],
        'name'     => $user['name'],
        'role'     => $user['role'],
    ];
}

/**
 * Clear user session on logout
 */
function clearUserSession(): void {
    unset($_SESSION['user']);
    session_destroy();
}

/**
 * Verify login credentials
 * @return array|null User data if valid, null otherwise
 */
function verifyLogin(string $username, string $password): ?array {
    require_once __DIR__ . '/db.php';

    $stmt = db()->prepare('SELECT * FROM users WHERE username = ? AND is_active = 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) return null;

    // Verify password using bcrypt
    if (!password_verify($password, $user['password'])) {
        return null;
    }

    return [
        'id'       => $user['id'],
        'username' => $user['username'],
        'name'     => $user['name'],
        'role'     => $user['role'],
    ];
}

/**
 * Create new user (admin only)
 * @return array|string User ID on success, error message on failure
 */
function createUser(string $username, string $password, string $name, string $role = 'staff'): array|string {
    require_once __DIR__ . '/db.php';

    if (!in_array($role, ['owner', 'manager', 'staff'], true)) {
        return ['error' => 'Invalid role'];
    }

    $id = 'user-' . now_ms();
    $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    $now = now_ms();

    $stmt = db()->prepare(
        'INSERT INTO users (id,username,password,name,role,is_active,created_at) VALUES (?,?,?,?,?,1,?)'
    );
    $stmt->bind_param('sssssi', $id, $username, $hashed, $name, $role, $now);

    if ($stmt->execute()) {
        return ['id' => $id];
    }

    // Check for duplicate username
    if (str_contains($stmt->error, 'Duplicate entry')) {
        return ['error' => 'Username already exists'];
    }

    return ['error' => $stmt->error];
}

/**
 * Update user details
 */
function updateUser(string $id, ?string $name = null, ?string $role = null, ?bool $is_active = null): array {
    require_once __DIR__ . '/db.php';

    $fields = [];
    $types = '';
    $params = [];

    if ($name !== null) {
        $fields[] = 'name=?';
        $types .= 's';
        $params[] = $name;
    }

    if ($role !== null && in_array($role, ['owner', 'manager', 'staff'], true)) {
        $fields[] = 'role=?';
        $types .= 's';
        $params[] = $role;
    }

    if ($is_active !== null) {
        $fields[] = 'is_active=?';
        $types .= 'i';
        $params[] = $is_active ? 1 : 0;
    }

    if (empty($fields)) {
        return ['error' => 'Nothing to update'];
    }

    $params[] = $id;
    $types .= 's';

    $stmt = db()->prepare('UPDATE users SET ' . implode(',', $fields) . ' WHERE id=?');
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        return ['ok' => true];
    }

    return ['error' => $stmt->error];
}

/**
 * Delete user
 */
function deleteUser(string $id): array {
    require_once __DIR__ . '/db.php';

    $stmt = db()->prepare('DELETE FROM users WHERE id=?');
    $stmt->bind_param('s', $id);

    if ($stmt->execute()) {
        return ['ok' => true];
    }

    return ['error' => $stmt->error];
}

/**
 * List all users
 */
function getAllUsers(): array {
    require_once __DIR__ . '/db.php';

    $rows = db()->query('SELECT id,username,name,role,is_active,created_at FROM users ORDER BY created_at DESC')
            ->fetch_all(MYSQLI_ASSOC);

    return array_map(fn($r) => [
        'id'       => $r['id'],
        'username' => $r['username'],
        'name'     => $r['name'],
        'role'     => $r['role'],
        'is_active' => (bool)$r['is_active'],
        'created_at' => (int)$r['created_at'],
    ], $rows);
}
