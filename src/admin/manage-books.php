<?php
session_start();
require_once '../config.php';
require_once '../check-logged.php';
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

$branches = find_librarian_branches($_SESSION['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $id_branch = $_POST['id_branch'];
        $isbn = $_POST['isbn'];
        $title = $_POST['title'];
        $publisher = $_POST['publisher'];
        $plot = $_POST['plot'];
        $selected_authors = $_POST['authors'];
        $copies_number = $_POST['copies_number'];

        insert_book_for_librarian($id_branch, $isbn, $title, $publisher, $plot, $selected_authors, $copies_number);
    }

    if (isset($_POST['update'])) {
        $id_book = $_GET['edit'];
        $id_branch = $_POST['id_branch'];
        $isbn = $_POST['isbn'];
        $title = $_POST['title'];
        $publisher = $_POST['publisher'];
        $plot = $_POST['plot'];
        $selected_authors = $_POST['authors'];
        $copies_number = $_POST['copies_number'];

        update_book_for_librarian($id_branch, $id_book, $isbn, $title, $publisher, $plot, $selected_authors, $copies_number);
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $db = get_connection();
    $query = "DELETE FROM book WHERE id = $1";
    $result = pg_prepare($db, 'delete_book_query', $query);
    $result = pg_execute($db, 'delete_book_query', array($id));
    if (!$result) {
        $error = pg_last_error($db);
    }

}

$books = find_books_for_librarian($_SESSION['id']);

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

    $query = "SELECT copies_number FROM physical_copy WHERE id_book = $1";
    $result = pg_prepare($db, 'select_copies_query', $query);
    $result = pg_execute($db, 'select_copies_query', array($id));
    $editBook['copies_number'] = pg_fetch_assoc($result)['copies_number'];

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
            <div class="form-group">
                <label>Branch:</label>
                <select name="id_branch" class="form-control" required>
                    <?php foreach ($branches as $branch): ?>
                        <option value="<?php echo htmlspecialchars($branch['id']); ?>"
                            <?php echo (isset($selectedBranch) && in_array($branch['id'], $selectedBranch)) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($branch['city'] . ' ' . $branch['address']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Copies number:</label>
                <input type="text" name="copies_number" class="form-control" value="<?php echo htmlspecialchars($editBook['copies_number'] ?? ''); ?>" required>
            </div>
            <?php if (isset($editBook)): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($editBook['id']); ?>">
                <button type="submit" name="update" class="btn btn-secondary">Update Book</button>
            <?php else: ?>
                <button type="submit" name="create" class="btn btn-primary">Add Book</button>
            <?php endif; ?>
        </form>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <h2>Books List</h2>
        <p>Listed here are books that are available in your libraries</p>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ISBN</th>
                    <th>Title</th>
                    <th>Publisher</th>
                    <th>Plot</th>
                    <th>Branch</th>
                    <th>Copies</th>
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
                    <td><?php echo htmlspecialchars($book['branch_name']); ?></td>
                    <td><?php echo htmlspecialchars($book['copies_number']); ?></td>
                    <td class="text-nowrap">
                        <a href="?edit=<?php echo $book['id']; ?>" class="btn btn-info btn-sm">Edit</a>
                        <a href="?delete=<?php echo $book['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="./js/index.js"></script>
</body>
</html>
