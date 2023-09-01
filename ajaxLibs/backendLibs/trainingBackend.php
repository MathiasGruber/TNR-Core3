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

require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
require_once(Data::$absSvrPath.'/libs/trainingSystem/trainLib.php');
require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');


// Setup class for handling stuff
class trainBackend extends trainLib {

    // Constructor
    public function __construct( $setup, $token = "N/A" ) {

        if(isset($_GET['mf']) && $_GET['mf'] == 'yes')
            $GLOBALS['mf'] = 'yes';

        //setting up quests
        $GLOBALS['Events'] = new Events();

        // Get training setup from the POST variable
        $this->trainSetup = $this->decodeSetup( $setup );

        // Setup the training, with a tokenCheck.
        $this->setupTrainingSystem(
            array_merge(
                $this->trainSetup,
                array(
                    "tokenCheck" => $token ,
                    "originalSetup" => $this->trainSetup
                )
            )
        );

        $GLOBALS['Events']->closeEvents();
    }

    // Get display
    public function getDisplay(){

        // Return main content
        $contentPage = ".".$GLOBALS['template']->tpl_vars['contentLoad']->value;

        if(isset($GLOBALS['mf']) && $GLOBALS['mf'] == 'yes')
                $contentPage = str_replace('.tpl','_mf.tpl',$contentPage);

        $returnData['mainContent'] = $GLOBALS['template']->fetch( $contentPage );

        // Return new user information
        if( isset( $this->backendUpdateArray ) ){
            $returnData['userInfo'] = $this->backendUpdateArray;
        }

        // Return to echo
        return $returnData;
    }
}