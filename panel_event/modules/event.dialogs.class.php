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
            $this->dialog_screen();
        } elseif ($_GET['act'] == 'new_dialog') {
            if (!isset($_POST['Submit'])) {
                $this->dialog_new_form();
            } else {
                $this->dialog_upload_new();
            }
        } elseif ($_GET['act'] == 'edit_dialog') {
            if (!isset($_POST['Submit'])) {
                $this->dialog_edit_form();
            } else {
                $this->dialog_do_edit();
            }
        } elseif ($_GET['act'] == 'delete_dialog') {
            if (!isset($_POST['Submit'])) {
                $this->dialog_confirm_delete();
            } else {
                $this->dialog_do_delete();
            }
        }
    }

    // dialog creation
    private function dialog_screen() {

        // Show form
        $min = tableParser::get_page_min();

        if(isset($_POST['search']) && $_POST['search'])
        {
            if($_POST['search'] != 'did')
                $where = "WHERE `{$_POST['search']}` LIKE '%{$_POST[$_POST['search']]}%'";
            else
                $where = "WHERE `{$_POST['search']}` = {$_POST[$_POST['search']]}";
        }
        else
            $where = '';

        $query = "SELECT * FROM `dialogs` {$where} ORDER BY `did` DESC LIMIT {$min},25";

        $dialogs = $GLOBALS['database']->fetch_data($query);
        tableParser::show_list(
                'dialog', 
                'Dialog admin', 
                $dialogs, 
                array(
                    'did' => 'did',
                    'notes' => 'notes'
                ), 
                array(
                    array("name" => "Modify", "act" => "edit_dialog", "did" => "table.did"),
                    array("name" => "Delete", "act" => "delete_dialog", "did" => "table.did")
                ), 
                true, // Send directly to contentLoad
                true, 
                array(
                    array("name" => "New dialog", "href" => "?id=" . $_GET["id"] . "&act=new_dialog")
                ),
                true,
                false,
                array(
                    array(
                        'infoText'=>'Did',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'did',
                        'postIdentifier'=>'search',
                        'inputName'=>'did'
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


    private function dialog_confirm_delete() {
        if (isset($_GET['did'])) {
            $GLOBALS['page']->Confirm("Delete this dialog?", 'Dialog System', 'Delete now!');
        } else {
            $GLOBALS['page']->Message("No valid dialog ID was specified.", 'Dialogs System', 'id=' . $_GET['id']);
        }
    }

    private function dialog_do_delete() {
        if (isset($_GET['did'])) {
            $query = "SELECT `did`,`notes` FROM `dialogs` WHERE `did` = '" . $_GET['did'] . "' LIMIT 1";
            $data = $GLOBALS['database']->fetch_data($query);
            if ($data != '0 rows') {
                
                    $GLOBALS['database']->execute_query("DELETE FROM `dialogs` WHERE `did` = '" . $data[0]['did'] . "' LIMIT 1");

                    $GLOBALS['page']->setLogEntry("Dialog Change", 'dialog '. $data[0]['did'] .' Deleted', $_GET['did'] );

            } else {
                $GLOBALS['page']->Message("Dialog could not be found.", 'Dialogs System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("No valid dialog ID was specified.", 'Dialogs System', 'id=' . $_GET['id']);
        }
    }

    private function dialog_edit_form() {
        if (isset($_GET['did'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `dialogs` WHERE `did` = '" . $_GET['did'] . "' LIMIT 1");
            if ($data != '0 rows') {
                tableParser::parse_form('dialogs', 'Edit dialog', array('dialog'), $data, null, "", false);
            } else {
                $GLOBALS['page']->Message("This dialog does not exist.", 'Dialogs System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid dialog ID specified.", 'Dialogs System', 'id=' . $_GET['id']);
        }
    }

    private function dialog_do_edit() {
        if (isset($_GET['did'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `dialogs` WHERE `did` = '" . $_GET['did'] . "'  LIMIT 1");
            if ($data != '0 rows') {
                $changed = tableParser::check_data('dialogs', 'did', $_GET['did'], array());
                if (tableParser::update_data('dialogs', 'did', $_GET['did'])) {

                    $GLOBALS['page']->Message("The dialog has been updated.", 'Dialogs System', 'id=' . $_GET['id']);

                    $GLOBALS['page']->setLogEntry("Dialog Change", "Dialog did:" . $_POST['did'] . " Changed:<br>" . $changed , $_GET['did'] );

                } else {
                    $GLOBALS['page']->Message("An error occured while updating the dialog.", 'Dialogs System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This dialog does not exist.", 'Dialogs System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid dialog ID specified.", 'Dialogs System', 'id=' . $_GET['id']);
        }
    }

    private function dialog_new_form() {
        tableParser::parse_form('dialogs', 'Insert new dialog', array('dialog'));
    }

    private function dialog_upload_new() {
        if (tableParser::insert_data('dialogs')) {
            $GLOBALS['page']->Message("The dialog has been inserted.", 'Dialogs System', 'id=' . $_GET['id']);
            $GLOBALS['page']->setLogEntry("Dialog New", 'dialog did: <i>' . $_POST['did'] . '</i> Created'
                                                             .'<br>did: '.$_POST['did'],"");

        } else {
            $GLOBALS['page']->Message("An error occured while inserting the dialog.", 'Dialogs System', 'id=' . $_GET['id']);
        }
    }


}

new blueMessage();