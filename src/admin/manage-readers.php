<?php
session_start();
require_once '../config.php';
require_once 'check-logged.php';
require_once 'lib/reader.php';


$db = get_connection();

$readers = find_readers_for_librarian($_SESSION['id']);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $fiscal_code = $_POST['fiscal_code'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    echo($password);

    if (isset($_POST['create'])) {
        $query = "INSERT INTO reader (username, password_hash, fiscal_code, name, surname) VALUES ($1, $2, $3, $4, $5)";
        $result = pg_prepare($db, 'insert_reader_query', $query);
        $result = pg_execute($db, 'insert_reader_query', array($username, $password, $fiscal_code, $name, $surname));

        if (!$result) {
            $error = pg_last_error($db);
        }
    } elseif (isset($_POST['update'])) {
        $id = $_GET['edit'];
        $query = "UPDATE reader SET username = $1, fiscal_code = $2, name = $3, surname = $4, password_hash = $5 WHERE id = $6";
        $result = pg_prepare($db, 'update_reader_query', $query);
        $result = pg_execute($db, 'update_reader_query', array($username, $fiscal_code, $name, $surname, $password, $id));

        if (!$result) {
            $error = pg_last_error($db);
        }
    } elseif (isset($_POST['reset'])) {
        echo("AAAAAAAAAAAAAAA");
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM reader WHERE id = $1";
    $result = pg_prepare($db, 'delete_reader_query', $query);
    $result = pg_execute($db, 'delete_reader_query', array($id));

    if (!$result) {
        $error = pg_last_error($db);
    }
}

$editReader = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM reader WHERE id = $1";
    $result = pg_prepare($db, 'select_reader_by_id_query', $query);
    $result = pg_execute($db, 'select_reader_by_id_query', array($id));

    if ($result) {
        $editReader = pg_fetch_assoc($result);
    } else {
        $error = pg_last_error($db);
    }
}

if (isset($_GET['reset-overdue'])) {
    $id = $_GET['reset-overdue'];
    $id_library = $_GET['library-id'];

    $query = "UPDATE library_reader SET overdue_returns = 0 WHERE id_reader = $1 AND id_library = $2";
    $result = pg_prepare($db, 'update_reader_query', $query);
    $result = pg_execute($db, 'update_reader_query', array($id, $id_library));

    if (!$result) {
        $error = pg_last_error($db);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Readers</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h1>Manage Readers</h1>
        <form method="POST" class="mb-4">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($editReader['username'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Fiscal Code:</label>
                <input type="text" name="fiscal_code" class="form-control" value="<?php echo htmlspecialchars($editReader['fiscal_code'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($editReader['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Surname:</label>
                <input type="text" name="surname" class="form-control" value="<?php echo htmlspecialchars($editReader['surname'] ?? ''); ?>" required>
            </div>
            <?php if (isset($editReader)): ?>
                <button type="submit" name="update" class="btn btn-secondary">Update Reader</button>
            <?php else: ?>
                <button type="submit" name="create" class="btn btn-primary">Add Reader</button>
            <?php endif; ?>
        </form>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <h2>Readers List</h2>
        <p>Listed here are readers that are registered to your libraries</p>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Library name</th>
                    <th>Overdue count</th>
                    <th>Fiscal Code</th>
                    <th>Name</th>
                    <th>Surname</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($readers as $reader): ?>
                <tr>
                    <td><?php echo htmlspecialchars($reader['username']); ?></td>
                    <td><?php echo htmlspecialchars($reader['library_name']); ?></td>
                    <td><?php echo htmlspecialchars($reader['overdue_returns']); ?></td>
                    <td><?php echo htmlspecialchars($reader['fiscal_code']); ?></td>
                    <td><?php echo htmlspecialchars($reader['name']); ?></td>
                    <td><?php echo htmlspecialchars($reader['surname']); ?></td>
                    <td class="text-nowrap">
                        <a href="?reset-overdue=<?php echo $reader['id']; ?>&library-id=<?php echo $reader['library_id']; ?>" class="btn btn-info btn-sm" onclick="return confirm('Are you sure you want to reset this reader\'s overdue count?')">Reset overdue</a>
                        <a href="?edit=<?php echo $reader['id']; ?>" class="btn btn-info btn-sm">Edit</a>
                        <a href="?delete=<?php echo $reader['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this reader?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="./js/index.js"></script>
</body>
</html>
