<?php
session_start();
require_once '../config.php';
require_once 'check-logged.php';

function change_password($librarian_id, $old_password, $new_password) {
    $db = get_connection();

    $query = "SELECT password_hash FROM librarian WHERE id = $1";
    $stmt = pg_prepare($db, 'select_librarian_password', $query);
    $result = pg_execute($db, 'select_librarian_password', array($librarian_id));
    if (!$result) {
        return [true, "Error executing SELECT query: " . pg_last_error($db)];
    }

    $row = pg_fetch_assoc($result);
    if (!password_verify($old_password, $row['password_hash'])) {
        return [true, 'Old password is incorrect.'];
    }

    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    $update_query = "UPDATE librarian SET password_hash = $1 WHERE id = $2";
    $stmt = pg_prepare($db, 'update_librarian_password', $update_query);
    if (!$stmt) {
        return [true, "Error preparing UPDATE query: " . pg_last_error($db)];
    }

    $result = pg_execute($db, 'update_librarian_password', array($hashed_password, $librarian_id));
    if (!$result) {
        return [true, "Error executing UPDATE query: " . pg_last_error($db)];
    }

    return [false, 'Password changed successfully.'];
}

$error = false;
$result = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $librarian_id = $_SESSION['id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = true;
        $result = 'New passwords do not match.';
    } else {
        [$error, $result] = change_password($librarian_id, $old_password, $new_password);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Change Password</title>
    <?php include '../header-libraries.php'; ?>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
                <h1>Change Password</h1>
                <div class="card-body">
                    <?php if (!empty($result)): ?>
                        <div class="alert <?= $error ? 'alert-danger' : 'alert-success' ?>" role="alert">
                            <?= htmlspecialchars($result); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="change_password.php">
                        <input type="hidden" name="librarian_id" value="<?= $_SESSION['id'] ?>">

                        <div class="form-group">
                            <label for="old_password">Old Password</label>
                            <input type="password" id="old_password" name="old_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Change Password</button>
                    </form>
                </div>
        </div>
    </div>
</div>
</body>
</html>
