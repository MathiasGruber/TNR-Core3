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
class locations {

    //	Constructor
    public function __construct() {
        if (!isset($_GET['act'])) {
            $this->main_page();
        } elseif ($_GET['act'] == 'mod') {
            if (!isset($_POST['Submit'])) {
                $this->edit_location_form();
            } else {
                $this->do_edit_location();
            }
        }
    }

    //	Main page
    private function main_page() {
        $locs = $GLOBALS['database']->fetch_data("SELECT * FROM `locations`");
        tableParser::show_list(
                'location', 'Location admin', $locs, array(
            'id' => "ID",
            'name' => "Location Name",
            'owner' => "Current Owner",
            'trait_1' => "Trait 1",
            'trait_2' => "Trait 2"
                ), array(
            array("name" => "Edit Location", "act" => "mod", "uid" => "table.id")
                ), 
                true, // Send directly to contentLoad
                false, 
                false,
                false, // No sorting on columns
                false, // No pretty options
                false, // No top search field
                'This panel allows you to put traits on given locations. The traits are the same as those used for AI.'
        );
    }

    private function edit_location_form() {
        if (isset($_GET['uid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `locations` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                tableParser::parse_form('locations', 'Edit Location', array('id','identifier','name','owner','attackableNeighboursIDlist'), $data);
            } else {
                $GLOBALS['page']->Message("This location does not exist?", 'Location System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("No location was specified", 'Location System', 'id=' . $_GET['id']);
        }
    }

    private function do_edit_location() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            $changed = tableParser::check_data('locations', 'id', $_GET['uid'], array('id'));
            if (tableParser::update_data('locations', 'id', $_GET['uid'])) {
                $GLOBALS['page']->setLogEntry("Location Change", 'Location with id: <i>' . $_GET['uid'] . '</i> changed: '.$changed, $_GET['uid'] );
                $GLOBALS['page']->Message('The location has been updated', 'Location System', 'id=' . $_GET['id']);
                
                // Delete cache
                cachefunctions::deleteLocations(true);
                cachefunctions::deleteLocationInformation(true);
            } else {
                $GLOBALS['page']->Message("An error occured while updating the location", 'Location System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("No location was specified", 'Location System', 'id=' . $_GET['id']);
        }
    }

}

new locations();