<?php
/**
 * Created by PhpStorm.
 * User: 201335700
 * Date: 2015-12-01
 * Time: 13:00
 */
include_once("protectedPage.php");
include_once("BaseDeDonne.php");
include_once("OperationsBD.php");

$DAL = new BDOperations();

$message = "";

if (isset($_POST["updatePassword"]))
{
    if ($_POST["password"] == "")
    {
        $message = "Le mot de passe ne peut pas être vide!";
    }
    else if($_POST["password"] != $_POST["confirmPassword"])
    {
        $message = "Les mots de passe de correspondent pas";
    }
    else
    {
        if($DAL->updateUserPassword($_SESSION["userID"], $_POST["password"]))
        {
            $message = "Le mot de passe a bien été changé!";
        }
        else
        {
            $message = "Une erreur innatendue est survenue, veuillez réessayer plus tard!";
        }
    }
}
if (isset($_POST["changeConnectionTimeout"]))
{
    $message = "Votre timeout de session est maintenant de ";

    if (isset($_POST["stayConnected"]))
    {
        $_SESSION["SESSION_TIMEOUT"] = 60 * 60 * 24;
        $message .= "24 heures";
    }
    else
    {
        $_SESSION["SESSION_TIMEOUT"] = 60 * 30;
        $message .= "30 minutes";
    }

    $_SESSION["LAST_ACTIVITY"] = time();
}


?>

Voici la page de profil

<form action="profil.php" method="post">
    <fieldset>
        <legend>Vos informations</legend>
        Mot de passe: <input name="password" type="password" size="30"><br>
        Confirmation du mot de passe: <input name="confirmPassword" type="password" size="30">
        <input type="submit" name="updatePassword" value="Sauvegarder"!">
    </fieldset>
</form>
<br>

<form action="profil.php" method="post">
    <fieldset>
        <legend>Vos informations</legend>
        Rester connecté: <input type="checkbox" name="stayConnected">
        <input type="submit" name="changeConnectionTimeout" value="Sauvegarder"!">
    </fieldset>
</form>
<br>

<?=$message?>
<br>


<a href="index.php">Retour</a>



