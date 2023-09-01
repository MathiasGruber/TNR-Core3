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

require_once(Data::$absSvrPath.'/libs/professionSystem/OccupationData.php');
class occupationControl
{
    public $OccupationData;
    public $normal_occupation;
    public $special_occupation;

    public function __construct($quiet = false)
    {
        try
        {
            functions::checkActiveSession();

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            $this->OccupationData = new OccupationData();
            $this->normal_occupation = $this->OccupationData->getNormalOccupation();
            $this->special_occupation = $this->OccupationData->getSpecialOccupation();

            $confirming = false;

            if($quiet === false)
            {
                //handle no normal occupation stuffs
                if($this->normal_occupation['none'])
                {

                    //handle no act for normal
                    if(!isset($_GET['act']) || 0 !== strpos($_GET['act'], 'normal'))
                    {
                        $this->showNormalOccupationList();
                    }

                    //handle act for normal
                    else
                    {
                        //handle actions that would happen on normal occupation list page.
                        if($_GET['act'] == 'normalgetoccupation' && !$this->OccupationData->hasNormalOccupation())
                        {
                            $this->aquireNormalOccupation();
                            $this->showNormalOccupation();
                        }
                        else
                        {
                            $this->showNormalOccupationList();
                        }
                    }

                }

                //handle normal occupation stuffs
                else
                {
                    //handle no act for normal
                    if(!isset($_GET['act']) || 0 !== strpos($_GET['act'], 'normal'))
                    {
                        $this->showNormalOccupation();
                    }

                    //handle act for normal
                    else
                    {
                        //handle acctions that would happen on normal occupation.
                        if($_GET['act'] == 'normalquit' && $this->OccupationData->hasNormalOccupation())
                        {
                            if( isset($_POST['Submit']))
                            {
                                $this->quitNormalOccupation();
                                $this->showNormalOccupationList();
                            }
                            else
                            {
                                $confirming = true;
                                $GLOBALS['page']->Confirm("Are you sure you wish to quit your job as ".$this->normal_occupation['name'], 'Occupation System', 'Quit Now!');
                            }
                        }
                        else if($_GET['act'] == 'normalpromotion')
                        {
                            $prom_time = ($this->normal_occupation['promotion'] + 24*3600) - $GLOBALS['user']->load_time;
                            $next = $this->OccupationData->getNextNormalOccupation($GLOBALS['userdata'][0]['rank_id']);
                            if($next != false && $prom_time <= 0 && $this->normal_occupation['level'] >= 5)
                            {
                                //getting what the level will be after the change
                                $level = $this->normal_occupation['level'] - 4;

                                if($level < 1)
                                    $level = 1;

                                if( isset($_POST['Submit']))
                                {
                                    $this->promoteNormalOccupation($next, $level);
                                    $this->showNormalOccupation();
                                }
                                else
                                {
                                    $confirming = true;
                                    $GLOBALS['page']->Confirm("Are you sure you wish to take a promotion to ".$next['name']."<br>Doing so will lower your occupation level to ".$level."!", 'Occupation System', 'Take It!');
                                }


                            }
                            else
                            {
                                $this->showNormalOccupation();
                            }
                        }
                        else if($_GET['act'] == 'normallevelup')
                        {

                            if(($this->normal_occupation['collect_count'] >= (2 + $this->normal_occupation['level'])) && $this->normal_occupation['level'] < 10)
                            {
                                if(isset($_POST['Submit']))
                                {
                                    $this->normalLevelUp();
                                    $this->showNormalOccupation();
                                }
                                else
                                {
                                    $confirming = true;
                                    $GLOBALS['page']->Confirm("Are you ready to level up your occupation to level ".($this->normal_occupation['level']+1)."?", 'Occupation System', 'Ready!');
                                }
                            }
                            else
                            {
                                $this->showNormalOccupation();
                            }
                        }
                        else if ($_GET['act'] == 'normalcollect' && $this->getClaimTime() === false)
                        {
                            if(isset($_POST['Submit']))
                            {
                                $this->collectGain();
                                $this->showNormalOccupation();
                            }
                            else
                            {
                                $confirming = true;
                                $gains = $this->getGains();
                                $GLOBALS['page']->Confirm("Profession Experience: <b>".$gains['profGain']."</b> (if applicable.)<br>".
                                                          "Experience: <b>".$gains['expGain']."</b><br>".
                                                          "Stats: <b>".$gains['statGain']."</b><br>".
                                                          "Ryo: <b>".$gains['ryoGain']."</b><br>", 'Occupation System', 'Collect!');

                            }

                        }
                        else
                        {
                            $this->showNormalOccupation();
                        }
                    }

                }


                //handle no special occupation stuffs
                if(!$this->OccupationData->hasSpecialOccupation())
                {

                    //handle no act for special
                    if(!isset($_GET['act']) || 0 !== strpos($_GET['act'], 'special'))
                    {
                        $this->showSpecialOccupationList();
                    }

                    //handle act for special
                    else
                    {
                        //handle actions that would happen on special occupation list page.
                        if($_GET['act'] == 'specialgetoccupation' && !$this->OccupationData->hasSpecialOccupation())
                        {
                            $this->aquireSpecialOccupation();
                            $this->showSpecialOccupation();
                        }
                        else
                        {
                            $this->showSpecialOccupationList();
                        }
                    }
                }

                //handle special occupation stuffs
                else
                {
                    //handle no act for special
                    if(!isset($_GET['act']) || 0 !== strpos($_GET['act'], 'special'))
                    {
                        $this->showSpecialOccupation();
                    }

                    //handle act for special
                    else
                    {
                        //handle acctions that would happen on normal occupation.
                        if($_GET['act'] == 'specialquit' && $this->OccupationData->hasSpecialOccupation())
                        {
                            if( isset($_POST['Submit']))
                            {
                                $this->quitSpecialOccupation();
                                $this->showSpecialOccupationList();
                            }
                            else
                            {
                                $confirming = true;
                                $GLOBALS['page']->Confirm("Are you sure you wish to quit your job as ".$this->special_occupation['name'], 'Occupation System', 'Quit Now!');
                            }
                        }
                        else if($_GET['act'] == 'specialheal' && $this->OccupationData->hasSpecialOccupation())
                        {
                            $this->healUser();
                        }
                        else if ($_GET['act'] == 'specialmark' && $this->OccupationData->hasSpecialOccupation())
                        {
                            $this->markBounty();

                            $this->showSpecialOccupation();
                        }
                        else
                        {
                            $this->showSpecialOccupation();
                        }

                    }
                }


                if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false)
                {
                    throw new Exception('There was an issue releasing the lock!');
                }

                if(!$confirming)
                    $GLOBALS['template']->assign('contentLoad', './templates/content/occupation/occupations.tpl');
            }

        }
        catch (Exception $e)
        {
            $GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__);
            $GLOBALS['page']->Message( $e->getMessage() , 'Occupation System', 'id='.$_GET['id'],'Return');
        }

    }

    function aquireNormalOccupation($job = false)
    {
        //check if the user can get this job
        if(!$this->OccupationData->hasNormalOccupation())
        {
            if($job === false)
                $job = $_GET['job'];

            //get occupation and check
            $occupation = $this->OccupationData->getNormalOccupations($GLOBALS['userdata'][0]['rank_id'], $job);
            if($occupation != false)
            {
                //set occupation

                $GLOBALS['Events']->acceptEvent('occupation_change', array('new'=>$occupation['id'], 'old'=>0));//$this->OccupationData->getNormalOccupation()['id']));
                $this->OccupationData->setNormalOccupation($occupation['id']);
                $this->normal_occupation = $this->OccupationData->getNormalOccupation();
            }
        }
    }

    function aquireSpecialOccupation($job = false)
    {
        //check if the user can get this job
        if(!$this->OccupationData->hasSpecialOccupation())
        {
            if($job === false)
                $job = $_GET['job'];

            //get occupation and check
            $occupation = $this->OccupationData->getSpecialOccupations($GLOBALS['userdata'][0]['rank_id'], $GLOBALS['userdata'][0]['village'], $job);
            if($occupation != false)
            {
                //set occupation
                $GLOBALS['Events']->acceptEvent('special_occupation_change', array('new'=>$occupation['id'], 'old'=>0));
                $this->OccupationData->setSpecialOccupation($occupation['id']);
                $this->special_occupation = $this->OccupationData->getSpecialOccupation();
            }
        }
    }

    function showNormalOccupationList()
    {
        $normal_occupations = $this->OccupationData->getNormalOccupations($GLOBALS['userdata'][0]['rank_id']);

        $GLOBALS['template']->assign('normal_occupations', $normal_occupations);
    }

    function showSpecialOccupationList()
    {
        $special_occupations = $this->OccupationData->getSpecialOccupations($GLOBALS['userdata'][0]['rank_id'], $GLOBALS['userdata'][0]['village']);

        $GLOBALS['template']->assign('special_occupations', $special_occupations);
    }

    function showNormalOccupation()
    {
        //set gains
        $gains = $this->getGains();

        //check gains
        $claim_time = $this->getClaimTime();

        //check for promotion
        $prom_time = ($this->normal_occupation['promotion'] + 24*3600) - $GLOBALS['user']->load_time;
        $next = $this->OccupationData->getNextNormalOccupation($GLOBALS['userdata'][0]['rank_id']);

        if($next != false && $this->normal_occupation['level'] >= 5)
            if($prom_time <= 0)
                $check_promotion = 'promotion';
            else
                $check_promotion = functions::convert_time($prom_time, 'promotiontime', 'false');
        else
            if($this->normal_occupation['collect_count'] >= (2 + $this->normal_occupation['level']) && $this->normal_occupation['level'] < 10)
                $check_promotion = 'level_up';
            else if ($this->normal_occupation['level'] >= 10)
                $check_promotion = 'You are at max level.';
            else
                if(((2+$this->normal_occupation['level']) - $this->normal_occupation['collect_count']) != 1)
                    $check_promotion = 'You need to claim '.((2+$this->normal_occupation['level']) - $this->normal_occupation['collect_count']).' more times to progress.';
                else
                    $check_promotion = 'You need to claim '.((2+$this->normal_occupation['level']) - $this->normal_occupation['collect_count']).' more time to progress.';

        $gains['stats'] = $this->normal_occupation['gain_1'];

        if($this->normal_occupation['gain_2'] != '')
            $gains['stats'] .= ', '.$this->normal_occupation['gain_2'];

        if($this->normal_occupation['gain_3'] != '')
            $gains['stats'] .= ', '.$this->normal_occupation['gain_3'];


        //update smarty
        $GLOBALS['template']->assign('normal_occupation', $this->normal_occupation);
        $GLOBALS['template']->assign('gains', $gains);
        $GLOBALS['template']->assign('claim_time', $claim_time);
        $GLOBALS['template']->assign('check_promotion', $check_promotion);
    }

    function showSpecialOccupation()
    {
        if($this->special_occupation['name'] == 'Surgeon' || $this->special_occupation['name'] == '"Veterinarian"')
            $this->showSurgeon();
        else if($this->special_occupation['name'] == 'Bounty Hunter' || $this->special_occupation['name'] == 'Mercenary')
            $this->showBountyHunter();
        else
            $GLOBALS['DebugTool']->push('how did you get here!!!!', 'SERIOUSLY PLEASE TELL ME!!!!(looks like special occupation names have been changes)', __METHOD__, __FILE__, __LINE__);
    }

    function showSurgeon()
    {
        $min =  tableParser::get_page_min();

        // Set data
        $sp_level = 1 + floor($this->special_occupation['surgeonSP_exp'] / 10000);
        $cp_level = 1 + floor($this->special_occupation['surgeonCP_exp'] / 10000);

        if($sp_level >= 1000)
        {
            $sp_level = 1000;
            $healSP = 150;
        }
        else
            $healSP = 0.9 + $sp_level * 0.1;

        if($cp_level >= 1000)
        {
            $cp_level = 1000;
            $healCP = 150;
        }
        else
            $healCP = 0.9 + $cp_level * 0.1;

        // Select allied villages
        $vils = "";
        $vil_for_region = array(strtolower($GLOBALS['userdata'][0]['village']));
        foreach ( $GLOBALS['userdata'][0]['alliance'][0] as $village => $status) {
            if ($status == 1 && !empty($village) ) {
                $vil_for_region[] = strtolower($village);
                $vils .= ( $vils == "" ) ? "`village` = '" . ucfirst($village) . "'" : " OR `village` = '" . ucfirst($village) . "'";
            }
        }

        //get alliance information
        $alliance_status = 0;
        if(isset($GLOBALS['userdata'][0]['alliance'][0][ucfirst($GLOBALS['current_tile'][3])]))
            $alliance_status = $GLOBALS['userdata'][0]['alliance'][0][ucfirst($GLOBALS['current_tile'][3])];

        //set up query
        $injuryQuery = "SELECT
            `id`,`username`,`cur_health`,`max_health`,`last_activity`,
            `latitude`,`longitude`, `village`,`status`,
            (`max_health` - `cur_health`) AS `injury`
        FROM
            `users`,`users_timer`,`users_statistics`,`users_preferences`
        WHERE
            `id` = `userid` AND
            `users_statistics`.`uid` = `userid` AND
            users_preferences.`uid` = `userid` AND
            `enable_heal` = '1' AND
            (" . $vils . ")
        ";

        //at war(3*3)
        if($alliance_status == 2)
        {
            $injuryQuery .= " AND ABS(`latitude` - {$GLOBALS['userdata'][0]['latitude']}) <= 1 && ABS(`longitude` - {$GLOBALS['userdata'][0]['longitude']}) <= 1 ";
        }

        //neutral with
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

            $injuryQuery .= " AND (
                                (
                                    (`region` in ($war_regions))
                                    AND
                                    ABS(`latitude` - {$GLOBALS['userdata'][0]['latitude']}) <= 1
                                    AND 
                                    ABS(`longitude` - {$GLOBALS['userdata'][0]['longitude']}) <= 1
                                )
                                OR 
                                ( 
                                    !(`region` in ($war_regions))
                                    AND
                                    ABS(`latitude` - {$GLOBALS['userdata'][0]['latitude']}) <= 2 
                                    AND 
                                    ABS(`longitude` - {$GLOBALS['userdata'][0]['longitude']}) <= 2 
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
            //get war regions and nuetral regions
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
            
            $injuryQuery .= " AND (
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
                                )
                            )";
        }
        else if($alliance_status == 1 && in_array($GLOBALS['userdata'][0]['location'],array('Konoki','Samui','Shroud','Silence','Shine','Gambler\'s Den','Bandit\'s Outpost','Poacher\'s Camp','Pirate\'s Hideout')))
        {
            $injuryQuery .= " AND ABS(`latitude` - {$GLOBALS['userdata'][0]['latitude']}) <= 6 && ABS(`longitude` - {$GLOBALS['userdata'][0]['longitude']}) <= 6 ";
        }
        else
            throw new exception('bad alliance status: '.$alliance_status);

        // Get Injured people in this location
        $injured = $GLOBALS['database']->fetch_data(
            $injuryQuery . " AND
                (`max_health` - `cur_health`) > 1  AND
                `last_activity` > '" . ($GLOBALS['user']->load_time - 300) . "' AND
                `status` != 'combat' AND `status` != 'exiting_combat'
            ORDER BY
                (`max_health` - `cur_health`)
            DESC
            LIMIT " . $min . ",20");

        // Fix up entries
        if( $injured !== "0 rows" ){
            for( $i=0 ; $i < count($injured) ; $i++ ){

                // Costs
                $sta_cost = $injured[$i]['injury'] / $healSP;
                $cha_cost = $injured[$i]['injury'] / $healCP;

                // Add to array
                foreach( array("sta","cha") as $type ){
                    if( ${$type."_cost"} <= $GLOBALS['userdata'][0][ "cur_".$type ] ){
                        $injured[$i][ $type."_heal" ] = '<a href="?id=' . $_GET['id'] . '&act=specialheal&user=' . $injured[$i]['id'] . '&type=' . $type . '">Heal</a>';
                    }
                    else{
                        $injured[$i][ $type."_heal" ] = "Not Enough";
                    }
                }

                // Location
                $injured[$i]["location"] = $injured[$i]["longitude"].".".$injured[$i]["latitude"];
            }
        }

        $surgeon_type = '';
        $villager_type = '';
        $ninja_type = '';
        if($this->special_occupation['name'] == 'Surgeon')
        {
            $surgeon_type = 'Surgeon';
            $villager_type = 'villager';
            $ninja_type = 'medical';
        }
        else if($this->special_occupation['name'] == '"Veterinarian"')
        {
            $surgeon_type = '"Veterinarian"';
            $villager_type = 'outlaw';
            $ninja_type = 'disgraced medical';
        }

        // Show the table of users
        tableParser::show_list(
            'users',
            $surgeon_type.' Options',
            $injured,
            array(
                'username' => "Username",
                'injury' => "Injury (HP)",
                'village' => "Village",
                'location' => "Location",
                'cha_heal' => "Chakra Heal",
                'sta_heal' => "Stamina Heal"
            ),
            false,
            false,   // Send directly to contentLoad
            true,   // Show previous/next links
            array(
                array("name" => "Leave Job", "href" =>"?id=".$_GET["id"]."&amp;act=specialquit")
            ),  // links at the top to show
            true,   // Allow sorting on columns
            false,   // pretty-hide options
            false,  // Top options
            "As a {$ninja_type} ninja your objective is to heal {$villager_type}s.
             <br><i>You can heal {$villager_type}s using Chakra at ". $healCP ." HP/CP or Stamina at ". $healSP ." HP/SP</i><br>
             <br><b>Chakra:</b> Level: ".$cp_level." - Experience: ".$this->special_occupation['surgeonCP_exp']."
             <br><b>Stamina:</b> Level: ".$sp_level." - Experience: ".$this->special_occupation['surgeonSP_exp']

        );
    }

    function healUser()
    {
        // Check user ID
        if (is_numeric($_GET['user'])) {

            // Check type
            if(  $_GET['type'] == 'cha' || $_GET['type'] == 'sta'  ){

                // Set data
                // Level Data
                $sp_level = 1 + floor($this->special_occupation['surgeonSP_exp'] / 10000);
                $cp_level = 1 + floor($this->special_occupation['surgeonCP_exp'] / 10000);

                if($sp_level >= 1000)
                {
                    $sp_level = 1000;
                    $healSP = 150;
                }
                else
                    $healSP = 0.9 + $sp_level * 0.1;

                if($cp_level >= 1000)
                {
                    $cp_level = 1000;
                    $healCP = 150;
                }
                else
                    $healCP = 0.9 + $cp_level * 0.1;

                // Select allied villages
                $vils = "";
                $vil_for_region = array(strtolower($GLOBALS['userdata'][0]['village']));
                foreach ( $GLOBALS['userdata'][0]['alliance'][0] as $village => $status) {
                    if ($status == 1 && !empty($village) ) {
                        $vil_for_region[] = strtolower($village);
                        $vils .= ( $vils == "" ) ? "`village` = '" . ucfirst($village) . "'" : " OR `village` = '" . ucfirst($village) . "'";
                    }
                }

                //get alliance information
                $alliance_status = 0;
                if(isset($GLOBALS['userdata'][0]['alliance'][0][ucfirst($GLOBALS['current_tile'][3])]))
                    $alliance_status = $GLOBALS['userdata'][0]['alliance'][0][ucfirst($GLOBALS['current_tile'][3])];

                //set up query
                $injuryQuery = "SELECT
                    `id`,`username`,`cur_health`,`max_health`,`last_activity`,
                    `latitude`,`longitude`, `village`,`status`,
                    (`max_health` - `cur_health`) AS `injury`
                FROM
                    `users`,`users_timer`,`users_statistics`,`users_preferences`
                WHERE
                    `id` = `userid` AND
                    `users_statistics`.`uid` = `userid` AND
                    users_preferences.`uid` = `userid` AND
                    `enable_heal` = '1' AND
                    (" . $vils . ")
                ";

                //at war(3*3)
                if($alliance_status == 2)
                {
                    $injuryQuery .= " AND ABS(`latitude` - {$GLOBALS['userdata'][0]['latitude']}) <= 1 && ABS(`longitude` - {$GLOBALS['userdata'][0]['longitude']}) <= 1 ";
                }

                //neutral with
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

                    $injuryQuery .= " AND (
                                            (
                                                (`region` in ($war_regions))
                                                AND
                                                ABS(`latitude` - {$GLOBALS['userdata'][0]['latitude']}) <= 1
                                                AND 
                                                ABS(`longitude` - {$GLOBALS['userdata'][0]['longitude']}) <= 1
                                            )
                                            OR 
                                            ( 
                                                !(`region` in ($war_regions))
                                                AND
                                                ABS(`latitude` - {$GLOBALS['userdata'][0]['latitude']}) <= 2 
                                                AND 
                                                ABS(`longitude` - {$GLOBALS['userdata'][0]['longitude']}) <= 2 
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
                    //get war regions and nuetral regions
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
            
                    $injuryQuery .= " AND (
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
                                            )
                                        )";
                }
                else if($alliance_status == 1 && in_array($GLOBALS['userdata'][0]['location'],array('Konoki','Samui','Shroud','Silence','Shine','Gambler\'s Den','Bandit\'s Outpost','Poacher\'s Camp','Pirate\'s Hideout')))
                {
                    $injuryQuery .= " AND ABS(`latitude` - {$GLOBALS['userdata'][0]['latitude']}) <= 6 && ABS(`longitude` - {$GLOBALS['userdata'][0]['longitude']}) <= 6 ";
                }
                else
                    throw new exception('bad alliance status: '.$alliance_status);

                // Get the injured user
                $injured = $GLOBALS['database']->fetch_data(
                    $injuryQuery . " AND `id` =  '" . $_GET['user'] . "' FOR UPDATE"
                );

                //	Check return from query
                if ($injured != '0 rows') {

                    // Check if in battle
                    if ( $injured[0]['status'] !== 'combat' && $injured[0]['status'] !== 'exiting_combat' ) {

                        //	Calculate cost
                        if ($injured[0]['injury'] > 0) {

                            // Get the pool cost
                            switch( $_GET['type'] ){
                                case "cha":
                                    $pool_cost = $injured[0]['injury'] / $healCP;
                                    $current_pool = $GLOBALS['userdata'][0][ 'cur_cha' ];
                                    $expColumn = "surgeonCP_exp";
                                    $typeTxt = "chakra";
                                    if(($this->special_occupation['surgeonCP_exp'] + ($pool_cost / 10)) > 10000 * 1000 )
                                    {
                                        $difference = (10000 * 1000) - $this->special_occupation['surgeonCP_exp'];
                                        if ($difference < 0)
                                            $difference = 0;

                                        $over_cap = true;
                                    }
                                    else
                                        $over_cap = false;
                                    break;
                                case "sta":
                                    $pool_cost = $injured[0]['injury'] / $healSP;
                                    $current_pool = $GLOBALS['userdata'][0][ 'cur_sta' ];
                                    $expColumn = "surgeonSP_exp";
                                    $typeTxt = "stamina";
                                    if(($this->special_occupation['surgeonSP_exp'] + ($pool_cost / 10)) > 10000 * 1000 )
                                    {
                                        $difference = (10000 * 1000) - $this->special_occupation['surgeonSP_exp'];
                                        if ($difference < 0)
                                            $difference = 0;

                                        $over_cap = true;
                                    }
                                    else
                                        $over_cap = false;
                                    break;
                            }

                            // Round Values
                            $pool_cost = round($pool_cost, 1);

                            // Check cost:
                            if ( $pool_cost <= $current_pool ) {

                                // Experience gain
                                $exp_gain = round($pool_cost / 10);

                                // Check for global event modifications
                                if( $event = functions::getGlobalEvent("IncreasedSurgeonExp")){
                                    if( isset( $event['data']) && is_numeric( $event['data']) ){
                                        $exp_gain *= round($event['data'] / 100,2);
                                    }
                                }

                                // Update of user
                                $userQuery = "
                                    UPDATE `users_statistics`,`users_occupations`
                                    SET
                                        `".$expColumn."` = `".$expColumn."` + '" . $exp_gain . "',
                                        `cur_".$_GET['type']."` = `cur_".$_GET['type']."` - '" . $pool_cost . "'
                                    WHERE
                                        `uid` = '" . $_SESSION['uid'] . "' AND
                                        `uid` = `userid` AND
                                        (`cur_".$_GET['type']."` - '" . $pool_cost . "') > 0";

                                // Update the target
                                $targetQuery = "
                                    UPDATE `users_statistics`
                                    SET
                                        `cur_health` = `max_health`
                                    WHERE
                                        `users_statistics`.`uid` = '" . $_GET['user'] . "'";

                                $GLOBALS['Events']->acceptEvent('surgeon_heal', array('extra'=>$injured[0]['injury'], 'data'=> $injured[0]['username']));

                                // Execute queries
                                if (
                                    $GLOBALS['database']->execute_query($userQuery) &&
                                    $GLOBALS['database']->execute_query($targetQuery)
                                ) {

                                    $users_notifications = new NotificationSystem('', $_GET['user']);

                                    $users_notifications->addNotification(array(
                                                        'id' => 2,
                                                        'duration' => 'none',
                                                        'text' => 'You have been healed by ' . $GLOBALS['userdata'][0]['username'],
                                                        'dismiss' => 'yes'
                                                    ));


                                    $users_notifications->recordNotifications();

                                    // Everything should be good at this point
                                    $GLOBALS['database']->transaction_commit();

                                    // Information
                                    $GLOBALS['template']->assign('heal_message', 'You have healed ' . $injured[0]['username'] . '<br>
                                                                                  This has used up ' . $pool_cost . ' '.$typeTxt.' and
                                                                                  awarded you ' . $exp_gain . ' experience as a medical ninja');

                                    if($expColumn == 'surgeonSP_exp')
                                        $this->OccupationData->updateSpecialOccupationCache(NULL, $this->special_occupation['surgeonSP_exp'] + $exp_gain, NULL);
                                    else
                                        $this->OccupationData->updateSpecialOccupationCache(NULL, NULL, $this->special_occupation['surgeonCP_exp'] + $exp_gain);


                                } else {
                                    throw new Exception("You do not have enough chakra to do this.");
                                }
                            } else {
                                throw new Exception("You do not have enough chakra to do this.");
                            }
                        } else {
                            throw new Exception("This user is not injured.");
                        }
                    } else {
                        throw new Exception("You cannot heal a user that is in battle.");
                    }
                } else {
                    throw new Exception("There was an error with this user, please try again");
                }
            }
            else{
                throw new Exception("Could not identify the type of healing.");
            }
        } else {
            throw new Exception("This is an invalid user ");
        }
    }

    function showBountyHunter()
    {
        // Set data

        // Select the type of bounty being collected
        if( $GLOBALS['userdata'][0]['village'] == "Syndicate" ){
            $bountyType = "SpecialBounty";
        }
        else{
            $bountyType = $GLOBALS['userdata'][0]['village'];
        }

        // Set the level
        $hunterLevel = ceil($this->special_occupation['bountyHunter_exp'] / 1000);

        if($hunterLevel > 500)
            $hunterLevel = 500;

        // Experience to next level
        if($hunterLevel != 500)
            $expToNextLevel = 1000 - $this->special_occupation['bountyHunter_exp'] % 1000;
        else
            $expToNextLevel = 0;

        // Calculate the gains from this level
        // Check if special bounty of normal
        if( $bountyType == "SpecialBounty" ){

            // Get max bounties & bonus
            switch( true ){
                case $hunterLevel < 100: $bonus = 2.5; break;
                case $hunterLevel < 200: $bonus = 5; break;
                case $hunterLevel < 300: $bonus = 7.5; break;
                case $hunterLevel < 400: $bonus = 10; break;
                case $hunterLevel < 500: $bonus = 12.5; break;
                default: $bonus = 15; break;
            }

        }
        else{

            // Get max bounties & bonus
            switch( true ){
                case $hunterLevel < 100: $bonus = 5; break;
                case $hunterLevel < 200: $bonus = 7.5; break;
                case $hunterLevel < 300: $bonus = 10; break;
                case $hunterLevel < 400: $bonus = 12.5; break;
                case $hunterLevel < 500: $bonus = 15; break;
                default: $bonus = 17.5; break;
            }
        }

        // Calculate max bounty
        if ($bountyType == 'SpecialBounty' && $hunterLevel < 500)
        {
            $maxBounty = 150000 + ( 11250 * $hunterLevel );

            if($hunterLevel < 50)
                $maxBounty = 150000 + ( 11250 * 50 );
        }
        else if( $hunterLevel < 500 ){
            $maxBounty = 1000 + ( 75 * $hunterLevel );

            if($hunterLevel < 50)
                $maxBounty = 1000 + ( 75 * 50 );
        }
        else{
            $maxBounty = 999999999999999999;
        }

        // Reduction in harvesting
        switch( true ){
            case $hunterLevel < 101: $harvestTime = 10; break;
            case $hunterLevel < 201: $harvestTime = 15; break;
            case $hunterLevel < 301: $harvestTime = 20; break;
            case $hunterLevel < 401: $harvestTime = 25; break;
            case $hunterLevel < 501: $harvestTime = 30; break;
            default: $harvestTime = 30; break;
        }

        // Set max bounty / gain
        $maxBounty = $maxBounty;
        $bountyBonus = $bonus;

        switch($GLOBALS['userdata'][0]['rank_id']) {
            case('4'): $rank_range = " `rank_id` IN ('3', '4', '5') "; break;
            case('5'): $rank_range = " `rank_id` IN ('4', '5') "; break;
            default: $rank_range = " `rank_id` IN ('3', '4') "; break;
        }

        // The select query
        $hunterSelectQuery = "
            SELECT
                ABS(`bingo_book`.`" . $bountyType . "`) as `".$bountyType."`, `users`.`username`,
                `users`.`latitude`, `users`.`longitude`, `users`.`id`, `users_timer`.`last_activity`
            FROM `users`,`bingo_book`,`users_timer`,`users_statistics`
            WHERE
                `bingo_book`.`userID` = `users`.`id` AND
                `users`.`id` = `users_timer`.`userID` AND
                `users`.`id` = `users_statistics`.`uid` AND
                ".$rank_range." AND
                `" . $bountyType . "` < 0 AND
                `" . $bountyType . "` > -".$maxBounty;

        $min =  tableParser::get_page_min();
        $order =  tableParser::get_page_order( array("username", $bountyType, "location") );
        if( empty($order) ){
            $order = "ORDER BY `" . $bountyType . "` ASC";
        }

        // Get the outlaws of this village / syndicate
        $outlaws = $GLOBALS['database']->fetch_data(
            $hunterSelectQuery . " AND
                `users_timer`.`last_activity` > " . ($GLOBALS['user']->load_time - 120) . "
            " . $order . "
            LIMIT " . $min . ",20");

        // Fix up entries
        if( $outlaws !== "0 rows" ){
            for( $i=0 ; $i < count($outlaws) ; $i++ ){
                if($outlaws[$i]['username'] == $this->special_occupation['feature'])
                    $outlaws[$i]["location"] = $outlaws[$i]["longitude"].".".$outlaws[$i]["latitude"];
                else
                    $outlaws[$i]["location"] = '?.?';
            }
        }

        // Set current tracking information
        $trackInfo = !empty($this->special_occupation['feature']) ? "<br><br>You are currently tracking: <b>".$this->special_occupation['feature'] ."</b>" : "";

        if($trackInfo != "")
            $leave_and_untrack_buttons = array(array("name" => "Leave Job", "href" =>"?id=".$_GET["id"]."&amp;act=specialquit"), array( "name" => "Untrack Target", "href" =>"?id=".$_GET["id"]."&amp;act=specialmark&mark=N/A"));
        else
            $leave_and_untrack_buttons = array(array("name" => "Leave Job", "href" =>"?id=".$_GET["id"]."&amp;act=specialquit"));

        // How high can the user claim
        $claim = ($hunterLevel < 500) ? "bounties up to " . $maxBounty : "all bounties";

        // Show the table of users
        tableParser::show_list(
            'bounties',
            'Interesting bounties ',
            $outlaws,
            array(
                'username' => "Username",
                $bountyType => "Bounty",
                'location' => "Location"
            ),
            array(
                array("name" => "View Profile", "id" => "13", "page" => "profile", "profile" => "table.username"),
                array("name" => "Track", "id" => $_GET['id'], "act" => "specialmark", "mark" => "table.username")
            ),
            false,   // Send directly to contentLoad
            true,   // Show previous/next links
            $leave_and_untrack_buttons,  // links at the top to show
            true,   // Allow sorting on columns
            false,   // pretty-hide options
            false,  // Top options
            "As a ".$this->special_occupation['name'].", you get to track and eliminate others to earn ryo. <br>You are currently lvl " . $hunterLevel . " with ".$this->special_occupation['bountyHunter_exp']." exp which means you can claim ".$claim." and get a " . $bountyBonus . "% bonus to the payout, and a ".$harvestTime." seconds reduction in resource gathering time. You need ".$expToNextLevel." exp to get your next level." .
             $trackInfo
        );

    }

    function markBounty()
    {
        // Set data
        // The select query
        // Select the type of bounty being collected
        if( $GLOBALS['userdata'][0]['village'] == "Syndicate" ){
            $bountyType = "SpecialBounty";
        }
        else{
            $bountyType = $GLOBALS['userdata'][0]['village'];
        }

        $level = ceil($this->special_occupation['bountyHunter_exp'] / 1000);

        if($level > 500)
            $level = 500;

        // Calculate max bounty
        if( $bountyType == "SpecialBounty" && $level < 500)
        {
            $maxBounty = 150000 + ( 11250 * $level );

            if($level < 50)
                $maxBounty = 150000 + ( 11250 * 50 );
        }
        else if( $level < 500 ){
            $maxBounty = 1000 + ( 75 * $level );

            if($level < 50)
                $maxBounty = 1000 + ( 75 * 50 );
        }
        else{
            $maxBounty = 999999999999999999;
        }


        //find all users killed in the last half hour.
        $select_query = "SELECT `id`,`time`,`type`,`census` FROM `battle_history` WHERE `census` like '%,".$GLOBALS['userdata'][0]['username']."/%' AND (`type` = '04') AND `time` > ".($GLOBALS['user']->load_time - 60*30);
        $result = '';

        try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception('query failed'); }
        catch (Exception $e)
        {
            try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception ('query failed'); }
            catch (Exception $e)
            {
                try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception ('query failed to update user information'); }
                catch (Exception $e)
                {
                    $GLOBALS['DebugTool']->push('','there was an error getting battle history information.', __METHOD__, __FILE__, __LINE__);
                    throw $e;
                }
            }
        }

        if($result != '0 rows')//if there is a history to look at
        {
            foreach($result as $record)//going through all records
            {
                $record_users = array();
                foreach(explode(',',$record['census']) as $users) //going through all users in record
                {
                    if($users != '')
                    {
                        $userdata = explode('/',$users);
                        $record_users[$userdata[0]] = $userdata[3];//getting username and status
                    }
                }

                if($record_users[$GLOBALS['userdata'][0]['username']] == 'win') //if this user won the battle
                {
                    foreach($record_users as $users_to_check_name => $users_to_check_status) //for each user in the battle
                    {
                        if($users_to_check_status != 'win' && $users_to_check_name == $_GET['mark']) //if they did not win
                        {
                            throw new Exception('You cannot track a user you have recently defeated.<br/>Please try again later.');
                        }
                    }
                }
            }
        }

        switch($GLOBALS['userdata'][0]['rank_id']) {
            case('4'): $rank_range = " `rank_id` IN ('3', '4', '5') "; break;
            case('5'): $rank_range = " `rank_id` IN ('4', '5') "; break;
            default: $rank_range = " `rank_id` IN ('3', '4') "; break;
        }

        $hunterSelectQuery = "
            SELECT
                ABS(`bingo_book`.`" . $bountyType . "`) as `".$bountyType."`, `users`.`username`,
                `users`.`latitude`, `users`.`longitude`, `users`.`id`, `users_timer`.`last_activity`
            FROM `users`,`bingo_book`,`users_timer`,`users_statistics`
            WHERE
                `bingo_book`.`userID` = `users`.`id` AND
                `users`.`id` = `users_timer`.`userID` AND
                `users`.`id` = `users_statistics`.`uid` AND
                ".$rank_range." AND
                `" . $bountyType . "` < 0 AND
                `" . $bountyType . "` > -".$maxBounty;

        // Check if setting or unsetting
        if($_GET['mark'] == 'N/A')
        {
            $this->OccupationData->setFeature('');
            $this->special_occupation['feature'] = '';
        }

        else
        {
            if($this->special_occupation['feature'] != $_GET['mark'])
            {
                // Get the user in question
                $outlaw = $GLOBALS['database']->fetch_data($hunterSelectQuery . " AND `username` = '" . $_GET['mark'] . "' LIMIT 1");

                // Check if that outlaw exists, and if so, set mark
                if( $outlaw !== "0 rows" )
                {
                    $this->OccupationData->setFeature($outlaw[0]['username']);
                    $this->special_occupation['feature'] = $outlaw[0]['username'];
                }


                else
                    throw new Exception("Could not identify this user.");
            }
        }
    }

    function getGains()
    {
        // Set stat gain base
        $ocupation_level = $this->normal_occupation['rankid'] - 1;

        if ($ocupation_level == 1)
        {
            $start_stat = 4;
            $level_stat = 1;
            $start_ryo = 2000;
            $level_ryo = 500;
            $start_exp = 400;
            $level_exp = 100;
            $start_prof = 0.25;
            $level_prof = 0.25;
        }
        else if ($ocupation_level == 2)
        {
            $start_stat = 6;
            $level_stat = 1.5;
            $start_ryo = 6750;
            $level_ryo = 750;
            $start_exp = 1800;
            $level_exp = 450;
            $start_prof = 0.375;
            $level_prof = 0.375;
        }
        else if ($ocupation_level == 3)
        {
            $start_stat = 7.5;
            $level_stat = 2.5;
            $start_ryo = 17500;
            $level_ryo = 2500;
            $start_exp = 2250;
            $level_exp = 750;
            $start_prof = 0.5;
            $level_prof = 0.5;
        }

        $statgain   = $start_stat + ($level_stat * $this->normal_occupation['level']);
        $ryogain  = $start_ryo  + ($level_ryo  * $this->normal_occupation['level']);
        $expgain  = $start_exp  + ($level_exp  * $this->normal_occupation['level']);
        $profgain = $start_prof + ($level_prof * $this->normal_occupation['level']);

        // Adjust from loyalty
        if( $GLOBALS['userdata'][0]['activateBonuses'] == "yes" ){
            switch( true ){
                case $GLOBALS['userdata'][0]['vil_loyal_pts'] >= 140: $statgain *= 3.0; $expgain *= 3.0; break;
                case $GLOBALS['userdata'][0]['vil_loyal_pts'] >= 75:  $statgain *= 2.2; $expgain *= 2.2; break;
                case $GLOBALS['userdata'][0]['vil_loyal_pts'] >= 30:  $statgain *= 1.8; $expgain *= 1.8; break;
                case $GLOBALS['userdata'][0]['vil_loyal_pts'] >= 10:  $statgain *= 1.4; $expgain *= 1.4; break;
            }
        }

        // Check for global event modifications
        if( $event = functions::getGlobalEvent("IncreasedOccupationGains")){
            if( isset( $event['data']) && is_numeric( $event['data']) ){
                $statgain   *= round($event['data'] / 100,2);
                $ryogain    *= round($event['data'] / 100,2);
                $expgain    *= round($event['data'] / 100,2);
                $profgain   *= round($event['data'] / 100,2);
            }
        }

        return array('statGain' => $statgain, 'ryoGain' => $ryogain, 'expGain' => $expgain, 'profGain' => $profgain );
    }

    function getClaimTime()
    {
        $time = ($this->normal_occupation['last_gain'] + 24*3600) - $GLOBALS['user']->load_time;

        if($time > 0)
            return functions::convert_time($time, 'gaintime', 'false');
        else
            return false;
    }

    function quitNormalOccupation()
    {
        $GLOBALS['Events']->acceptEvent('occupation_change', array('new'=>0, 'old'=>$this->OccupationData->getNormalOccupation()['id']));
        $this->OccupationData->quitNormalOccupation();
        $this->normal_occupation = $this->OccupationData->getNormalOccupation();
    }

    function quitSpecialOccupation()
    {
        $GLOBALS['Events']->acceptEvent('special_occupation_change', array('new'=>0, 'old'=>$this->OccupationData->getSpecialOccupation()['id']));

        $this->OccupationData->quitSpecialOccupation();
        $this->special_occupation = $this->OccupationData->getSpecialOccupation();
    }

    function promoteNormalOccupation($new_occupation, $level)
    {
        //set occupation
        $GLOBALS['Events']->acceptEvent('occupation_change', array('new'=>$new_occupation['id'], 'old'=>$this->OccupationData->getNormalOccupation()['id']));
        $this->OccupationData->setNormalOccupation($new_occupation['id'], $level);
        $this->normal_occupation = $this->OccupationData->getNormalOccupation();
    }

    function normalLevelUp()
    {
        $this->OccupationData->levelNormalOccupation();
        $GLOBALS['Events']->acceptEvent('occupation_level', array('new'=>$this->normal_occupation['level']+1, 'old'=>$this->normal_occupation['level']));
        $this->normal_occupation['level'] += 1;
        $this->normal_occupation['collect_count'] = 0;
        $this->normal_occupation['promotion'] = $GLOBALS['user']->load_time;
    }

    function collectGain()
    {
        $gains = $this->getGains();

        //give gains
        $query = "";
        for( $n=1 ; $n <= 3 ; $n++ ){
            $column = $this->normal_occupation[ 'gain_'.$n ];
            if( !empty($column ) && !($column == 'n/a')){
                $query .= ($query == "") ? "`".$column."` = `".$column."` + '".$gains['statGain']."'" : ", `".$column."` = `".$column."` + '".$gains['statGain']."'";
            }
        }

        $query .= ", `money` = (`money` + ".$gains['ryoGain'].")";
        $query .= ", `experience` = (`experience` + ".$gains['expGain'].")";

        $GLOBALS['Events']->acceptEvent('experience', array('new'=>$GLOBALS['userdata'][0]['experience'] + $gains['expGain'], 'old'=> $GLOBALS['userdata'][0]['experience'] ));
        $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] + $gains['ryoGain']));


        $profession_exp_gain = 0;
        if( !empty($query) ){
            if($GLOBALS['database']->execute_query( "UPDATE `users_statistics` SET ".$query." WHERE `uid` = ".$_SESSION['uid']." LIMIT 1" ) === false )
                throw new Exception("there was an error when giving you your gains. "."UPDATE `users_statistics` SET ".$query." WHERE `uid` = ".$_SESSION['uid']." LIMIT 1");
        }

        $profession = $this->OccupationData->getProfession();
        if ( $profession['name']!='' && strpos($this->normal_occupation['professionSupport'], $profession['name']) !== false)
            if( ($GLOBALS['userdata'][0]['rank_id'] == 5 && $profession['exp'] + $gains['profGain'] < 450) ||
                ($GLOBALS['userdata'][0]['rank_id'] == 4 && $profession['exp'] + $gains['profGain'] < 300) ||
                ($GLOBALS['userdata'][0]['rank_id'] == 3 && $profession['exp'] + $gains['profGain'] < 150))
            {
                if($GLOBALS['database']->execute_query( "UPDATE `users_occupations` SET `profession_exp` = `profession_exp` + ".$gains['profGain']." WHERE `userid` = ".$_SESSION['uid']) === false)
                    throw new Exception('there was an error when giving you your gains. '."UPDATE `users_occupations` SET `profession_exp` = `profession_exp` + ".$gains['profGain']." WHERE `userid` = ".$_SESSION['uid']);

                $profession_exp_gain = $gains['profGain'];
                $GLOBALS['Events']->acceptEvent('profession_exp', array('new'=>$profession['exp'] + $gains['profGain'], 'old'=> $profession['exp'] ));
            }


            else
            {
                if ($GLOBALS['userdata'][0]['rank_id'] == 5)
                    $diff = 450 - $profession['exp'];
                else if ($GLOBALS['userdata'][0]['rank_id'] == 4)
                    $diff = 300 - $profession['exp'];
                else if ($GLOBALS['userdata'][0]['rank_id'] == 3)
                    $diff = 150 - $profession['exp'];

                $profession_exp_gain = $diff;

                if($diff != 0)
                    if($GLOBALS['database']->execute_query( "UPDATE `users_occupations` SET `profession_exp` = `profession_exp` + ".$diff." where `userid` = ".$_SESSION['uid']) === false)
                    {
                        throw new Exception('there was an error upating you gains timer. '. "UPDATE `users_occupations` SET `profession_exp` = `profession_exp` + ".$diff." where `userid` = ".$_SESSION['uid']);
                    }
                    else
                        $GLOBALS['Events']->acceptEvent('profession_exp', array('new'=>$profession['exp'] + $diff, 'old'=> $profession['exp'] ));
            }

        //update timer
        $this->OccupationData->respondToGainsCollection($profession_exp_gain);
        $this->normal_occupation = $this->OccupationData->getNormalOccupation();
    }

}

new OccupationControl();