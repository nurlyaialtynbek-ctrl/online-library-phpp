<?php
require_once 'config/db.php';
require_once 'includes/header.php';
requireLogin();

$stmt = $pdo->prepare("SELECT b.*, c.name AS category_name FROM books b JOIN categories c ON b.category_id = c.id WHERE b.created_by = ? ORDER BY b.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$myBooks = $stmt->fetchAll();
?>
<section class="profile-box">
    <h2>Жеке профиль</h2>
    <p><strong>Аты-жөні:</strong> <?= e($_SESSION['user_name']) ?></p>
    <p><strong>Email:</strong> <?= e($_SESSION['user_email']) ?></p>
    <p><strong>Рөлі:</strong> <?= $_SESSION['user_role'] === 'admin' ? 'Әкімші (Admin)' : 'Оқырман (User)' ?></p>
</section>
<section>
    <h3>Мен қосқан кітаптар (<?= count($myBooks) ?>)</h3>
    <?php if (empty($myBooks)): ?>
        <p>Сіз әлі кітап қосқан жоқсыз. <a href="book_form.php">Алғашқы кітапты қосу</a></p>
    <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Атауы</th><th>Авторы</th><th>Санаты</th><th>Жылы</th><th>Әрекеттер</th></tr></thead>
            <tbody>
                <?php foreach ($myBooks as $b): ?>
                    <tr>
                        <td><a href="book_detail.php?id=<?= $b['id'] ?>"><?= e($b['title']) ?></a></td>
                        <td><?= e($b['author']) ?></td>
                        <td><?= e($b['category_name']) ?></td>
                        <td><?= (int)$b['publish_year'] ?></td>
                        <td><a href="book_form.php?id=<?= $b['id'] ?>">Өңдеу</a> | <a href="delete_book.php?id=<?= $b['id'] ?>" onclick="return confirm('Өшіруге сенімдісіз бе?');">Жою</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php require_once 'includes/footer.php'; ?>
