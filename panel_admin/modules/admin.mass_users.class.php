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

class users {

    // Page handler
    public function __construct() {
        
        try{
            
            // Check actions
            if (!isset($_GET['act'])) {
                $this->main_page();
            } elseif ($_GET['act'] == 'addItem') {
                if (!isset($_POST['Submit'])) {
                    $this->add_item_form();
                } else {
                    $this->do_item_add();
                }
            } elseif ($_GET['act'] == 'heal') {
                if (!isset($_POST['Submit'])) {
                    $this->heal_form();
                } else {
                    $this->do_heal();
                }
            } elseif ($_GET['act'] == 'refill') {
                if (!isset($_POST['Submit'])) {
                    $this->refill_form();
                } else {
                    $this->do_refill();
                }
            } elseif ($_GET['act'] == 'resetPVP') {
                if (!isset($_POST['Submit'])) {
                    $this->resetpvp_form();
                } else {
                    $this->do_resetpvp();
                }
            }
            
            
        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Mass User Edits', 'id='.$_GET['id'],'Return');
        }
        
        
    }

    //	Main page
    private function main_page() {

        // Show form
        $min = tableParser::get_page_min();
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `admin_edits` WHERE `changes` LIKE 'Mass Users: %' ORDER BY `time` DESC LIMIT " . $min . ",10");
        tableParser::show_list(
                'log', 'User admin', $edits, array(
            'aid' => "Admin Name",
            'uid' => "Admin UID",
            'time' => "Time",
            'IP' => "IP Used",
            'changes' => "Changes"
                ), false, true, true, array(
            array("name" => "Add Item to All Users", "href" => "?id=" . $_GET["id"] . "&act=addItem"),
            array("name" => "Heal All Users", "href" => "?id=" . $_GET["id"] . "&act=heal"),
            array("name" => "Refill Pools of all Users", "href" => "?id=" . $_GET["id"] . "&act=refill"),
            array("name" => "Reset PVP of all Users", "href" => "?id=" . $_GET["id"] . "&act=resetPVP")
                )
        );
    }
    
    // Add items to users
    private function add_item_form() {
        
        // Check how many users
        $edits = $GLOBALS['database']->fetch_data("
             SELECT count(`id`) as count FROM `users`, `users_timer` 
             WHERE 
                `last_activity` > '".(time()-48*3600)."' AND
                `users`.`id` = `users_timer`.`userid`
        ");
        
        // Get all items
        $items = $GLOBALS['database']->fetch_data("SELECT `id`,`name` FROM `items` ORDER BY `name` ASC");
        
        // Add to array list
        $options = array();
        foreach( $items as $item ){
            $options[ $item['id'] ] = $item['name'];
        }
        
        // Show form
        $GLOBALS['page']->UserInput( 
                "Add an item to all characters in the database who was active during the last 48 hours! Anyone online before that will not get an item. Items may result in over-full inventories. Use sparingly. Users that will be affected: ".$edits[0]['count'], 
                "Mass Add Item", 
                array(
                    // A select box
                    array(
                        "infoText"=>"Chose Item",
                        "inputFieldName"=>"iid",
                        "type"=>"select",
                        "inputFieldValue"=> $options
                    ),
                    array(
                        "infoText" => "Stack", 
                        "inputFieldName" => "stack", 
                        "type" => "input", 
                        "inputFieldValue" => "1"
                    )
                ), 
                array(
                    "href"=>"?id=".$_REQUEST['id']. "&act=addItem" ,
                    "submitFieldName"=>"Submit", 
                    "submitFieldText"=>"Submit"),
                false ,
                "trainingForm"
        );
    }
    
    // Do add the items
    private function do_item_add() {
        if (isset($_POST['iid']) && is_numeric($_POST['iid'])) {
            $item = $GLOBALS['database']->fetch_data("SELECT `name`,`durability` FROM `items` WHERE `id` = '" . $_POST['iid'] . "' LIMIT 1");
            if ($item != '0 rows') {
                if(ctype_digit($_POST['stack']) && $_POST['stack'] > 0){
                    // Get all the user IDs to insert for
                    $users = $GLOBALS['database']->fetch_data("
                        SELECT `id` FROM `users`, `users_timer` 
                        WHERE 
                           `last_activity` > '".(time()-48*3600)."' AND
                           `users`.`id` = `users_timer`.`userid`
                   ");

                    // Create insert query
                    $query = "INSERT INTO `users_inventory`
                        ( `uid` , `iid` , `equipped` , `stack` , `timekey` , `durabilityPoints`)
                    VALUES ";

                    // Insert values
                    foreach( $users as $key => $user ){
                        if( $key > 0 ){$query .= ", ";}
                        $query .= "('" . $user['id'] . "', '" . $_POST['iid'] . "', 'no', '".$_POST['stack']."', '" . time() . "', '".$item[0]['durability']."')";
                    }

                    if ($GLOBALS['database']->execute_query( $query ) ) {
                        $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('" . time() . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" . $GLOBALS['userdata'][0]['id'] . "', 'Mass Users: added item " . addslashes($item[0]['name']) . "', '" . $GLOBALS['user']->real_ip_address() . "');");
                        $GLOBALS['page']->Message('The ' . stripslashes($item[0]['name']) . ' has been added to the user inventories', 'Mass User Edits', 'id=' . $_GET['id'] );
                    } else {
                        throw new Exception('The ' . stripslashes($item[0]['name']) . ' could not be added to the user\'s inventory');
                    }
                }
                else{
                    throw new Exception("Invalid stack chosen");
                }
            } else {
                throw new Exception("The specified item does not exist");
            }
        } else {
            throw new Exception("No item ID was set");
        }
    }
    
    // Confirm healing all users
    protected function heal_form(){
        $GLOBALS['page']->Confirm("Fully restore the health of all users in the system.", 'Mass User Edits', 'Perform Now!');
    }
    
    // Confirm re-filling pools of all users
    protected function refill_form(){
        $GLOBALS['page']->Confirm("Fully refill pools of all users in the system.", 'Mass User Edits', 'Perform Now!');
    }
    
    // Heal all users
    protected function do_heal(){
        $GLOBALS['database']->execute_query("
            UPDATE `users_statistics` 
            SET `cur_health` = `max_health`"
        );
        $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('" . time() . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" . $GLOBALS['userdata'][0]['id'] . "', 'Mass Users: healed all users', '" . $GLOBALS['user']->real_ip_address() . "');");
        $GLOBALS['page']->Message( "All users have been healed" , 'Mass User Edits', 'id='.$_GET['id'],'Return');
    }
    
    // Refill all pool
    protected function do_refill(){
        $GLOBALS['database']->execute_query("
            UPDATE `users_statistics` 
            SET `cur_sta` = `max_sta`, `cur_cha` = `max_cha`"
        );
        $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('" . time() . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" . $GLOBALS['userdata'][0]['id'] . "', 'Mass Users: all pools refilled', '" . $GLOBALS['user']->real_ip_address() . "');");
        $GLOBALS['page']->Message( "All user's pools have been refilled" , 'Mass User Edits', 'id='.$_GET['id'],'Return');
    }
    
    // Confirm resetting pvp for all users
    protected function resetpvp_form(){
        $GLOBALS['page']->Confirm("Reset the pvp experience of all users in the system.", 'Mass User Edits', 'Perform Now!');
    }
    
    protected function do_resetpvp(){
        $GLOBALS['database']->execute_query("
            UPDATE `users_statistics` 
            SET `pvp_experience` = '0'"
        );
        $GLOBALS['database']->execute_query("INSERT INTO `admin_edits` (`time` ,`aid` ,`uid` ,`changes`,`IP`)VALUES ('" . time() . "', '" . $GLOBALS['userdata'][0]['username'] . "', '" . $GLOBALS['userdata'][0]['id'] . "', 'Mass Users: all pvp reset', '" . $GLOBALS['user']->real_ip_address() . "');");
        $GLOBALS['page']->Message( "All user pvp points have been reset" , 'Mass User Edits', 'id='.$_GET['id'],'Return');
    }

    

}

new users();
