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
        } elseif ($_GET['act'] == 'modify' && isset($_GET['nid'])) {
            if (!isset($_POST['Submit'])) {
                $this->update_form();
            } else {
                $this->update_village();
            }
        }elseif ($_GET['act'] == 'modifyS' && isset($_GET['nid'])) {
            if (!isset($_POST['Submit'])) {
                $this->update_formS();
            } else {
                $this->update_villageS();
            }
        }
        
    }

    //		Main screen:
    function main_screen() {
        $villages = $GLOBALS['database']->fetch_data("SELECT * FROM `villages`");
        tableParser::show_list(
                'villages', 'Village Funds Admin', $villages, array(
            'name' => "Village Name",
            'leader' => "Leader",
            'points' => "Points"
                ), array(
            array("name" => "Modify Village", "act" => "modify", "nid" => "table.name"),
            array("name" => "Modify Structures", "act" => "modifyS", "nid" => "table.name")
            
                ), true, // Send directly to contentLoad
                false, false
        );
    }

    function update_form() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `villages` WHERE `name` = '" . $_GET['nid'] . "'");
        if ($data != '0 rows') {
            tableParser::parse_form('villages', 'Update village item', array('name', 'longitude', 'latitude', 'orders', 'registration_choice', 'type', 'war_timer'), $data);
        } else {
            $GLOBALS['page']->Message("This village does not exist.", 'Village Funds Admin', 'id=' . $_GET['id']);
        }
    }

    function update_village() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `villages` WHERE `name` = '" . $_GET['nid'] . "'");
        if ($data != '0 rows') {

            $changed = tableParser::check_data('villages', 'name', $_GET['nid'], array());

            if (tableParser::update_data('villages', 'name', $_GET['nid'])) {

                // Update cache
                cachefunctions::deleteAlliances();
                
                // Insert log entry
                $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                        (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                        (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Village Change','" . $_GET['nid'] . "','Item ID:" . $_GET['nid'] . " Changed:<br>" . $changed . "')");

                $GLOBALS['page']->Message("The village has been updated.", 'Village Funds Admin', 'id=' . $_GET['id']);
            } else {
                $GLOBALS['page']->Message("An error occured while updating the village.", 'Village Funds Admin', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Village could not be found in the system.", 'Village Funds Admin', 'id=' . $_GET['id']);
        }
    }
    
    function update_formS() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `village_structures` WHERE `name` = '" . $_GET['nid'] . "'");
        if ($data != '0 rows') {
            tableParser::parse_form('village_structures', 'Update village data', array('name', 'hospital', 'shop', 'regen', 'wall_rob', 'wall_def', 'time_start', 'Konoki', 'Silence', 'Samui', 'Shine', 'Shroud', 'Syndicate', 'warRegenBoostTime'), $data);
        } else {
            $GLOBALS['page']->Message("This village does not exist.", 'Village Data Admin', 'id=' . $_GET['id']);
        }
    }

    function update_villageS() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `village_structures` WHERE `name` = '" . $_GET['nid'] . "'");
        if ($data != '0 rows') {

            $changed = tableParser::check_data('village_structures', 'name', $_GET['nid'], array());

            if (tableParser::update_data('village_structures', 'name', $_GET['nid'])) {

                
                // Update cache
                cachefunctions::deleteAlliances();
                
                // Insert log entry
                $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                        (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                        (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Village Data Change','" . $_GET['nid'] . "','Item ID:" . $_GET['nid'] . " Changed:<br>" . $changed . "')");

                $GLOBALS['page']->Message("The village has been updated.", 'Village Data Admin', 'id=' . $_GET['id']);
            } else {
                $GLOBALS['page']->Message("An error occured while updating the village.", 'Village Data Admin', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Village could not be found in the system.", 'Village Data Admin', 'id=' . $_GET['id']);
        }
    }

}

new news();