<?php 
 /* ============== LICENSE INFO START ==============
  * 2005 - 2016 Studie-Tech ApS, All Rights Reserved
  * 
  * This file is part of the project www.TheNinja-RPG.com.
  * Dissemination of this information or reproduction of this material
  * is strictly forbidden unless prior written permission is obtained
  * from Studie-Tech ApS.
  * ============== LICENSE INFO END ============== */ 
?>
<?php

// Create handler class based on the main library
require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
require_once(Data::$absSvrPath.'/libs/trainingSystem/trainLib.php');
class train extends trainLib {

    // Constructor
    public function __construct() {

        // Check if this user is in a squad
        try{

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Get information on the sensei
            $sensei = $GLOBALS['database']->fetch_data("
                 SELECT `sensei`,`username`
                 FROM `users_preferences`
                 LEFT JOIN `users` ON (`users_preferences`.`sensei` = `users`.`id`)
                 WHERE `uid` = '" . $_SESSION['uid'] . "'
                 LIMIT 1");
            if( $sensei !== "0 rows" && $sensei[0]['username'] !== null){
                $description = "Your sensei ".$sensei[0]['username']." can train you and teach you several things in order to help you on your way towards becoming a real ninja.";
            } else {
                $description = "You have not yet been assigned a sensei. Who needs those anyways though? You can train yourself just as easily.";
            }

            // Setup a training system
            $this->setupTrainingSystem(array(
                "availableJutsuTypes" => array("normal", "special"),
                "availableGenerals" => array("strength", "intelligence", "willpower", "speed"),
                "availableStats" => array("nin", "gen", "tai", "weap"),
                "jutsuAttackTypes" => array('ninjutsu', 'genjutsu', 'taijutsu', 'weapon','highest'),
                "jutsuTypes" => array('special','forbidden','normal','loyalty','village','clan','bloodline'),
                "jutsuMastery" => true,
                "mainText" => $description
            ));

            // Show the wrapper
            $this->showTrainingWrapper();

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        } catch (Exception $e) {
            $GLOBALS['page']->Message($e->getMessage(), 'Training System', 'id='.$_GET['id'], 'Return');
        }
    }
}
new train();