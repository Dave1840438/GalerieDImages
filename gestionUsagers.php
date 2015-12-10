<link rel="stylesheet" href="css/bootstrap.min.css"/>
<!-- jQuery -->
<script src="js/jquery.js"></script>
<script src="js/bootstrap.min.js"></script>

<?php

include_once("protectedPage.php");
include_once("BaseDeDonne.php");
include_once("OperationsBD.php");


$messageErreur = "";

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
        $messageErreur = "Le nom d'utilisateur doit etre unique et non vide!";
    }
    else if ($password == '')
    {
        $messageErreur = "Le mot de passe ne peut pas etre vide!";
    }
    else if ($password != $_POST["confirmpassword"])
    {
        $messageErreur = "Les mots de passe ne correspondent pas!";
    }
    else
    {
        if (!$DAL->inscrireUsager($username, $password))
        {
            $messageErreur = "Le nom d'usager doit etre unique!";
        }
    }
}


$tousLesUsagers = $DAL->selectAllUsagers();
$connectionLog = $DAL->getConnectionLog();

$username = "";
$password = "";


?>

<?php include 'Header.php' ?>




<div class="container">
    <div class="row">

        <div class="col-lg-4 col-lg-offset-4" style="margin-top: 70px">
<table class="table table-striped table-condensed">
<thead>
    <tr>
        <th>ID</th>
        <th>Nom</th>
        <th>Password</th>
        <th>Supprimer</th>
    </tr>
</thead>
<tbody>

<?php

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
            <input type="submit" class="btn btn-danger" value="Supprimer">
        </form>
        </td>
        </tr>
<?php
    }
}
?>
</tbody>


</table></div>
        <div class="col-lg-4 col-lg-offset-4" style="margin-top: 70px">
        <table class="table table-striped table-condensed">

            <?php

for ($i = 0; $i < count($connectionLog); $i++)
{
        echo "<tr>";
        echo "<td>" . $connectionLog[$i][0] . "</td>";
        echo "<td>" . $connectionLog[$i][1] . "</td>";
        echo "<td>" . $connectionLog[$i][2] . "</td>";
        echo "<tr>";
}
            ?>
</table>
            </div>



<style>
    b
    {
        color:red;
    }
</style>


Voici la page d'inscription

<form action="gestionUsagers.php" method="post">
    <div class="col-lg-4 col-lg-offset-4" style="margin-top: 50px">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="text-center">
                    LogIn
                </h4>
            </div>
            <div class="panel-body text-center" style="padding: 20px">
                    Nom d'utilisateur: <input name="username" class="form-control" value="<?= $username ?>" type="text" size="15"><br>
                    Mot de passe: <input name="password" class="form-control" type="password" size="30"><br>
                Confirmation du mot de passe: <input name="confirmpassword" class="form-control" type="password" size="30">
                <input type="submit" class="btn btn-info" value="S'inscrire!">
            </div>
        </div>
        <b><?=$messageErreur?></b>
    </div>
    </form>


    <br>
</div>
</div>

<?php include 'footer.php' ?>