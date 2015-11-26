<?php
include '_.php';
include_once("protectedPage.php");
include_once("BaseDeDonne.php");
include_once("OperationsBD.php");

$DAL = new BDOperations();

$rep = 'Images/';
$fich = $rep . phunction_Text::GUID();

//Vérifie le type du fichier, si c'est une image on sauvegarde le fichier
if (strpos($_FILES['fichier']['type'], 'image/') !== false)
{
    if ($DAL->ajouterPhoto($fich, $_SESSION["userID"]))
        move_uploaded_file($_FILES['fichier']['tmp_name'], $fich);
    else
        die("Erreur d'insertion");
}
header('Location: ./');

