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

class notes{
	
	public function __construct(){
        $this->main_page();        
	}   

	private function main_page(){
        ob_start(); 
        phpinfo(); 
        $info = ob_get_contents(); 
        ob_end_clean(); 
        $GLOBALS['page']->Message($info, 'Info System', 'id='.$_GET['id']); 
	}   
}

new notes();
