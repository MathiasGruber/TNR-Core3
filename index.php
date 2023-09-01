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

    ini_set('session.hash_function', 'sha256'); // Hashing Algorithm for Session Generation
    ini_set('session.cookie_lifetime', '0'); // Session Cookie Lifetime until Browser Closes (Or Refresh to have Logout and Session Destroyed)
    ini_set('session.cookie_httponly', '1'); // Cookie Access Through HTTP Only
    ini_set('session.gc_maxlifetime', '7200'); // Session Lifetime
    ini_set('session.gc_probability', '1'); // Numerator for Session Garbage Collector
    ini_set('session.gc_divisor', '100'); // Denominators for Session Garbage Collects
    // ini_set('memory_limit', '32M'); // PHP Local Memory Limit Value
    ini_set('memcache.chunk_size', '32768'); // Memcache Data Chunk Size

    // Limit execution time to 1 second - nothing should last longer
    if($_SERVER['SERVER_NAME'] != 'localhost')
    {
        ini_set('max_execution_time', '1');
        set_time_limit(1);
    }
    else
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }

    // These files are required by all
    require_once($_SERVER['DOCUMENT_ROOT'].'/global_libs/Site/data.class.php');
    require_once($_SERVER['DOCUMENT_ROOT'].'/libs/notificationSystem/notificationSystem.php');
    require_once(Data::$absSvrPath.'/global_libs/Quests/QuestContainer.php');
    require_once(Data::$absSvrPath.'/global_libs/Quests/QuestsControl.php');
    require_once(Data::$absSvrPath.'/global_libs/General/static.inc.php');
    require_once(Data::$absSvrPath.'/vendor/autoload.php');
    
    // Game Class
    class game {

        // Constructor
        public function __construct(){
            //force time zone
            date_default_timezone_set('America/Chicago');

            // Fix url
            if(substr($_SERVER['SERVER_NAME'],0,4) != "www." && $_SERVER['SERVER_NAME'] != 'localhost' && $_SERVER['SERVER_NAME'] != '174.78.248.216')
                header('Location: '. (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://').'www.'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);

            // Setup

            $this->loadDebugTool();
            $this->startPageTiming();
            $this->setErrorReporting(Data::$target_site);
            $this->setPlatform();
            $this->initiateSession();
            $this->loadPageClasses();

            if($_SERVER['SERVER_NAME'] != 'localhost' && $_SERVER['SERVER_NAME'] != '174.78.248.216')
                $this->loadIDS();
            else
                $this->IDSimpact = 0;

            $this->loadDatabase();            

            // Check IDS impact
            if ($this->IDSimpact <= 5) {

                // More setup
                $this->loadCache();
                $this->loadGameLibs();
                $siteInfo = $this->getSiteInfo();

                // Check site status
                if( $siteInfo[0]['value'] == "1"){

                    // Load user specific libs
                    $this->loadUserMenu();

                    if(isset($_SESSION['uid']))
                    {
                        $this->checkForMovementAndStartQuests();
                        $this->checkForSleepAndWake();
                    }

                    $this->connectWithS3();
                    $this->loadGlobalEvents();
                    $this->setLogoutTime();
                    $this->loadAds();
                    $this->loadPageData();

                    $this->setSmartyVars();                    

                    // User-based loading
                    if (isset($_SESSION['uid'])) {
                        $this->chechApprovedAdmins();

                        $this->setUserInboxNotifications();
                        
                        //recording previous page load
                        if(isset($_GET['id']))
                        {
                            if(!isset($_SESSION['previous_page_id']) || $_SESSION['previous_page_id'] != $_GET['id'])
                            {
                                if(isset($_SESSION['previous_page_id']))
                                    $GLOBALS['Events']->acceptEvent('page', array('new'=>$_GET['id'], 'old'=>$_SESSION['previous_page_id'] ));
                                else
                                    $GLOBALS['Events']->acceptEvent('page', array('new'=>$_GET['id'], 'old'=>0 ));
                            }

                            $_SESSION['previous_page_id'] = $_GET['id'];
                        }

                        $GLOBALS['Events']->closeEvents();

                        $this->loadMenu();
                        if(isset($GLOBALS['QuestsControl']->QuestsData))
                        {
                            $GLOBALS['QuestsControl']->QuestsData->updateCacheDo();
                        }

                        $this->showNotifications();
                    }
                    else{
                        $this->setToplistToLayout();
                    }

                    //update user data before layout parsing.
                    if(isset($GLOBALS['userdata'][0]))
                        $GLOBALS['template']->assign("userdata", $GLOBALS['userdata'][0]);

                    // Logging
                    $this->logLargeObjects();
                    $this->logLargeSessions();
                    $this->logMobileErrors();

                    // Finalize
                    $this->endPageTiming();
                    $this->endDatabaseConnection();
                    $this->parseLayout();
                    $this->popAllDebugTool();
                }
                else {
                    $this->gameUnavailable($this->IDSimpact, $this->IDSresult, $this->IDSinit, $siteInfo[0]['value']);
                    $GLOBALS['database']->close_connection();
                }
            }
            else{
                $this->gameUnavailable($this->IDSimpact, $this->IDSresult, $this->IDSinit);
                $GLOBALS['database']->close_connection();
            }
        }

        private function showNotifications()
        {
            $GLOBALS['NOTIFICATIONS']->showNotifications();
            $GLOBALS['NOTIFICATIONS']->recordNotifications();
        }

        private function connectWithS3(){
            $GLOBALS['S3'] = new Aws\S3\S3Client([
                'region'  => 'us-west-2',
                'version' => 'latest',
                'credentials' => [
                    'key'    => MEDIA_AWS_KEY,
                    'secret' => MEDIA_AWS_SECRET,
                ]
            ]);            
            $GLOBALS['S3']->registerStreamWrapper();
        }

        private function checkForMovementAndStartQuests()
        {
            try
            {
                $GLOBALS['database']->get_lock("battle",$_SESSION['uid'],__METHOD__);


                if(!isset($GLOBALS['Events']))
                {
                    require_once(Data::$absSvrPath.'/global_libs/Travel/doTravel.php');
                    require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');

                    //need to be able to tell Hooks if we moved this turn and where from / where to
                    $GLOBALS['Events'] = new Events();
                }

                if(isset($_POST['doTravel']))
                {
                    $DoTravel = new DoTravel();
                    $DoTravel->startPostMove($_POST['doTravel']);
                    $GLOBALS['template']->assign("userdata", $GLOBALS['userdata'][0]);
                }

                if(isset($_POST['MapUpdate']) && $GLOBALS['userdata'][0]['user_rank'] == 'Admin')
                {
                    $this->handleMapUpdate();

                    if(!isset($GLOBALS['map_data']))
                    {
                        require_once(Data::$absSvrPath.'/global_libs/Travel/getMapData.php');
                        GetMapData::get();
                    }
                    $GLOBALS['template']->assign('map_data', $GLOBALS['map_data']);
                    $GLOBALS['template']->assign('map_region_data', $GLOBALS['map_region_data']);
                }

                if(isset($_POST['jump']) && $GLOBALS['userdata'][0]['user_rank'] == 'Admin')
                {
                    $this->handleJump();
                    if(!isset($GLOBALS['map_data']))
                    {
                        require_once(Data::$absSvrPath.'/global_libs/Travel/getMapData.php');
                        GetMapData::get();
                    }
                    $GLOBALS['template']->assign('map_data', $GLOBALS['map_data']);
                    $GLOBALS['template']->assign('map_region_data', $GLOBALS['map_region_data']);
                }


                $GLOBALS['database']->release_lock("battle",$_SESSION['uid'],__METHOD__);
            }
            catch (exception $e)
            {
                $GLOBALS['database']->release_lock("battle",$_SESSION['uid'],__METHOD__);
                throw $e;
            }
        }

        private function handleJump()
        {
            if($_POST['jumpVillage'] == 'nill' && is_numeric($_POST['jumpX']) && is_numeric($_POST['jumpY']))
                $query = 'UPDATE `users` SET `longitude` = '.$_POST['jumpX'].', `latitude` = '.$_POST['jumpY'].' WHERE `id` = '.$_SESSION['uid'];

            else if($_POST['jumpVillage'] != 'nill')
                $query = 'UPDATE `users` SET `longitude` = '.explode(',',$_POST['jumpVillage'])[0].', `latitude` = '.explode(',',$_POST['jumpVillage'])[1].' WHERE `id` = '.$_SESSION['uid'];

            if(isset($query))
            {
                $GLOBALS['database']->execute_query($query);
            }
        }

        private function handleMapUpdate()
        {
            $impassability = '';

            if( isset($_POST['impassability_impassable']) && $_POST['impassability_impassable'] == 'on')
            {
                $impassability = ",impassable";
            }
            else
            {
                if(isset($_POST['impassability_north']) && $_POST['impassability_north'] == 'on')
                    $impassability .= ",impassable_north";

                if(isset($_POST['impassability_south']) && $_POST['impassability_south'] == 'on')
                    $impassability .= ",impassable_south";

                if(isset($_POST['impassability_east']) && $_POST['impassability_east'] == 'on')
                    $impassability .= ",impassable_east";

                if(isset($_POST['impassability_west']) && $_POST['impassability_west'] == 'on')
                    $impassability .= ",impassable_west";
            }

            $_POST['name']   = str_replace("'","\'",$_POST['name']);
            $_POST['region'] = str_replace("'","\'",$_POST['region']);
            
            $query_square = "UPDATE `map_data` SET `data` = '{$_POST['name']},{$_POST['region']}{$impassability}' WHERE `x` = {$GLOBALS['userdata'][0]['longitude']} AND `y` = {$GLOBALS['userdata'][0]['latitude']}";

            $query_region = 'UPDATE `map_region_data` SET `claimable` = ';

            if(isset($_POST['claimable']) && $_POST['claimable'] == 'on')
                $query_region .= '1';
            else
                $query_region .= '0';

            $query_region .= ', `owner` = "'.$_POST['owner'].'" WHERE `region` = "'.$_POST['region'].'"';

            $GLOBALS['database']->execute_query($query_square);
            $GLOBALS['database']->execute_query($query_region);

            require_once(Data::$absSvrPath.'/global_libs/Travel/getMapData.php');
            GetMapData::get();
            $GLOBALS['template']->assign('map_data', $GLOBALS['map_data']);
            $GLOBALS['template']->assign('map_region_data', $GLOBALS['map_region_data']);
            $GLOBALS['template']->assign('status', $GLOBALS['userdata'][0]['status']);
        }

        private function checkForSleepAndWake()
        {
            try
            {
                $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);
                
                if(isset($_POST['doSleep']))
                {
                    require_once(Data::$absSvrPath.'/libs/villageSystem/sleepLib.php');

                    if($GLOBALS['userdata'][0]['village'] != 'Syndicate' && $GLOBALS['userdata'][0]['village'] == $GLOBALS['userdata'][0]['location'])
                        $apt = $GLOBALS['userdata'][0]['apartment'];
                    else
                        $apt = false;

                    $DoSleep = new sleepLibrary();
                    if($_POST['doSleep'] == 'sleep' && $GLOBALS['userdata'][0]['status'] == 'awake')
                        $DoSleep->sleep($apt);
                    else if($_POST['doSleep'] == 'wakeup' && $GLOBALS['userdata'][0]['status'] == 'asleep')
                        $DoSleep->wakeup($apt);
                }


                $GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__);
            }
            catch (exception $e)
            {
                $GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__);
                throw $e;
            }
        }

        private function loadDebugTool()
        {
            require_once(Data::$absSvrPath.'/tools/DebugTool.php');
            $GLOBALS['DebugTool'] = new DebugTool();
        }

        private function popAllDebugTool()
        {
            $GLOBALS['DebugTool']->popAll();
        }

        // Check admins against pre-approaved list
        private function chechApprovedAdmins(){
            if ($GLOBALS['userdata'][0]['user_rank'] === "Admin") {
                if(!in_array($GLOBALS['userdata'][0]['username'], array("Terriator","EvilTerr","Mathias","MathiasFelixGruber","Koala","AlbaficaPisces","Kiira")) ){
                    $GLOBALS['database']->close_connection();
                    echo "";
                    die();
                }
            }
        }

        // Set error reporting
        private function setErrorReporting( $targetSite ){
            switch ($targetSite) {
                case 'TNR_':
                    error_reporting(E_ERROR || E_WARNING);
                break;
                default:
                    error_reporting(E_ALL & ~E_WARNING );
                    ini_set('display_errors', 1);
                break;
            }
        }

        // Set logout time
        private function setLogoutTime(){
            $logout_time = 1;
            if(isset($GLOBALS['userdata'][0]['user_rank'])) {
                // Determine User Logout Time
                switch($GLOBALS['userdata'][0]['user_rank']) {
                    case('Moderator'):
                    case('Supermod'):
                    case('PRmanager'):
                    case('Admin'):
                    case('Event'):
                    case('EventMod'):
                    case('ContentAdmin'):
                        $logout_time = 7200; break;
                    case('Paid'): case('Tester'): case('ContentMember'): {
                        switch($GLOBALS['userdata'][0]['federal_level']) {
                            case "Gold":
                                $logout_time = 7200; break;
                            case "Normal":
                            case "Silver":
                                $logout_time = 5400; break;
                            default:
                                $logout_time = 3600; break;
                        }
                    } break;
                    case('Member'):
                        $logout_time = 3600; break;
                    default:
                        $logout_time = 1; break;
                }
            }
        }

        // Check if call is from mobile API
        private function setPlatform(){
            if( isset($_GET['apiCall']) && $_GET['apiCall'] == "true"){
                $computedHash = md5( $_GET['deviceID']."BUZZOFF"."32987bf!7s(dsaALSh/1".$_SERVER['REQUEST_URI']);
                if( !isset($_GET['deviceID'],$_POST['hash']) || $computedHash !== $_POST['hash']  ){
                    echo "NOT SECURE CONNECTION"; die();
                }
                if(isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID'])){
                    session_id($_GET['PHPSESSID']);
                }
                $GLOBALS['returnJson'] = true;
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Credentials: true");
                header("Access-Control-Allow-Headers: Accept, X-Access-Token, X-Application-Name, X-Request-Sent-Time");
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            }
        }

        // Set general smarty variables
        private function setSmartyVars(){
            $GLOBALS['template']->assign("YEAR", date("Y"));
            $GLOBALS['template']->assign("manualLink", "http://www.theninja-forum.com/index.php?/page/index.html");
            $GLOBALS['template']->assign("forumLink", "https://theninja-forum.com/");
            $GLOBALS['template']->assign("absPath", Data::$absSvrPath);
            $GLOBALS['template']->assign("domain", Data::$domainName);
            $GLOBALS['template']->assign("fbAppId", Data::$fbAppID);
            $GLOBALS['template']->assign("memory", round(memory_get_peak_usage(true) / 1048576, 2) . "MB");

            if(isset($_SESSION)){$GLOBALS['template']->assign("_SESSION", $_SESSION);}
            if(isset($_COOKIE)){$GLOBALS['template']->assign("_COOKIE", $_COOKIE);}
            if(isset($_POST)){$GLOBALS['template']->assign("_POST", $_POST);}
            if(isset($_GET)){$GLOBALS['template']->assign("_GET", $_GET);}

            if( functions::isAPIcall() ){
                $GLOBALS['template']->assign("sessionID", session_id());
                $GLOBALS['template']->assign("apiCall", true );
            }
        }

        // Set layout variables
        private function setLayoutVar(){

            $this->loadMobileDetect();

            if(isset($GLOBALS['userdata'][0]['layout']))
                $default = $GLOBALS['userdata'][0]['layout'];
            else if(isset($_COOKIE['layout']) && $_COOKIE['layout'] != 'core2')    
                $default = $_COOKIE['layout'];
            else
                $default = 'default';

            if( isset($GLOBALS['layout']) && in_array($GLOBALS['layout'], array('default'))) //if you update this also update the check in profile backend
            {
                $GLOBALS['layout'] = $default;
                $GLOBALS['template']->assign('mf','yes');
                $GLOBALS['mf'] = 'yes';
            }
            else
            {
                $GLOBALS['layout'] = ($GLOBALS['deviceType'] === "phone") ? "default" : $default;

                if(in_array($GLOBALS['layout'], array('default')))
                {
                    $GLOBALS['template']->assign('mf','yes');
                    $GLOBALS['mf'] = 'yes';
                }
                else
                {
                    $GLOBALS['template']->assign('mf','no');
                    $GLOBALS['mf'] = 'no';
                }
            }

            //setting
            if(isset($GLOBALS['userdata'][0]['theme']) && $GLOBALS['userdata'][0]['theme'] != '')
                $GLOBALS['theme'] = $GLOBALS['userdata'][0]['theme'];
            else if(isset($_COOKIE['theme']) && $_COOKIE['theme'] != '')
                $GLOBALS['theme'] = $_COOKIE['theme'];
            else
                $GLOBALS['theme'] = 'default';


            // If API call, only use API layout
            if( functions::isAPIcall() ){
                $GLOBALS['layout'] = "api";
            }

            // Ability to force light layout
            if( isset($_GET['forceLayout'])){
                $GLOBALS['layout'] = $_GET['forceLayout'];
                if( isset($GLOBALS['layout']) && in_array($GLOBALS['layout'], array('default')))
                {
                    $GLOBALS['template']->assign('mf','yes');
                    $GLOBALS['mf'] = 'yes';
                }
                else
                {
                    $GLOBALS['template']->assign('mf','no');
                    $GLOBALS['mf'] = 'no';
                }
            }

            if( isset($_GET['forceTheme']))
            {
                $GLOBALS['theme'] = $_GET['forceTheme'];
            }
        }

        // Set player toplist to layout
        private function setToplistToLayout(){
            $GLOBALS['template']->assign('topPlayers', cachefunctions::getTopPlayers());
        }

        // Update user about inbox notifications (should maybe be moved to menu.class)
        private function setUserInboxNotifications(){
            if (isset($GLOBALS['page']->inboxIsFull) && $GLOBALS['page']->inboxIsFull) {
                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array("?id=3",'Your inbox is full!')));
            }
        }

        // Get site information
        private function getSiteInfo(){
            $siteInfo = $GLOBALS['database']->fetch_data('
                SELECT `site_information`.`value`
                FROM `site_information`
                WHERE `site_information`.`option` = "site_status" LIMIT 1');
            if( !isset($siteInfo) || $siteInfo == "0 rows" ){
                $GLOBALS['database']->close_connection();
                die('Failed to locate Site Status!');
            }
            return $siteInfo;
        }

        // Initiate session
        private function initiateSession(){
            session_start();
        }

        // Load page class and smarty
        private function loadPageClasses(){
            
            require_once(Data::$absSvrPath.'/global_libs/Site/page.class.php');
            require_once(Data::$absSvrPath.'/global_libs/Site/ads.class.php');

            $GLOBALS['page'] = new page();
            $GLOBALS['template'] = new Smarty;

            $GLOBALS['template']->error_unassigned = true;
            $GLOBALS['template']->error_reporting = error_reporting() & ~E_NOTICE;
            $GLOBALS['template']->muteExpectedErrors();
            //$GLOBALS['template']->debugging = true;
        }

        // Load page data
        private function loadPageData()
        {

            if(!isset($_SESSION['previous_page_url']))
                $_SESSION['previous_page_url'] = array();

            if(!isset($_POST['step_back']))
            {
                if  (   
                        end($_SESSION['previous_page_url']) != $_SERVER['REQUEST_URI']
                        &&
                        !(isset($_POST['ajaxRequest']) || isset($_GET['ajaxRequest']))
                    )
                {
                    $_SESSION['previous_page_url'][] = $_SERVER['REQUEST_URI'];
                }
            }
            else if(count($_SESSION['previous_page_url']) > 0)
            {
                array_pop($_SESSION['previous_page_url']);
            }

            if(count($_SESSION['previous_page_url']) > 50)
                array_shift($_SESSION['previous_page_url']);

            $GLOBALS['page']->load_content($GLOBALS['userdata']);
        }

        // Load user menu
        private function loadMenu(){
            $GLOBALS['menu']->construct_menu($GLOBALS['userdata']);

            //if QuestsControl has not bet started do so....
            if(!isset($GLOBALS['QuestsControl']))
                $GLOBALS['QuestsControl'] = new QuestsControl();//temp

            //handling logic for quest widget
            if(isset($GLOBALS['QuestsControl']->QuestsData->quests))
            {
                $trackable_quests = array();
                foreach($GLOBALS['QuestsControl']->QuestsData->quests as $qid => $quest)
                {
                    if($quest->track === '1' && $GLOBALS['userdata'][0]['quest_widget'] == 'yes')
                    {

                        $quest = QuestsControl::getActions($qid, $quest);
                        $GLOBALS['template']->assign('tracked_quest', $quest);
                    }

                    if($quest->status != 3 && $quest->status != 4)
                        $trackable_quests[$qid]=$quest;
                }

                $GLOBALS['template']->assign('questing_mode', $GLOBALS['userdata'][0]['QuestingMode']);
                $GLOBALS['template']->assign('quest_widget', $GLOBALS['userdata'][0]['quest_widget']);
                $GLOBALS['template']->assign('trackable_quests', $trackable_quests);
            }

            $GLOBALS['template']->assign('menuLoad', 'files/layout_' . $GLOBALS['layout'] . '/mainMenu.tpl');
            $GLOBALS['template']->assign('widgetLoad', 'files/layout_' . $GLOBALS['layout'] . '/mainWidgets.tpl');
        }

        // Load IDS
        private function loadIDS(){
            require_once(Data::$absSvrPath.'/global_libs/IDS/Init.php');

            set_include_path(get_include_path() . PATH_SEPARATOR . './global_libs/');

            $request = array(
                'REQUEST' => $_REQUEST,
                'GET' => $_GET,
                'POST' => $_POST,
                'COOKIE' => $_COOKIE
            );

            $this->IDSinit = IDS_Init::init(Data::$absSvrPath.'/global_libs/IDS/Config/Config.ini.php');
            $this->IDS = new IDS_Monitor($request, $this->IDSinit);
            $this->IDSresult = $this->IDS->run();
            $this->IDSimpact = $this->IDSresult->getImpact();
        }

        // Load cachefunctions
        private function loadCache(){
            require_once(Data::$absSvrPath.'/global_libs/User/cachefunctions.inc.php');
            cachefunctions::hook_up_memcache();
        }

        // Load standard game libraries
        private function loadGameLibs(){
            require_once(Data::$absSvrPath.'/global_libs/Site/error.class.php');
            require_once(Data::$absSvrPath.'/global_libs/General/tableparser.inc.php');
            $GLOBALS['error'] = new tnr_error();
        }

        // Load database
        private function loadDatabase(){
            require_once(Data::$absSvrPath.'/global_libs/Site/database.class.php');
            $GLOBALS['database'] = new database();
        }

        // Load menu & User
        private function loadUserMenu(){
            require_once(Data::$absSvrPath.'/global_libs/Site/menu.class.php');
            require_once(Data::$absSvrPath.'/global_libs/User/user.class.php');

            $GLOBALS['menu'] = new menu();
            $GLOBALS['user'] = new user();
            $GLOBALS['userdata'] = (isset($_SESSION['uid']) && !empty($_SESSION['uid'])) ? $GLOBALS['database']->load_user($_SESSION['uid']) : array();

            if( isset($_GET['forceLayout'])){
                $GLOBALS['userdata'][0]['layout'] = $_GET['forceLayout'];
            }

            $this->setLayoutVar();

            if( (!isset($_COOKIE['layout']) && isset($GLOBALS['layout'])) || (isset($GLOBALS['layout']) && $_COOKIE['layout'] != $GLOBALS['layout']) )
            {
                setcookie("layout", $GLOBALS['layout'], time()+1209600);
                setcookie("mf", $GLOBALS['mf'], time()+1209600);
            }

            if( isset($GLOBALS['userdata'][0]['layout']) && isset($GLOBALS['userdata'][0]['theme']) && (!isset($_COOKIE['theme']) || $_COOKIE['theme'] == '' || $_COOKIE['theme'] != $GLOBALS['userdata'][0]['theme']) && $GLOBALS['userdata'][0]['layout'] != '' )
                setcookie("theme", $GLOBALS['userdata'][0]['theme'], time()+1209600);

			//if( (isset($_GET['submit_on_move']) && $_GET['submit_on_move'] == 'on') || (isset($_GET['x']) && isset($_GET['y']) && $GLOBALS['userdata'][0]['longitude'] == $_GET['x'] && $GLOBALS['userdata'][0]['latitude'] == $_GET['y']))
			//{
			//	if(!in_array($_GET['region'], array('uncharted','ocean','shore','river','lake','dead lake')))
			//		$string = $_GET['region'].'_'.$GLOBALS['userdata'][0]['longitude'].'_'.$GLOBALS['userdata'][0]['latitude'];
			//	else
			//		$string = $_GET['region'];
            //
            //	$string .= ','.$_GET['region'];
            //
            //  var_dump('MAP DATA HAS CHANGED AND THIS IS NO LONGER VALID');
			//	
			//	$query = "UPDATE `map_data` SET `".$GLOBALS['userdata'][0]['longitude']."` = '".$string."' WHERE `y` = ".$GLOBALS['userdata'][0]['latitude'];
            //
			//	$GLOBALS['database']->execute_query($query);
			//}

            //if(isset($_POST['data']))
            //{
            //    var_dump('MAP DATA HAS CHANGED AND THIS IS NO LONGER VALID');
            //    $query = "UPDATE `map_data` SET `".$GLOBALS['userdata'][0]['longitude']."` = '".$_POST['data']."' WHERE `y` = ".$GLOBALS['userdata'][0]['latitude'];
            //    $GLOBALS['database']->execute_query($query);
            //}

            if( (isset($_SESSION['uid']) && !empty($_SESSION['uid'])) && (!isset($GLOBALS['map_data']) || $GLOBALS['map_data'] == ''))
            {
                require_once(Data::$absSvrPath.'/global_libs/Travel/getMapData.php');
                GetMapData::get();
                $GLOBALS['template']->assign('map_data', $GLOBALS['map_data']);
                $GLOBALS['template']->assign('map_region_data', $GLOBALS['map_region_data']);
                $GLOBALS['template']->assign('status', $GLOBALS['userdata'][0]['status']);
            }
            else if(isset($GLOBALS['map_data']) && $GLOBALS['map_data'] != '')
            {
                $GLOBALS['template']->assign('map_data', $GLOBALS['map_data']);
                $GLOBALS['template']->assign('map_region_data', $GLOBALS['map_region_data']);
                $GLOBALS['template']->assign('status', $GLOBALS['userdata'][0]['status']);
            }

            if(!isset($GLOBALS['Events']) && isset($_SESSION['uid']))
            {
                require_once(Data::$absSvrPath.'/global_libs/Travel/doTravel.php');
                require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');

                //need to be able to tell Hooks if we moved this turn and where from / where to
                $GLOBALS['Events'] = new Events();
            }

            $GLOBALS['user']->launch();

            if (session_id() === '' || !isset($_SESSION['uid'])) { // If Session ID is Outdated or Logout Timer has been Achieved
                @session_start();
                @session_regenerate_id(true); // Create New Unique Session ID and destroy old Session file
            }
        }

        // Load mobile detection
        private function loadMobileDetect(){
            require_once(Data::$absSvrPath.'/libs/Mobile_Detect.php');
            $detect = new Mobile_Detect();
            $GLOBALS['deviceType'] = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
            $GLOBALS['template']->assign("deviceType", $GLOBALS['deviceType']);
        }

        // Load global events
        private function loadGlobalEvents(){
            $GLOBALS['globalevents'] = cachefunctions::getAllGlobalEvents();
        }

        // Load ads
        private function loadAds(){
            if( !functions::isAPIcall() ){
                $ads = new Ads();
                $GLOBALS['template']->assign('ADS', ((isset($_GET['id']) && intval($_GET['id']) === 72) ? '' : $ads->returnAd()));
            }
        }

        // Start page timing
        private function startPageTiming(){
            $mtime = explode(" ", microtime());
            $GLOBALS['starttime'] = $mtime[1] + $mtime[0];
        }

        // End page timing
        private function endPageTiming(){
            if( class_exists("functions") ){
                $totaltime = functions::logPageTime();
            }
            else{
                $totaltime = 0;
            }
            $GLOBALS['template']->assign("parseTime", $totaltime);
        }

        // Close database connection
        private function endDatabaseConnection(){
            $GLOBALS['database']->close_connection();
        }

        // Parse layout
        private function parseLayout(){
            $GLOBALS['template']->assign("queries", $GLOBALS['database']->queriesRun);
            $GLOBALS['template']->assign("layoutDir", 'layout_'.$GLOBALS['layout']);
            $GLOBALS['template']->assign("themeDir", $GLOBALS['theme']);

			if($GLOBALS['mf'] == 'yes' && isset($_SESSION['uid']))
			{
				$left = array();
				$right = array();

				$widget_tpl_index = array('portrait' => 'widget-user-portrait.tpl',
										  'details' => 'widget-user-details.tpl',
										  'travel' => 'widget-travel.tpl',
										  'notifications' => 'widget-notifications.tpl',
										  'quests' => 'widget-quests.tpl',
                                          'menu' => 'widget-side-menu.tpl',
                                          'quick_links' => 'widget-quick-links.tpl'
										  );

				foreach($widget_tpl_index as $option => $file_name)
				{
					if($GLOBALS['userdata'][0]['layout_'.$option.'_location'] == 'right' && $GLOBALS['userdata'][0]['layout_'.$option.'_index'] != 0)
					{
						$right[$GLOBALS['userdata'][0]['layout_'.$option.'_index']] = $file_name;
					}
					else if($GLOBALS['userdata'][0]['layout_'.$option.'_location'] == 'left'  && $GLOBALS['userdata'][0]['layout_'.$option.'_index'] != 0)
					{
						$left[$GLOBALS['userdata'][0]['layout_'.$option.'_index']] = $file_name;
					}
				}

				ksort($left);
				ksort($right);

				if(count($left) == 0)
				{
					$GLOBALS['template']->assign("hide_left_bar", true);
				}
				else
				{
					$GLOBALS['template']->assign("hide_left_bar", false);
				}

				if(count($right) == 0)
				{
					$GLOBALS['template']->assign("hide_right_bar", true);
				}
				else
				{
					$GLOBALS['template']->assign("hide_right_bar", false);
				}

				$GLOBALS['template']->assign("left_widgets", $left);
				$GLOBALS['template']->assign("right_widgets", $right);
			}

            if(isset($GLOBALS['mf']) && isset($_SESSION['uid']))
				$GLOBALS['template']->assign("userdata", $GLOBALS['userdata'][0]);
            
            $GLOBALS['page']->parse_layout(Data::$absSvrPath.'/files/layout_'.$GLOBALS['layout'].'/layout.tpl');
        }

        // Log large object sizes
        private function logLargeObjects(){
            if( Data::$debugObjectSizes == true ){

                // Page ID
                $id = isset($_GET['id']) ? $_GET['id'] : 0;

                // Template objects
                $all_tpl_vars = $GLOBALS['template']->getTemplateVars();
                foreach( $all_tpl_vars as $key => $value ){
                    $size = strlen( serialize($value) );
                    if( $size > Data::$objectSizeLimit ){

                        $GLOBALS['database']->execute_query("
                            INSERT INTO `log_tempObjectLogger`
                                (`name`, `objectSize`, `time`, `pageID`,`type`)
                            VALUES
                                ('".$key."', ".$size.", ".time().", ".$id.", 'TemplateVar');");
                    }
                }
            }
        }

        // Log large session sizes
        private function logLargeSessions(){
            if( Data::$debugSessionSizes == true ){
                $size = strlen( serialize($_SESSION) );
                if( $size > Data::$objectSessionLimit ){
                    $id = isset($_GET['id']) ? $_GET['id'] : 0;
                    $GLOBALS['database']->execute_query("
                        INSERT INTO `log_tempObjectLogger`
                            (`name`, `objectSize`, `time`, `pageID`,`type`,`data`)
                        VALUES
                            ('SessionSize', ".$size.", ".time().", ".$id.", 'SessionData','".serialize($_SESSION) ."');");
                }
            }
        }

        // Log IDS result
        private function logIDSresult( $result ){
            if( $result !== NULL && $_SERVER['SERVER_NAME'] != 'localhost' && $_SERVER['SERVER_NAME'] != '174.78.248.216'){
                require_once(Data::$absSvrPath.'/global_libs/IDS/Log/Composite.php');
                require_once(Data::$absSvrPath.'/global_libs/IDS/Log/Database.php');

                $compositeLog = new IDS_Log_Composite();
                $compositeLog->execute($result);
            }
        }

        // Log mobile application errors
        private function logMobileErrors(){
            if( functions::isAPIcall() ){
                if( isset($_POST['appErrorTitle'],$_POST['appErrorMsg'],$_POST['appErrorXml']) ){
                    $uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
                    $GLOBALS['database']->execute_query("
                        INSERT INTO `log_mobileErrors`
                            ( `time`, `title`,`message`,`xmlContent`,`uid`,`gameVersion`)
                        VALUES
                            (".time().", '".addslashes($_POST['appErrorTitle'])."','".addslashes($_POST['appErrorMsg'])."','".addslashes($_POST['appErrorXml'])."','".$uid."','".$_GET['version']."');");
                }
            }
        }

        // Page to show if game cannot be loaded
        private function gameUnavailable( $impact = NULL, $result = NULL, $init = NULL, $theData = NULL ){

            // Setup
            $this->loadMobileDetect();
            $this->setLayoutVar();
            $this->loadAds();
            $this->setSmartyVars();

            // Log result in the database & send an email
            $this->logIDSresult($result);

            // Instantiate layout
            $GLOBALS['template']->assign("MSG", '<script type="text/javascript"><!--
                            google_ad_client = "ca-pub-8967932238961461";
                            /* MaintainanceTime */
                            google_ad_slot = "0895907982";
                            google_ad_width = 160;
                            google_ad_height = 600;
                            //-->
                            </script>
                            <script type="text/javascript"
                            src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
                            </script>');

            if (isset($theData) && $theData == 0) {
                $GLOBALS['page']->Message("The TNR tables are currently being optimized. The website will be back online in a minute.
                    We thank you for your patience. This automatic maintenance is necessary to ensure optimal system performance.", 'Maintenance', 'id=1');
            } elseif ($impact > 5) {
                $GLOBALS['page']->Message("The system has detected actions on your account which are suspicious.
                                           These actions have now been logged, and will be reviewed as soon as possible. <br><br>
                                           The security system is composed of an aggressive algorithm that analyses all user input information,
                                           e.g. messages, message titles, nindos etc. If you did not intentionally attempt to compromise our site,
                                           please disregard this message and re-attempt your previous action. Please note that extensive usage of
                                           symbols etc. in your input may cause the system to flag it as suspicious again. Threat rating: ".$impact."
                                               <br><br>
                                               Threat data: ".$result."
                                           ", "Suspicious Actions Registered", 'id=1');
            }

            $this->endPageTiming();
            $this->parseLayout();
        }
    }

    // Instantiate
   $GLOBALS['game'] = new game();
   
