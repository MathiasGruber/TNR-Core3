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

    class modPage {

        public $defaultPage;
        public $recordPage;
        public $jumpPage; 
        public $trackPage;


        public function __construct() {
            $this->visible_content = true;
            $this->userModuleID = $this->defaultPage = $this->recordPage = $this->jumpPage = $this->trackPage = 0;
        }

        public function load_content() {

            // Decide what to load
            if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
                $id = $_GET['id'];
            } else {
                $id = $this->defaultPage;
                $_GET['id'] = $id;
            }

            // Create and send hash for jQuery ajax call
            $GLOBALS['template']->assign("pageToken", functions::getToken());

            // Only show all for real admins
            $name = $this->modules[$id][1];

            if (in_array($GLOBALS['userdata'][0]['user_rank'], array('Admin', 'Supermod'), true) // Admins and Head Mods See Everything
                || ($GLOBALS['userdata'][0]['user_rank'] == "Moderator" && $name[0] === "moderator") // Moderators see Moderator Things
                || $name[1] === 'notes') { // Everyone sees Notes

                if (isset($this->modules[$id][0]) && file_exists(Data::$absSvrPath.'/'.$this->modules[$id][3] . $this->modules[$id][0])) {
                    //	File found, include it
                    require_once(Data::$absSvrPath.'/'.$this->modules[$id][3] . $this->modules[$id][0]);
                } 
                else {
                    $GLOBALS['page']->Message("This module could not be found.", 'Page Error', 'id=' . $_GET['id']);
                }
            } 
            else {
                 $GLOBALS['page']->Message("You are not allowed to view this section of the admin panel..", 'Page Error', 'id=' . $_GET['id']);
            }
        }

        public function Message($message, $title = "System Message", $returnLink = false, $returnLabel = "Return") {
            $GLOBALS['template']->assign('msg', $message);
            $GLOBALS['template']->assign('subHeader', $title);
            if ($returnLink !== false) {
                $GLOBALS['template']->assign('returnLink', $returnLink);
                $GLOBALS['template']->assign('returnLabel', $returnLabel);
            }
            $GLOBALS['template']->assign('contentLoad', './templates/message.tpl');
        }

        public function Confirm($message, $title = "System Message", $returnTitle) {
            $GLOBALS['template']->assign('msg', $message);
            $GLOBALS['template']->assign('subHeader', $title);
            $GLOBALS['template']->assign('returnLink', $returnTitle);
            $GLOBALS['template']->assign('contentLoad', './templates/confirm.tpl');
        }

        // Create a page wrapper, where content can be included in the mainScreen smarty variable
        // and a javascript library is included on the top of the page. Used for pages that support
        // backends
        public function createPageWrapper($javascriptFile) {

            // Take whatever is in the contentLoad variable and assign it to the mainScreen variable instead
            $GLOBALS['template']->assign("mainScreen", $GLOBALS['template']->tpl_vars['contentLoad']->value); 

            // Assign data to the wrapper
            $GLOBALS['template']->assign("scriptFile", $javascriptFile); 
            $GLOBALS['template']->assign("contentLoad",'./templates/pageWrapper.tpl'); 
        }

        // Request information from user. InputFields is an array with entries on the following form:
        // array("infoText"=>"Input Text Here","inputFieldName"=>"UserName")
        //
        // Form data must contain the following
        // array("href"=>"Link","submitFieldName"=>"postUserName", "submitFieldText"=>"Search User")
        public function UserInput($message, $title, $inputFields, $formData, $returnTitle, $formID = "autoForm", $inputType = "post", $saveVar="contentLoad") {
            $GLOBALS['template']->assign('inputMsg', $message);
            $GLOBALS['template']->assign('inputsubHeader', $title);
            $GLOBALS['template']->assign('inputFields', $inputFields);
            $GLOBALS['template']->assign('formData', $formData);
            $GLOBALS['template']->assign('formID', $formID);
            $GLOBALS['template']->assign('formInputType', $inputType);
            if( $returnTitle ){
                $GLOBALS['template']->assign('returnLink', $returnTitle);
            }

            if(isset($GLOBALS['mf']) && $GLOBALS['mf'] == 'yes')
            {
                $GLOBALS['template']->assign($saveVar, './templates/input_mf.tpl');
            }
            else
            {
                $GLOBALS['template']->assign($saveVar, './templates/input.tpl');
            }
        }

        public function load_modules() {

            // Load local modules
            $dir = './modules';
            $dirHandle = opendir($dir);
            $i = 0;
            $fileList = array();
            while (false !== ($file = readdir($dirHandle))) {
                if ($file != '.htaccess' && $file != '.' && $file != '..') {
                    $fileList[] = $file;
                    $i++;
                }
            }

            // Sort the files
            $fileList = functions::mergesort($fileList);

            // Add to modules
            for($i = 0, $size = count($fileList); $i < $size; $i++) {

                $this->modules[$i] = array($fileList[$i], explode('.', $fileList[$i]), $i, "panel_moderator/modules/");

                switch($fileList[$i]) {
                    case("moderator.notes.class.php"): $this->defaultPage = $i; break;
                    case("moderator.check_user_record.php"): $this->recordPage = $i; break;
                    case("moderator.jump_village.php"): $this->jumpPage = $i; break;
                    case("supermod.track_staff.php"): $this->trackPage = $i; break;
                }
            }

            // Send to menu
            $GLOBALS['template']->assign('menu', $this->modules);
        }

        public function load_menu() {
            $GLOBALS['template']->assign('menuLoad', './panel_moderator/files/menu.tpl');
        }

        public function parse_layout() {
            $GLOBALS['template']->display("./files/layout.tpl");
        }

        public function getModData($modID = false) {

            // ID to search
            $uid = (($modID === false) ? $_SESSION['uid'] : $modID);

            // Moderator data
            if(!($this->user = $GLOBALS['database']->fetch_data('SELECT `users`.`username`, `users`.`village`, 
                `users`.`id`, `users_statistics`.`user_rank`, `user_notes`.`message`
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id` 
                        AND `users_statistics`.`user_rank` IN ("Moderator", "Supermod", "Admin"))
                    LEFT JOIN `user_notes` ON (`user_notes`.`user_id` = 0)
                WHERE `users`.`id` = '.$uid.' LIMIT 1'))) {
                throw new Exception('There was an issue trying to obtain staff data!');
            }
            elseif($this->user === '0 rows') {
                throw new Exception('This staff member no longer exists in the system!');
            }

            // Villages
            $this->villages = Data::$VILLAGES;

            // Months
            $this->months = Data::$MONTHS;

            $this->banLengths = array();

            // Ban Options
            foreach(array('minute', 'hour', 'day', 'week', 'month', 'permanent') as $key) {
                switch($key) {
                    case('minute'): {
                        $options = ($this->user[0]['user_rank'] === 'Admin') ? range(10, 30, 10) : array(30);
                        foreach($options as $val) {
                            $this->banLengths[$val.$key.'s'] = $val . ' ' . ucfirst($key) . 's';
                        }
                    } break;
                    case('hour'): {
                        $options = ($this->user[0]['user_rank'] === 'Admin') ? range(1, 12, 1) : array(1, 12);
                        foreach($options as $val) {
                            if($val === 1) {
                                $this->banLengths[$val.$key] = $val . ' ' . ucfirst($key);
                            }
                            else {
                                $this->banLengths[$val.$key.'s'] = $val . ' ' . ucfirst($key) . 's';
                            }
                        }
                    } break;
                    case('day'): {
                        $options = ($this->user[0]['user_rank'] === 'Admin') ? range(1, 5, 1) : array(1, 3, 5);
                        foreach($options as $val) {
                            if($val === 1) {
                                $this->banLengths[$val.$key] = $val . ' ' . ucfirst($key);
                            }
                            else {
                                $this->banLengths[$val.$key.'s'] = $val . ' ' . ucfirst($key) . 's';
                            }
                        }
                    } break;
                    case('week'): {
                        foreach(range(1, 3, 1) as $val) {
                            if($val === 1) {
                                $this->banLengths[$val.$key] = $val . ' ' . ucfirst($key);
                            }
                            else {
                                $this->banLengths[$val.$key.'s'] = $val . ' ' . ucfirst($key) . 's';
                            }
                        }
                    } break;
                    case('month'): {
                        $options = ($this->user[0]['user_rank'] === 'Admin') ? range(1, 6, 1) : array(1);
                        foreach($options as $val) {
                            if($val === 1) {
                                $this->banLengths[$val.$key] = $val . ' ' . ucfirst($key);
                            }
                            else {
                                $this->banLengths[$val.$key.'s'] = $val . ' ' . ucfirst($key) . 's';
                            }
                        }
                    } break;
                    case('permanent'): $this->banLengths[$key] = ucfirst($key); break;
                }
            }
        }

        public function calcBanTime( $banString , $fromTime = false ) {

            // We use 1337 to signify permanent ban times
            if($banString === 'Permanent') { 
                return 1337;                 
            }
            
            // Set the from time. If unspecified, calculate from now
            $fromTime = ($fromTime == false) ? time() : $fromTime;

            // Check the ban string. This could be "30 Minutes" or the likes
            $banString = explode(' ', $banString);
            switch($banString[1]) {
                case('Minute'): case('Minutes'): { 
                    if(in_array($banString[0], range(10, 30, 10))) {
                        $seconds = $fromTime + $banString[0] * 60; 
                    }
                    else {
                        $seconds = $fromTime + 30 * 60;
                    }
                } break;
                case('Hour'): case('Hours'): {
                    if(in_array($banString[0], range(1, 12, 1))) {
                        $seconds = $fromTime + $banString[0] * 3600; 
                    }
                    else {
                        $seconds = $fromTime + 12 * 3600;
                     }
                } break;
                case('Day'): case('Days'): {
                    if(in_array($banString[0], range(1, 5, 1))) {
                        $seconds = $fromTime + $banString[0] * 24 * 3600;
                    }
                    else {
                        $seconds = $fromTime + 5 * 24 * 3600;
                    }
                } break;
                case('Week'): case('Weeks'): {
                    if(in_array($banString[0], range(1, 3, 1))) {
                        $seconds = $fromTime + $banString[0] * 7 * 24 * 3600; 
                    }
                    else {
                        $seconds = $fromTime + 3 * 7 * 24 * 3600;
                    }
                } break;
                case('Month'): case('Months'): { 
                    if(in_array($banString[0], range(1, 6, 1))) {
                        $seconds = $fromTime + $banString[0] * 30 * 24 * 3600; 
                    }
                    else { // 6 Months Max
                        $seconds = $fromTime + 6 * 30 * 24 * 3600;
                    }
                } break;
                default: $seconds = 0; break;   
            }

            return $seconds;
        }

        public function log_moderator_action( $time, $uid, $username, $duration, $moderatorName, $action, $reason, $message ){
           if($GLOBALS['database']->execute_query("INSERT INTO `moderator_log` 
                    (`time`, `uid`, `username`, `duration`, `moderator`, `action`, `reason`, `message`) 
                VALUES 
                    ('" . $time . "', '" . $uid . "', '" . $username . "', 
                     '" . $duration . "', '" . $moderatorName . "', '" . $action . "', 
                     '" . $reason . "', '" . functions::store_content($message) . "');") === false) {
               throw new Exception('Failed to Log Moderator Action!');
            }
        }

        public function log_for_admins( $time, $uid, $username, $message ){
            if($GLOBALS['database']->execute_query('INSERT INTO `admin_edits` 
                    (`time` , `aid`, `uid`, `changes`, `IP`) 
                VALUES 
                    ("' . $time . '", "' . $username . '", "' . $uid . '", 
                        "' . $message . '", "' . $GLOBALS['user']->real_ip_address() . '")') === false) {
                throw new Exception('Failed to Log Admin Edit Change!');
            }
        }

        // New Head Mod Functions, Core 3
        // $message, $title, $inputFields, $formData, $returnTitle, $formID = "autoForm", $inputType = "post", $saveVar="contentLoad"
        public function searchUsername() {

             // Create the fields to be shown
            $inputFields = array(
                array(
                    "infoText" => "Search Username", 
                    "inputFieldName" => "username", 
                    "type" => "input", 
                    "inputFieldValue" => ""
                ),
                array(
                    "infoText" => "Search User ID", 
                    "inputFieldName" => "userid", 
                    "type" => "input", 
                    "inputFieldValue" => ""
                )
            );

            // Show user prompt
            self::UserInput(
                "Use the search fields below to find the user in question", // Information
                "Search System", // Title
                $inputFields, // input fields
                array(
                    "href" => "?id=" . $_GET['id'], 
                    "submitFieldName" => "SearchUser",
                    "submitFieldText" => "Search"
                ), // Submit button
                false, // Return title
                "userSearchForm",
                "post",
                "searchBox"
            );
        }
    }