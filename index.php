<?php
require 'config/db.php';
require 'includes/auth_check.php';

$user_id = $_SESSION['user_id'];
$search = trim($_GET['search'] ?? '');
$category_id = $_GET['category'] ?? '';
$sort = ($_GET['sort'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

$limit = 5;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$where = ["t.user_id = ?"];
$params = [$user_id];

if ($search !== '') {
    $where[] = "(t.title LIKE ? OR t.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category_id !== '') {
    $where[] = "t.category_id = ?";
    $params[] = $category_id;
}

$whereSql = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM tasks t WHERE $whereSql");
$countStmt->execute($params);
$totalTasks = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalTasks / $limit));

$sql = "SELECT t.*, c.name AS category_name, c.color 
        FROM tasks t 
        JOIN categories c ON t.category_id = c.id 
        WHERE $whereSql 
        ORDER BY t.due_date $sort 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
include 'includes/header.php';
?>

<h2>Менің тапсырмаларым</h2>

<form method="GET" class="filter-bar">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Тапсырманы іздеу...">
    <select name="category">
        <option value="">Барлық санаттар</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select name="sort">
        <option value="DESC" <?= $sort === 'DESC' ? 'selected' : '' ?>>Мерзімі (Жаңасы)</option>
        <option value="ASC" <?= $sort === 'ASC' ? 'selected' : '' ?>>Мерзімі (Ескісі)</option>
    </select>
    <button type="submit" class="btn">Сүзу</button>
</form>

<?php if (empty($tasks)): ?>
    <div class="card"><p>Тапсырмалар табылмады. Жаңа тапсырма қосыңыз!</p></div>
<?php else: ?>
    <?php foreach ($tasks as $task): ?>
        <div class="task-card">
            <span class="badge" style="background:<?= htmlspecialchars($task['color']) ?>">
                <?= htmlspecialchars($task['category_name']) ?>
            </span>
            <h3><?= htmlspecialchars($task['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($task['description'])) ?></p>
            <small>Мерзімі: <?= htmlspecialchars($task['due_date']) ?> | Күйі: <b><?= $task['status'] === 'completed' ? 'Орындалды' : 'Орындалуда' ?></b></small>
            <?php if ($task['attachment']): ?>
                <p><a href="uploads/<?= htmlspecialchars($task['attachment']) ?>" target="_blank">📎 Тіркелген файлды ашу</a></p>
            <?php endif; ?>
            <div class="actions">
                <a href="task_form.php?id=<?= $task['id'] ?>">Өңдеу</a> |
                <a href="delete_task.php?id=<?= $task['id'] ?>" class="text-danger" onclick="return confirm('Жоюға сенімдісіз бе?')">Жою</a>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_id) ?>&sort=<?= $sort ?>" 
           class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>

<?php include 'includes/footer.php'; ?>
