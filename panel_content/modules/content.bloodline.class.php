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
require_once(Data::$absSvrPath.'/global_libs/User/user.class.php');

class bloodline_admin {

    public function __construct() {

        // Set permissions
        $this->canEdit = array("Admin", "ContentAdmin", "ContentMember");
        $this->canRead = array("Admin", "ContentAdmin", "ContentMember");

        $GLOBALS['userdata'] = $GLOBALS['database']->load_user($_SESSION['uid']);
        $GLOBALS['user'] = new user();

        try{
            if (!isset($_GET['act'])) {
                $this->main_screen();
            } elseif ($_GET['act'] == 'read_bloodline') {
                $this->showBloodline();
            } elseif ($_GET['act'] == 'new_bloodline') {
                if (!isset($_POST['Submit'])) {
                    $this->bloodline_new_form();
                } else {
                    $this->bloodline_upload_new();
                }
            } elseif ($_GET['act'] == 'edit_bloodline') {
                if (!isset($_POST['Submit'])) {
                    $this->edit_form();
                } else {
                    $this->do_edit();
                }
            } elseif ($_GET['act'] == 'delete_bloodline') {
                if (!isset($_POST['Submit'])) {
                    $this->confirm_delete();
                } else {
                    $this->do_delete();
                }
            } elseif ($_GET['act'] == 'browse') {
                $this->browse_list();
            }
        } catch (Exception $ex) {
            $GLOBALS['page']->Message( $ex->getMessage() , 'Bloodline System', 'id=' . $_GET['id']);
        }        
    }
    
    // Check if user can view this
    private function hasReadPermission(){
        if( in_array($GLOBALS['userdata'][0]['user_rank'], $this->canRead ) ){
            return true;
        }
        return false;
    }
    
    // Check if user can edit this
    private function hasEditPermission(){
        if( in_array($GLOBALS['userdata'][0]['user_rank'], $this->canEdit ) ){
            return true;
        }
        return false;
    }
    
    // Check the edit permission
    private function checkEditPermission(){
        if( $this->hasEditPermission() ){
            return true;
        }
        throw new Exception("You do not have permission to do edit data in this module");
    }
    
    // Show bloodline data
    private function showBloodline(){
        
        // Check if we can find the bloodline
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `bloodlines` WHERE `name` = '" . $_GET['bid'] . "'");
        if ($data != '0 rows') {

            $displayRows = tableParser::parseDatarowForDisplay($data[0]);

            // Parse table
            tableParser::show_list(
                    'item', 'Bloodline: ' . $data[0]['name'], $displayRows, array(
                'key' => 'Key',
                'value' => "Value"
                    ),false, true, // Send directly to contentLoad
                    false, false, false, // No sorting on columns
                    false, // No pretty options
                    false, // No top search field
                    ''
            );

            $GLOBALS['template']->assign("returnLink", true);
        } else {
            throw new Exception("This bloodline does not exist.");
        }
    }

    // Show overview
    private function main_screen() {

        // Create options
        $topOptions = array();

        // For users with edit permissions
        if( $this->hasEditPermission() ){
            $options[] = array("name" => "Modify", "act" => "edit_bloodline", "bid" => "table.name");
            $options[] = array("name" => "Delete", "act" => "delete_bloodline", "bid" => "table.name");
            $topOptions[] = array("name" => "New bloodline", "href" => "?id=" . $_GET["id"] . "&act=new_bloodline");
        }
        elseif( $this->hasReadPermission() ){
            $options[] = array("name" => "Read", "act" => "read_bloodline", "bid" => "table.name");
        }
        
        
        // Get bloodline and show them
        $bloodlines = $GLOBALS['database']->fetch_data("SELECT * FROM `bloodlines` ORDER BY `rarity`");
        tableParser::show_list(
                'bloodline', 'Bloodline admin', $bloodlines, array(
            'entry_id' => "ID",
            'name' => "Name",
            'rarity' => "Rarity",
            'event_bloodline' => "Event",
            'affinity_1' => "Element1",
            'affinity_2' => "Element2",
            'special_affinity' => "Special Element",
            'village' => "Village",
            'tags' => "tags"
                ), $options, true, // Send directly to contentLoad
                false, $topOptions
        );
    }

    private function bloodline_new_form() {
        $this->checkEditPermission();
        tableParser::parse_form('bloodlines', 'Insert new bloodline', array());
    }

    private function bloodline_upload_new() {
        $this->checkEditPermission();
        if (tableParser::insert_data('bloodlines')) {
            //$GLOBALS['database']->execute_query("UPDATE `bloodlines` SET `event_bloodline` = 'Yes' WHERE `name` = '" . $_POST['name'] . "' LIMIT 1");
            $GLOBALS['page']->Message("The bloodline has been inserted.", 'Bloodline System', 'id=' . $_GET['id']);

            $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
            (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
            (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Bloodline Change','','Bloodline named: <i>" . $_POST['name'] . "</i> Created')");
        } else {
            throw new Exception("An error occured while inserting the bloodline.");
        }
    }

    private function edit_form() {
        $this->checkEditPermission();
        if (isset($_GET['bid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `bloodlines` WHERE `name` = '" . $_GET['bid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                tableParser::parse_form('bloodlines', 'Edit bloodline', array(), $data);
            } else {
                $GLOBALS['page']->Message("This event bloodline does not exist.", 'Bloodline System', 'id=' . $_GET['id']);
            }
        } else {
            throw new Exception("Invalid bloodline ID specified.");
        }
    }

    private function do_edit() {
        $this->checkEditPermission();
        if (isset($_GET['bid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `bloodlines` WHERE `name` = '" . $_GET['bid'] . "'  LIMIT 1");
            if ($data != '0 rows') {
                $changed = tableParser::check_data('bloodlines', 'name', $_GET['bid'], array());
                if (tableParser::update_data('bloodlines', 'name', $_GET['bid'])) {

                    //$GLOBALS['database']->execute_query("UPDATE `bloodlines` SET `event_bloodline` = 'Yes' WHERE `name` = '" . $_POST['name'] . "' LIMIT 1");

                    $GLOBALS['page']->Message("The bloodline has been updated.", 'Bloodline System', 'id=' . $_GET['id']);

                    $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                    (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                    (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Bloodline Change','" . $_GET['bid'] . "','Bloodline Name:" . $_POST['name'] . " Changed:<br>" . $changed . "')");

                } else {
                    throw new Exception("An error occured while updating the bloodline.");
                }
            } else {
                throw new Exception("This bloodline does not exist.");
            }
        } else {
            throw new Exception("Invalid bloodline ID specified.");
        }
    }

    private function confirm_delete() {
        $this->checkEditPermission();
        if (isset($_GET['bid'])) {
            $GLOBALS['page']->Confirm("Delete this bloodline?", 'Bloodline System', 'Delete now!');
        } else {
            throw new Exception("No valid bloodline ID was specified.");
        }
    }

    private function do_delete() {
        $this->checkEditPermission();
        if (isset($_GET['bid'])) {
            $query = "SELECT `name`,`rarity` FROM `bloodlines` WHERE `name` = '" . $_GET['bid'] . "'   LIMIT 1";
            $data = $GLOBALS['database']->fetch_data($query);
            if ($data != '0 rows') {
                $GLOBALS['database']->execute_query("DELETE FROM `bloodlines` WHERE `name` = '" . $data[0]['name'] . "' LIMIT 1");

                $GLOBALS['database']->execute_query("UPDATE `users` SET `bloodline` = 'None', `notifications` = CONCAT('duration:none;text:Your bloodline was removed from the system and all characters;dismiss:yes;buttons:none;select:none;//',`notifications`) WHERE `bloodline` = '" . $data[0]['name'] . "'");

                $GLOBALS['page']->Message("The bloodline has been removed from the system.", 'Bloodline System', 'id=' . $_GET['id']);

                $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Bloodline Change','" . $_GET['bid'] . "','Bloodline " . $data[0]['name'] . " Deleted')");
            } else {
                throw new Exception("Bloodline could not be found.");
                $GLOBALS['page']->Message("", 'Bloodline System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("No valid bloodline ID was specified.", 'Bloodline System', 'id=' . $_GET['id']);
        }
    }

}

new bloodline_admin();
