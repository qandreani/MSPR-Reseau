<?php
//error_reporting(0);
require_once('config.php');
require_once('functions.php');

use RobThree\Auth\TwoFactorAuth;

session_start();

//TODO :
// - Ajouter les couches de sécurité sur les formulaires
// - Ajouter "required" dans le html du formulaire
// - Décommenter le errror_reporting en haut de cette page
?>

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
    <title>Portail</title>
</head>
<body>
<div class="container">

    <?php
    if ($_SESSION == NULL) { ?>
        <form action="" method="post" name="Login_Form" class="form-signin">
            <div class="row">
                <div class="col s12">
                    <h2 class="teal-text text-lighten-2">Connexion</h2>
                </div>
            </div>

            <div class="row">
                <div class="col s12">
                    <div class="row">
                        <div class="col s2"></div>
                        <div class="input-field col s8">
                            <i class="material-icons prefix">account_circle</i>
                            <input name="username" type="text" id="inputUsername" class="validate">
                            <label for="inputUsername">Identifiant</label>
                        </div>
                        <div class="col s2"></div>
                    </div>
                    <div class="row">
                        <div class="col s2"></div>
                        <div class="input-field col s8">
                            <i class="material-icons prefix">password</i>
                            <input name="password" type="password" id="inputPassword" class="validate">
                            <label for="inputPassword">Mot de Passe</label>
                        </div>
                        <div class="col s2"></div>
                    </div>
                    <button name="Submit" value="Login" class="btn waves-light right" type="submit">
                        <i class="material-icons left">cloud</i>Connexion
                    </button>
                </div>
            </div>
        </form>

        <?php // On vérifie si le login à bien été envoyé
        if (isset($_POST['Submit'])) {
            // On stocke l'adresse IP
            $adresseIp = getIp();
            // On stocke le User Agent
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            // On se connecte à l'Active Directory
            $ldapconn = ldap_connect($ldapserver, $ldapport);
            // Si la connexion a réussi
            if ($ldapconn) {
                // On initialise des check de connexion pour les logs
                $connected = 0;
                $ifNewUser = false;
                // On rentre des options de connection ldap
                ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
                ldap_set_option($ldapconn, LDAP_OPT_NETWORK_TIMEOUT, 10);
                // On récupère les identifiants
                $ldapuser = htmlspecialchars($_POST['username']);
                $ldappass = htmlspecialchars($_POST['password']);
                // On check si l'utilisateur existe en BDD
                $userReq = $pdo->prepare('SELECT * FROM user WHERE username = ?');
                $userReq->bindParam(1, $ldapuser);
                $userReq->execute();
                $user = $userReq->fetch(PDO::FETCH_ASSOC);

                if (!empty ($ldappass)) {
                    // On vérifie si le mot de passe et l'identifiant sont bons.
                    $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass);
                }
                // Si les identifiants sont bons
                if ($ldapbind) {
                    // Si la connection est Ok on le renseigne dans le logConnexion
                    $connected = 1;
                    // Si l'utilisateur existe dans la BDD
                    if ($user) {
                        //TODO :
                        // - Si le user existe faire une vérification avec l'adresse ip et l'agent via la bdd
                        // - Si navigateur pas connue envoyer un mail de CONFIRMATION
                        // - Si IP pas connue alors envoyer un mail de SIGNALEMENT

                        // On récupère l'adresse IP liée à l'utilisateur
                        $userIpReq = $pdo->prepare('SELECT adresse_ip FROM user_ip WHERE id_user = ?');
                        $userIpReq->bindParam(1, $user['id']);
                        $userIpReq->execute();
                        $userIp = $userIpReq->fetch(PDO::FETCH_ASSOC);

                        // On vérifie si elle correspond à l'adresse IP habituelle
                        if ($userIp["adresse_ip"] == $adresseIp) {

                            // On récupère le User Agent lié à l'utilisateur
                            $userAgentReq = $pdo->prepare('SELECT name FROM user_agent WHERE id_user = ?');
                            $userAgentReq->bindParam(1, $user['id']);
                            $userAgentReq->execute();
                            $userAgentBdd = $userAgentReq->fetch(PDO::FETCH_ASSOC);

                            // On vérifie s'il correspond avec le User Agent habituel
                            if ($userAgent == $userAgentBdd["name"]) {
                                // On stocke l'utilisateur dans la session
                                $_SESSION['username'] = $ldapuser;
                                $_SESSION['user_id'] = $user['id'];

                            } else {
                                //TODO :
                                // - Envoyer un mail de SIGNALEMENT
                                // - L'utilisateur peut tout de même se connecter
                            }

                        } else {
                            //TODO :
                            // - Envoyer un mail de CONFIRMATION
                            // - L'utilisateur ne peut pas se connecter
                        }

                    } else {
                        $userReq = $pdo->prepare("INSERT INTO user(username) VALUES (?);");
                        $userReq->bindParam(1, $ldapuser);
                        $userReq->execute();

                        $userReq = $pdo->prepare('SELECT * FROM user WHERE username = ?');
                        $userReq->bindParam(1, $ldapuser);
                        $userReq->execute();
                        $user = $userReq->fetch(PDO::FETCH_ASSOC);

                        $userReq = $pdo->prepare("INSERT INTO user_ip(adresse_ip, id_user) VALUES (?, ?);");
                        $userReq->bindParam(1, $adresseIp);
                        $userReq->bindParam(2, $user['id']);
                        $userReq->execute();

                        $userReq = $pdo->prepare("INSERT INTO user_agent(name, id_user) VALUES (?, ?);");
                        $userReq->bindParam(1, $userAgent);
                        $userReq->bindParam(2, $user['id']);
                        $userReq->execute();

                        $ifNewUser = true;
                    }

                    //TODO récupérer dans la session les informations de l'utilisateur de la BDD
                    //comme le code secret si il existe.


                } else { ?>
                    <div class="row">
                        <div class="col s2"></div>
                        <div class="col s8 card-panel red lighten-4" role="alert">
                            <p class="center-align">
                                <span class="material-icons left">warning_amber</span>
                                L'identifiant ou le mot de passe est incorrect.
                            </p>
                        </div>
                        <div class="col s2"></div>
                    </div> <?php
                }

                if ($user || $ifNewUser) {
                    $user_id = $user['id'];
                    // On stock dans les logs le id_user, ip, agent, date
                    $userReq = $pdo->prepare("INSERT INTO logConnexion(id_user, ip, agent, isConnected) VALUES (?, ?, ?, ?);");
                    $userReq->bindParam(1, $user_id);
                    $userReq->bindParam(2, $adresseIp);
                    $userReq->bindParam(3, $userAgent);
                    $userReq->bindParam(4, $connected);
                    $userReq->execute();
                }

                // On rafraîchit la page
                header("location:login.php");

            } else { ?>
                <div class="row">
                    <div class="col s2"></div>
                    <div class="col s8 card-panel red lighten-4" role="alert">
                        <p class="center-align">
                            <span class="material-icons left">warning_amber</span>
                            Impossible de se connecter à l'Active Directory
                        </p>
                    </div>
                    <div class="col s2"></div>
                </div> <?php
            }
        }
    } else {
        // On instancie la classe de double Authentification
        $tfa = new TwoFactorAuth();

        if (empty($_SESSION['tfa_secret'])) {
            // Sinon on la génère et on la stocke dans la session
            $_SESSION['tfa_secret'] = $tfa->createSecret();
        }
        $secret = $_SESSION['tfa_secret']; ?>

        <div class="row">
            <div class="col s2"></div>
            <div class="col s8 center-align" role="alert">
                <h3>Activation Double Authentification</h3>
                <?php
                //TODO : Ici si nous avons en session le user et qu'il à déjà un code secret de renseigné nous
                //TODO : pouvons ne pas faire apparaitre le QR Code puisqu'il est utilisé pour activité l'authentification et enregistrer le code secret en bdd  ?>
                <!--                <p>Code Secret : --><?//= $secret ?><!--</p>-->
                <p>QR Code :</p>
                <img src="<?= $tfa->getQRCodeImageAsDataUri('MSPR', $secret) ?>" alt="QR Code">
                <form method="POST">
                    <input type="text" placeholder="Vérification Code" name="tfa_code">
                    <button class="btn waves-light" type="submit">Valider</button>
                </form>
            </div>
            <div class="csol s2"></div>
        </div>

        <?php
        //TODO Ajouter les couches de sécurité sur les formulaires
        if (!empty($_POST['tfa_code'])) {
            if ($tfa->verifyCode($secret, $_POST['tfa_code'])) {
                //TODO Ici il nous faut enregistrer le code secret dans la BDD par rapport à l'utilisateur en session et ajouter
                //TODO une condition pour savoir si ce n'est pas déjà le cas, si il y a déjà un code secret pas besoins de l'enregistrer en bdd
//                $q = $db->prepare('UPDATE users SET secret = :secret WHERE id = :id');
//                $q->bindValue('secret', $secret);
//                $q->bindValue('id', $_SESSION['user_id']);
//                $q->execute();

                // On active la session
                $_SESSION['Active'] = true;
                // On redirige vers l'index
                header("location:index.php");
                exit;
            } else { ?>
                <div class="row">
                    <div class="col s2"></div>
                    <div class="col s8 card-panel red lighten-4" role="alert">
                        <p class="center-align"><span class="material-icons left">warning_amber</span>
                            Code de Validation incorrect
                        </p>
                    </div>
                    <div class="col s2"></div>
                </div> <?php
            }
        }
    }
    ?>
</div>

<script type="text/javascript" src="assets/js/materialize.min.js"></script>
</body>
</html>
