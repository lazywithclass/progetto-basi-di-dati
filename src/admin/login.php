<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $db = get_connection();
    $query = "SELECT id, password_hash FROM librarian WHERE username = $1";
    $result = pg_prepare($db, 'login_query', $query);
    $result = pg_execute($db, 'login_query', array($username));

    if ($result) {
        $row = pg_fetch_assoc($result);
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['loggedin_admin'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['id'] = $row['id'];
            header('Location: /admin/index.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <?php include '../header-libraries.php'; ?>
</head>
<body>
    <?php include '../handle-result.php'; ?>
    <div class="container">
        <h2 class="my-4 text-center">Quibreria - Librarian login</h2>
        <p><a href="/reader/">Switch</a> to reader login.</p>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" class="form-control" required>
                <div class="invalid-feedback">Please enter your username.</div>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required>
                <div class="invalid-feedback">Please enter your password.</div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
    </div>
</body>
</html>
