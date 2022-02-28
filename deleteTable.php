<?php
require_once('config.php');

$userReq = $pdo->prepare("DELETE FROM `user` WHERE `id` = 4;");
//$userReq = $pdo->prepare("TRUNCATE TABLE `utilisateur`");
$userReq->execute();