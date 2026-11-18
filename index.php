<?php
require_once 'config/db.php';
require_once 'includes/header.php';

$search = trim($_GET['search'] ?? '');
$category_id = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 6;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(b.title LIKE :search OR b.author LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($category_id) {
    $where[] = "b.category_id = :category_id";
    $params[':category_id'] = $category_id;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$orderSql = match ($sort) {
    'title_asc'  => 'ORDER BY b.title ASC',
    'year_desc'  => 'ORDER BY b.publish_year DESC',
    'year_asc'   => 'ORDER BY b.publish_year ASC',
    default      => 'ORDER BY b.created_at DESC',
};

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM books b $whereSql");
$countStmt->execute($params);
$totalBooks = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalBooks / $limit));

$sql = "SELECT b.*, c.name AS category_name, u.full_name AS creator_name
        FROM books b
        JOIN categories c ON b.category_id = c.id
        JOIN users u ON b.created_by = u.id
        $whereSql $orderSql LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$books = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<section class="page-header">
    <h1>Кітаптар каталогы</h1>
    <p>Барлығы: <strong><?= $totalBooks ?></strong> кітап табылды</p>
</section>

<form method="GET" action="index.php" class="filter-bar">
    <input type="text" name="search" placeholder="Кітап атауы немесе авторы..." value="<?= e($search) ?>">
    <select name="category">
        <option value="">Барлық санаттар</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $category_id === (int)$cat['id'] ? 'selected' : '' ?>>
                <?= e($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select name="sort">
        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Жаңа қосылғандар</option>
        <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Атауы бойынша (А-Я)</option>
        <option value="year_desc" <?= $sort === 'year_desc' ? 'selected' : '' ?>>Жылы (жаңадан ескіге)</option>
        <option value="year_asc" <?= $sort === 'year_asc' ? 'selected' : '' ?>>Жылы (ескіден жаңаға)</option>
    </select>
    <button type="submit" class="btn-primary">Іздеу</button>
    <?php if ($search || $category_id || $sort !== 'newest'): ?>
        <a href="index.php" class="btn-outline">Тазарту</a>
    <?php endif; ?>
</form>

<?php if (empty($books)): ?>
    <div class="alert">Сұраныс бойынша кітаптар табылмады.</div>
<?php else: ?>
    <div class="book-grid">
        <?php foreach ($books as $book): ?>
            <article class="book-card">
                <div class="book-cover">
                    <?php if (!empty($book['cover_image']) && file_exists('uploads/' . $book['cover_image'])): ?>
                        <img src="uploads/<?= e($book['cover_image']) ?>" alt="<?= e($book['title']) ?>">
                    <?php else: ?>
                        <div class="cover-placeholder">📖</div>
                    <?php endif; ?>
                </div>
                <div class="book-info">
                    <span class="badge"><?= e($book['category_name']) ?></span>
                    <h3><a href="book_detail.php?id=<?= $book['id'] ?>"><?= e($book['title']) ?></a></h3>
                    <p class="author"><?= e($book['author']) ?> • <?= (int)$book['publish_year'] ?> ж.</p>
                    <p class="copies">Қолжетімді дана: <strong><?= (int)$book['available_copies'] ?></strong></p>
                    <div class="card-actions">
                        <a href="book_detail.php?id=<?= $book['id'] ?>" class="btn-sm">Толығырақ</a>
                        <?php if (isLoggedIn() && (isAdmin() || (int)$_SESSION['user_id'] === (int)$book['created_by'])): ?>
                            <a href="book_form.php?id=<?= $book['id'] ?>" class="btn-sm btn-edit">Өңдеу</a>
                            <a href="delete_book.php?id=<?= $book['id'] ?>" class="btn-sm btn-danger" onclick="return confirm('Бұл кітапты өшіруге сенімдісіз бе?');">Жою</a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php $url = 'index.php?' . http_build_query(array_merge($_GET, ['page' => $i])); ?>
                <a href="<?= e($url) ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
