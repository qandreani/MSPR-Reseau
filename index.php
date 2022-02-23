<?php
require_once ('config.php');
session_start();

if ($_SESSION['Active'] == false) {
    header("location:login.php");
    exit;
} ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Import materialize.css-->
    <link type="text/css" rel="stylesheet" href="assets/css/materialize.min.css" media="screen,projection"/>
    <link rel="stylesheet" type="text/css" href="assets/css/stylesheet.css">
    <title>Logged in</title>
</head>
<body>
<div class="container">
    <div class="header clearfix">
        <nav>
            <ul class="nav nav-pills pull-right">
                <li role="presentation" class="active"><a href="#">Home</a></li>
                <li role="presentation"><a href="#">About</a></li>
                <li role="presentation"><a href="#">Contact</a></li>
            </ul>
        </nav>
        <h3 class="text-muted">PHP Login</h3>
    </div>

    <div class="jumbotron">
        <h1>Status: logged in</h1>
        <p class="lead">And just like that you've created your first password protected area with PHP and a little
            knowledge of HTML. Change the username and password in login.php for your own and give it a try!</p>
        <p><a class="btn btn-lg btn-success" href="logout.php" role="button">Log out</a></p>
    </div>

    <div class="row marketing">
        <div class="col-lg-6">
            <h4>Subheading</h4>
            <p>Donec id elit non mi porta gravida at eget metus. Maecenas faucibus mollis interdum.</p>

            <h4>Subheading</h4>
            <p>Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet
                fermentum.</p>

            <h4>Subheading</h4>
            <p>Maecenas sed diam eget risus varius blandit sit amet non magna.</p>
        </div>

        <div class="col-lg-6">
            <h4>Subheading</h4>
            <p>Donec id elit non mi porta gravida at eget metus. Maecenas faucibus mollis interdum.</p>

            <h4>Subheading</h4>
            <p>Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet
                fermentum.</p>

            <h4>Subheading</h4>
            <p>Maecenas sed diam eget risus varius blandit sit amet non magna.</p>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; Mathis Vendé | Erwan Leblanc | Quentin Andreani | Yann Rondeau </p>
    </footer>
</div>

<script type="text/javascript" src="assets/js/materialize.min.js"></script>
</body>
</html>
