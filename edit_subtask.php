<?php
session_start();
include 'db.php';

// Cek jika ada subtask_id dalam URL
$subtask_id = $_GET['subtask_id'] ?? null;
if (!$subtask_id) {
    header("Location: subtasks.php");
    exit();
}

// Mengambil data subtugas berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM subtasks WHERE id = ?");
$stmt->execute([$subtask_id]);
$subtask = $stmt->fetch();

if (!$subtask) {
    header("Location: subtasks.php");
    exit();
}

// Mengupdate subtugas jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subtask_name = $_POST['subtask'];
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];

    // Update subtugas dalam database
    $stmt = $pdo->prepare("UPDATE subtasks SET subtask = ?, due_date = ?, priority = ? WHERE id = ?");
    $stmt->execute([$subtask_name, $due_date, $priority, $subtask_id]);

    // Redirect ke halaman subtasks setelah sukses
    header("Location: subtasks.php?task_id=" . $subtask['task_id']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subtugas</title>
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
        input, select {
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
    </style>
</head>
<body>
    <div class="container">
        <h2>📝 Edit Subtugas</h2>
        <form method="POST">
            <input type="hidden" name="subtask_id" value="<?= $subtask['id'] ?>">
            <input type="text" name="subtask" value="<?= htmlspecialchars($subtask['subtask']) ?>" required>
            <input type="date" name="due_date" value="<?= $subtask['due_date'] ?>" required min="<?= date('Y-m-d') ?>">
            <select name="priority" required>
                <option value="Tinggi" <?= $subtask['priority'] == 'Tinggi' ? 'selected' : '' ?>>Tinggi</option>
                <option value="Sedang" <?= $subtask['priority'] == 'Sedang' ? 'selected' : '' ?>>Sedang</option>
                <option value="Rendah" <?= $subtask['priority'] == 'Rendah' ? 'selected' : '' ?>>Rendah</option>
            </select>
            <button type="submit">✔ Simpan Perubahan</button>
        </form>
        <a href="subtasks.php?task_id=<?= $subtask['task_id'] ?>" class="back-link">⬅ Kembali ke Subtugas</a>
    </div>
</body>
</html>
