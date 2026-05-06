<?php
require_once 'config/db.php';
try {
    $pdo = getPDO();
    $stmt = $pdo->prepare('DELETE FROM modules WHERE id = 4');
    $stmt->execute();
    echo "Rows deleted: " . $stmt->rowCount();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
