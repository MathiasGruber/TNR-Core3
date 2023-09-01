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

class ips {

    function __construct() {
        if (!isset($_POST['Submit'])) {
            $this->update_form();
        } else {
            $this->update_list();
        }
    }

    function update_form() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `site_information` WHERE `option` = 'admin_ips'");
        if ($data != '0 rows') {
            tableParser::parse_form('site_information', 'Update Admin IP list', array('id', 'option'), $data);
        } else {
            $GLOBALS['page']->Message("No admin IP list exists.", 'Admin IP System', 'id=' . $_GET['id']);
        }
    }

    function update_list() {
        if (tableParser::update_data('site_information', 'option', 'admin_ips')) {
            $GLOBALS['page']->Message("The list has been updated.", 'Admin IP System', 'id=' . $_GET['id']);
        } else {
            $GLOBALS['page']->Message("An error occured while updating the list.", 'Admin IP System', 'id=' . $_GET['id']);
        }
    }

}

new ips();