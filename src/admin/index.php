<?php
session_start();
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protected Page</title>
</head>
<body>
    <h2>Welcome!</h2>
    <p>You are logged in as <?php echo htmlspecialchars($_SESSION['username']); ?>.</p>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>
