<?php
require_once 'config/db.php';
try {
    $pdo = getPDO();
    $stmt = $pdo->query('DESCRIBE modules');
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($cols);
} catch (Exception $e) {
    echo $e->getMessage();
}
