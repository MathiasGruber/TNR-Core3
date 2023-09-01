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

// This class inherits from the user profile
require_once(Data::$absSvrPath.'/libs/profileFunctions/profileLib.inc.php');
class members extends profileFunctions {

    // Constructor
    public function __construct() {
        try {

            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            if (!isset($_GET['page'])) {
                $this->member_list();
            } elseif ($_GET['page'] == 'profile') {
                $this->view_profile();
            } elseif ($_GET['page'] == 'view_nindo') {
                $this->view_nindo();
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        }
        catch(Exception $e) {
            // Rollback possible transactions
            $GLOBALS['database']->transaction_rollback($e->getMessage());

            // Show error message
            $GLOBALS['page']->Message($e->getMessage(), 'Members Error', 'id='.$_GET['id']);
        }
    }

    // The main page, a list of members & options
    protected function member_list() {
        if (!isset($_GET['act']) || $_GET['act'] == "") {
            $_GET['act'] = "level";
        }

        if (!isset($_GET['rank']) || $_GET['rank'] == 'all') {
            $rank_sort = ", All users";
            $rank_q = "";
        } elseif ($_GET['rank'] == '1') {
            $rank_sort = ", Students";
            $rank_q = "AND `rank_id` = 1";
        } elseif ($_GET['rank'] == '2') {
            $rank_sort = ", Genins";
            $rank_q = "AND `rank_id` = 2";
        } elseif ($_GET['rank'] == '3') {
            $rank_sort = ", Chuunin";
            $rank_q = "AND `rank_id` = 3";
        } elseif ($_GET['rank'] == '4') {
            $rank_sort = ", Jounin";
            $rank_q = "AND `rank_id` = 4";
        } elseif ($_GET['rank'] == '5') {
            $rank_sort = ", Elite Jounin";
            $rank_q = "AND `rank_id` = 5";
        }

        // Query
        $headline = "";
        $query = "";
        if (!isset($_GET['act']) || $_GET['act'] == 'level') {
            $headline .= 'Sorted by exp';
            $query = 'SELECT `username`,`village`,`rank`,`experience` FROM `users`,`users_statistics` WHERE `uid` = `id` AND `user_rank` != \'Admin\' ' . $rank_q . ' ORDER BY `experience` DESC LIMIT 10';
            $table_var = 'EXP';
            $_custom = 'experience';
        } elseif ($_GET['act'] == 'reputation') {
            $headline .= 'Sorted by reputation points';
            $query = 'SELECT `username`,`village`,`rank`,`rep_ever` FROM `users`,`users_statistics` WHERE `uid` = `id` AND `user_rank` != \'Admin\' ' . $rank_q . ' ORDER BY `rep_ever` DESC LIMIT 10';
            $table_var = 'Reputation Points';
            $_custom = 'rep_ever';
        } elseif ($_GET['act'] == 'strength') {
            $headline .= 'Sorted by Strength Factor';
            $query = 'SELECT `username`,`village`,`rank`,`strengthFactor` FROM `users`,`users_statistics` WHERE `uid` = `id` AND `user_rank` != \'Admin\' ' . $rank_q . ' ORDER BY `strengthFactor` DESC LIMIT 10';
            $table_var = 'Strength Factor';
            $_custom = 'strengthFactor';
        } elseif ($_GET['act'] == 'battles') {
            $headline .= 'Sorted by battles fought';
            $query = 'SELECT `username`,`village`,`rank`,(`battles_won` + `battles_lost` + `battles_fled` + `battles_draws`) as `battles` FROM `users`,`users_missions`,`users_statistics` WHERE `uid` = `id` AND `users`.`id` = `users_missions`.`userid` AND `user_rank` != \'Admin\' ' . $rank_q . ' ORDER BY `battles` DESC LIMIT 10';
            $table_var = 'Battles';
            $_custom = 'battles';
        } elseif ($_GET['act'] == 'experience') {
            $headline .= 'Sorted by exp';
            $query = 'SELECT `username`,`village`,`rank`,`experience` FROM `users`,`users_statistics` WHERE `uid` = `id` AND `user_rank` != \'Admin\' ' . $rank_q . ' ORDER BY `rank_id` DESC,`experience` DESC LIMIT 10';
            $table_var = 'EXP';
            $_custom = 'experience';
        } elseif ($_GET['act'] == 'pvp_experience') {
            $headline .= 'Sorted by PvP exp';
            $query = 'SELECT `username`,`village`,`rank`,`pvp_experience` FROM `users`,`users_statistics` WHERE `uid` = `id` AND `user_rank` != \'Admin\' ' . $rank_q . ' ORDER BY `pvp_experience` DESC LIMIT 10';
            $table_var = 'PvP EXP';
            $_custom = 'pvp_experience';
        } elseif( $_GET['act'] == 'clan' && isset($_GET['vil']) && preg_match("/^(Konoki|Silence|Shine|Shroud|Samui)$/", $_GET['vil'] ) ){
            $headline .= 'Sorted by Clan points';
            $query = "SELECT `id`, `name` as `username`,`village`,`points` FROM `clans` WHERE `village` = '".$_GET['vil']."' ORDER BY `points` DESC LIMIT 10";
            $table_var = 'Clan Points';
            $_custom = 'points';
        }

        // Run Query
        if( $query !== "" ){

            // Get data
            $data = $GLOBALS['database']->fetch_data($query);
            if( $data !== "0 rows" ){

                // Determine Detail Link
                $detailLink = array();
                if( $_custom == "points" ){
                    $detailLink = array("name" => "Profile", "act2" => "clandetail", "cid" => "table.id", "id"=>"14");
                }
                else{
                    $detailLink = array("name" => "Profile", "page" => "profile", "profile" => "table.username");
                }

                // Show form
                $GLOBALS['template']->assign('preventStretch', 'true');
                tableParser::show_list(
                    'memberList',
                    $headline.$rank_sort,
                    $data,
                    array(
                        "username" => "Name",
                        "village" => "Village",
                        $_custom => $table_var
                    ),
                    array(
                        $detailLink
                    ),
                    false,
                    false,
                    array(
                        array("name" => "Top10 User Lists: ", "type"=>"text" ),
                        array("name" => "Level", "href" =>"?id=".$_GET["id"]."&amp;act=level"),
                        array("name" => "Reputed", "href" =>"?id=".$_GET["id"]."&amp;act=reputation"),
                        array("name" => "Strength", "href" =>"?id=".$_GET["id"]."&amp;act=strength"),
                        array("name" => "Battles", "href" =>"?id=".$_GET["id"]."&amp;act=battles"),
                        array("name" => "PvP Exp", "href" =>"?id=".$_GET["id"]."&amp;act=pvp_experience"),

                        array("name" => "<br>Top10 Clans: ", "type"=>"text" ),
                        array("name" => "Konoki", "href" =>"?id=".$_GET["id"]."&amp;act=clan&amp;vil=Konoki"),
                        array("name" => "Silence", "href" =>"?id=".$_GET["id"]."&amp;act=clan&amp;vil=Silence"),
                        array("name" => "Shroud", "href" =>"?id=".$_GET["id"]."&amp;act=clan&amp;vil=Shroud"),
                        array("name" => "Samui", "href" =>"?id=".$_GET["id"]."&amp;act=clan&amp;vil=Samui"),
                        array("name" => "Shine", "href" =>"?id=".$_GET["id"]."&amp;act=clan&amp;vil=Shine"),

                    ),
                    false, // No sorting on columns
                    false, // No pretty options
                    array(
                        array(
                            "infoText"=>"Search by username",
                            "postField"=>"name",
                            "postIdentifier"=>"postIdentifier",
                            "inputName"=>"Search User",
                            "href" => "?id=".$_GET['id']."&amp;page=profile"
                        )
                    ), // No top search field
                    array('message'=>"Here you will find top10 lists of game users and clans. You have several sorting options below.",'hidden'=>'yes') // Top information
                );
                $GLOBALS['template']->assign('contentLoad', './templates/content/members/memberList.tpl');
            }
            else{
                $GLOBALS['page']->Message("Could not retrieve any data", 'Top 10 Lists', 'id=13');
            }

        }
        else{
            $GLOBALS['page']->Message("Your request to the server did not make sense", 'Top 10 Lists', 'id=13');
        }
    }

    // Function for retrieving user data absed on GET/POST variables
    private function get_user() {

        // Digest GET/POST variables
        if (isset($_GET['name'])) {
            $search = " `username` = '" . addslashes($_GET['name']) . "'";
        } elseif (isset($_POST['name'])) {
            $search = " `username` = '" . addslashes($_POST['name']) . "'";
        } elseif (isset($_GET['profile'])) {
            $search = " `username` = '" . addslashes($_GET['profile']) . "'";
        } elseif (isset($_GET['uid'])) {
            $search = " users.`id` = '" . addslashes($_GET['uid']) . "'";
        } else {
            $search = " users.`id` = '0'";
        }

        // Quick get ID of this user
        $idQuery = $GLOBALS['database']->fetch_data("SELECT `users`.`id` FROM `users` WHERE " . $search . " LIMIT 1");
        if( $idQuery !== "0 rows" ){

            // Get user data
            $this->setCharData("otherUser", $idQuery[0]['id']);
            return true;
        }
        else{
            return false;
        }
    }

    // Show user profile
    protected function view_profile() {

        if( $this->get_user() ){
            if ($this->char_data !== '0 rows') {

                // Set ANBU & Clan. Defined in profile class.
                $this->setAnbuClanStatus();
                $this->setFedSupport();
                $this->setMarriage();
                $this->setWinStatistics();
                $this->setStudents();
                $this->setSensei();
                $this->setLoginStatus();

                $GLOBALS['template']->assign("extraRepsPerc", data::$REP_EXTRA);

                // Check Avatar
                $this->char_data[0]['avatar'] = functions::getAvatar($this->char_data[0]['id']);

                //getting dsr
                if($this->char_data[0]['sr'] != 0)
                    $this->char_data[0]['DSR'] = base_convert(floor(sqrt(($this->char_data[0]['dr'] * ( ( $this->char_data[0]['max_health'] ) / $this->char_data[0]['sr'] ))+4+24789)), 10, 9);
                else
                    $this->char_data[0]['DSR'] = 0;

                $this->char_data[0]['bloodline'] = explode(':',$this->char_data[0]['bloodline'])[0];

                // Send to smarty
                $GLOBALS['template']->assign('charInfo', $this->char_data[0]);
                $GLOBALS['template']->assign('sessionUser', $GLOBALS['userdata'][0]['username']);
                $GLOBALS['template']->assign('contentLoad', './templates/content/members/showProfile.tpl');

                // Show purchase options, maybe
                if ($this->char_data[0]['user_rank'] !== "Admin") {

                    // Check if enabled
                    $current = $GLOBALS['database']->fetch_data("SELECT * FROM `site_timer` WHERE `script` = 'paypalPayments'");
                    if( $current[0]['character_cleanup'] == "enabled" ){

                        // Button information
                        $customField = $this->char_data[0]['id']."|".$this->char_data[0]['username']."|".$_SESSION['uid']."|".$GLOBALS['userdata'][0]['username'];
                        $GLOBALS['template']->assign('customField', $customField);

                        // Allow to buy reputation points
                        $GLOBALS['template']->assign('loadPayPal', true);

                        // Allow the user to setup / remove federal support
                        if ($this->char_data[0]['user_rank'] == "Member" ) {

                            // Get information
                            $supp_data = $GLOBALS['database']->fetch_data("
                                SELECT `user_rank`,`federal_timer`,`subscr_id`
                                FROM `users`,`users_timer`,`users_statistics`
                                WHERE
                                    `id` = `userid` AND
                                    `uid` = `id` AND
                                    `id` = '" . $this->char_data[0]['id'] . "'
                                LIMIT 1");

                            // Send to smarty
                            $GLOBALS['template']->assign('supp_data', $supp_data);
                        }
                    }
                }

            }
            else{
                $GLOBALS['page']->Message("Could not get data", 'User not found in database', 'id=13');
            }
        }
        else{
            $GLOBALS['page']->Message("This user does not exist", 'User not found in database', 'id=13');
        }
    }

    // Show user nindo
    protected function view_nindo() {
        if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
            $nindo = $GLOBALS['database']->fetch_data("SELECT `nindo`,`username` FROM `users` WHERE `id` = '" . $_GET['uid'] . "' LIMIT 1");
            if ($nindo != '0 rows') {
                $GLOBALS['template']->assign('GET_uid', $_GET['uid']);
                if( !functions::isAPIcall() ){
                    $nindo[0]['nindo'] = functions::color_BB(functions::parse_BB($nindo[0]['nindo']));
                }
                else{
                    $nindo[0]['nindo'] = nl2br($nindo[0]['nindo']);
                }

                $GLOBALS['template']->assign('nindo', $nindo[0]);
                $GLOBALS['template']->assign('contentLoad', './templates/content/members/showNindo.tpl');
            } else {
                $GLOBALS['page']->Message("This user does not exist or does not have a nindo", 'Nindo could not be found', 'id=13&page=profile&uid=' . $_GET['uid'] . '');
            }
        } else {
            $GLOBALS['page']->Message("An invalid userid was submitted", 'User not found in database', 'id=13');
        }
    }

}

new members();