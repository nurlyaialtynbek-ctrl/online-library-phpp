<?php
require 'config/db.php';
require 'includes/auth_check.php';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
}
header("Location: index.php");
exit;
