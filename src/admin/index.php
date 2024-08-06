<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quibreria - Main Page</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="">Quibreria</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item"><a class="nav-link" href="#" id="create-user">Create Reader</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Create Book</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Register Book as Returned</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Zero Overdue Books</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Extend Lend</a></li>
            </ul>
            <span class="navbar-text mr-3">
                Logged in as <?php echo htmlspecialchars($username); ?>
            </span>
            <form class="form-inline my-2 my-lg-0" action="index.php" method="post">
                <button class="btn btn-outline-danger my-2 my-sm-0" type="submit" name="logout">Logout</button>
            </form>
        </div>
    </nav>

    <div class="container mt-4">
        <div id="content">
            <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
            <p>Select an option from the menu above to proceed.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="./js/index.js"></script>
</body>
</html>
