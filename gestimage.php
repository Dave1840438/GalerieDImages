

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
    if ($_SESSION["userID"] == $uneImage[0][2])
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

echo "<h1>L'image vue de plus proche</h1>";
echo '<a href="index.php">Retour</a><br>';

echo '<img src="' . $uneImage[0][1] . '" style="max-width:800px;max-height:600px">';

echo '<br>';



if ($_SESSION["userID"] == $uneImage[0][2])
{?>
    <form method="post" action="gestimage.php">
        <input type="hidden" name="IdImage" value="<?= $_POST['IdImage']?>">
        <input type="submit" name="deleteImage" value="Supprimer">
    </form>

<?php }

for ($i = 0; $i < count($tousLesCommentaires); $i++)
{
    echo 'Pseudonyme: ' . $tousLesCommentaires[$i][0] . '<br>';
    echo 'Commentaire: ' . $tousLesCommentaires[$i][1] . '<br>';
    echo 'Date de publication: ' . $tousLesCommentaires[$i][2] . '<br>';
}
?>


<form action="gestimage.php" method="post">
    <fieldset>
        <legend>Ajouter un commentaire</legend>
        Commentaire: <input name="comment" type="text" size="100"><br>
        <input type="submit" name="submitComment" value="Publier!">
        <input type="hidden" name="IdImage" value="<?= $_POST['IdImage']?>">
    </fieldset>
</form>

<


