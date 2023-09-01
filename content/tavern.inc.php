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

    // Basically just a simple instantiation of the chat system
    require_once(Data::$absSvrPath.'/libs/chatSystem/chatLib.inc.php');
    require_once(Data::$absSvrPath.'/ajaxLibs/staticLib/markitup.bbcode-parser.php');

    class tavern extends chatLib {

        // Constructor
        public function __construct() {

            try {

                functions::checkActiveSession();

                $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

                // Loads Necessary User Data
                parent::__construct();

                // Set table select
                $tableSelect = ($GLOBALS['page']->hasVillage) ? $GLOBALS['page']->userLocation : "Syndicate";

                // Setup the chat system
                parent::setupChatSystem(
                    array(
                        "userTitleOverwrite" => parent::getUserRank(),
                        "tavernTable" => "tavern",
                        "tableColumn" => "village_name",
                        "tableSelect" => $tableSelect,
                        "chatName" => parent::getTavernName(),
                        "smartyTemplate" => "contentLoad"
                    )
                );

                // Wrap the contentLoad in a page wrapper with this javascript library
                if( isset($GLOBALS['mf']) && $GLOBALS['mf'] == 'yes')
                    $GLOBALS['page']->createPageWrapper("./content/tavern/Scripts/chatScripts_mf.js");
                else
                    $GLOBALS['page']->createPageWrapper("./content/tavern/Scripts/chatScripts.js");

                if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                    throw new Exception('There was an issue releasing the lock!');
                }
            }
            catch(Exception $e) {
                // Rollback possible transactions
                $GLOBALS['database']->transaction_rollback($e->getMessage());

                // Show error message
                $GLOBALS['page']->Message($e->getMessage(), 'Tavern Error', 'id='.$_GET['id']);
            }
        }
    }

    new tavern();