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

class mission_admin {

    public function __construct() {

        // Parse used by entire module

        if (!isset($_GET['act'])) {
            $this->main_screen();
        } elseif ($_GET['act'] == 'new') {
            if (!isset($_POST['Submit'])) {
                $this->new_form();
            } else {
                $this->insert_new();
            }
        } elseif ($_GET['act'] == 'edit') {
            if (!isset($_POST['Submit'])) {
                $this->edit_form();
            } else {
                $this->do_edit();
            }
        } elseif ($_GET['act'] == 'delete') {
            if (!isset($_POST['Submit'])) {
                $this->verify_delete();
            } else {
                $this->do_delete();
            }
        }
    }

    private function main_screen() {
        if (isset($_GET['type']) && ($_GET['type'] == 'd' || $_GET['type'] == 'c' || $_GET['type'] == 'b' || $_GET['type'] == 'a' || $_GET['type'] == 's')) {
            $type = $_GET['type'];
        } else {
            $type = 'a';
        }
        $missions = $GLOBALS['database']->fetch_data("SELECT * FROM `missions` WHERE `mission_rank` = '".$type."'ORDER BY `mid` ASC");
        tableParser::show_list(
            'missions',
            'Mission admin, rank: ' . strtoupper($type), 
            $missions, array(
            'mid' => "ID",
            'objective' => "Objective"
                ), array(
            array("name" => "Modify", "act" => "edit", "mid" => "table.mid"),
            array("name" => "Delete", "act" => "delete", "mid" => "table.mid")
                ), true, // Send directly to contentLoad
                false, array(
            array("name" => "S-list", "href" => "?id=" . $_GET["id"] . "&type=s"),
            array("name" => "A-list", "href" => "?id=" . $_GET["id"] . "&type=a"),
            array("name" => "B-list", "href" => "?id=" . $_GET["id"] . "&type=b"),
            array("name" => "C-list", "href" => "?id=" . $_GET["id"] . "&type=c"),
            array("name" => "D-list", "href" => "?id=" . $_GET["id"] . "&type=d"),
            array("name" => "New Mission", "href" => "?id=" . $_GET["id"] . "&act=new&type=s")
                )
        );
    }

    private function new_form() {
        tableParser::parse_form('missions', 'Insert new mission', array('mid'));
    }

    private function insert_new() {
        if (tableParser::insert_data('missions' )) {
            $GLOBALS['page']->Message('The mission has been inserted', 'Mission System', 'id=' . $_GET['id']);
            $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
            (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
            (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Mission Change','','New mission created')");
        } else {
            $GLOBALS['page']->Message("An error occured while inserting the mission.", 'Mission System', 'id=' . $_GET['id']);
        }
    }

    private function edit_form() {
        if (isset($_GET['mid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `missions` WHERE `mid` = '" . $_GET['mid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                tableParser::parse_form('missions', 'Edit mission', array('mid'), $data);
            } else {
                $GLOBALS['page']->Message("Invalid mission", 'Mission System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid mission ID", 'Mission System', 'id=' . $_GET['id']);
        }
    }

    private function do_edit() {
        if (isset($_GET['mid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `missions` WHERE `mid` = '" . $_GET['mid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                $changed = tableParser::check_data('missions', 'mid', $_GET['mid'], array());
                if (tableParser::update_data('missions', 'mid', $_GET['mid'])) {
                    $GLOBALS['page']->Message('The mission has been updated', 'Mission System', 'id=' . $_GET['id']);
                    $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                    (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                    (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Mission Change','" . $_GET['mid'] . "','mission ID:" . $_GET['mid'] . " Changed:<br>" . $changed . "')");
                } else {
                    $GLOBALS['page']->Message('An error occured while updating the mission', 'Mission System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("Invalid mission", 'Mission System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid mission ID", 'Mission System', 'id=' . $_GET['id']);
        }
    }

    private function verify_delete() {
        if (isset($_GET['mid'])) {
            $GLOBALS['page']->Confirm("Delete this mission?", 'Mission System', 'Delete now!');
        } else {
            $GLOBALS['page']->Message("No valid mission ID was specified.", 'Mission System', 'id=' . $_GET['id']);
        }
    }

    private function do_delete() {
        if (isset($_GET['mid']) && is_numeric($_GET['mid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `missions` WHERE `mid` = '" . $_GET['mid'] . "' LIMIT 1");
            if ($data != '0 rows') {

                $GLOBALS['database']->execute_query("DELETE FROM `missions` WHERE `mid` = '" . $_GET['mid'] . "' LIMIT 1");

                $GLOBALS['database']->execute_query("DELETE FROM `multi_battle` WHERE `mission_id` LIKE '" . $data[0]['mid'] . "%'");

                $GLOBALS['page']->Message("The mission was removed from the database.", 'Mission System', 'id=' . $_GET['id']);

                $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Mission Change','','Mission ID: <i>" . $_GET['mid'] . "</i> was deleted')");
            } else {
                $GLOBALS['page']->Message("Invalid mission", 'Mission System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid mission ID", 'Mission System', 'id=' . $_GET['id']);
        }
    }

}

new mission_admin();