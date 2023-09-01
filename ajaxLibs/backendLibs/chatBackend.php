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

    require_once(Data::$absSvrPath.'/libs/chatSystem/chatLib.inc.php');
    require_once(Data::$absSvrPath.'/ajaxLibs/staticLib/markitup.bbcode-parser.php');

    // Set up class for handling stuff
    class chatBackend extends chatLib {

        // Constructor
        public function __construct($setup, $token = "N/A") {

            if(isset($_GET['mf']) && $_GET['mf'] == 'yes')
                $GLOBALS['mf'] = 'yes';

            // Get tavern setup from the POST variable
            $decoded_setup = parent::decodeSetup($setup);

            $chatSetup = array_merge(
                $decoded_setup, 
                array(
                    "tokenCheck" => $token, 
                    "originalSetup" => $decoded_setup
                )
            );
            
            // Loads Necessary User Data
            parent::__construct($chatSetup['tavernTable']);

            if(isset($_REQUEST['refresh']) && $_REQUEST['refresh'] == true) {
                parent::setupChatRefresh($chatSetup);
            }
            else {
                // Setup the tavern, with a tokenCheck.
                parent::setupChatSystem($chatSetup);
            }

        }

        public function getDisplay() {
            $contentPage = ".".$GLOBALS['template']->tpl_vars['contentLoad']->value;

            if(isset($GLOBALS['mf']) && $GLOBALS['mf'] == 'yes')
                $contentPage = str_replace('.tpl','_mf.tpl',$contentPage);

            $returnData['mainContent'] = $GLOBALS['template']->fetch($contentPage);
            return $returnData;
        }
    }