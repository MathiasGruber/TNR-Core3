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
                $this->do_update();
            }
        }
        
    }

    //		Main screen:
    function main_screen() {
        $villages = $GLOBALS['database']->fetch_data("SELECT * FROM `homes`");
        tableParser::show_list(
                'homes', 'Homes Admin', $villages, array(
            'name' => "Home Name",
            'price' => "Price",
            'regen' => "Regen",
            'required_rank' => "Required Rank"
                ), array(
            array("name" => "Modify", "act" => "modify", "nid" => "table.id")
            ), true, // Send directly to contentLoad
                false, false
        );
    }

    function update_form() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `homes` WHERE `id` = '" . $_GET['nid'] . "'");
        if ($data != '0 rows') {
            tableParser::parse_form('homes', 'Update home', array("id","married_home","loyaltyReq"), $data);
        } else {
            $GLOBALS['page']->Message("This home does not exist.", 'Homes Admin', 'id=' . $_GET['id']);
        }
    }

    function do_update() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `homes` WHERE `id` = '" . $_GET['nid'] . "'");
        if ($data != '0 rows') {

            $changed = tableParser::check_data('homes', 'id', $_GET['nid'], array());

            if (tableParser::update_data('homes', 'id', $_GET['nid'])) {

                $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                        (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                        (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Home Change','" . $_GET['nid'] . "','Item ID:" . $_GET['nid'] . " Changed:<br>" . $changed . "')");

                $GLOBALS['page']->Message("The home has been updated.", 'Home Admin', 'id=' . $_GET['id']);
            } else {
                $GLOBALS['page']->Message("An error occured while updating the home.", 'Home Admin', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Home could not be found in the system.", 'Home Admin', 'id=' . $_GET['id']);
        }
    }

}

new news();