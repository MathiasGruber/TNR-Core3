<?php
 /* ============== LICENSE INFO START ==============
  * 2005 - 2016 Studie-Tech ApS, All Rights Reserved
  *
  * This file is part of the project www.TheNinja-RPG.com.
  * Dissemination of this information or reproduction of this material
  * is strictly forbidden unless prior written permission is obtained
  * from Studie-Tech ApS.
  * ============== LICENSE INFO END ============== */


// This system is part of the resource gathering part of the job system
require_once(Data::$absSvrPath.'/libs/professionSystem/professionLib.php');
require_once(Data::$absSvrPath.'/libs/professionSystem/OccupationData.php');


class scout extends professionLib {

    // Show the people in the area
    function __construct() {

        // Try phrase
        try{

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Get the user
            $this->setupProfessionData();

            // Check if action is set
            if( !isset($_GET['act']) ){

                // Fetch the user
                $this->fetch_user();

                // Get all the people in the area
                $this->show_people();

                // Get all the resources in the area
                if(
                    isset( $this->user[0]['profession'] ) &&
                    $this->user[0]['profession'] > 0 &&
                    (
                        $this->user[0]['name'] == "Hunter" ||
                        $this->user[0]['name'] == "Miner" ||
                        $this->user[0]['name'] == "Herbalist"
                    )
                ){
                    $this->show_resources();
                }

                // Load the tempalte
                $this->load_template();

            }
            elseif( $_GET['act'] == "gather" ){

                // Start a transaction
                $GLOBALS['database']->transaction_start();

                // Fetch user data, lock table
                $this->fetch_user( true );

                // Check resources at this location
                $this->gatherResource();

            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        }
        catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Scouting System', 'id='.$_GET['id'],'Return');
        }
    }

    // Show people in this area
    private function show_people() {

        $min =  tableParser::get_page_min();
        $currentTime = $GLOBALS['user']->load_time;
        $query = '
            SELECT
                `users`.`id`, `users`.`status`,
                `users`.`longitude`, `users`.`latitude`, `users`.`username`,
                `users_statistics`.`rank`, `users_statistics`.`rank_id`,
                `users_loyalty`.`village`,
                `users_timer`.`last_activity`
            FROM `users_timer`
                INNER JOIN `users` ON (`users`.`id` = `users_timer`.`userid`)
                LEFT JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                LEFT JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
            WHERE ';

        $alliance_status = 0;
        if(isset($GLOBALS['userdata'][0]['alliance'][0][ucfirst($GLOBALS['current_tile'][3])]))
            $alliance_status = $GLOBALS['userdata'][0]['alliance'][0][ucfirst($GLOBALS['current_tile'][3])];

        //at war(3*3)
        if($alliance_status == 2)
        {
            $query .= "`users_timer`.`last_activity` >= ".($currentTime - (60 * 1))." AND ABS(`latitude` - {$GLOBALS['userdata'][0]['latitude']}) <= 1 && ABS(`longitude` - {$GLOBALS['userdata'][0]['longitude']}) <= 1 ";
        }

        //neutral
        else if($alliance_status == 0)
        {
            //get war regions
            $war_regions = [];
            foreach($GLOBALS['map_region_data'] as $region)
                if( isset($GLOBALS['userdata'][0]['alliance'][0][ucfirst($region['owner'])]) && $GLOBALS['userdata'][0]['alliance'][0][ucfirst($region['owner'])] == 2 )
                    $war_regions[] = "'".str_replace("'","\'",$region['region'])."'";

            $war_regions = implode(',',$war_regions);

            if($war_regions == '')
                $war_regions = "'n/a'";

            $query .= " (
                            (
                                (`region` in ($war_regions))
                                AND
                                ABS(`latitude` - {$GLOBALS['userdata'][0]['latitude']}) <= 1
                                AND 
                                ABS(`longitude` - {$GLOBALS['userdata'][0]['longitude']}) <= 1
                                AND
                                `users_timer`.`last_activity` >= ".($currentTime - (60 * 1))."
                            )
                            OR 
                            ( 
                                !(`region` in ($war_regions))
                                AND
                                ABS(`latitude` - {$GLOBALS['userdata'][0]['latitude']}) <= 2 
                                AND 
                                ABS(`longitude` - {$GLOBALS['userdata'][0]['longitude']}) <= 2 
                                AND
                                `users_timer`.`last_activity` >= ".($currentTime - (60 * 5))."
                            ) 
                        ) 
                        AND 
                        ( 
                            `location` = '".str_replace("'","\'",$GLOBALS['userdata'][0]['location'])."' 
                            OR
                            !(`location` in ('Konoki','Samui','Shroud','Silence','Shine','Gambler\'s Den','Bandit\'s Outpost','Poacher\'s Camp','Pirate\'s Hideout')) 
                        )";
        }

        //allies
        else if($alliance_status == 1 && !in_array($GLOBALS['userdata'][0]['location'],array('Konoki','Samui','Shroud','Silence','Shine','Gambler\'s Den','Bandit\'s Outpost','Poacher\'s Camp','Pirate\'s Hideout')))
        {
            //get war regions and neutral regions
            $war_regions = [];
            $neutral_regions = [];
            foreach($GLOBALS['map_region_data'] as $region)
                if( isset($GLOBALS['userdata'][0]['alliance'][0][ucfirst($region['owner'])]) && $GLOBALS['userdata'][0]['alliance'][0][ucfirst($region['owner'])] == 2 )
                    $war_regions[] = "'".str_replace("'","\'",$region['region'])."'";
                else if(isset($GLOBALS['userdata'][0]['alliance'][0][ucfirst($region['owner'])]) && $GLOBALS['userdata'][0]['alliance'][0][ucfirst($region['owner'])] == 0 )
                    $neutral_regions[] = "'".str_replace("'","\'",$region['region'])."'";

            $war_regions = implode(',',$war_regions);
            $neutral_regions = implode(',',$neutral_regions);

            if($war_regions == '')
                $war_regions = "'n/a'";

            if($neutral_regions == '')
                $neutral_regions = "'n/a'";
            
            $query .= " (
                            (
                                (`region` in ($war_regions))
                                AND
                                ABS(`latitude` - {$GLOBALS['userdata'][0]['latitude']}) <= 1
                                AND 
                                ABS(`longitude` - {$GLOBALS['userdata'][0]['longitude']}) <= 1
                                AND 
                                ( 
                                    `location` = '".str_replace("'","\'",$GLOBALS['userdata'][0]['location'])."' 
                                    OR
                                    !(`location` in ('Konoki','Samui','Shroud','Silence','Shine','Gambler\'s Den','Bandit\'s Outpost','Poacher\'s Camp','Pirate\'s Hideout')) 
                                )
                                AND
                                `users_timer`.`last_activity` >= ".($currentTime - (60 * 1))."
                            )
                            OR 
                            ( 
                                (`region` in ($neutral_regions))
                                AND
                                ABS(`latitude` - {$GLOBALS['userdata'][0]['latitude']}) <= 2 
                                AND 
                                ABS(`longitude` - {$GLOBALS['userdata'][0]['longitude']}) <= 2 
                                AND 
                                ( 
                                    `location` = '".str_replace("'","\'",$GLOBALS['userdata'][0]['location'])."' 
                                    OR
                                    !(`location` in ('Konoki','Samui','Shroud','Silence','Shine','Gambler\'s Den','Bandit\'s Outpost','Poacher\'s Camp','Pirate\'s Hideout')) 
                                )
                                AND
                                `users_timer`.`last_activity` >= ".($currentTime - (60 * 5))."
                            )
                            OR 
                            ( 
                                !(`region` in ($neutral_regions))
                                AND
                                !(`region` in ($war_regions))
                                AND
                                ABS(`latitude` - {$GLOBALS['userdata'][0]['latitude']}) <= 4
                                AND 
                                ABS(`longitude` - {$GLOBALS['userdata'][0]['longitude']}) <= 4
                                AND
                                `users_timer`.`last_activity` >= ".($currentTime - (60 * 10))."
                            )
                        )";
        }
        else if($alliance_status == 1 && in_array($GLOBALS['userdata'][0]['location'],array('Konoki','Samui','Shroud','Silence','Shine','Gambler\'s Den','Bandit\'s Outpost','Poacher\'s Camp','Pirate\'s Hideout')))
        {
            $query .= "`users_timer`.`last_activity` >= ".($currentTime - (60 * 15))." AND ABS(`latitude` - {$GLOBALS['userdata'][0]['latitude']}) <= 6 && ABS(`longitude` - {$GLOBALS['userdata'][0]['longitude']}) <= 6 ";
        }
        else
            throw new exception('bad alliance status: '.$alliance_status);

        $query .= ' ORDER BY (`users_statistics`.`dr` * ((`users_statistics`.`max_health`) / `users_statistics`.`sr`) ) DESC, `users_statistics`.`experience` DESC
            LIMIT '.$min.', 10';

        $users = $GLOBALS['database']->fetch_data($query);
        $showUsers = array();
        if( $users !== "0 rows" ){
            for( $i=0; $i < count($users); $i++ ){
                if( $currentTime - $users[$i]['last_activity'] >= 0 ){
                    $users[$i]["username"] = "<a href='?id=13&page=profile&name=".$users[$i]["username"]."'>".$users[$i]["username"]."</a>";
                    $users[$i]["location"] = $users[$i]["longitude"].".".$users[$i]["latitude"];
                    $showUsers[] = $users[$i];
                }
            }
        }

        // Show the table of users
        tableParser::show_list(
            'users',
            'Nearby Users',
            $showUsers,
            array(
                'username' => "Name",
                'rank' => "Rank",
                'village' => "Village",
                'last_activity' => "Last Activity",
                'status' => "Status",
                'location' => "Location"
            ),
            false,
            false, // Send directly to contentLoad
            true,   // Show previous/next links
            false,  // No links at the top to show
            false,   // Allow sorting on columns
            false,   // pretty-hide options
            false, // Top stuff
            array('message'=>"These are the users in your area. If you are allied with the owner of the current territory, you will be able to see everyone in the territory.",'hidden'=>'yes') // Top information

        );
    }

    // Show resources in the vicinity
    private function show_resources(){

        // Get library
        require_once(Data::$absSvrPath.'/libs/professionSystem/resourceLib.php');
        $resourceLib = new resourceLib();

        // Create an array with all locations withing 5 squares of the user
        $locArray = array();
        $x = $GLOBALS['userdata'][0]['longitude'];
        $y = $GLOBALS['userdata'][0]['latitude'];
        $d = 5;
        for( $i = $x-$d ; $i <= $x + $d ; $i++ ){
            for( $j = $y-$d ; $j <= $y + $d ; $j++ ){
                $locArray[] = $i.".".$j;
            }
        }

        // Get resources
        $resources = $resourceLib->checkIfResourceExist( $locArray );

        // Only show the resources we want
        $showResources = array();
        if( $resources !== false ){
            foreach( $resources as $resource ){

                // Variables
                $link = ($resource['x.y'] !== $x.".".$y) ? "Too Far Away" : "<a href='?id=".$_GET['id']."&act=gather'>Get It!</a>";
                $info = "";
                $show = false;

                // Set the data
                $data = unserialize(base64_decode($resource['data']));

                foreach( $data as $entry ){

                    if( $this->canUserPickResource($this->user[0], $entry) ){

                        // Add to entry
                        $show = true;

                        // Add to info
                        $info .= ($info == "") ? "<b>".$entry['subType']."</b>" : ", <b>".$entry['subType']."</b>";
                    }
                }

                // Add to show entry if something was found
                if( $show == true ){
                    $showResources[] = array(
                        "name" => $this->profLocation,
                        "location" => $resource['x.y'],
                        "link" => $link,
                        "info" => $info
                    );
                }
            }
        }
        else{
            $showResources = "0 rows";
        }

        // Show the table of users
        tableParser::show_list(
            'resources',
            'Nearby Resources',
            $showResources,
            array(
                'name' => "Name",
                'location' => "Location",
                'info' => "Further Info",
                'link' => "Action"
            ),
            false,
            false, // Send directly to contentLoad
            false,   // Show previous/next links
            false,  // No links at the top to show
            false,   // Allow sorting on columns
            false,   // pretty-hide options
            false, // Top stuff
            "Current position: <b>".$x.".".$y."</b>. ".$this->user[0]['name']." Profession: <b>".$this->user[0]['profession_exp']." experience</b>" // Top information
        );

    }

    // Check if user can pick up a certain resource
    private function canUserPickResource( $user, $resource ){

        // Check experience vs. required level
        if( $user['profession_exp']+1 >= $resource['level'] || $resource['level'] <= 1){

            // Add locations for profession
            if( $user['name'] == "Hunter" && $resource['type'] == "hunter" && ($user['profession_exp'] >= $resource['level'] || $resource['level'] <= 1)){
                $this->action = "hunting";
                $this->profLocation = "Hunting Ground";
                return true;
            }
            elseif( $user['name'] == "Miner" && $resource['type'] == "ore" ){
                $this->action = "mining";
                $this->profLocation = "Ore Deposit";
                return true;
            }
            elseif( $user['name'] == "Herbalist" && $resource['type'] == "herb" ){
                $this->action = "harvesting";
                $this->profLocation = "Herb Location";
                return true;
            }
        }
        return false;
    }

    // Check to see the resource
    private function gatherResource(){

        // Get library
        require_once(Data::$absSvrPath.'/libs/professionSystem/resourceLib.php');
        $resourceLib = new resourceLib();

        // Set easy variables
        $x = $GLOBALS['userdata'][0]['longitude'];
        $y = $GLOBALS['userdata'][0]['latitude'];

        // Get resources at this spot
        $resource = $resourceLib->checkIfResourceExist( array($x.".".$y) );
        if( $resource !== false ){

            // Digest the resource field
            $data = unserialize(base64_decode($resource[0]['data']));
            if( !empty($data) ){

                foreach( $data as $key => $entry ){

                    // Check if user can pickup this specific resource
                    if( $this->canUserPickResource($this->user[0], $entry) ){

                        // Check to see if the user is already gathering
                        $currentHarvest = cachefunctions::getHarvest($_SESSION['uid']);
                        if( !$currentHarvest ){

                            // Set harvest time
                            $harvestTime = 150 - floor($this->user[0]['profession_exp'] / 150)*30;
                            if( $harvestTime < 60 ){$harvestTime = 60;}

                            $OccupationData = new OccupationData();

                            // Extra harvest time for bounty hunters
                            if($OccupationData->hasSpecialOccupation())
                            {
                                $special_occupation = $OccupationData->getSpecialOccupation();


                                if($special_occupation['id'] == 2 || $special_occupation['id'] == 3)
                                {
                                    $this->hunterLevel = ceil($special_occupation['bountyHunter_exp'] / 1000);

                                    if($this->hunterLevel >= 400)
                                        $harvestTime -= 30;
                                    else if($this->hunterLevel >= 300)
                                        $harvestTime -= 25;
                                    else if($this->hunterLevel >= 200)
                                        $harvestTime -= 20;
                                    else if($this->hunterLevel >= 100)
                                        $harvestTime -= 15;
                                    else if($this->hunterLevel >= 0)
                                        $harvestTime -= 10;

                                }
                            }

                            // Check for global event modifications
                            if( $event = functions::getGlobalEvent("ModifyHarvestTime")){
                                if( isset( $event['data']) && is_numeric( $event['data']) ){
                                    $harvestTime *= round($event['data'] / 100,2);
                                    $harvestTime = floor($harvestTime);
                                }
                            }

                            // Start harvesting
                            $currentHarvest = cachefunctions::startHarvest($_SESSION['uid'], $x, $y, $GLOBALS['user']->load_time+$harvestTime );

                        }

                        // Check timer
                        $timeLeft = $currentHarvest['time'] - $GLOBALS['user']->load_time;
                        if( $timeLeft <= 0 ){

                            // Set the gains
                            $this->setHarvestGains($this->user[0], $entry);

                            // Check for global event modifications
                            if( $event = functions::getGlobalEvent("ModifyHarvestQuantity")){
                                if( isset( $event['data']) && is_numeric( $event['data']) ){
                                    $this->amount *= round($event['data'] / 100,2);
                                    $this->amount = floor($this->amount);
                                }
                            }

                            // Add the resource to the user inventory (depending on whether a process or material)
                            $this->itemLib->addItemToUser( $_SESSION['uid'] , $this->iid , $this->amount, 0, 'harvest' );

                            // A variable for storing extra information to be shown to the user
                            $extraInfo = "";

                            // Add special item as well if it's there
                            if( $this->specialItem > 0 ){

                                // Check for global event modifications
                                if( $event = functions::getGlobalEvent("ModifyHarvestQuantity")){
                                    if( isset( $event['data']) && is_numeric( $event['data']) ){
                                        $this->specialAmount *= round($event['data'] / 100,2);
                                        $this->specialAmount = floor($this->amount);
                                    }
                                }

                                $this->itemLib->addItemToUser( $_SESSION['uid'] , $this->specialItem , $this->specialAmount, 0, 'harvest' );
                                $extraInfo .= " You have also received ".$this->specialAmount." ".$this->specialName.".";
                            }
                            elseif($this->specialItem == -86 || $this->specialItem == -87 || $this->specialItem == -88 || $this->specialItem == -89)
                            {
                                $Aid = $this->specialItem * -1;
                                $Bid = 720;

                                $names = explode("*",$this->specialName);
                                $Aname = $names[0];
                                $Bname = $names[1];

                                $Aamount = ((int)$this->specialAmount);
                                $Bamount = ($this->specialAmount - ((int)$this->specialAmount)) * 10;

                                // Check for global event modifications
                                if( $event = functions::getGlobalEvent("ModifyHarvestQuantity")){
                                    if( isset( $event['data']) && is_numeric( $event['data']) ){
                                        $Aamount *= round($event['data'] / 100,2);
                                        $Aamount = floor($this->amount);
                                        $Bamount *= round($event['data'] / 100,2);
                                        $Bamount = floor($this->amount);
                                    }
                                }

                                $this->itemLib->addItemToUser( $_SESSION['uid'] , $Aid , $Aamount, 0, 'harvest' );
                                $extraInfo .= " You have also received ".$Aamount." ".$Aname.".";

                                $this->itemLib->addItemToUser( $_SESSION['uid'] , $Bid , $Bamount, 0, 'harvest' );
                                $extraInfo .= " You have also received ".$Bamount." ".$Bname.".";

                            }


                            // Update the experience to the user
                            if( isset($this->experience) && $this->experience > 0 ){

                                // Cap profession experience
                                $cap = 150;
                                switch( $GLOBALS['userdata'][0]['rank_id'] ){
                                    case 4: $cap = 300; break;
                                    case 5: $cap = 450; break;
                                }
                                if( $this->user[0]['profession_exp'] + $this->experience > $cap ){
                                    $GLOBALS['Events']->acceptEvent('profession_exp', array('new'=>$cap, 'old'=> $this->user[0]['profession_exp'] ));
                                    $this->user[0]['profession_exp'] = $cap;
                                    $extraInfo .= " You have capped the experience in your profession.";
                                }
                                else{
                                    $GLOBALS['Events']->acceptEvent('profession_exp', array('new'=>$this->user[0]['profession_exp'] + $this->experience, 'old'=> $this->user[0]['profession_exp'] ));
                                    $this->user[0]['profession_exp'] += $this->experience;
                                    $extraInfo .= " You have gained ".$this->experience." experience in your profession.";
                                }

                                // Update
                                $this->set_occupation_data(array("profession_exp" => $this->user[0]['profession_exp'] ) );
                            }

                            // Tell the resource lib that we picked up this resource & modify the resource map
                            $resourceLib->pickUpResource( $x , $y , $data, $key );

                            // Stop the harvesting on memcache
                            cachefunctions::endHarvest( $_SESSION['uid'] );

                            // Claim resource message
                            $GLOBALS['page']->Message( 'You have been '.$this->action.', and now '.$this->amount.' '.$this->name.' has now been added to your inventory.'.$extraInfo, 'Scouting System', 'id='.$_GET['id']);
                        }
                        else{

                            // Inform user
                            $GLOBALS['page']->Message( 'You are currently '.$this->action.': '.$entry['subType'].'. You will be finished in: '.functions::convert_time($timeLeft, 'harvestTime') . '
                                                    <br>Be careful, others may be '.$this->action.' here as well, and only the first one to claim the resource on this page once the timer expires will get it. Also note that if you do not return to this page relatively soon after the timer has expired, you will forfeit the resource and will have to start over.'
                                                    , 'Scouting System', 'id='.$_GET['id']);
                        }

                        // Commit any transactions now to release lock
                        $GLOBALS['database']->transaction_commit();

                        // Return for this function, i.e. end it
                        return true;
                    }
                }
            }

            // If here, someone already took the resource
            throw new Exception('There is no longer any resources here for you, maybe someone got it before you.');
        }
        else{
            throw new Exception('No resources could be found at this location');
        }
    }

    // Function for setting gains when picking harvest
    private function setHarvestGains( $user, $resource ){

        // Initial values
        $this->experience = 0;
        $this->amount = 0;
        $this->iid = 0;
        $this->name = "";

        // Some stuff give special items
        $this->specialItem = 0;
        $this->specialAmount = 0;
        $this->specialName = "";

        // Convenience
        $xp = $user['profession_exp'];

        // Add locations for profession
        if( $user['name'] == "Hunter" && $resource['type'] == "hunter" ){

            // Set item
            $this->iid = $resource['iid'];


            if(isset($resource['specialItem']) && isset($resource['specialItemName']))
            {
                $this->specialItem = $resource['specialItem'];
                $this->specialName = $resource['specialItemName'];
            }
            //setting carcass level based on flock status and resourse level and hunter level
            //if the hunters exp is 385 or greater and the resource is greater than 3 or if the resource is a flock set the carcass level accordingly
            else if($xp >= 385 && ($resource['level'] == 385 || $resource['level'] == 0))
            {
                $this->specialItem = 89;
                $this->specialName = "Lvl 4 Carcass";
            }

            //if the hunters exp is 235 or greater and the resource is grater than level 2 or if the resource is a flock set the carcass level accordingly
            else if($xp >= 235 && ($resource['level'] == 235 || $resource['level'] == 0))
            {
                $this->specialItem = 88;
                $this->specialName = "Lvl 3 Carcass";
            }

            //if the hunters exp is 85 or greater and the resource is grater than level 1 or if the resource is a flock set the carcass level accordingly
            else if($xp >= 85 && ($resource['level'] == 85 || $resource['level'] == 0))
            {
                $this->specialItem = 87;
                $this->specialName = "Lvl 2 Carcass";
            }

            //if the hunters exp is 0 or greater and the resource is grater than level 0 or if the resource is a flock set the carcass level accordingly
            else if($xp >= 0 && ($resource['level'] == 1 || $resource['level'] == 0))
            {
                $this->specialItem = 86;
                $this->specialName = "Lvl 1 Carcass";
            }


            //finding the bonus that should be added based on experiance
            if($xp <= 100)      { $bonus_quantity = 0; }
            else if($xp <= 200) { $bonus_quantity = 1; }
            else if($xp <= 400) { $bonus_quantity = 2; }
            else if($xp > 400)  { $bonus_quantity = 3; }



            //if this is not a flock
            if($resource['flock'] == false)
            {
                //setting base quantities and adding in bonus
                $min = 1;
                $max = 3 + $bonus_quantity;
            }

            //if this is a flock
            else
            {
                //setting min and max to 2 plus hunter exp bonus plus user rank
                $min = 2 + $bonus_quantity + $GLOBALS['userdata'][0]['rank_id'];
                $max = $min;

                if(random_int(0,1) == 1)
                {
                    if($this->specialItem==86){$this->specialItem=-86;}
                    else if($this->specialItem==87){$this->specialItem=-87;}
                    else if($this->specialItem==88){$this->specialItem=-88;}
                    else if($this->specialItem==89){$this->specialItem=-89;}

                    $this->specialName .= "*pristine feather";

                    $this->specialAmount += random_int(1,3)/10;
                }
            }


            //generate final quantaties.
            $this->amount = random_int($min,$max);

            if(!isset($resource['itemName']))
                $this->name = "raw skin";
            else
                $this->name = $resource['itemName'];

            $this->specialAmount += random_int($min,$max);

            if(isset($resource['itemAmountOverrideMax']) && isset($resource['itemAmountOverrideMin']) && isset($resource['specialItemAmountOverrideMax']) && isset($resource['specialItemAmountOverrideMin']))
            {
                $this->amount = random_int($resource['itemAmountOverrideMin'],$resource['itemAmountOverrideMax']);
                $this->specialAmount = random_int($resource['specialItemAmountOverrideMin'],$resource['specialItemAmountOverrideMax']);
            }

            // Set experince
            $this->experience = 0.15;
            if( ($this->amount + $this->specialAmount) >= $max ){
                $this->experience += 1;
            }
        }
        elseif( $user['name'] == "Miner" && $resource['type'] == "ore" ){

            // Set item
            $this->iid = $resource['iid'];

            // Set amount
            $this->amount = random_int($resource['dropMin'],$resource['dropMax']);
            $this->name = $resource['subType'] . " ore";

            // Other stuff to be found in the ores
            $specials = array(
                "copper"    => array( "iid" => 90, "name" => "coal", "min" => 1, "max" => 4, "chance" => 65 ),
                "tin"       => array( "iid" => 91, "name" => "small gem", "min" => 1, "max" => 2, "chance" => 45 ),
                "iron"      => array( "iid" => 92, "name" => "silver", "min" => 1, "max" => 3, "chance" => 45 ),
                "titanium"  => array( "iid" => 93, "name" => "gold", "min" => 1, "max" => 2, "chance" => 30 ),
                "radiant"   => array( "iid" => 94, "name" => "rare gem", "min" => 1, "max" => 3, "chance" => 7.5 )
            );

            // Set special mining
            if( isset( $specials[ $resource['subType'] ] ) ){
                $special = $specials[ $resource['subType'] ];
                if( random_int(1,100) < $special['chance'] ){
                    $this->specialItem = $special['iid'];
                    $this->specialAmount = random_int($special['min'],$special['max']);
                    $this->specialName = $special['name'];
                }
            }

            // Set experience
            $this->experience = 0.15;
            if( $this->amount == $resource['dropMax'] ){

                // Experience is based on level & resource
                if(
                    ( $resource['subType'] == "copper" && $this->user[0]['profession_exp'] < 100 ) ||
                    ( $resource['subType'] == "tin" && $this->user[0]['profession_exp'] < 150 ) ||
                    ( $resource['subType'] == "iron" && $this->user[0]['profession_exp'] < 350 ) ||
                    ( $resource['subType'] == "titanium" && $this->user[0]['profession_exp'] < 425 ) ||
                    ( $resource['subType'] == "radiant" && $this->user[0]['profession_exp'] < 500 )
                ){
                    $this->experience += 1;
                }
            }
        }
        elseif( $user['name'] == "Herbalist" && $resource['type'] == "herb" ){

            // Set item
            $this->iid = $resource['iid'];
            $this->name = $resource['subType'];

            // Set amount
            $this->amount = random_int(1,3);

            // Set experience
            $this->experience = 0.25;
            if( $this->amount == 3 ){
                $this->experience += 1;
            }
        }

        // Add to gain if silver/gold supporter
        switch( $GLOBALS['userdata'][0]['federal_level'] ){
            case "Silver":
                $this->experience *= 1.25;
            break;
            case "Gold":
                $this->experience *= 1.5;
            break;
        }

    }

    // Load the template
    private function load_template(){
        $GLOBALS['template']->assign('contentLoad', './templates/content/scout/mainView.tpl');
    }


}

new scout();

?>