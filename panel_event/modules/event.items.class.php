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

class itemPanel {

    public function __construct() {
        if ( !isset($_GET['act']) || $_GET['act'] == "itemcreation" ) {
            $this->item_screen();
        } elseif ($_GET['act'] == 'new') {
            if (!isset($_POST['Submit'])) {
                $this->new_item();
            } else {
                $this->insert_item();
            }
        } elseif ($_GET['act'] == 'modify' && is_numeric($_GET['iid'])) {
            if (!isset($_POST['Submit'])) {
                $this->modify_item();
            } else {
                $this->do_modify_item();
            }
        } elseif ($_GET['act'] == 'delete') {
            if (!isset($_POST['Submit'])) {
                $this->verify_delete_item();
            } else {
                $this->do_delete_item();
            }
        }
    }

    // Item Functions
    function item_screen() {

        // Show form
        if (isset($_GET['type'])) {
            switch ($_GET['type']) {
                case "arm": $type = 'armor';
                    break;
                case "wea": $type = 'weapon';
                    break;
                case "spc": $type = 'special';
                    break;
                case "ite": $type = 'item';
                    break;
                case "art": $type = 'artifact';
                    break;
                case "mat": $type = 'material';
                    break;
                case "pro": $type = 'process';
                    break;
            }
        } else {
            $type = 'artifact';
        }

        $query = "SELECT * FROM `items` WHERE `type` = '" . $type . "' AND `event_item` = 'Yes'";

        $where = array();

        if( isset($_POST['search']) )
            $where[] = " `". ($_POST['search'] == 'iid' ? 'id' : $_POST['search']) ."` LIKE '%{$_POST[$_POST['search']]}%' ";

        if( count($where) >= 1 )
            $query .= " AND ".implode(' AND ',$where);
            
        $result = $GLOBALS['database']->fetch_data($query);
        
        $items = $GLOBALS['database']->fetch_data($query);
        tableParser::show_list(
                'items', 
                'Item admin', 
                $result, 
                array(
                    'id' => "ID",
                    'name' => "Name",
                    'notes' => "Notes",
                    'required_rank' => "Rank",
                    'type' => "Type",
                    'in_shop' => "Shop",
                ), 
                array(
                    array("name" => "Modify", "act" => "modify", "iid" => "table.id"),
                    array("name" => "Delete", "act" => "delete", "iid" => "table.id")
                ), 
                true, // Send directly to contentLoad
                false, 
                array(
                    array("name" => "New item", "href" => "?id=" . $_GET["id"] . "&act=new"),
                    array("name" => "Armors", "href" => "?id=" . $_GET["id"] . "&act=itemcreation&type=arm"),
                    array("name" => "Weapons", "href" => "?id=" . $_GET["id"] . "&act=itemcreation&type=wea"),
                    array("name" => "Special", "href" => "?id=" . $_GET["id"] . "&act=itemcreation&type=spc"),
                    array("name" => "Items", "href" => "?id=" . $_GET["id"] . "&act=itemcreation&type=ite"),
                    array("name" => "Material", "href" => "?id=" . $_GET["id"] . "&act=itemcreation&type=mat"),
                    array("name" => "Process", "href" => "?id=" . $_GET["id"] . "&act=itemcreation&type=pro"),
                    array("name" => "Artifact", "href" => "?id=" . $_GET["id"] . "&act=itemcreation&type=art")
                ),
                true,
                false,
                array(
                    array(
                        'infoText'=>'Iid',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'iid',
                        'postIdentifier'=>'search',
                        'inputName'=>'iid'
                    ),
                    array(
                        'infoText'=>'Name',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'name',
                        'postIdentifier'=>'search',
                        'inputName'=>'name'
                    ),
                    array(
                        'infoText'=>'Notes',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'notes',
                        'postIdentifier'=>'search',
                        'inputName'=>'notes'
                    )
                )
        );

        // Set a return link for the page
        $GLOBALS['template']->assign('returnLink', true);
    }

    function new_item() {
        tableParser::parse_form('items', 'New item', array('id', 'event_item'));
    }

    function insert_item() {
        $data['event_item'] = 'Yes';
        if( !in_array( $_POST['type'], array("reduction", "tool", "repair") ) ){
            if (tableParser::insert_data('items', $data)) {
                cachefunctions::deleteItems();
                $GLOBALS['database']->execute_query("UPDATE `items` SET `event_item` = 'Yes' WHERE `name` = '" . $_POST['name'] . "' LIMIT 1");
                $GLOBALS['page']->Message("The item has been added.", 'Item System', 'id=' . $_GET['id']);
                $GLOBALS['page']->setLogEntry("Item Change", 'New item named <i>'. $_POST['name'] .'</i> was created'
                                                            .'<br>name: '.$_POST['name']
                                                            .'<br>price: '.$_POST['price']
                                                            .'<br>in_shop: '.$_POST['in_shop']
                                                            .'<br>use: '.$_POST['use']
                                                            .'<br>use2: '.$_POST['use2']
                                                            .'<br>strength: '.$_POST['strength']
                                                            .'<br>craft recipe: '.$_POST['craft recipe']
                                                            .'<br>professionRestriction: '.$_POST['professionRestriction']);
            } else {
                $GLOBALS['page']->Message("An error occured and the item has not been added.", 'Item System', 'id=' . $_GET['id']);
            }
        }
        else{
            $GLOBALS['page']->Message("Cannot add items with the type: ".$_POST['type'], 'Item System', 'id=' . $_GET['id']);
        }
    }

    function modify_item() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `id` = '" . $_GET['iid'] . "'");
        if ($data != '0 rows') {
            if ($data[0]['event_item'] == 'Yes') {
                tableParser::parse_form('items', 'Update Item', array('id', 'event_item'), $data);
            } else {
                $GLOBALS['page']->Message("This is not an event item.", 'Item System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("This item does not exist.", 'Item System', 'id=' . $_GET['id']);
        }
    }

    function do_modify_item() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `id` = '" . $_GET['iid'] . "' AND `event_item` = 'Yes'");
        if ($data != '0 rows') {
            if ($data[0]['event_item'] == 'Yes') {

                $changed = tableParser::check_data('items', 'id', $_GET['iid'], array());
                if (tableParser::update_data('items', 'id', $_GET['iid'])) {
                    cachefunctions::deleteItems();
                    $GLOBALS['page']->setLogEntry("Item Change", 'Item ID:' . $_GET['iid'] . ' Changed:<br>'. $changed , $_GET['iid']);
                    $GLOBALS['database']->execute_query("UPDATE `items` SET `event_item` = 'Yes' WHERE `name` = '" . $_POST['name'] . "' LIMIT 1");

                    $GLOBALS['page']->Message("The item has been updated.", 'Item System', 'id=' . $_GET['id']);
                } else {
                    $GLOBALS['page']->Message("An error occured while updating the item.", 'Item System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This is not an event item.", 'Item System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("This item does not exist.", 'Item System', 'id=' . $_GET['id']);
        }
    }

    function verify_delete_item() {
        if (isset($_GET['iid']) && is_numeric($_GET['iid'])) {
            $GLOBALS['page']->Confirm("Delete this item?", 'Item System', 'Delete now!');
        } else {
            $GLOBALS['page']->Message("No valid item ID was specified.", 'Item System', 'id=' . $_GET['id']);
        }
    }

    function do_delete_item() {
        if ($GLOBALS['database']->execute_query("DELETE FROM `items` WHERE `id` = '" . $_GET['iid'] . "' AND `event_item` = 'Yes' LIMIT 1")) {
            if ($GLOBALS['database']->execute_query("DELETE FROM `users_inventory` WHERE `iid` = '" . $_GET['iid'] . "'")) {
                cachefunctions::deleteItems();
                $GLOBALS['page']->setLogEntry("Item Change", 'Item ID: <i>'. $_GET['iid'] .'</i> was deleted' , $_GET['iid']);
                $GLOBALS['page']->Message("The item was deleted from the item table, and all user inventories.", 'Item System', 'id=' . $_GET['id']);
            } else {
                $GLOBALS['page']->Message("The item has been deleted from the item table but a problem occured when deleting the item from all the users, user inventory data is probably broken, contact an administrator with PMA access.", 'Item System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("An error occured while deleting the item.", 'Item System', 'id=' . $_GET['id']);
        }
    }

}

new itemPanel();