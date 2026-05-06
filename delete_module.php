<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
require_once 'config/db.php';

$userId = (int) $_SESSION['user_id'];
$id     = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $pdo  = getPDO();
        $stmt = $pdo->prepare('DELETE FROM modules WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        $rowCount = $stmt->rowCount();
        if ($rowCount === 0) {
            die("Error: No module found with id=$id and user_id=$userId. Or it was already deleted.");
        }
    } catch (Exception $e) {
        die("Exception: " . $e->getMessage());
    }
} else {
    die("Error: Invalid ID.");
}

header('Location: modules.php?deleted=1');
exit;
