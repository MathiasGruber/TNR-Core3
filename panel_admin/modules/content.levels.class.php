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

class level_admin {        

    function __construct() {    
        if (!isset($_GET['act'])) {
            $this->main_screen();
        } elseif ($_GET['act'] == 'modify' && is_numeric($_GET['iid'])) {
            if (!isset($_POST['Submit'])) {
                $this->modify_item();
            } else {
                $this->do_modify_item();
            }
        } 
    }
          

    // Main Menu
    function main_screen() {
        
        $items = $GLOBALS['database']->fetch_data("SELECT * FROM `levels` ORDER BY `levelID` ASC");
        tableParser::show_list(
            'levels',
            'Level admin', 
            $items,
            array(
                'levelID' => "Level ID", 
                'level' => "Level Number", 
                'rank' => "Rank"
            ), 
            array( 
                array("name" => "Modify", "act" => "modify", "iid" => "table.levelID")
            ) ,
            true, // Send directly to contentLoad
            false,
            false
        );
    }
            
    function modify_item() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `levels` WHERE `levelID` = '" . $_GET['iid'] . "'");
        if ($data != '0 rows') {
            tableParser::parse_form('levels', 'Update Level', array('levelID','level','rank_id','rank'), $data);
        } else {
            $GLOBALS['page']->Message("This level does not exist.", 'Level System', 'id='.$_GET['id']); 
        }
    }

    function do_modify_item() {
        $changed = tableParser::check_data('levels', 'levelID', $_GET['iid'], array());
        
        if (tableParser::update_data('levels', 'levelID', $_GET['iid'])) {
            
            $GLOBALS['page']->Message("The level has been updated.", 'Level System', 'id='.$_GET['id']); 
                           
            $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Level Change','" . $_GET['iid'] . "','Level ID:" . $_GET['iid'] . " Changed:<br>" . $changed . "')");
              
        } else {
            $GLOBALS['page']->Message("An error occured while updating the level.", 'Level System', 'id='.$_GET['id']); 
        }
    }
     

}

new level_admin();