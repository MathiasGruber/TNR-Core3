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

require_once($_SERVER['DOCUMENT_ROOT'].'/libs/notificationSystem/notificationSystem.php');

class user extends functions {

    private $enable_login;
    private $login_log_enable;
    private $user_update;
    private $ip;
    private $ua;
    public $load_time;

    public function launch() {

        // Control Variables
        $this->enable_login = true;
        $this->login_log_enable = false;
        $this->user_update = array();
        $this->ip = self::real_ip_address();
        $this->ua = $GLOBALS['database']->db_escape_string($_SERVER['HTTP_USER_AGENT']);

        # Regeneration Frequency (in seconds)
        $this->regenFrequency = 1;

        // Set load time
        $this->setLoadTime();

        // Always disable login on development domain
        if(stristr(Data::$absSvrPath, "development")) {
            $this->enable_login = true;
        }

        // Send to smarty
        $GLOBALS['template']->assign('serverTime', $this->load_time);

        // Check if used session is set. If not, check for login.
        if (isset($_SESSION['uid'])) {

            //starting notification system
            $GLOBALS['NOTIFICATIONS'] = new NotificationSystem($GLOBALS['userdata'][0]['notifications']);

            // Handle logout & user updates
            if(isset($_GET['act']) && ($_GET['act'] === 'logout')) {
                try {
                    $GLOBALS['database']->transaction_start();

                    if(($GLOBALS['database']->execute_query('SELECT `users`.`logout_timer`
                        FROM `users`
                        WHERE `users`.`id` = '.$_SESSION['uid'].' LIMIT 1 FOR UPDATE')) === false) {
                        throw new Exception('1');
                    }

                    // Update Logout Timer for User
                    if(($GLOBALS['database']->execute_query('UPDATE `users`
                        SET `users`.`logout_timer` = 0
                        WHERE `users`.`id` = '.$_SESSION['uid'].' LIMIT 1')) === false) {
                        throw new Exception('2');
                    }

                    $GLOBALS['database']->transaction_commit();
                }
                catch(Exception $e) {
                    $GLOBALS['database']->transaction_rollback('User Logout Rollback Error Message: '.$e->getMessage());
                }

                // Destroy User Session
                functions::removeSessionData();

                // Show login
                self::show_loginbox();
            }
            else {
                self::user_update();
            }
        }
        else {

            $GLOBALS['NOTIFICATIONS'] = new NotificationSystem('');

            // Do login page
            if(isset($_POST['lgn_usr_stpd']) || isset($_GET['loginFBid'])) {

                // Execute Login
                self::login_prompt($this->enable_login);

                // Update user data
                if(isset($_SESSION['uid']) && !empty($_SESSION['uid'])) {

                    // Retrieve data
                    $GLOBALS['userdata'] = $GLOBALS['database']->load_user($_SESSION['uid']);

                    // Update
                    self::user_update();

                    //redirect to landing page
                    if( !isset($GLOBALS['returnJson']) || $GLOBALS['returnJson'] !== true ){
                        header("Location: /?id=103");
                    }
                }
            }
            self::show_loginbox();
        }
    }

    // Set the load time
    public function setLoadTime(){
        $this->load_time = functions::getTimestamp();
    }

    private function FB_connection(&$ID) {

        require_once(Data::$absSvrPath.'/global_libs/General/facebook.class.php');
        $GLOBALS['facebook'] = new FBinteract;
        $GLOBALS['facebook']->fbConnect();
        $ID = $GLOBALS['facebook']->getUser();
    }

    public function real_ip_address() {
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { // De Facto Standard
            $tmp_ip = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']); // Expand IP Address Array if Formatted
            foreach($tmp_ip as $ip_addr) { // Iterate through IP Address
                if(filter_var($ip_addr, FILTER_VALIDATE_IP)) { // Validate IPv4 and IPv6 Format
                    return trim($ip_addr);
                }
            }
        }
        if( isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && !empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) ){
            return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        }
        return $_SERVER['REMOTE_ADDR']; // Return Reliable Variable if Nothing Else
    }

    // Data or Condition (True or False), Table Name, Table Column Variable, Value
    private function user_build_check($data_cond, $col, $var, $val) {

        $choice = ($data_cond === true) ? 'data' : 'cond'; // Choose Set Data or Where Condition

        // If Whole Condition where Table, Column and Value is Set, Ignore Overwritting Data
        if(!isset($this->user_update[$choice][$col][$var])) {
            // If Table and Column is Set, Set Value
            if(isset($this->user_update[$choice][$col])) { $this->user_update[$choice][$col][$var] = $val; }
            else { // If Table is Set, Add Column and Value. Otherwise Add Whole New Variable
                if(isset($this->user_update[$choice])) { $this->user_update[$choice][$col] = array($var => $val); }
                else { $this->user_update[$choice] = array($col => array($var => $val)); }
            }
        }
    }

    private function user_update_build_query() {

        $query = 'UPDATE ';
        $ctr = 0;
        $max = count($this->user_update['data']);

        // Create Update Tables Portion
        foreach(array_keys($this->user_update['data']) as $key) {
            $query .= '`'.$key.'`';
            $query .= ($ctr === $max - 1) ? ' SET ' : ', ';
            $ctr++;
        }

        $max = $ctr = 0;

        foreach(array_keys($this->user_update['data']) as $key) { $max += count($this->user_update['data'][$key]); }

        // Create Set Variables Portion
        foreach($this->user_update['data'] as $key => $val) {
            foreach($val as $key2 => $val2) {
                $query .= $key.'.'.$key2.' = '.((is_string($val2)) ? '"'.$val2.'"' : $val2);
                $query .= ($ctr === $max - 1) ? ' WHERE ' : ', ';
                $ctr++;
            }
        }

        $max = $ctr = 0;

        foreach(array_keys($this->user_update['cond']) as $key) { $max += count($this->user_update['cond'][$key]); }

        // Create Set Conditions Portion
        foreach($this->user_update['cond'] as $key => $val) {
            foreach($val as $key2 => $val2) {
                $query .= $key.'.'.$key2.' = '.((is_string($val2)) ? '"'.$val2.'"' : $val2);
                $query .= ($ctr === $max - 1) ? ((count($this->user_update['cond']) > 1) ? ' ' : ' LIMIT 1') : ' AND ';
                $ctr++;
            }
        }
        return $query;
    }

    // Needs to be revamped for new database structure
    private function user_update() {

        try {
            $GLOBALS['database']->transaction_start();

            // Check that session is set
            if(session_id() === '' || !isset($_SESSION['uid']) || empty($_SESSION['uid'])) { throw new Exception('1'); }

            // Check that 0 rows is not ser as userdata
            if($GLOBALS['userdata'] === '0 rows') { throw new Exception('5'); }

            // Verify Login ID Match
            if ((session_id().md5($GLOBALS['userdata'][0]['username']."xXx")) !== $GLOBALS['userdata'][0]['login_id']) { throw new Exception('2'); }

            // Logout user when timer expires
            if($GLOBALS['userdata'][0]['user_rank'] !== 'Event' && $GLOBALS['userdata'][0]['status'] != 'combat' && $GLOBALS['userdata'][0]['status'] != 'exiting_combat') {
                if ($GLOBALS['userdata'][0]['logout_timer'] < $this->load_time) { throw new Exception('3'); }
            }

            if($GLOBALS['userdata'][0]['battle_id'] == 0 && $GLOBALS['userdata'][0]['status'] == 'exiting_combat')
            {
                $GLOBALS['Events']->acceptEvent('status', array('new'=>'awake', 'old'=>$GLOBALS['userdata'][0]['status'] ));

                $GLOBALS['database']->execute_query("UPDATE `users` SET `status` = 'awake' WHERE `id` = ".$_SESSION['uid']);
                $GLOBALS['userdata'][0]['status'] == 'awake';
            }

            self::banCheck($GLOBALS['userdata']);

            // Log IP Address and Admin IPs
            self::log_IP_Check();

            // Log Last UA
            self::log_Last_UA();

            // Fix Hospital Issues
            self::hospital_transport_fix();

            // Login streak counter
            self::loginStreak_update();

            // Check & set loyalty/respect timers
            self::respect_update();

            // Check & set strength Factor
            self::strengthFactor_update();

            // Remove Federal Ranks
            self::federal_update();

            // Fix Overcap Issues
            if($GLOBALS['userdata'][0]['user_rank'] != "Event" && $GLOBALS['userdata'][0]['user_rank'] != "Admin")
                self::user_stats_overcap();

            // Call Regeneration Check Function (Last Call to be Made)
            self::regen_update();

            // Do update the user query
            self::buildAndRunUserQuery();

            $GLOBALS['database']->transaction_commit();
        }
        catch(Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            switch($e->getMessage()) {
                case('1'): $GLOBALS['page']->logout_override = 2; break; // No Active Session
                case('2'): { // Login ID doesn't Match
                    functions::removeSessionData();
                    $GLOBALS['page']->logout_override = 2;
                } break;
                case('3'): { // Logout Timer Expired
                    functions::removeSessionData();
                    $GLOBALS['page']->logout_override = 1;

                    //var_dump($e);

                } break;
                case("UserUpdateError"): {
                    // echo"Error updating user, do nothing";
                } break;
                default: { // User Data Fetch or Update Failed
                    functions::removeSessionData();
                    $GLOBALS['page']->logout_override = 1;

                    //var_dump($e);

                } break;
            }
        }
    }

    // Build & run user update query
    private function buildAndRunUserQuery() {

        // If all conditions have something within them, build the query
        $query = ($this->user_update !== array()) ? self::user_update_build_query() : '';

        // Run query
        if (!empty($query)) {
            if(($GLOBALS['database']->execute_query($query)) === false) {
                throw new Exception('UserUpdateError');
            }

            // Instant Update GLOBALS Array
            foreach($this->user_update['data'] as $val) {
                foreach($val as $key2 => $val2) { $GLOBALS['userdata'][0][$key2] = $val2; }
            }
            unset($this->user_update);
        }
    }

    private function log_IP_Check() {

        // IP Update Check
        if ($GLOBALS['userdata'][0]['last_ip'] !== $this->ip) {
            self::user_build_check(true, 'users', 'last_ip', $this->ip);
            self::user_build_check(false, 'users', 'id', $_SESSION['uid']);

            $past_ips = array_unique(explode('|||', $GLOBALS['userdata'][0]['past_IPs']), SORT_STRING);
            if(!in_array($this->ip, $past_ips, true)) {
                array_push($past_ips, $this->ip);
                if(count($past_ips) > 5) { array_shift($past_ips); }
                self::user_build_check(true, 'users', 'past_IPs', implode('|||', array_unique($past_ips, SORT_STRING)));
                self::user_build_check(false, 'users', 'id', $_SESSION['uid']);
            }

            // Log Admin IP
            if ($GLOBALS['userdata'][0]['user_rank'] === "Admin") {
                if($GLOBALS['database']->execute_query('INSERT INTO `adminIpLog`
                        (`admin`, `ip`, `time`)
                    VALUES
                        ("'.$GLOBALS['userdata'][0]['username'].'", "'.$this->ip.'", '.$this->load_time.');') === false) {
                    throw new Exception('There was an error logging the Admin IP!');
                }
            }
        }
    }

    private function log_Last_UA() {

        // UA Update Check
        if ($GLOBALS['userdata'][0]['last_UA'] !== $this->ua) {
            self::user_build_check(true, 'users', 'last_UA', $this->ua);
            self::user_build_check(false, 'users', 'id', $_SESSION['uid']);
        }
    }

    // Relocate User to Hospital Fix
    private function hospital_transport_fix() {

        if($GLOBALS['userdata'][0]['status'] === 'hospitalized') {
            if((int)$GLOBALS['userdata'][0]['cur_health'] === 0) {
                if(in_array($GLOBALS['userdata'][0]['village'], Data::$VILLAGES, true) && $GLOBALS['userdata'][0]['village'] !== "Syndicate") {
                    if(!in_array($GLOBALS['userdata'][0]['location'], array('Shroud','Shine','Samui','Silence','Konoki') )) {
                        if($GLOBALS['userdata'][0]['vil_latitude'] !== $GLOBALS['userdata'][0]['latitude']
                            || $GLOBALS['userdata'][0]['vil_longitude'] !== $GLOBALS['userdata'][0]['latitude']) {
                            self::user_build_check(true, 'users', 'latitude', $GLOBALS['userdata'][0]['vil_latitude']);
                            self::user_build_check(true, 'users', 'longitude', $GLOBALS['userdata'][0]['vil_longitude']);
                            self::user_build_check(true, 'users', 'location', $GLOBALS['userdata'][0]['village']);
                            self::user_build_check(false, 'users', 'id', $_SESSION['uid']);
                        }
                    }
                }
            }
        }
    }

    private function federal_update() {

        // Remove fed support if expired
        if ($GLOBALS['userdata'][0]['federal_timer'] !== '0') { // Check to see if Federal Timer was Set
            if($GLOBALS['userdata'][0]['federal_timer'] < $this->load_time) { // Check if Federal Timer Expired
                if(in_array($GLOBALS['userdata'][0]['user_rank'], array('Member', 'Paid'), true)) { // If Paid, Fix that
                    self::user_build_check(true, 'users', 'federal_timer', '0');

                    $GLOBALS['NOTIFICATIONS']->addNotification(array(
                                                                    'id' => 5,
                                                                    'duration' => time() + 60 * 60 * 2,
                                                                    'text' => 'Your federal support has expired. Jutsus have been untagged!',
                                                                    'dismiss' => 'no'
                                                                    ));

                    self::user_build_check(true, 'users_statistics', 'user_rank', 'Member');
                    self::user_build_check(true, 'users_statistics', 'federal_level', 'None');
                    self::user_build_check(false, 'users', 'id', $_SESSION['uid']);
                    self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);

                    //$count = $GLOBALS['database']->execute_query(
                    //        "SELECT count(*) as 'count' FROM `users_jutsu` where `uid` = ".$_SESSION['uid']."
                    //        && `tagged` regexp '[a-zA-Z]+:(9|10);'");
                    //
                    //if( $count !== false && $count[0]['count'] != 0 )
                    //{

                        $GLOBALS['database']->execute_query(
                            "UPDATE `users_jutsu`
                            SET `tagged` = REGEXP_REPLACE(`tagged`, '[^:;]+:(9|10);', '')
                            WHERE `uid` = ".$_SESSION['uid']." AND `tagged` != 'no'");

                        $GLOBALS['database']->execute_query(
                            "UPDATE `users_jutsu`
                            SET `tagged` = 'no'
                            WHERE `uid` = '" . $_SESSION['uid'] . "' AND `tagged` = '' ");

                    //}
                }
            }
        }
        else { // If Federal Timer was Reset, Check to see if User Rank Changed or Not
            if(in_array($GLOBALS['userdata'][0]['user_rank'], array('Paid'), true)) { // If they are Paid, Fix it

                $GLOBALS['NOTIFICATIONS']->addNotification(array(
                                                                    'id' => 5,
                                                                    'duration' => time() + 60 * 60 * 2,
                                                                    'text' => 'Your federal support has expired. Jutsus have been untagged!',
                                                                    'dismiss' => 'no'
                                                                    ));

                self::user_build_check(true, 'users_statistics', 'user_rank', 'Member');
                self::user_build_check(true, 'users_statistics', 'federal_level', 'None');
                self::user_build_check(false, 'users', 'id', $_SESSION['uid']);
                self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);

                //$count = $GLOBALS['database']->execute_query(
                //            "SELECT count(*) as 'count' FROM `users_jutsu` where `uid` = ".$_SESSION['uid']."
                //            && `tagged` regexp '[a-zA-Z]+:(9|10);'");
                //
                //if( $count !== false && $count[0]['count'] != 0 )
                //{

                    $GLOBALS['database']->execute_query(
                        "UPDATE `users_jutsu`
                        SET `tagged` = REGEXP_REPLACE(`tagged`, '[^:;]+:(9|10);', '')
                        WHERE `uid` = ".$_SESSION['uid']." AND `tagged` != 'no'");

                    $GLOBALS['database']->execute_query(
                        "UPDATE `users_jutsu`
                        SET `tagged` = 'no'
                        WHERE `uid` = '" . $_SESSION['uid'] . "' AND `tagged` = '' ");

                //}
            }
        }
    }

    // Update the login streak if needed
    private function loginStreak_update() {

        $close = false;

        // Check that the timer isn't zero
        if ((int)$GLOBALS['userdata'][0]['last_login_streak'] === 0) {
            $GLOBALS['userdata'][0]['last_login_streak'] = $this->load_time;
            self::user_build_check(true, 'users_timer', 'last_login_streak', $this->load_time);
            self::user_build_check(false, 'users_timer', 'userid', $_SESSION['uid']);
        }

        // Check time since last increment
        $secondsSinceIncrement = $this->load_time - $GLOBALS['userdata'][0]['last_login_streak'];

        // Check if we should update respect points
        if( $secondsSinceIncrement > 24 * 3600) {

            if(!isset($GLOBALS['Events']))
            {
                require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
                $close = true;
                $GLOBALS['Events'] = new Events();
            }

            // If two days passed, no award
            if( $secondsSinceIncrement < 2 * 24 * 3600 ){

                // Calculate new points
                $newPoints = $GLOBALS['userdata'][0]['login_streak']+1;
                $GLOBALS['Events']->acceptEvent('login_streak', array('old'=>$GLOBALS['userdata'][0]['login_streak'],'new'=> $GLOBALS['userdata'][0]['login_streak'] + 1));


                // Add a point
                self::user_build_check(true, 'users_statistics', 'login_streak', $newPoints );
                self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);

                // Add rewards
                $money = 10 * $newPoints * $newPoints;
                $expGain = 0; //floor(pow($newPoints, 1.6));
                $pops = 0;

                // Limit ryo
                if( $money > 250000 ){
                    $money = 250000;
                }

                // Check if we're awarding popularity points
                if( $newPoints % 5 == 0 ){
                    $pops += 1;
                }

                // Update the rewards
                self::user_build_check(true, 'users_statistics', 'money', $GLOBALS['userdata'][0]['money'] + $money);
                $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] + $money));

                self::user_build_check(true, 'users_statistics', 'experience', $GLOBALS['userdata'][0]['experience'] + $expGain);
                $GLOBALS['Events']->acceptEvent('experience', array('old'=>$GLOBALS['userdata'][0]['experience'],'new'=> $GLOBALS['userdata'][0]['experience'] + $expGain));

                self::user_build_check(true, 'users_statistics', 'pop_now', $GLOBALS['userdata'][0]['pop_now'] + $pops);
                self::user_build_check(true, 'users_statistics', 'pop_ever', $GLOBALS['userdata'][0]['pop_ever'] + $pops);
                $GLOBALS['Events']->acceptEvent('pop_gain', array('old'=>$GLOBALS['userdata'][0]['pop_now'],'new'=> $GLOBALS['userdata'][0]['pop_now'] + $pops));

                self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);

                // Calculate the time to set, so that the timer is updated at the same time each day
                $newTime = $GLOBALS['userdata'][0]['last_login_streak'] + 24*3600;

                // Update timer
                self::user_build_check(true, 'users_timer', 'last_login_streak', $newTime);
                self::user_build_check(false, 'users_timer', 'userid', $_SESSION['uid']);

                // Create the reward message
                $rewards = "As a reward you get <b>".$money."</b> ryo";
                if( $expGain > 0 ){
                    $rewards .= " and <b>".$expGain."</b> exp";
                }
                if( $pops > 0 ){
                    $rewards .= " and <b>".$pops."</b> popularity point";
                }

                // Give user message
                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'Login streak:<br> <b>'.$newPoints.'</b> days! <br><br>
                     '.$rewards.'.<br><br>
                     Keep logging in, certain streak numbers give special rewards! Thank you for Playing TNR!'));
            }
            else{

                // Remove all points
                self::user_build_check(true, 'users_statistics', 'login_streak', 0 );
                $GLOBALS['Events']->acceptEvent('login_streak', array('old'=>$GLOBALS['userdata'][0]['login_streak'],'new'=> 0));

                self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);

                // Update timer
                self::user_build_check(true, 'users_timer', 'last_login_streak', $this->load_time);
                self::user_build_check(false, 'users_timer', 'userid', $_SESSION['uid']);
            }
        }

        if($close)
            $GLOBALS['Events']->closeEvents();
    }


    // Update the users respect points if needed
    private function strengthFactor_update() {

        // Calculate the strength factor of this user
        $strengthFactor = functions::calc_strength_factor( $GLOBALS['userdata'][0] );

        // Check if it's higher than current
        if( $strengthFactor !==  $GLOBALS['userdata'][0]["strengthFactor"] ){
            self::user_build_check(true, 'users_statistics', 'strengthFactor', $strengthFactor);
            self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
        }
    }

    // Update the users respect points if needed
    private function respect_update() {

        // Check the timers
        if ((int)$GLOBALS['userdata'][0]['time_in_vil'] === 0) {
            self::user_build_check(true, 'users_loyalty', 'time_in_vil', $this->load_time);
            self::user_build_check(false, 'users_loyalty', 'uid', $_SESSION['uid']);
        }

        // Check time in village & respect points
        $secondsInVillage = $this->load_time - $GLOBALS['userdata'][0]['time_in_vil'];
        $updateCyclus = 24 * 3600;

        // Check if we should update respect points
        if($secondsInVillage > 10 * 24 * 3600) {

            // Check the point timer. If first time getting points in village, reset it.
            if ( (int)$GLOBALS['userdata'][0]['vil_pts_timer'] === 0 ||
                 $GLOBALS['userdata'][0]['vil_pts_timer'] < $GLOBALS['userdata'][0]['time_in_vil']
            ) {
                $GLOBALS['userdata'][0]['vil_pts_timer'] = $this->load_time;
                self::user_build_check(true, 'users_loyalty', 'vil_pts_timer', $this->load_time);
                self::user_build_check(false, 'users_loyalty', 'uid', $_SESSION['uid']);
            }

            // Check how long since last update
            $secondsSinceUpdate = $this->load_time - $GLOBALS['userdata'][0]['vil_pts_timer'];

            if ($secondsSinceUpdate > $updateCyclus) {
                // Points since update
                $totalPoints = $secondsSinceUpdate / $updateCyclus;
                $actualPoints = floor($totalPoints);
                $difference = $totalPoints - $actualPoints;

                if($actualPoints > 0) {
                    // new points and new timer
                    switch($GLOBALS['userdata'][0]['village']) {
                        case 'Syndicate': $newPoints = $GLOBALS['userdata'][0]['vil_loyal_pts'] - $actualPoints; break;
                        default: $newPoints = $GLOBALS['userdata'][0]['vil_loyal_pts'] + $actualPoints; break;
                    }

                    if(!isset($GLOBALS['Events']))
                    {
                        require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
                        $GLOBALS['Events'] = new Events();
                    }

                    $GLOBALS['Events']->acceptEvent('village_loyalty_gain', array('new'=>abs($newPoints), 'old'=>abs($GLOBALS['userdata'][0]['vil_loyal_pts']) ));

                    $newTimer = $this->load_time - $difference * $updateCyclus;

                    // Send to query array
                    self::user_build_check(true, 'users_loyalty', 'vil_loyal_pts', $newPoints);
                    self::user_build_check(true, 'users_loyalty', 'vil_pts_timer', $newTimer);
                    self::user_build_check(false, 'users_loyalty', 'uid', $_SESSION['uid']);
                }
            }
        }
    }

    private function regen_update() {

        // Welcome Message and Set Regen Time
        if ((int)$GLOBALS['userdata'][0]['last_regen'] === 0) {
            self::user_build_check(true, 'users_timer', 'last_regen', $this->load_time);
            self::user_build_check(false, 'users_timer', 'userid', $_SESSION['uid']);
            $GLOBALS['template']->assign("userIsNew", true);
            return;
        }

        // Log pageloads squares and sums for deviation calculations
        $microTime = microtime(true);
        if( !empty($GLOBALS['userdata'][0]['last_activity_ms']) &&
            $GLOBALS['userdata'][0]['last_activity_ms'] > 0 &&
            $GLOBALS['userdata'][0]['pageTrack_session_sampleSize'] > 0 )
        {

            // How long since last
            $sinceLastPageLoad = $microTime - $GLOBALS['userdata'][0]['last_activity_ms'];
            self::user_build_check(true, 'users_timer', 'pageTrack_session_sumSamples', $GLOBALS['userdata'][0]['pageTrack_session_sumSamples'] + $sinceLastPageLoad );
            self::user_build_check(true, 'users_timer', 'pageTrack_session_sumSamplesSquared', $GLOBALS['userdata'][0]['pageTrack_session_sumSamplesSquared'] + $sinceLastPageLoad*$sinceLastPageLoad );
        }

        // Log pageloads
        self::user_build_check(true, 'users_timer', 'pageTrack_session_sampleSize', $GLOBALS['userdata'][0]['pageTrack_session_sampleSize'] + 1 );
        self::user_build_check(true, 'users_timer', 'last_activity_ms', "".$microTime."" );
        self::user_build_check(false, 'users_timer', 'userid', $_SESSION['uid']);

        // Log user last activity
        if( (int)$GLOBALS['userdata'][0]['last_activity'] !== (int)$this->load_time) {
            self::user_build_check(true, 'users_timer', 'last_activity', $this->load_time);
            self::user_build_check(false, 'users_timer', 'userid', $_SESSION['uid']);
        }

        if($GLOBALS['userdata'][0]['status'] !== "combat" && $GLOBALS['userdata'][0]['status'] !== "exiting_combat") {

            $update_time = (int)$GLOBALS['userdata'][0]['last_regen'];

            // Check if regen boost endtime was exceeded
            if ($GLOBALS['userdata'][0]['regen_endtime'] <= $this->load_time) {
                if((int)$GLOBALS['userdata'][0]['regen_endtime'] !== 0) {
                    self::user_build_check(true, 'users', 'regen_boost', 0);
                    self::user_build_check(true, 'users', 'regen_endtime', 0);

                    $GLOBALS['NOTIFICATIONS']->addNotification(array(
                                                                    'id' => 6,
                                                                    'duration' => time() + 60 * 60 * 2,
                                                                    'text' => 'Your regeneration increase has expired.',
                                                                    'dismiss' => 'no'
                                                                    ));

                    self::user_build_check(false, 'users', 'id', $_SESSION['uid']);
                }
            }

            // Check if item regen boost endtime was exceeded
            if ($GLOBALS['userdata'][0]['item_regen_endtime'] <= $this->load_time) {
                if((int)$GLOBALS['userdata'][0]['item_regen_endtime'] !== 0) {
                    self::user_build_check(true, 'users', 'item_regen_boost', 0);
                    self::user_build_check(true, 'users', 'item_regen_endtime', 0);
                    self::user_build_check(false, 'users', 'id', $_SESSION['uid']);
                }
            }

            //	Determine if user regenerates
            if ($update_time + $this->regenFrequency < $this->load_time) {

                // Calculate the regeneration rate (rate/min)
                $regen = $this->actual_calc_regen($GLOBALS['userdata']);

                // Set the last regen value to the one it was before combat
                self::user_build_check(true, 'users_timer', 'last_regen_value', $regen);
                self::user_build_check(false, 'users_timer', 'userid', $_SESSION['uid']);

                // Save in cache
                cachefunctions::setUserRegeneration($_SESSION['uid'], $regen);
                // Build the part of the query that updates the user
                // cha/sta & health with the new regeneration
                self::updateUserWithRegeneration($regen, $update_time);

            }
        }
    }

    // Create query for updating the users health with X regeneration
    private function updateUserWithRegeneration($regen, $lastRegen) {

        // Determine regen values
        $passed_time = $this->load_time - $lastRegen;
        $regen_cycles = (int)floor($passed_time / $this->regenFrequency);

        // Correct regen from regen/min to regen/$this->regenFrequency
        $regen *= $this->regenFrequency / 60;

        //        Calculate new current stats:
        $new_cha = $GLOBALS['userdata'][0]['cur_cha'] + $regen_cycles * $regen;
        $new_sta = $GLOBALS['userdata'][0]['cur_sta'] + $regen_cycles * $regen;

        $new_cha = ($new_cha > $GLOBALS['userdata'][0]['max_cha']) ? $GLOBALS['userdata'][0]['max_cha'] : $new_cha;
        $new_sta = ($new_sta > $GLOBALS['userdata'][0]['max_sta']) ? $GLOBALS['userdata'][0]['max_sta'] : $new_sta;

        //        Check for caps
        if($GLOBALS['userdata'][0]['cur_cha'] < $GLOBALS['userdata'][0]['max_cha']) {
            self::user_build_check(true, 'users_statistics', 'cur_cha', $new_cha);
            self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
        }

        if($GLOBALS['userdata'][0]['cur_sta'] < $GLOBALS['userdata'][0]['max_sta']) {
            self::user_build_check(true, 'users_statistics', 'cur_sta', $new_sta);
            self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
        }

        // If user isn't hospitalized, health can be regenerated
        if ($GLOBALS['userdata'][0]['status'] !== 'hospitalized') {
            $new_health = $GLOBALS['userdata'][0]['cur_health'] + $regen_cycles * $regen;
            $new_health = ($new_health > $GLOBALS['userdata'][0]['max_health']) ? $GLOBALS['userdata'][0]['max_health'] : $new_health;

            if($GLOBALS['userdata'][0]['cur_health'] < $GLOBALS['userdata'][0]['max_health']) {
                self::user_build_check(true, 'users_statistics', 'cur_health', $new_health);
                self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
            }

            // Regeneration fix so it doesn't completely reset
            $back_correction = $passed_time - $regen_cycles * $this->regenFrequency;
            $back_correction = ($back_correction > $this->regenFrequency) ? 0 : $back_correction;

            // Set last regeneration time
            self::user_build_check(true, 'users_timer', 'last_regen', $this->load_time - $back_correction);
            self::user_build_check(false, 'users_timer', 'userid', $_SESSION['uid']);
        }
        else { // Otherwise, just update last regen condition
            self::user_build_check(true, 'users_timer', 'last_regen', $this->load_time);
            self::user_build_check(false, 'users_timer', 'userid', $_SESSION['uid']);
        }
    }

    // Fix overcapping
    private function user_stats_overcap() {

        // List of Variables to Check
        $strengths = array("tai_off", "nin_off", "gen_off", "weap_off", "tai_def", "nin_def", "gen_def", "weap_def");
        $generals = array("strength", "intelligence", "willpower", "speed");
        $pools = array("max_sta", "max_cha", "cur_sta", "cur_cha");
        $health = array("max_health", "cur_health");
        $masteries = array("element_mastery_1", "element_mastery_2");

        // Undercapped/Overcapped EM mastery
        foreach($masteries as $mastery) {
            if(isset($GLOBALS['userdata'][0][$mastery])) {
                if($GLOBALS['userdata'][0][$mastery] > Data::${'EM_'.$GLOBALS['userdata'][0]['rank_id']}) {
                    self::user_build_check(true, 'users_statistics', $mastery, Data::${'EM_'.$GLOBALS['userdata'][0]['rank_id']});
                    self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
                }
                elseif($GLOBALS['userdata'][0][$mastery] < 0) {
                    self::user_build_check(true, 'users_statistics', $mastery, 0);
                    self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
                }
            }
        }

        // Undercapped/Overcapped Offenses/Defenses
        foreach($strengths as $off_def) {
            if(isset($GLOBALS['userdata'][0][$off_def])) {
                if($GLOBALS['userdata'][0][$off_def] > Data::${'ST_MAX_'.$GLOBALS['userdata'][0]['rank_id']}) {
                    self::user_build_check(true, 'users_statistics', $off_def, Data::${'ST_MAX_'.$GLOBALS['userdata'][0]['rank_id']});
                    self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
                }
                elseif($GLOBALS['userdata'][0][$off_def] < 0) {
                    self::user_build_check(true, 'users_statistics', $off_def, 0);
                    self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
                }
            }
        }

        // Overcapped/Undercapped Generals
        foreach($generals as $val){
            if(isset($GLOBALS['userdata'][0][$val])) {
                if($GLOBALS['userdata'][0][$val] > Data::${'GEN_MAX_'.$GLOBALS['userdata'][0]['rank_id']}) {
                    self::user_build_check(true, 'users_statistics', $val, Data::${'GEN_MAX_'.$GLOBALS['userdata'][0]['rank_id']});
                    self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
                }
                elseif($GLOBALS['userdata'][0][$val] < 0) {
                    self::user_build_check(true, 'users_statistics', $val, 0);
                    self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
                }
            }
        }

        // Overcapped/Undercapped Health
        foreach($health as $val) {
            if (isset($GLOBALS['userdata'][0][$val])) {
                if($GLOBALS['userdata'][0][$val] > Data::${'MAX_HP_' . $GLOBALS['userdata'][0]['rank_id']}) {
                    self::user_build_check(true, 'users_statistics', $val, Data::${'MAX_HP_' . $GLOBALS['userdata'][0]['rank_id']});
                    self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
                }
                elseif($GLOBALS['userdata'][0][$val] < 0) {
                    self::user_build_check(true, 'users_statistics', $val, 0);
                    self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
                }
            }
        }

        // Overcapped/Undercapped Pools
        foreach($pools as $pool_stat) {
            if (isset($GLOBALS['userdata'][0][$pool_stat])) {
                if($GLOBALS['userdata'][0][$pool_stat] > Data::${'MAX_' . $GLOBALS['userdata'][0]['rank_id']}) {
                    self::user_build_check(true, 'users_statistics', $pool_stat, Data::${'MAX_' . $GLOBALS['userdata'][0]['rank_id']});
                    self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
                }
                elseif($GLOBALS['userdata'][0][$pool_stat] < 0) {
                    self::user_build_check(true, 'users_statistics', $pool_stat, 0);
                    self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
                }
            }
        }

        // Overcapped/Undercapped Bank
        if (isset($GLOBALS['userdata'][0]['bank'])) {
            if($GLOBALS['userdata'][0]['bank'] > Data::$MAX_BANK) {
                self::user_build_check(true, 'users_statistics', 'bank', Data::$MAX_BANK);
                self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
            }
            elseif($GLOBALS['userdata'][0]['bank'] < 0) {
                self::user_build_check(true, 'users_statistics', 'bank', 0);
                self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
            }
        }

        // Overcapped/Undercapped Money
        if (isset($GLOBALS['userdata'][0]['money'])) {
            if($GLOBALS['userdata'][0]['money'] > Data::$MAX_BANK) {
                self::user_build_check(true, 'users_statistics', 'money', Data::$MAX_BANK);
                self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
            }
            elseif($GLOBALS['userdata'][0]['money'] < 0) {
                self::user_build_check(true, 'users_statistics', 'money', 0);
                self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
            }
        }

        // Overcapped/Undercapped Level
        if (isset($GLOBALS['userdata'][0]['level'])) {
            if($GLOBALS['userdata'][0]['level'] > 10) {
                self::user_build_check(true, 'users_statistics', 'level', 10);
                self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
            }
            elseif($GLOBALS['userdata'][0]['level'] < 1) {
                self::user_build_check(true, 'users_statistics', 'level', 1);
                self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
            }
        }

        // Overcapped/Undercapped Level ID
        if (isset($GLOBALS['userdata'][0]['level_id'])) {
            if($GLOBALS['userdata'][0]['level_id'] > Data::$max_level_id) {
                self::user_build_check(true, 'users_statistics', 'level_id', Data::$max_level_id);
                self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
            }
            elseif($GLOBALS['userdata'][0]['level_id'] < 1) {
                self::user_build_check(true, 'users_statistics', 'level_id', 1);
                self::user_build_check(false, 'users_statistics', 'uid', $_SESSION['uid']);
            }
        }
    }

    // Login Functionality + Captcha
    private function login_prompt($login_enabled) {

        // Do login
        try {
            // Start db transaction
            $GLOBALS['database']->transaction_start();

            // Facebook Login ID
            $fbID = 0;

            // Only check captcha on WWW
            if( !isset($GLOBALS['returnJson']) || $GLOBALS['returnJson'] !== true ){

                // Check if Captcha was Submitted
                if( !$GLOBALS['error']->isCaptchaSubmitted() ){
                    throw new Exception('EmptyCaptcha');
                }

                // Check if Captcha is Correct
                if($GLOBALS['error']->checkCaptcha() === false) {
                    throw new Exception('WrongCaptcha');
                }
            }
            else{
                // Check if this is a login with FB id retrieved from APP. Security is in the GET vars and secret code hash.
                if( isset($_GET['loginFBid']) && !empty($_GET['loginFBid'])){
                    $tempCheck = $GLOBALS['database']->fetch_data("SELECT `fbID`, `username`, `password`, `salted_password` FROM `users` WHERE fbID = '".$_GET['loginFBid']."' LIMIT 1");
                    if( $tempCheck !== "0 rows" ){
                        $fbID = $_GET['loginFBid'];
                        $_POST['lgn_usr_stpd'] = $tempCheck[0]['username'];
                    }
                }
            }

            // Check to see if Username was Submitted
            if(!isset($_POST['lgn_usr_stpd']) || empty($_POST['lgn_usr_stpd']) ) {
                throw new Exception('You need to provide a username to login!');
            }

            // Check to see if Active Session Exists/User Logged In
            if(isset($_SESSION['uid'])) {
                throw new Exception("You are already logged in. Refreshing won't help you!");
            }


            // Check if Database Module was Loaded
            if(is_object($GLOBALS['database']) === false) {
                throw new Exception("The Database Module couldn't be loaded for some reason!");
            }

            // Pull Necessary Information about Submitted Username
            if(!($this->login_data = $GLOBALS['database']->fetch_data('
                SELECT `users`.`fbID`,
                    `users`.`username`, `users`.`password`, `users`.`salted_password`, `users`.`id`, `users`.`ban_time`, `users`.`tban_time`,
                    `users`.`tos_agree`, `users`.`activation`, `users`.`login_id`, `users`.`perm_ban`, `users`.`join_date`, `users`.`post_ban`,
                    `users_statistics`.`user_rank`, `users_statistics`.`federal_level`,
                    `users_preferences`.`layout`, `users_preferences`.`lock_count`,
                    `users_preferences`.`lock`,
                    `users_timer`.`last_regen`,
                    `site_timer`.`character_cleanup`,
                    `site_information`.`value`,
                    `users_timer`.`pageTrack_session_sampleSize`, `users_timer`.`pageTrack_session_sumSamples`, `users_timer`.`pageTrack_session_sumSamplesSquared`,
                    `users_timer`.`pageTrack_lifetime_sampleSize`, `users_timer`.`pageTrack_lifetime_sumSamples`, `users_timer`.`pageTrack_lifetime_sumSamplesSquared`,
                    `users_timer`.`pageTrack_lifetime_seconds`,
                    `users_timer`.`last_login`, `users_timer`.`last_activity`
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    INNER JOIN `users_preferences` ON (`users_preferences`.`uid` = `users`.`id`)
                    INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
                    INNER JOIN `site_timer` ON (`site_timer`.`script` = "login_script")
                    INNER JOIN `site_information` ON (`site_information`.`option` = "admin_ips")
                WHERE `users`.`username` = "'.$_POST['lgn_usr_stpd'].'" LIMIT 1 FOR UPDATE'))) {
                throw new Exception('There was an error trying to receive the user information!');
            }

            // If User Doesn't Exist, Throw Issue
            if($this->login_data === '0 rows') {
                throw new Exception("This user doesn't exist within the system!");
            }

            // Check to see if Login is Open
            if($login_enabled === false && !in_array($this->login_data[0]['user_rank'], Data::$STAFF_RANKS, true) && $this->login_data[0]['user_rank'] !== "Tester" && $this->login_data[0]['user_rank'] !== "EventMod" && $this->login_data[0]['user_rank'] !== "Moderator" && $this->login_data[0]['user_rank'] !== "Supermod" && $this->login_data[0]['user_rank'] !== "ContentAdmin" && $this->login_data[0]['user_rank'] !== "ContentMember") {
                throw new Exception('User Login has been temporarily disabled until further notice!');
            }

            // Make sure user has a salted password. If not, i.e. in case of admin reset, request he sets a new one
            self::giveNewPassword();

            // Check terms of service
            self::checkTOS();

            // If Cleanup Script is Running, Don't Allow Login
            if($this->login_data[0]['character_cleanup'] <= 0) {
                if($this->login_data[0]['user_rank'] !== 'Admin' && $this->login_data[0]['user_rank'] !== "Tester" && $this->login_data[0]['user_rank'] !== "EventMod" && $this->login_data[0]['user_rank'] !== "Moderator" && $this->login_data[0]['user_rank'] !== "Supermod" && $this->login_data[0]['user_rank'] !== "ContentAdmin" && $this->login_data[0]['user_rank'] !== "ContentMember") {
                    throw new Exception('User Login has been temporarily disabled. Please try again later!!');
                }
            }

            // If User Account is Locked
            if($this->login_data[0]['lock_count'] >= 3) {
                if((int)$this->login_data[0]['lock'] !== 0) {
                    throw new Exception('LockedAccount');
                }
            }

            // If User Account is Event Rank, No Longer Permitted
            if($this->login_data[0]['user_rank'] === 'Event') {
                throw new Exception('Event character cannot be logged in directly!');
            }

            // If User Account is not Activated
            if((int)$this->login_data[0]['activation'] !== 1) {
                throw new Exception('This account has not been activated yet! </text><a href="?id=63&amp;act=resend_activation">Resend Activation Email?</a><text>');
            }

            // Facebook Login Check
            if (isset($_GET['act']) && $_GET['act'] == "facebookLogin") {
                self::FB_connection($fbID);
            }
            elseif(isset($_POST['Facebook'])) {
                if(ctype_digit($_POST['Facebook'])) {
                    if((int)$_POST['Facebook'] !== 0) {
                        self::FB_connection($fbID);
                    }
                }
            }

            // If Password is Set
            if(isset($_POST['login_password'])) {
                if($this->ws_remove($_POST['login_password']) === '') { throw new Exception('You must submit a password to login!'); }
                if($this->login_data[0]['salted_password'] !== $this->encryptPassword( $_POST['login_password'], $this->login_data[0]['join_date'])) {
                    throw new Exception('WrongPassword');
                }
            }
            elseif($fbID !== 0) {
                if( $this->login_data[0]['fbID'] !== $fbID) {
                    throw new Exception('WrongPassword');
                }
            }
            else {
                throw new Exception('You must provide some login information to continue.');
            }

            // Check bans
            self::banCheck($this->login_data);

            // Check IP Lockdown
            // Admin Account IP Lockdown
            $admin = false;
            switch($_POST['lgn_usr_stpd']) {
                case('TerriatorOld'): {
                    if ($this->login_data[0]['value'] !== null) {
                        $entries = explode(";", $this->login_data[0]['value']);
                        foreach ($entries as $entry) {
                            if (strstr($entry, $_POST['lgn_usr_stpd'])) {
                                $adminip = explode(",", $entry);
                                if (strstr( $this->real_ip_address() , $adminip[1])) { 
                                    $admin = true; 
                                }
                            }
                        }
                    }
                } break;
                default: $admin = true; break;
            }

            // Check if Admin IP Lockdown Caught
            if ($admin !== true) { throw new Exception('AdminLock'); }

            // Log User Logins
            if ($this->login_log_enable) {
                if($file = fopen('./logs/login.log', 'a')) {
                    fwrite($file, "User Login Logger!\r\n".
                        "Date Attempted: ".date('Y-m-d G:i:s')."\r\n".
                        "Username: ".$_POST['lgn_usr_stpd']."\r\n".
                        "IP Address: ".$this->real_ip_address()."\r\n\r\n");
                    fclose($file);
                }
            }

            // Determine User Logout Time
            switch($this->login_data[0]['user_rank']) {
                case('Moderator'): case('Supermod'): case('PRmanager'): case('Admin'): case('Event'):
                case('EventMod'): case('ContentAdmin'): $logout_time = 7200; break;
                case('Paid'): case('Tester'): case('ContentMember'): {
                    switch($this->login_data[0]['federal_level']) {
                        case "Gold": $logout_time = 7200; break;
                        case "Normal": case "Silver": $logout_time = 5400; break;
                        default: $logout_time = 3600; break;
                    }
                } break;
                case('Member'): $logout_time = 3600; break;
                default: $logout_time = 0; break;
            }

            $_SESSION['uid'] = $this->login_data[0]['id'];
            $_SESSION['hash'] = sha1($this->login_data[0]['username']."secretKey746HSk29");

            // Default $_GET['id'] to ID=2
            if(!isset($_GET['id']) || $_GET['id'] == "63" ) {
                unset($_GET);

                // if new user show profile, otherwise show welcome
                if ((int)$this->login_data[0]['last_regen'] === 0) {
                    $_GET['id'] = '2';
                }
                else{
                    $_GET['id'] = '72';
                }
            }

            // Push all previous info on page tracking (for anti-bottin)
            // to the life-time variables. The session tracking is reset
            $this->login_data[0][ 'pageTrack_lifetime_sampleSize' ] += $this->login_data[0][ 'pageTrack_session_sampleSize' ];
            $this->login_data[0][ 'pageTrack_lifetime_sumSamples' ] += $this->login_data[0][ 'pageTrack_session_sumSamples' ];
            $this->login_data[0][ 'pageTrack_lifetime_sumSamplesSquared' ] += $this->login_data[0][ 'pageTrack_session_sumSamplesSquared' ];
            $this->login_data[0][ 'pageTrack_lifetime_seconds' ] += $this->login_data[0][ 'last_activity' ] - $this->login_data[0][ 'last_login' ];

            // Update User Essentials after Successful Login
            if(($GLOBALS['database']->execute_query('
                UPDATE `users`, `users_preferences`, `users_timer`
                SET `users_preferences`.`lock_count` = 0,
                    `users_timer`.`pageTrack_session_sampleSize` = 0,
                    `users_timer`.`pageTrack_session_sumSamples` = 0,
                    `users_timer`.`pageTrack_session_sumSamplesSquared` = 0,
                    `users_timer`.`pageTrack_lifetime_sampleSize` = '.$this->login_data[0][ 'pageTrack_lifetime_sampleSize' ].',
                    `users_timer`.`pageTrack_lifetime_sumSamples` = '.$this->login_data[0][ 'pageTrack_lifetime_sumSamples' ].',
                    `users_timer`.`pageTrack_lifetime_sumSamplesSquared` = '.$this->login_data[0][ 'pageTrack_lifetime_sumSamplesSquared' ].',
                    `users_timer`.`pageTrack_lifetime_seconds` = '.$this->login_data[0][ 'pageTrack_lifetime_seconds' ].',
                    `users`.`login_id` = "'.session_id().md5($this->login_data[0]['username'].'xXx').'",
                    `users`.`logout_timer` = '.($this->load_time + $logout_time).',
                    `users_timer`.`last_login` = '.$this->load_time.'
                WHERE `users`.`id` = '.$this->login_data[0]['id'].' AND `users_timer`.`userid` = `users`.`id`
                    AND `users_preferences`.`uid` = `users`.`id`')) === false)
            {
                throw new Exception('There was an error trying to update user credentials. Please try again!');
            }

            // Log mobile login data
            $this->logMobileLogin( $this->login_data[0]['id'] );

            // Commit the database transaction now
            $GLOBALS['database']->transaction_commit();
        }
        catch(Exception $e) {

            // Destroy Session Data, if it exists, if an Error was throw
            if(isset($_SESSION['uid'])) {
                functions::removeSessionData();
            }

            // Rollback database stuff
            $GLOBALS['database']->transaction_rollback($e->getMessage());

            // Disable page loading (otherwise the messages won't show)
            $GLOBALS['page']->visible_content = false;

            // Handle the error message
            switch($e->getMessage()) {
                case('EmptyCaptcha'): $GLOBALS['error']->captchaRequire("Please enter the confirmation code to confirm your humanity! <br/> Press \"Enter\" to continue!"); break;
                case('WrongCaptcha'): $GLOBALS['error']->captchaRequire('The captcha code you entered was incorrect. Please try again!'); break;
                case('LockedAccount'): $GLOBALS['page']->Message( '3 Invalid login attempts have been made on your account. It has now been locked!' , 'User Login Error', 'id=63&amp;act=send_unlock', 'Unlock the Account'); break;
                case('WeakPassword'): self::changePasswordForm(); break;
                case('TOSagree'): self::acceptTosForm(); break;
                case('WrongPassword'): { // Password Incorrect
                    try {
                        $GLOBALS['database']->transaction_start();

                        $GLOBALS['error']->handle_error('403', 'Your username or password are incorrect', '1', true);

                        if(($GLOBALS['database']->execute_query('UPDATE `users_preferences`, `users`
                            SET `users_preferences`.`lock_count` = `users_preferences`.`lock_count` + 1
                            WHERE `users`.`username` = "'.$_POST['lgn_usr_stpd'].'" AND `users_preferences`.`uid` = `users`.`id`')) === false) {
                            throw new Exception('1');
                        }

                        // Log Attempted User Login
                        /*
                        if($file = fopen('./logs/bad_login.log', 'a')) {
                            fwrite($file, "Incorrect Password Attempt!\r\n".
                                "Attempted Date: ".date('Y-m-d G:i:s')."\r\n".
                                "Username: ".$_POST['lgn_usr_stpd']."\r\n".
                                "IP Address: ".$this->real_ip_address()."\r\n\r\n");
                            fclose($file);
                        }
                        */

                        $GLOBALS['database']->transaction_commit();
                    }
                    catch(Exception $e) {
                        $GLOBALS['database']->transaction_rollback('User Lock Count Error Message: '.$e->getMessage());
                    }
                } break;
                case('AdminLock'): {
                    $GLOBALS['error']->handle_error('600', 'Admin accounts are locked down to IP\'s. Your IP: '.$this->real_ip_address(), '1', true);
                    //	Log Attempted Admin Login
                    /*
                    if($file = fopen('./logs/bad_login.log', 'a')) {
                        fwrite($file, "Wrong IP Address!\r\n".
                            "Attempted Date: ".date('Y-m-d G:i:s')."\r\n".
                            "Username: ".$_POST['lgn_usr_stpd']."\r\n".
                            "IP Address: ".$this->real_ip_address()."r\n\r\n");
                        fclose($file);
                    }
                    */
                } break;
                default: $GLOBALS['page']->Message($e->getMessage(), 'User Login Error', 'id=1'); break;
            }
        }
    }

    private function logMobileLogin( $uid ){
        if( isset($GLOBALS['returnJson']) && $GLOBALS['returnJson'] == true ){
            if( isset($_GET['deviceID'],$_GET['platform'],$_GET['pushProvider'],$_GET['pushID']) ){
                $record = $GLOBALS['database']->fetch_data("SELECT * FROM `log_mobileLogins` WHERE `uid` = '".$uid."' AND `deviceID` = '".$_GET['deviceID']."' AND `platform` = '".$_GET['platform']."' LIMIT 1;");
                if( $record !== "0 rows" ){
                    $GLOBALS['database']->execute_query('UPDATE `log_mobileLogins` SET `time` = "'.time().'", `gameVersion` = "'.$_GET['version'].'" WHERE `id` = '.$record[0]['id'].' LIMIT 1');
                }else{
                    $GLOBALS['database']->execute_query("INSERT INTO `log_mobileLogins` ( `time`, `uid`,`deviceID`,`platform`,`pushProvider`,`pushID`,`gameVersion`) VALUES (".time().", '".$uid."','".addslashes($_GET['deviceID'])."','".addslashes($_GET['platform'])."','".addslashes($_GET['pushProvider'])."','".addslashes($_GET['pushID'])."','".addslashes($_GET['version'])."');");
                }
            }

        }
    }

    private function banCheck($userdata) {

        // Check if user is banned
        if($userdata[0]['perm_ban'] === '1') {
            // Get all bans
            $banMessage = "";

            if(!($user_record = $GLOBALS['database']->fetch_data('SELECT `moderator_log`.`time`, `moderator_log`.`reason`,
                `moderator_log`.`duration`, `moderator_log`.`message`
                FROM `moderator_log`
                WHERE `moderator_log`.`uid` = '.$userdata[0]['id'].' AND
                    `moderator_log`.`action` IN ("Ban", "Reduction", "Extension")
                    ORDER BY `moderator_log`.`id` DESC'))) {
                throw new Exception('There was an error obtaining existing permanent ban data!');
            }
            elseif($user_record !== "0 rows") {
                foreach($user_record as $record) {
                    $banMessage .= "<br><br><i>".date('l jS \of F Y h:i:s A', $record['time']).
                        " - <b>".$record['reason']."</b> - Duration: ".$record['duration']." </i>: <br>".functions::parse_BB($record['message']);
                }
                throw new Exception('Your permanently banned! Record details are as follows:<br>'.$banMessage);
            }
            else {
                throw new Exception('You are permanently banned, but there is no indication of a record within the system.'.
                    '<br>Please report within forum under the Support System!');
            }
        }
        elseif($userdata[0]['ban_time'] !== '0') { // Ban Timer is Set
            if($userdata[0]['ban_time'] !== '1337') { // Ban Timer isn't Permanent
                if($userdata[0]['ban_time'] > $this->load_time) { // Ban Timer is Active
                    // Get all bans
                    $banMessage = "";
                    if(!($user_record = $GLOBALS['database']->fetch_data('SELECT `moderator_log`.`time`, `moderator_log`.`reason`,
                        `moderator_log`.`duration`, `moderator_log`.`message`
                        FROM `moderator_log`
                        WHERE `moderator_log`.`uid` = '.$userdata[0]['id'].' AND
                            `moderator_log`.`action` IN ("Ban", "Reduction", "Extension")
                            ORDER BY `moderator_log`.`id` DESC'))) {
                        throw new Exception('There was an error obtaining existing ban data!');
                    }
                    elseif($user_record !== "0 rows") {
                        foreach($user_record as $record) {
                            $banMessage .= "<br><br><i>".date('l jS \of F Y h:i:s A', $record['time']).
                                " - <b>".$record['reason']."</b> - Duration: ".$record['duration']." </i>: <br>".functions::parse_BB($record['message']);
                        }

                        // Set the time left of the ban
                        $timeLeft = functions::convert_time($userdata[0]['ban_time'] - $this->load_time, 'banTimer', 'false');
                        throw new Exception('You have been banned! Time left: '.$timeLeft.'.<br>Record details are as follows:<br>'.$banMessage);
                    }
                    else {
                        throw new Exception('You are banned, but there is no indication of a record within the system. '.
                            'Please report within forum under the Support System!');
                    }
                }
                else { // Ban Timer has Expired
                    // Update User Ban Time upon Successful Login
                    if($GLOBALS['database']->execute_query('UPDATE `users`
                        SET `users`.`ban_time` = "0"
                        WHERE `users`.`id` = '.$userdata[0]['id'].' LIMIT 1') === false) {
                        throw new Exception('There was an error trying to clear the ban timer!');
                    }
                }
            }
            else { // Time indicates permanent ban, but the indicator wasn't set
                // Update User Permanent Ban Status upon Successful Login
                if($GLOBALS['database']->execute_query('UPDATE `users`
                    SET `users`.`perm_ban` = "1"
                    WHERE `users`.`id` = '.$userdata[0]['id'].' LIMIT 1') === false) {
                    throw new Exception('There was an error trying to update your permanent ban status indicator!');
                }
            }
        }
        elseif($userdata[0]['tban_time'] !== '0') {
            if($userdata[0]['tban_time'] !== '1337') {
                if($userdata[0]['post_ban'] === '1') {
                    if($userdata[0]['tban_time'] <= $this->load_time) { // Tavern Ban Timer has Expired
                        // Update User Tavern Ban Timer upon Successful Login
                        if($GLOBALS['database']->fetch_data('UPDATE `users`
                            SET `users`.`post_ban` = "0", `users`.`tban_time` = `users`.`post_ban`
                            WHERE `users`.`id` = '.$userdata[0]['id'].' LIMIT 1') === false) {
                            throw new Exception('There was an error trying to remove tavern ban!');
                        }
                    }
                }
                else { // Post Ban Indicator is not Set
                    if($userdata[0]['tban_time'] <= $this->load_time) { // Tavern Ban Timer has Expired
                        // Update User Tavern Ban Timer upon Successful Login
                        if($GLOBALS['database']->fetch_data('UPDATE `users`
                            SET `users`.`tban_time` = "0"
                            WHERE `users`.`id` = '.$userdata[0]['id'].' LIMIT 1') === false) {
                            throw new Exception('There was an error trying to remove the tavern ban timer!');
                        }
                    }
                    else { // Tavern Ban Still in Effect
                        // Update User Tavern Ban Timer upon Successful Login
                        if($GLOBALS['database']->fetch_data('UPDATE `users`
                            SET `users`.`post_ban` = "1"
                            WHERE `users`.`id` = '.$userdata[0]['id'].' LIMIT 1') === false) {
                            throw new Exception('There was an error trying to update the tavern ban indicator!');
                        }
                    }
                }
            }
            else { // Time indicates permanent tavern ban
                if($userdata[0]['post_ban'] !== '1') { // Tavern Ban Indicator wasn't set
                    // Update User Permanent Tavern Ban Status upon Successful Login
                    if($GLOBALS['database']->execute_query('UPDATE `users`
                        SET `users`.`post_ban` = "1"
                        WHERE `users`.`id` = '.$userdata[0]['id'].' LIMIT 1') === false) {
                        throw new Exception('There was an error trying to update the tavern ban indicator!');
                    }
                }
            }
        }
        elseif($userdata[0]['post_ban'] === '1') { // All Ban Timers are Cleared, but Post Ban Indicator is Not Fixed
            // Update User Permanent Tavern Ban Status upon Successful Login
            if(($GLOBALS['database']->execute_query('UPDATE `users`
                SET `users`.`post_ban` = "0"
                WHERE `users`.`id` = '.$userdata[0]['id'].' LIMIT 1')) === false) {
                throw new Exception('There was an error trying to update the tavern ban indicator!');
            }
        }

        // Pending User Record Permanent/Regular Ban Catch
           /* if(!()) {
                throw new Exception('10');
            }

            // Pending User Record Permanent/Regular Ban Catch
            $user_permanent_ban_record = $user_ban_record = $user_reduction_record = array();

            // Pending User Record Permanent/Regular Ban Catch
            if($user_record !== '0 rows') {
                for($i = 0; $i < count(array_keys($user_record)); $i++) {
                    switch($user_record[$i]['action']) {
                        case('Ban'): {
                            if($user_record[$i]['duration'] === 'Permanent') {
                                array_push($user_permanent_ban_record, $user_record[$i]);
                            }
                            else {
                                array_push($user_ban_record, $user_record[$i]);
                            }
                        } break;
                        case('Reduction'): {
                            array_push($user_reduction_record, $user_record[$i]);
                        } break;
                    }
                }
            }*/

            /* LEFT JOIN `moderator_log` AS `Permanent` ON (Permanent.`action` = "ban"
                        AND Permanent.`duration` = "Permanent" AND Permanent.`uid` = `users`.`id`)
                    LEFT JOIN `moderator_log` AS `Ban` ON (Ban.`action` = "Ban" AND Ban.`uid` = `users`.`id`)
                    LEFT JOIN `moderator_log` AS `Extension` ON (Ban.`action` = "Ban" AND Ban.`uid` = `users`.`id`)*/

         //   $bandata = $GLOBALS['database']->fetch_data("SELECT * FROM `moderator_log`
           //             WHERE `action` = 'ban' AND `uid` = '" . $logindata[0]['id'] . "' ORDER BY `time` DESC LIMIT 1");
    }

    // Checking terms
    private function checkTOS() {

        // Check if we're accepting the terms
        self::doAcceptTOS();

        // Check if the terms are accepted
        if($this->login_data[0]['tos_agree'] === "0") {
            throw new Exception("TOSagree");
        }
    }

    // Accepting terms
    private function doAcceptTOS() {

        if(isset($_POST['Accept'])) {
            // Update database
            if($GLOBALS['database']->execute_query('UPDATE `users`
                SET `users`.`tos_agree` = "1"
                WHERE `users`.`id` = '.$this->login_data[0]['id'].' LIMIT 1') === false) {
                throw new Exception("Error accepting the new terms!");
            }

            // instant update
            $this->login_data[0]['tos_agree'] = "1";
        }
    }

    // User has a weak password, let him change it
    private function acceptTosForm() {

        // Include library
        require_once(Data::$absSvrPath."/ajaxLibs/staticLib/markitup.bbcode-parser.php");

        // Message
        if(!($page = $GLOBALS['database']->fetch_data("SELECT * FROM `information_pages`
            WHERE `information_pages`.`name` = 'Terms of Service'"))) {
            throw new Exception('There was an error trying to obtain Terms of Service Update!');
        }

        $message = ($page !== "0 rows") ? BBCode2Html($page[0]['content']) : "";

        // Create the input form
        $GLOBALS['page']->UserInput("<b><i>Our terms of service have been updated.
            To continue playing the game, <br>it is required
            that you accept these updated terms.</i></b><br><br>".$message,
            "Update of Terms of Service",
            array(
                // A select box
                array(
                    "type" => "hidden",
                    "inputFieldName" => "login_password",
                    "inputFieldValue" => $_POST['login_password']
                ),
                array(
                    "type" => "hidden",
                    "inputFieldName" => "lgn_usr_stpd",
                    "inputFieldValue" => $_POST['lgn_usr_stpd']
                )
            ),
            array(
                "href" => "?id=".$_GET['id'],
                "submitFieldName" => "Accept",
                "submitFieldText" => "Accept"
            ),
            false ,
            "trainingForm"
        );
    }

    // User has a weak password, let him change it
    private function changePasswordForm(){

        // Create the input form
        $GLOBALS['page']->UserInput("It has been required that you change your password. Passwords in core 3 are encrypted using much very
            strong algorithms, thereby making your information very secure should our databases be compromised.
            Even so, please do not chose an easy-to-crack password - always follow good password pracise.",
            "Login System",
            array(
                // A select box
                array(
                    "infoText" => "Enter Old Password",
                    "inputFieldName" => "old_password",
                    "type" => "password",
                    "inputFieldValue" => ""
                ),
                array(
                    "infoText" => "Enter New Password",
                    "inputFieldName" => "new_password",
                    "type" => "password",
                    "inputFieldValue" => ""
                ),
                array(
                    "type" => "hidden",
                    "inputFieldName" => "login_password",
                    "inputFieldValue" => $_POST['login_password']
                ),
                array(
                    "type" => "hidden",
                    "inputFieldName" => "lgn_usr_stpd",
                    "inputFieldValue" => $_POST['lgn_usr_stpd']
                )
            ),
            array(
                "href" => "?id=".$_GET['id'] ,
                "submitFieldName" => "Submit",
                "submitFieldText" => "Submit"),
            false ,
            "trainingForm"
        );
    }


    // Give a new password to the user
    private function giveNewPassword() {

        // Check if any of this needs to be run
        if(!empty($this->login_data[0]['password'])) {

            // Check if we're looking to change the password, or show form
            if(!isset($_POST['old_password'], $_POST['new_password'])) { throw new Exception("WeakPassword"); }

            // Check old password
            if($this->login_data[0]['password'] !== md5($_POST['old_password'])) {
                throw new Exception("Your old password did not match the one currently in the database");
            }

            // Encrypt new password
            $newPass = $this->encryptPassword($_POST['new_password'], $this->login_data[0]['join_date']);

            // Update database
            if($GLOBALS['database']->execute_query('UPDATE `users`
                SET `users`.`salted_password` = "'.$newPass.'",
                    `users`.`password` = ""
                WHERE `users`.`id` = '.$this->login_data[0]['id'].' LIMIT 1') === false) {
                throw new Exception("New password change has failed!");
            }

            // Update further login variables
            $this->login_data[0]['password'] = "";
            $this->login_data[0]['salted_password'] = $newPass;
            $_POST['login_password'] = $_POST['new_password'];
        }
    }

    // Login box
    private function show_loginbox() {

        if (!isset($_POST['lgn_usr_stpd'])) {
            // Setup to login
            $GLOBALS['template']->assign('OPTIONS', 'Welcome back, Guest');
            $GLOBALS['template']->assign('USERNAME', '<input type="text" size="8" class="textfield" name="lgn_usr_stpd" />');
            $GLOBALS['template']->assign('PASSWORD', '<input type="password" size="8" class="textfield" name="login_password" />');
            $GLOBALS['template']->assign('SUBMIT', '<input type="submit" class="button" name="LoginSubmit" value="Submit" />');
            $GLOBALS['template']->assign('widgetLoad', './templates/loginbox.tpl');
            $GLOBALS['template']->assign('menuLoad', './templates/screenshots.tpl');
        }
        else {
            $GLOBALS['template']->assign('widgetLoad', './templates/reCaptcha/reCaptchaInfo.tpl');
            $GLOBALS['template']->assign('menuLoad', './templates/screenshots.tpl');
            $GLOBALS['template']->assign('hide_left_bar', true);
        }
    }
}