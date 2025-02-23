<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $email, $password])) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Gagal mendaftar!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #F0C1E1, #8EACCD);
            color: white;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column;
            padding: 20px;
        }
        .container {
            width: 350px;
            max-width: 600px;
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        h2 {
            text-align: center;
            color:rgb(255, 255, 255);
            margin-bottom: 20px;
        }
        .input-group {
            margin-bottom: 15px;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 92%; /* Mengubah menjadi 100% untuk responsif */
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
            border-color: rgb(135, 206, 235);
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color:rgb(191, 168, 201);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: rgb(233, 205, 224);
        }
        .error {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
        p {
            text-align: center;
        }
        a {
            color: rgb(180, 147, 195);
            text-decoration: none;
            transition: color 0.3s;
        }
        a:hover {
            color: rgb(233, 205, 224);
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form method="POST">
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Login sekarang</a></p>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    </div>
</body>
</html>