<?php
require('./vendor/autoload.php');

/*
 * Connexion à la base de données
 * ********************************** */
//$db = "CHATELET";
//$dbhost = 'localhost';
//$dbport = '3309';
//$dbuser = 'root';
//$dbpasswd = 'root';

$db = "CHATELET";
$dbhost = '109.13.59.99';
$dbport = '3306';
$dbuser = 'mspr';
$dbpasswd = 'p@ssword44!';

//On établit la connexion et on vérifie la connexion
try {
    $pdo = new PDO('mysql:host='.$dbhost.';port='.$dbport.';dbname='.$db.'', $dbuser, $dbpasswd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET CHARACTER SET utf8");
} catch(PDOException $e) {
    echo $e->getMessage();
}

/*
 * Variable pour la connexion à l'AD
 * ********************************** */
$ldapserver = '109.13.59.99';
$ldapport = '389';
$ldaptree = "OU=labo,DC=DEV,DC=MSPR";
//$ldapuser = 'DEV\qandreani';
//$ldappass = 'azerty2022!';
