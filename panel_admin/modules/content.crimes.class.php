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

/*
 * 						Mission Administration
 * 			Add, remove, and modify D C B and A rank missions
 */

class crimes_admin {

    public function __construct() {

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

        if (isset($_GET['type']) && ($_GET['type'] == 'd' || $_GET['type'] == 'c' || $_GET['type'] == 'b' || $_GET['type'] == 'a')) {
            $type = $_GET['type'];
        } else {
            $type = 'a';
        }
        $missions = $GLOBALS['database']->fetch_data("SELECT * FROM `crimes` WHERE `crime_rank` = '".$type."' ORDER BY `cid` ASC");
        tableParser::show_list(
                'crimes', 
                'Crime admin, rank: ' . strtoupper($type), 
                $missions, 
            array(
                'cid' => strtoupper($type) . "-ID",
                'objective' => "Objective"
            ), array(
                array("name" => "Modify", "act" => "edit", "mid" => "table.cid"),
                array("name" => "Delete", "act" => "delete", "mid" => "table.cid")
            ), true, // Send directly to contentLoad
                false, array(
                    array("name" => "A-list", "href" => "?id=" . $_GET["id"] . "&type=a"),
                    array("name" => "B-list", "href" => "?id=" . $_GET["id"] . "&type=b"),
                    array("name" => "C-list", "href" => "?id=" . $_GET["id"] . "&type=c"),
                    array("name" => "New Crime", "href" => "?id=" . $_GET["id"] . "&act=new")
                )
        );
    }

    // Crime Functions
    private function new_form() {
        tableParser::parse_form('crimes', 'Insert new Crime', array('cid'));
    }

    private function insert_new() {
        if (tableParser::insert_data('crimes' )) {
            $GLOBALS['page']->Message('The crime has been inserted', 'Crime System', 'id=' . $_GET['id']);
            $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
            (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
            (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Crime Change','','New crime created')");
        } else {
            $GLOBALS['page']->Message("An error occured while inserting the Crime.", 'Crime System', 'id=' . $_GET['id']);
        }
    }

    private function edit_form() {
        if (isset($_GET['mid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `crimes` WHERE `cid` = '" . $_GET['mid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                tableParser::parse_form('crimes', 'Edit Crime', array('cid'), $data);
            } else {
                $GLOBALS['page']->Message("Invalid crime", 'Crime System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid crime ID", 'Crime System', 'id=' . $_GET['id']);
        }
    }

    private function do_edit() {
        if (isset($_GET['mid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `crimes` WHERE `cid` = '" . $_GET['mid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                $changed = tableParser::check_data('crimes', 'cid', $_GET['mid'], array());
                if (tableParser::update_data('crimes', 'cid', $_GET['mid'])) {
                    $GLOBALS['page']->Message('The crime has been updated', 'Crime System', 'id=' . $_GET['id']);
                    $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                    (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                    (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Crime Change','" . $_GET['mid'] . "','Crime ID:" . $_GET['mid'] . " Changed:<br>" . $changed . "')");
                } else {
                    $GLOBALS['page']->Message('An error occured while updating the crime', 'Crime System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("Invalid crime", 'Crime System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid crime ID", 'Crime System', 'id=' . $_GET['id']);
        }
    }

    private function verify_delete() {
        if (isset($_GET['mid'])) {
            $GLOBALS['page']->Confirm("Delete this crime?", 'Crime System', 'Delete now!');
        } else {
            $GLOBALS['page']->Message("No valid crime ID was specified.", 'Crime System', 'id=' . $_GET['id']);
        }
    }

    private function do_delete() {
        if (isset($_GET['mid']) && is_numeric($_GET['mid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `crimes` WHERE `cid` = '" . $_GET['mid'] . "' LIMIT 1");
            if ($data != '0 rows') {

                $GLOBALS['database']->execute_query("DELETE FROM `crimes` WHERE `cid` = '" . $_GET['mid'] . "' LIMIT 1");

                $GLOBALS['database']->execute_query("DELETE FROM `multi_battle` WHERE `mission_id` LIKE '" . $data[0]['cid'] . "%'");

                $GLOBALS['page']->Message("The Crime was removed from the database.", 'Crime System', 'id=' . $_GET['id']);

                $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Crime Change','','Crime ID: <i>" . $_GET['mid'] . "</i> was deleted')");
            } else {
                $GLOBALS['page']->Message("Invalid crime", 'Crime System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid crime ID", 'Crime System', 'id=' . $_GET['id']);
        }
    }

}

new crimes_admin();