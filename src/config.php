<?php
# https://stackoverflow.com/a/14451001
error_reporting(0);

function get_connection() {
    $host = 'localhost';
    $db   = 'quibreria';
    $user = 'pagemaster';
    $pass = '';
    $charset = 'utf8';

    $conn_string = "host=$host dbname=$db user=$user password=$pass options='--client_encoding=$charset'";
    return pg_connect($conn_string);
}
?>
