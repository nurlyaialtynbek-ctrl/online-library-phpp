<?php
require_once 'config/db.php';
require_once 'includes/auth_check.php';
requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch();

if ($book && (isAdmin() || (int)$book['created_by'] === (int)$_SESSION['user_id'])) {
    if (!empty($book['cover_image']) && file_exists(__DIR__ . '/uploads/' . $book['cover_image'])) {
        unlink(__DIR__ . '/uploads/' . $book['cover_image']);
    }
    $pdo->prepare("DELETE FROM books WHERE id = ?")->execute([$id]);
}
header('Location: index.php');
exit;
