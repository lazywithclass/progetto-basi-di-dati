<?php
session_start();
require_once '../config.php';
require_once 'check-logged.php';

$db = get_connection();

$loans = [];
$error = '';
$id = $_SESSION['id'];

$query = "
    SELECT l.id, l.length, l.start_date, b.title, r.name AS reader_name, r.surname AS reader_surname
    FROM loan l
    JOIN physical_copy pc ON l.id_physical_copy = pc.id
    JOIN reader r ON l.id_reader = r.id
    JOIN book b ON pc.id_book = b.id
    JOIN branch br ON pc.id_branch = br.id
    JOIN library_librarian ll ON br.id_library = ll.id_library
    WHERE ll.id_librarian = $1 AND l.is_returned = FALSE";
$result = pg_prepare($db, 'select_loans', $query);
$result = pg_execute($db, 'select_loans', array($id));

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $loans[] = $row;
    }
} else {
    $error = pg_last_error($db);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['return'])) {
        $loanId = $_POST['loan_id'];

        $query = "UPDATE loan SET is_returned = TRUE WHERE id = $1";
        $result = pg_prepare($db, 'return_loan_query', $query);
        $result = pg_execute($db, 'return_loan_query', array($loanId));

        if ($result) {
            $success = "Loan successfully returned";
            header('Location: manage-loans.php');
        } else {
            $error = pg_last_error($db);
        }
    } elseif (isset($_POST['extend'])) {
        $loanId = $_POST['loan_id'];

        $query = "SELECT extend_loan($1)";
        $result = pg_prepare($db, 'extend_loan_query', $query);
        $result = pg_execute($db, 'extend_loan_query', array($loanId));

        if ($result) {
            $success = "Loan successfully extended";
        } else {
            $error = pg_last_error($db);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Loans</title>
    <?php include '../header-libraries.php'; ?>
</head>
<body>

    <?php include 'navbar.php'; ?>
    <?php include '../handle-result.php'; ?>

    <div class="container mt-4">
        <h1>Manage Loans</h1>
        <p>Listed here are loans that are not yet returned to your libraries</p>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Reader</th>
                    <th>Loan date</th>
                    <th>Days left</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($loans as $loan): ?>
                    <?php
                        $loan_return_date = (new DateTime($loan['start_date']))->modify("+".$loan['length']." days");
                        $days_diff = (new DateTime())->diff($loan_return_date)->format("%r%a"); // do not use abs value
                    ?>
                <tr>
                    <td><?= htmlspecialchars($loan['title']); ?></td>
                    <td><?= htmlspecialchars($loan['reader_name'] . ' ' . $loan['reader_surname']); ?></td>
                    <td><?= $loan['start_date'] ?></td>
                    <td class="<?= ($days_diff[0] == "-") ? "text-danger" : "" ?>"><?= $days_diff ?></td>
                    <td class="text-nowrap">
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="loan_id" value="<?= $loan['id']; ?>">
                            <button type="submit" name="return" class="btn btn-success btn-sm">Mark as Returned</button>
                        </form>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="loan_id" value="<?= $loan['id']; ?>">
                            <button type="submit" name="extend" class="btn btn-warning btn-sm" <?= $loan['extension_count'] >= 3 ? 'disabled' : ''; ?>>Extend Loan</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php
                if (empty($loans)) {
                    echo "<tr><td colspan='7' class='text-center'>No loans found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
