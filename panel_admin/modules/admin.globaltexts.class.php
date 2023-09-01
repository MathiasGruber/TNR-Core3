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

class globalTexts {
    public function __construct() {
        
        // Show the correct page
        if (!isset($_GET['act'])) {
            
            // List all pages
            $this->main_page();
            
        } elseif( $_GET['act'] == "editPage" ){
            if (!isset($_POST['Submit'])) {
                
                // Show the current page form
                $this->editPageForm();
            } else {
                
                // Do edit
                $this->editPageDo();
            }
        }
    }

    private function main_page() {
        
        // Get clan users: ,
        $pages = $GLOBALS['database']->fetch_data("SELECT `id`,`name`,`time` FROM `information_pages`");
        
        // Show the table of users
        tableParser::show_list(
            'pages',
            'Information Pages', 
            $pages,
            array(
                'id' => "Page ID",
                'name' => "Page Name",
                'time' => "Last Activity"
            ), 
            array( 
                array("name" => "Edit Page", "id" => $_GET['id'], "act" => "editPage", "pageID" => "table.id")
            ) ,
            true, // Send directly to contentLoad
            true, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false
        );
    }
    
    private function editPageForm(){
        $page = $GLOBALS['database']->fetch_data("SELECT `content` FROM `information_pages` WHERE `id` = '".$_GET['pageID']."'");
        if( $page !== "0 rows" ){
            $GLOBALS['page']->UserInput( 
                    "Edit the content of the page below. HTML is not allowed, but feel free to use bbcode.", 
                    "Edit Page", 
                    array(
                        array("infoText"=>"","inputFieldName"=>"content","type"=>"textarea","inputFieldValue"=> $page[0]['content'], "maxlength" => 50000  ),
                        array("type"=>"hidden", "inputFieldName"=>"pageID", "inputFieldValue"=>$_GET['pageID'])
                    ), 
                    array(
                        "href"=>"?id=".$_GET['id']."&act=".$_GET['act'] ,
                        "submitFieldName"=>"Submit", 
                        "submitFieldText"=>"Submit Changes"),
                    "Return" 
             );
        }
    }
    
    private function editPageDo(){
        $page = $GLOBALS['database']->fetch_data("SELECT `id`, `content` FROM `information_pages` WHERE `id` = '".$_REQUEST['pageID']."'");
        if( $page !== "0 rows" ){
            
            // Store message
            $message = functions::store_content($_POST['content']);
            
            // Run update
            $GLOBALS['database']->execute_query("
            UPDATE `information_pages` 
            SET `content` = '".$message."',
                `time` = UNIX_TIMESTAMP()
            WHERE 
                `id` = '".$page[0]['id']."' LIMIT 1");
            
            // Give message
            $GLOBALS['page']->Message( "Page information has been updated" , 'GlobalText System', 'id='.$_REQUEST['id'],'Return');
        }
    }

    // Get & Save file data
    private function getFileContent($filename) {
        if (is_file($filename)) {
            $fp = fopen($filename, 'r');
            $fileData = stripslashes(fread($fp, filesize($filename)));
            fclose($fp);
            return $fileData;
        } else {
            return 0;
        }
    }

    private function saveFileContent($filename, $content) {
        $fp = fopen($filename, 'w');
        if (fwrite($fp, $content)) {
            fclose($fp);
            return 1;
        } else {
            fclose($fp);
            return 0;
        }
    }

    //	Edit the rules
    private function edit_rules_form() {
        if ($rules = $this->getFileContent("../templates/content/rules/rules_en.tpl")) {
            $GLOBALS['template']->assign('formText', $rules);
            $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/admin_globaltexts/form.tpl');
        }
    }

    private function upload_rules_edit() {
        if (isset($_POST['formData']) && strlen($_POST['formData']) > 0) {
            if ($this->saveFileContent("../templates/content/rules/rules_en.tpl", $_POST['formData'])) {
                $GLOBALS['page']->Message("The rules have been updated.", 'GlobalText System', 'id=' . $_GET['id']);
            } else {
                $GLOBALS['page']->Message("An error occured while uploading the new rules to file.", 'GlobalText System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("An error occured: you did not specify any rules.", 'GlobalText System', 'id=' . $_GET['id']);
        }
    }

    //    Edit the terms of service
    private function edit_terms_form() {
        if ($terms = $this->getFileContent("../templates/content/terms/terms_en.tpl")) {
            $GLOBALS['template']->assign('formText', $terms);
            $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/admin_globaltexts/form.tpl');
        }
    }

    private function upload_terms_edit() {

        if (isset($_POST['formData']) && strlen($_POST['formData']) > 0) {
            if ($this->saveFileContent("../templates/content/terms/terms_en.tpl", $_POST['formData'])) {
                $GLOBALS['page']->Message("The ToS have been updated.", 'GlobalText System', 'id=' . $_GET['id']);
            } else {
                $GLOBALS['page']->Message("An error occured while uploading the new ToS to file.", 'GlobalText System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("An error occured: you did not specify any ToS.", 'GlobalText System', 'id=' . $_GET['id']);
        }
    }

    // The TNR About page
    private function edit_about_form() {
        if ($about = $this->getFileContent("../templates/abouttnr.tpl")) {
            $GLOBALS['template']->assign('formText', $about);
            $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/admin_globaltexts/form.tpl');
        }
    }

    private function upload_about_edit() {
        if (isset($_POST['formData']) && strlen($_POST['formData']) > 0) {
            if ($this->saveFileContent("../templates/abouttnr.tpl", $_POST['formData'])) {
                $GLOBALS['page']->Message("The about page have been updated.", 'GlobalText System', 'id=' . $_GET['id']);
            } else {
                $GLOBALS['page']->Message("An error occured while uploading the new about page to file.", 'GlobalText System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("An error occured: you did not specify any about page.", 'GlobalText System', 'id=' . $_GET['id']);
        }
    }

    // Event History
    private function edit_history_form() {
        if ($about = $this->getFileContent("../templates/content/event/event.tpl")) {
            $GLOBALS['template']->assign('formText', $about);
            $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/admin_globaltexts/form.tpl');
        }
    }

    private function upload_history_edit() {
        if (isset($_POST['formData']) && strlen($_POST['formData']) > 0) {
            if ($this->saveFileContent("../templates/content/event/event.tpl", $_POST['formData'])) {
                $GLOBALS['page']->Message("The history page have been updated.", 'GlobalText System', 'id=' . $_GET['id']);
            } else {
                $GLOBALS['page']->Message("An error occured while uploading the new history page to file.", 'GlobalText System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("An error occured: you did not specify any history page.", 'GlobalText System', 'id=' . $_GET['id']);
        }
    }

    // Event Guide
    private function edit_eventGuide_form() {
        if ($about = $this->getFileContent("../files/eventGuide.inc")) {
            $GLOBALS['template']->assign('formText', $about);
            $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/admin_globaltexts/form.tpl');
        }
    }

    private function upload_eventGuide_edit() {
        if (isset($_POST['formData']) && strlen($_POST['formData']) > 0) {
            if ($this->saveFileContent("../files/eventGuide.inc", $_POST['formData'])) {
                $GLOBALS['page']->Message("The event guide page have been updated.", 'GlobalText System', 'id=' . $_GET['id']);
            } else {
                $GLOBALS['page']->Message("An error occured while uploading the new event guide page to file.", 'GlobalText System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("An error occured: you did not specify any event guide page.", 'GlobalText System', 'id=' . $_GET['id']);
        }
    }

}

new globalTexts();