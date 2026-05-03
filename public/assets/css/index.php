<?php
session_start();
unset(
    $_SESSION['id_user'],
    $_SESSION['user_name'],
    $_SESSION['email_user'],
    $_SESSION['pass_user'],
    $_SESSION['type_access'],
    $_SESSION['status_user']);
header("Location: ../_login.php");