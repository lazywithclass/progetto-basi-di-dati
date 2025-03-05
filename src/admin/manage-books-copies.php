<?php
session_start();
require_once '../config.php';
require_once 'check-logged.php';
require_once './lib/books.php';


$librarian_id = $_SESSION['id'] ?? null;
$db = get_connection();

if (isset($_POST['action']) && $_POST['action'] == "increase") {
    $id_book = $_POST['id_book'];
    $id_branch = $_POST['id_branch'];
    $query = "INSERT INTO physical_copy (id_book, id_branch) VALUES ($1, $2)";
    $result = pg_prepare($db, 'insert_physical_copy', $query);
    $result = pg_execute($db, 'insert_physical_copy', array($id_book, $id_branch));
    if ($result) {
        $success = "Physical copy added correctly";
    } else {
        $error = pg_last_error($db);
    }
}

if (isset($_POST['action']) && $_POST['action'] == "decrease") {
    $id_book = $_POST['id_book'];
    $id_branch = $_POST['id_branch'];

    $query = "SELECT *
        FROM physical_copy pc WHERE id NOT IN (
        SELECT pc.id
        FROM physical_copy pc 
        JOIN loan l ON pc.id = l.id_physical_copy
        WHERE l.is_returned = false)
        AND pc.id_book = $1 AND pc.id_branch = $2
        LIMIT 1";
    $result = pg_prepare($db, 'find_available_physical_copy', $query);
    $result = pg_execute($db, 'find_available_physical_copy', array($id_book, $id_branch));

    $record = pg_fetch_assoc($result);
    if (!$record) {
        $error = "No available copies";
    }

    $query = "DELETE FROM physical_copy WHERE id = $1;";
    $result = pg_prepare($db, 'delete_physical_copy', $query);
    $result = pg_execute($db, 'delete_physical_copy', array($record['id']));
    if ($result) {
        $success = "Physical copy removed correctly";
    } else {
        $error = pg_last_error($db);
    }
}

$query = "
    SELECT count(pc.*) as total, b.*, br.id as id_branch, br.city || ' ' || br.address as branch
    FROM physical_copy pc 
    JOIN branch br on br.id = pc.id_branch
    JOIN library_librarian ll on ll.id_library = br.id_library
    JOIN book b on pc.id_book = b.id
    GROUP BY pc.id_book, br.id, b.id, ll.id_librarian
    HAVING ll.id_librarian = $1;";
pg_prepare($db, 'select_physical_copies', $query);
$result = pg_execute($db, 'select_physical_copies', array($librarian_id));

$books = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $books[] = $row;
    }
} else {
    $error = pg_last_error($db);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Books Copies</title>
    <?php include '../header-libraries.php'; ?>
</head>
<body>
<?php include 'navbar.php'; ?>
<?php include '../handle-result.php'; ?>

<div class="container mt-4">
    <h1>Manage Books Copies</h1>

    <p>Books available in your libraries</p>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Title</th>
            <th>Branch</th>
            <th>Physical Copies</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($books as $book): ?>
            <tr>
                <td><?= htmlspecialchars($book['title']); ?></td>
                <td><?= htmlspecialchars($book['branch']); ?></td>
                <td><?= htmlspecialchars($book['total']); ?></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="id_book" value="<?= $book['id']; ?>">
                        <input type="hidden" name="id_branch" value="<?= $book['id_branch']; ?>">
                        <button type="submit" name="action" value="increase" class="btn btn-success btn-sm">+</button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="id_book" value="<?= $book['id']; ?>">
                        <input type="hidden" name="id_branch" value="<?= $book['id_branch']; ?>">
                        <button type="submit" name="action" value="decrease" class="btn btn-danger btn-sm">-</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($books)): ?>
            <tr>
                <td colspan="3" class="text-center">No physical copies found</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
