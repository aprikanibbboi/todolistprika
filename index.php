<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo "<script>alert('Anda harus login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Tambah tugas baru
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task = trim($_POST['task']);
    $due_date = trim($_POST['due_date']);

    if (empty($task)) {
        $error_message = "Harap isi judul tugas!";
    } elseif (empty($due_date)) {
        $error_message = "Harap isi tanggal tenggat!";
    } else {
        $status = 0; // Status default: Belum Selesai
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, task, due_date, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $task, $due_date, $status]);
        $_SESSION['success_message'] = "Tugas berhasil ditambahkan!";
        header("Location: index.php");
        exit();
    }
}

// Menghapus tugas
if (isset($_GET['delete_task'])) {
    $task_id = $_GET['delete_task'];
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    header("Location: index.php");
    exit();
}
// Perbarui status tugas yang sudah lewat tenggat
$updateStmt = $pdo->prepare("UPDATE tasks SET status = 2 WHERE due_date < CURDATE() AND status = 0");
$updateStmt->execute();

// Mengambil semua tugas
$tasks = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ?");
$tasks->execute([$user_id]);
$tasks = $tasks->fetchAll();

if (isset($_SESSION['success_message'])) {
    echo "<script>alert('{$_SESSION['success_message']}');</script>";
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>To-Do List</title>
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
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        h2 {
            color: white;
        }
        .logout-button {
            background: #8EACCD;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none;
            transition: background 0.3s;
        }
        .logout-button:hover {
            background: rgb(191, 168, 201);
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
            background-color:rgb(191, 168, 201);
            color: white;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        button:hover {
            background-color:rgb(233, 205, 224);
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
        .status {
            font-weight: bold;
        }
        .status.completed {
            color: green;
        }
        .status.pending {
            color: red;
        }
        .task-link {
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .task-link:hover {
            color: rgb(233, 205, 224);
            text-decoration: underline;
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
        .task-late {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>To-Do List</h2>
            <a href="logout.php" class="logout-button" onclick="return confirm('Apakah Anda yakin ingin logout?');">Logout</a>
        </div>

        <!-- Form Tambah Tugas -->
        <form method="POST" onsubmit="return validateForm();">
    <input type="text" id="task" name="task" placeholder="Tambahkan tugas baru">
    <input type="date" id="due_date" name="due_date" min="<?= date('Y-m-d') ?>">
    <button type="submit">Tambah Tugas</button>
</form>


        <!-- Tabel Daftar Tugas -->
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Tugas</th>
                    <th>Tenggat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach ($tasks as $task): ?>
        <?php 
            $is_late = strtotime($task['due_date']) < strtotime(date('Y-m-d')); 
            $is_completed = $task['status'] == 1;
        ?>
        <tr>
            <td>
                <span class="status <?= $task['status'] == 1 ? 'completed' : ($task['status'] == 2 ? 'task-late' : 'pending'); ?>">
                <?= $task['status'] == 1 ? 'Sudah Selesai' : ($task['status'] == 2 ? 'Lewat Tanggal Tenggat' : 'Belum Selesai') ?>
                </span>
            </td>
            <td>
                <a href="subtasks.php?task_id=<?= $task['id'] ?>" class="task-link">
                    <?= htmlspecialchars($task['task']) ?>
                </a>
            </td>
            <td>
                <?= date('Y-m-d', strtotime($task['due_date'])) ?>
            </td>
            <td class="action-buttons">
                <!-- Menyembunyikan tombol edit jika tugas sudah selesai atau lewat tenggat waktu -->
                <?php if (!$is_late && !$is_completed): ?>
                    <a href="edit_task.php?task_id=<?= $task['id'] ?>" class="edit-btn"> Edit</a>
                <?php endif; ?>
                
                <a href="?delete_task=<?= $task['id'] ?>" class="delete-btn" onclick="return confirm('Hapus tugas ini?');">Hapus</a>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>


        </table>
    </div>
    <script>
    function validateForm() {
        let task = document.getElementById("task").value.trim();
        let dueDate = document.getElementById("due_date").value.trim();

        if (task === "") {
            alert("Harap isi judul tugas!");
            return false; // Mencegah form dikirim
        }
        if (dueDate === "") {
            alert("Harap isi tanggal tenggat!");
            return false; // Mencegah form dikirim
        }

        return true; // Mengizinkan form dikirim jika validasi lolos
    }
</script>
</body>
</html>