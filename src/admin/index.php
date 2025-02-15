<?php
session_start();
require_once 'check-logged.php';


$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header('Location: /admin/login.php');
    exit;
}

// small easter egg
if ($username == "conan") {
    $username = "Conan The Librarian";
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
            <h1>Welcome, <?= $username ?>!</h1>
            <?php if ($username == "brooks") { // easter eggs everywhere ?>
                <small>Brooks was here</small>
            <?php } ?>
            <p>Select an option from the menu above to proceed.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="./js/index.js"></script>
</body>
</html>
