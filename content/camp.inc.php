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
    require_once(Data::$absSvrPath.'/libs/villageSystem/sleepLib.php');
    class camping extends sleepLibrary {

        // Constructor
        public function __construct() {

            // Try-Catch
            try {

                // Check the user session
                functions::checkActiveSession();

                // Obtain class lock
                $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

                // Start transaction here
                $GLOBALS['database']->transaction_start();

                // Determine what screen to display
                if ( !isset($_GET['act']) || !in_array($_GET['act'], array('sleep', 'wake'), true)) {
                    self::main_screen();
                }
                else {
                    switch($_GET['act']) {
                        case('sleep'): parent::sleep(); break;
                        case('wake'): parent::wakeup(); break;
                    }
                }

                // Commit transaction
                $GLOBALS['database']->transaction_commit();

                // Release class lock
                if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                    throw new Exception('There was an issue releasing the lock!');
                }
            }
            catch (Exception $e) {
                // Rollback transaction
                $GLOBALS['database']->transaction_rollback($e->getMessage());

                // Check for return message preferences
                $returnLink = isset($this->returnLink) ? $this->returnLink : 'id='.$_GET['id'];
                $returnMessage = isset($this->returnMessage) ? $this->returnMessage : 'Return';

                // Give a message
                $GLOBALS['page']->Message($e->getMessage(), "Camp", $returnLink, $returnMessage);
            }
        }

        // Main screen
        private function main_screen() {
            // Show in smarty
            $GLOBALS['template']->assign('status', $GLOBALS['userdata'][0]['status']);
            $GLOBALS['template']->assign('village', $GLOBALS['userdata'][0]['village']);
            $GLOBALS['template']->assign('increase', parent::getCampRegeneration($GLOBALS['userdata'][0]['rank_id']));
            
            if($GLOBALS['userdata'][0]['village'] == "Syndicate")
                $GLOBALS['template']->assign('syndicate_mode', true);
            else
                $GLOBALS['template']->assign('syndicate_mode', false);

            $GLOBALS['template']->assign('contentLoad', './templates/content/camp/camp_main_screen.tpl');
        }
    }

    new camping();