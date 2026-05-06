<?php
require_once 'config/db.php';
try {
    $pdo = getPDO();
    $stmt = $pdo->query('SELECT id, user_id, module_name FROM modules');
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($modules);

    echo "\nSession user_id is: ";
    session_start();
    echo $_SESSION['user_id'] ?? 'Not set';

} catch (Exception $e) {
    echo $e->getMessage();
}
