<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $fiscal_code = trim($_POST['fiscal_code']);
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);

    $errors = [];

    if (empty($username)) {
        $errors[] = 'Username is required.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($fiscal_code)) {
        $errors[] = 'Fiscal code is required.';
    }

    if (empty($name)) {
        $errors[] = 'Name is required.';
    }

    if (empty($surname)) {
        $errors[] = 'Surname is required.';
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $db = get_connection();
        $query = "INSERT INTO readers (username, password_hash, fiscal_code, name, surname) VALUES ($1, $2, $3, $4, $5)";
        $result = pg_prepare($db, 'create_reader_query', $query);
        $result = pg_execute($db, 'create_reader_query', array($username, $password_hash, $fiscal_code, $name, $surname));

        if ($result) {
            $success = "Reader created successfully.";
        } else {
            $errors[] = 'Error creating user: ' . pg_last_error($db);
        }
    } else {
        $errors[] = 'Error creating user: ' . pg_last_error($db);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quibreria - Create User</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo implode('<br>', $errors); ?>
            </div>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <a href="index.php" class="btn btn-primary">Back to Main Page</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
