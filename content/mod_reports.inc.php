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

    class module {

        public function __construct() {

            try{

                functions::checkActiveSession();

                $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

                require_once(Data::$absSvrPath.'/panel_moderator/global_libs/page.class.php');

                // Check user rank. Only allow staff
                if (!in_array($GLOBALS['userdata'][0]['user_rank'], Data::$MOD_STAFF_RANKS, true)) {
                    throw new Exception("You do not have access to view this page");
                }

                // Get an instance of the moderator page class
                $this->modPage = new modPage();
                $this->modPage->getModData( $GLOBALS['userdata'][0]['id'] );

                // Handle page viewing
                if (!isset($_GET['act'])) {
                    if( !isset($_POST['SearchUser']) && !isset($_GET['uid']) ){

                        // Setup the three reports
                        $this->setupReport("unviewed", "Unviewed Reports");
                        $this->setupReport("my", "Your Reports");
                        $this->setupReport("in progress", "Ongoing Reports");

                        // Get the orders
                        if (!($nindo = $GLOBALS['database']->fetch_data("SELECT `user_notes`.`message`
                            FROM `user_notes` WHERE `user_notes`.`user_id` = 0 LIMIT 1"))) {
                            throw new Exception('There was an error when obtaining staff orders, please try again!');
                        }

                        // Input form
                        $nindo_text = ($nindo !== "0 rows") ? functions::parse_BB($nindo[0]['message']) : '';

                        // Set extra content load to be shown below input
                        $GLOBALS['page']->Message(
                            $nindo_text,
                            'Staff Orders',
                            false,
                            false,
                            'extraContentLoad'
                        );

                        // Show the appropriate wrapper template
                        $GLOBALS['template']->assign('contentLoad', './panel_moderator/templates/reports/main.tpl');

                        // Search username
                        $this->modPage->searchUsername();
                    }
                    else{
                        $this->main_page();
                    }
                }
                elseif( $_GET['act'] == "logDetails" ){
                    $this->showLogEntryDetails();
                }
                elseif( $_GET['act'] == "ryoLog" ){
                    $this->ryolog();
                }
                elseif( $_GET['act'] == "logDelete" ){
                    if( !isset( $_POST['Submit'] ) ){
                        $this->confirmDeleteLogEntry();
                    }
                    else{
                        $this->deleteLogEntry();
                    }
                }
                elseif( $_GET['act'] == "reportDetails" ){
                    if( !isset( $_POST['Submit'] ) ){
                        $this->showReportDetails();
                    }
                    else{
                        $this->updateReportStatus();
                    }
                }
                elseif( $_GET['act'] == "userNotes" ){
                    if( isset($_POST['POSTCOMMENT']) ){
                        $this->doPostUserNote();
                    }
                    elseif( isset($_GET['perform']) && $_GET['perform'] = "delete" ){
                        $this->doDeleteUserNote();
                    }
                }
                elseif( $_GET['act'] == 'take_control')
                {
                    $this->takeControl();
                    $this->showReportDetails();
                }
                elseif( $_GET['act'] == 'delete')
                {
                    $this->delete();
                    $this->showReportDetails();
                }

                if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                    throw new Exception('There was an issue releasing the lock!');
                }

            } catch (Exception $e) {
                $GLOBALS['database']->transaction_rollback( $e->getMessage() );
                $GLOBALS['page']->Message( $e->getMessage() , "Check User Record System", 'id='.$_GET['id'],'Return');
            }
        }

        // Show reports overview
        private function setupReport( $type , $name ){

            // Create the query
            $query = "SELECT `report_id`,  `uid`, `rid`, `type`, `time`,
                        `reporter`.`username` as rname,
                        `reported`.`username` as nname,
                        `mt`
                      FROM `user_reports`
                      LEFT JOIN `users` AS reported ON `reported`.`id` = `user_reports`.`uid`
                      LEFT JOIN `users` AS reporter ON `reporter`.`id` = `user_reports`.`rid`";

            if ($type != 'my') {
                $query .= " WHERE `user_reports`.`status` = '" . $type . "'
                            ORDER BY `time` ASC";
            } else {
                $query .= " WHERE `user_reports`.`status` = 'in progress' AND `processed_by` = '" . $GLOBALS['userdata'][0]['username'] . "'
                            ORDER BY `time` ASC";
            }

            // get the data
            $data = $GLOBALS['database']->fetch_data( $query );

            // Fix up instances where the reporter is no more
            if( $data !== "0 rows" ){
                for ($i = 0; $i < count($data); $i++) {
                    if( empty($data[$i]['nname']) ){
                        $data[$i]['nname'] = "Deleted UID: ".$data[$i]['uid'];
                    }
                    if( empty($data[$i]['rname']) ){
                        $data[$i]['rname'] = "Deleted UID: ".$data[$i]['rid'];
                    }
                }
            }


            // Save data
            tableParser::show_list(
                str_replace(" ", "", $type),
                $name,
                $data, array(
                'nname' => "User",
                'rname' => "Reporter",
                'type' => "Type",
                'mt' => "Message Time",
                'time' => "Report Time"
                    ),
                array(
                    array("name" => "Details", "id" => $_GET['id'], "act" => "reportDetails", "eid" => "table.report_id")
                ),
                false, // Send directly to contentLoad
                true,   // Show previous/next links
                false,  // No links at the top to show
                false,   // Allow sorting on columns
                false,   // pretty-hide options
                false, // Top stuff
                ""
            );


        }

        // Get user data
        private function getUserData( $id = false ){

            // If id parameter is specified, overwrite default
            if( $id !== false ){
                $this->id = $id;
                $this->name = "N/A";
            }

            // Get the data
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `id` = '" . $this->id . "' OR `username` = '" . $this->name . "' LIMIT 1");

            // Check if data was retrieved. If yes, update scope vars
            if( $data !== "0 rows" ){
                $this->id = $data[0]['id'];
                $this->name = $data[0]['username'];
            }

            // Return data
            return $data;
        }

        // Functions relating to show the overview pages
        // =============================================

        // Main page
        private function main_page() {

            // Initial Fetch user data:
            if (isset($_POST['username']) && !empty($_POST['username']) ) {
                $this->name = $_POST['username'];
                $this->id = "N/A";
            } elseif (isset($_POST['userid']) && !empty($_POST['userid'])) {
                $this->id = $_POST['userid'];
                $this->name = "N/A";
            } elseif (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
                $this->id = $_GET['uid'];
                $this->name = "N/A";
            }

            // Look up data in user and name-change table
            $edits = $GLOBALS['database']->fetch_data("
                        SELECT `log_namechanges`.*,`users`.`username`
                        FROM `log_namechanges`
                        INNER JOIN `users` ON (`users`.`id` = `log_namechanges`.`uid`)
                        WHERE
                            `uid` = '".$this->id."' OR
                            `oldName` = '".$this->name."' OR
                            `newName` = '".$this->name."'
                        ORDER BY `time` DESC");

            // Check if there are name changes.
            if( is_numeric($this->id) || $edits == "0 rows" ){

                // Get user data
                $user_data = $this->getUserData();
                if( $user_data !== "0 rows"){
                    $this->showUserSheet( $user_data );
                }
                else{
                    throw new Exception("Could not find any users in the database from this data. UID: ".$this->id.". Name: ".$this->name);
                }
            }
            else{
                 // Show the user IDs & old/new names.
                $this->showUncertainRows( $edits );
            }

            // Remove user
            return false;

        }

        // Show the user sheet for user with user ID
        private function showUserSheet( $user_data ){

            // Show output:
            if ((isset($this->name) && !empty($this->name)) ||
                (isset($this->id) && is_numeric($this->id))
            ) {

                // Get the data from the moderator log
                $sheet = $GLOBALS['database']->fetch_data("
                    SELECT *
                    FROM `moderator_log`
                    WHERE `uid` = '" . $this->id . "'
                    ORDER BY `id` DESC");

                if( $sheet !== "0 rows" ){
                    foreach( $sheet as $key => $entry ){
                        if( !empty($entry['override_by']) && $entry['action'] !== "Extension" && $entry['action'] !== "Reduction" ){
                            $sheet[$key]['action'] .= "<br><span style='color:red;'>Overruled</a>";
                            $sheet[$key]['moderator'] .= "<br><span style='color:red;'>".$entry['override_by']."</a>";
                        }
                    }
                }

                $options = array( array("name" => "Details", "act" => "logDetails", "eid" => "table.id") );
                if( $this->canDeleteStuff() ){
                    $options[] = array("name" => "Delete", "act" => "logDelete", "eid" => "table.id");
                }

                tableParser::show_list(
                    'modLogData',
                    $this->name." - ID: ".$this->id." - Loc: ". $user_data[0]['longitude'] . ":" . $user_data[0]['latitude'],
                    $sheet,
                    array(
                    'action' => "Type",
                    'moderator' => "Moderator",
                    'duration' => "Duration",
                    'time' => "Time"),
                    $options ,
                    false, // Send directly to contentLoad
                    false, // No newer/older links
                    false, // No top options links
                    false, // No sorting on columns
                    false, // No pretty options
                    false, // No top search field
                    'Contains bans, extensions, reductions, warnings and so forth.'
                );

                // Get the data from the user reports table
                $data = $GLOBALS['database']->fetch_data("
                    SELECT `user_reports`.*,`users`.`username`
                    FROM `user_reports`
                    LEFT JOIN `users` ON `users`.`id` = `user_reports`.`rid`
                    WHERE `user_reports`.`status` != 'ungrounded' AND `user_reports`.`uid` = '" . $this->id . "'
                    ORDER BY `time` DESC");

                if( $data !== "0 rows" ){
                    foreach( $data as $key => $value ){
                        if( !isset($value['username']) || empty($value['username']) ){
                            $data[$key]['username'] = "ID: ".$value['rid'];
                        }
                    }
                    tableParser::show_list(
                        'userReports',
                        'Reports files against this user',
                        $data,
                        array(
                        'username' => "Filed By",
                        'status' => "Status",
                        'reason' => "Reason",
                        'type' => "Type"),
                        array( array("name" => "Show Details", "act" => "reportDetails", "eid" => "table.report_id") ) ,
                        false, // Send directly to contentLoad
                        false, // No newer/older links
                        false, // No top options links
                        false, // No sorting on columns
                        false, // No pretty options
                        false, // No top search field
                        'Information about user reports on this user.'
                    );
                }

                // Name changes belonging to this user
                $edits = $GLOBALS['database']->fetch_data("
                        SELECT *
                        FROM `log_namechanges`
                        WHERE `uid` = '".$this->id."'
                        ORDER BY `time` DESC");
                if( $edits !== "0 rows" ){
                    tableParser::show_list(
                        'changes',
                        'User Namechanges',
                        $edits, array(
                        'id' => "record ID",
                        'uid' => "User ID",
                        'oldName' => "Old Name",
                        'newName' => "New Name",
                        'time' => "Request Time",
                        'request_ip' => "IP"
                            ),
                            false ,
                    false, // Send directly to contentLoad
                    false, // No newer/older links
                    false, // No top options links
                    false, // No sorting on columns
                    false, // No pretty options
                    false, // No top search field
                    'Name history of this user'
                    );
                }

                // Discussion about user
                $extra = "";
                if (isset($user_data[0]['last_UA']) && ($user_data[0]['last_UA'] !== "")) {
                    $extra .= '<b>Last UA:</b> ' . $user_data[0]['last_UA'] . '<br>';
                }

                if (isset($user_data[0]['mail']) && ($user_data[0]['mail'] !== "")) {
                    $extra .= '<b>User Email:</b> ' . $user_data[0]['mail'] . '<br>';
                }


                // Tavern-like ting
                if (isset($user_data[0]['perm_ban']) && ($user_data[0]['perm_ban'] == 1)) {
                    $extra .= '<b>THIS USER IS CURRENTLY PERMANENTLY BANNED</b>';
                }
                $GLOBALS['template']->assign('extraNotices', $extra);

                // Display user notes
                $tavern = $GLOBALS['database']->fetch_data("SELECT * FROM `user_notes` WHERE `user` = '" . $this->name . "' ORDER BY `time`");
                $GLOBALS['template']->assign('tavern', $tavern);
                $GLOBALS['template']->assign('userid', $this->id);
                $GLOBALS['template']->assign('canDeleteStuff', $this->canDeleteStuff() );

                // Smarty template
                $GLOBALS['template']->assign('contentLoad', 'panel_moderator/templates/user_record/main.tpl');

            } else {
                throw new Exception("No user was specified or this user does not exist");
            }
        }

        // Show name changes
        private function showUncertainRows( $dataList ){

            // Add current user stuff
            $userData = $this->getUserData();
            if( $userData !== "0 rows" ){
                foreach( $userData as $user ){
                    $user['uid'] = $user['id'];
                    $user['oldName'] = "<b>Active</b>";
                    $user['newName'] = "<b>Active</b>";
                    $user['time'] = time();
                    $user['request_ip'] = $user['last_ip'];
                    $dataList[] = $user;
                }
            }

            // Show it
            tableParser::show_list(
                'changes',
                'User Namechanges',
                $dataList, array(
                'uid' => "User ID",
                'oldName' => "Old Name",
                'newName' => "New Name",
                'username' => "Current Name",
                'time' => "Request Time",
                'request_ip' => "IP"
                    ),
                array(
                    array("name" => "Check Record by UserID", "uid" => "table.uid")
                ) ,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Uuncertain results were found on your search.
             Please select the row with the appropriate user
             ID for the user you wish to check.'
            );

        }

        // Functions relating to moderator log entries
        // ===========================================

        // Show details
        protected function showLogEntryDetails() {

            // Sanitize input
            if( isset( $_GET['eid'] ) && is_numeric( $_GET['eid'] ) ){

                // Get the data
                $data = $GLOBALS['database']->fetch_data("SELECT `moderator_log`.* FROM `moderator_log` WHERE `id` = '" . $_GET['eid'] . "' LIMIT 1");
                if( $data !== "0 rows" ){

                    // Send to smarty
                    $data[0]['message'] = functions::parse_BB($data[0]['message']);
                    $data[0]['override_reason'] = functions::parse_BB($data[0]['override_reason']);
                    $GLOBALS['template']->assign('data', $data);

                    // Load templates
                    $GLOBALS['template']->assign('contentLoad', 'panel_moderator/templates/user_record/record_details.tpl');
                }
                else{
                    throw new Exception("This violation does not exist.");
                }
            }
            else{
                throw new Exception("You specified an invalid log entry.");
            }
        }

        // Can user delete a record
        private function canDeleteStuff(){
            if( $GLOBALS['userdata'][0]['user_rank'] == "Supermod" ||
                $GLOBALS['userdata'][0]['user_rank'] == "Admin"
            ){
                return true;
            }
            return false;
        }

        // Confirm deletion of entry
        private function confirmDeleteLogEntry(){
            $GLOBALS['page']->Confirm("Delete this log entry?", 'Check User Record System', 'Delete Now!');
        }

        // Delete entry
        protected function deleteLogEntry() {

            // Sanitize input
            if( isset( $_GET['eid'] ) && is_numeric( $_GET['eid'] ) ){
                if( $this->canDeleteStuff() ){

                    // Start transaction
                    $GLOBALS['database']->transaction_start();

                    // Get record
                    $record = $GLOBALS['database']->fetch_data("
                        SELECT *
                        FROM `moderator_log`
                        WHERE `id` = '" . $_GET['eid'] . "'
                        LIMIT 1 FOR UPDATE");
                    if ( $record !== "0 rows" && !empty($record) )
                    {

                        // Check the endtime of this if it's a ban, and make sure it's up!
                        if( preg_match("/(Ban|Tavern-Ban|Reduction|Extension)/", $record[0]['action']) ){

                            // get the end-time of the ban
                            $banTime = $this->modPage->calcBanTime( $record[0]['duration'] , $record[0]['time'] );

                            // Check if errors should be thrown
                            if($record[0]['duration'] === 'Permanent' && functions::ws_remove($record[0]['override_by']) === '') {
                                throw new Exception('You must unban an active permanent ban before you can delete the record!');
                            }
                            elseif( $banTime > time() && functions::ws_remove($record[0]['override_by']) === '' ){
                                throw new Exception("
                                    This record has not yet expired, and can therefore not be deleted from record.
                                    You may effectively unban the user, but the record must reman in the database till it's expired. - ".$banTime." - ".time());
                            }
                        }

                        // Log so admins can check
                        $change_message = addslashes("User record deleted. <br><br>
                        <b>Record Reason:</b><i> " . $record[0]['reason'] . "</i><br><br>
                        <b>Record Message: </b><i>" . $record[0]['message'] . "</i>");

                        $this->modPage->log_for_admins(
                            time(),
                            $record[0]['uid'],
                            $GLOBALS['userdata'][0]['username'],
                            $change_message
                        );

                        // Delete the record
                        if (($GLOBALS['database']->execute_query("DELETE FROM `moderator_log`
                            WHERE `id` = '" . $_GET['eid'] . "' LIMIT 1")) === false)
                        {
                            throw new Exception('There was an error actually deleting the record.');
                        }

                        // Commit transaction
                        $GLOBALS['database']->transaction_commit();

                        // Show message to user
                        $GLOBALS['page']->Message( "Record successfully deleted" , "Check User Record System", 'id='.$_GET['id']."&uid=".$record[0]['uid'],'Return');
                    }
                    else{
                        throw new Exception('There was an error trying to receive the record!');
                    }
                }
                else{
                    throw new Exception("You are not allowed to delete records.");
                }
            }
            else{
                throw new Exception("You specified an invalid log entry.");
            }
        }

        protected function takeControl()
        {
            if($GLOBALS['userdata'][0]['user_rank'] == 'Admin' || $GLOBALS['userdata'][0]['user_rank'] == 'Supermod')
                if (!$GLOBALS['database']->execute_query("UPDATE `user_reports` SET status = 'in progress', `processed_by` = '".$GLOBALS['userdata'][0]['username']."' where report_id = ".$_GET['eid']))
                {
                    throw new Exception('There was an error taking control: '."UPDATE `user_reports` SET status = 'in progress', `processed_by` = '".$GLOBALS['userdata'][0]['username']."' where report_id = ".$_GET['eid']);
                }
        }

        protected function delete()
        {
            if($GLOBALS['userdata'][0]['user_rank'] == 'Admin')
                if (!$GLOBALS['database']->execute_query("DELETE FROM `user_reports` WHERE `user_reports`.`report_id` = ".$_GET['eid']))
                {
                    throw new Exception('There was an error deleting: '."DELETE FROM `user_reports` WHERE `user_reports`.`report_id` = ".$_GET['eid']);
                }
        }

        // Functions relating to user report entries
        // =========================================

        protected function showReportDetails() {

            // Sanitize input
            if( isset( $_GET['eid'] ) && is_numeric( $_GET['eid'] ) ){

                // Get the report in question
                $report = $GLOBALS['database']->fetch_data("SELECT * FROM `user_reports` WHERE `report_id` = '" . $_GET['eid'] . "' LIMIT 1");
                if ($report !== "0 rows") {

                    // Get data on this user
                    $reportedData = $this->getUserData( $report[0]['uid'] );
                    $reporterData = $this->getUserData( $report[0]['rid'] );

                    if( $reportedData == "0 rows" ){
                        $reportedData = array(0 => array("username" => "Deleted UID: ".$report[0]['uid']));
                    }
                    if( $reporterData == "0 rows" ){
                        $reporterData = array(0 => array("username" => "Deleted UID: ".$report[0]['rid']));
                    }

                    // Fix up some stuff
                    if (trim($report[0]['processed_by']) != '') {
                        $processed = '<a href="?id=3&act=newpm&user=' . $report[0]['processed_by'] . '">' . $report[0]['processed_by'] . '</a>';
                    } else {
                        $processed = 'Nobody';
                    }

                    if($GLOBALS['userdata'][0]['user_rank'] == 'Admin' || $GLOBALS['userdata'][0]['user_rank'] == 'Supermod')
                        $can_take_control = true;
                    else
                        $can_take_control = false;

                    if($GLOBALS['userdata'][0]['user_rank'] == 'Admin')
                        $can_delete = true;
                    else
                        $can_delete = false;

                    // Pass along the infromation
                    $GLOBALS['template']->assign('can_take_control', $can_take_control);
                    $GLOBALS['template']->assign('can_delete', $can_delete);
                    $GLOBALS['template']->assign('message', functions::parse_BB($report[0]['message']) );
                    $GLOBALS['template']->assign('report', $report);
                    $GLOBALS['template']->assign('jumpPage', "99" );
                    $GLOBALS['template']->assign('rname', $reporterData[0]['username']);
                    $GLOBALS['template']->assign('name', $reportedData[0]['username']);
                    $GLOBALS['template']->assign('processed', $processed);
                    $GLOBALS['template']->assign('handleReport', $this->canHandleReport($report) );

                    // Show template
                    $GLOBALS['template']->assign('contentLoad', 'panel_moderator/templates/user_record/report_details.tpl');

                } else {
                    throw new Exception("There was an issue with retrieving database information.");
                }
            } else {
                throw new Exception("You specified an invalid log entry.");
            }
        }


        // Check if user can handle this report
        protected function canHandleReport( $report ){
            if( $report[0]['processed_by'] == '' || $report[0]['processed_by'] == $GLOBALS['userdata'][0]['username'] ){
                if( $report[0]['status'] != 'handled' && $report[0]['status'] != 'ungrounded' ){
                    return true;
                }
            }
            return false;
        }

        // Update report status
        protected function updateReportStatus() {

            // Sanitize input
            if( isset( $_GET['eid'] ) && is_numeric( $_GET['eid'] ) ){

                // Get the report in question
                $report = $GLOBALS['database']->fetch_data("SELECT * FROM `user_reports` WHERE `report_id` = '" . $_GET['eid'] . "' LIMIT 1");
                if ($report !== "0 rows") {

                    if ($_POST['status'] != 'unviewed') {
                        $GLOBALS['database']->execute_query("
                            UPDATE `user_reports`
                            SET `processed_by` = '" . $GLOBALS['userdata'][0]['username'] . "',
                                `status` = '" . $_POST['status'] . "'
                            WHERE `report_id` = '" . $_GET['eid'] . "' AND
                                  (`processed_by` = '' OR `processed_by` = '" . $GLOBALS['userdata'][0]['username'] . "')
                            LIMIT 1");
                    } elseif ($_POST['status'] == 'unviewed') {
                        $GLOBALS['database']->execute_query("
                            UPDATE `user_reports`
                            SET `processed_by` = '', `status` = '" . $_POST['status'] . "'
                            WHERE `report_id` = '" . $_GET['eid'] . "' AND
                                  (`processed_by` = '' OR `processed_by` = '" . $GLOBALS['userdata'][0]['username'] . "')
                            LIMIT 1");
                    }
                    $GLOBALS['page']->Message("The report status has been updated.", 'Reports System', 'id='.$_GET['id']."&uid=".$report[0]['uid'],'Return');

                } else {
                    throw new Exception("There was an issue with retrieving database information.");
                }
            } else {
                throw new Exception("You specified an invalid log entry.");
            }
        }

        // Functions relating to user notes
        // ================================

        // Post user note
        protected function doPostUserNote() {

            // keep transaction safe
            $GLOBALS['database']->transaction_start();

            // Sanitize message
            if (functions::ws_remove($_POST['message']) === '') {
                throw new Exception('You cannot post blank messages.');
            }

            // Message to be sent
            $message = functions::store_content($_POST['message']);

            // Get the user
            $user_data = $this->getUserData($_GET['uid']);
            if( $user_data == "0 rows" ){
                throw new Exception('There was an error trying to receive the user!');
            }

            // Insert the message
            if (($GLOBALS['database']->execute_query("
                INSERT INTO `user_notes`
                    (`user_id`, `user`, `moderator`, `time`, `message`)
                VALUES
                    ('" . $user_data[0]['id'] . "', '" . $user_data[0]['username'] . "',
                    '" . $GLOBALS['userdata'][0]['username'] . "', UNIX_TIMESTAMP(),
                    '" . $message . "');")) === false)
            {
                throw new Exception('An error has occurred, please try again.');
            }

            // Commit transaction
            $GLOBALS['database']->transaction_commit();

            // Show message
            $GLOBALS['page']->Message("User comment has been posted", 'User Record System', 'id='.$_GET['id']."&uid=".$user_data[0]['id'],'Return');
        }

        // Delete user note
        protected function doDeleteUserNote() {

            // Start transaction
            $GLOBALS['database']->transaction_start();

            // Check that it's ok
            if( !$this->canDeleteStuff() ){
                throw new Exception('You are not allowed to delete message.');
            }

            if (ctype_digit($_GET['postID']) === false) {
                throw new Exception('The post-data is corrupted, please try again!');
            }

            // Check user note
            if (($data = $GLOBALS['database']->fetch_data("
                SELECT * FROM `user_notes`
                WHERE `id` = '" . $_GET['postID'] . "' LIMIT 1
                FOR UPDATE")) === false)
            {
                throw new Exception('There was an error trying to receive the user note!');
            }

            // Delete user note
            if (($GLOBALS['database']->execute_query("
                DELETE FROM `user_notes`
                WHERE `id` = '" . $_GET['postID'] . "'
                LIMIT 1")) === false)
            {
                throw new Exception('There was an error trying to delete the user note!');
            }

            // Commit transaction
            $GLOBALS['database']->transaction_commit();

            // Message to user
            $GLOBALS['page']->Message("The message has been deleted.", 'Moderator Note System',  'id='.$_GET['id']."&uid=".$data[0]['user_id'],'Return');
        }

        // Ryo Log
        private function ryolog() {

            // Get the user
            $user_in_question = "0 rows";
            if ( isset($_GET['uid']) && is_numeric($_GET['uid'])) {
                $user_in_question = $GLOBALS['database']->fetch_data("SELECT `username` FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
                $id = $_GET['uid'];
            }
            if( $user_in_question == "0 rows" ){
                throw new Exception("Could not find the user");
            }

            // Use the table parser library to show notes in system
            $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `ryo_track` WHERE `s_uid` = '" . $id . "'  ORDER BY `time` DESC LIMIT 100");
            tableParser::show_list(
                    'sendingsFrom', 'Latest Sending from ' . $user_in_question[0]['username'] . '', $edits, array(
                'time' => "Time",
                'receiver' => "Received by",
                'amount' => "Ryo Amount"
                    ), false, false, false
            );

            $edits1 = $GLOBALS['database']->fetch_data("SELECT * FROM `ryo_track` WHERE `r_uid` = '" . $id . "'  ORDER BY `time` DESC LIMIT 100");
            tableParser::show_list(
                    'sendingsTo', 'Latest Sending to ' . $user_in_question[0]['username'] . '', $edits1, array(
                'time' => "Time",
                'sender' => "Received From",
                'amount' => "Ryo Amount"
                    ), false, false, false
            );

            // Load template
            $GLOBALS['template']->assign('username', $user_in_question[0]['username']);
            $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/content_users/ryoLog.tpl');
        }
    }

    new module();