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

require_once(Data::$absSvrPath.'/libs/professionSystem/professionLib.php');
require_once(Data::$absSvrPath.'/libs/itemSystem/itemFunctions.php');
class shopLib extends itemBasicFunctions{

    //  Function for displaying a given itemshop
    public function setupShopSystem($params) {

        // Save the params
        $this->params = $params;
        
        // Check for active transactions
        $this->hasTransaction = false;
        
        // Load basic data related to items
        parent::__construct();
        
        try{
            
            // Get links for the current page, used for navigation
            $this->link = functions::get_current_link( array("id","act") );
            $this->returnLink = functions::get_current_link( array("id","act") );

            // Figure out which action to perform
            if (!isset($_GET['act2']) && !isset($_GET['iid'])) 
            {
                // Load profession data for discount checking
                $this->loadProfessionData();
                
                // Get village data
                $this->setVillageData();
                
                // Parse the shop
                $this->parse_shop();
                
            } elseif (ctype_digit($_GET['iid']) && $_GET['iid'] > 0) {
                
                // Default data for items - sets max items & current items etc
                parent::__construct();

                if ($_GET['act2'] == "details") 
                {
                    $this->show_shop_details();
                } 
                elseif ($_GET['act2'] == "buy") 
                {
                    // Load profession data for discount checking
                    $this->loadProfessionData();
                    
                     // Get village data
                    $this->setVillageData();
                    
                    // Buy the item
                    $this->buy_item( $_SESSION['uid'], $_GET['iid'], '1');
                } 
                elseif ($_GET['act2'] == "stockUp") 
                {
                     // Get village data
                    $this->setVillageData();
                    
                    // Buy the item stock
                    $this->buy_item( $_SESSION['uid'], $_GET['iid'], "stock" );
                } 
                else 
                {
                    throw new Exception("There was an error understanding your request.");
                }
            } else 
            {
                throw new Exception("There was an error in your item ID request.");
            }
        } catch (Exception $e) {
            
            // Rollback possible transactions
            if( $this->hasTransaction == true ){
                $GLOBALS['database']->transaction_rollback($e->getMessage());
            }
            
            $GLOBALS['page']->Message( $e->getMessage() , $this->params['shopName'], 'id='.$_GET['id'], "Return", $this->params['smartyTemplate']);
        }
    }
    
    // Function to parse the itemshop
    private function parse_shop() {

        // Get the table parser
        $this->min = tableParser::get_page_min();
        $this->order = tableParser::get_page_order(array("name", "price", "item"));

        // Get the query for selecting the items
        $itemQuery = $this->getItemQuery();
        
        // Collect Bingo Book Data
        if (!$items = $GLOBALS['database']->fetch_data($itemQuery)) {
            throw new Exception("There was an error trying to receive the items from the database");
        }

        // Update item names with stack sizes + create stock up field
        if ($items !== "0 rows") {
            
            // Keep track of items not to be shown
            $inactiveItems = array();
            
            // Go through items
            for ($i = 0; $i < count($items); $i++) {
                
                // Stack-changes
                if ($items[$i]['stack_size'] > 1) {
                    $items[$i]['name'] .= " (" . $items[$i]['stack_size'] . ")";
                    $items[$i]['stockUp'] = "<a class='showTableLink' href='".$this->link."&act2=stockUp&iid=".$items[$i]['id']."'>Stock Up</a>";
                }
                else{
                    $items[$i]['stockUp'] = "N/A";
                }

                // On map shop price
                if (isset($this->params['is_map']) && $this->params['is_map']) {
                    $items[$i]['price'] = $items[$i]['price'] * 1.5;
                }
                
                // Discounts
                $items[$i] = $this->setDiscount($items[$i]);
                
                // Check dates
                if( !functions::checkStartEndDates($items[$i]) ){
                    $inactiveItems[] = $i;
                }
            }
            
            // Remove all the inactive items
            $inactiveItems = array_reverse($inactiveItems);
            foreach( $inactiveItems as $index ){
                unset( $items[$index] );
            }
        }

        // Set descriptions
        $description = "";
        if (isset($this->params['shopDescriptions'])) {
            $description .= $this->params['shopDescriptions']."<br>";
        }
        $description .= "You currently have <b>" . $GLOBALS['userdata'][0]['money'] . " Ryo</b> and your inventory holds <b>".$this->currentItems." / ".$this->maxitm."</b> items.";

        // Set the top options if more than one
        $topOptions = false;
        if (isset($this->params['types']) && count($this->params['types']) > 1) {
            $topOptions = array();
            foreach ($this->params['types'] as $type) {
                $topOptions[] = array("name" => ucfirst($type), "href" => $this->link . "&type=" . $type);
            }
        }
        
        // Set the working directory
        $dir = (isset($this->params['dirCorrection'])) ? $this->params['dirCorrection'] : "";

        // Create option links
        $optionArray = array();
        foreach( array("Details","Buy") as $key ){
            $optionArray[] = array("name" => $key, "id" => $_GET['id'], "act2" => strtolower($key), "iid" => "table.id");
            if( isset($_GET['act']) ){
                $optionArray[ count($optionArray)-1 ]['act'] = $_GET['act'];
            }
        }
        
        // Decide on what entries to show
        $showEntries = array();
        $showEntries['name'] = "Name (max. stack)";
        if( isset($items[0]['type']) && $items[0]['type'] == "weapon"){
            $showEntries['weapon_classifications'] = "Weapon Class";
        }
        $showEntries['price'] = "Price";
        $showEntries['stockUp'] = "Stock";
        
            
        // Show the table of users
        tableParser::show_list(
                $this->params['smartyTemplate'], 
                $this->params['shopName'], 
                $items, 
                $showEntries, // Main fields
                $optionArray, // option links 
                false, // Send directly to contentLoad
                true, // Show previous/next links
                $topOptions, // Category links
                true, // Allow sorting on columns
                false, // pretty-hide options
                false, // No search box on top
                $description, // Description at top
                $dir
        );

        // SubSelect variable, needed for smarty inclusion
        $GLOBALS['template']->assign("subSelect", $this->params['smartyTemplate'] );
        
        // Return Link
        $GLOBALS['template']->assign("returnLink", true);
        
        // Things for the backend to work
        $this->setShopToken();
        $setup = $this->encodeSetup();
        $GLOBALS['template']->assign('shopToken', $this->shopToken );
        $GLOBALS['template']->assign('setupData', $setup );
    }
    
    // Function to show details for item
    protected function show_shop_details() {
        
        // Show item, restrictions on shop
        $this->show_item_details( $_GET['iid'], $this->params['in_shop'] );        
    }

    // Function to buy an item
    private function buy_item( $uid, $iid, $amount ) {
        
        // Start transaction
        $GLOBALS['database']->transaction_start();
        $this->hasTransaction = true;

        // Sanity checks
        $this->setUserAndItemDataForUpdate( $uid, $iid, $amount );
        
        // Set the discount from profession if available
        $this->user_data[0] = $this->setDiscount($this->user_data[0]);
        
        // For shops, set the durability to 1/3
        $this->user_data[0]['durability'] = ceil( $this->user_data[0]['durability'] * 0.75);
        $this->user_data[0]['repairable'] = "no";
        
        // Check that the item in question conforms to the requeirements from the setup
        if( $this->isItemGood( $this->user_data ) ){
            
            // Perform actual insertion
            $this->performItemInsertion( $uid, $iid, $amount, $this->params['shopName'] );
            
        }
        
        // Message to the user
        $GLOBALS['page']->Message( 
                'You have bought ' . $this->purchaseAmount . ' ' . functions::pluralize($this->user_data[0]['name'], $this->purchaseAmount) . ' for ' . ($this->purchaseAmount * $this->user_data[0]['price']) . ' ryo.' , 
                $this->params['shopName'], 
                trim($this->returnLink,"?"), 
                "Return", 
                $this->params['smartyTemplate']
        );

        // Commit transaction
        $GLOBALS['database']->transaction_commit();
        $this->hasTransaction = false;
    }

    //  Create item query
    private function getItemQuery() {

        // Begin creating query
        $query = "";

        // Required Rank selection
        if (isset($this->params['required_rank'])) {
            $query .= " `required_rank` <= '" . $this->params['required_rank'] . "' ";
        }

        // Item level selection
        if (isset($this->params['item_level'])) {
            $query .= ($query == "") ? " `item_level` <= '" . $this->params['item_level'] . "' " : " AND `item_level` <= '" . $this->params['item_level'] . "' ";
        }

        // In shop selection
        if (isset($this->params['in_shop'])) {
            $subQuery = "(";
            foreach ($this->params['in_shop'] as $type) {
                $subQuery .= ($subQuery == "(") ? "`in_shop` = '" . $type . "'" : "OR `in_shop` = '" . $type . "'";
            }
            $subQuery .= ") ";
            $query .= ($query == "") ? $subQuery : " AND " . $subQuery;
        }

        // Type selection
        if (isset($this->params['types'])) {
            $type = ( isset($_GET['type']) && in_array($_GET['type'], $this->params['types']) ) ? $_GET['type'] : $this->params['types'][0];
            $query .= ($query == "") ? " `type` = '" . $type . "' " : " AND `type` = '" . $type . "' ";
        }
        
        // Village restriction
        $query .= ($query == "") ? 
                " ( `village_restriction` = 'ALL' OR  `village_restriction` = '".$GLOBALS['userdata'][0]['village']."') " : 
                " AND ( `village_restriction` = 'ALL' OR  `village_restriction` = '".$GLOBALS['userdata'][0]['village']."') ";


        // Prepend start of query
        $query = "SELECT * FROM `items` WHERE " . $query;

        // Put in ORDER
        if (isset($this->order)) {
            $query .= " " . $this->order . " ";
        }

        // Put in LIMIT
        if (isset($this->min)) {
            $query .= " LIMIT " . $this->min . ",10";
        }

        // Return query
        return $query;
    }

    // Calculated an encoded string for the setup
    protected function encodeSetup(){
        $encodedSetup = json_encode($this->params);
         $serialized = urlencode($encodedSetup);
        return $serialized;
    }
    
    // Decode the setup
    protected function decodeSetup( $string ){
        $decodedSetup = urldecode( $string );
        $unserialized = json_decode( $decodedSetup , true );
        return $unserialized;
    }
    
    // Function for setting a chat token for interaction with the backend
    protected function setShopToken(){
        
        // If we have a original setup, use that, otherwise use the constructor
        $setup = isset($this->params['originalSetup']) ? $this->params['originalSetup'] : $this->params;
        
        // Create the chat token from user data & chat setup
        $this->shopToken = functions::createHash( 
            array_merge( array( $GLOBALS['userdata'][0]['id'], $GLOBALS['userdata'][0]['login_id'] ) , $setup )
        );
    }

    // Check if item is good for this shop setup
    protected function isItemGood( $data ){
        
        // Check for max stacks
        if( isset($this->user_data[0]['currentStack'], $this->user_data[0]['max_stacks'], $this->user_data[0]['stack']) ){
            if( $this->user_data[0]['currentStack'] > 0 && $this->user_data[0]['max_stacks'] > 0 ){
                if( 
                    // Filling up current stack
                    ( $this->user_data[0]['stack'] < $this->user_data[0]['stack_size'] &&
                    $this->user_data[0]['currentStack'] > $this->user_data[0]['max_stacks'] ) || 
                    // Starting a new stack
                    ( $this->user_data[0]['stack'] >= $this->user_data[0]['stack_size'] &&
                    $this->user_data[0]['currentStack'] >= $this->user_data[0]['max_stacks'] )
                ){
                    throw new Exception("You can not have more of these items in your inventory. Only ".$this->user_data[0]['max_stacks']." stacks allowed and you have ".$this->user_data[0]['currentStack']);
                }
            }
        }
        
        // Check in shop
        if (isset($this->params['in_shop'])) {
            if( !in_array($data[0]['in_shop'], $this->params['in_shop']) ){
                throw new Exception("This item is not within this type of shop.");
            }
        }
        
        // Check dates
        if( !functions::checkStartEndDates($data[0]) ){
            throw new Exception("This is a time-limited item only available from ".date("m/d/Y",$data[0]['start_date'])." to ".date("m/d/Y",$data[0]['end_date']) );
        }
        
        // Check price
        if ( $data[0]['price'] > $data[0]['money'] ) {
            throw new Exception('You cannot afford this item');
        }

        // Check rank
        if ($data[0]['required_rank'] > $data[0]['rank_id']) {
            throw new Exception('You do not have the rank to acquire this item.');
        }
        
        // If tool, check if user already owns
        if( $data[0]['type'] == "tool" && isset($this->user_data[0]['iid'] ) ){
            throw new Exception("You can only buy one of each tool.");
        }
        
        // Check village respect for epic tiers
        $village_respect = $data[0][$data[0]['village']];
        $user_tier = ($village_respect >= 500000) ? intval(floor($village_respect / 500000)) : 0;

        if ($data[0]['item_level'] <= $data[0]['shop_level']) {
            if (!in_array($data[0]['village_restriction'], array('ALL', $data[0]['village']), true)) {
                throw new Exception('You cannot acquire this item in your village.');
            }
        } elseif ($data[0]['item_level'] >= 10) {
            if ($data[0]['item_level'] <= $user_tier + 9) {
                if (!in_array($data[0]['village_restriction'], array('ALL', $data[0]['village']), true)) {
                    throw new Exception('You cannot acquire this item in your village.');
                }
            } else {
                throw new Exception('You are not high enough tier to acquire this item.');
            }
        } else {
            throw new Exception('You are not high enough level to acquire this item.');
        }
        
        return true;
    }
    

}
