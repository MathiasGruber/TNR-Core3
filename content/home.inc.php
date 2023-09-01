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

    // Based on sleep library
    require_once(Data::$absSvrPath.'/libs/villageSystem/sleepLib.php');
    require_once(Data::$absSvrPath.'/libs/home/home_helper.php');
    require_once(Data::$absSvrPath.'/libs/itemSystem/itemFunctions.php');

    class home extends sleepLibrary {

        private $item_basic_function;
        public $items;
        public $storage_box;
        public $take_out_cost = 500;

        // Constructor
        public function __construct() {

            // Try-Catch
            try {

                functions::checkActiveSession();

                $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

                // Setup house data
                self::house_data_setup();

                $this->mergeAndSplit();

                //saving divorced users from being stuck asleep
                if ($this->user[0]['apartment'] === NULL && $this->user[0]['status'] == 'asleep' && $GLOBALS['userdata'][0]['village'] != "Syndicate")
                {
                    //echo 'UPDATE `users` SET
                    //`status` = "awake"
                    //WHERE `users`.`id` = '.$this->user[0]['id'];
                    $GLOBALS['Events']->acceptEvent('status', array('new'=>'awake', 'old'=> $GLOBALS['userdata'][0]['status']));

                    $GLOBALS['database']->execute_query('UPDATE `users` SET
                    `status` = "awake"
                    WHERE `users`.`id` = '.$this->user[0]['id']);

                    $this->user[0]['status'] = 'awake';
                }

                // Check if user has a home
                if ($this->user[0]['apartment'] === NULL) {

                    // Check to see if Apartment was Bought
                    if (isset($_GET['act']) && $_GET['act'] === 'buy') {

                        // Check if Apartment ID is a number
                        if( isset($_GET['house_id']) && ctype_digit($_GET['house_id']) === true) {

                            // Check to see if Apartment ID is a valid house they can buy
                            for($i = 0, $size = count($this->house_data); $i < $size; $i++) {
                                if($this->house_data[$i]['house_id'] === $_GET['house_id']) {
                                    $GLOBALS['database']->transaction_start();
                                    self::buy_home($i);
                                    $GLOBALS['database']->transaction_commit();
                                    break;
                                }
                                else { if($i === $size - 1) { throw new Exception("You cannot buy that home"); } }
                            }
                        }
                        else { throw new Exception("This is not a valid house. Please try again!"); }
                    }
                    else {
                        // Show a list of the homes to buy



                        if(isset($_GET['act']))
                            if( $GLOBALS['userdata'][0]['village'] != "Syndicate" || ($GLOBALS['userdata'][0]['village'] == "Syndicate" && $this->user[0]['status'] == 'asleep') )
                                if ($_GET['act'] === 'inventory')
                                {
                                    $GLOBALS['database']->transaction_start();
                                    self::homeInventory();
                                    $GLOBALS['database']->transaction_commit();
                                }
                                else if ($_GET['act'] === 'selection' && isset($_POST['Sell_Selected']))
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
                                            SELECT `home_inventory`.`id`, `home_inventory`.`stack`, `items`.`name`
                                            FROM `home_inventory`
                                            INNER JOIN `items` ON (`items`.`id` = `home_inventory`.`iid`)
                                            WHERE `home_inventory`.`uid` = '.$_SESSION['uid'].' AND `home_inventory`.`id` IN ('.implode(",", $_POST['inventoryIDs']).')
                                            LIMIT '.count($_POST['inventoryIDs']))))
                                        {
                                            throw new Exception("No items to sell. Remember, you can't sell professional tools and you can't sell items you're trading");
                                        }

                                        foreach($results as $result)
                                        {
                                            $message .= $result['stack'] . 'x' . $result['name'] . '<br>';
                                        }

                                        $GLOBALS['page']->Confirm($message, 'Inventory System', 'Sell Now!', 'contentLoad', 'Sell_Selected', $_POST['Sell_Selected'], 'inventoryIDs', implode( ':',$_POST['inventoryIDs']));
                                    }
                                }
                                elseif ($_GET['act'] === 'selection' && isset($_POST['Transfer_Selected']))
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
                                            SELECT `home_inventory`.`id`, `home_inventory`.`stack`, `items`.`name`
                                            FROM `home_inventory`
                                            INNER JOIN `items` ON (`items`.`id` = `home_inventory`.`iid`)
                                            WHERE `home_inventory`.`uid` = '.$_SESSION['uid'].' AND `home_inventory`.`id` IN ('.implode(",", $_POST['inventoryIDs']).')
                                            LIMIT '.count($_POST['inventoryIDs']))))
                                        {
                                            throw new Exception("No items to transfer. Remember, you can't transfer professional tools and you can't sell items you're trading");
                                        }

                                        foreach($results as $result)
                                        {
                                            $message .= $result['stack'] . 'x' . $result['name'] . '<br>';
                                        }

                                        $GLOBALS['page']->Confirm($message, 'Inventory System', 'Transfer Now!', 'contentLoad', 'Transfer_Selected', $_POST['Transfer_Selected'], 'inventoryIDs', implode( ':',$_POST['inventoryIDs']));
                                    }
                                }
                                elseif ($_GET['act'] === 'wake' && $GLOBALS['userdata'][0]['village'] == "Syndicate") {
                                    $GLOBALS['database']->transaction_start();
                                    parent::wakeup(false);
                                    $GLOBALS['database']->transaction_commit();
                                }
                                else
                                    if($GLOBALS['userdata'][0]['village'] != "Syndicate")
                                        self::home_list();
                                    else
                                    {
                                        $GLOBALS['database']->transaction_start();
                                        self::homeInventory();
                                        $GLOBALS['database']->transaction_commit();
                                    }
                            else
                                throw new Exception("You must be set up your camp before you can access its inventory. 1");
                        else
                            if($GLOBALS['userdata'][0]['village'] != "Syndicate")
                                self::home_list();
                            else
                                if($this->user[0]['status'] == 'asleep')
                                {
                                    $GLOBALS['database']->transaction_start();
                                    self::homeInventory();
                                    $GLOBALS['database']->transaction_commit();
                                }
                                else
                                throw new Exception("You must be set up your camp before you can access its inventory. 2");

                    }
                }
                else if (ctype_digit($this->user[0]['apartment']) === true) {

                    // Decide what page to show
                    if (!isset($_GET['act'])) { self::home_main(); }
                    elseif ($_GET['act'] === 'sell') {
                        if (!isset($_POST['Submit'])) { self::confirm_sell(); }
                        else {
                            $GLOBALS['database']->transaction_start();
                            self::sell_home();
                            $GLOBALS['database']->transaction_commit();
                        }
                    }
                    elseif ($_GET['act'] === 'sleep') {
                        $GLOBALS['database']->transaction_start();
                        parent::sleep(true);
                        $GLOBALS['database']->transaction_commit();
                    }
                    elseif ($_GET['act'] === 'wake') {
                        $GLOBALS['database']->transaction_start();
                        parent::wakeup(true);
                        $GLOBALS['database']->transaction_commit();
                    }
                    elseif ($_GET['act'] === 'list') {
                        self::home_list();
                    }
                    elseif ($_GET['act'] === 'inventory')
                    {
                        if(isset($_GET['process']))
                            if($_GET['process'] == "take_out")
                            {
                                $GLOBALS['database']->transaction_start();
                                self::takeOut();
                                $GLOBALS['database']->transaction_commit();
                            }

                        $GLOBALS['database']->transaction_start();
                        self::homeInventory();
                        $GLOBALS['database']->transaction_commit();
                    }
                    else if($_GET['act'] == 'merge_all_home')
                    {
                        $this->item_basic_functions->merge_all('home');
                        self::homeInventory();
                    }
                    elseif ($_GET['act'] === 'furniture')
                    {
                        $GLOBALS['database']->transaction_start();
                        self::furniture();
                        $GLOBALS['database']->transaction_commit();
                    }
                    elseif ($_GET['act'] === 'buy') {
                        throw new Exception("You cannot buy a home when you already have one.");
                    }
                    elseif ($_GET['act'] === 'selection' && isset($_POST['Sell_Selected']))
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
                                            SELECT `home_inventory`.`id`, `home_inventory`.`stack`, `items`.`name`
                                            FROM `home_inventory`
                                            INNER JOIN `items` ON (`items`.`id` = `home_inventory`.`iid`)
                                            WHERE `home_inventory`.`uid` = '.$_SESSION['uid'].' AND `home_inventory`.`id` IN ('.implode(",", $_POST['inventoryIDs']).')
                                            LIMIT '.count($_POST['inventoryIDs']))))
                            {
                                throw new Exception("No items to sell. Remember, you can't sell professional tools and you can't sell items you're trading");
                            }

                            foreach($results as $result)
                            {
                                $message .= $result['stack'] . 'x' . $result['name'] . '<br>';
                            }

                            $GLOBALS['page']->Confirm($message, 'Inventory System', 'Sell Now!', 'contentLoad', 'Sell_Selected', $_POST['Sell_Selected'], 'inventoryIDs', implode( ':',$_POST['inventoryIDs']));
                        }
                    }
                    elseif ($_GET['act'] === 'selection' && isset($_POST['Transfer_Selected']))
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
                                SELECT `home_inventory`.`id`, `home_inventory`.`stack`, `items`.`name`
                                FROM `home_inventory`
                                INNER JOIN `items` ON (`items`.`id` = `home_inventory`.`iid`)
                                WHERE `home_inventory`.`uid` = '.$_SESSION['uid'].' AND `home_inventory`.`id` IN ('.implode(",", $_POST['inventoryIDs']).')
                                LIMIT '.count($_POST['inventoryIDs']))))
                            {
                                throw new Exception("No items to transfer. Remember, you can't transfer professional tools and you can't sell items you're trading");
                            }

                            foreach($results as $result)
                            {
                                $message .= $result['stack'] . 'x' . $result['name'] . '<br>';
                            }

                            $GLOBALS['page']->Confirm($message, 'Inventory System', 'Transfer Now!', 'contentLoad', 'Transfer_Selected', $_POST['Transfer_Selected'], 'inventoryIDs', implode( ':',$_POST['inventoryIDs']));
                        }
                    }
                    elseif ($_GET['act'] === 'details')
                    {
                        $this->item_basic_functions->show_details($_SESSION['uid'], $_GET['inv_id'], 'home');
                    }
                    else {
                        throw new Exception("Invalid Action Attempted. Please Try Again.");
                    }
                }
                else {
                    throw new Exception('Invalid home ID: ' . $this->user[0]['apartment']);
                }

                if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                    throw new Exception('There was an issue releasing the lock!');
                }
            }
            catch (Exception $e) {

                // Rollback transaction
                $GLOBALS['database']->transaction_rollback($e->getMessage());

                // Check for return message preferences
                $returnLink = isset($this->returnLink) ? $this->returnLink : 'id='.$_GET['id'];
                $returnMessage = isset($this->returnMessage) ? $this->returnMessage : 'Return';

                // Give a message
                $GLOBALS['page']->Message($e->getMessage(), "Home", $returnLink, $returnMessage);
            }
        }

        // Setup house information
        public function house_data_setup() {

            $this->item_basic_functions = new itemBasicFunctions();

            // Gather All Necessary Information
            if(!($this->user = $GLOBALS['database']->fetch_data('SELECT `users`.`id`, `users`.`apartment`, `users`.`status`,
                `users_statistics`.`money`, `users_statistics`.`rank_id`,
                `users_loyalty`.`vil_loyal_pts` as `user_respect`, `users_loyalty`.`village` AS `user_village`,

                `homes`.`id` AS `house_id`, `homes`.`name`, `homes`.`price`, `homes`.`regen`, `homes`.`required_rank`,
                `homes`.`married_home`, `homes`.`loyaltyReq`, `homes`.`furniture_slots`, `homes`.`inventory_slots`,

                `marriages`.`married`,

                `spouse`.`village` AS `spouse_village`,
                `spouse`.`vil_loyal_pts` AS `spouse_respect`,
                `spouse_user`.`rank_id` AS `spouse_rank_id`
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                    INNER JOIN `homes` ON (`homes`.`id` > 0)
                    LEFT JOIN `marriages` ON (`marriages`.`married` = "Yes" AND (`marriages`.`uid` = `users`.`id` OR `marriages`.`oid` = `users`.`id`))
                    LEFT JOIN `users_loyalty` AS `spouse` ON (`spouse`.`uid` = IF(`marriages`.`uid` != `users`.`id`, `marriages`.`uid`, `marriages`.`oid`))
                    LEFT JOIN `users_statistics` AS `spouse_user` ON (`spouse_user`.`uid` = `spouse`.`uid`)
                WHERE `users`.`id` = '.$_SESSION['uid']))) {
                throw new Exception('There was an error trying to receive necessary information.');
            }

            // Check that something was found
            if($this->user === '0 rows') { throw new Exception('here was an error trying to receive user information.'); }

            // Rank limiter
            $rankLimit = $this->user[0]['rank_id'];
            $loyaltyLimit = $this->user[0]['user_respect'];
            $married = $this->user[0]['married'];

            // Check if married
            if($married === "Yes") {
                $marriagerankLimit = $this->user[0]['spouse_rank_id'];
                $marriageloyaltyLimit = $this->user[0]['spouse_respect'];
                $user_village = $this->user[0]['user_village'];
                $spouse_village = $this->user[0]['spouse_village'];
            }

            // Compile Allowed House Data
            $this->house_data = array();
            for($i = 0, $size = count($this->user); $i < $size; $i++) {
                if($this->user[$i]['required_rank'] <= $rankLimit) { // Check House Required Rank Constraint
                    if($this->user[$i]['loyaltyReq'] <= $loyaltyLimit || ($loyaltyLimit < 0 && $this->user[$i]['loyaltyReq'] == 0) || $this->user[$i]['apartment'] == $this->user[$i]['house_id'] ) { // Check Loyalty Constraint
                        if($this->user[$i]['married_home'] === "Yes") { // Check Married Home
                            if($married === "Yes") { // Are You Married?
                                if($user_village == $spouse_village) {
                                    if($this->user[$i]['required_rank'] <= $marriagerankLimit) { // Couple meet Rank Req?
                                        if($this->user[$i]['loyaltyReq'] <= $marriageloyaltyLimit || ($marriageloyaltyLimit < 0 && $this->user[$i]['loyaltyReq'] == 0) || $this->user[$i]['apartment'] == $this->user[$i]['house_id'] ) { // Couple meet Loyalty Req?
                                            array_push($this->house_data, array('house_id' => $this->user[$i]['house_id'],
                                                'name' => $this->user[$i]['name'], 'price' => $this->user[$i]['price'],
                                                'regen' => $this->user[$i]['regen'], 'married_home' => $this->user[$i]['married_home'],
                                                'furniture_slots' => $this->user[$i]['furniture_slots'], 'inventory_slots' => $this->user[$i]['inventory_slots']));
                                        }
                                    }
                                }
                            }
                        }
                        else { // Add Regular House
                            array_push($this->house_data, array('house_id' => $this->user[$i]['house_id'], 'name' => $this->user[$i]['name'],
                                'price' => $this->user[$i]['price'], 'regen' => $this->user[$i]['regen'],
                                'married_home' => $this->user[$i]['married_home'], 'furniture_slots' => $this->user[$i]['furniture_slots'],
                                'inventory_slots' => $this->user[$i]['inventory_slots']));
                        }
                    }
                }
                unset($this->user[$i]['house_id'], $this->user[$i]['name'], $this->user[$i]['price'], $this->user[$i]['regen'],
                    $this->user[$i]['required_rank'], $this->user[$i]['married_home'], $this->user[$i]['loyaltyReq'],
                    $this->user[$i]['married'], $this->user[$i]['spouse_village'], $this->user[$i]['spouse_respect'],
                    $this->user[$i]['spouse_rank_id']);
            }

            // Get Rid of Extra User Data
            for($i = count($this->user); $i > 0; $i--) { unset($this->user[$i]); }
        }

        // Buying homes
        private function home_list() {

            // Make green/red the house price
            for ($i = 0, $size = count($this->house_data); $i < $size; $i++) {
                $this->house_data[$i]['price'] = ($this->user[0]['money'] >= $this->house_data[$i]['price']) ?
                    "<font color='green'>".$this->house_data[$i]['price']."</font>" :
                    "<font color='red'>".$this->house_data[$i]['price']."</font>";
            }

            //sorting house data
            usort($this->house_data, 'home::cmp');

            if($this->user[0]['apartment'] === NULL)
                $storage_box_link = '<a href="?id='.$_GET['id'].'&act=inventory">Go to your storage box.</a>';
            else
                $storage_box_link = "";

            // View the homes
            tableParser::show_list(
                'homeList',
                "Available Homes for Purchase",
                $this->house_data,
                array(
                    'name' => "Name",
                    'price' => "Price",
                    'regen' => "Comfort Rate",
                    'furniture_slots' => "Furniture Space",
                    'inventory_slots' => "Inventory Space",
                    'married_home' => "Partner?"
                ),
                array(
                    array(
                        "name" => "Buy",
                        "id" => $_GET['id'],
                        "act" => "buy",
                        "house_id" => "table.house_id"
                    )
                ),
                true,   // Send directly to contentLoad
                false,   // Show previous/next links
                false,  // No links at the top to show
                false,   // Allow sorting on columns
                false,   // pretty-hide options
                false, // Top stuff
                array('message'=>"Buying a home will give you a place to sleep and regenerate faster. Respect in your village may unlock new homes. ".$storage_box_link." Current respect: ".$this->user[0]['user_respect'],'hidden'=>'yes') // Top information
            );
        }

        // Buy home with given ID
        private function buy_home( $choice ) {

            // Set user information
            if(!($this->user = $GLOBALS['database']->fetch_data('SELECT `users`.`id`, `users`.`apartment`,
                `users`.`status`,
                `users_statistics`.`money`, `users_loyalty`.`village` AS `user_village`,
                `homes`.`married_home`, `marriages`.`married`, `marriages`.`uid`, `marriages`.`oid`,
                `spouse`.`village` AS `spouse_village`, `spouse_user`.`apartment` AS `spouse_apartment`, `spouse_statistics`.`money` as `spouse_money`
                FROM `users`
                    INNER JOIN `homes` ON (`homes`.`id` = '.$this->house_data[$choice]['house_id'].')
                    INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    LEFT JOIN `marriages` ON (`marriages`.`married` = "Yes"
                        AND (`marriages`.`uid` = `users`.`id` OR `marriages`.`oid` = `users`.`id`))
                    LEFT JOIN `users_loyalty` AS `spouse` ON (`spouse`.`uid` =
                        IF(`marriages`.`uid` != `users`.`id`, `marriages`.`uid`, `marriages`.`oid`))
                    LEFT JOIN `users` AS `spouse_user` ON (`spouse_user`.`id` =
                        IF(`marriages`.`uid` != `users`.`id`, `marriages`.`uid`, `marriages`.`oid`))
                    LEFT JOIN `users_statistics` AS `spouse_statistics` ON (`spouse_statistics`.`uid` =
                        IF(`marriages`.`uid` != `users`.`id`, `marriages`.`uid`, `marriages`.`oid`))
                WHERE `users`.`id` = '.$_SESSION['uid'].' LIMIT 1 FOR UPDATE'))) {
                throw new Exception('There was an error trying to receive necessary information.');
            }

            // Check that the user information was found
            if($this->user === '0 rows') {
                throw new Exception("Either you're not awake or you don't exist. Lets hope it's not the latter!");
            }

            // Check status
            if($this->user[0]['status'] !== 'awake') {
                throw new Exception('You must be awake to buy a house!');
            }

            // Get the buyer UID
            $buyer = ($this->user[0]['id'] === $this->user[0]['uid']) ? $this->user[0]['uid'] : $this->user[0]['oid'];

            // Get the spouse UID
            $spouse = ($this->user[0]['id'] !== $this->user[0]['uid']) ? $this->user[0]['uid'] : $this->user[0]['oid'];

            // Check if user already has apartment
            if($this->user[0]['apartment'] !== NULL) { throw new Exception("You already have a house you're living in!"); }

            // Check price
            if ($this->user[0]['money'] < $this->house_data[$choice]['price']) { throw new Exception("You don't have enough money to purchase this house!"); }

            // Check if home is for married people or not
            if($this->house_data[$choice]['married_home'] === 'Yes') {

                // Must be married
                if($this->user[0]['married'] !== 'Yes') { throw new Exception("You aren't married, therefore cannot purchase a couple's house!"); }

                // Check spouse aparment. Only one possible
                if($this->user[0]['spouse_apartment'] !== NULL) {
                    throw new Exception("You cannot purchase a couple's house if your spouse already has a house.");
                }

                // Same village only
                if($this->user[0]['user_village'] !== $this->user[0]['spouse_village']) {
                    throw new Exception("You cannot purchase a couple's house if your spouse is in a different village.");
                }


                // Buy the house
                if(($GLOBALS['database']->execute_query('UPDATE `users_statistics` AS `user_stats_1`, `users` AS `user_1`,
                    `users` AS `user_2`
                    SET `user_stats_1`.`money` = `user_stats_1`.`money` - '.$this->house_data[$choice]['price'].',
                        `user_1`.`apartment` = '.$this->house_data[$choice]['house_id'].',
                        `user_2`.`apartment` = '.$this->house_data[$choice]['house_id'].'
                    WHERE `user_1`.`id` = '.$buyer.' AND `user_stats_1`.`uid` = `user_1`.`id`  AND `user_2`.`id` = '.$spouse)) === false) {
                    throw new Exception('There was an error trying to purchase the house for you and your spouse.');
                }
                else
                {
                    $GLOBALS['Events']->acceptEvent('home', array('data'=>$this->house_data[$choice]['house_id']));
                    $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$GLOBALS['userdata'][0]['money'], 'new'=> $GLOBALS['userdata'][0]['money'] - $this->house_data[$choice]['price']));

                    require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
                    $events = new Events($spouse);
                    $events->acceptEvent('home', array('data'=>$this->house_data[$choice]['house_id']));
                    $events->acceptEvent('money_loss', array('old'=>$this->user[0]['spouse_money'], 'new'=> $this->user[0]['spouse_money'] - $this->house_data[$choice]['price']));
                    $events->closeEvents();
                }
            }
            else {
                // Buy the house
                if(($GLOBALS['database']->execute_query('UPDATE `users_statistics`, `users`
                    SET `users_statistics`.`money` = `users_statistics`.`money` - '.$this->house_data[$choice]['price'].',
                        `users`.`apartment` = '.$this->house_data[$choice]['house_id'].'
                    WHERE `users`.`id` = '.$this->user[0]['id'].' AND `users_statistics`.`uid` = `users`.`id`')) === false) {
                    throw new Exception('There was an error trying to purchase the house.');
                }
                else
                {
                    $GLOBALS['Events']->acceptEvent('home', array('data'=>$this->house_data[$choice]['house_id']));
                    $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$GLOBALS['userdata'][0]['money'], 'new'=> $GLOBALS['userdata'][0]['money'] - $this->house_data[$choice]['price']));
                }
            }

            // Message the user
            $GLOBALS['page']->Message("You have bought the ".$this->house_data[$choice]['name']." for ".$this->house_data[$choice]['price']." ryo!",
                'House Purchase', 'id='.$_GET['id']);
        }

        // Confirm selling home
        private function confirm_sell() {
            $GLOBALS['page']->Confirm("Are you sure you wish to sell your home?", 'Auctioning Your House', 'Yes');
        }

        // Do sell home
        private function sell_home() {

            // Get user information
            if(!($this->user = $GLOBALS['database']->fetch_data('
                SELECT  `users`.`id`, `users`.`apartment`, `users`.`status`,
                        `users_statistics`.`money`,
                        `homes`.`married_home`, `homes`.`price`, `homes`.`name`,
                        `marriages`.`married`, `marriages`.`uid`, `marriages`.`oid`,
                        `spouse`.`status` as `spouseStatus`, `spouse_statistics`.`money` as `spouse_money`
                FROM `users`
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    INNER JOIN `homes` ON (`homes`.`id` = `users`.`apartment`)
                    LEFT JOIN `marriages` ON (`marriages`.`married` = "Yes" AND (`marriages`.`uid` = `users`.`id` OR `marriages`.`oid` = `users`.`id`))
                    LEFT JOIN `users` AS `spouse` ON (`spouse`.`id` = IF(`users`.`id` = `marriages`.`uid`, `marriages`.`oid`, `marriages`.`uid`))
                    LEFT JOIN `users_statistics` AS `spouse_statistics` ON (`spouse_statistics`.`uid` =
                                                                  IF(`users`.`id` = `marriages`.`uid`, `marriages`.`oid`, `marriages`.`uid`))
                WHERE `users`.`id` = '.$_SESSION['uid'].' LIMIT 1 FOR UPDATE'))) {
                throw new Exception('There was an error trying to receive necessary information.');
            }

            // Check information was found
            if($this->user === '0 rows') {
                throw new Exception("Either you're not awake or you don't exist. Lets hope it's not the latter!");
            }

            // Check user status
            if($this->user[0]['status'] !== 'awake') {
                throw new Exception('You must be awake to sell a house!');
            }

            // Check that there is something to sell
            if($this->user[0]['apartment'] === NULL) {
                throw new Exception("You don't have a house to sell!");
            }

            // Check if it's a marriage home or a single-home
            if($this->user[0]['married_home'] === 'Yes') {

                // Check user status
                if($this->user[0]['spouseStatus'] !== 'awake') {
                    throw new Exception('Your spouse must be awake to sell marriage house!');
                }

                //here
                HomeHelper::MoveAllToStorageBox($this->user[0]['uid']);
                HomeHelper::MoveAllToStorageBox($this->user[0]['oid']);

                // Money
                $money = ($this->user[0]['price'] / 4);

                // Sell the home
                if(($GLOBALS['database']->execute_query('UPDATE `users_statistics`, `users`
                    SET `users_statistics`.`money` = `users_statistics`.`money` + '.$money.', `users`.`apartment` = NULL
                    WHERE `users`.`id` IN ('.$this->user[0]['uid'].', '.$this->user[0]['oid'].') AND `users_statistics`.`uid` = `users`.`id`')) === false) {
                    throw new Exception('There was an error trying to sell the house for you and your spouse.');
                }
                else
                {
                    $GLOBALS['Events']->acceptEvent('home', array('data'=>'sold'));
                    $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'], 'new'=> $GLOBALS['userdata'][0]['money'] + $money));

                    require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
                    $events = new Events($this->user[0]['oid']);
                    $events->acceptEvent('home', array('data'=>'sold'));
                    $events->acceptEvent('money_gain', array('old'=>$this->user[0]['spouse_money'], 'new'=> $this->user[0]['spouse_money'] + $money));
                    $events->closeEvents();
                }

            }
            else {

                HomeHelper::MoveAllToStorageBox($_SESSION['uid']);

                // Money
                $money = ($this->user[0]['price'] / 2);

                // Sell home
                if(($GLOBALS['database']->execute_query('UPDATE `users_statistics`, `users`
                    SET `users_statistics`.`money` = `users_statistics`.`money` + '.$money.', `users`.`apartment` = NULL
                    WHERE `users`.`id` = '.$this->user[0]['id'].' AND `users_statistics`.`uid` = `users`.`id`')) === false) {
                    throw new Exception('There was an error trying to sell the house.');
                }
                else
                {
                    $GLOBALS['Events']->acceptEvent('home', array('data'=>'sold'));
                    $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'], 'new'=> $GLOBALS['userdata'][0]['money'] + $money));
                }
            }

            // Show message
            $GLOBALS['page']->Message("You have sold the ".$this->user[0]['name']." for ".$money." ryo!", 'House Auction', 'id='.$_GET['id']);
        }

         // Already has a home
        private function home_main() {

            // Go through all house data
            foreach($this->house_data as $key => $val) {

                // Check if we hit the users house ID
                if($val['house_id'] === $this->user[0]['apartment']) {
                    $GLOBALS['template']->assign('house', $val);
                    $GLOBALS['template']->assign('house_image', functions::getUserImage('/homes/', $val['house_id']));
                    $GLOBALS['template']->assign('user', $this->user[0]);
                    $GLOBALS['template']->assign('contentLoad', './templates/content/home/home_main.tpl');
                    break;
                }
                else {

                    // If house was not found, then remove it from the user
                    if(end($this->house_data) === $key) {

                        // Start the transaction
                        $GLOBALS['database']->transaction_start();

                        // Lock Necessary Data
                        if($GLOBALS['database']->execute_query('SELECT `users`.`username` FROM `users`
                            WHERE `users`.`id` = '.$this->user[0]['id'].' LIMIT 1 FOR UPDATE') === false) {
                            throw new Exception('There was an error locking the user data for house removal!');
                        }

                        // Remove home
                        if($GLOBALS['database']->execute_query('UPDATE `users`
                            SET `users`.`apartment` = NULL, `users`.`status` = IF(`users`.`status` = "asleep", "awake", `users`.`status`)
                            WHERE `users`.`id` = '.$this->user[0]['id'].' LIMIT 1') === false)
                        {
                            throw new Exception('There was an error trying to kick out user from home!');
                        }

                        // Commit transaction at this point
                        $GLOBALS['database']->transaction_commit();

                        // Show error message
                        $GLOBALS['page']->Message("You no longer meet the requirements for living in this house, ".
                            "and therefore you are kicked out of it by the landlord.", 'House Landlord', 'id='.$_GET['id']);
                    }
                }
            }
        }

        private function homeInventory()
        {
            $inventory = self::getInventory();



            $GLOBALS['template']->assign('totals',$inventory['totals']);
            $GLOBALS['template']->assign('storage', $inventory['storage']);
            $GLOBALS['template']->assign('storage_box', $inventory['storage_box']);
            $GLOBALS['template']->assign('item_array', $inventory['item_array']);



            if($GLOBALS['userdata'][0]['village'] == "Syndicate")
            {
                $GLOBALS['template']->assign('syndicate_mode', true);
                $GLOBALS['template']->assign('storage_box_mode', false);
            }
            else
            {
                $GLOBALS['template']->assign('syndicate_mode', false);

                if($this->user[0]['apartment'] === NULL)
                    $GLOBALS['template']->assign('storage_box_mode', true);
                else
                    $GLOBALS['template']->assign('storage_box_mode', false);
            }

            $GLOBALS['template']->assign('collapse_home', $GLOBALS['userdata'][0]['collapse_home']);

            $GLOBALS['template']->assign('contentLoad', './templates/content/home/home_inventory.tpl');
        }

        private function furniture()
        {
            //if the buy button was pressed
            if (isset($_POST['buy_button']))
            {
                //get the users current home
                $home = HomeHelper::getHome();

                //get the users furniture
                $furniture = HomeHelper::getFurniture();

                //get the users current balance
                $money = $this->user[0]['money'];

                //get the cost of the piece of furniture
                $price = HomeHelper::getFurnitureCost($_POST['buy_button']);


                //find the avaliable amount of furniture slots and the size of the piece of furniture being purchased.
                $slots_used = 0;
                $slot_cost;
                $owned;
                $profession_flag = false;
                $profession_name = HomeHelper::getProfessionName($_SESSION['uid']);
                foreach($furniture as $piece)
                {
                    $slots_used += $piece['owned'] * $piece['size'];

                    if($piece['id'] == $_POST['buy_button'])
                    {
                        $slot_cost = $piece['size'];
                        $owned = $piece['owned'];
                        $max_owned = $piece['max_owned'];

                        if($piece['required_profession'] != "NONE")
                            if($piece['required_profession'] != $profession_name)
                                $profession_flag = true;

                    }
                }

                $slots_avaliable = $home[0]['furniture_slots'] - $slots_used;

                //if the user has enough money to make the purchase and has enough slots and make sure you are not trying to buy a second tool
                if(($owned + $_POST['quantity']) <= $max_owned)
                    if(($slot_cost * $_POST['quantity']) <= $slots_avaliable)
                        if(!$profession_flag)
                            if(!is_array($price)) //if the cost is not an item
                            {
                                if( ($_POST['quantity'] * $price) <= $money)
                                {
                                    //subtract the funds from the user
                                    HomeHelper::subtractBalance($price * $_POST['quantity']);

                                    //add a row in the table for each piece purchased.
                                    for($i = 0; $i < $_POST['quantity']; $i++)
                                    {
                                        HomeHelper::addFurniture($_POST['buy_button']);

                                    }
                                }
                                else
                                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You do not have enough ryo to make that purchase.'));

                            }
                            else// if the cost is an item
                            {
                                //check all prices
                                $fail_count = false;
                                foreach($price as $key => $value)
                                {
                                    $price[$key]['item_count'] = HomeHelper::getItemCountFromUserInventory($value[0]);
                                    if($price[$key]['item_count'] < $value[1])
                                    {
                                        $fail_count = true;
                                        break;
                                    }
                                }

                                //check for failure
                                if(!$fail_count)
                                {
                                    //process all prices
                                    foreach($price as $key => $value)
                                    {
                                        HomeHelper::subtractItems($value[0], $value[1] * $_POST['quantity']);
                                    }

                                    for($i = 0; $i < $_POST['quantity']; $i++)
                                        HomeHelper::addFurniture($_POST['buy_button']);

                                }
                                else
                                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You do not have the needed items to make that purchase.'));

                            }
                        else
                            $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You do not have the profession required to make that purchase.'));

                    else
                        $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You do not have the furniture space to make that purchase.'));

                else
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You can not own more of that type of furniture.'));

            }

            //if the sell button was pressed
            else if(isset($_POST['sell_button']))
            {
                //ask for confirmation
                //$GLOBALS['page']->Confirm("Are you sure you wish to make this sale", 'selling Furniture', 'Yes');

                //get the primary keys for each piece of that furniture from the users home_inventory
                $current_ids = HomeHelper::getFurnitureKeys($_POST['sell_button']);

                $inventory = self::getInventory();

                //checking storage capacities to make sure that the sale can be made with out overflowing storage
                    $flag = true; //flag shows weather or not the sale can be made based on the overflowing storage status
                    $home = $inventory['home'];
                    $furniture = HomeHelper::getFurniture($_POST['sell_button']);
                    $type = $furniture[0]['storage_type'];

                    //get all relevant pieces of funiture and add up the storage total.
                    $primary_storage_furniture = $inventory['storage'][$type];

                    //get the number of items of that type in the inventory.
                    $primary_storage_item_count = count($inventory['item_array'][$type]);

                    //get the amount of storage that will be lost by the sale
                    $storage_lost = $furniture[0]['storage'] * $_POST['quantity'];

                    //if there is over flow check and make sure that there is enough storage in anything storage to accept the overflow
                    if($primary_storage_item_count > ($inventory['storage'][$type] - $storage_lost))
                    {

                        //getting how much overflow there is
                        $storage_difference = $primary_storage_item_count - ( $inventory['storage'][$type] - $storage_lost );

                        //if we were selling anything furniture there is now where for it to overflow to so fail it.
                        if($type == 'anything')
                            $flag = false;

                        //otherwise...
                        else
                        {
                            //get all relevent pieces of furniture and count up the storage
                            $secondary_storage_furniture = $inventory['storage']['anything'];

                            //count the amount of items in anything storage.
                            $secondary_storage_item_count = count($inventory['item_array']['anything']);

                            //if anything storage cannot accept the over flow fail it.
                            if(($secondary_storage_item_count + $storage_difference) > $inventory['storage']['anything'])
                                $flag = false;
                        }
                    }

                //make sure we can sell that manny and that we have any at all. also, make sure we will have enough inventory space indicated by flag.
                if(count($current_ids) > 0 )
                    if($furniture[0]['owned'] != 0)
                        if($flag)
                            if(count($current_ids) >= $_POST['quantity'])
                            {
                                //delete from the top down untill the quantity wanted is removed
                                for($i = 0; $i < $_POST['quantity']; $i++)
                                {
                                    HomeHelper::deleteFurniture($current_ids[$i]['id'], $current_ids[$i]['fid']);
                                }

                                //return 1/4 of the value to the user
                                if(is_numeric($furniture[0]['price']))
                                    HomeHelper::addBalance(($furniture[0]['price'] * $_POST['quantity'])/4);
                                else
                                    HomeHelper::addBalance(($furniture[0]['sale_value'] * $_POST['quantity']));

                            }
                            else
                                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You can not sell more furniture than you own.'));
                        else
                            $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'That piece of furniture can not be sold. You will not have enough storage space in your home for all of your items afterwards.'));
                    else
                        $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You can not sell furniture that you do not own.'));
                else
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You can not sell furniture that does not exist.'));
            }

            //pull furniture table from database
            $furniture = HomeHelper::getFurniture();

            $event_active = false;
            $home = HomeHelper::getHome();
            $slots;
            $slots['max'] = $home[0]['furniture_slots'];

            $slots['used'] = 0;
            $profession_name = HomeHelper::getProfessionName($_SESSION['uid']);
            foreach($furniture as $key => $piece)
            {
                $slots['used'] += $piece['owned'] * $piece['size'];

                if($piece['required_profession'] != 'NONE' && ( $piece['required_profession'] != $profession_name ) )
                    unset($furniture[$key]);

                if($piece['event_furniture'] && $piece['owned'] == 0 && ($piece['event_start'] > time() || $piece['event_end'] < time()))
                    unset($furniture[$key]);
                if($piece['event_furniture'] && $piece['event_start'] < time() && $piece['event_end'] > time() )
                    $event_active = true;
            }

            //assign the furniture array and the template
            $GLOBALS['template']->assign('event_active', $event_active);
            $GLOBALS['template']->assign('slots', $slots);
            $GLOBALS['template']->assign('furniture', $furniture);
            $GLOBALS['template']->assign('contentLoad', './templates/content/home/home_furniture.tpl');
        }

        public function getInventory()
        {
            $syndicate_mode = ($GLOBALS['userdata'][0]['village'] == "Syndicate");

            $totals;
            $totals['current'] = 0;

            if($syndicate_mode)
                $totals['max'] = $this->user[0]['rank_id'] * 10;
            else
                $totals['max'] = 0;

            if(!$syndicate_mode)
                $furniture = HomeHelper::getFurniture();

            $this->items = HomeHelper::getHomeInventory();

            //getting actions for items and fixing name and type of furniture
            if(is_array($this->items))
                foreach($this->items as $key => $item)
                {

                    //fixing furniture names
                    if(strlen($this->items[$key]['name']) == 0)
                    {
                        $this->items[$key]['name'] = $item['furniture_name'];
                        $this->items[$key]['type'] = "furniture";
                    }

                    if($item['in_storage'] == 'no')
                        $this->items[$key] = $this->setItemOptions($this->items[$key]);
                    else if($item['in_storage'] == 'yes')
                    {
                        $this->storage_box[] = $this->setItemOptions($this->items[$key], "storage_mode");
                        unset($this->items[$key]);
                    }
                }

            if(!$syndicate_mode)
            {
                $home = HomeHelper::getHome();
                if(isset($home[0]['inventory_slots']))
                    $totals['max'] =+ $home[0]['inventory_slots'];
            }

            $storage;
            if(isset($home[0]['inventory_slots']))
                $storage['anything'] = $home[0]['inventory_slots'];
            else
                if($syndicate_mode)
                    $storage['anything'] = $totals['max'];
                else
                $storage['anything'] = 0;

            $storage['weapon'] = 0;
            $storage['armor'] = 0;
            $storage['tool'] = 0;
            $storage['food'] = 0;
            $storage['metal'] = 0;
            $storage['gem'] = 0;
            $storage['leather'] = 0;
            $storage['book'] = 0;
            $item_array;
            $item_array['anything'] = array();
            $item_array['weapon'] = array();
            $item_array['armor'] = array();
            $item_array['tool'] = array();
            $item_array['food'] = array();
            $item_array['metal'] = array();
            $item_array['gem'] = array();
            $item_array['leather'] = array();
            $item_array['book'] = array();


            if(!$syndicate_mode)
            {
                $profession_name = HomeHelper::getProfessionName($_SESSION['uid']);
                //filter out furniture and count max items
                foreach($furniture as $key => $piece)
                {
                    if($piece['required_profession'] != 'NONE' && ( $piece['required_profession'] != $profession_name ) )
                        unset($furniture[$key]);

                    else
                    {
                        $totals['max'] += $piece['storage'] * $piece['owned'];

                        if($piece['storage_type'] == 'weapon')
                            $storage['weapon'] += $piece['storage'] * $piece['owned'];
                        else if ($piece['storage_type'] == 'armor')
                            $storage['armor'] += $piece['storage'] * $piece['owned'];
                        else if ($piece['storage_type'] == 'tool')
                            $storage['tool'] += $piece['storage'] * $piece['owned'];
                        else if ($piece['storage_type'] == 'food')
                            $storage['food'] += $piece['storage'] * $piece['owned'];
                        else if ($piece['storage_type'] == 'metal')
                            $storage['metal'] += $piece['storage'] * $piece['owned'];
                        else if ($piece['storage_type'] == 'gem')
                            $storage['gem'] += $piece['storage'] * $piece['owned'];
                        else if ($piece['storage_type'] == 'leather')
                            $storage['leather'] += $piece['storage'] * $piece['owned'];
                        else if ($piece['storage_type'] == 'book')
                            $storage['book'] += $piece['storage'] * $piece['owned'];
                        else if ($piece['storage_type'] == 'anything')
                            $storage['anything'] += $piece['storage'] * $piece['owned'];
                    }
                }
            }


            //filter items and count total items
            if(is_array($this->items)) // stops error when home_inventory is empty
            {
                foreach($this->items as $item)
                {
                    $totals['current']++;

                    if($item['content_type'] == 'tools')
                        $item['content_type'] = 'tool';

                    //filter out weapons into weapon slots until full
                    if(!$syndicate_mode && $item['content_type'] == 'weapon' && count($item_array['weapon']) < $storage['weapon'])
                        $item_array['weapon'][] = $item;

                    //filter out armor into armor slots until full
                    else if(!$syndicate_mode && $item['content_type'] == 'armor' && count($item_array['armor']) < $storage['armor'])
                        $item_array['armor'][] = $item;

                    //filter out tools into tool slots until full
                    else if(!$syndicate_mode && $item['content_type'] == 'tool' && count($item_array['tool']) < $storage['tool'])
                        $item_array['tool'][] = $item;

                    //filter out food into food slots until full
                    else if(!$syndicate_mode && $item['content_type'] == 'food' && count($item_array['food']) < $storage['food'])
                        $item_array['food'][] = $item;

                    //filter out metal into metal slots until full
                    else if(!$syndicate_mode && $item['content_type'] == 'metal' && count($item_array['metal']) < $storage['metal'])
                        $item_array['metal'][] =$item;

                    //filter out gems into gem slots until full
                    else if(!$syndicate_mode && $item['content_type'] == 'gem' && count($item_array['gem']) < $storage['gem'])
                        $item_array['gem'][] = $item;

                    //filter out skin / leather into skin/leather slots until full
                    else if(!$syndicate_mode && $item['content_type'] == 'leather' && count($item_array['leather']) < $storage['leather'])
                        $item_array['leather'][] = $item;

                    //filter out books into book slots until full
                    else if(!$syndicate_mode && $item['content_type'] == 'book' && count($item_array['book']) < $storage['book'])
                        $item_array['book'][] = $item;

                    //else add to free slots
                    else
                        $item_array['anything'][] = $item;


                }
            }

            $inventory;
            $inventory['totals'] = $totals;
            $inventory['storage'] = $storage;

            if(isset($this->storage_box))
                $inventory['storage_box'] = $this->storage_box;
            else
                $inventory['storage_box'] = array();

            $inventory['item_array'] = $item_array;

            if(!$syndicate_mode)
                $inventory['home'] = $home;
            else
                $inventory['home'] = array();

            return $inventory;
        }

        //start here
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
            }

            // Sell the list of items
            $this->item_basic_functions->do_sell_itemList( $list, $_SESSION['uid'], "home" );
        }

        // Set action option or message on item in the inventory
        private function setItemOptions( $item, $storage_box_item = false)
        {
            $balance = $this->user[0]['money'];

            // Standard links
            $item['action'] = "";
            if( $item['stack_size'] > 1 )
            {
                $item = $this->item_basic_functions->addMergeAndSplitActions( $item , array_column($this->items, "iid"), false, true, true, "home");
                $item['action'] = $item['split'];

                if($storage_box_item)
                {
                    $item['take_out_cost'] = $this->take_out_cost * $item['stack'];
                    if($this->user[0]['apartment'] !== NULL && $balance >= ($this->take_out_cost * $item['stack']))
                        $item['action']  .= "/<a href='?id=".$_GET['id']."&act=inventory&amp;process=take_out&amp;invID=".$item['id']."'>Take Out</a>";
                }
            }
            else
            {
                if(!$storage_box_item)
                    $item['action'] = "n/a";

                else if($storage_box_item)
                {
                    $item['take_out_cost'] = $this->take_out_cost;
                    if($this->user[0]['apartment'] !== NULL && $balance >= $this->take_out_cost)
                        $item['action']  .= "<a href='?id=".$_GET['id']."&act=inventory&amp;process=take_out&amp;invID=".$item['id']."'>Take Out</a>";
                }
            }

            if($item['action'] == "")
                $item['action'] = "n/a";

            return $item;
        }

        // Check merge amd split actions
        private function mergeAndSplit()
        {

            if( isset($_GET['process']) )
            {
                // Run any split/merge stack actions
                if( ($_GET['process'] == "split" && isset($_GET['invID'])) ||
                    ($_GET['process'] == "merge" && isset($_GET['iid']))  )
                {

                    // Start transaction for anything going on here
                    $GLOBALS['database']->transaction_start();

                    // Handle cases
                    switch( $_GET['process'] )
                    {
                        case "split": $this->item_basic_functions->splitUserStackInHalf( $_SESSION['uid'], $_GET['invID'], "home" ); break;
                        case "merge": $this->item_basic_functions->mergeUserStacks( $_SESSION['uid'], $_GET['iid'], "home"); break;
                        default: throw new Exception("Could not identify item action"); break;
                    }

                    // End this transaction
                    $GLOBALS['database']->transaction_commit();
                }
            }
        }

        private function takeOut()
        {
            //check and see if you have a home
            if($this->user[0]['apartment'] !== NULL && !$GLOBALS['page']->isOutlaw)//here
            {


            //get the users current home
                $home = HomeHelper::getHome();

                //get the users furniture
                $furniture = HomeHelper::getFurniture();

                //check how much it is going to cost
                $item = HomeHelper::getHomeItem($_GET['invID']);

                if(isset($item['id']))
                {
                    if(!isset($item['stack']))
                        $item['stack'] = 1;
                    $cost = $item['stack'] * $this->take_out_cost;

                    //if it is furniture make sure there is space for it.
                    $slots_used = 0;
                    $slot_cost = 0;
                    $owned = 0;
                    $max_owned = 0;
                    if(is_numeric($item['fid']))
                    {
                        foreach($furniture as $piece)
                        {
                            $slots_used += $piece['owned'] * $piece['size'];

                            if($piece['id'] == $item['fid'])
                            {
                                $slot_cost = $piece['size'];
                                $owned = $piece['owned'];
                                $max_owned = $piece['max_owned'];
                            }
                        }
                    }

                    if(($max_owned > $owned && $home[0]['furniture_slots'] >= ($slots_used + $slot_cost)) || !is_numeric($item['fid']))
                    {
                        //get balance
                        $balance = $this->user[0]['money'];

                        //check and see if you have enough money
                        if($cost <= $balance)
                        {
                            $home_inventory = $this->getInventory();

                            $counts;
                            $counts['anything'] = count($home_inventory['item_array']['anything']);
                            $counts['weapon'] = count($home_inventory['item_array']['weapon']);
                            $counts['armor'] = count($home_inventory['item_array']['armor']);
                            $counts['tool'] = count($home_inventory['item_array']['tool']);
                            $counts['food'] = count($home_inventory['item_array']['food']);
                            $counts['metal'] = count($home_inventory['item_array']['metal']);
                            $counts['gem'] = count($home_inventory['item_array']['gem']);
                            $counts['leather'] = count($home_inventory['item_array']['leather']);
                            $counts['book'] = count($home_inventory['item_array']['book']);

                            if($item['content_type'] == 'tools')
                                $item['content_type'] = 'tool';

                            if( is_numeric($item['fid']) || ($home_inventory['storage'][$item['content_type']] > $counts[$item['content_type']]) || $home_inventory['storage']['anything'] > $counts['anything'])
                            {

                                if($cost == 0)
                                    $cost = $this->take_out_cost;
                                //subtract the money
                                HomeHelper::subtractBalance($cost);

                                //change the in_storage tag
                                HomeHelper::removeFromStorageBox($_GET['invID']);

                                unset($this->item_basic_function);
                                unset($this->items);
                                unset($this->storage_box);
                                $this->house_data_setup();
                            }
                            else
                                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You do not have enough space to move that item.'));
                        }
                        else
                            $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You do not have enough ryo to take that out.'));
                    }
                    else
                        $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You do not have enough furniture space to take that out.'));
                }
                else
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You can not take out an item you do not have.'));
            }
            else
                $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You do not have a home to move that to.'));

        }

        private function transferItemList()
        {
            if(isset($_POST['inventoryIDs']))
            {
                $found_storage_box_item = false;

                foreach($_POST['inventoryIDs'] as $key => $home_inventory_key)
                {

                    if( $key[0] != "F" )
                        HomeHelper::transferItemFromHomeToUser($home_inventory_key);
                    else
                        $found_storage_box_item = true;
                }

                if($found_storage_box_item)
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You can not transfer items to your inventory from your storage box. you must move then to your home first with "takeout."'));

                //check over encumberment
                $inventory_raw = $GLOBALS['database']->fetch_data("
                    SELECT `users_inventory`.*, `items`.`name`, `items`.`inventorySpace`
                    FROM `users_inventory`
                    INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid`)
                    WHERE `uid` = '" . $_SESSION['uid'] . "'");


                $max = 6;
                if($GLOBALS['userdata'][0]['federal_level'] == "None")
                    $max += 0;
                else if($GLOBALS['userdata'][0]['federal_level'] == "Normal")
                    $max += 1;
                else if($GLOBALS['userdata'][0]['federal_level'] == "Silver")
                    $max += 3;
                else if($GLOBALS['userdata'][0]['federal_level'] == "Gold")
                    $max += 5;

                $current = 0;
                foreach($inventory_raw as $item)
                {
                    if($item['equipped'] == 'yes')
                    {
                        if($item['name'] == "Hunters Toolkit" || $item['name'] == "Herbalist Pouch" || $item['name'] == "Miners Toolkit")
                            $max += 3;
                    }
                    else
                    {
                        if($item['inventorySpace'] == '1')
                            $current++;
                    }

                }

                if($current > $max)
                {
                    if(!$GLOBALS['userdata'][0]['over_encumbered'])
                        $GLOBALS['database']->overEncumbered();
                }
                else if($GLOBALS['userdata'][0]['over_encumbered'])
                    $GLOBALS['database']->underEncumbered();
            }
            $GLOBALS['database']->transaction_start();
            self::homeInventory();
            $GLOBALS['database']->transaction_commit();

        }

        public static function cmp($a, $b)
        {
            return $a['regen'] - $b['regen'];
        }
    }

    new home();