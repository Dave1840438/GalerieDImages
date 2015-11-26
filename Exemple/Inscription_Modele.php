<?php



class Gestion
{

    private $bdd;

    function __construct()
    {
        global $bdd;
        $this->bdd = $bdd;
    }


    function Inscription($user,$type,$race)
    {

        if($sqlInsert = $this->bdd->prepare("INSERT INTO usagers(Username,letype,Race) VALUES(?,?,?)"))
        {

            $sqlInsert->bindParam(1,$user, PDO::PARAM_STR);
            $sqlInsert->bindParam(2,$type, PDO::PARAM_STR);
            $sqlInsert->bindParam(3,$race, PDO::PARAM_STR);

            if($sqlInsert->execute())
            {
                $sqlInsert->closeCursor();
                return true;
            }
            else
            {
                $sqlInsert->closeCursor();
                return false;
            }
        }
        else
        {
            die("Erreur : MYSQL statement n'a pas pu être préparé");
        }
    }

    function Desinscription($user)
    {
        if($sqlDelete = $this->bdd->prepare("DELETE  from usagers where Username=?"))
        {
            $sqlDelete->bindParam(1,$user, PDO::PARAM_STR);

            if($sqlDelete->execute())
            {
                $sqlDelete->closeCursor();
                return true;
            }
            else
            {
                $sqlDelete->closeCursor();
                return false;
            }
        }
        else
        {
            die("Erreur : MYSQL statement n'a pas pu être préparé");
        }
    }

    function SelectUsager()
    {

        if($sqlSelect = $this->bdd->prepare("SELECT * FROM USAGERS"))
        {
            $sqlSelect ->execute();
            $toutlesUsagers =  $sqlSelect->fetchAll();
            $sqlSelect->closeCursor();

            return  $toutlesUsagers ;

        }
        else
        {
            die("Erreur : MYSQL statement n'a pas pu être préparé");
        }
    }

}





