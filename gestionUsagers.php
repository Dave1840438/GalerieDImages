<?php

include_once("protectedPage.php");
include_once("BaseDeDonne.php");
include_once("OperationsBD.php");

if (!$_SESSION["isAdmin"])
    header('Location: ./');

$DAL = new BDOperations();

if (isset($_POST["idToDelete"]))
{
    if ($_SESSION["userID"] != $_POST["idToDelete"])
        $DAL->supprimerUsager($_POST["idToDelete"]);
}

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
    }
}


$tousLesUsagers = $DAL->selectAllUsagers();
$connectionLog = $DAL->getConnectionLog();

$username = "";
$password = "";

$messageErreur = "";


echo "Usagers autres que l'admin:<br>";

echo '<table>';

for ($i = 0; $i < count($tousLesUsagers); $i++)
{
    if ($tousLesUsagers[$i][3] != 1)
    {
        echo "<tr>";
        echo "<td>" . $tousLesUsagers[$i][0] . "</td>";
        echo "<td>" . $tousLesUsagers[$i][1] . "</td>";
        echo "<td>" . $tousLesUsagers[$i][2] . "</td>";
        ?>

        <td>
        <form action="gestionUsagers.php" method="post">
            <input type="hidden" name="idToDelete" value="<?= $tousLesUsagers[$i][0] ?>">
            <input type="submit" value="Supprimer">
        </form>
        </td>
        </tr>
<?php
    }
}

echo '</table>';

echo "Journal des connexions <br>";
echo '<table>';

for ($i = 0; $i < count($connectionLog); $i++)
{
        echo "<tr>";
        echo "<td>" . $connectionLog[$i][0] . "</td>";
        echo "<td>" . $connectionLog[$i][1] . "</td>";
        echo "<td>" . $connectionLog[$i][2] . "</td>";
        echo "<tr>";
}

echo '</table>';

?>

<style>
    b
    {
        color:red;
    }
</style>


Voici la page d'inscription

<form action="gestionUsagers.php" method="post">
    <fieldset>
        <legend>Vos informations</legend>
        Nom d'utilisateur: <input name="username" value="<?= $username ?>" type="text" size="15"><br>
        Mot de passe: <input name="password" type="password" size="30"><br>
        Confirmation du mot de passe: <input name="confirmpassword" type="password" size="30">
        <input type="submit" value="S'inscrire!">
    </fieldset>
</form>

<b><?=$messageErreur?></b>
<br>
<a href="index.php">Retour</a>;


