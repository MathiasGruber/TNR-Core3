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
require_once(Data::$absSvrPath.'/libs/elements/Elements.php');

class village {

    //  Set smarty data differentiating between villages & syndicate
    protected function setSmartyDefinitions(){
        $GLOBALS['template']->assign('cpName', $this->cpName );
        $GLOBALS['template']->assign('leaderName', $this->leaderName );
    }

    //  Add additional user data to the global user array
    protected function addUserData(){

        // Get additional user data
        if(!($user_data = $GLOBALS['database']->fetch_data('SELECT `users`.`id`, `users`.`bloodline`,
            `users_preferences`.`anbu`, `villages`.*
            FROM `users`
                INNER JOIN `users_preferences` ON (`users_preferences`.`uid` = `users`.`id`)
                INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                INNER JOIN `villages` ON (`villages`.`name` = `users_loyalty`.`village`)
            WHERE `users`.`id` = '.$_SESSION['uid'].' LIMIT 1'))
        ) {
            throw new Exception('Error retrieving data from database');
        }

        // Add user info to globals array
        foreach( $user_data[0] as $key => $value ){
            $GLOBALS['userdata'][0][ $key ] = $value;
        }
    }

    //  Show the main screen overview
    protected function main_screen( $menu ) {

        $GLOBALS['template']->assign('subHeader', ucfirst($this->cpName) . ' Hall');
        $GLOBALS['template']->assign('nCols', 3);
        $GLOBALS['template']->assign('nRows', ceil(count($menu)/3) );
        $GLOBALS['template']->assign('subTitle_info', "This is where you can see the status of the ".$this->cpName.", information on members and loyalty bonuses, and if you are strong enough, challenge the leader of the ".$this->cpName." for his or her position");
        $GLOBALS['template']->assign('linkMenu', $menu);
        $GLOBALS['template']->assign('buttonLayout', "SUBMIT");
        $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');
    }

    // Show latest global messages
    protected function blueMessages(){

        $min =  tableParser::get_page_min();
        $number = tableParser::set_items_showed( 10 );

        $chars = $GLOBALS['database']->fetch_data('SELECT * FROM `blueMessages` ORDER BY `blueMessages`.`time` DESC LIMIT '.$min.', '.$number);
        tableParser::show_list(
             'log',
             'Global Messages', $chars,
                array(
            'time' => "Time",
            'message' => "Message"
                ),
            false,
           true,
           true
        );

        // Return Link
        $GLOBALS['template']->assign("returnLink", true);
    }

    // show artisans in this village
    protected function artisansList()
    {
        //get artisan data
        /*$artisan_list = $GLOBALS['database']->fetch_data(
            "SELECT `users`.`username`, cast(`users_occupations`.`profession_exp` as int) as 'profession_exp', `occupations`.`name`, `users_statistics`.`rank`,
                        cast(((((".(time()+100)." - `users_timer`.`last_login`)/60)/60)/24) as int) as 'days_since_login'
                    FROM `users_occupations`
                    INNER JOIN `users` ON (`users`.`id` = `users_occupations`.`userid`)
                    INNER JOIN `occupations` ON (`occupations`.`id` = `users_occupations`.`profession`)
                    INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users_occupations`.`userid`)
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users_occupations`.`userid`)
                    WHERE `users_occupations`.profession != 0 and `users`.`village` = '".$GLOBALS['userdata'][0]['village']."' and (`users_timer`.`last_login` + 1728000) > ".(time()+1)."
                    ORDER BY `users_occupations`.`profession_exp` DESC,
		                `days_since_login` ASC,
                        `occupations`.`name`,
                        `users_statistics`.`rank`,
                        `users`.`username`");*/

        //temp replacement for above query until mysql gets updated.
        $artisan_list = $GLOBALS['database']->fetch_data(
            "SELECT `users`.`username`, `users_occupations`.`profession_exp`, `occupations`.`name`, `users_statistics`.`rank`,`users_timer`.`last_login`
                    FROM `users_occupations`
                    INNER JOIN `users` ON (`users`.`id` = `users_occupations`.`userid`)
                    INNER JOIN `occupations` ON (`occupations`.`id` = `users_occupations`.`profession`)
                    INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users_occupations`.`userid`)
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users_occupations`.`userid`)
                    WHERE `users_occupations`.profession != 0 and `users`.`village` = '".$GLOBALS['userdata'][0]['village']."' and (`users_timer`.`last_login` + 1728000) > ".(time()+1)." and `users_occupations`.`profession_exp` > 75
                    ORDER BY `users_occupations`.`profession_exp` DESC,
		                `last_login` DESC,
                        `occupations`.`name`,
                        `users_statistics`.`rank`,
                        `users`.`username`");

        foreach($artisan_list as $key => $item)
        {
            $artisan_list[$key]['days_since_login'] = (int)(((((time()+100) - $item['last_login'])/60)/60)/24);
        }


        //set artisan data
        $GLOBALS['template']->assign('artisan_list', $artisan_list);
        //set template
        $GLOBALS['template']->assign('contentLoad', './templates/content/town_hall/artisanList.tpl');
    }

    //  Show users in this faction
    protected function users() {

        $min =  tableParser::get_page_min();
        $number = tableParser::set_items_showed( 20 );
        $order =  tableParser::get_page_order( array("experience", "pvp_experience", "rank") );

        // If ordering on rank, order on rankID instead
        if( stristr($order,"rank") ){
            $order = str_replace("rank","rank_id", $order);
        }

        // Default ordering
        if( empty($order) ){
            $order = "ORDER BY `users_statistics`.`rank_id` DESC, `users_statistics`.`experience` DESC";
        }

        // Get users
        $users = $GLOBALS['database']->fetch_data('SELECT `users`.`id`, `users`.`username`,
            `users_statistics`.`rank`, `users_statistics`.`rank_id`, `users_statistics`.`experience`, `users_statistics`.`pvp_experience`
            FROM `users_loyalty`
                INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users_loyalty`.`uid`)
                INNER JOIN `users` ON (`users`.`id` = `users_loyalty`.`uid`)
            WHERE `users_loyalty`.`village` = "'.$GLOBALS['userdata'][0]['name'].'"
            '.$order.' LIMIT '.$min.', '.$number);

        // Show the table of users
        tableParser::show_list(
            'users',
            'List of Users',
            $users,
            array(
                'username' => "Name",
                'rank' => "Rank",
                'experience' => "Experience",
                'pvp_experience' => "Pvp Experience"
            ),
            array(
                array("name" => "Profile", "id" => "13", "page" => "profile", "profile" => "table.username")
            ),
            true, // Send directly to contentLoad
            true, // Show newer/older links
            false, // No top options links
            true //  sorting on columns
        );

        // Return Link
        $GLOBALS['template']->assign("returnLink", true);
    }

    protected function userSupply($type)
    {
        $log = $GLOBALS['database']->fetch_data("
            SELECT `users`.`username`, `users_actionLog`.`additional_info`
            FROM `users_actionLog`
            INNER JOIN `users`
	            ON (`users`.`id` = `users_actionLog`.`uid`)
            WHERE `users_actionLog`.`action` = 'villageSupply'
                AND `users_actionLog`.`attached_info` = '".$type."'
                AND `users`.`village` = '".$GLOBALS['userdata'][0]['village']."'
                AND (`users_actionLog`.`time` != 0)
            ORDER BY `additional_info` DESC, `username` ASC");


        $supplyList;
        if(is_array($log))
        {
            foreach($log as $entry)
            {
                $empty = false;
                if(!isset($supplyList[$entry['username']]))
                    $supplyList[$entry['username']] = $entry['additional_info'];
                else
                    $supplyList[$entry['username']] += $entry['additional_info'];
            }
        }
        else
        {
            $supplyList = 0;
        }

        $GLOBALS['template']->assign("type", $type);
        $GLOBALS['template']->assign("supplyList", $supplyList);
        $GLOBALS['template']->assign('contentLoad', './templates/content/town_hall/userSupply.tpl');
    }

    //  Show faction status
    protected function status() {

        // Get the count of various user ranks
        $totalCount = cachefunctions::getVillageTotalMemberCount($GLOBALS['userdata'][0]['name'] );
        $activeCount = cachefunctions::getVillageActiveMemberCount($GLOBALS['userdata'][0]['name'] );

        // Get structures
        $villageVars = $GLOBALS['database']->fetch_data('SELECT * FROM `village_structures`
            WHERE `village_structures`.`name` = "'.$GLOBALS['userdata'][0]['village'].'" LIMIT 1');

        // Send to smarty
        foreach( array("ramen","hospital") as $type ){
            $GLOBALS['template']->assign($type.'Supply', $villageVars[0][$type.'_supply'] );
            if( $villageVars[0][$type.'_bonus'] > $GLOBALS['user']->load_time ){
                $GLOBALS['template']->assign($type.'Bonus', functions::convert_time($villageVars[0][$type.'_bonus']-$GLOBALS['user']->load_time, $type."_timer", false) );
            }
            else{
                $GLOBALS['template']->assign($type.'Bonus', "N/A" );
            }
        }

        // Damage bonus
        if( $villageVars[0]['damageIncTimer'] > $GLOBALS['user']->load_time ){
            $wait = "<br>Time left: ".functions::convert_time(($villageVars[0]['damageIncTimer'] - $GLOBALS['user']->load_time), "damageTimer");
            $GLOBALS['template']->assign('DamageBonus', $wait );
        }


        $GLOBALS['template']->assign('villageV', $activeCount[0] );

        // Set smarty stuff
        $GLOBALS['template']->assign('total', $totalCount[0] );
        $GLOBALS['template']->assign('active', $activeCount[0] );
        $GLOBALS['template']->assign('user_data', $GLOBALS['userdata'][0] );
        $GLOBALS['template']->assign('contentLoad', './templates/content/town_hall/factionStatus.tpl');
    }

    //  Show faction leader orders
    protected function orders() {
        $GLOBALS['template']->assign('orderTitle', ucfirst($this->leaderName) . ' Orders' );
        $GLOBALS['template']->assign('orders', functions::parse_BB($GLOBALS['userdata'][0]['orders']));
        $GLOBALS['template']->assign('reportLink', '?id=53&act=kageorders&uname=' . $GLOBALS['userdata'][0]['leader'] );
        $GLOBALS['template']->assign('contentLoad', './templates/content/town_hall/orders.tpl');
    }

    //  Check if the user can challenge the kage
    protected function canChallengeKage(){
        if( $GLOBALS['userdata'][0]['leader'] !== $GLOBALS['userdata'][0]['username'] ){

            if(
                ( $GLOBALS['userdata'][0]['village'] !== "Syndicate" && $GLOBALS['userdata'][0]['vil_loyal_pts'] >= 90 ) ||
                ( $GLOBALS['userdata'][0]['village']  === "Syndicate" && $GLOBALS['userdata'][0]['vil_loyal_pts'] <= -90 )
            ){
                $bingoBook = $GLOBALS['database']->fetch_data('SELECT * FROM `bingo_book`
                    WHERE `bingo_book`.`userID` = '.$_SESSION['uid'].' LIMIT 1');
                if(
                    ( $GLOBALS['userdata'][0]['village'] !== "Syndicate" && $bingoBook[0][ $GLOBALS['userdata'][0]['village'] ] >= 500000 ) ||
                    ( $GLOBALS['userdata'][0]['village']  === "Syndicate" && $bingoBook[0][ $GLOBALS['userdata'][0]['village'] ] <= -500000 )) {
                    $startedWar = $GLOBALS['database']->fetch_data('SELECT * FROM `users_actionLog`
                        WHERE `users_actionLog`.`action` = "startWar" AND `users_actionLog`.`uid` = '.$_SESSION['uid'].'
                            AND `users_actionLog`.`time` > '.($GLOBALS['user']->load_time-10*24*3600).' LIMIT 1');
                    if( $startedWar == "0 rows" ){
                        $previousChallenge = $GLOBALS['database']->fetch_data('SELECT * FROM `users_actionLog`
                        WHERE `users_actionLog`.`action` = "challengeLeader" AND `users_actionLog`.`uid` = '.$_SESSION['uid'].'
                            AND `users_actionLog`.`time` > '.($GLOBALS['user']->load_time-7*24*3600).' LIMIT 1');
                        if( $previousChallenge == "0 rows" ){
                            if ($GLOBALS['userdata'][0]['pvp_experience'] >= $GLOBALS['userdata'][0]['avg_pvp']) {
                                if( $GLOBALS['userdata'][0]['anbu'] == '_none' || $GLOBALS['userdata'][0]['anbu'] == '' || $GLOBALS['userdata'][0]['anbu'] == '_disabled' ){
                                    if( $GLOBALS['userdata'][0]['status'] == "awake" ){
                                        if ($GLOBALS['userdata'][0]['rank_id'] > 3) {
                                            return "yes";
                                        }
                                        else{
                                            return "You must have a higher rank to challenge the ".$this->leaderName;
                                        }
                                    }
                                    else{
                                        return "You must be awake and not in battle to challenge the ".$this->leaderName;
                                    }
                                }
                                else{
                                    return "You cannot challenge the kage as an ANBU squad member.";
                                }
                            }
                            else{
                                return "You do not have enough PVP experience. You need at least ".($GLOBALS['userdata'][0]['avg_pvp'])." to challenge.";
                            }
                        }
                        else{
                            $time = functions::convert_time( $previousChallenge[0]['time']-($GLOBALS['user']->load_time-7*24*3600) , 'kageChallengeCooldown', 'false');
                            return "You can only start a leader challenge every 7 days. You must wait ".$time;
                        }
                    }
                    else{
                        return "You must wait 10 days after having started a war before you can be in the lead again. ";
                    }
                }
                else{
                    return "You need at least 500k respect to challenge the ".$this->leaderName;
                }
            }
            else{
                $required = ($GLOBALS['userdata'][0]['village'] == "Syndicate") ? -90 : 90;
                return "You do not have enough respect points to challenge the kage. At least ".$required." respect points are required and you have ".$GLOBALS['userdata'][0]['vil_loyal_pts'].".";
            }
        }
        else{
            return "You are the ".$this->leaderName;
        }
    }

    //  Check & challenge kage
    protected function check_kage() {

        // Gather Necessary Information
        if(!($kage_data = $GLOBALS['database']->fetch_data('SELECT `users`.`id` AS `user_id`,
            `users_loyalty`.`village`,
            `kage_user`.`id`, `kage_user`.`username`, `kage_user`.`bloodline`,
            `kage_stats`.`pvp_experience`, `kage_stats`.`rank_id`, `kage_stats`.`rank`,
            `villages`.`avg_pvp`, `villages`.`leader`, `villages`.`name`
            FROM `users`
                INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                INNER JOIN `villages` ON (`villages`.`name` = `users_loyalty`.`village`)
                LEFT JOIN `users` AS `kage_user` ON (`kage_user`.`username` = `villages`.`leader`)
                LEFT JOIN `users_statistics` AS `kage_stats` ON (`kage_stats`.`uid` = `kage_user`.`id`)
            WHERE `users`.`id` = '.$_SESSION['uid'].' LIMIT 1'))) {
            throw new Exception('There was an error trying to gather necessary user information!');
        }

        // If there was a Village AI kage determined
        if( Data::$VILLAGE_KAGENAMES[ $kage_data[0]['name'] ] === $kage_data[0]['leader']) {
            $kageInfo = array(
                "username" => $kage_data[0]['leader'],
                "avatar" => functions::getVillageAvatar( $kage_data[0]['village'] ),
                "rank" => 'Guardian',
                "bloodline" => "None",
                "challenge" => $this->canChallengeKage(),
                "village" => $kage_data[0]['name']
            );
            $GLOBALS['template']->assign('kageInfo', $kageInfo);
            $GLOBALS['template']->assign('contentLoad', './templates/content/town_hall/check_kage_screen.tpl');
        }
        else {
            // If it saw a User Kage, but the User doesn't exist
            if($kage_data[0]['id'] !== null) {

                // Check PVP Experience
                if($kage_data[0]['pvp_experience'] >= intval($kage_data[0]['avg_pvp'])) {

                    // Assign kage data to smarty variable
                    $kage_data[0]['challenge'] = $this->canChallengeKage();
                    $kage_data[0]['avatar'] = functions::getAvatar( $kage_data[0]['id'] );

                    // Pvpcode and kage info
                    $GLOBALS['template']->assign('pvpCode', md5($GLOBALS['user']->load_time . "-" . $kage_data[0]['id'] . "-" .$GLOBALS['userdata'][0]['longitude'] . "-" .$GLOBALS['userdata'][0]['latitude']) );
                    $GLOBALS['template']->assign('kageInfo', $kage_data[0]);

                    // Show kage template
                    $GLOBALS['template']->assign('contentLoad', './templates/content/town_hall/check_kage_screen.tpl');
                }
                else{

                    // Inactive kage. Remove the person
                    $GLOBALS['database']->transaction_start();
                    $this->hasTransaction = true;

                    // Get the kage
                    if(($GLOBALS['database']->execute_query('SELECT `users`.`username`
                        FROM `users_loyalty`
                            INNER JOIN `users` ON (`users`.`id` = `users_loyalty`.`uid`)
                        WHERE `users_loyalty`.`village` = "'.$kage_data[0]['name'].'" FOR UPDATE')) === false)
                    {
                        throw new Exception('The inactive '.$this->cpName.' could not be found. Something fishy is up.');
                    }

                    // Update the village
                    if(($GLOBALS['database']->execute_query('UPDATE `villages`, `users`, `users_loyalty`, `users_statistics`
                        SET `villages`.`leader` = "'.Data::$VILLAGE_KAGENAMES[ $kage_data[0]['village'] ].'",
                            `users_statistics`.`rank` = "'.functions::getRank($kage_data[0]['rank_id'], $kage_data[0]['village']).'",
                            `users`.`notifications` = '."CONCAT('id:17;duration:none;text:The Village Kage, ".$kage_data[0]['username'].", was fired for being inactive!;dismiss:yes;buttons:none;select:none;//',`notifications`)".'
                        WHERE `villages`.`name` = "'.$kage_data[0]['name'].'"
                            AND `users_loyalty`.`village` = `villages`.`name`
                            AND `users`.`id` = `users_loyalty`.`uid`
                            AND `users_statistics`.`uid` = '.$kage_data[0]['id'])) === false)
                    {
                        throw new Exception('There was an error updating the '.$this->cpName);
                    }

                    $events = new Events($kage_data[0]['username']);
                    $events->acceptEvent('kage', array('data'=>'removed'));
                    $events->closeEvents();

                   // Commit transaction
                    $GLOBALS['database']->transaction_commit();
                    $this->hasTransaction = false;

                    // Show a message for the user
                    $GLOBALS['page']->Message(
                            "The Village ".$this->cpName.", ".$kage_data[0]['username'].", was fired for being inactive, i.e. having too few PVP points.",
                            $this->cpName.' Removal',
                            'id='.$_GET['id']
                    );
                }
            }
            else{

                // Attempt to reset kage
                $GLOBALS['database']->transaction_start();
                $this->hasTransaction = true;

                // Get the village
                if(($GLOBALS['database']->execute_query('SELECT `villages`.`leader`
                    FROM `villages`
                    WHERE `villages`.`name` = "'.$kage_data[0]['name'].'" LIMIT 1 FOR UPDATE')) === false) {
                    throw new Exception('Could not identify a village with this name.');
                }

                // Update the village
                if(($GLOBALS['database']->execute_query('UPDATE `villages`
                    SET `villages`.`leader` = "'.Data::$VILLAGE_KAGENAMES[ $GLOBALS['userdata'][0]['village'] ].'"
                    WHERE `villages`.`name` = "'.$kage_data[0]['name'].'" LIMIT 1')) === false) {
                    throw new Exception('There was an error resetting the '.$this->cpName);
                }

                // Commit transaction
                $GLOBALS['database']->transaction_commit();
                $this->hasTransaction = false;

                // Show a message for the user
                $GLOBALS['page']->Message(
                    "Apparently the ".$this->cpName.", ".$kage_data[0]['leader'].", doesn't exist anymore!",
                    $this->cpName.' Reset',
                    'id='.$_GET['id']
                );
            }
        }
    }

    //  Do kage challenge
    protected function challenge() {

        // Quick checks
        $quickCheck = $this->canChallengeKage();
        if( $quickCheck == "yes"){

            // Check for AI kage
            if( Data::$VILLAGE_KAGENAMES[ $GLOBALS['userdata'][0]['name'] ] == $GLOBALS['userdata'][0]['leader']) {

                // Get AI kage
                $kage_data = $GLOBALS['database']->fetch_data('SELECT * FROM `ai`
                    WHERE `ai`.`name` = "'.$GLOBALS['userdata'][0]['leader'].'" LIMIT 1');
                if ( $kage_data !== "0 rows" ) {

                    /*// Properly set up AI information for battle
                    $kage_data[0] = functions::make_ai( $kage_data[0] );

                    // Check if AI is already in battle
                    $kage_battle = functions::checkIfInBattle( $kage_data[0]['id'] );
                    if( $kage_battle == "0 rows" ){

                        // Update Database
                        functions::insertIntoBattle(
                                array( $GLOBALS['userdata'][0]['id'] ),
                                array( $kage_data[0]['id'] ),
                                "kage",
                                "1",
                                array(),
                                $kage_data
                        );*/

                    //check for battle id in cache
                    if(true){

                        BattleStarter::startBattle( array(array('id'=>$_SESSION['uid'], 'team_or_extra_data'=>'challenger')),
                                                array(array('id'=>$kage_data[0]['id'],'team'=>'kage')),
                                                BattleStarter::kage);

                        // Give the user a message
                        $GLOBALS['page']->Message(
                            "You have challenged the ".$this->leaderName,
                            ucfirst($this->cpName) . ' Challenge',
                            'id=113',
                            "Go to Battle"
                        );

                        // Log the action
                        functions::log_user_action($_SESSION['uid'], "challengeLeader", $GLOBALS['userdata'][0]['village'] );
                    }
                    else{
                        throw new Exception("Kage is already in battle with someone else.");
                    }
                }
                else{
                    throw new Exception("For some reason the AI kage '".$GLOBALS['userdata'][0]['leader'] ."' could not be retrieved from the database.");
                }
            }
            else{
                // Check for PVP. Load battleInitiation library
                require_once(Data::$absSvrPath.'/libs/battleSystem/battleInitiation.php');
                $battleInitiator = new battleInitiation();

                // Set the target & type of battle
                $battleInitiator->setTarget( $GLOBALS['userdata'][0]['leader'] );
                $battleInitiator->setType( "kage" );

                // Instantiate the battle
                $battle = $battleInitiator->initiate_fight();

                // Log the action
                if( $battle ){
                    functions::log_user_action($_SESSION['uid'], "challengeLeader", $GLOBALS['userdata'][0]['village'] );
                }
            }
        }
        else{
            throw new Exception( $quickCheck );
        }
    }

    // Get ranks that a given user can set targets/bounties on
    protected function getTargetRanks( $userRank ){
        switch( $userRank ) {
            case('3'): return '3, 4'; break; // Chuunin User Rank
            case('4'): return '3, 4, 5'; break; // Jounin User Rank
            case('5'): return '4, 5'; break; // Elite Jounin User Rank
            default: throw new Exception('You must be at least a Chuunin to view the Bingo Book!');
        }
    }

    //  Bingo Book functions
    protected function bingo_book() {

        $min =  tableParser::get_page_min();
        $number = tableParser::set_items_showed( 20 );
        $order =  tableParser::get_page_order( array("bounty","username","rank","experience") );

        // Determine Rank Filtering by User's Current Rank
        $rank_sel = $this->getTargetRanks( $GLOBALS['userdata'][0]['rank_id'] );

        // Differences for outlaws vs. villages
        switch( $GLOBALS['userdata'][0]['village'] ){
            case "Syndicate":
                $panelTitle = "Mercenary Work";
                $panelInformation = array('message'=>"As an outlaw you can hunt the people that people put out illegal hits on, i.e. the people that really pissed someone off.",'hidden'=>'yes');
                $panelBountySelector = '(`bingo_book`.`SpecialBounty` * -1) as `bounty`';
                $panelBountyLimiter = '`bingo_book`.`SpecialBounty` < 0';
                $panelOptions = array(
                    array("name" => "View Profile", "id" => "13", "page" => "profile", "profile" => "table.username")
                );
            break;
            default:
                $panelTitle = "Bingo Book";
                $panelInformation = array('message'=>"As a villager, you can search other users in the game and put out either a bounty or a hit out on the user.
                                    <br><br><b>Bounty:</b> A bounty will effectively decrease the respect of a user from another faction within your village. You can only set this for users who don't already have a positive reputation within your village
                                    <br><br><b>Target:</b> You can put out a hit on any villager, and the hit can be claimed only by outlaws.",'hidden'=>'yes');
                $panelBountySelector = '(`bingo_book`.'.$GLOBALS['userdata'][0]['village'].' * -1) as `bounty`';
                $panelBountyLimiter = '`bingo_book`.'.$GLOBALS['userdata'][0]['village'].' < 0';
                $panelOptions = array(
                    array("name" => "Add Village Bounty", "id" => $_GET['id'], "act" => "bBook", "act2" => "increase", "profile" => "table.id"),
                    array("name" => "View Profile", "id" => "13", "page" => "profile", "profile" => "table.username"),
                    array("name" => "Add Syndicate Bounty", "id" => $_GET['id'], "act" => "bBook", "act2" => "setTarget", "profile" => "table.id")
                );
        }

        // Search for username
        $search = "";
        if( isset( $_POST['name'] ) && !empty($_POST['name']) ){
            $search = " `users`.`username` LIKE '%".$_POST['name']."%' AND ";
        }

        // Collect Bingo Book Data
        if(!($bingo_book_data = $GLOBALS['database']->fetch_data('SELECT '.$panelBountySelector.',
                `users`.`id`, `users`.`username`, `users_loyalty`.`village`,
                `users_statistics`.`rank`, `users_statistics`.`experience`
            FROM (`users`,`users_statistics`)
                LEFT JOIN `bingo_book` ON ('.$panelBountyLimiter.')
                LEFT JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users_statistics`.`uid`)
            WHERE `users`.`id` = `bingo_book`.`userID` AND `users_statistics`.`uid` = `users`.`id` AND
                ' . $search . ' `users_statistics`.`rank_id` IN ('.$rank_sel.') '.$order.' LIMIT '.$min.", ".$number)))
        {
            throw new Exception("There was an error trying to receive the village's bingo book information");
        }

        // Show the table of users
        tableParser::show_list(
            'users',
            $GLOBALS['userdata'][0]['village']."'s ".$panelTitle,
            $bingo_book_data,
            array(
                'bounty' => "Bounty",
                'username' => "Username",
                'rank' => "Rank",
                'village' => "Village"
            ),
            $panelOptions,
            true,   // Send directly to contentLoad
            true,   // Show previous/next links
            false,  // No links at the top to show
            true,   // Allow sorting on columns
            true,   // pretty-hide options
            array(
                array(
                    "infoText"=>"Search by username",
                    "postField"=>"name",
                    "postIdentifier"=>"postIdentifier",
                    "inputName"=>"Search User",
                    "href" => "?id=".$_GET['id']."&amp;act=".$_GET['act']
                )
            ),
            $panelInformation
        );

        // Return Link
        $GLOBALS['template']->assign("returnLink", true);

    }

    // A function for getting a bingo book user to check his stats
    protected function get_bBook_user( $id , $transactionSafe = false){
        if( isset( $id ) && is_numeric($id) ){

            // Create the query
            $query = '
            SELECT `bingo_book`.*, `users_loyalty`.`village`, `users`.`username`, `users_statistics`.`money`,
                `target`.`rank_id` AS `target_rank_id`,
                `targetLoyalty`.`village` AS `targetVillage`
            FROM `users_loyalty`
                INNER JOIN `bingo_book` ON (`bingo_book`.`userID` = '.$id.')
                INNER JOIN `users` ON (`users`.`id` = `bingo_book`.`userID`)
                INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users_loyalty`.`uid`)
                INNER JOIN `users_statistics` AS `target` ON (`target`.`uid` = `bingo_book`.`userID`)
                INNER JOIN `users_loyalty` AS `targetLoyalty` ON (`targetLoyalty`.`uid` = `bingo_book`.`userID`)
            WHERE `users_loyalty`.`uid` = '.$_SESSION['uid'].' LIMIT 1';

            // If required, set to transaction safe
            if( $transactionSafe == true ){
                $query .= " FOR UPDATE";
            }

            // Get the data
            $this->user = $GLOBALS['database']->fetch_data($query);

            // Do checks
            if( $this->user !== "0 rows" ){

                // Check rank
                if( $this->user[0]['target_rank_id'] < 3 ){
                    throw new Exception('The target has to be a Chuunin or above to place a bounty!');
                }

                if($this->user[0]['userID'] === null) {
                    throw new Exception("This user doesn't exist within the system!");
                }

                if($this->user[0]['village'] == "Syndicate") {
                    throw new Exception("Outlaws cannot put out hits on others.");
                }

                return true;
            }
            else{
                throw new Exception("There was an error collectig all the required data for this action.");
            }
        }
        else{
            throw new Exception("Could not identify the profile you're looking for");
        }
    }

    //  A function to check if the user can set a bounty on this person
    protected function can_set_bounty( $transactionSafe = false ){
        if( $this->get_bBook_user( $_GET['profile'], $transactionSafe) ){
            if( $this->user[0]['targetVillage'] !== $this->user[0]['village'] ){
                if( $this->user[0][ $this->user[0]['village'] ] < 0 ){
                    $rank_sel = $this->getTargetRanks( $GLOBALS['userdata'][0]['rank_id'] );
                    if( stristr( $rank_sel, $this->user[0]['target_rank_id']) ){
                        return true;
                    }
                    else{
                        throw new Exception("You cannot set a bounty on a user of this rank.");
                    }
                }
                else{
                    throw new Exception("You cannot set a bounty on someone who has a positive reputation in your village.");
                }
            }
            else{
                throw new Exception("You cannot set a bounty on someone from your own village.");
            }
        }
    }

    //  A function to check if the user can set a target on this person
    protected function can_set_target( $transactionSafe = false ){
        if( $this->get_bBook_user( $_GET['profile'], $transactionSafe) ){
            if( $this->user[0]['targetVillage'] !== "Syndicate" ){
                $rank_sel = $this->getTargetRanks( $GLOBALS['userdata'][0]['rank_id'] );
                if( stristr( $rank_sel, $this->user[0]['target_rank_id']) ){
                    return true;
                }
                else{
                    throw new Exception("You cannot set a bounty on a user of this rank.");
                }
            }
            else{
                throw new Exception("You cannot set a bounty on someone from the syndicate.");
            }
        }
    }

    //  A function for requesting user input on bounty.
    //  This one is used for set target, which sets a bounty on Syndicate.
    //  Syndicate bounties can be claimned by everyone.
    protected function input_set_target(){
        if( $this->can_set_target() ){
            $GLOBALS['page']->UserInput(
                    "Set this user as a target; i.e. put out a hit on this user. The prize can only be claimed by outlaws. A minimum of 150,000 ryo required.",
                    "Bingo Book",
                    array(
                        array("infoText"=>"Enter the Amount","inputFieldName"=>"bounty_increase_amount","type" => "input", "inputFieldValue" => "")
                    ),
                    array(
                        "href"=>"?id=".$_GET['id']."&act=".$_GET['act']."&act2=".$_GET['act2']."&profile=".$_GET['profile'],
                        "submitFieldName"=>"targetSubmit",
                        "submitFieldText"=>"Set Target"),
                    "Return"
             );
        }
    }

    //  A function for requesting user input on bounty
    protected function input_bounty_increase(){
        if( $this->can_set_bounty() ){
            $GLOBALS['page']->UserInput(
                    "Raise the bounty on this user.",
                    "Bingo Book",
                    array(
                        array("infoText"=>"Enter the Desired Increase","inputFieldName"=>"bounty_increase_amount", "type" => "input", "inputFieldValue" => "")
                    ),
                    array(
                        "href"=>"?id=".$_GET['id']."&act=".$_GET['act']."&act2=".$_GET['act2']."&profile=".$_GET['profile'],
                        "submitFieldName"=>"bountySubmit",
                        "submitFieldText"=>"Increase Bounty"),
                    "Return"
             );
        }
    }

    //  Increase the bounty - if village is set to "SpecialBounty", then it's a hit
    protected function do_increase_bounty( $village ) {

        // Start transaction
        $GLOBALS['database']->transaction_start();

        // Quick Sanity Checks
        if( ctype_digit($_GET['profile']) !== true || ctype_digit($_POST['bounty_increase_amount']) !== true ) {
            throw new Exception('The specific user information is corrupted!');
        }

        if(intval($_POST['bounty_increase_amount']) <= 0) {
            throw new Exception('You must enter a number greater than zero!');
        }

        if(intval($_POST['bounty_increase_amount']) > $GLOBALS['userdata'][0]['money']) {
            throw new Exception('You do not have enough ryo to increase the bounty this much!');
        }

        $cap = 12250;
        if($village == "SpecialBounty")
            $cap = 1837500;

        $query = 'SELECT * FROM `bingo_book` WHERE `userID` = '.$_GET['profile'];
        $target = $GLOBALS['database']->fetch_data($query);

        if($target[0][$village] == $cap*-1)
            throw new excpetion('the user already has the max bounty allowed.');

        if($target[0][$village] - $_POST['bounty_increase_amount'] < $cap * -1)
            $_POST['bounty_increase_amount'] = $cap - abs($target[0][$village]);

        if($_POST['bounty_increase_amount'] < 0)
            $_POST['bounty_increase_amount'] = 0;

        // Check if we should proceed. Transaction safe selection of $this->user
        if(
            ( $village == "SpecialBounty" && $this->can_set_target( true ) ) ||
            $this->can_set_bounty( true ) && $_POST['bounty_increase_amount'] != 0
        ){
            $query = 'UPDATE `bingo_book`, `users_statistics`, `users_timer`
                SET `bingo_book`.`'.$village.'` = `bingo_book`.`'.$village.'` - '.$_POST['bounty_increase_amount'].',
                    `users_statistics`.`money` = `users_statistics`.`money` - '.$_POST['bounty_increase_amount'].',
                    `users_timer`.`special_bounty_timer` = '. (time()) .'
                WHERE `users_statistics`.`uid` = '.$_SESSION['uid'].' AND `bingo_book`.`userID` = '.$_GET['profile'] . ' AND `users_timer`.`userID` = `bingo_book`.`userID` ';

            if($village == 'SpecialBounty')
                $query .= ' AND (`bingo_book`.`SpecialBounty` != 0 OR `users_timer`.`special_bounty_timer` < '. (time()-60*60*1).')';

            //get current diplomacy of target user
            $fetch_query = 'SELECT `'.$village.'` as `diplomacy` FROM `bingo_book` WHERE `userID` = '.$_GET['profile'];
            $result = $GLOBALS['database']->fetch_data($fetch_query);
            $new_diplo = $result[0]['diplomacy'] - $_POST['bounty_increase_amount'];

            if($new_diplo > ($cap*-1))
                $new_diplo = ($cap*-1);

            $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] - $_POST['bounty_increase_amount']));

            if($new_diplo != $result[0]['diplomacy'])
            {
                $events = new Events($_GET['profile']);
                $events->acceptEvent('diplomacy_loss', array('new'=>$new_diplo, 'old'=>$result[0]['diplomacy'], 'context'=>$village));
                $events->closeEvents();
            }

            // Attempt the update
            if(($GLOBALS['database']->execute_query($query)) == false)
            {
                throw new Exception('The user had a hit placed on them too recently. '.$query);
            }
        }

        // Message for the user
        if( $village == "SpecialBounty" ){
            $message = "You have put out a hit on ".$this->user[0]['username']." worth ".$_POST['bounty_increase_amount']." ryo!";
        }
        else{
            $message = $this->user[0]['username']."'s bounty in ".$village." has been increased by ".$_POST['bounty_increase_amount']."!";
        }
        $GLOBALS['page']->Message($message, 'Bounty Increase', 'id='.$_GET['id']);

        // Commit the transaction
        $GLOBALS['database']->transaction_commit();
    }

    //  Respect bonuses page
    protected function respectBonuses() {

        // Get the loyalty lib
        require_once(Data::$absSvrPath.'/libs/villageSystem/respectLib.php');
        $respectLib = new respectLib();

        // Days the user has been a member of village
        $respectPoints = $GLOBALS['userdata'][0]['vil_loyal_pts'];

        // Get a list of all his bonuses
        $list = $respectLib->getRewardList();

        // Fix up list with colors
        $displayList = array();
        foreach( $list as $key => $text ){

            // Only include relevant entries
            if( $GLOBALS['userdata'][0]['village'] == "Syndicate" && $key < 0 || $GLOBALS['userdata'][0]['village'] !== "Syndicate" && $key > 0 ){
                if(
                    ( $key < 0 && $respectPoints <= $key ) ||
                    ( $key > 0 && $respectPoints >= $key )
                ){
                    $displayList[] = array( "points" => $key, "benefit" => '<font color="#008000">'.$text."</font>" );
                }
                else{
                    $displayList[] = array( "points" => $key, "benefit" => '<font color="#800000">'.$text."</font>" );
                }
            }
        }

        // Activate/deactivate
        if( isset($_GET['action']) ){
            $action = false;
            switch( $_GET['action'] ){
                case "deactivate": $action = "no"; break;
                case "activate": $action = "yes"; break;
            }
            if( $action !== false && $GLOBALS['userdata'][0]['activateBonuses'] !== $action ){
                $GLOBALS['userdata'][0]['activateBonuses'] = $action;
                $GLOBALS['database']->execute_query('UPDATE `users_loyalty`
                    SET `users_loyalty`.`activateBonuses` = "'.$action.'"
                    WHERE `users_loyalty`.`uid` = '.$_SESSION['uid'].' LIMIT 1');
            }
        }

        // Top options
        switch( $GLOBALS['userdata'][0]['activateBonuses'] ){
            case "yes": $topOption = array("name" => "Deactivate All Bonuses", "href" =>"?id=".$_GET["id"]."&act=".$_GET['act']."&action=deactivate"); break;
            case "no": $topOption = array("name" => "Activate All Bonuses", "href" =>"?id=".$_GET["id"]."&act=".$_GET['act']."&action=activate"); break;
        }

        // Days in village
        $secondsInVillage = $GLOBALS['user']->load_time - $GLOBALS['userdata'][0]['time_in_vil'];
        $daysInVillage = floor(($secondsInVillage)/(24 * 3600));

        // Days till next respect point
        $timeToNext = "";
        $timeLeft = 0;
        $updateCyclus = 24 * 3600;
        if( $daysInVillage > 10 ){
            $timeLeft = $updateCyclus - ($GLOBALS['user']->load_time - $GLOBALS['userdata'][0]['vil_pts_timer']);
        }
        else{
            $timeLeft = 10*$updateCyclus-$secondsInVillage;
        }
        if( $timeLeft > 0 ){
            $timeToNext = "<br>Time to next respect point: " . functions::convert_time( $timeLeft , 'respectCount', 'false');
        }

        tableParser::show_list(
            'respect',
            'Respect Benefits',
            $displayList,
            array(
                'points' => "Required Respect",
                'benefit' => "Benefit"
            ),
            false,
            true, // Send directly to contentLoad
            false, // No newer/older links
            array( $topOption ), // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Depending on how long you\'ve been a member, various bonuses are unlocked. The bonuses currently unlocked by your character will be listed at this page. Remember, you must be a member of a faction for 10 days before you start earning respect, and you only earn the respect when you log in to the game.
             <br><br>You have been a member for '.$daysInVillage.' days and currently have '.$respectPoints." respect points.
             <br><br><i>Please remember that if you ever leave your village, you will lose a significant amount of respect points.</i><br>".$timeToNext
        );

        // Set return link
        $GLOBALS['template']->assign("returnLink", true);
    }


    //  Clan page
    protected function clans() {

        // Get the clan lib
        require_once(Data::$absSvrPath.'/libs/villageSystem/clanLib.php');
        $clanLib = new clanLib();

        // Show list of ANBUs
        $clanLib->showClanList( $GLOBALS['userdata'][0]['village'] );

    }

    //  Clan details page
    protected function clanDetails() {

        // Get the clan lib
        require_once(Data::$absSvrPath.'/libs/villageSystem/clanLib.php');
        $clanLib = new clanLib();

        // Show list of ANBU members
        $clanLib->showClanStatus( $_GET['cid'] );

    }

     //  Clan agenda page
    protected function clanAgenda() {

        // Get the clan lib
        require_once(Data::$absSvrPath.'/libs/villageSystem/clanLib.php');
        $clanLib = new clanLib();

        // Show list of ANBU members
        $clanLib->showAgenda( $_GET['cid'] );

    }

    //  ANBU page
    protected function anbu() {

        // Get the anbu lib
        require_once(Data::$absSvrPath.'/libs/villageSystem/anbuLib.php');
        $anbuLib = new anbuLib();

        // Show list of ANBUs
        $anbuLib->showAnbuList( $GLOBALS['userdata'][0]['village'] );

    }

    //  ANBU details page
    protected function anbuDetails() {

        // Get the anbu lib
        require_once(Data::$absSvrPath.'/libs/villageSystem/anbuLib.php');
        $anbuLib = new anbuLib();

        // Show list of ANBU members
        $anbuLib->showMembers( $_GET['aid'] );

    }

    //  Show war status
    protected function warstatus() {

        // Get the war lib
        require_once(Data::$absSvrPath.'/libs/villageSystem/warLib.php');
        $warLib = new warLib();

        // Village Status
        $warLib->setAlliances( true );
        $villagevars = $warLib->pick_out_village($GLOBALS['userdata'][0]['village'], $warLib->allAlliances);

        if( $villagevars !== "0 rows" ){

            // Save information on village status
            $GLOBALS['template']->assign('villageVars', $villagevars );

            // Get Alliance information
            $colorAlliance = $warLib->prettyAlliance( $GLOBALS['userdata'][0]['alliance'][0] );
            $GLOBALS['template']->assign('allianceData', $colorAlliance );

            // Check if we're in war
            $inWar = $warLib->inWar( $GLOBALS['userdata'][0]['alliance'][0] );
            if( $inWar == false ){
                $inWar = 0;
            }

            // Set template data
            $GLOBALS['template']->assign('warringVillages', $inWar );
            if( $inWar ){

                // Get village destructions in percentages
                $destructionPercentages = $warLib->getDestructionPerc( $villagevars );
                $GLOBALS['template']->assign('destructionPercs', $destructionPercentages );

                // Get war heroes, i.e. the people with the most structure points
                $this->setWarHeroes( $GLOBALS['userdata'][0]['village'] );
            }

            // Load the template
            $GLOBALS['template']->assign('contentLoad', './templates/content/town_hall/warStatus.tpl');

        }
        else{
            throw new Exception("Somehow data for the village '".$GLOBALS['userdata'][0]['village']."' could not be found in the database");
        }

    }

    // Set a smarty variable with the war heroes of the given village
    private function setWarHeroes( $village ){

        // Set value
        $min =  tableParser::get_page_min();
        $number = tableParser::set_items_showed( 10 );

        // Get clan users
        $users = $GLOBALS['database']->fetch_data('SELECT `users`.`id`, `users`.`username`, `users_statistics`.`rank`,
            `users_missions`.`structureDestructionPoints`, `users_missions`.`structureGatherPoints`,
                (`users_missions`.`structureDestructionPoints` + `users_missions`.`structureGatherPoints`) AS `totalPoints`
            FROM `users_loyalty`
                INNER JOIN `users_missions` ON (`users_missions`.`userid` = `users_loyalty`.`uid`)
                INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users_loyalty`.`uid`)
                INNER JOIN `users` ON (`users`.`id` = `users_loyalty`.`uid`)
            WHERE `users_loyalty`.`village` = "'.$village.'"
                ORDER BY `totalPoints` DESC,`experience` DESC LIMIT '.$min.', '.$number);

        // Set positions
        if( $users !== "0 rows" ){

            // Show the table of users
            tableParser::show_list(
                'warHeroes',
                'Heroes in this War',
                $users,
                array(
                    'username' => "Name",
                    'rank' => "Rank",
                    'structureDestructionPoints' => "Structures Destroyed",
                    'structureGatherPoints' => "Structures Healed"
                ),
                array(
                    array( "id" => 13, "name" => "Profile", "page" => "profile", "profile" => "table.username")
                ) ,
                false, // Send directly to contentLoad
                true, // No newer/older links
                false, // No top options links
                false, // No sorting on columns
                false, // No pretty options
                false, // No top search field
                false // No top description
            );
        }

    }

    //  Show territories in this faction
    protected function territoryList() {

        $min =  tableParser::get_page_min();
        $number = tableParser::set_items_showed( 20 );

        // Get users
        $users = $GLOBALS['database']->fetch_data("
                SELECT *
                FROM `locations`
                WHERE
                    `owner` = '" . $GLOBALS['userdata'][0]['village'] . "'");

        // Show the table of users
        tableParser::show_list(
            'users',
            'List of Territories',
            $users,
            array(
                'id' => "Area ID",
                'name' => "Territory Name",
                'owner' => "Owned By"
            ),
            false,
            true
        );

        // Return Link
        $GLOBALS['template']->assign("returnLink", true);
    }

    // Show the clinic
    protected function show_clinic(){

        // Check if user has bloodline
        if( !$this->hasBloodline() ){

            // Either has rolled, or not
            if( $this->hasRolled() ){

                // User has already rolled a bloodline
                $this->alerady_rolled_message();

            }
            else{

                // Confirm rolling
                if( isset($_POST['Submit']) ){
                    $this->do_roll_bloodline();
                }
                else{
                    $GLOBALS['page']->Confirm("Here at the bloodline clinic, we can run your blood through various tests to see if you are blessed with any genetic bloodlines.", 'Bloodline Clinic', 'Test now!');
                }
            }
        }
        else{
            $this->alerady_rolled_message();
        }
    }

    // Do roll a bloodline for the user
    protected function do_roll_bloodline(){

        //  Start Transaction
        $GLOBALS['database']->transaction_start();

        // Random number
        $number = random_int(1, 100);
        if ($number >= 80) {

            // User rolled a bloodline, determine bloodline type:
            $blood_type = random_int(1, 100);
            switch (true) {
                case ($blood_type <= 50): $bloodline_class = 'D'; break;
                case ($blood_type <= 79): $bloodline_class = 'C'; break;
                case ($blood_type <= 94): $bloodline_class = 'B'; break;
                case ($blood_type <= 99): $bloodline_class = 'A'; break;
                case ($blood_type === 100): $bloodline_class = 'S'; break;
                default: $bloodline_class = 'D'; break;
            }

            $bloodline = $GLOBALS['database']->fetch_data('SELECT * FROM `bloodlines`
                WHERE `bloodlines`.`rarity` = "'.$bloodline_class.'" AND
                   `bloodlines`.`village` IN ("All" , "'.$GLOBALS['userdata'][0]['village'].'")
                   ORDER BY RAND() LIMIT 1');
            if ($bloodline !== '0 rows') {

                if(strpos($bloodline[0]['tags'], '[T]') !== FALSE && strpos($bloodline[0]['tags'], '[N]') !== FALSE && strpos($bloodline[0]['tags'], '[G]') !== FALSE && strpos($bloodline[0]['tags'], '[B]') !== FALSE)
                    $split_type = array('Taijutsu','Ninjutsu','Genjutsu','Bukijutsu')[random_int(0,3)];
                else
                    $split_type = false;

                // Update the bloodline information
                $this->set_user_bloodline($bloodline, $split_type, 0);

                // Succes message
                $GLOBALS['page']->Message("The doctors discover that you have the ".$bloodline_class."-ranked bloodline <i>".$bloodline[0]['name']."</i>", 'Bloodline Clinic', 'id=' . $_GET['id'] . '');

            }
            else{
                throw new Exception("There was an error retrieving bloodline information");
            }
        } else {

            // User did not get a bloodline
            $GLOBALS['database']->execute_query('UPDATE `users` SET `users`.`bloodline` = "None" WHERE `users`.`id` = '.$_SESSION['uid'].' LIMIT 1');

            // Succes message
            $GLOBALS['page']->Message("Having thoroughly investigated your samples, the doctors conclude that you do not have any natural bloodlines. But do not despair, in this world there are other ways to get bloodlines.", 'Bloodline Clinic', 'id=' . $_GET['id'] . '');

        }

        // Commit Transaction
        $GLOBALS['database']->transaction_commit();
    }

    // Remove a bloodline from the user with an etched stone
    protected function do_remove_bloodline(){

        if( $this->hasBloodline() ){

            // Check for confirmation
            if( isset($_POST['Submit']) ){

                //  Start Transaction
                $GLOBALS['database']->transaction_start();

                // Check for etched stones
                $items = $GLOBALS['database']->fetch_data('SELECT COUNT(`users_inventory`.`timekey`) AS `stones`
                    FROM `users_inventory`
                    WHERE `users_inventory`.`iid` = 1 AND `users_inventory`.`uid` = '.$_SESSION['uid'].' FOR UPDATE');
                if ( $items[0]['stones'] >= 1 ) {

                    // Start constructing update query
                    $query = "";

                    // Get the user bloodline
                    $bloodline = $GLOBALS['database']->fetch_data('SELECT * FROM `bloodlines`
                        WHERE "'.$GLOBALS['userdata'][0]['bloodline'].'" LIKE CONCAT(`bloodlines`.`name`,"%") LIMIT 1');
                    if( $bloodline !== "0 rows" ){

                        // If there's a regen increase, remove it
                        if ($bloodline[0]['regen_increase'] > 0) {
                            $query = ", `regen_rate` = `regen_rate` - '" . $bloodline[0]['regen_increase'] . "' ";
                        }

                        // Check if the bloodline has special jutsus
                        if ($bloodline[0]['special_jutsu'] == 'yes') {

                            // Do delete the jutsus from the user
                            $GLOBALS['database']->execute_query('DELETE FROM `users_jutsu`
                                WHERE `users_jutsu`.`jid` IN (
                                    SELECT `jutsu`.`id` AS `jid` FROM `jutsu`
                                    WHERE `jutsu`.`bloodline` = "'.$bloodline[0]['name'].'"
                                ) AND `users_jutsu`.`uid` = "'.$_SESSION['uid'].'"');

                            // If user is training this jutsu, then stop him in doing that
                            $userJutsu = $GLOBALS['database']->fetch_data('SELECT `users_timer`.`jutsu`
                                FROM `users_timer`
                                    INNER JOIN `jutsu` ON (`jutsu`.`id` = `users_timer`.`jutsu` AND `jutsu`.`bloodline` != "")
                                WHERE `users_timer`.`userid` = '.$_SESSION['uid'].' LIMIT 1');

                            if ($userJutsu !== "0 rows") {
                                $query .= ", `jutsu` = '', `jutsu_timer` = '0'";
                            }
                        }
                    }

                    //updating elements in bloodline system.
                    $elements = new Elements();
                    $elements->removeUserBloodlineAffinities();

                    // Do update user
                    if( !$GLOBALS['database']->execute_query("
                         UPDATE `users`, `users_timer`, `users_statistics`
                         SET `users`.`bloodline` = 'None',
                            `users_timer`.`jutsu` = '',
                            `users_timer`.jutsu_timer = 0
                            ".$query."
                         WHERE
                            `users`.`id` = ".$_SESSION['uid']." AND
                            `users_timer`.`userid` = `users`.`id` AND
                            `users_statistics`.`uid` = `users`.`id`")
                    ){
                        throw new Exception("There was an error updating the user data");
                    }
                    $GLOBALS['Events']->acceptEvent('bloodline', array('data'=>'None', 'extra'=>'None' ));

                    if(!($users_inventory = $GLOBALS['database']->fetch_data("SELECT * FROM `users_inventory` where `iid` = 1 AND `uid` = ".$_SESSION['uid'])))
                        throw new Exception('There was an error trying to recieve necessary information.');

                    $stack = $quantity = 0;
                    foreach($users_inventory as $item_temp)
                    {
                        if(isset($item_temp['stack']))
                        {
                            $stack++;
                            $quantity=+$item_temp['stack'];
                        }
                    }

                    // Delete item
                    $GLOBALS['database']->execute_query('DELETE FROM `users_inventory`
                        WHERE `users_inventory`.`iid` = 1 AND `users_inventory`.`uid` = '.$_SESSION['uid'].' LIMIT 1');

                    $GLOBALS['Events']->acceptEvent('item_person', array('data'=>'!1', 'context'=>1, 'new'=>$stack-1, 'old'=>$stack ));
                    $GLOBALS['Events']->acceptEvent('item_quantity_loss', array('context'=>1, 'new'=>$quantity-1, 'old'=>$quantity ));

                    // Succes message
                    $GLOBALS['page']->Message("Using the Stone of Heraldry your bloodline is removed at the clinic", 'Bloodline Clinic', 'id=' . $_GET['id'] . '');

                    // Start Transaction
                    $GLOBALS['database']->transaction_commit();

                } else {
                    throw new Exception("This operation requires a Stone of Heraldry.");
                }
            }
            else{
                $GLOBALS['page']->Confirm("Are you sure you want to remove your bloodline?", 'Bloodline Clinic', 'Remove now!');
            }
        }
        else{
            throw new Exception("You do not have a bloodline to remove");
        }
    }

    // Show the user a list of his bloodline items
    protected function do_choose_bloodline_item(){

        // Get the items
        $items = $GLOBALS['database']->fetch_data('SELECT `items`.`name`, `users_inventory`.`iid`
            FROM `users_inventory`
                INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid` AND `use` LIKE "%bloodline:(%"
                    AND `use` != "BLOOD:R" AND `trading` IS NULL)
            WHERE `users_inventory`.`uid` = '.$_SESSION['uid']);

        if( $items !== "0 rows" ){

            // Select array
            $selectArray = array();
            foreach( $items as $item ){
                $selectArray[ $item['iid'] ] = $item['name'];
            }

            // Create the input form
            $GLOBALS['page']->UserInput(
                    "Which of the following bloodline items do you want to seal within your own body",
                    "Bloodline Clinic",
                    array(
                        // A select box
                        array(
                            "inputFieldName"=>"iid",
                            "type"=>"select",
                            "inputFieldValue"=> $selectArray
                        )
                    ),
                    array(
                        "href"=>"?id=".$_GET['id']."&act=clinic&act2=sealBloodline" ,
                        "submitFieldName"=>"SubmitItem",
                        "submitFieldText"=>"Submit"),
                    "Return" ,
                    "trainingForm"
            );

        }
        else{
            throw new Exception("You do not own any items with bloodlines sealed within them");
        }
    }

    // Use items to grant user a bloodline
    protected function do_user_bloodline_item()
    {

        if( !$this->hasBloodline() || $_POST['iid'] == 1032 || $_POST['iid'] == 1033 || $_POST['iid'] == 1034 || $_POST['iid'] == 1035)
        {

            //  Start Transaction
            $GLOBALS['database']->transaction_start();

            // Check item
            $item = $GLOBALS['database']->fetch_data('SELECT `items`.`use`, `items`.`name`
                FROM `users_inventory`
                    INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid` AND `items`.`use` LIKE "bloodline:(%")
                WHERE `users_inventory`.`uid` = '.$_SESSION['uid'].' AND `users_inventory`.`iid` = '.$_POST['iid'].' AND
                    `users_inventory`.`trading` IS NULL LIMIT 1 FOR UPDATE');
            if ($item !== '0 rows' && $item != "0")
            {
                $tag = explode(':',$item[0]['use']);
                if($tag[0] == 'bloodline')
                {
                    $name = true;
                    $rank = true;
                    $type = true;
                    $village = true;
                    $group = true;

                    $temp_fields = explode(';',rtrim(ltrim($tag[1],'('), ')'));
                    $fields = array();

                    foreach($temp_fields as $field_data)
                    {
                        $temp_data = explode('>', $field_data);
                        if(isset($temp_data[1]))
                        {
                            $fields[$temp_data[0]] = explode(',',rtrim(ltrim($temp_data[1],'('), ')'));
                        }
                    }

                    if(isset($fields['name']) && $fields['name'][0] != 'current_bloodline')
                        $name = ' `name` in ("'.implode('","',$fields['name']).'") AND';
                    else if(isset($fields['name']) && $fields['name'][0] == 'current_bloodline')
                        $name = " `name` = '".explode(':',$GLOBALS['userdata'][0]['bloodline'])[0]."' AND";

                    if(isset($fields['rank']))
                        $rank = ' `rarity` in ("'.implode('","',$fields['rank']).'") AND';

                    if(isset($fields['type']))
                        $type = ' `type` in ("'.implode('","',$fields['type']).'") AND';

                    if(isset($fields['village']))
                        $village = ' `village` in ("'.implode('","',$fields['village']).'") AND';

                    if(isset($fields['group']))
                        $group = ' `group` in ("'.implode('","',$fields['group']).'") AND';

                    $query = 'SELECT * FROM `bloodlines` LEFT JOIN (SELECT `bloodlineName`, COUNT(*) AS `count` FROM `bloodline_rolls` WHERE `uid` = '.$_SESSION['uid'].' AND (`iid` = '.$_POST['iid'].' OR `iid` = 0) GROUP BY `bloodlineName`) s on (`s`.`bloodlineName` = `bloodlines`.`name`) ';

                    if($name !== true || $rank !== true || $type !== true || $village !== true || $group !== true)
                    {
                        $query .= ' WHERE ';

                        if($name !== true)
                            $query .= $name;

                        if($rank !== true)
                            $query .= $rank;

                        if($type !== true)
                            $query .= $type;

                        if($village !== true)
                            $query .= $village;

                        if($group !== true)
                            $query .= $group;

                        $query = rtrim($query, ' AND');
                    }

                    $query .= ' ORDER BY `count`, RAND() LIMIT 1';

                    $bloodline = $GLOBALS['database']->fetch_data($query);

                    if ($bloodline != '0 rows')
                    {
                        if(isset($fields['setSplitType']) && strpos($bloodline[0]['tags'], '[T]') !== FALSE && strpos($bloodline[0]['tags'], '[N]') !== FALSE && strpos($bloodline[0]['tags'], '[G]') !== FALSE && strpos($bloodline[0]['tags'], '[B]') !== FALSE)
                            $split_type = $fields['setSplitType'];
                        else if(strpos($bloodline[0]['tags'], '[T]') !== FALSE && strpos($bloodline[0]['tags'], '[N]') !== FALSE && strpos($bloodline[0]['tags'], '[G]') !== FALSE && strpos($bloodline[0]['tags'], '[B]') !== FALSE)
                            $split_type = array('Taijutsu','Ninjutsu','Genjutsu','Bukijutsu')[random_int(0,3)];
                        else
                            $split_type = false;


                        // Insert bloodline
                        $this->set_user_bloodline($bloodline, $split_type, $_POST['iid']);

                        if(!($users_inventory = $GLOBALS['database']->fetch_data("SELECT * FROM `users_inventory` where `iid` = ".$_POST['iid']." AND `uid` = ".$_SESSION['uid'])))
                        throw new Exception('There was an error trying to recieve necessary information.');

                        $stack = $quantity = 0;
                        foreach($users_inventory as $item_temp)
                        {
                            if(isset($item_temp['stack']))
                            {
                                $stack++;
                                $quantity=+$item_temp['stack'];
                            }
                        }

                        // Delete item
                        $GLOBALS['database']->execute_query("DELETE FROM `users_inventory`
                            WHERE `uid` = '" . $_SESSION['uid'] . "' AND `iid` = '" . $_POST['iid'] . "' LIMIT 1");

                        $GLOBALS['Events']->acceptEvent('item_person', array('data'=>"!".$_POST['iid'], 'context'=>$_POST['iid'], 'new'=>$stack-1, 'old'=>$stack ));
                        $GLOBALS['Events']->acceptEvent('item_quantity_loss', array('context'=>$_POST['iid'], 'new'=>$quantity-1, 'old'=>$quantity ));

                        // Give message
                        if($_POST['iid'] == 1032 || $_POST['iid'] == 1033 || $_POST['iid'] == 1034 || $_POST['iid'] == 1035)
                            $GLOBALS['page']->Message("Using ".$item[0]['name']." changed your bloodline's type to ".$split_type[0].".", 'Bloodline Clinic', 'id=' . $_GET['id'] . '');
                        else
                            $GLOBALS['page']->Message("Using ".$item[0]['name']." you can now feel the power of the bloodline ".$bloodline[0]['name']." within your body.", 'Bloodline Clinic', 'id=' . $_GET['id'] . '');


                    }
                    else
                    {
                        throw new Exception("The bloodline granted by this item could not be found in the database: ".$query);
                    }


                }
                else
                {
                    throw new Exception('Bad tag: '.$tag[0]);
                }

            }
            else
            {
                throw new Exception("You do not own the specified item, or the item is not a valid bloodline item.");
            }

            // Commit Transaction
            $GLOBALS['database']->transaction_commit();
        }
        else
        {
            throw new Exception("The doctors are just about to start the procedure, but then realize you already have a bloodline. You need to get that removed first.");
        }
    }

    // User already rolled a bloodline
    private function alerady_rolled_message(){

        // Menu options (new bloodline item / remove item)
        $menu = array(
            array("name" => "Remove Bloodline",         "href" => "?id=" . $_GET['id'] . "&act=".$_GET['act']."&act2=removeBloodline"),
            array("name" => "Use Bloodline Item",  "href" => "?id=" . $_GET['id'] . "&act=".$_GET['act']."&act2=sealBloodline")
        );

        // Construct menu with options
        $GLOBALS['template']->assign('subHeader', ucfirst($this->cpName) . ' Hall');
        $GLOBALS['template']->assign('nCols', 2);
        $GLOBALS['template']->assign('nRows', 1 );
        $GLOBALS['template']->assign('subTitle', "What are you looking for here at the clinic?");
        $GLOBALS['template']->assign('linkMenu', $menu);
        $GLOBALS['template']->assign('buttonLayout', "SUBMIT");
        $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');

    }

    // Sets the bloodline of a user and logs the action
    private function set_user_bloodline( $bloodline, $split_type = false, $iid ){

        if(is_array($split_type))
            $split_type = $split_type[random_int(0,count($split_type)-1)];

        // User update query
        $query = ($bloodline[0]['regen_increase'] > 0) ? ", `regen_rate` = `regen_rate` + '" . $bloodline[0]['regen_increase'] . "'" : "";

        // Update user information:
        if($split_type === false)
        {
            if(!$GLOBALS['database']->execute_query("UPDATE `users`, `users_statistics` SET `bloodline` = '" . $bloodline[0]['name'] . "' ".$query." WHERE `id` = '" . $_SESSION['uid'] . "' AND `uid` = `id`"))
            {
                throw new Exception('there was an issue setting user bloodline data');
            }
            $GLOBALS['Events']->acceptEvent('bloodline', array('data'=>$bloodline[0]['name'], 'extra'=>$split_type ));
        }
        else if($split_type == 'Taijutsu' || $split_type == 'Ninjutsu' || $split_type == 'Genjutsu' || $split_type == 'Bukijutsu' || $split_type == 'Highest')
        {
            if(!$GLOBALS['database']->execute_query("UPDATE `users`, `users_statistics` SET `bloodline` = '" . $bloodline[0]['name']. ':' . $split_type . "' ".$query." WHERE `id` = '" . $_SESSION['uid'] . "' AND `uid` = `id`"))
            {
                throw new Exception('there was an issue setting user bloodline data');
            }
            $GLOBALS['Events']->acceptEvent('bloodline', array('data'=>$bloodline[0]['name'], 'extra'=>$split_type ));
        }
        else
        {
            throw new Exception('bad split type: '.$split_type);
        }


        // Insert into log
        $GLOBALS['database']->execute_query("INSERT INTO `bloodline_rolls`
                (`uid`,`time`,`bloodlineName`,`bloodRank`,`iid`)
            VALUES
                ('" . $_SESSION['uid'] . "','" . $GLOBALS['user']->load_time . "','" . $bloodline[0]['name'] . "', '".$bloodline[0]['rarity']."', ".$iid.")");

        //setting affinities.
        $elements = new Elements();
        $affinities = $elements->getUserBloodlineAffinities();
        if($bloodline[0]['affinity_1'] != $affinities[0] || $bloodline[0]['affinity_2'] != $affinities[1] || $bloodline[0]['special_affinity'] != $affinities[2])
        {
            $elements->setUserBloodlineAffinities(array($bloodline[0]['affinity_1'],$bloodline[0]['affinity_2'],$bloodline[0]['special_affinity']));
        }
    }

    // Check if the user has a bloodline
    private function hasBloodline(){
        return ($GLOBALS['userdata'][0]["bloodline"] !== "None" && $GLOBALS['userdata'][0]["bloodline"] !== "") ? true : false;
    }

    // Check if the user has rolled a bloodline yet
    private function hasRolled(){
        return ($GLOBALS['userdata'][0]["bloodline"] !== "") ? true : false;
    }
}