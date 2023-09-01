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

class anbuLib {
    
    // Get list of loyalties
    public function showAnbuList( $village , $canEdit = false, $descriptionOverwrite = false ){
        
        // Set a link
        $this->link = functions::get_current_link( array("id","act") );
        
        $min =  tableParser::get_page_min();
        
        // Get users
        $squads = $GLOBALS['database']->fetch_data("
                SELECT * 
                FROM  `squads`
                WHERE  
                    `village`  = '" . $village . "' 
                ORDER BY (`pt_rage` + `pt_def`) DESC
                LIMIT " . $min . ",10");
        
        // Create options arrays
        $optionArray = array( array_merge( $_GET, array("name" => "Details", "act2" => "details", "aid" => "table.id") ) );
        $topOptions = false;
        if( $canEdit == true ){
            
            // Edit links
            $optionArray[] = array_merge( $_GET, array("name" => "Edit Orders", "act2" => "editOrder", "aid" => "table.id") );
            $optionArray[] = array_merge( $_GET, array("name" => "Change Leader", "act2" => "editSquad", "aid" => "table.id") );
            $optionArray[] = array_merge( $_GET, array("name" => "Abolish Squad", "act2" => "removeSquad", "aid" => "table.id") );
            
            // Create new link
            $topOptions = array( array("name" => "Create New Squad", "href" => $this->link . "&act2=createAnbu") );
        }
        
        // Description of the squads
        $description = array('message'=>"ANBU squads are created and controlled by the kage.",'hidden'=>'yes');
        if( $descriptionOverwrite !== false ){
            $description = $descriptionOverwrite;
        }
        
        // Show the table of users
        tableParser::show_list(
            'loyalty',
            'ANBU Squads', 
            $squads,
            array(
                'name' => "Name",
                'pt_rage' => "Assault pts.",
                'pt_def' => "Defense pts."
            ), 
            $optionArray,
            true, // Send directly to contentLoad
            false, // No newer/older links
            $topOptions, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            $description
        );
        
        // Return Link
        $GLOBALS['template']->assign("returnLink", true);
    }
    
    // Set user to be part of a ANBU squad
    public function setAnbuSquad( $uid, $squad ){
        if( !isset($squad) || $squad == "" ){
            $squad = "_none";
        }
        if( $GLOBALS['database']->execute_query("
                UPDATE `users`,`users_preferences` 
                SET 
                    `anbu` = '".$squad."'
                WHERE 
                    `uid` = '" . $uid . "' AND 
                    `uid` = `id`") 
        ){
            $users_notifications = new NotificationSystem('', $uid);

            $users_notifications->addNotification(array(
                                                        'id' => 11,
                                                        'duration' => 'none',
                                                        'text' => 'Your ANBU squad status has changed.',
                                                        'dismiss' => 'yes'
                                                    ));

            $users_notifications->recordNotifications();

            $GLOBALS['Events']->acceptEvent('anbu', array('new'=>$squad, 'old'=>$GLOBALS['userdata'][0]['anbu'] ));

            return true;
        }
        return false;
    }
    
    // Create new ANBU squad and return ID of said squad
    public function createAnbuSquad( $village, $name, $leaderID, $orders ){
        $GLOBALS['database']->execute_query("
            INSERT INTO `squads` ( `village` , `name` , `orders`, `leader_uid` )
            VALUES ('" . $village . "', '" . $name . "', '" . $orders . "', '" . $leaderID . "');");
        return $GLOBALS['database']->get_inserted_id();               
    }
    
    
    // Get informatiom about the anbu squad
    public function getAnbuSquad( $anbuID ){
        $squad = $GLOBALS['database']->fetch_data("SELECT * FROM  `squads` WHERE `id` = '".$anbuID."' LIMIT 1");
        return $squad;
    }
    
    // Get Usernames for ANBU squad members based on their IDs
    public function getAnbuUsernames( $squad ){
        
        // Variable to indicate whether squad has any users at all
        $hasUsers = false;
        
        // Check that the squad is valid
        if( $squad !== "0 rows" ){
        
            // Loop through squad and get the usernames
            $ids = array();
            foreach( $squad[0] as $key => $value ){
                if( preg_match("/(_uid$)/", $key) ){
                    if( isset( $value ) && ctype_digit($value) && $value > 0 ){
                        $ids[] = $value;
                        $hasUsers = true;
                    }
                }
            }

            // Create select query
            if( $hasUsers == true ){
                $query = "";
                foreach( $ids as $id ){
                    $query .= ($query == "") ? "`id` = ".$id :  " OR `id` = ".$id;
                }
                $query = "
                        SELECT `id`, `last_activity`, `username` 
                        FROM `users`,`users_timer`,`users_preferences` 
                        WHERE 
                            `anbu` = '" . $squad[0]['id'] . "' AND
                            `id` = `userid` AND 
                            `id` = `uid` AND 
                            (".$query.")
                        LIMIT ".count($ids);
                $members = $GLOBALS['database']->fetch_data( $query );
                if( $members == "0 rows" ){
                    $hasUsers = false;
                }
            }

            // Assign usernames and last activities to squad array
            if( $hasUsers ){

                // An array for users no longer in database, which should be removed from ANBU squad
                $inactiveUsers = array();

                // Go through the squad positions and assign where appropriate
                foreach( $squad[0] as $key => $value ){

                    // Only check the user IDs
                    if( preg_match("/(_uid$)/", $key) && isset( $value ) && ctype_digit($value) && $value > 0 ){

                        // Go through the members loaded in the DB
                        $foundMember = false;
                        foreach( $members as $member ){
                            if( $member['id'] == $value ){
                                $foundMember = true;
                                $squad[0][ $key . "_username" ] = $member['username'];
                                $squad[0][ $key . "_last_activity" ] = $member['last_activity'];
                            }
                        }

                        // Member was not found for this position, so reset it
                        if( $foundMember == false ){
                            $inactiveUsers[] = $key;
                        }
                    }
                }

                // If we found inactive/deleted users, then reset the ANBU db positions
                if( !empty($inactiveUsers) ){

                    // Create and load the query
                    $query = "";
                    foreach( $inactiveUsers as $key ){
                        $query .= ( $query == "" ) ? `$key` . " = '0' " : "," . `$key` . " = '0' ";
                    }
                    if( !$GLOBALS['database']->execute_query("
                        UPDATE `squads` 
                        SET " . $query . "
                        WHERE `id` = '" . $squad[0]['id'] . "' 
                        LIMIT 1") )
                    {
                        throw new Exception("There was an error removing users from the ANBU squad.");
                    }

                    // Update the squad information, so it'll load correctly
                    foreach( $inactiveUsers as $key ){
                        $squad[0][$key] = 0;
                    }
                }   
            }
            else{
                // Anbu doesn't have members. Delete it.
                $GLOBALS['database']->execute_query("DELETE FROM `squads` WHERE `id` = '" . $squad[0]['id'] . "' LIMIT 1");
                throw new Exception("This ANBU squad has no users left, and therefore it has been disbanded.");
            }
        }
        else{
            throw new Exception("This ANBU squad does not exist");
        }
        
        // Return the squad
        return $squad;
    }
    
    // Check if this user is an anbu
    public function isUserAnbu( $anbuStatus ){
        if( 
            $anbuStatus !== '_none' && 
            $anbuStatus !== '' &&
            $anbuStatus !== '_disabled' &&
            ctype_digit( $anbuStatus )
        ){
            return $anbuStatus;
        }               
        return false;
    }
    
    // Show anbu details
    public function showMembers( $anbuID , $sendToContentLoad = true ){
         
        if( isset($anbuID) && is_numeric($anbuID) && $anbuID > 0 ){
        
            // Stuff neccesary if this function is loaded by itself
            if( $sendToContentLoad ){

                // Get the squad
                $this->squad = $this->getAnbuSquad( $anbuID );
                $this->squad = $this->getAnbuUsernames( $this->squad );

                // Set a return link, since this is the only thing displayed
                $this->link = functions::get_current_link( array("id","act") );
                $GLOBALS['template']->assign("returnLink", $this->link);
            }

            // Check if squad exists
            if( $this->squad !== "0 rows" ){

                // Prettify the squad information
                $squadDisplay = array();                
                if( isset($this->squad[0]['leader_uid_username']) && !empty($this->squad[0]['leader_uid_username']) ){
                    $squadDisplay[] = array( "position" => "Leader", "name" => $this->squad[0]['leader_uid_username'] , "profile" => "<a href='?id=13&page=profile&name=".$this->squad[0]['leader_uid_username']."'>Profile</a>");
                }
                
                foreach( $this->squad[0] as $key => $value ){
                    if( preg_match("/(^member_[0-9]_uid$)/", $key) ){

                        // Set a proper position name based on column name
                        $tags = explode("_", $key);
                        $position = $tags[0] . " " . $tags[1];

                        // Show first empty and then filled positions
                        if( !isset($value) || empty($value) ){
                            $squadDisplay[] = array( "position" => $position, "name" => "N/A", "profile" => "Empty Spot" );
                        }
                        else{
                            $squadDisplay[] = array( "position" => $position, "name" => $this->squad[0][ $key . "_username" ], "profile" => "<a href='?id=13&page=profile&uid=".$value."'>Profile</a>" );
                        }
                    }
                }

                // Show the table of users
                tableParser::show_list(
                    'anbuSquad',
                    'Members', 
                    $squadDisplay,
                    array(
                        'position' => "Position",
                        'name' => "Name",
                        'profile' => "Profile"
                    ), 
                    false,
                    $sendToContentLoad, // Send directly to contentLoad
                    false, // No newer/older links
                    false, // No top options links
                    false, // No sorting on columns
                    false, // No pretty options
                    false, // No top search field
                    '<b>Squad Orders:</b> '.functions::color_BB(functions::parse_BB($this->squad[0]['orders']))
                );

            }
            else{
                throw new Exception("Could not find the ANBU squad in the database");
            }
        }
        else{
            throw new Exception("Invalid squad ID specified");
        }
    }
    
    // Check if user is leader
    protected function isLeader(){
        if( $this->squad[0]['leader_uid'] == $GLOBALS['userdata'][0]['id'] ){
            return true;
        }
        return false;
    }
    
    // Function for setting the squad data
    public function setSquadData( $squad ){
        $this->squad = $squad;
    }
    
    // Check if the squad has members
    protected function hasMembers(){
        foreach( $this->squad[0] as $key => $value ){
            if( preg_match("/(^member_[1-9]_uid$)/", $key) ){
                if( isset( $value ) && ctype_digit($value) && $value > 0 ){
                    return true;
                }
            }
        }
        return false;
    }
    
    // Get the ANBU ID of the user
    protected function getANBUmemberID( $uid ){
        foreach( $this->squad[0] as $key => $value ){
            if( preg_match("/(^member_[1-9]_uid$)/", $key) ){
                if( isset( $value ) && ctype_digit($value) && $value == $uid ){
                    return $key;
                }
            }
        }
        return false;
    }
    
    // Get the ID and key of a random user
    protected function getRandomMemberData(){
        foreach( $this->squad[0] as $key => $value ){
            if( preg_match("/(^member_[1-9]_uid$)/", $key) ){
                if( isset( $value ) && ctype_digit($value) && $value > 0 ){
                    return array($key,$value);
                }
            }
        }
        return false;
    }
    
    // Show the main page
    protected function squadMain() {
        
        // Get usernames for the squad
        $this->squad = $this->getAnbuUsernames( $this->squad );
        
        // Get the leader
        $GLOBALS['template']->assign('squad', $this->squad[0] );
        $GLOBALS['template']->assign('orders', functions::parse_BB($this->squad[0]['orders']));
        
        // Set the memberlist. Called with false, so it's not send directly to contentLoad
        $this->showMembers( $this->squad[0]['id'], false );
        
        // Update clan rank if this is the leader
        if ( $this->isLeader() ) {
            $this->updateRank();
        }
        
        // Load the main template for ANBU
        $GLOBALS['template']->assign('contentLoad', './templates/content/anbu/anbu_main.tpl');
    }
    
    //  Update Clan Rank   
    protected function updateRank() {
        if ($this->squad[0]['rank'] == 'Trainees') {
            if (($this->squad[0]['pt_rage'] + $this->squad[0]['pt_def']) > 50) {
                $GLOBALS['database']->execute_query("UPDATE `squads` SET `rank` = 'Rookies' WHERE `id` = '" . $this->squad[0]['id'] . "' LIMIT 1");
            }
        } elseif ($this->squad[0]['rank'] == 'Rookies') {
            if ($this->squad[0]['pt_rage'] + $this->squad[0]['pt_def'] > 250) {
                $GLOBALS['database']->execute_query("UPDATE `squads` SET `rank` = 'Veterans' WHERE `id` = '" . $this->squad[0]['id'] . "' LIMIT 1");
            }
        } elseif ($this->squad[0]['rank'] == 'Veterans') {
            if ($this->squad[0]['pt_rage'] + $this->squad[0]['pt_def'] > 500) {
                $GLOBALS['database']->execute_query("UPDATE `squads` SET `rank` = 'Elite' WHERE `id` = '" . $this->squad[0]['id'] . "' LIMIT 1");
            }
        } elseif ($this->squad[0]['rank'] == 'Elite') {
            if ($this->squad[0]['pt_rage'] > ($this->squad[0]['pt_rage'] + $this->squad[0]['pt_def'] / 100) * 66 && ($this->squad[0]['pt_rage'] + $this->squad[0]['pt_def'] > 2000)) 
            {
                $GLOBALS['database']->execute_query("UPDATE `squads` SET `rank` = 'Warbirds' WHERE `id` = '" . $this->squad[0]['id'] . "' LIMIT 1");
            } 
            elseif ($this->squad[0]['pt_def'] > ($this->squad[0]['pt_rage'] + $this->squad[0]['pt_def'] / 100) * 66 && ($this->squad[0]['pt_rage'] + $this->squad[0]['pt_def'] > 500)) 
            {
                $GLOBALS['database']->execute_query("UPDATE `squads` SET `rank` = 'Defenders' WHERE `id` = '" . $this->squad[0]['id'] . "' LIMIT 1");
            }
        }
    }
    
    // Invite the user form
    protected function inviteUserForm() {
        $GLOBALS['page']->UserInput( 
                "Invite a user into the ANBU squad", 
                "Invite Member", 
                array(
                    array("infoText"=>"Enter username","inputFieldName"=>"username", "type" => "input", "inputFieldValue" => "")
                ), 
                array(
                    "href"=>"?id=".$_GET['id']."&act=".$_GET['act'] ,
                    "submitFieldName"=>"Submit", 
                    "submitFieldText"=>"Invite"),
                "Return" 
         );
    }
    
    //	Add user to the ANBU.
    //  If position is not specified, it will just pick a free spot
    public function inviteUser( $userName , $position = false ) {
        if (isset($userName) && $userName != '') {
            $user = $GLOBALS['database']->fetch_data("
                SELECT `id`,`anbu`,`rank_id`,`users_loyalty`.`village`,`username`,`leader` 
                FROM 
                    `users`,`users_preferences`,`users_statistics`,`users_loyalty`
                    LEFT JOIN `villages` ON (`villages`.`name` = `users_loyalty`.`village`)
                WHERE 
                    `username` = '" . $userName . "'
                    AND `users_statistics`.`uid` = `id` 
                    AND `users_loyalty`.`uid` = `id` 
                    AND `users_preferences`.`uid` = `id` 
                LIMIT 1"
            );
            if ($user != '0 rows') {
                if ($user[0]['village'] == $this->squad[0]['village']) {
                    if ($user[0]['rank_id'] >= 3) {
                        if( !$this->isUserAnbu( $user[0]['anbu'] ) && $user[0]['anbu'] !== "_disabled" ){
                            if( $user[0]['username'] !== $user[0]['leader']  ) {
                                
                                // Get free spot
                                $freeKey = "";
                                foreach( $this->squad[0] as $key => $value ){
                                    if( preg_match("/(_uid$)/", $key) && $freeKey == "" ){
                                        if( isset( $value ) && ctype_digit($value) && $value == 0 ){
                                            $freeKey = $key;
                                        }
                                    }
                                }
                                
                                // Overwrite position in squad
                                if( $position !== false ){
                                    
                                    // Check if someone already has position
                                    if( $this->squad[0][ $position ] > 0 ){
                                        $this->setAnbuSquad($this->squad[0][ $position ], "_none");
                                    }
                                    
                                    // Set the key
                                    $freeKey = $position;
                                    
                                }
                                
                                if ( $freeKey !== "" ) {
                                    $query = "  UPDATE `squads` 
                                                SET `" . $freeKey . "` = '" . $user[0]['id'] . "' 
                                                WHERE `id` = '" . $this->squad[0]['id'] . "' 
                                                LIMIT 1";
                                    $GLOBALS['database']->execute_query($query);
                                    $this->setAnbuSquad( $user[0]['id'], $this->squad[0]['id']  );
                                    $GLOBALS['page']->Message('The user has been added to your ANBU squad.', 'ANBU HQ', 'id=' . $_GET['id'] . '');
                                    
                                } else {
                                    throw new Exception( "You have no open slots in your squad." );
                                }
                            } else {
                                throw new Exception( "The faction leader cannot join any ANBU squads." );
                            }
                        } else {
                            throw new Exception( "The specified user is already in an ANBU squad, or has disabled ANBU invitations." );
                        }
                    } else {
                        throw new Exception( "The user you specified does not meet the rank requirements." );
                    }
                } else {
                    throw new Exception( "The user you specified is not a member of your village." );
                }
            } else {
                throw new Exception( "The user you specified does not exist." );
            }
        } else {
            throw new Exception( "No username was specified." );
        }
    }
    
    // Anbu Orders Form
    protected function ANBUOrders() {
        $GLOBALS['page']->UserInput( 
                "Write the orders for your squad in the field below.", 
                "Edit Squad Orders", 
                array(
                    array("infoText"=>"",
                          "inputFieldName"=>"orders",
                          "type"=>"textarea",
                          "inputFieldValue"=> $this->squad[0]['orders'],
                          "maxlength" => 1500
                    )
                ), 
                array(
                    "href"=>"?id=".$_GET['id']."&act=".$_GET['act'] ,
                    "submitFieldName"=>"Submit", 
                    "submitFieldText"=>"Submit Orders"),
                "Return" 
         );
    }

    // Do edit orders
    protected function edit_orders() {
        if (isset($_POST['orders'])) {
            if (strlen($_POST['orders']) < 1500) {
                $order_text = functions::store_content($_POST['orders']);
                if ($order_text != '') {
                    $orders = $GLOBALS['database']->fetch_data("SELECT `orders` FROM `squads` WHERE `leader_uid` = '" . $_SESSION['uid'] . "'");
                    if ($orders != '0 rows') {
                        //	Update:
                        $GLOBALS['database']->execute_query("UPDATE `squads` SET `orders` = '" . $order_text . "' WHERE `leader_uid` = '" . $_SESSION['uid'] . "' LIMIT 1");
                        $GLOBALS['page']->Message("Your orders have been updated", 'Edit Squad Orders', 'id=' . $_GET['id']);
                    } else {
                        throw new Exception( "Orders could not be found in database for update, please report to coder." );
                    }
                } else {
                    //	Update Orders:
                    $GLOBALS['database']->execute_query("UPDATE `squads` SET `orders` = '' WHERE `leader_uid` = '" . $_SESSION['uid'] . "' LIMIT 1");
                    $GLOBALS['page']->Message("The orders have been cleared", 'Edit Squad Orders', 'id=' . $_GET['id']);
                }
            } else {
                throw new Exception( "Your orders are too long." );
            }
        } else {
            throw new Exception( "No orders were specified." );
        }
    }

    // Kick User Form
    protected function kickUserForm() {
        
        // Get usernames for the squad
        $this->squad = $this->getAnbuUsernames( $this->squad );
        
        // Show the form
        $GLOBALS['template']->assign('squad', $this->squad[0]);
        $GLOBALS['template']->assign('contentLoad', './templates/content/anbu/anbu_kickuserform.tpl');
    }

    // Do kick this user
    protected function kickUser() {
        if (isset($_POST['memberID']) && is_numeric($_POST['memberID'])) {
            if ($this->squad[0][ 'member_' . $_POST['memberID']."_uid" ] != 0) {
                if ($GLOBALS['database']->execute_query("
                        UPDATE `squads` 
                        SET `member_" . $_POST['memberID'] . "_uid` = '' 
                        WHERE `id` = '" . $this->squad[0]['id'] . "'
                        LIMIT 1")
                ) {
                    $this->setAnbuSquad($this->squad[0][ 'member_' . $_POST['memberID']."_uid" ], "_none");
                    $GLOBALS['page']->Message('The member has been kicked from your squad', 'ANBU HQ', 'id=' . $_GET['id'] . '');
                } else {
                    throw new Exception("There was an error while updating the squad data, please try again.");
                }
            } else {
                throw new Exception("This member spot was already empty.");
            }
        } else {
            throw new Exception("No member ID was set.");
        }
    }

    // Resign from ANBU squad form
    protected function resignForm() {
        
        // Get usernames for the squad
        $this->squad = $this->getAnbuUsernames( $this->squad );
        
        // Show the form
        $GLOBALS['template']->assign('isLeader', $this->isLeader() );
        $GLOBALS['template']->assign('hasMembers', $this->hasMembers() );
        $GLOBALS['template']->assign('squad', $this->squad[0]);
        $GLOBALS['template']->assign('contentLoad', './templates/content/anbu/anbu_resignform.tpl');
    }
    
    // Do resign user from ANBU
    public function  ANBUresign( $uid , $anbuID = false) {
        
        // If anbu ID is set, then retrieve that anbu
        if( $anbuID !== false ){
            $this->squad = $this->getAnbuSquad($anbuID);
        }
        
        // Get usernames for the squad (only members for which usernames are attached are actually part of the ANBU still)
        $this->squad = $this->getAnbuUsernames( $this->squad );
        
        // Update the user already here
        if( $this->setAnbuSquad( $uid , "_none" ) ){
            
            // Find a new leader using the post suggestion
            if( $this->isLeader() ){
                $this->findNewLeader();
            }
            elseif( 
                $key = $this->getANBUmemberID( $uid ) 
            ){
                $GLOBALS['database']->execute_query("
                    UPDATE `squads` 
                    SET `" . $key . "` = '0' 
                    WHERE `id` = '" . $this->squad[0]['id'] . "' 
                    LIMIT 1"
                );
            }
            else{
                throw new Exception("There was an error updating the status to the ANBU squad.");
            }
            
            // Message to user
            $GLOBALS['page']->Message('You have resigned from your ANBU squad.', 'ANBU HQ', 'id=' . $_GET['id'] . '');
        }
        else{
            throw new Exception("There was an error changing your ANBU status");
        }
    }
    
    // Set a new leader for the squad based on suggestion
    protected function findNewLeader( ){
        
        // Check is suggestions were submitted
        if( isset($_POST['newLeader']) && $_POST['newLeader'] !== ""){
            $memberIDsuggestion = $_POST['newLeader'];
        }
        
        // Convenience variables
        $dbEntry = $uid = ""; // The user to be removed from ANBU squad
        
        // Check if the squad has any members to take over leader position
        if( $this->hasMembers() ){
            
            // Members remaining	
            if ( 
                isset($memberIDsuggestion) && 
                $memberIDsuggestion > 0 && 
                $memberIDsuggestion <= 9
            ) {
                
                // Check that this ID is a member
                if( 
                    $this->squad[0][ "member_".$memberIDsuggestion."_uid" ] > 0 &&
                    isset( $this->squad[0][ "member_".$memberIDsuggestion."_uid_username" ] )
                ){
                    list( $dbEntry, $uid ) = array( "member_".$memberIDsuggestion."_uid" , $this->squad[0][ "member_".$memberIDsuggestion."_uid" ] );
                }
                else{
                    list( $dbEntry, $uid ) = $this->getRandomMemberData();
                }
            } else {
                list( $dbEntry, $uid ) = $this->getRandomMemberData();
            }
            
            if( $dbEntry !== "" && $uid !== "" ){
                $GLOBALS['database']->execute_query("UPDATE `squads` SET `leader_uid` = `" . $dbEntry . "`, `$dbEntry` = 0 WHERE `".$dbEntry."` > 0 AND `id` = '" . $this->squad[0]['id'] . "' LIMIT 1");
            }
            else{
                throw new Exception("There was an error figuring out who should be the new leader of the squad.");
            }
        }
        else{
            $GLOBALS['database']->execute_query("DELETE FROM `squads` WHERE `id` = '" . $this->squad[0]['id'] . "' LIMIT 1");
        }
    }
    
    
    // Get the item level for this squad rank
    protected function getItemLevel(){
        $itemLevel = 0;
        if ($this->squad[0]['rank'] == 'Rookies') {
            $itemLevel = 0;
        } elseif ($this->squad[0]['rank'] == 'Veterans') {
            $itemLevel = 1;
        } elseif ($this->squad[0]['rank'] == 'Elite') {
            $itemLevel = 2;
        } elseif ($this->squad[0]['rank'] == 'Defenders') {
            $itemLevel = 3;
        } elseif ($this->squad[0]['rank'] == 'Warbirds') {
            $itemLevel = 4;
        }
        return $itemLevel;
    }
}