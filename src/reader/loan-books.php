<?php
session_start();
require_once '../config.php';
require_once 'check-logged.php';


$db = get_connection();

$query = "SELECT id_library FROM library_reader WHERE id_reader = $1";
$result = pg_prepare($db, 'select_user_library_query', $query);
$result = pg_execute($db, 'select_user_library_query', array($_SESSION['id']));
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $userLibraries[] = $row['id_library'];
    }
} else {
    $error = pg_last_error($db);
}

$searchResults = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['search'])) {
        $searchTerm = $_POST['search_term'];
        // we do not show copies that are already loaned
        // but still we have a trigger that protects against race conditions
        $query = "
            SELECT 
                pc.id as id_physical_copy,
                b.*, 
                br.id as id_branch,
                br.city as branch_city,
                br.address as branch_address,
                COUNT(pc.id) AS num_copies, 
                a.name || ' ' || a.surname AS author
            FROM book b
            JOIN author_book ab ON b.id = ab.id_book
            JOIN author a ON a.id = ab.id_author
            JOIN physical_copy pc ON pc.id_book = b.id
            JOIN branch br ON br.id = pc.id_branch
            LEFT JOIN loan l ON l.id_physical_copy = pc.id AND l.is_returned = FALSE
            WHERE 
                br.id_library = ANY($1) 
                AND (b.title ILIKE $2 OR b.isbn ILIKE $2)
                AND (l.id IS NULL OR l.is_returned = TRUE)  -- exclude copies that have an active loan
            GROUP BY b.id, a.id, br.id, pc.id;";

        $result = pg_prepare($db, 'search_books_query', $query);
        $result = pg_execute($db, 'search_books_query', array('{' . implode(',', $userLibraries) . '}', '%' . $searchTerm . '%'));

        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $searchResults[] = $row;
            }
        }

        if (!$result) {
            $error = pg_last_error($db);
        }

        $books = [];
        foreach ($searchResults as $book) {
            $bookId = $book['id'];

            if (!isset($books[$bookId])) {
                $books[$bookId] = [
                    'id_physical_copy' => $book['id_physical_copy'],
                    'title' => $book['title'],
                    'isbn' => $book['isbn'],
                    'author' => $book['author'],
                    'branches' => []
                ];
            }

            $books[$bookId]['branches'][] = [
                'id' => $book['id_branch'],
                'city' => $book['branch_city'],
                'address' => $book['branch_address']
            ];
        }
    }

    if (isset($_POST['loan'])) {
        $query = "INSERT INTO loan(id_reader, id_physical_copy, start_date, length) VALUES($1, $2, $3, $4)";

        $id_reader = $_SESSION['id'];
        $id_physical_copy = $_POST['id_physical_copy'];
        $result = pg_prepare($db, 'search_books_query', $query);
        $result = pg_execute($db, 'search_books_query', array($id_reader, $id_physical_copy, date('m/d/Y h:i:s a'), 30));

        if (!$result) {
            $error = pg_last_error($db);
        } else {
            $success = "Book loan requested successfully";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Loan Books</title>
    <?php include '../header-libraries.php'; ?>
</head>
<body>

    <?php include 'navbar.php'; ?>
    <?php include '../handle-result.php'; ?>

    <div class="container mt-4">
        <h1>Loan Books</h1>
        <p>You could have as many loans as your library category allows, so for each library you could have a different limit</p>
        <form method="POST" class="mb-4">
            <div class="form-group">
                <label for="search_term">Search by Title or ISBN:</label>
                <input type="text" name="search_term" id="search_term" class="form-control" placeholder="Enter title or ISBN" required value="<?= $_POST['search_term'] ?>">
            </div>
            <button type="submit" name="search" class="btn btn-primary">Search</button>
        </form>

        <h2>Search Results</h2>
        <p>Results only include books for which there's an availability</p>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>ISBN</th>
                    <th>Author</th>
                    <th>Branch</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                <tr>
                    <td><?= htmlspecialchars($book['title']); ?></td>
                    <td><?= htmlspecialchars($book['isbn']); ?></td>
                    <td><?= htmlspecialchars($book['author']); ?></td>
                    <td>
                        <select name="id_branch" class="form-control">
                            <?php foreach ($book['branches'] as $branch): ?>
                                <option value="<?= htmlspecialchars($branch['id']); ?>">
                                    <?= htmlspecialchars($branch['city'] . ' - ' . $branch['address']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <form action="loan-books.php" method="POST">
                            <input type="hidden" name="id_physical_copy" value="<?= htmlspecialchars($book['id_physical_copy']); ?>">
                            <button type="submit" name="loan" class="btn btn-success">Loan request</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search']) && empty($searchResults)): ?>
                    <tr><td colspan="5" class='text-center'>No books found matching your search criteria in your library's branches.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
