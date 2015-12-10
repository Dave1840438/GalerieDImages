<link rel="stylesheet" href="css/bootstrap.min.css"/>
<script src="js/jquery.js"></script>
<script src="js/bootstrap.min.js"></script>

<style>
    .Images
    {
        max-height: 500px;
        max-width: 500px;
    }

</style>

<?php
include_once("protectedPage.php");
include_once("BaseDeDonne.php");
include_once("OperationsBD.php");


$DAL = new BDOperations();

if (isset($_POST["submitComment"]))
{
    $DAL->insertComment($_POST["comment"], $_POST["IdImage"], $_SESSION["userID"]);
}
$uneImage = $DAL->selectionnerPhoto($_POST['IdImage']);
$tousLesCommentaires = $DAL->selectAllCommentsForPicture($_POST["IdImage"]);

if (isset($_POST["deleteImage"]))
{
    if ($_SESSION["userID"] == $uneImage[0][2] || $_SESSION["isAdmin"])
    {
        unlink('./' . $uneImage[0][1]);
        $DAL->supprimerPhoto($uneImage[0][0]);
        header('Location: ./');
    }
}

if ($uneImage == false)
{
    die("L'image n'existe pas");
}
?>
<?php include 'Header.php' ?>
<div class="container" style="margin-top: 70px">
    <div class="row">

        <div class="col-lg-6 col-lg-offset-3">
            <div class="panel panel-default" style="border-radius: 0px">
                <div class="panel-heading">
                    <h4 class="text-center">

                    </h4>
                </div>
                <div class="panel-body text-center" style="padding: 0px">
                    <?php
                    echo '<img src="' . $uneImage[0][1] . '" style="max-width:500px;max-height:500px">';
                    ?>
                    <?php

                    if ($_SESSION["userID"] == $uneImage[0][2] || $_SESSION["isAdmin"]) {
                        ?>

                        <form method="post" action="gestimage.php">
                            <input type="hidden" name="IdImage" value="<?= $_POST['IdImage'] ?>">
                            <input type="submit" name="deleteImage" class="btn btn-info" value="Supprimer">
                        </form>
                    <?php
                    }
                    ?>
                </div>

                <div class="message-wrap col-lg-12" style="background-color: #EFEFEF;">
                    <div class="msg-wrap">
                        <hr>
                        <?php

                        for ($i = 0; $i < count($tousLesCommentaires); $i++) {
                            ?>

                            <div class="media msg " style="margin: 10px;background-color:#cccccc; padding: 10px">
                                <div class="media-body">
                                    <small class="pull-right time">
                                        <i class="fa fa-calendar"></i> <?php
                                       echo '<h7>'.$tousLesCommentaires[$i][2].'</h7>';
                                        ?><br>
                                    </small>
                                    <h5 class="media-heading">
                                        <?php
                                       echo '<h4>'.$tousLesCommentaires[$i][0]. '</h4>';
                                        ?>

                                    </h5>
                                    <small class="col-lg-10">
                                        <?php
                                       echo  '<h6>'.$tousLesCommentaires[$i][1]. '</h6>';
                                        ?>
                                    </small>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                    <form action="gestimage.php" method="post">
                    <div class="send-wrap ">
                        <textarea class="form-control send-message" name="comment" rows="3" size="150" placeholder="Write a reply..."></textarea>
                    </div>
                    <div class="btn-panel">
                        <input type="submit" class="btn btn-info" style="margin-top:10px " name="submitComment" value="Publier!">
                        <input type="hidden" name="IdImage" value="<?= $_POST['IdImage']?>">
                    </div>
                    </form>
                </div>
            </div>
            <?php
            echo '<a href="index.php" class="btn btn-info">Retour</a><br>';
            ?>
        </div>
    </div>
</div>



<?php include 'footer.php' ?>


