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

/*Author: Tyler Smith
 *Class: BattleStarter
 *  this class is used by the system to start a battle.
 */

require_once(Data::$absSvrPath.'/libs/Battle/Battle.php');

class BattleStarter
{
    //battle types
    const generic = '00';
    const travel = '01';//
    const event = '02';//
    const spar = '03';
    const pvp = '04';
    const small_crimes = '05';//
    const mission = '06';//
    const kage = '07';
    const clan = '08';
    const arena = '09';//
    const mirror = '10';//
    const torn = '11';//
    const territory = '12';
    const quest = '13';//

    //array of all battle type ids
    const battle_types = array( '00', '01',
                                '02', '03',
                                '04', '05',
                                '06', '07',
                                '08', '09',
                                '10', '11',
                                '12', '13' );

    //startBattle
    //this function starts battles
    //it takes an array of users and an array of ai
    //  structure of arrays: 
    //      users needs id=>uid, team_or_extra_data=>team or (team=> &| attacker=>bool &| defender=>bool &| respondent=>bool &| opponents_allegiance=>bool &| no_cfh=>bool)
    //      ai needs id=>aid, team=>team_name
    //
    //takes a battle_type id
    //
    //optional arguments...
    //takes a bool value for turning debugging on and off
    //takes an extra field needed for pasing situation specific information to the battle system.(it is requied by some battle types for extra information)
    //takes a bool value to indicate if getting locks should be skipped or not
    //takes a number to set the battle_id to a given value
    static public function startBattle( $users, $ai, $battle_type = false, $debugging = false, $extra = false, $no_locks = false, $battle_id = false)
    {
        try
        {
            //geting locks on users lock and checking for combat status
            if(is_array($users))
            {
                //getting a lock on each user
                foreach($users as $user)
                    if( ($no_locks === false || $user['id'] != $_SESSION['uid']) && $no_locks != 'hard')
                    {
                        $GLOBALS['database']->get_lock('battle',$user['id'],__METHOD__);
                    }

                //getting a uid for each user
                $user_ids = array();
                foreach($users as $user)
                {
                    $user_ids[] = $user['id'];
                }

                //getting the status of each user in the database to make sure they are all ready for combat
                $results = $GLOBALS['database']->fetch_data('SELECT `status`, `username` FROM `users` WHERE `id` IN ('.implode(',',$user_ids).')');

                //checking the status of each user in the database to make sure they are all ready for combat
                foreach($results as $result)
                    if($result['status'] != 'awake' && $battle_type != self::territory && !($result['status'] == 'asleep' && $battle_type == self::spar))
                        throw new Exception('Start of battle failed. One of the users is not awake. '.implode(', ', array_column($results, 'username')) .implode(', ', array_column($results, 'status')));
            }

            $data = true;
            $battle_type_code = '00';

            //checking battle_type
            if( !in_array( $battle_type ,self::battle_types) || $battle_type === false )
            {
                $battle_type_code = '00';
            }
            else
            {
                $battle_type_code = $battle_type;
            }

            //getting battle_id and making sure its unique
            if($battle_id == false)
            {
                //while data is not false(aka we have not found a empty battle id yet.)
                while($data !== false)
                {
                    //special handling for kage battle type.
                    if($battle_type == self::kage)
                    {
                        $villages = array('Konoki'=>1,'Silence'=>2,'Samui'=>3,'Shroud'=>4,'Shine'=>5,'Syndicate'=>6);
                        $battle_id = ($villages[$GLOBALS['userdata'][0]['village']]).$battle_type_code;
                    }
                    else
                        $battle_id = ((string)random_int(1,9999999)).$battle_type_code;

                    //trying to get any data that might exist at the chosen battle id
                    if(isset($GLOBALS['cache']))
                    {
                        try{ $data = $GLOBALS['cache']->get(Data::$target_site.$battle_id.Battle::TAGS); } //if it fails try again...
                        catch (Exception $e)
                        {
                            try { $data = $GLOBALS['cache']->get(Data::$target_site.$battle_id.Battle::TAGS); }//if it fails try again...
                            catch (Exception $e)
                            {
                                try { $data = $GLOBALS['cache']->get(Data::$target_site.$battle_id.Battle::TAGS); }//if it fails again throw exception.
                                catch (Exception $e)
                                {
                                    //throw new Exception('there was an issue with reading the cache.');
                                    $data = false;
                                }
                            }
                        }
                    }
                    else
                    {
                        $data = false;
                    }

                    if($data === false)
                    {
                        $query = "SELECT * FROM `battle_fallback` WHERE `id` = ".$battle_id;
                        try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception('cant pull battle data from database'); }
                        catch (Exception $e)
                        {
                            try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception ('cant pull battle data from database'); }
                            catch (Exception $e)
                            {
                                try { if(! $data = $GLOBALS['database']->fetch_data($query)) throw new Exception ('cant pull battle data from database'); }
                                catch (Exception $e)
                                {
                                    $GLOBALS['DebugTool']->push('','cant pull battle data from database', __METHOD__, __FILE__, __LINE__);
                                    throw $e;
                                }
                            }
                        }

                        if(!is_array($data) || $data == "0 rows")
                            $data = false;
                    }

                    //if there is already a kage battle prevent it.
                    if($data !== false && $battle_type == self::kage)
                    {
                        throw new Exception('There already is an ongoing kage battle in this village.');
                    }
                }
            }

            //init battle and variables inside battle
            $battle = new Battle($battle_id, 15, $debugging, true);
            $battle->users = array();
            $battle->teams = array();
            $battle->jutsus = array();
            $battle->location_tags = array();
            $battle->battle_log = array();
            $battle->removed_users = array();
            $battle->rng = 24789;//random_int(-11478,48129);
            $battle->turn_counter = 0;
            $battle->turn_timer = time() + 62;
            $battle->user_index = array();
            $battle->extra = $extra;
            $battle->census = array();

            $ids_for_query = '';

            //add each user
            if(is_array($users))
                foreach( $users as $i => $data)
                {
                    $battle->addUser($data['id'], $data['team_or_extra_data']);

                    if($ids_for_query != '')
                        $ids_for_query .= ',';

                    $ids_for_query .= "'".$data['id']."'";
                }

            if($battle_type != self::territory && $battle_type != self::spar )
            {
                $query = "SELECT `status`, `battle_id` FROM `users` WHERE `id` in (".$ids_for_query.") AND (`status` != 'awake' OR `battle_id` != 0 )";
                try { if(! $result = $GLOBALS['database']->fetch_data($query)) throw new Exception('a user is not ready for combat.'); }
                catch (Exception $e)
                {
                    try { if(! $result = $GLOBALS['database']->fetch_data($query)) throw new Exception ('a user is not ready for combat.'); }
                    catch (Exception $e)
                    {
                        try { if(! $result = $GLOBALS['database']->fetch_data($query)) throw new Exception ('a user is not ready for combat.'); }
                        catch (Exception $e)
                        {
                            $GLOBALS['DebugTool']->push('','a user is not ready for combat.', __METHOD__, __FILE__, __LINE__);
                            throw $e;
                        }
                    }
                }

                if(is_array($result))
                    throw new Exception('a user is not ready for combat.');
            }



            //update user status and battle id
            $GLOBALS['Events']->acceptEvent('status', array('new' => 'combat', 'old' => $GLOBALS['userdata'][0]['status']));
            $query = "UPDATE `users` SET `status` = 'combat', `database_fallback` = 0, `battle_id` = '".$battle_id."' WHERE `id` in (".$ids_for_query.")";
            try { $GLOBALS['database']->execute_query($query); }
            catch (Exception $e)
            {
                try { $GLOBALS['database']->execute_query($query); }
                catch (Exception $e)
                {
                    try { $GLOBALS['database']->execute_query($query); }
                    catch (Exception $e)
                    {
                        throw $e;
                    }
                }
            }
            $GLOBALS['userdata'][0]['status'] = 'combat';
            $GLOBALS['userdata'][0]['database_fallback'] = 0;

            //add each ai
            if(is_array($ai))
                foreach( $ai as $i => $data)

                    if(isset($data['count']))
                        for($i = 0; $i < $data['count']; $i++)
                            $battle->addAI($data['id'],$data['team']);

                    else
                        $battle->addAI($data['id'],$data['team']);

            //creating battle history record
            $battle->openBattleHistory();

            //saving work done to cache
            $battle->updateCache();

            //release locks
            if(is_array($users))
                foreach($users as $user)
                    if($no_locks === false)
                        $GLOBALS['database']->release_lock('battle',$user['id']);

            //returning the instance of the battle
            return $battle;

        }
        catch (Exception $e) //if there was an issue release locks and throw exception.
        {
            if(is_array($users))
                foreach($users as $user)
                    if($no_locks === false)
                        $GLOBALS['database']->release_lock('battle',$user['id']);

            throw $e;
        }
    }
}