<?php
include_once("BaseDeDonne");
include_once("Inscription_Controleur.php");
/**
 * Created by PhpStorm.
 * User: 201356187
 * Date: 2015-11-18
 * Time: 18:13
 */
echo "<form method='POST'>
          <div>
             Nom d'hero :
            <input type='text' name='Username'>
          </div>
          <div>
             Type :
             <input type='text' name='Type'>
          </div>
          <div>
             Race :
             <input type='text' name='Race'>
          </div>

          <input type='submit' name='Inscription' value='Je veux joindre la bataille'>
      </form>";
echo $VariableErreur;


echo "";

?>
<table>
    <tr>
        <td>Nom</td>
        <td>Type de joueur</td>
        <td>Race du joueur</td>
    </tr>
<?php
   for($i = 0; $i < count($TableauCharacter);$i++)
   {
 ?>
    <tr>
        <td><?php echo($TableauCharacter[$i][0]); ?></td>
        <td><?php echo($TableauCharacter[$i][1]); ?></td>
        <td><?php echo($TableauCharacter[$i][2]); ?></td>
        <form action="Inscription_Vue.php" method="post">
        <td><button name="Desinscription" value="<?php echo($TableauCharacter[$i][0]); ?>" >Supprimer</button></td>
        </form>
    </tr>
<?php }?>

</table>


