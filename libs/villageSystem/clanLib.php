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

// The basic clan lib with all the relevant clan functions
class clanLib {

    // ** BELOW ARE FUNCTIONS SHOWING DATA ** //

    // Show anbu details
    public function showClanStatus( $clanID ){

        // Get the clan
        $this->clan = $this->getClan( $clanID );
        $GLOBALS['template']->assign('clan', $this->clan[0] );

        // Fix potential issues where e.g. the leader has a coleader spot
        $this->doFixLeaderPositionErrors();

        // Get the total members in clan
        $this->countMembers = cachefunctions::getClanCount($this->clan[0]['id'] );
        $this->countMembers =  $this->countMembers[0]['count'];
        $GLOBALS['template']->assign('clanUsers', $this->countMembers );

        // Get user rank in clan
        $this->setClanStatusInformation();
        $GLOBALS['template']->assign('avgPoints', $this->avgPoints );
        $GLOBALS['template']->assign('userPoints', $this->userPoints );
        $GLOBALS['template']->assign('userClanRank', $this->rank );

        // Set value
        $min =  tableParser::get_page_min();
        $number = tableParser::set_items_showed( 20 );

        // Get an array with userIDs => position
        $positionArray = $this->getClanPositions();
        $leaderOrdering = !empty($positionArray) ? "FIELD(`id`, ".implode( ",", array_keys($positionArray) ).") DESC, " : "";

        // Get clan users: ,
        $users = $GLOBALS['database']->fetch_data("
            SELECT `id`,`username`,`longitude`,`latitude` FROM `users`,`users_preferences`,`users_statistics`
            WHERE `clan` = '".$clanID."' AND `users_statistics`.`uid` = `id` AND `users_preferences`.`uid` = `id`
            ORDER BY ".$leaderOrdering." `experience` DESC
            LIMIT ".$min.",".$number
        );

        // Delete clan if not a core clan
        if( $this->clan[0]['clan_type'] !== "core" && $users == "0 rows" ) {
            $this->removeClan($this->clan[0]['id'], "No Users");
            throw new Exception("No users were found in this clan so it has been deleted");
        }

        // Set the position within the clan for all the shown users
        if( $users !== "0 rows" ){
            for( $i=0 ; $i < count($users) ; $i++ ){

                // Default - member, no challenge
                $users[ $i ]['challenge'] = "N/A";

                // Check if the ID has a position within the clan
                if(array_key_exists($users[$i]['id'], $positionArray) ){

                    // Set the position
                    $users[ $i ]['position'] = $positionArray[ $users[$i]['id'] ];

                    // If the position is leader, and the position is not already owned by the leader
                    if( $GLOBALS['userdata'][0]['clan'] == $this->clan[0]['id'] &&
                        $users[ $i ]['position'] == "Leader" &&
                        $users[ $i ]['id'] !== $_SESSION['uid']
                    ){
                        if( $this->canChallengeLeader == true ){
                            $code = md5($GLOBALS['user']->load_time . "-" . $users[ $i ]['id']. "-" .$GLOBALS['userdata'][0]['longitude'] . "-" .$GLOBALS['userdata'][0]['latitude'] );
                            $users[ $i ]['challenge'] = "<a href='?id=".$_GET['id']."&act2=challengeLeader&code=".$code."'>Challenge</a>";
                        }
                        else{
                            $users[ $i ]['challenge'] = "<i>Need Higher Clan Rank</i>";
                        }
                    }
                }
                else{
                    $users[ $i ]['position'] = "Member";
                }
            }
        }

        // Show the table of users
        tableParser::show_list(
            'clanMembers',
            'Clan Members',
            $users,
            array(
                'position' => "Position",
                'username' => "Name",
                'challenge' => "Challenge"
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
            '<b>Clan Orders:</b> '.functions::color_BB(functions::parse_BB($this->clan[0]['orders']))
        );

        // Set signature to smarty
        $image = functions::getUserImage('/clansigs/', $this->clan[0]['id']);
        $GLOBALS['template']->assign('signature', $image );

        // Set the switch on whether users can claim leadership.
        $canClaim = !$this->hasLeader() && $this->clan[0]['id'] == $GLOBALS['userdata'][0]['clan'];
        $GLOBALS['template']->assign("canClaim", $canClaim );

        // Show the smarty template
        $GLOBALS['template']->assign('contentLoad', './templates/content/clan/clan_status.tpl');

        // Set a return link, since this is the only thing displayed
        $this->link = functions::get_current_link( array("id","act") );
        $GLOBALS['template']->assign("returnLink", $this->link);

    }

    // Get list of loyalties
    public function showClanList( $village , $canEdit = false, $canJoin = false, $newDescription = false ){

        // Set a link
        $this->link = functions::get_current_link( array("id","act") );
        $min =  tableParser::get_page_min();

        // Set the options
        $options = array(
            array_merge( $_GET, array("name" => "Details", "act2" => "clandetail", "cid" => "table.id") ) ,
            array_merge( $_GET, array("name" => "Agenda", "act2" => "agenda", "cid" => "table.id") )
        );

        // Can edit?
        $topOptions = false;
        if( $canEdit == true ){

            // Set options
            // $options[] = array_merge( $_GET, array("name" => "Edit", "act2" => "editClan", "cid" => "table.id") ) ;

            // Set create link
            $topOptions = array(
                array("name" => "Create Clan", "href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act']. "&act2=createClan" )
            );
        }

        // Can join?
        if( $canJoin == true ){
            $options[] = array_merge( $_GET, array("name" => "Apply", "act2" => "apply", "cid" => "table.id") ) ;
        }

        // Set description
        $description = ($newDescription !== false) ? $newDescription : array('message'=>"Clans are gatherings of ninjas -
their orders are not subject to the rules of any of the kages, rather, each clan has a leader and
5 co-leaders who are in charge of anything that goes on in the clan. Accordingly, the only way to
get into a clan, is to be invited by one of these leading characters. Clan leaders must have the rank
of at least jounin and clan members must be of the same village!",'hidden'=>'yes');

        // Get users
        $squads = $GLOBALS['database']->fetch_data("
                SELECT *
                FROM  `clans`
                WHERE
                    `village`  = '" . $village . "'
                ORDER BY `activity` DESC
                LIMIT " . $min . ",10");

        // Show the table of users
        tableParser::show_list(
            'loyalty',
            'Village Clans',
            $squads,
            array(
                'name' => "Name",
                'rank' => "Clan Rank",
                'activity' => "Clan Points"
            ),
            $options,
            true, // Send directly to contentLoad
            true, // No newer/older links
            $topOptions, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            $description
        );

        // Return Link
        $GLOBALS['template']->assign("returnLink", true);
    }

    // Show the clan agenda
    public function showAgenda( $clanID ) {

        // Get & check clan
        $clan = $this->getClan($clanID);
        $GLOBALS['page']->Message( functions::parse_BB($clan[0]['orders']) , 'Clan System', 'id='.$_GET['id'],'Return');
    }

    // Show the main clain menu
    public function showMenu() {

        // Get the clan & check its existence
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

        // Create the menu
        $menu = array(
            array("name" => "Clan Chat", "href" => "?id=94"),
            array("name" => "Clan Agenda", "href" => "?id=" . $_GET['id'] . "&act2=agenda"),
            array("name" => "Clan Status", "href" => "?id=" . $_GET['id'] . "&act2=clanStatus"),
            array("name" => "Clan Jutsu", "href" => "?id=" . $_GET['id'] . "&act2=clanjutsu"),
            array("name" => "Other Clans", "href" => "?id=" . $_GET['id'] . "&act2=otherclans"),
            array("name" => "Resign", "href" => "?id=" . $_GET['id'] . "&act2=resign")
        );

        // Add extras
        if( $this->isLeader() || $this->isCoLeader() ){
            $menu[] = array("name" => "Edit Agenda", "href" => "?id=" . $_GET['id'] . "&act2=editagenda");
            $menu[] = array("name" => "Clan Upgrades", "href" => "?id=" . $_GET['id'] . "&act2=points");
            $menu[] = array("name" => "Edit Signature", "href" => "?id=" . $_GET['id'] . "&act2=editsignature");
            $menu[] = array("name" => "User Applications", "href" => "?id=" . $_GET['id'] . "&act2=applications");
        }

        // Add extra extras
        if( $this->isLeader() ){
            $menu[] = array("name" => "Kick User", "href" => "?id=" . $_GET['id'] . "&act2=kick");
            $menu[] = array("name" => "Manage Coleaders", "href" => "?id=" . $_GET['id'] . "&act2=manageleaders");
            $menu[] = array("name" => "Kage Support", "href" => "?id=" . $_GET['id'] . "&act2=support");
        }

        // Show the menu
        $GLOBALS['template']->assign('subHeader', 'Clan System');
        $GLOBALS['template']->assign('nCols', 3);
        $GLOBALS['template']->assign('nRows', ceil(count($menu)/3) );
        $GLOBALS['template']->assign('subTitle', "You are part of the clan <b>".$this->clan[0]['name']."</b>");
        $GLOBALS['template']->assign('linkMenu', $menu);
        $GLOBALS['template']->assign('buttonLayout', "SUBMIT");
        $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');
    }

    // Show chat
    protected function showChat(){

        // Get libraries
        require_once(Data::$absSvrPath.'/libs/chatSystem/chatLib.inc.php');
        require_once(Data::$absSvrPath.'/ajaxLibs/staticLib/markitup.bbcode-parser.php');

        // Get the clan & check its existence
        $this->clan = $this->getClan($GLOBALS['userdata'][0]['clan']);

        // Instantiate chat class
        $clanChat = new chatLib('tavern_clan');

        $clanChat->setupChatSystem(
            array(
                "userTitleOverwrite" => $clanChat->getUserRank("Clan Leader"),
                "tavernTable" => "tavern_clan",
                "tableColumn" => "clan_name",
                "tableSelect" => $GLOBALS['userdata'][0]['clan'],
                "chatName" => $this->clan[0]['name']." Chat",
                "smartyTemplate" => "contentLoad"
            )
        );

        // Wrap the contentLoad in a page wrapper with this javascript library
        if($GLOBALS['mf'] == 'yes')
                    $GLOBALS['page']->createPageWrapper("./content/tavern/Scripts/chatScripts_mf.js");
                else
                    $GLOBALS['page']->createPageWrapper("./content/tavern/Scripts/chatScripts.js");

    }

    // Resign from clan squad form
    protected function showResign() {

        // Get the clan & check its existence
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

        // Get usernames for the squad (only members for which usernames are attached are actually part of the ANBU still)
        $this->clan = $this->getClanUsernames( $this->clan );

        // Show the form
        $GLOBALS['template']->assign('isLeader', $this->isLeader() );
        $GLOBALS['template']->assign('hasMembers', $this->hasMembers() );
        $GLOBALS['template']->assign('clan', $this->clan[0]);
        $GLOBALS['template']->assign('contentLoad', './templates/content/clan/clan_resignform.tpl');
    }

    // Clan Agenda Form
    protected function showAgendaForm() {

        // Get the clan
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

        // Check that the user is the leader
        if( $this->isLeader() || $this->isCoLeader() ){
            $GLOBALS['page']->UserInput(
                    "Write the agenda for your clan in the field below.",
                    "Edit Clan Agenda",
                    array(
                        array("infoText"=>"",
                              "inputFieldName"=>"orders",
                              "type"=>"textarea",
                              "inputFieldValue"=> $this->clan[0]['orders'],
                              "maxlength" => 1500
                        )
                    ),
                    array(
                        "href"=>"?id=".$_GET['id']."&act2=".$_GET['act2'] ,
                        "submitFieldName"=>"Submit",
                        "submitFieldText"=>"Submit Agenda"),
                    "Return"
             );
        }
        else{
            throw new Exception("You have to be either a leader or coleader to edit the agenda.");
        }
    }

    // Show the application to the user
    protected function showApplication( $application ){

        // Check that the clan is valid
        $this->clan = $this->getClan($application[0]['clan_id']);

        // Get the votes
        list($yes,$no) = $this->getVotes( $application );

        // Get the number of leaders/coleaders, figure out if accepted
        $leaderNumber = $this->hasLeader() ? 1 : 0;
        if( $yes >= $this->getNumberOfColoaders()+$leaderNumber || $yes >= 3 ){

            // Set the clan data for the user
            $this->updateClanData($_SESSION['uid'], $this->clan[0]['id']);

            // Remove the application
            $this->removeApplication( $application[0]['id'] );

            // Message
            $GLOBALS['page']->Message( "You have been accepted as a member of the clan: ".$this->clan[0]['name'] , 'Clan System', 'id='.$_GET['id'],'Continue');
        }
        else{
            // Show the message
            $GLOBALS['page']->Message( "You are applying for the clan ".$this->clan[0]['name']. ".
                                        <br> 3 out of 5 co-leaders need to accept your application.
                                        <br>So far you have ".$yes." yes-votes and ".$no." no-votes!" , 'Clan System', 'id='.$_GET['id']."&act2=stopApplication",'Cancel Application');
        }
    }

    // Show applications to leaders, and allow them to vote
    protected function showApplications(){

        // Get the clan
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

        // Check that user is leader/coleader
        if( $this->isCoLeader() || $this->isLeader() ){

            // Handle all voting
            if( isset($_GET['vid']) && isset($_GET['vote']) ){
                $this->updateVoting( $_GET['vote'], $_GET['vid'] );
            }

            $min =  tableParser::get_page_min();

            // Get the column of the leader
            $key = $this->getClanMemberID( $GLOBALS['userdata'][0]['id'] );

            // Get users
            $applications = $GLOBALS['database']->fetch_data("
                    SELECT
                        `clan_applications`.*,
                        `users`.`username`,
                        `users_statistics`.`rank`
                    FROM  `clan_applications`, `users`, `users_statistics`
                    WHERE
                        `clan_applications`.`applicant_uid` = `users`.`id` AND
                        `users_statistics`.`uid` = `users`.`id` AND
                        `clan_applications`.`clan_id`  = '" . $this->clan[0]['id'] . "' AND
                        `clan_applications`.`".$key."_vote` = '0'
                    LIMIT " . $min . ",10");

            // Set the options
            $options = array(
                array_merge( $_GET, array("name" => "Vote Yes", "act2" => $_GET['act2'], "vid" => "table.id", "vote" => "yes") ) ,
                array_merge( $_GET, array("name" => "Vote No", "act2" => $_GET['act2'], "vid" => "table.id", "vote" => "no") )
            );

            // Show the table of users
            tableParser::show_list(
                'applications',
                'Clan Applications',
                $applications,
                array(
                    'username' => "Username",
                    'rank' => "User Rank"
                ),
                $options,
                true, // Send directly to contentLoad
                true, // No newer/older links
                false, // No top options links
                false, // No sorting on columns
                false, // No pretty options
                false, // No top search field
                "These are the users applying to come into the clan. As a leader, it is your job to give them a yes/no vote for joining"
            );

            // Return Link
            $GLOBALS['template']->assign("returnLink", true);

        }
        else{
            throw new Exception("Only leaders and coleaders can access this page");
        }
    }

    // Form for kicking a user out of the clan. Only available for leader and coleaders
    protected function showKickForm() {

        // Get the clan
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

        // Check that user is leader/coleader
        if( $this->isCoLeader() || $this->isLeader() ){

            $GLOBALS['page']->UserInput(
                    "Kick a user out of the clan.",
                    "Kick Out Member",
                    array(
                        array("infoText"=>"Enter username",
                              "inputFieldName"=>"username",
                              "type" => "input",
                              "inputFieldValue" => "")
                    ),
                    array(
                        "href"=>"?id=".$_GET['id']."&act2=".$_GET['act2'] ,
                        "submitFieldName"=>"Submit",
                        "submitFieldText"=>"Kick"),
                    "Return"
             );
        }
        else{
            throw new Exception("Only leaders and coleaders can access this page");
        }
    }

    // Form for editing the co-leaders
    protected function showLeaderEditForm() {

        // Get the clan
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

        // Attach usernames to the coleaders
        $this->clan = $this->getClanUsernames( $this->clan );

        // Check that user is leader/coleader
        if( $this->isLeader() ){

            // Show an input form that allows the leader to edit the names of all the coleaders
            $inputFields = array();
            for( $i=1; $i<=5; $i++ ){
                $inputFields[] = array("infoText"=>"Co-Leader ".$i,"inputFieldName"=>"coleader".$i."_uid", "type" => "input","inputFieldValue"=> $this->clan[0]['coleader'.$i."_uid_username"] );
            }

            // Show user prompt
            $GLOBALS['page']->UserInput(
                "As the leader you can manage the co-leaders of the clan.", // Information
                "Manage Co-Leaders", // Title
                $inputFields, // input fields
                array("href" => "?id=" . $_GET['id'] . "&act2=" . $_GET['act2'], "submitFieldName" => "Submit","submitFieldText" => "Submit Change"), // Submit button
                "Return" // Return link name
            );

        }
        else{
            throw new Exception("Only the clan leader can access this page");
        }
    }

    // Form for changing the clan signature
    protected function showSignatureForm() {

        // Get the clan
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

        // Check that user is leader/coleader
        if( $this->isLeader() || $this->isCoLeader() ){

            // Get the signature
            $image = functions::getUserImage('/clansigs/', $this->clan[0]['id']);

            // Get the fileuploadlibrary
            require_once(Data::$absSvrPath.'/global_libs/General/fileUploads.php');
            fileUploader::uploadForm(array(
                "maxsize" => "100kb",
                "subTitle" => "Clan Signature",
                "image" => $image,
                "description" => "Upload a new signature image for your clan.",
                "dimX" => 234,
                "dimY" => 60
            ));

            // Return Link
            $GLOBALS['template']->assign("returnLink", true);

        }
        else{
            throw new Exception("Only the clan leader can access this page");
        }
    }

    // Function showing how clan leader can user clan points
    protected function showPointMenu() {

        // Get the clan
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

        // Check that user is leader/coleader
        if( $this->isLeader() || $this->isCoLeader() ){

            // Clan points on template
            $GLOBALS['template']->assign('totalPoints', $this->clan[0]['points'] );

            // Calculate prices
            $updates = $this->getAvailableUpgrades();
            foreach( $updates as $key => $value ){
                if( isset($value['time']) && $value['time'] > 0 ){
                    $updates[ $key ]['timer'] = functions::convert_time( $value['time'] , $key.'timer', 'false');
                }
            }
            $GLOBALS['template']->assign('updates', $updates);

            // Get the template
            $GLOBALS['template']->assign('contentLoad', './templates/content/clan/clan_points.tpl');

        }
        else{
            throw new Exception("Only the clan leader can access this page");
        }
    }

    // Show the clan jutsu, if it's set
    protected function showClanJutsu(){

        // Get the clan
        $clan = $this->getClan($GLOBALS['userdata'][0]['clan']);

        // Check if a jutsu has been set
        if( !empty($clan[0]['clan_jutsu']) ){

            // Show the jutsu
            require_once(Data::$absSvrPath.'/libs/jutsuSystem/jutsuFunctions.php');
            $jutsuLib = new jutsuBasicFunctions();
            $jutsuLib->show_details( $clan[0]['clan_jutsu'] , true );

        }
        else{
            throw new Exception("The clan jutsu has not yet been unlocked. More points must be earned, and then the clan leaders can unlock the jutsu.");
        }
    }

    // Show the clan jutsu, if it's set
    protected function showKageSupport(){

        // Get the clan
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

        // Check that user is leader/coleader
        if( $this->isLeader() ){

            // Get the clan
            $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan']  );
            $GLOBALS['template']->assign('clan', $this->clan[0] );

            // Get the total members in clan
            $this->countMembers = cachefunctions::getClanCount($this->clan[0]['id']);
            $this->countMembers =  $this->countMembers[0]['count'];
            $GLOBALS['template']->assign('clanUsers', $this->countMembers );

            // Get user rank in clan
            $this->setClanStatusInformation();
            $GLOBALS['template']->assign('avgPoints', $this->avgPoints );
            $GLOBALS['template']->assign('userPoints', $this->userPoints );
            $GLOBALS['template']->assign('userClanRank', $this->rank );

            // Set signature to smarty
            $image = functions::getUserImage('/clansigs/', $this->clan[0]['id']);
            $GLOBALS['template']->assign('signature', $image );

            // Get number of clans in village, and of opposing clans
            $influence = $this->getKageInfluence( $GLOBALS['userdata'][0]['village'] );
            $GLOBALS['template']->assign('kageInfluence', $influence );

            // Show the smarty template
            $GLOBALS['template']->assign('contentLoad', './templates/content/clan/clan_kageSupport.tpl');

        }
        else{
            throw new Exception("Only the clan leader can access this page.");
        }
    }

    // ** BELOW ARE FUNCTIONS PERFORMING ACTIONS ** //

    // Do resign user from clan
    public function doResign( $userID ) {

        // Start transaction
        $GLOBALS['database']->transaction_start();

        // Get the clan & check its existence
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] , true );

        // Get usernames for the squad (only members for which usernames are attached are actually part of the ANBU still)
        $this->clan = $this->getClanUsernames( $this->clan );

        // Update the user already here
        if( $this->updateClanData($userID, "_none" ) ){

            // Find a new leader using the post suggestion
            if( $this->isLeader() ){
                $this->getNewLeader();
            }
            elseif(
                $key = $this->getClanMemberID( $userID )
            ){
                $GLOBALS['database']->execute_query("
                    UPDATE `clans`
                    SET `" . $key . "` = '0'
                    WHERE `id` = '" . $this->clan[0]['id'] . "'
                    LIMIT 1"
                );
            }

            // Message to user
            $GLOBALS['page']->Message('You have resigned from your clan.', 'Clan System', 'id=' . $_GET['id'] . '');

            // Commit transaction
            $GLOBALS['database']->transaction_commit();

        }
        else{
            throw new Exception("There was an error changing your clan status");
        }
    }

    // Do edit agenda
    protected function doEditAgenda() {

        // Get the clan
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

        // Check that the user is the leader
        if( $this->isLeader() || $this->isCoLeader() ){

            // Check orders
            if (isset($_POST['orders'])) {
                if (strlen($_POST['orders']) < 1500) {

                    $GLOBALS['database']->execute_query("UPDATE `clans` SET `orders` = '" . functions::store_content($_POST['orders']) . "' WHERE `id` = '" . $this->clan[0]['id'] . "' LIMIT 1");
                    $GLOBALS['page']->Message("The agenda has been updated", 'Clan System', 'id=' . $_GET['id']);

                } else {
                    throw new Exception( "Your orders are too long." );
                }
            } else {
                throw new Exception( "No orders were specified." );
            }
        } else {
            throw new Exception("You have to be either a leader or coleader to edit the agenda.");
        }
    }

    // Do apply for membership
    protected function doAddApplication() {

        if( !$this->isUserClan($GLOBALS['userdata'][0]['clan']) ){

            // Check the clan
            $clan = $this->getClan( $_GET['cid'] );

            // Check that the user is not already appying somewhere
            if( !$this->getUserApplication($_SESSION['uid']) ){

                // Insert application in database
                $GLOBALS['database']->execute_query("
                    INSERT INTO `clan_applications` ( `clan_id` , `applicant_uid` , `time` )
                    VALUES ( '" . $clan[0]['id'] . "', '" . $_SESSION['uid'] . "', '" . $GLOBALS['user']->load_time . "');");

                // Message to user
                $GLOBALS['page']->Message('You have applied to be a member of this clan.', 'Clan System', 'id=' . $_GET['id'] . '');
            }
            else{
                throw new Exception("You can only apply for one clan at a time");
            }
        }
        else{
            throw new Exception("You are already in a clan.");
        }
    }

    // Do apply for membership
    protected function doCancelApplication() {

        // Check that the user is not already appying somewhere
        if( $application = $this->getUserApplication($_SESSION['uid']) ){

            // Insert application in database
            $this->removeApplication( $application[0]['id'] );

            // Message to user
            $GLOBALS['page']->Message('You have cancelled your application for this clan', 'Clan System', 'id=' . $_GET['id'] . '');
        }
        else{
            throw new Exception("You not applying for clan membership, so you cannot cancel your application.");
        }
    }

    // Do kick the user out of the clan
    protected function doKickUser() {

        // Check the username is set
        if (isset($_POST['username']) && $_POST['username'] != '') {

            // Get the clan
            $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

            // Check that user is leader/coleader
            if( $this->isCoLeader() || $this->isLeader() ){

                // Get the user
                $user = $GLOBALS['database']->fetch_data("SELECT `id`,`clan` FROM `users`,`users_preferences` WHERE `username` = '" . $_POST['username'] . "' AND `users_preferences`.`uid` = `users`.`id` AND `clan` = '".$this->clan[0]['id']."' LIMIT 1");
                if ($user != '0 rows') {

                    // Check that it's not a leader/coleader
                    if( !$this->isCoLeader($user[0]['id']) && !$this->isLeader($user[0]['id']) ){

                        // Set user data
                        $this->updateClanData($user[0]['id'], "_none");

                        // Message
                        $GLOBALS['page']->Message( "The user has been kicked out of the clan." , 'Clan System', 'id='.$_GET['id'],'Return');

                    }
                    else{
                        throw new Exception("You cannot kick a leader out of the clan.");
                    }
                }
                else {
                    throw new Exception("You cannot remove this user from his/her clan.");
                }
            }
            else{
                throw new Exception("Only leaders and coleaders can access this page");
            }
        }
        else {
            throw new Exception("No member was specified");
        }
    }

    // Claim leadership
    protected function claimLeader(){

        // Get the clan
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

        // Check for leader
        if( !$this->hasLeader() ){

            if( $GLOBALS['userdata'][0]['rank_id'] >= 4 ){

                // Query
                $query = "UPDATE `clans` SET `leader_uid` = '".$_SESSION['uid']."' WHERE `id` = '" . $this->clan[0]['id'] . "' LIMIT 1";
                $GLOBALS['Events']->acceptEvent('clan_leader', array('data'=>$this->clan[0]['id']));

                // Update the database
                $GLOBALS['database']->execute_query($query);

                // Message to user
                $GLOBALS['page']->Message('You have claimed the leader position.', 'Clan System', 'id=' . $_GET['id'] . '');

            }
            else{
                throw new Exception("Your rank is not high enough for claiming clan leadership");
            }
        }
        else{
            throw new Exception("This clan already has a leader. You can only claim leadership if the spot is empty.");
        }
    }

    // Do modify the co-leader setup
    protected function doUpdateLeaders() {

        // Get the clan
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

        // Attach usernames to the coleaders
        $this->clan = $this->getClanUsernames( $this->clan );

        // Check that user is leader/coleader
        if( $this->isLeader() ){

            $changes = array();
            foreach( $_POST as $key => $value ){
                if( preg_match("/^coleader[1-5]_uid$/", $key) ){
                    if(
                       ($this->clan[0][ $key."_username" ] == "N/A" && $value !== "N/A") ||
                       ($this->clan[0][ $key."_username" ] !== "N/A" && ($value == "N/A" || $value == ""))
                    ){
                         $changes[ $key ] = $value;
                    }
                }
            }

            // Check that some changes are present
            if( !empty($changes) ){

                // Get user IDs for the username
                $users = $GLOBALS['database']->fetch_data( "SELECT `id`,`username`,`clan`,`rank_id` FROM  `users`, `users_preferences`, `users_statistics` WHERE `username` IN ('".implode( "','", $changes)."') AND `users_preferences`.`uid` = `id` AND `clan` = '".$this->clan[0]['id']."' AND `users_preferences`.`uid` = `users_statistics`.`uid` LIMIT ".count($changes) );
                if( $users !== "0 rows" ){

                    // Go through all the retrieved user IDs
                    foreach( $users as $user ){

                        // Check rank
                        if( $user['rank_id'] > 3 ){

                            // For each user, find the entry in changes array it matches
                            foreach( $changes as $key => $value ){

                                // Make sure the ID doesn't match the ID of the leader
                                if( $user['id'] !== $_SESSION['uid'] ){
                                    if( $changes[$key] == $user['username'] ){
                                        $changes[$key] = $user['id'];
                                    }
                                }
                                else{
                                    throw new Exception("You cannot assign yourself as coleader.");
                                }
                            }
                        }
                        else{
                            throw new Exception($user['username']. " is not yet jounin.");
                        }
                    }
                }

                // Create the query
                $query = "";
                foreach( $changes as $key => $value){
                    if( !empty($key) ){
                        $query .= ($query == "") ? "`".$key."` = '".$value."'" :  ", `".$key."` = '".$value."'";
                    }
                }

                // Run the update if data was set.
                if( !empty($query) ){
                    $query = "UPDATE `clans` SET ".$query." WHERE `id` = '" . $this->clan[0]['id'] . "' LIMIT 1";
                    $GLOBALS['database']->execute_query($query);
                }


                // Message to user
                $GLOBALS['page']->Message('You have successfully changed the ordering of clan leaders.', 'Clan System', 'id=' . $_GET['id'] . '');

            }
            else{
                throw new Exception("No changes to the current configuration was registered");
            }

        }
        else{
            throw new Exception("Only the clan leader can access this page");
        }
    }

    // Fix up errors
    protected function doFixLeaderPositionErrors(){

        // Loop through the clan positions and get the uIDs
        $removeKeys = array();
        $usedUIDs = array( $this->clan[0]['leader_uid'] );

        // Go through all the coleaders
        foreach( $this->clan[0] as $column => $value ){
            if( preg_match("/coleader[1-5]_uid/", $column ) ){
                if(is_numeric($value) && $value > 0 ){
                    if( in_array($value, $usedUIDs) ){
                        $removeKeys[] = $column;
                    }
                    else{
                        $usedUIDs[] = $value;
                    }
                }
            }
        }

        // Do remove coleaders if they were set
        if( !empty($removeKeys) ){

            // Create the query
            $query = "";
            foreach( $removeKeys as $column ){
                $query .= ($query == "") ? "`".$column."` = NULL" :  ", `".$key."` = NULL";
            }
            $query = "UPDATE `clans` SET ".$query." WHERE `id` = '" . $this->clan[0]['id'] . "' LIMIT 1";

            // Update the database
            $GLOBALS['database']->execute_query($query);
        }
    }

    // Do change the signature
    protected function doChangeSignature() {

        // Get the clan
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

        // Check that user is leader/coleader
        if( $this->isLeader() || $this->isCoLeader() ){

            // Get the fileuploadlibrary
            require_once(Data::$absSvrPath.'/global_libs/General/fileUploads.php');
            $upload = fileUploader::doUpload(array(
                "maxsize" => 102400,
                "destination" => 'clansigs/',
                "filename" => $this->clan[0]['id'],
                "dimX" => 234,
                "dimY" => 60
            ));

            // Message to user

            if( $upload == true ){
                $GLOBALS['page']->Message('You have successfully uploaded the clan signature image.', 'Clan System', 'id=' . $_GET['id'] . '');
            }

        }
        else{
            throw new Exception("Only the clan leader can access this page");
        }
    }

    // Spend the points
    protected function doSpendPoints() {

        // Get the clan
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

        // Check that user is leader/coleader
        if( $this->isLeader() || $this->isCoLeader() ){

            // Check for main point purchases
            if (isset($_POST['radio'])) {

                // Calculate prices
                $updates = $this->getAvailableUpgrades();

                // Go through updates and see if radio-submission matches any of them
                $foundUpdate = false;
                foreach( $updates as $key => $value ){
                    if( $_POST['radio'] == $key ){

                        // Save price
                        $price = $value['price'];

                        // We found the udpate, yay
                        $foundUpdate = true;

                        // Check the udpate
                        if ( $this->clan[0]['points'] >= $price ) {

                            // Update & Set message
                            $message = $query = " `points` = `points` - '".$price."', ";
                            switch( $key ){
                                case "diplomacy":
                                    $query .= "`diplomacy_increase` = '".($GLOBALS['user']->load_time+24*3600)."'";
                                    $message = "You have purchased the increased diplomacy upgrade";
                                break;
                                case "hospital":
                                    $query .= "`hospital_reduction` = '".($GLOBALS['user']->load_time+7*24*3600)."'";
                                    $message = "You have purchased the hospital cost reduction upgrade";
                                break;
                                case "ramen":
                                    $query .= "`clan_ramen_shop` = '".($GLOBALS['user']->load_time+7*24*3600)."'";
                                    $message = "You have purchased the ramen shop discount";
                                break;
                                case "jutsu":
                                    $jutsus = $GLOBALS['database']->fetch_data("SELECT `id` FROM `jutsu` WHERE `jutsu_type` = 'clan' AND `element` = '".$this->clan[0]['element']."' LIMIT 1");
                                    if( $jutsus !== "0 rows" ){
                                        $query .= "`clan_jutsu` = '".$jutsus[0]['id']."'";
                                        $message = "You have unlocked the clan jutsu";
                                    }
                                    else{
                                        throw new Exception("Could not find an applicable jutsu in the database");
                                    }
                                break;
                            }

                            // Finish & load query
                            $GLOBALS['database']->execute_query("UPDATE `clans` SET " . $query . " WHERE `id` = '" . $this->clan[0]['id'] . "' LIMIT 1");

                            // Message for the user
                            $GLOBALS['page']->Message($message, "Clan Upgrades", 'id=' . $_GET['id'] . "&act2=" . $_GET['act2'] );

                        } else {
                            throw new Exception("You do not have enough points for this transaction.");
                        }
                    }
                }

                // Check if the udpate was set
                if( $foundUpdate == false ){
                    throw new Exception("Could not identify the upgrade you're trying to update");
                }
            }
            else{
                throw new Exception("Could not intepret your request.");
            }
        }
        else{
            throw new Exception("Only the clan leader can access this page");
        }
    }

    //  Do kage challenge
    protected function doChallengeLeader() {

        // Get the clan
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

        // Get the total members in clan
        $this->countMembers = cachefunctions::getClanCount($this->clan[0]['id']);
        $this->countMembers =  $this->countMembers[0]['count'];

        // Set the user rank, and see if he can challenge
        $this->setClanStatusInformation();

        // Check if can challenge
        if(
            $this->canChallengeLeader == true &&
            $_SESSION['uid'] !== $this->clan[0]['leader_uid']
        ){

            // Check for PVP. Load battleInitiation library
            require_once(Data::$absSvrPath.'/libs/battleSystem/battleInitiation.php');
            $battleInitiator = new battleInitiation();

            // Set the target & type of battle
            $battleInitiator->setTarget( $this->clan[0]['leader_uid'] );
            $battleInitiator->setType( "clan" );

            // Instantiate the battle
            $battleInitiator->initiate_fight();

        }
        else{
            throw new Exception( "You are not eligible to challenge the clan leader" );
        }
    }

    // Show the clan jutsu, if it's set
    protected function doUpdateKageSupport(){

        // Get the clan
        $this->clan = $this->getClan( $GLOBALS['userdata'][0]['clan'] );

        // Check that user is leader/coleader
        if( $this->isLeader() ){

            // Update the clan depending on what was set
            $newStatus = ( $_POST['Submit'] == "Oppose Kage" ) ? "oppose" : "support";
            if( $newStatus !== $this->clan[0]['kage_vote'] ){
                $GLOBALS['database']->execute_query("UPDATE `clans` SET `kage_vote` = '" . $newStatus . "' WHERE `id` = '".$this->clan[0]['id'] ."' LIMIT 1");
            }

            // Get the current kage influence
            $influence = $this->getKageInfluence( $GLOBALS['userdata'][0]['village'] );
            if( $influence > 0 ){

                // Message
                $GLOBALS['page']->Message( "You have updated your position to ".$newStatus." the current kage" , 'Clan System', 'id='.$_GET['id'],'Return');


            }
            else{

                // Remove kage
                if(($GLOBALS['database']->execute_query('
                    UPDATE `villages`, `users`, `users_loyalty`
                    SET `villages`.`leader` = "'.Data::$VILLAGE_KAGENAMES[ $GLOBALS['userdata'][0]['village'] ].'",
                        '."`notifications` = CONCAT('id:17;duration:none;text:The Village Kage, ".$GLOBALS['userdata'][0]['leader'].", was fired because the majority of the village clans opposed his/her rule!;dismiss:yes;buttons:none;select:none;//',`notifications`)".'
                    WHERE `villages`.`name` = "'.$GLOBALS['userdata'][0]['village'].'"
                        AND `users_loyalty`.`village` = `villages`.`name`
                        AND `users`.`id` = `users_loyalty`.`uid`')) === false)
                {
                    throw new Exception('There was an error updating the '.$this->cpName);
                }
                else
                {
                    $events = new Events($GLOBALS['userdata'][0]['leader']);
                    $events->acceptEvent('kage', array('data'=>'removed'));
                    $events->closeEvents();
                }

                // Set all village clans to support
                $GLOBALS['database']->execute_query("UPDATE `clans` SET `kage_vote` = 'support' WHERE `village` = '". $GLOBALS['userdata'][0]['village'] ."'");

                // Message
                $GLOBALS['page']->Message( "You have updated your position toward the current kage. This has resulted in him being removed from his position." , 'Clan System', 'id='.$_GET['id'],'Return');
            }
        }
        else{
            throw new Exception("Only the clan leader can access this page.");
        }
    }

    // ** BELOW ARE CONVENIENCE FUNCTIONS USED ALL AROUND ** //

    // Get the rank of the user in the clan
    protected function setClanStatusInformation(){

        // Get Required user information
        $userInfo = $GLOBALS['database']->fetch_data( "
                    SELECT `clan_activity`
                    FROM `users_missions`
                    WHERE `userid` = '" . $_SESSION['uid']. "'
                    LIMIT 1"
        );
        if( $userInfo !== "0 rows" ){

            // Retrieve and calculate things
            $this->avgPoints = floor( $this->countMembers / ($this->clan[0]['activity']+1) );
            $this->userPoints = $userInfo[0]['clan_activity'];

            // Decide on rank
            $this->rank = "";
            $this->canChallengeLeader = false;
            switch( true ){
                case $this->userPoints < (0.15*$this->avgPoints):
                    $this->rank = "Starter";
                break;
                case $this->userPoints < (0.35*$this->avgPoints):
                    $this->rank = "Uprising";
                break;
                case $this->userPoints < (0.75*$this->avgPoints):
                    $this->rank = "Influential";
                break;
                case $this->userPoints > (0.75*$this->avgPoints):
                    $this->rank = "Elite";
                    $this->canChallengeLeader = true;
                break;
            }
        }
        else{
            throw new Exception("There was an error retrieving user data from the mission table.");
        }
    }

    // Get the kage influence, fresh
    public function getKageInfluence( $village ){

        // Get number of clans in village, and of opposing clans
        $total = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) as count FROM `clans` WHERE `village` = '" . $village . "'");
        $opposing = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) as count FROM `clans` WHERE `village` = '" . $village . "' AND `kage_vote` = 'oppose'");

        // Get counts
        $influence = $total[0]['count'] - 2*$opposing[0]['count'];
        return $influence;
    }


    //  Calculate prices of the different updates
    public function getAvailableUpgrades() {

        // Array to store information
        $updatePrices = array();

        // Diplomacy
        $price = ($this->clan[0]['diplomacy_increase'] < $GLOBALS['user']->load_time) ? 1000 : 0;
        $updatePrices["diplomacy"] = array(
            "name" => "Increase diplomacy gain by 15%",
            "price" => $price,
            "time" => $this->clan[0]['diplomacy_increase'] - $GLOBALS['user']->load_time
        );

        // Hospital
        $price = ($this->clan[0]['hospital_reduction'] < $GLOBALS['user']->load_time) ? 1000 : 0;
        $updatePrices["hospital"] = array(
            "name" => "10% reduced hospital cost",
            "price" => $price,
            "time" => $this->clan[0]['hospital_reduction'] - $GLOBALS['user']->load_time
        );

        // Ramen
        $price = ($this->clan[0]['clan_ramen_shop'] < $GLOBALS['user']->load_time) ? 1000 : 0;
        $updatePrices["ramen"] = array(
            "name" => "20% Cheaper Ramen",
            "price" => $price,
            "time" => $this->clan[0]['clan_ramen_shop'] - $GLOBALS['user']->load_time
        );

        // Jutsu
        $price = empty($this->clan[0]['clan_jutsu']) ? 1000 : 0;
        $updatePrices["jutsu"] = array(
            "name" => "Unlock Clan Jutsu",
            "price" => $price
        );

        // Return the options
        return $updatePrices;
    }

    // Get the number of active coleader in clan
    private function getNumberOfColoaders(){
        $number = 0;
        foreach( $this->clan[0] as $key => $value ){
            if( preg_match("/(^coleader[1-5]_uid$)/", $key) ){
                if( isset( $value ) && ctype_digit($value) && $value > 0 ){
                    $number++;
                }
            }
        }
        return $number;
    }

    // Calculate the number of votes for the user
    private function getVotes( $application ){
        $yes = $no = 0;
        foreach( $application[0] as $key => $value ){
            if( preg_match("/leader/", $key) && is_numeric($value) ){
                if( $value == 1 ){
                    $yes += 1;
                }elseif( $value == -1 ){
                    $no += 1;
                }
            }
        }
        return array($yes, $no);
    }

    // Get user application
    protected function getUserApplication( $uid ){
        $query = "SELECT * FROM  `clan_applications` WHERE `applicant_uid` = '".$uid."' LIMIT 1";
        $application = $GLOBALS['database']->fetch_data( $query );
        if( $application !== "0 rows" ){
            return $application;
        }
        return false;
    }

    // Get informatiom about the clan
    public function getClan( $id , $lock = false ){

        if( isset($id) && is_numeric($id) && $id > 0 ){

            $query = "
                SELECT `clans`.*, `users`.`username` as `leaderName`
                FROM `clans`
                LEFT JOIN `users` ON (`users`.`id` = `clans`.`leader_uid`)
                WHERE `clans`.`id` = '".$id."'
                LIMIT 1";
            if( $lock == true ){
                $query .= " FOR UPDATE";
            }
            $clan = $GLOBALS['database']->fetch_data( $query );
            if( $clan == "0 rows" ){

                // Throw exception for further handling
                throw new Exception("Could not identify the clan with ID: ".$id.". User clan id was: ".$GLOBALS['userdata'][0]['clan']);
            }
            return $clan;
        }
        else{
            throw new Exception("Invalid clan ID specified");
        }
    }

    // Get user ID => position array of clan
    protected function getClanPositions(){
        $returnArray = array();
        foreach( $this->clan[0] as $key => $value ){
            if( preg_match("/(_uid$)/", $key) ){
                if( isset( $value ) && is_numeric($value) && $value > 0 ){
                    if( stristr( $key, "coleader" ) ){
                        $returnArray[ $value ] = "Coleader";
                    }
                    elseif( $key == "leader_uid" ){
                        $returnArray[ $value ] = "Leader";
                    }
                }
            }
        }
        return $returnArray;
    }


    // Get Usernames for Clan members based on their IDs
    protected function getClanUsernames( $clan ){

        // Variable to indicate whether clan has any users at all
        $hasUsers = false;

        // Loop through squad and get the usernames
        $ids = array();
        foreach( $clan[0] as $key => $value ){
            if( preg_match("/(_uid$)/", $key) ){
                $clan[0][ $key . "_username" ] = "N/A";
                if( isset( $value ) && ctype_digit($value) && $value > 0 ){
                    $ids[] = $value;
                    $hasUsers = true;
                }
            }
        }

        // Create select query
        if( $hasUsers == true ){
            $query = "";
            foreach( $ids as $id ){
                $query .= ($query == "") ? "`id` = ".$id :  " OR `id` = ".$id;
            }
            $query = "
                    SELECT `id`, `last_activity`, `username`
                    FROM `users`,`users_timer`,`users_preferences`
                    WHERE
                        `clan` = '" . $clan[0]['id'] . "' AND
                        `id` = `userid` AND
                        `id` = `uid` AND
                        (".$query.")
                    LIMIT ".count($ids);
            $members = $GLOBALS['database']->fetch_data( $query );
            if( $members == "0 rows" ){
                $hasUsers = false;
            }
        }

        // Assign usernames and last activities to squad array
        if( $hasUsers ){

            // An array for users no longer in database, which should be removed from ANBU squad
            $inactiveUsers = array();

            // Go through the squad positions and assign where appropriate
            foreach( $clan[0] as $key => $value ){

                // Only check the user IDs
                if( preg_match("/(_uid$)/", $key) && isset( $value ) && ctype_digit($value) && $value > 0 ){

                    // Go through the members loaded in the DB
                    $foundMember = false;
                    foreach( $members as $member ){
                        if( $member['id'] == $value ){
                            $foundMember = true;
                            $clan[0][ $key . "_username" ] = $member['username'];
                            $clan[0][ $key . "_last_activity" ] = $member['last_activity'];
                        }
                    }

                    // Member was not found for this position, so reset it
                    if( $foundMember == false ){
                        $inactiveUsers[] = $key;
                    }
                }
            }

            // If we found inactive/deleted users, then reset the clan db positions
            if( !empty($inactiveUsers) ){

                // Create and load the query
                $query = "";
                foreach( $inactiveUsers as $key ){
                    $query .= ( $query == "" ) ? `$key` . " = '0' " : "," . `$key` . " = '0' ";
                }
                if( !$GLOBALS['database']->execute_query("
                    UPDATE `clans`
                    SET " . $query . "
                    WHERE `id` = '" . $clan[0]['id'] . "'
                    LIMIT 1") )
                {
                    throw new Exception("There was an error removing users from the clan squad.");
                }

                // Update the squad information, so it'll load correctly
                foreach( $inactiveUsers as $key ){
                    $clan[0][$key] = 0;
                }
            }
        }

        // Return the squad
        return $clan;
    }

    // Set a new leader for the squad based on suggestion
    protected function getNewLeader( ){

        // Check is suggestions were submitted
        if( isset($_POST['newLeader']) && $_POST['newLeader'] !== ""){
            $memberIDsuggestion = $_POST['newLeader'];
        }

        // Convenience variables
        $dbEntry = $uid = ""; // The user to be removed from clan

        // Check if the clan has any coleaders to take over leader position
        if( $this->hasMembers() ){

            // Members remaining
            if (
                isset($memberIDsuggestion) &&
                $memberIDsuggestion > 0 &&
                $memberIDsuggestion <= 5
            ) {

                // Check that this ID is a member
                if(
                    $this->clan[0][ "coleader".$memberIDsuggestion."_uid" ] > 0 &&
                    isset( $this->clan[0][ "coleader".$memberIDsuggestion."_uid_username" ] )
                ){
                    list( $dbEntry, $uid ) = array( "coleader".$memberIDsuggestion."_uid" , $this->clan[0][ "coleader".$memberIDsuggestion."_uid" ] );
                }
                else{
                    list( $dbEntry, $uid ) = $this->getRandomMemberData();
                }
            } else {
                list( $dbEntry, $uid ) = $this->getRandomMemberData();
            }

            if( $dbEntry !== "" && $uid !== "" ){
                $GLOBALS['database']->execute_query("UPDATE `clans` SET `leader_uid` = `" . $dbEntry . "`, `$dbEntry` = 0 WHERE `".$dbEntry."` > 0 AND `id` = '" . $this->clan[0]['id'] . "' LIMIT 1");

                $events = new Events($dbEntry);
                $events->acceptEvent('clan_leader', array('data'=>$this->clan[0]['id']));
                $events->closeEvents();

                $events = new Events($this->clan[0]['leader_uid']);
                $events->acceptEvent('clan_leader', array('data'=>'removed'));
                $events->closeEvents();
            }
            else{
                throw new Exception("There was an error figuring out who should be the new leader of the clan.");
            }
        }
        else{
            $this->removeClan($this->clan[0]['id'], "No users to take leader");
        }
    }

    // Get the ID and key of a random user
    protected function getRandomMemberData(){
        foreach( $this->clan[0] as $key => $value ){
            if( preg_match("/(^coleader[1-5]_uid$)/", $key) ){
                if( isset( $value ) && ctype_digit($value) && $value > 0 ){
                    return array($key,$value);
                }
            }
        }
        return false;
    }

    // Get the clan ID of the user
    protected function getClanMemberID( $uid ){
        foreach( $this->clan[0] as $key => $value ){
            if( preg_match("/(^(coleader[1-5]_uid|leader_uid)$)/", $key) ){
                if( isset( $value ) && ctype_digit($value) && $value == $uid ){
                    return $key;
                }
            }
        }
        return false;
    }

    // Handle the voting
    private function updateVoting($vote, $applicationID){
        if( $vote == "yes" || $vote == "no" ){
            $application = $GLOBALS['database']->fetch_data( "SELECT * FROM  `clan_applications` WHERE `id` = '".$applicationID."' LIMIT 1" );
            if( $application !== "0 rows" ){

                // Get the column of the leader
                $key = $this->getClanMemberID( $GLOBALS['userdata'][0]['id'] );
                $vote = ($vote == "yes") ? 1 : -1;

                $GLOBALS['database']->execute_query("
                    UPDATE `clan_applications`
                    SET `" . $key . "_vote` = '".$vote."'
                    WHERE `id` = '" . $applicationID . "'
                    LIMIT 1");
            }
        }
    }

    // Set user to be part of a ANBU squad
    public function updateClanData( $uid, $clan ){
        if( !isset($clan) || $clan == "" ){
            $clan = "_none";
        }
        if( $GLOBALS['database']->execute_query("
                UPDATE `users`,`users_preferences`
                SET
                    `clan` = '".$clan."'
                WHERE
                    `uid` = '" . $uid . "' AND
                    `uid` = `id`")
        ){
            $users_notifications = new NotificationSystem('', $uid);

            $users_notifications->addNotification(array(
                                                        'id' => 12,
                                                        'duration' => 'none',
                                                        'text' => 'Your clan status has changed.',
                                                        'dismiss' => 'yes'
                                                    ));

            $users_notifications->recordNotifications();

            $GLOBALS['Events']->acceptEvent('clan', array('new'=>$clan, 'old'=>$GLOBALS['userdata'][0]['clan'] ));

            return true;
        }
        return false;
    }

    // Create new ANBU squad and return ID of said squad
    public function insertClan( $village, $name, $leaderID, $orders, $element = false ){

        // Get the clan lib
        require_once(Data::$absSvrPath.'/libs/elements/Elements.php');

        // Set element
        if ($element == false){
            $element = Elements::getRandomElement();
        }

        // Insert
        $GLOBALS['database']->execute_query("
            INSERT INTO `clans` ( `village` , `name` , `orders`, `leader_uid`, `clan_type`, `element` )
            VALUES ('" . $village . "', '" . $name . "', '" . $orders . "', '" . $leaderID . "', 'kage', '".$element."');");

        // Return the id
        return $GLOBALS['database']->get_inserted_id();
    }

    // Remove application
    private function removeApplication( $id ){
        $GLOBALS['database']->execute_query("DELETE FROM `clan_applications` WHERE `id` = '".$id."' LIMIT 1");
    }

    // Remove user application
    public function removeUserApplications( $uid ){
        $GLOBALS['database']->execute_query("DELETE FROM `clan_applications` WHERE `applicant_uid` = '".$uid."' ");
    }

    // Function for removing a clan
    protected function removeClan( $id , $reason = "No Reason"){

        // Remove the clan
        $GLOBALS['database']->execute_query("DELETE FROM `clans` WHERE `id` = '" . $id . "' AND `clan_type` != 'core' LIMIT 1");

        // Log the deletion
        functions::log_user_action($_SESSION['uid'], "deleteClan", $reason );

        // For all core clans, reset all leader positions
        $GLOBALS['database']->execute_query("
             UPDATE `clans`
             SET
                `coleader1_uid` = NULL,
                `coleader2_uid` = NULL,
                `coleader3_uid` = NULL,
                `coleader4_uid` = NULL,
                `coleader5_uid` = NULL,
                `leader_uid` = NULL
             WHERE
                `id` = '" . $id . "' AND
                `clan_type` = 'core'
             LIMIT 1");

        // Remove applications from this clan ID
        $GLOBALS['database']->execute_query("
            DELETE `clan_applications`.*
            FROM `clan_applications`
            LEFT JOIN `clans` ON `clan_applications`.`clan_id` = `clans`.`id`
            WHERE `clans`.`id` is null");

        // Update all users who are marked with this clan
        if( !isset($this->clan) || $this->clan[0]['clan_type'] !== "core"){
            $GLOBALS['database']->execute_query("UPDATE `users_preferences` SET `clan` = '_none' WHERE `clan` = '" . $id . "'");
            $GLOBALS['Events']->acceptEvent('clan', array('new'=>'_none', 'old'=>$GLOBALS['userdata'][0]['clan'] ));

        }

    }

    // Check if this user is an anbu
    public function isUserClan( $clanStatus ){
        if(
            $clanStatus !== '_none' &&
            $clanStatus !== '' &&
            $clanStatus !== '_disabled' &&
            ctype_digit( $clanStatus )
        ){
            return $clanStatus;
        }
        return false;
    }

    // Check if user is leader
    public function isLeader( $uid = false ){
        $uid = ($uid == false) ? $GLOBALS['userdata'][0]['id'] : $uid;
        if( $this->clan[0]['leader_uid'] == $uid ){
            return true;
        }
        return false;
    }

    // Check if user is leader
    public function isCoLeader( $uid = false ){
        $uid = ($uid == false) ? $GLOBALS['userdata'][0]['id'] : $uid;
        for( $i=1; $i<=5; $i++ ){
            if( $this->clan[0]['coleader'.$i."_uid"] == $uid ){
                return true;
            }
        }
        return false;
    }

    // Does the clan have a leader
    protected function hasLeader(){
        if( !empty($this->clan[0]['leader_uid']) ){
            return true;
        }
        return false;
    }

    // Check if the clan has members
    protected function hasMembers(){
        foreach( $this->clan[0] as $key => $value ){
            if( preg_match("/(^coleader[1-5]_uid$)/", $key) ){
                if( isset( $value ) && ctype_digit($value) && $value > 0 ){
                    return true;
                }
            }
        }
        return false;
    }

}