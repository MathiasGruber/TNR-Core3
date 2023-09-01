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

    require_once(Data::$absSvrPath.'/libs/villageSystem/anbuLib.php');

    class clanChat extends anbuLib {

        public function __construct(){

            // Try running the page
            try{

                functions::checkActiveSession();

                $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

                // Check if in anbu
                $anbuID = parent::isUserAnbu($GLOBALS['userdata'][0]['anbu']);

                if(!($anbuID)){
                    throw new Exception("You are not currently part of a squad. Request more info from the kage.");
                }

                // Get the squad
                $this->squad = parent::getAnbuSquad($anbuID);

                // Get libraries
                require_once(Data::$absSvrPath.'/libs/chatSystem/chatLib.inc.php');
                require_once(Data::$absSvrPath.'/ajaxLibs/staticLib/markitup.bbcode-parser.php');

                // Instantiate chat class
                $anbuChat = new chatLib('tavern_anbu');

                $anbuChat->setupChatSystem(
                    array(
                        "userTitleOverwrite" => $anbuChat->getUserRank("ANBU Leader"),
                        "tavernTable" => "tavern_anbu",
                        "tableColumn" => "anbu_name",
                        "tableSelect" => $GLOBALS['userdata'][0]['anbu'],
                        "chatName" => $this->squad[0]['name']." Chat",
                        "canCombat" => true,
                        "smartyTemplate" => "contentLoad"
                    )
                );

                // Wrap the contentLoad in a page wrapper with this javascript library
                if($GLOBALS['mf'] == 'yes')
                    $GLOBALS['page']->createPageWrapper("./content/tavern/Scripts/chatScripts_mf.js");
                else
                    $GLOBALS['page']->createPageWrapper("./content/tavern/Scripts/chatScripts.js");

                if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                    throw new Exception('There was an issue releasing the lock!');
                }

            } catch (Exception $e) {
                $GLOBALS['database']->transaction_rollback( $e->getMessage() );
                $GLOBALS['page']->Message( $e->getMessage() , 'Clan System', 'id='.$_GET['id'],'Return');
            }
        }
    }

    new clanChat();