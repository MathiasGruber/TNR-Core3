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
    
class moderatorLib extends functions {
    
    protected $user;
    protected $villages;
    protected $months;
    
    // New Head Mod Functions, Core 3
    protected function searchUsername(){
        
         // Create the fields to be shown
        $inputFields = array(
            array("infoText"=>"Lookup Username","inputFieldName"=>"username", "type" => "input", "inputFieldValue" => "")
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Write the username you want to lookup in the field below", // Information
            "Search System", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] , "submitFieldName" => "SearchUser","submitFieldText" => "Search"), // Submit button
            "Return" // Return link name
        );
    }
    
    // Check transaction log
    protected function paypalLog(){
        
        // Get data
        $min = tableParser::get_page_min();
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `ipn_payments` WHERE `recipient` = '" . $_POST['username'] . "' LIMIT 100");

        // Modify data
        if ($edits !== "0 rows") {
            $i = 0;
            while ($i < count($edits)) {
                if ($edits[$i]['sender'] == "") {
                    $edits[$i]['sender'] = "Unregistered";
                }
                $i++;
            }
        }

        // Show form
        tableParser::show_list(
                'log', 'PayPal Log', $edits, array(
            'time' => "Time of Transaction",
            'item' => "Item",
            'sender' => "From User",
            'txn_id' => "txn#"
                ), false, true, false, false
        );

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] );
    }
    
    // Check ryo log
    protected function ryoLog(){
        
        // Get the user
        $user_in_question = $GLOBALS['database']->fetch_data("SELECT `id`, `username` FROM `users` WHERE `username` = '" . $_POST['username'] . "' LIMIT 1");
        $id = $user_in_question[0]['id'];

        // Use the table parser library to show notes in system
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `ryo_track` WHERE `uid` = '" . $id . "'  ORDER BY `time` DESC LIMIT 100");
        tableParser::show_list(
                'sendingsFrom', 'Latest Sending from ' . $user_in_question[0]['username'] . '', $edits, array(
            'time' => "Time",
            'receiver' => "Received by",
            'amount' => "Ryo Amount"
                ), false, false, false
        );

        $edits1 = $GLOBALS['database']->fetch_data("SELECT * FROM `ryo_track` WHERE `receiver` = '" . $user_in_question[0]['username'] . "'  ORDER BY `time` DESC LIMIT 100");
        tableParser::show_list(
                'sendingsTo', 'Latest Sending to ' . $user_in_question[0]['username'] . '', $edits1, array(
            'time' => "Time",
            'sender' => "Sent by",
            'amount' => "Ryo Amount"
                ), false, false, false
        );

        // Load template
        $GLOBALS['template']->assign('username', $user_in_question[0]['username']);
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/content_users/ryoLog.tpl');
    }
    
    // Latest Changes Log
    protected function newEmailForm(){
        
        // Get User
        $user = $GLOBALS['database']->fetch_data("SELECT `id`, `username`, `mail` FROM `users` WHERE `username` = '" . $_POST['username'] . "' LIMIT 1");
        if( $user !== "0 rows" ){
            
            // Create the fields to be shown
            $inputFields = array(
                array("infoText"=>"New Email","inputFieldName"=>"mail", "type" => "input", "inputFieldValue" => ""),
                array("type"=>"hidden", "inputFieldName"=>"username", "inputFieldValue"=>$_POST['username']),
            );

            // Show user prompt
            $GLOBALS['page']->UserInput(
                "The email of this user is currently: ".$user[0]['mail'], // Information
                "New Email", // Title
                $inputFields, // input fields
                array("href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] , "submitFieldName" => "doChangeEmail","submitFieldText" => "Change"), // Submit button
                "Return" // Return link name
            ); 
        }
        else{
            $GLOBALS['page']->Message("User could not be found", 'User Search', 'id=' . $_GET['id']);
        }
    }
    
    // Latest Changes Log
    protected function changeEmail(){
        
        // Get User
        $user = $GLOBALS['database']->fetch_data("SELECT `id`, `username`, `mail` FROM `users` WHERE `username` = '" . $_POST['username'] . "' LIMIT 1");
        if( $user !== "0 rows" ){
            
            // Check that email is not already in system
            $mail = $GLOBALS['database']->fetch_data("SELECT `id`, `username`, `mail` FROM `users` WHERE `mail` = '" . $_POST['mail'] . "' LIMIT 1");
            if( $mail == "0 rows" ){
                
                // Update email
                $GLOBALS['database']->execute_query('UPDATE `users` 
                    SET `mail` = "' . $_POST['mail'] . '" 
                    WHERE `username` = "' . $_POST['username'] . '" LIMIT 1');
                
                // Log action
                $GLOBALS['database']->execute_query('INSERT INTO `moderator_log` 
                    (`time`, `uid`, `username`, `moderator`, `reason`, `message`) 
                VALUES 
                    (UNIX_TIMESTAMP(), ' . $user[0]['id'] . ', "' . $user[0]['username'] . '", "' . $this->user[0]['username'] 
                    . '", "Email Change", "From '.$user[0]['mail'].' to '.$_POST['mail'].'")');
                
                $GLOBALS['page']->Message("Email has been changed to: ".$_POST['mail'], 'Email Change', 'id=' . $_GET['id']);
            }
            else{
                $GLOBALS['page']->Message("This email is already attached to: ".$mail[0]['username'], 'User Search', 'id=' . $_GET['id']);
            }
        }
        else{
            $GLOBALS['page']->Message("User could not be found", 'User Search', 'id=' . $_GET['id']);
        }
    }
    
    // Do activate a user
    protected function activateUser(){
        
        // Get User
        $user = $GLOBALS['database']->fetch_data("SELECT `activation`, `id`, `username`, `mail` FROM `users` WHERE `username` = '" . $_POST['username'] . "' LIMIT 1");
        if( $user !== "0 rows" ){
            
            // Check current activation
            if( $user[0]['activation'] == "0" ){
                
                // Update email
                $GLOBALS['database']->execute_query('UPDATE `users` 
                    SET `activation` = "1" 
                    WHERE `username` = "' . $_POST['username'] . '" LIMIT 1');
                
                // Log action
                $GLOBALS['database']->execute_query('INSERT INTO `moderator_log` 
                    (`time`, `uid`, `username`, `moderator`, `reason`, `message`) 
                VALUES 
                    (UNIX_TIMESTAMP(), ' . $user[0]['id'] . ', "' . $user[0]['username'] . '", "' . $this->user[0]['username'] 
                    . '", "Account Activation", "Activated the user: '.$user[0]['username'].'")');
                
                $GLOBALS['page']->Message("Account has been activated", 'Account Activation', 'id=' . $_GET['id']);
            }
            else{
                $GLOBALS['page']->Message("Account is already activated", 'Account Activation', 'id=' . $_GET['id']);
            }
        }
        else{
            $GLOBALS['page']->Message("User could not be found", 'User Search', 'id=' . $_GET['id']);
        }
        
    }
    
    //	Jump options
    protected function do_jump() {

        // Get library and instantiate
        require_once('./libs/villageSystem/respectLib.php');
        $respectLib = new respectLib();

        // Move village
        $result = $respectLib->moderator_jump( $_POST['village_choice'] );

        // Show message
        $GLOBALS['page']->Message($result, 'Moderator Jump Ability', 'id=' . $_GET['id']);
    }

    //	Edit orders
    protected function order_form() {
        try {
            
            // Get the info
            if (!($orders = $GLOBALS['database']->fetch_data("
                SELECT `user_notes`.`message` 
                FROM `user_notes`
                WHERE `user_notes`.`user_id` = 0 LIMIT 1"))) {
                throw new Exception('There was an error when obtaining mod orders, please try again!');
            }
            $ordersText = ($orders !== "0 rows") ? $orders[0]['message'] : '';
            
            // Check that the user is the leader            
            $GLOBALS['page']->UserInput( 
                "Write the agenda for the moderators in the field below.", 
                "Edit Moderator Orders", 
                array(
                    array("infoText"=>"",
                          "inputFieldName"=>"mod_orders",
                          "type"=>"textarea",
                          "inputFieldValue"=> $ordersText,
                          "maxlength" => 1500 
                    )
                ), 
                array(
                    "href"=>"?id=".$_GET['id'] ,
                    "submitFieldName"=>"Submit", 
                    "submitFieldText"=>"Submit Agenda"),
                "Return" 
             );

        } catch (Exception $e) {
            $GLOBALS['page']->Message( $e->getMessage() , 'Moderator Orders', 'id=' . $_GET['id'] . '');
        }
    }

    protected function edit_orders() {
        try {
            $GLOBALS['database']->transaction_start();
            if (!isset($_POST['mod_orders'])) {
                throw new Exception('1');
            }

            if (functions::ws_remove($_POST['mod_orders']) === '') {
                throw new Exception('2');
            }

            if (strlen($_POST['mod_orders']) > 1500) {
                throw new Exception('3');
            }

            $nindo_text = functions::store_content($_POST['mod_orders']);

            if (!($nindo = $GLOBALS['database']->fetch_data('SELECT `user_notes`.`message`
                FROM `user_notes` 
                WHERE `user_notes`.`user_id` = 0 LIMIT 1 FOR UPDATE'))) {
                throw new Exception('4');
            }

            if ($nindo !== "0 rows") {
                if (($GLOBALS['database']->execute_query('UPDATE `user_notes` 
                    SET `user_notes`.`message` = "' . $nindo_text . '" 
                    WHERE `user_id` = 0 LIMIT 1')) === false) {
                    throw new Exception('5');
                }
                $GLOBALS['page']->Message('Moderator Orders have been updated within the system!', 'Moderator Orders', 'id=' . $_GET['id']);
            } else { // Attempt to Re-Create and Insert Nindo if it doesn't exist
                if (($GLOBALS['database']->execute_query('INSERT INTO `user_notes` 
                        (`user_id`, `user`, `moderator`, `time`, `message`) 
                    VALUES 
                        (0, "Mod_Orders", "' . $this->user[0]['username'] . '", UNIX_TIMESTAMP(), "' . $nindo_text . '")')) === false) {
                    throw new Exception('6');
                }
                $GLOBALS['page']->Message('Moderator Orders could not be found in system, but it was successfully re-created!', 'Moderator Orders', 'id=' . $_GET['id']);
            }
            $GLOBALS['database']->transaction_commit();
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            switch ($e->getMessage()) {
                case('1'):
                case('2'): $GLOBALS['page']->Message('Moderator Orders cannot be left blank!', 'Moderator Orders', 'id=' . $_GET['id'] . '');
                    break;
                case('3'): $GLOBALS['page']->Message('Moderator Orders cannot be over 1500 characters!', 'Moderator Orders', 'id=' . $_GET['id'] . '');
                    break;
                case('4'): case('5'):
                case('6'): $GLOBALS['page']->Message('There was an error when obtaining Moderator Orders, please try again!', 'Moderator Orders', 'id=' . $_GET['id'] . '');
                    break;
                default: $GLOBALS['page']->Message('An error has occurred, please try again.', 'Moderator Orders', 'id=' . $_GET['id'] . '');
                    break;
            }
        }
    }

    // Notes system
    protected function view_note() {
        try {
            if (!isset($_GET['nid'])) {
                throw new Exception('1');
            }

            if (ctype_digit($_GET['nid']) === false) {
                throw new Exception('2');
            }

            if (!($result = $GLOBALS['database']->fetch_data('SELECT * FROM `admin_notes` 
                WHERE `admin_notes`.`id` = "' . $_GET['nid'] . '" LIMIT 1'))) {
                throw new Exception('3');
            }

            if ($result === '0 rows') {
                throw new Exception('4');
            }

            $GLOBALS['template']->assign('result', $result);
            $GLOBALS['template']->assign('contentLoad', './templates/content/moderator/mod_view_note.tpl');
        } catch (Exception $e) {
            switch ($e->getMessage()) {
                case('1'):
                case('2'): $GLOBALS['page']->Message('An Invalid Note ID was specified, please try again!', 'Administration Notes', 'id=' . $_GET['id']);
                    break;
                case('3'):
                case('4'): $GLOBALS['page']->Message("Administration Note could not be found in system!", 'Administration Notes', 'id=' . $_GET['id']);
                    break;
                default: $GLOBALS['page']->Message('An error has occurred, please try again.', 'Administration Notes', 'id=' . $_GET['id'] . '');
                    break;
            }
        }
    }

    protected function edit_note() {
        if (($this->user[0]['user_rank'] == 'Supermod') || ($this->user[0]['user_rank'] == 'Admin')) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_notes` WHERE `id` = '" . $_GET['nid'] . "'");
            if ($data !== "0 rows") {
                if (($data[0]['visibility'] != 'Admin') || ($this->user[0]['user_rank'] == 'Admin')) {
                    // Show form
                    tableParser::parse_form('admin_notes', 'Update note', array('id', 'time', 'posted_by'), $data);
                } else {
                    $GLOBALS['page']->Message("You do not have access to this note", 'Note System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This note does not exist", 'Note System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("You are not allowed to edit notes", 'Note System', 'id=' . $_GET['id']);
        }
    }

    protected function do_edit_note() {
        if (($this->user[0]['user_rank'] == 'Supermod') || ($this->user[0]['user_rank'] == 'Admin')) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_notes` WHERE `id` = '" . $_GET['nid'] . "'");
            if ($data !== "0 rows") {
                if (($data[0]['visibility'] != 'Admin') || ($this->user[0]['user_rank'] == 'Admin')) {
                    // Do update
                    if (tableParser::update_data('admin_notes', 'id', $_GET['nid'])) {
                        $GLOBALS['page']->Message("The note has been updated", 'Note System', 'id=' . $_GET['id']);
                    } else {
                        $GLOBALS['page']->Message("An error occured while updating the note", 'Note System', 'id=' . $_GET['id']);
                    }
                } else {
                    $GLOBALS['page']->Message("You do not have access to this note", 'Note System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This note does not exist", 'Note System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("You are not allowed to edit notes", 'Note System', 'id=' . $_GET['id']);
        }
    }

    protected function new_note() {

        // Show page
        if (($this->user[0]['user_rank'] == 'Supermod') || ($this->user[0]['user_rank'] == 'Admin')) {
            tableParser::parse_form('admin_notes', 'New note', array('id', 'posted_by', 'time'));
        } else {
            $GLOBALS['page']->Message("You are not allowed to add notes", 'Note System', 'id=' . $_GET['id']);
        }
    }

    protected function do_new_note() {
        if (($this->user[0]['user_rank'] == 'Supermod') || ($this->user[0]['user_rank'] == 'Admin')) {
            // Set data array
            $data['time'] = time();
            $data['posted_by'] = $this->user[0]['username'];

            // Run set data function
            if (tableParser::insert_data('admin_notes', $data)) {
                $GLOBALS['page']->Message("The note has been added", 'Note System', 'id=' . $_GET['id']);
            } else {
                $GLOBALS['page']->Message("An error occured when adding the note", 'Note System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("You are not allowed to add notes", 'Note System', 'id=' . $_GET['id']);
        }
    }

    protected function verify_delete_note() {
        try {
            if ($this->user[0]['user_rank'] !== 'Supermod') {
                if ($this->user[0]['user_rank'] !== 'Admin') {
                    throw new Exception('1');
                }
            }

            if (!($data = $GLOBALS['database']->fetch_data('SELECT * FROM `admin_notes` 
                WHERE `admin_notes`.`id` = "' . $_GET['nid'] . '" LIMIT 1'))) {
                throw new Exception('2');
            }

            if ($data === '0 rows') {
                throw new Exception('3');
            }

            if (($data[0]['visibility'] === 'Admin')) {
                if ($this->user[0]['user_rank'] !== 'Admin') {
                    throw new Exception('4');
                }
            }
            $GLOBALS['page']->Confirm("Delete this note?", 'Administration Notes', 'Delete Now!');
        } catch (Exception $e) {
            switch ($e->getMessage()) {
                case('1'): $GLOBALS['page']->Message("You don't have the ability to delete Administration Notes!", 'Administration Notes', 'id=' . $_GET['id']);
                    break;
                case('2'):
                case('3'): $GLOBALS['page']->Message("This Administration Note doesn't exist within the system!", 'Administration Notes', 'id=' . $_GET['id']);
                    break;
                case('4'): $GLOBALS['page']->Message("You don't have access to this Administration Note!", 'Administration Notes', 'id=' . $_GET['id']);
                    break;
                default: $GLOBALS['page']->Message('An error has occurred, please try again.', 'Administration Notes', 'id=' . $_GET['id'] . '');
                    break;
            }
        }
    }

    protected function do_delete_note() {
        try {
            $GLOBALS['database']->transaction_start();

            if ($this->user[0]['user_rank'] !== 'Supermod') {
                if ($this->user[0]['user_rank'] !== 'Admin') {
                    throw new Exception('1');
                }
            }

            if (!($data = $GLOBALS['database']->fetch_data('SELECT * FROM `admin_notes` 
                WHERE `admin_notes`.`id` = "' . $_GET['nid'] . '" LIMIT 1 FOR UPDATE'))) {
                throw new Exception('2');
            }

            if ($data === '0 rows') {
                throw new Exception('3');
            }

            if (($data[0]['visibility'] === 'Admin')) {
                if ($this->user[0]['user_rank'] !== 'Admin') {
                    throw new Exception('4');
                }
            }

            if (($GLOBALS['database']->execute_query("DELETE FROM `admin_notes` 
                WHERE `id` = '" . $_GET['nid'] . "' LIMIT 1")) === false) {
                throw new Exception('5');
            }
            $GLOBALS['page']->Message("The admin note has been deleted", 'Note System', 'id=' . $_GET['id']);
            $GLOBALS['database']->transaction_commit();
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            switch ($e->getMessage()) {
                case('1'): $GLOBALS['page']->Message("You don't have the ability to delete Administration Notes!", 'Administration Notes', 'id=' . $_GET['id']);
                    break;
                case('2'):
                case('3'): $GLOBALS['page']->Message("This Administration Note doesn't exist within the system!", 'Administration Notes', 'id=' . $_GET['id']);
                    break;
                case('4'): $GLOBALS['page']->Message("You don't have access to this Administration Note!", 'Administration Notes', 'id=' . $_GET['id']);
                    break;
                case('5'): $GLOBALS['page']->Message("There was an error during the Administration Note deletion!", 'Administration Notes', 'id=' . $_GET['id']);
                    break;
                default: $GLOBALS['page']->Message('An error has occurred, please try again.', 'Administration Notes', 'id=' . $_GET['id'] . '');
                    break;
            }
        }
    }

    //	Fire moderator:
    protected function fire_form() {
        try {
            if (!($mods = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users`.`id` 
                FROM `users`, `users_statistics` 
                WHERE `users_statistics`.`uid` = `users`.`id` AND `users_statistics`.`user_rank` = 'Moderator' 
                    ORDER BY `users`.`username` ASC"))) {
                throw new Exception('1');
            }
            $GLOBALS['template']->assign('mods', $mods);
        } catch (Exception $e) {
            switch ($e->getMessage()) {
                case('1'): $GLOBALS['page']->Message("There was an error retrieving moderators, please try again.", 'Moderator HQ', 'id=' . $_GET['id']);
                    break;
                default: $GLOBALS['page']->Message("An error has occurred, please try again.", 'Moderator HQ', 'id=' . $_GET['id']);
                    break;
            }
        }
    }

    protected function do_fire() {
        try {
            $GLOBALS['database']->transaction_start();
            if (!isset($_POST['fire_user'])) {
                throw new Exception('1');
            }

            if (ctype_digit($_POST['fire_user']) === false) {
                throw new Exception('2');
            }

            if (!($user = $GLOBALS['database']->fetch_data('SELECT `users_statistics`.`user_rank`, 
                `users`.`username`, `users`.`id` 
                FROM `users`, `users_statistics` 
                WHERE `users`.`id` = "' . $_POST['fire_user'] . '" AND `users_statistics`.`uid` = `users`.`id`
                    AND `users_statistics`.`user_rank` = "Moderator" LIMIT 1 FOR UPDATE'))) {
                throw new Exception('3');
            }

            if ($user === "0 rows") {
                throw new Exception('4');
            }

            if (($GLOBALS['database']->execute_query('UPDATE `users_statistics`, `users`
                                SET `users_statistics`.`user_rank` = "Member", `users`.`logout_timer` = UNIX_TIMESTAMP()
                                WHERE `users_statistics`.`uid` = ' . $user[0]['id'] . ' 
                                        AND `users`.`id` = `users_statistics`.`uid`')) === false) {
                throw new Exception('5');
            }

            $GLOBALS['page']->Message('You have fired ' . $user[0]['username'] . ' from being a Moderator!', 'Moderator HQ', 'id=' . $_GET['id']);
            $GLOBALS['database']->transaction_commit();
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            switch ($e->getMessage()) {
                case('1'):
                case('2'): $GLOBALS['page']->Message("You selected an invalid user, please try again!", 'Moderator HQ', 'id=' . $_GET['id']);
                    break;
                case('3'): $GLOBALS['page']->Message("There was an error trying to receive the moderator, please try again!", 'Moderator HQ', 'id=' . $_GET['id']);
                    break;
                case('4'): $GLOBALS['page']->Message("This user is not a moderator within the system!", 'Moderator HQ', 'id=' . $_GET['id']);
                    break;
                case('5'): $GLOBALS['page']->Message("There was an error trying to fire the moderator, please try again!", 'Moderator HQ', 'id=' . $_GET['id']);
                    break;
                default: $GLOBALS['page']->Message("An error has occurred, please try again.", 'Moderator HQ', 'id=' . $_GET['id']);
                    break;
            }
        }
    }

    //	Hire moderator:
    protected function do_hire() {
        try {
            $GLOBALS['database']->transaction_start();
            if (!isset($_POST['username_hire'])) {
                throw new Exception('1');
            }

            if (functions::ws_remove($_POST['username_hire']) === '') {
                throw new Exception('2');
            }

            if (!($user = $GLOBALS['database']->fetch_data('SELECT `users`.`username`, `users`.`id`, `users`.`federal_timer`,
                `users_statistics`.`user_rank`
                FROM `users`, `users_statistics` 
                WHERE `users`.`username` = "' . $_POST['username_hire'] . '" 
                    AND `users_statistics`.`uid` = `users`.`id` 
                    AND (`users_statistics`.`user_rank` = "Member" 
                    OR `users_statistics`.`user_rank` = "Paid") LIMIT 1 FOR UPDATE'))) {
                throw new Exception('3');
            }

            if ($user === "0 rows") {
                throw new Exception('4');
            }

            if ($user[0]['user_rank'] == 'Member') {
                if (($GLOBALS['database']->execute_query('UPDATE `users_statistics` 
                    SET `users_statistics`.`user_rank` = "Moderator" 
                    WHERE `users_statistics`.`uid` = ' . $user[0]['id'] . ' LIMIT 1')) === false) {
                    throw new Exception('5');
                }
            } else { // Remove Federal upon Rank change
                if (($GLOBALS['database']->execute_query('UPDATE `users`, `users_statistics` 
                    SET `users`.`federal_timer` = UNIX_TIMESTAMP(), 
                        `users_statistics`.`user_rank` = "Moderator" 
                    WHERE `users`.`id` = "' . $user[0]['id'] . '" AND `users_statistics`.`uid` = `users`.`id`')) === false) {
                    throw new Exception('6');

                    $users_notifications = new NotificationSystem('', $user[0]['id']);

                    $users_notifications->addNotification(array(
                                                                'duration' => 'none',
                                                                'text' => "Your federal support has been removed due to becoming a Moderator!",
                                                                'dismiss' => 'yes'
                                                            ));

                    $users_notifications->recordNotifications();
                }
            }
            $GLOBALS['page']->Message('You have appointed ' . $user[0]['username'] . ' as a moderator', 'Moderator System', 'id=' . $_GET['id']);
            $GLOBALS['database']->transaction_commit();
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            switch ($e->getMessage()) {
                case('1'):
                case('2'): $GLOBALS['page']->Message("You entered an invalid user, please try again!", 'Moderator HQ', 'id=' . $_GET['id']);
                    break;
                case('3'):
                case('4'): $GLOBALS['page']->Message("This user is already a staff member or doesn't exist!", 'Moderator HQ', 'id=' . $_GET['id']);
                    break;
                case('5'):
                case('6'): $GLOBALS['page']->Message("There was an error updating the user to a Moderator!", 'Moderator HQ', 'id=' . $_GET['id']);
                    break;
                default: $GLOBALS['page']->Message("An error has occurred, please try again.", 'Moderator HQ', 'id=' . $_GET['id']);
                    break;
            }
        }
    }

    //	Warn user:
    protected function give_warning() {
        try {
            if (functions::ws_remove($_POST['warn_username']) === '') {
                throw new Exception('1');
            }

            if (functions::ws_remove($_POST['warn_message']) === '') {
                throw new Exception('2');
            }

            if (functions::ws_remove($_POST['warn_reason']) === '') {
                throw new Exception('3');
            }

            if (!($user = $GLOBALS['database']->fetch_data('SELECT `users`.`username`, `users`.`id`
                FROM `users` 
                WHERE `users`.`username` = "' . $_POST['warn_username'] . '" LIMIT 1 FOR UPDATE'))) {
                throw new Exception('4');
            }

            if ($user === '0 rows') {
                throw new Exception('5');
            }

            //	Log the warning:
            echo"GIVING WARNING";
            if (($GLOBALS['database']->execute_query('INSERT INTO `moderator_log` 
                    (`time`, `uid`, `username`, `moderator`, `reason`, `message`) 
                VALUES 
                    (UNIX_TIMESTAMP(), ' . $user[0]['id'] . ', "' . $user[0]['username'] . '", "' . $this->user[0]['username'] 
                    . '", "' . $_POST['warn_reason'] . '", "' . functions::store_content($_POST['warn_message']) . '")')) === false) {
                throw new Exception('6');
            }

            //	Send the warning message:
            if (($GLOBALS['database']->execute_query("INSERT INTO `users_pm` 
                    (`sender_uid`, `receiver_uid`, `time`, `message`, `subject`) 
                VALUES 
                    ('" . $this->user[0]['id'] . "', '" . $user[0]['id'] . "', UNIX_TIMESTAMP(), 
                        '" . functions::store_content($_POST['warn_message']) . "', 'Official Warning!')")) === false) {
                throw new Exception('7');
            }

            $users_notifications = new NotificationSystem('', $user[0]['id']);

            $users_notifications->addNotification(array(
                                                        'id' => 15,
                                                        'duration' => 'none',
                                                        'text' => "You have received an OFFICIAL warning, which can be found in your inbox. Please read it as soon as possible!",
                                                        'dismiss' => 'yes'
                                                    ));

            $users_notifications->recordNotifications();

            //	Output success:
            $GLOBALS['page']->Message("Your warning has been sent", 'Warning System', 'id=' . $_GET['id']);
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            switch ($e->getMessage()) {
                case('1'): $GLOBALS['page']->Message("You did not specify a username", 'Moderator Warning System', 'id=' . $_GET['id']);
                    break;
                case('2'): $GLOBALS['page']->Message("You did not specify a message to send to the user", 'Moderator Warning System', 'id=' . $_GET['id']);
                    break;
                case('3'): $GLOBALS['page']->Message("You did not specify a reason", 'Moderator Warning System', 'id=' . $_GET['id']);
                    break;
                case('4'):
                case('5'): $GLOBALS['page']->Message("This user does not exist within the system!", 'Moderator Warning System', 'id=' . $_GET['id']);
                    break;
                case('6'): 
                case('7'):
                case('8'): $GLOBALS['page']->Message("There was an error trying to implement the Warning, please try again! - ".$e->getMessage(), 'Moderator Warning System', 'id=' . $_GET['id']);
                    break;
                default: $GLOBALS['page']->Message("An error has occurred, please try again.", 'Moderator Warning System', 'id=' . $_GET['id']);
                    break;
            }
        }
    }

    //	Ban user:
    protected function give_ban() {
        if (functions::ws_remove($_POST['ban_username']) !== '') {
            $user = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users`.`village`, `users`.`id`, `users`.`ban_time`, `users_statistics`.`user_rank`, `villages`.`leader` 
                FROM `users`, `users_statistics`, `villages` 
                WHERE `users`.`username` = '" . $_POST['ban_username'] . "' AND `villages`.`name` = `users`.`village` 
                    AND `users_statistics`.`uid` = `users`.`id` LIMIT 1");
            if ($user !== "0 rows") {
                if (((int) $user[0]['ban_time'] === 0) || ($user[0]['ban_time'] < time())) {
                    $check_rank = array('Member', 'Paid', 'Moderator', 'Event', 'EventMod');
                    if (in_array($user[0]['user_rank'], $check_rank, true)) {
                        if (functions::ws_remove($_POST['ban_message']) !== '') {
                            if (functions::ws_remove($_POST['ban_reason']) !== '') {
                                if ($user[0]['ban_time'] < time()) {
                                    //    Determine the bantime:
                                    switch ($_POST['game_ban_time']) {
                                        case('1 Hour'): $bantime = time() + 3600;
                                            break;
                                        case('1 Day'): $bantime = time() + 86400;
                                            break;
                                        case('3 Days'): $bantime = time() + 259200;
                                            break;
                                        case('1 Week'): $bantime = time() + 604800;
                                            break;
                                        case('2 Weeks'): $bantime = time() + 1209600;
                                            break;
                                        default: $bantime = 0;
                                            break;
                                    }

                                    //    Log the ban:
                                    $GLOBALS['database']->execute_query("INSERT INTO `moderator_log` 
                                            (`time`, `uid`, `username`, `duration`, `moderator`, `action`, `reason`, `message`) 
                                        VALUES 
                                            (UNIX_TIMESTAMP(), '" . $user[0]['id'] . "', '" . $user[0]['username'] 
                                            . "', '" . $_POST['game_ban_time'] . "', '" . $this->user[0]['username']
                                            . "', 'ban', '" . $_POST['ban_reason'] . "', '" . functions::store_content($_POST['ban_message']) . "');");

                                    //    Ban the user
                                    if ($user[0]['leader'] === $user[0]['username']) {
                                        $GLOBALS['database']->execute_query("
                                            UPDATE `users`, `villages` 
                                            SET `users`.`login_id` = DEFAULT, 
                                                `users`.`ban_time` = '" . $bantime . "', 
                                                `users`.`logout_timer` = UNIX_TIMESTAMP(),
                                                `villages`.`leader` = '".Data::$VILLAGE_KAGENAMES[ $user[0]['village'] ]."'
                                            WHERE `users`.`id` = '" . $user[0]['id'] . "' AND `villages`.`leader` = '" . $user[0]['username'] . "'");
                                    } else {
                                        $GLOBALS['database']->execute_query("UPDATE `users` 
                                            SET `users`.`login_id` = DEFAULT, `users`.`logout_timer` = UNIX_TIMESTAMP(), `users`.`ban_time` = '" . $bantime . "'
                                            WHERE `users`.`id` = '" . $user[0]['id'] . "' LIMIT 1");
                                    }

                                    //    Output success:
                                    $GLOBALS['page']->Message($user[0]['username'] . ' has been banned.', 'Ban System', 'id=' . $_GET['id']);
                                } else {
                                    $GLOBALS['page']->Message($user[0]['username'] . ' is already banned.', 'Ban System', 'id=' . $_GET['id']);
                                }
                            } else {
                                $GLOBALS['page']->Message("You did not specify a reason", 'Ban System', 'id=' . $_GET['id']);
                            }
                        } else {
                            $GLOBALS['page']->Message("You did not specify a message to send to the user", 'Ban System', 'id=' . $_GET['id']);
                        }
                    } else {
                        $GLOBALS['page']->Message("You cannot ban administrators or supermods", 'Ban System', 'id=' . $_GET['id']);
                    }
                } else {
                    $GLOBALS['page']->Message("User is already banned", 'Ban System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This user does not exist", 'Ban System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("You did not specify a username", 'Ban System', 'id=' . $_GET['id']);
        }
    }

    protected function do_unban() {
        if (isset($_POST['unban_uid']) && is_numeric($_POST['unban_uid']) && isset($_POST['unban_time'])) {
            if (strlen($_POST['override_reason']) > 10) {
                $user = $GLOBALS['database']->fetch_data("SELECT `username`, `id`, `ban_time` FROM `users` WHERE `id` = '" . $_POST['unban_uid'] . "' LIMIT 1");
                if (($user !== "0 rows") && ($user[0]['ban_time'] > 0)) {
                    $GLOBALS['database']->execute_query("UPDATE `moderator_log`, `users` 
                        SET `moderator_log`.`override_reason` = '" . functions::store_content($_POST['override_reason']) . "', 
                            `moderator_log`.`override_by` = '" . $this->user[0]['username'] . "', `users`.`ban_time` = DEFAULT 
                        WHERE `moderator_log`.`uid` = '" . $user[0]['id'] . "' AND `moderator_log`.`time` = '" . $_POST['unban_time'] . "' 
                            AND `users`.`id` = `moderator_log`.`uid`");

                    $GLOBALS['page']->Message('You have unbanned ' . $user[0]['username'], 'Ban System', 'id=' . $_GET['id']);
                } else {
                    //	This user does not exist?
                    $GLOBALS['page']->Message("This user does not exist, or is not currently banned", 'Ban System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("You did not specify a valid reason for unbanning this user", 'Ban System', 'id=' . $_GET['id']);
            }
        } else {
            //	no or invalid user set.
            $GLOBALS['page']->Message("You did not specify a user", 'Ban System', 'id=' . $_GET['id']);
        }
    }

    protected function do_reduce() {
        // Reductions & Extensions use the same initial ban time to group together
        // All actions have separate and unique action IDs associated to them
        if (isset($_POST['reduce_uid']) && (functions::ws_remove($_POST['reduce_uid']) !== '')) {
            $user = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users`.`id`, `users`.`ban_time`,
                `moderator_log`.`time`, `moderator_log`.`reason`
                FROM `users`, `users_statistics`, `moderator_log`
                WHERE `users`.`id` = '" . $_POST['reduce_uid'] . "' AND `moderator_log`.`uid` = `users`.`id` 
                    AND `moderator_log`.`time` = '" . $_POST['reduce_time'] . "' LIMIT 1");

            if ($user !== "0 rows") {
                if ($user[0]['ban_time'] > time()) {
                    if (isset($_POST['override_reason']) && (functions::ws_remove($_POST['override_reason']) !== '') && (strlen(functions::ws_remove($_POST['override_reason'])) > 10)) {
                        // Determine the bantime
                        switch ($_POST['reduce_ban_time']) {
                            case('1 Hour'): $newbantime = 3600 + $_POST['reduce_time'];
                                break; // 1 Hour
                            case('1 Day'): $newbantime = 86400 + $_POST['reduce_time'];
                                break; // 1 Day
                            case('3 Days'): $newbantime = 259200 + $_POST['reduce_time'];
                                break; // 3 Days
                            case('1 Week'): $newbantime = 604800 + $_POST['reduce_time'];
                                break; // 1 Week
                            case('2 Weeks'): $newbantime = 1209600 + $_POST['reduce_time'];
                                break; // 2 Weeks
                            default: $newbantime = 0;
                                break; // Faulty Reduction Time
                        }

                        if ($newbantime !== 0) {
                            //    Log the ban, then ban the user
                            if ($GLOBALS['database']->execute_query("INSERT INTO `moderator_log` 
                                    (`time`, `uid`, `username`, `duration`, `moderator`, `action`, `reason`, `message`) 
                                VALUES
                                    ('" . $user[0]['time'] . "', '" . $user[0]['id'] . "', '" . $user[0]['username'] 
                                    . "', '" . $_POST['reduce_ban_time'] . "', '" . $this->user[0]['username'] 
                                    . "', 'reduction', '" . $user[0]['reason'] . "', '" . $_POST['override_reason'] . "');") &&
                                    $GLOBALS['database']->execute_query("UPDATE `users`
                                        SET `users`.`ban_time` = '" . $newbantime . "', `users`.`logout_timer` = UNIX_TIMESTAMP()
                                        WHERE `users`.`id` = '" . $user[0]['id'] . "' LIMIT 1")) {
                                //    Output success:
                                $GLOBALS['page']->Message('The ban regarding ' . $user[0]['username'] . ' (UID: ' . $user[0]['id'] . ') has been reduced to a ' . $_POST['reduce_ban_time'] . ' ban!', 'Ban System', 'id=' . $_GET['id']);
                            } else {
                                // Output Failure
                                $GLOBALS['page']->Message('The ban regarding ' . $user[0]['username'] . ' (UID: ' . $user[0]['id'] . ') has failed due to a system failure!', 'Ban System', 'id=' . $_GET['id']);
                            }
                        } else {
                            // Output Failure
                            $GLOBALS['page']->Message('The ban regarding ' . $user[0]['username'] . ' (UID: ' . $user[0]['id'] . ') has failed due to a faulty reduction choice!', 'Ban System', 'id=' . $_GET['id']);
                        }
                    } else {
                        $GLOBALS['page']->Message("You did not specify a good or valid reason!", 'Ban System', 'id=' . $_GET['id']);
                    }
                } else {
                    $GLOBALS['page']->Message("User is no longer or hasn't been banned within the system!", 'Ban System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This user does not exist!", 'Ban System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("An error occurring trying to obtain User's Record Data!", 'Ban System', 'id=' . $_GET['id']);
        }
    }

    protected function show_banlog() {
        $banned = $GLOBALS['database']->fetch_data("SELECT `username`, `id`, `ban_time` 
            FROM `users` 
            WHERE (`ban_time` > UNIX_TIMESTAMP()) AND (`perm_ban` != '1' && `perm_ban` != 1) ORDER BY `username`");
        $GLOBALS['template']->assign('banned', $banned);
        $ban_arr = array();
        if ($banned !== '0 rows') {
            for ($i = 0; $i < count($banned); $i++) {
                $ban_record = $GLOBALS['database']->fetch_data("SELECT `moderator`, `reason`, `time` 
                    FROM `moderator_log` 
                    WHERE `uid` = '" . $banned[$i]['id'] . "' AND (`action` = 'ban' OR `action` = 'reduction' OR `action` = 'extension') 
                        ORDER BY `id` DESC LIMIT 1");
                if ($ban_record === '0 rows') { // No Result?
                    $recent = array('moderator' => 'None', 'reason' => 'Unspecified', 'time' => 0);
                } else { // Return Latest Result
                    $recent = array('moderator' => $ban_record[0]['moderator'], 'reason' => $ban_record[0]['reason'],
                        'time' => $ban_record[0]['time']);
                }
                array_push($ban_arr, $recent);
            }
        }
        $GLOBALS['template']->assign('temp', $ban_arr);
        $GLOBALS['template']->assign('contentLoad', './templates/content/moderator/mod_show_banlog.tpl');
    }

    //    Tavern ban user:
    protected function give_tavernban() {
        if (isset($_POST['user_tban']) && (functions::ws_remove($_POST['user_tban']) !== '')) {
            $user = $GLOBALS['database']->fetch_data("
                SELECT `username`, `id`, `user_rank` 
                FROM `users`,`users_statistics` WHERE 
                    `username` = '" . $_POST['user_tban'] . "' AND
                    `uid` = `id`");
            if ($user !== "0 rows") {
                if (isset($_POST['tban_message']) && (functions::ws_remove($_POST['user_tban']) !== '')) {
                    if (isset($_POST['tban_reason']) && (functions::ws_remove($_POST['user_tban']) !== '')) {
                        if (($user[0]['user_rank'] === 'Member') || ($user[0]['user_rank'] === 'Paid')) {
                            //    Determine the bantime:
                            switch ($_POST['tavern_ban_time']) {
                                case('1 Hour'): $bantime = time() + 3600;
                                    break;
                                case('12 Hours'): $bantime = time() + 43200;
                                    break;
                                case('1 Day'): $bantime = time() + 86400;
                                    break;
                                case('3 Days'): $bantime = time() + 259200;
                                    break;
                                case('1 Week'): $bantime = time() + 604800;
                                    break;
                                case('2 Weeks'): $bantime = time() + 1209600;
                                    break;
                                default: $bantime = 0;
                                    break;
                            }

                            //    Log the warning:
                            $GLOBALS['database']->execute_query("INSERT INTO `moderator_log` 
                                    (`time`, `uid`, `username`, `moderator`, `action`, `reason`, `message`, `duration`) 
                                VALUES
                                    (UNIX_TIMESTAMP(), '" . $user[0]['id'] . "', '" . $user[0]['username']
                                    . "', '" . $this->user[0]['username'] . "', 'tavern-ban', '" . $_POST['tban_reason']
                                    . "', '" . functions::store_content($_POST['tban_message']) . "', '" . $_POST['tavern_ban_time'] . "');");

                            //    Send the warning message:
                            $GLOBALS['database']->execute_query("INSERT INTO `users_pm` 
                                    (`sender_uid`, `receiver_uid`, `time`, `message`, `subject`) 
                                VALUES
                                    ('" . $this->user[0]['id'] . "','" . $user[0]['id'] . "', UNIX_TIMESTAMP(), '"
                                        . functions::store_content($_POST['tban_message']) 
                                    . "', 'You have been banned from the tavern: " . $_POST['tavern_ban_time'] . "')");

                            $GLOBALS['database']->execute_query("UPDATE `users` 
                                SET `new_pm` = `new_pm` + 1, `post_ban` = 1, `tban_time` = '" . $bantime . "' 
                                WHERE `id` = '" . $user[0]['id'] . "' LIMIT 1");

                            //    Output success:
                            $GLOBALS['page']->Message($user[0]['username'] . ' has been tavern-banned', 'Ban System', 'id=' . $_GET['id']);
                        } else {
                            $GLOBALS['page']->Message("You cannot tavern-ban staff members", 'Ban System', 'id=' . $_GET['id']);
                        }
                    } else {
                        $GLOBALS['page']->Message("You did not specify a reason", 'Ban System', 'id=' . $_GET['id']);
                    }
                } else {
                    $GLOBALS['page']->Message("You did not specify a message to send to the user", 'Ban System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This user does not exist", 'Ban System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("You did not specify a username", 'Ban System', 'id=' . $_GET['id']);
        }
    }

    protected function un_tavern_ban() {
        if (isset($_POST['untban_uid']) && is_numeric($_POST['untban_uid']) && isset($_POST['untban_time'])) {
            if (strlen($_POST['override_reason']) > 10) {
                $user = $GLOBALS['database']->fetch_data("SELECT `username`, `id`, `ban_time` FROM `users` WHERE `id` = '" . $_POST['untban_uid'] . "' LIMIT 1");
                if ($user !== "0 rows") {
                    $GLOBALS['database']->execute_query("UPDATE `moderator_log`, `users` 
                        SET `moderator_log`.`override_reason` = '" . functions::store_content($_POST['override_reason']) . "', 
                            `moderator_log`.`override_by` = '" . $this->user[0]['username'] . "', `users`.`post_ban` = 0 
                        WHERE `moderator_log`.`uid` = '" . $user[0]['id'] . "' AND `moderator_log`.`time` = '" . $_POST['untban_time'] . "' 
                            AND `users`.`id` = `moderator_log`.`uid`");
                    $GLOBALS['page']->Message('You have unbanned ' . $user[0]['username'] . ' from the tavern', 'Ban System', 'id=' . $_GET['id']);
                } else {
                    //	This user does not exist?
                    $GLOBALS['page']->Message("This user does not exist, or is not currently banned.", 'Ban System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("You did not specify a valid reason for unbanning this user.", 'Ban System', 'id=' . $_GET['id']);
            }
        } else {
            //	no or invalid user set.
            $GLOBALS['page']->Message("You did not specify a user.", 'Ban System', 'id=' . $_GET['id']);
        }
    }

    protected function tavern_ban_log() {
        $ban_arry = array();
        if (!isset($_GET['min']) || !is_numeric($_GET['min']) || ($_GET['min'] < 0)) {
            $min = $newminm = 0;
            $newmini = 20;
        } else {
            $min = $_GET['min'];
            $newminm = $min - 20;
            $newminm = ($newminm < 0) ? 0 : $newminm;
            $newmini = $min + 20;
        }

        if (isset($_POST['tban_search'])) {
            $users = $GLOBALS['database']->fetch_data("SELECT `username`, `id` 
                FROM `users` 
                WHERE `post_ban` = 1 AND `username` = '" . addslashes($_POST['tban_search']) . "'");
        } else {
            $users = $GLOBALS['database']->fetch_data("SELECT `username`, `id` 
                FROM `users` 
                WHERE `post_ban` = 1 ORDER BY `username` LIMIT " . $min . ",20");
        }
        $GLOBALS['template']->assign('users', $users);

        if ($users !== "0 rows") {
            for ($i = 0; $i < count($users); $i++) {
                $temp = $GLOBALS['database']->fetch_data("SELECT * FROM `moderator_log` WHERE `uid` = '" . $users[$i]['id'] . "' AND `action` = 'tavern-ban' ORDER BY `id` DESC LIMIT 1");
                if ($temp === "0 rows") {
                    unset($temp);
                    $temp[0]['moderator'] = 'Unknown';
                    $temp[0]['reason'] = 'Unknown';
                    $temp[0]['time'] = time();
                }
                array_push($ban_arry, $temp);
            }
        }
        $GLOBALS['template']->assign('temp', $ban_arry);
        $GLOBALS['template']->assign('newmini', $newmini);
        $GLOBALS['template']->assign('newminm', $newminm);
        $GLOBALS['template']->assign('contentLoad', './templates/content/moderator/mod_tavern_ban_log.tpl');
    }

    //	User Check.
    protected function show_user_sheet() {
        //    Fetch user:
        if (isset($_POST['Check_User'])) {
            $name = $_POST['check_username'];
            $id = "N/A";
        } elseif (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            $id = $_GET['uid'];
            $name = "N/A";
        }
        
        //    Show output:
        if ((isset($name) && (functions::ws_remove($name) !== '')) || (isset($id) && ($id !== "N/A"))) {
            
            // Correct ID & Name based on moderator log
            $sheet = $GLOBALS['database']->fetch_data("SELECT * FROM `moderator_log` WHERE (`uid` = '" . $id . "' OR `username` = '" . $name . "') AND (`action` = 'Ban' OR `action` = 'Tavern-Ban' OR `action` = 'Permanent' OR `action` = 'Warning') ORDER BY `id` DESC");
            $location = "";
            if ($sheet === "0 rows") {
                if (functions::ws_remove($id) === "") {
                    $id = "N/A";
                }
                if (functions::ws_remove($name) === "") {
                    $name = "N/A";
                }
            }
            if( $sheet != "0 rows" ){
                $id = $sheet[count($sheet) - 1]['uid'];
                $name = $sheet[count($sheet) - 1]['username'];
                if ($id == "") {
                    $id = "N/A";
                }
                if ($name == "") {
                    $name = "N/A";
                }
            }
            // Correct record based on data from users table
            $user_data = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `id` = '" . $id . "' OR `username` = '" . $name . "' LIMIT 1");
            
            if ( $id !== "N/A" && $user_data !== "0 rows") {
                if ( $id !== $user_data[0]['id'] && $name == $user_data[0]['username']) {
                    $GLOBALS['template']->assign('warning', "Any records from before <b>" . date("F j, Y, g:i a", $user_data[0]['join_date']) . "</b> do NOT belong to this user.<br>");
                }
            }
            if ($user_data !== "0 rows") {
                $id = $user_data[0]['id'];
                $name = $user_data[0]['username'];
                $location = "" . $user_data[0]['longitude'] . ":" . $user_data[0]['latitude'] . "";
            }
            
            // Send to smarty
            $GLOBALS['template']->assign('mod_rank', $this->user[0]['user_rank']);
            $GLOBALS['template']->assign('username', $name);
            $GLOBALS['template']->assign('userid', $id);
            $GLOBALS['template']->assign('userlocation', $location);
            $GLOBALS['template']->assign('sheet', $sheet);

            //    Show reports against this user: (except unfounded)
            $data = $GLOBALS['database']->fetch_data("SELECT `uid`, `rid`, `type`, `time`, `status` FROM `user_reports` WHERE `status` != 'ungrounded' AND `uid` = '" . $id . "' ORDER BY `time` DESC");
            $GLOBALS['template']->assign('reports', $data);

            // Discussion about user
            $user = $GLOBALS['database']->fetch_data("SELECT `id`, `username`, `last_UA`, `perm_ban` FROM `users` WHERE `id` = '" . $id . "' LIMIT 1");
            if ($user !== "0 rows") {
                // Extra notices
                $extra = "";
                if (isset($user[0]['last_UA']) && ($user[0]['last_UA'] !== "")) {
                    $extra .= '<b>Last UA:</b> ' . $user[0]['last_UA'] . '<br>';
                }
                // Tavern-like ting
                if (isset($user[0]['perm_ban']) && ($user[0]['perm_ban'] == 1)) {
                    $extra .= '<b>THIS USER IS CURRENTLY PERMANENTLY BANNED</b>';
                }
                $GLOBALS['template']->assign('extraNotices', $extra);

                $tavern = $GLOBALS['database']->fetch_data("SELECT * FROM `user_notes` WHERE `user` = '" . $name . "' ORDER BY `time`");
                $GLOBALS['template']->assign('tavern', $tavern);
            }
            
            // Get name changes
            $edits = $GLOBALS['database']->fetch_data("
                SELECT *
                FROM `log_namechanges` 
                WHERE 
                    `uid` = '".$id."' OR 
                    `oldName` = '".$name."'
                ORDER BY `time` DESC");
            
            if( $edits !== "0 rows" ){
                foreach( $edits as $key => $edit ){
                    $edits[$key]['oldName'] = "<a href='?id=".$_GET['id']."&act=check_user&uid=".$edit['uid']."'>".$edit['oldName']."</a>";
                    $edits[$key]['newName'] = "<a href='?id=".$_GET['id']."&act=check_user&uid=".$edit['uid']."'>".$edit['newName']."</a>";
                }
            }
            
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
            'Click usernames to search the user in question on his/her userID instead of username'
            );
            
            // Loop through name changes and possibly set a warning
            if( $name !== "N/A" && isset($_POST['check_username'])){
                foreach( $edits as $edit ){
                    if( $edit['oldName'] == $_POST['check_username'] ){
                        $GLOBALS['template']->assign('warning', $edit['oldName']." changed name to ".$edit['newName']." on <b>" . date("F j, Y, g:i a", $edit['time']) . "</b>. <br>Records before this date belong to ".$edit['newName']."<br>");
                    }
                }
            }

            // Smarty template
            $GLOBALS['template']->assign('contentLoad', './templates/content/moderator/mod_user_record.tpl');
        } else {
            $GLOBALS['page']->Message("No user was specified or this user does not exist", 'User Record System', 'id=' . $_GET['id']);
        }
    }

    protected function show_details() {
        // Array to hold Check Variables
        $checksum = array(isset($_GET['time']), isset($_GET['uid']), isset($_GET['action_id']),
            is_numeric($_GET['time']), is_numeric($_GET['uid']), is_numeric($_GET['action_id']));

        // If all variables are true, proceed to fetch data
        if (count(array_keys($checksum, true)) === count($checksum)) {
            $data = $GLOBALS['database']->fetch_data("SELECT `moderator_log`.* FROM `moderator_log` WHERE `time` = '" . $_GET['time'] . "' AND `uid` = '" . $_GET['uid'] . "' AND `id` = '" . $_GET['action_id'] . "'");
        } else { // Otherwise, return nothing
            $data = '0 rows';
        }

        if ($data !== '0 rows') {
            // Send to smarty
            $GLOBALS['template']->assign('data', $data);
            $data[0]['message'] = functions::parse_BB($data[0]['message']);
            $data[0]['override_reason'] = functions::parse_BB($data[0]['override_reason']);
            $GLOBALS['template']->assign('contentLoad', './templates/content/moderator/mod_record_details.tpl');
        } else {
            $GLOBALS['page']->Message("This violation does not exist.", 'User Record System', 'id=' . $_GET['id']);
        }
    }

    protected function deleterecord() {
        try {
            if ($this->user[0]['user_rank'] !== 'Supermod') {
                if ($this->user[0]['user_rank'] !== 'Admin') {
                    throw new Exception('1');
                }
            }

            if (ctype_digit($_GET['time']) === false) {
                throw new Exception('2');
            }

            if (ctype_digit($_GET['uid']) === false) {
                throw new Exception('3');
            }

            if (!($record = $GLOBALS['database']->fetch_data("SELECT * FROM `moderator_log` 
                WHERE `uid` = '" . $_GET['uid'] . "' AND `time` = '" . $_GET['time'] . "' LIMIT 1"))) {
                throw new Exception('4');
            }

            if ($record === '0 rows') {
                throw new Exception('5');
            }

            $GLOBALS['template']->assign('confirmDeletion', 1);
            $this->show_details();
        } catch (Exception $e) {
            switch ($e->getMessage()) {
                case('1'): $GLOBALS['page']->Message("You are not allowed to delete records.", 'User Record System', 'id=' . $_GET['id']);
                    break;
                case('2'):
                case('3'): $GLOBALS['page']->Message("The data is corrupted, please try again!", 'User Record System', 'id=' . $_GET['id']);
                    break;
                case('4'): $GLOBALS['page']->Message("There was an error trying to receive the record!", 'User Record System', 'id=' . $_GET['id']);
                    break;
                case('5'): $GLOBALS['page']->Message("Record could not be found within the system!", 'User Record System', 'id=' . $_GET['id']);
                    break;
                default: $GLOBALS['page']->Message("An error has occurred, please try again.", 'User Record System', 'id=' . $_GET['id']);
                    break;
            }
        }
    }

    protected function deleterecord_done() {
        try {
            $GLOBALS['database']->transaction_start();
            if ($this->user[0]['user_rank'] !== 'Supermod') {
                if ($this->user[0]['user_rank'] !== 'Admin') {
                    throw new Exception('1');
                }
            }

            if (ctype_digit($_GET['time']) === false) {
                throw new Exception('2');
            }

            if (ctype_digit($_GET['uid']) === false) {
                throw new Exception('3');
            }

            if (!($record = $GLOBALS['database']->fetch_data("SELECT `moderator_log`.*, 
                `users`.`ban_time`, `users`.`post_ban`
                FROM `moderator_log`, `users`
                WHERE `uid` = '" . $_GET['uid'] . "' 
                    AND `time` = '" . $_GET['time'] . "' LIMIT 1 FOR UPDATE"))) {
                throw new Exception('4');
            }

            if ($record === '0 rows') {
                throw new Exception('5');
            }

            $change_message = addslashes("User record deleted. <br><br>
                <b>Record Reason:</b><i> " . $record[0]['reason'] . "</i><br><br>
                <b>Record Message: </b><i>" . $record[0]['message'] . "</i>");

            if (($GLOBALS['database']->execute_query('INSERT INTO 
                `admin_edits` 
                    (`time` , `aid`, `uid`, `changes`, `IP`) 
                VALUES
                    (UNIX_TIMESTAMP(), "' . $this->user[0]['username'] . '", "' . $_GET['uid'] . '", 
                    "' . $change_message . '", "' . $GLOBALS['user']->real_ip_address() . '")')) === false) {
                throw new Exception('6');
            }

            if (($GLOBALS['database']->execute_query("DELETE FROM `moderator_log` 
                WHERE `uid` = '" . $_GET['uid'] . "' AND `time` = '" . $_GET['time'] . "' LIMIT 1")) === false) {
                throw new Exception('7');
            }

            // Record end time
            switch ($record[0]['duration']) {
                case('1 Hour'): $endtime = $record[0]['time'] + 3600;
                    break;
                case('12 Hours'): $endtime = $record[0]['time'] + 43200;
                    break;
                case('1 Day'): $endtime = $record[0]['time'] + 86400;
                    break;
                case('3 Days'): $endtime = $record[0]['time'] + 259200;
                    break;
                case('1 Week'): $endtime = $record[0]['time'] + 604800;
                    break;
                case('2 Weeks'): $endtime = $record[0]['time'] + 1209600;
                    break;
                default: $endtime = 0;
                    break;
            }

            switch ($record[0]['action']) {
                case("ban"): { // Game Ban
                        if (($endtime > time()) && ($criminal[0]['ban_time'] > time())) {
                            if (($GLOBALS['database']->execute_query('UPDATE `users` 
                                SET `users`.`ban_time` = 0 
                                WHERE `users`.`id` = ' . $_GET['uid'] . ' LIMIT 1')) === false) {
                                throw new Exception('8');
                            }
                        }
                    } break;
                case("tavern-ban"): { // Tavern Ban
                        if (($endtime > time()) && ($criminal[0]['tban_time'] > time())) {
                            if (($GLOBALS['database']->execute_query('UPDATE `users` 
                                SET `users`.`ban_time` = 0, `users`.`post_ban` = "0" 
                                WHERE `users`.`id` = ' . $_GET['uid'] . ' LIMIT 1')) === false) {
                                throw new Exception('9');
                            }
                        }
                    } break;
                default: { // Either Warning or Undefined Action
                        if ($record[0]['action'] !== "warning") {
                            $GLOBALS['page']->Message("Record action could not be determined!", 'User Record System', 'id=' . $_GET['id']);
                        }
                    } break;
            }

            $GLOBALS['database']->transaction_commit();
            header('Location:?id=' . $_GET['id'] . '&act=check_user&uid=' . $_GET['uid'] . '');
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            switch ($e->getMessage()) {
                case('1'): $GLOBALS['page']->Message("You are not allowed to delete records.", 'User Record System', 'id=' . $_GET['id']);
                    break;
                case('2'):
                case('3'): $GLOBALS['page']->Message("The data is corrupted, please try again!", 'User Record System', 'id=' . $_GET['id']);
                    break;
                case('4'): $GLOBALS['page']->Message("There was an error trying to receive the record!", 'User Record System', 'id=' . $_GET['id']);
                    break;
                case('5'): $GLOBALS['page']->Message("Record could not be found within the system!", 'User Record System', 'id=' . $_GET['id']);
                    break;
                case('6'): case('7'): case('8'):
                case('9'): $GLOBALS['page']->Message("There was an error trying to change the record!", 'User Record System', 'id=' . $_GET['id']);
                    break;
                default: $GLOBALS['page']->Message("An error has occurred, please try again.", 'User Record System', 'id=' . $_GET['id']);
                    break;
            }
        }
    }

    protected function do_delete_post() {
        try {
            $GLOBALS['database']->transaction_start();
            if ($this->user[0]['user_rank'] !== 'Supermod') {
                if ($this->user[0]['user_rank'] !== 'Admin') {
                    throw new Exception('1');
                }
            }

            if (ctype_digit($_GET['postIDs']) === false) {
                throw new Exception('2');
            }

            if (($GLOBALS['database']->execute_query("SELECT * FROM `user_notes` 
                WHERE `id` = '" . $_GET['postIDs'] . "' LIMIT 1 FOR UPDATE")) === false) {
                throw new Exception('3');
            }

            if (($GLOBALS['database']->execute_query("DELETE FROM `user_notes` 
                WHERE `id` = '" . $_GET['postIDs'] . "' LIMIT 1")) === false) {
                throw new Exception('4');
            }
            $GLOBALS['database']->transaction_commit();
            $GLOBALS['page']->Message("The message has been deleted.", 'Moderator Note System', 'id=' . $_GET['id']);
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            switch ($e->getMessage()) {
                case('1'): $GLOBALS['page']->Message("You are not allowed to delete message.", 'Moderator Note System', 'id=' . $_GET['id']);
                    break;
                case('2'): $GLOBALS['page']->Message("The post-data is corrupted, please try again!", 'Moderator Note System', 'id=' . $_GET['id']);
                    break;
                case('3'): $GLOBALS['page']->Message("There was an error trying to receive the user note!", 'Moderator Note System', 'id=' . $_GET['id']);
                    break;
                case('4'): $GLOBALS['page']->Message("There was an error trying to delete the user note!", 'Moderator Note System', 'id=' . $_GET['id']);
                    break;
                default: $GLOBALS['page']->Message("An error has occurred, please try again.", 'Moderator Note System', 'id=' . $_GET['id']);
                    break;
            }
        }
    }

    protected function do_post() {
        try {
            $GLOBALS['database']->transaction_start();
            if (functions::ws_remove($_POST['message']) === '') {
                throw new Exception('1');
            }
            $message = functions::store_content($_POST['message']);
            if (!($userIDs = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username` 
                FROM `users` 
                WHERE `id` = '" . $_GET['userIDs'] . "' LIMIT 1"))) {
                throw new Exception('2');
            }

            if ($userIDs === '0 rows') {
                throw new Exception('3');
            }

            if (($GLOBALS['database']->execute_query("INSERT INTO 
                `user_notes` 
                    (`user_id`, `user`, `moderator`, `time`, `message`) 
                VALUES
                    ('" . $userIDs[0]['id'] . "', '" . $userIDs[0]['username'] . "', 
                    '" . $this->user[0]['username'] . "', UNIX_TIMESTAMP(), '" . $message . "');")) === false) {
                throw new Exception('4');
            }
            $GLOBALS['database']->transaction_commit();

            header('Location:?id=' . $_GET['id'] . '&act=check_user&uid=' . $userIDs[0]['id'] . '');
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            switch ($e->getMessage()) {
                case('1'): $GLOBALS['page']->Message("You cannot post blank messages.", 'Moderator Note System', 'id=' . $_GET['id']);
                    break;
                case('2'):
                case('3'): $GLOBALS['page']->Message("There was an error trying to receive the user note!", 'Moderator Note System', 'id=' . $_GET['id']);
                    break;
                case('4'): $GLOBALS['page']->Message("There was an error trying to insert the user note!", 'Moderator Note System', 'id=' . $_GET['id']);
                    break;
                default: $GLOBALS['page']->Message("An error has occurred, please try again.", 'Moderator Note System', 'id=' . $_GET['id']);
                    break;
            }
        }
    }

    //	User generated reports:
    protected function report_main_screen() {
        $GLOBALS['template']->assign('contentLoad', './templates/content/moderator/mod_reports_main.tpl');
    }

    protected function show_reports($type) {
        // Get data
        if ($type != 'my') {
            $data = $GLOBALS['database']->fetch_data("SELECT `uid`, `rid`, `type`, `time` 
                FROM `user_reports` 
                WHERE `status` = '" . $type . "' ORDER BY `time` ASC");
        } else {
            $data = $GLOBALS['database']->fetch_data("SELECT `uid`, `rid`, `type`, `time` 
                FROM `user_reports` 
                WHERE `status` = 'in progress' AND `processed_by` = '" . $this->user[0]['username'] . "' ORDER BY `time` ASC");
        }

        if ($data !== "0 rows") {
            for ($i = 0; $i < count($data); $i++) {
                $userdata = $GLOBALS['database']->fetch_data("SELECT `username`, `id` FROM `users` WHERE `id` = '" . $data[$i]['uid'] . "' OR `id` = " . $data[$i]['rid']);
                if( $userdata !== "0 rows" ){
                    if ($data[$i]['rid'] == $data[$i]['uid']) {
                        $rname = $userdata[0]['username'];
                        $name = $userdata[0]['username'];
                    } elseif ( $userdata[0]['id'] == $data[$i]['rid']) {
                        $rname = isset($userdata[0]['username']) ? $userdata[0]['username'] : "N/A";
                        $name = isset($userdata[1]['username']) ? $userdata[1]['username'] : "N/A";
                    } else {
                        $name = isset($userdata[0]['username']) ? $userdata[0]['username'] : "N/A";
                        $rname = isset($userdata[1]['username']) ? $userdata[1]['username'] : "N/A";
                    }
                    $data[$i]['nname'] = $name;
                    $data[$i]['rname'] = $rname;
                }
                else{
                    $data[$i]['nname'] = $data[$i]['uid'];
                    $data[$i]['rname'] = $data[$i]['rid'];
                }
            }
        }

        if ($type == 'my') {
            $GLOBALS['template']->assign('my', $data);
        } elseif ($type == 'in progress') {
            $GLOBALS['template']->assign('in_progress', $data);
        } elseif ($type == 'unviewed') {
            $GLOBALS['template']->assign('unviewed', $data);
        }
    }

    protected function report_show_details() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            if (isset($_GET['rid']) && is_numeric($_GET['rid'])) {
                if (isset($_GET['time']) && is_numeric($_GET['time'])) {
                    $report = $GLOBALS['database']->fetch_data("SELECT * FROM `user_reports` WHERE `time` = '" . $_GET['time'] . "' AND `rid` = '" . $_GET['rid'] . "' AND `uid` = '" . $_GET['uid'] . "' LIMIT 1");
                    if ($report !== "0 rows") {
                        $userdata = $GLOBALS['database']->fetch_data("SELECT `username`, `id` FROM `users` WHERE `id` = '" . $report[0]['uid'] . "' OR `id` = " . $report[0]['rid']);
                        if ($userdata !== "0 rows") {
                            // Information for smarty
                            if ($_GET['rid'] == $_GET['uid']) {
                                $rname = $userdata[0]['username'];
                                $name = $userdata[0]['username'];
                            } elseif ($userdata[0]['id'] == $_GET['rid']) {
                                $rname = $userdata[0]['username'];
                                $name = $userdata[1]['username'];
                            } else {
                                $name = $userdata[0]['username'];
                                $rname = $userdata[1]['username'];
                            }

                            if (trim($report[0]['processed_by']) != '') {
                                $processed = '<a href="?id=3&act=newpm&user=' . $report[0]['processed_by'] . '">' . $report[0]['processed_by'] . '</a>';
                            } else {
                                $processed = 'Nobody';
                            }

                            if ($report[0]['type'] != 'user') {
                                $message = functions::parse_BB($report[0]['message']);
                            } else {
                                $message = '';
                            }
                            // Pass along the infromation
                            $GLOBALS['template']->assign('report_message', functions::parse_BB($report[0]['message']));
                            $GLOBALS['template']->assign('report', $report);
                            $GLOBALS['template']->assign('userdata', $userdata);
                            $GLOBALS['template']->assign('rname', $rname);
                            $GLOBALS['template']->assign('name', $name);
                            $GLOBALS['template']->assign('processed', $processed);
                            $GLOBALS['template']->assign('message', $message);
                            $GLOBALS['template']->assign('sessionUser', $GLOBALS['userdata'][0]['username']);
                            
                            // Show template
                            $GLOBALS['template']->assign('contentLoad', './templates/content/moderator/mod_show_report_details.tpl');
                        } else { //    Incorrect time flags
                            $GLOBALS['page']->Message("There was an issue with retrieving database information.", 'Reports System', 'id=' . $_GET['id']);
                        }
                    } else { //    Incorrect time flags
                        $GLOBALS['page']->Message("There was an issue with retrieving database information.", 'Reports System', 'id=' . $_GET['id']);
                    }
                } else { //	Incorrect time flags
                    $GLOBALS['page']->Message("There was an issue with the time parameter. Please inform active coder.", 'Reports System', 'id=' . $_GET['id']);
                }
            } else { //	Incorrect rid flags
                $GLOBALS['page']->Message("There was an issue with the rid parameter. Please inform active coder.", 'Reports System', 'id=' . $_GET['id']);
            }
        } else { //	Incorrect uid flags
            $GLOBALS['page']->Message("There was an issue with the uid parameter. Please inform active coder.", 'Reports System', 'id=' . $_GET['id']);
        }
    }

    protected function update_report_status() {
        if ((isset($_GET['uid']) && is_numeric($_GET['uid'])) && ((isset($_GET['rid']) && is_numeric($_GET['rid']))) && ((isset($_GET['time']) && is_numeric($_GET['time'])))) {
            if ($_POST['status'] != 'unviewed') {
                $GLOBALS['database']->execute_query("UPDATE `user_reports` 
                    SET `processed_by` = '" . $this->user[0]['username'] . "', 
                        `status` = '" . $_POST['status'] . "' 
                    WHERE `rid` = '" . $_GET['rid'] . "' AND `uid` = '" . $_GET['uid'] . "' 
                        AND `time` = '" . $_GET['time'] . "' AND (`processed_by` = ''
                            OR `processed_by` = '" . $this->user[0]['username'] . "') LIMIT 1");
            } elseif ($_POST['status'] == 'unviewed') {
                $GLOBALS['database']->execute_query("UPDATE `user_reports` 
                    SET `processed_by` = '', `status` = '" . $_POST['status'] . "' 
                        WHERE `rid` = '" . $_GET['rid'] . "' AND `uid` = '" . $_GET['uid'] . "' 
                            AND `time` = '" . $_GET['time'] . "' AND (`processed_by` = '' 
                                OR `processed_by` = '" . $this->user[0]['username'] . "') LIMIT 1");
            }
            $GLOBALS['page']->Message("The report status has been updated.", 'Reports System', 'id=' . $_GET['id']);
        } else {
            $GLOBALS['page']->Message("There was an issue with the parameters passed for this report. Please inform active coder.", 'Reports System', 'id=' . $_GET['id']);
        }
    }

    //  Moderator tracking
    protected function main_modtrack() {
        $modtrack = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users`.`id` 
            FROM `users_statistics` 
                INNER JOIN `users` ON (`users`.`id` = `users_statistics`.`uid`)
            WHERE `users_statistics`.`user_rank` IN('Moderator', 'Supermod') ORDER BY `users`.`username` ASC");
        $GLOBALS['template']->assign('modtrack', $modtrack);
    }

    protected function modtrack_stats() {
        if (isset($_POST['moderator_track']) && (trim($_POST['moderator_track']) != '')) {
            $mod_data = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username`, `users_statistics`.`user_rank`, `users_loyalty`.`village`
                                FROM `users`, `users_statistics`, `users_loyalty` 
                                WHERE (`users_statistics`.`user_rank` = 'Moderator' OR `users_statistics`.`user_rank` = 'Supermod') 
                                        AND `users`.`username` = '" . $_POST['moderator_track'] . "' AND `users_statistics`.`uid` = `users`.`id` 
                                        AND `users_loyalty`.`uid` = `users_statistics`.`uid` LIMIT 1");
            $GLOBALS['template']->assign('mod_data', $mod_data);

            if ($mod_data !== "0 rows") {
                //  All time
                $count_data = $GLOBALS['database']->fetch_data("SELECT (SELECT COUNT(`time`) FROM `moderator_log` WHERE `moderator` = '" . $mod_data[0]['username'] . "' AND `action` = 'ban') AS `bans_ever`, (SELECT COUNT(`time`) FROM `moderator_log` WHERE `moderator` = '" . $mod_data[0]['username'] . "' AND `action` = 'warning') AS `warning_ever`, (SELECT COUNT(`time`) FROM `moderator_log` WHERE `moderator` = '" . $mod_data[0]['username'] . "' AND `action` = 'tavern-ban') AS `tbans_ever` ");
                $GLOBALS['template']->assign('count_data', $count_data);

                $report_data = $GLOBALS['database']->fetch_data("SELECT COUNT( `time` ) AS `reports_ever` FROM `user_reports`WHERE `processed_by` = '" . $mod_data[0]['username'] . "'");
                $GLOBALS['template']->assign('report_data', $report_data);

                //  Last week
                $week_count_data = $GLOBALS['database']->fetch_data("SELECT (SELECT COUNT(`time`) FROM `moderator_log` WHERE `moderator` = '" . $mod_data[0]['username'] . "' AND `action` = 'ban' AND `time` >= UNIX_TIMESTAMP() - 604800) AS `bans_ever`, (SELECT COUNT(`time`) FROM `moderator_log` WHERE `moderator` = '" . $mod_data[0]['username'] . "' AND `action` = 'warning' AND `time` >= UNIX_TIMESTAMP() - 604800) AS `warning_ever`, (SELECT COUNT(`time`) FROM `moderator_log` WHERE `moderator` = '" . $mod_data[0]['username'] . "' AND `action` = 'tavern-ban' AND `time` >= UNIX_TIMESTAMP() - 604800) AS `tbans_ever` ");
                $GLOBALS['template']->assign('week_count_data', $week_count_data);

                $week_report_data = $GLOBALS['database']->fetch_data("SELECT COUNT( `time` ) AS `reports_ever` FROM `user_reports`WHERE `processed_by` = '" . $mod_data[0]['username'] . "' AND `time` > UNIX_TIMESTAMP() - 604800");
                $GLOBALS['template']->assign('week_report_data', $week_report_data);

                //  Last month
                $month_count_data = $GLOBALS['database']->fetch_data("SELECT (SELECT COUNT(`time`) FROM `moderator_log` WHERE `moderator` = '" . $mod_data[0]['username'] . "' AND `action` = 'ban' AND `time` >= UNIX_TIMESTAMP() - 2678400) AS `bans_ever`, (SELECT COUNT(`time`) FROM `moderator_log` WHERE `moderator` = '" . $mod_data[0]['username'] . "' AND `action` = 'warning' AND `time` >= UNIX_TIMESTAMP() - 2678400) AS `warning_ever`, (SELECT COUNT(`time`) FROM `moderator_log` WHERE `moderator` = '" . $mod_data[0]['username'] . "' AND `action` = 'tavern-ban' AND `time` >= UNIX_TIMESTAMP() - 2678400) AS `tbans_ever` ");
                $GLOBALS['template']->assign('month_count_data', $month_count_data);

                $month_report_data = $GLOBALS['database']->fetch_data("SELECT COUNT( `time` ) AS `reports_ever` FROM `user_reports`WHERE `processed_by` = '" . $mod_data[0]['username'] . "' AND `time` >= UNIX_TIMESTAMP() - 2678400");
                $GLOBALS['template']->assign('month_report_data', $month_report_data);
                $GLOBALS['template']->assign('contentLoad', './templates/content/moderator/mod_modtrack_stats.tpl');
            } else {
                $GLOBALS['page']->Message("No moderator found.", 'Modtrack System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("No moderator specified.", 'Modtrack System', 'id=' . $_GET['id']);
        }
    }

    protected function modtrack_bans() {
        if (isset($_GET['mid']) && (functions::ws_remove($_GET['mid']) !== '')) {
            $ban_arry = array();
            $mod_data = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username`, `users_statistics`.`user_rank`, `users_loyalty`.`village`
                                FROM `users`, `users_statistics`, `users_loyalty` 
                                WHERE (`users_statistics`.`user_rank` = 'Moderator' OR `users_statistics`.`user_rank` = 'Supermod') 
                                        AND `users`.`username` = '" . $_GET['mid'] . "' AND `users_statistics`.`uid` = `users`.`id` 
                                        AND `users_loyalty`.`uid` = `users_statistics`.`uid` LIMIT 1");
            $GLOBALS['template']->assign('mod_data', $mod_data);

            $banned = $GLOBALS['database']->fetch_data("SELECT `moderator_log`.* FROM `moderator_log` WHERE `action` = 'ban' AND `moderator` = '" . $_GET['mid'] . "'");
            $GLOBALS['template']->assign('banned', $banned);

            if ($banned !== "0 rows") {
                for ($i = 0; $i < count($banned); $i++) {
                    $temp = $GLOBALS['database']->fetch_data("SELECT `username` FROM `users` WHERE `id` = '" . $banned[$i]['uid'] . "'");
                    if ($temp === '0 rows') {
                        unset($temp);
                        $temp[0]['username'] = 'No longer exists';
                    }
                    array_push($ban_arry, $temp);
                }
            }
            $GLOBALS['template']->assign('temp', $ban_arry);
            $GLOBALS['template']->assign('contentLoad', './templates/content/moderator/mod_modtrack_bans.tpl');
        } else {
            $GLOBALS['page']->Message("No moderator specified.", 'Modtrack System', 'id=' . $_GET['id']);
        }
    }

    protected function modtrack_warnings() {
        if (isset($_GET['mid']) && (functions::ws_remove($_GET['mid']) !== '')) {
            $warn_arry = array();
            $mod_data = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username`, `users_statistics`.`user_rank`, `users_loyalty`.`village`
                                FROM `users`, `users_statistics`, `users_loyalty` 
                                WHERE (`users_statistics`.`user_rank` = 'Moderator' OR `users_statistics`.`user_rank` = 'Supermod') 
                                        AND `users`.`username` = '" . $_GET['mid'] . "' AND `users_statistics`.`uid` = `users`.`id`
                                        AND `users_loyalty`.`uid` = `users_statistics`.`uid` LIMIT 1");
            $GLOBALS['template']->assign('mod_data', $mod_data);

            $banned = $GLOBALS['database']->fetch_data("SELECT `moderator_log`.* FROM `moderator_log` WHERE `action` = 'warning' AND `moderator` = '" . $_GET['mid'] . "'");
            $GLOBALS['template']->assign('banned', $banned);

            if ($banned !== "0 rows") {
                for ($i = 0; $i < count($banned); $i++) {
                    $temp = $GLOBALS['database']->fetch_data("SELECT `username` FROM `users` WHERE `id` = '" . $banned[$i]['uid'] . "'");
                    if ($temp === '0 rows') {
                        unset($temp);
                        $temp[0]['username'] = 'No longer exists';
                    }
                    array_push($warn_arry, $temp);
                }
            }
            $GLOBALS['template']->assign('temp', $warn_arry);
            $GLOBALS['template']->assign('contentLoad', './templates/content/moderator/mod_modtrack_warn.tpl');
        } else {
            $GLOBALS['page']->Message("No moderator specified.", 'Modtrack System', 'id=' . $_GET['id']);
        }
    }

    protected function modtrack_reports() {
        if (isset($_GET['mid']) && (functions::ws_remove($_GET['mid']) !== '')) {
            $report_arry = array();

            $mod_data = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username`, 
                `users_statistics`.`user_rank`, `users_loyalty`.`village` 
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id` 
                        AND `users_statistics`.`user_rank` IN('Moderator', 'Supermod'))
                    INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users_statistics`.`uid`)
                WHERE `users`.`username` = '".$_GET['mid']."' LIMIT 1");
            $GLOBALS['template']->assign('mod_data', $mod_data);

            $banned = $GLOBALS['database']->fetch_data("SELECT user_reports.* 
                FROM `user_reports` 
                WHERE user_reports.processed_by = '" . $_GET['mid'] . "' ORDER BY `time` DESC LIMIT 3000");
            $GLOBALS['template']->assign('banned', $banned);

            if ($banned !== "0 rows") {
                for ($i = 0; $i < count($banned); $i++) {
                    $temp = $GLOBALS['database']->fetch_data("SELECT `username` 
                        FROM `users` 
                        WHERE `users`.`id` = ".$banned[$i]['uid']." LIMIT 1");
                    if ($temp === '0 rows') {
                        unset($temp);
                        $temp[0]['username'] = 'No longer exists';
                    }
                    array_push($report_arry, $temp);
                }
            }

            $GLOBALS['template']->assign('temp', $report_arry);
            $GLOBALS['template']->assign('contentLoad', './templates/content/moderator/mod_modtrack_report.tpl');
        } else {
            $GLOBALS['page']->Message("No moderator specified.", 'Modtrack System', 'id=' . $_GET['id']);
        }
    }

    // IP check
    protected function ipcheck_done() {
        try {
            if (functions::ws_remove($_POST['ip_username']) !== '') {
                $condition = '`users`.`username` = "' . $_POST['ip_username'] . '"';
            } 
            elseif (functions::ws_remove($_POST['ip_userid']) !== '') {
                if (ctype_digit($_POST['ip_userid']) === false) {
                    throw new Exception('1');
                }
                $condition = '`users`.`id` = "' . $_POST['ip_userid'] . '"';
            }
            else {
                throw new Exception('2');
            }

            // Return all consolidated data based on search of Original User's Last/Join/Past IP address
            if(!($user_search = $GLOBALS['database']->fetch_data('SELECT `users`.`username`, `users`.`id`, `users`.`join_ip`, 
                `users`.`last_ip`, `users`.`past_IPs`, `users`.`perm_ban`
                FROM `users` WHERE '.$condition.' LIMIT 1'))) {
                throw new Exception('User Search Failed. Non-existant user or Incorrect username!');
            }

            if(!($join_ip_search = $GLOBALS['database']->fetch_data('SELECT `join_users`.`username`, `join_users`.`id`, `join_users`.`perm_ban`
                FROM `users`
                    INNER JOIN `users` AS `join_users` ON (`join_users`.`join_ip` = `users`.`join_ip` AND `join_users`.`id` != `users`.`id`)
                WHERE `users`.`id` = '.$user_search[0]['id']))) {
                throw new Exception('Join IP Check Failed!');
            }

            if(!($last_ip_search = $GLOBALS['database']->fetch_data('SELECT `last_users`.`username`, `last_users`.`id`, `last_users`.`perm_ban`
                FROM `users`
                    INNER JOIN `users` AS `last_users` ON (`last_users`.`last_ip` = `users`.`last_ip` AND `last_users`.`id` != `users`.`id`)
                WHERE `users`.`id` = '.$user_search[0]['id']))) {
                throw new Exception('Last IP Check Failed!');
            }

            if(!($join_past_ip_search = $GLOBALS['database']->fetch_data('SELECT `old_join_users`.`username`, `old_join_users`.`id`,
                `old_join_users`.`perm_ban`
                FROM `users`
                    INNER JOIN `users` AS `old_join_users` ON (`old_join_users`.`past_IPs` LIKE CONCAT("%", `users`.`join_ip`, "%") 
                        AND `old_join_users`.`id` != `users`.`id`)
                WHERE `users`.`id` = '.$user_search[0]['id']))) {
                throw new Exception('Past Join IP Check Failed!');
            }

            if(!($last_past_ip_search = $GLOBALS['database']->fetch_data('SELECT `old_past_users`.`username`, `old_past_users`.`id`,
                `old_past_users`.`perm_ban`
                FROM `users`
                    INNER JOIN `users` AS `old_past_users` ON (`old_past_users`.`past_IPs` LIKE CONCAT("%", `users`.`last_ip`, "%") 
                        AND `old_past_users`.`id` != `users`.`id`)
                WHERE `users`.`id` = '.$user_search[0]['id']))) {
                throw new Exception('Past Last IP Check Failed!');
            }

            $GLOBALS['template']->assign('user', $user_search);
            $GLOBALS['template']->assign('join_IPs', $join_ip_search);
            $GLOBALS['template']->assign('last_IPs', $last_ip_search);
            $GLOBALS['template']->assign('last_past_IPs', $last_past_ip_search);
            $GLOBALS['template']->assign('last_join_IPs', $join_past_ip_search);
            $GLOBALS['template']->assign('contentLoad', './templates/content/moderator/mod_ipcheck_done.tpl');
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    // Nindo Check
    protected function rnindo_done() {
        try {
            $GLOBALS['database']->transaction_start();

            if (!($criminal = $GLOBALS['database']->fetch_data('SELECT `users`.`id`, `users`.`username`, `users`.`nindo` 
                FROM `users` 
                WHERE `users`.`username` = "' . $_POST['nindo_username'] . '" LIMIT 1 FOR UPDATE'))) {
                throw new Exception('1');
            }

            if ($criminal === '0 rows') {
                throw new Exception('2');
            }

            if (($GLOBALS['database']->execute_query('UPDATE `users` 
                SET `users`.`nindo` = "Removed by Moderator!" 
                WHERE `users`.`id` = ' . $criminal[0]['id'] . ' LIMIT 1')) === false) {
                throw new Exception('3');
            }

            $GLOBALS['database']->transaction_commit();
            $GLOBALS['page']->Message($criminal[0]['username'] . "'s Nindo Removed: " . $criminal[0]['nindo'], 'Nindo Removal', 'id=' . $_GET['id']);
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage()); // Submit Transaction Error Message and Rollback
            switch ($e->getMessage()) { // Use Preconfigured Error Response Messages if Possible
                case('1'): $GLOBALS['page']->Message("There was an error trying to receive the user's nindo!", 'Nindo Removal', 'id=' . $_GET['id']);
                    break;
                case('2'): $GLOBALS['page']->Message("The user, " . $_POST['nindo_username'] . ", doesn't exist on the site.", 'Nindo Removal', 'id=' . $_GET['id']);
                    break;
                case('3'): $GLOBALS['page']->Message("There was an error trying to delete " . $criminal[0]['username'] . "'s nindo!", 'Nindo Removal', 'id=' . $_GET['id']);
                    break;
                default: $GLOBALS['page']->Message("An error occured while deleting the nindo, please try again.", 'Nindo Removal', 'id=' . $_GET['id']);
                    break;
            }
        }
    }

    protected function ravatar_done() {
        try {
            $pic_types = array('.gif', '.jpg', '.jpeg', '.png'); // Accepted File Type Extensions

            if (!($criminal = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username` 
                                FROM `users` 
                                WHERE `users`.`username` = '" . $_POST['avatar_username'] . "' LIMIT 1"))) {
                throw new Exception('1');
            }

            if ($criminal === '0 rows') {
                throw new Exception('2');
            }

            // Remove the Avatar
            for ($i = 0; $i < count($pic_types); $i++) {
                if (file_exists('./images/avatars/' . $criminal[0]['id'] . $pic_types[$i])) {
                    if (unlink('./images/avatars/' . $criminal[0]['id'] . $pic_types[$i]) === false) {
                        throw new Exception('3');
                    }

                    $GLOBALS['page']->Message("The avatar was removed and replaced with the default avatar.", 'Avatar Removal', 'id=' . $_GET['id']);
                    break;
                } elseif ($i === (count($pic_types) - 1)) {
                    throw new Exception('4');
                }
            }
        } catch (Exception $e) {
            switch ($e->getMessage()) { // Use Preconfigured Error Response Messages if Possible
                case('1'): $GLOBALS['page']->Message("There was an error trying to receive the user's data!", 'Avatar Removal', 'id=' . $_GET['id']);
                    break;
                case('2'): $GLOBALS['page']->Message("The user, " . $_POST['nindo_username'] . ", doesn't exist on the site.", 'Avatar Removal', 'id=' . $_GET['id']);
                    break;
                case('3'): $GLOBALS['page']->Message("An error occurred in the avatar removal process. Please try again.", 'Avatar Removal', 'id=' . $_GET['id']);
                    break;
                case('4'): $GLOBALS['page']->Message("An error occurred or the avatar doesn't exist. Please try again.", 'Avatar Removal', 'id=' . $_GET['id']);
                default: $GLOBALS['page']->Message("An error occured while deleting the avatar, please try again.", 'Avatar Removal', 'id=' . $_GET['id']);
                    break;
            }
        }
    }

    protected function do_mod_month_report() {
        try {
            $GLOBALS['database']->transaction_start();
            $month = $_POST['report_month_time'];
            $year = $_POST['report_year_time'];
            $village = $_POST['report_village'];
            $rating = $_POST['report_vil_rate'];
            $message = $_POST['month_report_message'];

            // Check if village selection is within available villages
            if (in_array($village, $this->villages, true) === false) {
                throw new Exception('1');
            }

            if (ctype_digit($year) === false) {
                throw new Exception('2');
            }

            if ((in_array($rating, array('1', '2', '3', '4', '5'), true) === false) || (ctype_digit($rating) === false)) {
                throw new Exception('3');
            }

            if (in_array($month, $this->months, true) === false) {
                throw new Exception('4');
            }

            if (functions::ws_remove($message) === '') {
                throw new Exception('5');
            }

            // Check to see if a report made on the village exists
            if (!($month_report = $GLOBALS['database']->fetch_data('SELECT 1 AS `result`
                                FROM `moderator_report` 
                                WHERE `moderator_report`.`year` = "' . $year . '" AND `moderator_report`.`month` = "' . $month . '" 
                                        AND `moderator_report`.`mod_id` = ' . $this->user[0]['id']))) {
                throw new Exception('6');
            }

            if ($month_report !== '0 rows') {
                throw new Exception('7');
            }

            if (($GLOBALS['database']->execute_query('INSERT INTO 
                                `moderator_report`
                                        (`mod_id`, `time`, `village`, `month`, `year`, `village_rating`, `report`)
                                VALUES
                                        (' . $this->user[0]['id'] . ', UNIX_TIMESTAMP(), "' . $village . '", 
                                                "' . $month . '", "' . $year . '", "' . $rating . '", "' . $message . '")')) === false) {
                throw new Exception('8');
            }

            $GLOBALS['page']->Message('Your village report regarding the village, ' . $village . ', has been submitted!<br>
                                Please keep the Submission Acceptance Time in case the system is acting slow or unresponsive!<br><br>
                                Submission Acceptance Time: ' . date("F j, Y G:i:s") . '<br>', 'Monthly Village Report', 'id=' . $_GET['id']);
            $GLOBALS['database']->transaction_commit();
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage()); // Submit Transaction Error Message and Rollback
            switch ($e->getMessage()) { // Use Preconfigured Error Response Messages if Possible
                case('1'): $GLOBALS['page']->Message("The village you are trying to report isn't recognized by the system!", 'Monthly Village Report', 'id=' . $_GET['id']);
                    break;
                case('2'): $GLOBALS['page']->Message("The year data is corrupted or not acceptable!", 'Monthly Village Report', 'id=' . $_GET['id']);
                    break;
                case('3'): $GLOBALS['page']->Message("The village rating data is corrupted or not acceptable!", 'Monthly Village Report', 'id=' . $_GET['id']);
                    break;
                case('4'): $GLOBALS['page']->Message("The month data is corrupted or not acceptable!", 'Monthly Village Report', 'id=' . $_GET['id']);
                    break;
                case('5'): $GLOBALS['page']->Message("You cannot submit a blank report for the month!", 'Monthly Village Report', 'id=' . $_GET['id']);
                    break;
                case('6'): $GLOBALS['page']->Message("There was an error trying to receive the village report data!", 'Monthly Village Report', 'id=' . $_GET['id']);
                    break;
                case('7'): $GLOBALS['page']->Message("You have already made a report during this month!", 'Monthly Village Report', 'id=' . $_GET['id']);
                    break;
                case('8'): $GLOBALS['page']->Message("There was an error trying to send your monthly village report!", 'Monthly Village Report', 'id=' . $_GET['id']);
                    break;
                default: $GLOBALS['page']->Message("An error occured while submitting your monthly report, please try again.", 'Monthly Village Report', 'id=' . $_GET['id']);
                    break;
            }
        }
    }
}
?>