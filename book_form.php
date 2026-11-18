<?php
require_once 'config/db.php';
require_once 'includes/header.php';
requireLogin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$isEdit = false;
$book = ['title' => '', 'author' => '', 'publish_year' => date('Y'), 'description' => '', 'available_copies' => 1, 'category_id' => '', 'cover_image' => ''];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) die('Кітап табылмады.');
    if (!isAdmin() && (int)$existing['created_by'] !== (int)$_SESSION['user_id']) {
        http_response_code(403);
        die('Бұл кітапты өңдеуге құқығыңыз жоқ.');
    }
    $book = $existing;
    $isEdit = true;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $publish_year = (int)($_POST['publish_year'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $available_copies = max(0, (int)($_POST['available_copies'] ?? 1));
    $category_id = (int)($_POST['category_id'] ?? 0);

    if ($title === '') $errors[] = 'Кітап атауын енгізіңіз.';
    if ($author === '') $errors[] = 'Автордың аты-жөнін енгізіңіз.';
    if ($publish_year < 1000 || $publish_year > (int)date('Y')) $errors[] = 'Шыққан жылы қате көрсетілген.';
    if ($category_id <= 0) $errors[] = 'Санатты таңдаңыз.';

    $coverFileName = $book['cover_image'];

    if (!empty($_FILES['cover_image']['name'])) {
        $file = $_FILES['cover_image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes, true)) {
            $errors[] = 'Тек JPG, PNG немесе WEBP форматындағы суреттерге рұқсат етіледі.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Сурет өлшемі 2 МБ-тан аспауы тиіс.';
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $coverFileName = uniqid('cover_', true) . '.' . strtolower($ext);
            move_uploaded_file($file['tmp_name'], __DIR__ . '/uploads/' . $coverFileName);
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            $sql = "UPDATE books SET title=?, author=?, publish_year=?, description=?, cover_image=?, available_copies=?, category_id=? WHERE id=?";
            $pdo->prepare($sql)->execute([$title, $author, $publish_year, $description, $coverFileName, $available_copies, $category_id, $id]);
        } else {
            $sql = "INSERT INTO books (title, author, publish_year, description, cover_image, available_copies, category_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$title, $author, $publish_year, $description, $coverFileName, $available_copies, $category_id, $_SESSION['user_id']]);
        }
        header('Location: index.php');
        exit;
    }
}
?>
<div class="form-box">
    <h2><?= $isEdit ? 'Кітапты өңдеу' : 'Жаңа кітап қосу' ?></h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="form-stack">
        <label>Кітап атауы * <input type="text" name="title" value="<?= e($book['title']) ?>" required></label>
        <label>Авторы * <input type="text" name="author" value="<?= e($book['author']) ?>" required></label>
        <div class="form-row">
            <label>Шыққан жылы * <input type="number" name="publish_year" value="<?= (int)$book['publish_year'] ?>" required></label>
            <label>Қолжетімді дана саны * <input type="number" name="available_copies" min="0" value="<?= (int)$book['available_copies'] ?>" required></label>
        </div>
        <label>Санат (жанр) *
            <select name="category_id" required>
                <option value="">-- Санатты таңдаңыз --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (int)$book['category_id'] === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Қысқаша сипаттама <textarea name="description" rows="4"><?= e($book['description'] ?? '') ?></textarea></label>
        <label>Мұқаба суреті (JPG, PNG, WEBP, макс. 2МБ) <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"></label>
        <div class="form-actions">
            <button type="submit" class="btn-primary"><?= $isEdit ? 'Сақтау' : 'Қосу' ?></button>
            <a href="index.php" class="btn-outline">Бас тарту</a>
        </div>
    </form>
</div>
<?php require_once 'includes/footer.php'; ?>
