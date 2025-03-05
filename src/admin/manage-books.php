<?php
session_start();
require_once '../config.php';
require_once 'check-logged.php';
require_once './lib/books.php';
require_once './lib/librarians.php';


$db = get_connection();
$error = '';

$query = "SELECT id, name, surname FROM author";
$result = pg_prepare($db, 'select_author_query', $query);
$result = pg_execute($db, 'select_author_query', array());
$authors = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $authors[] = $row;
    }
}
if (!$result) {
    $error = pg_last_error($db);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $isbn = $_POST['isbn'];
        $title = $_POST['title'];
        $publisher = $_POST['publisher'];
        $plot = $_POST['plot'];
        $selected_authors = $_POST['authors'];

        $error = insert_book_for_librarian($isbn, $title, $publisher, $plot, $selected_authors);
        if (empty($error)) {
            $success = "Book added successfully";
        }
    }

    if (isset($_POST['update'])) {
        $id_book = $_GET['edit'];
        $isbn = $_POST['isbn'];
        $title = $_POST['title'];
        $publisher = $_POST['publisher'];
        $plot = $_POST['plot'];
        $selected_authors = $_POST['authors'];

        $error = update_book_for_librarian($id_book, $isbn, $title, $publisher, $plot, $selected_authors);
        if (empty($error)) {
            $success = "Book updated successfully";
        }
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $db = get_connection();
    $query = "DELETE FROM book WHERE id = $1";
    $result = pg_prepare($db, 'delete_book_query', $query);
    $result = pg_execute($db, 'delete_book_query', array($id));
    if ($result) {
        $success = "Book deleted successfully";
    } else {
        $error = pg_last_error($db);
    }
}

$editBook = null;
$selectedAuthors = [];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];

    $db = get_connection();
    $query = "SELECT * FROM book WHERE id = $1";
    $result = pg_prepare($db, 'select_book_query', $query);
    $result = pg_execute($db, 'select_book_query', array($id));
    if ($result) {
        $editBook = pg_fetch_assoc($result);
    } else {
        $error = pg_last_error($db);
    }

    $query = "SELECT id_author FROM author_book WHERE id_book = $1";
    $result = pg_prepare($db, 'select_authors_query', $query);
    $result = pg_execute($db, 'select_authors_query', array($id));

    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $selectedAuthors[] = $row['id_author'];
        }
    } else {
        $error = pg_last_error($db);
    }
}

$search = $_GET['search'] ?? '';
$books = find_books_for_librarian($_SESSION['id'], $search);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Books</title>
    <?php include '../header-libraries.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <?php include '../handle-result.php'; ?>

    <div class="container mt-4">
        <h1>Manage Books</h1>

        <form method="POST" class="mb-4">
            <div class="form-group">
                <label>ISBN:</label>
                <input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($editBook['isbn'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Title:</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editBook['title'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Publisher:</label>
                <input type="text" name="publisher" class="form-control" value="<?= htmlspecialchars($editBook['publisher'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Plot:</label>
                <textarea name="plot" class="form-control" required><?= htmlspecialchars($editBook['plot'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Authors:</label>
                <select name="authors[]" class="form-control" multiple required>
                    <?php foreach ($authors as $author): ?>
                        <option value="<?= htmlspecialchars($author['id']); ?>"
                            <?= (isset($selectedAuthors) && in_array($author['id'], $selectedAuthors)) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($author['name'] . ' ' . $author['surname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (isset($editBook)): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($editBook['id']); ?>">
                <button type="submit" name="update" class="btn btn-secondary">Update Book</button>
            <?php else: ?>
                <button type="submit" name="create" class="btn btn-success">Add Book</button>
            <?php endif; ?>
        </form>

        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by ISBN or title" value="<?= htmlspecialchars($_GET['search'] ?? ''); ?>">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>

        <h2>Books List</h2>
        <p>Listed here are books that are available in your libraries</p>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ISBN</th>
                    <th>Title</th>
                    <th>Publisher</th>
                    <th>Plot</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                <tr>
                    <td class="text-nowrap"><?= htmlspecialchars($book['isbn']); ?></td>
                    <td><?= htmlspecialchars($book['title']); ?></td>
                    <td><?= htmlspecialchars($book['publisher']); ?></td>
                    <td><?= htmlspecialchars($book['plot']); ?></td>
                    <td class="text-nowrap">
                        <a href="?edit=<?= $book['id']; ?>" class="btn btn-info btn-sm">Edit</a>
                        <a href="?delete=<?= $book['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php
                if (empty($books)) {
                    echo "<tr><td colspan='7' class='text-center'>No books found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
