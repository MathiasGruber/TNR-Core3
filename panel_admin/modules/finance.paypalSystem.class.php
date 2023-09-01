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
class paypalSystem{
    
    public function __construct(){
        if(!isset($_GET['act'])){
            $this->main_screen();
        }
        elseif($_GET['act'] == 'enable'){
            if(!isset($_POST['Submit'])){
                $this->confirm_dialog();   
            }
            else{
                $this->do_switch();
            }
        }
        elseif($_GET['act'] == 'disable'){
            if( !isset($_POST['Submit']) ){
                $this->confirm_dialog();   
            }
            else{
                $this->do_switch();
            }
        }            
    }
    
    private function main_screen(){  
        $menu = array(
            array( "name" => "Enabled Paypal Payments", "href" => "?id=".$_GET['id']."&act=enable"),
            array( "name" => "Disable Paypal Payments", "href" => "?id=".$_GET['id']."&act=disable")           
        );
        $GLOBALS['template']->assign('subHeader', 'Enabled / Disable Paypal system');
        $GLOBALS['template']->assign('nCols', 2);
        $GLOBALS['template']->assign('nRows', 1);
        $GLOBALS['template']->assign('subTitle', "Don't disable without good reason. Still allows current transactions to go through.");
        $GLOBALS['template']->assign('linkMenu', $menu);
        $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');  
    }
    
    private function confirm_dialog(){  
        $current = $GLOBALS['database']->fetch_data("SELECT * FROM `site_timer` WHERE `script` = 'paypalPayments'");
        if( $current[0]['character_cleanup'] == "enabled" ){
            $GLOBALS['page']->Confirm("Disable the paypal system!?", 'Paypal System', 'Disable!');   
        }
        else{
            $GLOBALS['page']->Confirm("Enable the paypal system", 'Paypal System', 'Enable!');   
        }
    }
    
    private function do_switch(){
        $current = $GLOBALS['database']->fetch_data("SELECT * FROM `site_timer` WHERE `script` = 'paypalPayments'");
        if( $current[0]['character_cleanup'] == "enabled" ){
            $GLOBALS['page']->Message("System has been disabled", 'Paypal System', 'id='.$_GET['id']); 
            $GLOBALS['database']->execute_query("UPDATE `site_timer` SET `character_cleanup` = 'disabled' WHERE `script` = 'paypalPayments'");            
        }
        else{
            $GLOBALS['page']->Message("System has been enabled", 'Paypal System', 'id='.$_GET['id']); 
            $GLOBALS['database']->execute_query("UPDATE `site_timer` SET `character_cleanup` = 'enabled' WHERE `script` = 'paypalPayments'");
        }		
    }

}
new paypalSystem();