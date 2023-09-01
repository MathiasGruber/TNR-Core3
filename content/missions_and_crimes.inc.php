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

require_once(Data::$absSvrPath.'/libs/villageSystem/missionCrimeLib.php');

class missions_and_crimes extends missionCrimeLib {

    public function __construct() {

        // Try running the page
        try{

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Include the tasksQuestMission library
            require_once(Data::$absSvrPath.'/libs/taskQuestMission.inc.php');
            $this->taskLibrary = new tasks;

            // Set all the data needed on page
            $this->setPageInformation();

            // Decide on what page to show
            $this->activeMission = $this->isDoingMission();

            if( !$this->activeMission ){
                if (!isset($_POST['Submit'])) {
                    $this->main_page();
                } else {
                    $this->initiateMission();
                }
            }
            else{
                if( isset($_GET['act']) && $_GET['act'] == "quitMission" ){
                    if (!isset($_POST['Submit'])) {
                        $GLOBALS['page']->Confirm("Are you sure you want to quit the mission.", 'Mission System', 'Quit now!');
                    }
                    else {
                        $this->quitMission( $this->activeMission );
                    }
                }
                else{
                    $this->finishMission();
                }
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Mission System', 'id='.$_GET['id'],'Return');
        }
    }
}

// instantiate
$page = new missions_and_crimes();