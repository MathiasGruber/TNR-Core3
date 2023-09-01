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

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            try {

                if (isset($_GET['act'])) {

                    if(!(self::canTouchNotes())) {
                        throw new Exception('You do not have permission to edit the admin notes!');
                    }

                    switch($_GET['act']) {
                        case('new'): {
                            (!isset($_POST['Submit'])) ? self::new_note() : self::post_new_note();
                        } break;
                        case('view'): {
                            if(!ctype_digit($_GET['nid'])) {
                                throw new Exception('The note ID must be a numeric value!');
                            }
                            self::view_note();
                        } break;
                        case('delete'): {
                            if(!ctype_digit($_GET['nid'])) {
                                throw new Exception('The note ID must be a numeric value!');
                            }
                            (!isset($_POST['Submit'])) ? self::validate_delete() : self::do_delete();
                        } break;
                        case('modify'): {
                            if(!ctype_digit($_GET['nid'])) {
                                throw new Exception('The note ID must be a numeric value!');
                            }
                            (!isset($_POST['Submit'])) ? self::modify_note() : self::do_modify_note();
                        } break;
                        case('clear'): {
                            (!isset($_POST['Submit'])) ? self::validate_clear() : self::clear_notes();
                        } break;
                        default: self::main_page(); break;
                    }

                }
                else {
                    self::main_page();
                }
            }
            catch(Exception $e) {
                $GLOBALS['database']->transaction_rollback($e->getMessage());
                $GLOBALS['page']->Message($e->getMessage(), 'Note System', 'id=' . $_GET['id']);
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        }

        // Main page
        private function main_page() {

            // Set the top options if more than one
            $topOptions = array(
                array(
                    "name" => "New Note",
                    "href" => "?id=" . $_GET['id'] . "&act=new"
                ),
                array(
                    "name" => "Clear Notes",
                    "href" => "?id=" . $_GET['id'] . "&act=clear"
                )
            );

            // Get list of current moderators
            if(!($admins = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users_statistics`.`user_rank`
                FROM `users_statistics`
                    INNER JOIN `users` ON (`users`.`id` = `users_statistics`.`uid`)
                WHERE `users_statistics`.`user_rank` IN ('Moderator', 'Supermod')"))) {
                throw new Exception('There was an error trying to obtain Moderators and Head Mods!');
            }
            elseif($admins === '0 rows') {
                throw new Exception('There are no moderators and head mods in the system!');
            }

            $GLOBALS['template']->assign('admins', $admins);

            // Get Orders / Announcement
            $announ_nindo = ($GLOBALS['page']->user[0]['message'] !== null) ?
                functions::parse_BB($GLOBALS['page']->user[0]['message'], ".")
                : "No orders were given or it doesn't exist!";

            $GLOBALS['template']->assign('announ_nindo', $announ_nindo);

            // Get notes
            if(self::canTouchNotes()) {
                $visible = '`admin_notes`.`visibility` IN ("All"';
                switch ($GLOBALS['page']->user[0]['user_rank']) {
                    case('Supermod'): $visible .= ', "Supermod")'; break;
                    case('Admin'): $visible .= ', "Supermod", "Admin")'; break;
                    default: $visible .= ')'; break;
                }

                if(!($notes = $GLOBALS['database']->fetch_data('SELECT * FROM `admin_notes` WHERE '.$visible.' ORDER BY `admin_notes`.`id` DESC'))) {
                    throw new Exception('There was an error trying to obtain admin notes!');
                }

                tableParser::show_list('notes', 'Admin Notes in System',
                    $notes,
                    array(
                        'title' => "Title",
                        'posted_by' => "Author"
                    ),
                    array(
                        array(
                            "name" => "View",
                            "act" => "view",
                            "nid" => "table.id"
                        ),
                        array(
                            "name" => "Modify",
                            "act" => "modify",
                            "nid" => "table.id"
                        ),
                        array(
                            "name" => "Delete",
                            "act" => "delete",
                            "nid" => "table.id"
                        )
                    ),
                    false, // Send directly to contentLoad
                    false,   // Show previous/next links
                    $topOptions,  // No links at the top to show
                    false,   // Allow sorting on columns
                    false,   // pretty-hide options
                    false, // Top stuff
                    "Here you can post notes to fellow members of the administration."
                );
            }

            // Get latest entries from moderator log
            $min = tableParser::get_page_min();

            if(!($log = $GLOBALS['database']->fetch_data("SELECT * FROM `moderator_log` ORDER BY `moderator_log`.`time` DESC LIMIT ".$min.', 10'))) {
                throw new Exception('There was an error trying to obtain moderator log information!');
            }

            tableParser::show_list('modLog', 'Latest Moderator Actions',
                $log,
                array(
                     'time' => "Time",
                     'action' => "Action",
                     'moderator' => "Moderator",
                     'username' => "Target",
                     'reason' => "Reason",
                     'message' => "Message"
                ),
                false,
                false, // Send directly to contentLoad
                true,   // Show previous/next links
                false,  // No links at the top to show
                false,   // Allow sorting on columns
                false,   // pretty-hide options
                false, // Top stuff
                "These represent the latest log entries in the moderator log"
            );

            // Load the notes template
            $GLOBALS['template']->assign('contentLoad', './panel_moderator/templates/notes/main.tpl');
        }

        // Can touch notes
        private function canTouchNotes(){
            return in_array($GLOBALS['page']->user[0]['user_rank'], array('Admin', 'Supermod'), true);
        }

        // New Note
        private function new_note() {
            tableParser::parse_form('admin_notes', 'New note', array('id', 'posted_by', 'time'));
        }

        private function post_new_note() {
            $data['time'] = time();
            $data['posted_by'] = $GLOBALS['userdata'][0]['username'];

            $GLOBALS['database']->transaction_start();

            if(!(tableParser::insert_data('admin_notes', $data))) {
                throw new Exception('An error occurred when adding the note!');
            }

            $GLOBALS['page']->Message('The note has been added!', 'Note System', 'id=' . $_GET['id']);

            $GLOBALS['database']->transaction_commit();
        }

        // View Note
        private function view_note() {
            if(!($note = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_notes`
                WHERE `admin_notes`.`id` = '" . $_GET['nid'] . "' LIMIT 1"))) {
                throw new Exception('There was an error trying to obtaining the admin note!');
            }
            elseif($note === '0 rows') {
                throw new Exception('An invalid or non-existant note ID was specified!');
            }

            $GLOBALS['template']->assign('result', $note);
            $GLOBALS['template']->assign('contentLoad', './templates/content/moderator/mod_view_note.tpl');
        }

        // Modify Note
        private function modify_note() {
            if(!($data = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_notes`
                WHERE `admin_notes`.`id` = '" . $_GET['nid'] . "' LIMIT 1"))) {
                throw new Exception('There was an error trying to obtain the admin note!');
            }
            elseif($data === '0 rows') {
                throw new Exception('An invalid or non-existant note ID as specified!');
            }

            tableParser::parse_form('admin_notes', 'Update note', array('id', 'time', 'posted_by'), $data);
        }

        private function do_modify_note() {

            $GLOBALS['database']->transaction_start();

            if (!(tableParser::update_data('admin_notes', 'id', $_GET['nid']))) {
                throw new Exception('An error occurred while updating the note!');
            }

            $GLOBALS['page']->Message("The note has been updated", 'Note System', 'id=' . $_GET['id']);

            $GLOBALS['database']->transaction_commit();
        }

        // Delete Note
        private function validate_delete() {
            $GLOBALS['page']->Confirm("Delete this note?", 'Note System', 'Delete now!');
        }

        private function do_delete() {

            $GLOBALS['database']->transaction_start();

            if ($GLOBALS['database']->execute_query("DELETE FROM `admin_notes`
                WHERE `admin_notes`.`id` = '" . $_GET['nid'] . "' LIMIT 1") === false) {
                throw new Exception('There was an error deleting the admin note!');
            }

            $GLOBALS['page']->Message("The admin note has been deleted", 'Note System', 'id=' . $_GET['id']);

            $GLOBALS['database']->transaction_commit();
        }

        // Clear Notes
        private function validate_clear() {
            $GLOBALS['page']->Confirm("Clear All Notes", 'Note System', 'Clear now!');
        }

        private function clear_notes() {
            if ($GLOBALS['database']->execute_query("TRUNCATE TABLE `admin_notes`") === false) {
                throw new Exception('There was an error trying to remove all admin notes!');
            }

            $GLOBALS['page']->Message("All admin / mod notes have been removed", 'Note System', 'id=' . $_GET['id']);
        }

    }

    new notes();