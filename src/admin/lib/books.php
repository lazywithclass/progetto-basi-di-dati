<?php
require_once '../config.php';

function update_book_for_librarian($id_book, $isbn, $title, $publisher, $plot, $selected_authors) {
    $db = get_connection();
    $query = "UPDATE book SET isbn = $1, title = $2, publisher = $3, plot = $4 WHERE id = $5";
    $result = pg_prepare($db, 'update_book_query', $query);
    $result = pg_execute($db, 'update_book_query', array($isbn, $title, $publisher, $plot, $id_book));

    if (!$result) {
        return pg_last_error($db);
    }

    $query = "DELETE FROM author_book WHERE id_book = $1";
    $result = pg_prepare($db, 'delete_author_book_query', $query);
    $result = pg_execute($db, 'delete_author_book_query', array($id_book));

    if (!$result) {
        return pg_last_error($db);
    }

    foreach ($selected_authors as $id_author) {
        $query = "INSERT INTO author_book (id_author, id_book) VALUES ($1, $2)";
        $result = pg_prepare($db, 'insert_author_book_query', $query);
        $result = pg_execute($db, 'insert_author_book_query', array($id_author, $id_book));
        if (!$result) {
            return pg_last_error($db);
        }
    }
}

function insert_book_for_librarian($isbn, $title, $publisher, $plot, $selected_authors) {
    $db = get_connection();
    $query = "INSERT INTO book (isbn, title, publisher, plot) VALUES ($1, $2, $3, $4) RETURNING id";
    $result = pg_prepare($db, 'insert_book_query', $query);
    $result = pg_execute($db, 'insert_book_query', array($isbn, $title, $publisher, $plot));

    if (!$result) {
        return pg_last_error($db);
    }

    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        $id_book = $row['id'];
    }

    foreach ($selected_authors as $id_author) {
        $query = "INSERT INTO author_book (id_author, id_book) VALUES ($1, $2)";
        $result = pg_prepare($db, 'insert_author_book_query', $query);
        $result = pg_execute($db, 'insert_author_book_query', array($id_author, $id_book));
        if (!$result) {
            return pg_last_error($db);
        }
    }

    return pg_last_error($db);
}

function find_books_for_librarian($librarianId, $search_term) {
    if (empty($search_term)) {
        return [];
    }
    $db = get_connection();
    $params = [];
    $params[] = "$librarianId";
    $params[] = "%$search_term%";
    $query = "SELECT * FROM librarian_books WHERE id_librarian = $1 AND (title ILIKE $2 OR isbn ILIKE $2)";
    $result = pg_prepare($db, 'select_books_for_librarian', $query);
    $result = pg_execute($db, 'select_books_for_librarian', $params);

    $books = [];
    while ($row = pg_fetch_assoc($result)) {
        $books[] = $row;
    }

    return $books;
}

?>
