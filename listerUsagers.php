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

$tousLesUsagers = $DAL->selectAllUsagers();

echo "Usagers autres que l'admin:<br>";

echo '<table>';

for ($i = 0; $i < count($tousLesUsagers); $i++)
{
    if (isset($_SESSION["idToDelete"]) && $_SESSION["userID"] != $_POST["idToDelete"])
    {
        echo "<tr>";
        echo "<td>" . $tousLesUsagers[$i][0] . "</td>";
        echo "<td>" . $tousLesUsagers[$i][1] . "</td>";
        echo "<td>" . $tousLesUsagers[$i][2] . "</td>";
        echo "<td>" . $tousLesUsagers[$i][3] . "</td>";
        ?>

        <td>
        <form action="listerUsagers.php" method="post">
            <input type="hidden" name="idToDelete" value="<?= $tousLesUsagers[$i][0] ?>">
            <input type="submit" value="Supprimer">
        </form>
        </td>
        </tr>
<?php
    }
}

echo '</table>';
echo '<a href="index.php">Retour</a>';


