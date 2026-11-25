<?php
require 'config/db.php';
require 'includes/auth_check.php';

$stmt = $pdo->prepare("SELECT username, email, role, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ?");
$countStmt->execute([$_SESSION['user_id']]);
$taskCount = $countStmt->fetchColumn();

include 'includes/header.php';
?>
<h2>Пайдаланушы профилі</h2>
<div class="card">
    <p><b>Аты-жөні (Логин):</b> <?= htmlspecialchars($user['username']) ?></p>
    <p><b>Email:</b> <?= htmlspecialchars($user['email']) ?></p>
    <p><b>Рөлі:</b> <?= htmlspecialchars($user['role']) ?></p>
    <p><b>Тіркелген уақыты:</b> <?= htmlspecialchars($user['created_at']) ?></p>
    <p><b>Барлық тапсырмалар саны:</b> <?= $taskCount ?></p>
</div>
<?php include 'includes/footer.php'; ?>
