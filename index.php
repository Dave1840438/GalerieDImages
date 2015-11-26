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


if ($_SESSION["isAdmin"])
{
    echo 'admin shits:<br>';
    echo '<a href="inscription.php">Ajouter des usagers</a><br>';
    echo '<a href="listerUsagers.php">Supprimer des usagers</a><br>';
}

echo 'Options:<br><a href="Deconnection.php">Deconnection</a><br>';

$DAL = new BDOperations();
$touteLesImages = $DAL->selectAllImages();

echo '<h1>Hello ' . $_SESSION["username"] . '!</h1>';

echo '<table>';

for ($i = 0; $i < count($touteLesImages); $i++)
{?>
    <form action=gestImage.php method="post">
    <input type="hidden" name="IdImage", value="<?= $touteLesImages[$i][0]?>">
    <input type="image" class="Images" src="<?= $touteLesImages[$i][1] ?>" border="3" alt="Submit" />
    </form>
    <br>
<?php }


?>
</table>

<br>
<br>
<form action="sauvegarderPhoto.php" method="post"
        enctype="multipart/form-data">
    <fieldset>
    <Legend>Ajouter une photo!</Legend>
    <input type="hidden" name="MAX_FILE_SIZE" value="10000000">
    Photo: <input name="fichier" size="45" type="file" accept="image/*">
    <input type="submit" value="envoyer">
    </fieldset>
</form>
