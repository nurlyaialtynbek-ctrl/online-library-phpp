<?php
require_once 'config/db.php';
require_once 'includes/header.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $comment = trim($_POST['comment'] ?? '');
    if ($comment !== '') {
        $pdo->prepare("INSERT INTO reviews (book_id, user_id, rating, comment) VALUES (?, ?, ?, ?)")
            ->execute([$id, $_SESSION['user_id'], $rating, $comment]);
        header("Location: book_detail.php?id=$id");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT b.*, c.name AS category_name, u.full_name AS creator_name FROM books b JOIN categories c ON b.category_id = c.id JOIN users u ON b.created_by = u.id WHERE b.id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch();
if (!$book) die('Кітап табылмады.');

$revStmt = $pdo->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.book_id = ? ORDER BY r.created_at DESC");
$revStmt->execute([$id]);
$reviews = $revStmt->fetchAll();
?>
<div class="detail-card">
    <div class="detail-cover">
        <?php if (!empty($book['cover_image']) && file_exists('uploads/' . $book['cover_image'])): ?>
            <img src="uploads/<?= e($book['cover_image']) ?>" alt="<?= e($book['title']) ?>">
        <?php else: ?>
            <div class="cover-placeholder large">📖</div>
        <?php endif; ?>
    </div>
    <div class="detail-info">
        <span class="badge"><?= e($book['category_name']) ?></span>
        <h1><?= e($book['title']) ?></h1>
        <p class="meta"><strong>Авторы:</strong> <?= e($book['author']) ?> | <strong>Жылы:</strong> <?= (int)$book['publish_year'] ?></p>
        <p class="meta"><strong>Қолжетімді дана:</strong> <?= (int)$book['available_copies'] ?> | <strong>Қосқан:</strong> <?= e($book['creator_name']) ?></p>
        <div class="description">
            <h3>Сипаттама</h3>
            <p><?= nl2br(e($book['description'] ?: 'Сипаттама берілмеген.')) ?></p>
        </div>
        <a href="index.php" class="btn-outline">← Каталогқа оралу</a>
    </div>
</div>
<section class="reviews-section">
    <h2>Оқырман пікірлері (<?= count($reviews) ?>)</h2>
    <?php if (isLoggedIn()): ?>
        <form method="POST" class="form-stack review-form">
            <label>Бағалау (1–5)
                <select name="rating">
                    <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                    <option value="4">⭐⭐⭐⭐ (4)</option>
                    <option value="3">⭐⭐⭐ (3)</option>
                    <option value="2">⭐⭐ (2)</option>
                    <option value="1">⭐ (1)</option>
                </select>
            </label>
            <label>Пікіріңіз <textarea name="comment" rows="3" required></textarea></label>
            <button type="submit" class="btn-primary">Пікір қалдыру</button>
        </form>
    <?php else: ?>
        <p><a href="login.php">Жүйеге кіріңіз</a>, пікір қалдыру үшін.</p>
    <?php endif; ?>
    <div class="review-list">
        <?php foreach ($reviews as $rev): ?>
            <div class="review-item">
                <div class="review-head"><strong><?= e($rev['full_name']) ?></strong> <span><?= str_repeat('⭐', (int)$rev['rating']) ?></span></div>
                <p><?= nl2br(e($rev['comment'])) ?></p>
                <small><?= e($rev['created_at']) ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
