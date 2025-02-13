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

    // TODO handle error
    header('Location: manage-branches.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $id_library = $_POST['id_library'];
    $city = $_POST['city'];
    $address = $_POST['address'];

    $query = "UPDATE branch SET id_library = $2, city = $3, address = $4 WHERE id = $1";
    $result = pg_prepare($db, 'update_branch', $query);
    $result = pg_execute($db, 'update_branch', array($id, $id_library, $city, $address));

    // TODO handle error
    header('Location: manage-branches.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $query = "DELETE FROM branch WHERE id = $1";
    $result = pg_prepare($db, 'delete_branch', $query);
    $result = pg_execute($db, 'delete_branch', array($id));

    // TODO handle error
    header('Location: manage-branches.php');
    exit;
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
    $searchQuery = " ";
    $params = [];
    $params[] = "%$search%";

    // TODO
//    POSSIBLE new QUERY
//    SELECT b.id, b.city, b.address, l.name AS library_name, COUNT(pc.*),  COUNT(distinct bo.isbn)
//    FROM branch b
//    join physical_copy pc on pc.id_branch = b.id
//    join book bo on bo.id = pc.id_book
//    JOIN library l ON b.id_library = l.id
//    group by b.id, bo.isbn, b.id, b.city, b.address, l.name

    $query = "
        SELECT branch.id, branch.city, branch.address, library.name AS library_name
        FROM branch
        JOIN library ON branch.id_library = library.id
        WHERE branch.city ILIKE $1 OR branch.address ILIKE $1 OR library.name ILIKE $1";

    $result = pg_prepare($db, 'select_branches', $query);
    $result = pg_execute($db, 'select_branches', $params);
    $branches = [];
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $branches[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Branches</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h1>Manage Branches</h1>

        <form method="POST" class="mb-4">
            <div class="form-group">
                <label>Library:</label>
                <select name="id_library" class="form-control" required>
                    <?php foreach ($libraries as $library): ?>
                        <option value="<?php echo htmlspecialchars($library['id']); ?>"
                            <?php echo (isset($editBranch) && $editBranch['id_library'] == $library['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($library['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>City:</label>
                <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($editBranch['city'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Address:</label>
                <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($editBranch['address'] ?? ''); ?>" required>
            </div>
            <?php if (isset($editBranch)): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($editBranch['id']); ?>">
                <button type="submit" name="update" class="btn btn-secondary">Update Branch</button>
            <?php else: ?>
                <button type="submit" name="create" class="btn btn-success">Add Branch</button>
            <?php endif; ?>
        </form>

        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by city, address, or library" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>

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
                    <td><?php echo htmlspecialchars($branch['library_name']); ?></td>
                    <td><?php echo htmlspecialchars($branch['city']); ?></td>
                    <td><?php echo htmlspecialchars($branch['address']); ?></td>
                    <td class="text-nowrap">
                        <a href="?edit=<?php echo $branch['id']; ?>" class="btn btn-info btn-sm">Edit</a>
                        <a href="?delete=<?php echo $branch['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this branch?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php
                if (empty($branches)) {
                    echo "<tr><td colspan='4' class='text-center'>No branches found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="./js/index.js"></script>
</body>
</html>
