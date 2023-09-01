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

class backup {

    public function __construct() {
        if (!isset($_GET['act'])) {
            $this->main_screen();   
        } 
        elseif ($_GET['act'] == 'doClear') {
            $this->do_clear();
        }               
    }

    private function main_screen() {        
        $GLOBALS['page']->Message( "Clear everything in the cache (use e.g. when things you put into the admin panel did not update)" , 'Cache System', 'id='.$_GET['id']."&act=doClear", "Clear now");       
    }
    
    private function do_clear(){
        cachefunctions::flushCache();
        $GLOBALS['page']->Message( "Cache has been cleaned" , 'Cache System', 'id='.$_GET['id']);       
    }    
}

new backup();