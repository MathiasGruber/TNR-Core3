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
require_once(Data::$absSvrPath."/ajaxLibs/staticLib/markitup.bbcode-parser.php");

class globalTexts {
    public function __construct(){
        
        try{
            
            // Page name to show
            $this->page = "";

            // Get page name of ID
            switch( $_GET['id'] ){
                case 15: $this->page = "Rules"; break;
                case 7: $this->page = "Terms of Service"; break;
                case 75: $this->page = "About"; break;
                case 76: $this->page = "Event History"; break;
            }

            // Get the page
            $page = $GLOBALS['database']->fetch_data("SELECT * FROM `information_pages` WHERE `name` = '".$this->page."'");
            if( $page !== "0 rows" ){

                // Format the message
                $message = BBCode2Html($page[0]['content']);
                $lastUpdate = functions::convert_PM_time($GLOBALS['user']->load_time - $page[0]['time']);
                
                // Smarty call
                $GLOBALS['template']->assign('pageTitle', $page[0]['name']);
                $GLOBALS['template']->assign('pageContent', $message);
                $GLOBALS['template']->assign('pageupdated', $lastUpdate);
                $GLOBALS['template']->assign('contentLoad', './templates/content/globalTexts/main.tpl');

                
            }
            else{
                throw new Exception("Could not find this page in the database");
            }
            
            
        } catch (Exception $e) {
            $GLOBALS['page']->Message( $e->getMessage() , "Global Text Manager", 'id='.$_GET['id'],'Return');
        }
    }
}

new globalTexts();