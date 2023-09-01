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
class ohShit{
    
    public function __construct(){
        try{
            if(!isset($_GET['act'])){
                $this->main_screen();
            }
            elseif($_GET['act'] == 'logout'){
                if(!isset($_POST['Submit'])){
                    $this->confirm_logout();   
                }
                else{
                    $this->do_force();
                }
            }
            elseif(
                $_GET['act'] == 'loginSwitch' ||
                $_GET['act'] == 'tavernSwitch' ||
                $_GET['act'] == 'pmsSwitch' ||
                $_GET['act'] == 'warsSwitch' 
            ){
                if( !isset($_POST['Submit']) ){
                    $this->confirm_switchChange();   
                }
                else{
                    $this->do_switchChange();
                }
            } 
        } catch (Exception $ex) {
            $GLOBALS['page']->Message($ex->getMessage(), 'Oh Shit System', 'id='.$_GET['id']); 
        }                          
    }
    
    // Main screen
    private function main_screen(){  
        $menu = array(
            array( "name" => "Massive Force Logout", "href" => "?id=".$_GET['id']."&act=logout"),
            array( "name" => "Disable / Enable Login", "href" => "?id=".$_GET['id']."&act=loginSwitch"),
            array( "name" => "Tavern On / Off", "href" => "?id=".$_GET['id']."&act=tavernSwitch"),
            array( "name" => "PMs On / Off", "href" => "?id=".$_GET['id']."&act=pmsSwitch"),
            array( "name" => "Wars On / Off", "href" => "?id=".$_GET['id']."&act=warsSwitch")
        );
        $GLOBALS['template']->assign('subHeader', 'Oh Shit Admin');
        $GLOBALS['template']->assign('nCols', 3);
        $GLOBALS['template']->assign('nRows', 2);
        $GLOBALS['template']->assign('subTitle', 'Only for emergency situations!.');
        $GLOBALS['template']->assign('linkMenu', $menu);
        $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');  
    }
    
    // Force logouts
    private function confirm_logout(){
        $GLOBALS['page']->Confirm("Confirm Force Logout of all users!?", 'Oh Shit System', 'Force Logout!'); 
    }
    
    private function do_force(){
        $GLOBALS['page']->Message("All users have been logged out.", 'Oh Shit System', 'id='.$_GET['id']); 
		$forcer = "UPDATE `users` SET `logout_timer` = `logout_timer` - 3600 WHERE `logout_timer` > 0";
		$GLOBALS['database']->execute_query($forcer);
    }

    // Confirm Enable / disable switches
    private function confirm_switchChange(){  
        $script = $this->getCurrentScript();        
        $current = $GLOBALS['database']->fetch_data("SELECT * FROM `site_timer` WHERE `script` = '".$script."'");
        if( $current[0]['character_cleanup'] == 1 ){
            $GLOBALS['page']->Confirm("Disable for all users!?", 'Oh Shit System', 'Disable!');       
        }
        else{
            $GLOBALS['page']->Confirm("Enable for all users!?", 'Oh Shit System', 'Enable!');       
        }
    }
    
    // Do Enable / disable switches
    private function do_switchChange(){
        $script = $this->getCurrentScript();        
        $current = $GLOBALS['database']->fetch_data("SELECT * FROM `site_timer` WHERE `script` = '".$script."'");
        if( $current[0]['character_cleanup'] == 1 ){
            $GLOBALS['page']->Message("Feature has been disabled for all users.", 'Oh Shit System', 'id='.$_GET['id']); 
            $GLOBALS['database']->execute_query("UPDATE `site_timer` SET `character_cleanup` = 0 WHERE `script` = '".$script."'");            
        }
        else{
            $GLOBALS['page']->Message("Feature has been enabled for all users.", 'Oh Shit System', 'id='.$_GET['id']); 
            $GLOBALS['database']->execute_query("UPDATE `site_timer` SET `character_cleanup` = 1 WHERE `script` = '".$script."'");
        }		
    }
    
    // Get the current script
    private function getCurrentScript(){
        if( isset($_GET['act']) ){
            switch( $_GET['act'] ){
                case "loginSwitch": return "login_script"; break;
                case "tavernSwitch": return "tavernSwitch"; break;
                case "pmsSwitch": return "pmsSwitch"; break;
                case "warsSwitch": return "warsSwitch"; break;
                default: throw new Exception("Could not determine which script to switch.");
            }
        }
        else{
            throw new Exception("No script was selected");
        }
        
    }

}
new ohShit();