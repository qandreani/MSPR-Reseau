<?php

use PHPMailer\PHPMailer\PHPMailer;
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
    ?>
    <div class="row">
        <div class="col s2"></div>
        <div class="col s8 card-panel red lighten-4" role="alert">
            <p class="center-align">
                <span class="material-icons left">warning_amber</span>
                Impossible de se connecter à la base de données :
                <?= $e->getMessage(); ?>
            </p>
        </div>
        <div class="col s2"></div>
    </div>
    <?php
}

/*
 * Variable pour la connexion à l'AD
 * ********************************** */
$ldapserver = '109.13.59.99';
$ldapport = '389';
$ldaptree = "OU=labo,DC=DEV,DC=MSPR";
//$ldapuser = 'DEV\qandreani';
//$ldappass = 'azerty2022!';

/*
 * Configuration phpMailer
 * ********************************** */
//require 'vendor/autoload.php';
//$mail = new PHPMailer;
//$mail->isSMTP();
//$mail->SMTPDebug = 2;
//$mail->Host = 'smtp.hostinger.fr';
//$mail->Port = 587;
//$mail->SMTPAuth = true;
//$mail->Username = 'test@hostinger-tutorials.fr';
//$mail->Password = 'VOTRE MOT DE PASSE ICI';
//$mail->setFrom('test@hostinger-tutorials.fr', 'Votre nom');
//$mail->addReplyTo('test@hostinger-tutorials.fr', 'Votre nom');
//$mail->addAddress('exemple@gmail.com', 'Nom du destinataire');
//$mail->Subject = 'Essai de PHPMailer';
//$mail->msgHTML(file_get_contents('message.html'), __DIR__);
//$mail->Body = 'Ceci est le contenu du message en texte clair';