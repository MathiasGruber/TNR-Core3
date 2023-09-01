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

// Load stuff
require_once(Data::$absSvrPath.'/libs/professionSystem/basicJobFunctions.php');
require_once(Data::$absSvrPath.'/libs/itemSystem/itemFunctions.php');

class professionLib extends basicJobFunctions {

    // Setup data
    public function setupProfessionData(){

        // Setup variables used
        $this->jobType = "profession";

        // Item functions
        $this->itemLib = new itemBasicFunctions();

        // Type limitation: implemented in all functions retrieving professions
        if (!isset($this->queryLimitation)) {
            $this->queryLimitation = '';
        }
        $this->queryLimitation .= "`occupations`.`type` = 'profession' ";
    }

    // Set top links
    private function getTopLinks( $process, $material, $crafting, $ramen, $hospital ){
        $array = array();

        if( $process ){
            $array[] = array("name" => "Pre-Process Materials", "href" =>"?id=".$_GET["id"]."&page=process");
        }

        if( $material ){
            $array[] = array("name" => "Materials", "href" =>"?id=".$_GET["id"]."&page=material");
        }

        if( $crafting ){
            $array[] = array("name" => "Crafting Recipes", "href" =>"?id=".$_GET["id"]."&page=crafting");
        }

        if( $ramen ){
            $array[] = array("name" => "Ramen Supply", "href" =>"?id=".$_GET["id"]."&page=ramen");
        }

        if( $hospital ){
            $array[] = array("name" => "Hospital Supply", "href" =>"?id=".$_GET["id"]."&page=hospital");
        }

        return $array;
    }

        // Set profession gains to be shown
    private function setProfessionVariables( $job ){

        // Defaults
        $this->maxProcessQueue = 50;
        $this->maxCraftQueue = 1;

        $this->experienceGains = array(
            "base" => 0, // base gain
            "0" => 1,    // hardest craft
            "1" => 0.50, // second craft
            "2" => 0.25  // third craft
        );

        // Set normal gains
        $this->setGains( $job );

        // Add info on tool
        $this->gains[] = array( "type" => "info", "text" => "Tools used: ".$this->toolData[0]['name'] );

        // Set the gains
        switch( $job ){
            case "Hunter":
                $this->processName = "process";


                // Set options
                $this->pageOptions = array(
                    "process" => true,
                    "material" => true,
                    "crafting" => false,
                    "ramen" => false,
                    "hospital" => false
                );
            break;
            case "Herbalist":
                $this->processName = "process";
                $this->maxCraftQueue = 3;

                // Overwrite experience gains
                $this->experienceGains = array(
                    "base" => 0.5,
                    "0" => 0,
                    "1" => 0,
                    "2" => 0
                );

                // Set options
                $this->pageOptions = array(
                    "process" => true,
                    "material" => true,
                    "crafting" => true,
                    "ramen" => false,
                    "hospital" => true
                );
            break;
            case "Miner":
                $this->processName = "smelt";

                // Set options
                $this->pageOptions = array(
                    "process" => true,
                    "material" => true,
                    "crafting" => false,
                    "ramen" => false,
                    "hospital" => false
                );
            break;
            case "Chef Cook":
                $this->processName = "prepare";

                // Set options
                $this->pageOptions = array(
                    "process" => false,
                    "material" => true,
                    "crafting" => true,
                    "ramen" => true,
                    "hospital" => false
                );
            break;
            case "Weapon Smith":
                $this->processName = "process";

                // Set options
                $this->pageOptions = array(
                    "process" => true,
                    "material" => true,
                    "crafting" => true,
                    "ramen" => false,
                    "hospital" => false
                );
            break;
            case "Armor Craftsman":
                $this->processName = "process";

                // Set options
                $this->pageOptions = array(
                    "process" => true,
                    "material" => true,
                    "crafting" => true,
                    "ramen" => false,
                    "hospital" => false
                );
            break;
            default:
                return false;
            break;
        }

        // Set the panel options array
        $this->cPanelOptions = $this->getTopLinks(
            $this->pageOptions['process'],
            $this->pageOptions['material'],
            $this->pageOptions['crafting'],
            $this->pageOptions['ramen'],
            $this->pageOptions['hospital']
        );
    }

    // Function for setting the gains depending on profession
    public function setGains( $job ){

        // Set the gains
        $this->gains = false;
        switch( $job ){
            case "Hunter":
                $this->gains = array(
                    array( "type" => "item", "discount" => floor(35 * $this->user[0]['profession_exp'] / 450), "identifier" => "tool" ),
                    array( "type" => "item", "discount" => floor(35 * $this->user[0]['profession_exp'] / 450), "identifier" => "weapon" ),
                );
            break;
            case "Herbalist":
                $this->gains = array(
                    array( "type" => "ramen", "discount" => floor(25 * $this->user[0]['profession_exp'] / 450) ),
                    array( "type" => "item", "discount" => floor(25 * $this->user[0]['profession_exp'] / 450), "identifier" => "item", "subIdentifier" => "healing" ),
                    array( "type" => "hospital", "discount" => floor(5 * $this->user[0]['profession_exp'] / 450) ),
                );
            break;
            case "Miner":
                $this->gains = array(
                    array( "type" => "item", "discount" => floor(35 * $this->user[0]['profession_exp'] / 450), "identifier" => "item"),
                    array( "type" => "item", "discount" => floor(35 * $this->user[0]['profession_exp'] / 450), "identifier" => "weapon"),
                );
            break;
            case "Chef Cook":
                $this->gains = array(
                    array( "type" => "ramen", "discount" => floor(45 * $this->user[0]['profession_exp'] / 450)),
                );
            break;
            case "Weapon Smith":
                $this->gains = array(
                    array( "type" => "item", "discount" => floor(35 * $this->user[0]['profession_exp'] / 450), "identifier" => "weapon"),
                    array( "type" => "info", "text" => "Can repair own and other people's weapons" ),
                );
            break;
            case "Armor Craftsman":
                $this->gains = array(
                    array( "type" => "item", "discount" => floor(35 * $this->user[0]['profession_exp'] / 450), "identifier" => "armor"),
                    array( "type" => "info", "text" => "Can repair own and other people's armor" ),
                );
            break;
            default:
                return false;
            break;
        }
        return true;
    }

    // Show jobs
    protected function profession_list( $rankID ) {

        // Get occupations
        $professions = $GLOBALS['database']->fetch_data("
             SELECT
                `occupations`.*,
                `items`.`name` as `itemname`
             FROM `occupations`
             LEFT JOIN items ON (`items`.`id` = `occupations`.`required_item_id`)
             WHERE
                `rankid` <= '" . $rankID . "' AND
                " . $this->queryLimitation . "
             ORDER BY `id`");

        tableParser::show_list(
            'professionList',
            "Available Professions",
            $professions,
            array(
                'name' => "Name",
                'description' => "Description",
                'itemname' => "Required Tool"
            ),
            array(
                array("name" => "Sign Up", "id" => $_GET['id'], "act" => "getprofession", "job" => "table.id")
            ),
            true,   // Send directly to contentLoad
            false,   // Show previous/next links
            false,  // No links at the top to show
            false,   // Allow sorting on columns
            false,   // pretty-hide options
            false, // Top stuff
            false // Top information
        );
    }

    // Function for getting a new profession
    public function start_profession($job = false, $quiet = false) {

        if($job === false)
            $job = $_GET['job'];

        // Check, throws errors itself
        if( $job = $this->can_get_job($job) ){

            // Check that the user has the neccesary item
            if( $item_data = $this->itemLib->userHasItem(
                array( "uid" => $_SESSION['uid'], "type" => "tool", "professionRestriction" => $job[0]['name'] )
            ) ){

                //	Upload job
                if( $this->set_occupation_data(
                        array(
                            "profession" => $job[0]['id']
                        )
                    )
                ){

                    $GLOBALS['Events']->acceptEvent('profession_change', array('new'=>$job[0]['id'], 'old'=>0));//$GLOBALS['userdata'][0]['profession']));

                    // Everything should be good at this point
                    $GLOBALS['database']->transaction_commit();

                    // Information
                    if(!$quiet)
                        $GLOBALS['page']->Message( 'You are now a ' . $job[0]['name'] , 'Profession System', 'id='.$_GET['id'],'Return');

                    return true;

                } else {
                    throw new Exception("An error occured while uploading your ".$this->jobType.", please try again");
                }
            }
            else{
                throw new Exception("You do not have the required item for this profession.");
            }
        }
        return false;
    }

    // Function for quitting job
    protected function quit_profession() {

        // Make sure the user is not crafting / processing anything
        $currentCrafting = $GLOBALS['database']->fetch_data("
            SELECT  COUNT(`users_inventory`.`id`) as `processingCount`, `occupation`
            FROM `users_inventory`,`items` RIGHT JOIN `users_occupations` ON (`users_occupations`.`userid` = ".$_SESSION['uid'].")
            WHERE
                `uid` = ".$_SESSION['uid']." AND
                `iid` = `items`.`id` AND
                `equipped` = 'no' AND
                (`items`.`craftable` = 'Yes' OR `items`.`type` = 'process')
                AND `finishProcessing` != '0'
        ");
        if( $currentCrafting[0]['processingCount'] > 0 ){
            throw new Exception("You cannot quit your profession while crafting/processing items.");
        }

        //	Upload job
        if( $this->set_occupation_data( array( "profession" => 0, "profession_exp" => 0) ) ){

            // Delete all items of the user that are processes
            $GLOBALS['database']->execute_query("
                DELETE `users_inventory`
                FROM `users_inventory`
                LEFT JOIN `items` ON (`items`.`id` = `users_inventory`.`iid`)
                WHERE
                   `items`.`type` = 'process' AND
                   `users_inventory`.`uid` = '".$_SESSION['uid']."'");

            // Everything should be good at this point
            $GLOBALS['database']->transaction_commit();

            $GLOBALS['Events']->acceptEvent('profession_change', array('new'=>0, 'old'=>$currentCrafting[0]['occupation']));//$GLOBALS['userdata'][0]['profession']));


            // Information
            $GLOBALS['page']->Message( 'You have quit your job.' , 'Profession System', 'id='.$_GET['id'],'Return');

        } else {
            throw new Exception("An error occured while uploading your application, please try again");
        }
    }

    // Check if user has the tool required
    public function user_has_tool(){
        $this->userHasTool = false;
        if( $this->toolData = $this->itemLib->userHasItem(
            array( "uid" => $_SESSION['uid'], "type" => "tool", "professionRestriction" => $this->user[0]['name'] )
        ) ){
            $this->userHasTool = true;
            return true;
        }
        return false;
    }

    // The main profession page where people can see all kinds of stuff about their profession
    protected function profession_main(){

         // Check that the user has the neccesary item
        if( $this->user_has_tool() ){

            // Set occupation data
            $this->setProfessionVariables( $this->user[0]['name'] );
            $GLOBALS['template']->assign('gains', $this->gains);

            // Depending on GET-act either show the processing menu, or the crafting menu
            if( !isset($_GET['page']) ){
                $this->showMaterials();
            }
            elseif( isset($this->pageOptions[ $_GET['page'] ]) && $this->pageOptions[ $_GET['page'] ] == true ){
                if( $_GET['page'] == "process" ){
                    $this->showProcessing();
                }
                elseif( $_GET['page'] == "material" ){
                    $this->showMaterials();
                }
                elseif( $_GET['page'] == "ramen" ){
                    $this->supplyVillage("ramen");
                }
                elseif( $_GET['page'] == "hospital" ){
                    $this->supplyVillage("hospital");
                }
                else{
                    $this->showCrafting();
                }
            }
            else{
                throw new Exception("Trying to access non-existant page");
            }

            // Send data to smarty
            $GLOBALS['template']->assign('data', $this->user[0]);

            // Load tempalte
            $GLOBALS['template']->assign('contentLoad', './templates/content/profession/profession.tpl');
        }
        else{
            throw new Exception("You do not have the required item for this profession.");
        }
    }

    // Check merge & split actions
    private function mergeAndSplit(){

        // Run any split/merge stack actions
        if( isset($_GET['process']) &&
            ((isset($_GET['timekey']) && isset($_GET['iid'])) || isset($_GET['invID']))
        ){

            // Start transaction for anything going on here
            $GLOBALS['database']->transaction_start();

            // Handle cases
            switch( $_GET['process'] ){
                case "split": $this->itemLib->splitUserStackInHalf( $_SESSION['uid'], $_GET['invID'] ); break;
                case "merge": $this->itemLib->mergeUserStacks( $_SESSION['uid'], $_GET['iid'] ); break;
                //case "drop": $this->itemLib->removeUserItem( array("uid" => $_SESSION['uid'], "id" => $_GET['invID']) ); break;
                default: throw new Exception("Could not identify mergin action"); break;
            }

            // End this transaction
            $GLOBALS['database']->transaction_commit();
        }
    }



    // Show processing
    private function showProcessing(){

        // Check that the user is asleep
        if( $GLOBALS['page']->isAsleep ){

            // Handle actions
            $this->mergeAndSplit();

            // Check if we have stuff on hold
            $onHold = false;

            // Get user inventory
            $inventory = $GLOBALS['database']->fetch_data("
                SELECT
                        `users_inventory`.*, `users_inventory`.`id` as `inv_id`,
                        `items`.`name`,`items`.`stack_size`,`items`.`inventorySpace`,
                        `items`.`price`,`items`.`type`,`items`.`armor_types`,`items`.`required_rank`,
                        `items`.`processed_results`, `items`.`craftProcessMinutes`, `items`.`professionRestriction`
                FROM `users_inventory`,`items`
                WHERE
                    `uid` = '" . $_SESSION['uid'] . "' AND
                    `iid` = `items`.`id` AND
                    `equipped` = 'no' AND
                    `items`.`type` = 'process' AND
                    `durabilityPoints` > 0
                ORDER BY
                    `finishProcessing` ASC,`name` DESC");

            // Variables used in loop
            $foundProcessingItem = false;
            $timeTrack = $GLOBALS['user']->load_time;

            $timeMod = 1;
            // Check for global event modifications
            if( $event = functions::getGlobalEvent("ModifyCraftTime")){
                if( isset( $event['data']) && is_numeric( $event['data']) ){
                    $timeMod = round($event['data'] / 100,2);
                }
            }

            // Stuff to do if something is there
            if ($inventory != '0 rows') {

                // Find the latest item currently being finished
                $processingCount = 0;
                $inventoryCount = count($inventory);
                for( $i = 0 ; $i < $inventoryCount ; $i++ ){
                    if( $inventory[$i]['finishProcessing'] > $timeTrack  ){
                        $timeTrack = $inventory[$i]['finishProcessing'];
                    }
                    if( $inventory[$i]['finishProcessing'] > 0 ){
                        $processingCount += $inventory[$i]['stack'];
                    }
                }

                // Modify what is shown
                $this->itemsIDsInInventory = array();
                for( $i = 0 ; $i < $inventoryCount; $i++ ){

                    // Add action links
                    $inventory[$i] = $this->itemLib->addMergeAndSplitActions( $inventory[$i] , $this->itemsIDsInInventory, true, true, true);

                    // Add item ID to array
                    $this->itemsIDsInInventory[] = $inventory[$i]['iid'];

                    // Process time for this item
                    $entryTime = floor($inventory[$i]['craftProcessMinutes'] * $timeMod * $inventory[$i]['stack']);

                    // Show processing link
                    $inventory[$i]['processing'] = "<a href='?id=".$_GET['id']."&page=process&process=process&inv_id=".$inventory[$i]['id']."'>".ucfirst($this->processName)."</a> (".$entryTime." minutes)";

                    // Show for not yet processed links
                    if( $processingCount + $inventory[$i]['stack'] <= $this->maxProcessQueue ){

                        // Check if we should start processing
                        if( $inventory[$i]['finishProcessing'] == 0 ){

                            // Check if professions match
                            if( $inventory[$i]['professionRestriction'] == "none" || $inventory[$i]['professionRestriction'] == $this->user[0]['name'] ){

                                // Check if there's room in the inventory for the processed results
                                //$resultCount = count(explode(";",$inventory[$i]['processed_results']));
                                //if( $this->itemLib->currentItems + $resultCount <= $this->itemLib->maxitm ){

                                    // Check processing action
                                    if(
                                        isset($_GET['process']) &&
                                        $_GET['process'] == "process" &&
                                        isset($_GET['inv_id']) &&
                                        $_GET['inv_id'] == $inventory[$i]['id']
                                    ){

                                        // Set process time for the next loop
                                        $timeTrack += 60 * $entryTime;
                                        $processingCount += $inventory[$i]['stack'];
                                        $inventory[$i]['finishProcessing'] = $timeTrack;

                                        // Start processing
                                        $this->itemLib->updateUserItem(
                                            array("finishProcessing" => $timeTrack ) ,
                                            array(
                                                "id" => $inventory[$i]['id'],
                                                "uid" => $_SESSION['uid'] ,
                                                "iid" => $inventory[$i]['iid']
                                            )
                                        );

                                    }
                                //}
                                //else{
                                    // Will be overwritten in next check if item is actually in queue already
                                //    $inventory[$i]['processing'] = "<i>Not Enough Space</i>";
                                //}
                            }
                            else{
                                // Will be overwritten in next check if item is actually in queue already
                                $inventory[$i]['processing'] = "<i>Not Correct Profession</i>";
                            }
                        }
                    }
                    else{
                        // Will be overwritten in next check if item is actually in queue already
                        $inventory[$i]['processing'] = "<i>Full Queue</i>";
                    }

                    // Show for processing links
                    if( $inventory[$i]['finishProcessing'] != 0 ){

                        // Check the time
                        $diff = $inventory[$i]['finishProcessing'] - $GLOBALS['user']->load_time;

                        // See if we already found the item processing
                        if($foundProcessingItem == false){
                            if( $diff > 0 ){
                                $foundProcessingItem = true;
                                $inventory[$i]['processing'] = functions::convert_time($diff, 'processTimer'.$diff);
                            }
                        }
                        else{
                            $inventory[$i]['processing'] = "<i>Queued</i>";
                        }

                        // Check for removal
                        if(  $diff < 0  ){

                            // Get the items that have been processed
                            $processedInto = explode(";", $inventory[$i]['processed_results']);

                            // Calculate total amount of processed items being added
                            $totalProcessedItems = 0;
                            $idAndAmountArray = array();
                            foreach($processedInto as $result){
                                $subResult = explode( ",", $result );
                                if( random_int(1,100) <= $subResult[3] ){
                                    $amount = random_int(  ($subResult[1]  *  $inventory[$i]['stack'])  ,  ($subResult[2]  *  $inventory[$i]['stack']));
                                    $idAndAmountArray[ $subResult[0] ] = $amount;
                                    $totalProcessedItems += $amount;
                                }
                            }

                            // Check if these items can be added
                            if( $this->itemLib->canAddItems( $idAndAmountArray ) ){

                                // Start transaction for anything going on here
                                $GLOBALS['database']->transaction_start();

                                // This one was finished, so remove process and insert materials
                                if( $totalProcessedItems > 0 ){
                                    foreach( $idAndAmountArray as $processedID => $processedAmount ){

                                        // Insert item
                                        $this->itemLib->addItemToUser( $_SESSION['uid'] , $processedID , $processedAmount, 0, 'processed' );//

                                    }
                                    $GLOBALS['NOTIFICATIONS']->addTempNotification( array( 'text' => ucfirst($this->processName) . " finished: ".$inventory[$i]['name']));
                                }
                                else{
                                    $GLOBALS['NOTIFICATIONS']->addTempNotification( array( 'text' => ucfirst($this->processName) . " failed processing: ".$inventory[$i]['name']));
                                }


                                // Remove the process item
                                $this->itemLib->removeUserItem( array(
                                    "timekey" => $inventory[$i]['timekey'],
                                    "id" => $inventory[$i]['id'],
                                    "uid" => $_SESSION['uid'] ,
                                    "iid" => $inventory[$i]['iid']
                                ) );

                                // Start transaction for anything going on here
                                $GLOBALS['database']->transaction_commit();

                                // Unset/remove
                                unset( $inventory[$i] );
                            }
                            else{
                                $onHold = true;
                                $inventory[$i]['processing'] = "<i>On Hold (".$totalProcessedItems." items)</i>";
                            }
                        }
                    }
                }

                // If element were deleted, the fix up the array keys
                $inventory = array_values($inventory);

            }

            // Tell the user to sell items if he's crafting too much
            $extra = $onHold == true ? "<br><font color='darkred'>Processed items could not be added to your full inventory. <br>Either drop the items or make room in your inventory.</font>" : "";

            tableParser::show_list(
                'controlPanel',
                "Pre-Processed Materials",
                $inventory,
                array(
                    'name' => "Name",
                    'split' => "Action",
                    'type' => "Type",
                    'processing' => "Process"
                ),
                false,
                false,   // Send directly to contentLoad
                false,   // Show previous/next links
                $this->cPanelOptions, // Options on the top
                false,   // Allow sorting on columns
                false,   // pretty-hide options
                false, // Top stuff
                "These are the items currently in your possession that need to be processed before they can be used.".$extra // Top information
            );
        }
        else{
            throw new Exception("You must be either at home or in camp (with status set to asleep) to process items");
        }
    }

    // Show materials
    private function showMaterials(){

        // Handle actions
        $this->mergeAndSplit();

        // Get user inventory
        $inventory = $GLOBALS['database']->fetch_data("
            SELECT
                    `users_inventory`.`id`, `users_inventory`.`iid`, `users_inventory`.`timekey`,`users_inventory`.`stack`,
                    `users_inventory`.`finishProcessing`, `users_inventory`.`id` as `inv_id`,
                    `items`.`name`,`items`.`stack_size`,`items`.`inventorySpace`,
                    `items`.`price`,`items`.`type`,`items`.`armor_types`,`items`.`required_rank`,
                    `items`.`processed_results`, `users_inventory`.`trading`
            FROM `users_inventory`,`items`
            WHERE
                `uid` = '" . $_SESSION['uid'] . "' AND
                `iid` = `items`.`id` AND
                `equipped` = 'no' AND
                `items`.`type` = 'material'
            ORDER BY
                `finishProcessing`,`name`
        ");

        // Modify what is shown

        if( $inventory !== "0 rows" ){
            $this->itemsIDsInInventory = array();
            for( $i = 0 ; $i < count($inventory) ; $i++ ){

                // Add action links
                $inventory[$i] = $this->itemLib->addMergeAndSplitActions( $inventory[$i] , $this->itemsIDsInInventory, true, true, false);

                // Check if it's being processed
                if( $inventory[$i]['finishProcessing'] > 0 ){
                    $inventory[$i]['type'] = "<i>Under Work</i>";
                }

                // Check if it's being traded
                if( $inventory[$i]['trading'] !== null ){
                    $inventory[$i]['split'] = "<i>Trading</i>";
                }

                // Add item ID to array
                $this->itemsIDsInInventory[] = $inventory[$i]['iid'];
            }
        }

        tableParser::show_list(
            'controlPanel',
            "Material Inventory",
            $inventory,
            array(
                'name' => "Name",
                'split' => "Action",
                'type' => "Type",
                'price' => "Worth"
            ),
            array(
                array("name" => "Details", "id" => $_GET['id'], "act" => "detail", "iid" => "table.iid")
            ),
            false,   // Send directly to contentLoad
            false,   // Show previous/next links
            $this->cPanelOptions, // Options on the top
            false,   // Allow sorting on columns
            false,   // pretty-hide options
            false, // Top stuff
            array('message'=>"These are the materials currently in your possession. These are the main components used for crafting new items. Your inventory currently holds ".$this->itemLib->currentItems."/".$this->itemLib->maxitm .", but only the materials are shown below.<br/><br/>",'hidden'=>'yes') // Top information
        );
    }

    // Function for checking if a user can craft a given item
    private function canCraftItem( $item ){
        $playerExp = $this->user[0]['profession_exp'] > 1 ? $this->user[0]['profession_exp'] : $this->user[0]['profession_exp'] + 1;
        if($item['craftable'] == "Yes" &&
           $item['professionRestriction'] == $this->user[0]['name'] &&
           $item['profession_level'] <= $playerExp &&
           ($item['village_restriction'] == "ALL" ||
            $item['village_restriction'] == $GLOBALS['userdata'][0]['village'])
        ){
            return true;
        }
        return false;
    }

    // Return the three most difficult crafts available to the user
    private function hardestCrafts(){

        // Get the three with the highest level
        $highest = array();
        foreach( $this->craftIDs as $craftID ){
            $highest[ $craftID ] = $this->sortedItems[ $craftID ]['profession_level'];
        }
        arsort($highest);

        // Find cutoff profession level
        $lastValue = $stages = 0;
        $categories = array();
        foreach( $highest as $key => $level ){
            if( $stages < 3 && $level !== $lastValue ){
                $lastValue = $level;
                $stages++;
            }
            $categories[ $key ] = $stages;
        }


        // Cut off everything below limit
        $itemIDs = array();
        foreach( $highest as $k => $v ){
            if( $v >= $lastValue ){
                $itemIDs[] = $k;
            }
        }

        return array($categories, $itemIDs);
    }

    // Show crafting
    private function showCrafting(){

        // Check that the user is home & asleep
        if( $GLOBALS['page']->isAsleep ){

            // Check if user is currently crafting something
            $currentCrafting = $GLOBALS['database']->fetch_data("
                SELECT  `users_inventory`.*,
                        `items`.`name`,`items`.`type`,`items`.`craftProcessMinutes`
                FROM `users_inventory`,`items`
                WHERE
                    `uid` = '" . $_SESSION['uid'] . "' AND
                    `iid` = `items`.`id` AND
                    `equipped` = 'no' AND
                    `items`.`type` != 'process' AND
                    `items`.`craftable` = 'Yes' AND
                    `finishProcessing` != '0' AND
                    `durabilityPoints` > 0 AND
                    (`trade_type` IS NULL OR `trade_type` != 'repair')
            ");

            // Sort user items on iid
            $this->sortedUserItems = array();
            $this->userIIDs = array();
            $this->currentCrafts = 0;
            if( $currentCrafting !== "0 rows" ){
                foreach( $currentCrafting as $item ){
                    $this->sortedUserItems[ $item['iid'] ] = $item;
                    $this->userIIDs[] = $item['iid'];
                    $this->currentCrafts += 1;
                }
            }

            // Get all items
            $allItems = cachefunctions::getCraftingItems( true  );

            // Get all craftable IDs and sort array by IID
            $this->sortedItems = array();
            $this->craftIDs = array();
            foreach( $allItems as $item ){

                // If craftin more than one, see it in the name
                $item['originalName'] = $item['name'];
                if( $item['craft_stack'] > 1 ){
                    $item['name'] .= " (".$item['craft_stack'].")";
                }

                $this->sortedItems[ $item['id'] ] = $item;

                // Check if user can craft this item
                if( $this->canCraftItem($item) ){

                    // Save the ID
                    $this->craftIDs[] = $item['id'];
                }
            }

            $this->craftIDcount = count($this->craftIDs);

            // Get the three hardest craft recipes
            list( $this->craftDifficultyCategory , $this->threeHardest ) = $this->hardestCrafts();

            // Finish all crafting
            $this->finishCraftings();

            // Start crafting
            if( isset($_GET['process']) && $_GET['process'] == "craft" &&
                isset($_GET['iid']) && is_numeric($_GET['iid']) &&
                in_array($_GET['iid'], $this->craftIDs)
            ){
                $this->startCrafting( $_GET['iid'] );
            }


            // Create array with user recipes
            $userRecipes = array();
            for( $i=0 ; $i < $this->craftIDcount ; $i++ ){

                // Local variable
                $itemID = $this->craftIDs[ $i ];

                // General item data
                $userRecipes[ $i ] = $this->sortedItems[ $itemID ];

                // Craft requirements
                $ingredients = array();
                $requirementData = $this->getRequirements($itemID);
                foreach( $requirementData as $id => $amount ){
                    $ingredients[] = $amount." ".$this->sortedItems[ $id ]['originalName'];
                }

                // Get the create link
                $userRecipes[ $i ]['create'] = $this->createCraftLink( $itemID );

                // To show
                $userRecipes[ $i ]['recipe'] = "<i>".implode("<br>", $ingredients)."</i>";
            }

            tableParser::show_list(
                'controlPanel',
                "Item Crafting",
                $userRecipes,
                array(
                    'name' => "Name",
                    'type' => "Type",
                    'price' => "Value",
                    'recipe' => "Required",
                    'create' => "Create"
                ),
                array(
                    array("name" => "Details", "id" => $_GET['id'], "act" => "detail", "iid" => "table.id")
                ),
                false,   // Send directly to contentLoad
                false,   // Show previous/next links
                $this->cPanelOptions, // Options on the top
                false,   // Allow sorting on columns
                false,   // pretty-hide options
                false, // Top stuff
                "These are the crafting recipes currently available for you at your profession level." // Top information
            );
        }
        else{
            throw new Exception("You must be either at home or in camp (with status set to asleep) to craft items");
        }
    }

    // Function for getting the required IDs & amounts for crafting a specific item
    private function getRequirements( $itemID ){
        $reqData = array();
        $requirements = explode(";",$this->sortedItems[ $itemID ]['craft_recipe']);
        foreach( $requirements as $requirement ){
            list($reqID,$reqAmount) = explode(",", $requirement);
            $reqData[ $reqID ] = $reqAmount;
        }
        return $reqData;
    }

    // Function for building the craft-link in the craft menu. Separated from main loop for clarity
    private function createCraftLink( $itemID ){

        $timeMod = 1;
        // Check for global event modifications
        if( $event = functions::getGlobalEvent("ModifyCraftTime")){
            if( isset( $event['data']) && is_numeric( $event['data']) ){
                $timeMod = round($event['data'] / 100,2);
            }
        }

        // Time to craft
        $entryTime = ceil($this->sortedItems[ $itemID ]['craftProcessMinutes'] * $timeMod);

        // The craft-link OR show how far it is OR show queue
        if( $this->currentCrafts > 0 ){

            if(in_array($itemID, $this->userIIDs) ){

                // If in array and it's not been removed, then show time left
                $diff = $this->sortedUserItems[ $itemID ]['finishProcessing'] - $GLOBALS['user']->load_time;
                return functions::convert_time(
                        $diff,
                        'processTimer'.$diff,
                        'true',
                        1,
                        "Show",
                        "?id=".$_GET['id']."&page=crafting"
                );
            }
            else{
                if( $this->currentCrafts < $this->maxCraftQueue ){
                    //if( $this->itemLib->currentItems + 1 <= $this->itemLib->maxitm ){
                        return "<a href='?id=".$_GET['id']."&page=crafting&process=craft&iid=".$itemID."'>Craft</a> (".$entryTime." minutes)";
                    //}
                    //else{
                    //    return "<i>No Room</i>";
                    //}
                }
                else{
                    return "<i>Full Queue</i>";
                }
            }
        }
        else{
            //if( $this->itemLib->currentItems + 1 <= $this->itemLib->maxitm ){
                return "<a href='?id=".$_GET['id']."&page=crafting&process=craft&iid=".$itemID."'>Craft</a> (".$entryTime." minutes)";
            //}
            //else{
            //    return "<i>No Room</i>";
            //}
        }
    }

    // Finish all the ongoing finished crafting & update variables
    private function finishCraftings(){

        // Create array with user recipes
        for( $i=0 ; $i < $this->craftIDcount ; $i++ ){

            // The craft-link OR show how far it is OR show queue
            $itemID = $this->craftIDs[ $i ];
            if( $this->currentCrafts > 0 && in_array($itemID, $this->userIIDs) ){

                // Check time
                $diff = $this->sortedUserItems[$itemID]['finishProcessing'] - $GLOBALS['user']->load_time;
                if(  $diff < 0 && $this->sortedUserItems[$itemID]['finishProcessing'] > 0 ){

                    // Reduce the count
                    $this->currentCrafts -= 1;

                    // Start processing
                    $this->itemLib->updateUserItem(
                        array("finishProcessing" => 0 ) ,
                        array(
                            "timekey" => $this->sortedUserItems[$itemID]['timekey'],
                            "uid" => $this->sortedUserItems[$itemID]['uid'] ,
                            "id" => $this->sortedUserItems[$itemID]['id'] ,
                            "iid" => $this->sortedUserItems[$itemID]['iid']
                        )
                    );

                    // Update for next loop
                    $this->sortedUserItems[$itemID]['finishProcessing'] = 0;

                    // Check if experience was gained
                    $extraMessage = "";
                    if( in_array($itemID, $this->threeHardest) ){

                        // Calculate gain
                        $gain = $this->experienceGains['base'];
                        switch( $this->craftDifficultyCategory[ $itemID ] ){
                            case 1: $gain += $this->experienceGains['0']; break;
                            case 2: $gain += $this->experienceGains['1']; break;
                            case 3: $gain += $this->experienceGains['2']; break;
                            default: throw new Exception("Could not figure out what experience to grant"); break;
                        }

                        // Add to gain if silver/gold supporter
                        switch( $GLOBALS['userdata'][0]['federal_level'] ){
                            case "Silver":
                                $gain *= 1.25;
                            break;
                            case "Gold":
                                $gain *= 1.5;
                            break;
                        }

                        // Round off the value
                        $gain = round($gain,2);

                        // Cap profession experience
                        $cap = 150;
                        switch( $GLOBALS['userdata'][0]['rank_id'] ){
                            case 4: $cap = 300; break;
                            case 5: $cap = 450; break;
                        }
                        if( $this->user[0]['profession_exp'] + $gain > $cap ){
                            $GLOBALS['Events']->acceptEvent('profession_exp', array('new'=>$cap, 'old'=> $this->user[0]['profession_exp'] ));
                            $this->user[0]['profession_exp'] = $cap;
                            $extraMessage .= " You have capped the experience in your profession.";
                        }
                        else{
                            $GLOBALS['Events']->acceptEvent('profession_exp', array('new'=>$this->user[0]['profession_exp'] + $gain, 'old'=> $this->user[0]['profession_exp'] ));
                            $this->user[0]['profession_exp'] += $gain;
                            $extraMessage .= $gain . " profession exp gained.";
                        }

                        // Update the database
                        $this->set_occupation_data(array("profession_exp" => ($this->user[0]['profession_exp'] ) ) );

                    }

                    // Finish crafting
                    $GLOBALS['Events']->acceptEvent('profession_craft', array('data'=>$itemID, 'count'=>$this->sortedItems[ $itemID ]['craft_stack'] ));
                    $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => "Craft Finished: ".$this->sortedItems[ $itemID ]['name'].". ".$extraMessage));
                }
            }
        }
    }

    // Function for initiating crafting
    private function startCrafting( $itemID ){

            // Start transaction for anything going on here
            $GLOBALS['database']->transaction_start();

            // Get the required crafting items
            $requirements = $this->getRequirements($itemID);

            // Get the neccesary database rows and lock them
            $currentCrafting = $GLOBALS['database']->fetch_data("
                SELECT  `users_inventory`.*, `items`.`type`
                FROM `users_inventory`
                LEFT JOIN `items` ON (`users_inventory`.`iid` = `items`.`id`)
                WHERE
                    `uid` = '" . $_SESSION['uid'] . "' AND
                    `equipped` = 'no' AND
                    `trade_type` IS NULL AND
                    (
                        `iid` IN (".implode(',',array_keys($requirements)).") OR
                        `finishProcessing` != '0'
                    )
                FOR UPDATE
            ");

            // Get current crafts
            $currentCrafts = 0;
            $this->timeTrack = $GLOBALS['user']->load_time;
            $this->userIIDs = array();

            // Initial loop-through
            if( $currentCrafting !== "0 rows" && $currentCrafting !== 0){
                foreach( $currentCrafting as $userItem ){
                    if( $userItem['type'] !== "process" ){
                        if( $userItem['finishProcessing'] > 0 ){
                            $currentCrafts++;
                            $this->userIIDs[] = $userItem['iid'];
                        }
                        if( $userItem['finishProcessing'] > $this->timeTrack  ){
                            $this->timeTrack = $userItem['finishProcessing'];
                        }
                    }
                }
            }


            //if( $this->itemLib->currentItems + 1 <= $this->itemLib->maxitm ){

                // Check that the user can craft this item
                if( $this->canCraftItem($this->sortedItems[ $itemID ]) ){

                    // Check if can craft any more
                    if( $currentCrafts < $this->maxCraftQueue ){

                        if( !in_array( $itemID,$this->userIIDs) ){

                            // Two variables for storing information on which items to be updated (stack) and removed
                            $updateItems = array();
                            $removeItems = array();

                            // Check if the user has the required items, and if not, throw exception
                            foreach( $requirements as $id => $amount ){

                                // Variable to store the found amount
                                $found = 0;
                                $amountInRemovedStacks = 0;
                                if( $currentCrafting !== "0 rows" && $currentCrafting !== 0){
                                    foreach( $currentCrafting as $userItem ){

                                        // Is this the item we're looking for?
                                        if( $userItem['iid'] == $id &&
                                            $userItem['finishProcessing'] == 0 &&
                                            empty($userItem['trading'])
                                        ){

                                            // Extra check is required, so that we don't update & remove
                                            if( $found < $amount ){

                                                // Update the counter for how many we've found
                                                $found += $userItem['stack'];

                                                // Check if enough
                                                if( $found > $amount ){
                                                    $newStack = $userItem['stack'] - ($amount - $amountInRemovedStacks);
                                                    $updateItems[ $userItem['id'] ] = $newStack;
                                                    break;
                                                }
                                                else{
                                                    $removeItems[] = $userItem['id'];
                                                    $amountInRemovedStacks += $userItem['stack'];
                                                }
                                            }
                                            else{
                                                break;
                                            }
                                        }
                                    }
                                }

                                // Check if enough
                                if( $found < $amount ){
                                    throw new Exception("You do not have enough: ".$this->sortedItems[ $id ]['originalName'] );
                                }
                            }

                            // Remove items
                            if( !empty($removeItems) ){
                                foreach( $removeItems as $id ){
                                    $this->itemLib->removeUserItem( array(
                                        "id" => $id,
                                        "uid" => $_SESSION['uid']
                                    ) );
                                }
                            }

                            // Update items
                            if( !empty($updateItems) ){
                                foreach( $updateItems as $id => $newStack ){
                                     $this->itemLib->updateUserItem(
                                        array("stack" => $newStack ) ,
                                        array(
                                            "uid" => $_SESSION['uid'] ,
                                            "id" => $id
                                        )
                                    );
                                }
                            }

                            $timeMod = 1;
                            // Check for global event modifications
                            if( $event = functions::getGlobalEvent("ModifyCraftTime")){
                                if( isset( $event['data']) && is_numeric( $event['data']) ){
                                    $timeMod = round($event['data'] / 100,2);
                                }
                            }

                            // Insert new item
                            $finishTime = $this->timeTrack + floor($this->sortedItems[ $itemID ]['craftProcessMinutes'] * $timeMod * 60);
                            $this->itemLib->addItemToUser(
                                $_SESSION['uid'] ,
                                $itemID ,
                                $this->sortedItems[ $itemID ]['craft_stack'] ,
                                $finishTime ,
                                'crafting'
                            );

                            // Update for the page log
                            $this->userIIDs[] = $itemID;
                            $this->sortedUserItems[ $itemID ]['finishProcessing'] = $finishTime;
                            $this->currentCrafts++;

                            // Start crafting
                            $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => "You have started creating: ".$this->sortedItems[ $itemID ]['name']));
                        }
                        else{
                            throw new Exception("You cannot craft two of the same item at once");
                        }
                    }
                    else{
                        throw new Exception("Cannot craft more items at once");
                    }
                }
                else{
                    throw new Exception("You are not eligible to craft this item");
                }
            //}
            //else{
            //    throw new Exception("You cannot fit any more into your inventory");
            //}

            // Commit the transaction
            $GLOBALS['database']->transaction_commit();

    }

    // A page for showing everything related to village supplying
    private function supplyVillage( $type ){

        // Handle any submissions
        if( isset($_GET['iid']) && is_numeric($_GET['iid'])){
            $this->submitForSupply( $type, $_GET['iid'] );
        }

        $activeCount = cachefunctions::getVillageActiveMemberCount($GLOBALS['userdata'][0]['village'] );
        $activeCount['total'] = $activeCount[0]['as_count'] + $activeCount[0]['genin_count'] + $activeCount[0]['chuunin_count'] + $activeCount[0]['jounin_count'] + $activeCount[0]['sj_count'];

        // Check if user is currently crafting something
        $supplies = $GLOBALS['database']->fetch_data("
            SELECT  `users_inventory`.*,
                    `items`.`name`,
                    `items`.`type`,
                    `items`.`price`
            FROM `users_inventory`
            INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid`)
            WHERE
                `uid` = '" . $_SESSION['uid'] . "' AND
                `iid` = `items`.`id` AND
                `items`.`type` = 'reduction' AND
                (
                    `items`.`village_restriction` = 'All' OR
                    `items`.`village_restriction` = '".$GLOBALS['userdata'][0]['village']."'
                ) AND
                `professionRestriction` = '".$this->user[0]['name']."' AND
                `finishProcessing` = '0'
        ");

        // Check supplies
        if( $supplies !== "0 rows" ){
            for( $i=0; $i<count($supplies); $i++ ){
                if( $supplies[$i]['stack'] > 1 ){
                    $supplies[$i]['name'] .= " (".$supplies[$i]['stack'].")";
                }
            }
        }

        // Get current supply
        $village = $GLOBALS['database']->fetch_data("
            SELECT * FROM `village_structures`
            WHERE `name` = '" . $GLOBALS['userdata'][0]['village'] . "'
            LIMIT 1"
        );


        // Set the description
        $description = "";
        switch( $type ){
            case "hospital":
                $description = "Herbalists have the ability to bring Recipes to the Hospital, the hospital gets a counter for this. Each time the Counter reaches a number equal to the Total village members, the automatic heal chance increases to 33% for 48 hours. <b>Current supply:</b> <a href='?id=9&act=hospitalSupply'>".$village[0]['hospital_supply']." / ".$activeCount['total']."</a>";
            break;
            case "ramen":
                $description = "Chefs have the ability to bring Ramen to the Ramen shop, the shop gets a counter for this. Each time the Counter reaches a number equal to the Total village members, the cost for ramen is reduced by 50% for 48 hours. <b>Current supply:</b> <a href='?id=9&act=ramenSupply'>".$village[0]['ramen_supply']." / ".$activeCount['total']."</a>";
            break;
        }

        tableParser::show_list(
            'controlPanel',
            "Item Crafting",
            $supplies,
            array(
                'name' => "Name",
                'type' => "Type",
                'price' => "Ryo Value"
            ),
            array(
                array("name" => "Send", "id" => $_GET['id'], "page" => $_GET['page'], "act2" => "send", "iid" => "table.id")
            ),
            false,   // Send directly to contentLoad
            false,   // Show previous/next links
            $this->cPanelOptions, // Options on the top
            false,   // Allow sorting on columns
            false,   // pretty-hide options
            false, // Top stuff
            $description // Top information
        );
    }

    // A function for submitting a material for the village
    private function submitForSupply( $type, $inventoryID ){

        // Start transaction for anything going on here
        $GLOBALS['database']->transaction_start();

        // Check that the user has the neccesary item
        if( $item_data = $this->itemLib->userHasItem(
            array( "uid" => $_SESSION['uid'], "`users_inventory`.`id`" => $_GET['iid'] )
        ) ){

            // Check that the user can craft this item
            if( $item_data[0]['type'] == "reduction" && $item_data[0]['professionRestriction'] == $this->user[0]['name'] ){

                // Get current villages & village data
                $village = $GLOBALS['database']->fetch_data("
                    SELECT * FROM `village_structures`
                    WHERE `name` = '" . $GLOBALS['userdata'][0]['village'] . "'
                    LIMIT 1"
                );
                if( $village !== "0 rows" ){

                    // Active villagers
                    $villagers = cachefunctions::getVillageACount();
                    $key = strtolower($GLOBALS['userdata'][0]['village'] )."_vcount";
                    if( isset($villagers[0][ $key ]) && $villagers[0][ $key ] > 0){

                        // Determine whether to give bonus or increment amount
                        $newAmount = $village[0][ $type."_supply" ] + $item_data[0]['strength']*$item_data[0]['stack'];
                        $newTime = $village[0][ $type."_bonus" ];

                        // Check the new amount
                        if( $newAmount > $villagers[0][ $key ] ){
                            $newAmount -= $villagers[0][ $key ];
                            $newTime = $GLOBALS['user']->load_time + 48*3600;

                            // Set message to villagers
                            $GLOBALS['database']->execute_query("
                                UPDATE `users`
                                SET `notifications` = CONCAT('id:17;duration:none;text:Village ".$type." supply has been fully
                                    stocked by the ninja players in
                                    the village. Appropriate bonuses will be present 
                                    the next 48 hours.;dismiss:yes;buttons:none;select:none;//',`notifications`)
                                WHERE `village` = '".$GLOBALS['userdata'][0]['village']."'");

                            $GLOBALS['database']->execute_query("
                                UPDATE `users_actionLog`
                                    INNER JOIN `users` ON (`users`.`id` = `users_actionLog`.`uid`)
                                    SET `users_actionLog`.`time` = 0
                                    WHERE `users`.`village` = '".$GLOBALS['userdata'][0]['village']."'
                                    AND `users_actionLog`.`attached_info` = '".$type."'
                                ");

                            $GLOBALS['database']->execute_query("
                            INSERT INTO `users_actionLog`(`id`, `notes`, `uid`,                `time`,     `action`,        `attached_info`, `additional_info`)
                            VALUES 		                 (NULL, '',      ".$_SESSION['uid'].", ".(time()-10).", 'villageSupply', '".$type."',     ".$item_data[0]['strength']*$item_data[0]['stack'] - $newAmount.")");

                            $GLOBALS['database']->execute_query("
                            INSERT INTO `users_actionLog`(`id`, `notes`, `uid`,                `time`,     `action`,        `attached_info`, `additional_info`)
                            VALUES 		                 (NULL, '',      ".$_SESSION['uid'].", ".(time()+10).", 'villageSupply', '".$type."',     ".$newAmount.")");

                        }
                        else
                        {
                                $GLOBALS['database']->execute_query("
                            INSERT INTO `users_actionLog`(`id`, `notes`, `uid`,                `time`,     `action`,        `attached_info`, `additional_info`)
                            VALUES 		                 (NULL, '',      ".$_SESSION['uid'].", ".time().", 'villageSupply', '".$type."',     ".$item_data[0]['strength']*$item_data[0]['stack'].")");
                        }

                        // Update table
                        $GLOBALS['database']->execute_query( "
                            UPDATE `village_structures`
                            SET
                                `".$type."_supply` = '".$newAmount."',
                                `".$type."_bonus` = '".$newTime."'
                            WHERE `name` = '" . $GLOBALS['userdata'][0]['village'] . "'
                            LIMIT 1"
                        );


                        // Remove the item
                        $this->itemLib->removeUserItem(array(
                            "uid" => $_SESSION['uid'],
                            "id" => $_GET['iid']
                        ));

                        // Commit transaction
                        $GLOBALS['database']->transaction_commit();
                    }
                    else{
                        throw new Exception("Could not determine the amount of users in your faction");
                    }
                }
                else{
                    throw new Exception("Could not determine where you're from");
                }
            }
            else{
                throw new Exception("This is not a ".$type." supply.");
            }
        }
        else{
            throw new Exception("You do not seem to own the item you're trying to submit");
        }
    }
}
