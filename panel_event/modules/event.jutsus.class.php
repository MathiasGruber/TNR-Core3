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

class jutsusPanel {

    public function __construct() {
        if (!isset($_GET['act'])) {  // Jutsu System
            $this->jutsu_screen();
        } elseif ($_GET['act'] == 'jutsu_modify' && is_numeric($_GET['jid'])) {
            if (!isset($_POST['Submit'])) {
                $this->jutsu_update_form();
            } else {
                $this->update_jutsu();
            }
        } elseif ($_GET['act'] == 'jutsu_delete' && is_numeric($_GET['jid'])) {
            if (!isset($_POST['Submit'])) {
                $this->jutsu_verify_delete();
            } else {
                $this->do_jutsu_delete();
            }
        } elseif ($_GET['act'] == 'jutsu_new') {
            if (!isset($_POST['Submit'])) {
                $this->jutsu_new_form();
            } else {
                $this->insert_jutsu();
            }
        }
    }

    // Jutsu Functions
    function jutsu_screen() {

        // Show form
        if (isset($_GET['type'])) {
            switch ($_GET['type']) {
                case "gen": $type = 'genjutsu';
                    break;
                case "tai": $type = 'taijutsu';
                    break;
                case "wea": $type = 'weapon';
                    break;
                case "nin": $type = 'ninjutsu';
                    break;
                case "high": $type = 'highest';
                    break;
            }
        } else {
            $type = 'ninjutsu';
        }

        $query = "SELECT * FROM `jutsu` WHERE `attack_type` = '" . $type . "' AND `event_jutsu` = 'Yes'";

        $where = array();

        if( isset($_POST['search']) )
            $where[] = " `". ($_POST['search'] == 'jid' ? 'id' : $_POST['search']) ."` LIKE '%{$_POST[$_POST['search']]}%' ";

        if( count($where) >= 1 )
            $query .= " AND ".implode(' AND ',$where);
            
        $result = $GLOBALS['database']->fetch_data($query);

        tableParser::show_list(
                'jutsu', 
                'Jutsu admin', 
                $result, 
                array(
                    'id' => 'JutsuID',
                    'name' => "Name",
                    'required_rank' => "Rank",
                    'attack_type' => "Type",
                    'jutsu_type' => "Jutsu Type",
                    'tags' => 'Tags',
                    'notes' => "Notes"
                ), 
                array(
                    array("name" => "Modify", "act" => "jutsu_modify", "jid" => "table.id"),
                    array("name" => "Delete", "act" => "jutsu_delete", "jid" => "table.id")
                ), 
                true, // Send directly to contentLoad
                false, 
                array(
                    array("name" => "New Jutsu", "href" => "?id=" . $_GET["id"] . "&act=jutsu_new"),
                    array("name" => "Ninjutsu", "href" => "?id=" . $_GET["id"] . "&type=nin"),
                    array("name" => "Genjutsu", "href" => "?id=" . $_GET["id"] . "&type=gen"),
                    array("name" => "Taijutsu", "href" => "?id=" . $_GET["id"] . "&type=tai"),
                    array("name" => "Weapon", "href" => "?id=" . $_GET["id"] . "&type=wea"),
                    array("name" => "Highest", "href" => "?id=" . $_GET["id"] . "&type=high")
                ),
                true,
                false,
                array(
                    array(
                        'infoText'=>'Jid',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'jid',
                        'postIdentifier'=>'search',
                        'inputName'=>'jid'
                    ),
                    array(
                        'infoText'=>'Name',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'name',
                        'postIdentifier'=>'search',
                        'inputName'=>'name'
                    ),
                    array(
                        'infoText'=>'Rank (1-5)',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'required_rank',
                        'postIdentifier'=>'search',
                        'inputName'=>'required_rank'
                    ),
                    array(
                        'infoText'=>'Description',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'description',
                        'postIdentifier'=>'search',
                        'inputName'=>'description'
                    ),
                    array(
                        'infoText'=>'Notes',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'notes',
                        'postIdentifier'=>'search',
                        'inputName'=>'notes'
                    ),
                    array(
                        'infoText'=>'Tags',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'tags',
                        'postIdentifier'=>'search',
                        'inputName'=>'tags'
                    )
                )
        );
    }

    function jutsu_new_form() {
        tableParser::parse_form('jutsu', 'New jutsu', array('id', 'event_jutsu'));
    }

    function insert_jutsu() {
        $data['event_jutsu'] = 'Yes';
        if (tableParser::insert_data('jutsu', $data)) {
            $GLOBALS['database']->execute_query("UPDATE `jutsu` SET `event_jutsu` = 'Yes' WHERE `name` = '" . $_POST['name'] . "' LIMIT 1");
            $GLOBALS['page']->setLogEntry("Jutsu Change", 'Jutsu named: <i>'. $_POST['name'] . '</i> Created'
                                                         .'<br>name: '.$_POST['name']
                                                         .'<br>element: '.$_POST['element']
                                                         .'<br>village: '.$_POST['village']
                                                         .'<br>bloodline: '.$_POST['bloodline']
                                                         .'<br>clan: '.$_POST['clan']
                                                         .'<br>attack_type: '.$_POST['attack_type']
                                                         .'<br>jutsu_type: '.$_POST['jutsu_type'],"" );
            $GLOBALS['page']->Message("The event jutsu has been added.", 'Jutsu System', 'id=' . $_GET['id']);
        } else {
            $GLOBALS['page']->Message("An error occured and the jutsu has not been added.", 'Jutsu System', 'id=' . $_GET['id']);
        }
    }

    function jutsu_update_form() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `jutsu` WHERE `id` = '" . $_GET['jid'] . "'");
        if ($data != '0 rows') {
            if ($data[0]['event_jutsu'] == "Yes") {
                tableParser::parse_form('jutsu', 'Update jutsu', array('id'), $data);
            } else {
                $GLOBALS['page']->Message("Not an event entry.", 'Jutsu System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("This jutsu does not exist.", 'Jutsu System', 'id=' . $_GET['id']);
        }
    }

    function update_jutsu() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `jutsu` WHERE `id` = '" . $_GET['jid'] . "'  AND `event_jutsu` = 'Yes' LIMIT 1");
        if ($data != '0 rows') {
            if ($data[0]['event_jutsu'] == "Yes") {
                $changed = tableParser::check_data('jutsu', 'id', $_GET['jid'], array());

                if (tableParser::update_data('jutsu', 'id', $_GET['jid'])) {
                    $GLOBALS['page']->setLogEntry("Jutsu Change", 'Jutsu ID:' . $_GET['jid'] . ' Changed:<br>' . $changed , $_GET['jid'] );
                    $GLOBALS['database']->execute_query("UPDATE `jutsu` SET `event_jutsu` = 'Yes' WHERE `name` = '" . $_POST['name'] . "' LIMIT 1");
                    $GLOBALS['page']->Message("The jutsu has been updated.", 'Jutsu System', 'id=' . $_GET['id']);
                } else {
                    $GLOBALS['page']->Message("An error occured while updating the jutsu.", 'Jutsu System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("Not an event entry.", 'Jutsu System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Jutsu could not be found in the system.", 'Jutsu System', 'id=' . $_GET['id']);
        }
    }

    function jutsu_verify_delete() {
        if (isset($_GET['jid'])) {
            $GLOBALS['page']->Confirm("Delete this jutsu?", 'Jutsu System', 'Delete now!');
        } else {
            $GLOBALS['page']->Message("No valid jutsu ID was specified.", 'Jutsu System', 'id=' . $_GET['id']);
        }
    }

    function do_jutsu_delete() {
        if ($GLOBALS['database']->execute_query("DELETE FROM `jutsu` WHERE `id` = '" . $_GET['jid'] . "' AND `event_jutsu` = 'Yes' LIMIT 1")) {
            if ($GLOBALS['database']->execute_query("DELETE FROM `users_jutsu` WHERE `jid` = '" . $_GET['jid'] . "'")) {

                $GLOBALS['page']->setLogEntry("Jutsu Change", "Jutsu ID: <i>" . $_GET['jid'] . "</i> was Deleted");
                $GLOBALS['page']->Message("The jutsu has been deleted from the jutsu table, and all users.", 'Jutsu System', 'id=' . $_GET['id']);
            } else {
                $GLOBALS['page']->Message("The jutsu has been deleted from the jutsu table but a problem occured when deleting the jutsu from all the users, user jutsu data is probably broken, contact an administrator with PMA access.", 'Jutsu System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("An error occured while deleting the jutsu.", 'Jutsu System', 'id=' . $_GET['id']);
        }
    }

}

new jutsusPanel();