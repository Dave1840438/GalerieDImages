<?php
$server = "mysql:host=localhost; dbname=db_davedom; charset=utf8";
$usager = "davedom ";
$pass ="88ch,,Vm";

$bdd = new PDO($server, $usager, $pass,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

?>