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

require_once(Data::$absSvrPath.'/libs/home/home_helper.php');

// Class definition
class userpref {

    // Constructor
    public function __construct() {

        // try-catch
        try{

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Get user data for every page
            $this->getUsrData();

            // Check if handling a submit action
            if (!isset($_POST['Submit'])) {

                // Show the main menu
                $this->main_screen();

                // Check if we're taking any action
                if (isset($_GET['act'])) {

                    // Figure out what the user wants to do
                    if ($_GET['act'] == 'password') {

                        // Password change
                        $this->change_password();

                    } elseif ($_GET['act'] == 'avatar') {

                        // Change avatar
                        if ($this->user[0]['rank_id'] >= 2) {
                            $this->change_avatar();
                        } else {
                            throw new Exception("You must be at least a genin to upload an avatar");
                        }
                    } elseif ($_GET['act'] == 'delete') {
                        if ($this->user[0]['deletion_timer'] == 0) {
                            $this->delete_form();
                        } elseif ($this->user[0]['deletion_timer'] > 0 && $this->user[0]['deletion_timer'] + 604800 > $GLOBALS['user']->load_time) {
                            $this->undelete_form();
                        } else {
                            $this->account_deletion();
                        }
                    } elseif ($_GET['act'] == 'main') {
                        $this->preferences();
                    } elseif ($_GET['act'] == 'nindo') {
                        $this->nindo_form();
                    } elseif ($_GET['act'] == 'village') {
                        $this->confirm_leave();
                    } elseif ($_GET['act'] == 'layout') {
                        $this->layout_form();
                    } elseif ($_GET['act'] == 'layout_settings') {
                        $this->layout_settings_form();
                    } elseif ($_GET['act'] == 'popup') {
                        $this->popup_form();
                    } elseif ($_GET['act'] == 'color') {
                        $this->color_form();
                    } elseif ($_GET['act'] == 'nameChange') {
                        $this->namechange_form();
                    } elseif ($_GET['act'] == 'blacklist') {
                        $this->blacklist_screen();
                    } elseif ($_GET['act'] == 'student' && $this->user[0]['rank_id'] >= 4) {
                        $this->student_main();
                    } elseif ($_GET['act'] == 'studentadd' && $this->user[0]['rank_id'] >= 4) {
                        $this->student_add();
                    } elseif ($_GET['act'] == 'removeStudent' && $this->user[0]['rank_id'] >= 4) {
                        $this->remove_student();
                    } elseif ($_GET['act'] == 'specialsig') {
                        $this->specialsig();
                    } elseif ($_GET['act'] == 'reset') {
                        if ($this->user[0]['reset_timer'] == 0) {
                            $this->reset_form();
                        } elseif ($this->user[0]['reset_timer'] > 0 && $this->user[0]['reset_timer'] > $GLOBALS['user']->load_time) {
                            $this->unreset_form();
                        } else {
                            $this->user_reset_account();
                        }
                    } elseif ($_GET['act'] == 'fight_settings') {
                        $this->fight_settings_form();
                    } elseif ($_GET['act'] == 'layout_settings') {
                        $this->layout_settings_form();
                    } elseif ($_GET['act'] == 'key_bindings') {
                        $this->key_bindings_form();
                    }
                }
            } elseif ($_POST['Submit'] == 'Send Request') {
                $this->do_namechange();
            } elseif ($_POST['Submit'] == 'Change Now') {
                $this->do_change_password();
            } elseif ($_POST['Submit'] == 'Upload') {
                $this->do_avatar_change();
            } elseif ($_POST['Submit'] == 'Delete') {
                $this->set_delete_flag();
            } elseif ($_POST['Submit'] == 'Cancel') {
                $this->unset_delete_flag();
            } elseif ($_POST['Submit'] == 'Reset') {
                $this->set_reset_flag();
            } elseif ($_POST['Submit'] == 'Cancel Reset') {
                $this->unset_reset_flag();
            } elseif ($_POST['Submit'] == 'Submit') {
                $this->change_settings();
            } elseif ($_POST['Submit'] == 'Save') {
                $this->alter_nindo();
            } elseif ($_POST['Submit'] == 'Leave Now') {
                $this->leave_village();
            } elseif ($_POST['Submit'] == 'Change layout') {
                $this->alter_layout();
            } elseif ($_POST['Submit'] == 'Change Combat Settings') {
                $this->fight_settings_update();
            } elseif ($_POST['Submit'] == 'Change layout settings') {
                $this->alter_layout_settings();
            } elseif ($_POST['Submit'] == 'Change popup') {
                $this->alter_popup();
            } elseif ($_POST['Submit'] == 'Change color') {
                $this->alter_color();
            } elseif ($_POST['Submit'] == 'Remove selected') {
                $this->list_remove_user();
            } elseif ($_POST['Submit'] == 'Add user') {
                $this->list_add_user();
            } elseif ($_POST['Submit'] == 'Save setting') {
                $this->list_save_setting();
            } elseif ($_POST['Submit'] == 'Add student' && $this->user[0]['rank_id'] >= 4) {
                $this->student_do_add();
            } elseif($_POST['Submit'] == 'Change Layout Settings') {
                $this->layout_settings_update();
            } elseif($_POST['Submit'] == 'Reset Fight Defaults') {
                $this->fight_settings_reset();
            } elseif($_POST['Submit'] == 'Reset Layout Defaults') {
                $this->layout_settings_reset();
            } elseif($_POST['Submit'] == 'Change Keybinding Settings') {
                $this->key_binding_settings_update();
            } elseif($_POST['Submit'] == 'Reset Keybinding Defaults') {
                $this->key_binding_settings_reset();
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }


        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Preferences', 'id='.$_GET['id'],'Return');
        }
    }

    // Collect required userdata
    private function getUsrData() {
        $this->user = $GLOBALS['database']->fetch_data("
                    SELECT `users`.`id`, `enable_heal`, `enable_marriage`, `show_level_up_button`, `username`, `user_rank`,
                           `level`, `rank`, `rank_id`, `clan`, `bloodline`,
                           `salted_password`, `pm_block`, `pm_by_email`, `village`, `sensei`, `anbu`,
                           `lock`, `deletion_timer`, `reset_timer`, `dynamic_signature`,
                           `status`, `student_1`, `student_2`, `student_3`,
                           `battles_won`, `battles_lost`, `battles_fled`, `battles_draws`, `join_date`, `chat_autoupdate`,
                           `silence_spar`, `collapse_home`, `QuestingMode`, `quest_widget`, `turn_log_length`, `travel_default_redirect`,
						   `layout_portrait_location`, `layout_portrait_index`,
						   `layout_details_location`, `layout_details_index`,
						   `layout_travel_location`, `layout_travel_index`, `layout_travel_mobile`,
						   `layout_notifications_location`, `layout_notifications_index`,
						   `layout_quests_location`, `layout_quests_index`,
                           `layout_menu_location`, `layout_menu_index`,
                           `layout_quick_links_location`, `layout_quick_links_index`,
                           `layout_quick_links_style`,
                           `layout_quick_links`,
                           `layout_quick_mobile`, `layout_mobile_quick_links`,
                           `key_bindings_status`, `key_bindings`,
                           `layout_font`, `layout_colors`

                    FROM `users`,`users_timer`, `users_missions`,`users_preferences`, `users_statistics`
                    WHERE `id` = '" . $_SESSION['uid'] . "' AND
                          `users`.`id` = `users_timer`.`userid` AND
                          `users`.`id` = `users_missions`.`userid` AND
                          `users`.`id` = `users_statistics`.`uid` AND
                          `users`.`id` = `users_preferences`.`uid`");
    }

    //	Main screen:
    public function main_screen() {

        $menu = array(
            array("name" => "Black / Whitelist", "href" => "?id=" . $_GET['id'] . "&act=blacklist"),
            array("name" => "Change Avatar", "href" => "?id=" . $_GET['id'] . "&act=avatar"),
            array("name" => "Change Color", "href" => "?id=" . $_GET['id'] . "&act=color"),
            array("name" => "Change Username", "href" => "?id=" . $_GET['id'] . "&act=nameChange"),
            array("name" => "Delete Account", "href" => "?id=" . $_GET['id'] . "&act=delete"),
            array("name" => "Leave Village", "href" => "?id=" . $_GET['id'] . "&act=village"),
            array("name" => "Nindo", "href" => "?id=" . $_GET['id'] . "&act=nindo"),
            array("name" => "Preferences", "href" => "?id=" . $_GET['id'] . "&act=main"),
            array("name" => "Password Change", "href" => "?id=" . $_GET['id'] . "&act=password"),
            array("name" => "Reset Account", "href" => "?id=" . $_GET['id'] . "&act=reset"),
            array("name" => "Combat Settings", "href" => "?id=" . $_GET['id'] . "&act=fight_settings")
        );
        
        if( !isset($GLOBALS['returnJson']) || $GLOBALS['returnJson'] == false ){
            $menu[] = array("name" => "Change Popup", "href" => "?id=" . $_GET['id'] . "&act=popup");
            $menu[] = array("name" => "Get Signature", "href" => "?id=" . $_GET['id'] . "&act=specialsig");

            $menu[] = array("name" => "Change Layout", "href" => "?id=" . $_GET['id'] . "&act=layout");
            
            if( in_array($GLOBALS['layout'], array('default')))
            {
                $menu[] = array("name" => "Layout Settings", "href" => "?id=" . $_GET['id'] . "&act=layout_settings");
                $menu[] = array("name" => "Key Bindings", "href" => "?id=" . $_GET['id'] . "&act=key_bindings");
            }

        }

        if( $GLOBALS['userdata'][0]['rank_id'] >= 4 ){
            $menu[] = array("name" => "Sensei Settings", "href" => "?id=" . $_GET['id'] . "&act=student");
        }

        $GLOBALS['template']->assign('subHeader', 'Preferences');
        $GLOBALS['template']->assign('nCols', 3);
        $GLOBALS['template']->assign('nRows', ceil(count($menu)/3) );
        $GLOBALS['template']->assign('subTitle_info', "Here you can adjust various settings for your character");
        $GLOBALS['template']->assign('linkMenu', $menu);
        $GLOBALS['template']->assign('buttonLayout', "SUBMIT");
        $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');

    }

    //	Password settings form
    private function change_password() {

        // Create the fields to be shown
        $inputFields = array(
            array("infoText"=>"New password","inputFieldName"=>"new_pass", "type" => "password", "inputFieldValue" => ""),
            array("infoText"=>"Confirm new password","inputFieldName"=>"new_pass_conf", "type" => "password", "inputFieldValue" => ""),
            array("infoText"=>"Old password","inputFieldName"=>"old_pass", "type" => "password", "inputFieldValue" => "")
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Change your account's password.", // Information
            "Preferences", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] , "submitFieldName" => "Submit","submitFieldText" => "Change Now"), // Submit button
            "Return" // Return link name
        );
    }

    //	Password settings do
    private function do_change_password() {

        // Entered passwords
        $newPass = functions::encryptPassword($_POST['new_pass'], $this->user[0]['join_date']);
        $oldPass = functions::encryptPassword($_POST['old_pass'], $this->user[0]['join_date']);

        // Check new pass matches
        if( $_POST['new_pass'] == $_POST['new_pass_conf'] && $_POST['new_pass'] != '' ){
            if( $oldPass == $this->user[0]['salted_password'] ){

                // Update password
                $GLOBALS['database']->execute_query("UPDATE `users` SET `salted_password` = '" . $newPass . "'  WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");

                // Message
                $GLOBALS['page']->Message( "Your password has been changed." , 'Preferences', 'id='.$_GET['id'],'Return');

            }
            else{
                throw new Exception("Your old password did not match");
            }
        }
        else{
            throw new Exception("Your new password did not match the confirmation.");
        }
    }

    // Avatar functions

    // Set limits of the avatars
    private function setAvatarLimits(){

        switch($GLOBALS['userdata'][0]['federal_level']) {
            case "Normal":  $this->dim = 150; $this->size = 600; break;
            case "Silver":  $this->dim = 200; $this->size = 800; break;
            case "Gold":    $this->dim = 250; $this->size = 1000; break;
            default: $this->dim = 100; $this->size = 400; break;
        }

        // Because I love our hard workers...
        if(in_array($GLOBALS['userdata'][0]['user_rank'], Data::$STAFF_RANKS, true)) { $this->dim = 250; $this->size = 2000; }
    }

    //	Avatar change form
    private function change_avatar() {

        // Set avatar limits for this user
        $this->setAvatarLimits();

        // Get the signature
        $image = functions::getUserImage('/avatars/', $_SESSION['uid']);

        // Get the fileuploadlibrary
        require_once(Data::$absSvrPath.'/global_libs/General/fileUploads.php');
        fileUploader::uploadForm(array(
            "maxsize" => $this->size."kb",
            "subTitle" => "Change Avatar",
            "image" => $image,
            "description" => "Upload a new signature image for your avatar.",
            "dimX" => $this->dim,
            "dimY" => $this->dim
        ));

        // Return Link
        $GLOBALS['template']->assign("returnLink", true);

    }

    // Avatar change do
    private function do_avatar_change() {

        // Set avatar limits for this user
        $this->setAvatarLimits();

        // Get the fileuploadlibrary
        require_once(Data::$absSvrPath.'/global_libs/General/fileUploads.php');
        $upload = fileUploader::doUpload(array(
            "maxsize" => $this->size * 1024,
            "destination" => 'avatars/',
            "filename" => $_SESSION['uid'],
            "dimX" => $this->dim,
            "dimY" => $this->dim
        ));

        // Message to user
        if( $upload == true ){
            $GLOBALS['page']->Message('You have successfully uploaded a new avatar.', 'Change Avatar', 'id=' . $_GET['id'] . '');
        }
    }

    //	Account deletion functions

    // Form letting the user mark the account for deletion
    private function delete_form() {

        // Create the fields to be shown
        $inputFields = array(
            array("infoText"=>"Password","inputFieldName"=>"old_pass", "type" => "password", "inputFieldValue" => "")
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "This will flag your account for deletion, 7 days after this your account will be deleted without any way to retrieve it. You may cancel this at any time before the 7 days are up", // Information
            "Delete Your Account", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] , "submitFieldName" => "Submit","submitFieldText" => "Delete"), // Submit button
            "Return" // Return link name
        );
    }

    // Do mark the user for deletion
    private function set_delete_flag() {

        // Entered password
        $oldPass = functions::encryptPassword($_POST['old_pass'], $this->user[0]['join_date']);
        if( $oldPass == $this->user[0]['salted_password'] ){

            // Set timer
            $GLOBALS['database']->execute_query("UPDATE `users` SET `deletion_timer` = '" . ($GLOBALS['user']->load_time) . "' WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");

            // Message
            $GLOBALS['page']->Message('You have marked your character for deletion. Return to this page in 7 days to confirm the deletion.', 'Delete Account', 'id=' . $_GET['id'] . '');
        }
        else{
            throw new Exception("The password does not match");
        }
    }

    // Form letting the user un-mark the account for deletion
    private function undelete_form() {

        // Set timer
        $time = ($this->user[0]['deletion_timer'] + 604800) - $GLOBALS['user']->load_time;
        $timer = functions::convert_time($time, 'flagtime', 'false');

        // Create the fields to be shown
        $inputFields = array(
            array("infoText"=>"Password","inputFieldName"=>"old_pass", "type" => "password", "inputFieldValue" => "")
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Your account is currently flagged for deletion
             <br>You have ".$timer." left to cancel this process.", // Information
            "Account Deletion", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] , "submitFieldName" => "Submit","submitFieldText" => "Cancel"), // Submit button
            "Return" // Return link name
        );
    }

    // Do unmark the user for deletion
    private function unset_delete_flag() {

        // Entered password
        $oldPass = functions::encryptPassword($_POST['old_pass'], $this->user[0]['join_date']);
        if( $oldPass == $this->user[0]['salted_password'] ){

            // Set timer
            $GLOBALS['database']->execute_query("UPDATE `users` SET `deletion_timer` = '0' WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");

            // Message
            $GLOBALS['page']->Message('Your account is no longer flagged for deletion', 'Delete Account', 'id=' . $_GET['id'] . '');
        }
        else{
            throw new Exception("The password does not match");
        }
    }

    // No more changing deletion timer
    private function account_deletion() {
        $GLOBALS['page']->Message('Your account was flagged for deletion over 7 days ago, you can now no longer cancel the process. Your account will be deleted in the next sweep.', 'Delete Account', 'id=' . $_GET['id'] . '');
    }

    // General settings

    //	Main settings form
    private function preferences() {
        $GLOBALS['template']->assign('user', $this->user);
        $GLOBALS['template']->assign('contentLoad', './templates/content/preferences/preferences.tpl');
    }

    //	Main settings change
    private function change_settings() {

        // Check PM block
        $temp_query = "UPDATE `users`,`users_preferences` SET";
        if (isset($_POST['pm_block']) && ($_POST['pm_block'] == '0' || $_POST['pm_block'] == '1')) {

            // Update PM block
            $temp_query .= " `pm_block` = '" . $_POST['pm_block'] . "'";

            // Check PM by Email
            if (is_numeric($_POST['pm_by_email'])) {
                $temp_query .= ", `pm_by_email` = '" . $_POST['pm_by_email'] . "'";
            }
            else{
                throw new Exception("There was an error with the PM by Email setting");
            }

            // Check Lock
            if ( isset($_POST['account_lock']) && is_numeric($_POST['account_lock'])) {
                $temp_query .= ", `lock` = '" . $_POST['account_lock'] . "'";
            }
            else if(!isset($_POST['account_lock']))
            {}
            else
            {
                throw new Exception("There was an error with the account setting");
            }

            // Heal block
            if (is_numeric($_POST['heal_block'])) {
                $temp_query .= ", `enable_heal` = '" . $_POST['heal_block'] . "'";
            }
            else{
                 throw new Exception("There was an error with the heal block setting");
            }

            // Marriage block
            if (isset($_POST['marriage_block']) && is_numeric($_POST['marriage_block'])) {
                $temp_query .= ", `enable_marriage` = '" . $_POST['marriage_block'] . "'";
            }
            else if(!isset($_POST['marriage_block']))
            {}
            else{
                 throw new Exception("There was an error with the marriage block setting");
            }

            // Level up button
            if (isset($_POST['show_level_up_button']) && is_numeric($_POST['show_level_up_button'])) {
                $temp_query .= ", `show_level_up_button` = '" . $_POST['show_level_up_button'] . "'";
            }
            else if(!isset($_POST['show_level_up_button'])) 
            {}
            else{
                 throw new Exception("There was an error with the level up button block setting");
            }

             // Level up button
            if (is_numeric($_POST['chat_autoupdate'])) {
                $temp_query .= ", `chat_autoupdate` = '" . $_POST['chat_autoupdate'] . "'";
            }
            else{
                 throw new Exception("There was an error with the level up button block setting");
            }

            // Sensei check for rank_id 2
            if ($this->user[0]['rank_id'] == '2') {

                // Block sensei
                if ($_POST['sensei_block'] == 'no' || ( isset($_POST['anbu_block']) && $_POST['anbu_block'] == '0')) {

                    // Set sensei to blocked
                    $temp_query .= " ,`sensei` = '_disabled'";

                    // Remove from current sensei
                    $data = $GLOBALS['database']->fetch_data("SELECT `username`,`student_1`,`student_2`,`student_3` FROM `users` WHERE `id` = '" . $this->user[0]['sensei'] . "' LIMIT 1");
                    if ($data != '0 rows') {

                        // Figure out the student# for sensei
                        $place = "";
                        if ($data[0]['student_1'] == $this->user[0]['id']) {
                            $place = 'student_1';
                        } elseif ($data[0]['student_2'] == $this->user[0]['id']) {
                            $place = 'student_2';
                        } elseif ($data[0]['student_3'] == $this->user[0]['id']) {
                            $place = 'student_3';
                        }

                        // Update sensei
                        if( !empty($place) ){
                            $GLOBALS['database']->execute_query("UPDATE `users` SET `" . $place . "` = '_none' WHERE `username` = '" . $data[0]['username'] . "' LIMIT 1");
                        }
                    }
                } else {

                    // Update sensei if not set
                    if ($this->user[0]['sensei'] == '_disabled') {
                        $temp_query .= " ,`sensei` = NULL";
                    }
                }
            }

            // Anbu check for chuunin+
            if ($this->user[0]['rank_id'] >= '3') {
                if ( isset($_POST['anbu_block']) && ($_POST['anbu_block'] == 'no' || $_POST['anbu_block'] == '0')) {

                    // Reset ANBU setting
                    require_once(Data::$absSvrPath.'/libs/villageSystem/anbuLib.php');
                    $anbuLib = new anbuLib();
                    if( $anbuLib->isUserAnbu( $this->user[0]['anbu'] ) ){
                        try{
                            $anbuLib->ANBUresign( $_SESSION['uid'] , $this->user[0]['anbu'] );
                        }
                        catch(Exception $e) {
                            // Do nothing with exceptions
                        }
                    }

                    // update user
                    $temp_query .= " ,`anbu` = '_disabled'";

                    $GLOBALS['Events']->acceptEvent('anbu', array('new'=>'_disabled', 'old'=>$GLOBALS['userdata'][0]['anbu'] ));

                } else {

                    // Set to none if it was previous disabled
                    if ($this->user[0]['anbu'] == '_disabled') {
                        $temp_query .= " ,`anbu` = '_none'";
                        $GLOBALS['Events']->acceptEvent('anbu', array('new'=>'_none', 'old'=>$GLOBALS['userdata'][0]['anbu'] ));
                    }
                }
            }

            // Clan block check
            if ($_POST['clan_block'] == 'no' || $_POST['clan_block'] == '0') {

                // Reset Clan settings
                require_once(Data::$absSvrPath.'/libs/villageSystem/clanLib.php');
                $clanLib = new clanLib();
                if( $clanLib->isUserClan( $this->user[0]['clan'] ) ){
                    $this->user[0]['clan'] = "_disabled";
                    try{
                        $clanLib->doResign( $_SESSION['uid'] );
                    }
                    catch(Exception $e) {
                        // Do nothing with exceptions
                    }
                }

                // Update user
                $temp_query .= " ,`clan` = '_disabled'";

            } else {

                // update user if clan was previously disabled
                if ($this->user[0]['clan'] == '_disabled') {
                    $temp_query .= " ,`clan` = '_none'";
                }
            }

            if ( isset($_POST['silence_spar']) && $_POST['silence_spar'] == 'yes' || $_POST['silence_spar'] == '1')
            {
                $temp_query .= " ,`silence_spar` = 'yes'";
            }
            else if( isset($_POST['silence_spar']) && $_POST['silence_spar'] == 'no' || $_POST['silence_spar'] == '0')
            {
                $temp_query .= " ,`silence_spar` = 'no'";
            }

            if( isset($_POST['collapse_home']) && $_POST['collapse_home'] == 'yes' || $_POST['collapse_home'] == '1')
            {
                $temp_query .= " ,`collapse_home` = 'yes'";
            }
            else if( isset($_POST['collapse_home']) && $_POST['collapse_home'] == 'no' || $_POST['collapse_home'] == '0')
            {
                $temp_query .= " ,`collapse_home` = 'no'";
            }

            if( isset($_POST['questing_mode']) && $_POST['questing_mode'] == 'yes' || $_POST['questing_mode'] == '1' ||$_POST['questing_mode'] == 'alert')
            {
                $temp_query .= " ,`QuestingMode` = 'alert'";
            }
            else if( isset($_POST['questing_mode']) && $_POST['questing_mode'] == 'no' || $_POST['questing_mode'] == '0' || $_POST['questing_mode'] == 'quiet')
            {
                $temp_query .= " ,`QuestingMode` = 'quiet'";
            }

            if( isset($_POST['quest_widget']) && $_POST['quest_widget'] == 'yes' || $_POST['quest_widget'] == '1' ||$_POST['quest_widget'] == 'yes')
            {
                $temp_query .= " ,`quest_widget` = 'yes'";
            }
            else if( isset($_POST['quest_widget']) && $_POST['quest_widget'] == 'no' || $_POST['quest_widget'] == '0' || $_POST['quest_widget'] == 'no')
            {
                $temp_query .= " ,`quest_widget` = 'no'";
            }

            if( isset($_POST['turn_log_length']) && in_array($_POST['turn_log_length'], array('1','2','3','4','5','10','25','50','100','1000')))
            {
                $temp_query .= " ,`turn_log_length` = ".$_POST['turn_log_length'];
            }

            if( isset($_POST['travel_default_redirect']) && in_array($_POST['travel_default_redirect'], array('Combat','Scout','Rob','Profile','QuestJournal')))
            {
                $temp_query .= " ,`travel_default_redirect` = '".$_POST['travel_default_redirect']."'";
            }

            // Run query
            $temp_query .= " WHERE `id` = '" . $_SESSION['uid'] . "' AND `id` = `uid`";

            $GLOBALS['database']->execute_query($temp_query);

            // Success message
            $GLOBALS['page']->Message('Your preferences have been updated', 'User Preferences', 'id=' . $_GET['id'] . '&act=' . $_GET['act']);

        } else {
            throw new Exception("There was an error with the PM block setting");
        }
    }

    // Nindo functions
    private function nindo_length(){
        $nindo_limit = 500;
        switch( $GLOBALS['userdata'][0]['federal_level'] ){
            case "None": $nindo_limit = 1500; break;
            case "Normal": $nindo_limit = 1500*1.25; break;
            case "Silver": $nindo_limit = 1500*1.5; break;
            case "Gold": $nindo_limit = 1500*2; break;
        }
        return $nindo_limit;
    }

    //	Nindo form
    private function nindo_form() {

        // Get current nindo
        $nindo_text = '';
        $nindo = $GLOBALS['database']->fetch_data("SELECT `nindo` FROM `users` WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");
        if ($nindo != '0 rows') {
            $nindo_text = $nindo[0]['nindo'];
        }

        // Show it to the user
        $GLOBALS['page']->UserInput(
                "Describe your way of the ninja",
                "Nindo",
                array(array(
                    "inputFieldName" => "nindo",
                    "type" => "textarea",
                    "inputFieldValue" => $nindo_text,
                    "maxlength" => $this->nindo_length() )
                ),
                array("href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] ,"submitFieldName" => "Submit","submitFieldText" => "Save"),
                "Return");

    }

    // Do change nindo
    private function alter_nindo() {

        // Get nindo length
        $nindo_limit = $this->nindo_length();
        if (isset($_POST['nindo'])) {
            if (strlen($_POST['nindo']) < $nindo_limit) {
                $nindo_text = functions::store_content($_POST['nindo']);
                if ($nindo_text != '') {

                    // update database
                    $GLOBALS['database']->execute_query("UPDATE `users` SET `nindo` = '" . $nindo_text . "' WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");

                    // Show message
                    $GLOBALS['page']->Message('Your nindo has been updated', 'Nindo Change', 'id=' . $_GET['id'] . '&act=' . $_GET['act']);

                }
                else{
                    throw new Exception("You entered an empty nindo");
                }
            }
            else{
                throw new Exception("Your nindo exceeds the character limit of: ".$nindo_limit." characters & spaces.");
            }
        }
        else{
            throw new Exception("No nindo was specified");
        }
    }

    // Leaving village functions

    //Leave village form
    private function confirm_leave() {
        $GLOBALS['page']->Confirm('Are you sure you wish to leave this village? <br>Neither the administration nor the moderators will put you back in your village if you click this button "by accident"', 'Leave Village', 'Leave Now');
    }

    // Leave village do
    private function leave_village() {
        if ($this->user[0]['status'] == 'awake' ) {

            // Get alliance & check if in war
            $this->alliance = cachefunctions::getAlliance( $GLOBALS['userdata'][0]['village'] );
            require_once(Data::$absSvrPath.'/libs/villageSystem/warLib.php');
            $this->warLib = new warLib();
            if( !$this->warLib->inWar( $this->alliance[0] ) ){

                // Turn outlaw
                require_once(Data::$absSvrPath.'/libs/villageSystem/respectLib.php');
                $respectLib = new respectLib();
                $message = $respectLib->turn_outlaw( $_SESSION['uid'] );
                $GLOBALS['page']->Message( $message , 'Leaving Village', 'id='.$_GET['id']);

                // Log the change
                functions::log_village_changes(
                    $GLOBALS['userdata'][0]['id'],
                    $GLOBALS['userdata'][0]['village'],
                    "Syndicate",
                    "Left through preferences menu"
                );


            }
            else{
                throw new Exception("You cannot leave your village as long as the village is in war.");
            }
        }
        else{
            throw new Exception("You must be awake to leave your village");
        }
    }

    // Color functions

    private function get_available_colors(){
        $availableColors = array();
        if( in_array($GLOBALS['userdata'][0]['user_rank'], array("Paid", "Admin", "EventMod", "Event"), true) ){
            if( in_array($GLOBALS['userdata'][0]['federal_level'] , array("Normal","Silver","Gold"), true)){
                $availableColors["Normal"] = "Midnight Blue";
            }
            if( in_array($GLOBALS['userdata'][0]['federal_level'] , array("Silver","Gold"), true)){
                $availableColors["Silver"] = "Silver";
            }
            if( in_array($GLOBALS['userdata'][0]['federal_level'] , array("Gold"), true)){
                $availableColors["Gold"] = "Golden";
            }
        }
        return $availableColors;
    }

    // Change layout form
    private function color_form() {

        // Available layouts
        $availableColors = $this->get_available_colors();

        // Create the input form
        $GLOBALS['page']->UserInput(
                "You can change the color in which your character name is displayed using this feature. The name of your color automatically change with your federal support level, and you can therefore only pick colors corresponding to lower federal support levels - not higher. To get more colors, you should purchase federal support for your character.",
                "Color Change",
                array(
                    // A select box
                    array(
                        "inputFieldName"=>"color",
                        "type"=>"select",
                        "inputFieldValue"=> $availableColors
                    )
                ),
                array(
                    "href"=>"?id=".$_GET['id']."&act=".$_GET['act'] ,
                    "submitFieldName"=>"Submit",
                    "submitFieldText"=>"Change color"),
                "Return"
        );
    }

    // Change layout do
    private function alter_color() {

        // Available layouts
        $availableColors = $this->get_available_colors();

        // Check that value was set and exists
        if (isset($_POST['color']) && array_key_exists($_POST['color'], $availableColors)) {

            // Update user
            $GLOBALS['database']->execute_query("UPDATE `users_preferences` SET `visibleRank` = '" . $_POST['color'] . "' WHERE `uid` = '" . $_SESSION['uid'] . "' LIMIT 1");

            // Give message
            $GLOBALS['page']->Message( "Your color has been changed" , 'Color Change', 'id='.$_GET['id']."&act=".$_GET['act']);

        } else {
            throw new Exception("You did not pick a valid color");
        }
    }

    // Layout functions

    // Change layout form
    private function layout_form() {

        // Available layouts
        $layouts = array();

        // Retrieve available
        $dir = opendir('./files');
        while (false !== ($file = readdir($dir))) {
            if (($file != '..') && ($file != '.')) {
                if (is_dir('./files/' . $file)) {
                    $filename = str_replace('_', ' ', str_replace('layout_', '', $file));
                    if ( !in_array($filename,array("general includes","javascript","api")) ) {
                        $layouts[$filename] = $filename;
                    }
                }
            }
        }
        closedir($dir);
        
        if($GLOBALS['userdata'][0]['layout'] == 'default')
        {
            // Available themes
            $themes = array('default'=>'default');
            if (is_dir('./files/layout_default/themes'))
            {
                // Retrieve available
                $dir = opendir('./files/layout_default/themes');
                readdir($dir);
                while (false !== ($file = readdir($dir))) {
                    if (($file != '..') && ($file != '.')) {
                        if (is_dir('./files/layout_default/themes/' . $file)) {
                            $themes[$file] = $file;
                        }
                    }
                }
                closedir($dir);
            }


            // Create the input form
            $GLOBALS['page']->UserInput(
                    "Change your layout and theme",
                    "Layout and Theme Change",
                    array(
                        // A select box
                        array(
                            "infoText" => "Layout",
                            "inputFieldName"=>"layout",
                            "type"=>"select",
                            "selected"=>$GLOBALS['userdata'][0]['layout'],
                            "inputFieldValue"=> $layouts
                        ),

                        array(
                            "infoText" => "Theme",
                            "inputFieldName"=>"theme",
                            "type"=>"select",
                            "selected"=>$GLOBALS['userdata'][0]['theme'],
                            "inputFieldValue"=> $themes
                        )

                    ),
                    array(
                        "href"=>"?id=".$_GET['id']."&act=".$_GET['act'] ,
                        "submitFieldName"=>"Submit",
                        "submitFieldText"=>"Change layout"),
                    "Return"
            );
        }
        else
        {
            // Create the input form
            $GLOBALS['page']->UserInput(
                "Change your layout<br>You may only change your theme for the default layout.<br>To change your theme you must visit this page while you are on the default theme.",
                "Layout Change",
                array(
                    // A select box
                    array(
                        "infoText" => "Layout",
                        "inputFieldName"=>"layout",
                        "type"=>"select",
                        "selected"=>$GLOBALS['userdata'][0]['layout'],
                        "inputFieldValue"=> $layouts
                    )
                ),
                array(
                    "href"=>"?id=".$_GET['id']."&act=".$_GET['act'] ,
                    "submitFieldName"=>"Submit",
                    "submitFieldText"=>"Change layout"),
                "Return"
        );
        }
    }

    // Change layout do
    private function alter_layout() {
        if (isset($_POST['layout'])) {
            $GLOBALS['template']->assign('post_success', true);
            if (is_dir('./files/layout_' . str_replace(' ', '_', trim($_POST['layout'])))) {

                if( !isset($_POST['theme']) || $_POST['theme'] == '')
                    $_POST['theme'] = 'default';

                if (!is_dir('./files/layout_'.$GLOBALS['userdata'][0]['layout'].'/themes/'.$_POST['theme']))
                    $_POST['theme'] = 'default';

                if($_POST['layout'] == 'mobile')
                    $_POST['layout'] = 'default';
                
                // Update user
                $GLOBALS['database']->execute_query("UPDATE `users_preferences` 
                                                        SET `layout` = '" . str_replace(' ', '_', trim($_POST['layout'])) . "', 
                                                            `theme` = '".$_POST['theme']."'
                                                        WHERE `uid` = '" . $_SESSION['uid'] . "' LIMIT 1");
                $GLOBALS['layout'] = str_replace(' ', '_', trim($_POST['layout']));
                $GLOBALS['theme'] = $_POST['theme'];

                // Give message
                $GLOBALS['page']->Message( "Your layout has been changed" , 'Layout Change', 'id='.$_GET['id']."&act=".$_GET['act']);


            } else {
                throw new Exception("Could not find this layout on the server");
            }
        } else {
            throw new Exception("You did not pick a layout");
        }
    }

    private function fight_settings_form()
    {
        $GLOBALS['template']->assign('settings', json_decode($GLOBALS['userdata'][0]['fight_settings'], true));
        $GLOBALS['template']->assign('contentLoad', './templates/content/preferences/pref_fight_settings.tpl');
    }

    private function fight_settings_reset()
    {
        $query = "UPDATE `users_preferences`
                    SET 
                        `fight_settings` = '{\"village\":true,\"rank\":true,\"activity\":false,\"dsr\":true,\"directions\":true,\"name_text_color_match_alliance\":false,\"all_text_color_match_alliance\":false,\"rank_compress\":false,\"hide_syndicate_ranks\":false,\"hide_self\":false,\"hide_ally\":false,\"hide_betray\":true,\"hide_call_for_help\":false,\"hide_glimpseable\":false,\"hide_chase\":false,\"colors\":{\"Ally\":\"rgb(106,168,79)\",\"Self\":\"rgb(106,168,79)\",\"Neutral\":\"rgb(60,120,216)\",\"Enemy\":\"rgb(166,28,0)\",\"Betray\":\"rgb(166,77,121)\",\"Faint\":\"rgb(153,153,153)\",\"Attack-Neutral\":\"rgb(60,120,216)\",\"Attack-Enemy\":\"rgb(166,28,0)\",\"Chase-Neutral\":\"rgb(60,120,216)\",\"Chase-Enemy\":\"rgb(166,28,0)\",\"Help\":\"rgb(106,168,79)\"}}'
                                                          
                    WHERE `uid` = " . $_SESSION['uid'];

        if(!$GLOBALS['database']->execute_query($query))
        {
            //this is throwing an error to tell the user that no changes were made
			throw new exception('No changes have been made.');
        }
        else
        {
            //this is displaying the message to the user that the changes went well.
            $GLOBALS['template']->assign('post_success', true);
            $GLOBALS['page']->Message( "Your Combat Settings have been reset!" , 'Combat Settings Reset', 'id='.$_GET['id']."&act=".$_GET['act']);
        }
    }

    private function fight_settings_update()
    {
        foreach($_POST as $key => $value)
        {
            if($value === "1")
                $_POST[$key] = 'true';
            else if($value === "0")
                $_POST[$key] = 'false';
        }

        $query = "UPDATE `users_preferences` SET `fight_settings` = '{\"village\":{$_POST['village']},\"rank\":{$_POST['rank']},\"activity\":{$_POST['activity']},\"dsr\":{$_POST['dsr']},\"directions\":{$_POST['directions']},\"name_text_color_match_alliance\":{$_POST['name_text_color_match_alliance']},\"all_text_color_match_alliance\":{$_POST['all_text_color_match_alliance']},\"rank_compress\":{$_POST['rank_compress']},\"hide_syndicate_ranks\":{$_POST['hide_syndicate_ranks']},\"hide_self\":{$_POST['hide_self']},\"hide_ally\":{$_POST['hide_ally']},\"hide_betray\":{$_POST['hide_betray']},\"hide_call_for_help\":{$_POST['hide_call_for_help']},\"hide_glimpseable\":{$_POST['hide_glimpseable']},\"hide_chase\":{$_POST['hide_chase']},\"colors\":{\"Ally\":\"{$_POST['Ally']}\",\"Self\":\"{$_POST['Self']}\",\"Neutral\":\"{$_POST['Neutral']}\",\"Enemy\":\"{$_POST['Enemy']}\",\"Betray\":\"{$_POST['Betray']}\",\"Faint\":\"{$_POST['Faint']}\",\"Attack-Neutral\":\"{$_POST['Attack-Neutral']}\",\"Attack-Enemy\":\"{$_POST['Attack-Enemy']}\",\"Chase-Neutral\":\"{$_POST['Chase-Neutral']}\",\"Chase-Enemy\":\"{$_POST['Chase-Enemy']}\",\"Help\":\"{$_POST['Help']}\"}}' WHERE `uid` = {$_SESSION['uid']};";

        if(!$GLOBALS['database']->execute_query($query))
        {
            //this is throwing an error to tell the user that no changes were made
            throw new exception('No changes have been made.');
        }
        else
        {
            //this is displaying the message to the user that the changes went well.
            $GLOBALS['template']->assign('post_success', true);
            $GLOBALS['page']->Message( "Your Combat Settings have been updated!" , 'Combat Settings Reset', 'id='.$_GET['id']."&act=".$_GET['act']);
        }
    }

    private function layout_settings_form()
    {
        $GLOBALS['template']->assign('layout_quick_links', json_decode($GLOBALS['userdata'][0]['layout_quick_links'], true));
        $GLOBALS['template']->assign('layout_mobile_quick_links', json_decode($GLOBALS['userdata'][0]['layout_mobile_quick_links'], true));
        $GLOBALS['template']->assign('layout_colors', $GLOBALS['userdata'][0]['layout_colors'], true);
        $GLOBALS['template']->assign('contentLoad', './templates/content/preferences/pref_layout_settings.tpl');
    }

    private function layout_settings_reset()
    {
        $query = "UPDATE `users_preferences`
                    SET 
                        `layout_portrait_location`    = 'left', layout_portrait_index         = '1',
                        `layout_details_location`     = 'left', layout_details_index          = '2',
                        `layout_travel_location`      = 'left', layout_travel_index           = '3',
                        `layout_travel_mobile`        = 'on',   layout_notifications_location = 'right',
                        `layout_notifications_index`  = '1',    layout_quests_location        = 'right',
                        `layout_quests_index`         = '3',    layout_menu_location          = 'right',
                        `layout_menu_index`           = '4',     layout_quick_links_location  = 'right',
                        `layout_quick_links_index`    = '2',     layout_quick_links_style     = 'text',
                        `layout_quick_mobile`         = 'on',

                        `layout_quick_links`  ='{\"quick-link-1\":\"combat\",    \"quick-link-2\":\"scout\",    \"quick-link-3\":\"mission\",
                                                 \"quick-link-4\":\"errands\",   \"quick-link-5\":\"training\", \"quick-link-6\":\"inbox\",
                                                 \"quick-link-7\":\"inventory\", \"quick-link-8\":\"jutsu\",    \"quick-link-9\":\"bank\"}',
                        
                        `layout_mobile_quick_links`   = '{\"mobile-quick-link-1\":\"combat\",   \"mobile-quick-link-2\":\"scout\", 
                                                          \"mobile-quick-link-3\":\"training\", \"mobile-quick-link-4\":\"errands\", 
                                                          \"mobile-quick-link-5\":\"mission\"}',
                                                          
                        `layout_font` = 'default',
                        
                        `layout_colors` = '{    \"DASHDASHbody-background\":       \"rgb(241,224,186)\",
                                                \"DASHDASHaccent-color\":          \"rgb(255,249,230)\",
                                                \"DASHDASHaccent-color-dim\":      \"rgb(128,121,102)\",
                                                \"DASHDASHaccent-color-dark\":     \"rgb(38,36,31)\",
                                                \"DASHDASHaccent-color-light\":    \"rgb(242,236,218)\",
                                                \"DASHDASHaccent-border-color\":   \"rgb(166,157,133)\",
                                                \"DASHDASHbackground-light\":      \"rgb(243,232,205)\",
                                                \"DASHDASHbackground-light-alt\":  \"rgb(255,255,255)\",
                                                \"DASHDASHbackground-normal\":     \"rgb(253,246,227)\",
                                                \"DASHDASHbackground-normal-alt\": \"rgb(253,246,227)\",
                                                \"DASHDASHbackground-dark\":       \"rgb(146,69,57)\",
                                                \"DASHDASHbackground-dark-alt\":   \"rgb(146,69,57)\"}'
                                                          
                    WHERE `uid` = " . $_SESSION['uid'];

        if(!$GLOBALS['database']->execute_query($query))
        {
            //this is throwing an error to tell the user that no changes were made
			throw new exception('No changes have been made.');
        }
        else
        {
            //this is displaying the message to the user that the changes went well.
            $GLOBALS['template']->assign('post_success', true);
            $GLOBALS['page']->Message( "Your layout settings have been reset!" , 'Layout Settings Reset', 'id='.$_GET['id']."&act=".$_GET['act']);
        }
    }

    private function layout_settings_update()
    {
        $query_sets = array();
		$changes_to_make_to_userdata = array();

        //this is the things that are sent via post or we add to post that can be sent to the database
        //we define these here so that a malicious user can not send things via post and change them that arn't in this list
		$options = array('layout_portrait_location', 'layout_portrait_index',
						 'layout_details_location', 'layout_details_index',
						 'layout_travel_location', 'layout_travel_index', 
						 'layout_travel_mobile',
						 'layout_notifications_location', 'layout_notifications_index',
						 'layout_quests_location', 'layout_quests_index',
                         'layout_menu_location', 'layout_menu_index', 
                         'layout_quick_links_location', 'layout_quick_links_index', 
                         'layout_quick_links_style', 'layout_quick_links',
                         'layout_quick_mobile',  'layout_mobile_quick_links',
                         'layout_font', 'layout_colors');

        //this section here is trying to check and make sure that the things that have index's and left/right positions are not overlapping
        //this was the old code designed to prevent matching index and locations.
		$right = $left = array(0,0,0,0,0,0,0);
		$location_options = array('portrait','details','travel','notifications','quests','menu');

        //look at each option that can have an index/side
		foreach($location_options as $option)
		{
            //if this option is on the right
			if($_POST['layout_'.$option.'_location'] == 'right')
			{
                //if this position on the right side already has something in it and the position is not 0 throw an error
				if( $right[$_POST['layout_'.$option.'_index']] !== 0 && $_POST['layout_'.$option.'_index'] != 0)
					throw new exception("You may not set these options in a way that a widget will be on the same side and have the same index as another. (".$option." and ".$right[$_POST['layout_'.$option.'_index']]." conflict)");
                
                //this can be reached if no error has been thrown, aka nothing is in this spot yet
                //so record that there is something in this spot
				$right[$_POST['layout_'.$option.'_index']] = $option;
            }
            
            //repeat for the left side
			else if($_POST['layout_'.$option.'_location'] == 'left')
			{
				if($left[$_POST['layout_'.$option.'_index']] !== 0 && $_POST['layout_'.$option.'_index'] != 0)
					throw new exception("You may not set these options in a way that a widget will be on the same side and have the same index as another. (".$option." and ".$left[$_POST['layout_'.$option.'_index']]." conflict)");

				$left[$_POST['layout_'.$option.'_index']] = $option;
			}
        }
        
        //process quick-link and mobile-quick-link
        $quick_links = array();
        $mobile_quick_links = array();
        $colors = array();

        //foreach thing sent via post
        foreach($_POST as $option => $value)
        {
            //if this is a quick link option record it in the quick link var
            if( substr($option, 0, 10) == 'quick-link' )
                $quick_links[$option] = $value;

            //if this is a quick link MOBILE option record it in the quick link MOBILE var
            else if( substr($option, 0, 17) == 'mobile-quick-link' )
                $mobile_quick_links[$option] = $value;

            //if this is a color option record it in the colors var
            else if( substr($option, 0, 8) == 'DASHDASH' )
            {
                if($value != '')
                    $colors[$option] = $value;

                else
                    $colors[$option] = $GLOBALS['userdata'][0]['layout_colors'][$option];
            }
        }

        //convert the arrays built above into json strings and store them in post
        $_POST['layout_quick_links'] = json_encode($quick_links);
        $_POST['layout_mobile_quick_links'] = json_encode($mobile_quick_links);

        $colors = json_encode($colors);
        if(strlen($colors) > 10)
            $_POST['layout_colors'] = $colors;
        else
            $_POST['layout_colors'] = json_encode($GLOBALS['userdata'][0]['layout_colors']);
        
        //go through everything in post
		foreach($_POST as $option => $value)
		{
            //if we have cleared this thing as being okay to send to the database and this value is not what is currently in the database 
            //build this bit of the query that will record this information
			if(in_array($option, $options) && $value != $GLOBALS['userdata'][0][$option])
			{
				$query_sets[] = "`" . $option . "` = '" . $value . "'"; //this is filling an array with what will follow "SET" in the query but be before "WHERE"
				$changes_to_make_to_userdata[$option] = $value; //this is filling an array with the changes that are being made so that we can enforce those changes now as well, instead of require another page refresh for them to take effect
			}
		}

        //check to see that changes were needed
		if(count($query_sets) > 0)
		{
            //final construction of the query and running of the query. (this should be checking for failure but is not)
            if( !$GLOBALS['database']->execute_query("UPDATE `users_preferences` SET " . implode(', ',$query_sets) . " WHERE `uid` = " . $_SESSION['uid'] . " LIMIT 1"))
                throw new exception("Query Failed: "."UPDATE `users_preferences` SET " . implode(', ',$query_sets) . " WHERE `uid` = " . $_SESSION['uid'] . " LIMIT 1");
            

            //after the query is ran this updates the global variable with the changes as well.
            //this causes the changes to take effect this page load instead of require a page refresh.
			foreach($changes_to_make_to_userdata as $option => $value)
				$GLOBALS['userdata'][0][$option] = $value;

            //this is displaying the message to the user that the changes went well.
			$GLOBALS['template']->assign('post_success', true);
			$GLOBALS['page']->Message( "Your settings layout have been updated" , 'Layout Settings Update', 'id='.$_GET['id']."&act=".$_GET['act']);
		}
		else
		{
            //this is throwing an error to tell the user that no changes were made
			throw new exception('No changes have been made.');
		}
    }

    private function key_bindings_form()
    {
        $GLOBALS['template']->assign('keyBindings', json_decode($GLOBALS['userdata'][0]['key_bindings'], true));
        $GLOBALS['template']->assign('key_bindings_status', $GLOBALS['userdata'][0]['key_bindings_status']);
        $GLOBALS['template']->assign('contentLoad', './templates/content/preferences/pref_key_bindings.tpl');
    }

    private function key_binding_settings_reset()
    {
        $query = 'UPDATE `users_preferences`
                    SET `key_bindings` = \'{"move-north":"w,up,8","move-south":"s,down,2","move-west":"a,left,4","move-east":"d,right,6","move-north-west":"q,7","move-north-east":"e,9","move-south-east":"c,3","move-south-west":"z,1","link-anbu":"shift+a","link-bank":"b","link-global-trade":"v","link-clan":"f1","link-errands":"shift+e","link-home-inventory":"shift+i","link-inbox":"g","link-inventory":"i","link-jutsu":"j","link-marriage":"n","link-missions":"m","link-occupation":"h","link-profile":"p","link-preferences":"y","link-profession":"u","link-quests":"comma","link-ramen":"r","link-rob":"f","link-scout":"x","link-sleep":"tab","link-wakeup":"shift+tab","link-tavern":"o","link-training":"t","link-travel":"k","link-territory":"shift+k","link-combat":"`","link-ramen-full-heal":"shift+r","link-missions-a":"alt+a,shift+4","link-missions-b":"alt+b,shift+3","link-missions-c":"alt+c,shift+2","link-missions-d":"alt+d,shift+1" }\',
                        `key_bindings_status` = 1
                    
                    WHERE `uid` = ' . $_SESSION['uid'];

        if(!$GLOBALS['database']->execute_query($query))
        {
            //this is throwing an error to tell the user that no changes were made
			throw new exception('No changes have been made.');
        }
        else
        {
            //this is displaying the message to the user that the changes went well.
            $GLOBALS['template']->assign('post_success', true);
            $GLOBALS['page']->Message( "Your key binding settings have been reset!" , 'Key Binding Settings Reset', 'id='.$_GET['id']."&act=".$_GET['act']);
        }
    }

    private function key_binding_settings_update()
    {
        $query_sets = array();
		$changes_to_make_to_userdata = array();

        if($_POST['key_bindings_status'] != $this->user[0]['key_bindings_status'])
        {
            $query_sets[] = "`key_bindings_status` = '{$_POST['key_bindings_status']}'";
            $changes_to_make_to_userdata['key_bindings_status'] = $_POST['key_bindings_status'];
        }

        $key_bindings = array();
        foreach($_POST as $key => $value)
        {
            if(substr($key, 0, 4) == 'link' || substr($key, 0, 4) == 'move')
            {
                $key_bindings[$key] = str_replace('~','`',$value);
            }
        }
        $key_bindings = json_encode($key_bindings);


        if($key_bindings != $this->user[0]['key_bindings'])
        {
            $query_sets[] = "`key_bindings` = '{$key_bindings}'";
            $changes_to_make_to_userdata['key_bindings'] = $key_bindings;
        }
        
        //check to see that changes were needed
		if(count($query_sets) > 0)
		{
            //final construction of the query and running of the query. (this should be checking for failure but is not)
            if( !$GLOBALS['database']->execute_query("UPDATE `users_preferences` SET " . implode(', ',$query_sets) . " WHERE `uid` = " . $_SESSION['uid'] . " LIMIT 1"))
                throw new exception("Query Failed: "."UPDATE `users_preferences` SET " . implode(', ',$query_sets) . " WHERE `uid` = " . $_SESSION['uid'] . " LIMIT 1");
            
            //after the query is ran this updates the global variable with the changes as well.
            //this causes the changes to take effect this page load instead of require a page refresh.
			foreach($changes_to_make_to_userdata as $option => $value)
                $GLOBALS['userdata'][0][$option] = $value;
                
           //this is displaying the message to the user that the changes went well.
			$GLOBALS['template']->assign('post_success', true);
			$GLOBALS['page']->Message( "Your key binding settings have been updated" , 'Key Binding Settings Update', 'id='.$_GET['id']."&act=".$_GET['act']);
		}
		else
		{
            //this is throwing an error to tell the user that no changes were made
			throw new exception('No changes have been made.');
		}
    }

    private function popup_form() {

        // Create the input form
        $GLOBALS['page']->UserInput(
                "Change your popup theme",
                "popup Change",
                array(
                    // A select box
                    array(
                        "inputFieldName"=>"popup",
                        "type"=>"select",
                        "inputFieldValue"=> array('light' => 'light', 'dark' => 'dark', 'supervan' => 'full_page', 'modern' => 'light_2', 'material' => 'light_3', 'bootstrap' => 'light_4' )
                    )
                ),
                array(
                    "href"=>"?id=".$_GET['id']."&act=".$_GET['act'] ,
                    "submitFieldName"=>"Submit",
                    "submitFieldText"=>"Change popup"),
                "Return"
        );
    }

    // Change layout do
    private function alter_popup() {
        if (isset($_POST['popup'])) {
            $GLOBALS['template']->assign('post_success', true);
            // Update user
            $GLOBALS['database']->execute_query("UPDATE `users_preferences` SET `popup` = '" . $_POST['popup'] . "' WHERE `uid` = '" . $_SESSION['uid'] . "' LIMIT 1");
            $GLOBALS['userdata'][0]['popup'] = $_POST['popup'];

            // Give message
            $GLOBALS['page']->Message( "Your popup theme has been changed" , 'Popup Change', 'id='.$_GET['id']."&act=".$_GET['act']);
        } else {
            throw new Exception("You did not pick a popup theme");
        }
    }

    // Change layout form
    private function namechange_form() {

        // Create the fields to be shown
        $inputFields = array(
            array("infoText"=>"New Name","inputFieldName"=>"username", "type" => "input", "inputFieldValue" => "")
        );

        // Get data
        $user = $GLOBALS['database']->fetch_data("SELECT `nameChanges` FROM `users_statistics` WHERE `uid` = '".$_SESSION['uid']."' LIMIT 1");

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "This feature allows you to change the name of your character. Please note that all namechanges are logged and are clearly visible to all moderators. You currently have ".$user[0]['nameChanges']." namechanges available. Namechanges can be purchased in the black market for reputation points.
            ", // Information
            "Change Username", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] , "submitFieldName" => "Submit","submitFieldText" => "Send Request"), // Submit button
            "Return" // Return link name
        );
    }

    // Change layout form
    private function do_namechange() {

        // Start transaction
        $GLOBALS['database']->transaction_start();

        // Get data
        if(!($user = $GLOBALS['database']->fetch_data("SELECT `users_statistics`.`nameChanges`, `users_statistics`.`user_rank`,
            `users_statistics`.`uid`
            FROM `users_statistics`
            WHERE `users_statistics`.`uid` = '".$_SESSION['uid']."' LIMIT 1 FOR UPDATE"))) {
            throw new Exception('There was an error trying to obtain necessary user information!');
        }
        elseif($user === '0 rows') {
            throw new Exception('Either your session has ended or the user does not exist!');
        }

        if(in_array($user[0]['user_rank'], Data::$STAFF_RANKS, true)) {
            throw new Exception("Cannot use this feature with the user rank: ".$user[0]['user_rank']);
        }
        elseif($user[0]['nameChanges'] <= 0) {
            throw new Exception("You do not have any username changes available for your account!");
        }

        // Get the library for checking usernames
        require_once(Data::$absSvrPath."/libs/profileFunctions/registrationChecks.php");
        $_POST['username'] = str_replace(' ', '', trim($_POST['username']));
        $testUsername = username_check($_POST['username']);

        if ($testUsername[1] !== 1) {
            throw new Exception("Error with username: ".$testUsername[0]);
        }


        if(!($fbData = $GLOBALS['database']->fetch_data("SELECT `fbRequests`.`username` FROM `users`
            INNER JOIN `fbRequests` ON (`fbRequests`.`username` = `users`.`username`)
            WHERE `users`.`id` = '".$user[0]['uid']."' FOR UPDATE"))) {
            throw new Exception('There was an error with trying to obtain Facebook Data!');
        }
        elseif($fbData !== '0 rows') {
            if($GLOBALS['database']->execute_query("UPDATE `users`
                    INNER JOIN `fbRequests` ON (`fbRequests`.`username` = `users`.`username`)
                SET `fbRequests`.`username` = '".$_POST['username']."'
                WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
                if($GLOBALS['database']->getErrorReported() === true) {
                    throw new Exception('There was an issue trying to clean up Facebook Request user information!');
                }
            }
        }
        unset($fbData);

        if(!($ipnRData = $GLOBALS['database']->fetch_data("SELECT `ipn_payments`.`recipient` FROM `users`
            INNER JOIN `ipn_payments` ON (`ipn_payments`.`r_uid` = `users`.`id`)
            WHERE `users`.`id` = '".$user[0]['uid']."' FOR UPDATE"))) {
            throw new Exception('There was an error with trying to obtain Paypal Receive Data!');
        }
        elseif($ipnRData !== '0 rows') {
            if($GLOBALS['database']->execute_query("UPDATE `users`
                    INNER JOIN `ipn_payments` ON (`ipn_payments`.`r_uid` = `users`.`id`)
                SET `ipn_payments`.`recipient` = '".$_POST['username']."'
                WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
                if($GLOBALS['database']->getErrorReported() === true) {
                    throw new Exception('There was an issue trying to clean up PayPal Recipient user information!');
                }
            }
        }
        unset($ipnRData);

        if(!($ipnSData = $GLOBALS['database']->fetch_data("SELECT `ipn_payments`.`sender` FROM `users`
            INNER JOIN `ipn_payments` ON (`ipn_payments`.`s_uid` = `users`.`id`)
            WHERE `users`.`id` = '".$user[0]['uid']."' FOR UPDATE"))) {
            throw new Exception('There was an error with trying to obtain Paypal Sent Data!');
        }
        elseif($ipnSData !== '0 rows') {
            if($GLOBALS['database']->execute_query("UPDATE `users`
                    INNER JOIN `ipn_payments` ON (`ipn_payments`.`s_uid` = `users`.`id`)
                SET `ipn_payments`.`sender` = '".$_POST['username']."'
                WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
                if($GLOBALS['database']->getErrorReported() === true) {
                    throw new Exception('There was an issue trying to clean up PayPal Sender user information!');
                }
            }
        }
        unset($ipnSData);

        if(!($NFData = $GLOBALS['database']->fetch_data("SELECT `ninja_farmer`.`user` FROM `users`
            INNER JOIN `ninja_farmer` ON (`ninja_farmer`.`user` = `users`.`username`)
            WHERE `users`.`id` = '".$user[0]['uid']."' FOR UPDATE"))) {
            throw new Exception('There was an error with trying to obtain Ninja Farmer Data!');
        }
        elseif($NFData !== '0 rows') {
            if($GLOBALS['database']->execute_query("UPDATE `users`
                    INNER JOIN `ninja_farmer` ON (`ninja_farmer`.`user` = `users`.`username`)
                SET `ninja_farmer`.`user` = '".$_POST['username']."'
                WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
                if($GLOBALS['database']->getErrorReported() === true) {
                    throw new Exception('There was an issue trying to clean up Ninja Farmer user information!');
                }
            }
        }
        unset($NFData);

        if(!($PCData = $GLOBALS['database']->fetch_data("SELECT `promotionCodes`.`collector` FROM `users`
            INNER JOIN `promotionCodes` ON (`promotionCodes`.`collector` = `users`.`username`)
            WHERE `users`.`id` = '".$user[0]['uid']."' FOR UPDATE"))) {
            throw new Exception('There was an error with trying to obtain Promotion Code Data!');
        }
        elseif($PCData !== '0 rows') {
            if($GLOBALS['database']->execute_query("UPDATE `users`
                    INNER JOIN `promotionCodes` ON (`promotionCodes`.`collector` = `users`.`username`)
                SET `promotionCodes`.`collector` = '".$_POST['username']."'
                WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
                if($GLOBALS['database']->getErrorReported() === true) {
                    throw new Exception('There was an issue trying to clean up Promotion Code user information!');
                }
            }
        }
        unset($PCData);


        if(!($RTRData = $GLOBALS['database']->fetch_data("SELECT `ryo_track`.`receiver` FROM `users`
            INNER JOIN `ryo_track` ON (`ryo_track`.`r_uid` = `users`.`id`)
            WHERE `users`.`id` = '".$user[0]['uid']."' FOR UPDATE"))) {
            throw new Exception('There was an error with trying to obtain Received Ryo Data!');
        }
        elseif($RTRData !== '0 rows') {
            if($GLOBALS['database']->execute_query("UPDATE `users`
                    INNER JOIN `ryo_track` ON (`ryo_track`.`r_uid` = `users`.`id`)
                SET `ryo_track`.`receiver` = '".$_POST['username']."'
                WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
                if($GLOBALS['database']->getErrorReported() === true) {
                    throw new Exception('There was an issue trying to clean up Received Ryo user information!');
                }
            }
        }
        unset($RTRData);


        if(!($RTSData = $GLOBALS['database']->fetch_data("SELECT `ryo_track`.`sender` FROM `users`
            INNER JOIN `ryo_track` ON (`ryo_track`.`s_uid` = `users`.`id`)
            WHERE `users`.`id` = '".$user[0]['uid']."' FOR UPDATE"))) {
            throw new Exception('There was an error with trying to obtain Sent Ryo Data!');
        }
        elseif($RTSData !== '0 rows') {
            if($GLOBALS['database']->execute_query("UPDATE `users`
                    INNER JOIN `ryo_track` ON (`ryo_track`.`s_uid` = `users`.`id`)
                SET `ryo_track`.`sender` = '".$_POST['username']."'
                WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
                if($GLOBALS['database']->getErrorReported() === true) {
                    throw new Exception('There was an issue trying to clean up Sent Ryo user information!');
                }
            }
        }
        unset($RTSData);


        if(!($ULData = $GLOBALS['database']->fetch_data("SELECT `unlock`.`username` FROM `users`
            INNER JOIN `unlock` ON (`unlock`.`uid` = `users`.`id`)
            WHERE `users`.`id` = '".$user[0]['uid']."' FOR UPDATE"))) {
            throw new Exception('There was an error with trying to obtain Unlock Data!');
        }
        elseif($ULData !== '0 rows') {
            if($GLOBALS['database']->execute_query("UPDATE `users`
                    INNER JOIN `unlock` ON (`unlock`.`uid` = `users`.`id`)
                SET `unlock`.`username` = '".$_POST['username']."'
                WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
                if($GLOBALS['database']->getErrorReported() === true) {
                    throw new Exception('There was an issue trying to clean up Unlock user information!');
                }
            }
        }
        unset($ULData);


        if(!($UNData = $GLOBALS['database']->fetch_data("SELECT `user_notes`.`user` FROM `users`
            INNER JOIN `user_notes` ON (`user_notes`.`user_id` = `users`.`id`)
            WHERE `users`.`id` = '".$user[0]['uid']."' FOR UPDATE"))) {
            throw new Exception('There was an error with trying to obtain User Note Data!');
        }
        elseif($UNData !== '0 rows') {
            if($GLOBALS['database']->execute_query("UPDATE `users`
                    INNER JOIN `user_notes` ON (`user_notes`.`user_id` = `users`.`id`)
                SET `user_notes`.`user` = '".$_POST['username']."'
                WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
                if($GLOBALS['database']->getErrorReported() === true) {
                    throw new Exception('There was an issue trying to clean up User Note user information!');
                }
            }
        }
        unset($UNData);


        if(!($MLData = $GLOBALS['database']->fetch_data("SELECT `moderator_log`.`username` FROM `users`
            INNER JOIN `moderator_log` ON (`moderator_log`.`uid` = `users`.`id`)
            WHERE `users`.`id` = '".$user[0]['uid']."' FOR UPDATE"))) {
            throw new Exception('There was an error with trying to obtain Moderator Record Data!');
        }
        elseif($MLData !== '0 rows') {
            if($GLOBALS['database']->execute_query("UPDATE `users`
                    INNER JOIN `moderator_log` ON (`moderator_log`.`uid` = `users`.`id`)
                SET `moderator_log`.`username` = '".$_POST['username']."'
                WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
                if($GLOBALS['database']->getErrorReported() === true) {
                    throw new Exception('There was an issue trying to clean up Moderator Record user information!');
                }
            }
        }
        unset($MLData);


        if(!($VLData = $GLOBALS['database']->fetch_data("SELECT `villages`.`leader` FROM `users`
            INNER JOIN `villages` ON (`villages`.`leader` = `users`.`username`)
            WHERE `users`.`id` = '".$user[0]['uid']."' FOR UPDATE"))) {
            throw new Exception('There was an error with trying to obtain Village Leader Data!');
        }
        elseif($VLData !== '0 rows') {
            if($GLOBALS['database']->execute_query("UPDATE `users`
                    INNER JOIN `villages` ON (`villages`.`leader` = `users`.`username`)
                SET `villages`.`leader` = '".$_POST['username']."'
                WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
                if($GLOBALS['database']->getErrorReported() === true) {
                    throw new Exception('There was an issue trying to clean up Village Leader user information!');
                }
            }
        }
        unset($VLData);


        if(!($TData = $GLOBALS['database']->fetch_data("SELECT `tavern`.`user` FROM `users`
            INNER JOIN `tavern` ON (`tavern`.`uid` = `users`.`id`)
            WHERE `users`.`id` = '".$user[0]['uid']."' FOR UPDATE"))) {
            throw new Exception('There was an error with trying to obtain Tavern Data!');
        }
        elseif($TData !== '0 rows') {
            if($GLOBALS['database']->execute_query("UPDATE `users`
                    INNER JOIN `tavern` ON (`tavern`.`uid` = `users`.`id`)
                SET `tavern`.`user` = '".$_POST['username']."'
                WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
                if($GLOBALS['database']->getErrorReported() === true) {
                    throw new Exception('There was an issue trying to clean up Tavern user information!');
                }
            }
        }
        unset($TData);


        if(!($TAData = $GLOBALS['database']->fetch_data("SELECT `tavern_anbu`.`user` FROM `users`
            INNER JOIN `tavern_anbu` ON (`tavern_anbu`.`uid` = `users`.`id`)
            WHERE `users`.`id` = '".$user[0]['uid']."' FOR UPDATE"))) {
            throw new Exception('There was an error with trying to obtain ANBU Tavern Data!');
        }
        elseif($TAData !== '0 rows') {
            if($GLOBALS['database']->execute_query("UPDATE `users`
                    INNER JOIN `tavern_anbu` ON (`tavern_anbu`.`uid` = `users`.`id`)
                SET `tavern_anbu`.`user` = '".$_POST['username']."'
                WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
                if($GLOBALS['database']->getErrorReported() === true) {
                    throw new Exception('There was an issue trying to clean up ANBU Tavern user information!');
                }
            }
        }
        unset($TAData);


        if(!($TCData = $GLOBALS['database']->fetch_data("SELECT `tavern_clan`.`user` FROM `users`
            INNER JOIN `tavern_clan` ON (`tavern_clan`.`uid` = `users`.`id`)
            WHERE `users`.`id` = '".$user[0]['uid']."' FOR UPDATE"))) {
            throw new Exception('There was an error with trying to obtain Clans Tavern Data!');
        }
        elseif($TCData !== '0 rows') {
            if($GLOBALS['database']->execute_query("UPDATE `users`
                    INNER JOIN `tavern_clan` ON (`tavern_clan`.`uid` = `users`.`id`)
                SET `tavern_clan`.`user` = '".$_POST['username']."'
                WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
                if($GLOBALS['database']->getErrorReported() === true) {
                    throw new Exception('There was an issue trying to clean up Clans Tavern user information!');
                }
            }
        }
        unset($TCData);


        if(!($TLData = $GLOBALS['database']->fetch_data("SELECT `tavern_leaders`.`user` FROM `users`
            INNER JOIN `tavern_leaders` ON (`tavern_leaders`.`uid` = `users`.`id`)
            WHERE `users`.`id` = '".$user[0]['uid']."' FOR UPDATE"))) {
            throw new Exception('There was an error with trying to obtain Leaders Tavern Data!');
        }
        elseif($TLData !== '0 rows') {
            if($GLOBALS['database']->execute_query("UPDATE `users`
                    INNER JOIN `tavern_leaders` ON (`tavern_leaders`.`uid` = `users`.`id`)
                SET `tavern_leaders`.`user` = '".$_POST['username']."'
                WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
                if($GLOBALS['database']->getErrorReported() === true) {
                    throw new Exception('There was an issue trying to clean up Leaders Tavern user information!');
                }
            }
        }
        unset($TLData);


        if(!($TMData = $GLOBALS['database']->fetch_data("SELECT `tavern_marriage`.`user` FROM `users`
            INNER JOIN `tavern_marriage` ON (`tavern_marriage`.`uid` = `users`.`id`)
            WHERE `users`.`id` = '".$user[0]['uid']."' FOR UPDATE"))) {
            throw new Exception('There was an error with trying to obtain Marriage Tavern Data!');
        }
        elseif($TMData !== '0 rows') {
            if($GLOBALS['database']->execute_query("UPDATE `users`
                    INNER JOIN `tavern_marriage` ON (`tavern_marriage`.`uid` = `users`.`id`)
                SET `tavern_marriage`.`user` = '".$_POST['username']."'
                WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
                if($GLOBALS['database']->getErrorReported() === true) {
                    throw new Exception('There was an issue trying to clean up Marriage Tavern user information!');
                }
            }
        }
        unset($TMData);

        if($GLOBALS['database']->execute_query("UPDATE `users`
                INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
            SET `users`.`username` = '".$_POST['username']."',
                `users`.`login_id` = '".(session_id().md5($_POST['username'].'xXx'))."',
                `users_statistics`.`nameChanges` = `users_statistics`.`nameChanges` - 1
            WHERE `users`.`id` = '".$user[0]['uid']."'") === false) {
            throw new Exception('There was an issue trying to update username and name changes!');
        }

        // Log namechanges
        if($GLOBALS['database']->execute_query("INSERT INTO `log_namechanges`
                (`uid`, `oldName`, `newName`, `time`, `request_ip`)
            VALUES
                ('" . $user[0]['uid'] . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" . $_POST['username'] . "',
                 '" . $GLOBALS['user']->load_time . "', '" . $GLOBALS['userdata'][0]['last_ip'] . "');") === false) {
            throw new Exception('Failed to log Username Change!');
        }

        // Instant update
        $GLOBALS['userdata'][0]['username'] = $_POST['username'];

        // Give message
        $GLOBALS['page']->Message("Your username has been changed to " . $_POST['username'] . "!", 'Username Change', 'id=' . $_GET['id'] . "&act=" . $_GET['act']);

        // Commit transaction
        $GLOBALS['database']->transaction_commit();
    }

    //  BLACKLIST / WHITELIST SETTINGS

    //  PM Settings
    private function blacklist_screen() {

        // Settings
        $settings = $GLOBALS['database']->fetch_data("SELECT `pm_setting`, `CFHsetting` FROM `users_preferences` WHERE `uid` = '" . $_SESSION['uid'] . "' LIMIT 1");
        $GLOBALS['template']->assign('settings', $settings);

        // Parse people on the blacklist
        $blacklisted = $GLOBALS['database']->fetch_data("SELECT `username` , `id` FROM `users` , `users_preferences` WHERE INSTR( `pm_blacklist` , CONCAT( ';', `id` , ';' ) ) AND `uid` = '" . $_SESSION['uid'] . "' ORDER BY `username` ASC ");
        $GLOBALS['template']->assign('blacklisted', $blacklisted);

        // Parse people on the whitelist
        $whitelisted = $GLOBALS['database']->fetch_data("SELECT `username` , `id` FROM `users` , `users_preferences` WHERE INSTR( `pm_whitelist` , CONCAT( ';', `id` , ';' ) ) AND `uid` = '" . $_SESSION['uid'] . "' ORDER BY `username` ASC ");
        $GLOBALS['template']->assign('whitelisted', $whitelisted);

        // Retrieve template to show all
        $GLOBALS['template']->assign('contentLoad', './templates/content/preferences/pref_blacklist.tpl');
    }

    // Update basic pm settings
    private function list_save_setting() {

        // Query
        $query = "";

        // Add pm setting
        if (isset($_POST['setting'])) {
            if ($_POST['setting'] == 'off' || $_POST['setting'] == 'white_only' || $_POST['setting'] == 'block_black' || $_POST['setting'] == 'block_black_tavern' || $_POST['setting'] == 'block_black_pm') {
                $query = " `pm_setting` = '" . $_POST['setting'] . "' ";
            }
        }

        // add cfh setting
        if (isset($_POST['CFHsetting'])) {
            if ($_POST['CFHsetting'] == 'CFHoff' || $_POST['CFHsetting'] == 'CFHwhite_only' || $_POST['CFHsetting'] == 'CFHblock_

') {
                $query .= !empty($query) ? ", `CFHsetting` = '" . $_POST['CFHsetting'] . "' " :  " `CFHsetting` = '" . $_POST['CFHsetting'] . "' ";
            }
        }

        // Run Query unless empty
        if( $GLOBALS['database']->execute_query("UPDATE `users_preferences` SET ".$query." WHERE `uid` = '" . $_SESSION['uid'] . "' LIMIT 1") ){

            // Give message
            $GLOBALS['page']->Message( "Basic settings have been updated" , 'Black / Whitelist Setting', 'id='.$_GET['id']."&act=".$_GET['act']);

        }
        else{
            throw new Exception("There was an error updating the user information to the database");
        }
    }

    // Add user to either list
    private function list_add_user() {

        // Get user preferences
        if (isset($_POST['listtype'])) {

            $settings = $GLOBALS['database']->fetch_data("SELECT `pm_blacklist`, `pm_whitelist` FROM `users_preferences` WHERE `uid` = '" . $_SESSION['uid'] . "' LIMIT 1");
            if( $settings !== "0 rows" ){

                // Get target information
                $user = $GLOBALS['database']->fetch_data("SELECT `id`,`user_rank`,`username` FROM `users`,`users_statistics` WHERE `username` = '" . addslashes($_POST['username']) . "' AND `uid` = `id` LIMIT 1");
                if ($user != '0 rows') {

                    // Check user rank
                    if ($user[0]['user_rank'] == 'Member' || $user[0]['user_rank'] == 'Paid' || $_POST['listtype'] == 'white') {

                        //  Check if user is already white or blacklisted
                        if (!stristr($settings[0]['pm_whitelist'], ';' . $user[0]['id'] . ';') && !stristr($settings[0]['pm_blacklist'], ';' . $user[0]['id'] . ';')) {

                            //  Check white or blacklist
                            if ($_POST['listtype'] == 'white') {
                                $column = 'pm_whitelist';
                            } else {
                                $column = 'pm_blacklist';
                            }

                            // Newlist
                            $settings[0][ $column ] .= !empty($settings[0][ $column ]) ?
                                    $user[0]['id'] . ";" :
                                    ";" . $user[0]['id'] .";";

                            // Update to database
                            if ($GLOBALS['database']->execute_query("UPDATE `users_preferences` SET `" . $column . "` = '".$settings[0][ $column ]."' WHERE `uid` = '" . $_SESSION['uid'] . "' LIMIT 1")) {

                                // Give message
                                $GLOBALS['page']->Message( "User has been added to your list" , 'Black / Whitelist Setting', 'id='.$_GET['id']."&act=".$_GET['act']);

                            } else {
                                throw new Exception("There was an error updating the database");
                            }
                        }
                        else{
                            throw new Exception("This user is already on your white or blacklist");
                        }
                    }
                    else{
                        throw new Exception("You cannot blacklist admins or mods.");
                    }
                }
                else{
                    throw new Exception("Could not find the user you're trying to add to your list.");
                }
            }
            else{
                throw new Exception("Could not find your current settings in the database");
            }
        }
        else {
            throw new Exception("You must specify which list you want to add this user to");
        }
    }

    // Remove users from list
    private function list_remove_user() {

        //  Fetch string
        $settings = $GLOBALS['database']->fetch_data("SELECT `pm_blacklist`, `pm_whitelist` FROM `users_preferences` WHERE `uid` = '" . $_SESSION['uid'] . "' LIMIT 1");

        //  Run through whitelist
        $i = 0;
        $whitelist = $settings[0]['pm_whitelist'];
        while ($i < count($_POST['whitelist'])) {
            if ($_POST['whitelist'][$i] != 'none') {
                $whitelist = str_replace(';' . $_POST['whitelist'][$i] . ';', ';', $whitelist);
            }
            $i++;
        }

        //  Run through black
        $i = 0;
        $blacklist = $settings[0]['pm_blacklist'];
        while ($i < count($_POST['blacklist'])) {
            if ($_POST['blacklist'][$i] != 'none') {
                $blacklist = str_replace(';' . $_POST['blacklist'][$i] . ';', ';', $blacklist);
            }
            $i++;
        }

        //  UPDATE white / blacklists in the database
        if ($GLOBALS['database']->execute_query("UPDATE `users_preferences` SET `pm_whitelist` = '" . $whitelist . "', `pm_blacklist`= '" . $blacklist . "' WHERE `uid` = '" . $_SESSION['uid'] . "' LIMIT 1")) {

            // Give message
            $GLOBALS['page']->Message( "Your black and whitelist have been updated" , 'Black / Whitelist Setting', 'id='.$_GET['id']."&act=".$_GET['act']);

        } else {
            throw new Exception("There was an error updating the database");
        }
    }

    // Student functions

    //  Student main form
    private function student_main() {

        // For non students
        $noStudent = array(
            "username" => "N/A",
            "detail" => "N/A",
            "action" => '<a href="?id='.$_GET['id'].'&act=studentadd">Add Student</a>'
        );

        // Create an array of students to show
        $students = array(
            0 => $noStudent,
            1 => $noStudent,
            2 => $noStudent
        );

        // Retrieve students
        $data = $GLOBALS['database']->fetch_data("
                    SELECT `username`, `id`
                    FROM `users`
                    WHERE `id` = '" . $this->user[0]['student_1'] . "' OR
                          `id` = '" . $this->user[0]['student_2'] . "' OR
                          `id` = '" . $this->user[0]['student_3'] . "'");

        // Create showing list for the users
        for( $i=0; $i<3; $i++ ){

            // Check if student is set or not
            if( $data !== "0 rows" && isset($data[$i]) ){

                // Get position of student
                $pos = null;
                switch( $data[$i]['id'] ){
                    case $this->user[0]['student_1']: $pos = 0; break;
                    case $this->user[0]['student_2']: $pos = 1; break;
                    case $this->user[0]['student_3']: $pos = 2; break;
                }

                // User was found
                $students[$pos] = $data[$i];
                $students[$pos]['detail'] = "<a href='?id=13&page=profile&name=".$data[$i]['username']."'>Details</a>";
                $students[$pos]['action'] = "<a href='?id=".$_GET['id']."&act=removeStudent&sid=".$data[$i]['id']."'>Remove Student</a>";

            }
        }

        // Show the list of students
        tableParser::show_list(
            'students',
            'Students',
            $students,
            array(
                'username' => "Student",
                'detail' => "Information",
                'action' => "Action"
            ),
            false ,
            true, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'As a '.$this->user[0]['rank']." you can help other users by taking them as students. Remember, a good teacher helps his students and teaches them how to play the game."
        );

        $GLOBALS['template']->assign("returnLink", true);
    }

    // Add user form
    private function student_add() {

        // Create the fields to be shown
        $inputFields = array(
            array("infoText"=>"Student username","inputFieldName"=>"username", "type" => "input", "inputFieldValue" => "")
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Search the username of the user you want to add as your student", // Information
            "Add Student", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] , "submitFieldName" => "Submit","submitFieldText" => "Add student"), // Submit button
            "Return" // Return link name
        );
    }

    // Add user do
    private function student_do_add() {

        // Check sent username
        if (isset($_POST['username']) && $_POST['username'] != '') {

            // Search in db
            $user = $GLOBALS['database']->fetch_data("SELECT `id`, `username`,`rank_id`,`sensei`,`village` FROM `users`, `users_statistics`,`users_preferences` WHERE `username` = '" . $_POST['username'] . "' AND `users_statistics`.`uid` = `users`.`id` AND `users_preferences`.`uid` = `users`.`id` LIMIT 1");
            if ($user != '0 rows') {

                // Check village
                if ($user[0]['village'] == $this->user[0]['village']) {

                    // Check rank
                    if ($user[0]['rank_id'] == 2) {

                        // Check sensei
                        if ($user[0]['sensei'] == '' ) {

                            // Get position
                            if ($this->user[0]['student_1'] == '_none' || $this->user[0]['student_1'] == "") {
                                $spot = 1;
                            } elseif ($this->user[0]['student_2'] == '_none' || $this->user[0]['student_2'] == "") {
                                $spot = 2;
                            } elseif ($this->user[0]['student_3'] == '_none' || $this->user[0]['student_3'] == "") {
                                $spot = 3;
                            } else {
                                $spot = 'none';
                            }

                            // Update
                            if ($spot != 'none') {
                                if ($GLOBALS['database']->execute_query("UPDATE `users_preferences` SET `sensei` = '" . $_SESSION['uid'] . "' WHERE (`sensei` IS NULL OR `sensei` = '') AND `uid` = '" . $user[0]['id'] . "' LIMIT 1")) {
                                    if( $GLOBALS['database']->execute_query("UPDATE `users` SET `student_" . $spot . "` = '" . $user[0]['id'] . "' WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1") ){

                                        // Give message
                                        $GLOBALS['page']->Message( "The user ".$user[0]['username']." has been added as your student" , 'Student System', 'id='.$_GET['id']."&act=student");

                                    }
                                    else{
                                        throw new Exception("There was a problem attaching your profile to the student");
                                    }
                                } else {
                                    throw new Exception("There was an error updating the userdata to the database");
                                }
                            }
                            else {
                                throw new Exception("You don't have room for any more students");
                            }

                        }
                        elseif( $user[0]['sensei'] == '_disabled' ){
                            throw new Exception("User does not want a sensei");
                        }
                        else {
                            throw new Exception("This user already has a sensei");
                        }
                    }
                    else {
                        throw new Exception("Your student must be genin-rank");
                    }
                }
                else {
                    throw new Exception("You must be in the same village as the user");
                }
            }
            else {
                throw new Exception("Could not find this user in the database");
            }
        } else {
            throw new Exception("You did not write any username");
        }
    }

    // Remove user form
    private function remove_student() {

        // Get students
        $data = $GLOBALS['database']->fetch_data("
                    SELECT `username`, `id`
                    FROM `users`
                    WHERE `id` = '" . $this->user[0]['student_1'] . "' OR
                          `id` = '" . $this->user[0]['student_2'] . "' OR
                          `id` = '" . $this->user[0]['student_3'] . "'");
        if( $data !== "0 rows" ){

            // Sanity of user
            if( isset($_GET['sid']) && is_numeric($_GET['sid']) && $_GET['sid'] > 0 ){

                // Go through the users
                foreach( $data as $student ){
                    if( $_GET['sid'] == $student['id'] ){

                        // Check if this user id is a student and get position
                        $pos = 0;
                        if( $this->user[0]['student_1'] == $_GET['sid'] ){
                            $pos = 1;
                        } elseif( $this->user[0]['student_2'] == $_GET['sid'] ){
                            $pos = 2;
                        } elseif( $this->user[0]['student_3'] == $_GET['sid'] ){
                            $pos = 3;
                        } else {
                            throw new Exception("This user is not your student");
                        }

                        // Update student
                        if (!$GLOBALS['database']->execute_query("UPDATE `users_preferences` SET `sensei` = NULL WHERE `uid` = '" . $_GET['sid'] . "' LIMIT 1")) {
                            throw new Exception("There was an error updating the student");
                        }

                        // Update user
                        if (!$GLOBALS['database']->execute_query("UPDATE `users` SET `student_" . $pos . "` = '_none' WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1")) {
                            throw new Exception("There was an error updating your userdata");
                        }

                        // Message
                        $GLOBALS['page']->Message( "The user ".$student['username']." has been removed from your student list" , 'Student System', 'id='.$_GET['id']."&act=student");

                        // Break loop
                        break;
                    }
                }
            }
            else{
                throw new Exception("Could not make sense of what student you're trying to remove");
            }
        }
        else{
            throw new Exception("You do not have any students to remove");
        }
    }

    // Show special signature to use on forums etc
    private function specialsig() {
        if (isset($_GET['action'])) {
            if ($_GET['action'] == "update") {
                if (($this->user[0]['dynamic_signature'] < ($GLOBALS['user']->load_time - 10800)) || ($this->user[0]['dynamic_signature'] == 0)) {
                    $this->create_image();
                    $GLOBALS['database']->execute_query("UPDATE `users_timer` SET `dynamic_signature` = '" . $GLOBALS['user']->load_time . "' WHERE `userid` = '" . $_SESSION['uid'] . "' LIMIT 1");
                    header("Location: ?id=4&act=specialsig");
                }
            }
        }
        $GLOBALS['template']->assign('user', $this->user);
        $GLOBALS['template']->assign('contentLoad', './templates/content/preferences/pref_special_sig.tpl');
    }

    // Special Signature script
    private function create_image() {
        // User data
        $message1 = 'Name: ' . $this->user[0]['username'] . '';
        $message2 = 'Rank: lvl. ' . $this->user[0]['level'] . ' ' . $this->user[0]['rank'] . '';
        if ($this->user[0]['village'] !== "") {
            $message3 = 'Village: ' . $this->user[0]['village'] . '';
        } else {
            $message3 = 'Village: N/A';
        }
        if ($this->user[0]['bloodline'] !== "") {
            $message4 = 'Bloodline: ' . $this->user[0]['bloodline'] . '';
        } else {
            $message4 = 'Bloodline: N/A';
        }
        $message5 = 'Battles Fought: ' . ($this->user[0]['battles_won'] + $this->user[0]['battles_lost'] + $this->user[0]['battles_fled'] + $this->user[0]['battles_draws']) . '';

        $im = imagecreatefromgif("./images/signatures/default3.gif") or die("Cannot Initialize new GD image stream");
        $text = imagecolorallocate($im, 0, 0, 0);      // text colour
        imagestring($im, 2, 225, 12, $message1, $text);
        imagestring($im, 2, 225, 25, $message2, $text);
        imagestring($im, 2, 225, 38, $message3, $text);
        imagestring($im, 2, 225, 50, $message4, $text);
        imagestring($im, 2, 225, 63, $message5, $text);
        imagegif($im, "./images/signatures/" . $_SESSION['uid'] . ".gif");
        imagedestroy($im);
    }

    // Reset account functions
    //
    // Form letting the user mark the account for deletion
    private function reset_form() {

        // Create the fields to be shown
        $inputFields = array(
            array("infoText"=>"Password","inputFieldName"=>"old_pass", "type" => "password", "inputFieldValue" => "")
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Are you sure you want to reset this account? 7 days after this your account will be reset and everything except popularity points & reputation points will be lost. You will also lose any bloodline on your account, and be able to re-roll once you get to Genin. You will <b>not</b> be able to re-roll the same bloodline that you have now again! Spent Reputation Points <b>are not</b> returned in this process! You <b>will</b> be assigned to a random village. You may cancel this at any time before the 7 days are up", // Information
            "Reset Your Account", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] , "submitFieldName" => "Submit","submitFieldText" => "Reset"), // Submit button
            "Return" // Return link name
        );
    }

    // Do mark the user for deletion
    private function set_reset_flag() {

        // Entered password
        $oldPass = functions::encryptPassword($_POST['old_pass'], $this->user[0]['join_date']);
        if( $oldPass == $this->user[0]['salted_password'] ){

            // Set timer
            $GLOBALS['database']->execute_query("UPDATE `users` SET `reset_timer` = '" . ($GLOBALS['user']->load_time + 604800) . "' WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");

            // Message
            $GLOBALS['page']->Message('You have marked your character for reset. Return to this page in 7 days to confirm the reset.', 'Reset Account', 'id=' . $_GET['id'] . '');
        }
        else{
            throw new Exception("The password does not match");
        }
    }

    // Form letting the user un-mark the account for deletion
    private function unreset_form() {

        // Set timer
        $time = $this->user[0]['reset_timer'] - $GLOBALS['user']->load_time;
        $timer = functions::convert_time($time, 'flagtime', 'false');

        // Create the fields to be shown
        $inputFields = array(
            array("infoText"=>"Password","inputFieldName"=>"old_pass", "type" => "password", "inputFieldValue" => "")
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Your account is currently flagged for reset you have <b>".$timer."</b> left to cancel this process.", // Information
            "Account Reset", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'] , "submitFieldName" => "Submit","submitFieldText" => "Cancel Reset"), // Submit button
            "Return" // Return link name
        );
    }

    // Do unmark the user for deletion
    private function unset_reset_flag() {

        // Entered password
        $oldPass = functions::encryptPassword($_POST['old_pass'], $this->user[0]['join_date']);
        if( $oldPass == $this->user[0]['salted_password'] ){

            // Set timer
            $GLOBALS['database']->execute_query("UPDATE `users` SET `reset_timer` = '0' WHERE `id` = '" . $_SESSION['uid'] . "' LIMIT 1");

            // Message
            $GLOBALS['page']->Message('Your account is no longer flagged for reset', 'Reset Account', 'id=' . $_GET['id'] . '');
        }
        else{
            throw new Exception("The password does not match");
        }
    }

    // Do reset user
    private function user_reset_account() { 

        $GLOBALS['database']->transaction_start();

        if(!($user = $GLOBALS['database']->fetch_data('
            SELECT
                `users`.`id`,
                `users`.`student_1`, `users`.`student_2`, `users`.`student_3`,
                `users`.`username`,
                `users_preferences`.`clan`,
                `users_preferences`.`anbu`,
                `users_preferences`.`sensei`,

                `lottery`.`id` AS `lottery_id`,
                `pass_request`.`p_id` AS `pass_id`,
                `spar_challenges`.`id` AS `spar_id`,
                `users_inventory`.`iid` AS `item_id`,
                `home_inventory`.`iid` AS `home_id`,
                `home_inventory`.`fid` AS `furn_id`,
                (Select count(`qid`) from `users_quests` where `uid` = '.$_SESSION['uid'].') AS `quest_count`,
                `users_jutsu`.`jid` AS `jutsu_id`,
                `marriages`.`mid` AS `marriage_id`,
                `trades`.`id` AS `trade_id`,

                `villages`.`leader` AS `village_leader`
            FROM `users`
                LEFT JOIN `bingo_book` ON (`bingo_book`.`userID` = `users`.`id`)
                LEFT JOIN `lottery` ON (`lottery`.`userid` = `users`.`id`)
                LEFT JOIN `pass_request` ON (`pass_request`.`uid` = `users`.`id`)
                LEFT JOIN `spar_challenges` ON (`spar_challenges`.`uid` = `users`.`id` OR `spar_challenges`.`oid` = `users`.`id`)
                LEFT JOIN `marriages` ON (`marriages`.`uid` = `users`.`id` OR `marriages`.`oid` = `users`.`id`)
                LEFT JOIN `users_inventory` ON (`users_inventory`.`uid` = `users`.`id`)
                LEFT JOIN `home_inventory` ON (`home_inventory`.`uid` = `users`.`id`)
                LEFT JOIN `users_jutsu` ON (`users_jutsu`.`uid` = `users`.`id`)
                LEFT JOIN `trades` ON (`trades`.`uid` = `users`.`id`)
                LEFT JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                LEFT JOIN `users_missions` ON (`users_missions`.`userid` = `users`.`id`)
                LEFT JOIN `users_occupations` ON (`users_occupations`.`userid` = `users`.`id`)
                LEFT JOIN `users_preferences` ON (`users_preferences`.`uid` = `users`.`id`)
                LEFT JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                LEFT JOIN `users_timer` ON (`users_timer`.`userID` = `users`.`id`)
                LEFT JOIN `villages` ON (`villages`.`name` = `users_loyalty`.`village`)
                LEFT JOIN `votes` ON (`votes`.`userID` = `users`.`id`)
            WHERE `users`.`id` = '.$_SESSION['uid'].' AND
                `users`.`status` IN ("awake", "asleep") LIMIT 1 FOR UPDATE')))
        {
            throw new Exception('User must be awake or asleep for reset to occur!');
        }

        // Reset the students of the sensei
        foreach( array("student_1","student_2","student_3") as $sPos ){
            if ( isset($user[0][$sPos]) && $user[0][$sPos] != '_none') {
                $GLOBALS['database']->execute_query("UPDATE `users_preferences` SET `sensei` = '' WHERE `uid` = '" . $user[0][$sPos] . "' LIMIT 1");
            }
        }

        // If user has sensei, remove him as student form the sensei
        if( !empty($user[0]["sensei"]) && $user[0]['sensei'] != "_disabled" ){

            // Get sensei data
            $sensei = $GLOBALS['database']->fetch_data('
            SELECT `users`.`id`,
                   `users`.`student_1`,`users`.`student_2`,`users`.`student_3`,
                   `users`.`username`
            FROM `users`
            WHERE `users`.`id` = '.$user[0]["sensei"].'
            LIMIT 1');
            if( $sensei !== "0 rows" ){
                foreach( array("student_1","student_2","student_3") as $sPos ){
                    if ($sensei[0][$sPos] == $user[0]['id'] ) {
                        $GLOBALS['database']->execute_query("UPDATE `users` SET `".$sPos."` = '' WHERE `id` = '" . $sensei[0]['id'] . "' LIMIT 1");
                    }
                }
            }
        }

        // If the user is coleader/leader in clan, he cannot reset
        if( isset($user[0]['clan']) ){
            require_once(Data::$absSvrPath.'/libs/villageSystem/clanLib.php');
            $clanLib = new clanLib();
            if( $clanLib->isUserClan( $user[0]['clan'] ) ){

                // Get clan information & check for leader
                $clanLib->clan = $clanLib->getClan( $user[0]['clan'] );
                if( $clanLib->isCoLeader() || $clanLib->isLeader() ){
                    throw new Exception("You cannot reset your account as long as you are part of a clan");
                }
            }
        }


        // If the user is in anbu, he cannot reset
        require_once(Data::$absSvrPath.'/libs/villageSystem/anbuLib.php');
        $anbuLib = new anbuLib();
        if( $anbuLib->isUserAnbu( $user[0]['anbu'] ) ){
            throw new Exception("You cannot reset your account as long as you are part of an ANBU squad");
        }

        // If the user is in leader, he cannot reset
        if($user[0]['village_leader'] === $user[0]['username']) {
            throw new Exception("You cannot reset your account as long as you hold a leader position");
        }

        // Reset log
        $resetThings = array("Mission data", "User Statistics", "User Timers", "Bingo Book data", "Votes", "Loyalties");

        // Remove lottery tickets
        if($user[0]['lottery_id'] !== null) {
            $resetThings[] = "Lottery";
            if(($GLOBALS['database']->execute_query('DELETE FROM `lottery`
                WHERE `lottery`.`userid` = '.$user[0]['id'])) === false)
            {
                throw new Exception('There was an error trying to remove Lottery information!');
            }
        }

        // Remove password requests
        if($user[0]['pass_id'] !== null) {
            $resetThings[] = "Password Requests";
            if(($GLOBALS['database']->execute_query('DELETE FROM `pass_request`
                WHERE `pass_request`.`uid` = '.$user[0]['id'])) === false)
            {
                throw new Exception('There was an error trying to remove Password Request information!');
            }
        }

        // Remove spar challenges
        if($user[0]['spar_id'] !== null) {
            $resetThings[] = "Spar Challenges";
            if(($GLOBALS['database']->execute_query('DELETE FROM `spar_challenges`
                WHERE (`spar_challenges`.`uid` = '.$user[0]['id'].'
                    OR `spar_challenges`.`oid` = '.$user[0]['id'].')')) === false)
            {
                throw new Exception('There was an error trying to remove Spar Challenge information!');
            }
        }

        // Not if married. Has to deal with marriage homes etc.
        if($user[0]['marriage_id'] !== null) {
            throw new Exception('You cannot reset your character as long as you\'re married! Please inform your spouse first!');
        }

        // Remove inventory items
        if($user[0]['item_id'] !== null) {
            $resetThings[] = "Inventory";
            if(($GLOBALS['database']->execute_query('DELETE FROM `users_inventory`
                WHERE `users_inventory`.`uid` = '.$user[0]['id'])) === false)
            {
                throw new Exception('There was an error trying to remove Inventory information!');
            }
        }

        //Remove home items
        if($user[0]['home_id'] !== null || $user[0]['furn_id'] !== null) {
            $resetThings[] = "Inventory";
            if(($GLOBALS['database']->execute_query('DELETE FROM `home_inventory`
                WHERE `home_inventory`.`uid` = '.$user[0]['id'])) === false)
            {
                throw new Exception('There was an error trying to remove Inventory information!');
            }
        }

        // Remove jutsus
        if($user[0]['jutsu_id'] !== null) {
            $resetThings[] = "Jutsus";
            if(($GLOBALS['database']->execute_query('DELETE FROM `users_jutsu`
                WHERE `users_jutsu`.`uid` = '.$user[0]['id'])) === false)
            {
                throw new Exception('There was an error trying to remove Jutsu information!');
            }
        }

        // Remove trades
        if( $user[0]['trade_id'] !== null ){

            $resetThings[] = "Trades & Offers";

            // Remove trades
            if(($GLOBALS['database']->execute_query('DELETE FROM `trades`
                WHERE `trades`.`uid` = '.$user[0]['id'])) === false)
            {
                throw new Exception('There was an error trying to remove trades information!');
            }

            // Remove offers without trade
            $GLOBALS['database']->execute_query("DELETE `trade_offers`.*
                                            FROM `trade_offers`
                                            LEFT JOIN `trades` ON `trades`.`id` = `trade_offers`.`tid`
                                            WHERE `trades`.`id` is null");
        }

        //remove all quests
        if( $user[0]['quest_count'] > 0 ){

            $resetThings[] = "Quests";

            if(($GLOBALS['database']->execute_query('DELETE FROM `users_quests`
                WHERE `users_quests`.`uid` = '.$user[0]['id'])) === false)
            {
                throw new Exception('There was an error trying to remove your quests!');
            }

        }


        // Reset general stuff
        if( $this->standard_user_reset( $user[0]['id'] ) === false ){
            throw new Exception("There was an error updating general information on this user");
        }

        // Show message
        $GLOBALS['page']->Message("Congrats, you've reset your account! Following information has been wiped:".implode(", ",$resetThings), 'Account Reset', 'id='.$_GET['id']);

        // Commit transaction
        $GLOBALS['database']->transaction_commit();

    }

    // Update reset function
    private function standard_user_reset($uid) {

        // Random new village
        $newVillage = Data::$VILLAGES[ random_int(1,5) ];
        $village_data = $GLOBALS['database']->fetch_data("SELECT * FROM `villages` WHERE `name` = '" . $newVillage . "' AND `registration_choice` = 'yes' LIMIT 1");


        // Random new clan
        $clan_data = $GLOBALS['database']->fetch_data("
             SELECT * FROM `clans`
             WHERE
                `village` = '" . $newVillage . "' AND
                `clan_type` = 'core'
             ORDER BY RAND()
             LIMIT 1");


        $query = 'UPDATE
            `bingo_book`, `users`,
            `users_loyalty`, `users_missions`, `users_occupations`,
            `users_statistics`, `users_timer`,
            `users_preferences`

            SET `bingo_book`.`Konoki` = DEFAULT, `bingo_book`.`Samui` = DEFAULT, `bingo_book`.`Silence` = DEFAULT,
                `bingo_book`.`Shine` = DEFAULT, `bingo_book`.`Shroud` = DEFAULT, `bingo_book`.`Syndicate` = DEFAULT,

                `users_preferences`.`clan` = "'.$clan_data[0]['id'].'",
                `users_preferences`.`sensei` = DEFAULT,
                `users_preferences`.`anbu` = DEFAULT,

                `users`.`battle_id` = DEFAULT,
                `users`.`status` = "awake",
                `users`.`student_1` = DEFAULT, `users`.`student_2` = DEFAULT, `users`.`student_3` = DEFAULT,
                `users`.`notifications` = DEFAULT, `users`.`login_id` = DEFAULT, `users`.`ryoCheckLimit` = DEFAULT, `users`.`logout_timer` = '.$GLOBALS['user']->load_time.',
                `users`.`ban_time` = DEFAULT, `users`.`tban_time` = DEFAULT, `users`.`deletion_timer` = DEFAULT,
                `users`.`reset_timer` = DEFAULT, `users`.`immunity` = DEFAULT, `users`.`apartment` = DEFAULT,
                `users`.`village` = "'.$newVillage.'",
                `users`.`location` = "' . $village_data[0]['name'] . '",
                `users`.`latitude` = "' . $village_data[0]['latitude'] . '",
                `users`.`longitude` = "' . $village_data[0]['longitude'] . '",
                `users`.`bloodline` = DEFAULT,

                `users_loyalty`.`time_in_vil` = '.$GLOBALS['user']->load_time.', `users_loyalty`.`vil_pts_timer` = DEFAULT,
                `users_loyalty`.`vil_loyal_pts` = DEFAULT,
                `users_loyalty`.`village` = "'.$newVillage.'",

                `users_missions`.`a_crime` = DEFAULT, `users_missions`.`b_crime` = DEFAULT,
                `users_missions`.`c_crime` = DEFAULT, `users_missions`.`s_mission` = DEFAULT,
                `users_missions`.`a_mission` = DEFAULT, `users_missions`.`b_mission` = DEFAULT,
                `users_missions`.`c_mission` = DEFAULT, `users_missions`.`d_mission` = DEFAULT,
                `users_missions`.`tasks` = DEFAULT, `users_missions`.`mission_monthly` = DEFAULT,
                `users_missions`.`battles_lost` = DEFAULT, `users_missions`.`battles_won` = DEFAULT,
                `users_missions`.`battles_fled` = DEFAULT, `users_missions`.`battles_draws` = DEFAULT,
                `users_missions`.`errands` = DEFAULT,
                `users_missions`.`scrimes` = DEFAULT, `users_missions`.`clan_activity` = DEFAULT,
                `users_missions`.`arrested` = DEFAULT, `users_missions`.`torn_record` = DEFAULT,
                `users_missions`.`AIwon` = DEFAULT, `users_missions`.`AIlost` = DEFAULT,
                `users_missions`.`AIfled` = DEFAULT, `users_missions`.`AIdraw` = DEFAULT,
                `users_missions`.`last_supermission` = DEFAULT, `users_missions`.`structureDestructionPoints` = DEFAULT,
                `users_missions`.`structureGatherPoints` = DEFAULT, `users_missions`.`structurePointsActivity` = DEFAULT,

                `users_occupations`.`profession` = DEFAULT, 
                `users_occupations`.`occupation` = DEFAULT, `users_occupations`.`special_occupation` = DEFAULT,
                `users_occupations`.`level` = DEFAULT, `users_occupations`.`promotion` = DEFAULT,
                `users_occupations`.`last_gain` = DEFAULT, `users_occupations`.`collect_count` = DEFAULT,
                `users_occupations`.`feature` = DEFAULT, `users_occupations`.`surgeonSP_exp` = DEFAULT,
                `users_occupations`.`surgeonCP_exp` = DEFAULT, `users_occupations`.`bountyHunter_exp` = DEFAULT,
                `users_occupations`.`profession_exp` = DEFAULT,

                `users_statistics`.`rank` = DEFAULT, `users_statistics`.`rank_id` = DEFAULT,
                `users_statistics`.`level` = DEFAULT, `users_statistics`.`level_id` = DEFAULT,
                `users_statistics`.`experience` = DEFAULT, `users_statistics`.`pvp_experience` = DEFAULT,
                `users_statistics`.`cur_health` = DEFAULT, `users_statistics`.`pvp_streak` = DEFAULT,
                `users_statistics`.`cur_sta` = DEFAULT, `users_statistics`.`cur_cha` = DEFAULT,
                `users_statistics`.`max_health` = DEFAULT, `users_statistics`.`max_sta` = DEFAULT,
                `users_statistics`.`max_cha` = DEFAULT, `users_statistics`.`regen_rate` = DEFAULT,
                `users_statistics`.`regen_bonus` = DEFAULT, `users_statistics`.`money` = DEFAULT,
                `users_statistics`.`bank` = DEFAULT, `users_statistics`.`tai_off` = DEFAULT,
                `users_statistics`.`tai_def` = DEFAULT, `users_statistics`.`nin_off` = DEFAULT,
                `users_statistics`.`nin_def` = DEFAULT, `users_statistics`.`gen_off` = DEFAULT,
                `users_statistics`.`gen_def` = DEFAULT, `users_statistics`.`weap_off` = DEFAULT,
                `users_statistics`.`weap_def` = DEFAULT, `users_statistics`.`intelligence` = DEFAULT,
                `users_statistics`.`willpower` = DEFAULT, `users_statistics`.`strength` = DEFAULT,
                `users_statistics`.`speed` = DEFAULT, `users_statistics`.`specialization` = DEFAULT,
                `users_statistics`.`strengthFactor` = DEFAULT, `users_statistics`.`element_affinity_1` = DEFAULT,
                `users_statistics`.`element_affinity_2` = DEFAULT, `users_statistics`.`bloodline_affinity_1` = DEFAULT,
                `users_statistics`.`bloodline_affinity_2` = DEFAULT, `users_statistics`.`bloodline_affinity_special` = DEFAULT,
                `users_statistics`.`element_mastery_1` = DEFAULT, `users_statistics`.`element_mastery_2` = DEFAULT,

                `users_timer`.`last_activity` = '.$GLOBALS['user']->load_time.', `users_timer`.`last_battle` = '.$GLOBALS['user']->load_time.',
                `users_timer`.`last_regen` = '.$GLOBALS['user']->load_time.', `users_timer`.`last_login` = '.$GLOBALS['user']->load_time.',
                `users_timer`.`jutsu` = DEFAULT, `users_timer`.`jutsu_timer` = DEFAULT,
                `users_timer`.`hospital_timer` = DEFAULT,
                `users_timer`.`jail_timer` = DEFAULT, `users_timer`.`regen_cooldown` = DEFAULT,
                `users_timer`.`cooldown` = DEFAULT, `users_timer`.`battle_colldown` = DEFAULT,
                `users_timer`.`dynamic_signature` = DEFAULT, `users_statistics`.`reinforcements` = DEFAULT,
                `users_timer`.`read_blue_msg` = DEFAULT

            WHERE `bingo_book`.`userID` = '.$uid.' AND
                `users`.`id` = `bingo_book`.`userID` AND
                `users_loyalty`.`uid` = `bingo_book`.`userID` AND
                `users_missions`.`userid` = `bingo_book`.`userID` AND
                `users_occupations`.`userid` = `bingo_book`.`userID` AND
                `users_preferences`.`uid` = `bingo_book`.`userID` AND
                `users_statistics`.`uid` = `bingo_book`.`userID` AND
                `users_timer`.`userid` = `bingo_book`.`userID`';

        $result = $GLOBALS['database']->execute_query($query);

        if($result === false)
            throw new Exception($query);
        else
            return $result;

        //clear cache items
        require_once(Data::$absSvrPath.'/libs/professionSystem/OccupationData.php');
        require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
        OccupationData::dumpCache();
        Elements::dumpCache();

        $GLOBALS['NOTIFICATIONS']->addNotification(array(
                                                        'id' => 10,
                                                        'duration' => 'none',
                                                        'text' => "Your account has recently been reset!",
                                                        'dismiss' => 'no'
                                                        ));
    }

}

new userpref();