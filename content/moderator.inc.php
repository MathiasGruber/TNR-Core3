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

require('./libs/moderatorSystem/moderatorLib.inc.php');

class mod_panel extends moderatorLib {

    private $mod_report_enable;

    public function __construct() {
        $this->getModData();
        if (in_array($this->user[0]['user_rank'], array('Moderator', 'Admin', 'Supermod'), true)) {
            if (isset($_POST['TavernBan_User']) && ($_POST['TavernBan_User'] === "Submit")) {
                $this->give_tavernban();
            } 
            elseif (isset($_GET['act']) && ($_GET['act'] === 'postcomment')) {
                $this->do_post();
            } 
            elseif (isset($_POST['Jump_Village'])) {
                $this->do_jump();
            } 
            elseif (isset($_POST['Warn_User']) && ($_POST['Warn_User'] === "Submit")) {
                $this->give_warning();
            } 
            elseif ((isset($_GET['uid']) && isset($_GET['act']) && ($_GET['act'] === "check_user")) || (isset($_POST['Check_User']) && ($_POST['Check_User'] === "Submit"))) {
                if (isset($_GET['perform']) && ($_GET['perform'] === 'deletepost')) {
                    $this->do_delete_post();
                } else {
                    $this->show_user_sheet();
                }
            } 
            elseif (isset($_GET['act']) && ($_GET['act'] === 'permanentBan')) {
                // $this->show_details();
            } 
            elseif (isset($_GET['act']) && ($_GET['act'] === 'markScammer')) {
                // $this->show_details();
            } 
            elseif (isset($_GET['act']) && ($_GET['act'] === 'checkRyolog')) {
                if( !isset($_POST['SearchUser']) ){
                    $this->searchUsername();
                }
                else{
                    $this->ryoLog();
                }
            } 
            elseif (isset($_GET['act']) && ($_GET['act'] === 'extendBans')) {
                // $this->show_details();
            } 
            elseif (isset($_GET['act']) && ($_GET['act'] === 'tbanlog')) {
                if (isset($_POST['Submit_Untban']) && ($_POST['Submit_Untban'] === "Unban")) {
                    $this->un_tavern_ban();
                } 
                elseif (isset($_POST['Tban_Submit']) && ($_POST['Tban_Submit'] === "Search Username")) {
                    $this->tavern_ban_log();
                } 
                else {
                    $this->tavern_ban_log();
                }
            } 
            elseif (isset($_GET['act']) && ($_GET['act'] === 'reports')) {
                if (isset($_GET['page']) && ($_GET['page'] === 'details')) {
                    (!isset($_POST['Submit'])) ? $this->report_show_details() : $this->update_report_status();
                } 
                else {
                    $this->show_reports("unviewed");
                    $this->show_reports("my");
                    $this->show_reports("in progress");
                    $this->report_main_screen();
                }
            } 
            elseif (isset($_GET['act']) && ($_GET['act'] === 'note')) {
                switch ($_GET['page']) {
                    case("view"): $this->view_note(); break;
                    case("edit"): (!isset($_POST['Submit'])) ? $this->edit_note() 
                        : $this->do_edit_note(); break;
                    case("delete"): (!isset($_POST['Submit'])) ? $this->verify_delete_note() 
                        : $this->do_delete_note(); break;
                    case("new"): (!isset($_POST['Submit'])) ? $this->new_note() 
                        : $this->do_new_note(); break;
                    default: break;
                }
            } 
            elseif (($this->user[0]['user_rank'] === 'Supermod') || ($this->user[0]['user_rank'] === 'Admin')) {
                if (isset($_POST['Hire_Mod']) && ($_POST['Hire_Mod'] === "Submit")) {
                    $this->do_hire();
                } 
                elseif (isset($_POST['Fire_Mod']) && ($_POST['Fire_Mod'] === "Submit")) {
                    $this->do_fire();
                } 
                elseif (isset($_POST['Edit_Order']) && ($_POST['Edit_Order'] === "Save")) {
                    $this->edit_orders();
                } 
                elseif (isset($_POST['Track_Mod']) && ($_POST['Track_Mod'] === "Submit")) {
                    $this->modtrack_stats();
                } 
                elseif (isset($_POST['Check_IP']) && ($_POST['Check_IP'] === "Run Search")) {
                    $this->ipcheck_done();
                } 
                elseif (isset($_POST['Remove_Nindo']) && ($_POST['Remove_Nindo'] === "Submit")) {
                    $this->rnindo_done();
                } 
                elseif (isset($_POST['Remove_Avatar']) && ($_POST['Remove_Avatar'] === "Submit")) {
                    $this->ravatar_done();
                } 
                else {
                    if (isset($_GET['act']) && $_GET['act'] !== 'chat') {
                        switch ($_GET['act']) {
                            case('deleterecord'): (!isset($_POST['Submit'])) ? $this->deleterecord() 
                                : $this->deleterecord_done(); break;
                            case('trackbans'): $this->modtrack_bans(); break;
                            case('trackreports'): $this->modtrack_reports(); break;
                            case('trackwarnings'): $this->modtrack_warnings(); break;
                            default: $GLOBALS['page']->Message("This page doesn't exist.", 'Moderator HQ', 'id=52'); break;
                        }
                    } 
                    else {
                        $this->order_form();
                        $this->fire_form();
                        $this->main_modtrack();
                        $this->main_screen();
                    }
                }
            } 
            else {
                $this->main_screen();
            }
        } 
        else {
            $GLOBALS['page']->Message("You are not a moderator, supermod or administrator.", 'User Privileges', 'id=2');
        }
    }


    
}
$mod = new mod_panel();
?>