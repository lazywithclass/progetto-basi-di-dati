<?php
session_start();
require_once '../config.php';
require_once 'check-logged.php';


$db = get_connection();
$query = "SELECT id, name, surname FROM author";
$result = pg_prepare($db, 'select_author_query', $query);
$result = pg_execute($db, 'select_author_query', array());

$authors = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $authors[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $isbn = $_POST['isbn'];
    $title = $_POST['title'];
    $publisher = $_POST['publisher'];
    $plot = $_POST['plot'];
    $selected_authors = $_POST['authors'];

    $db = get_connection();
    $query = "INSERT INTO book (isbn, title, publisher, plot) VALUES ($1, $2, $3, $4)";
    $result = pg_prepare($db, 'insert_book_query', $query);
    $result = pg_execute($db, 'insert_book_query', array($isbn, $title, $publisher, $plot));

    foreach ($selected_authors as $author_id) {
        $query = "INSERT INTO author_book (author_id, isbn) VALUES ($1, $2)";
        $result = pg_prepare($db, 'insert_author_book_query', $query);
        $result = pg_execute($db, 'insert_author_book_query', array($author_id, $isbn));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $isbn = $_POST['isbn'];
    $title = $_POST['title'];
    $publisher = $_POST['publisher'];
    $plot = $_POST['plot'];
    $selected_authors = $_POST['authors'];

    $db = get_connection();
    $query = "UPDATE book SET title = $2, publisher = $3, plot = $4 WHERE isbn = $1";
    $result = pg_prepare($db, 'update_book_query', $query);
    $result = pg_execute($db, 'update_book_query', array($isbn, $title, $publisher, $plot));

    $query = "DELETE FROM author_book WHERE isbn = $1";
    $result = pg_prepare($db, 'delete_author_book_query', $query);
    $result = pg_execute($db, 'delete_author_book_query', array($isbn));

    foreach ($selected_authors as $author_id) {
        $query = "INSERT INTO author_book (author_id, isbn) VALUES ($1, $2)";
        $result = pg_prepare($db, 'insert_author_book_query', $query);
        $result = pg_execute($db, 'insert_author_book_query', array($author_id, $isbn));
    }
}

if (isset($_GET['delete'])) {
    $isbn = $_GET['delete'];

    $db = get_connection();
    $query = "DELETE FROM book WHERE isbn = $1";
    $result = pg_prepare($db, 'delete_book_query', $query);
    $result = pg_execute($db, 'delete_book_query', array($isbn));

    $query = "DELETE FROM author_book WHERE isbn = $1";
    $result = pg_prepare($db, 'delete_author_book_query', $query);
    $result = pg_execute($db, 'delete_author_book_query', array($isbn));
}

$db = get_connection();
$query = "SELECT * FROM book";
$result = pg_prepare($db, 'select_book_query', $query);
$result = pg_execute($db, 'select_book_query', array());

$books = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $books[] = $row;
    }
}

$editBook = null;
$selectedAuthors = [];
if (isset($_GET['edit'])) {
    $isbn = $_GET['edit'];

    $db = get_connection();
    $query = "SELECT * FROM book WHERE isbn = $1";
    $result = pg_prepare($db, 'select_book_by_isbn_query', $query);
    $result = pg_execute($db, 'select_book_by_isbn_query', array($isbn));
    if ($result) {
        $editBook = pg_fetch_assoc($result);
    }

    $query = "SELECT author_id FROM author_book WHERE isbn = $1";
    $result = pg_prepare($db, 'select_authors_by_isbn_query', $query);
    $result = pg_execute($db, 'select_authors_by_isbn_query', array($isbn));

    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $selectedAuthors[] = $row['author_id'];
        }
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h1>Manage Books</h1>

        <form method="POST" class="mb-4">
            <div class="form-group">
                <label>ISBN:</label>
                <input type="text" name="isbn" class="form-control" value="<?php echo htmlspecialchars($editBook['isbn'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Title:</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($editBook['title'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Publisher:</label>
                <input type="text" name="publisher" class="form-control" value="<?php echo htmlspecialchars($editBook['publisher'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Plot:</label>
                <textarea name="plot" class="form-control" required><?php echo htmlspecialchars($editBook['plot'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Authors:</label>
                <select name="authors[]" class="form-control" multiple required>
                    <?php foreach ($authors as $author): ?>
                        <option value="<?php echo htmlspecialchars($author['id']); ?>"
                            <?php echo (isset($selectedAuthors) && in_array($author['id'], $selectedAuthors)) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($author['name'] . ' ' . $author['surname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (isset($editBook)): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($editBook['id']); ?>">
                <button type="submit" name="update" class="btn btn-secondary">Update Book</button>
            <?php else: ?>
                <button type="submit" name="create" class="btn btn-primary">Add Book</button>
            <?php endif; ?>
        </form>

        <h2>Books List</h2>
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
                    <td class="text-nowrap"><?php echo htmlspecialchars($book['isbn']); ?></td>
                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                    <td><?php echo htmlspecialchars($book['publisher']); ?></td>
                    <td><?php echo htmlspecialchars($book['plot']); ?></td>
                    <td class="text-nowrap">
                        <a href="?edit=<?php echo $book['isbn']; ?>" class="btn btn-info btn-sm">Edit</a>
                        <a href="?delete=<?php echo $book['isbn']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="./js/index.js"></script>
</body>
</html>
