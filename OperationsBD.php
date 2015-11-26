<?php

class BDOperations
{
    private $bdd;

    function __construct()
    {
        global $bdd;
        $this->bdd = $bdd;
    }

    function inscrireUsager($username, $password)
    {
        if ($sqlInsert = $this->bdd->prepare("INSERT INTO USERS (USERNAME, PASSWORD, ISADMIN) VALUES(?, ?, 0)"))
        {
            $sqlInsert->bindParam(1, $username, PDO::PARAM_STR);
            $sqlInsert->bindParam(2, $password, PDO::PARAM_STR);

            $result = $sqlInsert->execute();
            $sqlInsert->closeCursor();
            return $result;
        }
        else
        {
            die("Erreur d'acc�s � la bd!");
        }
    }

    function supprimerUsager($id)
    {
        if ($sqlDelete = $this->bdd->prepare("DELETE FROM USERS WHERE ID = ?"))
        {
            if ($sqlSelect = $this->bdd->prepare("SELECT GUID FROM PICTURES WHERE IDOWNER = ?"))
            {
                $sqlSelect->bindParam(1, $id, PDO::PARAM_INT);

                if($sqlSelect->execute())
                {
                    $images = $sqlSelect->fetchAll();
                    $sqlSelect->closeCursor();

                    for ($i = 0; $i < count($images); $i++)
                    {
                        unlink('./' . $images[$i][0]);
                    }
                }
                else
                {
                    die("Erreur lors de la suppression en cascade des images");
                }
            }
            else
            {
                die("Erreur d'acc�s � la bd!");
            }


            $sqlDelete->bindParam(1, $id, PDO::PARAM_INT);

            $result = $sqlDelete->execute();
            $sqlDelete->closeCursor();
            return $result;
        }
        else
        {
            die("Erreur d'acc�s � la bd!");
        }
    }


    function selectAllUsagers()
    {
        if ($sqlSelect = $this->bdd->prepare("SELECT * FROM USERS"))
        {
            $sqlSelect->execute();
            $tousLesUsagers = $sqlSelect->fetchAll();
            $sqlSelect->closeCursor();

            return $tousLesUsagers;
        }
        else
        {
            die("Erreur d'acc�s � la bd!");
        }
    }

    function selectAllImages()
    {
        if ($sqlSelect = $this->bdd->prepare("SELECT * FROM PICTURES ORDER BY ID DESC"))
        {
            $sqlSelect->execute();
            $toutesLesImages = $sqlSelect->fetchAll();
            $sqlSelect->closeCursor();

            return $toutesLesImages;
        }
        else
        {
            die("Erreur d'acc�s � la bd!");
        }
    }

    function login($username, $password)
    {
        if ($sqlSelect = $this->bdd->prepare("SELECT * FROM USERS WHERE USERNAME = ? AND PASSWORD = ?"))
        {
            $sqlSelect->bindParam(1, $username, PDO::PARAM_STR);
            $sqlSelect->bindParam(2, $password, PDO::PARAM_STR);

            $sqlSelect->execute();
            $unUsager = $sqlSelect->fetchAll();
            $sqlSelect->closeCursor();

            if (count($unUsager) == 1)
                return $unUsager;
            else
                return false;
        }
        else
        {
            die("Erreur d'acc�s � la bd!");
        }
    }

    function isAdmin($id)
    {
        if ($sqlSelect = $this->bdd->prepare("SELECT * FROM USERS WHERE ID = ? AND ISADMIN = 1"))
        {
            $sqlSelect->bindParam(1, $id, PDO::PARAM_INT);

            $sqlSelect->execute();
            $unUsager = $sqlSelect->fetchAll();
            $sqlSelect->closeCursor();

            if (count($unUsager) == 1)
                return true;
            else
                return false;
        }
        else
        {
            die("Erreur d'acc�s � la bd!");
        }
    }

    function ajouterPhoto($nomFichier, $ownerId)
    {
        if ($sqlInsert = $this->bdd->prepare("INSERT INTO PICTURES (GUID, IDOWNER) VALUES(?, ?)"))
        {
            $sqlInsert->bindParam(1, $nomFichier, PDO::PARAM_STR);
            $sqlInsert->bindParam(2, $ownerId, PDO::PARAM_STR);

            $result = $sqlInsert->execute();
            $sqlInsert->closeCursor();
            return $result;
        }
        else
        {
            die("Erreur d'acc�s � la bd!");
        }
    }

    function supprimerPhoto($idImage)
    {
        if ($sqlDelete = $this->bdd->prepare("DELETE FROM PICTURES WHERE ID = ?"))
        {
            $sqlDelete->bindParam(1, $idImage, PDO::PARAM_INT);

            $result = $sqlDelete->execute();
            $sqlDelete->closeCursor();
            return $result;
        }
        else
        {
            die("Erreur d'acc�s � la bd!");
        }
    }

    function selectionnerPhoto($idImage)
    {
        if ($sqlSelect = $this->bdd->prepare("SELECT * FROM PICTURES WHERE ID = ?"))
        {
            $sqlSelect->bindParam(1, $idImage, PDO::PARAM_INT);

            $sqlSelect->execute();
            $uneImage = $sqlSelect->fetchAll();
            $sqlSelect->closeCursor();

            if (count($uneImage) == 1)
                return $uneImage;
            else
                return false;
        }
        else
        {
            die("Erreur d'acc�s � la bd!");
        }
    }

    function insertComment($comment, $pictureId, $userId)
    {
        if ($sqlInsert = $this->bdd->prepare("INSERT INTO COMMENTS (COMMENT, IDPICTURE, IDAUTHOR) VALUES(?, ?, ?)"))
        {
            $sqlInsert->bindParam(1, $comment, PDO::PARAM_STR);
            $sqlInsert->bindParam(2, $pictureId, PDO::PARAM_INT);
            $sqlInsert->bindParam(3, $userId, PDO::PARAM_INT);

            $result = $sqlInsert->execute();
            $sqlInsert->closeCursor();
            return $result;
        }
        else
        {
            die("Erreur d'acc�s � la bd!");
        }
    }

    function selectAllCommentsForPicture($idImage)
    {
        if ($sqlSelect = $this->bdd->prepare("SELECT U.USERNAME, C.COMMENT, C.TIMEOFPOST FROM COMMENTS C INNER JOIN USERS U ON C.IDAUTHOR = U.ID WHERE IDPICTURE = ? ORDER BY C.ID DESC"))
        {
            $sqlSelect->bindParam(1, $idImage, PDO::PARAM_INT);

            $sqlSelect->execute();
            $tousLesCommentaires = $sqlSelect->fetchAll();
            $sqlSelect->closeCursor();

            return $tousLesCommentaires;
        }
        else
        {
            die("Erreur d'acc�s � la bd!");
        }
    }
}
