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

<link rel="stylesheet" href="css/bootstrap.min.css"/>
<!-- jQuery -->
<script src="js/jquery.js"></script>
<script src="js/bootstrap.min.js"></script>

<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Home</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li class="active">
                    <?php
                    echo '<a href="profil.php">Profil</a>';
                    ?>
                </li>
                <?php
                if ($_SESSION["isAdmin"])
                {
                    echo '<li><a href="listerUsagers.php">Supprimer des usagers</a></li>';
                }
                ?>
                <li>
                    <?php
                    echo '<a href="Deconnection.php">Deconnection</a>';
                    ?>
                </li>

            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>







    <div class="container" style="margin-top: 70px">
        <div class="row">

            <div class="col-lg-4 col-lg-offset-4" style="margin-top: 50px">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="text-center">
                            Profile
                        </h4>
                    </div>
                    <div class="panel-body text-center" style="padding: 20px">
                        <form action="profil.php" method="post">
                        Mot de passe: <input name="password" class="form-control" type="password" size="30"><br>
                        Confirmation du mot de passe: <input name="confirmPassword" class="form-control" type="password" size="30" style="margin-bottom: 10px">
                        <input type="submit" class="btn btn-info" name="updatePassword" value="Sauvegarder"!">
                        </form>
                        <hr>
                        <form action="profil.php" method="post">
                            <fieldset>

                                Rester LogIn: <input type="checkbox" name="stayConnected">
                                <input type="submit" name="changeConnectionTimeout" class="btn btn-info" value="Sauvegarder"!">
                            </fieldset>
                        </form>
                        <?=$message?>
                    </div>
                </div>
             </div>
        </div>
    </div>

<br>




