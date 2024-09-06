<?php
session_start();
session_unset();
session_destroy();
header('Location: /reader/login.php');
exit;
