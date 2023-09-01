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

class changeLog {

    public function __construct() {
        if (!isset($_GET['act'])) {
            $this->main_screen();
        } elseif ($_GET['act'] == 'new') {
            if (!isset($_POST['Submit'])) {
                $this->new_form();
            } else {
                $this->insert_new();
            }
        } elseif ($_GET['act'] == 'modify' && is_numeric($_GET['nid'])) {
            if (!isset($_POST['Submit'])) {
                $this->update_form();
            } else {
                $this->update_news();
            }
        } 
    }

    //		Main screen:
    protected function main_screen() {

        // Show form
        $min =  tableParser::get_page_min();
        $changes = $GLOBALS['database']->fetch_data("SELECT * FROM `log_changeLog` ORDER BY `time` DESC LIMIT ".$min.", 10");
        
        tableParser::show_list(
                'news', 'Changelog', $changes, array(
            'id' => "ChangeID",
            'author' => "Author",
            'time' => "Time",
            'info' => "Info"
                ), array(
            array("name" => "Modify", "act" => "modify", "nid" => "table.id"),
            array("name" => "Remove", "act" => "delete", "nid" => "table.id")
                ), 
           true, // Send directly to contentLoad
           true, 
            array(
                array("name" => "New Entry", "href" => "?id=" . $_GET["id"] . "&act=new")
            )
        );

        // Set a return link for the page
        $GLOBALS['template']->assign('returnLink', true);
    }

    // New log entry:
    protected function new_form() {
        tableParser::parse_form('log_changeLog', 'New Log Entry', array('id', 'time','author'));
    }

    protected function insert_new() {
        $data['time'] = time();
        $data['author'] = $GLOBALS['userdata'][0]['username'];
        if (tableParser::insert_data('log_changeLog', $data)) {
            $GLOBALS['page']->Message("The log entry has been added", 'Changelog System', 'id=' . $_GET['id']);
        } else {
            $GLOBALS['page']->Message("An error occured when adding the log entry", 'Changelog System', 'id=' . $_GET['id']);
        }
    }

    // Update log entry:
    protected function update_form() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `log_changeLog` WHERE `id` = '" . $_GET['nid'] . "'");
        if ($data != '0 rows') {
            tableParser::parse_form('log_changeLog', 'Update log entry', array('id', 'time'), $data);
        } else {
            $GLOBALS['page']->Message("This log entry does not exist", 'Changelog System', 'id=' . $_GET['id']);
        }
    }

    protected function update_news() {
        if (tableParser::update_data('log_changeLog', 'id', $_GET['nid'])) {
            $GLOBALS['page']->Message("The log entry has been updated", 'Changelog System', 'id=' . $_GET['id']);
        } else {

            $GLOBALS['page']->Message("An error occured while updating the log entry ", 'Changelog System', 'id=' . $_GET['id']);
        }
    }
}

new changeLog();