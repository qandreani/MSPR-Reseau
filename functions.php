<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

/*
 * Fonction de récupération de l'adresse IP
 * ******************************************** */
function getIp()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/*
 * Fonction d'envoi de mail phpMailer
 * ******************************************** */
function smtpmailer($to, $sujet, $message)
{
    try {
        $mail = new PHPMailer();
        $mail->SMTPDebug = 2;
        $mail->IsSMTP();
        $mail->IsHTML();
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->Username = 'mspr.reseau.2022@gmail.com';
        $mail->Password = 'joxkjsbubfqfdtad';
        $mail->SetFrom('mspr.reseau.2022@gmail.com');
        $mail->CharSet = "utf-8";
        $mail->Subject = $sujet;
        $mail->Body = $message;
        $mail->AddAddress($to);
        $mail->Send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"; //Add confirm/error message to user
        echo $e;
    }
}

/*
 * Fonction de génération de clé
 * ******************************************** */
function keyGen()
{
    for ($i = 1; $i < 15; $i++) {
        $key .= mt_rand(0, 9);
    }
    return $key;
}