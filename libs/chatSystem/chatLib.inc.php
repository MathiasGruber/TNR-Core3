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

    // Chat library
    class chatLib {

        public function __construct($table = 'tavern', $mod_panel = false) {
            $this->table = $table;
            $this->mod_panel = $mod_panel;
            // Grab Necessary User Data for Everything
            self::userData($this->table);
        }

        public function tavernError($message)
        {
            throw new Exception("<span class=\"tavern-error\">$message</span>");
        }

        protected function tavernData() {

            // Get tavern messages
            if(!($this->tavern_data = $GLOBALS['database']->fetch_data("SELECT `".$this->params['tavernTable']."`.*
                FROM `".$this->params['tavernTable']."`
                WHERE `".$this->params['tavernTable']."`.`".$this->params['tableColumn']."` = '".$this->params['tableSelect']."'
                    ORDER BY `".$this->params['tavernTable']."`.`tid` DESC LIMIT ".$this->min.", ".($this->ppp * 3).""))) {
                $this->tavernError('There was an error obtaining the tavern data!');
            }

            //get users that are blacklisted
            $blacklisted = $GLOBALS['database']->fetch_data("SELECT `username` FROM `users` , `users_preferences` WHERE INSTR( `pm_blacklist` , CONCAT( ';', `id` , ';' ) ) AND `uid` = '" . $_SESSION['uid'] . "' ORDER BY `username` ASC ");
            $black_list_settings = $GLOBALS['database']->fetch_data("SELECT `pm_setting` FROM `users_preferences` WHERE `uid` = ".$_SESSION['uid']);

            
            for (   $i = 0, $size = is_array($this->tavern_data) ? count($this->tavern_data) : 0; 
                    $i < $size;
                    $i++)
            {
                //remove entries in the taver_data from blacklisted users.
                if(is_array($blacklisted[0]) && $size != 0 && ($black_list_settings[0]['pm_setting'] == "block_black" || $black_list_settings[0]['pm_setting'] == "block_black_tavern"))
                {
                    $break_flag = false;

                    foreach($blacklisted as $bltemp)
                    {
                        if(isset($this->tavern_data[$i]['user']))
                        {
                            if( in_array($this->tavern_data[$i]['user'], $bltemp))
                            {
                                array_splice($this->tavern_data, $i, 1);
                                $size--;
                                $i--;

                                $break_flag = true;
                            }

                            if($break_flag){break;}
                        }
                    }

                }
            }

            if($this->tavern_data !== "0 rows" && count($this->tavern_data) > $this->ppp)
            {
                $this->tavern_data = array_slice($this->tavern_data, 0, $this->ppp);
            }

        }

        protected function userData($table) {
            if(trim($table) != '')
            {
                if(!($this->user_data = $GLOBALS['database']->fetch_data("SELECT `users`.`tban_time`, `".$table."`.`uid`,
                    `villages`.`name`, `clans`.`leader_uid` AS `clan_leader`, `squads`.`leader_uid` AS `anbu_leader`, `site_timer`.`character_cleanup`
                    FROM `users`
                        INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                        INNER JOIN `users_preferences` ON (`users_preferences`.`uid` = `users`.`id`)
                        INNER JOIN `site_timer` ON (`site_timer`.`script` = 'tavernSwitch')
                        LEFT JOIN `squads` ON (`squads`.`id` = `users_preferences`.`anbu`)
                        LEFT JOIN `clans` ON (`clans`.`id` = `users_preferences`.`clan`)
                        LEFT JOIN `".$table."` ON (`".$table."`.`uid` = `users`.`id`
                            AND `".$table."`.`time` = ".$GLOBALS['user']->load_time.")
                        LEFT JOIN `villages` ON (`villages`.`leader` = `users`.`username` AND `villages`.`name` = `users_loyalty`.`village`)
                    WHERE `users`.`id` = ".$GLOBALS['userdata'][0]['id']." LIMIT 1"))) {
                    $this->tavernError('There was an error trying to receive necessary user information for tavern!');
                }
                elseif($this->user_data === '0 rows') {
                    $this->tavernError('The necessary tavern user data failed to load!');
                }
            }
            else
                $this->tavernError('Incorrect tavern selection, data can not be loaded.');
        }

        // The overall function for setting up a chat system on this page
        public function setupChatSystem($params) {

            // Save parameters
            $this->params = $params;

            // Clean weird characters
            if( isset($this->params['chatName']) ){
                $this->params['chatName'] = preg_replace('/[[:^print:]]/', '', $this->params['chatName']);
            }

            // Posts per Page
            if(isset($GLOBALS['mf']) && $GLOBALS['mf'] == 'yes')
                $this->ppp = 25;
            else
                $this->ppp = 10;

            // For checking if transactions were started
            $this->hasTransaction = false;

            $this->min = 0;

            // Encapsulate in try block
            try {

                // Sub message
                if( isset($this->params['subMessage']) ){
                    $GLOBALS['template']->assign("subMessage", $this->params['subMessage']);
                    unset($this->params['subMessage']);
                }

                // Set secret hash
                self::setChatToken();

                // Check if user should even be able to see the page
                self::canSeeTavern();

                // Check if user is t-banned. If he isn't, set post variable. If he is, check if he should be unbanned
                $this->canPost = self::canUserPost();
                $GLOBALS['template']->assign('allowPost', $this->canPost);
//                if($this->canPost === "yes") { $GLOBALS['template']->assign('markItUp', true); }

                // Set the requested min counter
                $this->min = (isset($_REQUEST['min']) && ctype_digit($_REQUEST['min']) && $_REQUEST['min'] > 0) ? $_REQUEST['min'] : 0;

                // Capture Delete Post Requests
                if(isset($_REQUEST['identifier'])) {
                    list($this->postTime, $this->posterID) = explode(":", $_REQUEST['identifier']);
                    if(!empty($this->postTime) && !empty($this->posterID) ) { self::do_delete(); }
                }

                // Capture Post new Post Requests
                if(isset($_REQUEST['message'])) {
                    self::do_post();
                    $this->min = 0;
                }

                // Set the next and previous min-counters
                $prevMin = ($this->min - $this->ppp > 0) ? $this->min - $this->ppp : 0;
                $nextMin = $this->min + $this->ppp;

                // Encode the tavern setup
                $setup = self::encodeSetup();

                // Send stuff to smarty
                $GLOBALS['template']->assign("welcomeMessage", $this->params['chatName']);
                $GLOBALS['template']->assign("tavernTable", $this->params['tavernTable']);
                $GLOBALS['template']->assign('currentLink', functions::get_current_link());

                switch($this->table) {
                    case('tavern'): $GLOBALS['template']->assign('isAdmin', (self::isAdmin())); break;
                    case('tavern_anbu'): $GLOBALS['template']->assign('isAdmin', (self::isANBULeader() || self::isAdmin())); break;
                    case('tavern_clan'): $GLOBALS['template']->assign('isAdmin', (self::isClanLeader() || self::isAdmin())); break;
                    case('tavern_marriage'): $GLOBALS['template']->assign('isAdmin', true); break;
                    case('tavern_mod'): $GLOBALS['template']->assign('isAdmin', self::isAdmin()); break;
                    case('tavern_leaders'): $GLOBALS['template']->assign('isAdmin', self::isAdmin()); break;
                    default: $GLOBALS['template']->assign('isAdmin', self::isAdmin()); break;
                }

                $GLOBALS['template']->assign('mins', array($prevMin, $this->min, $nextMin));
                $GLOBALS['template']->assign('chatToken', $this->chatToken);
                $GLOBALS['template']->assign('setupData', $setup);

                // Get tavern messages
                self::tavernData();

                // Go through tavern messages
                if($this->tavern_data !== '0 rows') {
                    // Fix all the BB code

                    $close = false;
                    if(!isset($GLOBALS['Events']))
                    {
                        require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
                        $close = true;
                        $GLOBALS['Events'] = new Events();
                    }

                    for ($i = 0, $size = count($this->tavern_data); $i < $size; $i++) {
                        $this->tavern_data[$i]['message'] = BBCode2Html($this->tavern_data[$i]['message']);
                        $this->tavern_data[$i]['color_user'] = functions::username_color($this->tavern_data[$i]['user_group'], $this->tavern_data[$i]['user']);
                        $this->tavern_data[$i]['rank'] = $this->tavern_data[$i]['user_data'];
                        $this->tavern_data[$i]['village'] = $GLOBALS['userdata'][0]['village'];

                        if($GLOBALS['userdata'][0]['username'] != $this->tavern_data[$i]['user'])
                            $GLOBALS['Events']->acceptEvent('tavern_receive', array('data'=>preg_replace('/([^\\\\])(\\\\{2}\')/m', '$1\\\\\\\\\\\'', str_replace('\'','\\\'',strip_tags($this->tavern_data[$i]['message']))), 'context'=>$this->tavern_data[$i]['user']));

                        // Avatar Insertion Later
                       // $this->tavern_data[$i]['avatar'] = functions::getAvatar($this->tavern_data[$i]['uid']);
                    }

                    if($close)
                        $GLOBALS['Events']->closeEvents();

                    // Send to smarty
                    $GLOBALS['template']->assign("mod_panel", $this->mod_panel);
                    $GLOBALS['template']->assign('data', $this->tavern_data);

                }

                if($this->hasTransaction === true) { $GLOBALS['database']->transaction_commit(); }

                $GLOBALS['template']->assign('autoUpdateChat', $GLOBALS['userdata'][0]['chat_autoupdate'] ? 'true' : 'false');
                // Load main chat template
                $GLOBALS['template']->assign($this->params['smartyTemplate'], './templates/content/tavern/mainTavern.tpl');
            }
            catch (Exception $e) {
                // Rollback possible transactions
                if($this->hasTransaction === true) { $GLOBALS['database']->transaction_rollback($e->getMessage()); } // Roll Back

                // Show error message
                $this->Message($e->getMessage(), 'Chat System', 'id='.$_GET['id'], "Return", $this->params['smartyTemplate']);
            }
        }

        // The overall function for setting up a chat system on this page
        public function setupChatRefresh($params) {

            // Save parameters
            $this->params = $params;

            // Posts per Page
            if(isset($GLOBALS['mf']) && $GLOBALS['mf'] == 'yes')
                $this->ppp = 25;
            else
                $this->ppp = 10;

            // DB Record Selection
            $this->min = 0;

            // Encapsulate in try block
            try {

                // Set the requested min counter
                $this->min = (isset($_REQUEST['min']) && ctype_digit($_REQUEST['min']) && $_REQUEST['min'] > 0) ? $_REQUEST['min'] : 0;

                switch($this->table) {
                    case('tavern'): $GLOBALS['template']->assign('isAdmin', (self::isAdmin())); break;
                    case('tavern_anbu'): $GLOBALS['template']->assign('isAdmin', (self::isANBULeader() || self::isAdmin())); break;
                    case('tavern_clan'): $GLOBALS['template']->assign('isAdmin', (self::isClanLeader() || self::isAdmin())); break;
                    case('tavern_marriage'): $GLOBALS['template']->assign('isAdmin', true); break;
                    case('tavern_mod'): $GLOBALS['template']->assign('isAdmin', self::isAdmin()); break;
                    case('tavern_leaders'): $GLOBALS['template']->assign('isAdmin', self::isAdmin()); break;
                    default: $GLOBALS['template']->assign('isAdmin', self::isAdmin()); break;
                }

                // Get tavern messages
                self::tavernData();

                // Go through tavern messages
                if($this->tavern_data !== '0 rows') {

                    require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
                    $events = new Events();

                    // Fix all the BB code
                    for ($i = 0, $size = count($this->tavern_data); $i < $size; $i++) {
                        $this->tavern_data[$i]['message'] = BBCode2Html($this->tavern_data[$i]['message']);
                        $this->tavern_data[$i]['color_user'] = functions::username_color($this->tavern_data[$i]['user_group'], $this->tavern_data[$i]['user']);
                        $this->tavern_data[$i]['rank'] = $this->tavern_data[$i]['user_data'];
                        $this->tavern_data[$i]['village'] = $GLOBALS['userdata'][0]['village'];

                        //var_dump($GLOBALS['userdata'][0]['username']);
                        //var_dump("user: ".$this->tavern_data[$i]['user']."    message: ".strip_tags($this->tavern_data[$i]['message']));
                        if($GLOBALS['userdata'][0]['username'] != $this->tavern_data[$i]['user'])
                            $events->acceptEvent('tavern_receive', array('data'=>preg_replace('/([^\\\\])(\\\\{2}\')/m', '$1\\\\\\\\\\\'', str_replace('\'','\\\'',strip_tags($this->tavern_data[$i]['message']))), 'context'=>$this->tavern_data[$i]['user']));


                        // Avatar Insertion Later
                       // $this->tavern_data[$i]['avatar'] = functions::getAvatar($this->tavern_data[$i]['uid']);
                    }

                    $events->closeEvents();

                    // Send to smarty
                    // Send to smarty
                    //$GLOBALS['page']->Message(count($this->tavern_data)."<-count and ppp->".$this->ppp,"debug", 'id=2');
                    //echo '<script type="text/javascript">alert("'.count($this->tavern_data)."<-count and ppp->".$this->ppp.'");</script>';
                        $GLOBALS['template']->assign('data', $this->tavern_data);
                }

                // Load main chat template
                $GLOBALS['template']->assign("tavernTable", $this->params['tavernTable']);
                $GLOBALS['template']->assign("mod_panel", $this->mod_panel);
                $GLOBALS['template']->assign($this->params['smartyTemplate'], './templates/content/tavern/messages.tpl');
            }
            catch (Exception $e) {
                // Show error message
                $this->Message($e->getMessage(), 'Chat System', 'id='.$_GET['id'], "Return", $this->params['smartyTemplate']);
            }
        }

        // Calculated an encoded string for the setup
        protected function encodeSetup() {
            return urlencode(json_encode($this->params));
        }

        protected function decodeSetup($string) {
            return json_decode(urldecode($string), true);
        }

        // Function for setting a chat token for interaction with the backend
        protected function setChatToken() {

            // If we have a original setup, use that, otherwise use the constructor
            $setup = isset($this->params['originalSetup']) ? $this->params['originalSetup'] : $this->params;

            // Create the chat token from user data & chat setup
            $this->chatToken = functions::createHash(
                array_merge(
                    array(
                        $GLOBALS['userdata'][0]['id'],
                        $GLOBALS['userdata'][0]['login_id']
                    ),
                    $setup
                )
            );
        }

        protected function Message($message, $title = "System Message", $returnLink = false,
            $returnLabel = "Return", $smartyTemplate = "contentLoad") {
            $GLOBALS['template']->assign('msg', $message);
            $GLOBALS['template']->assign('subHeader', $title);
            $GLOBALS['template']->assign('returnLabel', $returnLabel);
            $GLOBALS['template']->assign($smartyTemplate , './templates/message.tpl');
            if ($returnLink !== false) { $GLOBALS['template']->assign('returnLink', $returnLink); }
        }


        // Check if the user can see the chat (i.e. not if in battle etc)
        protected function canSeeTavern() {

            // Check if in Battle or Hospital
            if(in_array($GLOBALS['userdata'][0]['status'], array('hospitalized', 'combat'), true)) {
                switch($GLOBALS['userdata'][0]['status']) {
//                    case('hospitalized'): $this->tavernError("You are in the hospital and can therefore not hang out in the chat."); break;
                    case('combat'): {
                        if(!isset($this->params['canCombat'])) {
                            if($this->params['canCombat'] == true) {
                                $this->tavernError("You are in battle and do not have time to check out the chat.");
                            }
                        }
                    } break;
                }
            }

            // Check Chat Token
            if(isset($this->params['tokenCheck'])) {
                if($this->params['tokenCheck'] !== $this->chatToken) {
                    $this->tavernError("The token sent to the server did not match, and therefore you cannot view the chat.");
                }
            }

            // Check global settings (if tavern is enabled)
            if($this->user_data[0]['character_cleanup'] <= 0) {
                $this->tavernError('Tavern has been temporarily disabled. Please try again later!');
            }

            // Check location
            if($this->params['tavernTable'] === "tavern") { // Check Chat Paramaters and Conditions
                if(in_array($GLOBALS['userdata'][0]['village'], Data::$VILLAGES, true)) {
                    switch($GLOBALS['userdata'][0]['village']) {
                        case('Syndicate'): { // Within Syndicate
                            if(!in_array($GLOBALS['userdata'][0]['location'], array('Konoki','Shine','Samui','Silence','Shroud'))) { return true; } // Not in a Village
                        } break;
                        default: { // NOT Within Syndicate
                             if(in_array($GLOBALS['userdata'][0]['location'], array('Konoki','Shine','Samui','Silence','Shroud'))) { return true; } // In a village
                        } break;
                    }
                }

                // If no Return was Called, Throw Exception
                $this->tavernError("Your location prevents you from participating in this chat");
            }


            return true; // Don't Care about other instances
        }

        // Check if this user is allowed to post
        protected function canUserPost() {

            if(!isset($_SESSION['uid']) || !ctype_digit($_SESSION['uid'])) {
                return "Session Invalid!";
            }
            elseif($GLOBALS['userdata'][0]['post_ban'] === '0') {
                return "yes";
            }
            else {

                $banMessage = 'You have been banned from the tavern. ';

                if($this->user_data[0]['tban_time'] >= $GLOBALS['user']->load_time) {
                    $banMessage .= 'Ban Timer: '.functions::convert_time(
                        ($this->user_data[0]['tban_time'] - $GLOBALS['user']->load_time), 'bantime', 'false'
                    );
                }
                elseif($this->user_data[0]['tban_time'] === '1337') {
                    $banMessage .= 'You are permanently banned';
                }
                else {

                    if(!$this->hasTransaction) { $GLOBALS['database']->transaction_start(); }
                    $this->hasTransaction = true;

                    if($GLOBALS['database']->execute_query("SELECT `users`.`id`, `users_timer`.`userid`
                        FROM `users`
                            INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
                        WHERE `users`.`id` = ".$_SESSION['uid']." LIMIT 1 FOR UPDATE") === false) {
                        $this->tavernError('There was an issue obtaining locks to undo tavern ban!');
                    }

                    if($GLOBALS['database']->execute_query("UPDATE `users`
                        SET `users`.`post_ban` = '0', `users`.`tban_time` = '0'
                        WHERE `users`.`id` = ".$_SESSION['uid']."") === false) {
                        $this->tavernError('There was an error updating the user out of tavern ban!');
                    }

                    return "yes";
                }
                return $banMessage;
            }
        }

        // Do post message
        protected function do_post() {

            // Check if user can post
            if($this->canPost !== 'yes') {
                $this->tavernError("Posting error: ".$this->canPost);
            }
            elseif(functions::ws_remove($_REQUEST['message']) === '' || functions::store_content($_REQUEST['message']) === '') {
                $this->tavernError("You cannot post blank messages");
            }

            // Validate message
            $message = functions::store_content($_REQUEST['message']);
            if(strlen($message) < 5 || strlen($message) > 500) {
                $this->tavernError("Your message must be between 5 and 500 characters long!");
            }

            // The user rank
            if( in_array($GLOBALS['userdata'][0]['user_rank'], array("Paid",  "Event"), true) ){

                // Set to federal level
                $userGroup = $GLOBALS['userdata'][0]['federal_level'];

                // Check if user changed his color / don't allow picking of higher colors
                if( !empty($GLOBALS['userdata'][0]['visibleRank']) ){
                    if( $GLOBALS['userdata'][0]['federal_level'] == "Gold" ||
                        ($GLOBALS['userdata'][0]['federal_level'] == "Silver" && $GLOBALS['userdata'][0]['visibleRank'] !== "Gold" ) ||
                        ($GLOBALS['userdata'][0]['federal_level'] == "Normal" && $GLOBALS['userdata'][0]['visibleRank'] !== "Gold" && $GLOBALS['userdata'][0]['visibleRank'] !== "Silver" )
                    ){
                        $userGroup = $GLOBALS['userdata'][0]['visibleRank'];
                    }
                }
            }
            else{
                $userGroup = $GLOBALS['userdata'][0]['user_rank'];
            }

            // Kage / leader
            if(stristr($this->params['userTitleOverwrite'], "Leader") || stristr($this->params['userTitleOverwrite'], "Kage")) {
                $userGroup = "Kage";
            }

            if(!$this->hasTransaction) { $GLOBALS['database']->transaction_start(); }
            $this->hasTransaction = true;

            // Check that the user hasn't previously posted this secont
            if($this->user_data[0]['uid'] == null) {
                if($GLOBALS['database']->execute_query("INSERT INTO `".$this->params['tavernTable']."`
                        (`".$this->params['tableColumn']."`, `user`, `user_data`, `uid`, `time`, `message`, `user_group`)
                    VALUES
                        ('". $this->params['tableSelect'] ."', '".$GLOBALS['userdata'][0]['username']."',
                            '".$this->params['userTitleOverwrite']."', '".$GLOBALS['userdata'][0]['id']."',
                            '".$GLOBALS['user']->load_time."',  '".$message."', '".$userGroup."');") === false) {
                    $this->tavernError("There was an error inserting your message into the database");
                }
            }

            $close = false;
            if(!isset($GLOBALS['Events']))
            {
                require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
                $close = true;
                $GLOBALS['Events'] = new Events();
            }

            $GLOBALS['Events']->acceptEvent('tavern_send', array('data'=>preg_replace('/([^\\\\])(\\\\{2}\')/m', '$1\\\\\\\\\\\'', str_replace('\'','\\\'',strip_tags($message))), 'context'=>$GLOBALS['userdata'][0]['username']));

            if($close)
                $GLOBALS['Events']->closeEvents();

            return true;
        }

         // Get the name of the tavern
        public function getTavernName() {
            return (($GLOBALS['userdata'][0]['village'] === 'Syndicate') ? 'The Underground Environment'
                : ucfirst(strtolower(str_replace(' village', '', $GLOBALS['userdata'][0]['location']))). "'s Tavern");
        }

        // Check if the user is kage
        public function isKage() {
            return (($this->user_data[0]['name'] === $GLOBALS['userdata'][0]['village']) ? true : false);
        }

        // Check if user can administer posts in general
        public function isAdmin() {
            // Check if person is moderator
            return ((!(isset($GLOBALS['userdata'][0]['user_rank']))) ? false
                : in_array($GLOBALS['userdata'][0]['user_rank'], Data::$MOD_STAFF_RANKS, true));
        }

        // Check if User is Clan Leader
        public function isClanLeader() {
            return (($this->user_data[0]['clan_leader'] === $GLOBALS['userdata'][0]['id']) ? true : false);
        }

        // Check if User is ANBU Leader
        public function isANBULeader() {
            return (($this->user_data[0]['anbu_leader'] === $GLOBALS['userdata'][0]['id']) ? true : false);
        }

        // Function to get the user rank
        public function getUserRank($overwriteLeaderTitle = false) {
            switch($this->table) {
                case('tavern'): {
                    if(!(self::isKage())) { return $GLOBALS['userdata'][0]['rank']; }
                    return (($overwriteLeaderTitle !== false) ? $overwriteLeaderTitle
                        : Data::$VILLAGE_KAGETITLES[$GLOBALS['userdata'][0]['village']]);
                } break;
                case('tavern_anbu'): {
                    if(!(self::isANBULeader())) { return $GLOBALS['userdata'][0]['rank']; }
                    return (($overwriteLeaderTitle !== false) ? $overwriteLeaderTitle : 'ANBU Leader');
                } break;
                case('tavern_clan'): {
                    if(!(self::isClanLeader())) { return $GLOBALS['userdata'][0]['rank']; }
                    return (($overwriteLeaderTitle !== false) ? $overwriteLeaderTitle : 'Clan Leader');
                } break;
                case('tavern_leaders'): {
                    return (($overwriteLeaderTitle !== false) ? $overwriteLeaderTitle
                        : Data::$VILLAGE_KAGETITLES[$GLOBALS['userdata'][0]['village']]);
                } break;
                default: return $GLOBALS['userdata'][0]['rank']; break;
            }
        }

        // Delete post
        protected function do_delete() {

            switch($this->table) {
                case('tavern'): {
                    if (!(self::isAdmin())) $this->tavernError("You are not allowed to delete messages!");
                } break;
                case('tavern_anbu'): {
                    if (!(self::isANBULeader() || self::isAdmin())) $this->tavernError("You are not allowed to delete messages!");
                } break;
                case('tavern_clan'): {
                    if (!(self::isClanLeader() || self::isAdmin())) $this->tavernError("You are not allowed to delete messages!");
                } break;
                case('tavern_marriage'): break; // Always Allowed for Couples
                case('tavern_mod'): {
                    if (!(self::isAdmin())) $this->tavernError("You are not allowed to delete messages!");
                } break;
                case('tavern_leaders'): {
                    if (!(self::isAdmin())) $this->tavernError("You are not allowed to delete messages!");
                } break;
                default: $this->tavernError("You are not allowed to delete messages!"); break;
            }

            if (!ctype_digit($this->postTime) || !ctype_digit($this->posterID)) {
                $this->tavernError("The postdata is corrupted, please try again");
            }

            if(!$this->hasTransaction) { $GLOBALS['database']->transaction_start(); }
            $this->hasTransaction = true;

            if($GLOBALS['database']->execute_query("DELETE FROM `".$this->params['tavernTable']."`
                WHERE `".$this->params['tavernTable']."`.`".$this->params['tableColumn']."` = '".$this->params['tableSelect']."'
                    AND `".$this->params['tavernTable']."`.`uid` = ".$this->posterID."
                    AND `".$this->params['tavernTable']."`.`time` = ".$this->postTime." LIMIT 1") === false) {
                $this->tavernError('Failed to Delete Tavern Post!');
            }

            return true;
        }

    }