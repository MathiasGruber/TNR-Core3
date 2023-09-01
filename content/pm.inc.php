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

    // Based on sleep library
    require_once(Data::$absSvrPath.'/libs/pmSystem/pmLib.inc.php');
    require_once(Data::$absSvrPath.'/ajaxLibs/staticLib/markitup.bbcode-parser.php');

    class PM extends pmBasicFunctions {

        // Constructor
        public function __construct() {

            try{

                // Obtain lock
                $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

                // Check if this system is activated
                $globalSetting = $GLOBALS['database']->fetch_data("SELECT `character_cleanup` FROM `site_timer` WHERE `site_timer`.`script` = 'pmsSwitch' LIMIT 1");
                if( $globalSetting[0]['character_cleanup'] <= 0 ){
                    throw new Exception('PM system has been temporarily disabled. Please try again later!');
                }

                // Setup PM system
                ($_REQUEST['id'] === '3') ? $this->set_inbox_system() : $this->set_outbox_system();

                // Wrap the contentLoad in a page wrapper with this javascript library
                if($GLOBALS['mf'] == 'no')
                    $GLOBALS['page']->createPageWrapper("./content/PM/Scripts/pmSystemScript.js");

                // Release lock
                if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                    throw new Exception('There was an issue releasing the lock!');
                }

            } catch (Exception $ex) {
                $GLOBALS['page']->Message($ex->getMessage(), 'PM System', 'id='.$_GET['id'], "Return");
            }


        }
    }

    new PM();