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
<link rel="stylesheet" href="css/bootstrap.min.css"/>
<script src="js/bootstrap.min.js"></script>
<style>
    b{
        color:red;
    }
</style>

<form action="login.php" method="post">
    <div class="container">
        <div class="row">

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
                        <input type="submit" class="btn btn-info" value="Se connecter!">
                    </div>
                </div>
            </div>
        </div>
    </div>



</form>

<b><?= $message ?></b>


