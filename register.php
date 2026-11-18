<?php
require_once 'config/db.php';
require_once 'includes/header.php';

if (isLoggedIn()) { header('Location: index.php'); exit; }

$errors = [];
$full_name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (mb_strlen($full_name) < 3) $errors[] = 'Аты-жөніңіз кемінде 3 таңбадан тұруы тиіс.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Электрондық пошта форматы қате.';
    if (strlen($password) < 6) $errors[] = 'Құпия сөз кемінде 6 таңбадан тұруы тиіс.';
    if ($password !== $password_confirm) $errors[] = 'Құпия сөздер сәйкес келмейді.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Бұл электрондық пошта жүйеде тіркелген.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'user')")
                ->execute([$full_name, $email, $hash]);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<div class="auth-box">
    <h2>Тіркелу</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?></div>
    <?php endif; ?>
    <form method="POST" action="register.php" class="form-stack">
        <label>Толық аты-жөніңіз <input type="text" name="full_name" value="<?= e($full_name) ?>" required></label>
        <label>Email <input type="email" name="email" value="<?= e($email) ?>" required></label>
        <label>Құпия сөз <input type="password" name="password" required></label>
        <label>Құпия сөзді қайталаңыз <input type="password" name="password_confirm" required></label>
        <button type="submit" class="btn-primary">Тіркелу</button>
    </form>
    <p class="auth-switch">Аккаунтыңыз бар ма? <a href="login.php">Кіру</a></p>
</div>
<?php require_once 'includes/footer.php'; ?>
