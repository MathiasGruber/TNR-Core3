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
class repairLib extends itemBasicFunctions {

    // The main submit page for broken stuff
    protected function mainRepair(){

        // Get min
        $min = tableParser::get_page_min();

        // Get the user items that are broken
        $item_data = $GLOBALS['database']->fetch_data('SELECT
            `users_inventory`.`durabilityPoints`,`users_inventory`.`id` as `inv_id`,
            `users_inventory`.`id`, `users_inventory`.`iid`, `users_inventory`.`uid`,
            `users_inventory`.`timekey`, `users_inventory`.`trade_type`,
            `items`.`durability`, `items`.`type`, `items`.`name`
            FROM `users_inventory`
                INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid` AND `items`.`durability` > `users_inventory`.`durabilityPoints`)
            WHERE `users_inventory`.`uid` = '.$_SESSION['uid'].' AND
                (`users_inventory`.`trading` IS NULL OR `users_inventory`.`trade_type` = "repair") AND
                `users_inventory`.`canRepair` = "yes" AND `users_inventory`.`durabilityPoints` > 0 LIMIT '.$min.', 10');

        // Fix data for the items
        $sortedItemData = array();
        if( $item_data !== "0 rows" ){
            for( $i=0 ; $i<count($item_data); $i++ ){

                // Check if durability is lower
                if( $item_data[$i]['durabilityPoints'] < $item_data[$i]['durability'] ){
                    if( $item_data[$i]['type'] == "armor" || $item_data[$i]['type'] == "weapon" ){

                        // Durability left
                        $item_data[$i]['durPerc'] = $item_data[$i]['durabilityPoints'] ." / ".$item_data[$i]['durability'];

                        // Link
                        if(  $item_data[$i]['trade_type'] == "repair" ){
                            $item_data[$i]['link'] = "<a href='?id=".$_GET['id']."&act=removeOffer&iid=".$item_data[$i]['iid']."&inv_id=".$item_data[$i]['id']."'>Remove Offer</a>";
                        }
                        else{
                            $item_data[$i]['link'] = "<a href='?id=".$_GET['id']."&act=offer&iid=".$item_data[$i]['iid']."&inv_id=".$item_data[$i]['id']."'>Submit For Repair</a>";
                        }

                        // Add to final list
                        $sortedItemData[] = $item_data[$i];
                    }
                }
            }
        }

        // Show the list
        tableParser::show_list(
            'repairHall',
            "Submit Broken Weapon/Armor",
            $sortedItemData,
            array(
                'name' => "Name",
                'type' => "Type",
                'durPerc' => "Durability",
                'link' => "Action"
            ),
            array(
                array("name" => "Details", "id" => "11", "act" => "details", "inv_id" => "table.inv_id"),
            ),
            true,   // Send directly to contentLoad
            true,   // Show previous/next links
            array(
                array("name" => "Submission List", "href" => "?id=" . $_GET['id'] . "&amp;act=submit"),
                array("name" => "Weapon Repair Jobs", "href" => "?id=" . $_GET['id'] . "&amp;act=weaponRepair"),
                array("name" => "Armor Repair Jobs", "href" => "?id=" . $_GET['id'] . "&amp;act=armorRepair")
            ), // Options on the top
            false,   // Allow sorting on columns
            false,   // pretty-hide options
            false, // Top stuff
            array('message'=>"Here you can either submit an item (weapon or armor) for repair,
             or if you're a weapon smith or armor crafter you can repair the items of others.",'hidden'=>'yes') // Top information
        );

    }

    // Set repair offer
    protected function doRemoveOffer(){

        // Start transaction
        $GLOBALS['database']->transaction_start();

        // Select the user, his money, and the item for update
        $this->setUserAndItemDataForUpdate( $_SESSION['uid'], $_GET['iid'], 1 , $_GET['inv_id']);

        // Check that the user owns the item
        if( $this->user_data[0]['iid'] !== null ){

            // Check trading
            if( $this->user_data[0]['trading'] == 1 && $this->user_data[0]['trade_type'] == 'repair'){

                // Set item to be trading
                $this->updateUserItem(
                        array(
                            "trade_type"=>'NULL',
                            'tradeValue'=>0,
                            'trading'=>'NULL',
                            'equipped' => 'no'
                        ),
                        array("id" => $_GET['inv_id'])
                );

                // End transaction
                $GLOBALS['database']->transaction_commit();

                // Show message
                $GLOBALS['page']->Message('You have retrieved the item '.$this->user_data[0]['name'].". You are not refunded the ryo put up for the repair.", 'Town Repair Hall', 'id=' . $_GET['id'] . '');

            }
            else{
                throw new Exception("This item is not currently in your possesion.");
            }
        }
        else{
            throw new Exception("You do not own this item");
        }
    }

    // Set repair offer
    protected function setRepairOffer(){

        // Sanity check of input data
        if( is_numeric( $_POST['ryo'] ) ){

            // Start transaction
            $GLOBALS['database']->transaction_start();

            // Select the user, his money, and the item for update
            $this->setUserAndItemDataForUpdate( $_SESSION['uid'], $_GET['iid'], 1 , $_GET['inv_id']);

            // Check that the user owns the item
            if( $this->user_data[0]['iid'] !== null ){

                // Check trading
                if( $this->user_data[0]['trading'] == null ){

                    // Check money
                    if( $this->user_data[0]['money'] >= $_POST['ryo'] ){

                        // Set item to be trading
                        $this->updateUserItem(
                                array(
                                    "trade_type"=>'repair',
                                    'tradeValue'=>ceil($_POST['ryo']*0.85),
                                    'trading'=>1,
                                    'equipped' => 'no'
                                ),
                                array("id" => $_GET['inv_id'])
                        );

                        // Update user money
                        $this->updateUserMoney( $_SESSION['uid'], $_POST['ryo']);

                        // End transaction
                        $GLOBALS['database']->transaction_commit();

                        // Show message
                        $GLOBALS['page']->Message('You have put '.$this->user_data[0]['name']." up for repair.", 'Town Repair Hall', 'id=' . $_GET['id'] . '');
                    }
                    else{
                        throw new Exception("You do not have enough money for this action.");
                    }
                }
                else{
                    throw new Exception("This item is not currently in your possesion.");
                }
            }
            else{
                throw new Exception("You do not own this item");
            }
        }
        else{
            throw new Exception("This is not a valid amount of ryo");
        }
    }



    // Show broken weapon list
    protected function showBrokenItems( $type ){

        // Check that the user is home & asleep
        if( ($GLOBALS['page']->isHome || $GLOBALS['page']->isOutlaw) && $GLOBALS['page']->isAsleep ){

            // See if the user is fit to repair this type of items
            if( $this->canRepairType($type) ){

                // Show different page depending on whether user is already repairing something or not
                if( $this->currentlyRepairingInvID == false ){
                    $this->doShowBrokenItems($type);
                }
                else{
                    $this->doShowRepairingItem();
                }

            }
            else{
                throw new Exception("You do not have the profession for repairing this type of item");
            }
        }
        else{
            throw new Exception("You must be in your home (with status set to asleep) to repair items");
        }
    }

    // A function to see if the user can repair a given item given his profession,
    protected function canRepairType( $type ){

        // get the user occupation
        $this->extraUserData = $GLOBALS['database']->fetch_data('SELECT `users_occupations`.`profession`,
            `users_inventory`.`iid`, `users_inventory`.`trading`, `users_inventory`.`tradeValue`,
            `users_inventory`.`id`, `users_inventory`.`tempModifier`, `users_inventory`.`finishProcessing`,
            `users_statistics`.`money`, `users_inventory`.`uid`, `users_inventory`.`durabilityPoints`,
            `items`.`name`
            FROM `users_occupations`
                INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users_occupations`.`userid`)
                LEFT JOIN `users_inventory` ON (`users_inventory`.`trading` = `users_occupations`.`userid`)
                LEFT JOIN `items` ON (`users_inventory`.`iid` = `items`.`id`)
            WHERE `users_occupations`.`userid` = '.$_SESSION['uid'].' LIMIT 1 FOR UPDATE');

        // If we found that the user is currently repairing an item
        $this->currentlyRepairingInvID = false;
        if( isset($this->extraUserData[0]['iid']) ){
            $this->currentlyRepairingInvID = $this->extraUserData[0]['id'];
        }

        // Check if it exists
        if( $this->extraUserData !== "0 rows" ){

            // Check if it's good
            if(
                $type == "armor" && $this->extraUserData[0]['profession'] == 4 ||
                $type == "weapon" && $this->extraUserData[0]['profession'] == 5
            ){
                return true;
            }
        }
        return false;
    }

    // set a variable with the user repair kits
    protected function getRepairKits( $type ){

        // Get the user items that are broken
        $item_data = $GLOBALS['database']->fetch_data('SELECT
            `items`.`strength`, `items`.`use`, `items`.`profession_level`, `items`.`name`,
            `items`.`id` as `itemID`,
            `users_inventory`.`id` as `invID`
            FROM `items`
                INNER JOIN `users_inventory` ON
                    (`users_inventory`.`iid` = `items`.`id` AND `users_inventory`.`trading` IS NULL AND `users_inventory`.`uid` = "'.$_SESSION['uid'].'")
            WHERE `items`.`type` = "repair"');

        // Data variables to set
        $this->repairKits = array();

        // Got hrough the data
        if( $item_data !== "0 rows" ){
            foreach( $item_data as $item ){
                if( !empty($item['use']) ){
                    $tags = explode(":", $item['use']);
                    if(count($tags) == 3){
                        if( $tags[1] == $type ){
                            $this->repairKits[] = array(
                                "itemID" => $item['itemID'],
                                "invID" =>  $item['invID'],
                                "level" => $tags[2]
                           );
                        }
                    }
                }
            }
        }
    }

    // Function for showing broken items
    private function doShowBrokenItems( $type ){

        // Get min
        $min = tableParser::get_page_min();

        // Get the user items that are broken
        $item_data = $GLOBALS['database']->fetch_data('SELECT `users_inventory`.`durabilityPoints`,
            `users_inventory`.`tradeValue`, `users_inventory`.`id`, `users_inventory`.`iid`,
            `items`.`durability`, `items`.`type`, `items`.`profession_level`, `items`.`name`
            FROM `users_inventory`
                INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid`
                    AND `items`.`type` = "'.$type.'" AND `items`.`durability` > `users_inventory`.`durabilityPoints`)
            WHERE `users_inventory`.`trading` = 1 AND `users_inventory`.`trade_type` = "repair" AND
                `users_inventory`.`canRepair` = "yes" AND `users_inventory`.`durabilityPoints` > 0 AND
                `users_inventory`.`tradeValue` > 0 LIMIT '.$min.', 10');

        // Get the repair kits
        $this->getRepairKits($type);

        // Fix data for the items
        $sortedItemData = array();
        if( $item_data !== "0 rows" ){
            for( $i=0 ; $i<count($item_data); $i++ ){

                // Durability left
                $item_data[$i]['durPerc'] = $item_data[$i]['durabilityPoints'] ." / ".$item_data[$i]['durability'];

                // Money
                $item_data[$i]['tradeValue'] .= " ryo";

                // Default repair link
                $item_data[$i]['repair'] = "<i>Need Better Kit</i>";

                // Loop through kits
                if( !empty($this->repairKits) ){

                    // select the one with lowest diff
                    $selectedKit = $selectedDiff = false;
                    foreach( $this->repairKits as $kit ){
                        if( $kit['level'] >= $item_data[$i]['profession_level'] ){
                            if( $selectedDiff == false || $kit['level'] - $item_data[$i]['profession_level'] < $selectedDiff ){
                                $selectedKit = $kit;
                                $selectedDiff = $kit['level'] - $item_data[$i]['profession_level'];
                            }
                        }
                    }

                    // If one was selected, show it
                    if( $selectedKit !== false ){
                        $item_data[$i]['repair'] = "<a href='?id=".$_GET['id']."&act=doRepair&iid=".$item_data[$i]['iid']."&inv_id=".$item_data[$i]['id']."&repairKitID=".$selectedKit['itemID']."&repairKitInv=".$selectedKit['invID']."'>Do Repair</a>";
                    }
                }

                // Add to final list
                $sortedItemData[] = $item_data[$i];

            }
        }

        // Show the list
        tableParser::show_list(
            'repairHall',
            ucfirst($type)." Repair Hall",
            $sortedItemData,
            array(
                'name' => "Name",
                'durPerc' => "Durability",
                'profession_level' => "Difficulty",
                'tradeValue' => "Offer",
                'repair' => "Action"
            ),
            false,
            true,   // Send directly to contentLoad
            true,   // Show previous/next links
            array(
                array("name" => "Submit an Item", "href" => "?id=" . $_GET['id'] . "&amp;act=submit"),
                array("name" => "Weapon Repair Jobs", "href" => "?id=" . $_GET['id'] . "&amp;act=weaponRepair"),
                array("name" => "Armor Repair Jobs", "href" => "?id=" . $_GET['id'] . "&amp;act=armorRepair")
            ), // Options on the top
            false,   // Allow sorting on columns
            false,   // pretty-hide options
            false, // Top stuff
            array('message'=>"Here you can either submit an item (weapon or armor) for repair,
             or if you're a weapon smith or armor crafter you can repair the items of others.",'hidden'=>'yes') // Top information
        );
    }

    // Repair a broken item
    protected function startRepairItem(){

        // Sanity check of input data
        if(
            isset( $_GET['iid'] ) &&
            isset( $_GET['inv_id'] ) &&
            isset( $_GET['repairKitID'] ) &&
            is_numeric( $_GET['iid'] ) &&
            is_numeric( $_GET['inv_id'] )&&
            is_numeric( $_GET['repairKitID'] )
        ){

            // Start transaction
            $GLOBALS['database']->transaction_start();

            // Select the user, his money, and the item for update
            $this->setUserAndItemDataForUpdate( $_SESSION['uid'], $_GET['repairKitID'], 1 , $_GET['repairKitInv'] );

            // Check that the item exists
            if( $this->user_data[0]['iid'] !== null ){

                // Check trading
                if( $this->user_data[0]['trading'] == null ){

                    // Get the item we're trying to repair
                    $brokenItem = $this->selectItemForUpdate(
                        array(
                            "`items`.`id`" => $_GET['iid'] ,
                            "`users_inventory`.`id`" => $_GET['inv_id']
                        )
                    );

                    // Check that it exists
                    if( $brokenItem !== "0 rows" ){

                        // Check that it's broke
                        if( $brokenItem[0]['durability'] - $brokenItem[0]['durabilityPoints'] > 0 && $brokenItem[0]['canRepair']){

                            // Check that we can repair this type
                            if( $this->canRepairType($brokenItem[0]['type']) ){

                                // Check that user is not repairing other item already
                                if( $this->currentlyRepairingInvID == false ){

                                    // Check that it's up for repair
                                    if( $brokenItem[0]['trading'] !== null && $brokenItem[0]['trade_type'] == "repair" ){

                                        // Check that the kit can repair this type of item
                                        $repairData = explode( ":", $this->user_data[0]['use'] );
                                        if( $repairData[0] == "repair" && $repairData[1] == $brokenItem[0]['type'] ){

                                            // Check that the kit is good enough for this type of item
                                            if( $repairData[2] >= $brokenItem[0]['profession_level'] ){

                                                // Passed all checks. Remove kit
                                                if( $this->user_data[0]['stack'] > 1 ){
                                                    $this->updateUserItemStack(
                                                            $_SESSION['uid'],
                                                            $_GET['repairKitID'],
                                                            $_GET['repairKitInv'],
                                                            $this->user_data[0]['stack']-1
                                                     );
                                                }
                                                else{
                                                    $this->removeUserItem(array( "uid" => $_SESSION['uid'], "id" => $_GET['repairKitInv']  ));
                                                }

                                                // Calculate new durabilty
                                                $newDurability = $brokenItem[0]['durability'];

                                                // Update broken item
                                                $this->updateUserItem(
                                                        array(
                                                            'trading'=> $_SESSION['uid'],
                                                            "finishProcessing" => $GLOBALS['user']->load_time+10*60,
                                                            "tempModifier" => $newDurability
                                                        ),
                                                        array("id" => $_GET['inv_id'])
                                                );

                                                // End transaction
                                                $GLOBALS['database']->transaction_commit();

                                                // Show message
                                                $GLOBALS['page']->Message('You have started repairing '.$brokenItem[0]['name'].". Remember that you cannot leave your house during this repair, or it will be stopped.", 'Town Repair Hall', 'id=' . $_GET['id']);
                                            }
                                            else{
                                                throw new Exception("This repair kit is not good enough for this type of repair");
                                            }
                                        }
                                        else{
                                            throw new Exception("You cannot repair this item with this kit");
                                        }
                                    }
                                    else{
                                        throw new Exception("No request for repairing this item exist anymore");
                                    }
                                }
                                else{
                                    throw new Exception("You are already repairing another item");
                                }
                            }
                            else{
                                throw new Exception("You cannot repair this type of item");
                            }
                        }
                        else{
                            throw new Exception("You cannot repair this item, it's not broken.");
                        }
                    }
                    else{
                        throw new Exception("Could not find the item you're trying to repair");
                    }
                }
                else{
                    throw new Exception("This item is not currently in your possesion.");
                }
            }
            else{
                throw new Exception("You do not own this item: ".$this->user_data[0]['iid']);
            }
        }
        else{
            throw new Exception("You have not specified which item you want to repair.");
        }
    }

    // Function for showing current repair
    private function doShowRepairingItem(){

        // get the time left
        $diff = $this->extraUserData[0]['finishProcessing'] - $GLOBALS['user']->load_time;

        // Figure out action
        if( $diff < 0 ){

            // Start transaction
            $GLOBALS['database']->transaction_start();

            // To work with item functions, we need to set user_data
            $this->user_data = $this->extraUserData;

            // Update broken item
            $this->updateUserItem(
                    array(
                        "trade_type"=>"NULL",
                        'tradeValue'=>"NULL",
                        'trading'=>"NULL",
                        "durabilityPoints"=>$this->extraUserData[0]['tempModifier'],
                        "finishProcessing"=>0
                    ),
                    array("id" => $this->extraUserData[0]['id'])
            );

            $events = new Events($this->extraUserData[0]['uid']);
            $events->acceptEvent('item_durability_gain', array('context'=>$this->extraUserData[0]['iid'], 'new'=>$this->extraUserData[0]['tempModifier'], 'old'=>$this->extraUserData[0]['durabilityPoints'] ));
            $events->closeEvents();

            $users_notifications = new NotificationSystem('', $this->extraUserData[0]['uid']);
            $users_notifications->addNotification(array(
                                                        'id' => 21,
                                                        'duration' => 'none',
                                                        'text' => $GLOBALS['userdata'][0]['username']." has finished repairing your \"".$this->extraUserData[0]['name']."\"!",
                                                        'dismiss' => 'yes'
                                                    ));
            $users_notifications->recordNotifications();

            $GLOBALS['Events']->acceptEvent('item_repair', array('data'=>$this->extraUserData[0]['iid'], 'new'=>$this->extraUserData[0]['tempModifier'], 'old'=>$this->extraUserData[0]['durabilityPoints'] ));

            // Update money for the repair
            $this->updateUserMoney( $_SESSION['uid'], -$this->extraUserData[0]['tradeValue']);

            // End transaction
            $GLOBALS['database']->transaction_commit();

            // Message
            $GLOBALS['page']->Message('You repaired an item and earned '.$this->extraUserData[0]['tradeValue']." ryo.", 'Town Repair Hall', 'id=' . $_GET['id'] . '');
        }
        else{
            $GLOBALS['page']->Message("Current repair will finish in: ".functions::convert_time($diff, 'processTimer'.$diff), 'Town Repair Hall', 'id=' . $_GET['id'] . '');
        }
    }
}