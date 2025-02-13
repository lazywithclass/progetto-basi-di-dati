<?php
require_once '../config.php';

function find_readers_for_librarian($librarianId, $search_term) {
    if (empty($search_term)) {
        return [];
    }
    $db = get_connection();

    $query = "SELECT id_library FROM library_librarian WHERE id_librarian = $1";
    $result = pg_prepare($db, 'select_id_library_by_id_librarian_query', $query);
    $result = pg_execute($db, 'select_id_library_by_id_librarian_query', array($librarianId));

    $row = pg_fetch_assoc($result);
    $libraryId = $row['id_library'];

    $params = [];
    $params[] = $libraryId;
    $params[] = "%$search_term%";
    $query = "SELECT DISTINCT r.*, l.id as library_id, l.name as library_name, lr.overdue_returns FROM reader r
              JOIN library_reader lr ON lr.id_reader = r.id
              JOIN library l ON l.id = lr.id_library
              WHERE lr.id_library = $1 AND (r.username ILIKE $2 OR r.name ILIKE $2 OR r.surname ILIKE $2)";
    $result = pg_prepare($db, 'select_readers_in_libraries_query', $query);
    $result = pg_execute($db, 'select_readers_in_libraries_query', $params);

    $readers = [];
    while ($row = pg_fetch_assoc($result)) {
        $readers[] = $row;
    }

    return $readers;
}

?>
