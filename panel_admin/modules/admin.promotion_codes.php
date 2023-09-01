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

class news {

    function __construct() {
        if (!isset($_GET['act'])) {
            $this->main_screen();
        } elseif ($_GET['act'] == 'new') {
            if (!isset($_POST['Submit'])) {
                $this->new_form();
            } else {
                $this->insert_new();
            }
        } elseif ($_GET['act'] == 'delete' && is_numeric($_GET['nid'])) {
            if (!isset($_POST['Submit'])) {
                $this->verify_delete();
            } else {
                $this->do_delete();
            }
        } elseif ($_GET['act'] == 'clear') {
            if (!isset($_POST['Submit'])) {
                $this->verify_clear();
            } else {
                $this->do_clear();
            }
        }
    }

    //		Main screen:
    function main_screen() {

        // Show form
        $min =  tableParser::get_page_min();
        $news = $GLOBALS['database']->fetch_data("SELECT * FROM `promotionCodes` ORDER BY `time` DESC LIMIT ".$min.",10");
        tableParser::show_list(
                'promotionCodes', 
                'Promotion Codes', 
                $news, 
                array(
                    'code' => "Code",
                    'created_by' => "Admin",
                    'collector' => "Collector",
                    'note' => "Note",
                    'inputTimes' => "#inputs"
                ), 
                array(
                    array("name" => "Remove", "act" => "delete", "nid" => "table.id")
                ), true, // Send directly to contentLoad
                true, 
                array(
                    array("name" => "Create New Codes", "href" => "?id=" . $_GET["id"] . "&act=new"),
                    array("name" => "Clear Unclaimed Codes", "href" => "?id=" . $_GET["id"] . "&act=clear")
                )
        );

        // Set a return link for the page
        $GLOBALS['template']->assign('returnLink', true);
    }

    // Generate Code
    private function generateCode() {
        $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
        $pass = '';
        for ($i = 0; $i < 5; $i++) {
            $pass .= $letters[random_int(0, count($letters)-1)];
        }
        return $pass;
    }

    //		New news item:
    private function new_form() {
        $GLOBALS['template']->assign('contentLoad', 'panel_admin/templates/admin_promotionCodes/newCodesForm.tpl');
    }

    private function insert_new() {
        if (strlen($_POST["notes"]) > 0) {
            if (ctype_digit($_POST["codes"]) && $_POST["codes"] > 0 && $_POST["notes"] < 11) {
                $n = 0;
                $codes = "";
                while ($n < $_POST["codes"]) {
                    $code = $this->generateCode();
                    $codes .= $code.", ";
                    $GLOBALS['database']->execute_query("
                        INSERT INTO `promotionCodes` 
                        (`time` ,`code` ,`created_by` ,`note`)VALUES 
                        (UNIX_TIMESTAMP(), '" . $code . "', '" . $GLOBALS['userdata'][0]['username'] . "',  '" . $_POST["notes"] . "');");
                
                    $n++;
                }
                
                $GLOBALS['page']->Message("Following codes have been generated; ".$codes, 'Promotion Code System', 'id=' . $_GET['id']);
            } else {
                $GLOBALS['page']->Message("You can not create more than 10 codes at a time.", 'Promotion Code System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("You have to specify a purpose of the promotion codes.", 'Promotion Code System', 'id=' . $_GET['id']);
        }
    }

    //		Delete news item
    function verify_delete() {
        $GLOBALS['page']->Confirm("Delete this promotion code?", 'Promotion Code System', 'Delete now!');
    }

    function do_delete() {
        if ($GLOBALS['database']->execute_query("DELETE FROM `promotionCodes` WHERE `id` = '" . $_GET['nid'] . "' LIMIT 1")) {
            $GLOBALS['page']->Message("The promotion code has been deleted", 'News System', 'id=' . $_GET['id']);        
        }
    }

    // Clear news
    function verify_clear() {
        $GLOBALS['page']->Confirm("Clear All Unused Codes", 'Promotion Code System', 'Clear now!');
    }

    function do_clear() {
        if ($GLOBALS['database']->execute_query("DELETE FROM `promotionCodes` WHERE `collector` = 'Unclaimed'")) {
            $GLOBALS['page']->Message("The unclaimed promotion codes have been deleted", 'Promotion Code System', 'id=' . $_GET['id']);        
        }
    }

}

new news();