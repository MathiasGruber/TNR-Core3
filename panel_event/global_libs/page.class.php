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

class page {

    function __construct() {
        $this->visible_content = true;
        $this->userModuleID = 0;
        $this->load_time = time();
    }

    function load_content() {

        // Decide what to load
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $id = $_GET['id'];
        } else {
            $id = $this->defaultPage;
            $_GET['id'] = $id;
        }

        // Only show all for real admins
        $name = $this->modules[$id][1];
        if (
            ($GLOBALS['userdata'][0]['user_rank'] == "Admin" ||
             $GLOBALS['userdata'][0]['user_rank'] == 'Event' ||
             $GLOBALS['userdata'][0]['user_rank'] == 'EventMod' ||
             $GLOBALS['userdata'][0]['user_rank'] == 'ContentAdmin')
        ) {

            if (isset($this->modules[$id][0]) && file_exists(Data::$absSvrPath.'/panel_event/modules/' . $this->modules[$id][0])) {
                //	File found, include it
                require_once(Data::$absSvrPath.'/panel_event/modules/' . $this->modules[$id][0]);
            } else {
                $GLOBALS['page']->Message("This module could not be found.", 'Page Error', 'id=' . $_GET['id']);
            }
        } else {
             $GLOBALS['page']->Message("You are not allowed to view this section of the admin panel..", 'Page Error', 'id=' . $_GET['id']);
        }
    }

    function Message($message, $title = "System Message", $returnLink = false, $returnLabel = "Return") {
        $GLOBALS['template']->assign('msg', $message);
        $GLOBALS['template']->assign('subHeader', $title);
        $GLOBALS['template']->assign('returnLabel', $returnLabel);
        if ($returnLink !== false) {
            $GLOBALS['template']->assign('returnLink', $returnLink);
        }
        $GLOBALS['template']->assign('contentLoad', './templates/message.tpl');
    }

    function Confirm($message, $title = "System Message", $returnTitle) {
        $GLOBALS['template']->assign('msg', $message);
        $GLOBALS['template']->assign('subHeader', $title);
        $GLOBALS['template']->assign('returnLink', $returnTitle);
        $GLOBALS['template']->assign('contentLoad', './templates/confirm.tpl');
    }
    
    // Request information from user. InputFields is an array with entries on the following form:
    // array("infoText"=>"Input Text Here","inputFieldName"=>"UserName")
    //
    // Form data must contain the following
    // array("href"=>"Link","submitFieldName"=>"postUserName", "submitFieldText"=>"Search User")
    public function UserInput($message, $title, $inputFields, $formData, $returnTitle, $formID = "autoForm", $inputType = "post") {
        $GLOBALS['template']->assign('inputMsg', $message);
        $GLOBALS['template']->assign('inputsubHeader', $title);
        $GLOBALS['template']->assign('inputFields', $inputFields);
        $GLOBALS['template']->assign('formData', $formData);
        $GLOBALS['template']->assign('formID', $formID);
        $GLOBALS['template']->assign('formInputType', $inputType);
        $GLOBALS['template']->assign('returnLink', $returnTitle);
        $GLOBALS['template']->assign('contentLoad', './templates/input.tpl');
    }

    function load_modules() {
        $dir = './modules';
        $dirHandle = opendir($dir);
        $i = 0;
        while (false !== ($file = readdir($dirHandle))) {
            if ($file != '.htaccess' && $file != '.' && $file != '..') {
                $name = explode('.', $file);
                $this->modules[$i] = array($file, $name, $i);
                if ($file == "event.log.class.php") {
                    $this->defaultPage = $i;
                }
                $i++;
            }
        }
        $GLOBALS['template']->assign('menu', $this->modules);
    }

    function load_menu() {
        $GLOBALS['template']->assign('menuLoad', './panel_event/files/menu.tpl');
    }

    public function parse_layout() {
        $GLOBALS['template']->display("./files/layout.tpl");
    }
    
    // Send log entry
    public function setLogEntry( $type, $message , $contentID = ""){
        $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
            (   `time`,
                `aid`,
                `ip`,
                `title`,
                `contentID`,
                `changes`,
                `sub_identifier`
            ) VALUES
            (   '" . $GLOBALS['page']->load_time. "',
                '" . $GLOBALS['userdata'][0]['username'] . "',
                '" . $GLOBALS['user']->real_ip_address() . "', 
                '" . $type . "',
                '" . $contentID . "',
                '" . $message . "',
                'Event'
            )");
    }

}