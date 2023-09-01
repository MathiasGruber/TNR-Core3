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

require_once(Data::$absSvrPath.'/libs/villageSystem/blackMarketLib.php');

class specialsurprise extends blackMarketLib{

    public function __construct() {
        
            try {

            if ( !isset($_GET['act']) || $_GET['act'] == "overview" ) {
                $this->overview();
            } elseif ($_GET['act'] == 'new') {
                if (!isset($_POST['Submit'])) {
                    $this->new_surprise();
                } else {
                    $this->insert_surprise();
                }
            } elseif ($_GET['act'] == 'modify' && is_numeric($_GET['iid'])) {
                if (!isset($_POST['Submit'])) {
                    $this->modify_surprise();
                } else {
                    $this->do_modify_surprise();
                }
            } elseif ($_GET['act'] == 'delete') {
                if (!isset($_POST['Submit'])) {
                    $this->verify_delete_surprise();
                } else {
                    $this->do_delete_surprise();
                }
            }             
        } catch (Exception $e) {
            
            // Rollback possible transactions
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            
            // Show error message
            $GLOBALS['page']->Message( $e->getMessage() , 'Black Market Special Surprise Admin', 'id='.$_GET['id']);
        }
    }
    
    // Set entry requirement to item requirement
    private function setItemReq(){
        if( isset( $_GET['setReq'], $_GET['iid']) ){
            $item = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `id` = '" . $_GET['iid'] . "' LIMIT 1");
            if( $item !== "0 rows" ){
                $GLOBALS['database']->execute_query("UPDATE `blackmarket_surprises` SET `requiredProfessionLvl` = '".$item[0]['profession_level']."' WHERE `id` = '" . $_GET['setReq'] . "' LIMIT 1");
            }
        }
    }
    
    // Show the current special surprises
    private function overview() {
        
        // Check if we should set requirement for item based on item data
        $this->setItemReq();
        
        // Get special surprises
        $surprises = $this->getSpecialSurprises( true , false, true );
        
        // Show them all
        tableParser::show_list(
            'surprises', 
            'Black Market Special Surprises admin', 
            $surprises, 
            array(
                'id' => "ID",
                'name' => "Name",
                'description' => "Description",
                'itemname' => "Item Reward",
                'min_amount' => "Min Amount",
                'max_amount' => "Max Amount",
                'cost_type' => "Cost Type",
                'cost_amount' => "Cost",
                'chanceDisplay' => "Chance",
                'start_date' => "Start Date",
                'end_date' => "End Date",
                'profession' => "Profession",
                'requiredProfessionLvl' => "Req. Prof. Lvl",
                'maxProfessionLvl' => "Max. Prof. Lvl",
                'extraRewards' => "# Extra Rewards",
                'isLive' => "isLive?"
                ), array(
                    array("name" => "SetReq", "act" => "overview", "setReq" => "table.id", "iid" => "table.reward_item_id"),
                    array("name" => "Modify", "act" => "modify", "iid" => "table.id"),
                    array("name" => "Delete", "act" => "delete", "iid" => "table.id")
                ), 
                true, // Send directly to contentLoad
                false, 
                array(
                    array("name" => "New Item", "href" => "?id=" . $_GET["id"] . "&act=new")
                ),
                false, // No sorting on columns
                false, // No pretty options
                false, // No top search field
                'Here you can edit the profession bags which are sold on the black market. 
                 Please be extra careful to test all entries created with this panel, 
                 so that we minimize complaints in the billing department.<br><br>
                 Entries are grouped by their cost types/amounts, such that multiple 
                 separate special surprises may be in the system at once. 
                 The chance of each entry in its given group is calculated based on the "frequency" entry on each item'
        );
    }
    
    // Form for inserting new surprise
    private function new_surprise(){        
        tableParser::parse_form('blackmarket_surprises', 'New Surprise', $this->noShowFields(true) );
    }

    // Do insert the new surprise
    private function insert_surprise(){
        
        // Insert the date
        if (tableParser::insert_data('blackmarket_surprises')) {
            
            // Check item exists
            $rewardName = $this->checkItem($_POST['reward_item_id']);
            
            // For event team, it's always an event item
            if( $GLOBALS['userdata'][0]['user_rank'] !== 'Admin' ){
                $GLOBALS['database']->execute_query("UPDATE `blackmarket_surprises` SET `event_item` = 'yes' WHERE `name` = '" . $_POST['name'] . "' AND `profession` = '".$_POST['profession'] ."' ");
            }
            
            // Set it to be a profession item
            $GLOBALS['database']->execute_query("UPDATE `blackmarket_surprises` SET `isProfessionEntry` = 'yes' WHERE `name` = '" . $_POST['name'] . "' AND `profession` = '".$_POST['profession'] ."'");
            
            // Log the insertion
            $GLOBALS['page']->setLogEntry("Surprise Change", 'The surprise named '.$_POST['name'].' was created, rewarding the item: '.$rewardName );
            
            // Show message
            $GLOBALS['page']->Message("The surprise ".$_POST['name']." has been added, rewarding the item: ".$rewardName, 'Surprise System', 'id=' . $_GET['id']);
            
        } else {
            throw new Exception("An error occured and the item has not been added.");
        }
    }

    // Edit a surprise
    private function modify_surprise() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `blackmarket_surprises` WHERE `id` = '" . $_GET['iid'] . "'");
        if ($data != '0 rows') {
            if ( $data[0]['event_item'] == 'yes' || $GLOBALS['userdata'][0]['user_rank'] == 'Admin' ) {
                tableParser::parse_form('blackmarket_surprises', 'Update Surprise', $this->noShowFields(true), $data);
            } else {
                throw new Exception("This is not an event surprise and you are not an admin.");
            }
        } else {
            throw new Exception("This surprise does not exist.");
        }
    }

    // Do perform the edit of the surprise
    private function do_modify_surprise() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `blackmarket_surprises` WHERE `id` = '" . $_GET['iid'] . "'");
        if ($data != '0 rows') {
            if ($data[0]['event_item'] == 'yes' || $GLOBALS['userdata'][0]['user_rank'] == 'Admin') {

                // Figure out what was changed / update
                $changed = tableParser::check_data('blackmarket_surprises', 'id', $_GET['iid'], array());
                if (tableParser::update_data('blackmarket_surprises', 'id', $_GET['iid'])) {   
                    
                    // Make sure the item exists
                    $rewardName = $this->checkItem($_POST['reward_item_id']);
             
                    // Make sure it's event if not admin
                    if( $GLOBALS['userdata'][0]['user_rank'] !== 'Admin' ){
                        $GLOBALS['database']->execute_query("UPDATE `blackmarket_surprises` SET `event_item` = 'yes' WHERE `name` = '" . $_POST['name'] . "' LIMIT 1");
                    }
                    
                    // Log the entry change
                    $GLOBALS['page']->setLogEntry("Surprise Change", 'Surprise ID:' . $_GET['iid'] . ' Changed:<br>'. $changed , $_GET['iid']);

                    // Show message to user
                    $GLOBALS['page']->Message("The surprise has been updated. Item award is: ".$rewardName, 'surprise System', 'id=' . $_GET['id']);
                } else {
                    throw new Exception("An error occured while updating the surprise.");
                }
            } else {
                throw new Exception("This is not an event surprise and you are not an admin.");
            }
        } else {
            throw new Exception("This surprise does not exist.");
        }
    }

    // Delete surprise
    private function verify_delete_surprise() {
        if (isset($_GET['iid']) && is_numeric($_GET['iid'])) {
            $GLOBALS['page']->Confirm("Delete this item?", 'Surprise System', 'Delete now!');
        } else {
            throw new Exception("This surprise does not exist.");
        }
    }

    private function do_delete_surprise() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `blackmarket_surprises` WHERE `id` = '" . $_GET['iid'] . "'");
        if ($data != '0 rows') {
            if ($data[0]['event_item'] == 'yes' || $GLOBALS['userdata'][0]['user_rank'] == 'Admin') {

                 if ($GLOBALS['database']->execute_query("DELETE FROM `blackmarket_surprises` WHERE `id` = '" . $_GET['iid'] . "' LIMIT 1")) {
                     
                     // Create log enetry
                     $GLOBALS['page']->setLogEntry("Item Change", 'Item ID: <i>'. $_GET['iid'] .'</i> was deleted' , $_GET['iid']);
                     
                     // Show message to user
                     $GLOBALS['page']->Message("The surprise was deleted from the item table.", 'Item System', 'id=' . $_GET['id']);

                } else {
                    throw new Exception("An error occured while deleting the surprise.");
                }
            } else {
                throw new Exception("This is not an event surprise and you are not an admin.");
            }
        } else {
            throw new Exception("This surprise does not exist.");
        }
        
        
       
    }

}

new specialsurprise();
