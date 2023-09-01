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

// Show All Error Reports
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Essential Files For Execution
require_once($_SERVER['DOCUMENT_ROOT'].'/global_libs/Site/data.class.php');
require_once(Data::$absSvrPath.'/panel_admin/global_libs/page.class.php');
require_once(Data::$absSvrPath.'/global_libs/User/cachefunctions.inc.php');
require_once(Data::$absSvrPath.'/global_libs/Site/error.class.php');
require_once(Data::$absSvrPath.'/global_libs/Site/database.class.php');
require_once(Data::$absSvrPath.'/global_libs/General/static.inc.php');
require_once(Data::$absSvrPath.'/global_libs/General/tableparser.inc.php');
require_once(Data::$absSvrPath.'/global_libs/User/user.class.php');
require_once(Data::$absSvrPath.'/vendor/autoload.php');


class admin {

    function __construct() {

        try {
            // Start Session
            session_start();

            // Check If User Logged In
            if (!isset($_SESSION['uid']) || empty($_SESSION['uid'])) {
                throw new Exception('NonAdmin');
            }
            
            // Load Library Classes
            $GLOBALS['page'] = new page;
            $GLOBALS['error'] = new error;
            $GLOBALS['database'] = new database;
            $GLOBALS['user'] = new user();

            // Hook Up Memcache Server
            cachefunctions::hook_up_memcache();

            // Set user load time
            $GLOBALS['user']->setLoadTime();

            // Load User Data (Debatable Since It Already Ran...)
            if(!($GLOBALS['userdata'] = $GLOBALS['database']->load_user($_SESSION['uid']))) {
                throw new Exception('Failed to Load User...Please try again!');
            }

            // Check User Hash
            if(sha1($GLOBALS['userdata'][0]["username"]."secretKey746HSk29") !== $_SESSION['hash']) {
                throw new Exception('NonAdmin');
            }

            // Check Accepted Ranks
            if (!in_array($GLOBALS['userdata'][0]['user_rank'], array('Admin', 'ContentAdmin'), true)) {
                throw new Exception('NonAdmin');
            }

            // IP Check
            if(!($data = $GLOBALS['database']->fetch_data("SELECT `site_information`.`value` FROM `site_information`
                WHERE `site_information`.`option` = 'admin_ips' LIMIT 1"))) {
                throw new Exception('Failed to Load Admin IPs!');
            }

            $admin = false; // Admin Check
            $entries = explode(";", $data[0]['value']); // IP Address Values of Admins

            // Search Through All Accepted Admin IPs For Admin
            
            foreach ($entries as $entry) {
                $adminip = explode(",", $entry);

                // If IP Match Found, Pass IP Check
                if (isset($adminip[1]) && strstr( $GLOBALS['user']->real_ip_address() , $adminip[1])) {
                    $admin = true;
                    break;
                }
            }

            // Check Admin IP Check Variable
            if ($admin !== true && ENV !== 'local') {
                throw new Exception('NonAdmin');
            }
            
            // Check Username Login
            if (isset($_POST['lgn_usr_stpd']) && functions::ws_remove($_POST['lgn_usr_stpd']) !== '') {

                // Check If Admin ID Assigned
                if (!isset($_SESSION['adminid'])) {
                    $newPass = functions::encryptPassword($_POST['login_password'], $GLOBALS['userdata'][0]['join_date']);

                    // Obtain Admin Information
                    if(!($logindata = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username`, `users`.`password`,
                        `users_statistics`.`user_rank`
                        FROM `users`
                            INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`
                                AND `users_statistics`.`user_rank` IN('Admin', 'ContentAdmin'))
                        WHERE `users`.`id` = ".$_SESSION['uid']." AND `users`.`username` = '".$_POST['lgn_usr_stpd']."'
                            AND `users`.`salted_password` = '".$newPass."' LIMIT 1"))) {
                        throw new Exception('Failed to Load Admin...Please try again!');
                    }

                    // If Admin Login Successful, Assign Admin ID
                    if ($logindata !== "0 rows") {
                        $_SESSION['adminid'] = $logindata[0]['id'];
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
            $GLOBALS['template']->assign('serverTime', time());
            echo '<script type="text/javascript" src="files/javascript/sorttable.js"></script>';

            // Secondary Login
            if (isset($_SESSION['adminid'])) {
                
                // Check If Admin Exists
                if ($GLOBALS['userdata'] === "0 rows") {
                    throw new Exception('NonAdmin');
                }
                
                // Kill If Accepted Users Not Acknowledged
                switch($GLOBALS['userdata'][0]['username']) {
                    case("Terriator"): case("Koala"): case("AlbaficaPisces"): case("Kiira"): break;
                    default: die("Hardcoded Lock. Die asshole!"); break;
                }

                // Load Necessary Modules
                $GLOBALS['page']->load_modules();
                $GLOBALS['page']->load_content();
                $GLOBALS['page']->load_menu();
            } 
            else { 
                // Load Admin Login Page
                $GLOBALS['template']->assign('contentLoad', './panel_admin/files/login.tpl');
            }

            // Parse Layout
            $GLOBALS['page']->parse_layout();
        }
        catch(Exception $e) { 
            // Destry Session Admin ID
            if(isset($_SESSION['adminid'])) {
                unset($_SESSION['adminid']);
            }
            
            // Handle Error Message
            switch($e->getMessage()) {
                case('NonAdmin'): header("Location:../?id=1"); break;
                default: $GLOBALS['page']->Message($e->getMessage(), 'User Login Error', 'id=1'); break;
            }
        }
    }
}

new admin;