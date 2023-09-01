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
class optimize {             
    
    public function __construct(){
        if(!isset($_POST['Submit'])){
            $this->purge_form();
        }
        else{
            $this->do_optimize();
        }                       
    }

    private function purge_form(){        
        $GLOBALS['page']->Confirm("Run 5min maintenance?", 'Maintenance System', 'Run Now!');         
    }
    
    private function do_optimize(){
        require_once(Data::$absSvrPath.'/clusterup/5min.php');
        
        $GLOBALS['page']->Message("5min maintenance has been ran.", 'Maintenance System', 'id='.$_GET['id']); 
    }
}
new optimize();