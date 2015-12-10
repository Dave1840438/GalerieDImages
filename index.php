<link rel="stylesheet" href="css/bootstrap.min.css"/>
<!-- jQuery -->
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


include ("protectedPage.php");
include_once("BaseDeDonne.php");
include_once("OperationsBD.php");
include_once("_.php");



$message = "";
$titrePhoto = "";
$DAL = new BDOperations();

if (isset($_POST["title"]))
    $titrePhoto = $_POST["title"];

if (isset($_POST["submitPicture"]))
{
    $rep = 'Images/';
    $fich = $rep . phunction_Text::GUID();

    if ($_POST["title"] == "")
    {
        $message = "L'image doit avoir un titre!";
    }
    else if (strpos($_FILES['fichier']['type'], 'image/') !== false)
        //Vérifie le type du fichier, si c'est une image on sauvegarde le fichier
    {
        if ($DAL->ajouterPhoto($fich, $_SESSION["userID"], $_POST["title"]))
        {
            move_uploaded_file($_FILES['fichier']['tmp_name'], $fich);
            header('Location: ./');
        }
        else
            $message = "Erreur d'insertion";
    }
    else
        $message = "Le fichier est invalide!";
}
$touteLesImages = $DAL->selectAllImages();
?>
<?php include 'Header.php' ?>
<?php



for ($i = 0; $i < count($touteLesImages); $i++)
{?>

<div class="col-lg-4 col-lg-offset-4" style="margin-top: 70px">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="text-center">
                Titre: <?=$touteLesImages[$i][1]?><br>
                Auteur: <?=$touteLesImages[$i][2]?>

            </h4>
        </div>
        <div class="panel-body text-center" style="padding: 0px">
            <form action=gestImage.php method="post">
                <input type="hidden" name="IdImage", value="<?= $touteLesImages[$i][0]?>">
                <input type="image" class="Images" style="max-height: 150px; max-width: 200px" src="<?= $touteLesImages[$i][3] ?>" alt="Submit" />
            </form>
        </div>
        <div class="panel-footer">
            Date de publication: <?=$touteLesImages[$i][4]?>
        </div>
    </div>
</div>
<?php }


?>


<br>
<br>
<div class="col-lg-4 col-lg-offset-4" style="margin-top: 70px">
    <div class="col-lg-6 col-lg-offset-3">
<form action="index.php" method="post"
        enctype="multipart/form-data">
    <fieldset>
    <Legend>Ajouter une photo!</Legend>
    Titre: <input type="text" class="form-control" name="title" value="<?=$titrePhoto?>" size="20"><br>
    <input type="hidden" name="MAX_FILE_SIZE" value="10000000">
    Photo: <input name="fichier" size="45" type="file" style="margin-bottom: 20px" accept="image/*">
    <input type="submit" class="btn btn-info btn-block" name="submitPicture" value="envoyer">
    </fieldset>
</form>
    </div>
    <b style="color:red;"><?=$message?></b>
</div>
<br>


<?php include 'footer.php' ?>
