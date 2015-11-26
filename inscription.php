<?php

include_once("protectedPage.php");
include_once("BaseDeDonne.php");
include_once("OperationsBD.php");

if (!$_SESSION["isAdmin"])
    header('Location: ./');

$DAL = new BDOperations();

$username = "";
$password = "";

$messageErreur = "";
if (isset($_POST["username"]))
{
    $username = $_POST["username"];
    $password = $_POST["password"];

    if ($username == '' /* ou qu'il exsite d�ja'*/)
    {
        $messageErreur = "Le nom d'utilisateur doit �tre unique et non vide!";
    }
    else if ($password == '')
    {
        $messageErreur = "Le mot de passe ne peut pas �tre vide!";
    }
    else if ($password != $_POST["confirmpassword"])
    {
        $messageErreur = "Les mots de passe ne correspondent pas!";
    }
    else
    {
        if (!$DAL->inscrireUsager($username, $password))
        {
            $messageErreur = "Le nom d'usager doit �tre unique!";
        }
        else
            header('Location: ./');
    }
}

?>

<style>
    b
    {
        color:red;
    }
</style>


Voici la page d'inscription

<form action="inscription.php" method="post">
    <fieldset>
        <legend>Vos informations</legend>
        Nom d'utilisateur: <input name="username" value="<?= $username ?>" type="text" size="15"><br>
        Mot de passe: <input name="password" type="password" size="30"><br>
        Confirmation du mot de passe: <input name="confirmpassword" type="password" size="30">
        <input type="submit" value="S'inscrire!">
    </fieldset>
</form>

<?php

 echo '<b>'. $messageErreur . '</b>';
echo '<a href="index.php">Retour</a>';

