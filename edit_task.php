<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = $_GET['task_id'] ?? null;

if (!$task_id) {
    header("Location: index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$task_id, $user_id]);
$task = $stmt->fetch();

if (!$task) {
    header("Location: index.php");
    exit();
}

// **Fix: Inisialisasi variabel `$is_late` sebelum digunakan**
$is_late = strtotime($task['due_date']) < time() && $task['status'] == 0;
$is_locked = ($task['status'] == 1) || $is_late;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$is_locked) {
    $task_name = $_POST['task'];
    $due_date = $_POST['due_date'];
    
    $stmt = $pdo->prepare("UPDATE tasks SET task = ?, due_date = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_name, $due_date, $task_id, $user_id]);
    
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(to right, #8EACCD, #F0C1E1);
            color: white;
        }
        .container {
            background: rgba(255, 255, 255, 0.2);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 350px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        input, select, button {
            padding: 12px;
            border: none;
            border-radius: 5px;
            width: 100%;
        }
        input {
            background: rgba(255, 255, 255, 0.8);
            font-size: 16px;
            text-align: center;
        }
        button {
            background-color: rgb(191, 168, 201);
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background-color: rgb(233, 205, 224);
        }
        .back-link {
            display: block;
            margin-top: 15px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }
        .back-link:hover {
            color: rgb(233, 205, 224);
        }
        .error-message {
            color: red;
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📝 Edit Tugas</h2>
        
        <?php if ($is_locked): ?>
            <p class="error-message">Tugas ini sudah selesai atau melewati tenggat waktu dan tidak dapat diedit lagi.</p>
        <?php else: ?>
            <form method="POST">
                <input type="text" name="task" value="<?= htmlspecialchars($task['task']) ?>" required>
                <input type="date" name="due_date" value="<?= $task['due_date'] ?>" required min="<?= date('Y-m-d') ?>">
                <button type="submit">✔ Simpan Perubahan</button>
            </form>
        <?php endif; ?>

        <a href="index.php" class="back-link">⬅ Kembali ke Daftar Tugas</a>
    </div>
</body>
</html>
