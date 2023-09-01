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

class globalEvents {

    public function __construct() {
        if ( !isset($_GET['act']) ) {
            $this->currentEvents();
        } 
        elseif ($_GET['act'] == 'modify' && is_numeric($_GET['iid'])) 
        {
            if (!isset($_POST['Submit'])) {
                $this->modify_item();
            } else {
                $this->do_modify_item();
            }
        } 
    }
    
    function currentEvents() {

        $events = $GLOBALS['database']->fetch_data("SELECT * FROM `global_events`");
        tableParser::show_list(
                'events', 
            'Global Events Admin', $events, 
            array(
                'id' => "ID",
                'identifier' => "Identifier",
                'data' => "Data",
                'adminInfo' => "Info",
                'userVisual' => "Text for User",
                'active' => "Active?",
                'lock' => "Locked?"
            ), array(
            array("name" => "Modify", "act" => "modify", "iid" => "table.id")
                ), true, // Send directly to contentLoad
                false, false
        );

        // Set a return link for the page
        $GLOBALS['template']->assign('returnLink', true);
    }

    function modify_item() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `global_events` WHERE `id` = '" . $_GET['iid'] . "'");
        if ($data != '0 rows') {
            tableParser::parse_form('global_events', 'Update Global Event', array('id', 'identifier', 'adminInfo'), $data);            
        } else {
            $GLOBALS['page']->Message("This global event does not exist.", 'Global Event System', 'id=' . $_GET['id']);
        }
    }

    function do_modify_item() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `global_events` WHERE `id` = '" . $_GET['iid'] . "'");
        if ($data != '0 rows') {
            $changed = tableParser::check_data('global_events', 'id', $_GET['iid'], array());
            if (tableParser::update_data('global_events', 'id', $_GET['iid'])) {
                cachefunctions::deleteAllGlobalEvents();
                $GLOBALS['page']->Message("The global event has been updated.", 'Global Event System', 'id=' . $_GET['id']);
            } else {
                $GLOBALS['page']->Message("An error occured while updating the global event.", 'Global Event System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("This global event does not exist.", 'Global Event System', 'id=' . $_GET['id']);
        }
    }

}

new globalEvents();