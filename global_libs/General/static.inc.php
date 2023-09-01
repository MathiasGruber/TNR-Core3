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

abstract class functions {

    public static function startPageTime(){
        $mtime = explode(" ", microtime());
        $GLOBALS['starttime'] = $mtime[1] + $mtime[0];
        return $GLOBALS['starttime'];
    }

    // Log the page time
    public static function logPageTime( $type = "UserPageview" ){

        // Logging of parsetimes, disabled for now
        $mtime = explode(" ", microtime());
        $endtime = $mtime[1] + $mtime[0];
        $totaltime = round(($endtime - $GLOBALS['starttime']), 4);

        // Timings
        if( Data::$debugObjectTimes == true ){

            // Page load times
            if( $totaltime > Data::$objectTimeLimit  ){
                $url = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "";
                $url = str_replace("/ajaxLibs/mainBackend.php", "", $url);
                $id = isset($_GET['id']) ? $_GET['id'] : 0;
                $GLOBALS['database']->execute_query("
                    INSERT INTO `log_tempObjectLogger`
                        (`name`, `objectSize`, `time`, `pageID`,`type`,`data`)
                    VALUES
                        ('PageLoadTime', ".$totaltime.", ".( $mtime[1]-ceil($totaltime) ).", ".$id.", '".$type."','".$url."');");
            }
        }

        // Return the time
        return $totaltime;
    }

    // Check if call is from mobile API
    public static function isAPIcall(){
        if( isset($GLOBALS['returnJson']) && $GLOBALS['returnJson'] == true ){
            return true;
        }
        return false;
    }

    // Function for completely removing session data
    public static function removeSessionData(){
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        $_SESSION = array();
    }

    // Check active session if it's valid
    public static function checkActiveSession() {
        if(!isset($_SESSION['uid']) || empty($_SESSION['uid']) || !ctype_digit($_SESSION['uid'])) {
            $_SESSION = array();
            throw new Exception("Session no longer valid. You may have been logged out.");
        }
    }

    // Retrieve a global event
    public static function getGlobalEvent( $eventName ){
        if( isset($GLOBALS['globalevents']) && !empty($GLOBALS['globalevents']) ){
            foreach( $GLOBALS['globalevents'] as $event ){
                if( $event['identifier'] == $eventName && $event['active'] == "yes"){
                    return $event;
                }
            }
        }
        return false;
    }

    // Get a timestamp
    public static function getTimestamp() {
        return cachefunctions::getHighestTimestamp();
    }

    // Redirect Link using JavaScript
    public static function redirect() {
        echo "<script>window.location.href='?id=" . $_GET['id'] . "';</script>";
    }

    // Whitespace Remover
    // Stricter than "trim" function which only removes extra whitespace
    public static function ws_remove($string = '') { // Return replaced string with whitespace removed
        return preg_replace('/\s/', '', $string);
    }

    // Add s to plural entry
    public static function pluralize($text, $num) {
        return (($num > 1) ? $text . "s" : $text);
    }

    // Shorthand Function for General Purpose Inner Class Status Check
    public static function user_status_check($user_status) {
        return in_array($user_status, array('awake', 'asleep'), true);
    }

    public static function username_color($user_group, $username) {
        $rainbow_users = array('Rei');
        
        if( !isset($GLOBALS['returnJson']) || $GLOBALS['returnJson'] != true )
            if(in_array($username, $rainbow_users))
                $user_group = 'Rainbow';

        if($user_group == 'EventMod')
        {
            $user = $GLOBALS['database']->fetch_data('SELECT `baby_mode` FROM `users_statistics` INNER JOIN `users` ON (`users`.`id` = `users_statistics`.`uid`) WHERE `username` = \''.$username.'\'');
            if(is_array($user) && $user[0]['baby_mode'] == 'yes')
                $user_group = 'baby';
        }

        return (isset(Data::$USERNAME_COLORS[$user_group]) ?
            str_ireplace('%USERNAME%', $username, Data::$USERNAME_COLORS[$user_group]) : $username);
    }

    // Shorthand Function for General Purpose Inner Class Status Redirect
    public static function user_status_redirect($user_status) {
        if ($user_status === 'hospitalized') {
            echo "<script>window.location.href='?id=34';</script>";
        } // Redirect to Hospital
        else {
            echo "<script>window.location.href='?id=50';</script>";
        } // Redirect to Combat Page
    }

    public static function actual_calc_regen($user) {

        // Rank modifier. Keep it in this format since it makes updates easy!
        switch ($user[0]['rank_id']) {
            case('2'): $rankmodifier = 15;
                break; // Genin
            case('3'): $rankmodifier = 100;
                break; // Chuunin
            case('4'): $rankmodifier = 100;
                break; // Jounin
            case('5'): $rankmodifier = 100;
                break; // Special Jounin
            default: $rankmodifier = 0;
                break; // Lol who changed the rank?
        }

        $villageregen = ($user[0]['regen_level'] * ($rankmodifier / 100));

        // Bonus village regeneration from loyalty
        if( $user[0]['activateBonuses'] == "yes" ){
            if (abs($user[0]['vil_loyal_pts']) >= 200) {
                $villageregen *= 1.1;
            }
        }

        // War regen boost, lasts 7 days after winning war
        $warBoost = ($user[0]['warRegenBoostTime'] > (time() - 7 * 24 * 3600)) ? 1.1 : 1;

        // PVP regen
        $pvp_regen_mod = ($user[0]['avg_pvp'] === '0') ? 0 : (3 * round($user[0]['pvp_experience'] / $user[0]['avg_pvp'], 1));
        $pvp_regen_mod = ($pvp_regen_mod > 50) ? 50 : $pvp_regen_mod;

        // Determine regen
        $regen = $user[0]['regen_rate'] * (1 + $pvp_regen_mod / 100) + $villageregen;
        $regen = $regen * (($user[0]['regen_boost'] / 100) + 1);
        $regen += $user[0]['item_regen_boost'];
        $regen *= $warBoost;

        if ($user[0]['regen_cooldown'] !== '0') {
            if ($user[0]['regen_cooldown'] > time()) {
                $factor = $user[0]['regen_cooldown'] - time();
                switch (true) {
                    case($factor < (24 * 3600)): $modifier = 75;
                        break;
                    case($factor < (3 * 24 * 3600)): $modifier = 50;
                        break;
                    case($factor < (4 * 24 * 3600)): $modifier = 25;
                        break;
                    default: $modifier = 100;
                        break;
                }
                $regen = ($regen / 100) * $modifier;
            }
        }

        // Check for global event modifications
        if( $event = functions::getGlobalEvent("IncreasedRegen") ){
            if( isset( $event['data']) && is_numeric( $event['data']) ){
                $regen *= $event['data'] / 100;
            }
        }

        //if in ocean set regen to zero
        if( isset($GLOBALS['current_tile'][1]) && $GLOBALS['current_tile'][1] == 'ocean')
            $regen = 0;

        // Return regen
        return $regen;
    }

    public static function calc_regen($user) {

    }

    public static function calc_strength_factor( $user ){

        // Calculate highest offence
        $highestOffence = 0;
        foreach( array("nin_off", "gen_off", "tai_off", "weap_off") as $offStat ){
            $highestOffence = ($user[ $offStat ] > $highestOffence) ? $user[ $offStat ] : $highestOffence;
        }

        // Calculate total defence
        $avgDefence = $highestDef = $lowestDef = 0;
        foreach( array("nin_def", "gen_def", "tai_def", "weap_def") as $defStat ){
            $avgDefence += $user[ $defStat ];
            $highestDef = ($user[ $defStat ] > $highestDef) ? $user[ $defStat ] : $highestDef;
            $lowestDef = ($user[ $defStat ] < $lowestDef || $lowestDef == 0) ? $user[ $defStat ] : $lowestDef;
        }
        $avgDefence = ($avgDefence + $highestDef - $lowestDef)*0.25;

        // Calculate total defence
        $avgGens = $highestGens = $lowestGens = 0;
        foreach( array("strength", "intelligence", "speed", "willpower") as $gen ){
            $avgGens += $user[ $gen ];
            $highestGens = ($user[ $gen ] > $highestGens) ? $user[ $gen ] : $highestGens;
            $lowestGens = ($user[ $gen ] < $lowestGens || $lowestGens == 0) ? $user[ $gen ] : $lowestGens;
        }
        $avgGens = 10 * ($avgGens + $highestGens - $lowestGens)*0.25;

        // Calculate strength factor
        $strengthFactor = round(($user["max_health"] + $highestOffence + $avgDefence + $avgGens) / 1000, 2);
        if( $strengthFactor > 7500 ){
            $strengthFactor = 7500;
        }

        // Return strength factor
        return $strengthFactor;
    }

    // Get current GET link
    public static function get_current_link($selector = false) {
        $link = "?";
        foreach ($_GET as $key => $value) {
            if ($selector === false || in_array($key, $selector, true)) {
                $link .= ($link === "?") ? $key . "=" . $value : "&" . $key . "=" . $value;
            }
        }
        return $link;
    }

    // Function for encrypting passwords
    public static function encryptPassword($pass, $salt) {
        $crypt = explode("$", crypt($pass, '$6$' . $salt . 'Some7654XHpwdShit$'));
        return $crypt[3];
    }

    // A function for logging a user action in the database.
    public static function log_user_action($uid, $action, $info = "", $notes = "") {
        $time = isset($GLOBALS['user']->load_time) ? $GLOBALS['user']->load_time : time();
        if ($GLOBALS['database']->execute_query("INSERT INTO `users_actionLog`
                (`uid`, `time`, `action`, `attached_info`,`notes`)
            VALUES
                (" . $uid . ", " . $time . ", '" . $action . "', '" . $info . "', '" . $notes . "')") === false) {
            throw new Exception('An error occurred inserting User Action into Log!');
        }
    }

    // A function for logging user village changes
    public static function log_village_changes($uid, $startVillage, $endVillage, $info = "") {
        if ($GLOBALS['database']->execute_query(
            "INSERT INTO `log_villageChanges` (`uid`, `time`, `startVillage`, `endVillage`, `reason`)
            VALUES (" . $uid . ", UNIX_TIMESTAMP(), '" . $startVillage . "', '" . $endVillage . "', '" . $info . "')") === false) {
            throw new Exception('An error occurred inserting village change into Log!');
        }
    }

    // A function for logging a user action in the database.
    public static function log_event_action($uid, $entry, $message , $time ) {
        $GLOBALS['database']->execute_query('INSERT INTO `events_log` (`event_id`, `uid`, `title`, `info`, `time`)
            VALUES ("'.$entry['id'].'", "'.$uid.'", "'.$entry['name'].'", "'.$message.'", "'.$GLOBALS['user']->load_time.'");');
    }

    // Add to string from data
    public static function arrayToString($array) {
        $string = "";
        if (is_array($array)) {
            foreach ($array as $element) {
                $string .= self::arrayToString($element);
            }
        } else {
            $string = $array;
        }
        return $string;
    }

    // Creates a secret hash, usefull for backend interactions
    public static function createHash($arrayKeys) {
        // Start string. Secret identifier + Array to String Hash
        return sha1("XHsg" . self::arrayToString($arrayKeys));
    }

    // Common function for creating session token for talking with the backend
    public static function getToken() {
        if (isset($_SESSION['uid'])) {
            return self::createHash(array("uid" => $_SESSION['uid'], "login_id" => $GLOBALS['userdata'][0]['login_id']));
        }
        return false;
    }

    // Checks the secret hash on the backend. Times out after 3 seconds!
    public static function checkHash($arrayKeys, $hash) {
        return ((self::createHash($arrayKeys) === $hash) ? true : false);
    }

    //			BBCode Functions
    public static function color_BB($contents) {
        $i1 = array('/\[color=(.+?)\](.+?)\[\/color\]/si');
        $i2 = array('<span style="color: $1;">$2</span>');
        return preg_replace($i1, $i2, $contents);
    }

    public static function parse_BB($text, $dirCorrection = "") {
        require_once($dirCorrection . "./ajaxLibs/staticLib/markitup.bbcode-parser.php");
        return BBCode2Html($text);
    }

    public static function check_BB($contents) {
        //          Check BBtag validation
        $check = array('[b]', '[i]', '[u]', '[list]', '[mail]');
        $check_close = array('[/b]', '[/i]', '[/u]', '[/list]', '[/mail]');
        for ($i = 0, $size = count($check); $i < $size; $i++) {
            if (substr_count($contents, $check[$i]) > substr_count($contents, $check_close[$i])) {
                if ($check[$i] === '[mail]') {
                    $contents = str_replace($check[$i], '', $contents);
                } else {
                    $contents .= $check_close[$i];
                }
            }
        }
        return $contents;
    }

    // Preps the content to be stored in the database
    public static function store_content($contents) {

        // General things
        $contents = strip_tags($contents);
        $contents = htmlspecialchars($contents);
        $contents = self::check_BB(stripslashes($contents));
        $contents = str_replace("--#", "", $contents);
        $contents = str_replace("--", "", $contents);
        $contents = str_replace("/*", "", $contents);
        $contents = str_replace("bleach-game", "*", $contents);
        $contents = str_replace("chaotic-souls", "*", $contents);
        $contents = str_replace('&nbsp;', '', $contents);
        $contents = str_replace('[/url]', ' [/url]', $contents);
        $contents = str_replace('[/URL]', ' [/URL]', $contents);
        $contents = addslashes($contents);
        $contents = trim($contents);
        $contents = self::insert_linebreaks($contents);

        // Remove javascript crap
        $contents = str_replace("PHPSESSID", "&nbsp;", $contents);
        $contents = str_replace("javascript:document", "&nbsp;", $contents);
        $contents = str_replace("document.cookie", "&nbsp;", $contents);
        return $contents;
    }

    public static function insert_linebreaks($message) {
        if (strlen($message) > 50) {
            if (stristr($message, ' ') || stristr($message, '<br>')) {
                for ($i = 0, $tmp = explode('<br>', $message), $size = count($tmp); $i < $size; $i++) {
                    if (strlen($tmp[$i]) > 50) {
                        if (!stristr($tmp[$i], ' ')) { //  Cut string (preferably on a space)
                            $tmp[$i] = implode('<br>', str_split($tmp[$i], 50));  //  No space exists.
                        }
                    }
                }
                return implode('', $tmp);
            }
            return implode('<br>', str_split($message, 50));
        }
        return $message;
    }

    public static function getUserImage($folder, $filename) {
        foreach (Data::$IMG_TYPES as $type) {
            $bucketpath = $folder . $filename . $type;
            $s3path = 's3://' . MEDIA_BUCKET . $bucketpath;        
            if (file_exists($s3path)) {
                return MEDIA_ROOT . $bucketpath;
            }
        }
        return './images/default_avatar.png';
    }

    public static function getAvatar($userID) {
        return self::getUserImage("/avatars/", $userID);
    }

    public static function getVillageAvatar($villageName) {
        return self::getUserImage("/villages/", $villageName);
    }

    //	Convert seconds to days / hours / minutes / seconds
    public static function convert_time($time, $name, $refresh = 'true', $regen = 1, $showTimer = "Show", $refreshLink = "reload", $endMobileTxts=true) {
        $seconds = $time % 60;
        $rest = floor(($time - $seconds) / 60); //	Raw minutes
        $minutes = $rest % 60;
        $rest = floor(($rest - $minutes) / 60);
        $hours = $rest % 24;
        $rest = floor(($rest - $hours) / 24);
        $days = $rest;

        $string = "";
        if ($days > 0) {
            $string .= $days . ' Days ';
        }
        if ($hours > 0) {
            $string .= $hours . ' Hours ';
        }
        if ($minutes > 0) {
            $string .= $minutes . ' Minutes ';
        }
        if ($seconds > 0) {
            $string .= $seconds . ' Seconds ';
        }

        // If on mobile, don't return javascript
        if( isset($GLOBALS['returnJson']) && $GLOBALS['returnJson'] == true ){
            $return = ($endMobileTxts == true) ? "</text>" : "";
            $return .= '<countdown time="'.$time.'" reload="true" prepend="" postpend="" format="true"></countdown>';
            $return .= ($endMobileTxts == true) ? "<text>" : "";        
            return $return;
        }

        // Return for web
        if(!isset($GLOBALS['mf']) || $GLOBALS['mf'] != 'yes')
		{
            return ('<script type="text/javascript">
                        $(document).ready(function(){ updateTimer("' . $name . '", ' . $time . ', "' . $refresh . '", ' . $regen . ', "' . $showTimer . '", "' . $refreshLink . '"); });
                     </script>
                     <noscript>' . $string . '</noscript>' . 
                     (($showTimer === "Show") ? '<span id="' . $name . '"></span>' : ''));
		}
        else
        {
            return( '<span id="'.$name.'" class="count-down" data-show="'.$showTimer.'" data-regen="'.$regen.'" data-refresh="'.$refresh.'" data-callback="'.( $time > 1 ? 'refreshPage' : 'doNothing').'" data-timer-seconds="'.$time.'" title="'.date("F j, Y, g:i a",time()+$time).'"></span>' );
        }
    }

    public static function convert_PM_time($time) {
        $seconds = $time % 60;
        $rest = floor(($time - $seconds) / 60);    //    Raw minutes
        $minutes = $rest % 60;
        $rest = floor(($rest - $minutes) / 60);
        $hours = $rest % 24;
        $rest = floor(($rest - $hours) / 24);
        $days = $rest;

        switch (true) {
            case($days > 0): return $days . ' '.self::pluralize("Day",$days).' Ago';
                break;
            case($hours > 0): return $hours . ' '.self::pluralize("Hour",$hours).' Ago';
                break;
            case($minutes > 0): return $minutes . ' '.self::pluralize("Minute",$minutes).' Ago';
                break;
            case($seconds > 0): return $seconds . ' '.self::pluralize("Second",$seconds).' Ago';
                break;
            default: return 'Just Now';
                break;
        }
    }

    // user AI for battle
    public static function make_ai($charArray) {
        // ID is added to AI so that it is not confused with Player IDs
        $ai_traits = array('original_id' => $charArray['id'],
            'id' => ($charArray['id'] + random_int(15000000, 16000000)),
            'is_ai' => 1,
            'rank_id' => round($charArray['level'] / 10),
            'username' => $charArray['name'],
            'max_health' => $charArray['life'],
            'cur_health' => $charArray['life'],
            'cur_cha' => $charArray['chakra'],
            'max_cha' => $charArray['chakra'],
            'cur_sta' => $charArray['chakra'],
            'max_sta' => $charArray['chakra'],
            'money' => $charArray['level'] * 10,
            'gender' => isset($charArray['gender']) ? $charArray['gender'] : "none");

        foreach ($ai_traits as $key => $val) {
            $charArray[$key] = $val;
        }

        return $charArray;
    }

    public static function checkIfInBattle($uid, $battleID = false, $transactionSafe = false) {

        // Start the query
        $query = 'SELECT * FROM `multi_battle` WHERE ';



        // Select on the battle table
        if (!empty($battleID)) {

            // Direct battle ID
            $query .= '`multi_battle`.`id` = ' . $battleID . ' LIMIT 1';
        } else {

            // User ID, get battle ID first
            $user = $GLOBALS['database']->fetch_data("SELECT `battle_id` FROM `users` WHERE `id` = '" . $uid . "' LIMIT 1");
            if ($user !== "0 rows" && $user[0]['battle_id'] > 0) {

                // Get battle based on battle ID
                $query .= '`multi_battle`.`id` = ' . $user[0]['battle_id'] . ' LIMIT 1';
            } else {
                return "0 rows";
            }
        }

        // Make transaction safe
        if ($transactionSafe === true) {
            $query .= ' FOR UPDATE';
        }

        // Run query
        if (!($battle = $GLOBALS['database']->fetch_data($query))) {
            throw new Exception('An error occurred checking battle information!');
        }

        // Confirm that user is in the battle if it's found on battle ID
        if (!empty($battleID) && $battle !== "0 rows") {
            $userIds = array_filter(explode("|||", $battle[0]["user_ids"]));
            $oppIds = array_filter(explode("|||", $battle[0]["opponent_ids"]));
            $battle = (!in_array($uid, $userIds, true) && !in_array($uid, $oppIds, true)) ? '0 rows' : $battle;
        }

        // Return data
        return $battle;
    }

    // Add a single user ID / or AI to a battle.
    // This does not secure transactin security, this must be managed outside the function
    public static function addIntoBattle(
            $uid,                   // User ID to insert into battle
            $battleData,            // Data for the battle we want to insert into
            $addSide,               // Side of the battle to add to
            $userData,              // User data to insert. Only needed for AIs
            $updateStatus = true,   // As per default, the user status is updated to combat
            $updateTime = true      // On default, update the last_action on the battle so user has time to act
    ) {
        // Create new string for battle id-column
        $idColumn = $addSide . "_ids";
        $helpColumn = $addSide . "_help";
        $newBattleIDlist = $battleData[$idColumn] . $uid . "|||";

        // Time update if any
        $timeUpdate = ($updateTime == true) ? ", `multi_battle`.`last_action` = ".$GLOBALS['user']->load_time : "";

        // Update battle row
        if (!($GLOBALS['database']->execute_query("
            UPDATE `multi_battle`
            SET
                `multi_battle`.`" . $idColumn . "` = '" . $newBattleIDlist . "' ,
                `multi_battle`.`" . $helpColumn . "` = `multi_battle`.`" . $helpColumn . "` + 1,
                `multi_battle`.`" . $addSide . "s_added` = `multi_battle`.`" . $addSide . "s_added` + 1
                ".$timeUpdate."
            WHERE `multi_battle`.`id` = " . $battleData['id'] . " LIMIT 1"))) {
            throw new Exception("Battle Update Failed!");
        }

        // Update user status

        if ($updateStatus === true) {
            if (!($GLOBALS['database']->execute_query('SELECT `users`.`id` FROM `users`
                WHERE `users`.`id` = ' . $uid . ' AND `users`.`status` = "awake" LIMIT 1 FOR UPDATE'))) {
                throw new Exception("User Awake Lock Failed!");
            }

            if (!($GLOBALS['database']->execute_query('UPDATE `users`
                SET `users`.`status` = "combat", `users`.`battle_id` = ' . $battleData['id'] . '
                WHERE `users`.`id` = ' . $uid . ' LIMIT 1'))) {
                throw new Exception("Update user Query Failed!");
            }

            // Instant update
            if (isset($GLOBALS['userdata'][0]['status'])) {
                $GLOBALS['Events']->acceptEvent('status', array('new'=>'combat', 'old'=>$GLOBALS['userdata'][0]['status'] ));

                $GLOBALS['userdata'][0]['status'] = "combat";
                $GLOBALS['template']->assign('userStatus', 'combat');
                $GLOBALS['userdata'][0]['battle_id'] = $battleData['id'];
            }
        }

        // End all harvesting for user
        cachefunctions::endHarvest($uid);
    }

    // Creates a new battle and insert it
    // As per default, this function selects the uid FOR UPDATE,
    // and ensures that the user is awake and not in battle already.
    // In all cases where PVP is used, the $customTrans should be
    // set to true, and then custom transaction security should be
    // implemented. The default one is mainly useful just for cases
    // where we set the user into a battle with an AI.
    public static function insertIntoBattle(
            $uids, // Array of user IDs
            $oids, // Array of opponent IDs
            $battle_type, // Type of battle
            $mission_id, // Mission ID
            $usrData, // User DATA Array
            $oppData, // Opponent DATA Array
            $updateStatus = true, // Default is to update the combat status of the users
            $customTrans = false, // Default is that this function ensures all transaction security.
            $disableTrans = false, // Explicitly disable transaction calls
            $rankLimitations = ""
    ) {
        // Prepare insert data
        $insertUids = "|||";
        $insertOids = "|||";
        $insertUdata = array();
        $insertOdata = array();
        $ai_ids = array();

        // Prepare combat-update query
        $whereQuery = "";
        $userCount = 0;

        foreach ($uids as $id) {
            $insertUids .= $id . "|||";
        }
        foreach ($oids as $id) {
            $insertOids .= $id . "|||";
        }
        foreach ($usrData as $key => $data) {
            $insertUdata["data"][$data['id']] = $data;
            $insertUdata["data"][$data['id']]['bloodlineEffect'] = array();
            $insertUdata['ids'][] = $data['id'];
            if( isset($data['is_ai']) ){
                $ai_ids[] = $data['id'];
                if( isset($data['gender']) && $data['gender'] == "random" ){
                    $insertUdata["data"][$data['id']]['gender'] = random_int(1,2)==1 ? "male" : "female";
                    $insertUdata["data"][$data['id']]['money'] = 0;
                }
            }
        }
        foreach ($oppData as $key => $data) {
            $insertOdata["data"][$data['id']] = $data;
            $insertOdata["data"][$data['id']]['bloodlineEffect'] = array();
            $insertOdata['ids'][] = $data['id'];
            if( isset($data['is_ai']) ){
                $ai_ids[] = $data['id'];
                if( isset($data['gender']) && $data['gender'] == "random" ){
                    $insertOdata["data"][$data['id']]['gender'] = random_int(1,2)==1 ? "male" : "female";
                    $insertOdata["data"][$data['id']]['money'] = 0;
                }
            }
        }


        foreach ($uids as $id) {
            $whereQuery .= ($whereQuery === "") ? "`users`.`id` = " . $id : " OR `users`.`id` = " . $id;
            $userCount += 1;

            // End all harvesting for user
            cachefunctions::endHarvest($id);
        }
        foreach ($oids as $id) {
            $whereQuery .= ($whereQuery === "") ? "`users`.`id` = " . $id : " OR `users`.`id` = " . $id;
            $userCount += 1;

            // End all harvesting for user
            cachefunctions::endHarvest($id);
        }

        // Check if custom transaction settings are used outside this function or not
        if ($customTrans === false) {

            // Make this thing transaction Safe
            if( $disableTrans == false ){
                $GLOBALS['database']->transaction_start();
            }

            // Loop through all the users & opponents who are not AI
            foreach (array("uids", "oids") as $idIdentifier) {
                foreach (${$idIdentifier} as $id) {
                    if ( !in_array($id, $ai_ids) ) {

                        // Select the user & battle for update
                        $localUser = $GLOBALS['database']->fetch_data("SELECT `users`.`status` FROM `users`
                            WHERE `users`.`id` = " . $id . " LIMIT 1 FOR UPDATE");

                        // Check user
                        if( isset($localUser) && !empty($localUser) && $localUser !== "0 rows" ){
                            // Check that user data is good
                            if ($localUser[0]['status'] !== "awake") {
                                throw new Exception("The user is not awake and ready to battle");
                            }

                            // Check that battle data is good
                            if (self::checkIfInBattle( $id ) !== "0 rows") {
                                throw new Exception("This user is already in battle");
                            }
                        }
                        else{
                            throw new Exception("User could not be retrieved for battle right now");
                        }
                    }
                }
            }
        }

        // Insert the battle into the database
        if (!($GLOBALS['database']->execute_query("
            INSERT INTO `multi_battle`
                (`user_ids`,
                 `opponent_ids`,
                 `battle_type`,
                 `mission_id`,
                 `rankLimits`,
                 `last_action`,
                 `user_data`,
                 `opponent_data`)
            VALUES (
                '" . $insertUids . "',
                '" . $insertOids . "',
                '" . $battle_type . "',
                '" . $mission_id . "',
                '" . $rankLimitations . "',
                '" . self::getTimestamp() . "',
                '" . base64_encode(serialize($insertUdata)) . "',
                '" . base64_encode(serialize($insertOdata)) . "')")
        )) {
            throw new Exception("Battle Insertion Failed");
        }

        // User update
        $battleID = $GLOBALS['database']->get_inserted_id();

        // Update the user status, if that is required
        if ($updateStatus === true) {
            if (!($GLOBALS['database']->execute_query("UPDATE `users`
                SET `users`.`status` = 'combat', `database_fallback` = 0, `users`.`battle_id` = " . $battleID . "
                WHERE " . $whereQuery . " LIMIT " . $userCount))) {
                throw new Exception('User Battle Update Failed!');
            }

            if (isset($GLOBALS['userdata'][0]['status'])) {
                $GLOBALS['Events']->acceptEvent('status', array('new'=>'combat', 'old'=>$GLOBALS['userdata'][0]['status'] ));

                $GLOBALS['userdata'][0]['status'] = "combat";
                $GLOBALS['template']->assign('userStatus', 'combat');
                $GLOBALS['userdata'][0]['database_fallback'] = 0;
                $GLOBALS['userdata'][0]['battle_id'] = $battleID;
            }
        }

        // If this is the default usage (i.e. AI, then commit transaction)
        if ($customTrans === false && $disableTrans === false ) {
            $GLOBALS['database']->transaction_commit();
        }

        return $battleID;
    }

    public static function curPageURL() {
        $pageURL = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on") ? 'https' : 'http';
        $pageURL .= "://" . (($_SERVER["SERVER_PORT"] !== "80") ? ($_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"]) : $_SERVER["SERVER_NAME"]);
        return $pageURL;
    }

    public static function getRank($rankID, $village) {
        if($rankID === 1 || $rankID === '1') return "Academy Student";
        else if($rankID === 2 || $rankID === '2') return "Genin";
        else if($rankID === 3 || $rankID === '3') return (($village !== "Syndicate") ? "Chuunin" : "Lower Outlaw");
        else if($rankID === 4 || $rankID === '4') return (($village !== "Syndicate") ? "Jounin" : "Higher Outlaw");
        else if($rankID === 5 || $rankID === '5') return (($village !== "Syndicate") ? "Elite Jounin" : "Elite Outlaw");
        else throw new exception("Invalid rank id: ".print_r($rankID, true));
    }


    // Numerical (Low to High) Optimized Merge Sort
    public static function mergesort($data) {

        $size = count($data); // Obtain Size of Array

        if($size > 1) { // Process an Array Dataset greater than 1

            // Find the Middle of the Array Dataset
            $data_middle = round($size / 2, 0, PHP_ROUND_HALF_DOWN);

            // Recursively Call Self on Left Side of the Middle of Dataset
            $data_part1 = self::mergesort(array_slice($data, 0, $data_middle));
            $data_part1_size = count($data_part1); // Find Data Size of Left Side

            // Recursively Call Self on Right Side of the Middle of Dataset
            $data_part2 = self::mergesort(array_slice($data, $data_middle, $size));
            $data_part2_size = count($data_part2); // Find Data Size of Right Side

            $counter1 = $counter2 = 0; // Utilize counters to track Data Retention and Placement

            for ($i = 0; $i < $size; $i++) { // Iterate through Dataset currently being processed and Reassemble
                if($counter1 === $data_part1_size) { // Utilize the 2nd Half if the 1st Half is Done
                    $data[$i] = $data_part2[$counter2];
                    ++$counter2;
                }
                elseif (($counter2 === $data_part2_size)
                    || ($data_part1[$counter1] < $data_part2[$counter2])) {  // Utilize Rest if 2nd Half is Done or 1st Half smaller than 2nd Half
                    $data[$i] = $data_part1[$counter1];
                    ++$counter1;
                }
                else { // Process the Last Piece of Dataset
                    $data[$i] = $data_part2[$counter2];
                    ++$counter2;
                }
            }
        }
        return $data;
    }

    // Check start/end date
    public static function checkStartEndDates( $entry ){
        if( isset($entry['start_date']) && !empty($entry['start_date']) ){
            if( $entry['start_date'] > $GLOBALS['user']->load_time){
                return false;
            }
        }
        if( isset($entry['end_date']) && !empty($entry['end_date']) ){
            if( $entry['end_date'] < $GLOBALS['user']->load_time){
                return false;
            }
        }
        return true;
    }

}
