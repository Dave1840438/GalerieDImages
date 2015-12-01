<h3> La merveilleuse galerie de photos! :)</h3>
<br>
<br>

<style>
    .Images
    {
        max-height: 200px;
        max-width: 200px;
    }

</style>

<?php


include ("protectedPage.php");
include_once("BaseDeDonne.php");
include_once("OperationsBD.php");
include_once("_.php");


if ($_SESSION["isAdmin"])
{
    echo 'admin shits:<br>';
    echo '<a href="listerUsagers.php">Supprimer des usagers</a><br>';
}

echo 'Options: <br>';

echo '<a href="profil.php">Profil</a><br>';
echo '<a href="Deconnection.php">Deconnection</a><br>';


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
            move_uploaded_file($_FILES['fichier']['tmp_name'], $fich);
        else
            $message = "Erreur d'insertion";
    }
    else
        $message = "Le fichier est invalide!";

}

$DAL = new BDOperations();
$touteLesImages = $DAL->selectAllImages();

echo '<h1>Hello ' . $_SESSION["username"] . '!</h1>';

for ($i = 0; $i < count($touteLesImages); $i++)
{?>
    Titre: <?=$touteLesImages[$i][1]?><br>
    Auteur: <?=$touteLesImages[$i][2]?>
    <form action=gestImage.php method="post">
    <input type="hidden" name="IdImage", value="<?= $touteLesImages[$i][0]?>">
    <input type="image" class="Images" src="<?= $touteLesImages[$i][3] ?>" border="3" alt="Submit" />
    </form>
    Date de publication: <?=$touteLesImages[$i][4]?>
    <br>
    <br>
    <hr>
<?php }


?>


<br>
<br>
<form action="index.php" method="post"
        enctype="multipart/form-data">
    <fieldset>
    <Legend>Ajouter une photo!</Legend>
    Titre: <input type="text" name="title" value="<?=$titrePhoto?>" size="20"><br>
    <input type="hidden" name="MAX_FILE_SIZE" value="10000000">
    Photo: <input name="fichier" size="45" type="file" accept="image/*">
    <input type="submit" name="submitPicture" value="envoyer">
    </fieldset>
</form>
<br>
<b style="color:red;"><?=$message?></b>
