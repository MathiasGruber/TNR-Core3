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
require_once(Data::$absSvrPath.'/global_libs/Site/map.inc.php');
require_once(Data::$absSvrPath.'/libs/Battle/BattleStarter.php');


class page {

    // Constructor
    public function __construct() {
        $this->key_no = 0;
        $this->visible_content = true;
        $this->logout_override = false;
        $this->page_time = time();
        $this->checkedInbox = false;
        $this->locationInfo = false;
        $this->enableRecaptchaLimit = false;
        $this->pageLoadIncrements = 0;
    }

    // A function for setting the details for what page can be viewed and not
    public function setCurrentDetails( $user ){
        
        // Standard
        $this->isJailed = ($user[0]['status'] === 'jailed') ? true : false;
        $this->isAsleep = ($user[0]['status'] === 'asleep') ? true : false;
        $this->isQuesting = ($user[0]['status'] === 'questing') ? true : false;
        $this->inBattle = ($user[0]['status'] === 'combat' || $user[0]['status'] === 'exiting_combat') ? true : false;
        $this->exitingCombat = ($user[0]['status'] === 'exiting_combat') ? true : false;
        $this->inHospital = ($user[0]['status'] === 'hospitalized') ? true : false;
        $this->isDrowning = ($user[0]['status'] === 'drowning') ? true : false;
        $this->userLocation = str_replace(' village', '', $user[0]['location']);
        $this->inOutlawBase = ( in_array($this->userLocation, array('Gambler\'s Den','Bandit\'s Outpost','Poacher\'s Camp','Pirate\'s Hideout') ) ) ? true : false;
        $this->isHome = ($this->userLocation === $user[0]['village']) ? true : false;
        $this->inTown = in_array($user[0]['location'], array('Konoki','Shine','Samui','Silence','Shroud')) ? true : false;
        $this->inVillage = $this->inTown;
        $this->inRamen = ( in_array($this->userLocation, array('Emiko\'s Meatery', 'Stillwater\'s Chateau Barge', 'Black Lodge', 'Skyview Restaurant', 'Shaded Rest Inn') ) ) ? true : false;
        $this->hasVillage = ($user[0]['village'] !== 'Syndicate') ? true : false;
        $this->isOutlaw = ($user[0]['village'] === 'Syndicate') ? true : false;
        $this->location = "LOCATION(" . $user[0]['longitude'] . "," . $user[0]['latitude'] . ")";
        $this->travelRedirect = $user[0]['travel_default_redirect'];

        if(isset($GLOBALS['current_tile'][1]))
            $this->inOcean = $GLOBALS['current_tile'][1] == 'ocean';
        else
            $this->inOcean = false;

        if(!$GLOBALS['userdata'][0]['user_rank'] == 'Admin')
            $this->isOverEncumbered = $user[0]['over_encumbered'];
        else
            $this->isOverEncumbered = false;

        // Check inbox
        if( $this->checkedInbox == false ){
            $this->inboxIsFull = $this->checkInbox($user);
        }

        // Special checks for City of Mei
        if( $this->inOutlawBase ){
            // Check syndicate home
            if( !$this->hasVillage ){
                $this->isHome = true;
            }
            // This is a town
            $this->inTown = true;
        }

        // Get location info, only once also
        if( $this->locationInfo == false ){
            $this->locationInfo = cachefunctions::getLocationInformation( $this->userLocation );
        }

        // Set the alliance array, which is saved in the user. See the load_user functin in database.class.php
        if( isset($user[0]['alliance']) && isset($this->locationInfo[0]['owner'])  && $this->locationInfo[0]['owner'] !== "Neutral" ){

            // Get the owner of the current location, and plug that into the user alliance array
            $owner = $this->locationInfo[0]['owner'];
            $allyStatus = (int)$user[0]['alliance'][0][$owner];

            // Set alliance statuses
            $this->isAlly = $allyStatus === 1 ? true : false;
            $this->isWar = $allyStatus === 2 ? true : false;
            $this->isNeutral = $allyStatus === 2 ? true : false;
        }
        else{
            $this->isAlly = false;
            $this->isWar = false;
            $this->isNeutral = true;
        }
    }

    // A function for checking if the page can be viewed. If not, it will throw reason.
    public function checkPage( $pageData , $rankID, $checking_page_for_menu = false){
        
        // Only check pages that require login
        if( $pageData['require_login'] == "yes" ){
            // Check for session
            if( isset($_SESSION['uid']) ){
                // Check rank
                if( !($pageData['allow_ranks'] == "ALL" || strstr($pageData['allow_ranks'], $rankID )) ){
                    return "You cannot access this location with your current rank. You rank is <i>".Data::$RANKNAMES[$rankID]."</i> and the minimum allowed rank is <i>".Data::$RANKNAMES[$pageData['allow_ranks'][0]]."</i>";
                }

                // Check access restrictions
                if( !(  $pageData['access_restrictions'] == "NONE" || $pageData['access_restrictions'] == "DROWNING" ||// No Access Restrictions

                        ( ($this->isHome && preg_match("/(^HOME|^TOWN|^ALLY)/", $pageData['access_restrictions']) ) || (  $this->isOutlaw && $this->isAsleep && ($pageData['title'] == "Home" || ($pageData['title']  == "Item Repair" && $this->userLocation == 'Gamblers Valley')) ) ) || // User is in home village or user has no village and is at camp and is trying to access home

                        ( $this->inTown && $this->hasVillage && $this->isAlly && preg_match("/(^TOWN|^ALLY)/", $pageData['access_restrictions']) ) || // In ally village
                        ( $this->inTown && $this->hasVillage && $this->isWar && preg_match("/(^TOWN|^WAR)/", $pageData['access_restrictions']) ) || // In war village
                        ( $this->inTown && $this->hasVillage && $this->isNeutral && preg_match("/(^TOWN)/", $pageData['access_restrictions']) ) || // In neutral village
                        ( $this->inTown && !$this->hasVillage && preg_match("/(^TOWN)/", $pageData['access_restrictions']) ) || // Outlaw
                        (!$this->inTown && preg_match("/^(!TOWN|" . preg_quote($this->location) . ")/", $pageData['access_restrictions']) ) || // Not in a town
                        ( $this->inRamen && preg_match("/(^RAMEN)/", $pageData['access_restrictions']) )|| // this is a ramen stand
                        (!$this->inVillage && preg_match("/^(!VILLAGE|" . preg_quote($this->location) . ")/", $pageData['access_restrictions']) ) ||
                        (!$this->inOutlawBase && preg_match("/^(!HIDEOUT|" . preg_quote($this->location) . ")/", $pageData['access_restrictions']) ) ||
                        ( $this->inVillage && preg_match("/^(VILLAGE)/", $pageData['access_restrictions']) ) ||
                        ( $this->inOutlawBase && preg_match("/^(HIDEOUT)/", $pageData['access_restrictions']) )
                        )
                ){
                    if(isset($_POST['doTravel']) && !$checking_page_for_menu)
                    {
                        $message = 'You cannot view '.$pageData['menu_name'].' here. You have been re-directed to ';

                        if($this->travelRedirect == 'Combat')
                            $this->reloadNewPage($message.'Combat.',50,$rankID);
                        else if($this->travelRedirect == 'Scout')
                            $this->reloadNewPage($message.'Scout.',30,$rankID);
                        else if($this->travelRedirect == 'Rob' && $this->isOutlaw)
                            $this->reloadNewPage($message.'Rob.',49,$rankID);
                        else if($this->travelRedirect == 'Profile')
                            $this->reloadNewPage($message.'Profile.',2,$rankID);
                        else if($this->travelRedirect == 'QuestJournal')
                            $this->reloadNewPage($message.'QuestJournal.',120,$rankID);
                        else
                            $this->reloadNewPage($message.'Combat.',50,$rankID);
                    }
                    else
                    {
                        return "Access restrictions prevents you from accessing this location. Be sure to check your current location.";
                    }
                }

                // Check outlaw
                if( !( ( $this->hasVillage && preg_match("/(^ANY|^!OUTLAW)/", $pageData['rank_access']) ) || // Not an outlaw
                        (!$this->hasVillage && preg_match("/(^ANY|^OUTLAW)/", $pageData['rank_access']) )     // Outlaws
                )){
                    return "You cannot access this page with your current alignment";
                }

                // Check jail
                if( !(  ( $this->isJailed && preg_match("/(^any|^yes)/", $pageData['jail_access']) ) || // Not an outlaw
                        (!$this->isJailed && preg_match("/(^any|^no)/", $pageData['jail_access']) )     // Outlaws
                )){
                    return "You cannot access this location because of your current jail-status.";
                }

                // Sleep access
                if( !(  ( $this->isAsleep && preg_match("/(^ANY|^SLEEP)/", $pageData['sleep_access']) ) || // Is asleep
                        (!$this->isAsleep && preg_match("/(^ANY|^!SLEEP)/", $pageData['sleep_access']) )   // Not asleep
                )){
                    return "You cannot access this location because of your current sleep-status.";
                }

                // Questing access
                if( !(  ( $this->isQuesting && preg_match("/(^any|^yes)/", $pageData['questing_access']) ) || // Is asleep
                        (!$this->isQuesting && preg_match("/(^any|^no)/", $pageData['questing_access']) )   // Not asleep
                )){
                    return "You cannot access this location because of your current questing-status.";
                }

                //Over Encumbered Access
                if( !(  ( $this->isOverEncumbered && preg_match("/(^ANY|^OVER_ENCUMBERED)/", $pageData['over_encumbered_access']) ) || // Is asleep
                        (!$this->isOverEncumbered && preg_match("/(^ANY|^!OVER_ENCUMBERED)/", $pageData['over_encumbered_access']) )   // Not asleep
                )){
                    return "You cannot access this location because of your current Encumbered-status.";
                }

                // Battle access
                if( !(  ( $this->inBattle && preg_match("/(^ANY|^BATTLE)/", $pageData['battle_access']) ) || // In battle
                        (!$this->inBattle && preg_match("/(^ANY|^!BATTLE)/", $pageData['battle_access']) )   // Not battle
                )){
                    return "You cannot access this location because of your current battle-status.";
                }

                // Hospital access
                if( !(  ( $this->inHospital && preg_match("/(^any|^yes)/", $pageData['hospital_access']) ) ||
                        !$this->inHospital
                )){
                    return "You cannot access this location because of your current hospital-status.";
                }

                //RAMEN ACCESS
                if( $pageData['access_restrictions'] == "RAMEN" && !$this->inRamen )
                {
                    return "You cannot access this location because of your current ramen-status.";
                }

                // Hospital access
                if( ($pageData['access_restrictions'] == "DROWNING" && !$this->isDrowning) || !(  ( $this->isDrowning && preg_match("/(^any|^yes)/", $pageData['drowning_access']) ) ||
                        !$this->isDrowning
                )){
                    return "You cannot access this location because of your current drowning-status.";
                }

                if( $pageData['ocean_access'] == "no" && $this->inOcean )
                {
                    return "you cannot access this page because of your current location.";
                }

            }
            else{
                return "You have to be logged in to view this page";
            }
        }

        // Passed everything, return true
        return true;
    }

    // Check if user loaded more than 3 pages per second
    public function checkPageLoads(){
        if ($GLOBALS['memOn'] === true) {

            // Get user page data
            $this->userPageData = ( isset($_SESSION['uid']) ) ? cachefunctions::getPageData($_SESSION['uid']) : false;

            // Update page count
            if (isset($this->userPageData['loads'])) {
                if ($this->userPageData['timer'] == $this->page_time) {
                    if( $this->pageLoadIncrements == 0 ){
                        $this->userPageData['loads']++;
                        $this->pageLoadIncrements++;
                        cachefunctions::setPageData($_SESSION['uid'], $this->userPageData);
                    }
                } else {
                    $this->userPageData['loads'] = 0;
                    $this->userPageData['timer'] = $this->page_time;
                    cachefunctions::setPageData($_SESSION['uid'], $this->userPageData);
                }
            }

            // Run check
            if ( isset($this->userPageData['loads']) && $this->userPageData['loads'] >= Data::$pageLoadLimit ) {
                return false;
            }
        }
        return true; // Default to true
    }

    // Redirect to different page
    private function reloadNewPage( $message, $id, $rankID ){
        if($message != '')
            $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => $message));

        $this->page_data = cachefunctions::getPage( $id );
        $_GET['id'] = $id;
        $this->pageCheck = $this->checkPage( $this->page_data[0] , $rankID);
    }

    // Load Page Function
    public function load_content( $user , $quickRefreshes = true ) {

        // See if page ID is set
        if(!isset($_GET['id']) || ctype_digit($_GET['id'])  ){
            if ($this->visible_content == true) {
                if ($this->logout_override == 0) {
                    if (!isset($_GET['id']) || is_numeric($_GET['id'])) {

                        // Set default Page
                        if (!isset($_GET['id']) && !isset($_SESSION['uid'])) {
                            $_GET['id'] = 72;   // Set default to welcome
                        } elseif (!isset($_GET['id'])) {
                            $_GET['id'] = 2;      // Set logged in default to profile
                        }

                        // Get page data. From cache if available
                        $this->page_data = cachefunctions::getPage( $_GET['id'] );

                        if ( $this->page_data ) {

                            // Check for page loads. If none found, let the user see page as well
                            if ( $this->checkPageLoads() ) {
                                // If logged in, log the page the user visited
                                if( isset($_SESSION['uid']) ){
                                    cachefunctions::updateUserPages($_SESSION['uid'], $_GET['id']);
                                }

                                // Check to see if the page exists
                                if ($this->page_data != '0 rows') {

                                    // Assign title to the page
                                    $GLOBALS['template']->assign('TITLE', $this->page_data[0]['title']);

                                    // Check for user, and if set, then set settings
                                    $rankID = false;
                                    if( isset($_SESSION['uid']) ){
                                        $this->setCurrentDetails( $user );
                                        $rankID = $user[0]['rank_id'];
                                    }

                                    // Check the page
                                    $this->pageCheck = $this->checkPage( $this->page_data[0] , $rankID);

                                    //if the user is exiting combat force the user to the summary page.
                                    if(isset($this->exitingCombat) && $this->exitingCombat === true && $user[0]['battle_id'] != 0)
                                        $this->reloadNewPage( "You have been redirected to your battle's summary", 113, $rankID );

                                    else if(isset($this->exitingCombat) && $this->exitingCombat === true && $user[0]['battle_id'] == 0)
                                    {
                                        //change player status to awake if in no combat.
                                        $GLOBALS['Events']->acceptEvent('status', array('new'=>'awake', 'old'=> $GLOBALS['userdata'][0]['status']));

                                        $GLOBALS['database']->execute_query("UPDATE `users` SET `status` = 'awake' WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");
                                    }

                                    // Special handline of battle error & sleep errors
                                    if( $quickRefreshes == true ){
                                        if( stristr($this->pageCheck, "battle-status") && $this->inBattle && substr($user[0]['battle_id'],-2) != BattleStarter::territory ){
                                            $this->reloadNewPage( "You have been redirected to the battle you're currently in", 113, $rankID );
                                        }
                                        elseif( stristr($this->pageCheck, "sleep-status") && $this->isAsleep ){
                                            $redirectPage = $this->hasVillage ? 23 : 19;
                                            if( $redirectPage == 23 && !$this->inTown  ){
                                                $redirectPage = 19;
                                            }
                                            $this->reloadNewPage( "You have been redirected to the sleep page", $redirectPage , $rankID );
                                        }
                                    }

                                    // Check the pagecheck
                                    if( $this->pageCheck === true ){
                                        if (file_exists(Data::$absSvrPath.'/content/' . $this->page_data[0]['content'])) {
                                            if (!require_once(Data::$absSvrPath.'/content/' . $this->page_data[0]['content'])) {
                                                $GLOBALS['error']->handle_error("404", "Error including file", 1);
                                            }
                                        } else {
                                            $GLOBALS['error']->handle_error("404", "this page include file '".$this->page_data[0]['content']."' does not exist", 5);
                                        }
                                    }
                                    else{
                                        $GLOBALS['page']->Message( $this->pageCheck , 'Page Error', '');
                                    }
                                } else {
                                    $GLOBALS['error']->handle_error("404", "Page does not exist", 1);
                                }
                            } else {
                                $GLOBALS['page']->Message("You can't view more than ".Data::$pageLoadLimit." pages / second. Please try again.", 'Page Error', 'id=1');
                            }
                        } else {
                            $GLOBALS['error']->handle_error("404", "Page does not exist", 1);
                        }
                    } else {
                        $GLOBALS['error']->handle_error("404", "Invalid pagemark: " . strip_tags($_GET['id']), 2);
                    }
                } elseif ($this->logout_override == 1) {
                    $GLOBALS['page']->Message("You have been logged out.", 'Page Error', 'id=1');
                } elseif ($this->logout_override == 2) {
                    $GLOBALS['page']->Message("Your session ID does not match, you will now be logged out.", 'Page Error', 'id=1');
                }
            }
        }
        else{
            $GLOBALS['page']->Message("This is not a valid page identifier.", 'Page Error', 'id=1');
        }
    }

    // Parse layout
    public function parse_layout($layout_file) {
        if (file_exists($layout_file)) {
            self::loadURIs( Data::$absSvrPath );
            $getData = implode("-",$_GET)."page";
            $GLOBALS['template']->display( $layout_file , $getData );
        } elseif (isset($_SESSION['uid'])) {
            $GLOBALS['database']->execute_query("UPDATE `users_preferences` SET `layout` = 'default' WHERE `uid` = '" . $_SESSION['uid'] . "' LIMIT 1");
            $GLOBALS['layout'] = 'default';

            $GLOBALS['error']->handle_error('500', 'The layout file does not exist<br>Most likely you are using an outdated layout that was removed.<br>Your layout will now automatically reset to the default layout.', '8');
        } else {
            $GLOBALS['error']->handle_error('500', 'The layout file does not exist', '8');
        }
    }

    // Function for showing message
    public function Message(
        $message,
        $title = "System Message",
        $returnLink = false,
        $returnLabel = "Return",
        $smartyTemplate = "contentLoad",
        $returnLinkClass = "returnLink"
     ) {
        $GLOBALS['template']->assign('msg', $message);
        $GLOBALS['template']->assign('subHeader', $title);
        $GLOBALS['template']->assign('returnLabel', $returnLabel);
        $GLOBALS['template']->assign('returnLinkClass', $returnLinkClass);

        if ($returnLink !== false) {
            $GLOBALS['template']->assign('returnLink', $returnLink);
        }

        $GLOBALS['template']->assign('full_page', true);

        $GLOBALS['template']->assign( $smartyTemplate , './templates/message.tpl');
    }

    // Function for confirming user input
    public function Confirm($message, $title = "System Message", $returnTitle = "Return", $smartyTemplate = "contentLoad", $storage_name_1 = 'n/a', $storage_value_1 = 'n/a', $storage_name_2 = 'n/a', $storage_value_2 = 'n/a') {
        $GLOBALS['template']->assign('msg', $message);
        $GLOBALS['template']->assign('subHeader', $title);
        $GLOBALS['template']->assign('returnLink', $returnTitle);
        $GLOBALS['template']->assign('storage_name_1', $storage_name_1);
        $GLOBALS['template']->assign('storage_value_1', $storage_value_1);
        $GLOBALS['template']->assign('storage_name_2', $storage_name_2);
        $GLOBALS['template']->assign('storage_value_2', $storage_value_2);
        $GLOBALS['template']->assign( $smartyTemplate, './templates/confirm.tpl');
    }


    // Request information from user. InputFields is an array with entries on the following form:
    // array("infoText"=>"Input Text Here","inputFieldName"=>"UserName")
    //
    // Form data must contain the following
    // array("href"=>"Link","submitFieldName"=>"postUserName", "submitFieldText"=>"Search User")
    public function UserInput($message, $title, $inputFields, $formData, $returnTitle, $formID = "autoForm", $inputType = "post") {
        $GLOBALS['template']->assign('inputMsg', $message);
        $GLOBALS['template']->assign('inputsubHeader', $title);
        $GLOBALS['template']->assign('inputFields', $inputFields);
        $GLOBALS['template']->assign('formData', $formData);
        $GLOBALS['template']->assign('formID', $formID);
        $GLOBALS['template']->assign('formInputType', $inputType);
         $GLOBALS['template']->assign('contentLoad', './templates/input.tpl');

        // Set the return link to true if returntitle is simply return
        $returnLink = ( isset($returnTitle) && $returnTitle == "Return" ) ? true : $returnTitle;
        $GLOBALS['template']->assign('returnLink', $returnLink);
    }

    // Create a page wrapper, where content can be included in the mainScreen smarty variable
    // and a javascript library is included on the top of the page. Used for pages that support
    // backends
    public function createPageWrapper( $javascriptFile ){

        // Take whatever is in the contentLoad variable and assign it to the mainScreen variable instead
        $GLOBALS['template']->assign( "mainScreen" , $GLOBALS['template']->tpl_vars['contentLoad']->value );

        // Assign data to the wrapper
        $GLOBALS['template']->assign( "scriptFile" , $javascriptFile );
        $GLOBALS['template']->assign( "contentLoad" ,'./templates/pageWrapper.tpl');
    }

    // A function for hiding content. Primarily used in error class
    function content_visibility($toggle) {
        if ($this->visible_content != false) {
            // Double hide, just in case. It's the hideContent that matters though.
            $this->visible_content = false;
            $GLOBALS['template']->assign('contentLoad', '');
            $GLOBALS['template']->assign('hideContent', true);
        }
    }

    // Convert Small Images ( < 32KB Size) into Base64 Data URI to Reduce HTTP Requests
    function loadURIs($absPath) {
        $img_array = array();
        $GLOBALS['layout'] = isset($GLOBALS['layout']) ? $GLOBALS['layout'] : "default";

        switch($GLOBALS['layout']) { // Create Image Array Based on Different Layouts
            case('core3.0'): {
                $img_array = array(
                    'arrowNORTH' => $absPath.'/files/layout_core3.0/images/arrow_north.png',
                    'arrowSOUTH' => $absPath.'/files/layout_core3.0/images/arrow_south.png',
                    'arrowEAST' => $absPath.'/files/layout_core3.0/images/arrow_east.png',
                    'arrowWEST' => $absPath.'/files/layout_core3.0/images/arrow_west.png',
                    'HP' => $absPath.'/files/layout_core3.0/images/hp.png',
                    'CP' => $absPath.'/files/layout_core3.0/images/cp.png',
                    'SP' => $absPath.'/files/layout_core3.0/images/sp.png',
                    'buttonCOMMUNICATION' => $absPath.'/files/layout_core3.0/images/button_communication.png',
                    'buttonCHARACTER' => $absPath.'/files/layout_core3.0/images/button_character.png',
                    'buttonTRAIN' => $absPath.'/files/layout_core3.0/images/button_train.png',
                    'buttonVILLAGE' => $absPath.'/files/layout_core3.0/images/button_village.png',
                    'buttonMAP' => $absPath.'/files/layout_core3.0/images/button_map.png',
                    'buttonMISSIONS' => $absPath.'/files/layout_core3.0/images/button_missions.png',
                    'buttonSUPPORT' => $absPath.'/files/layout_core3.0/images/button_support.png',
                    'buttonCOMMUNICATION' => $absPath.'/files/layout_core3.0/images/button_communication.png',
                    'buttonCOMBAT' => $absPath.'/files/layout_core3.0/images/button_combat.png',
                    'fbCONNECT' => $absPath.'/images/fbconnect.png',
                    'tutorialIcon' => $absPath.'/files/layout_core3.0/images/tutorialIcon.png'
                );
            } break;
            case('light'): {

            } break;
            case('default'): {

            } break;
            case('core2'): {

            } break;
        }

        // Assign the Variables
        foreach($img_array as $key => $val) {
            $GLOBALS['template']->assign($key, $this->dataURIconvert($val));
        }
    }

    public function addFacebookEvent( $eventName , $value = "" ){
        $GLOBALS['template']->append('facebookEvents', array($eventName,$value) );
    }

    private function dataURIconvert($filename) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type aka mimetype extension
        $result = 'data:'.finfo_file($finfo, $filename).';base64,'.base64_encode(file_get_contents($filename));
        finfo_close($finfo);
        return $result;
    }

    private function checkInbox($user) {
        $msgCount = $GLOBALS['database']->fetch_data('SELECT COUNT(`users_pm`.`pm_id`) AS `msgCount`
                    FROM `users_pm` WHERE `receiver_uid` = '.$user[0]['id'].' LIMIT 1');

        $pmMax = ($user[0]['user_rank'] !== 'Member') ? 75 : 50;
        if( $user[0]['federal_level'] == "Gold" ){
            $pmMax += 25;
        }

        // Has checked now, don't re-check ever
        $this->checkedInbox = true;

        // Increase for staff
        if(in_array($user[0]['user_rank'], Data::$STAFF_RANKS, true)) { $pmMax = 500; }

        return !($msgCount[0]['msgCount'] < $pmMax);
    }

    // Send all un-tracked transactions to google analytics
    public function sendTransactionsToAnalytics(){

        // Get un-analyzed records
        $edits = $GLOBALS['database']->fetch_data("
            SELECT *
            FROM `ipn_payments`
            WHERE
                `s_uid` = '" . $_SESSION['uid'] . "' AND
                `txn_id` != '' AND
                `item` != '' AND
                `analyticsSent` = 'no'");

        // Check entries
        if( $edits !== "0 rows" ){

            // Entries in the data layer
            $dataEntries = array();

            // Add entries to layer
            foreach( $edits as $entry ){
                $dataEntries[] = "{
                    'transactionId': '".$entry['txn_id']."',
                    'transactionAffiliation': '".$entry['item']."',
                    'transactionTotal': '".$entry['price']."',
                    'transactionTax': '0',
                    'transactionProducts': [{
                        'sku': '".$entry['transaction_id']."',
                        'name': '".$entry['item']."',
                        'category': '".$entry['item']."',
                        'price': '".$entry['price']."',
                        'quantity': '1'
                    }]}";
            }

            // Compile them into the data layer
            $dataLayer = "dataLayer = [".implode(",", $dataEntries)."];";

            // Send data to facebook also
            $GLOBALS['page']->addFacebookEvent("Purchase",$entry['price']);

            // Send to smarty
            $GLOBALS['template']->assign("dataLayer", $dataLayer);

            // Get un-analyzed records
            $GLOBALS['database']->execute_query("
            UPDATE `ipn_payments`
            SET `analyticsSent` = 'yes'
            WHERE
                `s_uid` = '" . $_SESSION['uid'] . "' AND
                `txn_id` != '' AND
                `item` != '' AND
                `analyticsSent` = 'no'");

        }
    }

    // DEPRECATED CODE - MIGHT BE OF USE AT A LATER POINT
    public function getRandomQuote(){
        $quotes = array(
            array("Learn from yesterday, live for today, look to tomorrow, rest this afternoon.", "Snoopy"),
            array("Achievement — the man who rows the boat generally doesn't have time to rock it.", "Unknown"),
            array("Nothing will be attempted if all possible objections must first be overcome.", 'The golden principle, Paul Dickson\'s "The Official Rules"'),
            array("Everywhere is walking distance if you have the time.", 'Steven Wright'),
            array("Be not the first by whom the new are tried, nor yet the last to lay the old aside.", 'Alexander Pope'),
            array("Life can only be understood backward, but must be lived forward.", 'Kirkegaard'),
            array("Sacred cows make the best hamburger.", 'Mark Twain'),
            array("The solution to a problem changes the problem.", 'John Peers: Paul Dickson\'s "The Official Rules"'),
            array("Be like a postage stamp. Stick to one thing until you get there.", 'Josh Billings'),
            array("To err is human — and to blame it on a computer is even more so..", 'Unknown'),
            array("Better three hours too soon than a minute too late.", 'William Shakespeare'),

            array("My favorite things in life don't cost any money. It's really clear that the most precious resource we all have is time.", 'Steve Jobs'),
            array("If you spend too much time thinking about a thing, you'll never get it done.", 'Bruce Lee'),
            array("Patience and time do more than strength or passion.", 'Jean de La Fontaine'),
            array("How did it get so late so soon? Its night before its afternoon. December is here before its June. My goodness how the time has flewn. How did it get so late so soon?", 'Dr. Seuss'),
            array("You can fool all the people some of the time, and some of the people all the time, but you cannot fool all the people all the time.", 'Abraham Lincoln'),
            array("Time you enjoy wasting, was not wasted.", 'John Lennon'),
            array("The only reason for time is so that everything doesn't happen at once.", 'Albert Einstein'),
            array("Time is a circus, always packing up and moving away.", 'Ben Hecht'),
            array("Most people spend more time and energy going around problems than in trying to solve them.", 'Henry Ford')
        );

        return $quotes[ random_int(0, count($quotes)-1 ) ];
    }

    // Update the captcha page counter.
    // This function is called on content pages where we want to restrict botting,
    // e.g. of the battle systems & travel systems. Once the counter has reached 100
    // the game will show the login captcha for the user to input on all pages
    // if time passed since last call is higher than $clearSeconds, action isn't counted
    // after $decayLimit the counter starts counting backwars to 0
    public function updateCaptchaPageCounter( $increment = 1 , $clearSeconds = 1 , $decayLimit = 4 ){

        // Check system availability
        if ($this->enableRecaptchaLimit == true &&
            $GLOBALS['memOn'] === true &&
            isset($this->userPageData['captchaLoads']) ) {

            // Time since last update
            $timeSinceLast = $this->page_time - $this->userPageData['captchaTimer'];

            // Only update if it's less then $clearSeconds since last update
            if( $timeSinceLast <= $clearSeconds ){

                // Update the load counter
                $this->userPageData['captchaLoads'] += $increment;

            }

            // If a long time has passed since last call to this function, we decrease the counter
            if( $timeSinceLast > $decayLimit ){

                // Update the load counter
                $this->userPageData['captchaLoads'] -= $increment;
                if( $this->userPageData['captchaLoads'] < 0 ){
                    $this->userPageData['captchaLoads'] = 0;
                }
            }

            // Update time for last call of this function
            $this->userPageData["captchaTimer"] = $this->page_time;

            // If load speed is above 100, set the require captcha flag, which lasts 5min
            $isLocked = false;
            if( $this->userPageData['captchaLoads'] > 100 ){
                cachefunctions::setCaptchaLock( $_SESSION['uid'] , true );
                $isLocked = true;
            }
            else{
                $isLocked = cachefunctions::getCaptchaLock($_SESSION['uid']);
            }

            if( $isLocked ){

                // Message for user
                $quote = $this->getRandomQuote();
                $mess = "<br><blockquote>".$quote[0]."<br>- <i>".$quote[1]."</i></blockquote> <br><br>";
                $mess .= "
                     Because of your fast ninja clicking, we require that you
                     enter the captcha code below to confirm your humanity.
                     <br><br>
                     <i>Some ninjas are intent on cheating the system, and thereby you -
                     this system is a measure to prevent them from doing so. If you limit
                     how many times you press the refresh button in your browser, e.g. when
                     waiting for an attack timer to run out, you can limit the amount of times you have
                     to see this captcha.</i><br>
                ";

                // Check if Captcha is Correct
                if( $GLOBALS['error']->isCaptchaSubmitted() ){
                    // Check response
                    if( functions::ws_remove($_POST['recaptcha_response_field'] === '')) {
                        $mess .= "<br>You have to type something in the captcha field!";
                    }
                    elseif($GLOBALS['error']->checkCaptcha() === false) {
                        $mess .= "<br>Your entry was not valid!.";
                    }
                    else{
                        $isLocked = false;
                        $this->userPageData['captchaLoads'] = 0;
                        cachefunctions::deleteCaptchaLock( $_SESSION['uid'] );
                    }
                }

                // Check if still locked
                if( $isLocked ){
                    $GLOBALS['error']->captchaRequire( $mess );
                    // echo "<br><font color='green'>User is Locked</font>";
                }
            }

            // Update the timer
            cachefunctions::setPageData($_SESSION['uid'], $this->userPageData);
        }
    }
} 