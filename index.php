<?php
$host = 'localhost';
$dbname = 'tasks_db';
$user = 'root';
$password = 'root';

function connectToDatabase($host, $dbname, $user, $password) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

function addTask($pdo, $title) {
    if (!empty($title)) {
        $stmt = $pdo->prepare("INSERT INTO tasks (title, completed) VALUES (:title, false)");
        $stmt->execute(['title' => $title]);
    }
}

function deleteTask($pdo, $id) {
    if (!empty($id)) {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}

function updateTask($pdo, $id, $completed) {
    if (!empty($id)) {
        $stmt = $pdo->prepare("UPDATE tasks SET completed = :completed WHERE id = :id");
        $stmt->execute(['completed' => $completed, 'id' => $id]);
    }
}

function getTasks($pdo) {
    $stmt = $pdo->query("SELECT * FROM tasks");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$pdo = connectToDatabase($host, $dbname, $user, $password);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $title = $_POST['title'] ?? '';
    $id = $_POST['id'] ?? '';
    $completed = $_POST['completed'] ?? '';

    switch ($action) {
        case 'add':
            addTask($pdo, $title);
            break;
        case 'delete':
            deleteTask($pdo, $id);
            break;
        case 'update':
            updateTask($pdo, $id, $completed);
            break;
    }
}

$tasks = getTasks($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="task-manager">
    <header class="header">
        <h1 class="header__title">Task Manager</h1>
    </header>

    <main class="main-content">
        <section class="task-form">
            <form method="POST" class="task-form__form">
                <input type="text" name="title" class="task-form__input" placeholder="Task title" required>
                <input type="hidden" name="action" value="add">
                <button type="submit" class="task-form__button">Add Task</button>
            </form>
        </section>

        <section class="task-list">
            <h2 class="task-list__title">Task List</h2>
            <ul class="task-list__items">
                <?php foreach ($tasks as $task): ?>
                    <li class="task-list__item <?php echo $task['completed'] ? 'task-list__item--completed' : ''; ?>">
                        <form method="POST" class="task-list__form">
                            <input type="checkbox" name="completed" class="task-list__checkbox" value="1" onchange="this.form.submit()" <?php echo $task['completed'] ? 'checked' : ''; ?>>
                            <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                            <input type="hidden" name="action" value="update">
                        </form>
                        <span class="task-list__text"><?php echo htmlspecialchars($task['title']); ?></span>
                        <form method="POST" class="task-list__form">
                            <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="task-list__delete-button">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>
</body>
</html>
