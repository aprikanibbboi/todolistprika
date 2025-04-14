<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (strlen($password) < 6) {
        $error = "Password harus memiliki minimal 6 karakter!";
    } elseif (preg_match('/\s/', $username)) {  // Cek apakah ada spasi
        $error = "Username tidak boleh mengandung spasi!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashed_password])) {
            echo "<script>alert('Akun berhasil dibuat!'); window.location.href='login.php';</script>";
            exit();
        } else {
            $message = "Gagal mendaftar!";
        }
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
                <input type="password" name="password" placeholder="Password minimal 6 karakter" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Login sekarang</a></p>
        <?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.querySelector("form");
        form.addEventListener("submit", function(event) {
            const username = document.querySelector('input[name="username"]').value;
            const password = document.querySelector('input[name="password"]').value;

            if (password.length < 6) {
                alert("Password harus minimal 6 karakter!");
                event.preventDefault();
            } else if (/\s/.test(username)) { // Cek apakah ada spasi dalam username
                alert("Username tidak boleh mengandung spasi!");
                event.preventDefault();
            }
        });
    });
    </script>
</body>
</html>