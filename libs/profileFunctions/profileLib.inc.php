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

class profileFunctions {

    // Set character data based on page being showed
    protected function setCharData($page, $uid) {
        switch($page) {
            case("level"): {
                $this->char_data = $GLOBALS['database']->fetch_data("SELECT `users`.`status`,

                    `users_statistics`.`level_id`, `users_statistics`.`experience`, `users_statistics`.`max_health`, `users_statistics`.`max_cha`,
                    `users_statistics`.`max_sta`, `users_statistics`.`rank_id`, `users_statistics`.`level`, `users_statistics`.`rank`,

                    `levels`.`experience_required`, `levels`.`rank` AS `levelRank`
                    FROM `users`
                        INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                        INNER JOIN `levels` ON (`levels`.`levelID` = `users_statistics`.`level_id`)
                        LEFT JOIN `levels` AS `next_level` ON (`next_level`.`levelID` = `users_statistics`.`level_id` + 1)
                    WHERE `users`.`id` = ".$uid." LIMIT 1");
            } break;
            case("statistics"): {
                $this->char_data = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username`, `users`.`gender`, `users`.`mail`,

                        `users_missions`.`errands`, `users_missions`.`scrimes`, `users_missions`.`arrested`, `users_missions`.`d_mission`,
                        `users_missions`.`c_mission`, `users_missions`.`b_mission`, `users_missions`.`a_mission`, `users_missions`.`s_mission`,
                        `users_missions`.`c_crime`, `users_missions`.`b_crime`, `users_missions`.`a_crime`, `users_missions`.`battles_won`,
                        `users_missions`.`battles_lost`, `users_missions`.`battles_fled`, `users_missions`.`battles_draws`, `users_missions`.`AIwon`,
                        `users_missions`.`AIlost`, `users_missions`.`AIfled`, `users_missions`.`AIdraw`, `users_missions`.`torn_record`,
                        `users_missions`.`structureDestructionPoints`, `users_missions`.`structureGatherPoints`, `users_missions`.`structurePointsActivity`,

                        `users_statistics`.`tai_off`, `users_statistics`.`nin_off`, `users_statistics`.`gen_off`, `users_statistics`.`weap_off`,
                        `users_statistics`.`tai_def`, `users_statistics`.`nin_def`, `users_statistics`.`gen_def`, `users_statistics`.`weap_def`,
                        `users_statistics`.`strength`, `users_statistics`.`intelligence`, `users_statistics`.`speed`, `users_statistics`.`willpower`,
                        `users_statistics`.`rep_ever`, `users_statistics`.`rep_now`, `users_statistics`.`pop_ever`, `users_statistics`.`pop_now`,
                        `users_statistics`.`user_rank`, `users_statistics`.`rank_id`, `users_statistics`.`money`, `users_statistics`.`bank`,
                        `users_statistics`.`dr`, `users_statistics`.`sr`, `users_statistics`.`cur_health`,
                        `users_statistics`.`level`, `users_statistics`.`federal_level`
                    FROM `users`
                        INNER JOIN `users_missions` ON (`users_missions`.`userid` = `users`.`id`)
                        INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    WHERE `users`.`id` = ".$uid." LIMIT 1");
            } break;
            case("exam"): {
                $this->char_data = $GLOBALS['database']->fetch_data("SELECT `users`.`username`, `users`.`status`, `users`.`village`,

                    `users_statistics`.`level_id`, `users_statistics`.`rank_id`, `users_statistics`.`experience`, `users_statistics`.`intelligence`,
                    `users_statistics`.`rank`, `users_statistics`.`gen_def`, `users_statistics`.`nin_def`, `users_statistics`.`tai_def`,
                    `users_statistics`.`weap_def`, `users_statistics`.`gen_off`, `users_statistics`.`nin_off`, `users_statistics`.`tai_off`,
                    `users_statistics`.`weap_off`, `users_statistics`.`strength`, `users_statistics`.`speed`, `users_statistics`.`willpower`,

                    `users_preferences`.`sensei`,

                    `levels`.`experience_required`
                    FROM `users`
                        INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                        INNER JOIN `users_preferences` ON (`users_preferences`.`uid` = `users`.`id`)
                        INNER JOIN `levels` ON (`levels`.`levelID` = `users_statistics`.`level_id`)
                        LEFT JOIN `levels` AS `next_level` ON (`next_level`.`levelID` = `users_statistics`.`level_id` + 1)
                    WHERE `users`.`id` = '" . $uid . "' LIMIT 1");
            } break;
            case("main"): {
                $this->char_data = $GLOBALS['database']->fetch_data('SELECT `levels`.`experience_required`, `levels`.`rank` AS `levelRank`,

                `homes`.`regen`,

                `bloodlines`.`regen_increase`, `bloodlines`.`affinity_1`, `bloodlines`.`affinity_2`,

                `clans`.`name` AS `clan_name`,

                `next_level`.`rank_id` AS `newRankid`, `next_level`.`rank` AS `next_user_level`,

                `squads`.`leader_uid`,

                `users_statistics`.`dr`, `users_statistics`.`sr`, `users_statistics`.`cur_health`,

                `users_sensei`.`username` AS `sensei`
                FROM `levels`
                    LEFT JOIN `users_preferences` ON (`users_preferences`.`uid` = "'.$GLOBALS['userdata'][0]['id'].'")
                    LEFT JOIN `homes` ON (`homes`.`id` = "'.$GLOBALS['userdata'][0]['apartment'].'")
                    LEFT JOIN `bloodlines` ON ("'.$GLOBALS['userdata'][0]['bloodline'].'" LIKE CONCAT(`bloodlines`.`name`,"%"))
                    LEFT JOIN `clans` ON (`clans`.`id` = "'.$GLOBALS['userdata'][0]['clan'].'")
                    LEFT JOIN `levels` AS `next_level` ON (`next_level`.`levelID` = "'.($GLOBALS['userdata'][0]['level_id'] + 1).'")
                    LEFT JOIN `squads` ON (`squads`.`id` = "'.$GLOBALS['userdata'][0]['anbu'].'")
                    LEFT JOIN `users` AS `users_sensei` ON (`users_sensei`.`id` = `users_preferences`.`sensei`)
                    LEFT JOIN `users_statistics` on (`users_statistics`.`uid` = "'.$GLOBALS['userdata'][0]['id'].'")
                WHERE `levels`.`levelID` = '. $GLOBALS['userdata'][0]['level_id'] .' LIMIT 1');
            } break;
            case("otherUser"): {
                $this->char_data = $GLOBALS['database']->fetch_data('SELECT `users`.`id`, `users`.`username`, `users`.`apartment`,
                `users`.`bloodline`, `users`.`bloodlineMask`, `users`.`status`, `users`.`location`, `users`.`gender`,

                `users_preferences`.`anbu`, `users_preferences`.`clan`,

                `users_loyalty`.`village`,

                `users_timer`.`last_activity`,

                `users_missions`.`battles_won`, `users_missions`.`battles_lost`, `users_missions`.`battles_fled`, `users_missions`.`battles_draws`,
                `users_missions`.`AIwon`, `users_missions`.`AIlost`, `users_missions`.`AIfled`, `users_missions`.`AIdraw`,
                `users_missions`.`torn_record`,

                `users_statistics`.`level`, `users_statistics`.`rank`, `users_statistics`.`user_rank`, `users_statistics`.`experience`,
                `users_statistics`.`cur_health`, `users_statistics`.`max_health`, `users_statistics`.`cur_cha`, `users_statistics`.`max_cha`,
                `users_statistics`.`cur_sta`, `users_statistics`.`max_sta`, `users_statistics`.`pvp_experience`, `users_statistics`.`level_id`,
                `users_statistics`.`rank_id`, `users_statistics`.`rep_ever`, `users_statistics`.`rep_now`, `users_statistics`.`pop_ever`,
                `users_statistics`.`pop_now`,`users_statistics`.`federal_level`,`users_statistics`.`strengthFactor`,
                `users_statistics`.`dr`, `users_statistics`.`sr`,


                `clans`.`name` AS `clan_name`,

                `squads`.`leader_uid`,

                `users_sensei`.`username` AS `sensei`,

                `users_student1`.`username` AS `student_1`,

                `users_student2`.`username` AS `student_2`,

                `users_student3`.`username` AS `student_3`
                FROM `users`
                    INNER JOIN `users_preferences` ON (`users_preferences`.`uid` = `users`.`id`)
                    INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id`)
                    INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
                    INNER JOIN `users_missions` ON (`users_missions`.`userid` = `users`.`id`)
                    INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)
                    LEFT JOIN `clans` ON (`clans`.`id` = `users_preferences`.`clan`)
                    LEFT JOIN `squads` ON (`squads`.`id` = `users_preferences`.`anbu`)
                    LEFT JOIN `users` AS `users_sensei` ON (`users_sensei`.`id` = `users_preferences`.`sensei`)
                    LEFT JOIN `users` AS `users_student1` ON (`users_student1`.`id` = `users`.`student_1`)
                    LEFT JOIN `users` AS `users_student2` ON (`users_student2`.`id` = `users`.`student_2`)
                    LEFT JOIN `users` AS `users_student3` ON (`users_student3`.`id` = `users`.`student_3`)
                WHERE `users`.`id` = '.$uid.' LIMIT 1');
            } break;
        }
    }

    protected function getTimers() {
        $timers = array();

        //login streak timer
        $timeToNext = ( 24*3600 - ($GLOBALS['user']->load_time - $this->char_data[0]['last_login_streak']) );
        $timers['login_streak_timer'] = array(
            'name' => 'Next Login Streak bonus',
            'time' => functions::convert_time( $timeToNext , 'loginStreakTimer', 'true'),
        );


        //hospital timer
        // Get hospital library
        require_once(Data::$absSvrPath.'/libs/hospitalSystem/healLib.inc.php');
        $lib = new hospitalFunctions();
        $lib->setHospitalDataWpar();

        if ($this->char_data[0]['hospital_timer']) {
            $time = ($this->char_data[0]['hospital_timer'] - $GLOBALS['user']->load_time < 0)
                ? 0
                : $this->char_data[0]['hospital_timer'] - $GLOBALS['user']->load_time;

            if ($time > 0) {
                $timers['hospital_timer'] = array(
                    'name' => 'You can leave the hospital without paying the cost after',
                    'time' => functions::convert_time($time, 'hospitalTimer', 'false'),
                );
            }
        }

        //health regeneration timer
        if( $this->char_data[0]['regen_data']['per_second'] > 0){

            if ($this->char_data[0]['status'] != 'hospitalized' &&
                $this->char_data[0]['max_health'] > $this->char_data[0]['cur_health']
            ) {
                $diff = $this->char_data[0]['max_health'] - $this->char_data[0]['cur_health'];
                $time = ceil($diff / $this->char_data[0]['regen_data']['per_second']) + 1;
                $timers['health_timer'] = array(
                    'name' => 'Time to full health',
                    'time' => functions::convert_time($time, 'healthTimer', 'false'),
                );
            }

            //chakra regeneration timer
            if ($this->char_data[0]['max_cha'] > $this->char_data[0]['cur_cha']) {
                $diff = $this->char_data[0]['max_cha'] - $this->char_data[0]['cur_cha'];
                $time = ceil($diff / $this->char_data[0]['regen_data']['per_second']) + 1;
                $timers['chakra_timer'] = array(
                    'name' => 'Time to full chakra',
                    'time' => functions::convert_time($time, 'chakraTimer', 'false'),
                );
            }

            //stamina regeneration timer
            if ($this->char_data[0]['max_sta'] > $this->char_data[0]['cur_sta']) {
                $diff = $this->char_data[0]['max_sta'] - $this->char_data[0]['cur_sta'];
                $time = ceil($diff / $this->char_data[0]['regen_data']['per_second']) + 1;
                $timers['stamina_timer'] = array(
                    'name' => 'Time to full stamina',
                    'time' => functions::convert_time($time, 'staminaTimer', 'false'),
                );
            }
        }

        //next mission
        // mission avaliable in x time
        if(($GLOBALS['userdata'][0]['rank_id'] > 1 && $GLOBALS['userdata'][0]['village'] != 'Syndicate') || $GLOBALS['userdata'][0]['rank_id'] > 2)
        {
            if($GLOBALS['userdata'][0]['mission_count'] < 4 && $GLOBALS['userdata'][0]['mission_collection_time'] + (60*30) > time())
            {
                $timers['stamina_timer'] = array(
                    'name' => 'Next '. (($GLOBALS['userdata'][0]['village'] != 'Syndicate') ? 'mission' : 'crime') . ' available in',
                    'time' => functions::convert_time($GLOBALS['userdata'][0]['mission_collection_time'] + (60*30) - time(), 'missionTimer', 'false')
                );
            }

            //mission avaliable next day.
            else if($GLOBALS['userdata'][0]['mission_count'] >= 4 && (floor(( (time() - 18000) /86400)) - floor(( ($GLOBALS['userdata'][0]['mission_collection_time'] - 18000) /86400)) < 1))
            {
                $timers['mission_timer'] = array(
                    'name' => (($GLOBALS['userdata'][0]['village'] != 'Syndicate') ? 'Mission' : 'Crime') . ' reset in',
                    'time' => functions::convert_time( 86400 - ((time() - 18000) % 86400), 'missionTimer', 'false')
                );
            }

            //mission avaliable
            else
            {
                $timers['mission_timer'] = array(
                    'name' => 'Next '. (($GLOBALS['userdata'][0]['village'] != 'Syndicate') ? 'mission' : 'crime'). ' available in',
                    'time' => 'Available  now'
                );
            }
        }

        // super mission timer
        //require_once(Data::$absSvrPath.'/libs/villageSystem/missionCrimeLib.php');
        //$missionPage = new missionCrimeLib();
        //$missionPage->setPageInformation();
//
        //if( $missionPage->countCompletions < $missionPage->allowedMissions ){
        //    $time = 1800 - ($GLOBALS['user']->load_time - $missionPage->user[0]['last_supermission']);
        //    if ($time > 0) {
        //        $timers['last_supermission'] = array(
        //            'name' => 'Next mission can be activated',
        //            'time' => functions::convert_time($time, 'missiontimer', false),
        //        );
        //    }
        //}


        //respect timer
        // Days in village
        $secondsInVillage = $GLOBALS['user']->load_time - $this->char_data[0]['time_in_vil'];
        $daysInVillage = floor(($secondsInVillage)/(24 * 3600));

        // Days till next respect point
        $timeToNext = "";
        $timeLeft = 0;
        $updateCyclus = 24 * 3600;
        if( $daysInVillage > 10 ){
            $timeLeft = $updateCyclus - ($GLOBALS['user']->load_time - $this->char_data[0]['vil_pts_timer']);
        }
        else{
            $timeLeft = 10*$updateCyclus-$secondsInVillage;
        }

        if( $timeLeft > 0 ){
            $timers['respect_point_timer'] = array(
                'name' => 'Time to next respect point',
                'time' => functions::convert_time($timeLeft, 'respectCount', false),
            );
        }

        //regen boost timer
        if( isset($this->char_data[0]['regen_endtime']) &&
            !empty($this->char_data[0]['regen_endtime']) &&
            $this->char_data[0]['regen_endtime'] > 0)
        {
            $timers['regen_boost'] = array(
                'name' => 'Regeneration Boosts ends',
                'time' => functions::convert_time($this->char_data[0]['regen_endtime'] - $GLOBALS['user']->load_time, 'regenBoostTimer', false),
            );

        }

        //repel effect timer
        if (!empty($this->char_data[0]['repel_endtime']) && ($this->char_data[0]['repel_endtime'] - $GLOBALS['user']->load_time) > 0) {
            $timers['perel_effect'] = array(
                'name' => 'Repel Effect ends',
                'time' => functions::convert_time($this->char_data[0]['repel_endtime'] - $GLOBALS['user']->load_time, 'repelEffectTimer', false),
            );
        }

        //federal support timer
        if (!empty($this->char_data[0]['federal_timer']) && ($this->char_data[0]['federal_timer'] - $GLOBALS['user']->load_time) > 0) {
            $timers['federal_support'] = array(
                'name' => 'Federal Support expires',
                'time' => functions::convert_time($this->char_data[0]['federal_timer'] - $GLOBALS['user']->load_time, 'federalSupportTimer', false),
            );
        }

        //craft/repair timer
        $active = $GLOBALS['database']->fetch_data('SELECT `users_inventory`.`finishProcessing` FROM `users_inventory`
            WHERE ((`users_inventory`.`trading` = '.$_SESSION['uid'].' AND `users_inventory`.`trade_type` = "repair")
            OR `users_inventory`.`uid` = '.$_SESSION['uid'].')  AND `users_inventory`.`finishProcessing` > 0
            ORDER BY `users_inventory`.`finishProcessing` DESC
            LIMIT 1');

        if( $active !== "0 rows" && $active[0]['finishProcessing'] - $GLOBALS['user']->load_time > 0 ){
            $timers['repair'] = array(
                'name' => 'Current crafting/repair action ends',
                'time' => functions::convert_time($active[0]['finishProcessing'] - $GLOBALS['user']->load_time, 'repairTimer', false),
            );
        }

        //special jitsu traning timer
        if (($this->char_data[0]['jutsu_timer'] - $GLOBALS['user']->load_time) > 0) {
            $timers['jitsu'] = array(
                'name' => 'Jutsu training concludes',
                'time' => functions::convert_time($this->char_data[0]['jutsu_timer'] - $GLOBALS['user']->load_time, 'jitsuTimer', false),
            );
        }

        //harvest timer
        $currentHarvest = cachefunctions::getHarvest($_SESSION['uid']);
        if( $currentHarvest ){

            $timeLeft = $currentHarvest['time'] - $GLOBALS['user']->load_time;
            $timers['harvest'] = array(
                'name' => 'Harvest is completed',
                'time' => functions::convert_time($timeLeft, 'harvestTimer', false),
            );
        }

        //regen cooldown timer
        if ($this->char_data[0]['regen_cooldown'] > $GLOBALS['user']->load_time) {
            $timers['regen_cooldown'] = array(
                'name' => 'The end of any penalties applied to a user’s regeneration rate',
                'time' => functions::convert_time($this->char_data[0]['regen_cooldown'] - $GLOBALS['user']->load_time, 'regenCooldownTimer', false),
            );
        }

        //jail timer
        if ($this->char_data[0]['jail_timer'] > $GLOBALS['user']->load_time) {
            $timers['jail'] = array(
                'name' => 'User is freed from Jail',
                'time' => functions::convert_time($this->char_data[0]['jail_timer'] - $GLOBALS['user']->load_time, 'regenCooldownTimer', false),
            );
        }

        //torn battle arena timer
        if (($this->char_data[0]['last_battle'] + 300) > $GLOBALS['user']->load_time) {
            $timers['torn'] = array(
                'name' => 'You can engage in a Torn Battle Ring challenge',
                'time' => functions::convert_time($this->char_data[0]['last_battle'] + 300 - $GLOBALS['user']->load_time, 'tornBattleArenaTimer', false),
            );
        }

        //voting systems
        $votingTimes = $GLOBALS['database']->fetch_data('SELECT `AWG`, `TWG`, `DOG`, `GALAXY`, `OGLAB` FROM `votes`
            WHERE `votes`.`userid` = '.$_SESSION['uid'].'
            LIMIT 1');

        if ($votingTimes != '0 rows') {
            foreach ($votingTimes[0] as $key => $time) {
                if (($time + 86400) > $GLOBALS['user']->load_time) {
                    $timers['voting'] = array(
                        'name' => 'Link to the ' . $key . ' is available again',
                        'time' => functions::convert_time($time + 86400 - $GLOBALS['user']->load_time, 'voting' . $key, false),
                    );
                }
            }
        }

        return $timers;
    }
    
    protected function set_regen() {
        
        // This would only break if a village wasn't set within the appropriate tables and system
        if ($this->char_data[0]['avg_pvp'] === null) {
            $GLOBALS['error']->handle_error('404', 'There was an error loading your character\'s village! It couldn\'t be found!', '1');
            return;
        }
            
        $actualregen = (functions::actual_calc_regen($this->char_data));

        /*
        // Rank modifier. Keep it in this format since it makes updates easy!
        switch ($this->char_data[0]['rank_id']) {
            case 2: $rankmodifier = 15; break; // Genin 
            case 3: $rankmodifier = 100; break; // Chuunin 
            case 4: $rankmodifier = 100; break; // Jounin
            case 5: $rankmodifier = 100; break; // Elite Jounin
            default: $rankmodifier = 0; break; // Lol who changed the rank?
        }

        $villageregen = ($this->char_data[0]['regen_level'] * ($rankmodifier / 100));

        // Bonus village regeneration from loyalty
        if( $GLOBALS['userdata'][0]['activateBonuses'] == "yes" ){
            if($this->char_data[0]['vil_loyal_pts'] >= 200 || $this->char_data[0]['vil_loyal_pts'] <= -200) {
                $villageregen *= 1.1;
            }
        }

        // War regen boost, lasts 7 days after winning war
        $warBoost = ($this->char_data[0]['warRegenBoostTime'] > ($GLOBALS['user']->load_time - 7 * 24 * 3600)) ? 1.1 : 1;

        

        // Set regeneration values (actual and shown on page)
        $regen = $this->char_data[0]['regen_rate'] + $villageregen;

        // Boosts bought with reputation points & item boosts
        $regen += $this->char_data[0]['regen_boost'];
        $regen += $this->char_data[0]['item_regen_boost'];

        // Add war boost
        $regen *= $warBoost;

        // Cooldown modification
        if ($this->char_data[0]['regen_cooldown'] > $GLOBALS['user']->load_time) {
            if((int)$this->char_data[0]['regen_cooldown'] !== 0) {
                $factor = $this->char_data[0]['regen_cooldown'] - $GLOBALS['user']->load_time;
                switch (true) {
                    case($factor < (24*3600)): $modifier = 75; break;
                    case($factor < (3*24*3600)): $modifier = 50; break;
                    case($factor < (4*24*3600)): $modifier = 25; break;
                    default: $modifier = 100; break;
                }
                $regen = ($regen / 100) * $modifier;
            }
        } 
        else
        */
        
        // PVP regen
        // Prevents Division By Zero. Also, if the Village AVG_PVP is 0, then no one should benefit from that.
        $pvp_regen_mod = ((int)$this->char_data[0]['avg_pvp'] === 0) ? 0 
            : (3 * round($this->char_data[0]['pvp_experience'] / $this->char_data[0]['avg_pvp'], 1));

        // Constraint Check for Max PvP Regen
        $pvp_regen_mod = ($pvp_regen_mod > 50) ? 50 : $pvp_regen_mod;

        // Show PVP Regen Bonus Value
        if ($pvp_regen_mod > 0) { $this->char_data[0]['regen_data']['PVP'] = $pvp_regen_mod; }
            
        // Fix regen cooldown if time
        if ($this->char_data[0]['regen_cooldown'] <= $GLOBALS['user']->load_time) {
            if((int)$this->char_data[0]['regen_cooldown'] !== 0) {
                $GLOBALS['database']->execute_query("UPDATE `users_timer` 
                    SET `users_timer`.`regen_cooldown` = 0 
                    WHERE `users_timer`.`userid` = ".$_SESSION['uid']." LIMIT 1");
            }
        }

        // Regen timer
        $timeSinceRegen = $GLOBALS['user']->load_time - $this->char_data[0]['last_regen'];
        $timeSinceRegen = $timeSinceRegen < 0 ? 0 : $timeSinceRegen;
        $timer = $GLOBALS['user']->regenFrequency - ($timeSinceRegen);
         
        // Add the regeneration to the cache
        cachefunctions::setUserRegeneration($_SESSION['uid'], $actualregen);

        // Modify Actual Regen
        if ($this->char_data[0]['status'] === 'combat') {
            $actualregen = 0;
            $this->char_data[0]['regen_data']['battleRegen'] = 100; 
        }
        
        // Hospital fix
        if ($this->char_data[0]['status'] === 'hospitalized') {
            $actualregen = 0;
        }
        
        // Get regen rate in rate / regen frequency
        $regenPerSec = round($actualregen * $GLOBALS['user']->regenFrequency / 60,2);
        
        // Add to character array        
        $this->char_data[0]['regen_data']['Show'] = $actualregen;
        $this->char_data[0]['regen_data']['per_second'] = $regenPerSec;
        $this->char_data[0]['regen_data']['Timer'] = functions::convert_time($timer, 'regentimer', 'updateProfile', $regenPerSec, "dontShow");
        
        // Countdown for next login streak
        $timeToNext = ( 24*3600 - ($GLOBALS['user']->load_time - $this->char_data[0]['last_login_streak']) );
        $this->char_data[0]['loginStreakTimer'] = functions::convert_time( $timeToNext , 'loginStreakCounter', 'true');
    }
    
    // Set the status shown for ANBU & Clan
    protected function setAnbuClanStatus() {
        // Set ANBU Status
        if (!in_array($this->char_data[0]['anbu'], array('_none', null, '_disabled', ''), true)) {
            // Check if Leader UID is the Users
            $this->char_data[0]['anbu'] = "Squad ".(($this->char_data[0]['leader_uid'] !== $this->char_data[0]['id']) ? 'Member' : 'Leader');
        }
        else { $this->char_data[0]['anbu'] = "Not an ANBU"; }

        // Set Clan Status
        $this->char_data[0]['clan'] = (in_array($this->char_data[0]['clan'], array('_none', null, '_disabled', ''), true)) ?
            'None' : $this->char_data[0]['clan_name'];
    }

    // Set marriage status
    protected function setMarriage(){
        $marriage = $GLOBALS['database']->fetch_data('SELECT `users`.`username`
            FROM `marriages`
                INNER JOIN `users` ON (`users`.`id` = IF(`marriages`.`uid` = '.$this->char_data[0]['id'].', `marriages`.`oid`, `marriages`.`uid`))
            WHERE (`marriages`.`uid` = '.$this->char_data[0]['id'].'
                OR `marriages`.`oid` = '.$this->char_data[0]['id'].') AND `marriages`.`married` = "Yes" LIMIT 1');

        if(isset($marriage[0]['username'])) {
            $spouse = "</text><a href='?id=13&amp;page=profile&amp;name=".$marriage[0]['username']."'>".$marriage[0]['username']."</a><text>";
        }
        $this->char_data[0]['marriedTo'] = (isset($spouse)) ? "Married to: ".$spouse."<br>" : "";
    }

    // Set special elemental mastery
    protected function setElementMasteries(){
        $elements = new Elements();
        $masteries = $elements->getUserElementMastery();
        $this->char_data[0]['element_mastery_1'] = $masteries[0];
        $this->char_data[0]['element_mastery_2'] = $masteries[1];
        $this->char_data[0]['element_mastery_special'] = $masteries[2];
    }

    // Set federal support
    protected function setFedSupport() {
        $this->char_data[0]['federal'] = ($this->char_data[0]['user_rank'] === 'Paid') ? 'yes' : 'no';
    }

    // Set winning statistics
    protected function setWinStatistics() {
        if(($this->char_data[0]['battles_won'] + $this->char_data[0]['battles_lost'] + $this->char_data[0]['battles_fled'] + $this->char_data[0]['battles_draws']) > 0) {
            $this->char_data[0]['percentage'] = floor(($this->char_data[0]['battles_won'] * 100) /
                ($this->char_data[0]['battles_won'] + $this->char_data[0]['battles_lost'] + $this->char_data[0]['battles_fled'] + $this->char_data[0]['battles_draws']));
        }
        else { $this->char_data[0]['percentage'] = 0; }

        switch(true) {
            case($this->char_data[0]['percentage'] >= 75): $this->char_data[0]['color'] = 'green'; break;
            case($this->char_data[0]['percentage'] >= 50): $this->char_data[0]['color'] = 'orange'; break;
            default: $this->char_data[0]['color'] = 'red'; break;
        }
    }

    // Set students
    protected function setStudents() {
        if ($this->char_data[0]['student_1'] !== '_none') {
            $this->char_data[0]['student_1'] = '<a href="?id=13&amp;page=profile&amp;name='.$this->char_data[0]['student_1'].
                '">'.$this->char_data[0]['student_1'].'</a>';
        }
        else { $this->char_data[0]['student_1'] = 'None'; }

        if ($this->char_data[0]['student_2'] !== '_none') {
            $this->char_data[0]['student_2'] = '<a href="?id=13&amp;page=profile&amp;name='.$this->char_data[0]['student_2'].
                '">'.$this->char_data[0]['student_2'].'</a>';
        }
        else { $this->char_data[0]['student_2'] = 'None'; }

        if ($this->char_data[0]['student_3'] !== '_none') {
            $this->char_data[0]['student_3'] = '<a href="?id=13&amp;page=profile&amp;name='.$this->char_data[0]['student_3'].
                '">'.$this->char_data[0]['student_3'].'</a>';
        }
        else { $this->char_data[0]['student_3'] = 'None'; }
    }

    // Set sensei
    protected function setSensei() {
        if ( empty($this->char_data[0]['sensei']) || $this->char_data[0]['sensei'] === '_disabled' || $this->char_data[0]['sensei'] === '_none') {
            $this->char_data[0]['sensei'] = 'None';
        } else {
            $this->char_data[0]['sensei'] = '<a href="?id=13&amp;page=profile&amp;name=' . $this->char_data[0]['sensei'] . '">' . $this->char_data[0]['sensei'] . '</a>';
        }
    }

    // Set login status
    protected function setLoginStatus() {
        if ($this->char_data[0]['last_activity'] > ($GLOBALS['user']->load_time - 300)) {
            $GLOBALS['template']->assign('onStatus', 1);
        } else {
            $elapsed = $GLOBALS['user']->load_time - $this->char_data[0]['last_activity'];
            $GLOBALS['template']->assign('onStatus', $elapsed);
            if ((int)$this->char_data[0]['last_activity'] !== 0) {
                $GLOBALS['template']->assign('lastOnline', functions::convert_PM_time($elapsed, 'LastOnline'));
            }
        }
    }
}
