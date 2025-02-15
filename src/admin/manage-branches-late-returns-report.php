<?php
session_start();
require_once '../config.php';
require_once 'check-logged.php';

$db = get_connection();

$query = "
    SELECT *
    FROM overdue_readers_view
    WHERE id_branch = $1";

$id_branch = $_GET['id_branch'];
$result = pg_prepare($db, 'select_late_returns', $query);
$result = pg_execute($db, 'select_late_returns', array($id_branch));

if (!$result) {
    die("Database query failed: " . pg_last_error($db));
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="overdue_readers_report.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, ['Branch ID', 'Library ID', 'Reader ID', 'Overdue Returns']);

while ($row = pg_fetch_assoc($result)) {
    fputcsv($output, [$row['id_branch'], $row['id_library'], $row['id_reader'], $row['overdue_returns']]);
}

fclose($output);
exit;
?>
