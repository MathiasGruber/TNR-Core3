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

// Based on pm library
require_once($GLOBALS['serverPath'].'/libs/pmSystem/pmLib.inc.php');
require_once($GLOBALS['serverPath'].'/ajaxLibs/staticLib/markitup.bbcode-parser.php');
        
class PMbackend extends pmBasicFunctions { 
    
    // Variable containing the return data
    public $returnData = array();
    
    // Backend constructor
    public function __construct() {
        // Setup PM system
        ($_REQUEST['id'] === '3') ? $this->set_inbox_system() : $this->set_outbox_system();
    }   
    
    // Get display
    public function getDisplay(){
        
        // Return main content
        $contentPage = ".".$GLOBALS['template']->tpl_vars['contentLoad']->value;
        $this->returnData['mainContent'] = $GLOBALS['template']->fetch( $contentPage );
        
        // Return data
        return $this->returnData;
        
    }
}