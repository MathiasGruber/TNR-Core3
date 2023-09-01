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

// Get libraries required for this backend
require_once(Data::$absSvrPath.'/libs/itemSystem/shopLib.inc.php');
require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');

// Set up class for handling stuff
class shopBackend extends shopLib{

    // Constructor
    public function __construct( $setup, $token = "N/A" ){
        $GLOBALS['Events'] = new Events();

        // Get tavern setup from the POST variable
        $this->shopSetup = $this->decodeSetup( $setup );

        // Setup the shop, with a tokenCheck.
        $this->setupShopSystem(
            array_merge(
                $this->shopSetup,
                array(
                    "tokenCheck" => $token ,
                    "originalSetup" => $this->shopSetup,
                    "dirCorrection" => "./"
                )
            )
        );
        $GLOBALS['Events']->closeEvents();
    }

    public function getDisplay(){
        $contentPage = ".".$GLOBALS['template']->tpl_vars['contentLoad']->value;
        $returnData['mainContent'] = $GLOBALS['template']->fetch( $contentPage );
        return $returnData;
    }
}