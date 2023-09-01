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

error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once($_SERVER['DOCUMENT_ROOT'].'/global_libs/Site/data.class.php');
require_once(Data::$absSvrPath.'/panel_event/global_libs/page.class.php');
require_once(Data::$absSvrPath.'/global_libs/User/cachefunctions.inc.php'); 
require_once(Data::$absSvrPath.'/global_libs/Site/error.class.php');
require_once(Data::$absSvrPath.'/global_libs/Site/database.class.php');
require_once(Data::$absSvrPath.'/global_libs/General/static.inc.php');
require_once(Data::$absSvrPath.'/global_libs/General/tableparser.inc.php');
require_once(Data::$absSvrPath.'/global_libs/User/user.class.php');
require_once(Data::$absSvrPath.'/vendor/autoload.php');


class eventPanel {

    public function __construct() {

        // Start session 
        session_start();

        // Check if user is logged in
        if (isset($_SESSION['uid']) && !empty($_SESSION['uid'])) {

            //    Include library classes:
            $GLOBALS['page'] = new page;
            $GLOBALS['error'] = new error;
            $GLOBALS['database'] = new database;
            $GLOBALS['user'] = new user();
            
            // Hook up with Memcache server  
            cachefunctions::hook_up_memcache();
            
            // Set the load time
            $GLOBALS['user']->setLoadTime();

            // Get the user
            $GLOBALS['userdata'] = $GLOBALS['database']->load_user($_SESSION['uid']);
            
            // Check hash
            if( sha1($GLOBALS['userdata'][0]["username"]."secretKey746HSk29") == $_SESSION['hash'] ){
                
                if (
                       ($GLOBALS['userdata'][0]['user_rank'] == 'Admin' ||
                        $GLOBALS['userdata'][0]['user_rank'] == 'Event' ||
                        $GLOBALS['userdata'][0]['user_rank'] == 'EventMod' ||
                        $GLOBALS['userdata'][0]['user_rank'] == 'ContentAdmin') && $GLOBALS['userdata'][0]['baby_mode'] == 'no'
                ) {
                    // Check if passed IP Check
                    if (isset($_POST['lgn_usr_stpd']) && $_POST['lgn_usr_stpd'] != '') {
                        if (!isset($_SESSION['eventid'])) {
                            $newPass = functions::encryptPassword( $_POST['login_password'], $GLOBALS['userdata'][0]['join_date'] );
                            $logindata = $GLOBALS['database']->fetch_data("
                                SELECT `id`, `username`,`password`,`user_rank` 
                                FROM `users`, `users_statistics` 
                                WHERE 
                                    `users`.`id` = `users_statistics`.`uid` AND 
                                    `users`.`username` = '" . $_POST['lgn_usr_stpd'] . "' AND 
                                    `users`.`salted_password` = '" . $newPass . "' AND 
                                    (`users_statistics`.`user_rank` = 'Admin' OR  
                                     `users_statistics`.`user_rank` = 'Event' OR 
                                     `users_statistics`.`user_rank` = 'EventMod' OR 
                                     `users_statistics`.`user_rank` = 'ContentAdmin') AND 
                                    `users`.`id` = '".$_SESSION['uid']."' LIMIT 1");
                            if ($logindata !== "0 rows") {
                                $_SESSION['eventid'] = $logindata[0]['id'];
                            }
                        }
                    }

                    // Launch Smarty
                    $GLOBALS['template'] = new Smarty;
                    $GLOBALS['template']->setCompileDir('../templates_c');
                    $GLOBALS['template']->error_reporting = error_reporting() & ~E_NOTICE;
                    $GLOBALS['template']->assign("absPath", Data::$absSvrPath);
                    $GLOBALS['template']->assign("YEAR", date("Y"));
                    $GLOBALS['template']->assign("memory", round(memory_get_peak_usage(true) / 1048576, 2) . "MB");
                    $GLOBALS['template']->assign('user_rank', $GLOBALS['userdata'][0]['user_rank']);

                    // Secondary Login
                    if (isset($_SESSION['eventid'])) {
                        
                        // Admin exist
                        if ($GLOBALS['userdata'] !== "0 rows") {
                            
                            //	Load all modules:
                            $GLOBALS['page']->load_modules();
                            $GLOBALS['page']->load_content();
                            $GLOBALS['page']->load_menu();
                            
                        } else {
                            header("Location:../?id=1");
                        }
                    } else {
                        $GLOBALS['template']->assign('contentLoad', './panel_admin/files/login.tpl');
                    }

                    //    Parse the layout file:
                    $GLOBALS['page']->parse_layout();
                    
                } else {
                    header("Location:../?id=1");
                }
            }
            else{
                header("Location:../?id=1");
            }
        } else {
            header("Location:../?id=1");
        }
    }

}

new eventPanel();