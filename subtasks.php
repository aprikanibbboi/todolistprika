<?php
session_start();
include 'db.php';

// Cek apakah pengguna sudah login
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

// Mengambil informasi tugas utama
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$task_id, $user_id]);
$task = $stmt->fetch();

if (!$task) {
    header("Location: index.php");
    exit();
}

// Menambahkan subtugas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subtask'])) {
    $subtask = $_POST['subtask'];
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];

    // Proses penyimpanan subtugas ke dalam database
    $stmt = $pdo->prepare("INSERT INTO subtasks (task_id, subtask, due_date, priority, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$task_id, $subtask, $due_date, $priority, 0]); // status = 0 (belum selesai)
    header("Location: subtasks.php?task_id=$task_id");
    exit();
}

// Mengambil semua subtugas
$subtasks = $pdo->prepare("
    SELECT * FROM subtasks 
    WHERE task_id = ? 
    ORDER BY FIELD(priority, 'Tinggi', 'Sedang', 'Rendah'), due_date ASC
");
$subtasks->execute([$task_id]);
$subtasks = $subtasks->fetchAll();

// Menghapus subtugas
if (isset($_GET['delete_subtask'])) {
    $subtask_id = $_GET['delete_subtask'];
    
    // Pastikan hanya pengguna yang terkait dengan tugas yang bisa menghapus subtugas
    $stmt = $pdo->prepare("DELETE FROM subtasks WHERE id = ? AND task_id IN (SELECT id FROM tasks WHERE user_id = ?)");
    $stmt->execute([$subtask_id, $user_id]);
    
    header("Location: subtasks.php?task_id=$task_id");
    exit();
}

// Menandai subtugas sebagai selesai atau belum selesai
if (isset($_POST['subtask_status'])) {
    $subtask_id = $_POST['subtask_id'];
    $status = $_POST['status'];  // status 1 untuk selesai, 0 untuk belum selesai

    // Update status subtugas
    $stmt = $pdo->prepare("UPDATE subtasks SET status = ? WHERE id = ? AND task_id = ?");
    $stmt->execute([$status, $subtask_id, $task_id]);

    header("Location: subtasks.php?task_id=$task_id");
    exit();
}

// Menandai semua subtugas selesai dan mengubah status tugas utama menjadi selesai
if (isset($_POST['mark_all_completed'])) {
    // Update semua subtugas menjadi selesai (status = 1)
    $stmt = $pdo->prepare("UPDATE subtasks SET status = 1 WHERE task_id = ?");
    $stmt->execute([$task_id]);

    // Cek apakah semua subtugas sudah selesai, jika ya update tugas utama menjadi selesai
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM subtasks WHERE task_id = ? AND status = 0");
    $stmt->execute([$task_id]);
    $unfinished_subtasks = $stmt->fetchColumn();

    if ($unfinished_subtasks == 0) {
        // Update status tugas utama menjadi selesai
        $stmt = $pdo->prepare("UPDATE tasks SET status = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
    }

    header("Location: subtasks.php?task_id=$task_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subtugas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #8EACCD, #F0C1E1);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            width: 90%;
            max-width: 700px;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h2 {
            color: white;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 10px;
            margin-top: 10px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }
        .back-link:hover {
            color: rgb(233, 205, 224);
            text-decoration: underline;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }
        input, select, button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            width: 100%;
        }
        button {
            background-color: rgb(191, 168, 201);
            color: white;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        button:hover {
            background-color: rgb(233, 205, 224);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: rgb(191, 168, 201);
            color: white;
        }
        .task-completed {
            text-decoration: line-through;
            color: gray;
        }
        .checkbox {
            transform: scale(1.2);
            cursor: pointer;
        }
        .action-buttons a {
            text-decoration: none;
            padding: 6px 10px;
            margin: 2px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
        }
        .edit-btn {
            background: linear-gradient(to right, #FFE9AE, #8EACCD);
            color: white;
        }
        .delete-btn {
            background: linear-gradient(to right, #DC8686, #8EACCD);
            color: white;
        }
        .edit-btn:hover {
            background: #FFE9AE;
        }
        .delete-btn:hover {
            background: #DC8686;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📝 Subtugas untuk: <br><?= htmlspecialchars($task['task']) ?></h2>
    <a href="index.php?task_id=<?= $task_id ?>" class="back-link">⬅ Kembali ke halaman utama </a>

    <!-- Form Tambah Subtugas -->
<form method="POST">
    <input type="text" name="subtask" placeholder="Tambahkan subtugas baru" required>
    <input type="date" name="due_date" 
       required 
       min="<?= htmlspecialchars(date('Y-m-d')) ?>" 
       max="<?= htmlspecialchars($task['due_date']) ?>" 
       value="<?= htmlspecialchars($task['due_date']) ?>">
    <select name="priority" required>
        <option value="Tinggi">Tinggi</option>
        <option value="Sedang">Sedang</option>
        <option value="Rendah">Rendah</option>
    </select>
    <button type="submit">Tambah Subtugas</button>
</form>

    <!-- Tombol "Semua Selesai" dipindahkan ke bawah form tambah tugas -->
    <form method="POST" style="margin-top: 10px;">
        <button type="submit" name="mark_all_completed">✔ Semua Selesai</button>
    </form>


    <!-- Tabel Daftar Subtugas -->
    <table>
        <thead>
            <tr>
                <th>Subtugas</th>
                <th>Tenggat</th>
                <th>Prioritas</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subtasks as $subtask): ?>
                <tr>
                    <td><?= htmlspecialchars($subtask['subtask']) ?></td>
                    <td><?= date('Y-m-d', strtotime($subtask['due_date'])) ?></td>
                    <td><strong><?= htmlspecialchars($subtask['priority']) ?></strong></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="checkbox" name="subtask_status" value="1" class="checkbox" <?= $subtask['status'] == 1 ? 'checked' : ''; ?>
                                   onclick="this.form.submit();">
                            <input type="hidden" name="subtask_id" value="<?= $subtask['id'] ?>">
                            <input type="hidden" name="status" value="<?= $subtask['status'] == 1 ? 0 : 1 ?>"> <!-- Toggle status -->
                        </form>
                    </td>
                    <td class="action-buttons">
                        <!-- Link Edit Subtugas -->
                        <a href="edit_subtask.php?subtask_id=<?= $subtask['id'] ?>" class="edit-btn">✏️ Edit</a>
    
                        <!-- Link Hapus Subtugas -->
                        <a href="?delete_subtask=<?= $subtask['id'] ?>" class="delete-btn" onclick="return confirm('Hapus subtugas ini?');">🗑️ Hapus</a>
                    </td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
