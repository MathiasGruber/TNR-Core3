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

class notes {

    public function __construct() {
        if (!isset($_GET['act'])) {
            $this->main_page();
        } elseif ($_GET['act'] == 'new') {
            if (!isset($_POST['Submit'])) {
                $this->new_note();
            } else {
                $this->post_new_note();
            }
        } elseif ($_GET['act'] == 'view' && is_numeric($_GET['nid'])) {
            $this->view_note();
        } elseif ($_GET['act'] == 'delete' && is_numeric($_GET['nid'])) {
            if (!isset($_POST['Submit'])) {
                $this->validate_delete();
            } else {
                $this->do_delete();
            }
        } elseif ($_GET['act'] == 'modify' && is_numeric($_GET['nid'])) {
            if (!isset($_POST['Submit'])) {
                $this->modify_note();
            } else {
                $this->do_modify_note();
            }
        } elseif ($_GET['act'] == 'clear') {
            if (!isset($_POST['Submit'])) {
                $this->validate_clear();
            } else {
                $this->clear_notes();
            }
        }
    }

    // Main page
    private function main_page() {

        // Get the table parse library
        $dir = $GLOBALS['template']->getTemplateVars('absPath');

        // Use the table parser library to show notes in system
        $notes = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_notes` ORDER BY `time` DESC");
        tableParser::show_list(
                'notes', 'Admin Notes in System', $notes, 
                array(
                    'title' => "Title",
                    'posted_by' => "Author"
                ), array(
            array("name" => "View", "act" => "view", "nid" => "table.id"),
            array("name" => "Modify", "act" => "modify", "nid" => "table.id"),
            array("name" => "Delete", "act" => "delete", "nid" => "table.id")
                ), false, false
        );

        // Use the table parser library to get admin edits
        $min = tableParser::get_page_min();
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_edits` ORDER BY `time` DESC LIMIT " . $min . ",10");
        tableParser::show_list(
                'log', 'Latest Admin Edits', $edits, 
                array(
                    'aid' => "Admin Name",
                    'time' => "Time",
                    'IP' => "IP Used",
                    'uid' => "User",
                    'changes' => "Changes"
                ), false, false, true
        );

        $admins = $GLOBALS['database']->fetch_data("SELECT `users`.`username` 
            FROM `users_statistics`
                INNER JOIN `users` ON (`users`.`id` = `users_statistics`.`uid`) 
            WHERE `users_statistics`.`user_rank` IN('Admin', 'ContentAdmin')");
        $GLOBALS['template']->assign('admins', $admins);

        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/notes/main.tpl');
    }

    // New Note
    private function new_note() {
        tableParser::parse_form('admin_notes', 'New note', array('id', 'posted_by', 'time'));
    }

    private function post_new_note() {
        $data['time'] = time();
        $data['posted_by'] = $GLOBALS['userdata'][0]['username'];
        if (tableParser::insert_data('admin_notes', $data)) {
            $GLOBALS['page']->Message("The note has been added", 'Note System', 'id=' . $_GET['id']);
        } else {
            $GLOBALS['page']->Message("An error occured when adding the note", 'Note System', 'id=' . $_GET['id']);
        }
    }

    // View Note
    private function view_note() {
        $note = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_notes` WHERE `id` = '" . $_GET['nid'] . "' LIMIT 1");
        if ($note != '0 rows') {
            // Use same template as used in modeator system
            $GLOBALS['template']->assign('result', $note);
            $GLOBALS['template']->assign('contentLoad', './templates/content/moderator/mod_view_note.tpl');
        } else {
            $GLOBALS['page']->Message("An invalid note ID was specified", 'No note specified', 'id=' . $_GET['id']);
        }
    }

    // Modify Note
    private function modify_note() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_notes` WHERE `id` = '" . $_GET['nid'] . "'");
        if ($data != '0 rows') {
            tableParser::parse_form('admin_notes', 'Update note', array('id', 'time', 'posted_by'), $data);
        } else {
            $GLOBALS['page']->Message("This note does not exist", 'Note System', 'id=' . $_GET['id']);
        }
    }

    private function do_modify_note() {
        if (tableParser::update_data('admin_notes', 'id', $_GET['nid'])) {
            $GLOBALS['page']->Message("The note has been updated", 'Note System', 'id=' . $_GET['id']);
        } else {
            $GLOBALS['page']->Message("An error occured while updating the note", 'Note System', 'id=' . $_GET['id']);
        }
    }

    // Delete Note
    private function validate_delete() {
        $GLOBALS['page']->Confirm("Delete this note?", 'Note System', 'Delete now!');
    }

    private function do_delete() {
        if ($GLOBALS['database']->execute_query("DELETE FROM `admin_notes` WHERE `id` = '" . $_GET['nid'] . "' LIMIT 1")) {
            $GLOBALS['page']->Message("The admin note has been deleted", 'Note System', 'id=' . $_GET['id']);
        }
    }

    // Clear Notes
    private function validate_clear() {
        $GLOBALS['page']->Confirm("Clear All Notes", 'Note System', 'Clear now!');
    }

    private function clear_notes() {
        if ($GLOBALS['database']->execute_query("TRUNCATE TABLE `admin_notes`")) {
            $GLOBALS['page']->Message("All admin / mod notes have been removed", 'Note System', 'id=' . $_GET['id']);
        }
    }

}

new notes();