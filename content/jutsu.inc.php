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

// Get Libraries
require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
require_once(Data::$absSvrPath.'/libs/jutsuSystem/jutsuFunctions.php');

class jutsu extends jutsuBasicFunctions {

    // Public constructor
    public function __construct() {

        // try-catch
        try {

            // Check for an active session
            functions::checkActiveSession();

            // Obtain neccesary lock
            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            // Get all the user jutsu
            $this->getAllUserJutsu($_SESSION['uid']); // Get the users jutsu

            // Decide what to show
            if(!isset($_GET['act'])) {

                $array_keys = array_keys($_POST);
                foreach($array_keys as $key)
                    if( substr( $key, 0, 13 ) === "selectLoadout" )
                        $_POST['selectLoadout'] = $_POST[$key];

                // Show main page or upload new tags
                if( !isset($_POST['Update']) && !isset($_POST['selectLoadout']) && !isset($_POST['deleteLoadout']) ){
                    $this->main_page();
                }
                else if( isset($_POST['Update']) ) {
                    $this->upload_tags();
                    $this->getAllUserJutsu($_SESSION['uid']); // Get the users jutsu
                    $this->main_page();
                }
                else if( isset($_POST['selectLoadout']) ) {
                    $this->selectLoadout(trim($_POST['selectLoadout']));
                    $this->getAllUserJutsu($_SESSION['uid']); // Get the users jutsu
                    $this->main_page();
                }
                else if( isset($_POST['deleteLoadout']) ) {
                    $this->deleteLoadout(trim($_POST['deleteLoadout']));
                    $this->getAllUserJutsu($_SESSION['uid']); // Get the users jutsu
                    $this->main_page();
                }
            }
            elseif($_GET['act'] === 'detail' && ctype_digit($_GET['jid'])) {
                $this->show_details( $_GET['jid'] ); // Show jutsu details
            }
            elseif ($_GET['act'] === 'forget' && ctype_digit($_GET['jid'])) {
                // Confirm / Do Jutsu Removal
                (isset($_POST['Submit'])) ? $this->jutsu_do_forget()
                    : $GLOBALS['page']->Confirm("Please confirm that you want to forget this jutsu", 'Jutsu System', 'Forget now!');
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        }
        catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            $GLOBALS['page']->Message($e->getMessage(), 'Jutsu System', 'id='.$_GET['id'], 'Return');
        }
    }

    // Show the main page
    public function main_page() {

        // The display array for smarty
        $displayArray = array(
            "ninjutsu" => array(),
            "genjutsu" => array(),
            "taijutsu" => array(),
            "weapon" => array(),
            "highest" => array()
        );

        // More storage variables
        $this->allJutsu = array();
        $this->taggableJutsu = array();
        $this->tagged = array();
        $this->jutsuNumber = 0;


        // get affinities
        $elements = new Elements();
        $affinities = $elements->getUserElements();
        $affinities[3] = 'none';

        $taggedGroups = array();

        // Add to array
        if($this->jutsu !== "0 rows") {

            $count_for_fix = 1;
            // Sort the jutsu


            // Process the jutsu
            foreach($this->jutsu as $jutsu) {

                // Available or not
                $available = in_array(strtolower($jutsu['element']), $affinities , true) ? "yes" : "no";

                // If a clan jutsu, it may also be unavailable
                if( $available == "yes" && $jutsu['jutsu_type'] == "clan" && $_SESSION['uid'] == "3819"){
                    if( isset($GLOBALS['userdata'][0]['clan']) &&
                        !empty($GLOBALS['userdata'][0]['clan']) &&
                        $GLOBALS['userdata'][0]['clan'] !== "disabled" &&
                        $GLOBALS['userdata'][0]['clan'] !== "None")
                    {
                        $clan = $GLOBALS['database']->fetch_data( "SELECT `clan_jutsu` FROM `clans` WHERE `clans`.`id` = '".$GLOBALS['userdata'][0]['clan']."' LIMIT 1" );
                        if( $clan == "0 rows" || $clan[0]['clan_jutsu'] !== $jutsu['jid'] ){
                            $available = "no";
                        }
                    }
                    else{
                        $available = "no";
                    }
                }

                // If it's a time-limited jutsu, it may also be unavailable
                if( $available == "yes" && !functions::checkStartEndDates($jutsu) ){
                    $available = "no";
                }

                // Required rank
                $reqRank = isset( Data::$RANKNAMES[$jutsu['required_rank']] ) ? Data::$RANKNAMES[$jutsu['required_rank']] : "Unknown";

                // Add to array
                $displayArray[$jutsu['attack_type']][] = array(
                    $jutsu['jid'],
                    $jutsu['name'],
                    $reqRank,
                    $jutsu['level'],
                    $jutsu['jutsu_type'],
                    $jutsu['element']  ,
                    $available
                );

                // Save all jutsu names
                if($available === "yes") {
                    $this->allJutsu[] = array($jutsu['jid'], $jutsu['name']);
                }

                if($jutsu['tagged'] != 'no')
                {
                    $tempgroup = explode(';', $jutsu['tagged']);
                    $group = array();


                    foreach($tempgroup as $key => $piece)
                    {
                        if($piece != '')
                        {
                            $explode = explode(':', $piece);

                            if($explode[1] == 0)
                            {
                                $explode[1] = $count_for_fix;
                                $count_for_fix++;
                            }

                            $group[$explode[0]] = $explode[1] - 1;

                            $taggedGroups[$explode[0]] = $explode[0];
                        }
                    }

                    // Save all tagged jutsus
                    if(strpos($jutsu['tagged'], $GLOBALS['userdata'][0]['taggedGroup']) !== false) { $this->tagged[$group[$GLOBALS['userdata'][0]['taggedGroup']]] = array($jutsu['jid'], $jutsu['name']); }
                }

                $this->jutsuNumber++;
            }
        }

        $LoadoutMax = 1;

        //getting number of max load outs avaliable.
        //if you are an admin, or eventMod, or event
        if( in_array($GLOBALS['userdata'][0]['user_rank'], array("Moderator", "Supermod", "PRmanager","Admin","Event","EventMod","ContentAdmin", "Tester"), true) )
            $loadoutMax = 4;

        //if you are a normal 
        else if ( in_array($GLOBALS['userdata'][0]['user_rank'], array("Paid", "ContentMember"), true) )
        {
            if($GLOBALS['userdata'][0]['federal_level'] == 'Normal')
                $loadoutMax = 2;

            //if you are a silver
            else if($GLOBALS['userdata'][0]['federal_level'] == 'Silver')
                $loadoutMax = 3;

            //if you are a gold
            else if($GLOBALS['userdata'][0]['federal_level'] == 'Gold')
                $loadoutMax = 4;
        }
        else if($GLOBALS['userdata'][0]['user_rank'] == 'Member')
            $loadoutMax = 1;

        else
        {
            $loadoutMax = 1;
            echo "unknown-user-rank: ".$GLOBALS['userdata'][0]['user_rank'];
        }

        $loadoutCount = count($taggedGroups);

        if($loadoutMax > 1 && $loadoutCount <= $loadoutMax)
        {
            $GLOBALS['template']->assign('show_loadouts', true);

            if($loadoutCount > 1)
                $GLOBALS['template']->assign('delete_loadouts', true);

            if($loadoutCount < $loadoutMax)
                $GLOBALS['template']->assign('add_loadouts', true);

            $GLOBALS['template']->assign('taggedGroups', $taggedGroups);
        }

        //force delete of a loadout
        if($loadoutCount > $loadoutMax)
        {
            $GLOBALS['template']->assign('taggedGroups', $taggedGroups);
            $GLOBALS['template']->assign('force_delete', true);
        }

        $GLOBALS['template']->assign('loadout_count', '('.$loadoutCount.'/'.$loadoutMax.')');

        $GLOBALS['template']->assign('current_loadout', $GLOBALS['userdata'][0]['taggedGroup']);

        // Set the jutsu dropdown lists
        $GLOBALS['template']->assign('jutsuLists', $this->construct_dropdown_options() );
        $GLOBALS['template']->assign('jutsuCount', $this->jutsuNumber );

        // Jutsu lists
        $GLOBALS['template']->assign('displayArray', $displayArray);
        $GLOBALS['template']->assign('userRank', $GLOBALS['userdata'][0]['user_rank']);

        // Show the main template
        $GLOBALS['template']->assign('contentLoad', './templates/content/myjutsu/main_page.tpl');

    }

    // Get the number of jutsus this user can tag
    private function getTagLimit() {
        switch($GLOBALS['userdata'][0]['federal_level']) {
            case("None"): case("Normal"): return 8; break;
            case("Silver"): case("Gold"): return 10; break;
            default: return 0; break;
        }
    }

    // Drop-down menu constructor for jutsu tagging
    public function construct_dropdown_options() {
        $lists = array();

        // Function for sorting jutsu list by name
        function compareByName($a, $b) {
            return strcmp($a[1], $b[1]);
        }

        for($i = 0, $size = $this->getTagLimit(); $i < $size; $i++) {

            // book keeping of tagging
            $tagged = 0;

            // Sort the allJutsu alphabetically
            if( !empty($this->allJutsu) ){
                usort($this->allJutsu, 'compareByName');
            }

            // Check if already tagged
            if(isset($this->tagged[$i])){
                foreach($this->allJutsu as $jutsu ){
                    if($jutsu[0] === $this->tagged[$i][0]) {
                        $tagged = 1;
                        $lists[$i][] = array($jutsu[0], $jutsu[1], 1);
                    }
                    else {
                        $lists[$i][] = array($jutsu[0], $jutsu[1], 0);
                    }
                }
            }
            else{
                $lists[$i] = $this->allJutsu;
            }

            // Set the default selected to the tagged one, if that exists
            if($tagged === 0) {
                $lists[$i][] = array( "None", "None", 1 );
            }
            else {
                $lists[$i][] = array( "None", "None" , 0 );
            }
        }
        return $lists;
    }

    // Upload new tagged jutsu:
    public function upload_tags() {

        if(!isset($GLOBALS['userdata'][0]['taggedGroup']))
            $GLOBALS['userdata'][0]['taggedGroup'] = 'default';

        // Unset all jutsu
        $GLOBALS['database']->execute_query("UPDATE `users_jutsu`
            SET `tagged` = REGEXP_REPLACE(`tagged`, '".$GLOBALS['userdata'][0]['taggedGroup'].":\\\\d+;', '')
            WHERE `uid` = '" . $_SESSION['uid'] . "' AND `tagged` != 'no'");

        //setting all jutsu with no tagged setting to "no"
        $GLOBALS['database']->execute_query("UPDATE `users_jutsu`
            SET `tagged` = 'no'
            WHERE `uid` = '" . $_SESSION['uid'] . "' AND `tagged` = '' ");

        require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
        $elements = new Elements($_SESSION['uid'], $GLOBALS['userdata'][0]['rank_id']);
        $elements = $elements->getUserElements();

        $pre_reqs = 
        ' AND (`jutsu`.`required_rank` <= '.$GLOBALS['userdata'][0]['rank_id'].' OR `jutsu`.`required_rank` = "" OR `jutsu`.`required_rank` IS NULL)'.
        ' AND (`jutsu`.`village` = "'.$GLOBALS['userdata'][0]['village'].'" OR `jutsu`.`village` = "" OR `jutsu`.`village` IS NULL)'.
        ' AND (`jutsu`.`bloodline` = "'.(explode(':',$GLOBALS['userdata'][0]['bloodline'])[0]).'" OR `jutsu`.`bloodline` = "" OR `jutsu`.`bloodline` IS NULL)'.
        ' AND (`jutsu`.`loyaltyRespectReq` <= '.$GLOBALS['userdata'][0]['vil_loyal_pts'].' OR `jutsu`.`loyaltyRespectReq` = "" OR `jutsu`.`loyaltyRespectReq` IS NULL OR `jutsu`.`loyaltyRespectReq` = 0)'.
        ' AND (`jutsu`.`element` = "'.$elements[0].'" OR `jutsu`.`element` = "'.$elements[1].'" OR `jutsu`.`element` = "'.$elements[2].'" OR `jutsu`.`element` = "none" OR `jutsu`.`element` = "None" OR `jutsu`.`element` = "" OR `jutsu`.`element` IS NULL) ';

        // Check all the jutsus posts
        $where = "";
        $set = "";
        for ($x = 1, $size = $this->getTagLimit(); $x <= $size; $x++) {
            if ( isset($_POST['jutsu'.$x]) && $_POST['jutsu'.$x] !== 'none' && $_POST['jutsu'.$x] !== 'None') {
                $where .= ($where === "") ? " `jid` = '" . $_POST['jutsu'.$x] . "' " : " OR `jid` = '" . $_POST['jutsu'.$x] . "' ";
                $set .= "WHEN `jid` = '" . $_POST['jutsu'.$x] . "' AND `tagged` = 'no' ".$pre_reqs." THEN '".$GLOBALS['userdata'][0]['taggedGroup'].":".$x.";' ";
                $set .= "WHEN `jid` = '" . $_POST['jutsu'.$x] . "' AND `tagged` != 'no' ".$pre_reqs." THEN concat(`tagged`, '".$GLOBALS['userdata'][0]['taggedGroup'].":".$x.";') ";
            }
        }



        // Update jutsu if any
        if(!empty($where)) {
            $GLOBALS['database']->execute_query("
                UPDATE `users_jutsu`, `jutsu`
                SET `tagged` = CASE ".$set."
                END
                WHERE (" . $where . ") AND `uid` = '" . $_SESSION['uid'] . "' AND `jutsu`.`id` = `users_jutsu`.`jid`" );
        }
    }

    public function selectLoadout($name) 
    {
        if(strlen($name) > 24)
            throw new Exception('Load out name is too long. Must be under 25 characters long.');

        $name = preg_replace("/[^A-Za-z0-9 ]/", "", $name);

        if($name != $GLOBALS['userdata'][0]['taggedGroup'] && $name != '')
        {
            if( $GLOBALS['database']->execute_query("UPDATE `users_statistics`
                SET `taggedGroup` = '".$name."'
                WHERE `uid` = '" . $_SESSION['uid'] . "'") === false)
                throw new Exception('Could not update user\'s selected loadout.');

            $GLOBALS['userdata'][0]['taggedGroup'] = $name;
        }
    }

    public function deleteLoadout($name) 
    {
        if(strtolower($name) == 'default')
        {
            $GLOBALS['page']->Message('The loadout "default" can not been deleted.', 'Jutsu Tagged', 'id=' . $_GET['id']);
            return;
        }

        if( $GLOBALS['database']->execute_query("UPDATE `users_jutsu`
            SET `tagged` = REGEXP_REPLACE(`tagged`, '".$name.":\\\\d+;', '')
            WHERE `uid` = '" . $_SESSION['uid'] . "' AND `tagged` != 'no'") === false)
            throw new Exception('Could not delete user\'s selected loadout.');

        if($GLOBALS['userdata'][0]['taggedGroup'] == $name)
        {
            if( $GLOBALS['database']->execute_query("UPDATE `users_statistics`
            SET `taggedGroup` = 'default'
            WHERE `uid` = '" . $_SESSION['uid'] . "'") === false)
            throw new Exception('Could not update user\'s selected loadout.');

            $GLOBALS['userdata'][0]['taggedGroup'] = 'default';
        }
    }
}
new jutsu();