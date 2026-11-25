<?php
require 'config/db.php';
require 'includes/auth_check.php';

$id = $_GET['id'] ?? null;
$task = ['title' => '', 'description' => '', 'category_id' => 1, 'status' => 'pending', 'due_date' => date('Y-m-d'), 'attachment' => null];
$errors = [];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $task = $stmt->fetch();
    if (!$task) {
        die("Тапсырма табылмады немесе рұқсат жоқ.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 1);
    $status = in_array($_POST['status'] ?? '', ['pending', 'completed']) ? $_POST['status'] : 'pending';
    $due_date = $_POST['due_date'] ?? date('Y-m-d');
    $attachment = $task['attachment'];

    if ($title === '') {
        $errors[] = "Тапсырма атауын енгізу міндетті.";
    }

    if (!empty($_FILES['attachment']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'docx'];
        $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = "Тек jpg, png, pdf, docx файлдарына рұқсат етілген.";
        } elseif ($_FILES['attachment']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Файл өлшемі 2 МБ-тан аспауы керек.";
        } else {
            $attachment = uniqid('file_') . '.' . $ext;
            move_uploaded_file($_FILES['attachment']['tmp_name'], 'uploads/' . $attachment);
        }
    }

    if (empty($errors)) {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE tasks SET title=?, description=?, category_id=?, status=?, due_date=?, attachment=? WHERE id=? AND user_id=?");
            $stmt->execute([$title, $description, $category_id, $status, $due_date, $attachment, $id, $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO tasks (user_id, category_id, title, description, status, due_date, attachment) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $category_id, $title, $description, $status, $due_date, $attachment]);
        }
        header("Location: index.php");
        exit;
    }
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
include 'includes/header.php';
?>
<h2><?= $id ? 'Тапсырманы өңдеу' : 'Жаңа тапсырма қосу' ?></h2>
<?php foreach ($errors as $e): ?><p class="error"><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
<form method="POST" enctype="multipart/form-data" class="card">
    <label>Тапсырма тақырыбы</label>
    <input type="text" name="title" value="<?= htmlspecialchars($task['title']) ?>" placeholder="Мысалы: PHP жобасын аяқтау" required>
    <label>Сипаттамасы</label>
    <textarea name="description" rows="4" placeholder="Толық мәлімет..."><?= htmlspecialchars($task['description']) ?></textarea>
    <label>Санаты</label>
    <select name="category_id">
        <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $task['category_id'] == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <label>Орындалу күйі</label>
    <select name="status">
        <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>Орындалуда</option>
        <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Орындалды</option>
    </select>
    <label>Аяқтау мерзімі</label>
    <input type="date" name="due_date" value="<?= htmlspecialchars($task['due_date']) ?>">
    <label>Файл тіркеу (jpg, png, pdf, docx - макс 2MB)</label>
    <input type="file" name="attachment">
    <button type="submit" class="btn">Сақтау</button>
</form>
<?php include 'includes/footer.php'; ?>
