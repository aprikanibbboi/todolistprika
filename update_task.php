<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Akses ditolak!";
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subtask_id'], $_POST['status'])) {
    $subtask_id = $_POST['subtask_id'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE subtasks SET status = ? WHERE id = ? AND task_id IN (SELECT id FROM tasks WHERE user_id = ?)");
    $stmt->execute([$status, $subtask_id, $user_id]);

    echo "Berhasil diperbarui!";
} else {
    echo "Permintaan tidak valid!";
}
?>
