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

/*
 * 				Item administration
 * 		add, remove, browse, and edit items
 */

class admin_item {        

    function __construct() {    
        if (!isset($_GET['act'])) {
            $this->main_screen();
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
        } elseif ($_GET['act'] == 'delete' && is_numeric($_GET['iid'])) {
            if (!isset($_POST['Submit'])) {
                echo '1';
                $this->verify_delete();
            } else {
                $this->do_delete();
            }
        } elseif ($_GET['act'] == 'picture') {
            if (!isset($_POST['Submit'])) {
                $this->change_avatar();
            } else {
                $this->do_avatar_change();
            }
        } elseif ($_GET['act'] == 'search') {
            if(!isset($_POST['Submit'])){
                $this->search();
            }
            else{
                $this->execute_search();
            }            
        }
    }
    

    // Main Menu
    function main_screen() {
        
        // Entriews to show
        $toShow = array(
            'id' => "ID", 
            'name' => "Name", 
            'required_rank' => "Rank",
            'strength' => "Strength",
            'type' => "Type",
            'in_shop' => "Shop",
            'use' => "Use1",
            'use2' => "Use2",
        );
        
        
        if( isset( $_GET['type'] ) ){
            switch( $_GET['type'] ){
                case "arm": $type = 'armor'; break;
                case "wea": $type = 'weapon'; break;
                case "spc": $type = 'special'; break;
                case "ite": $type = 'item'; $toShow['consumable'] = "Consumable?"; break;
                case "art": $type = 'artifact'; break;
                case "pro": $type = 'process'; break;
                case "mat": $type = 'material'; break;
                case "tol": $type = 'tool'; break;
                case "rep": $type = 'repair'; break;
                case "red": $type = 'reduction'; break;
            }
        }
        else{
            $type = 'artifact';
        }

        $query = "SELECT * FROM `items` WHERE `type` = '" . $type . "'";

        $where = array();

        if( isset($type) && $type != '' )
            $where[] = " `type` = '{$type}' ";
        
        if( isset($_POST['search']) )
            $where[] = " `". ($_POST['search'] == 'iid' ? 'id' : $_POST['search']) ."` LIKE '%{$_POST[$_POST['search']]}%' ";

        if( count($where) >= 1 )
            $query .= " AND ".implode(' AND ',$where);

        $query .= ' ORDER BY `id` DESC';
            
        $result = $GLOBALS['database']->fetch_data($query);

        tableParser::show_list(
            'items',
            'Item admin', 
            $result,
            $toShow, 
            array( 
                array("name" => "Picture", "act" => "picture", "iid" => "table.id"), 
                array("name" => "Modify", "act" => "modify", "iid" => "table.id"), 
                array("name" => "Delete", "act" => "delete", "iid" => "table.id")
            ) ,
            true, // Send directly to contentLoad
            false,
            array(
                array("name" => "New item", "href" =>"?id=".$_GET["id"]."&act=new"),
                array("name" => "Armors", "href" =>"?id=".$_GET["id"]."&type=arm"),
                array("name" => "Weapons", "href" =>"?id=".$_GET["id"]."&type=wea"),
                array("name" => "Special", "href" =>"?id=".$_GET["id"]."&type=spc"),
                array("name" => "Items", "href" =>"?id=".$_GET["id"]."&type=ite"),
                array("name" => "Artifact", "href" =>"?id=".$_GET["id"]."&type=art"),
                array("name" => "Process", "href" =>"?id=".$_GET["id"]."&type=pro"),
                array("name" => "Material", "href" =>"?id=".$_GET["id"]."&type=mat"),
                array("name" => "Tools", "href" =>"?id=".$_GET["id"]."&type=tol"),
                array("name" => "Repair", "href" =>"?id=".$_GET["id"]."&type=rep"),
                array("name" => "Reduction", "href" =>"?id=".$_GET["id"]."&type=red"),
                array("name" => "Search", "href" =>"?id=".$_GET["id"]."&act=search")
            ),
            false,
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
                    
    // Item Functions
    function new_item() {
        tableParser::parse_form('items', 'New item', array('id'));
    }

    function insert_item() {
        if (tableParser::insert_data('items')) {

            $GLOBALS['page']->Message("The item has been added.", 'Item System', 'id='.$_GET['id']); 
            
            $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
            (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
            (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Item Change','','New item named <i>" . $_POST['name'] . "</i> was created')");
        
            cachefunctions::deleteItems();
            
        } else {
            $GLOBALS['page']->Message("An error occured and the item has not been added.", 'Item System', 'id='.$_GET['id']); 
        }
    }
            
    function modify_item() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `id` = '" . $_GET['iid'] . "'");
        if ($data != '0 rows') {
            tableParser::parse_form('items', 'Update Item', array('id'), $data);
        } else {
            $GLOBALS['page']->Message("This item does not exist.", 'Item System', 'id='.$_GET['id']); 
        }
    }

    function do_modify_item() {
        $changed = tableParser::check_data('items', 'id', $_GET['iid'], array());
        
        if (tableParser::update_data('items', 'id', $_GET['iid'])) {
            
            $GLOBALS['page']->Message("The item has been updated.", 'Item System', 'id='.$_GET['id']); 
                           
            $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Item Change','" . $_GET['iid'] . "','Item ID:" . $_GET['iid'] . " Changed:<br>" . $changed . "')");
              
            cachefunctions::deleteItems();
            
        } else {
            $GLOBALS['page']->Message("An error occured while updating the item.", 'Item System', 'id='.$_GET['id']); 
        }
    }
                   
    function verify_delete() {
        if (isset($_GET['iid']) ) {
              $GLOBALS['page']->Confirm("Delete this item?", 'Item System', 'Delete now!'); 
        } else {
            $GLOBALS['page']->Message("No valid item ID was specified.", 'Item System', 'id='.$_GET['id']); 
        }
    }

    function do_delete() {
        if ($GLOBALS['database']->execute_query("DELETE FROM `items` WHERE `id` = '" . $_GET['iid'] . "' LIMIT 1")) {
            if ($GLOBALS['database']->execute_query("DELETE FROM `users_inventory` WHERE `iid` = '" . $_GET['iid'] . "'")) {

                $GLOBALS['page']->Message("The item was deleted from the item table, and all user inventories.", 'Item System', 'id='.$_GET['id']); 

                $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                (UNIX_TIMESTAMP(),'" . $GLOBALS['userdata'][0]['username'] . "','" . $GLOBALS['user']->real_ip_address() . "', 'Item Change','','Item ID: <i>" . $_GET['iid'] . "</i> was deleted')");
                
                cachefunctions::deleteItems();
                
            } else {
                $GLOBALS['page']->Message("The item has been deleted from the item table but a problem occured when deleting the item from all the users, user inventory data is probably broken, contact an administrator with PMA access.", 'Item System', 'id='.$_GET['id']); 
            }
        } else {
            $GLOBALS['page']->Message("An error occured while deleting the item.", 'Item System', 'id='.$_GET['id']); 
        }
    }

    //		Search
    function search() {  
        $GLOBALS['template']->assign('contentLoad', 'panel_admin/templates/content_items/search.tpl');        
    }

    function execute_search() {
        $preset = 0;
        $query = "SELECT `name`,`required_rank`,`type`,`in_shop`,`id` FROM `items` ";
        if ($_POST['name'] != '') {
            $query .= "WHERE `name` LIKE '%" . $_POST['name'] . "%'";
            $preset = 1;
        }
        if ($_POST['in_shop'] != 'any') {
            if ($preset == 1) {
                $query .= " AND ";
            } else {
                $query .= "WHERE";
            }
            $query .= "`in_shop` = '" . $_POST['in_shop'] . "'";
        }
        if ($_POST['armor_type'] != 'any') {
            if ($preset == 1) {
                $query .= " AND ";
            } else {
                $query .= "WHERE";
            }
            $query .= "`armor_types` = '" . $_POST['armor_type'] . "'";
        }
        if ($_POST['item_type'] != 'any') {
            if ($preset == 1) {
                $query .= " AND ";
            } else {
                $query .= "WHERE";
            }
            $query .= "`item_type` = '" . $_POST['item_type'] . "'";
        }
        if ($_POST['price_int'] != '') {
            if ($preset == 1) {
                $query .= " AND ";
            } else {
                $query .= "WHERE";
            }
            $query .= "`price` " . $_POST['price_type'] . " '" . $_POST['price_int'] . "'";
        }
        if ($_POST['rank_id'] != '1' || $_POST['rank_type'] != '>=') {
            if ($preset == 1) {
                $query .= " AND ";
            } else {
                $query .= "WHERE";
            }
            $query .= "`required_rank` " . $_POST['rank_type'] . " '" . $_POST['rank_id'] . "'";
        }
        if ($_POST['itemID'] != '') {
            if ($preset == 1) {
                $query .= " AND ";
            } else {
                $query .= "WHERE";
            }
            $query .= "`id` = " . $_POST['itemID'] . " ";
        }
        
        $items = $GLOBALS['database']->fetch_data($query);
        tableParser::show_list(
            'items',
            'Item admin', 
            $items,
            array(
                'id' => "ID", 
                'name' => "Name", 
                'required_rank' => "Rank",
                'type' => "Type",
                'in_shop' => "Shop",
            ), 
            array( 
                array("name" => "Picture", "act" => "picture", "iid" => "table.id"), 
                array("name" => "Modify", "act" => "modify", "iid" => "table.id"), 
                array("name" => "Delete", "act" => "delete", "iid" => "table.id")
            ) ,
            true, // Send directly to contentLoad
            false,
            array(
                array("name" => "New item", "href" =>"?id=".$_GET["id"]."&act=new"),
                array("name" => "Armors", "href" =>"?id=".$_GET["id"]."&type=arm"),
                array("name" => "Weapons", "href" =>"?id=".$_GET["id"]."&type=wea"),
                array("name" => "Special", "href" =>"?id=".$_GET["id"]."&type=spc"),
                array("name" => "Items", "href" =>"?id=".$_GET["id"]."&type=ite"),
                array("name" => "Artifact", "href" =>"?id=".$_GET["id"]."&type=art"),
                array("name" => "Search", "href" =>"?id=".$_GET["id"]."&act=search")
            )
        );
        
        // Set a return link for the page
        $GLOBALS['template']->assign('returnLink', true);
    }
    
    // Item avatar form
    function change_avatar() {
        
        // Get the signature
        $image = functions::getUserImage('/items/', $_GET['iid']);

        // Get the fileuploadlibrary
        require('../global_libs/General/fileUploads.php');
        fileUploader::uploadForm(array(
            "maxsize" => "100kb",
            "subTitle" => "Change Item Picture",
            "image" => $image,
            "description" => "Change the picture of this item",
            "dimX" => 200,
            "dimY" => 200
        ));

        // Return Link
        $GLOBALS['template']->assign("returnLink", true);
    }

    function do_avatar_change() {
        
        // Get the fileuploadlibrary
        require('../global_libs/General/fileUploads.php');
        $upload = fileUploader::doUpload(array(
            "maxsize" => 102400,
            "destination" => 'items/',
            "filename" => $_GET['iid'],
            "dimX" => 200,
            "dimY" => 200
        ));

        // Message to user
        if( $upload == true ){
            $GLOBALS['page']->Message('You have successfully uploaded the item image.', 'Item System', 'id=' . $_GET['id'] . '');
        }
    }

}

new admin_item();