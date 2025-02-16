<?php
session_start();
require_once '../config.php';
require_once 'check-logged.php';


$db = get_connection();

// Fetching libraries for the dropdown
$query = "SELECT id, name FROM library";
$result = pg_prepare($db, 'select_libraries', $query);
$result = pg_execute($db, 'select_libraries', array());

$libraries = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $libraries[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $id_library = $_POST['id_library'];
    $city = $_POST['city'];
    $address = $_POST['address'];

    $query = "INSERT INTO branch (id_library, city, address) VALUES ($1, $2, $3)";
    $result = pg_prepare($db, 'insert_branch', $query);
    $result = pg_execute($db, 'insert_branch', array($id_library, $city, $address));

    $error = pg_last_error($db);
    if (empty($error)) {
        $success = "Branch created successfully";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $id_library = $_POST['id_library'];
    $city = $_POST['city'];
    $address = $_POST['address'];

    $query = "UPDATE branch SET id_library = $2, city = $3, address = $4 WHERE id = $1";
    $result = pg_prepare($db, 'update_branch', $query);
    $result = pg_execute($db, 'update_branch', array($id, $id_library, $city, $address));

    $error = pg_last_error($db);
    if (empty($error)) {
        $success = "Branch updated successfully";
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $query = "DELETE FROM branch WHERE id = $1";
    $result = pg_prepare($db, 'delete_branch', $query);
    $result = pg_execute($db, 'delete_branch', array($id));

    $error = pg_last_error($db);
    if (empty($error)) {
        $success = "Branch deleted successfully";
    }
}

$editBranch = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];

    $query = "SELECT * FROM branch WHERE id = $1";
    $result = pg_prepare($db, 'select_branch_by_id', $query);
    $result = pg_execute($db, 'select_branch_by_id', array($id));
    if ($result) {
        $editBranch = pg_fetch_assoc($result);
    }
}

$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $params = [];
    $params[] = "%$search%";
    $query = "
        SELECT b.id, b.city, b.address, l.name
        FROM branch b
        JOIN library l ON b.id_library = l.id
        WHERE city ILIKE $1 OR address ILIKE $1 OR name ILIKE $1;";
    $result = pg_prepare($db, 'select_branches', $query);
    $result = pg_execute($db, 'select_branches', $params);
    $branches = [];
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $branches[] = $row;
        }
    }
}

$search_with_stats = $_GET['search-with-stats'] ?? '';
if (!empty($search_with_stats)) {
    $result = pg_prepare($db, 'refresh_branch_stats', "REFRESH MATERIALIZED VIEW branch_stats;");
    $result = pg_execute($db, 'refresh_branch_stats', []);

    $params = [];
    $params[] = $search_with_stats;
    $query = "SELECT * FROM branch_stats WHERE id = $1;";
    $result = pg_prepare($db, 'select_branches_with_stats', $query);
    $result = pg_execute($db, 'select_branches_with_stats', $params);
    $branches_with_stats = [];
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $branches_with_stats[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Branches</title>
    <?php include '../header-libraries.php'; ?>
</head>
<body>

    <?php include 'navbar.php'; ?>
    <?php include '../handle-result.php'; ?>

    <div class="container mt-4">
        <h1>Manage Branches</h1>

        <form method="POST" class="mb-4">
            <div class="form-group">
                <label>Library:</label>
                <select name="id_library" class="form-control" required>
                    <?php foreach ($libraries as $library): ?>
                        <option value="<?= $library['id'] ?>"
                            <?= (isset($editBranch) && $editBranch['id_library'] == $library['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($library['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>City:</label>
                <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($editBranch['city'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Address:</label>
                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($editBranch['address'] ?? ''); ?>" required>
            </div>
            <?php if (isset($editBranch)): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($editBranch['id']); ?>">
                <button type="submit" name="update" class="btn btn-secondary">Update Branch</button>
            <?php else: ?>
                <button type="submit" name="create" class="btn btn-success">Add Branch</button>
            <?php endif; ?>
        </form>

        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by city, address, or library" value="<?= htmlspecialchars($_GET['search'] ?? ''); ?>">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>

        <?php if (!empty($_GET['search-with-stats'])) { ?>
            <h2>Branches List With Stats</h2>
            <p>Listed here are branches stats that belong to your libraries</p>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Library</th>
                    <th>City</th>
                    <th>Address</th>
                    <th>Total copies</th>
                    <th>Total distinct ISBNs</th>
                    <th>Active loans</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($branches_with_stats as $branch): ?>
                    <tr>
                        <td><?= htmlspecialchars($branch['name']); ?></td>
                        <td><?= htmlspecialchars($branch['city']); ?></td>
                        <td><?= htmlspecialchars($branch['address']); ?></td>
                        <td><?= htmlspecialchars($branch['total_copies']); ?></td>
                        <td><?= htmlspecialchars($branch['total_distinct_isbn']); ?></td>
                        <td><?= htmlspecialchars($branch['active_loans']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php
                if (empty($branches_with_stats)) {
                    echo "<tr><td colspan='6' class='text-center'>No branches found</td></tr>";
                }
                ?>
                </tbody>
            </table>
        <?php } ?>

        <?php if (empty($_GET['search-with-stats'])) { ?>
            <h2>Branches List</h2>
            <p>Listed here are branches that belong to your libraries</p>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Library</th>
                        <th>City</th>
                        <th>Address</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($branches as $branch): ?>
                    <tr>
                        <td><?= htmlspecialchars($branch['name']); ?></td>
                        <td><?= htmlspecialchars($branch['city']); ?></td>
                        <td><?= htmlspecialchars($branch['address']); ?></td>
                        <td class="text-nowrap">
                            <a href="?search-with-stats=<?= $branch['id']; ?>" class="btn btn-info btn-sm">Statistics</a>
                            <a href="manage-branches-late-returns-report.php?id_branch=<?= $branch['id']; ?>" class="btn btn-info btn-sm">Late returns report</a>
                            <a href="?edit=<?= $branch['id']; ?>" class="btn btn-info btn-sm">Edit</a>
                            <a href="?delete=<?= $branch['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this branch?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php
                    if (empty($branches)) {
                        echo "<tr><td colspan='4' class='text-center'>No branches found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</body>
</html>
