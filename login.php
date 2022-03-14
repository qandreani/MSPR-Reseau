<?php
error_reporting(0);
require_once('config.php');
require_once('functions.php');

use RobThree\Auth\TwoFactorAuth;

session_start();
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
    if ($_COOKIE['mailKey'] AND !isset($_GET['key']) AND empty($_GET['key'])) {?>
    <div class="row">
        <div class="col s2"></div>
        <div class="col s8 card-panel green lighten-4" role="alert">
            <p class="center-align"><span class="material-icons left">warning_amber</span>
                Un lien de confirmation vous a été envoyé par mail
            </p>
        </div>
        <div class="col s2"></div>
    </div> <?php }

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
                            <input name="username" type="text" id="inputUsername" class="validate" required>
                            <label for="inputUsername">Identifiant</label>
                        </div>
                        <div class="col s2"></div>
                    </div>
                    <div class="row">
                        <div class="col s2"></div>
                        <div class="input-field col s8">
                            <i class="material-icons prefix">password</i>
                            <input name="password" type="password" id="inputPassword" class="validate" required>
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

                        // On récupère l'adresse IP liée à l'utilisateur
                        $userIpReq = $pdo->prepare('SELECT adresse_ip FROM user_ip WHERE id_user = ?');
                        $userIpReq->bindParam(1, $user['id']);
                        $userIpReq->execute();
                        $userIps = $userIpReq->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($userIps as $key => $value) {
                            if ($adresseIp == $value["adresse_ip"]) {
                                $checkIP = true;
                            }
                        }

                        // Si au moins une adresse IP en BDD correspond
                        if ($checkIP) {

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

                                // On check si l'utilisateur à la clé secrète pour la double authentification google
                                $userSecretReq = $pdo->prepare('SELECT secret FROM user WHERE id = ?');
                                $userSecretReq->bindParam(1, $user['id']);
                                $userSecretReq->execute();
                                $userSecret = $userSecretReq->fetch(PDO::FETCH_ASSOC);

                                // Si l'utilisateur à une clé secrète de double authentification google
                                if ($userSecret != NULL) {
                                    $_SESSION['tfa_secret'] = $userSecret['secret'];
                                }

                            } else {
                                // On stocke le contenu du mail
                                $message = "Nous constatons une connexion non-habituel sur le portail.";
                                $message .= " Si c'est vous alors veuillez ne pas tenir compte de ce message.";
                                // On envoie le mail
                                smtpmailer('q.andreani@gmail.com', 'SIGNALEMENT', $message);

                                // On stocke l'utilisateur dans la session
                                $_SESSION['username'] = $ldapuser;
                                $_SESSION['user_id'] = $user['id'];

                                // On check si l'utilisateur à la clé secrète pour la double authentification google
                                $userSecretReq = $pdo->prepare('SELECT secret FROM user WHERE id = ?');
                                $userSecretReq->bindParam(1, $user['id']);
                                $userSecretReq->execute();
                                $userSecret = $userSecretReq->fetch(PDO::FETCH_ASSOC);

                                // Si l'utilisateur à une clé secrète de double authentification google
                                if ($userSecret != NULL) {
                                    $_SESSION['tfa_secret'] = $userSecret['secret'];
                                }
                            }

                        } else {
                            if(isset($_GET['key']) AND !empty($_GET['key'])){
                                if ($_COOKIE['mailKey'] == $_GET['key']) {
                                    $userReq = $pdo->prepare("INSERT INTO user_ip(adresse_ip, id_user) VALUES (?, ?);");
                                    $userReq->bindParam(1, $adresseIp);
                                    $userReq->bindParam(2, $user['id']);
                                    $userReq->execute();

                                    setcookie("mailKey", "", time()-3600);

                                    // On stocke l'utilisateur dans la session
                                    $_SESSION['username'] = $ldapuser;
                                    $_SESSION['user_id'] = $user['id'];

                                    // On check si l'utilisateur à la clé secrète pour la double authentification google
                                    $userSecretReq = $pdo->prepare('SELECT secret FROM user WHERE id = ?');
                                    $userSecretReq->bindParam(1, $user['id']);
                                    $userSecretReq->execute();
                                    $userSecret = $userSecretReq->fetch(PDO::FETCH_ASSOC);

                                    // Si l'utilisateur à une clé secrète de double authentification google
                                    if ($userSecret != NULL) {
                                        $_SESSION['tfa_secret'] = $userSecret['secret'];
                                    }
                                }
                            } else {
                                $mailKey = keyGen();
                                setcookie('mailKey', $mailKey);

                                // On stocke le contenu du mail
                                $message = '
                                 <html>
                                    <body>
                                       <div align="center">
                                            <p>Une connexion inhabituel a été relevé sur votre compte veuillez confirmer votre identité en cliquan sur ce lien</p>
                                            <a href="http://localhost:8888/MSPR-Reseau/login.php?key=' . $mailKey . '">Confirmez !</a>
                                       </div>
                                    </body>
                                 </html>';

                                // On envoie le mail
                                smtpmailer('q.andreani@gmail.com', 'CONFIRMATION', $message);
                            }
                        }

                    } else {
                        // On enregistre le login du nouvel utilisateur
                        $userReq = $pdo->prepare("INSERT INTO user(username) VALUES (?);");
                        $userReq->bindParam(1, $ldapuser);
                        $userReq->execute();

                        // On récupère l'id en base du nouvel utilisateur
                        $userReq = $pdo->prepare('SELECT * FROM user WHERE username = ?');
                        $userReq->bindParam(1, $ldapuser);
                        $userReq->execute();
                        $user = $userReq->fetch(PDO::FETCH_ASSOC);

                        // On enregistre l'adresse IP du nouvel utilisateur en base
                        $userReq = $pdo->prepare("INSERT INTO user_ip(adresse_ip, id_user) VALUES (?, ?);");
                        $userReq->bindParam(1, $adresseIp);
                        $userReq->bindParam(2, $user['id']);
                        $userReq->execute();

                        // On enregistre le User Agent du nouvel utilisateur en base
                        $userReq = $pdo->prepare("INSERT INTO user_agent(name, id_user) VALUES (?, ?);");
                        $userReq->bindParam(1, $userAgent);
                        $userReq->bindParam(2, $user['id']);
                        $userReq->execute();

                        // On stocke le nouvel utilisateur dans la session
                        $_SESSION['username'] = $ldapuser;
                        $_SESSION['user_id'] = $user['id'];

                        // On passe la variable pour les logs à True
                        $ifNewUser = true;
                    }

                    if ($user || $ifNewUser) {
                        $user_id = $user['id'];
                        // On stocke dans les logs le id_user, ip, agent, date
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
                                L'identifiant ou le mot de passe est incorrect.
                            </p>
                        </div>
                        <div class="col s2"></div>
                    </div> <?php
                }

                if ($user || $ifNewUser) {
                    $user_id = $user['id'];
                    // On stocke dans les logs le id_user, ip, agent, date
                    $userReq = $pdo->prepare("INSERT INTO logConnexion(id_user, ip, agent, isConnected) VALUES (?, ?, ?, ?);");
                    $userReq->bindParam(1, $user_id);
                    $userReq->bindParam(2, $adresseIp);
                    $userReq->bindParam(3, $userAgent);
                    $userReq->bindParam(4, $connected);
                    $userReq->execute();
                }

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

        // S'il n'y a pas de clé secrète dans la session
        if (empty($_SESSION['tfa_secret'])) {

            // On la génère une clé secrète
            $_SESSION['tfa_secret'] = $tfa->createSecret();
            $secret = $_SESSION['tfa_secret']; ?>

            <div class="row">
                <div class="col s2"></div>
                <div class="col s8 center-align" role="alert">
                    <h3>Activation Double Authentification</h3>
                    <p>QR Code :</p>
                    <img src="<?= $tfa->getQRCodeImageAsDataUri('MSPR', $secret) ?>" alt="QR Code">
                    <form method="POST">
                        <input type="text" placeholder="Vérification Code" name="tfa_code">
                        <button class="btn waves-light" type="submit">Valider</button>
                    </form>
                </div>
                <div class="csol s2"></div>
            </div> <?php
            $_SESSION['newUser'] = true;

        } else {
            $secret = $_SESSION['tfa_secret']; ?>

            <div class="row">
                <div class="col s2"></div>
                <div class="col s8 center-align" role="alert">
                    <h3>Authentification</h3>
                    <form method="POST">
                        <input type="text" placeholder="Vérification Code" name="tfa_code">
                        <button class="btn waves-light" type="submit">Valider</button>
                    </form>
                </div>
                <div class="csol s2"></div>
            </div> <?php
        }

        // Si le code de validation à bien été rentré
        if (!empty($_POST['tfa_code'])) {
            // On récupère la clé dans la session
            $secret = $_SESSION['tfa_secret'];

            // On vérifie si le code match avec la clé secrète
            if ($tfa->verifyCode($secret, $_POST['tfa_code'])) {

                // Si c'est un nouvel utilisateur on enregistre la clé secrète
                if ($_SESSION['newUser']) {
                    // On enregistre le login du nouvel utilisateur
                    $userReq = $pdo->prepare("UPDATE user SET secret = ? WHERE id = ?;");
                    $userReq->bindParam(1, $secret);
                    $userReq->bindParam(2, $_SESSION['user_id']);
                    $userReq->execute();
                }

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
    } ?>
</div>

<script type="text/javascript" src="assets/js/materialize.min.js"></script>
</body>
</html>
