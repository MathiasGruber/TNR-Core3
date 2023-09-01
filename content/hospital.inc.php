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

// Get hospital library
require_once(Data::$absSvrPath.'/libs/hospitalSystem/healLib.inc.php');

class hospital extends hospitalFunctions{

    // Constructor
    public function __construct() {

        try {

            // Obtain lock
            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Set name variable
            $this->hospitalName = "Hospital";

            // Set hospital data & fix various errors
            $this->setHospitalData();

            if($GLOBALS['userdata'][0]['status'] != 'hospitalized')
            {
                $GLOBALS['page']->Message("The hospital is for the sick, the wounded, and their visitors. You have no business here. Please leave.",
                    $this->hospitalName, 'id='.$_GET['id']);
            }

            // Decide what screen to show
            else if (!isset($_GET['act'])) {

                // Main screen
                $this->main_screen();

                // Load the smarty template
                $GLOBALS['template']->assign('contentLoad', './templates/content/hospital/hospital_main.tpl');

            } else {

                // Check that user is not sleeping
                if( in_array($GLOBALS['userdata'][0]['status'], array("hospitalized","awake"), true) ){

                    // Action screens
                    if ($_GET['act'] == 'bribe' ) {
                        $this->bribe();
                    } elseif ($_GET['act'] == 'release') {
                        $this->release();
                    }
                }
                else{
                    $GLOBALS['page']->Message("You cannot be released from the hospital when you're asleep", $this->hospitalName, 'id=2');
                }
            }

            // Release lock
            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            $GLOBALS['page']->Message($e->getMessage(), 'Hospital System', 'id=2');
        }
    }
}

new hospital();