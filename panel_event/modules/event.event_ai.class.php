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
        if (!isset($_GET['act'])) {
            $this->list_AI();
        } elseif ($_GET['act'] == 'addAI') {
            if (!isset($_POST['Submit'])) {
                $this->add_AI_form();
            } else {
                $this->insert_add_AI();
            }
        } elseif ($_GET['act'] == 'delAI') {
            if (!isset($_POST['Submit'])) {
                $this->del_AI_form();
            } else {
                $this->do_delete_AI();
            }
        } elseif ($_GET['act'] == 'editAI') {
            if (!isset($_POST['Submit'])) {
                $this->edit_AI_form();
            } else {
                $this->insert_edit_AI();
            }
        }
    }

    // AI Functions
    private function list_AI() {

        // Show form
        $query = "SELECT * FROM `ai` WHERE `type` = 'event'";
        $where = array();
        
        if( isset($_POST['search']) )
            $where[] = " `". ($_POST['search'] == 'aid' ? 'id' : $_POST['search']) ."` LIKE '%{$_POST[$_POST['search']]}%' ";

        if( count($where) >= 1 )
            $query .= " AND ".implode(' AND ',$where);

        $query .= " ORDER BY `id` DESC";
            
        $result = $GLOBALS['database']->fetch_data($query);

        tableParser::show_list(
            'event', 
            'Event characters', 
            $result, 
            array(
                'id' => 'ID',
                'name' => "Username",
                'rank' => "Rank",
                'notes' => "Notes"
            ), 
            array(
                array("name" => "Modify", "act" => "editAI", "uid" => "table.id"),
                array("name" => "Delete", "act" => "delAI", "uid" => "table.id")
            ),
            true, // Send directly to contentLoad
            false, 
            array(
                array("name" => "Add Event AI", "href" => "?id=" . $_GET["id"] . "&act=addAI")
            ),
            true,
            false,
            array(
                array(
                    'infoText'=>'Aid',
                    'href'=>"?id=" . $_GET["id"],
                    'postField'=>'aid',
                    'postIdentifier'=>'search',
                    'inputName'=>'aid'
                ),
                array(
                    'infoText'=>'Name',
                    'href'=>"?id=" . $_GET["id"],
                    'postField'=>'name',
                    'postIdentifier'=>'search',
                    'inputName'=>'name'
                ),
                array(
                    'infoText'=>'Notes',
                    'href'=>"?id=" . $_GET["id"],
                    'postField'=>'notes',
                    'postIdentifier'=>'search',
                    'inputName'=>'notes'
                )
            )
        );
    }

    private function add_AI_form() {
        tableParser::parse_form('ai', 'New AI', array('id', 'type'));
    }

    private function insert_add_AI() {
        $users = $GLOBALS['database']->fetch_data("SELECT `name` FROM `ai` WHERE `name` LIKE '" . $_POST['name'] . "' LIMIT 1");
        if ($users == '0 rows') {
            $data['type'] = 'event';
            if (tableParser::insert_data('ai', $data)) {
                $GLOBALS['page']->setLogEntry("Event AI", 'Event AI <i>' . $_POST['name'] . '</i> Created' );
                $GLOBALS['page']->Message("AI was successfully created.", 'AI System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("AI name already in use.", 'AI System', 'id=' . $_GET['id']);
        }
    }

    private function del_AI_form() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            $GLOBALS['page']->Confirm("Delete this AI?", 'AI System', 'Delete now!');
        } else {
            $GLOBALS['page']->Message("No valid AI ID was specified.", 'AI System', 'id=' . $_GET['id']);
        }
    }

    private function do_delete_AI() {
        if (is_numeric($_GET['uid']) && $_GET['uid'] > 0) {
            $users = $GLOBALS['database']->fetch_data("SELECT `name`,`rank`,`type` FROM `ai` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
            if ($users != '0 rows') {
                if ($users[0]['type'] == 'event') {
                    if ($GLOBALS['database']->execute_query("DELETE FROM `ai` WHERE `id` = '" . $_GET['uid'] . "' AND `type` = 'event' LIMIT 1")) {
                        $GLOBALS['page']->setLogEntry("Event AI", 'Event AI <i>' . $users[0]['name'] . '</i> deleted', $_GET['uid'] );
                        $GLOBALS['page']->Message("The AI has been removed.", 'AI System', 'id=' . $_GET['id']);
                    } else {
                        $GLOBALS['page']->Message("An error occured while removing the AI.", 'AI System', 'id=' . $_GET['id']);
                    }
                } else {
                    $GLOBALS['page']->Message("This is not an event AI.", 'AI System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This AI does not exist.", 'AI System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("An invalid AI has been specified.", 'AI System', 'id=' . $_GET['id']);
        }
    }

    private function edit_AI_form() {
        if (isset($_GET['uid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `ai` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                if ($data[0]['type'] == 'event') {
                    tableParser::parse_form('ai', 'Edit AI', array('id', 'type'), $data);
                } else {
                    $GLOBALS['page']->Message("This is not an event AI.", 'AI System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This AI does not exist.", 'AI System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("No AI was specified.", 'AI System', 'id=' . $_GET['id']);
        }
    }

    private function insert_edit_AI() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `ai` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                if ($data[0]['type'] == 'event') {

                    // Get what is changed
                    $changed = tableParser::check_data('ai', 'id', $_GET['uid'], array('id', 'type'));

                    // Run the update
                    if (tableParser::update_data('ai', 'id', $_GET['uid'])) {
                        $GLOBALS['page']->setLogEntry("Event AI", 'AI stats updated:<br> ' . $changed , $_GET['uid'] );
                        $GLOBALS['page']->Message("The AI has been updated.", 'AI System', 'id=' . $_GET['id']);
                    } else {
                        $GLOBALS['page']->Message("An error occured while updating the AI.", 'AI System', 'id=' . $_GET['id']);
                    }
                } else {
                    $GLOBALS['page']->Message("This is not an event AI.", 'AI System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This AI does not exist.", 'AI System', 'id=' . $_GET['id']);
            }
        }
    }
}

new jutsusPanel();