<?php

if (!isset($_SESSION['loggedin_admin']) || $_SESSION['loggedin_admin'] !== true) {
    header('Location: /admin/login.php');
    exit;
}
