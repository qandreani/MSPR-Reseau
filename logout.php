<?php
    session_start();
    session_destroy();
    setcookie("mailKey", "", time() - 3600);

    header("location:login.php");
    exit;
