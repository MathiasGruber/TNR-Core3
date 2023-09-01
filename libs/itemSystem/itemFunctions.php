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

require_once(Data::$absSvrPath.'/libs/home/home_helper.php');
require_once(Data::$absSvrPath.'/libs/jutsuSystem/jutsuFunctions.php');
require_once(Data::$absSvrPath.'/libs/jutsuSystem/jutsuFunctions.php');
require_once(Data::$absSvrPath.'/libs/battleSystem/battleInitiation.php');

class itemBasicFunctions {

    // Constructor
    public function  __construct(){

        // Current items always start at zero
        $this->currentItems = 0;

        // Only run if userdata is set
        if( isset($GLOBALS['userdata']) ){

            // Get the max amount of items this user can carry
            $this->maxitm = $this->getMaximumItems();

            // Set the current number of items
            $this->userInventory = $GLOBALS['database']->fetch_data("SELECT
                    `items`.`id`, `items`.`name`, `items`.`stack_size`,
                    `users_inventory`.`stack`, `items`.`inventorySpace`,
                    `users_inventory`.`id` as inv_id,
                    `users_inventory`.`equipped`
                FROM `users_inventory`
                    INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid`)
                WHERE `users_inventory`.`uid` = ".$_SESSION['uid']." AND `users_inventory`.`stack` > 0 AND `users_inventory`.`durabilityPoints` > 0");

            // Set count
            if($this->userInventory !== "0 rows") {
                foreach($this->userInventory as $item) {
                    if($item['equipped'] != "yes")
                        $this->currentItems += $item['inventorySpace'];
                }
            }
        }
    }

    // Check if a given item id is in the inventory
    public function isItemInInventory($iid) {
        foreach($this->userInventory as $entry) {
            if($entry['id'] === $iid) {
                return $entry['inv_id'];
            }
        }
        return false;
    }

    // Check the number of a items of specific ID owned by user
    public function countUserItems( $iid ){
        $count = 0;
        if( is_array($this->userInventory))
        {
            foreach($this->userInventory as $entry)
            {
                if($entry['id'] === $iid)
                {
                    $count += $entry['stack'];
                }
            }
        }
        return $count;
    }

    // Get the maximum amount of items for this user
    protected function getMaximumItems()
    {
        $max_pack_size = 6;

        $inventory_tool = $GLOBALS['database']->fetch_data("SELECT `items`.`name` FROM `users_inventory` INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid`) WHERE `users_inventory`.`equipped` = 'yes' and `items`.`type` = 'tool' and `users_inventory`.`uid` = ".$_SESSION['uid']);

        if(isset($inventory_tool[0]['name']))
            {
                $profession_name = HomeHelper::getProfessionName($_SESSION['uid']);
                if(($inventory_tool[0]['name'] == 'Herbalist Pouch'  && $profession_name == 'Herbalist') ||
                   ($inventory_tool[0]['name'] ==  'Hunters Toolkit' && $profession_name == 'Hunter') ||
                   ($inventory_tool[0]['name'] ==  'Miners Toolkit'  && $profession_name == 'Miner'))
                    $max_pack_size += 4;
            }

        //find max inventory size
        if($GLOBALS['userdata'][0]['federal_level'] == "None")
            $max_pack_size += 0;
        else if($GLOBALS['userdata'][0]['federal_level'] == "Normal")
            $max_pack_size += 1;
        else if($GLOBALS['userdata'][0]['federal_level'] == "Silver")
            $max_pack_size += 3;
        else if($GLOBALS['userdata'][0]['federal_level'] == "Gold")
            $max_pack_size += 5;

        return $max_pack_size;
    }

    // Add merge and split actions keys to inventory item
    public function addMergeAndSplitActions($entry, $idsInInventory, $drop = true, $merge = true, $split = true, $location = 'user') {
        // Set the link
        $link = isset($_GET['page']) ? "?id=".$_GET['id']."&amp;page=".$_GET['page'] : "?id=".$_GET['id'];

        if($location == 'user')
            $extralink = "";
        else if($location == 'home')
            $extralink = "&act=inventory";
        else
            throw new InvalidArgumentException('location must be home or user.');

        $entry['split'] = "";

        // Variable for the action variable
        //if($drop === true) { $entry['split'] = "<a href='".$link.$extralink."&amp;process=drop&amp;invID=".$entry['id']."'>Drop</a>"; }

        // Show stack and figure out stack actions
        if ($entry['stack'] > 1) {
            $entry['name'] .= " (".$entry['stack'].")";
            if($split === true) {
                $entry['split'] .= !empty($entry['split']) ? " / " : "";
                $entry['split'] .= "<a href='".$link.$extralink."&amp;process=split&amp;invID=".$entry['id']."'>Split</a>";
            }
        }

        // Show the merge it if more than one item was present
        if(!empty($idsInInventory) && in_array($entry['iid'], $idsInInventory, true)) {
            if($merge === true) {
                $entry['split'] .= !empty($entry['split']) ? " / " : "";
                $entry['split'] .= "<a href='".$link.$extralink."&amp;process=merge&amp;timekey=".$entry['timekey']."&amp;iid=".$entry['iid']."'>Merge</a>";
            }
        }
        return $entry;
    }

    // A function for loading user profession information
    protected function loadProfessionData(){

        // Get occupation and reduce cost based on this
        $this->professionLib = new professionLib();
        $this->professionLib->setJobType("profession");
        $this->professionLib->fetch_user();

        // Check for professions
        if( isset($this->professionLib->user[0]['name']) ){
            if( $this->professionLib->setGains($this->professionLib->user[0]['name']) ){
                $this->discounts = $this->professionLib->gains;
            }
        }
    }

    // Load village data
    protected function setVillageData(){

        // Get data on village and vassal village
        $this->village = $GLOBALS['database']->fetch_data("
             SELECT
                `villages`.`owned_territories`,`villages`.`name`,
                `vassal_village`.`owned_territories` as `vassal_territories`,
                `vassal`.`name` AS `isVassel`,
                `vassal_village`.`name` AS `hasVassal`
             FROM `villages`
             LEFT JOIN `village_structures` AS `master` ON (`master`.`name` = `villages`.`name` AND `master`.`vassal` != '')
             LEFT JOIN `village_structures` AS `vassal` ON (`vassal`.`vassal` = `villages`.`name`)
             LEFT JOIN `villages` AS `vassal_village` ON (`vassal_village`.`name` = `master`.`vassal`)
             WHERE
                `villages`.`name` = '" . $GLOBALS['userdata'][0]['village'] . "'
             LIMIT 1");

        // If vassal, no territories
        if( !empty($this->village[0]['isVassel']) ){
            $this->village[0]['owned_territories'] = 0;
        }

        if( !empty($this->village[0]['hasVassal']) ){
            $this->village[0]['owned_territories'] += $this->village[0]['vassal_territories'];
        }
    }

    // Setting the discount for an item
    protected function setDiscount( $item ){

        // Go through the discounts
        if( isset($this->discounts) && !empty($this->discounts) ){
            foreach( $this->discounts as $discount ){
                if( $discount['type'] == "item" ){
                    if( $item['type'] == $discount['identifier'] ){
                        if(
                            !isset($discount['subIdentifier']) ||
                            ($discount['subIdentifier'] == "healing" && stristr( $item['use'], "HEA"))
                        ){
                            $item['price'] = $item['price'] * ((100-$discount['discount']) / 100);
                        }
                    }
                }
            }
        }

        // Set the discount
        $percReduction = $this->village[0]['owned_territories'] * 2;
        if( $percReduction > 30 ){
            $percReduction = 30;
        }
        $percReduction = ( 100-$percReduction ) / 100;
        $item['price'] = ceil( $percReduction * $item['price']);

        // Price increase from traveling merchant
        if( $this->village[0]['name'] == "Syndicate" ){
            $item['price'] = ceil( 1.25 * $item['price']);
        }

        // Price reduction from global event
        if( $event = functions::getGlobalEvent("ShopPrices") ){
            if( isset( $event['data']) && is_numeric( $event['data']) ){
                $item['price'] *= round($event['data'] / 100,2);
            }
        }

        // Return the updated item
        return $item;
    }

    // Do item effects. Supported effects are:
    // JUTSU_QUEST:x
    // REGEN:x
    // HEA:PERC|STAT:STR|statInt
    // STA:PERC|STAT:STR|statInt
    // CHA:PERC|STAT:STR|statInt
    private function callItemEffect($tag) {

        // Explode tag
        $subTags = explode(":", $tag);

        // Check tag
        if(count($subTags) <= 1) {
            throw new Exception("Something is wrong with the tag for this item");
        }

        // Identify tag
        switch($subTags[0]) {
            case("JUTSU_QUEST"): case("START_QUEST"): case("LEARN_QUEST"): {

                if(is_numeric($subTags[1]))
                    $qid = $subTags[1];
                else
                    throw new exception("Bad quest id on item tag: ".$qid);

                if(!isset($GLOBALS['QuestsControl']))
                {
                    require_once(Data::$absSvrPath.'/global_libs/Quests/QuestsControl.php');
                    $GLOBALS['QuestsControl'] = new QuestsControl();
                }

                if(!isset($GLOBALS['QuestsControl']->QuestsData->quests[$qid]))
                    $GLOBALS['QuestsControl']->learnQuest($qid,0);

                if($subTags[0] != "LEARN_QUEST")
                    $GLOBALS['QuestsControl']->tryStart($qid);

            } break;
            case("REGEN"): {
                // Check current boost
                $test = $GLOBALS['database']->fetch_data("SELECT `users`.`item_regen_boost` FROM `users` WHERE `users`.`id` = ".$_SESSION['uid']." LIMIT 1");

                if ((int)$test[0]['item_regen_boost'] !== 0) { throw new Exception("An item regeneration boost is already present."); }

                // Run update of DB
                if ($GLOBALS['database']->execute_query("UPDATE `users`
                     SET `users`.`item_regen_boost` = '".$subTags[1]."', `users`.`item_regen_endtime` = ".($GLOBALS['user']->load_time + 24 * 3600)."
                     WHERE `users`.`id` = ".$_SESSION['uid']." LIMIT 1") === false) {
                    throw new Exception("An error occurred while updating your user with regeneration.");
                }

                // Return message
                return "Your regeneration has been increased by ".$subTags[1]." for 24h";
            } break;
            case("REPEL"): {
                // Run update of DB
                if ($GLOBALS['database']->execute_query("UPDATE `users`
                    SET `users`.`repel_chance` = '".$subTags[1]."', `users`.`repel_endtime` = ".($GLOBALS['user']->load_time + 24 * 3600)."
                    WHERE `users`.`id` = ".$_SESSION['uid']." LIMIT 1") === false) {
                    throw new Exception("An error occurred while updating your user with regeneration.");
                }

                // Return message
                if($subTags[1] > 0)
                    return "There is now a ".$subTags[1]."% chance that enemies will not attack you when travelling. The effect will be active for 24h";
                else
                    return "There is now a ".ABS($subTags[1])."% chance that enemies will attack you when travelling. The effect will be active for 24h";
            } break;
            case("HEA"): case("STA"): case("CHA"): {

                // Set the strings used in the three different cases:
                $fullName = $shortName = "";
                switch($subTags[0]){
                    case("HEA"): $fullName = "health"; $shortName = "health"; break;
                    case("STA"): $fullName = "stamina"; $shortName = "sta"; break;
                    case("CHA"): $fullName = "chakra"; $shortName = "cha"; break;
                }

                // Calculate heal
                if ($subTags[1] === 'STAT') {
                    if ($subTags[2] === 'STR') { $heal = $this->item_data[0]['strength']; }
                    else { $heal = $subTags[2]; }
                }
                elseif ($subTags[1] === 'PERC') {
                    if ($subTags[2] === 'STR') { $heal = $this->item_data[0]['strength'] * ($GLOBALS['userdata'][0]['max_'.$shortName] / 100); }
                    else { $heal = $subTags[2]; }
                }

                // New health
                $newHealth = (($GLOBALS['userdata'][0]['cur_'.$shortName] + $heal) > $GLOBALS['userdata'][0]['max_'.$shortName]) ?
                    $GLOBALS['userdata'][0]['max_'.$shortName] : $GLOBALS['userdata'][0]['cur_'.$shortName] + $heal;

                // Upload heal
                if ($GLOBALS['database']->execute_query("
                    UPDATE `users_statistics`
                    SET `users_statistics`.`cur_".$shortName."` = ".$newHealth."
                    WHERE `users_statistics`.`uid` = ".$_SESSION['uid']." LIMIT 1"
                ) === false) {
                    throw new Exception("An error occurred while updating your user with ".$fullName);
                }

                // Instant update
                $GLOBALS['userdata'][0]['cur_'.$shortName] = $newHealth;

                // Return message
                return "Your ".$fullName." has been refilled for ".$heal." points";
            } break;
        }
    }

    // Use item
    protected function do_use_item($uid, $iid, $invID) {
        if(!($this->item_data = $this->userHasItem(array("uid" => $uid, "iid" => $iid, "`users_inventory`.`id`" => $invID)))) {
            throw new Exception("You do not have this item, so you cannot use it.");
        }

        // Message to show to user
        $message = "";

        // Handle the two effects
        if(!empty($this->item_data[0]['use'])) {
            $message .= $this->callItemEffect($this->item_data[0]['use']);
        }

        if(!empty($this->item_data[0]['use2'])) {
            $message .= $this->callItemEffect($this->item_data[0]['use2']);
        }

        // Remove or udpate item
        if($this->item_data[0]['stack'] > 1)
        { 
            $this->updateUserItemStack($uid, $iid, $invID, ($this->item_data[0]['stack'] - 1));
            $GLOBALS['Events']->acceptEvent('item_quantity_loss', array('context'=>$iid, 'count'=>-1, 'old'=>$this->item_data[0]['stack'], 'new'=>$this->item_data[0]['stack'] - 1 ));
            $GLOBALS['Events']->acceptEvent('item_used', array('context'=>$iid, 'new'=>1+$this->item_data[0]['times_used'], 'old'=>$this->item_data[0]['times_used'] ));
        }
        else
        {
            $this->removeUserItem(array("uid" => $uid, "iid" => $iid, "`users_inventory`.`id`" => $invID), 1, null, $this->item_data[0]['inventorySpace']);
        }

        // Show message
        $GLOBALS['page']->Message('You have used the item: '.$this->item_data[0]['name'].'<br>'.$message, 'Inventory', 'id='.$_GET['id']);
    }

    // Sell item list
    public function do_sell_itemList($listOfItems, $uid, $location = "user") {

        // Do delete
        $GLOBALS['database']->transaction_start();

        // Select the items in question
        if($location == "user")
        {
            if(!($itemData = $GLOBALS['database']->fetch_data('
            SELECT `users_inventory`.`id`, `users_inventory`.`stack`, `users_inventory`.`finishProcessing`, `items`.`price`, `users_inventory`.`iid`
            FROM `users_inventory`
                INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid`)
            WHERE `users_inventory`.`uid` = '.$uid.' AND `users_inventory`.`id` IN ('.implode(",", $listOfItems).') AND
                (`users_inventory`.`trading` IS NULL OR `users_inventory`.`trade_type` = "repair") LIMIT '.count($listOfItems).' FOR UPDATE'))) {
                throw new Exception("No items to sell. Remember, you can't sell professional tools and you can't sell items you're trading");
            }
        }
        else if($location == "home")
        {
            if(!($itemData = $GLOBALS['database']->fetch_data('
                SELECT `home_inventory`.`id`, `home_inventory`.`stack`, `home_inventory`.`finishProcessing`, `items`.`price`, `items`.`type`, `home_inventory`.`iid`
                FROM `home_inventory`
                INNER JOIN `items` ON (`items`.`id` = `home_inventory`.`iid`)
                WHERE `home_inventory`.`uid` = '.$uid.' AND `home_inventory`.`id` IN ('.implode(",", $listOfItems).') LIMIT '.count($listOfItems).' FOR UPDATE')))
            {
                throw new Exception("No items to sell. Remember, you can't sell professional tools and you can't sell items you're trading");
            }

            if($itemData === '0 rows')
            {
                if(!($itemData = $GLOBALS['database']->fetch_data('
                    SELECT `home_inventory`.`id`, `home_inventory`.`finishProcessing`, `home_furniture`.`price`, `home_ineventory`.`iid`
                    FROM `home_inventory`
                    INNER JOIN `home_furniture` ON (home_furniture.`id` = `home_inventory`.`fid`)
                    WHERE `home_inventory`.`uid` = '.$uid.' AND `home_inventory`.`id` IN ('.implode(",", $listOfItems).') LIMIT '.count($listOfItems).' FOR UPDATE')))
                    {
                        throw new Exception("No items to sell. Remember, you can't sell professional tools and you can't sell items you're trading");
                    }
            }

            if(count($itemData) != count($listOfItems))
            {
                $temp;
                if(!($temp = $GLOBALS['database']->fetch_data('
                    SELECT `home_inventory`.`id`, `home_inventory`.`finishProcessing`, `home_furniture`.`price`, `home_inventory`.`iid`
                    FROM `home_inventory`
                    INNER JOIN `home_furniture` ON (home_furniture.`id` = `home_inventory`.`fid`)
                    WHERE `home_inventory`.`uid` = '.$uid.' AND `home_inventory`.`id` IN ('.implode(",", $listOfItems).') LIMIT '.count($listOfItems).' FOR UPDATE')))
                    {
                        throw new Exception("No items to sell. Remember, you can't sell professional tools and you can't sell items you're trading");
                    }

                $itemData = array_merge($itemData, $temp);
            }
        }
        else
        {
            throw new InvalidArgumentException("location must be home or user.");
        }

        // Nothing found
        if($itemData === '0 rows') {
            throw new Exception('Could not find any items to sell');
        }

        // Set village data
        $this->setVillageData();

        // Get the retrieved ids (those are the ones that actually belong to the user
        $sellList = array();
        $sellList_hooks = array();
        $sell_price = 0;



        foreach($itemData as $key => $item)
        {
            if(isset($item['finishProcessing']))
            if($item['finishProcessing'] == 0)
            {
                if(!isset($item['stack']))
                    $item['stack'] = 1;

                $item = $this->setDiscount($item);
                $sellList[] = $item['id'];
                $sellList_hooks[] = $item['iid'];
                $sell_price += floor((($item['price'] / 2) * $item['stack']));
            }
            else
            //pulling out items that are currently processing
                unset($itemData[$key]);
        }

        //if there are no items left after removing processing item thrown an exeption.
        if(count($itemData) == 0)
            throw new Exception("you may not sell items that are currently processing.");

        // Price reduction from global event
        if( $event = functions::getGlobalEvent("ShopPrices") ){
            if( isset( $event['data']) && is_numeric( $event['data']) ){
                $sell_price *= round($event['data'] / 100,2);
            }
        }

        // Check that some were found
        if(empty($sellList)) { throw new Exception("No items belonging to you were found."); }

        // Do delete
        if($location == "user")
        {
            if(!($users_inventory = $GLOBALS['database']->fetch_data("SELECT * FROM `users_inventory` where `uid` = ".$_SESSION['uid'])))
                throw new Exception('There was an error trying to recieve necessary information.');

            if(($GLOBALS['database']->execute_query('DELETE FROM `users_inventory`
            WHERE `users_inventory`.`id` IN ('.implode(",", $sellList).') LIMIT '.count($sellList))) === false) {
                throw new Exception('There was an error deleting the items');
            }

            $stuff = array();
            foreach($sellList_hooks as $iid)
            {
                if(!isset($stuff[$iid]))
                {
                    $stuff[$iid] = array('stack'=>0, 'quantity'=>0, 'stack_removed'=>0, 'quantity_removed'=>0);
                    foreach($users_inventory as $item)
                    {
                        if(isset($item['stack']))
                        {
                            $stuff[$iid]['stack']++;
                            $stuff[$iid]['quantity'] += $item['stack'];

                            if(in_array($item['id'], $sellList))
                            {
                                $stuff[$iid]['stack_removed']++;
                                $stuff[$iid]['quantity_removed'] += $item['stack'];
                            }
                        }
                    }
                }


                $GLOBALS['Events']->acceptEvent('item_person', array('data'=>'!'.$iid, 'context'=>$iid, 'new'=>$stuff[$iid]['stack']-$stuff[$iid]['stack_removed'], 'old'=>$stuff[$iid]['stack'] ));
                $GLOBALS['Events']->acceptEvent('item_quantity_loss', array('context'=>$iid, 'new'=>$stuff[$iid]['quantity']-$stuff[$iid]['quantity_removed'], 'old'=>$stuff[$iid]['quantity'] ));
            }
        }
        else if($location == "home")
        {
            if(!($home_inventory = $GLOBALS['database']->fetch_data("SELECT * FROM `home_inventory` where `uid` = ".$_SESSION['uid'])))
                throw new Exception('There was an error trying to recieve necessary information.');

            if(($GLOBALS['database']->execute_query('DELETE FROM `home_inventory`
            WHERE `home_inventory`.`id` IN ('.implode(",", $sellList).') LIMIT '.count($sellList))) === false) {
                throw new Exception('There was an error deleting the items');
            }

            $stuff = array();
            foreach($sellList_hooks as $iid)
            {
                if(!isset($stuff[$iid]))
                {
                    $stuff[$iid] = array('stack'=>0, 'quantity'=>0, 'stack_removed'=>0, 'quantity_removed'=>0);
                    foreach($home_inventory as $item)
                    {
                        if(isset($item['stack']))
                        {
                            $stuff[$iid]['stack']++;
                            $stuff[$iid]['quantity'] += $item['stack'];

                            if(in_array($item['id'], $sellList))
                            {
                                $stuff[$iid]['stack_removed']++;
                                $stuff[$iid]['quantity_removed'] += $item['stack'];
                            }
                        }
                    }
                }


                $GLOBALS['Events']->acceptEvent('item_home', array('data'=>'!'.$iid, 'context'=>$iid, 'new'=>$stuff[$iid]['stack']-$stuff[$iid]['stack_removed'], 'old'=>$stuff[$iid]['stack'] ));
            }
        }
        else
        {
            throw new InvalidArgumentException("location must be user or home");
        }

        // Update money
        if($sell_price > 0) {
            if($GLOBALS['database']->execute_query("UPDATE `users_statistics`
                SET `users_statistics`.`money` = `users_statistics`.`money` + ".$sell_price."
                WHERE `users_statistics`.`uid` = ".$uid." LIMIT 1") === false) {
                throw new Exception("Did not manage to give you the ryo. Giving you back items.");
            }
            else
            {
                $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] + $sell_price));
            }
        }

        // Show message
        $GLOBALS['page']->Message('You sold the selected items for '.$sell_price.' ryo.', 'Equipment', 'id='.$_GET['id']);

        // Commit the transaction
        $GLOBALS['database']->transaction_commit();
    }

    // Equip item
    protected function equip_item($uid, $invID) {
        if($item_data = $this->userHasItem(array( "uid" => $uid, "`users_inventory`.`id`" => $invID ))) {

            if ($GLOBALS['userdata'][0]['rank_id'] < $item_data[0]['required_rank']) {
                throw new Exception('You cannot equip this item at this rank.');
            }
            if ($item_data[0]['type'] !== 'armor' && $item_data[0]['type'] !== 'weapon' && $item_data[0]['type'] !== 'tool' ) {
                throw new Exception('You cannot equip items of this item type!');
            }
            if ( is_numeric($item_data[0]['trading']))
                throw new Exception('You cannot equip items that are being traded.');

            // Check for equip / unequip
            if ($item_data[0]['equipped'] === 'yes') {
                //  Unequip item, no need for further checks D:
                $GLOBALS['database']->execute_query("UPDATE `users_inventory`
                    SET `users_inventory`.`equipped` = 'no'
                    WHERE `users_inventory`.`uid` = ".$uid." AND `users_inventory`.`id` = ".$invID." LIMIT 1");
                $GLOBALS['page']->Message('You unequipped your '.$item_data[0]['name'], 'Equipment', 'id='.$_GET['id']);

                //update dsr here
                $battle_init = new battleInitiation();
                $battle_init->updateDSR($uid);
                $GLOBALS['Events']->acceptEvent('item_equip', array('data'=>'!'.$item_data[0]['iid'], 'context'=>$item_data[0]['iid'], 'extra'=>array('armor'=>$item_data[0]['armor_types'],'weapon'=>$item_data[0]['weapon_classifications']) ));
            }
            else { // All checks: Clear, proceed to final check before equipping
                if ($item_data[0]['type'] === 'armor') {
                    // Query
                    $GLOBALS['database']->execute_query("UPDATE `users_inventory`
                        SET `users_inventory`.`equipped` = 'yes'
                        WHERE `users_inventory`.`uid` = ".$uid." AND `users_inventory`.`id` = ".$invID."
                            AND NOT EXISTS (
                                SELECT `iid` FROM (
                                    SELECT `users_inventory`.`iid`
                                    FROM `users_inventory`
                                        INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid` AND `items`.`type` = 'armor' AND
                                            `items`.`armor_types` = '".$item_data[0]['armor_types']."')
                                    WHERE `users_inventory`.`uid` = ".$uid." AND `users_inventory`.`equipped` = 'yes' AND
                                        `users_inventory`.`durabilityPoints` > 0
                                ) AS T
                            ) LIMIT 1");

                    if ((int)$GLOBALS['database']->getAffectedRows() !== 1) {
                        throw new Exception('You already have this type of armor equipped, please un-equip it first.');
                    }

                    $GLOBALS['page']->Message('You equipped your '.$item_data[0]['name'], 'Equipment', 'id='.$_GET['id']);

                    //update dsr here
                    $battle_init = new battleInitiation();
                    $battle_init->updateDSR($uid);
                    $GLOBALS['Events']->acceptEvent('item_equip', array('data'=>$item_data[0]['iid'], 'context'=>$item_data[0]['iid'], 'extra'=>array('armor'=>$item_data[0]['armor_types'],'weapon'=>$item_data[0]['weapon_classifications']) ));

                }
                elseif ($item_data[0]['type'] === 'weapon') {
                    $weapons = $GLOBALS['database']->fetch_data("SELECT COUNT(`users_inventory`.`iid`) AS `weapons`
                        FROM `users_inventory`
                            INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid` AND `items`.`type` = 'weapon')
                        WHERE `users_inventory`.`uid` = ".$uid." AND `users_inventory`.`equipped` = 'yes' AND `users_inventory`.`durabilityPoints` > 0");

                    if ($weapons[0]['weapons'] >= 4) { // Still a weapon spot left, set weapon->equipped
                        throw new Exception('You already have four weapons equipped, please un-equip one first.');
                    }

                    $GLOBALS['database']->execute_query("UPDATE `users_inventory`
                        SET `users_inventory`.`equipped` = 'yes'
                        WHERE `users_inventory`.`uid` = ".$uid." AND `users_inventory`.`id` = ".$invID." LIMIT 1");

                    $GLOBALS['page']->Message('You have equipped your '.$item_data[0]['name'], 'Equipment', 'id='.$_GET['id']);

                    //update dsr here
                    $battle_init = new battleInitiation();
                    $battle_init->updateDSR($uid);
                    $GLOBALS['Events']->acceptEvent('item_equip', array('data'=>$item_data[0]['iid'], 'data'=>$item_data[0]['iid'], 'extra'=>array('armor'=>$item_data[0]['armor_types'],'weapon'=>$item_data[0]['weapon_classifications']) ));
                }
                elseif($item_data[0]['type'] === 'tool' )
                {
                    $tools = $GLOBALS['database']->fetch_data("SELECT COUNT(`users_inventory`.`iid`) AS `tool`
                        FROM `users_inventory`
                            INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid` AND `items`.`type` = 'tool')
                        WHERE `users_inventory`.`uid` = ".$uid." AND `users_inventory`.`equipped` = 'yes' AND `users_inventory`.`durabilityPoints` > 0");

                    if($tools[0]['tool'] >= 1)
                    {
                        throw new Exception("You already have a tool equipped, please un-equip it first.");
                    }

                    $GLOBALS['database']->execute_query("UPDATE `users_inventory`
                        SET `users_inventory`.`equipped` = 'yes'
                        WHERE `users_inventory`.`uid` = ".$uid." AND `users_inventory`.`id` = ".$invID." LIMIT 1");

                    $GLOBALS['page']->Message('You have equipped your '.$item_data[0]['name'], 'Equipment', 'id='.$_GET['id']);
                    $GLOBALS['Events']->acceptEvent('item_equip', array('data'=>$item_data[0]['iid'], 'data'=>$item_data[0]['iid'], 'extra'=>array('armor'=>$item_data[0]['armor_types'],'weapon'=>$item_data[0]['weapon_classifications']) ));

                }

                else { throw new Exception('How the hell did you get this error?!'); }
            }
        }
    }

    // Check if the user has the item with the given item ID
    public function userHasItem($params, $overWriteTradingLock = false) {

        // Locally save the parameters
        $query = "";
        foreach($params as $key => $value) {
            $query .= ($query === "") ? "".$key." = '".$value."'" :  " AND ".$key." = '".$value."'";
        }

        if (!is_numeric($params['uid'])) {
            throw new Exception('Incorrect item ID: ' . $iid);
        }

        // No limit, we need to return all rows so that we can potentially merge
        $item_data = $GLOBALS['database']->fetch_data("
            SELECT `users_inventory`.*, `items`.*, `users_inventory`.`id` as `inv_id`
            FROM `users_inventory`, `items`
            WHERE `items`.`id` = `users_inventory`.`iid` AND ".$query."
            FOR UPDATE"
        );

        if ($item_data === '0 rows') {
            throw new Exception('You do not have the required item.');
        }

        if ($item_data[0]['trading'] !== null && $overWriteTradingLock !== true) {
            
            $item_data = $GLOBALS['database']->fetch_data("
                SELECT `users_inventory`.*, `items`.*, `users_inventory`.`id` as `inv_id`
                FROM `users_inventory`, `items`
                WHERE `items`.`id` = `users_inventory`.`iid` AND `trading` is null AND ".$query."
                FOR UPDATE"
                );
            
            if ($item_data === '0 rows') {
                throw new Exception('This item is currently being traded.');
            }
            else{
                return $item_data;
            }
        }
        else {
            return $item_data;
        }
    }

    // Check if the user has the item in their home with the given item ID
    public function homeHasItem($params, $overWriteTradingLock = false) {

        // Locally save the parameters
        $query = "";
        foreach($params as $key => $value) {
            $query .= ($query === "") ? "".$key." = '".$value."'" :  " AND ".$key." = '".$value."'";
        }

        if (!is_numeric($params['uid'])) {
            throw new Exception('Incorrect item ID: ' . $iid);
        }

        // No limit, we need to return all rows so that we can potentially merge
        $item_data = $GLOBALS['database']->fetch_data("
            SELECT `home_inventory`.*, `items`.*, `home_inventory`.`id` as `inv_id`
            FROM `home_inventory`, `items`
            WHERE `items`.`id` = `home_inventory`.`iid` AND `home_inventory`.`in_storage` = 'no' AND ".$query."
            FOR UPDATE"
        );

        if ($item_data === '0 rows') {
            throw new Exception('You do not have the required item.');
        }

        if ($item_data[0]['trading'] !== null && $overWriteTradingLock !== true) {
            throw new Exception('This item is currently being traded.');
        }
        else {
            return $item_data;
        }
    }

    // Retrieve information about a specific item. No locking
    public function getItemData( $itemID ){
        $itemData = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE id = '".$itemID."' LIMIT 1");
        if( $itemData == "0 rows" ){
            throw new Exception("Could not find the item with the ID: ".$itemID." in the database");
        }
        return $itemData;
    }

    // Check if enough items
    public function canAddItems($idAndAmountArray) {

        // How much inventory space are we adding
        $addedInventorySpace = 0;

        // Go through added items
        if(!empty($idAndAmountArray)) {

            // Check if this item is already in inventory, and if we can add to stack
            foreach($idAndAmountArray as $processedID => $processedAmount) {

                // Check if item was found
                $foundKey = false;
                foreach($this->userInventory as $key => $item) {
                    if($item['id'] === $processedID) { $foundKey = $key; }
                }

                // Get the item and figure out how much space it takes
                $item = $this->getItemData($processedID);
                $itemSize = $item[0]['inventorySpace'];

                // Check if possible to add to stack, otherwise add new stack
                if($foundKey !== false) {
                    if($this->userInventory[$foundKey]['stack'] + $processedAmount > $this->userInventory[$foundKey]['stack_size']) {
                        $addedInventorySpace += $itemSize;
                    }
                }
                else {
                    $addedInventorySpace += $itemSize;
                }
            }
        }
        // Check if we can add this inventory space
        return ($this->currentItems + $addedInventorySpace <= $this->maxitm) ? true : false;
    }

    // Add an item to the user inventory. Doesn't deduct money
    public function addItemToUser($uid, $iid, $amount, $processingFinish = 0, $from = '') {

        // Sanity checks
        $this->setUserAndItemDataForUpdate($uid, $iid, $amount);

        // When using this function, the price is neglected
        $this->user_data[0]['price'] = 0;
        $this->user_data[0]['procesTime'] = $processingFinish;

        // Perform actual insertion
        $this->performItemInsertion($uid, $iid, $amount , false, $from);
    }

    // Perform the actual item insertion: this is called after set-user-and-item-data-for-update  has been called already
    protected function performItemInsertion($uid, $iid, $amount, $checkLimit = true, $from = '') {

        // Check if filling stack or not
        if($amount !== "stock") {

            // Check if this is a stock item or not
            if($this->user_data[0]['stack_size'] > 1) {

                // Check if user already has item or the stack is full
                if ($this->user_data[0]['iid'] === null || $this->user_data[0]['stack'] === $this->user_data[0]['stack_size']
                    || (isset($this->user_data[0]['procesTime']) && $this->user_data[0]['procesTime'] > 0)) { // Insert user item
                    if($this->updateUserMoney($_SESSION['uid'], $amount * $this->user_data[0]['price'])) {
                        $this->insertUserItem($_SESSION['uid'], $iid, $amount, $checkLimit);
                    }
                }
                else { // Update user item

                    if($this->updateUserMoney($_SESSION['uid'], $amount * $this->user_data[0]['price'])) { // Calculate new total
                        $newStack = $this->user_data[0]['stack'] + $amount;



                        // Check if new stack is valid
                        if($newStack > $this->user_data[0]['stack_size']) {
                            // Update the first item
                            $this->updateUserItemStack($_SESSION['uid'], $iid, $this->user_data[0]['inv_id'], $this->user_data[0]['stack_size']);

                            // Insert leftovers
                            $this->insertUserItem($_SESSION['uid'], $iid, $newStack - $this->user_data[0]['stack_size'], $checkLimit);
                        }
                        else { // Check if we should
                            if(!($users_inventory = $GLOBALS['database']->fetch_data("SELECT * FROM `users_inventory` where `iid` = ".$iid." AND `uid` = ".$_SESSION['uid'])))
                                throw new Exception('There was an error trying to recieve necessary information.');

                            $this->updateUserItemStack($_SESSION['uid'], $iid, $this->user_data[0]['inv_id'], $newStack);

                            $quantity = 0;
                            if(is_array($users_inventory))
                            {
                                foreach($users_inventory as $nack)
                                {
                                    if(isset($nack['stack']))
                                    {
                                        $quantity += $nack['stack'];
                                    }
                                }
                            }

                            $GLOBALS['Events']->acceptEvent('item_quantity_gain', array('data'=>$iid, 'context'=>$iid, 'new'=>$quantity+$amount, 'old'=>$quantity));//, 'from'=>$from ));
                        }
                    }
                }
            }
            elseif((int)$this->user_data[0]['stack_size'] === 1) { // Inset user item
                if($this->updateUserMoney($_SESSION['uid'], $this->user_data[0]['price'])) {
                    $this->insertUserItem($_SESSION['uid'], $iid, $amount, $checkLimit);
                }
            }
            else { throw new Exception("Could not figure out the stack size for this item"); }
        }
        elseif($amount === "stock" && $this->user_data[0]['stack_size'] > 1) {
            // Get current stack
            $current_stack = ($this->user_data[0]['iid'] === null) ? 0 : (int)$this->user_data[0]['stack'];

            // Check if user already has stack or not
            if($current_stack === 0 || $current_stack === (int)$this->user_data[0]['stack_size']) { // Inset user item
                $amount = $this->user_data[0]['stack_size'];
                if($this->updateUserMoney($_SESSION['uid'], $amount * $this->user_data[0]['price'])) {
                    $this->insertUserItem($_SESSION['uid'], $iid, $amount, $checkLimit);
                }
            }
            else { // Update user item
                $amount = $this->user_data[0]['stack_size'] - $current_stack;
                if($this->updateUserMoney($_SESSION['uid'], $amount * $this->user_data[0]['price'])) {
                    $this->updateUserItemStack($_SESSION['uid'], $iid, $this->user_data[0]['inv_id'], $this->user_data[0]['stack_size']);

                    if(!($users_inventory = $GLOBALS['database']->fetch_data("SELECT * FROM `users_inventory` where `iid` = ".$iid." AND `uid` = ".$_SESSION['uid'])))
                        throw new Exception('There was an error trying to recieve necessary information.');

                    $quantity = 0;
                    if(is_array($users_inventory))
                    {
                        foreach($users_inventory as $nack)
                        {
                            if(isset($nack['stack']))
                            {
                                $quantity += $nack['stack'];
                            }
                        }
                    }

                    $GLOBALS['Events']->acceptEvent('item_quantity_gain', array('data'=>$iid, 'context'=>$iid, 'new'=>$quantity+$amount, 'old'=>$quantity));//, 'from'=>$from ));
                }
            }
        }
        else { throw new Exception("Trying to stock up on an item that's not stackable is wrong"); }

        // Set the purchased amount to this
        $this->purchaseAmount = $amount;
    }

    // Function for inserting item into users table
    protected function insertUserItem($uid, $iid, $amount, $checkLimit = true, $defaultStackSize = null, $defaultInventoryVolume = null, $merge = 'no') {

        // Get the stack size
        $stackSize = ($defaultStackSize !== null) ? $defaultStackSize : $this->user_data[0]['stack_size'];

        // Get the processing time of item if that has been set
        $processTime = isset( $this->user_data[0]['procesTime']) ? $this->user_data[0]['procesTime'] : 0;
        $processTime = is_numeric( $processTime ) ? $processTime : 0;

        // Get the durability and divide it by three (in case of crafting, it's been multiplied with 3 previously)
        $durability =  isset($this->user_data[0]['durability']) ? $this->user_data[0]['durability'] : 1;

        // Set whether it should be repairable or not
        $repairable =  isset($this->user_data[0]['repairable']) ? $this->user_data[0]['repairable'] : "no";

        // Set the inventory space per item
        $inventorySpace = ($defaultInventoryVolume !== null) ? $defaultInventoryVolume : $this->user_data[0]['inventorySpace'];

        // Calculate how much volume these items would required
        $inventoryVolume = ceil($amount / $stackSize) * $inventorySpace;

        // Confirm max item count if user_data is set
        if ($checkLimit && ($this->currentItems + $inventoryVolume > $this->maxitm)) {  throw new Exception('Your inventory is full.'); }

        // Calculate count bins based on stack size
        $statBins = $this->calculateItemStacks($amount, $stackSize);


        if(!($users_inventory = $GLOBALS['database']->fetch_data("SELECT * FROM `users_inventory` where `iid` = ".$iid." AND `uid` = ".$_SESSION['uid'])))
                throw new Exception('There was an error trying to recieve necessary information.');

        $quantity = 0;
        $stack = 0;
        if(is_array($users_inventory))
        {
            foreach($users_inventory as $nack)
            {
                if(isset($nack['stack']))
                {
                    $stack++;
                    $quantity += $nack['stack'];
                }
            }
        }

        $stack_added = 0;
        $quantity_added = 0;
        // Insert new item stacks
        foreach($statBins as $count) {
            //echo"Testing, don't report. Now inserting item with iid ".$iid." with amount ".$count."<br>";

            // Insert new item
            $GLOBALS['database']->execute_query("INSERT INTO `users_inventory`
                    (`uid`, `iid`, `equipped`, `stack`, `timekey`, `finishProcessing`, `durabilityPoints`, `canRepair`)
                VALUES
                    (".$uid.", ".$iid.", 'no', ".$count.", ".$GLOBALS['user']->load_time.", ".$processTime.", ".$durability.", '".$repairable."');");

            $stack_added++;
            $quantity_added += $count;

            // Update counter
            $this->currentItems += $inventorySpace;
        }

        $GLOBALS['Events']->acceptEvent('item_person', array('data'=>$iid, 'context'=>$iid, 'new'=>$stack+$stack_added, 'old'=>$stack ));

        if($merge != 'merge')
            $GLOBALS['Events']->acceptEvent('item_quantity_gain', array('context'=>$iid, 'new'=>$quantity+$quantity_added, 'old'=>$quantity ));
    }


    // Function for inserting item into users home inventory table
    protected function insertHomeItem($uid, $iid, $amount, $checkLimit = true, $defaultStackSize = null, $defaultInventoryVolume = null, $in_storage) {

        // Get the stack size
        $stackSize = ($defaultStackSize !== null) ? $defaultStackSize : $this->user_data[0]['stack_size'];

        // Get the processing time of item if that has been set
        $processTime = isset( $this->user_data[0]['procesTime']) ? $this->user_data[0]['procesTime'] : 0;
        $processTime = is_numeric( $processTime ) ? $processTime : 0;

        // Get the durability and divide it by three (in case of crafting, it's been multiplied with 3 previously)
        $durability =  isset($this->user_data[0]['durability']) ? $this->user_data[0]['durability'] : 1;

        // Set whether it should be repairable or not
        $repairable =  isset($this->user_data[0]['repairable']) ? $this->user_data[0]['repairable'] : "no";

        // Set the inventory space per item
        $inventorySpace = ($defaultInventoryVolume !== null) ? $defaultInventoryVolume : $this->user_data[0]['inventorySpace'];

        // Calculate how much volume these items would required
        $inventoryVolume = ceil($amount / $stackSize) * $inventorySpace;

        // Confirm max item count if user_data is set
        if ($checkLimit && ($this->currentItems + $inventoryVolume > $this->maxitm)) {  throw new Exception('Your inventory is full.'); }

        // Calculate count bins based on stack size
        $statBins = $this->calculateItemStacks($amount, $stackSize);
        if(!($home_inventory = $GLOBALS['database']->fetch_data("SELECT * FROM `home_inventory` where `iid` = ".$iid." AND `uid` = ".$_SESSION['uid'])))
                throw new Exception('There was an error trying to recieve necessary information.');

        $quantity = 0;
        $stack = 0;
        if(is_array($home_inventory))
        {
            foreach($home_inventory as $nack)
            {
                if(isset($nack['stack']))
                {
                    $stack++;
                    $quantity += $nack['stack'];
                }
            }
        }

        $stack_added = 0;
        $quantity_added = 0;
        // Insert new item stacks
        foreach($statBins as $count) {
            //echo"Testing, don't report. Now inserting item with iid ".$iid." with amount ".$count."<br>";

            // Insert new item
            $GLOBALS['database']->execute_query("INSERT INTO `home_inventory`
                    (`uid`, `iid`, `equipped`, `stack`, `timekey`, `finishProcessing`, `durabilityPoints`, `canRepair`, `fid`, `in_storage`)
                VALUES
                    (".$uid.", ".$iid.", 'no', ".$count.", ".$GLOBALS['user']->load_time.", ".$processTime.", ".$durability.", '".$repairable."', NULL, '".$in_storage."');");

            $stack_added++;
            $quantity_added += $count;

            // Update counter
            $this->currentItems += $inventorySpace;
            $GLOBALS['Events']->acceptEvent('item_home', array('data'=>$iid, 'context'=>$iid, 'new'=>$stack+$stack_added, 'old'=>$stack ));
        }
    }


    // Function to increase/set stack of user item
    protected function updateUserItemStack($uid, $iid, $invID, $newStack) {
        // Confirm max stack count
        if ($newStack <= 0 || (isset($this->user_data) && $newStack > $this->user_data[0]['stack_size'])) {
            throw new Exception('Your stack is already full, tried to set stack to: '.$newStack." from ".$this->user_data[0]['stack']);
        }

        //echo"Testing, don't report. Now updating item with invID ".$invID." with amount ".$newStack."<br>";

        // Increase stack of this item by $this->user_data[0]['stack_size']-$this->user_data[0]['stack'] amount
        $GLOBALS['database']->execute_query("UPDATE `users_inventory`
            SET `users_inventory`.`stack` = ".$newStack."
            WHERE `users_inventory`.`uid` = ".$uid." AND `users_inventory`.`iid` = ".$iid." AND `users_inventory`.`id` = ".$invID." LIMIT 1");
    }

    // Function for updating user money
    protected function updateUserMoney($uid, $price) {
        if((int)$price === 0) { return true; }
        elseif ($price <= $this->user_data[0]['money']) {
            $GLOBALS['database']->execute_query("UPDATE `users_statistics`
                SET `users_statistics`.`money` = `users_statistics`.`money` - ".$price."
                WHERE `users_statistics`.`uid` = ".$uid." LIMIT 1");

            if($price > 0 && $uid == $_SESSION['uid'])
                $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] - $price));
            else if($uid == $_SESSION['uid'])
                $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] - $price));
            else
                throw new Exception ('dosnt support event proc for other users, please update.');


            return true;
        }
        else { throw new Exception('You cannot afford this item'); }
        return false;
    }

    // Get user and item data for update
    protected function setUserAndItemDataForUpdate($uid, $iid, $amount, $inventoryID = null) {

        if (!is_numeric($iid) || !is_numeric($uid)) {
            throw new Exception('The entered item ID or user ID was incorrect: '.$uid." and ".$iid);
        }

        if (!isset($amount) || ($amount !== "stock" && !is_numeric($amount))) {
            throw new Exception('There was something wrong with the amount of items you are trying to buy: '.$amount);
        }

        // Extra selection on inventory ID if neccesary
        $inventorySelector = ($inventoryID !== null) ? " AND `users_inventory`.`id` = ".$inventoryID." " :
            " AND `users_inventory`.`finishProcessing` = 0 AND `users_inventory`.`trading` IS NULL ";

        // Dangerous query
        $query = '
            SELECT
                `bingo_book`.*,
                `items`.*,
                `users_inventory`.*,
                `users_inventory`.`id` AS `inv_id`,
                `village_structures`.`shop_level`,
                `users_loyalty`.`village`,
                `users_statistics`.`user_rank`,
                `users_statistics`.`rank_id`,
                `users_statistics`.`money`
            FROM `bingo_book`
                INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `bingo_book`.`userid`)
                INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `bingo_book`.`userid`)
                INNER JOIN `village_structures` ON (`village_structures`.`name` = `users_loyalty`.`village`)
                LEFT JOIN `items` ON (`items`.`id` = '.$iid.')
                LEFT JOIN `users_inventory`
                ON (`users_inventory`.`uid` = `bingo_book`.`userid` AND `users_inventory`.`iid` = '.$iid.' '.$inventorySelector.' AND `users_inventory`.`stack` > 0)
                LEFT JOIN `users_inventory` AS `stackTory`
                ON (`stackTory`.`uid` = `bingo_book`.`userid` AND `stackTory`.`iid` = '.$iid.'  AND `stackTory`.`finishProcessing` = 0 AND `stackTory`.`trading` IS NULL AND `stackTory`.`stack` > 0  AND `stackTory`.`durabilityPoints` > 0 )
            WHERE `bingo_book`.`userid` = '.$_SESSION['uid'].'
            LIMIT 1 FOR UPDATE';

        // Get the user data
        if (!($this->user_data = $GLOBALS['database']->fetch_data( $query ))) {
            throw new Exception('There was something wrong with the DB query for this user.');
        }

        // Get current item of this type with the lowest stack & overwrite any data in previous array
        $this->lowStackItem = $GLOBALS['database']->fetch_data('
            SELECT *, `id` AS `inv_id`
            FROM `users_inventory`
            WHERE
                `users_inventory`.`iid` = '.$iid.' AND
                `users_inventory`.`uid` = '.$_SESSION['uid'].' '.$inventorySelector.' AND
                `users_inventory`.`stack` > 0 AND
                `users_inventory`.`durabilityPoints` > 0
            ORDER BY `stack` ASC
            FOR UPDATE');

        // Check if any was found
        if($this->lowStackItem !== "0 rows") {

            // Overwrite current stack
            $this->user_data[0]['currentStack'] = count( $this->lowStackItem );

            // Save the lowest stack item
            if(!empty($this->lowStackItem)) {
                $this->user_data[0] = array_merge($this->user_data[0], $this->lowStackItem[0]);
            }
        }
        else{
            $this->user_data[0]['currentStack'] = 0;
        }

        /* Test message
        echo"<font size='+4'>Don't report, I'm testing. ~Terr</font><pre />";
        print_r($this->user_data[0]);
        echo"Added";
        print_r($this->lowStackItem[0]);
        */

        // Check that the user was found
        if ($this->user_data === '0 rows') { throw new Exception('This user could not be found in the database.'); }
    }

    // A function for selecting an item for update
    public function selectItemForUpdate($whereArray, $limit = 1) {
        // Where part
        $part2 = "";
        foreach($whereArray as $key => $value) { $part2 .= ($part2 === "") ? "".$key." = '".$value."'" : " AND ".$key." = '".$value."'"; }

        // Return result
        return $GLOBALS['database']->fetch_data("SELECT * FROM `users_inventory`, `items`
            WHERE `items`.`id` = `users_inventory`.`iid` AND ".$part2." LIMIT ".$limit." FOR UPDATE");
    }

    // Update user item, no checks are performed
    public function updateUserItem($updateArray, $whereArray) {
        // Update part
        $part1 = "";
        foreach($updateArray as $key => $value) {
            $setTo = ($value === "NULL") ? "NULL" : "'".$value."'";
            $part1 .= ($part1 === "") ? "`".$key."` = ".$setTo : ", `".$key."` = ".$setTo;
        }

        // Where part
        $part2 = "";
        foreach($whereArray as $key => $value) { $part2 .= ($part2 === "") ? "`".$key."` = '".$value."'" : " AND `".$key."` = '".$value."'"; }

        // Gather query for debugging
        $query = "UPDATE `users_inventory` SET ".$part1." WHERE ".$part2." LIMIT 1";

        // Run
        if($GLOBALS['database']->execute_query($query) === false) {
            throw new Exception('There was an error updating the user item!');
        }
    }

    // Update user home item, no checks are performed
    public function updateHomeItem($updateArray, $whereArray) {
        // Update part
        $part1 = "";
        foreach($updateArray as $key => $value) {
            $setTo = ($value === "NULL") ? "NULL" : "'".$value."'";
            $part1 .= ($part1 === "") ? "`".$key."` = ".$setTo : ", `".$key."` = ".$setTo;
        }

        // Where part
        $part2 = "";
        foreach($whereArray as $key => $value) { $part2 .= ($part2 === "") ? "`".$key."` = '".$value."'" : " AND `".$key."` = '".$value."'"; }

        // Gather query for debugging
        $query = "UPDATE `home_inventory` SET ".$part1." WHERE ".$part2." LIMIT 1";

        // Run
        if($GLOBALS['database']->execute_query($query) === false) {
            throw new Exception('There was an error updating the user item!');
        }
    }

    // Reduce user items by given amount
    public function reduceNumberOfItems( $uid, $iid, $number ){

        // Get the user item
        $this->item_data = $this->userHasItem(
            array(
                "uid" => $uid,
                "iid" => $iid
            )
        );

        // Keep removing items until number has been reduced
        foreach( $this->item_data as $item ){
            if( $number > 0 ){
                if( $item['stack'] > $number ) {
                    $this->updateUserItemStack(
                        $uid,$iid , // user & item id
                        $item['inv_id'], // inventory id
                        ($item['stack'] - $number) // updated stack
                    );//
                    $GLOBALS['Events']->acceptEvent('item_quantity_loss', array('context'=>$iid, 'count'=>$number*-1, 'old'=>$item['stack'], 'new'=>$item['stack'] - $number ));
                }
                else {
                    $this->removeUserItem(
                        array(
                            "uid" => $uid,
                            "id" => $item['inv_id']
                        )
                    );
                }
                $number -= $item['stack'];
            }
        }

        // Test that enough were removed
        if( $number > 0 ) {
            throw new Exception("You do not have the required item.");
        }
    }

    // Remove user item, no checks are performed on data
    public function removeUserItem($whereArray, $limit = 1, $exceptionIDs = null, $inventoryVolume = 1, $merge = 'no') {

        // Where part
        $part2 = "";
        foreach($whereArray as $key => $value)
        {
            $part2 .= ($part2 === "") ? "".$key." = '".$value."'" : " AND ".$key." = '".$value."'";
        }

        // Exception part
        if(!empty($exceptionIDs)) { $part2 .= " AND `users_inventory`.`id` NOT IN (".implode(",",$exceptionIDs).")"; }

        // Update counter
        $this->currentItems -= $inventoryVolume;


        //getting items for delete
        if(!($rows_for_deletion = $GLOBALS['database']->fetch_data("SELECT * FROM `users_inventory` WHERE ".$part2." LIMIT ".$limit)))
                throw new Exception('There was an error trying to recieve necessary information.');

        $index = array();
        $list_of_ids = array();
        $query = "SELECT * FROM `users_inventory` WHERE ";
        foreach($rows_for_deletion as $row)
        {
            $list_of_ids[] = $row['id'];
            $index[$row['uid'].'.'.$row['iid']] = array('uid'=>$row['uid'], 'iid'=>$row['iid']);
            $query .= "(`uid` = ".$row['uid']." AND `iid` = ".$row['iid'].") OR ";
        }
        $query = rtrim($query,' OR ');

        //getting all items for counting
        if(!($all_needed_items = $GLOBALS['database']->fetch_data($query)))
                throw new Exception('There was an error trying to recieve necessary information.');

        foreach($index as $item)
        {
            $stack = $stack_removed = $quantity = $quantity_removed = 0;
            foreach($all_needed_items as $nick)
            {
                if($nick['iid'] == $item['iid'] && $nick['uid'] == $item['uid'])
                {
                    $stack++;
                    $quantity += $nick['stack'];

                    if(in_array($nick['id'],$list_of_ids))
                    {
                        $stack_removed++;
                        $quantity_removed+=$nick['stack'];
                    }
                }
            }

            if($item['uid'] == $_SESSION['uid'])
            {
                $GLOBALS['Events']->acceptEvent('item_person', array('data'=>'!'.$item['iid'], 'context'=>$item['iid'], 'new'=>$stack-$stack_removed, 'old'=>$stack ));

                if($merge != 'merge')
                    $GLOBALS['Events']->acceptEvent('item_quantity_loss', array('context'=>$item['iid'], 'new'=>$quantity+$quantity_removed, 'old'=>$quantity ));
            }
            else
            {
                throw new expection("tell koala to stop being lazy and just finish the thing. also screen cap this for him.");
            }
        }

        // Run
        if($GLOBALS['database']->execute_query("DELETE FROM `users_inventory` WHERE ".$part2." LIMIT ".$limit) === false) {
            throw new Exception("There was an error removing the item");
        }
    }


    // Remove user item, no checks are performed on data
    public function removeHomeItem($whereArray, $limit = 1, $exceptionIDs = null, $inventoryVolume = 1) {

        // Where part
        $part2 = "";
        foreach($whereArray as $key => $value)
        {
            $part2 .= ($part2 === "") ? "`".$key."` = '".$value."'" : " AND `".$key."` = '".$value."'";

            if($key == 'iid' || $key == '`iid`')
                $GLOBALS['Events']->acceptEvent('item_home', array('data'=>$value, 'count'=>'all' ));

            if($key == 'fid' || $key == '`fid`')
                $GLOBALS['Events']->acceptEvent('item_furniture', array('data'=>$value, 'count'=>'all' ));
        }

        // Exception part
        if(!empty($exceptionIDs)) { $part2 .= " AND `home_inventory`.`id` NOT IN (".implode(",",$exceptionIDs).")"; }

        // Update counter
        $this->currentItems -= $inventoryVolume;

        //getting items for delete
        if(!($rows_for_deletion = $GLOBALS['database']->fetch_data("SELECT * FROM `home_inventory` WHERE ".$part2." LIMIT ".$limit)))
                throw new Exception('There was an error trying to recieve necessary information.');

        $index = array();
        $list_of_ids = array();
        $query = "SELECT * FROM `home_inventory` WHERE ";
        foreach($rows_for_deletion as $row)
        {
            $list_of_ids[] = $row['id'];
            $index[$row['uid'].'.'.$row['iid']] = array('uid'=>$row['uid'], 'iid'=>$row['iid']);

            if($row['iid'] != '')
                $query .= "(`uid` = ".$row['uid']." AND `iid` = ".$row['iid'].") OR ";
            else if($row['fid'] != '')
                $query .= "(`uid` = ".$row['uid']." AND `fid` = ".$row['fid'].") OR ";
        }
        $query = rtrim($query,' OR ');

        //getting all items for counting
        if(!($all_needed_items = $GLOBALS['database']->fetch_data($query)))
                throw new Exception('There was an error trying to recieve necessary information.');

        foreach($index as $item)
        {
            $stack = $stack_removed = 0;
            foreach($all_needed_items as $nick)
            {
                if( (($nick['iid'] == $item['iid'] && $nick['iid'] != '') || ($nick['fid'] == $item['fid'] && $nick['fid'] != '')) && $nick['uid'] == $item['uid'])
                {
                    $stack++;

                    if(in_array($nick['id'],$list_of_ids))
                    {
                        $stack_removed++;
                    }
                }
            }

            if($item['uid'] == $_SESSION['uid'])
            {
                if($item['iid'] != '')
                    $GLOBALS['Events']->acceptEvent('item_home', array('data'=>'!'.$item['iid'], 'context'=>$item['iid'], 'new'=>$stack-$stack_removed, 'old'=>$stack ));
                else if($item['fid'] != '')
                    $GLOBALS['Events']->acceptEvent('item_furniture', array('data'=>'!'.$item['fid'], 'context'=>$item['fid'], 'new'=>$stack-$stack_removed, 'old'=>$stack ));
            }
            else
            {
                throw new expection("tell koala to stop being lazy and just finish the thing. also screen cap this for him.");
            }
        }

        // Run
        if($GLOBALS['database']->execute_query("DELETE FROM `home_inventory` WHERE `home_inventory`.`in_storage` = 'no' AND ".$part2." LIMIT ".$limit) === false) {
            throw new Exception("There was an error removing the item");
        }
    }


    // Calculate the contents of resulting stacks
    private function calculateItemStacks($totalItems, $stack_size) {
        $stackItemsCount = array(0);
        for($i = 0; $i < $totalItems; $i++) {
            $curStack = count($stackItemsCount) - 1;
            if($stackItemsCount[ $curStack ] < $stack_size) { $stackItemsCount[$curStack]++; }
            else { $stackItemsCount[] = 1; }
        }
        return $stackItemsCount;
    }

    // Function for splitting a user stack
    public function splitUserStackInHalf($uid, $invID, $location = "user" ) {

        // Set the user & item for update
        if($location == 'user')
        {
            $item_data = $this->userHasItem(array("uid" => $uid, "`users_inventory`.`id`" => $invID ));
        }
        elseif($location == 'home')
        {
            $item_data = $this->homeHasItem(array("uid" => $uid, "`home_inventory`.`id`" => $invID));
        }
        else
        {
            throw new InvalidArgumentException("location must be user or home");
        }

        if($item_data) {

            // Save item id
            $iid = $item_data[0]['id'];

            // Check that the stack is higher than 1
            if($item_data[0]['stack'] <= 1) { throw new Exception("You cannot split a stack of 1"); }

            // Only processes can be split
            // Check that the stack is higher than 1
            if(!in_array($item_data[0]['type'], array("process", "material", "item", "reduction", "special", "repair"), true)) {
                throw new Exception("You can only split stackable items. This type is: ".$item_data[0]['type']);
            }

            // Calculate new stack amounts
            $newItemStack = floor($item_data[0]['stack'] / 2);
            $oldItemStack = $item_data[0]['stack'] - $newItemStack;

            // Insert new stack
            if($location == 'user')
                $this->insertUserItem($uid, $iid, $newItemStack, false, $item_data[0]['stack_size'], $item_data[0]['inventorySpace'], 'merge');
            else if($location == 'home')
                $this->insertHomeItem($uid, $iid, $newItemStack, false, $item_data[0]['stack_size'], $item_data[0]['inventorySpace'], $item_data[0]['in_storage']);
            else
                throw new InvalidArgumentException('location must be home or user.');

            $timeMod = 1;
            // Check for global event modifications
            if( $event = functions::getGlobalEvent("ModifyCraftTime")){
                if( isset( $event['data']) && is_numeric( $event['data']) ){
                    $timeMod = round($event['data'] / 100,2);
                }
            }

            // Processing time
            if($item_data[0]['finishProcessing'] > 0) { $item_data[0]['finishProcessing'] -= ( floor($item_data[0]['craftProcessMinutes'] * $timeMod * 60 * $newItemStack)); }

            // update old stack & processing time if there

            if($location == 'user')
                $this->updateUserItem(
                    array("stack" => $oldItemStack, "finishProcessing" => $item_data[0]['finishProcessing']),
                    array("uid" => $uid, "id" => $invID ));
            else if($location == 'home')
                $this->updateHomeItem( //here
                    array("stack" => $oldItemStack, "finishProcessing" => $item_data[0]['finishProcessing']),
                    array("uid" => $uid, "id" => $invID ));
            else
                throw new InvalidArgumentException('location must be home or user.');

            return true;
        }
    }

    // Function for merging a user stack with any similar items
    public function mergeUserStacks($uid, $iid, $location = 'user', $merging_all = false) {
        // Set the user & item for update
        if($location == 'user')
            $item_data = $this->userHasItem(array("uid" => $uid, "iid" => $iid), true);
        else if($location == 'home')
            $item_data = $this->homeHasItem(array("uid" => $uid, "iid" => $iid), true);
        else
            throw new InvalidArgumentException('location must be user or home');

        if($item_data  && (count($item_data) > 1 || !$merging_all)) {
            // Check that the number of items is higher than 1
            if(count($item_data) <= 1) { throw new Exception("You cannot merge the stacks of this item any more than what has already been done"); }

            // Calculate total stack
            $totalItems = $inventoryVolume = 0;
            $processingIDs = array();

            foreach($item_data as $item) {
                if((int)$item['finishProcessing'] === 0 && $item['trading'] === null) {
                    $totalItems += $item['stack'];
                    $inventoryVolume += $item['inventorySpace'];
                }
                else { $processingIDs[] = $item['inv_id']; }
            }

            // only if there are some items
            if($totalItems > 0) {

                // Remove the user items from current inventory
                if($location == 'user')
                    $this->removeUserItem(array("uid" => $uid, "iid" => $iid), count($item_data), $processingIDs, $inventoryVolume, 'merge');
                else if($location == 'home')
                    $this->removeHomeItem(array("uid" => $uid, "iid" => $iid), count($item_data), $processingIDs, $inventoryVolume);
                else
                    throw new InvalidArgumentException('location must be user or home');


                // Insert new stack
                if($location == 'user')
                    $this->insertUserItem($uid, $iid, $totalItems, false, $item_data[0]['stack_size'], $item_data[0]['inventorySpace'], 'merge');
                else if($location =='home')
                    $this->insertHomeItem($uid, $iid, $totalItems, false, $item_data[0]['stack_size'], $item_data[0]['inventorySpace'], $item_data[0]['in_storage']);
                else
                    throw new InvalidArgumentException('location must be user or home');

            }
            return true;
        }
    }

    public function merge_all($location)
    {
        if($location == 'user')
        {
            $iid_and_count_list = $GLOBALS['database']->fetch_data("SELECT `iid`, count(`iid`) as 'count' FROM `users_inventory` inner join `items` on (`items`.`id` = `users_inventory`.`iid`) where `uid` = {$_SESSION['uid']} and `equipped` = 'no' and `stack_size` > 1 group by `iid`");
        }
        else
        {
            $iid_and_count_list = $GLOBALS['database']->fetch_data("SELECT `iid`, count(`iid`) as 'count' FROM `home_inventory` inner join `items` on (`items`.`id` = `home_inventory`.`iid`) where `uid` = {$_SESSION['uid']} and `stack_size` > 1 and `in_storage` = 'no' group by `iid`");
        }

        foreach($iid_and_count_list as $iid_and_count)
        {
            if($iid_and_count['count'] > 1 && $location == 'user')
                $this->mergeUserStacks( $_SESSION['uid'], $iid_and_count['iid'],'user', true );
            else if($iid_and_count['count'] > 1 && $location == 'home')
            {
                $this->mergeUserStacks( $_SESSION['uid'], $iid_and_count['iid'], 'home', true );
            }
        }
    }

    // Function to show details for item
    public function show_item_details($iid, $restrictions = null) {
        if(!is_numeric($iid)) { throw new Exception('Incorrect item ID: '.$iid); }

        $item_data = $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `id` = '".$iid."' LIMIT 1");

        if($item_data === '0 rows') { throw new Exception('This item does not exist.'); }

        // Check if this item is in this shop
        if (isset($restrictions)) {

            // Check the type
            if(!in_array($item_data[0]['in_shop'], $restrictions, true)) {
                throw new Exception("This item is not within this type of shop.");
            }

            // Check the date
            if( !functions::checkStartEndDates($item_data[0]) ){
                throw new Exception("This is a time-limited item only available from ".date("m/d/Y",$item_data[0]['start_date'])." to ".date("m/d/Y",$item_data[0]['end_date']) );
            }
        }

        // Template
        $template = isset($this->params['smartyTemplate']) ? $this->params['smartyTemplate'] : "contentLoad";

        // Show some information to the user
        $GLOBALS['template']->assign('item_data', $item_data);
        $GLOBALS['template']->assign('Details', functions::parse_BB($item_data[0]['description']));

        $GLOBALS['template']->assign('effectsOnUse', jutsuBasicFunctions::parseEffects($item_data[0]['on_use_tags']));
        $GLOBALS['template']->assign('effectsOnJutsu', jutsuBasicFunctions::parseEffects($item_data[0]['on_jutsu_tags']));
        $GLOBALS['template']->assign('effectsOnEquip', jutsuBasicFunctions::parseEffects($item_data[0]['when_equipped_tags']));

        $GLOBALS['template']->assign($template , './templates/content/item_shop/shop_show_details.tpl');
    }

    // Show details for the item
    public function show_details($uid, $inv_id, $location = 'user') {

        if($location == 'user')
            $item_data = $this->userHasItem(array("uid" => $uid, "`users_inventory`.`id`" => $inv_id), true);
        else if($location == 'home')
            $item_data = $this->homeHasItem(array("uid" => $uid, "`home_inventory`.`id`" => $inv_id), true);
        else
            throw new InvalidArgumentException('location must be home or user');


        if($item_data) {
            $GLOBALS['template']->assign('item_data', $item_data);
            $GLOBALS['template']->assign('Details', functions::parse_BB($item_data[0]['description']));

            if($location == 'home')
                $GLOBALS['template']->assign('extralink', "&act=inventory");
            else
                $GLOBALS['template']->assign('extralink', "");

            $GLOBALS['template']->assign('effectsOnUse', jutsuBasicFunctions::parseEffects($item_data[0]['on_use_tags']));
            $GLOBALS['template']->assign('effectsOnJutsu', jutsuBasicFunctions::parseEffects($item_data[0]['on_jutsu_tags']));
            $GLOBALS['template']->assign('effectsOnEquip', jutsuBasicFunctions::parseEffects($item_data[0]['when_equipped_tags']));

            $GLOBALS['template']->assign('contentLoad', './templates/content/equip/equip_show_detail.tpl');
        }
    }
}