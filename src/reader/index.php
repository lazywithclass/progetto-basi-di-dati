<?php
session_start();
require_once 'check-logged.php';

$username = $_SESSION['username'];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header('Location: /reader/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Quibreria - Main Page</title>
    <?php include '../header-libraries.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-4">
        <div id="content">
            <h1>Welcome, <?php echo $username; ?>!</h1>
            <p>Select an option from the menu above to proceed.</p>
        </div>
    </div>
</body>
</html>
