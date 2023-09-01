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

class clan_admin {

    public function __construct() {

        if (!isset($_GET['act'])) {
            $this->main_screen();
        } elseif ($_GET['act'] == 'new_clan') {
            if (!isset($_POST['Submit'])) {
                $this->clan_new_form();
            } else {
                $this->clan_upload_new();
            }
        } elseif ($_GET['act'] == 'edit_clan') {
            if (!isset($_POST['Submit'])) {
                $this->edit_form();
            } else {
                $this->do_edit();
            }
        } elseif ($_GET['act'] == 'delete_clan') {
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

        $clans = $GLOBALS['database']->fetch_data("SELECT * FROM `clans` ORDER BY `name`");
        tableParser::show_list(
                'clan', 'Clan admin', $clans, array(
            'id' => "ID",
                    'name' => "Name",
            'village' => "Village",
                    'clan_type' => "Type"
                ), array(
            array("name" => "Modify", "act" => "edit_clan", "bid" => "table.name"),
            array("name" => "Delete", "act" => "delete_clan", "bid" => "table.name")
                ), true, // Send directly to contentLoad
                false, array(
            array("name" => "New clan", "href" => "?id=" . $_GET["id"] . "&act=new_clan")
                )
        );
    }

    private function clan_new_form() {
        tableParser::parse_form('clans', 'Insert new clan', array());
    }

    private function clan_upload_new() {
        if (tableParser::insert_data('clans')) {
            //$GLOBALS['database']->execute_query("UPDATE `clans` SET `event_clan` = 'Yes' WHERE `name` = '" . $_POST['name'] . "' LIMIT 1");
            $GLOBALS['page']->Message("The clan has been inserted.", 'Clan System', 'id=' . $_GET['id']);

            $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
            (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
            (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Clan Change','','Clan named: <i>" . $_POST['name'] . "</i> Created')");
        } else {
            $GLOBALS['page']->Message("An error occured while inserting the clan.", 'Clan System', 'id=' . $_GET['id']);
        }
    }

    private function edit_form() {
        if (isset($_GET['bid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `clans` WHERE `name` = '" . $_GET['bid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                tableParser::parse_form('clans', 'Edit clan', array(), $data);
            } else {
                $GLOBALS['page']->Message("This event clan does not exist.", 'Clan System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid clan ID specified.", 'Clan System', 'id=' . $_GET['id']);
        }
    }

    private function do_edit() {
        if (isset($_GET['bid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `clans` WHERE `name` = '" . $_GET['bid'] . "'  LIMIT 1");
            if ($data != '0 rows') {
                $changed = tableParser::check_data('clans', 'name', $_GET['bid'], array());
                if (tableParser::update_data('clans', 'name', $_GET['bid'])) {

                    //$GLOBALS['database']->execute_query("UPDATE `clans` SET `event_clan` = 'Yes' WHERE `name` = '" . $_POST['name'] . "' LIMIT 1");

                    $GLOBALS['page']->Message("The clan has been updated.", 'Clan System', 'id=' . $_GET['id']);

                    $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                    (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                    (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Clan Change','" . $_GET['bid'] . "','Clan Name:" . $_POST['name'] . " Changed:<br>" . $changed . "')");
                } else {
                    $GLOBALS['page']->Message("An error occured while updating the clan.", 'Clan System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This clan does not exist.", 'Clan System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid clan ID specified.", 'Clan System', 'id=' . $_GET['id']);
        }
    }

    private function confirm_delete() {
        if (isset($_GET['bid'])) {
            $GLOBALS['page']->Confirm("Delete this clan?", 'Clan System', 'Delete now!');
        } else {
            $GLOBALS['page']->Message("No valid clan ID was specified.", 'Clan System', 'id=' . $_GET['id']);
        }
    }

    private function do_delete() {
        if (isset($_GET['bid'])) {
            $query = "SELECT `name`,`rarity` FROM `clans` WHERE `name` = '" . $_GET['bid'] . "'   LIMIT 1";
            $data = $GLOBALS['database']->fetch_data($query);
            if ($data != '0 rows') {
                $GLOBALS['database']->execute_query("DELETE FROM `clans` WHERE `name` = '" . $data[0]['name'] . "' LIMIT 1");

                $GLOBALS['database']->execute_query("UPDATE `users` SET `clan` = 'None', `notifications` = CONCAT('duration:none;text:Your clan was removed from the system and all characters;dismiss:yes;buttons:none;select:none;//',`notifications`) WHERE `clan` = '" . $data[0]['name'] . "'");

                $GLOBALS['page']->Message("The clan has been removed from the system.", 'Clan System', 'id=' . $_GET['id']);

                $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Clan Change','" . $_GET['bid'] . "','Clan " . $data[0]['name'] . " Deleted')");
            } else {
                $GLOBALS['page']->Message("Clan could not be found.", 'Clan System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("No valid clan ID was specified.", 'Clan System', 'id=' . $_GET['id']);
        }
    }

}

new clan_admin();