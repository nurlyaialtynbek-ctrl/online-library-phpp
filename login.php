<?php
require_once 'config/db.php';
require_once 'includes/header.php';

if (isLoggedIn()) { header('Location: index.php'); exit; }

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Email немесе құпия сөз қате.';
    }
}
?>
<div class="auth-box">
    <h2>Жүйеге кіру</h2>
    <?php if (isset($_GET['registered'])): ?><div class="alert alert-success">Тіркелу сәтті аяқталды!</div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="POST" action="login.php" class="form-stack">
        <label>Email <input type="email" name="email" value="<?= e($email) ?>" required></label>
        <label>Құпия сөз <input type="password" name="password" required></label>
        <button type="submit" class="btn-primary">Кіру</button>
    </form>
    <p class="auth-switch">Аккаунтыңыз жоқ па? <a href="register.php">Тіркелу</a></p>
</div>
<?php require_once 'includes/footer.php'; ?>
