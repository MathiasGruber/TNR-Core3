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
require_once(Data::$absSvrPath.'/panel_content/global_libs/page.class.php');
require_once(Data::$absSvrPath.'/global_libs/User/cachefunctions.inc.php'); 
require_once(Data::$absSvrPath.'/global_libs/Site/error.class.php');
require_once(Data::$absSvrPath.'/global_libs/Site/database.class.php');
require_once(Data::$absSvrPath.'/global_libs/General/static.inc.php');
require_once(Data::$absSvrPath.'/global_libs/General/tableparser.inc.php');
require_once(Data::$absSvrPath.'/global_libs/User/user.class.php');
require_once(Data::$absSvrPath.'/vendor/autoload.php');


class contentPanel {

    public function __construct() {

        try {
        
            // Start session 
            session_start();

            // Check Session Hash
            if (!isset($_SESSION['hash']) || empty($_SESSION['hash'])) {
                throw new Exception('Session does not match anymore!');
            }

            functions::checkActiveSession();
            
            //    Include library classes:
            $GLOBALS['page'] = new page;
            $GLOBALS['error'] = new error;
            $GLOBALS['database'] = new database;
            $GLOBALS['user'] = new user();
            $GLOBALS['template'] = new Smarty;
            $GLOBALS['template']->setCompileDir('../templates_c');
            $GLOBALS['template']->error_reporting = error_reporting() & ~E_NOTICE;

            // Hook up with Memcache server  
            cachefunctions::hook_up_memcache();

            // Get the user
            if(!($GLOBALS['userdata'] = $GLOBALS['database']->load_user($_SESSION['uid']))) {
                throw new Exception('Error loading User Data!');
            }
            elseif($GLOBALS['userdata'] === '0 rows' || empty($GLOBALS['userdata'])) { // Check User Exists
                throw new Exception('User does not exist or not logged in!');
            }

            // Check For Appropriate User Ranks
            if (!in_array($GLOBALS['userdata'][0]['user_rank'], array('Admin', 'ContentAdmin', 'ContentMember'), true)) {
                throw new Exception('Invalid Game Staff Rank!');
            }

            // Check hash
            if(sha1($GLOBALS['userdata'][0]["username"]."secretKey746HSk29") !== $_SESSION['hash']) {
                throw new Exception('User Session Hash Invalid!');
            }
            
            // Launch Smarty
            $GLOBALS['template']->assign("absPath", Data::$absSvrPath);
            $GLOBALS['template']->assign("YEAR", date("Y"));
            $GLOBALS['template']->assign('serverTime', time());
            $GLOBALS['template']->assign("memory", round(memory_get_peak_usage(true) / 1048576, 2) . "MB");
            $GLOBALS['template']->assign('user_rank', $GLOBALS['userdata'][0]['user_rank']);
            
            //	Load all modules:
            $GLOBALS['page']->load_modules();
            $GLOBALS['page']->load_content();
            $GLOBALS['page']->load_menu();

            //    Parse the layout file:
            $GLOBALS['page']->parse_layout();

        }
        catch (Exception $e) {
            header("Location:../?id=1");
        } 
    }

}

new contentPanel();