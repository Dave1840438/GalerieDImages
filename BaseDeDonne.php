<?php
$server = "mysql:host=127.0.0.1; dbname=GalerieImagesBD; charset=utf8";
$usager = "root";
$pass ="";

$bdd = new PDO($server, $usager, $pass,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

?>