<?php
/**
 * Created by PhpStorm.
 * User: 201356743
 * Date: 2015-12-10
 * Time: 13:07
 */
?>

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
            <a class="navbar-brand" href="index.php">8gag</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li>
                    <?php
                    echo '<a href="profil.php">Profil</a>';
                    ?>
                </li>
                <?php
                if ($_SESSION["isAdmin"])
                {
                    echo '<li><a href="gestionUsagers.php">Gestion des usagers</a></li>';
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