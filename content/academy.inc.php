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

        try {

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Setup a training system
            $this->setupTrainingSystem(array(
                "availableJutsuTypes" => array("normal", "special"),
                "availableGenerals" => array("intelligence"),
                "availableStats" => array("nin", "gen", "tai", "weap"),
                "jutsuAttackTypes" => array('ninjutsu', 'genjutsu', 'taijutsu', 'weapon','highest'),
                "jutsuTypes" => array('special','forbidden','normal','loyalty','village','clan','bloodline'),
                "jutsuMastery" => false
            ));

            // Show the wrapper
            $this->showTrainingWrapper();

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        }
        catch(Exception $e) {
            $GLOBALS['page']->Message($e->getMessage(), 'Academy', 'id='.$_GET['id'], 'Return');
        }
    }
}
new train();