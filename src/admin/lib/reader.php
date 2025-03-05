<?php
require_once '../config.php';

function find_readers_for_librarian($librarianId, $search_term) {
    if (empty($search_term)) {
        return [];
    }
    $db = get_connection();

    $params = [];
    $params[] = $librarianId;
    $params[] = "%$search_term%";
    $query = "SELECT DISTINCT r.*, l.id as library_id, l.name as library_name, lr.overdue_returns FROM reader r
              JOIN library_reader lr ON lr.id_reader = r.id
              JOIN library l ON l.id = lr.id_library
              JOIN library_librarian ll ON ll.id_library = l.id
              WHERE ll.id_librarian = $1 AND (r.username ILIKE $2 OR r.name ILIKE $2 OR r.surname ILIKE $2)";
    $result = pg_prepare($db, 'select_readers_in_libraries_query', $query);
    $result = pg_execute($db, 'select_readers_in_libraries_query', $params);

    $readers = [];
    while ($row = pg_fetch_assoc($result)) {
        $readers[] = $row;
    }

    return $readers;
}

?>
