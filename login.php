<?php

session_start();
include_once('BaseDeDonne.php');
include_once('OperationsBD.php');

$DAL = new BDOperations();

$username = "";
$message = "";

if (isset($_SESSION["userID"]))
    header('Location: ./');

if (isset($_POST["username"]) && isset($_POST["password"]))
{
    $username = $_POST["username"];
    $usager = $DAL->login($username, $_POST["password"]);

    if ($usager !== false)
    {
        $_SESSION["SESSION_TIMEOUT"] = 60 * 10; // trente minutes
        $_SESSION["LAST_ACTIVITY"] = time(); //maintenant
        $_SESSION["userID"] = $usager[0][0];
        $_SESSION["username"] = $usager[0][1];
        $_SESSION["isAdmin"] = (bool)($usager[0][3] == 1);

        $DAL->insertConnectionLog($usager[0][0], $_SERVER['REMOTE_ADDR']);

        header('Location: ./');
    }
    else
        $message = "Nom d'usager ou mot de passe invalide";
}

?>

Voici la page de login

<style>
    b{
        color:red;
    }
</style>

<form action="login.php" method="post">
    <fieldset>
        <legend>Vos informations</legend>
        Nom d'utilisateur: <input name="username" value="<?= $username ?>" type="text" size="15"><br>
        Mot de passe: <input name="password" type="password" size="30"><br>
        <input type="submit" value="Se connecter!">
    </fieldset>
</form>

<b><?= $message ?></b>


