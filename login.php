<?php
require 'config/db.php';
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Email немесе құпиясөз қате!";
    }
}
include 'includes/header.php';
?>
<h2>Жүйеге кіру</h2>
<?php if (isset($_GET['registered'])): ?>
    <p class="success">Тіркелу сәтті аяқталды! Енді жүйеге кіріңіз.</p>
<?php endif; ?>
<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="POST" class="card">
    <label>Email</label>
    <input type="email" name="email" placeholder="Email" required>
    <label>Құпиясөз</label>
    <input type="password" name="password" placeholder="Құпиясөз" required>
    <button type="submit" class="btn">Кіру</button>
</form>
<?php include 'includes/footer.php'; ?>
