<?php
// Adds username and role columns to users table if missing and backfills data
$dsn = 'mysql:host=localhost;dbname=veigest;charset=utf8mb4';
$db = new PDO($dsn, 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function columnExists(PDO $db, $table, $column) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c");
    $stmt->execute([':t' => $table, ':c' => $column]);
    return $stmt->fetchColumn() > 0;
}

echo "=== Alterando tabela users ===\n";

// Add username
if (!columnExists($db, 'users', 'username')) {
    echo "Adding column users.username...\n";
    $db->exec("ALTER TABLE users ADD COLUMN username VARCHAR(64) NOT NULL AFTER nome");
    // Unique per company
    // Avoid failure if exists
    try { $db->exec("ALTER TABLE users ADD UNIQUE KEY uk_username_company (username, company_id)"); } catch (Exception $e) {}
}

// Add role
if (!columnExists($db, 'users', 'role')) {
    echo "Adding column users.role...\n";
    $db->exec("ALTER TABLE users ADD COLUMN role ENUM('admin','gestor','condutor') NOT NULL DEFAULT 'condutor' AFTER telefone");
    try { $db->exec("ALTER TABLE users ADD INDEX idx_role (role)"); } catch (Exception $e) {}
}

// Backfill username
echo "Backfilling usernames...\n";
$rows = $db->query("SELECT id, email, nome, username FROM users")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    if (empty($r['username'])) {
        $base = '';
        if (!empty($r['nome'])) {
            // create slug from nome
            $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $r['nome']));
            $base = trim($base, '-');
        } elseif (!empty($r['email'])) {
            $base = strtolower(substr($r['email'], 0, strpos($r['email'], '@') ?: strlen($r['email'])));
        } else {
            $base = 'user'.$r['id'];
        }
        if ($base === '') { $base = 'user'.$r['id']; }
        // ensure uniqueness per company
        $username = $base;
        $suffix = 1;
        while (true) {
            $exists = $db->prepare("SELECT 1 FROM users WHERE username = :u LIMIT 1");
            $exists->execute([':u' => $username]);
            if (!$exists->fetch()) break;
            $username = $base.'-'.$suffix++;
        }
        $upd = $db->prepare("UPDATE users SET username = :u WHERE id = :id");
        $upd->execute([':u' => $username, ':id' => $r['id']]);
        echo " - user {$r['id']} -> username={$username}\n";
    }
}

// Backfill role from RBAC assignments
echo "Backfilling roles from RBAC...\n";
$assignments = $db->query("SELECT user_id, item_name FROM auth_assignment")->fetchAll(PDO::FETCH_ASSOC);
$byUser = [];
foreach ($assignments as $a) {
    $byUser[$a['user_id']][] = $a['item_name'];
}

foreach ($rows as $r) {
    $role = null;
    $userId = (int)$r['id'];
    if (isset($byUser[$userId])) {
        $names = $byUser[$userId];
        if (in_array('admin', $names)) {
            $role = 'admin';
        } elseif (in_array('gestor', $names)) {
            $role = 'gestor';
        } elseif (in_array('condutor', $names)) {
            $role = 'condutor';
        } else {
            $role = $names[0];
        }
    }
    if ($role === null) { $role = 'condutor'; }
    $upd = $db->prepare("UPDATE users SET role = :r WHERE id = :id");
    $upd->execute([':r' => $role, ':id' => $userId]);
    echo " - user {$userId} -> role={$role}\n";
}

echo "\nDone.\n";
