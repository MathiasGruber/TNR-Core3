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

class blueMessage {

    public function __construct() {
        if (!isset($_GET['act'])) {
            $this->bloodline_screen();
        } elseif ($_GET['act'] == 'new_bloodline') {
            if (!isset($_POST['Submit'])) {
                $this->bloodline_new_form();
            } else {
                $this->bloodline_upload_new();
            }
        } elseif ($_GET['act'] == 'edit_bloodline') {
            if (!isset($_POST['Submit'])) {
                $this->bloodline_edit_form();
            } else {
                $this->bloodline_do_edit();
            }
        } elseif ($_GET['act'] == 'delete_bloodline') {
            if (!isset($_POST['Submit'])) {
                $this->bloodline_confirm_delete();
            } else {
                $this->bloodline_do_delete();
            }
        }
    }

    // Bloodline creation
    private function bloodline_screen() {

        // Show form
        $min = tableParser::get_page_min();
        $bloodlines = $GLOBALS['database']->fetch_data("SELECT * FROM `bloodlines` WHERE `event_bloodline` = 'Yes' ORDER BY `entry_id` DESC LIMIT " . $min . ",10");
        tableParser::show_list(
                'bloodline', 'Bloodline admin', $bloodlines, array(
            'entry_id' => 'ID',
            'name' => "Name",
            'rarity' => "Rarity"
                ), array(
            array("name" => "Modify", "act" => "edit_bloodline", "bid" => "table.entry_id"),
            array("name" => "Delete", "act" => "delete_bloodline", "bid" => "table.entry_id")
                ), true, // Send directly to contentLoad
                true, array(
            array("name" => "New bloodline", "href" => "?id=" . $_GET["id"] . "&act=new_bloodline")
                )
        );
    }


    private function bloodline_confirm_delete() {
        if (isset($_GET['bid'])) {
            $GLOBALS['page']->Confirm("Delete this bloodline?", 'Bloodline System', 'Delete now!');
        } else {
            $GLOBALS['page']->Message("No valid bloodline ID was specified.", 'Bloodline System', 'id=' . $_GET['id']);
        }
    }

    private function bloodline_do_delete() {
        if (isset($_GET['bid'])) {
            $query = "SELECT `name`,`rarity`,`event_bloodline` FROM `bloodlines` WHERE `entry_id` = '" . $_GET['bid'] . "'  AND `event_bloodline` = 'Yes' LIMIT 1";
            $data = $GLOBALS['database']->fetch_data($query);
            if ($data != '0 rows') {
                if( $data[0]['event_bloodline'] == "Yes" ){
                    $GLOBALS['database']->execute_query("DELETE FROM `bloodlines` WHERE `name` = '" . $data[0]['name'] . "' LIMIT 1");

                    $GLOBALS['database']->execute_query("UPDATE `users` SET `bloodline` = 'None', `notifications` = CONCAT('duration:none;text:Your bloodline was removed from the system and all characters;dismiss:yes;buttons:none;select:none;//',`notifications`) WHERE `bloodline` = '" . $data[0]['name'] . "'");

                    $GLOBALS['page']->Message("The bloodline has been removed from the system.", 'Bloodline System', 'id=' . $_GET['id']);

                    $GLOBALS['page']->setLogEntry("Bloodline Change", 'Bloodline '. $data[0]['name'] .' Deleted', $_GET['bid'] );

                }
                else{
                    $GLOBALS['page']->Message("Not an event entry", 'Bloodline System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("Bloodline could not be found.", 'Bloodline System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("No valid bloodline ID was specified.", 'Bloodline System', 'id=' . $_GET['id']);
        }
    }

    private function bloodline_edit_form() {
        if (isset($_GET['bid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `bloodlines` WHERE `entry_id` = '" . $_GET['bid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                if( $data[0]['event_bloodline'] == "Yes" ){
                    tableParser::parse_form('bloodlines', 'Edit bloodline', array('event_bloodline', 'entry_id'), $data);
                }
                else{
                    $GLOBALS['page']->Message("Not an event entry", 'Bloodline System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This event bloodline does not exist.", 'Bloodline System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid bloodline ID specified.", 'Bloodline System', 'id=' . $_GET['id']);
        }
    }

    private function bloodline_do_edit() {
        if (isset($_GET['bid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `bloodlines` WHERE `entry_id` = '" . $_GET['bid'] . "'  AND `event_bloodline` = 'Yes' LIMIT 1");
            if ($data != '0 rows') {
                if( $data[0]['event_bloodline'] == "Yes" ){
                    $changed = tableParser::check_data('bloodlines', 'entry_id', $_GET['bid'], array());
                    if (tableParser::update_data('bloodlines', 'entry_id', $_GET['bid'])) {

                        $GLOBALS['database']->execute_query("UPDATE `bloodlines` SET `event_bloodline` = 'Yes' WHERE `name` = '" . $_POST['name'] . "' LIMIT 1");

                        $GLOBALS['page']->Message("The bloodline has been updated.", 'Bloodline System', 'id=' . $_GET['id']);

                        $GLOBALS['page']->setLogEntry("Bloodline Change", "Bloodline Name:" . $_POST['name'] . " Changed:<br>" . $changed , $_GET['bid'] );

                    } else {
                        $GLOBALS['page']->Message("An error occured while updating the bloodline.", 'Bloodline System', 'id=' . $_GET['id']);
                    }
                }
                else{
                    $GLOBALS['page']->Message("Not an event entry", 'Bloodline System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This event bloodline does not exist.", 'Bloodline System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid bloodline ID specified.", 'Bloodline System', 'id=' . $_GET['id']);
        }
    }

    private function bloodline_new_form() {
        tableParser::parse_form('bloodlines', 'Insert new bloodline', array('event_bloodline','entry_id'));
    }

    private function bloodline_upload_new() {
        $data['event_bloodline'] = 'Yes';
        if (tableParser::insert_data('bloodlines', $data)) {
            $GLOBALS['database']->execute_query("UPDATE `bloodlines` SET `event_bloodline` = 'Yes' WHERE `name` = '" . $_POST['name'] . "' LIMIT 1");
            $GLOBALS['page']->Message("The bloodline has been inserted.", 'Bloodline System', 'id=' . $_GET['id']);
            $GLOBALS['page']->setLogEntry("Bloodline Change", 'Bloodline named: <i>' . $_POST['name'] . '</i> Created'
                                                             .'<br>name: '.$_POST['name']
                                                             .'<br>regen increase: '.$_POST['regen increase']
                                                             .'<br>trait 1: '.$_POST['trait 1']
                                                             .'<br>trait 2: '.$_POST['trait 2']
                                                             .'<br>trait 3: '.$_POST['trait 3']
                                                             .'<br>trait 4: '.$_POST['trait 4'],"");

        } else {
            $GLOBALS['page']->Message("An error occured while inserting the bloodline.", 'Bloodline System', 'id=' . $_GET['id']);
        }
    }


}

new blueMessage();