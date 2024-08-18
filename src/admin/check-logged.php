<?php

if (!isset($_SESSION['loggedin_admin']) || $_SESSION['loggedin_admin'] !== true) {
    header('Location: index.php');
    exit;
}
