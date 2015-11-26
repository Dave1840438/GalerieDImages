<?php
include_once("Inscription_Modele.php");

$Gestion  = new Gestion();
/**
 * Created by PhpStorm.
 * User: 201356187
 * Date: 2015-11-18
 * Time: 18:14
 */
$VariableErreur ="";
$VariableSucces ="";

$TableauCharacter = $Gestion->SelectUsager();


if(isset($_POST["Inscription"]))
{
    if(empty($_POST['Username']) && empty($_POST['Type']) && empty($_POST['Race']))
    {
                if($Gestion->Inscription($Username,$Type,$Race))
                {
                    $VariableSucces ="Bravo asti de champion";
                }
                else
                {
                    $VariableErreur ="Ca marche pas";
                }
    }
    else
    {
        $VariableErreur = "Des champs sont vide biatch";
    }
}

if(isset($_POST["Desinscription"]))
{
    echo("yolo");
    $Gestion->Desinscription($_POST["Desinscription"]);
    $TableauCharacter = $Gestion->SelectUsager();

}


