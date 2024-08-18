<?php
session_start();
require_once '../config.php';
require_once '../check-logged.php';

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
    WHERE ll.id_librarian = $1";
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

        $query = "DELETE FROM loan WHERE id = $1";
        $result = pg_prepare($db, 'return_loan_query', $query);
        $result = pg_execute($db, 'return_loan_query', array($loanId));

        if (!$result) {
            $error = pg_last_error($db);
        } else {
            // TODO do this also elsewhere
            header('Location: manage-loans.php');
            exit;
        }
    } elseif (isset($_POST['extend'])) {
        $loanId = $_POST['loan_id'];

        $query = "UPDATE loan SET length = length + 7 WHERE id = $1";
        $result = pg_prepare($db, 'extend_loan_query', $query);
        $result = pg_execute($db, 'extend_loan_query', array($loanId));

        if (!$result) {
            $error = pg_last_error($db);
        } else {
            header('Location: manage-loans.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Loans</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h1>Manage Loans</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <p>Listed here are loans that are not yet returned to your libraries</p>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Reader</th>
                    <th>Days left</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($loans as $loan): ?>
                <tr>
                    <td><?php echo htmlspecialchars($loan['title']); ?></td>
                    <td><?php echo htmlspecialchars($loan['reader_name'] . ' ' . $loan['reader_surname']); ?></td>
                    <td><?php echo(strtotime(date('m/d/Y')) - strtotime($loan['start_date']) + $loan['length']); ?></td>
                    <td class="text-nowrap">
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                            <button type="submit" name="return" class="btn btn-success btn-sm">Mark as Returned</button>
                        </form>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                            <button type="submit" name="extend" class="btn btn-warning btn-sm" <?php echo $loan['extension_count'] >= 3 ? 'disabled' : ''; ?>>Extend Loan</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="./js/index.js"></script>
</body>
</html>
