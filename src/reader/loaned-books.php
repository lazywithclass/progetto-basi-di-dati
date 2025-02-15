<?php
session_start();
require_once '../config.php';
require_once 'check-logged.php';

$db = get_connection();

$id_reader = $_SESSION['id'];

$query = "
    SELECT b.title, b.isbn, pc.id as id_physical_copy, l.start_date, l.length, br.city, br.address
    FROM loan l
    JOIN physical_copy pc ON l.id_physical_copy = pc.id
    JOIN book b ON b.id = pc.id_book
    JOIN branch br ON br.id = pc.id_branch
    WHERE l.id_reader = $1 AND l.end_date IS NULL
    ORDER BY l.end_date ASC";

$result = pg_prepare($db, 'select_loans_query', $query);
$result = pg_execute($db, 'select_loans_query', array($id_reader));

$loans = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $loans[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Loaned Books</title>
    <?php include '../header-libraries.php'; ?>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h1>Loaned Books</h1>
        <p>Listed here are books that you loaned, to return a book bring it to its branch</p>

        <?php if (count($loans) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>ISBN</th>
                        <th>Branch (City)</th>
                        <th>Branch (Address)</th>
                        <th>Start date</th>
                        <th>Loan length (days)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $loan): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($loan['title']); ?></td>
                        <td><?php echo htmlspecialchars($loan['isbn']); ?></td>
                        <td><?php echo htmlspecialchars($loan['city']); ?></td>
                        <td><?php echo htmlspecialchars($loan['address']); ?></td>
                        <td><?php echo htmlspecialchars($loan['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($loan['length']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                You currently have no active loans.
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
