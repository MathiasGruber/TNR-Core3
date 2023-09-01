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
require_once(Data::$absSvrPath.'/libs/Battle/BattleStarter.php');
class travelLib extends mapfunctions {

    // Set data
    public function set_data(){

        // Queries
        $this->user = $GLOBALS['database']->fetch_data("SELECT `occupation`, `special_occupation`,`feature` FROM `users_occupations` WHERE `userid` = '" . $GLOBALS['userdata'][0]['id'] . "' ");

        // Messages for user
        $this->menuMessage = array();
        $this->canContinue = array();
        $this->startedBattle = false;
        $this->checkedTileEvents = false;
        $this->checkedQuestEvents = false;

        // Get all events
        $this->allEvents = cachefunctions::getEvents();

    }

    // Pass Information to Smarty
    public function updateSmarty(){
        $GLOBALS['template']->assign('x', $GLOBALS['userdata'][0]['longitude']);
        $GLOBALS['template']->assign('y', $GLOBALS['userdata'][0]['latitude']);
        $GLOBALS['template']->assign('location', stripslashes($GLOBALS['userdata'][0]['location']));
        $GLOBALS['template']->assign('mapLimit', 250 );
    }

    // Function for loading the travel page
    public function load_travel(){

        try
        {
        //create lock on self
            $GLOBALS['database']->get_lock('battle',$_SESSION['uid'],__METHOD__);

            // Check awake status
            if( $GLOBALS['userdata'][0]['status'] == "awake" ){

                // Confirm that user hasn't logged out yet
                if ($GLOBALS['userdata'][0]['logout_timer'] - $GLOBALS['user']->load_time > 0) {

                    // Get user
                    $this->user = $GLOBALS['database']->fetch_data("
                        SELECT
                            `login_id`, `logout_timer`, `longitude`,`rank_id`,`latitude`,
                            `village`,`location`,`region`,`level_id`,`occupation`, `special_occupation`,
                            `feature`,`user_rank`, `status`, `fbID`,`battle_id`,
                            `new_pm`, `users`.`id`, `users_timer`.`jutsu_timer`, `rank`, `username`,
                            `max_cha`, `cur_cha`, `max_sta`, `cur_sta`, `max_health`, `cur_health`,
                            `deletion_timer`, `jutsu_timer`, `drowning`
                        FROM `users`,`users_occupations`, `users_statistics`, `users_timer`
                        WHERE
                            `id` = '" . $GLOBALS['userdata'][0]['id'] . "' AND
                            `users`.`id` = `users_occupations`.`userid` AND
                            `users`.`id` = `users_statistics`.`uid` AND
                            `users`.`id` = `users_timer`.`userid`
                        LIMIT 1 FOR UPDATE"
                    );

                    // Get the battle with the latest locked battle ID
                    $this->battle = functions::checkIfInBattle(
                        $GLOBALS['userdata'][0]['id'],
                        $this->user[0]['battle_id']
                    );

                    // Before allowing to watch, check for current battles
                    if ( $this->battle == '0 rows' ) {

                        if ( $this->user[0]['status'] == "awake") {

                            // Update quest if requested
                            if( isset($_GET['act']) && $_GET['act'] == "activateQuest" ){
                                $this->activateQuest();
                            }

                            // Update dialogue travel event. Also fire potential new resulting events
                            if( isset($_GET['act']) && $_GET['act'] == "answerEvent" ){
                                $this->answerDialogueEvent();
                            }

                            // Check if bounty hunter for target
                            $this->checkBountyHunter( $GLOBALS['userdata'][0]['village'], $this->user[0]['special_occupation'], $this->user[0]['feature'], $GLOBALS['userdata'][0]['rank_id']);

                            // Move Around
                            if ( isset($_GET['move']) && !$GLOBALS['userdata'][0]['over_encumbered'] && preg_match( "/(north|south|west|east)/", $_GET['move'] )  ) {
                                $this->do_move( $_GET['move'] );
                            }
                            else{
                                $this->startedBattle = true;
                                $this->check_tiles(
                                    $GLOBALS['userdata'][0]['longitude'],
                                    $GLOBALS['userdata'][0]['latitude'],
                                    $GLOBALS['userdata'][0]['location'],
                                    $GLOBALS['userdata'][0]['region']
                                );
                                $this->check_quests();
                            }
                        }
                        else {
                            $GLOBALS['userdata'][0]['status'] = $this->user[0]['status'];
                            $GLOBALS['template']->assign('userStatus', $this->user[0]['status']);
                            throw new Exception("You cannot travel when your status is: ".$this->user[0]['status']);
                        }

                    }
                    else {
                        $GLOBALS['Events']->acceptEvent('status', array('new'=>'combat', 'old'=>$GLOBALS['userdata'][0]['status'] ));

                        $GLOBALS['database']->execute_query("UPDATE `users` SET `status` = 'combat', `database_fallback` = 0, `battle_id` = '".$this->battle[0]['id']."' WHERE `id` = '" . $GLOBALS['userdata'][0]['id'] . "' AND `status` = 'awake' LIMIT 1");
                        $GLOBALS['userdata'][0]['status'] = "combat";
                        $GLOBALS['template']->assign('userStatus', 'combat');
                        $GLOBALS['userdata'][0]['database_fallback'] = 0;
                        $GLOBALS['database']->transaction_commit();
                        throw new Exception("The system has found your character to be engaged in battle");
                    }
                }
                else{
                    throw new Exception("Your logout timer has expired");
                }
            }
            else{
                throw new Exception("Your status is not awake");
            }
        }
        catch(Exception $e)
        {
            //release lock on self
            $GLOBALS['database']->release_lock('battle',$_SESSION['uid'],__METHOD__);

            if( stripos($e->getMessage(), "There was an issue obtaining a lock.") === false)
                throw $e;
        }
        //release lock on self
            $GLOBALS['database']->release_lock('battle',$_SESSION['uid'],__METHOD__);
    }

    // Move the user
    private function do_move( $direction ) {



        // Check that the user is awake
        if( $this->user[0]['status'] == "awake" ){


            // Coordinates
            $x = $this->user[0]['longitude'];
            $y = $this->user[0]['latitude'];
            $location = $this->user[0]['location'];
            $owner = "";
            $region = $this->user[0]['region'];
            $backupLong = $this->user[0]['longitude'];
            $backupLat = $this->user[0]['latitude'];

            // Movement!
            $hasMoved = false;
            switch ($direction) {
                case "west":
                    if ($this->user[0]['longitude'] > -250) {
                        $y = $this->user[0]['latitude'];
                        $x = $this->user[0]['longitude'] - 1;
                        $hasMoved = true;
                    }
                    break;
                case "east":
                    if ($this->user[0]['longitude'] < 250) {
                        $y = $this->user[0]['latitude'];
                        $x = $this->user[0]['longitude'] + 1;
                        $hasMoved = true;
                    }
                    break;
                case "north":
                    if ($this->user[0]['latitude'] > -250) {
                        $y = $this->user[0]['latitude'] - 1;
                        $x = $this->user[0]['longitude'];
                        $hasMoved = true;
                    }
                    break;
                case "south":
                    if ($this->user[0]['latitude'] < 250) {
                        $y = $this->user[0]['latitude'] + 1;
                        $x = $this->user[0]['longitude'];
                        $hasMoved = true;
                    }
                    break;
                case "standGround":
                    $y = $this->user[0]['latitude'];
                    $x = $this->user[0]['longitude'];
                    $hasMoved = true;
                    break;
            }

            // Do checks on movement
            if ($hasMoved == true) {


                // List of regions outside map
                $region = 'wasteland';
                $limits = array(
                    array("limits" => array(0,26,21,0), "location" => "Uncharted territory", "region" => "uncharted"),
                    array("limits" => array(-100,100,100,-100), "location" => "ocean", "region" => "ocean"),
                    array("limits" => array(-250,250,250,-250), "location" => "You're Lost", "region" => "ocean")
                );
                foreach ($limits as $entry) {
                    if ($x <= $entry['limits'][0] ||
                        $x >= $entry['limits'][1] ||
                        $y >= $entry['limits'][2] ||
                        $y <= $entry['limits'][3]
                    ) {
                        $location = $entry['location'];
                        $region = $entry['region'];
                    }
                }

                if(mapfunctions::isOceanTile( mapfunctions::getOceanTiles(), $x, $y ))
                {
                    $location = "ocean";
                    $region = "ocean";
                }

                // Get Map information & Check location information
                $mapInformation = mapfunctions::getMapInformation();
                $locationInformation = cachefunctions::getLocations();
                $locInfo = mapfunctions::getTerritoryInformation(array("x.y" => $x . "." . $y), $mapInformation, $locationInformation);

                // Set important stuff
                $owner = $locInfo ? $locInfo['owner'] : "Unclaimed Territory";
                $location = $locInfo ? '' . $locInfo['name'] . '' : $location;

                // Check to see if new location is allowed & if user is teleported or moved
                if( $this->allEvents["tile_events"] !== "0 rows" ){
                    foreach ($this->allEvents["tile_events"] as $key => $entry) {
                        if( $this->isEventActive($key) ){
                            if( $this->testLocation($entry['area'], $x, $y, $location, $region) ){
                                if( $entry['event_type'] == "block" ){

                                    // Determine if blocked or not
                                    if( $this->checkAllCommonTags($entry['data']) !== true ){
                                        $this->logUserAction( $key , "User was blocked from entering blocked area");
                                        throw new Exception("This tile is blocked or you do not have the requirements for entering.");
                                    }
                                    else{
                                        $this->logUserAction( $key , "User managed to enter a blocked area");
                                    }

                                } elseif( $entry['event_type'] == "teleport" ||
                                          $entry['event_type'] == "forcemove" )
                                {
                                    list($x, $y) = $this->handleMovementTags( $key, $x, $y );
                                }
                            }
                        }
                    }
                }


            //update drowning status

                $drowning = $this->user[0]['drowning'];
                $status = $this->user[0]['status'];

                //if the user will be in the ocean after move and drowning counter is not at max increase drowning counter
                if( mapfunctions::isOceanTile( mapfunctions::getOceanTiles(), $x, $y ))
                {

                    if($drowning < 5 + $this->user[0]['rank_id'])
                    {
                        $drowning++;
                    }
                }

                //if the user will not be in the ocean after move decress drowning counter unless the counter is already at zero.
                else if ($drowning > 0)
                {


                    $drowning--;
                }

                //if the user is drowning
                if($drowning >= 5 + $this->user[0]['rank_id'])
                {

                    $old_health = $GLOBALS['userdata'][0]['cur_health'];
                    $GLOBALS['userdata'][0]['cur_health'] -= floor($GLOBALS['userdata'][0]['max_health'] * 0.09);

                    if($GLOBALS['userdata'][0]['cur_health'] < 0)
                    {


                        $GLOBALS['userdata'][0]['cur_health'] = 0;
                    }

                    $GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `users_statistics`.`cur_health` = " . $GLOBALS['userdata'][0]['cur_health'] . " WHERE `users_statistics`.`uid` = " . $_SESSION['uid']);

                    $GLOBALS['Events']->acceptEvent('stats_cur_health', array('new'=>$GLOBALS['userdata'][0]['cur_health'], 'old'=>$old_health ));

                    if($GLOBALS['userdata'][0]['cur_health'] == 0)
                    {
                        $status = "drowning";
                        $drowning = 0;

                        //square search that fills out layer by layer from cardinals
                        for($n = 1, $notLand = true; $notLand; $n++)
                        {
                            for($i = 0; $i <= $n && $notLand; $i++)
                            {
                                     if($i != 0 && $i != $n && !mapfunctions::isOceanTile( mapfunctions::getOceanTiles(), $x + $n, $y + $i )){ $notLand = false; $x += $n; $y += $i;}
                                else if($i != 0 && $i != $n && !mapfunctions::isOceanTile( mapfunctions::getOceanTiles(), $x - $n, $y - $i )){ $notLand = false; $x -= $n; $y -= $i;}
                                else if($i != 0 && $i != $n && !mapfunctions::isOceanTile( mapfunctions::getOceanTiles(), $x - $i, $y + $n )){ $notLand = false; $x -= $i; $y += $n;}
                                else if($i != 0 && $i != $n && !mapfunctions::isOceanTile( mapfunctions::getOceanTiles(), $x + $i, $y - $n )){ $notLand = false; $x += $i; $y -= $n;}

                                else if(!mapfunctions::isOceanTile( mapfunctions::getOceanTiles(), $x + $n, $y - $i )){ $notLand = false; $x += $n; $y -= $i;}
                                else if(!mapfunctions::isOceanTile( mapfunctions::getOceanTiles(), $x - $n, $y + $i )){ $notLand = false; $x -= $n; $y += $i;}
                                else if(!mapfunctions::isOceanTile( mapfunctions::getOceanTiles(), $x + $i, $y + $n )){ $notLand = false; $x += $i; $y += $n;}
                                else if(!mapfunctions::isOceanTile( mapfunctions::getOceanTiles(), $x - $i, $y - $n )){ $notLand = false; $x -= $i; $y -= $n;}
                            }
                        }

                    }
                }

                //  Update user
                if (!$GLOBALS['database']->execute_query("
                        UPDATE `users`
                        SET `longitude` = '" . $x . "',
                            `latitude`= '" . $y . "',
                            `location` = '" . addslashes($location) . "',
                            `status` = '" . addslashes($status) . "',
                            `region` = '" . addslashes($region) . "',
                            `drowning` = '" . $drowning . "'
                        WHERE `id` = '" . $GLOBALS['userdata'][0]['id'] . "'
                        LIMIT 1") && $direction !== "standGround"
                ) {
                    throw new Exception("Failed to move user");
                }
                $GLOBALS['Events']->acceptEvent('status', array('new'=>$status, 'old'=>$GLOBALS['userdata'][0]['status'] ));


                // Remove any resources the user is gathering
                cachefunctions::endHarvest( $GLOBALS['userdata'][0]['id'] );

                // Update important variables
                //$GLOBALS['userdata'][0]['longitude'] = $x;
                //$GLOBALS['userdata'][0]['latitude'] = $y;
                //$GLOBALS['userdata'][0]['location'] = addslashes($location);
                //$GLOBALS['userdata'][0]['owner'] = $owner;
                //$GLOBALS['userdata'][0]['region'] = $region;
                //$GLOBALS['userdata'][0]['drowning'] = $drowning;
                //$GLOBALS['userdata'][0]['status'] = $status;



                // Update user tracking in the cache
                cachefunctions::updateUserMovement($GLOBALS['userdata'][0]['id'], $x, $y, $region, $location);

                //adding drowning notification and event
                if($GLOBALS['userdata'][0]['status'] == "drowning")
                {
                    //$this->menuMessage[] = array(
                    //    "id" => time()+random_int(10,1010),
                    //    "duration" => "none",
                    //    "dismiss" => "no",
                    //    "text" => 'You are unconscious!',
                    //    "buttons" => array('?id=109',"Drift"),
                    //    "popUp" => true,
                    //    "dismiss" => 'yes'
                    //);

                    $GLOBALS['NOTIFICATIONS']->addTempNotification( array('text' => 'You are unconscious!', 'buttons' => array('?id=109','Drift'), 'popup' => 'yes') );
                }

                // Check event and attacks. Independt transactions for all events where needed
                $this->check_events($x, $y, $region, $location, $GLOBALS['userdata'][0]['id'], $GLOBALS['userdata'][0]['level_id']);

                // Transaction finish
                $GLOBALS['database']->transaction_commit();

            }
            else {
                throw new Exception("You cannot move this way");
            }
        }
        else {
            $GLOBALS['userdata'][0]['status'] = $this->user[0]['status'];
            $GLOBALS['template']->assign('userStatus', $this->user[0]['status']);
            throw new Exception("You cannot travel when your status is: ".$this->user[0]['status']);
        }
    }

    // Checks for current events in the designated square
    // This includes: item events, ai attacks, message events etc
    private function check_events($x, $y, $region, $location, $uid, $levelID) {

        // Step 1: Go through all events and check if they should be loaded
        $this->check_tiles( $x, $y, $location, $region );

        // Get quest events
        $quest_events = $this->getQuestEvents();

        // Step 2: Load all the events that were determined in step 1
        $noattack = false;
        if (isset($quest_events[0])) {
            $this->quest_events($quest_events, $uid, $levelID, $location);
        }

        // Check missions & crimes
        if( !$noattack ){
            $noattack = $this->check_missions( $uid , $location );
        }

        // Check for attacks
        if (!$noattack) {
            $this->check_attack($uid, $levelID, $location, $region);
        }
    }

    // All Tile Event Functions
    // ========================

    // Clean initial data string and return array of tags
    private function getDataTags( $data ){
        $data = trim($data);
        $temp = explode(";", $data);
        foreach( $temp as $key => $entry ){
            if( !stristr($entry,"MSG:") && !stristr($entry,"OPTION:")){
                $temp[$key] = preg_replace('/\s+/', '', $temp[$key]);
            }
        }
        return $temp;
    }

    // For tags moving the user, get coordinates from tag with tagType in data
    private function getPositionFromTag( $data, $tagType ){
        foreach( $this->getDataTags($data) as $tag ){
            if( stristr($tag, $tagType.":") ){
                $temp = explode(":", $tag);
                if( isset($temp[1]) ){
                    $pos = explode(".", $temp[1]);
                    if( is_numeric($pos[0]) && is_numeric($pos[1]) ){
                        return array( $pos[0], $pos[1] );
                    }
                }
            }
        }
        return false;
    }

    // Check data for all common tags and see if they are all fulfilled
    private function checkAllCommonTags( $data ){
        $allRequirements = true;
        foreach( $this->getDataTags($data) as $tag ){
            $allRequirements = ($allRequirements == true) ? $this->checkCommonTag($tag) : false;
        }
        return $allRequirements;
    }

    // Check if user has item
    private function hasItem( $id ){
        $entry = $GLOBALS['database']->fetch_data("
              SELECT `users_inventory`.`id` as `inv_id`
              FROM `users_inventory`
              WHERE
                `users_inventory`.`iid` = ".$id." AND
                `users_inventory`.`uid` = ".$GLOBALS['userdata'][0]['id']." AND
                `durabilityPoints` > 0 AND
                `trading` IS NULL AND
                `finishProcessing` = 0
              LIMIT 1");
        if( $entry !== "0 rows" ){
            return true;
        }
        return false;
    }

    // Check if user has item
    private function hasJutsu( $id ){
        $entry = $GLOBALS['database']->fetch_data("
              SELECT `entry_id`
              FROM `users_jutsu`
              WHERE `jid` = ".$id." AND `uid` = ".$GLOBALS['userdata'][0]['id']."
              LIMIT 1");
        if( $entry !== "0 rows" ){
            return true;
        }
        return false;
    }

    // Check if a user has performed a given event already
    private function hasPerformedEvent( $id ){
        $entry = $GLOBALS['database']->fetch_data("
              SELECT `id`
              FROM `events_log`
              WHERE `event_id` = ".$id." AND `uid` = ".$GLOBALS['userdata'][0]['id']."
              LIMIT 1");
        if( $entry !== "0 rows" ){
            return true;
        }
        return false;
    }

    // Log user interaction with automated event
    private function logUserAction( $key, $message ){
        if( $this->allEvents["tile_events"] !== "0 rows" && ($this->allEvents["tile_events"][$key]['enable_log'] == "yes" || $this->allEvents["tile_events"][$key]['redoable'] == "no") ){
            $entry = $this->allEvents["tile_events"][$key];
            functions::log_event_action($GLOBALS['userdata'][0]['id'], $entry, $message, $GLOBALS['user']->load_time);
        }
    }

    // Check if an event is still active
    private function isEventActive( $key ){
        if( $this->allEvents["tile_events"] !== "0 rows" ){
            if( $this->allEvents["tile_events"][$key]['end_time'] <= 0 ||
                $this->allEvents["tile_events"][$key]['start_time']+$this->allEvents["tile_events"][$key]['end_time']*3600 > $GLOBALS['user']->load_time
            ){
                if( $this->allEvents["tile_events"][$key]['redoable'] == "yes" ||
                    !$this->hasPerformedEvent($this->allEvents["tile_events"][$key]['id'])
                ){
                    return true;
                }
            }
        }
        return false;
    }

    // For answering dialogs
    private function answerDialogueEvent(){
        try
        {
            if( isset($_GET['eventID']) && is_numeric($_GET['eventID']) ){
                if( isset($_GET['option']) && is_numeric($_GET['option']) ){
                    if( list($key, $event) = $this->getEventByID($_GET['eventID']) ){
                        if( $this->testLocation($event['area'], $GLOBALS['userdata'][0]['longitude'], $GLOBALS['userdata'][0]['latitude'], $GLOBALS['userdata'][0]['location'], $GLOBALS['userdata'][0]['region']) ){

                            // Get the chosen options
                            $options = $answers = array();
                            foreach( $this->getDataTags($event['data']) as $tag ){
                                $temp = explode(":", $tag);
                                if(  stristr($temp[0], "OPTION") && is_numeric($temp[2]) ){
                                    $options[$temp[2]] = $temp[2];
                                    $answers[$temp[2]] = stripslashes($temp[1]);
                                }
                            }

                            // Check that the chosen option is one of the available
                            if( !empty($options) && isset($options[ $_GET['option'] ]) ){

                                // Log action
                                $this->logUserAction( $key , "User answered event with: ".$answers[$_GET['option']]);

                                // Force activate this event, disable all others
                                foreach( $this->allEvents["tile_events"] as $key => $entry ){
                                    if( $options[ $_GET['option'] ] == $entry['id'] ){
                                        $this->allEvents["tile_events"][$key]['chance'] = 100;
                                        $this->allEvents["tile_events"][$key]['area'] = "AREA(-1000.1000.-1000.1000)";

                                    }
                                    else{
                                        $this->allEvents["tile_events"][$key]['chance'] = 0;

                                    }
                                }

                                // Launch the move function. This will potentially move the user according to events and launch other events.
                                $this->do_move("standGround");
                            }
                        }
                    }
                }
            }
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    // Get event with given event ID
    private function getEventByID( $id ){
        if( $this->allEvents["tile_events"] !== "0 rows" ){
            foreach( $this->allEvents["tile_events"] as $key => $event ){
                if( $event['id'] == $id){
                    return array($key , $event);
                }
            }
        }
        return false;
    }

    // Check & verify common tags for all tiles
    private function checkCommonTag( $tag ){
        $check = true;
        if( $tag !== "" ){
            $temp = explode(":", $tag);
            switch( $temp[0] ){
                case "ITM":
                    if( !(is_numeric($temp[1]) && $this->hasItem( $temp[1] ) )){
                        $check = false;
                    }
                break;
                case "JUT":
                    if( !(is_numeric($temp[1]) && $this->hasJutsu( $temp[1] ) )){
                        $check = false;
                    }
                break;
                case "user":
                    if( !($temp[1] == "rank_id" &&
                          is_numeric($temp[2]) &&
                          $GLOBALS['userdata'][0]['rank_id'] >= $temp[2] ))
                    {
                        $check = false;
                    }
                break;
            }
        }
        return $check;
    }

    // Handle movements
    private function handleMovementTags( $key , $x, $y ){
        $entry = $this->allEvents["tile_events"][$key];
        if( $entry['chance'] >= random_int(1,100) ){
            $coor = false;
            switch( $entry['event_type'] ){
                case "teleport":
                    if( $coor = $this->getPositionFromTag($entry['data'], "LOC") ){
                        $x = $coor[0];
                        $y = $coor[1];
                    }
                break;
                case "forcemove":
                    if( $coor = $this->getPositionFromTag($entry['data'], "MOVE") ){
                        $x += $coor[0];
                        $y += $coor[1];
                    }
                break;
            }

            // Attach message as well
            if( $coor !== false ){
                $this->fireMessageTag( $key , $entry['data'], $entry['event_type']);
            }
        }
        return array($x, $y);
    }

    // Fires a message to the user notifications
    private function fireMessageTag( $key , $data , $eventType = "message"){
        foreach( $this->getDataTags($data) as $tag ){
            $temp = explode(":", $tag);
            if( $temp[0] == "MSG" ){
                //$this->menuMessage[] = array("text" => stripslashes($temp[1]), "type" => 'menu', "dismiss" => 'yes');
                $GLOBALS['NOTIFICATIONS']->addTempNotification( array('text' => stripslashes($temp[1])) );
                $this->logUserAction( $key , "User received message from event type: ".$eventType);
            }
        }
    }

    // Handle all the special tags on a given tile event
    private function fireAllSpecialTags( $key ){
        $entry = $this->allEvents["tile_events"][$key];
        switch( $entry['event_type'] ){
            case "itemdrop":

                // Check if duplicates are allowed
                $allowDuplicates = true;
                foreach( $this->getDataTags($entry['data']) as $tag ){
                    if( stristr($tag, "DUP:no") ){
                        $allowDuplicates = false;
                    }
                }

                // Drop Items
                foreach( $this->getDataTags($entry['data']) as $tag ){
                    if( stristr($tag, "DROP:") ){
                        $temp = explode(":", $tag);
                        if( isset($temp[1]) && is_numeric($temp[1]) ){

                            // Get the item library
                            require_once(Data::$absSvrPath.'/libs/itemSystem/itemFunctions.php');
                            $this->itemLib = new itemBasicFunctions();

                            // Check if we can add this item
                            if( $this->itemLib->canAddItems( array( $temp[1] => 1 ) ) ){
                                if(
                                    $allowDuplicates ||
                                    (!$allowDuplicates && !$this->itemLib->isItemInInventory( $temp[1] ) )
                                ){
                                    // Log action
                                    $this->logUserAction( $key , "User picked up item");

                                    // Insert item
                                    $this->itemLib->addItemToUser( $GLOBALS['userdata'][0]['id'] , $temp[1]  , 1 );

                                    //$this->menuMessage[] = array(
                                    //    "text" => 'You have found an item on the ground. It has been added to your inventory.',
                                    //    'type' => 'menu',
                                    //    "popUp" => true,
                                    //    "dismiss" => 'yes'
                                    //);

                                    $GLOBALS['NOTIFICATIONS']->addTempNotification( array("text" => 'You have found an item on the ground. It has been added to your inventory.', 'popup' => 'yes') );

                                }
                            }
                            else{
                                //$this->menuMessage[] = array(
                                //    "text" => 'You have found an item on the ground, but your inventory is already full',
                                //    'type' => 'menu',
                                //    "popUp" => true,
                                //    "dismiss" => 'yes'
                                //);

                                $GLOBALS['NOTIFICATIONS']->addTempNotification( array( "text" => 'You have found an item on the ground, but your inventory is already full', 'popup' => 'yes') );
                            }
                        }
                    }
                }

            break;
            case "message":
                $this->fireMessageTag( $key , $entry['data']);
            break;
            case "battle":
                foreach( $this->getDataTags($entry['data']) as $tag ){
                    $temp = explode(":", $tag);
                    if( $temp[0] == "AI" && isset($temp[1]))
                    {
                        $this->startBattleWithAI($temp[1], "event", $entry['id'], $GLOBALS['userdata'][0]['id']);
                        $this->logUserAction( $key , "User started battle");
                    }
                    else
                    {
                        error_log('bad tag: '.$tag);
                        throw new exception('bad start battle tag.');
                    }
                }
            break;
            case "dialogue":

                // First get message & answer options
                $message = "";
                $options = array();
                foreach( $this->getDataTags($entry['data']) as $tag ){
                    $temp = explode(":", $tag);
                    if( stristr($temp[0],"MSG") ){
                        $message = stripslashes($temp[1]);
                    }
                    elseif(  stristr($temp[0],"OPTION") ){
                        $options[] = stripslashes($temp[2]);
                        $options[] = stripslashes($temp[1]);
                    }
                }

                // Compile message for menu
                if( $message !== "" && !empty($options) ){
                    //$this->menuMessage[] = array(
                    //    "text" => $message,
                    //    "buttons" => 'none',
                    //    "select" => array($options,array('id','8','act','answerEvent','eventID',$entry['id'],'yes')),
                    //    "type" => 'menu',
                    //    "dismiss" => 'yes'
                    //    );

                    $GLOBALS['NOTIFICATIONS']->addTempNotification( array('text' => $Message, 'select' => array($options,array('id','8','act','answerEvent','eventID',$entry['id'],'yes')) ) );
                }

            break;
        }
    }


    // All Occupation Functions
    // =============================

    // Function for checking for what the bounty hunter is tracking
    private function checkBountyHunter($userVillage, $userOccupation, $userFeature, $userRankid) {

        if (
                ($userOccupation == 3 || $userOccupation == 2) &&
                $userFeature !== NULL
        ) {
            // If mercenary, change to specialBounty
            $userVillage = ($userOccupation == 3) ? "SpecialBounty" : $userVillage;

            $outlaw = $GLOBALS['database']->fetch_data("
                SELECT `bingo_book`.`" . $userVillage . "`, `users`.`username`, `users`.`longitude`,`users`.`latitude`,`users`.`location`
                FROM
                    `users`,`bingo_book`,`users_statistics`
                WHERE
                    `users`.`username` = '" . $userFeature . "' AND
                    `" . $userVillage . "` < 0 AND
                    `bingo_book`.`userid` = `users`.`id` AND
                    `users_statistics`.`uid` = `users`.`id`
                LIMIT 1");

            if ($outlaw !== "0 rows") {
                $this->menuMessage[] = array(
                    "text" => "Bounty Hunter Target Location:
                             " . $outlaw[0]['longitude'] . ".
                             " . $outlaw[0]['latitude'] . " -
                             " . ucfirst(stripslashes($outlaw[0]['location'])),
                    "hideMSG" => true,
                    "dismiss" => 'yes',
                    "type" => 'travel'
                );
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    // All Quest & Mission Functions
    // =============================

    // Activate User Quest
    private function activateQuest(){
        if( isset( $_GET['qID'] ) && ctype_digit($_GET['qID']) ){

            // Get Current User Tasks
            $userTasks = cachefunctions::getUserTasks( $GLOBALS['userdata'][0]['id'] );
            $userTasks = json_decode($userTasks[0]['tasks'], true);
            $quest = $GLOBALS['database']->fetch_data("SELECT * FROM `tasksAndQuests` WHERE `id` = '" . $_GET['qID'] . "' LIMIT 1");

            //no idea why but sometimes the region is empty when entering here...
            //so im setting the region to the location. its a sloppy fix...
            //but it is fixing an issue with ocean quests.
            if($GLOBALS['userdata'][0]['region'] == "")
                $GLOBALS['userdata'][0]['region'] = $GLOBALS['userdata'][0]['location'];

            // Check that he fills requirements and
            if( $quest !== "0 rows" ){
                if( $GLOBALS['userdata'][0]['level_id'] >= $quest[0]['levelReq'] &&
                    $GLOBALS['userdata'][0]['level_id'] <= $quest[0]['levelMax'] ){
                    if( $this->testLocation($quest[0]["locationReq"], $GLOBALS['userdata'][0]['longitude'], $GLOBALS['userdata'][0]['latitude'], $GLOBALS['userdata'][0]['location'], $GLOBALS['userdata'][0]['region']) ){
                        if(
                            !isset($userTasks['' . $quest[0]['id'] . ''])
                        ){
                            cachefunctions::deleteUserTasks( $GLOBALS['userdata'][0]['id'] );
                            $userTasks[$quest[0]['id']] = "a";
                            $userTasks[$quest[0]['id']."_time"] = $GLOBALS['user']->load_time;
                            $GLOBALS['database']->execute_query("UPDATE `users_missions` SET `tasks` = '".json_encode( $userTasks )."' WHERE `userid` = '" . $GLOBALS['userdata'][0]['id'] . "' LIMIT 1");
                        }
                    }
                }
            }
        }
    }

    // Get quest events
    private function getQuestEvents(){
        $quest_events = array();
        if( $this->allEvents["quest_events"] !== "0 rows"){

            // Include the task library to check the restrictions
            $this->taskLibrary = new tasks;
            $this->taskLibrary->userTasks = cachefunctions::getUserTasks( $GLOBALS['userdata'][0]['id'] );
            $this->taskLibrary->userTasks = json_decode($this->taskLibrary->userTasks[0]['tasks'] , true);

            foreach ($this->allEvents["quest_events"] as $entry) {
                if(
                    !isset($entry['restrictions']) ||
                    $entry['restrictions'] == "" ||
                    $this->taskLibrary->checkRestrictions( $entry['restrictions'] )
                ){
                    $quest_events[] = $entry;
                }
            }
        }
        return $quest_events;
    }

    // Show quests
    private function check_quests(){
        $quest_events = $this->getQuestEvents();
        if ( isset($quest_events[0])  ) {
            $this->quest_events($quest_events, $GLOBALS['userdata'][0]['id'], $GLOBALS['userdata'][0]['level_id'], $GLOBALS['userdata'][0]['location'] );
        }
    }

    // Check tile events
    private function check_tiles( $x, $y, $location, $region ){
        if( $this->checkedTileEvents == false ){
            $this->checkedTileEvents = true;
            if( $this->allEvents["tile_events"] !== "0 rows" ){
                foreach ($this->allEvents["tile_events"] as $key => $entry) {
                    if( $this->isEventActive($key) ){
                        if( $entry['chance'] >= random_int(1,100) ){
                            if( $this->testLocation($entry['area'], $x, $y, $location, $region) ){
                                if( $this->checkAllCommonTags($entry['data']) ){
                                    $this->fireAllSpecialTags($key);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // Quest Events
    private function quest_events($quest_events, $uid, $levelID, $location ) {

        // Check that it's only run once
        if( $this->checkedQuestEvents == false ){
            $this->checkedQuestEvents = true;

            // Get current user tasks & decode
            $userTasks = cachefunctions::getUserTasks($uid);
            $userTasks = json_decode($userTasks[0]['tasks'], true);

            // Loop over quests in this area
            foreach ($quest_events as $quest) {
                if ($levelID >= $quest['levelReq'] && $levelID <= $quest['levelMax']) {

                    $quest['description'] = stripslashes(nl2br($quest['description']));
                    if (
                        !isset($userTasks['' . $quest['id'] . ''])
                    ) {
                        if( $this->testLocation($quest["locationReq"], $GLOBALS['userdata'][0]['longitude'], $GLOBALS['userdata'][0]['latitude'], $GLOBALS['userdata'][0]['location'], $GLOBALS['userdata'][0]['region']) ){
                            if( random_int(1,100) < $quest["questChance"] && functions::checkStartEndDates($quest) ){
                                $this->menuMessage[] = array(
                                    "text" => "<b>" . $quest['name'] . "</b>: " . $quest['description'],
                                    "buttons" => array("?id=8&act=activateQuest&qID=" . $quest['id'], "Activate Quest!"),
                                    "type" => "travel",
                                    "hideMSG" => true,
                                    "popUp" => true,
                                    "dismiss" => 'yes',
                                    "href" => "?id=8&act=activateQuest&qID=" . $quest['id'],
                                    "linkText" => "Activate Quest!"
                                );

                                if($_GET['id'] != 8)
                                    $GLOBALS['NOTIFICATIONS']->addTempNotification( array('text' => "<b>" . $quest['name'] . "</b>: " . $quest['description'], 
                                                                                          'buttons' => array("?id=8&act=activateQuest&qID=" . $quest['id'], "Activate Quest!"),
                                                                                          'hide' => 'yes',
                                                                                          'popup' => 'yes') );
                            }
                        }
                    } elseif ($userTasks['' . $quest['id'] . ''] == "a") {
                        if( functions::checkStartEndDates($quest) ){
                            $txt = isset($GLOBALS['returnJson']) ? $quest['name'] : "<a href='?id=120&act=details&eid=".$quest['id']."'>" . $quest['name'] . "</a>";
                            $this->menuMessage[] = array(
                                "text" => "<b>" . $txt . "</b>: Quest is Active",
                                "type" => "travel",
                                "hideMSG" => true,
                                "popUp" => false,
                                "dismiss" => 'yes'
                            );

                            $this->checkTaskTag( $quest['id'], $location, $uid );
                        }
                    }
                }
            }
        }
    }

    // Check missions
    private function check_missions( $uid , $location ){
        // Check for mission events
        $this->battleStarted = false;
        $userTasks = cachefunctions::getUserTasks( $uid );
        if( trim($userTasks[0]['tasks']) !== ""  ){
            $userTasks = json_decode($userTasks[0]['tasks'] , true);
            if( isset($userTasks) ){
                foreach( $userTasks as $key => $task ){
                    if( $task == "m" ){
                        $this->checkTaskTag( $key, $location, $uid );
                    }
                }
            }
        }
        return $this->battleStarted;
    }

    // Check event tags
    private function checkTaskTag( $taskID , $location, $uid){

        // Get the mission/crime
        $mission = cachefunctions::getTasksQuestsMission($taskID);

        if( $mission !== "0 rows"){

            // Fix up
            $mission[0]['simpleGuide'] = explode(";", $mission[0]['simpleGuide']);
            $mission[0]['description'] = stripslashes($mission[0]['description']);

            // See if mission has a move or createAI requirement
            $tagsToCheck = array();
            if( isset($mission[0]['requirements']) && $mission[0]['requirements'] !== ""){
                $mission[0]['requirements'] = explode(";", $mission[0]['requirements']);
                foreach( $mission[0]['requirements'] as $requirement ){
                    $tags = explode(",", trim($requirement));
                    if( $tags[0] == "move" || $tags[0] == "createAI" ){
                        $tagsToCheck[] = $tags;
                    }
                }
            }

            // If both info and move requirement is present, check if completed
            if( !empty($tagsToCheck) ){
                foreach( $tagsToCheck as $tagToCheck ){
                    switch( $tagToCheck[0] ){

                        // In the case of a move requirement
                        case "move":
                            // Get the info to show
                            $infoToShow = false;
                            if( isset($mission[0]['simpleGuide']) && $mission[0]['simpleGuide'] !== ""){
                                foreach( $mission[0]['simpleGuide'] as $guideline ){
                                    $tags = explode(":", trim($guideline));
                                    if( $tags[0] == "info" ){
                                        $infoToShow = $tags[1];
                                    }
                                }
                            }
                            // Check info
                            if( $infoToShow ){
                                // Include library
                                $taskLibrary = new tasks;

                                // Check the move tags
                                if( $taskLibrary->checkMove($tagToCheck, $uid) ){
                                    //$this->menuMessage[] = array("text" => $infoToShow, "dismiss" => 'yes' );
                                    $GLOBALS['NOTIFICATIONS']->addTempNotification( array("text" => $InfoToShow) );
                                }
                            }
                        break;
                        // In the case of AI having been created
                        case "createAI":
                            // createAI,[.-separated IDlist for single battle],territoryName,chance
                            // Save the amount of times to beat the AI in the userTasks array

                            // Check territory name
                            if(
                                $this->testLocation($tagToCheck[2], $GLOBALS['userdata'][0]['longitude'], $GLOBALS['userdata'][0]['latitude'], $GLOBALS['userdata'][0]['location'], $GLOBALS['userdata'][0]['region']) &&
                                random_int(1,100) <= $tagToCheck[3] &&
                                !strstr($location, 'village')
                            ){

                                // Get the battle type
                                $typeNrank = explode( "_", $mission[0]['type'] );

                                // Start battle
                                $this->startBattleWithAI($tagToCheck[1], $typeNrank[0], $mission[0]['id'], $uid);
                            }
                        break;
                    }
                }

            }
        }
    }


    // Convenience Functions
    // =====================

    // Function to test x.y, location and region against tag
    private function testLocation( $tagIdentifier , $x, $y, $location, $region ){

        if( !empty($tagIdentifier) ){

            // Get stuff within paranthesis
            preg_match( "/\(([^)]+)\)/" , $tagIdentifier , $match );
            if( !empty($match) ){

                $info = $match[1];


                if( preg_match( "/(REGION)/", $tagIdentifier ) ){

                    if( $info == $region ){
                        return true;
                    }
                }
                elseif( preg_match( "/(TERRITORY)/", $tagIdentifier ) ){
                    if(  stristr( $info, $location ) ){
                        return true;
                    }
                }
                elseif( preg_match( "/(AREA)/", $tagIdentifier ) ){
                    $area = explode( ".", $info );
                    if( $x >= $area[0] && $x <= $area[1] && $y >= $area[2] && $y <= $area[3]){
                        return true;
                    }
                }
            }
        }
        else{
            return true;
        }

        // Else return false
        return false;
    }

    // Start a battle with AI list if possible
    private function startBattleWithAI( $aiList, $battleType , $id, $uid ){
        // Put into battle with the AI
        $aiList = explode(".",$aiList);
        $dbList = $GLOBALS['database']->fetch_data("SELECT * FROM `ai` WHERE `id` IN (".implode(",",$aiList).") LIMIT ".count($aiList));
        $opponent = array();
        foreach( $aiList as $aiID ){
            foreach( $dbList as $dbEntry ){
                if( $dbEntry['id'] == $aiID ){
                    $opponent[] = $dbEntry;
                    break;
                }
            }
        }

        // Start Battle with these opponents
        if ( !empty($opponent)  && $this->startedBattle == false ) {
            $users = array();
            $users[] = array('id'=>$_SESSION['uid'], 'team_or_extra_data'=>$GLOBALS['userdata'][0]['village']);

            $ai = array();
            foreach($opponent as $opponent_data)
            {
               $ai[] = array('id'=>$opponent_data['id'],'team'=>false);
            }

            $type='';
            if($battleType == 'quest')
                $type = BattleStarter::quest;
            else if($battleType == 'event')
                $type = BattleStarter::event;
            else if($battleType == 'mission' || $battleType == 'crime')
                $type = BattleStarter::mission;
            else
            {
                echo'report this bug please: error on->travellib1120?';
                echo'';
                echo'';
                throw new Exception($battleType);
            }

            BattleStarter::startBattle( $users, $ai, $type, false, false, true);

            $names = array();
            foreach($opponent as $thing)
                $names[] = $thing['name'];

            $GLOBALS['NOTIFICATIONS']->addTempNotification( array(  'text' => 'You have been attacked by: ' . implode(',',$names) . '. Defend yourself!',
                                                                    'buttons' => array('?id=113','go','no'),
                                                                    'popup' => 'yes',
                                                                    'color' =>'red')
                                                                 );

            $this->battleStarted = true;
        }
    }

    // Random Encounter Function
    // =========================

    public function check_attack( $uid , $levelID , $location , $region , $forceAttack = false ) {

        //  Check attack:
        $rand = random_int(1, 100);
        $select = "";
        if (($rand > 30 || $forceAttack) && $region == 'void') {
            $select = " `location` = 'void' AND ";
        } elseif (($rand > 60 || $forceAttack) && $region == "uncharted") {
            $select = " `location` = 'uncharted' AND ";
        } elseif (($rand > 65 || $forceAttack) && $region == 'lost') {
            $select = " `location` = 'uncharted' AND ";
        } elseif (($rand > 90 || $forceAttack) && !strstr($location, 'village') &&  $region == 'wasteland') {
            $select = " `location` = 'wasteland' AND ";
        } elseif(($rand > 90 || $forceAttack) && $region == 'ocean'){
            $select = " `location` = 'ocean' AND ";
        }

        // Get the opponent
        if(
           (!empty($select) &&
            !stristr($GLOBALS['userdata'][0]['location'],"village") &&
            $GLOBALS['userdata'][0]['location'] !== "City of Mei"
           ) || $forceAttack
        ){

            // Start query

            if($region == "ocean")
            {
                $query = "SELECT * FROM `ai` WHERE `location` = 'ocean' AND `type` = 'random' AND ";
            }
            else
            {
                $query = "SELECT * FROM `ai` WHERE `type` = 'random' AND ";
            }

            // Got opponent of lower level and higher level
            $lower = $GLOBALS['database']->fetch_data($query.$select."
                `level` <= ".$levelID."
                ORDER BY `level` DESC
                LIMIT 1");

            $higher = $GLOBALS['database']->fetch_data($query.$select."
                    `level` > ".$levelID."
                    ORDER BY `level` ASC
                    LIMIT 1");

            // Determine which opponent to return
            if( $lower !== "0 rows" && $higher !== "0 rows"){
                if( abs( $lower[0]['level'] - $levelID ) < abs( $higher[0]['level'] - $levelID ) ){
                    $opponent = $lower;
                }
                else{
                    $opponent = $higher;
                }
            }
            elseif( $lower !== "0 rows" ){
                $opponent = $lower;
            }
            else{
                $opponent = $higher;
            }

            // If none was found, select one of higher level.
            // MySQLi makes it difficult to do a efficient one-query solution for this
            // TODO: can probably be done and optimized somehow
            if( $opponent == "0 rows" ){


            }
        }

        // Process attack
        if (isset($opponent) && $opponent != '0 rows') {

            // Default the user passes to battle
            $pass = true;

            // Figure out the repel chance of the user
            $baseRepel = $GLOBALS['userdata'][0]['repel_effect'];
            if( $GLOBALS['userdata'][0]['repel_endtime'] > $GLOBALS['user']->load_time ){
                $baseRepel += $GLOBALS['userdata'][0]['repel_chance'];
            }

            // Add loyalty chance to repel
            if( $GLOBALS['userdata'][0]['activateBonuses'] == "yes" ){
                if( $GLOBALS['userdata'][0]['vil_loyal_pts'] >= 225 || $GLOBALS['userdata'][0]['vil_loyal_pts'] <= -220 ){
                    $baseRepel += 15;
                }
                if( $GLOBALS['userdata'][0]['vil_loyal_pts'] >= 275 ){
                    $baseRepel += 15;
                }
            }

            // Check the repel effect
            $rand = random_int(1, 100);
            if( $rand <= $baseRepel && !$forceAttack ){
                $pass = false;
            }

            // Check if pass
            if ($pass == true && (!isset($this->startedBattle) || $this->startedBattle == false) ) 
            {
                $users = array();
                $users[] = array('id'=>$_SESSION['uid'], 'team_or_extra_data'=>$GLOBALS['userdata'][0]['village']);

                $ai = array();

                foreach($opponent as $opp)
                    $ai[] = array('id'=>$opp['id'], 'team'=>false);

                BattleStarter::startBattle( $users, $ai, BattleStarter::travel, false, false, true);

                //$this->menuMessage[] = array(
                //    "text" => 'You have been attacked by: ' . $opponent[0]['name'] . '. Your position: '.$region,
                //    "buttons" => array("?id=113","To battle!"),
                //    "hideMSG" => true,
                //    "popUp" => true,
                //    "dismiss" => 'yes',
                //    "color" => 'red'
                //);
                //addTempNotification($text, $buttons = 'none', $select = 'none', $popup = 'no', $hide = 'no', $color = 'blue')

                $GLOBALS['NOTIFICATIONS']->addTempNotification( array(  'text' => 'You have been attacked by: ' . $opponent[0]['name'] . '. Your position: '.$region,
                                                                        'buttons' => array('?id=113','go','no'),
                                                                        'popup' => 'yes',
                                                                        'color' =>'red')
                                                                     );               

                $this->startedBattle = true;

                return true;
            }
        }
        return false;
    }

}