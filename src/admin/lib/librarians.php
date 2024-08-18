<?php
require_once '../config.php';

function find_librarian_branches($id_librarian) {
    $db = get_connection();

    $query = "
        SELECT b.id, b.city, b.address
        FROM branch b
        JOIN library_librarian ll ON ll.id_library = b.id_library
        WHERE ll.id_librarian = $1
    ";

    $result = pg_prepare($db, 'select_branches_for_librarian', $query);
    $result = pg_execute($db, 'select_branches_for_librarian', array($id_librarian));

    $branches = [];
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $branches[] = $row;
        }
    }

    return $branches;
}


?>
