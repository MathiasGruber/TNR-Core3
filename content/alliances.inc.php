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

// Get library
require_once(Data::$absSvrPath.'/libs/villageSystem/warLib.php');

// Create new class
class alliances extends warLib {

    // Constructor
    public function __construct() {

        try {

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Get alliance data
            $this->setAlliances();

            // Get alliances for show
            $alliances = $this->getAlliesForDisplay();

            // Create alliance page
            $GLOBALS['template']->assign('allianceData', $alliances);
            $GLOBALS['template']->assign('contentLoad', './templates/content/alliance/alliances.tpl');

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        }
        catch(Exception $e) {
            $GLOBALS['page']->Message($e->getMessage(), 'Alliances', 'id='.$_GET['id'], 'Return');
        }
    }
}

new alliances();