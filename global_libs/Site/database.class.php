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

    class database extends mysqli {

        // TNR Server & DB Credentials
        private $MYSQL_SERVER; // Server Name / Location
        private $MYSQL_USER; // DB Username
        private $MYSQL_PASS; // DB Password
        private $MYSQL_DEFAULT_DB; // Default DB Name

        // Affected Rows by INSERTs, UPDATEs, REPLACEs or DELETEs
        public $last_affected_rows = 'none';

        // Number of Queries Executed
        public $queriesRun = 0;

        // Transaction Indicator
        private $hasTransaction = false;

        // MySQLi Database Object Handler
        private $dbHandler;

        // Enable Query Logging
        public $enableLog = true;

        // Accumulated Query Log
        public $queriesLog = "";

        // First Instance Construct (Database Selection/Connection)
        // mysql_db = MySQL Database Name
        // p_connect = Persistent Connection Indicator
        public function __construct($mysql_db = NULL) {

            // Default TNR Server & DB Login Credentials
            $this->MYSQL_SERVER = MYSQL_HOST;
            $this->MYSQL_USER = MYSQL_USER;
            $this->MYSQL_PASS = MYSQL_PASS;
            $this->MYSQL_DEFAULT_DB = MYSQL_NAME;
            $this->MYSQL_PORT = MYSQL_PORT;

            // Set MySQL Database
            if(!isset($mysql_db)) { $mysql_db = $this->MYSQL_DEFAULT_DB; }

            // Check MySQLi Initialization
            if(!$this->dbHandler = mysqli_init()) {
                die('Database Initialization Failed!');
            }

            // Check MySQLi Connection Option Chnages
            if(!$this->dbHandler->options(MYSQLI_OPT_CONNECT_TIMEOUT, 15)) {
                die('Setting Database Connection Timeout Failed!');
            }

            // Check if MySQLi Connection Succeeded
            //
            // Typical Setup Application
            // Server Name: Server Location (or IP Address)
            // Username: MySQL Username
            // Password: MySQL Username Password
            // Database: Database Name
            // Port Number: 3306 (MySQL Database System Port)
            // Socket Location: tmp/mysql.sock (MySQL Unix Socket File)
            // Flag(s): MySQLi Client Compression (Minimize Bandwidth on Server and DB, if separate)

            if(!$this->dbHandler->real_connect($this->MYSQL_SERVER, $this->MYSQL_USER, $this->MYSQL_PASS,
                $mysql_db, $this->MYSQL_PORT, MYSQL_SOCK, MYSQLI_CLIENT_COMPRESS)) {
                    
                $message = "TNR is currently facing issues.  A team of highly skilled ninjas have been dispatched to solve this problem.
                    Sadly they are out of ninja stars so progress may be slow.<br><br>
                    In the meantime, check out our forum at: <a href='http://www.theninja-forum.com'>TheNinja-Forum</a><br><br>";
echo $this->dbHandler->connect_error;
                self::mysql_error_report($message.'Cannot connect to MySQL: '. $this->dbHandler->connect_error.
                    ', Error Number: '.$this->dbHandler->connect_errno);

                die($message);
            }
        }

        // Database Connection Closer
        public function close_connection() {

            // Log Uncommitted Transaction Error
            if($this->hasTransaction) {
                self::mysql_error_report("Unfinished transaction");
            }

            // Close MySQL connection
            if(!$this->dbHandler->close()) {
                die("MySQL Connection Termination Error: " . $this->dbHandler->error . "<br>");
            }
        }

        // MySQLi Database Switch
        public function dbChange($mysql_db = NULL) {

            if(!isset($mysql_db)) { throw new Exception('You did not specify a Database!'); }
            elseif(!$this->dbHandler->select_db($mysql_db)) {
                throw new Exception('Failed to switch to specified Database!');
            }
        }

        // MySQLi Real Escape String (used for methods outside of DB class)
        public function db_escape_string($string) {
            return $this->dbHandler->real_escape_string($string);
        }

        // Return Auto Generated ID (used for methods outside of DB class)
        public function get_inserted_id() {
            return $this->dbHandler->insert_id;
        }

        // Return Executed Query's Last Affected Rows (used for methods outside of DB class)
        public function getAffectedRows() {
            return $this->last_affected_rows;
        }

        // Database Error String (used for methods outside of DB class)
        public function getDBError() {
            return $this->dbHandler->error;
        }

        // Database Error Number (used for methods outside of DB class)
        public function getDBErrno() {
            return $this->dbHandler->errno;
        }

        // MySQL Error Indicator
        // Return False if Empty String (No Error) AND Error Number is 0, Return True Otherwise
        public function getErrorReported() {
            return !(empty($this->dbHandler->error) && empty($this->dbHandler->errno));
        }

        // Set isolation level
        // See http://dev.mysql.com/doc/refman/5.5/en/set-transaction.html for options
        public function setIsolationLevel( $isolationLevel = "SERIALIZE" ){
            $this->dbHandler->query("SET SESSION TRANSACTION ISOLATION LEVEL ".$isolationLevel);
        }

        // Single Query Execution Function
        public function execute_query($query_string, $echoQuery = false, $logging = true, $disable_cleaning = false) {

            // Log Query Action
            if($this->enableLog) {
                $this->queriesLog .= $this->dbHandler->real_escape_string("Running query: ".$query_string."\r\n");
            }

            // Echo the query
            if( $echoQuery ){
                echo "<pre /><br><br>".$query_string;
            }

            // Unsure Why This is Here or Used (Don't Remove unless Explained)
            if(!$disable_cleaning)
                $sql_query = str_replace(array("#", "--"), "", $query_string);
            else
                $sql_query = $query_string;

            // MySQLi Query Note
            // Returns FALSE on failure.
            // For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries, mysqli_query() will return a mysqli_result object.
            // For other successful queries mysqli_query() will return TRUE.
            if (!($query = $this->dbHandler->query($sql_query))) { // Check for Failed Query

                // Log All MySQL Errors
                if($logging && self::getErrorReported()) { // Log MySQL Error
                    self::mysql_error_report($this->dbHandler->real_escape_string($sql_query));
                }
                return false;
            }

            $this->queriesRun++; // Increment Queries Run (Unsure if Needed)

            $this->last_affected_rows = $this->dbHandler->affected_rows; // Affected Rows from Query Execution

            // Grab the SQL Keyword being used (which is the first string word)
            // Ensure Whitespace that is left of string is removed!
            $query_keyword = array_slice(preg_split("/[\s]+/", ltrim($sql_query)), 0, 1, TRUE);
            $query_keyword = array_pop($query_keyword);

            // Check Query for Allowed Keywords
            switch($query_keyword) {
                case('SELECT'): case('SHOW'): { // Check for SELECT, SHOW, DESCRIBE, EXPLAIN
                    return $query; // Return Successful Query
                } break;
                case('UPDATE'): case('INSERT'): case('REPLACE'):{ // Check for UPDATE and INSERT
                    if($this->dbHandler->affected_rows !== -1) { // Check if Error Occurred
                        if($this->dbHandler->affected_rows >= 1) { // Check if Rows were Updated or Not
                            return $query; // Return Successful Query
                        }
                    }
                } break;
                case('DELETE'): { // Check for DELETE
                    if($this->dbHandler->affected_rows !== -1) { // Check if Error Occurred
                        return $query;
                    }
                } break;
                case('ANALYZE'): case('OPTIMIZE'): case('FLUSH'): case('TRUNCATE'): {  // Check for ANALYZE, OPTIMIZE, FLUSH, TRUNCATE
                    return $query;
                } break;
                default: {
                    echo 'Not a Permissible Query Performed!<br>';
                } break;
            }
            return false;
        }


        // Query Data Fetch Function (Use for SELECTs)
        public function fetch_data($query_string, $echoQuery = false) {

            if(!($queryID = self::execute_query($query_string, $echoQuery))) { // Execute Query and Check if Failed
                echo"QUERY: ".$query_string."<br><br>";
                throw new Exception('This is not a data fetch. Query Error.<br><br>'.$this->dbHandler->error.'<br><br>Query: '.$query_string.'<br><br>');
            }
            elseif(get_class($queryID) === 'mysqli_result') { // Check for MySQLi Result Object
                if ($queryID->num_rows > 0) { // Check to see if anything was returned
                    for($i = 0, $size = $queryID->num_rows; $i < $size; $i++) { // Return the rows from the result set
                        if(!($data = $queryID->fetch_assoc())) { // If the fetch happens to fail or go above boundaries
                            self::log_scope_error($query_string); // Log the scope error
                            $queryID->free(); // Free Memory Resources
                            return 0; // Return 0 for System Error
                        }
                        $return_data[$i] = $data; // Store Array into Return Data Object
                    }
                    $queryID->free(); // Free Memory Resources

                    // Log the size of the object if required
                    if( Data::$debugObjectSizes == true ){
                        $size = strlen( serialize($return_data) );
                        if( $size > Data::$objectSizeLimit ){
                            $id = isset($_GET['id']) ? $_GET['id'] : 0;
                            $GLOBALS['database']->execute_query("
                                INSERT INTO `log_tempObjectLogger`
                                    (`data`, `objectSize`, `time`, `pageID`,`type`)
                                VALUES
                                    ('".addslashes($query_string)."', ".$size.", ".time().", ".$id.", 'FetchQuery');");
                        }
                    }
                    return $return_data; // Return Data
                }
                $queryID->free(); // Free Memory Resources
            }
            return '0 rows'; // Return No Results
        }

        // User Data Load Function
        public function load_user($id) {

            // Load Essential User Data Information
            if(!($user = self::fetch_data('SELECT
                `users_timer`.`last_activity`, `users_timer`.`last_activity_ms`,
                `users_timer`.`last_battle`, `users_timer`.`last_regen`, `users_timer`.`last_login`,
                `users_timer`.`jutsu`, `users_timer`.`jutsu_timer`, `users_timer`.`hospital_timer`,
                `users_timer`.`jail_timer`, `users_timer`.`regen_cooldown`, `users_timer`.`cooldown`, `users_timer`.`battle_colldown`, `users_timer`.`arena_cooldown`,
                `users_timer`.`dynamic_signature`, `users_timer`.`read_blue_msg`, `users_timer`.`last_robbing`, `users_timer`.`last_login_streak`,

                `users_timer`.`pageTrack_session_sampleSize`, `users_timer`.`pageTrack_session_sumSamples`, `users_timer`.`pageTrack_session_sumSamplesSquared`,
                `users_timer`.`pageTrack_lifetime_sampleSize`, `users_timer`.`pageTrack_lifetime_sumSamples`, `users_timer`.`pageTrack_lifetime_sumSamplesSquared`,
                `users_timer`.`pageTrack_lifetime_seconds`,

                `users_timer`.`mission_count`, `users_timer`.`mission_collection_time`,
                `users_timer`.`missions_collected`, `users_timer`.`missions_offered`,

                `users_statistics`.`level_id`, `users_statistics`.`level`,
                `users_statistics`.`rank_id`, `users_statistics`.`rank`, `users_statistics`.`baby_mode`,
                `users_statistics`.`user_rank`, `users_statistics`.`reinforcements`,
                `users_statistics`.`pvp_experience`,
                `users_statistics`.`pvp_streak`,`users_statistics`.`experience`,
                `users_statistics`.`regen_rate`, `users_statistics`.`regen_bonus`,
                `users_statistics`.`cur_cha`, `users_statistics`.`max_cha`,
                `users_statistics`.`cur_sta`, `users_statistics`.`max_sta`,
                `users_statistics`.`cur_health`, `users_statistics`.`max_health`,
                (`users_statistics`.`bank` + `users_statistics`.`money`) AS `geld`,
                `users_statistics`.`money`, `users_statistics`.`bank`, `users_statistics`.`federal_level`,

                `users_statistics`.`repel_effect`, `users_statistics`.`specialization`,
                `users_statistics`.`tai_off`, `users_statistics`.`tai_def`,
                `users_statistics`.`nin_off`, `users_statistics`.`nin_def`,
                `users_statistics`.`gen_off`, `users_statistics`.`gen_def`,
                `users_statistics`.`weap_off`, `users_statistics`.`weap_def`,
                `users_statistics`.`strength`, `users_statistics`.`intelligence`,
                `users_statistics`.`willpower`, `users_statistics`.`speed`,
                `users_statistics`.`login_streak`,
                `users_statistics`.`pop_now`,`users_statistics`.`pop_ever`,`users_statistics`.`rep_now`,`users_statistics`.`strengthFactor`,
                `users_statistics`.`over_encumbered`, `users_statistics`.`taggedGroup`,
                `users_statistics`.`dr`, `users_statistics`.`sr`,

                `users`.`id`, `users`.`username`, `users`.`status`, `users`.`post_ban`, `users`.`regen_boost`, `users`.`regen_endtime`,
                `users`.`login_id`, `users`.`latitude`, `users`.`longitude`, `users`.`location`, `users`.`region`, `users`.`join_date`,
                `users`.`new_pm`, `users`.`fbID`, `users`.`last_ip`, `users`.`past_IPs`,
                `users`.`federal_timer`, `users`.`logout_timer`, `users`.`deletion_timer`, `users`.`reset_timer`, `users`.`last_UA`,
                `users`.`repel_chance`, `users`.`repel_endtime`, `users`.`item_regen_boost`, `users`.`item_regen_endtime`,
                `users`.`apartment`, `users`.`bloodline`, `users`.`bloodlineMask`, `users`.`battle_id`, `users`.`database_fallback`, `users`.`tos_agree`,
                `users`.`ban_time`, `users`.`tban_time`, `users`.`perm_ban`, `users`.`drowning`, `users`.`notifications`, `users`.`dialog`,

                `users_preferences`.`clan`,                             `users_preferences`.`anbu`, 
                `users_preferences`.`visibleRank`,
                `users_preferences`.`show_level_up_button`,             `users_preferences`.`chat_autoupdate`,
                `users_preferences`.`layout`,                           `users_preferences`.`theme`,
                `users_preferences`.`silence_spar`,                     `users_preferences`.`collapse_home`,
                `users_preferences`.`popup`,                            `users_preferences`.`QuestingMode`,                     
                `users_preferences`.`quest_widget`,                     `users_preferences`.`turn_log_length`,                  `users_preferences`.`travel_default_redirect`,

				`users_preferences`.`layout_portrait_location`,         `users_preferences`.`layout_portrait_index`,

				`users_preferences`.`layout_details_location`,          `users_preferences`.`layout_details_index`,

				`users_preferences`.`layout_travel_location`,           `users_preferences`.`layout_travel_index`, 
				`users_preferences`.`layout_travel_mobile`,

				`users_preferences`.`layout_notifications_location`,    `users_preferences`.`layout_notifications_index`,

				`users_preferences`.`layout_quests_location`,           `users_preferences`.`layout_quests_index`,

				`users_preferences`.`layout_menu_location`,             `users_preferences`.`layout_menu_index`, 

				`users_preferences`.`layout_quick_links_location`,      `users_preferences`.`layout_quick_links_index`, 
                `users_preferences`.`layout_quick_links_style`,         `users_preferences`.`layout_quick_links`,

				`users_preferences`.`layout_quick_mobile`,              `users_preferences`.`layout_mobile_quick_links`,

                `users_preferences`.`key_bindings_status`,              `users_preferences`.`key_bindings`,

                `users_preferences`.`layout_font`,                      `users_preferences`.`layout_colors`,

                `users_preferences`.`fight_settings`,

                `users_loyalty`.`time_in_vil`, `users_loyalty`.`vil_loyal_pts`,
                `users_loyalty`.`vil_pts_timer`, `users_loyalty`.`village`,
                `users_loyalty`.`activateBonuses`,

                COUNT(`users_jutsu`.`jid`) AS `jutsu_tag_count`,

                `villages`.`avg_pvp`, `villages`.`latitude` AS `vil_latitude`, `villages`.`longitude` AS `vil_longitude`,
                `villages`.`leader`,

                `village_structures`.`regen_level`, `village_structures`.`warRegenBoostTime`, `village_structures`.`vassal`,

                `users_event_buffers`.`event_buffer`

                FROM `users`
                    INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    INNER JOIN `users_preferences` ON (`users_preferences`.`uid` = `users`.`id`)
                    INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                    INNER JOIN `village_structures` ON (`village_structures`.`name` = `users_loyalty`.`village`)
                    INNER JOIN `villages` ON (`villages`.`name` = `users_loyalty`.`village`)
                    LEFT JOIN `users_jutsu` ON (users_jutsu.`uid` = `users`.`id` AND locate(`users_statistics`.`taggedGroup`, `users_jutsu`.`tagged` ) > 0 )
                    LEFT JOIN `users_event_buffers` ON (`users_event_buffers`.`uid` = `users`.`id`)
                WHERE `users`.`id` = '.$id.' LIMIT 1'))) {
                throw new Exception('There was an error trying to load user information!');
            }
            elseif($user === '0 rows') {
                throw new Exception('Account could be logged out or does not exist anymore!');
            }

            // Set user alliance
            $user[0]['alliance'] = cachefunctions::getAlliance( $user[0]['village'] );

            // Set user layout to global
            $GLOBALS['layout'] = $user[0]['layout'];

            try{
                $user[0]['layout_colors'] = json_decode($user[0]['layout_colors'], true);
            }
            catch (Exception $e)
            {
                echo'There is an issue with your color settings.';
            }

            return $user;
        }

        // Implicitly Start a Transaction
        public function transaction_start() {

            // Log Query Action
            if($this->enableLog) {
                $this->queriesLog .= "Starting Transaction\r\n";
            }

            if(!$this->dbHandler->query('START TRANSACTION;')) { // Disables AutoCommit and Defines Transaction Parameters
                self::mysql_error_report('Starting A Transaction Failed!');
                die('Starting A Transaction Failed!');
            }

            // Transaction Start Notification
            $this->hasTransaction = true;
        }

        // Commit data to the associated tables and Re-enable Autocommit to 1
        public function transaction_commit() {

            // Check if Transaction is happening
            if($this->hasTransaction) {

                // Log Query Action
                if($this->enableLog) {
                    $this->queriesLog .= "Committing Transaction\r\n";
                }

                if(!$this->dbHandler->commit()) {
                    self::mysql_error_report('Committing A Transaction Failed!');
                    die('Committing A Transaction Failed!');
                }

                // Transaction End Notification
                $this->hasTransaction = false;
            }
            else{
                if($this->enableLog) {
                    $this->queriesLog .= "Failed to Commit Transaction - none present.\r\n";
                }
            }
        }

        // Revert Data back to earlier phase and Re-enable Autocommit to 1
        public function transaction_rollback($err_msg = "") {

            // Check if Transaction is happening
            if($this->hasTransaction) {

                // Log Query Action
                if($this->enableLog) {
                    $this->queriesLog .= "Rolling Back Transaction\r\n";
                }

                if(!$this->dbHandler->rollback()) {
                    self::mysql_error_report('Rolling Back A Transaction Failed!');
                    die('Rolling Back A Transaction Failed!');
                }

                // Transaction End Notification
                $this->hasTransaction = false;
            }
            else{
                if($this->enableLog) {
                    $this->queriesLog .= "Failed to Roll Back Transaction - none present.\r\n";
                }
            }
        }

        // Obtain Mutex Software Lock
        // Note: Prevents Duplicate Script Execution
        // Lock Timeout: 1 Second
        public function get_lock($type, $uid, $location = '')
        {
            //slimming down get lock to be faster.

            //if the lock is not free return failure
            $free = null;
            $lock_success = false;
            for($i = 0; !$lock_success; $i++)
            {
                //echo'lock attempt '.$i.' - ';
                //if the lock has failed to catch in 4 cycles throw an error.
                if($i >= 3)
                    throw new Exception('There was an issue obtaining a lock. Location: '.$location);

                if(!($free = self::fetch_data('SELECT IS_FREE_LOCK("'.$type.'_'.$uid.'") AS FREE')))
                    return false;

                if($free[0]['FREE'])
                    $lock_success = true;
                else
                    usleep(333333 * (1 + $i));
            }

            //if it is free
            if ($free[0]['FREE'])
            {
                //obtain the lock.
                $lock_success = false;
                for($i = 0; !$lock_success; $i++)
                {
                    //if the lock has failed to catch in 4 cycles throw an error.
                    if($i >= 3)
                        throw new Exception('There was an issue obtaining a lock. Location: '.$location);

                    //atempt to get the lock and set the result.
                    $result = self::fetch_data('SELECT GET_LOCK("'.$type.'_'.$uid.'", 1) AS RESULT');
                    $lock_success = $result[0]['RESULT'];

                    //if there was a failure to get the lock try again in 1/4th of a second.
                    if(!$lock_success)
                        usleep(333333 * (1 + $i));
                    //if there was no failure in getting the lock return true;
                    else
                        return true;
                }
                //if the loop is broken out of then aquiring the lock was successful.
                return true;
            }
            //final catch to return failure. this should never be reached.
            throw new Exception('There was an issue obtaining a lock. Location: '.$location);


            // Check if Named Lock is Already Taken (User's ID + _ + CLASS NAME)
            /*if(!($data = self::fetch_data('SELECT IS_FREE_LOCK("'.$sid.'_'.$class.'")'
                .' AS `'.$sid.'_'.$class.'`'))) { // If Query Fails, Lock has alread been obtained
                return false;
            }
            elseif($data !== '0 rows') { // Ensure Data Retrieval
                if($data[0][$sid.'_'.$class] === '1') { // Check if Lock is free
                    // Attempt to Obtain Named Lock (1 Second Timeout)
                    if(!($lock = self::fetch_data('SELECT GET_LOCK("'.$sid.'_'.$class.'", 1) AS `'.$sid.'_'.$class.'`'))) {
                        // If Query Fails, Lock Obtaining Failed
                        return false;
                    }
                    elseif($lock !== '0 rows') {
                        // Ensure Data Retrieval
                        if($lock[0][$sid.'_'.$class] === '1') {
                            // Check if Lock was successfully Obtained
                            // Check to verify the Lock is being Used by the Same User Connection
                            if(!($check = self::fetch_data('SELECT IF(IS_USED_LOCK("'.$sid.'_'.$class.'") = CONNECTION_ID(), 1, 0) AS `'.$sid.'_'.$class.'`'))) {
                                // If Query Fails, Lock Connection ID Checking Failed
                                return false;
                            }
                            elseif($check !== '0 rows') {
                                // Ensure Data Retrieval
                                if($check[0][$sid.'_'.$class] === '1') {
                                    // Check if Lock is retrieved by the intended user
                                    return true; // Mutex Lock Successfully Obtained
                                }
                            }
                        }
                    }
                }
            }
            return false; // Mutex Lock Failed to Obtain*/
        }

        // Release Mutex Software Lock
        // Note: Prevents Scripts from being Locked for longer than intended
        public function release_lock($type, $uid, $location = '') {

            // Ensure that Releasing Lock was Executed Successfully
            // Doesn't Matter what it returns, since we ensure the lock was obtained by the intended user
            return (self::execute_query('SELECT RELEASE_LOCK("'.$type.'_'.$uid.'")') !== false) ?  true : false;
        }

        // Full Table(s) Lock
        public function lock_tables($table) {

            // Log Query Action
            if($this->enableLog){
                $this->queriesLog .= "Locking ".$table." tables\r\n";
            }

            if(!$this->dbHandler->autocommit(false)) {
                self::mysql_error_report('Changing Autocommit (F) Failed!');
                die('Changing Autocommit (F) Failed!');
            }
            elseif(!$this->dbHandler->query('LOCK TABLES `'.$table.'` WRITE;')) {
                self::mysql_error_report('Locking '.$table.' Table Failed!');
                die('Locking '.$table.' Table Failed!');
            }
        }

        // Full Tables Unlock
        public function unlock_tables() {

            // Log Query Action
            if($this->enableLog){
                $this->queriesLog .= "Unlocking tables\r\n";
            }

            if(!$this->dbHandler->query('UNLOCK TABLES;')) {
                self::mysql_error_report('Unlocking Table(s) Failed!');
                die('Unlocking Table(s) Failed!');
            }
            elseif(!$this->dbHandler->autocommit(true)) {
                self::mysql_error_report('Changing Autocommit (T) Failed!');
                die('Changing Autocommit (T) Failed!');
            }
        }

        // MySQL Error Logger
        private function mysql_error_report($query) {

            // Save to database table
            $query_report = "INSERT INTO `log_sql_errors`
                    (`page`, `uid`, `time`, `failed_query`, `page_queries`, `error_message`, `error_number`)
                VALUES (
                    '". (isset($_GET['id']) ? $_GET['id'] : "None")."',
                    '".(isset($_SESSION['uid']) ? $_SESSION['uid'] : 0)."',
                    UNIX_TIMESTAMP(),
                    '". addslashes($query)."',
                    '". addslashes($this->queriesLog) ."',
                    '". addslashes($this->dbHandler->error) ."',
                    '". $this->dbHandler->errno ."')";

            try {
                if(self::execute_query($query_report, false, false) === false) {
                    throw new Exception("Error in error query");
                }
            }
            catch (Exception $e) {
                echo "<br><br>There was an error logging the query error. Inception much? " . $e->getMessage();
            }
        }

        // Query Scope Error Logger (Should be a Rarity to Occur)
        private function log_scope_error($query) {

            try {
                if (is_dir(Data::$absSvrPath.'/logs/')) { // Check if it is a directory
                    if ($fp = fopen(Data::$absSvrPath.'/logs/scope_fetch_error_' . date('Y-m-d') . '.log', 'a')) { // Open New or Existing File for Writing
                        fwrite($fp, "Time of Error: " . date("G:i:s") . "\r\n" .
                            "Error Occurrence Page: " . (isset($_GET['id']) ? $_GET['id'] : "None") . "\r\n" .
                            "User ID: " . (isset($_SESSION['uid']) ? $_SESSION['uid'] : 'No User Logged In!')  . "\r\n" .
                            "Affected Query: " . $query . "\r\n\r\n"); // Write to File
                        fclose($fp); // Close File
                    }
                }
            }
            catch (Exception $e) {
                echo "Error Report TNR_Beta: " . $e->getMessage() . "<br>";
            }
        }

        // Transaction Error Logger
        private function log_transaction_error($error_message) {

            try {
                if (is_dir(Data::$absSvrPath.'/logs/')) { // Check if it is a directory
                    if ($fp = fopen(Data::$absSvrPath.'/logs/transaction_error_' . date('Y-m-d') . '.log', 'a')) { // Open New or Existing File for Writing
                        fwrite($fp, "Time of Error: " . date("G:i:s") . "\r\n" .
                            "Error Occurrence Page: " . (isset($_GET['id']) ? $_GET['id'] : "None") . "\r\n" .
                            "User ID: " . (isset($_SESSION['uid']) ? $_SESSION['uid'] : 'No User Logged In!') . "\r\n" .
                            "Transaction Error Message: " . $error_message . "\r\n" .
                            "MySQL Error: " . ((!empty($this->dbHandler->error)) ?
                                $this->dbHandler->error : "Probably Query Build Error") . "\r\n\r\n"); // Write to File
                        fclose($fp); // Close File
                    }
                }
            }
            catch (Exception $e) {
                echo "Error Report TNR_Gamma: " . $e->getMessage() . "<br>";
            }
        }


        //makes the user over encumbered
    public function overEncumbered()
    {
        $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => array('?id=11','You are over-encumbered!')));

        if (!$GLOBALS['userdata'][0]['over_encumbered'] && $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `over_encumbered` = b'1' WHERE `users_statistics`.`uid` = '".$_SESSION['uid']."'") === false)
        {
            throw new Exception('there was an issue setting this user as overEncumbered');
        }
        else
        {
            $GLOBALS['userdata'][0]['over_encumbered'] = true;
        }
    }

    //makes the user under encumbered
    public function underEncumbered()
    {
        if($GLOBALS['userdata'][0]['over_encumbered'] && $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `over_encumbered` = b'0' WHERE `users_statistics`.`uid` = '".$_SESSION['uid']."'") === false)
        {
            throw new Exception('there was an issue wsetting this user as underEncumbered');
        }
        else
        {
            !$GLOBALS['userdata'][0]['over_encumbered'] = false;
        }
    }

    }