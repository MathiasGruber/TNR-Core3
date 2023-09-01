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

class achievements {

    function __construct() {
        if (!isset($_GET['act'])) {
            $this->main_page();
        } elseif ($_GET['act'] == "new") {
            if (!isset($_POST['Submit'])) {
                $this->achievement_form();
            } else {
                $this->achievement_insert();
            }
        } elseif ($_GET['act'] == "current") {
            $this->currentList();
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
        } elseif ($_GET['act'] == 'picture') {
            if (!isset($_POST['Submit'])) {
                $this->change_avatar();
            } else {
                $this->do_avatar_change();
            }
        } elseif ($_GET['act'] == 'search') {
            if (!isset($_POST['Submit'])) {
                $this->search_form();
            } else {
                $this->execute_search();
            }
        } elseif ($_GET['act'] == 'deleteFBachievement') {
            if (!isset($_POST['Submit'])) {
                $this->verify_delete_fb();
            } else {
                $this->removeFacebookAchievement();
            }
        }
    }

    private function main_page() {
        
        // Get facebook Achievements
        require('../global_libs/General/facebook.class.php');
        $GLOBALS['facebook'] = new FBinteract; 
        $GLOBALS['facebook']->fbConnect(); 
        $fbAchievements = $GLOBALS['facebook']->getAchievements();
        $GLOBALS['template']->assign('fbAchievements', $fbAchievements['data'] );
        
        // Get the total score
        $totalScore = $GLOBALS['database']->fetch_data("SELECT SUM(`score`) as `total` FROM `tasksAndQuests` WHERE `facebookAchievment` = 'yes' OR `tnrAchievment` = 'yes'");
        if( $totalScore == "0 rows" ){
            $totalScore[0]['total'] = 0;
        }
        $GLOBALS['template']->assign('totalScore', $totalScore[0]['total'] );
        
        // Get the table parse library
        $min = tableParser::get_page_min();
        $Achievements = $GLOBALS['database']->fetch_data("SELECT * FROM `tasksAndQuests` ORDER BY `id` ASC");
        tableParser::show_list(
                'tasksAndQuests', 'Current Tasks & Quests', $Achievements, array(
            'id' => "ID",
            'name' => "Name",
            'type' => "Type",
            'rewards' => "Rewards"
                ), array(
            array("name" => "Picture", "act" => "picture", "oid" => "table.id"),
            array("name" => "Edit", "act" => "edit", "oid" => "table.id"),
            array("name" => "Delete", "act" => "delete", "oid" => "table.id")
                ), false, true
        );

        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/admin_tasks/main.tpl');

        tableParser::parse_form(
                'tasksAndQuests', 'New Task/Quest', array('id', 'picture', 'implemented'), null, "NewForm", "?id=" . $_GET['id'] . "&act=new"
        );
    }
    
    private function removeFacebookAchievement(){
        if (isset($_GET['oid']) ) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `tasksAndQuests` WHERE `name` = '" . $_GET['oid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                // Facebook Stuff
                require('../global_libs/General/facebook.class.php');
                $GLOBALS['facebook'] = new FBinteract; 
                $GLOBALS['facebook']->fbConnect(); 
                $GLOBALS['facebook']->deleteAchievement( $data[0]['id'] );
                $GLOBALS['page']->Message("Achievement de-registered from facebook", 'Task/Quest System', 'id=' . $_GET['id']);
            } else {
                $GLOBALS['page']->Message("Entry could not be found.", 'Task/Quest System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid entry ID: ".$_GET['oid'], 'Task/Quest System', 'id=' . $_GET['id']);
        }
    }
    
    private function verify_delete_fb() {
        if (isset($_GET['oid']) ) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `tasksAndQuests` WHERE `name` = '" . $_GET['oid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                $GLOBALS['page']->Confirm("Remove this entry from facebook?", 'Task/Quest System', 'Delete now!');
            } else {
                $GLOBALS['page']->Message("Entry could not be found.", 'Task/Quest System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid entry ID: ".$_GET['oid'], 'Task/Quest System', 'id=' . $_GET['id']);
        }
    }

    private function achievement_form() {
        tableParser::parse_form('tasksAndQuests', 'New Task/Quest', array('id', 'picture', 'implemented'));
    }

    private function achievement_insert() {
        if (tableParser::insert_data('tasksAndQuests')) {
            
            // Delete Cache
            cachefunctions::deleteTasksQuestsMissions();
            cachefunctions::deleteEvents();
                    
            $GLOBALS['page']->Message("The entry has been added to the table. Cache has been cleared.", 'Task&Quest System', 'id=' . $_GET['id']);
            $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
            (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
            (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Task&Quest Change','','New entry named <i>: " . $_POST['name'] . "</i>')");
        } else {
            $GLOBALS['page']->Message("An error occured while adding the entry to the table.", 'Task&Quest System', 'id=' . $_GET['id']);
        }
    }

    private function currentList() {
        $fbAchievements = $GLOBALS['database']->fetch_data("SELECT * FROM `tasksAndQuests` ORDER BY `id` ASC");
        tableParser::show_list(
                'tasksAndQuests', 'Task&Quest Admin', $fbAchievements, array(
            'id' => "ID",
            'name' => "Name",
            'type' => "Type"
                ), array(
            array("name" => "Picture", "act" => "picture", "oid" => "table.id"),
            array("name" => "Edit", "act" => "edit", "oid" => "table.id"),
            array("name" => "Delete", "act" => "delete", "oid" => "table.id")
                ), true, // Send directly to contentLoad
                true, false
        );
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id']);
    }

    private function edit_form() {
        if (isset($_GET['oid']) && is_numeric($_GET['oid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `tasksAndQuests` WHERE `id` = '" . $_GET['oid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                tableParser::parse_form('tasksAndQuests', 'Edit Entry', array('id', 'picture'), $data);
            } else {
                $GLOBALS['page']->Message("Entry could not be found.", 'Task&Quest System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid entry ID.", 'Task&Quest System', 'id=' . $_GET['id']);
        }
    }

    private function do_edit() {
        if (isset($_GET['oid']) && is_numeric($_GET['oid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `tasksAndQuests` WHERE `id` = '" . $_GET['oid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                $changed = tableParser::check_data('tasksAndQuests', 'id', $_GET['oid'], array());
                if (tableParser::update_data('tasksAndQuests', 'id', $_GET['oid'])) {
                    
                    $fbChanges = "";
                    if( $data[0]['picture'] !== "" ){
                        // Facebook Stuff
                        require('../global_libs/General/facebook.class.php');
                        $GLOBALS['facebook'] = new FBinteract; 
                        $GLOBALS['facebook']->fbConnect(); 
                        
                        // Check if already exists, if yes, delete
                        $facebookData = $GLOBALS['facebook']->getAchievement( $_POST['name'] );
                        if( $data[0]['facebookAchievment'] == "yes" && $_POST['facebookAchievment'] == "no" ){
                            $fbChanges .= "Facebook achievement switched from 'yes' to 'no', so fb achievement was deleted. ";
                            if( $facebookData ){
                                $GLOBALS['facebook']->deleteAchievement( $_GET['oid'] );
                            }
                        }
                        elseif( $_POST['facebookAchievment'] == "yes" ){
                            if( !$facebookData ){
                                $fbChanges .= "No facebook entry for this achievement found, so one was registered.";
                                $GLOBALS['facebook']->registerAchievement( $_GET['oid'] );
                            }
                            else{
                                $fbChanges .= "A facebook entry for this achievement was found, so a new one was not created.";
                            }
                        }
                    }
                    
                    
                    // Delete Cache
                    cachefunctions::deleteTasksQuestsMissions();
                    cachefunctions::deleteMissionsAndCrimes();
                    cachefunctions::deleteEvents();
                    cachefunctions::deleteTasksQuestsMission($_GET['oid']);
                    
                    // Message to user
                    $GLOBALS['page']->Message("The entry has been updated. Cache has been cleared. ". $fbChanges, 'Task&Quest System', 'id=' . $_GET['id']);
 
                    // Log Changes                    
                    $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                    (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                    (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Task/Quest Change','" . $_GET['oid'] . "','Entry ID:" . $_GET['oid'] . " Changed:<br>" . $changed . "')");
                } else {
                    $GLOBALS['page']->Message("An error occured while updating the entry. Possibly nothing was changed.", 'Task&Quest System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("Entry could not be found.", 'Task&Quest System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid entry ID.", 'Task&Quest System', 'id=' . $_GET['id']);
        }
    }

    private function verify_delete() {
        if (isset($_GET['oid']) && is_numeric($_GET['oid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `tasksAndQuests` WHERE `id` = '" . $_GET['oid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                $GLOBALS['page']->Confirm("Delete this Entry?", 'Task/Quest System', 'Delete now!');
            } else {
                $GLOBALS['page']->Message("Entry could not be found.", 'Task/Quest System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid entry ID.", 'Task/Quest System', 'id=' . $_GET['id']);
        }
    }

    private function do_delete() {
        if (isset($_GET['oid']) && is_numeric($_GET['oid'])) {

            $GLOBALS['database']->execute_query("DELETE FROM `tasksAndQuests` WHERE `id` = '" . $_GET['oid'] . "' LIMIT 1");
            
            // Delete all caches
            cachefunctions::deleteTasksQuestsMissions();
            cachefunctions::deleteEvents();
            cachefunctions::deleteTasksQuestsMission($_GET['oid']);

            $GLOBALS['page']->Message("The entry was removed.", 'Task/Quest System', 'id=' . $_GET['id']);

            $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
            (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
            (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Task/Quest Change','','Entry ID: <i>" . $_GET['oid'] . "</i> was deleted')");
        } else {
            $GLOBALS['page']->Message("Invalid entry ID.", 'Task/Quest System', 'id=' . $_GET['id']);
        }
    }

    // Achievement picture form
    function change_avatar() {
        
        // Get the signature
        $image = functions::getUserImage('/facebook/Achievements/', $_GET['oid']);

        // Get the fileuploadlibrary
        require('../global_libs/General/fileUploads.php');
        fileUploader::uploadForm(array(
            "maxsize" => "100kb",
            "subTitle" => "Change Achievement Picture",
            "image" => $image,
            "description" => "Change the picture of this entry",
            "dimX" => 200,
            "dimY" => 200
        ));

        // Return Link
        $GLOBALS['template']->assign("returnLink", true);

    }

    // Do change the achievement picture
    function do_avatar_change() {
        
        // Get the fileuploadlibrary
        require('../global_libs/General/fileUploads.php');
        $upload = fileUploader::doUpload(array(
            "maxsize" => 102400,
            "destination" => 'facebook/Achievements/',
            "filename" => $_GET['oid'],
            "dimX" => 200,
            "dimY" => 200
        ));

        // Message to user
        if( $upload == true ){
            $GLOBALS['page']->Message('You have successfully uploaded the task image.', 'Task System', 'id=' . $_GET['id'] . '');
        }
    }

    // Search AI
    private function search_form() {
        $GLOBALS['template']->assign('contentLoad', 'panel_admin/templates/admin_tasks/search.tpl');
    }

    private function execute_search() {
        
        // Query
        $preset = 0;
        $query = "SELECT * FROM `tasksAndQuests` ";
        if ($_POST['name'] != '') {
            $query .= "WHERE `name` LIKE '%" . $_POST['name'] . "%'";
            $preset = 1;
        }
        if ($_POST['type'] != 'any') {
            if ($preset == 1) {
                $query .= " AND ";
            } else {
                $query .= "WHERE";
            }
            switch($_POST['type']  ){
                case "task": $query .= "`type` = '" . $_POST['type'] . "'"; break;
                case "quest": $query .= "`type` = '" . $_POST['type'] . "'"; break;
                case "achievement": $query .= "(`tnrAchievment` = 'yes' || `facebookAchievment` = 'yes')"; break;
            }
            
        }
        
        $query .= " ORDER BY `name` ASC";

        $min = tableParser::get_page_min();
        $fbAchievements = $GLOBALS['database']->fetch_data( $query );
        tableParser::show_list(
                'tasksAndQuests', 'Task&Quest Admin', $fbAchievements, array(
            'id' => "ID",
            'name' => "Name",
            'type' => "Type",
            'hook_point' => "Hook Point",
                ), array(
            array("name" => "Picture", "act" => "picture", "oid" => "table.id"),
            array("name" => "Edit", "act" => "edit", "oid" => "table.id"),
            array("name" => "Delete", "act" => "delete", "oid" => "table.id")
                ), true, // Send directly to contentLoad
                true, false
        );
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id']);
        
    }

}

new achievements();