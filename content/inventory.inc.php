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
require_once(Data::$absSvrPath.'/libs/home/home_helper.php');
require_once(Data::$absSvrPath.'/content/home.inc.php');

class inventory2 extends itemBasicFunctions
{
    public $user_proffesion;
    public $inventory_raw;
    public $inventory_equipped;
    public $inventory_pack;
    public $max_pack_size;

    public function __construct()
    {
        //opening try block
        try
        {

            functions::checkActiveSession();

            //getting lock
            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            //starting db transaction
            $GLOBALS['database']->transaction_start();

            //running constructor on parent
            parent::__construct();


            //if no action, then display normal invintory page.
            if(!isset($_GET['act']))
            {
                $this->display_inventory();
            }

            //if act is detail then
            else if($_GET['act'] == "details")
            {
                //call to itemFunctions function show_details
                $this->show_details($_SESSION['uid'], $_GET['inv_id']);
            }

            //if act is equip
            else if($_GET['act'] == "equip")
            {
                //call the equip function with user id and inventory item id then display inventory
                $this->equip_item($_SESSION["uid"], $_GET['inv_id']);
                $this->display_inventory();
            }

            //if act is use
            else if($_GET['act'] == "use")
            {
                //call the do use item function with user id, item id, and inventory id
                $this->do_use_item($_SESSION["uid"], $_GET["iid"], $_GET["inv_id"]);
            }

            //if act is sell confirmation
            else if($_GET["act"] == "selected" && isset($_POST['Sell_Selected']) )
            {
                if( isset($_POST['Submit']))
                {
                    //unpacking post data array pasted through the confirm page
                    $_POST['inventoryIDs'] = explode(':',$_POST['inventoryIDs']);
                    $this->sellItemList();
                }
                else
                {
                    $message = 'Are you sure you would like to sell ';

                    if ( count($_POST['inventoryIDs']) > 1)
                        $message .= 'these items: <br>';
                    else
                        $message .= 'this item: ';

                    if(!($results = $GLOBALS['database']->fetch_data('
                        SELECT `users_inventory`.`id`, `users_inventory`.`stack`, `items`.`name`
                        FROM `users_inventory`
                        INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid`)
                        WHERE `users_inventory`.`uid` = '.$_SESSION['uid'].' AND `users_inventory`.`id` IN ('.implode(",", $_POST['inventoryIDs']).') AND
                        (`users_inventory`.`trading` IS NULL OR `users_inventory`.`trade_type` = "repair") LIMIT '.count($_POST['inventoryIDs']))))
                    {
                        throw new Exception("No items to sell. Remember, you can't sell professional tools and you can't sell items you're trading");
                    }

                    if(is_array($results))
                    {
                        foreach($results as $result)
                        {
                            $message .= $result['stack'] . 'x' . $result['name'] . '<br>';
                        }

                        $GLOBALS['page']->Confirm($message, 'Inventory System', 'Sell Now!', 'contentLoad', 'Sell_Selected', $_POST['Sell_Selected'], 'inventoryIDs', implode( ':',$_POST['inventoryIDs']));
                    }
                    else
                        $GLOBALS['page']->Message('Nothing suitable for sale here.' , 'Inventory System', 'id='.$_GET['id'],'Return');

                }
            }

            //if act is transfer
            else if ($_GET["act"] == "selected" && isset($_POST['Transfer_Selected']))
            {
                //call teh transfer item list function
                if( isset($_POST['Submit']) )
                {
                    //unpacking post data array pasted through the confirm page
                    $_POST['inventoryIDs'] = explode(':',$_POST['inventoryIDs']);
                    $this->transferItemList();
                }
                else
                {
                    $message = 'Are you sure you would like to transfer ';

                    if ( count($_POST['inventoryIDs']) > 1)
                        $message .= 'these items: <br>';
                    else
                        $message .= 'this item: ';

                    if(!($results = $GLOBALS['database']->fetch_data('
                        SELECT `users_inventory`.`id`, `users_inventory`.`stack`, `items`.`name`
                        FROM `users_inventory`
                        INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid`)
                        WHERE `users_inventory`.`uid` = '.$_SESSION['uid'].' AND `users_inventory`.`id` IN ('.implode(",", $_POST['inventoryIDs']).') AND
                        (`users_inventory`.`trading` IS NULL) LIMIT '.count($_POST['inventoryIDs']))))
                    {
                        throw new Exception("No items to sell. Remember, you can't sell professional tools and you can't sell items you're trading");
                    }


                    if(is_array($results))
                        foreach($results as $result)
                        {
                            $message .= $result['stack'] . 'x' . $result['name'] . '<br>';
                        }
                    else
                        $message .= 'No good items!<br>';

                    $GLOBALS['page']->Confirm($message, 'Inventory System', 'Transfer Now!', 'contentLoad', 'Transfer_Selected', $_POST['Transfer_Selected'], 'inventoryIDs', implode( ':',$_POST['inventoryIDs']));
                }
            }
            else if($_GET['act'] == 'merge_all_user')
            {
                $this->merge_all('user');
                $this->display_inventory();
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false)
            {
                throw new Exception('There was an issue releasing the lock!');
            }


            //commiting db transaction
            $GLOBALS['database']->transaction_commit();
        }

        //catching errors
        catch (Exception $e)
            {
                $GLOBALS['database']->transaction_rollback($e->getMessage());
                $GLOBALS['page']->Message($e->getMessage() , 'Inventory System', 'id='.$_GET['id'],'Return');
            }
    }


    //assigns variables, templates, and calles fill_inventory
    private function display_inventory()
    {
        //call fill inventory to populate page variables
        $this->fill_inventory();

        //get response

        //assigning template(displaying results of constructor formated as detailed by inventory_main.tpl)

        if($GLOBALS['page']->isAsleep && ($GLOBALS['page']->isHome || $GLOBALS['page']->isOutlaw))
            $GLOBALS['template']->assign('transfer_available', true);
        else
            $GLOBALS['template']->assign('transfer_available', false);


        $GLOBALS['template']->assign('inventory_equipped', $this->inventory_equipped);
        $GLOBALS['template']->assign('inventory_pack', $this->inventory_pack);
        $GLOBALS['template']->assign('pack_count', $this->checkPackSize($this->inventory_pack));
        $GLOBALS['template']->assign('max_pack_size', $this->max_pack_size);
        $GLOBALS['template']->assign('contentLoad','./templates/content/inventory/inventory_main.tpl');
    }


    //method that does all of the data proccessing to prepare variables for the template.
    private function fill_inventory()
    {
        //setting default staring value
        $this->max_pack_size = 6;

        //check if anything needs merged or split
        $this->mergeAndSplit();

        $totalRepel = 0;
        $this->inventory_raw = $GLOBALS['database']->fetch_data("
            SELECT
                `users_inventory`.*,`users_inventory`.`id` as `inv_id`,
                `items`.`name`,`items`.`use`,
                `items`.`stack_size`,`items`.`inventorySpace`,
                `items`.`price`,`items`.type,`items`.`armor_types`,
                `items`.`required_rank`, `items`.`consumable`,
                `items`.`durability` as `max_durability`,
                `items`.`inventorySpace`, `items`.`armor`,
                `items`.`mastery`, `items`.`stability`,
                `items`.`accuracy`, `items`.`expertise`,
                `items`.`chakra_power`, `items`.`critical_strike`
            FROM `users_inventory`,`items`
            WHERE
                `uid` = '" . $_SESSION['uid'] . "' AND
                `iid` = `items`.`id` AND
                `durabilityPoints` > 0 AND
                `stack` > 0
            ORDER BY `type`,`name` ASC");

        //fixing empty issue.
        if(!is_array($this->inventory_raw))
            $this->inventory_raw = array();

        //fix data loop if need be.
        for($i = 0; $i < count($this->inventory_raw); $i++)
        {
            // Set information
                $this->inventory_raw[$i] = $this->setItemOptions($this->inventory_raw[$i]);

                if($this->inventory_raw[$i]['inventorySpace'] != '1')
                    $this->inventory_raw[$i]['inventorySpace'] = 0;

                // Fix up repel
                if ($this->inventory_raw[$i]['type'] == 'armor')
                {

                    // Increase the repel if armor has some
                    if( $this->inventory_raw[$i]['equipped'] == "yes" &&
                        stristr($this->inventory_raw[$i]['use'], "REPEL:")
                    ){
                        $tempSplit = explode(":", $this->inventory_raw[$i]['use']);
                        $totalRepel += $tempSplit[1];
                    }
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

        //pull out equipped
        $this->inventory_equipped = range(0,10);
        $i = 0;
        $armor = 0;
        $armor_weapon = array();
        $mastery = 0;
        $mastery_weapon = array();
        $stability = 0;
        $stability_weapon = array();
        $accuracy = 0;
        $accuracy_weapon = array();
        $expertise = 0;
        $expertise_weapon = array();
        $chakra_power = 0;
        $chakra_power_weapon = array();
        $critical_strike = 0;
        $critical_strike_weapon = array();

        foreach($this->inventory_raw as $item)
        {

            if(     $item['equipped'] == "yes" && $item['armor_types'] == "helmet")
            {
                $this->inventory_equipped[0] = $item;
                $armor += $item['armor'];
                $mastery += $item['mastery'];
                $stability += $item['stability'];
                $accuracy += $item['accuracy'];
                $expertise += $item['expertise'];
                $chakra_power += $item['chakra_power'];
                $critical_strike += $item['critical_strike'];
            }
            else if($item['equipped'] == "yes" && $item['armor_types'] == "armor")
            {
                $this->inventory_equipped[1] = $item;
                $armor += $item['armor'];
                $mastery += $item['mastery'];
                $stability += $item['stability'];
                $accuracy += $item['accuracy'];
                $expertise += $item['expertise'];
                $chakra_power += $item['chakra_power'];
                $critical_strike += $item['critical_strike'];
            }
            else if($item['equipped'] == "yes" && $item['armor_types'] == "belt")
            {
                $this->inventory_equipped[2] = $item;
                $armor += $item['armor'];
                $mastery += $item['mastery'];
                $stability += $item['stability'];
                $accuracy += $item['accuracy'];
                $expertise += $item['expertise'];
                $chakra_power += $item['chakra_power'];
                $critical_strike += $item['critical_strike'];
            }
            else if($item['equipped'] == "yes" && $item['armor_types'] == "gloves")
            {
                $this->inventory_equipped[3] = $item;
                $armor += $item['armor'];
                $mastery += $item['mastery'];
                $stability += $item['stability'];
                $accuracy += $item['accuracy'];
                $expertise += $item['expertise'];
                $chakra_power += $item['chakra_power'];
                $critical_strike += $item['critical_strike'];
            }
            else if($item['equipped'] == "yes" && $item['armor_types'] == "pants")
            {
                $this->inventory_equipped[4] = $item;
                $armor += $item['armor'];
                $mastery += $item['mastery'];
                $stability += $item['stability'];
                $accuracy += $item['accuracy'];
                $expertise += $item['expertise'];
                $chakra_power += $item['chakra_power'];
                $critical_strike += $item['critical_strike'];
            }
            else if($item['equipped'] == "yes" && $item['armor_types'] == "shoes")
            {
                $this->inventory_equipped[5] = $item;
                $armor += $item['armor'];
                $mastery += $item['mastery'];
                $stability += $item['stability'];
                $accuracy += $item['accuracy'];
                $expertise += $item['expertise'];
                $chakra_power += $item['chakra_power'];
                $critical_strike += $item['critical_strike'];
            }
            else if($item['equipped'] == "yes" && $item['type'] == "weapon" )
            {
                $this->inventory_equipped[6+$i] = $item;
                $i++;
                $armor_weapon[] = $item['armor'];
                $mastery_weapon[] = $item['mastery'];
                $stability_weapon[] = $item['stability'];
                $accuracy_weapon[] = $item['accuracy'];
                $expertise_weapon[] = $item['expertise'];
                $chakra_power_weapon[] = $item['chakra_power'];
                $critical_strike_weapon[] = $item['critical_strike'];
            }
            else if($item['equipped'] == "yes" && $item['type'] == "tool" )
            {
                $this->inventory_equipped[10] = $item;

                //adding 3 additional pack space for having a tool equipped.
                $profession_name = HomeHelper::getProfessionName($_SESSION['uid']);
                if(($item['name'] == 'Herbalist Pouch'  && $profession_name == 'Herbalist') ||
                   ($item['name'] ==  'Hunters Toolkit' && $profession_name == 'Hunter') ||
                   ($item['name'] ==  'Miners Toolkit'  && $profession_name == 'Miner'))
                $this->max_pack_size += 4;
            }

        }

        if($i>0)
        {
            $armor += array_sum($armor_weapon)/$i;
            $mastery += array_sum($mastery_weapon)/$i;
            $stability += array_sum($stability_weapon)/$i;
            $accuracy += array_sum($accuracy_weapon)/$i;
            $expertise += array_sum($expertise_weapon)/$i;
            $chakra_power += array_sum($chakra_power_weapon)/$i;
            $critical_strike += array_sum($critical_strike_weapon)/$i;
        }

        $GLOBALS['template']->assign('armor',$armor);
        $GLOBALS['template']->assign('mastery',$mastery);
        $GLOBALS['template']->assign('stability',$stability);
        $GLOBALS['template']->assign('accuracy',$accuracy);
        $GLOBALS['template']->assign('expertise',$expertise);
        $GLOBALS['template']->assign('chakra_power',$chakra_power);
        $GLOBALS['template']->assign('critical_strike',$critical_strike);

        //find max inventory size
        if($GLOBALS['userdata'][0]['federal_level'] == "None")
            $this->max_pack_size += 0;
        else if($GLOBALS['userdata'][0]['federal_level'] == "Normal")
            $this->max_pack_size += 1;
        else if($GLOBALS['userdata'][0]['federal_level'] == "Silver")
            $this->max_pack_size += 3;
        else if($GLOBALS['userdata'][0]['federal_level'] == "Gold")
            $this->max_pack_size += 5;

        //filling pack
        $this->inventory_pack = array();
        foreach($this->inventory_raw as $item)
        {
            if($item['equipped'] == "no")
            {
                array_push( $this->inventory_pack, $item);
            }
        }

        //determin and update encumberment status
        if($this->checkPackSize($this->inventory_pack) > $this->max_pack_size)
        {
            if(!$GLOBALS['userdata'][0]['over_encumbered'])
                $GLOBALS['database']->overEncumbered();
        }
        else if($GLOBALS['userdata'][0]['over_encumbered'])
            $GLOBALS['database']->underEncumbered();

    }


    //counting pack size
    private function checkPackSize($pack)
    {
        $pack_size = 0;

        foreach($pack as $item)
            if($item['inventorySpace'] != 0)
                $pack_size++;

        return $pack_size;
    }


    //functions stolen from equip.inc.php
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
        } elseif (($item['type'] == 'armor' || $item['type'] == 'weapon' || $item['type'] == 'tool') && $GLOBALS['userdata'][0]['rank_id'] >= $item['required_rank']) {
            $item['action'] = $equipLink;
        } elseif ($item['type'] == 'armor' || $item['type'] == 'weapon') {
            $item['action'] = "N/A";
        } elseif( $item['stack_size'] > 1 ){
            $item = $this->addMergeAndSplitActions( $item , array_column($this->inventory_raw, "iid"), false, true, true);
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

    // Check merge and split actions
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
        }
        catch (Exception $e) {
            // User did not have profession tool
        }

        // The list of items
        $list = array();

        // Check that list is specified
        if( !isset($_POST['inventoryIDs']) ||
            empty($_POST['inventoryIDs']) ||
            count($_POST['inventoryIDs']) === 0)
        {
            throw new Exception('No items selected for sale.');
        }

        // Add ids to list
        foreach( $_POST['inventoryIDs'] as $key => $value ){
            if(!ctype_digit($value) ) {
                throw new Exception('Invalid item id: '.$value );
            }
            elseif( !in_array( $value, $toolIDs , true ) ){
                $list[] = $value;
            }
            else
            {
                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'Sale of a tool used by your profession is not allowed.'));
            }
        }

        // Sell the list of items
        $this->do_sell_itemList( $list, $_SESSION['uid'] );
    }



    private function transferItemList()
    {
        if(isset($_POST['inventoryIDs']))
        {
            //checking if the transfers can be made
            //find the storage available for each category
            $home = new home();
            $home->house_data_setup();
            $home_inventory = $home->getInventory();


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
            }
            catch (Exception $e) {
                // User did not have profession tool
            }

            $counts;
            $counts['anything'] = count($home_inventory['item_array']['anything']);
            $counts['weapon'] = count($home_inventory['item_array']['weapon']);
            $counts['armor'] = count($home_inventory['item_array']['armor']);
            $counts['tools'] = count($home_inventory['item_array']['tool']);
            $counts['food'] = count($home_inventory['item_array']['food']);
            $counts['metal'] = count($home_inventory['item_array']['metal']);
            $counts['gem'] = count($home_inventory['item_array']['gem']);
            $counts['leather'] = count($home_inventory['item_array']['leather']);
            $counts['book'] = count($home_inventory['item_array']['book']);

            //get the item information for each inventory id
            $items;
            if(!($items = $GLOBALS['database']->fetch_data("SELECT `users_inventory`.`id`, `users_inventory`.`trading`, `items`.`content_type` FROM `users_inventory` INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid`) WHERE `users_inventory`.`id` IN (".implode(",",$_POST['inventoryIDs']).")")))
            {
                throw new Exception('There was an error trying to receive necessary information.');
            }
            //fixing empty issue.
            if(!is_array($items))
                $items = array();

            $not_enough_space = false;
            $not_enough_count = 0;
            $profession_tool_flag = false;

            //then transfer each item tracking the counts
            foreach($items as $item)
                if(!is_numeric($item['trading']))
                {
                    if($item['content_type'] == 'tools')
                        $item['content_type'] = 'tool';

                    if(!isset($home_inventory['storage'][$item['content_type']]))
                    {
                        $doc ="
                        Bad home/item data: '{$item['content_type']}' type missing from storage.
                        <br><br>
                        <details>
                            <summary>
                                Item
                            </summary>
                            <pre>
                                ".print_r($item, true)."
                            </pre>
                        </details>
                        <details>
                            <summary>
                                Storage
                            </summary>
                            <pre>
                                ".print_r($home_inventory['storage'], true)."
                            </pre>
                        </details>
                        ";
                        throw new exception($doc);
                    }

                    if($item['content_type'] != "anything" && $home_inventory['storage'][$item['content_type']] > $counts[$item['content_type']])
                    {
                        if(!in_array( $item['id'], $toolIDs , true ))
                        {
                            HomeHelper::transferItemFromUserToHome($item['id']);
                            $counts[$item['content_type']]++;
                        }
                        else
                            $profession_tool_flag = true;
                    }
                    else if( $home_inventory['storage']['anything'] > $counts['anything'] )
                    {
                        if(!in_array( $item['id'], $toolIDs , true ))
                        {
                            HomeHelper::transferItemFromUserToHome($item['id']);
                            $counts['anything']++;
                        }
                        else
                            $profession_tool_flag = true;
                    }
                    else
                    {
                        $not_enough_space = true;
                        $not_enough_count++;
                    }
                }
                else
                {
                    if($item['trading'] == 1)
                        $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You may not transfer an item if it is being repaired. 1 item was not transfered.'));
                    else
                        $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You may not transfer an item if it is being traded. 1 item was not transfered.'));
                }
        }
        if($not_enough_space)
        {
            if($not_enough_count < count($items))
            {
                if($not_enough_count == 1)
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'There was not enought space in your home. 1 item was not transfered.'));
                else
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'There was not enought space in your home. '.$not_enough_count.' items were not transfered.'));
            }
            else
                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'Your home is full. Nothing was transfered.'));
        }

        if($profession_tool_flag)
            $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'Transfer of a tool used by your profession is not allowed.'));

        $this->display_inventory();
    }
}

new inventory2();