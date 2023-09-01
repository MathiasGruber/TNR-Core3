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

    // Start session

    ini_set('session.hash_function', 'sha256'); // Hashing Algorithm for Session Generation
    ini_set('session.cookie_lifetime', '0'); // Session Cookie Lifetime until Browser Closes (Or Refresh to have Logout and Session Destroyed)
    ini_set('session.cookie_httponly', '1'); // Cookie Access Through HTTP Only
    ini_set('session.gc_maxlifetime', '7200'); // Session Lifetime
    ini_set('session.gc_probability', '1'); // Numerator for Session Garbage Collector
    ini_set('session.gc_divisor', '100'); // Denominators for Session Garbage Collects
    session_start();
    // ini_set('memory_limit', '32M'); // PHP Local Memory Limit Value
    ini_set('memcache.chunk_size', '32768'); // Memcache Data Chunk Size

    // Limit execution time to 1 second - nothing should last longer
    ini_set('max_execution_time', '1');
    set_time_limit(1);

    // Change Dir
    chdir( "../" );

    // Page generation
    $mtime = explode(" ", microtime());
    $GLOBALS['starttime'] = $mtime[1] + $mtime[0];

    // Set data & get absolute path
    require_once('./global_libs/Site/data.class.php');
    $GLOBALS['serverPath'] = Data::$absSvrPath;

    // Just for development, let's see all errors
    switch (Data::$target_site) {
        case 'TND_':
            error_reporting(E_ALL ^ E_DEPRECATED);
            ini_set('display_errors', 1);
        break;
        default:
            error_reporting(E_ERROR || E_WARNING);
        break;
    }

    // Include libraries
    require_once($GLOBALS['serverPath'].'/global_libs/Site/database.class.php');
    require_once($GLOBALS['serverPath'].'/global_libs/User/cachefunctions.inc.php');
    require_once($GLOBALS['serverPath'].'/global_libs/General/static.inc.php');
    require_once($GLOBALS['serverPath'].'/global_libs/Site/page.class.php');
    require_once($GLOBALS['serverPath'].'/global_libs/User/user.class.php');
    require_once($GLOBALS['serverPath'].'/global_libs/General/tableparser.inc.php');
    require_once($GLOBALS['serverPath'].'/global_libs/Site/menu.class.php');
    require_once($GLOBALS['serverPath'].'/vendor/autoload.php');

    // Instantiate
    $GLOBALS['template'] = new Smarty;    
    $GLOBALS['template']->error_reporting = error_reporting() & ~E_NOTICE;
    $GLOBALS['page'] = new page;
    $GLOBALS['user'] = new user;
    $GLOBALS['menu'] = new menu;

     // Get IDS library
    require_once($GLOBALS['serverPath'].'/global_libs/IDS/Init.php');
    set_include_path(get_include_path() . PATH_SEPARATOR . './global_libs/');
    
    // Initiate IDS
    $request = array(
        'REQUEST' => $_REQUEST,
        'GET' => $_GET,
        'POST' => $_POST,
        'COOKIE' => $_COOKIE
    );

    $init = IDS_Init::init($GLOBALS['serverPath'].'/global_libs/IDS/Config/Config.ini.php');
    $ids = new IDS_Monitor($request, $init);

    // Change absolute path directory so all includes work
    $GLOBALS['template']->assign("absPath", $GLOBALS['serverPath']);

    // Setup class for handling stuff
    class Backend {

        // Backend Variables
        private $backend;

        // Constructor
        public function loadBackend() {

            // Try-Catch
            try {

                functions::checkActiveSession();

                // Get a lock
                //$GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

                // Load the different backends
                if($_REQUEST['backend'] === "PMbackend") {
                    require_once($GLOBALS['serverPath'].'/ajaxLibs/backendLibs/pmBackend.php');
                    $this->backend = new PMbackend();
                    $this->regenerateMainMenu();
                }
                elseif($_REQUEST['backend'] === "TravelBackend") {
                    require_once($GLOBALS['serverPath'].'/ajaxLibs/backendLibs/travelBackend.php');
                    $this->backend = new travelBackend();
                    self::regenerateMainMenu();
                    self::updateSleepLink();
                    self::regeneratePageData(array('30','49'));
                }
                elseif($_REQUEST['backend'] === "ShopBackend") {
                    require_once($GLOBALS['serverPath'].'/ajaxLibs/backendLibs/ItemShopBackend.php');
                    self::pageChecker();
                    $this->backend = new shopBackend( stripslashes($_REQUEST['setupData']), $_REQUEST['shopToken']);
                }
                elseif( $_REQUEST['backend'] === "ChatBackend") {
                    require_once($GLOBALS['serverPath'].'/ajaxLibs/backendLibs/chatBackend.php');
                    self::pageChecker();
                    $this->backend = new chatBackend(stripslashes($_REQUEST['setupData']), $_REQUEST['chatToken']);
                }
                elseif($_REQUEST['backend'] === "trainingBackend") {
                    require_once($GLOBALS['serverPath'].'/ajaxLibs/backendLibs/trainingBackend.php');
                    self::pageChecker();
                    $this->backend = new trainBackend(stripslashes($_REQUEST['setupData']), $_REQUEST['trainToken']);
                }
                elseif($_REQUEST['backend'] === "profileBackend") {
                    require_once($GLOBALS['serverPath'].'/ajaxLibs/backendLibs/profileBackend.php');
                    self::pageChecker();
                    $this->backend = new profileBackend();
                }
                elseif($_REQUEST['backend'] === "combatBackend") {
                    $_GET['id'] = 41;
                    require_once($GLOBALS['serverPath'].'/ajaxLibs/backendLibs/combatBackend.php');
                    self::pageChecker();
                    $this->backend = new battleBackend();

                    // Update menu on summary page
                    if(isset($this->backend->stage) && (int)$this->backend->stage === 4) {
                        self::regenerateMainMenu();
                        self::updateSleepLink();
                    }
                }
                else {
                    throw new Exception("Attempted to send data to unknown backend");
                }

                // Release the lock again
                //if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                //    throw new Exception('There was an issue releasing the lock!');
                //}
            }
            catch (Exception $e) {
                $GLOBALS['page']->Message($e->getMessage(), "Backend Error", 'id='.$_REQUEST['id'], 'Return');
            }
        }

        // Function for regenerating the widget menu
        private function regenerateMainMenu() {
            $GLOBALS['page']->loadURIs($GLOBALS['serverPath']);
            $GLOBALS['menu']->construct_menu($GLOBALS['userdata'], $GLOBALS['userdata'][0]['alliance']);
            $this->backend->returnData["menuHtml"] = $GLOBALS['template']->fetch($GLOBALS['serverPath'].'/files/layout_'.$GLOBALS['layout'].'/mainMenu.tpl');
        }

        // Update the sleep link
        private function updateSleepLink() {
            $this->backend->returnData['sleepLink'] = ucfirst($GLOBALS['userdata'][0]['status']);
            if( isset($GLOBALS['menu']->sleepLink) && !empty($GLOBALS['menu']->sleepLink) ){
                $this->backend->returnData['sleepLink'] .= " (".$GLOBALS['menu']->sleepLink.")";
            }
        }

        // Regenerate page data
        private function regeneratePageData($pages) {

            // Redo the page
            if(in_array($_GET['id'], $pages, true)){

                // Load the new page and attach to return array
                $GLOBALS['page']->load_content($GLOBALS['userdata']);
                $contentPage = ".".$GLOBALS['template']->tpl_vars['contentLoad']->value;

                // Remove popup message
                unset($this->backend->returnData['popupMessage']);

                // Set on return data
                $this->backend->returnData['pageHtml'] = $GLOBALS['template']->fetch($contentPage);
            }
        }

        // Check if page can be loaded
        private function pageChecker() {
            // Get the page based on page ID, and then check it
            $page_data = cachefunctions::getPage($_REQUEST['id']);
            $pageCheck = $GLOBALS['page']->checkPage($page_data[0] , $GLOBALS['userdata'][0]['rank_id']);
            if($pageCheck !== true) {
                $GLOBALS['database']->close_connection();
                die(json_encode(array("popupMessage" => array("txt" => $pageCheck))));
            }
            return true;
        }

        // Get display from backend library
        public function getDisplay() {
            if( is_object($this->backend) ){
                return $this->backend->getDisplay();
            }
            else{
                $contentPage = ".".$GLOBALS['template']->tpl_vars['contentLoad']->value;
                return array('pageHtml' => $GLOBALS['template']->fetch( $contentPage ) );
            }
        }
    }

    // Run IDS
    $result = $ids->run();
    $impact = $result->getImpact();

    // Check impact
    if ($impact > 5) {

        // Set message
        $GLOBALS['page']->Message("Suspicious data sent to server. Your information has been logged and admins notified. Security threat: ".
            $impact."Info on suspicious data: ".$result, "Backend Error", false);

        // Return to user
        $contentPage = ".".$GLOBALS['template']->tpl_vars['contentLoad']->value;
        echo json_encode( array('pageHtml' => $GLOBALS['template']->fetch( $contentPage ) ) );
        
        // Stop further execution
        die();
    }

    // Interact using jQuery. For all interactions, require a token, a user ID, and a page ID
    if(!isset($_REQUEST['token'], $_REQUEST['uid'], $_REQUEST['id'], $_REQUEST['backend'])) {
        functions::removeSessionData();
        die("Sent data does not make sense");
    }

    // Check Session Exists and Matches
    if(
            !(isset($_SESSION['uid']) &&
              !empty($_SESSION['uid']) &&
              ctype_digit($_SESSION['uid']) &&
              $_SESSION['uid'] === $_REQUEST['uid']
            )
    ) {
        functions::removeSessionData();
        die("Session no longer valid. You may have been logged out.");
    }

    // Prepare output type
    @header('Content-type: application/json');

    // Start database & cache now
    cachefunctions::hook_up_memcache();
    $GLOBALS['database'] = new database;
    
    // Get global events
    $GLOBALS['globalevents'] = cachefunctions::getAllGlobalEvents();

    // Get & Update Userdata
    $GLOBALS['userdata'] = $GLOBALS['database']->load_user($_SESSION['uid']);

    // Check the token
    if($_REQUEST['token'] !== functions::getToken()) {
        $GLOBALS['database']->close_connection();
        functions::removeSessionData();
        $_SESSION = array();
        die("Backend token has been corrupted");
    }

    // Update user information (regen etc.)
    $GLOBALS['user']->launch();

    // Send to page
    $GLOBALS['page']->setCurrentDetails($GLOBALS['userdata']);

    // Check pageloads
    if( !$GLOBALS['page']->checkPageLoads() ){
        die("You can't view more than ".Data::$pageLoadLimit." pages / second. Please try again.");
    }

    // Instantiate the backend class
    $myPage = new Backend();
    $myPage->loadBackend();

    // The pagetime / store in DB
    $totaltime = functions::logPageTime();

    // Close database connection
    $GLOBALS['database']->close_connection();

    // Only return all this if headers have not been sent already during script execution
    if(headers_sent()) { //if headers already sent out print some message.
        die("\n\nNote to user: Above errors were registered. Please report these in the forum, ".
            "along with information on how/why they occured, and any information that can be used to reproduce the error. ".
            "Please make sure to check if someone else already reported this before you.");
    }

    // Return Data If Successful
    echo json_encode($myPage->getDisplay());  