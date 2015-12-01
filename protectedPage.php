<?php
session_start();
if (!isset($_SESSION["userID"]))
    header('Location: ./login.php');

if (isset($_SESSION["LAST_ACTIVITY"]) && isset($_SESSION["SESSION_TIMEOUT"])
    && time() - $_SESSION["LAST_ACTIVITY"] > $_SESSION["SESSION_TIMEOUT"])
        header('Location: ./Deconnection.php');

$_SESSION["LAST_ACTIVITY"] = time();


