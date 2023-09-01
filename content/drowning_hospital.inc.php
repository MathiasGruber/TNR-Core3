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

class hospital extends hospitalFunctions {
    public function __construct() {

        try{

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Set name variable
            $this->hospitalName = "Drowning";

            // Set hospital data & fix various errors
            $this->setHospitalDataDrowning();

            // Decide what screen to show
            if (!isset($_GET['act'])) {

                // Main screen
                $this->main_screen();

                // Load the smarty template
                $GLOBALS['template']->assign('contentLoad', './templates/content/drowning/drowning_main.tpl');

            }
            else
            {
                // Action screens
                if ($_GET['act'] == 'release')
                {
                    $this->releaseDrowning();
                }
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

            // Catch exceptions
        }
        catch (Exception $ex) {
            $GLOBALS['page']->Message($ex->getMessage(), 'Drowning Hospital System', 'id='.$_GET['id'], "Return");
        }
    }
}

new hospital();