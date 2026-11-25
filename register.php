<?php
require 'config/db.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($username) < 3) $errors[] = "Пайдаланушы аты кемінде 3 таңба болуы керек.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email форматы қате.";
    if (strlen($password) < 6) $errors[] = "Құпиясөз кемінде 6 таңба болуы керек.";

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$username, $email, $hash]);
            header("Location: login.php?registered=1");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Бұл email немесе логин бос емес.";
        }
    }
}
include 'includes/header.php';
?>
<h2>Тіркелу</h2>
<?php foreach ($errors as $err): ?>
    <p class="error"><?= htmlspecialchars($err) ?></p>
<?php endforeach; ?>
<form method="POST" class="card">
    <label>Логин</label>
    <input type="text" name="username" placeholder="Логин енгізіңіз" required>
    <label>Email</label>
    <input type="email" name="email" placeholder="Email енгізіңіз" required>
    <label>Құпиясөз</label>
    <input type="password" name="password" placeholder="Кемінде 6 таңба" required>
    <button type="submit" class="btn">Тіркелу</button>
</form>
<?php include 'includes/footer.php'; ?>
