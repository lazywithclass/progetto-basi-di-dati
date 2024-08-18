<?php
session_start();
require_once '../config.php';
require_once '../check-logged.php';

$db = get_connection();

$query = "SELECT id_library FROM library_reader WHERE id_reader = $1";
$result = pg_prepare($db, 'select_user_library_query', $query);
$result = pg_execute($db, 'select_user_library_query', array($_SESSION['id']));
if ($result) {
    $row = pg_fetch_assoc($result);
    if ($row) {
        $userLibraryId = $row['id_library'];
    } else {
        $error = "Could not find a library linked to this user";
    }
} else {
    $error = pg_last_error($db);
}

$searchResults = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['search'])) {
        $searchTerm = $_POST['search_term'];

        $query = "
          SELECT b.*, pc.id as id_physical_copy, br.*
          FROM book b
          JOIN physical_copy pc ON pc.id_book = b.id
          JOIN branch br ON br.id = pc.id_branch
          WHERE br.id_library = $1 AND (b.title ILIKE $2 OR b.isbn ILIKE $2)";

        $result = pg_prepare($db, 'search_books_query', $query);
        $result = pg_execute($db, 'search_books_query', array($userLibraryId, '%' . $searchTerm . '%'));

        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $searchResults[] = $row;
            }
        }
    }

    if (isset($_POST['loan'])) {
        $query = "INSERT INTO loan(id_reader, id_physical_copy, start_date, length) VALUES($1, $2, $3, $4)";

        $result = pg_prepare($db, 'search_books_query', $query);
        $result = pg_execute($db, 'search_books_query', array($_SESSION['id'], $_POST['id_physical_copy'], date('m/d/Y h:i:s a'), 30));

        if (!$result) {
            $error = pg_last_error($db);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Books</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h1>Loan Books</h1>

        <form method="POST" class="mb-4">
            <div class="form-group">
                <label for="search_term">Search by Title or ISBN:</label>
                <input type="text" name="search_term" id="search_term" class="form-control" placeholder="Enter title or ISBN" required>
            </div>
            <button type="submit" name="search" class="btn btn-primary">Search</button>
        </form>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($searchResults)): ?>
            <h2>Search Results</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>ISBN</th>
                        <th>Branch City</th>
                        <th>Branch Address</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($searchResults as $book): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                        <td><?php echo htmlspecialchars($book['city']); ?></td>
                        <td><?php echo htmlspecialchars($book['address']); ?></td>
                        <td>
                            <form action="loan-books.php" method="POST">
                                <input type="hidden" name="id_physical_copy" value="<?php echo htmlspecialchars($book['id_physical_copy']); ?>">
                                <button type="submit" name="loan" class="btn btn-success">Loan</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])): ?>
            <p>No books found matching your search criteria in your library's branches.</p>
        <?php endif; ?>
    </div>

</body>
</html>
