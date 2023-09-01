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

require_once(Data::$absSvrPath.'/global_libs/General/static.inc.php');

class marriage extends functions {

    // Vars
    private $user;
    private $married;
    private $marriage_results;
    private $location;

    function __construct() {

        // Try-Catch
        try {

            // Obtain lock
            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Get marriage data andrun general checks
            $this->married = $this->marriage_check();

            // Check if married
            if($this->married === true) {

                // Check for actions, and otherwise show main page
                if(isset($_GET['act']) && $_GET['act'] == "getDivorce") {
                    if (!isset($_REQUEST['Submit'])) {
                        $GLOBALS['page']->Confirm("Are you sure you want to file for a divorce?", 'Marriage System', 'File now!');
                    } else {
                        $this->delete_marriage();
                    }

                }
                else {
                    $this->main_page();
                }
            }
            elseif($this->married === false) {

                // Check for actions, and otherwise show main page
                if(isset($_REQUEST['Proposal_Submit']) && ($_REQUEST['Proposal_Submit'] === 'Send Proposal')) {
                    $this->do_proposal();
                }
                elseif(isset($_REQUEST['proposal_user_delete']) ){
                    $this->delete_proposal('decline');
                }
                elseif(isset($_REQUEST['remove_user_proposal']) ){
                    $this->delete_proposal('delete');
                }
                elseif(isset($_REQUEST['marriage_user_accept']) ){
                    $this->accept_proposal();
                }
                else {
                    $this->main_page();
                }
            }

            // Release lock
            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        }
        catch (Exception $e) {

            // Rollback transaction
            $GLOBALS['database']->transaction_rollback($e->getMessage());

            // Give a message
            $GLOBALS['page']->Message($e->getMessage(), 'Marriage System', 'id=2');
        }

    }

    private function marriage_check() {

        // Gather all Data about Marriage/Proposals to User at Once
        if(!($this->marriage_results = $GLOBALS['database']->fetch_data('
            SELECT
                `users`.`id`, `users`.`username`, `users`.`gender`, `users`.`status`, `users`.`post_ban`, `users`.`location`,
                `users_statistics`.`rank_id`, `users_statistics`.`rank`, `user_rank`,
                `users_loyalty`.`village`,
                `users_preferences`.`enable_marriage`,
                `marriages`.`uid`, `marriages`.`oid`, `marriages`.`married`, `marriages`.`time`, `marriages`.`mid`,
                `user_1_table`.`username` AS `user_1`, `user_2_table`.`username` AS `user_2`
            FROM `users`
                INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                INNER JOIN `users_preferences` ON (`users_preferences`.`uid` = `users`.`id`)
                LEFT JOIN `marriages` ON (`marriages`.`uid` = `users`.`id` OR `marriages`.`oid` = `users`.`id`)
                LEFT JOIN `users` AS `user_1_table` ON (`user_1_table`.`id` = `marriages`.`uid`)
                LEFT JOIN `users` AS `user_2_table` ON (`user_2_table`.`id` = `marriages`.`oid`)
            WHERE `users`.`id` = '.$_SESSION['uid'])))
        {
            throw new Exception('There was an error trying to receive marriage information');
        }

        // If nothing was found, then there was an error
        if($this->marriage_results === '0 rows') {
            throw new Exception('There was an error trying to receive user information');
        }

        // Push first row for User Data Tailoring
        $this->user[0] = $this->marriage_results[0];

        // Remove Excess User Data Associated on all Rows for Marriage Data
        for($i = 0; $i < count(array_keys($this->marriage_results)); $i++) {
            unset($this->marriage_results[$i]['id'], $this->marriage_results[$i]['username'],$this->marriage_results[$i]['gender'],
                    $this->marriage_results[$i]['status'], $this->marriage_results[$i]['post_ban'],
                    $this->marriage_results[$i]['location'], $this->marriage_results[$i]['rank_id'],
                    $this->marriage_results[$i]['rank'], $this->marriage_results[$i]['user_rank'],
                    $this->marriage_results[$i]['village'], $this->marriage_results[$i]['enable_marriage']);
        }

        // Remove NULL Entries from First Record
        // NULL Entries can result LEFT JOINS from empty entries due to the conditions
        if(count(array_keys($this->marriage_results)) === 1) {
            if($this->marriage_results[0]['mid'] === null) {
                foreach($this->marriage_results[0] as $key => $value) {
                    if(is_null($value)) { // If entry is NULL, Remove
                            unset($this->marriage_results[0][$key]);
                    }
                    elseif(empty($value)) { // If entry is Empty, Replace
                            $this->marriage_results[0][$key] = 'Not Available';
                    }
                }

                if(empty($this->marriage_results[0])) {
                    $this->marriage_results = '0 rows';
                }
            }
        }

        if($this->marriage_results === '0 rows') {
            return false;
        }
        elseif(intval($this->user[0]['enable_marriage']) === 1) {
            if(count(array_keys($this->marriage_results)) === 1) {
                if($this->marriage_results[0]['married'] === 'Yes') {
                    return true;
                }
                return false;
            }
            elseif(count(array_keys($this->marriage_results)) > 1) {
                $marriage_count = 0;
                for($i = 0; $i < count(array_keys($this->marriage_results)); $i++) {
                    if($this->marriage_results[$i]['married'] === 'Yes') {
                        $marriage_count++;
                    }
                }

                if($marriage_count === 1) {
                    throw new Exception("
                        The system detected several proposals leftover within the system.
                        Please come back and try again to see if there's any changes."
                    );
                }
                elseif($marriage_count > 1) {
                    throw new Exception('
                        You seem to be married to multiple people. As such, all marriages
                        with your account shall be purged from the system to prevent further errors occurring.'
                    );
                }
            }
        }
        return false;
    }

    private function marriage_purge($choice) {
        try {
            $GLOBALS['database']->transaction_start();

            if(($GLOBALS['database']->execute_query('SELECT * FROM `marriages`
                WHERE (`marriages`.`uid` = '.$_SESSION['uid'].' OR
                    `marriages`.`oid` = '.$_SESSION['uid'].') FOR UPDATE')) === false) {
                throw new Exception('1');
            }

            if($choice === 'excess') {
                $m_id = 0;
                for($i = 0; $i < count(array_keys($this->marriage_results)); $i++) {
                    if($this->marriage_results[$i]['married'] === 'Yes') {
                        $m_id = $i; break;
                    }
                }
                if(($GLOBALS['database']->execute_query('DELETE FROM `marriages`
                    WHERE `marriages`.`mid` != '.$this->marriage_results[$m_id]['mid'].' AND
                        (`marriages`.`uid` = '.$_SESSION['uid'].' OR
                        `marriages`.`oid` = '.$_SESSION['uid'].')')) === false) {
                    throw new Exception('2');
                }
            }
            else {
                if(($GLOBALS['database']->execute_query('DELETE FROM `marriages`
                    WHERE `marriages`.`uid` = '.$_SESSION['uid'].' OR
                        `marriages`.`oid` = '.$_SESSION['uid'])) === false) {
                    throw new Exception('3');
                }
            }
            $GLOBALS['database']->transaction_commit();
        }
        catch(Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
        }
    }

    private function main_page() {

        // Marriage data
        $marriage = $this->marriage_results;
        $name = '';

        // If there were results of being Married or Proposals
        if($marriage !== "0 rows"){

            // If the return result was one row, you're married and
            // shouldn't have any proposals
            if(count($marriage) === 1 && $marriage[0]['married'] === 'Yes') {

                // User is married
                $GLOBALS['template']->assign('marriage', $marriage);
                $GLOBALS['template']->assign('proposals', 0);


                switch($this->user[0]['id']){
                    case $marriage[0]['uid']: $name = $marriage[0]['user_2']; break;
                    case $marriage[0]['oid']: $name = $marriage[0]['user_1']; break;
                }

                // Get spouse ID
                $spouseID = $this->user[0]['id'] == $marriage[0]['uid'] ? $marriage[0]['oid'] : $marriage[0]['uid'];

                // Show tavern
                //
                // Get libraries
                require_once(Data::$absSvrPath.'/libs/chatSystem/chatLib.inc.php');
                require_once(Data::$absSvrPath.'/ajaxLibs/staticLib/markitup.bbcode-parser.php');

                // Instantiate chat class
                $marChat = new chatLib('tavern_marriage');

                // Get user rank
                $userRank = ($this->user[0]['gender'] === "Male") ? "Husband" : "Wife";

                $marChat->setupChatSystem(
                    array(
                        "userTitleOverwrite" => $userRank,
                        "tavernTable" => "tavern_marriage",
                        "tableColumn" => "marriage_id",
                        "tableSelect" => $marriage[0]['mid'],
                        "chatName" => "Marriage Chat",
                        "subMessage" => "
                            <text>You are married to ".$name."</text><br>
                            <a href='?id=".$_GET['id']."&amp;mid=".$spouseID."&amp;act=getDivorce'>Click here to file for divorce</a>
                        ",
                        "canCombat" => true,
                        "smartyTemplate" => "contentLoad"
                    )
                );

                // Wrap the contentLoad in a page wrapper with this javascript library
                if($GLOBALS['mf'] == 'yes')
                    $GLOBALS['page']->createPageWrapper("./content/tavern/Scripts/chatScripts_mf.js");
                else
                    $GLOBALS['page']->createPageWrapper("./content/tavern/Scripts/chatScripts.js");

            }
            else { // You're not married, but have proposals

                // Clean up $proposals[$i].uid
                foreach( $marriage as $key => $val ){
                    if( $val['uid'] == $_SESSION['uid'] ){
                        $marriage[$key]['action'] = "<a href='?id=".$_GET['id']."&amp;remove_user_proposal=".$val['oid']."'>Delete</a>";
                    }
                    else{
                        $marriage[$key]['action'] = "<a href='?id=".$_GET['id']."&amp;proposal_user_delete=".$val['uid']."'>Decline</a> / <a href='?id=".$_GET['id']."&amp;marriage_user_accept=".$val['uid']."'>Accept</a>";
                    }
                }

                // Show form
                tableParser::show_list(
                    'proposalList',
                    'Marriage Proposals',
                    $marriage,
                    array(
                        "user_1" => "From",
                        "user_2" => "To",
                        "time" => "Time",
                        "action" => "Action"),
                    array(),
                    false,
                    false,
                    false, // Not top links
                    false, // No sorting on columns
                    false, // No pretty options
                    false, // No top search field
                    "Below are current proposals related to your character"
                );


                $GLOBALS['template']->assign('marriage', 0);
                $GLOBALS['template']->assign('contentLoad','./templates/content/marriage/main.tpl');
            }
        }
        else { // You're not married and have no proposals
            $GLOBALS['template']->assign('marriage', 0);
            $GLOBALS['template']->assign('contentLoad','./templates/content/marriage/main.tpl');
        }
    }


    // OTHER FUNCTIONS
    private function do_proposal() {

        // Start transaction
        $GLOBALS['database']->transaction_start();

        // Check for username input
        if(!isset($_REQUEST['proposed_user']) || $this->ws_remove($_REQUEST['proposed_user']) === '') {
            throw new Exception('You must put in a username to propose!');
        }

        // Save variables
        $name = $_REQUEST['proposed_user'];
        $user_ID = $_SESSION['uid'];

        // Get proposal data
        if(!($proposal_data = $GLOBALS['database']->fetch_data('SELECT `users`.`id` AS `target_user`,
            `marriages`.`married`, `marriages`.`uid` AS `user_1`, `marriages`.`oid` AS `user_2`,
            `users_preferences`.`enable_marriage`,
            `recipient_stats`.`rank_id` AS `recipient_rank_id`,
            `target_stats`.`rank_id` AS `target_rank_id`,
            `recipient`.`status`, `recipient`.`username`
            FROM `users`
                LEFT JOIN `marriages` ON (`marriages`.`uid` = `users`.`id` OR `marriages`.`oid` = `users`.`id`)
                LEFT JOIN `users_preferences` ON (`users_preferences`.`uid` = `users`.`id`)
                LEFT JOIN `users_statistics` AS `recipient_stats` ON (`recipient_stats`.`uid` = '.$user_ID.')
                LEFT JOIN `users_statistics` AS `target_stats` ON (`target_stats`.`uid` = `users`.`id`)
                LEFT JOIN `users` AS `recipient` ON (`recipient`.`id` = '.$user_ID.')
                LEFT JOIN `users` AS `target` ON (`target`.`id` = `users`.`id`)
            WHERE `users`.`username` = "'.$name.'"')))
        {
            throw new Exception('There was an error trying to receive user information.');
        }

        // Run various checks on data
        if($proposal_data === '0 rows') {
            throw new Exception("Apparently, the user you are trying to propose to doesn't exist!");
        }

        if($proposal_data[0]['status'] === 'hospitalized') {
            throw new Exception('You cannot propose right now since you are hospitalized!');
        }

        if($proposal_data[0]['status'] === 'drowning') {
            throw new Exception('You cannot propose right now since you are drowning!');
        }

        if($proposal_data[0]['status'] === 'combat' || $proposal_data[0]['status'] === 'exiting_combat') {
            throw new Exception('You cannot propose right now since you are in combat!');
        }

        if($proposal_data[0]['recipient_rank_id'] <= 2) {
            throw new Exception("You cannot propose for you haven't met the required rank!");
        }

        if($proposal_data[0]['target_rank_id'] <= 2) {
            throw new Exception("You cannot propose to this user for they haven't met the required rank!");
        }

        if(intval($proposal_data[0]['enable_marriage']) !== 1) {
            throw new Exception('You cannot propose to this user for they have disabled marriage!');
        }

        $target_uid = $proposal_data[0]['target_user'];

        if($target_uid === $user_ID) {
            throw new Exception('You cannot propose to yourself!');
        }

        // Do some more checks on proposal data
        for($i = 0; $i < count(array_keys($proposal_data)); $i++) {
            if($proposal_data[$i]['married'] === 'Yes') {
                // Target has already been Married
                throw new Exception('The user is already married right now!');
            }
            elseif($proposal_data[$i]['user_1'] === $user_ID) {
                if($proposal_data[$i]['user_2'] === $target_uid) {
                    // User has already proposed to the target
                    throw new Exception('You have already proposed to the user!');
                }
            }
            elseif($proposal_data[$i]['user_2'] === $user_ID) {
                if($proposal_data[$i]['user_1'] === $target_uid) {
                    // Target has already proposed to the user
                    throw new Exception('The user has already proposed to you!');
                }
            }
        }

        // Insert new proposal if not stopped at this point
        if(($GLOBALS['database']->execute_query('INSERT INTO
            `marriages`
                (`uid`, `oid`, `time`, `married`)
            VALUES
                ('.$_SESSION['uid'].', '.$target_uid.', '.$GLOBALS['user']->load_time.', "No")')) === false) {
            throw new Exception('There was an error trying to propose to the user during proposal insertion. Please try again!');
        }

        // Message the user
        $users_notifications = new NotificationSystem('', $target_uid);

        $users_notifications->addNotification(array(
                                                    'id' => 8,
                                                    'duration' => 'none',
                                                    'text' => $proposal_data[0]['username'] . ' has proposed to you!',
                                                    'dismiss' => 'yes'
                                                ));

        $users_notifications->recordNotifications();

        // Show page to user
        $GLOBALS['page']->Message('You have proposed to '.$name.'!' , 'Marriage Proposal','id='.$_GET['id'].'');

        // Commit the transaction
        $GLOBALS['database']->transaction_commit();

    }

    private function delete_proposal($choice) {

        // Start tranasction
        $GLOBALS['database']->transaction_start();

        // Check the choise
        if($choice === 'decline') {
             if((ctype_digit($_REQUEST['proposal_user_delete']) === false)
                && ($_REQUEST['proposal_user_delete'] !== $_SESSION['uid'])) {
                throw new Exception('The proposal deletion data has been corrupted!');
             }
             $affected_user = $_REQUEST['proposal_user_delete'];

             if(!($proposal = $GLOBALS['database']->fetch_data('SELECT `marriages`.`mid`,
                `user_2_table`.`username` AS `user_2_username`,
                `user_1_table`.`status`
                FROM `marriages`
                    LEFT JOIN `users` AS `user_2_table` ON (`user_2_table`.`id` = '.$affected_user.')
                    LEFT JOIN `users` AS `user_1_table` ON (`user_1_table`.`id` = '.$_SESSION['uid'].')
                WHERE (`marriages`.`uid` = '.$affected_user.' AND
                    `marriages`.`oid` = '.$_SESSION['uid'].') LIMIT 1 FOR UPDATE'))) {
                throw new Exception('There was an error trying to receive proposal information.');
            }
        }
        else {
            if((ctype_digit($_REQUEST['remove_user_proposal']) === false)
                && ($_REQUEST['remove_user_proposal'] !== $_SESSION['uid'])) {
                throw new Exception('The proposal deletion data has been corrupted!');
            }
            $affected_user = $_REQUEST['remove_user_proposal'];

            if(!($proposal = $GLOBALS['database']->fetch_data('SELECT `marriages`.`mid`,
                `user_2_table`.`username` AS `user_2_username`,
                `user_1_table`.`status`
                FROM `marriages`
                    LEFT JOIN `users` AS `user_2_table` ON (`user_2_table`.`id` = '.$affected_user.')
                    LEFT JOIN `users` AS `user_1_table` ON (`user_1_table`.`id` = '.$_SESSION['uid'].')
                WHERE (`marriages`.`uid` = '.$_SESSION['uid'].' AND
                    `marriages`.`oid` = '.$affected_user.') LIMIT 1 FOR UPDATE'))) {
                throw new Exception('There was an error trying to receive proposal information.');
            }
        }

        // Run checks
        if($proposal === '0 rows') {
            throw new Exception("Apparently, the proposal you are trying to delete doesn't exist!");
        }

        if($proposal[0]['status'] === 'combat' || $proposal[0]['status'] === 'exiting_combat') {
            throw new Exception('You cannot propose right now since you are in combat!');
        }

        if($proposal[0]['status'] === 'hospitalized') {
            throw new Exception('You cannot propose right now since you are hospitalized!');
        }

        if($proposal[0]['status'] === 'drowning') {
            throw new Exception('You cannot propose right now since you are drowning!');
        }

        // Delete proposal
        $significant_other = $proposal[0]['user_2_username'];
        if(($GLOBALS['database']->execute_query('DELETE FROM `marriages`
            WHERE `marriages`.`mid` = '.$proposal[0]['mid'].' LIMIT 1')) === false) {
            throw new Exception('There was an error trying to delete the proposal. Please try again!');
        }

        // Message for user
        $GLOBALS['page']->Message("The proposal regarding ".$significant_other." has been denied and deleted!",
            'Marriage Proposal Denied','id='.$_GET['id'].'');

        // Commit the transaction
        $GLOBALS['database']->transaction_commit();

    }

    private function accept_proposal() {

        $GLOBALS['database']->transaction_start();

        if((ctype_digit($_REQUEST['marriage_user_accept']) === false)
            && ($_REQUEST['marriage_user_accept'] !== $_SESSION['uid'])) {
            throw new Exception('The marriage acceptance data has been corrupted!');
        }

        if(!($marriage = $GLOBALS['database']->fetch_data('SELECT `marriages`.`mid`,
            `user_2_table`.`username` AS `user_2_username`,
            `user_1_table`.`status`
            FROM `marriages`
                LEFT JOIN `users` AS `user_2_table` ON (`user_2_table`.`id` = '.$_REQUEST['marriage_user_accept'].')
                LEFT JOIN `users` AS `user_1_table` ON (`user_1_table`.`id` = '.$_SESSION['uid'].')
            WHERE (`marriages`.`uid` = '.$_REQUEST['marriage_user_accept'].' AND
                `marriages`.`oid` = '.$_SESSION['uid'].') LIMIT 1 FOR UPDATE'))) {
            throw new Exception('There was an error trying to receive marriage information.');
        }

        if($marriage === '0 rows') {
            throw new Exception("Apparently, the marriage you are trying to delete doesn't exist!");
        }

        if($marriage[0]['status'] === 'combat' || $marriage[0]['status'] === 'exiting_combat') {
            throw new Exception('You cannot propose right now since you are in combat!');
        }

        if($marriage[0]['status'] === 'hospitalized') {
            throw new Exception('You cannot propose right now since you are hospitalized!');
        }

        if($marriage[0]['status'] === 'drowning') {
            throw new Exception('You cannot propose right now since you are drowning!');
        }

        if(($GLOBALS['database']->execute_query('UPDATE `marriages`
            SET `marriages`.`married` = "Yes"
            WHERE `marriages`.`mid` = '.$marriage[0]['mid'].' LIMIT 1')) === false) {
            throw new Exception('There was an error trying to marry you. Please try again!');
        }

        if(($GLOBALS['database']->execute_query('DELETE FROM `marriages`
            WHERE `marriages`.`mid` != '.$marriage[0]['mid'].' AND
                (`marriages`.`uid` = '.$_REQUEST['marriage_user_accept'].' OR
                `marriages`.`oid` = '.$_SESSION['uid'].') OR (`marriages`.`uid` = '.$_SESSION['uid'].'
                OR `marriages`.`oid` = '.$_REQUEST['marriage_user_accept'].')')) === false) {
            throw new Exception('There was an error trying to delete all other proposals. Please try again!');
        }

        $GLOBALS['Events']->acceptEvent('marriage', array('data'=>'married'));

        require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
        $events = new Events($_REQUEST['marriage_user_accept']);
        $events->acceptEvent('marriage', array('data'=>'married'));
        $events->closeEvents();

        $GLOBALS['page']->Message('You have accepted the proposal. You are now married to '.$marriage[0]['user_2_username'], 'Marriage','id='.$_GET['id'].'');

        $GLOBALS['database']->transaction_commit();

    }

    private function delete_marriage() {

        $GLOBALS['database']->transaction_start();

        if((ctype_digit($_GET['mid']) === false)
            && ($_GET['mid'] !== $_SESSION['uid'])) {
            throw new Exception('The marriage deletion data has been corrupted!');
        }

        if(!($marriage = $GLOBALS['database']->fetch_data('
            SELECT
                `marriages`.`mid`,
                `user_2_table`.`username` AS `user_2_username`,
                `user_2_table`.`id` AS `user_2_id`,
                `user_2_table`.`status` AS `user_2_status`,
                `user_2_table`.`location` AS `user_2_location`,
                `user_1_table`.`status`, `user_1_table`.`id` AS `user_1_id`,
                `homes`.`married_home`, `homes`.`price`, `homes`.`regen`, `homes`.`id` AS `homeID`,
                `users_statistics_2_table`.`money` as `user_2_money`,
                `users_statistics_1_table`.`money` as `user_1_money`
            FROM `marriages`
                LEFT JOIN `users` AS `user_2_table` ON (`user_2_table`.`id` = '.$_GET['mid'].')
                LEFT JOIN `users` AS `user_1_table` ON (`user_1_table`.`id` = '.$_SESSION['uid'].')

                LEFT JOIN `users_statistics` AS `users_statistics_2_table` ON (`users_statistics_2_table`.`uid` = '.$_GET['mid'].')
                LEFT JOIN `users_statistics` AS `users_statistics_1_table` ON (`users_statistics_1_table`.`uid` = '.$_SESSION['uid'].')

                LEFT JOIN `homes` ON (`homes`.`id` = `user_1_table`.`apartment`)
            WHERE (`marriages`.`uid` = '.$_GET['mid'].' AND
                `marriages`.`oid` = '.$_SESSION['uid'].') OR (`marriages`.`uid` = '.$_SESSION['uid'].'
                AND `marriages`.`oid` = '.$_GET['mid'].') LIMIT 1 FOR UPDATE'))) {
            throw new Exception('There was an error trying to receive marriage information.');
        }

        // Marriage could not be found
        if($marriage === '0 rows') {
            throw new Exception("Apparently, the marriage you are trying to delete doesn't exist!");
        }

        // Cannot divorce during combat
        if($marriage[0]['status'] === 'combat' || $marriage[0]['status'] === 'exiting_combat') {
            throw new Exception('You cannot propose right now since you are in combat!');
        }

        // Cannot divorce during hospitalized
        if($marriage[0]['status'] === 'hospitalized') {
            throw new Exception('You cannot propose right now since you are hospitalized!');
        }

        if($marriage[0]['status'] === 'drowning') {
            throw new Exception('You cannot propose right now since you are drowning!');
        }

        // Save name of spouse for easier access
        $significant_other = $marriage[0]['user_2_username'];

        // Check if they have a couple house
        if($marriage[0]['married_home'] === 'Yes') {

            // Test if the significant other is asleep
            $regenChange = 0;
            $newStatus = $marriage[0]['user_2_status'];
            if( $marriage[0]['user_2_status'] == "asleep" && in_array( $marriage[0]['user_2_location'], array('Shroud','Shine','Samui','Silence','Konoki') ) ){
                $regenChange = $marriage[0]['regen'];
                $newStatus = "awake";
            }

            // Set user to awake and remove marriage home
            if(($GLOBALS['database']->execute_query('
                UPDATE `users`, `users_statistics`
                SET `users`.`status` = "'.$newStatus.'",
                    `users_statistics`.`regen_rate` = `users_statistics`.`regen_rate` - '.$regenChange.',
                    `users`.`apartment` = NULL,
                    `users_statistics`.`money` = `users_statistics`.`money` + '.($marriage[0]['price'] / 4).'
                WHERE
                    `users`.`id` IN ('.$marriage[0]['user_1_id'].', '.$marriage[0]['user_2_id'].') AND
                    `users_statistics`.`uid` IN ('.$marriage[0]['user_1_id'].', '.$marriage[0]['user_2_id'].')')) === false
            ){
                throw new Exception('There was an error trying to remove the married home. Please try again!');
            }
            else
            {
                $GLOBALS['Events']->acceptEvent('home', array('data'=>'sold'));
                $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] + ($marriage[0]['price'] / 4)));

                $id = 0;
                if($marriage[0]['user_1_id'] == $_SESSION['uid'])
                {
                    $id = $marriage[0]['user_2_id'];
                    $money = $marriage[0]['user_2_money'];
                }
                else
                {
                    $id = $marriage[0]['user_1_id'];
                    $money = $marriage[0]['user_1_money'];
                }

                require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
                $events = new Events($id);
                $events->acceptEvent('home', array('data'=>'sold'));

                $events->acceptEvent('money_gain', array('old'=>$money,'new'=> $money + ($marriage[0]['price'] / 4)));
                $events->closeEvents();
            }
        }

        // Delete the marriage
        if(($GLOBALS['database']->execute_query('DELETE FROM `marriages`
            WHERE `marriages`.`mid` = '.$marriage[0]['mid'].' LIMIT 1')) === false) {
            throw new Exception('There was an error trying to delete the marriage. Please try again!');
        }

        $id = 0;
        if($marriage[0]['user_1_id'] == $_SESSION['uid'])
            $id = $marriage[0]['user_2_id'];
        else
            $id = $marriage[0]['user_1_id'];

        $GLOBALS['Events']->acceptEvent('marriage', array('data'=>'divorced'));

        require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
        $events = new Events($id);
        $events->acceptEvent('marriage', array('data'=>'divorced'));
        $events->closeEvents();

        $GLOBALS['database']->transaction_commit();
        $GLOBALS['page']->Message("You have divorced ".$significant_other."! Forever alone once again.",
            'Marriage: Divorce','id='.$_GET['id'].'');

    }
}
new marriage();
