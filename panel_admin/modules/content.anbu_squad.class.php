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

class anbu_admin {

    // Constructor
    public function __construct() {

        // Extra information to be displayed to admin
        $this->displayInfo = "";
        
        // Figure out what to do
        if (!isset($_GET['act'])) {
            $this->main_screen();
        } elseif ($_GET['act'] == 'new_anbu') {
            if (!isset($_POST['Submit'])) {
                $this->anbu_new_form();
            } else {
                $this->anbu_upload_new();
            }
        } elseif ($_GET['act'] == 'edit_anbu') {
            if (!isset($_POST['Submit'])) {
                $this->edit_form();
            } else {
                $this->do_edit();
            }
        } elseif ($_GET['act'] == 'delete_anbu') {
            if (!isset($_POST['Submit'])) {
                $this->confirm_delete();
            } else {
                $this->do_delete();
            }
        } elseif ($_GET['act'] == 'browse') {
            $this->browse_list();
        }
    }

    private function main_screen() {

        $anbus = $GLOBALS['database']->fetch_data("
             SELECT `squads`.* , `users`.`username`
             FROM `squads` 
             LEFT JOIN `users` ON (`users`.`id` = `squads`.`leader_uid`)
             ORDER BY `village`");
        tableParser::show_list(
                'anbu', 'Anbu admin', $anbus, array(
            'id' => "ID",
            'name' => "Name",
            'village' => "Village",
            'pt_rage' => "Agression pts.",
            'pt_def' => "Defensive pts.",
            'username' => "Leader",
                    'rank' => "Rank"
                ), array(
            array("name" => "Modify", "act" => "edit_anbu", "bid" => "table.id"),
            array("name" => "Delete", "act" => "delete_anbu", "bid" => "table.id")
                ), true, // Send directly to contentLoad
                false, array(
            array("name" => "New anbu", "href" => "?id=" . $_GET["id"] . "&act=new_anbu")
                )
        );
    }

    private function anbu_new_form() {
        tableParser::parse_form('squads', 'Insert new anbu', array("id"));
    }
    
    // Function for getting ANBu data
    private function get_anbu( $id, $name = null ){
        if( $id !== null ){
            return $GLOBALS['database']->fetch_data("SELECT * FROM `squads` WHERE `id` = '" . $id . "' LIMIT 1");
        }
        elseif( $name !== null ){
            return $GLOBALS['database']->fetch_data("SELECT * FROM `squads` WHERE `name` = '" . $name . "' LIMIT 1");
        }
        else{
            throw new Exception("Could retrieve anbu squad based on supplied information: ".$id." - ".$name);
        }
        
    }
    
    // Updates the user table and assigns users to anbu according to what was chosen for the ANBU
    // Also ensures that if the user was in a previous ANBU, they are removed from that
    private function update_users_anbu($data){
        
        // Get the anbu
        if( isset($_GET['bid'] ) ){
            $anbu = $this->get_anbu( $_GET['bid']  );
        }
        else{
            $anbu = $this->get_anbu( null, $_POST['name']  );
        }
        
        // Create user columns to be user
        $userColumns = array("leader_uid");
        for($i = 1; $i < 10; $i++){
            $userColumns[] = "member_".$i."_uid";
        }
            
        // Go through all the users
        foreach( $userColumns as $colName ){
            
            // Check if we have data for this user
            if( isset($data[$colName]) && !empty($data[$colName]) ){
                
                // Remove user from any previous anbu
                foreach( $userColumns as $fixColumn ){
                    
                    // Check if user is in ANBU
                    $temp = $GLOBALS['database']->fetch_data("SELECT * FROM `squads` WHERE `".$fixColumn."` = '".$data[$colName]."' AND name != '".$anbu[0]['name']."' LIMIT 1");
                    if( $temp !== "0 rows" ){
                        
                        // Remove user from this other ANBU so he/she is not part of multiple
                        $GLOBALS['database']->execute_query("
                            UPDATE `squads` 
                            SET `".$fixColumn."` = 0
                            WHERE `id` = '".$temp[0]['id']."'
                            LIMIT 1");
                        $this->displayInfo .= "<br>Removed ".$data[$colName]." from ".$fixColumn." anbu: ".$temp[0]['name'];
                    }
                    
                }
                
                // Update user
                $GLOBALS['database']->execute_query("
                     UPDATE `users_preferences`
                     SET `anbu` = '".$anbu[0]['id']."'
                     WHERE 
                        `users_preferences`.`uid` = '".$data[$colName]."'");

                $users_notifications = new NotificationSystem('', $data[$colName]);

                $users_notifications->addNotification(array(
                                                            'id' => 11,
                                                            'duration' => 'none',
                                                            'text' => 'Your anbu was set to ' . $anbu[0]['name'] . ' by admin.',
                                                            'dismiss' => 'yes'
                                                        ));

                $users_notifications->recordNotifications();

                $this->displayInfo .= "<br>Moved ".$data[$colName]." into anbu: ".$anbu[0]['name'];
                
            }            
        }
    }

    private function anbu_upload_new() {
        if (tableParser::insert_data('squads')) {
            
            // Insert log entry
            $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
            (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
            (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Anbu Change','','Anbu named: <i>" . $_POST['name'] . "</i> Created')");
            
            // Update the users who were entered into this ANBU
            $this->update_users_anbu( $_POST );
            
            // Show users the message
            $GLOBALS['page']->Message("The anbu has been inserted.".$this->displayInfo, 'Anbu System', 'id=' . $_GET['id']);
            
        } else {
            $GLOBALS['page']->Message("An error occured while inserting the anbu.", 'Anbu System', 'id=' . $_GET['id']);
        }
    }

    private function edit_form() {
        if (isset($_GET['bid'])) {
            $data = $this->get_anbu( $_GET['bid']  );
            if ($data != '0 rows') {
                tableParser::parse_form('squads', 'Edit anbu', array(), $data);
            } else {
                $GLOBALS['page']->Message("This event anbu does not exist.", 'Anbu System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid anbu ID specified.", 'Anbu System', 'id=' . $_GET['id']);
        }
    }

    private function do_edit() {
        if (isset($_GET['bid'])) {
            $data = $this->get_anbu( $_GET['bid']  );
            if ($data != '0 rows') {
                $changed = tableParser::check_data('squads', 'id', $_GET['bid'], array());
                if (tableParser::update_data('squads', 'id', $_GET['bid'])) {
                    
                    // Update the users who were entered into this ANBU
                    $this->update_users_anbu( $_POST );

                    // Insert log entry
                    $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                    (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                    (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Anbu Change','" . $_GET['bid'] . "','Anbu Name:" . $_POST['name'] . " Changed:<br>" . $changed . "')");
                    
                    // Show users the message
                    $GLOBALS['page']->Message("The anbu has been updated.".$this->displayInfo, 'Anbu System', 'id=' . $_GET['id']);
                    
                } else {
                    $GLOBALS['page']->Message("An error occured while updating the anbu.", 'Anbu System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This anbu does not exist.", 'Anbu System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid anbu ID specified.", 'Anbu System', 'id=' . $_GET['id']);
        }
    }

    private function confirm_delete() {
        if (isset($_GET['bid'])) {
            $GLOBALS['page']->Confirm("Delete this anbu?", 'Anbu System', 'Delete now!');
        } else {
            $GLOBALS['page']->Message("No valid anbu ID was specified.", 'Anbu System', 'id=' . $_GET['id']);
        }
    }

    private function do_delete() {
        if (isset($_GET['bid'])) {
            
            $data = $this->get_anbu( $_GET['bid']  );
            if ($data != '0 rows') {
                $GLOBALS['database']->execute_query("DELETE FROM `squads` WHERE `id` = '" . $data[0]['id'] . "' LIMIT 1");

                $GLOBALS['database']->execute_query("
                     UPDATE `users_preferences`, `users` 
                     SET `anbu` = '_none', 
                         `notifications` = CONCAT('id:11;duration:none;text:Your anbu was removed from the system and all characters;dismiss:yes;buttons:none;select:none;//',`notifications`)
                     WHERE 
                        `anbu` = '" . $data[0]['id'] . "' AND
                        `users_preferences`.`uid` = `users`.`id`");

                $GLOBALS['page']->Message("The anbu has been removed from the system.", 'Anbu System', 'id=' . $_GET['id']);

                $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Anbu Change','" . $_GET['bid'] . "','Anbu " . $data[0]['name'] . " Deleted')");
            } else {
                $GLOBALS['page']->Message("Anbu could not be found.", 'Anbu System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("No valid anbu ID was specified.", 'Anbu System', 'id=' . $_GET['id']);
        }
    }

}

new anbu_admin();