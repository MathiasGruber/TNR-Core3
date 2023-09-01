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

require_once(Data::$absSvrPath.'/libs/itemSystem/itemFunctions.php');
class inventory extends itemBasicFunctions {

    // Setup & handle all in the inventory
    public function __construct() {

        // Try phrase
        try{

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Run constructor on parent
            parent::__construct();

            // Page to show
            if (!isset($_GET['act'])) {

                //	Main inventory screen
                $this->main_page();

            } else {

                // Start a transaction
                $GLOBALS['database']->transaction_start();

                // Different actions
                if ($_GET['act'] == 'selllist') {

                    // Sell item list
                    $this->sellItemList();

                } elseif ($_GET['act'] == 'equip') {

                    // Equip item
                    $this->equip_item( $_SESSION['uid'], $_GET['inv_id'] );

                    // Show menu instead of message from equip function
                    $this->main_page();

                } elseif ($_GET['act'] == 'details') {

                    // Show details
                    $this->show_details( $_SESSION['uid'], $_GET['inv_id'] );

                } elseif ($_GET['act'] == 'use') {

                    // Use an item
                    $this->do_use_item( $_SESSION['uid'], $_GET['iid'], $_GET['inv_id'] );
                }

                // End transactions
                $GLOBALS['database']->transaction_commit();
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        } catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Inventory System', 'id='.$_GET['id'],'Return');
        }
    }

    // Show items in inventory
    protected function main_page() {

        // Max items for this user
        $maxitem = $this->getMaximumItems();

        // Handle actions
        $this->mergeAndSplit();

        // Get user inventory
        $inventory = $GLOBALS['database']->fetch_data("
            SELECT
                `users_inventory`.*,`users_inventory`.`id` as `inv_id`,
                `items`.`name`,`items`.`use`,
                `items`.`stack_size`,`items`.`inventorySpace`,
                `items`.`price`,`items`.`type`,`items`.`armor_types`,
                `items`.`required_rank`, `items`.`consumable`
            FROM `users_inventory`,`items`
            WHERE
                `uid` = '" . $_SESSION['uid'] . "' AND
                `iid` = `items`.`id` AND
                `durabilityPoints` > 0 AND
                `stack` > 0
            ORDER BY `type`,`name` ASC");

        // Fix up inventory
        $itemCount = $totalRepel = 0;
        if ($inventory != '0 rows') {

            // remember recorded items
            $this->itemsIDsInInventory = array();
            for( $i = 0 ; $i < count($inventory) ; $i++ ){

                // Set information
                $inventory[$i] = $this->setItemOptions($inventory[$i]);

                // Fix repair link
                if ($inventory[$i]['type'] !== 'armor' && $inventory[$i]['type'] !== 'weapon') {
                    $inventory[$i]['canRepair'] = 'N/A';
                }

                // Fix up type for armors so it shows position insted
                if ($inventory[$i]['type'] == 'armor') {

                    // Set type
                    $inventory[$i]['type'] = $inventory[$i]['armor_types'];

                    // Increase the repel if armor has some
                    if( $inventory[$i]['equipped'] == "yes" &&
                        stristr($inventory[$i]['use'], "REPEL:")
                    ){
                        $tempSplit = explode(":", $inventory[$i]['use']);
                        $totalRepel += $tempSplit[1];
                    }
                }

                // Add inventory volume
                $inventory[$i]['type'] .= " (".$inventory[$i]['inventorySpace'].")";

                // Increase inventory count
                $itemCount += $inventory[$i]['inventorySpace'];

                // Add item ID to array
                $this->itemsIDsInInventory[] = $inventory[$i]['iid'];

            }
        }

        // Update the user repel amount based on his items
        if( $totalRepel !== $GLOBALS['userdata'][0]['repel_effect'] ){

            // Update database
            $GLOBALS['database']->execute_query("
                UPDATE `users_statistics`
                SET `repel_effect` = '".$totalRepel."'
                WHERE `uid` = '" . $_SESSION['uid'] . "'
                LIMIT 1"
            );

            // Instant update
            $GLOBALS['userdata'][0]['repel_effect'] = $totalRepel;
        }

        // Color based on how filled
        if ($itemCount < ($maxitem / 4)) {
            $color = 'green';
        } elseif ($itemCount < ($maxitem / 2)) {
            $color = 'green';
        } elseif ($itemCount < $maxitem / (4 / 3)) {
            $color = 'orange';
        } else {
            $color = 'red';
        }

        // Check if overburdened
        $overburden = "";
        if( $itemCount > $maxitem ){
            $overburden = "<font color='darkred'>You are overburdened. This will negatively affect your character</font>. ";
        }

        // Show the table of users
        tableParser::show_list(
            'inventory',
            "Inventory",
            $inventory,
            array(
                'name' => "Name",
                'type' => "Type (space)",
                'canRepair' => "Repairable",
                'action' => "Action"
            ),
            array(
                array("name" => "Details", "id" => $_GET['id'], "act" => "details", "inv_id" => "table.inv_id"),
                array( "parseType"=> "select",
                       "name" => "Sell",
                       "formName" => "inventoryIDs",
                       "value" => "table.id",
                       "href"=>"?id=".$_GET['id']."&amp;act=selllist",
                       "submitName" => "Sell Selected"
                )
            ),
            true,   // Send directly to contentLoad
            false,   // Show previous/next links
            false,  // No links at the top to show
            false,   // Allow sorting on columns
            false,   // pretty-hide options
            false, // Top stuff
            "Your inventory currently holds <font color='".$color."'>".$itemCount." / ".$maxitem."</font> items. All of your items are shown in the inventory, except those that needs to be processed by the respective professions. ".$overburden  // Top information
        );
    }

    // Set action option or message on item in the inventory
    private function setItemOptions( $item ){

        $equipLink = '<a href="?id=' . $_GET['id'] . '&amp;act=equip&amp;inv_id=' . $item['inv_id']. '">Equip</a>';
        $unequipLink = '<a href="?id=' . $_GET['id'] . '&amp;act=equip&amp;inv_id=' . $item['inv_id']. '">Unequip</a>';
        $useLink = '<a href="?id=' . $_GET['id'] . '&amp;act=use&amp;iid=' . $item['iid'] . '&amp;inv_id=' . $item['id'] . '">Use Item</a>';

        // Standard links
        $item['action'] = "";
        if ($item['trading'] != '') {
            $item['action'] = $item["trade_type"] == "repair" ? "Under Repair" : "Trading";
            if ($item['stack'] > 1) {
                $item['name'] .= " (".$item['stack'].")";
            }
        } elseif ($item['finishProcessing'] != 0) {
            $item['action'] = "Under Work";
            if ($item['stack'] > 1) {
                $item['name'] .= " (".$item['stack'].")";
            }
        } elseif ($item['equipped'] == 'yes') {
            $item['action'] = $unequipLink;
        } elseif (($item['type'] == 'armor' || $item['type'] == 'weapon') && $GLOBALS['userdata'][0]['rank_id'] >= $item['required_rank']) {
            $item['action'] = $equipLink;
        } elseif ($item['type'] == 'armor' || $item['type'] == 'weapon') {
            $item['action'] = "N/A";
        } elseif( $item['stack_size'] > 1 ){
            $item = $this->addMergeAndSplitActions( $item , $this->itemsIDsInInventory, false, true, true);
            $item['action'] = $item['split'];
        }

        // Usage link
        if( $item['consumable'] == 'yes' && $item['finishProcessing'] == 0) {
            $item['action'] .= empty($item['action']) ? $useLink : " / ".$useLink;
        }

        // If nothing
        if( empty($item['action']) ){
            $item['action'] = "N/A";
        }

        return $item;
    }

    // Check merge amd split actions
    private function mergeAndSplit(){

        if( isset($_GET['process']) ){
            // Run any split/merge stack actions
            if( ($_GET['process'] == "split" && isset($_GET['invID'])) ||
                ($_GET['process'] == "merge" && isset($_GET['iid']))  ){

                // Start transaction for anything going on here
                $GLOBALS['database']->transaction_start();

                // Handle cases
                switch( $_GET['process'] ){
                    case "split": $this->splitUserStackInHalf( $_SESSION['uid'], $_GET['invID'] ); break;
                    case "merge": $this->mergeUserStacks( $_SESSION['uid'], $_GET['iid'] ); break;
                    default: throw new Exception("Could not identify item action"); break;
                }

                // End this transaction
                $GLOBALS['database']->transaction_commit();
            }
        }
    }

    // Sell selected list of items
    private function sellItemList(){

        // Get the tools of active profession if any
        require_once(Data::$absSvrPath.'/libs/professionSystem/professionLib.php');
        $profession = new professionLib();
        $profession->setupProfessionData();
        $profession->fetch_user();
        $toolIDs = array();
        try{
            if( $profession->user_has_tool() ){
                $toolIDs[] = $profession->toolData[0]['inv_id'];
            }
        } catch (Exception $e) {
            // User did not have profession tool
        }

        // The list of items
        $list = array();

        // Check that list is specified
        if( !isset($_REQUEST['inventoryIDs']) ||
            empty($_REQUEST['inventoryIDs']) ||
            count($_REQUEST['inventoryIDs']) === 0)
        {
            throw new Exception('No items selected for deletion');
        }

        // Add ids to list
        foreach( $_REQUEST['inventoryIDs'] as $key => $value ){
            if(!ctype_digit($value) ) {
               throw new Exception('Invalid item id: '.$value );
            }
            elseif( !in_array( $value, $toolIDs , true ) ){
                $list[] = $value;
            }
        }

        // Sell the list of items
        $this->do_sell_itemList( $list, $_SESSION['uid'] );
    }
}

new inventory();