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
 *Class: Battle
 *  this class defines the functions of a battle and controls what hapens in a battle.
 *  this call is extended by other classes to change how a battle functions for different
 *  types of battles.
 */

require_once(Data::$absSvrPath.'/global_libs/Tags/Tags.php');
require_once(Data::$absSvrPath.'/global_libs/Tags/Tag.php');
require_once(Data::$absSvrPath.'/tools/DebugTool.php');
require_once(Data::$absSvrPath.'/libs/elements/Elements.php');
require_once(Data::$absSvrPath.'/libs/hospitalSystem/healLib.inc.php');
require_once(Data::$absSvrPath.'/libs/Battle/BattleStarter.php');
require_once(Data::$absSvrPath.'/libs/professionSystem/OccupationData.php');
require_once(Data::$absSvrPath.'/libs/jutsuSystem/jutsuFunctions.php');
require_once(Data::$absSvrPath.'/libs/notificationSystem/notificationSystem.php');

class Battle extends Tags
{
    //this defines how much durability damage is taken at each rank.
    //this can be overriden
    public $DURABILITYDAMAGESCALE = array(1,200000,200000,128000,160000,200000);

    //does nothing other than call Tags constructor at the moment
    function __construct($uid,$cache_time,$debugging,$starting = false)
    {
        //marking this a debugging.
        $this->debugging = $debugging;

        //calling parent constructor which is the Tags class
        parent::__construct($uid,$cache_time,$debugging,$starting);

        //setting global information about villages
        $this->village_locations = array(   "Konoki"           => 'Konoki',
                                            "Silence"          => 'Silence',
                                            "Shroud"           => 'Shroud',
                                            "Shine"            => 'Shine',
                                            "Samui"            => 'Samui',
                                            "Gambler's Den"    => 'Syndicate',
                                            "Bandit's Outpost" => 'Syndicate',
                                            "Poacher's Camp"   => 'Syndicate',
                                            "Pirate's Hideout" => 'Syndicate');

        //the random value for each user used by get turn order.
        $this->turn_order_rng = array();

        //init for priority log for turn order display
        $this->priority = array();

        $this->query_buffer = array();

        //adding rng to turn order
        //foreach($this->users as $username => $userdata)
        //{
        //    $this->turn_order_rng[$username] = random_int(-51,51);
        //}
    }


    //addAI
    //gets all data required by the ai and adds it to the cache.
    function addAI($usernames, $team = false, $username_over_id = false)
    {
        //making sure that usernames is an array
        if (!is_array($usernames))
            $usernames = array($usernames);

        $temp_names = '';

        //getting user names into an sql friendly format.
        foreach($usernames as $i => $username)
        {
            if($i != 0)
                $temp_names .= ',';

            $temp_names .= "'" . $username . "'";
        }

        //building ai statement to add ai
        $select_statement = "SELECT * FROM `ai` WHERE ";

        if($username_over_id === true)
            $select_statement .= "`name` IN (".$temp_names.")";
        else
            $select_statement .= "`id` IN (".$temp_names.")";

        if(!isset($this->query_buffer[$select_statement]))
        {
            //calling db to get ai and recurseive call to avoid issues.
        //aka it it hickups and fails it will try again up to twice.
            try { if(! $query = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception('query failed'); }
            catch (Exception $e)
            {
                try { if(! $query = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed'); }
                catch (Exception $e)
                {
                    try { if(! $query = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed to update user information'); }
                    catch (Exception $e)
                    {
                        $GLOBALS['DebugTool']->push($usernames,'there was an error collecting the ais information.', __METHOD__, __FILE__, __LINE__);
                        throw $e;
                    }
                }
            }
        }
        else
        {
            $query = $this->query_buffer[$select_statement];
        }


        $ais = array();

        //checking to make sure the query went well
        if(is_array($query) && isset($query[0]['id']))
        {
            $this->query_buffer[$select_statement] = $query;

            foreach( $query as $user )
            {
                $username = $user['name'];

                //flag to mark if the ai was found currently in the battle.
                //this is for adding the ai then ai 2, ai 3, ai 4.
                //if it does not do this it will just overwrite the first ai and
                //you could only have 1 of any given ai.
                $found = false;

                $max = 1;
                foreach($this->users as $temp_username => $userdata)
                {
                    if( substr( $temp_username, 0, strlen($username) ) === $username)
                    {
                        $getting_number = explode('#', $temp_username);
                        if(count($getting_number) == 2 && $getting_number[1] > $max)
                            $max = $getting_number[1];
                    }
                }

                //looking for the last ai with the same name to know what to name this ai
                for($i = $max; !$found ; $i++)
                {

                    if( ($i != 1 && !isset($this->users[$username.' #'.$i])) || ( $i == 1 && !isset($this->users[$username])))
                    {
                        $found = true;
                        if($i != 1)
                            $username = $username.' #'.$i;
                        else
                            $username = $username;
                    }
                }

                $ais[] = $username;

                //if team is set to false get village name for team
                if($team === false)
                    $team = $user['village'];

                //calling add user to add that name and team to the battle
                parent::addUser($username,$team);
                $this->census[] = $username.'/'.$team.'/ai:'.$user['id'];

                //marking the user as an ai
                $this->users[$username]['ai'] = true;
                $this->users[$username]['events'] = array();

                //setting alot of this ai's misc. information
                $this->users[$username][parent::HEALTH] = $user['life'];
                $this->users[$username][parent::HEALTHMAX] = $user['life'];
                $this->users[$username]['display_name'] = $user['name'];
                $this->users[$username][parent::RANK] = $user['rank_id'];
                $this->users[$username]['display_rank'] = $user['rank'];
                $this->users[$username]['aid'] = $user['id'];
                $this->users[$username]['show_count'] = $user['show_count'];
                $this->users[$username]['database_fallback'] = 0;

                $user['gender'] = strtolower($user['gender']);

                //setting ai gender.
                if($user['gender'] != 'random')
                    $this->users[$username]['gender'] = $user['gender'];
                else
                {
                    if(random_int(0,1))
                        $this->users[$username]['gender'] = 'male';
                    else
                        $this->users[$username]['gender'] = 'female';
                }

                //settin offense
                $this->users[$username][parent::OFFENSE.parent::TAIJUTSU] = $user['tai_off'];
                $this->users[$username][parent::OFFENSE.parent::NINJUTSU] = $user['nin_off'];
                $this->users[$username][parent::OFFENSE.parent::GENJUTSU] = $user['gen_off'];
                $this->users[$username][parent::OFFENSE.parent::BUKIJUTSU] = $user['weap_off'];

                //settin defense
                $this->users[$username][parent::DEFENSE.parent::TAIJUTSU] = $user['tai_def'];
                $this->users[$username][parent::DEFENSE.parent::NINJUTSU] = $user['nin_def'];
                $this->users[$username][parent::DEFENSE.parent::GENJUTSU] = $user['gen_def'];
                $this->users[$username][parent::DEFENSE.parent::BUKIJUTSU] = $user['weap_def'];

                //settin elements
                $this->users[$username][parent::ELEMENTS] = explode(',', $user['elements']);
                $this->users[$username][parent::ELEMENTMASTERIES] = explode(',', $user['elementmasteries']);

                //setting status effect area
                $this->users[$username]['status_effects'] = array();

                //setting money
                $this->users[$username]['money'] = 0;

                //settin generals
                $this->users[$username][parent::STRENGTH]       = $user['strength'];
                $this->users[$username][parent::WILLPOWER]      = $user['willpower'];
                $this->users[$username][parent::INTELLIGENCE]   = $user['intelligence'];
                $this->users[$username][parent::SPEED]          = $user['speed'];

                if($user['specialization'] != '' && $user['specialization'] != ':0')
                    $this->users[$username][parent::SPECIALIZATION] = $user['specialization'];
                else
                {
                    //setting spec
                    $keys = array('offenseT','offenseN','offenseG','offenseB');
                    $highest = -1;
                    $highestKeys = array();
                    foreach($keys as $key)
                    {
                        if($this->users[$username][$key] > $highest)
                        {
                            $highest = $this->users[$username][$key];
                            $highestKeys = array($key);
                        }
                        else if ($this->users[$username][$key] == $highest)
                        {
                            $highestKeys[] = $key;
                        }
                    }
                    $this->users[$username][parent::SPECIALIZATION] = substr($highestKeys[random_int(0, count($highestKeys) - 1)], -1);                    
                }

                //settin other stats
                $this->users[$username][parent::ARMORBASE]      = $user['armor'];
                $this->users[$username][parent::MASTERY]        = $user['mastery'];
                $this->users[$username][parent::STABILITY]      = $user['stability'];
                $this->users[$username][parent::ACCURACY]       = $user['accuracy'];
                $this->users[$username][parent::EXPERTISE]      = $user['expertise'];
                $this->users[$username][parent::CHAKRAPOWER]    = $user['chakraPower'];
                $this->users[$username][parent::CRITICALSTRIKE] = $user['criticalStrike'];

                $this->users[$username]['ignoreCooldown'] = $user['ignoreCooldown'];

                $this->users[$username]['rarity'] = false;
                $this->users[$username]['jutsu_level_weight'] = 100;

                //settin geting action information.
                $this->users[$username]['ai_actions'] = array();
                $this->users[$username]['ai_actions']['jutsu'] = array();
                $this->users[$username]['ai_actions']['weapon'] = array();
                $this->users[$username]['ai_actions']['item'] = array();

                //parsing actions the ai can take and getting their information from the database.
                {
                    //if there are actions
                    if( $user['ai_actions']  != '')
                    {
                        //for each action
                        foreach( explode('|', $user['ai_actions']) as $action)
                        {
                            //if there is an action here
                            if($action != '')
                            {
                                //split its information into an array
                                $action_data = explode(',',$action);

                                //getting its data
                                $type = trim($action_data[0]);
                                $id = trim($action_data[1]);
                                $level = trim($action_data[2]);
                                $targeting = trim($action_data[3]);

                                //pulling all weapons listed into an array
                                $weapons = array();
                                for($i = 4; $i < count($action_data); $i++)
                                {
                                    $weapons[] = trim($action_data[$i]);
                                }

                                //recording the action in the users ai acions information area
                                $this->users[$username]['ai_actions'][$type][$id] = array('type' => $type, 'id' => $id, 'level' => $level, 'targeting' => $targeting, 'weapons' => $weapons);
                            }
                        }
                    }

                    //pulling all data not listed in the ai actions field for these actions.
                    foreach( $this->users[$username]['ai_actions'] as $type => $action_group )
                    {
                        //temp holders for the ids for the differentthings that need to be pulled from the database.
                        $jids = array();
                        $weapon_ids = array();
                        $item_ids = array();

                        //finding everything that needs to be grabed from the database
                        foreach($action_group as $action)
                        {
                            if($type == 'jutsu')
                            {
                                $jids[] = $action['id'];
                            }

                            else if($type == 'weapon')
                            {
                                $weapon_ids[] = $action['id'];
                            }

                            else if($type == 'item')
                            {
                                $item_ids[] = $action['id'];
                            }
                        }

                        //grabbing everything from the database and recording it.
                        //if this is a jutsu pull jutsu data
                        if($type == 'jutsu' && count($jids) != 0)
                        {
                            $temp = '';
                            //building ids in an sql friendly way
                            foreach($jids as $i => $jid)
                            {
                                if($i != 0)
                                    $temp .= ',';
                                $temp .= '"'.$jid.'"';
                            }
                            $jids = $temp;

                            //setting the query statement.
                            $query = 'SELECT  `id` as `jid`,           `name`,
                                            `description`,         `battle_description`,
                                            `element`,             `hea_cost`,
                                            `targeting_type`,      `jutsu_type`,
                                            `splitJutsu`,          `loyaltyRespectReq`,
                                            `tags`,                `cooldown_pool_set`,
		                                    `cooldown_pool_check`, `weapons`,
                                            `override_cooldown`,   `max_uses`,
                                            `reagents`, `priority`
                                             from `jutsu` WHERE `id` in ('.$jids.')';

                            if(!isset($this->query_buffer[$query]))
                            {
                                //getting jutsu data from database, nested for stability.
                                //if the query fails it will try again.
                                try { if(! $jutsus = $GLOBALS['database']->fetch_data($query)) throw new Exception('query failed'); }
                                catch (Exception $e)
                                {
                                    try { if(! $jutsus = $GLOBALS['database']->fetch_data($query)) throw new Exception ('query failed'); }
                                    catch (Exception $e)
                                    {
                                        try { if(! $jutsus = $GLOBALS['database']->fetch_data($query)) throw new Exception ('query failed to update user information'); }
                                        catch (Exception $e)
                                        {
                                            $GLOBALS['DebugTool']->push('','there was an error getting jutsu information.', __METHOD__, __FILE__, __LINE__);
                                            throw $e;
                                        }
                                    }
                                }
                            }
                            else
                            {
                                $jutsus = $this->query_buffer[$query];
                            }

                            //making sure that response from db was good.
                            if(is_array($jutsus) && count($jutsus) != 0)
                            {
                                //saving this query result
                                $this->query_buffer[$query] = $jutsus;

                                //foeach jutsu found record the data.
                                foreach($jutsus as $jutsu_key => $jutsu)
                                {
                                    //process split jutsu data
                                    if($jutsu['splitJutsu'] == 'yes')
                                    {
                                        //for each field in the jutsu
                                        foreach($jutsu as $field_key => $field)
                                        {
                                            //check for split jutsu data
                                            $bracket_count = substr_count($field,']');
                                            $bracket_count_2 = substr_count($field,'[');
                                            if($bracket_count > 0 || $bracket_count_2 > 0)
                                            {
                                                if($bracket_count == 4 && $bracket_count_2 == 4)
                                                {
                                                    //breaking ]
                                                    $first_break = explode(']',$field);
                                                    $last_break = array();

                                                    //breaking [
                                                    foreach($first_break as $break)
                                                    {
                                                        if($break != '')
                                                        {
                                                            $temp = explode('[',$break);
                                                            $last_break[$temp[0]] = $temp[1];
                                                        }
                                                    }

                                                    //setting data.
                                                    $jutsu[$field_key] = $last_break;
                                                }
                                                else
                                                    $GLOBALS['DebugTool']->push($field_key.' : '.$field,'bad split string data.', __METHOD__, __FILE__, __LINE__);
                                            }

                                        }
                                    }

                                    //record the jutsu's information in the pool of all users jutsu information
                                    $jutsu['cooldown_pool_set'] = explode(',',$jutsu['cooldown_pool_set']);
                                    $jutsu['cooldown_pool_check'] = explode(',',$jutsu['cooldown_pool_check']);
                                    if(!isset($this->jutsus[$jutsu['jid']]))
                                    {
                                        $this->jutsus[$jutsu['jid']] = $jutsu;
                                        unset($this->jutsus[$jutsu['jid']]['level']);
                                        unset($this->jutsus[$jutsu['jid']]['exp']);
                                        unset($this->jutsus[$jutsu['jid']]['max_uses']);
                                        unset($this->jutsus[$jutsu['jid']]['hea_cost']);
                                    }

                                    //make sure user jutsu location is set
                                    if(!isset($this->users[$username]['jutsus']))
                                        $this->users[$username]['jutsus'] = array();

                                    //set personal jutsu level for this jutsu to user information.
                                    $this->users[$username]['jutsus'][$jutsu['jid']] = array('ai_actions'=> $this->users[$username]['ai_actions'][$type][$jutsu['jid']],
                                                                                             'exp'=>0,
                                                                                             'max_uses'=>$jutsu['max_uses'], 'uses' => 0,
                                                                                             'hea_cost' =>$jutsu['hea_cost'], 'reagents' => $jutsu['reagents'],
                                                                                             'allow_elemental_weapons' => false);

                                    //check for elemental affinity and apply bonus's from elemental mastery.
                                    $found_mastery = -1;
                                    if($jutsu['element'] != '' && $jutsu['element'] != 'none' && $jutsu['element'] != 'NONE' && $jutsu['element'] != 'None')
                                    {
                                        if($jutsu['element'] == $this->users[$username][parent::ELEMENTS][0] && $this->users[$username]['rank'] >= 3)
                                            $found_mastery = 0;

                                        else if (isset($this->users[$username][parent::ELEMENTS][1]) && $jutsu['element'] == $this->users[$username][parent::ELEMENTS][1] && $this->users[$username]['rank'] >= 4)
                                            $found_mastery = 1;

                                        else if (isset($this->users[$username][parent::ELEMENTS][3]) && $jutsu['element'] == $this->users[$username][parent::ELEMENTS][3] && $this->users[$username]['rank'] >= 4)
                                            $found_mastery = 2;
                                    }

                                    // if a mastery fasfound record its information.
                                    if($found_mastery != -1)
                                    {
                                        $mastery_percentage = $this->users[$username][parent::ELEMENTMASTERIES][$found_mastery]; //getting matsery percentage

                                        //updating costs
                                        $this->users[$username]['jutsus'][$jutsu['jid']]['hea_cost'] -= $this->users[$username]['jutsus'][$jutsu['jid']]['hea_cost'] * (($mastery_percentage * 0.35) / 100);

                                        //updating max_uses
                                        if($this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] < 0)
                                        {
                                            //do nothing
                                        }
                                        else if($mastery_percentage >= 100)
                                            $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 0;

                                        else if($mastery_percentage >= 70)
                                            $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 1;

                                        else if($mastery_percentage >= 40)
                                            $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 2;

                                        else
                                            $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 3;

                                        //updating weather or not you can use elemental weapons of that type.
                                        if($mastery_percentage >= 25)
                                            $this->users[$username]['jutsus'][$jutsu['jid']]['allow_elemental_weapons'] = true;
                                    }


                                    //open cool down pools for this user.
                                    if(!isset($this->users[$username]['jutsus']['cooldowns']))
                                        $this->users[$username]['jutsus']['cooldowns'] = array();

                                    //get list of all cool down pools this users can reference
                                    if(!is_array($jutsu['cooldown_pool_set']))
                                        $jutsu['cooldown_pool_set'] = array();

                                    if(!is_array($jutsu['cooldown_pool_check']))
                                        $jutsu['cooldown_pool_check'] = array();

                                    $cooldown_pools = array_merge($jutsu['cooldown_pool_set'], $jutsu['cooldown_pool_check']);

                                    //start all cool down pools
                                    foreach($cooldown_pools as $cooldown_pool)
                                    {
                                        if($cooldown_pool != '')
                                            if(!isset($this->users[$username]['jutsus']['cooldowns'][$cooldown_pool]))
                                                $this->users[$username]['jutsus']['cooldowns'][$cooldown_pool] = 0;
                                    }

                                    //start cool down for this jutsu
                                    $this->users[$username]['jutsus']['cooldowns'][$jutsu['jid']] = 0;
                                }
                            }
                        }

                        //grabbing everything from the database and recording it.
                        //if this is a weapon pull weapon data
                        else if($type == 'weapon' && count($weapon_ids) != 0)
                        {
                            $temp = '';
                            //building the ids in a sql friendly way
                            foreach($weapon_ids as $i => $weapon_id)
                            {
                                if($i != 0)
                                    $temp .= ',';
                                $temp .= '"'.$weapon_id.'"';
                            }
                            $weapon_ids = $temp;

                            //building query for weapons
                            $query = 'SELECT `id`, `name`,
                                            `element`, `on_use_tags`,
                                            `on_jutsu_tags`, `targeting_type`, `max_uses`, `priority`
                                                FROM `items` WHERE `id` in ('.$weapon_ids.')';

                            if(!isset($this->query_buffer[$query]))
                            {
                                //getting jutsu data from database, nested for stability.
                            //if the query fails it will try again.
                                try { if(! $weapons = $GLOBALS['database']->fetch_data($query)) throw new Exception('query failed'); }
                                catch (Exception $e)
                                {
                                    try { if(! $weapons = $GLOBALS['database']->fetch_data($query)) throw new Exception ('query failed'); }
                                    catch (Exception $e)
                                    {
                                        try { if(! $weapons = $GLOBALS['database']->fetch_data($query)) throw new Exception ('query failed to update user information'); }
                                        catch (Exception $e)
                                        {
                                            $GLOBALS['DebugTool']->push('','there was an error getting weapon information.', __METHOD__, __FILE__, __LINE__);
                                            throw $e;
                                        }
                                    }
                                }
                            }
                            else
                            {
                                $weapons = $this->query_buffer[$query];
                            }

                            //double checking to make sure that db response was good
                            if(is_array($weapons) && count($weapons) != 0)
                            {
                                $this->query_buffer[$query] = $weapons;

                                //foreach weapon found
                                foreach($weapons as $weapon)
                                {
                                    //recording needed data.
                                    $this->users[$username]['equipment'][$weapon['id']] = $weapon;
                                    $this->users[$username]['equipment'][$weapon['id']]['uses'] = 0;
                                    $this->users[$username]['equipment'][$weapon['id']]['iid'] = $this->users[$username]['equipment'][$weapon['id']]['id'];
                                    $this->users[$username]['equipment'][$weapon['id']]['ai_actions'] = $this->users[$username]['ai_actions'][$type][$weapon['id']];
                                    $this->users[$username]['equipment'][ $weapon['id'] ]['uses'] = 0; 
                                }
                            }
                        }

                        //grabbing everything from the database and recording it.
                        //if this is a item pull item data
                        else if($type == 'item' && count($item_ids) != 0)
                        {
                            $temp = '';

                            //building ids in an sql friendly format
                            foreach($item_ids as $i => $item_id)
                            {
                                if($i != 0)
                                    $temp .= ',';
                                $temp .= '"'.$item_id.'"';
                            }
                            $item_ids = $temp;

                            //building query for database
                            $query = 'SELECT `id`, `name`,
                                            `element`, `on_use_tags`,
                                            `on_jutsu_tags`, `targeting_type`, `max_uses`, `priority`
                                                FROM `items` WHERE `id` in ('.$item_ids.')';

                            //getting jutsu data from database, nested for stability.
                            //if the query fails it will try again.
                            try { if(! $items = $GLOBALS['database']->fetch_data($query)) throw new Exception('query failed'); }
                            catch (Exception $e)
                            {
                                try { if(! $items = $GLOBALS['database']->fetch_data($query)) throw new Exception ('query failed'); }
                                catch (Exception $e)
                                {
                                    try { if(! $items = $GLOBALS['database']->fetch_data($query)) throw new Exception ('query failed to update user information'); }
                                    catch (Exception $e)
                                    {
                                        $GLOBALS['DebugTool']->push('','there was an error getting item information.', __METHOD__, __FILE__, __LINE__);
                                        throw $e;
                                    }
                                }
                            }

                            //making user response from db was good.
                            if(is_array($items) && count($items) != 0)
                            {
                                //foreach item found
                                foreach($items as $item)
                                {
                                    //record needed data.
                                    $this->users[$username]['items'][$item['id']] = $item;
                                    $this->users[$username]['items'][$item['id']]['uses'] = 0;
                                    $this->users[$username]['items'][$item['id']]['iid'] = $this->users[$username]['items'][$item['id']]['id'];
                                    $this->users[$username]['items'][$item['id']]['ai_actions'] = $this->users[$username]['ai_actions'][$type][$item['id']];
                                    $this->users[$username]['items'][$item['id']]['uses'] = 0;
                                }
                            }
                        }
                    }
                }

                //getting instruction information.
                $this->users[$username]['instructions'] = $user['instructions'];

                //parsing instructions
                if($user['instructions'] != '' && $user['instructions'] != 'random')
                {
                    //break each instruction into an array
                    $this->users[$username]['instructions'] = explode('|',$user['instructions']);

                    //foreach instruction
                    foreach($this->users[$username]['instructions'] as $key => $data)
                    {
                        //break instruction data down into an array.
                        $this->users[$username]['instructions'][$key] = explode(',',$data);

                        //pull each piece out into its own var for readablility
                        $targeting = trim($this->users[$username]['instructions'][$key][0]);
                        $control = trim($this->users[$username]['instructions'][$key][1]);
                        $percentage = trim($this->users[$username]['instructions'][$key][2]);
                        $breaking = trim($this->users[$username]['instructions'][$key][3]);
                        $this->users[$username]['instructions'][$key][0] = $targeting;
                        $this->users[$username]['instructions'][$key][1] = $control;
                        $this->users[$username]['instructions'][$key][2] = $percentage;
                        $this->users[$username]['instructions'][$key][3] = $breaking;

                        //this is a little short cut to get an array of all actions to be taken
                        //by this instruction. (ability chaining).
                        $actions = $this->users[$username]['instructions'][$key];
                        unset($actions[0]);
                        unset($actions[1]);
                        unset($actions[2]);
                        unset($actions[3]);

                        //this is re-basing the array so the keys start at 0 not 4
                        $actions = array_values($actions);

                        //saving instruction to user
                        $this->users[$username]['instructions'][$key] = array('targeting' => $targeting, 'control' => $control, 'percentage' => $percentage, 'breaking' => $breaking, 'actions' => $actions);

                        //then for each action avaliable to this action
                        foreach($this->users[$username]['instructions'][$key]['actions'] as $action_key => $action_data)
                        {
                            //break the actions data down into an array and save it.
                            $this->users[$username]['instructions'][$key]['actions'][$action_key] = explode(';', $this->users[$username]['instructions'][$key]['actions'][$action_key]);
                            $this->users[$username]['instructions'][$key]['actions'][$action_key] = array('type' => $this->users[$username]['instructions'][$key]['actions'][$action_key][0], 'id' => $this->users[$username]['instructions'][$key]['actions'][$action_key][1]);
                        }

                    }
                }


                //adding bloodline tags
                if(isset($user['tags']) && $user['tags'] != '')
                    $this->addTags( $this->parseTags($user['tags']), $username, $username, parent::BLOODLINE);



                //defining the basic attack jutsu for the ai.
                if(!isset($this->jutsus[-1]))
                {
                    $this->jutsus[-1] = array( 'jid' => -1,
                                               'name' => 'Basic Attack',
                                               'description' => 'a basic attack.',
                                               'battle_description' => '%user attacked %opponent using %useritem %target_type.',
                                               'element' => 'None',
                                               'village' => '',
                                               'bloodline' => '',
                                               'clan' => '',
                                               'kage' => '',
                                               'jutsu_type' => 'normal',
                                               'splitJutsu' => 'no',
                                               'loyaltyRespectReq' => NULL,
                                               'tags' => 'damage:(value>(250,250);targetGeneral>(highest,highest);targetType>highest)',
                                               'cooldown_pool_set' => array(''),
                                               'cooldown_pool_check' => array(''),
                                               'reagents' => NULL,
                                               'weapons' => NULL,
                                               'max_level' => '1000',
                                               'override_cooldown' => '0',
                                               'targeting_type' => 'opponent',
                                               'targetElement' => 'None',
                                               'priority' => 2 );
                }

                //adding cool down spot of basic attack jutsu
                $this->users[$username]['jutsus']['cooldowns'][-1] = 0;

                //adding basic attack jutsu information for the individual user.
                $rank = 0;
                if($user['rank_id'] == 2)
                    $rank = 1;
                else if($user['rank_id'] == 3)
                    $rank = 7;
                else if($user['rank_id'] == 4)
                    $rank = 28;
                else if($user['rank_id'] == 5)
                    $rank = 63;

                $this->users[$username]['jutsus'][-1] = array(
                    'level' => $rank,
                    'exp' => -100000,
                    'max_uses' => -1,
                    'uses' => 0,
                    'reagent_status' => true,
                    'hea_cost' => 0,
                    'sta_cost' => 0,
                    'cha_cost' => 0,
                    'allow_elemental_weapons' => false,
                    'ai_actions' => array('type' => 'jutsu', 'id' => -1, 'level' => $rank, 'targeting' => 'opponent', 'weapons' => '')
                    );

                $this->updateDR_SR($username);
                $this->users[$username]['DSR'] = $this->findDSR($username);
                $this->users[$username]['update']['starting_dsr'] = $this->users[$username]['DSR'];

            }
        }
        //if the query failed display an error message.
        else
            //if debugging show debugging message.
            if($this->debugging)
                $GLOBALS['DebugTool']->push('could not fetch user data. query: '."SELECT `users`.`id`, `users`.`bloodline`, `users_statistics`.`rank_id`, `users_statistics`.`cur_health`, `users_statistics`.`max_health`, `users_statistics`.`cur_sta`, `users_statistics`.`max_sta`, `users_statistics`.`cur_cha`, `users_statistics`.`max_cha`, `users_statistics`.`tai_off`, `users_statistics`.`tai_def`, `users_statistics`.`nin_off`, `users_statistics`.`nin_def`, `users_statistics`.`gen_off`, `users_statistics`.`gen_def`, `users_statistics`.`weap_off`, `users_statistics`.`weap_def`, `users_statistics`.`intelligence`, `users_statistics`.`willpower`, `users_statistics`.`speed`, `users_statistics`.`strength`, `users_statistics`.`specialization` from `users`inner join `users_statistics` on (`users`.`id` = `users_statistics`.`uid`) WHERE `username` = '".$username."'", 'there is an issue with this user: '.$username, __METHOD__, __FILE__, __LINE__);
            //if not debugging throw exception.
            else
                throw new Exception ('could not fetch user data');

        return $ais;
    }


    //addUser extends the addUser method of Tags
    //gets all data required by the user and adds it to the cache.
    function addUser($uid, $team_or_extra_data, $username_over_uid = false)
    {
        //processing extradata/team
        $team = '';
        $attacker = null;
        $defender = null;
        $respondent = null;
        $opponents_allegiance = null;
        $no_cfh = null;

        //pulling extra data out and setting it
        if(is_array($team_or_extra_data))
        {
            $team = $team_or_extra_data['team'];

            if(isset($team_or_extra_data['attacker']))
                $attacker = $team_or_extra_data['attacker'];

            if(isset($team_or_extra_data['defender']))
                $defender = $team_or_extra_data['defender'];

            if(isset($team_or_extra_data['respondent']))
                $defender = $team_or_extra_data['respondent'];

            if(isset($team_or_extra_data['opponents_allegiance']))
                $opponents_allegiance = $team_or_extra_data['opponents_allegiance'];

            if(isset($team_or_extra_data['no_cfh']))
                $no_cfh = $team_or_extra_data['no_cfh'];

            if(isset($team_or_extra_data['starting_status']))
                $starting_status = $team_or_extra_data['starting_status'];
            else
                $starting_status = false;
        }
        else
        {
            $team = $team_or_extra_data;
        }

        //making sure that usernames is an array
        if (!is_array($uid))
            $uid = array($uid);

        $temp_names = '';

        //getting user names into an sql friendly format.
        foreach($uid as $i => $username)
        {
            if($i != 0)
                $temp_names .= ',';

            $temp_names .= "'" . $username . "'";
        }

        //getting user data from database
        $select_statement = "SELECT
            `users`.`id`, `users`.`bloodline`, `users`.`village`, `users`.`username`,
            `users`.`latitude`, `users`.`longitude`,
            `users`.`gender`, `users`.`database_fallback`,
            `users`.`location`,

            `users_statistics`.`rank_id`, `users_statistics`.`rank`,
            `users_statistics`.`cur_health`, `users_statistics`.`max_health`,
            `users_statistics`.`cur_sta`, `users_statistics`.`max_sta`,
            `users_statistics`.`cur_cha`, `users_statistics`.`max_cha`,

            `users_statistics`.`tai_off`, `users_statistics`.`tai_def`,
            `users_statistics`.`nin_off`, `users_statistics`.`nin_def`,
            `users_statistics`.`gen_off`, `users_statistics`.`gen_def`,
            `users_statistics`.`weap_off`, `users_statistics`.`weap_def`,

            `users_statistics`.`intelligence`, `users_statistics`.`willpower`,
            `users_statistics`.`speed`, `users_statistics`.`strength`,
            `users_statistics`.`specialization`, `users_statistics`.`pvp_streak`,
            `users_statistics`.`money`, `users_statistics`.`user_rank`,

            `users_preferences`.`anbu`, `users_preferences`.`clan`,

            `clans`.`name` as clan_name, `clans`.`element` as `clan_element`,

            `bloodlines`.`tags`, `bloodlines`.`rarity`,

            `users_loyalty`.`vil_loyal_pts`,

            `users_occupations`.`special_occupation`, `users_occupations`.`feature`,
            `users_occupations`.`bountyHunter_exp`,

            `villages`.`leader`,

            CASE
            	WHEN `users`.`village` = 'Konoki' THEN `bingo_book`.`Konoki`
                WHEN `users`.`village` = 'Silence' THEN `bingo_book`.`Silence`
                WHEN `users`.`village` = 'Samui' THEN `bingo_book`.`Samui`
                WHEN `users`.`village` = 'Shroud' THEN `bingo_book`.`Shroud`
                WHEN `users`.`village` = 'Shine' THEN `bingo_book`.`Shine`
                WHEN `users`.`village` = 'Syndicate' THEN `bingo_book`.`Syndicate`
            END AS diplomacy

            from `users`
            inner join `users_statistics` on (`users`.`id` = `users_statistics`.`uid`)
            inner join `users_loyalty` on (`users_loyalty`.`uid` = `users`.`id`)
            inner join `users_preferences` on (`users`.`id` = `users_preferences`.`uid`)
            inner join `users_occupations` on (`users`.`id` = `users_occupations`.`userid`)
            inner join `bingo_book` on (`users`.`id` = `bingo_book`.`userID`)
            inner join `villages` on (`users`.`village` = `villages`.`name`)
            left join `clans` on (`users_preferences`.`clan` = `clans`.`id`)
            left join `bloodlines` on (`users`.`bloodline` LIKE CONCAT(`bloodlines`.`name`,'%'))

            WHERE ";

            if($username_over_uid === true)
                $select_statement .= "`username` IN (".$temp_names.")";
            else if ($username_over_uid === false)
                $select_statement .= "`users`.`id` IN (".$temp_names.")";

        //sending query to db and getting response.
        //nested for stability, will call up to 3 times if there are errors.
        try { if(! $query = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception('query failed'); }
        catch (Exception $e)
        {
            try { if(! $query = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed'); }
            catch (Exception $e)
            {
                try { if(! $query = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed to update user information'); }
                catch (Exception $e)
                {
                    $GLOBALS['DebugTool']->push($temp_names,'there was an error getting user information.', __METHOD__, __FILE__, __LINE__);
                    throw $e;
                }
            }
        }

        //checking to make sure the query went well
        if(is_array($query) && isset($query[0]['id']))
        {
            foreach($query as $user)
            {
                //getting username
                $username = $user['username'];

                //adding username and team to the system.
                parent::addUser($username,$team);
                $this->census[] = $username.'/'.$team.'/human/na';

                //setting data
                {
                    //priming the update location
                    $this->users[$username]['update'] = array();

                    //checking and updating traitor status
                    if($team == 'Traitor')
                    {
                        $this->users[$username]['update']['traitor'] = true;
                    }

                    //setting user location.
                    $this->users[$username]['location'] = $user['location'];

                    //setting first link code
                    $this->users[$username]['link_code'] = random_int(0,PHP_INT_MAX);
                    $this->users[$username]['database_fallback'] = 0;

                    //setting starting status (this is for spar)
                    if(isset($starting_status))
                        $this->users[$username]['starting_status'] = $starting_status;

                    //setting pools
                    $this->users[$username][parent::HEALTH] = $user['cur_health'];
                    $this->users[$username][parent::HEALTHMAX] = $user['max_health'];
                    $this->users[$username][parent::STAMINA] = $user['cur_sta'];
                    $this->users[$username][parent::STAMINAMAX] = $user['max_sta'];
                    $this->users[$username][parent::CHAKRA] = $user['cur_cha'];
                    $this->users[$username][parent::CHAKRAMAX] = $user['max_cha'];
                    $this->users[$username]['ai'] = false;
                    $this->users[$username]['events'] = array();

                    //setting offense
                    $this->users[$username][parent::OFFENSE.parent::TAIJUTSU] = $user['tai_off'];
                    $this->users[$username][parent::OFFENSE.parent::NINJUTSU] = $user['nin_off'];
                    $this->users[$username][parent::OFFENSE.parent::GENJUTSU] = $user['gen_off'];
                    $this->users[$username][parent::OFFENSE.parent::BUKIJUTSU] = $user['weap_off'];

                    //setting defense
                    $this->users[$username][parent::DEFENSE.parent::TAIJUTSU] = $user['tai_def'];
                    $this->users[$username][parent::DEFENSE.parent::NINJUTSU] = $user['nin_def'];
                    $this->users[$username][parent::DEFENSE.parent::GENJUTSU] = $user['gen_def'];
                    $this->users[$username][parent::DEFENSE.parent::BUKIJUTSU] = $user['weap_def'];

                    //setting rank id and display and village
                    $this->users[$username][parent::RANK] = $user['rank_id'];
                    $this->users[$username]['user_rank'] = $user['user_rank'];
                    $this->users[$username]['display_rank'] = $user['rank'];
                    $this->users[$username]['gender'] = strtolower($user['gender']);
                    $this->users[$username]['village'] = $user['village'];
                    $this->users[$username]['leader'] = $user['leader'];
                    $this->users[$username]['vil_loyal_pts'] = $user['vil_loyal_pts'];
                    $this->users[$username]['diplomacy'] = $user['diplomacy'];
                    $this->users[$username]['pvp_streak'] = $user['pvp_streak'];

                    //checking to see if this user is a bounty hunter and if so set their target.
                    if($user['special_occupation'] == '2' || $user['special_occupation'] == '3')
                    {
                        $this->users[$username]['bounty_hunter'] = $user['feature'];
                        $this->users[$username]['bounty_hunter_exp'] = $user['bountyHunter_exp'];
                    }
                    else
                        $this->users[$username]['bounty_hunter'] = false;

                    //getting lat and lon
                    $this->users[$username]['latitude'] = $user['latitude'];
                    $this->users[$username]['longitude'] = $user['longitude'];

                    //getting money
                    $this->users[$username]['money'] = $user['money'];

                    //setting UID
                    $this->users[$username]['uid'] = $user['id'];
                    $this->user_index[$user['id']] = $username;

                    //setting elements
                    $elements = new Elements($user['id'], $user['rank_id']);
                    $this->users[$username][parent::ELEMENTS] = $elements->getUserElements();
                    $this->users[$username][parent::ELEMENTMASTERIES] = $elements->getUserElementMastery(NULL, $user['rank_id']);

                    //setting bloodline
                    $this->users[$username][parent::BLOODLINE] = $user['bloodline'];
                    $this->users[$username]['rarity'] = $user['rarity'];

                    //checking for split bloodline
                    $temp = explode(':', $this->users[$username][parent::BLOODLINE]);
                    if(isset($temp[1]) && $temp[1] != '')
                    {
                        if($temp[1] == 'Taijutsu' || $temp[1] == 'Ninjutsu' || $temp[1] == 'Genjutsu' || $temp[1] == 'Bukijutsu' || $temp[1] == 'Highest')
                        {
                            $this->users[$username]['bloodline_type'] = $temp[1];
                        }
                        else
                        {
                            $this->users[$username]['bloodline_type'] = 'Highest';
                        }
                    }
                    else
                        $this->users[$username]['bloodline_type'] = 'none';

                    //setting status effect area
                    $this->users[$username]['status_effects'] = array();

                    //setting generals
                    $this->users[$username][parent::STRENGTH]       = $user['strength'];
                    $this->users[$username][parent::WILLPOWER]      = $user['willpower'];
                    $this->users[$username][parent::INTELLIGENCE]   = $user['intelligence'];
                    $this->users[$username][parent::SPEED]          = $user['speed'];
                    $this->users[$username][parent::SPECIALIZATION] = $user['specialization'];

                    //check for specialization
                    if($this->users[$username][parent::SPECIALIZATION] != '')
                    {
                        //get specialization
                        $this->users[$username][parent::SPECIALIZATION] = explode(':',$this->users[$username][parent::SPECIALIZATION]);
                        if(count($this->users[$username][parent::SPECIALIZATION]) == 2)
                            //if specialization is good record it.
                            if($this->users[$username][parent::SPECIALIZATION][1] == 1)
                                $this->users[$username][parent::SPECIALIZATION] = $this->users[$username][parent::SPECIALIZATION][0];

                            //if specialization is bad set it to default
                            else
                            {
                                if($this->debugging)
                                    $GLOBALS['DebugTool']->push('set specialization to N','specialization was set to "0" ', __METHOD__, __FILE__, __LINE__);

                                $this->users[$username][parent::SPECIALIZATION] = false;
                            }
                        //if specialization is bad set it to deault
                        else
                        {
                            if($this->debugging)
                                $GLOBALS['DebugTool']->push('set specialization to N','specialization was missing ":" ', __METHOD__, __FILE__, __LINE__);

                            $this->users[$username][parent::SPECIALIZATION] = false;
                        }
                    }
                    //if specialization is bad set it to default
                    else
                    {
                        if($this->debugging)
                            $GLOBALS['DebugTool']->push('set specialization to N','specialization was "" ', __METHOD__, __FILE__, __LINE__);

                        $this->users[$username][parent::SPECIALIZATION] = false;
                    }

                    //setting equipment stats to zero at default
                    $this->users[$username][parent::ARMORBASE]      = 0;
                    $this->users[$username][parent::MASTERY]        = 0;
                    $this->users[$username][parent::STABILITY]      = 0;
                    $this->users[$username][parent::ACCURACY]       = 0;
                    $this->users[$username][parent::EXPERTISE]      = 0;
                    $this->users[$username][parent::CHAKRAPOWER]    = 0;
                    $this->users[$username][parent::CRITICALSTRIKE] = 0;

                    //initializing equipment array
                    $this->users[$username][parent::EQUIPMENT] = array();

                    //getting profile picture location
                    $this->users[$username]['avatar'] = functions::getAvatar($user['id']);

                    //processing anbu and clan information
                    if($user['anbu'] !== '_none' && $user['anbu'] !== '_disabled' && $user['anbu'] !== '' && is_numeric($user['anbu']))
                        $this->users[$username]['anbu'] = $user['anbu'];
                    else
                        $this->users[$username]['anbu'] = false;

                    if($user['clan'] !== '_none' && $user['clan'] !== '_disabled' && $user['clan'] !== '' && is_numeric($user['clan']))
                    {
                        $this->users[$username]['clan'] = $user['clan'];
                        $this->users[$username]['clan_name'] = $user['clan_name'];
                        $this->users[$username]['clan_element'] = $user['clan_element'];

                    }
                    else
                        $this->users[$username]['clan'] = false;

                    //adding extra data passed in.

                    if($attacker != null)
                        $this->users[$username]['attacker'] = $attacker;

                    if($defender != null)
                        $this->users[$username]['defender'] = $defender;

                    if($respondent != null)
                        $this->users[$username]['respondent'] = $respondent;

                    if($opponents_allegiance != null)
                        $this->users[$username]['update']['opponents_allegiance'] = $opponents_allegiance;

                    if($no_cfh != null)
                        $this->users[$username]['no_cfh'] = $no_cfh;
                }

                //adding bloodline tags
                if(isset($user['tags']) && $user['tags'] != '')
                {
                    if($this->users[$username]['bloodline_type'] == 'none')
                        $this->addTags( $this->parseTags($user['tags']), $username, $username, parent::BLOODLINE);
                    else
                    {
                        //breaking up split tags into an array
                        $un_exploded_types = explode(']',$user['tags']);
                        $exploded_types = array();
                        foreach($un_exploded_types as $un_exploded_type)
                        {
                            $exploded_type = explode('[',$un_exploded_type);
                            if(isset($exploded_type[0]) && isset($exploded_type[1]) && $exploded_type[1] != '')
                                $exploded_types[$exploded_type[1]] = $exploded_type[0];
                        }


                        //finding what type to use
                        if($this->users[$username]['bloodline_type'] == 'Taijutsu')
                            $type = 'T';
                        else if($this->users[$username]['bloodline_type'] == 'Ninjutsu')
                            $type = 'N';
                        else if($this->users[$username]['bloodline_type'] == 'Genjutsu')
                            $type = 'G';
                        else if($this->users[$username]['bloodline_type'] == 'Bukijutsu')
                            $type = 'B';
                        else if($this->users[$username]['bloodline_type'] == 'Highest')
                        {
                            if($this->users[$username][parent::SPECIALIZATION] !== false)
                                $type = $this->users[$username][parent::SPECIALIZATION];
                            else
                                $type = false;
                        }
                        else
                            $type = 'T';

                        //adding tags
                        if($type !== false)
                        {
                            $user['tags'] = $exploded_types[$type];
                            $this->addTags( $this->parseTags($user['tags']), $username, $username, parent::BLOODLINE);
                        }
                    }
                }

                //processing equipment
                {
                    //getting equipped item data
                    $select_statement = "SELECT
                    `users_inventory`.`id`, `users_inventory`.`iid`,
                    `users_inventory`.`equipped`, `users_inventory`.`durabilityPoints`,
                    `users_inventory`.`times_used`, `users_inventory`.`stack`,

                    `items`.`name`, `items`.`type`,
                    `items`.`armor_types`, `items`.`weapon_classifications`,
                    `items`.`max_uses`, `items`.`when_equipped_tags`,
                    `items`.`on_jutsu_tags`, `items`.`uses`,
                    `items`.`on_use_tags`, `items`.`armor`,
                    `items`.`mastery`, `items`.`stability`,
                    `items`.`accuracy`, `items`.`expertise`,
                    `items`.`chakra_power`, `items`.`critical_strike`,
                    `items`.`strength`, `items`.`element`,
                    `items`.`targeting_type`, `items`.`consumable`,
                    `items`.`priority`, `items`.`infinity_durability`,
                    `items`.`durability`, `items`.`description`,
                    `items`.`stack_size`

                    FROM `users_inventory`
                    INNER JOIN `items` on (`users_inventory`.`iid` = `items`.`id`)
                    WHERE `users_inventory`.`trading` is null
                    and `users_inventory`.`finishProcessing` = 0
                    and `users_inventory`.`uid` = ".$this->users[$username]['uid'];

                    //getting query response from db
                    //nested calls for catching errors, will call 1 to 3 times.
                    try { if(! $items = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception('query failed'); }
                    catch (Exception $e)
                    {
                        try { if(! $items = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed'); }
                        catch (Exception $e)
                        {
                            try { if(! $items = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed to update user information'); }
                            catch (Exception $e)
                            {
                                $GLOBALS['DebugTool']->push('','there was an error getting  information.', __METHOD__, __FILE__, __LINE__);
                                throw $e;
                            }
                        }
                    }


                    //if there is atleast one item
                    if(isset($items[0]['name']))
                    {
                        //makingsure that weapon used array is initialized
                        if(!isset($this->users[$username]['weapons_used']))
                            $this->users[$username]['weapons_used'] = array();

                        //counting weapons
                        $weapon_count = 0;
                        foreach($items as $item)
                        {
                            if($item['type'] == 'weapon')
                                $weapon_count++;
                        }

                        //fixing weapon stats
                        foreach($items as $item_key => $item)
                        {
                            if($item['type'] == 'weapon')
                            {
                                $items[$item_key][parent::ARMORBASE] /= $weapon_count;
                                $items[$item_key][parent::MASTERY]  /= $weapon_count;
                                $items[$item_key][parent::STABILITY]  /= $weapon_count;
                                $items[$item_key][parent::ACCURACY]  /= $weapon_count;
                                $items[$item_key][parent::EXPERTISE]  /= $weapon_count;
                                $items[$item_key]['chakra_power']  /= $weapon_count;
                                $items[$item_key]['critical_strike']  /= $weapon_count;
                            }
                        }

                        //for each item found
                        foreach($items as $item)
                        {
                            //so long as this item is equipped and has a durability > 0
                            if($item['durabilityPoints'] > 0 && $item['equipped'] == 'yes')
                            {
                                //recording equipments data
                                $this->users[$username][parent::EQUIPMENT][$item['id']] = array();
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['type'] = $item['type'];

                                $armor_types_conversion = array('armor'=>'C', 'helmet'=>'H', 'gloves'=>'G', 'belt'=>'W', 'shoes'=>'F', 'pants'=>'L');
                                if($item['type'] == 'armor')
                                    $this->users[$username][parent::EQUIPMENT][$item['id']]['armor_type'] = $armor_types_conversion[$item['armor_types']];
                                else
                                    $this->users[$username][parent::EQUIPMENT][$item['id']]['armor_type'] = '';

                                $this->users[$username][parent::EQUIPMENT][$item['id']]['durability'] = $item['durabilityPoints'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['max_durability'] = $item['durability'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['starting_durability'] = $item['durabilityPoints'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['infinity_durability'] = $item['infinity_durability'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['starting_times_used'] = $item['times_used'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['weapon_classifications'] = $item['weapon_classifications'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['name'] = $item['name'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['description'] = $item['description'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['iid'] = $item['iid'];
                                $this->users[$username]['equipment_used'][$item['iid']] = array('uses' => 0, 'max_uses' => $item['max_uses']);

                                $this->users[$username][parent::EQUIPMENT][$item['id']]['element'] = $item['element'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['targeting_type'] = $item['targeting_type'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['stack'] = $item['stack'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['starting_stack'] = $item['stack'];

                                //set collect stat information
                                $this->users[$username][parent::EQUIPMENT][$item['id']][parent::ARMORBASE]      = $item[parent::ARMORBASE];
                                $this->users[$username][parent::EQUIPMENT][$item['id']][parent::MASTERY]        = $item[parent::MASTERY];
                                $this->users[$username][parent::EQUIPMENT][$item['id']][parent::STABILITY]      = $item[parent::STABILITY];
                                $this->users[$username][parent::EQUIPMENT][$item['id']][parent::ACCURACY]       = $item[parent::ACCURACY];
                                $this->users[$username][parent::EQUIPMENT][$item['id']][parent::EXPERTISE]      = $item[parent::EXPERTISE];
                                $this->users[$username][parent::EQUIPMENT][$item['id']][parent::CHAKRAPOWER]    = $item['chakra_power'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']][parent::CRITICALSTRIKE] = $item['critical_strike'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['strength']             = $item['strength'];

                                //recording on use tags.
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['on_use_tags']          = $item['on_use_tags'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['on_jutsu_tags']        = $item['on_jutsu_tags'];

                                $this->users[$username][parent::EQUIPMENT][$item['id']]['on_use_effects']       = jutsuBasicFunctions::parseEffects($item['on_use_tags']);
                                
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['on_jutsu_effects']     = jutsuBasicFunctions::parseEffects($item['on_jutsu_tags']);

                                //recording item priority
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['priority'] = $item['priority'];

                                //recording armor
                                $this->users[$username][parent::ARMORBASE]      += $item[parent::ARMORBASE];

                                //if this would break the mastery cap fix it
                                if($this->users[$username][parent::MASTERY] + $item[parent::MASTERY] > $this->MASTERYCAP[$user['rank_id']] && $item['type'] != 'weapon')
                                {
                                    $diff = ($this->users[$username][parent::MASTERY] + $item[parent::MASTERY]) - $this->MASTERYCAP[$user['rank_id']];
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::MASTERY] = $diff;
                                    $this->users[$username][parent::MASTERY] = $this->MASTERYCAP[$user['rank_id']];
                                }
                                //if this would drop mastery less than 0
                                else if($this->users[$username][parent::MASTERY] + $item[parent::MASTERY] < 0 && $item['type'] != 'weapon')
                                {
                                    $diff = $this->users[$username][parent::MASTERY] * -1;
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::MASTERY] = $diff;
                                    $this->users[$username][parent::MASTERY] = 0;
                                }
                                else
                                    $this->users[$username][parent::MASTERY]    += $item[parent::MASTERY];



                                //if this would break the stability cap fix it
                                if($this->users[$username][parent::STABILITY] + $item[parent::STABILITY] > $this->STABILITYCAP[$user['rank_id']] && $item['type'] != 'weapon')
                                {
                                    $diff = ($this->users[$username][parent::STABILITY] + $item[parent::STABILITY]) - $this->STABILITYCAP[$user['rank_id']];
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::STABILITY] = $diff;
                                    $this->users[$username][parent::STABILITY] = $this->STABILITYCAP[$user['rank_id']];
                                }
                                //if this would drop stability less than 0
                                else if($this->users[$username][parent::STABILITY] + $item[parent::STABILITY] < 0 && $item['type'] != 'weapon')
                                {
                                    $diff = $this->users[$username][parent::STABILITY] * -1;
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::STABILITY] = $diff;
                                    $this->users[$username][parent::STABILITY] = 0;
                                }
                                else
                                    $this->users[$username][parent::STABILITY]      += $item[parent::STABILITY];



                                //if this would break the accuracy cap fix it
                                if($this->users[$username][parent::ACCURACY] + $item[parent::ACCURACY] > $this->ACCURACYCAP[$user['rank_id']] && $item['type'] != 'weapon')
                                {
                                    $diff = ($this->users[$username][parent::ACCURACY] + $item[parent::ACCURACY]) - $this->ACCURACYCAP[$user['rank_id']];
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::ACCURACY] = $diff;
                                    $this->users[$username][parent::ACCURACY] = $this->ACCURACYCAP[$user['rank_id']];
                                }
                                //if this would drop accuracy less than 0
                                else if($this->users[$username][parent::ACCURACY] + $item[parent::ACCURACY] < 0 && $item['type'] != 'weapon')
                                {
                                    $diff = $this->users[$username][parent::ACCURACY] * -1;
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::ACCURACY] = $diff;
                                    $this->users[$username][parent::ACCURACY] = 0;
                                }
                                else
                                    $this->users[$username][parent::ACCURACY]       += $item[parent::ACCURACY];



                                //if this would break the expertise cap fix it
                                if($this->users[$username][parent::EXPERTISE] + $item[parent::EXPERTISE] > $this->EXPERTISECAP[$user['rank_id']] && $item['type'] != 'weapon')
                                {
                                    $diff = ($this->users[$username][parent::EXPERTISE] + $item[parent::EXPERTISE]) - $this->EXPERTISECAP[$user['rank_id']];
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::EXPERTISE] = $diff;
                                    $this->users[$username][parent::EXPERTISE] = $this->EXPERTISECAP[$user['rank_id']];
                                }
                                //if this would drop accuracy less than 0
                                else if($this->users[$username][parent::EXPERTISE] + $item[parent::EXPERTISE] < 0 && $item['type'] != 'weapon')
                                {
                                    $diff = $this->users[$username][parent::EXPERTISE] * -1;
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::EXPERTISE] = $diff;
                                    $this->users[$username][parent::EXPERTISE] = 0;
                                }
                                else
                                    $this->users[$username][parent::EXPERTISE]      += $item[parent::EXPERTISE];



                                //if this would break the chakra power cap fix it
                                if($this->users[$username][parent::CHAKRAPOWER] + $item['chakra_power'] > $this->CHAKRAPOWERCAP[$user['rank_id']] && $item['type'] != 'weapon')
                                {
                                    $diff = ($this->users[$username][parent::CHAKRAPOWER] + $item['chakra_power']) - $this->CHAKRAPOWERCAP[$user['rank_id']];
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::CHAKRAPOWER] = $diff;
                                    $this->users[$username][parent::CHAKRAPOWER] = $this->CHAKRAPOWERCAP[$user['rank_id']];
                                }
                                //if this would drop accuracy less than 0
                                else if($this->users[$username][parent::CHAKRAPOWER] + $item['chakra_power'] < 0 && $item['type'] != 'weapon')
                                {
                                    $diff = $this->users[$username][parent::CHAKRAPOWER] * -1;
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::CHAKRAPOWER] = $diff;
                                    $this->users[$username][parent::CHAKRAPOWER] = 0;
                                }
                                else
                                    $this->users[$username][parent::CHAKRAPOWER]    += $item['chakra_power'];



                                //if this would break the critical strike cap fix it
                                if($this->users[$username][parent::CRITICALSTRIKE] + $item['critical_strike'] > $this->CRITICALSTRIKECAP[$user['rank_id']] && $item['type'] != 'weapon')
                                {
                                    $diff = ($this->users[$username][parent::CRITICALSTRIKE] + $item['critical_strike']) - $this->CRITICALSTRIKECAP[$user['rank_id']];
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::CRITICALSTRIKE] = $diff;
                                    $this->users[$username][parent::CRITICALSTRIKE] = $this->CRITICALSTRIKECAP[$user['rank_id']];
                                }
                                //if this would drop accuracy less than 0
                                else if($this->users[$username][parent::CRITICALSTRIKE] + $item['critical_strike'] < 0 && $item['type'] != 'weapon')
                                {
                                    $diff = $this->users[$username][parent::CRITICALSTRIKE] * -1;
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::CRITICALSTRIKE] = $diff;
                                    $this->users[$username][parent::CRITICALSTRIKE] = 0;
                                }
                                else
                                    $this->users[$username][parent::CRITICALSTRIKE] += $item['critical_strike'];



                                //if this piece of equipment has equipped tags apply them
                                if($item['when_equipped_tags'] != '')
                                    if($item['type'] == 'armor')
                                        $this->addTags($this->parseTags($item['when_equipped_tags']), $username, $username, parent::ARMOR, $item['id']);
                                    else if($item['type'] == 'weapon')
                                        $this->addTags($this->parseTags($item['when_equipped_tags']), $username, $username, parent::WEAPON, $item['id']);
                                    else
                                        $this->addTags($this->parseTags($item['when_equipped_tags']), $username, $username, parent::ITEM, $item['id']);
                            }

                            //making sure items and items used arrays are initialized
                            if(!isset($this->users[$username]['items']))
                            {
                                $this->users[$username]['items'] = array();
                                $this->users[$username]['items_used'] = array();
                            }

                            //if this item is a consumable item
                            if($item['type'] == 'item')
                            {
                                //record this item
                                $this->users[$username]['items'][$item['id']] = array( 'name' => $item['name'], 'targeting_type' => $item['targeting_type'],
                                                                                    'uses' => $item['uses'], 'times_used' => $item['times_used'],
                                                                                    'max_uses' => $item['max_uses'], 'stack' => $item['stack'], 
                                                                                    'starting_stack' => $item['stack'],
                                                                                    'stack_size' => $item['stack_size'],
                                                                                    'iid' => $item['iid'], 'on_use_tags'  => $item['on_use_tags'],
                                                                                    'priority' => $item['priority'], 'starting_times_used' => $item['times_used'],
                                                                                    'description'=> $item['description'],
                                                                                    'effects'=>jutsuBasicFunctions::parseEffects($item['on_use_tags']));

                                //mark it as un used
                                $this->users[$username]['items_used'][$item['iid']] = 0;
                            }
                        }
                    }

                }



                //processing jutsus
                //getting jutsu data
                $select_statement = "SELECT `jid`, `users_jutsu`.`level`, `name`, `description`, `battle_description`, `element`, `village`,
                             `bloodline`, `clan`, `kage`, `cha_cost`, `sta_cost`, `hea_cost`, `targeting_type`,
                             `jutsu_type`, `splitJutsu`, `loyaltyRespectReq`, `tags`, `cooldown_pool_set`, `priority`,
                             `cooldown_pool_check`, `reagents`, `weapons`, `max_level`, `exp`, `override_cooldown`, `max_uses`, `tagged`, `taggedGroup`, `required_rank`, `times_used` as `total_times_used`
                             from `users_jutsu`
                                inner join `jutsu` on (`jid` = `id`)
                                inner join `users_statistics` on (`users_jutsu`.`uid` = `users_statistics`.`uid`)
                             WHERE locate(`users_statistics`.`taggedGroup`, `users_jutsu`.`tagged` ) > 0
                             and `users_jutsu`.`uid` = ".$this->users[$username]['uid'];

                //getting dbs response to query
                //nested for stability. if there is an error it will try again up to 3 times.
                try { if(! $jutsus = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception('query failed'); }
                catch (Exception $e)
                {
                    try { if(! $jutsus = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed'); }
                    catch (Exception $e)
                    {
                        try { if(! $jutsus = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed to update user information'); }
                        catch (Exception $e)
                        {
                            $GLOBALS['DebugTool']->push('','there was an error getting jutsu information.', __METHOD__, __FILE__, __LINE__);
                            throw $e;
                        }
                    }
                }

                $this->users[$username]['jutsu_level_weight'] = 0;

                //makeing sure jutsu data is good.
                if(is_array($jutsus))
                {

                    $jutsu_levels = array();

                    //foeach jutsu found
                    $message = array();

                    foreach($jutsus as $jutsu_key => $jutsu)
                    {

                        if( !in_array($this->users[$username]['user_rank'], array("Moderator", "Supermod", "PRmanager","Admin","Event","EventMod","ContentAdmin"), true) )
                        {
                            //checking village
                            if( $jutsu['village'] != '' && $jutsu['village'] != $this->users[$username]['village'] )
                            {
                                $message[] = "You no longer meet the requirements for the village jutsu \"{$jutsu['name']}\", please un-tag it.";
                                continue;
                            }

                            //checking bloodline
                            if( $jutsu['bloodline'] != '' && (!isset($this->users[$username][parent::BLOODLINE]) || substr($this->users[$username][parent::BLOODLINE], 0, strlen($jutsu['bloodline'])) !== $jutsu['bloodline']) )
                            {
                                $message[] = "You no longer meet the requirements for the bloodline jutsu \"{$jutsu['name']}\", please un-tag it.";
                                continue;
                            }

                            //checking clan name
                            if( $jutsu['clan'] != '' && ( !isset($this->users[$username]['clan_name']) || $jutsu['clan'] != $this->users[$username]['clan_name'] ))
                            {
                                $message[] = "You no longer meet the requirements for the clan jutsu \"{$jutsu['name']}\", please un-tag it.";
                                continue;
                            }

                            //checking clan element
                            if($jutsu['jutsu_type'] == 'clan' && ( !isset($this->users[$username]['clan_name']) || !isset($this->users[$username]['clan_element']) || $jutsu['element'] != $this->users[$username]['clan_element'] || !in_array($jutsu['element'], $this->users[$username][parent::ELEMENTS]) ))
                            {
                                $message[] = "You no longer meet the requirements for the clan/elemental jutsu \"{$jutsu['name']}\", please un-tag it.";
                                continue;
                            }


                            //checking element
                            if($jutsu['element'] != 'None' && $jutsu['element'] != '' && !in_array(strtolower($jutsu['element']), $this->users[$username]['elements']))
                            {
                                $message[] = "You no longer meet the requirements for the elemental jutsu \"{$jutsu['name']}\", please un-tag it.";
                                continue;
                            }

                            if($jutsu['loyaltyRespectReq'] != '' && $jutsu['loyaltyRespectReq'] > $user['vil_loyal_pts'])
                            {
                                $message[] = "You no longer meet the requirements for the village loyalty jutsu \"{$jutsu['name']}\", please un-tag it.";
                                continue;
                            }
                        }

                        //process split jutsu data
                        if($jutsu['splitJutsu'] == 'yes' && $this->users[$username][parent::SPECIALIZATION] !== false)
                        {
                            //for each field in the jutsu
                            foreach($jutsu as $field_key => $field)
                            {
                                //check for split jutsu data
                                $bracket_count = substr_count($field,']');
                                $bracket_count_2 = substr_count($field,'[');
                                if($bracket_count > 0 || $bracket_count_2 > 0)
                                {
                                    if($bracket_count == 4 && $bracket_count_2 == 4)
                                    {
                                        //breaking ]
                                        $first_break = explode(']',$field);
                                        $last_break = array();

                                        //breaking [
                                        foreach($first_break as $break)
                                        {
                                            if(trim($break) != '')
                                            {
                                                $temp = explode('[',$break);

                                                if(isset($temp[1]))
                                                    $last_break[$temp[0]] = $temp[1];
                                                else
                                                {   
                                                    echo('Bad Split Jutsu on: "'.$break.'" id: '.$jutsu['jid'].' name: '.$jutsu['name']);
                                                    var_dump(
                                                        array(
                                                            'break'=>$break,
                                                            'after explode'=>$temp,
                                                            'the collection'=>$last_break
                                                        )
                                                    );
                                                    $last_break[$temp[0]] = false;
                                                }
                                            }
                                        }

                                        //setting data.
                                        $jutsu[$field_key] = $last_break;
                                    }
                                    else
                                        if($this->debugging)
                                        $GLOBALS['DebugTool']->push($field_key.' : '.$field,'bad split string data.', __METHOD__, __FILE__, __LINE__);
                                }

                            }
                        }

                        else if ($jutsu['splitJutsu'] == 'yes' && $this->users[$username][parent::SPECIALIZATION] === false)
                        {
                            $message[] = "You may not use this split jutsu \"{$jutsu['name']}\" without a specialization set, please un-tag it.";
                            continue;
                        }

                        //processing reagent data
                        if($jutsu['reagents'] !== '' && $jutsu['reagents'] !== NULL)
                        {
                            //breaking reagent data into an array.
                            $temp = explode(',',$jutsu['reagents']);

                            //initializing reagents array();
                            $jutsu['reagents'] = array();

                            //for each reagent
                            foreach($temp as $reagent)
                                //if this is not empty
                                if($reagent !== '')
                                {
                                    //checking for cost
                                    if(substr_count($reagent,'(') == 1 && substr_count($reagent,')') == 1)
                                    {
                                        //breaking cost out
                                        $explode = explode('(',$reagent);

                                        //if this reagent is not listed yet make it.
                                        if(!isset($jutsu['reagents'][$explode[0]]))
                                            $jutsu['reagents'][$explode[0]] = rtrim($explode[1],')');

                                        //if this reagent is listed already add to it.
                                        else
                                            $jutsu['reagents'][$explode[0]] += rtrim($explode[1],')');
                                    }
                                    //if no cost
                                    else
                                    {
                                        $jutsu['reagents'][$reagent] = 1;
                                    }

                                }
                        }


                        //record the jutsu's information in the pool of all users jutsu information
                        $jutsu['cooldown_pool_set'] = explode(',',$jutsu['cooldown_pool_set']);
                        $jutsu['cooldown_pool_check'] = explode(',',$jutsu['cooldown_pool_check']);

                        //adding global listing and removing data not needed.
                        if(!isset($this->jutsus[$jutsu['jid']]))
                        {
                            $this->jutsus[$jutsu['jid']] = $jutsu;
                            unset($this->jutsus[$jutsu['jid']]['level']);
                            unset($this->jutsus[$jutsu['jid']]['exp']);
                            unset($this->jutsus[$jutsu['jid']]['max_uses']);
                            unset($this->jutsus[$jutsu['jid']]['cha_cost']);
                            unset($this->jutsus[$jutsu['jid']]['sta_cost']);
                            unset($this->jutsus[$jutsu['jid']]['hea_cost']);
                            unset($this->jutsus[$jutsu['jid']]['required_rank']);
                        }

                        //make sure user jutsu location is set
                        if(!isset($this->users[$username]['jutsus']))
                            $this->users[$username]['jutsus'] = array();

                        //getting jutsu order
                        $tempgroup = explode(';', $jutsu['tagged']);
                        $group = array();
                        foreach($tempgroup as $key => $piece)
                        {
                            if($piece != '')
                            {
                                $explode = explode(':', $piece);
                                $group[$explode[0]] = $explode[1] - 1;
                            }
                        }

                        $effects = "";

                        if($this->users[$username][parent::SPECIALIZATION] == "W")
                                    $this->users[$username][parent::SPECIALIZATION] = 'B';

                        if(is_array($jutsu['tags']))
                            $effects = jutsuBasicFunctions::parseEffects( array_flip($jutsu['tags'])[$this->users[$username][parent::SPECIALIZATION]] );
                        else
                            $effects = jutsuBasicFunctions::parseEffects( $jutsu['tags'] );

                        //set personal jutsu level for this jutsu to user information.
                        $this->users[$username]['jutsus'][$jutsu['jid']] = array('level'=>$jutsu['level'], 'exp'=>$jutsu['exp'],
                                                                                    'max_uses'=>$jutsu['max_uses'], 'uses' => 0,
                                                                                    'reagent_status'=>true, 'cha_cost' => $jutsu['cha_cost'],
                                                                                    'sta_cost' => $jutsu['sta_cost'], 'hea_cost' => $jutsu['hea_cost'],
                                                                                    'allow_elemental_weapons' => false, 'order' => $group[$jutsu['taggedGroup']] - 1,
                                                                                    'total_times_used' =>$jutsu['total_times_used'], 'required_rank'=>$jutsu['required_rank'],
                                                                                    'effects'=>$effects);

                        $jutsu_levels[] = $jutsu['level'];

                        //check for elemental affinity and apply bonus's from elemental mastery.
                        $found_mastery = -1;
                        if($jutsu['element'] != '' && $jutsu['element'] != 'none' && $jutsu['element'] != 'NONE' && $jutsu['element'] != 'None')
                        {
                            if($jutsu['element'] == $this->users[$username][parent::ELEMENTS][0] && $user['rank_id'] >= 3)
                                $found_mastery = 0;

                            else if ($jutsu['element'] == $this->users[$username][parent::ELEMENTS][1] && $user['rank_id'] >= 4)
                                $found_mastery = 1;

                            else if ($jutsu['element'] == $this->users[$username][parent::ELEMENTS][3] && $user['rank_id'] >= 4)
                                $found_mastery = 2;
                        }

                        if($found_mastery != -1)
                        {
                            $mastery_percentage = $this->users[$username][parent::ELEMENTMASTERIES][$found_mastery]; //getting matsery percentage

                            //updating costs
                            $this->users[$username]['jutsus'][$jutsu['jid']]['hea_cost'] -= $this->users[$username]['jutsus'][$jutsu['jid']]['hea_cost'] * (($mastery_percentage * 0.35) / 100);
                            $this->users[$username]['jutsus'][$jutsu['jid']]['sta_cost'] -= $this->users[$username]['jutsus'][$jutsu['jid']]['sta_cost'] * (($mastery_percentage * 0.35) / 100);
                            $this->users[$username]['jutsus'][$jutsu['jid']]['cha_cost'] -= $this->users[$username]['jutsus'][$jutsu['jid']]['cha_cost'] * (($mastery_percentage * 0.35) / 100);

                            //updating max_uses
                            if($this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] < 0)
                            {
                                //do nothing
                            }
                            else if($mastery_percentage >= 100)
                                $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 0;

                            else if($mastery_percentage >= 70)
                                $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 1;

                            else if($mastery_percentage >= 40)
                                $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 2;

                            else
                                $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 3;

                            //updating weather or not you can use elemental weapons of that type.
                            if($mastery_percentage >= 25)
                                $this->users[$username]['jutsus'][$jutsu['jid']]['allow_elemental_weapons'] = true;
                        }


                        //open cool down pools for this user.
                        if(!isset($this->users[$username]['jutsus']['cooldowns']))
                            $this->users[$username]['jutsus']['cooldowns'] = array();

                        //get list of all cool down pools this users can reference
                        if(!is_array($jutsu['cooldown_pool_set']))
                            $jutsu['cooldown_pool_set'] = array();

                        if(!is_array($jutsu['cooldown_pool_check']))
                            $jutsu['cooldown_pool_check'] = array();

                        $cooldown_pools = array_merge($jutsu['cooldown_pool_set'], $jutsu['cooldown_pool_check']);

                        //start all cool down pools
                        foreach($cooldown_pools as $cooldown_pool)
                        {
                            if($cooldown_pool != '')
                                if(!isset($this->users[$username]['jutsus']['cooldowns'][$cooldown_pool]))
                                    $this->users[$username]['jutsus']['cooldowns'][$cooldown_pool] = 0;
                        }

                        //start cool down for this jutsu
                        $this->users[$username]['jutsus']['cooldowns'][$jutsu['jid']] = 0;
                    }

                    //sending message to user if they have bad jutsu tagged.
                    if(count($message) >= 1)
                        $this->notifyUser($this->users[$username]['uid'], implode('<br><br>',$message));


                    if(count($jutsu_levels) >= 3)
                    {
                        $highest_jutsu_levels = array();

                        $key_of_highest_level = array_search(max($jutsu_levels), $jutsu_levels);
                        $highest_jutsu_levels[] = $jutsu_levels[$key_of_highest_level];
                        unset($jutsu_levels[$key_of_highest_level]);
                        $key_of_highest_level = array_search(max($jutsu_levels), $jutsu_levels);
                        $highest_jutsu_levels[] = $jutsu_levels[$key_of_highest_level];
                        unset($jutsu_levels[$key_of_highest_level]);
                        $key_of_highest_level = array_search(max($jutsu_levels), $jutsu_levels);
                        $highest_jutsu_levels[] = $jutsu_levels[$key_of_highest_level];
                        unset($jutsu_levels[$key_of_highest_level]);

                        $this->users[$username]['jutsu_level_weight'] = array_sum($highest_jutsu_levels)/3;
                    }
                    else if(count($jutsu_levels) >= 1)
                        $this->users[$username]['jutsu_level_weight'] = max($jutsu_levels);
                    else
                        $this->users[$username]['jutsu_level_weight'] = 0;
                }


                //defining the basic attack jutsu
                if(!isset($this->jutsus[-1]))
                {
                    $this->jutsus[-1] = array( 'jid' => -1,
                                               'name' => 'Basic Attack',
                                               'description' => 'a basic attack.',//////
                                               'battle_description' => '%user attacked %opponent using %useritem %target_type.',
                                               'element' => 'None',
                                               'village' => '',
                                               'bloodline' => '',
                                               'clan' => '',
                                               'kage' => '',
                                               'jutsu_type' => 'normal',
                                               'splitJutsu' => 'no',
                                               'loyaltyRespectReq' => NULL,
                                               'tags' => 'damage:(value>(250,250);targetGeneral>(highest,highest);targetType>highest)',
                                               'cooldown_pool_set' => array(''),
                                               'cooldown_pool_check' => array(''),
                                               'reagents' => NULL,
                                               'weapons' => NULL,
                                               'max_level' => '1000',
                                               'override_cooldown' => '0',
                                               'targeting_type' => 'opponent',
                                               'targetElement' => 'None',
                                               'priority' => 2 );
                }

                //adding cool down spot of basic attack jutsu
                $this->users[$username]['jutsus']['cooldowns'][-1] = 0;

                //adding basic attack jutsu information for the individual user.
                $rank = 0;
                if($user['rank_id'] == 2)
                    $rank = 1;
                else if($user['rank_id'] == 3)
                    $rank = 7;
                else if($user['rank_id'] == 4)
                    $rank = 28;
                else if($user['rank_id'] == 5)
                    $rank = 63;

                $this->users[$username]['jutsus'][-1] = array(
                    'level' => $rank,
                    'exp' => -100000,
                    'max_uses' => -1,
                    'uses' => 0,
                    'reagent_status' => true,
                    'hea_cost' => 0,
                    'sta_cost' => 0,
                    'cha_cost' => 0,
                    'allow_elemental_weapons' => false,
                    'order' => 999
                    );

                //sorting jutsu
                uasort($this->users[$username]['jutsus'],
                    function($a, $b){
                        if(!isset($a['order']))
                            return 1;
                        if(!isset($b['order']))
                            return -1;

                        if($a['order'] > $b['order']) return 1;
                        else if($a['order'] < $b['order']) return -1;
                        return 0; });

                //recording dsr
                $this->updateDR_SR($username);
                $this->users[$username]['DSR'] = $this->findDSR($username);
                $this->users[$username]['update']['starting_dsr'] = $this->users[$username]['DSR'];

                if(isset($this->balanceDSR[$team]))
                    $this->balanceDSR[$team] += $this->users[$username]['DSR'];
                else
                    $this->balanceDSR[$team] = $this->users[$username]['DSR'];
            }
        }

        //if the query failed display an error message.
        else
            //if debugging show debugging message.
            if($this->debugging)
                $GLOBALS['DebugTool']->push($select_statement, 'there is an issue with this user: '.$username, __METHOD__, __FILE__, __LINE__);
            //if not debugging throw exception.
            else
                throw new Exception ('could not fetch user data');
    }


    //useWeapon
    //this method is called when a user attacks whith a weapon.
    //this method simply applys the weapons tags to the target and
    //manages durability
    public function useWeapon($target_username, $owner_username, $weapon_id)
    {
        //check to make sure that origin user has that weapon.
        if( isset( $this->users[$owner_username]['equipment'][$weapon_id]) && ($this->users[$owner_username]['equipment_used'][ $this->users[$owner_username]['equipment'][$weapon_id]['iid'] ]['uses'] < $this->users[$owner_username]['equipment_used'][ $this->users[$owner_username]['equipment'][$weapon_id]['iid'] ]['max_uses'] || $this->users[$owner_username]['equipment_used'][ $this->users[$owner_username]['equipment'][$weapon_id]['iid'] ]['max_uses'] < 0) )
        {
            //checking target
            if($this->users[$owner_username]['equipment'][$weapon_id]['targeting_type'] == 'self' && $owner_username != $target_username)
            {
                throw new exception('bad target: must target self, iid:'.$this->users[$owner_username]['equipment'][$weapon_id]['iid'].' user:'.$owner_username);
            }
            else if ( ($this->users[$owner_username]['equipment'][$weapon_id]['targeting_type'] == 'other' || $this->users[$owner_username]['equipment'][$weapon_id]['targeting_type'] == 'allOthers') && $owner_username == $target_username)
            {
                throw new exception('bad target: must target other, iid:'.$this->users[$owner_username]['equipment'][$weapon_id]['iid'].' user:'.$owner_username);
            }
            else if ( ($this->users[$owner_username]['equipment'][$weapon_id]['targeting_type'] == 'opponent' || $this->users[$owner_username]['equipment'][$weapon_id]['targeting_type'] == 'rivalTeams') && $this->users[$owner_username]['team'] == $this->users[$target_username]['team'])
            {
                throw new exception('bad target: must target opponent, iid:'.$this->users[$owner_username]['equipment'][$weapon_id]['iid'].' user:'.$owner_username);
            }
            else if ($this->users[$owner_username]['equipment'][$weapon_id]['targeting_type'] == 'ally' && ($this->users[$owner_username]['team'] != $this->users[$target_username]['team'] || $owner_username == $target_username))
            {
                throw new exception('bad target: must target ally, iid:'.$this->users[$owner_username]['equipment'][$weapon_id]['iid'].' user:'.$owner_username);
            }
            else if ($this->users[$owner_username]['equipment'][$weapon_id]['targeting_type'] == 'ally_and_self' && $this->users[$owner_username]['team'] != $this->users[$target_username]['team'])
            {
                throw new exception('bad target: must target ally, iid:'.$this->users[$owner_username]['equipment'][$weapon_id]['iid'].' user:'.$owner_username);
            }

            //pulling weapon and user data
            $weapon = $this->users[$owner_username]['equipment'][$weapon_id];
            $owner = $this->users[$owner_username];

            //check to make sure that em is good
            if( $weapon[parent::ELEMENT] == '' || $weapon[parent::ELEMENT] == 'none' || $weapon[parent::ELEMENT] == 'None' ||
                ( in_array($weapon[parent::ELEMENT], $owner[parent::ELEMENTS]) && $owner[parent::ELEMENTMASTERIES][ array_search( $weapon[parent::ELEMENT], $owner[parent::ELEMENTS] ) ] >25 ))
            {
                //apply tags durability is done inside of this
                $this->addTags($this->parseTags($weapon['on_use_tags']), $target_username, $owner_username, parent::WEAPON, $weapon_id, false, $weapon['targeting_type'], false, $weapon_id);
                $this->users[$owner_username]['equipment_used'][ $this->users[$owner_username]['equipment'][$weapon_id]['iid'] ]['uses']++;

                //recording priority for turn order display
                $this->priority[$owner_username] = $weapon['priority'];

                //recording event
                //$this->recordEvent($owner_username, 'item_used', $this->users[$owner_username]['equipment'][$weapon_id]['iid'], $this->users[$owner_username]['equipment_used'][ $this->users[$owner_username]['equipment'][$weapon_id]['iid'] ]['uses']);
                $GLOBALS['Events']->acceptEvent('item_used', array('context'=>$this->users[$owner_username]['equipment'][$weapon_id]['iid'], 'old'=>$this->users[$owner_username]['equipment'][$weapon_id]['starting_times_used'], 'new'=>$this->users[$owner_username]['equipment_used'][ $this->users[$owner_username]['equipment'][$weapon_id]['iid'] ]['uses']));
            }
        }
    }


    //useItem
    //this method is called when a user uses a item in combat.
    //this method simply applys the items tags to the target and
    //then calls itemConsumption to handle that.
    public function useItem( $invin_id, $owner_username, $target_username )
    {
        //check to make sure that origin user has that weapon.
        if( isset( $this->users[$owner_username]['items'][$invin_id]) && $this->users[$owner_username]['items'][$invin_id]['stack'] > 0 && ($this->users[$owner_username]['items_used'][ $this->users[$owner_username]['items'][$invin_id]['iid'] ] < $this->users[$owner_username]['items'][$invin_id]['max_uses'] || $this->users[$owner_username]['items'][$invin_id]['max_uses'] < 0))
        {
            //checking target
            if($this->users[$owner_username]['items'][$invin_id]['targeting_type'] == 'self' && $owner_username != $target_username)
            {
                throw new exception('bad target: must target self, iid:'.$this->users[$owner_username]['items'][$invin_id]['iid'].' user:'.$owner_username);
            }
            else if ( ($this->users[$owner_username]['items'][$invin_id]['targeting_type'] == 'other' || $this->users[$owner_username]['items'][$invin_id]['targeting_type'] == 'allOthers') && $owner_username == $target_username)
            {
                throw new exception('bad target: must target other, iid:'.$this->users[$owner_username]['items'][$invin_id]['iid'].' user:'.$owner_username);
            }
            else if ( ($this->users[$owner_username]['items'][$invin_id]['targeting_type'] == 'opponent' || $this->users[$owner_username]['items'][$invin_id]['targeting_type'] == 'rivalTeams') && $this->users[$owner_username]['team'] == $this->users[$target_username]['team'])
            {
                throw new exception('bad target: must target opponent, iid:'.$this->users[$owner_username]['items'][$invin_id]['iid'].' user:'.$owner_username);
            }
            else if ($this->users[$owner_username]['items'][$invin_id]['targeting_type'] == 'ally' && ($this->users[$owner_username]['team'] != $this->users[$target_username]['team'] || $owner_username == $target_username))
            {
                throw new exception('bad target: must target ally, iid:'.$this->users[$owner_username]['items'][$invin_id]['iid'].' user:'.$owner_username);
            }
            else if ($this->users[$owner_username]['items'][$invin_id]['targeting_type'] == 'ally_and_self' && $this->users[$owner_username]['team'] != $this->users[$target_username]['team'])
            {
                throw new exception('bad target: must target ally, iid:'.$this->users[$owner_username]['items'][$invin_id]['iid'].' user:'.$owner_username);
            }

            //updating times used and marking at a used item
            $this->users[$owner_username]['items'][$invin_id]['times_used']++;
            $this->users[$owner_username]['items_used'][ $this->users[$owner_username]['items'][$invin_id]['iid'] ]++;

            if(!isset($this->users[$owner_username]['update']['times_used']))
                $this->users[$owner_username]['update']['times_used'] = array();

            if(isset($this->users[$owner_username]['update']['times_used'][$invin_id]))
                $this->users[$owner_username]['update']['times_used'][$invin_id]++;
            else
                $this->users[$owner_username]['update']['times_used'][$invin_id] = 1;

            //getting item data.
            $item = $this->users[$owner_username]['items'][$invin_id];

            //adding tags to the system from the item.
            $this->addTags($this->parseTags($item['on_use_tags']), $target_username, $owner_username, parent::ITEM, $invin_id, false, $item['targeting_type']);

            //recording priority for turn order display
            $this->priority[$owner_username] = $item['priority'];

            $GLOBALS['Events']->acceptEvent('item_used', array('context'=>$item['iid'], 'old'=>$this->users[$owner_username]['items'][$invin_id]['starting_times_used'], 'new'=>$this->users[$owner_username]['update']['times_used'][$invin_id] ));

            //marks item as used
            //actuall db consumption does not occur until the battle is over.
            $this->itemConsumption( $invin_id, $owner_username );

            //$this->recordEvent($owner_username, 'item_used', $item['iid'], $this->users[$owner_username]['update']['times_used'][$invin_id], $this->users[$owner_username]['items'][$invin_id]['starting_times_used']);
        }
    }

    //itemConsumption
    //this method is called by useItem.
    //this method handles the consumption of items directly used in combat.
    public function itemConsumption( $invin_id, $owner_username )
    {
        if($this->users[$owner_username]['items'][$invin_id]['times_used'] >= $this->users[$owner_username]['items'][$invin_id]['uses'])
        {
            $this->users[$owner_username]['items'][$invin_id]['times_used'] = 0;
            $this->users[$owner_username]['items'][$invin_id]['stack']--;

            //marking item for removal from user at end of battle
            if(!isset($this->users[$owner_username]['update']['stack']))
                $this->users[$owner_username]['update']['stack'] = array();

            $this->users[$owner_username]['update']['stack'][$invin_id] = true;
        }

        if($this->users[$owner_username]['items'][$invin_id]['stack'] == 0)
        {
            $iid = $this->users[$owner_username]['items'][$invin_id]['iid'];
            unset($this->users[$owner_username]['items'][$invin_id]);

            //marking item for removal from user at end of battle
            if(!isset($this->users[$owner_username]['update']['remove']))
                $this->users[$owner_username]['update']['remove'] = array();

            if(!isset($this->users[$owner_username]['update']['remove_iid']))
                $this->users[$owner_username]['update']['remove_iid'] = array();

            $this->users[$owner_username]['update']['remove'][$invin_id] = true;
            $this->users[$owner_username]['update']['remove_iid'][$iid] = $iid;

            if(isset($this->users[$owner_username]['update']['stack'][$invin_id]))
                unset($this->users[$owner_username]['update']['stack'][$invin_id]);

            if(isset($this->users[$owner_username]['update']['times_used'][$invin_id]))
                unset($this->users[$owner_username]['update']['times_used'][$invin_id]);
        }
    }

    //doJutsu
    //this method is called when a user uses a jutsu.
    //this method processes the jutsu as needed and makes it happen.
    //weapon_ids are the users_inventory id's
    public function doJutsu($target_username, $owner_username, $jutsu_id, $weapon_ids = false, $respondent = false)
    {
        if(!isset($this->jutsus[$jutsu_id]['weapons']) || $this->jutsus[$jutsu_id]['weapons'] == '')
            $weapon_ids = false;

     // * check isset jutsu and checking pool cost
        if( isset( $this->users[$owner_username]['jutsus'][$jutsu_id] ) && ($this->users[$owner_username]['jutsus'][$jutsu_id]['uses'] < $this->users[$owner_username]['jutsus'][$jutsu_id]['max_uses'] || $this->users[$owner_username]['jutsus'][$jutsu_id]['max_uses'] < 0) &&
            $this->users[$owner_username][parent::HEALTH]  >= $this->users[$owner_username]['jutsus'][$jutsu_id]['hea_cost'] &&
            $this->users[$owner_username][parent::STAMINA] >= $this->users[$owner_username]['jutsus'][$jutsu_id]['sta_cost'] &&
            $this->users[$owner_username][parent::CHAKRA]  >= $this->users[$owner_username]['jutsus'][$jutsu_id]['cha_cost'])
        {
            //checking target
            if($this->jutsus[$jutsu_id]['targeting_type'] == 'self' && $owner_username != $target_username)
            {
                throw new exception('bad target: must target self, jid:'.$jutsu_id.' user:'.$owner_username);
            }
            else if ( ($this->jutsus[$jutsu_id]['targeting_type'] == 'other' || $this->jutsus[$jutsu_id]['targeting_type'] == 'allOthers') && $owner_username == $target_username)
            {
                throw new exception('bad target: must target other, jid:'.$jutsu_id.' user:'.$owner_username);
            }
            else if ( ($this->jutsus[$jutsu_id]['targeting_type'] == 'opponent' || $this->jutsus[$jutsu_id]['targeting_type'] == 'rivalTeams') && $this->users[$owner_username]['team'] == $this->users[$target_username]['team'])
            {
                throw new exception('bad target: must target opponent, jid:'.$jutsu_id.' user:'.$owner_username);
            }
            else if ($this->jutsus[$jutsu_id]['targeting_type'] == 'ally' && ($this->users[$owner_username]['team'] != $this->users[$target_username]['team'] || $owner_username == $target_username))
            {
                throw new exception('bad target: must target ally, jid:'.$jutsu_id.' user:'.$owner_username);
            }
            else if ($this->jutsus[$jutsu_id]['targeting_type'] == 'ally_and_self' && $this->users[$owner_username]['team'] != $this->users[$target_username]['team'])
            {
                throw new exception('bad target: must target ally, jid:'.$jutsu_id.' user:'.$owner_username);
            }


     //     *
     //     * check isset weapons
            if( !is_array($weapon_ids) && $weapon_ids !== false )
                $weapon_ids = array($weapon_ids);

            //flag for processing weapons.
            $weapon_error = false;

            //making sure that weapons exist
            if($weapon_ids !== false && count($weapon_ids) != 0)
            {
                //for each weapon_id
                foreach( $weapon_ids as $id )
                {
                    //making sure the user has the item
                    if(!isset($this->users[$owner_username][parent::EQUIPMENT][$id]) && $this->users[$owner_username][parent::EQUIPMENT][$id]['durability'] > 0)
                    {
                        //if the user does not have an item mark the error.
                        $weapon_error = true;

                        //if debugging record error message
                        if($this->debugging)
                            $GLOBALS['DebugTool']->push($weapon_ids,'missing weapon: ', __METHOD__, __FILE__, __LINE__);
                    }

                    // if the weapon is at max_uses
                    else if( $this->users[$owner_username]['equipment_used'][ $this->users[$owner_username][parent::EQUIPMENT][$id]['iid'] ]['uses'] >= $this->users[$owner_username]['equipment_used'][ $this->users[$owner_username][parent::EQUIPMENT][$id]['iid'] ]['max_uses'] && $this->users[$owner_username]['equipment_used'][ $this->users[$owner_username][parent::EQUIPMENT][$id]['iid'] ]['max_uses'] > 0)
                    {
                        //mark the error
                        $weapon_error = true;

                        //if debugging show the error
                        if($this->debugging)
                            $GLOBALS['DebugTool']->push($weapon_ids,'no uses left', __METHOD__, __FILE__, __LINE__);
                    }
                }
            }

            //if there has not been an error yet
            if( $weapon_error !== true )
            {
     //         *
     //         * check for split jutsu and process if so
                if($this->jutsus[$jutsu_id]['splitJutsu'] == 'yes')
                {




                    //getting cooldown_pool_set
                    if(is_array($this->jutsus[$jutsu_id]['cooldown_pool_set']) && isset($this->jutsus[$jutsu_id]['cooldown_pool_set'][$this->users[$owner_username][parent::SPECIALIZATION]]))
                        $cooldown_pool_set = $this->jutsus[$jutsu_id]['cooldown_pool_set'][$this->users[$owner_username][parent::SPECIALIZATION]];
                    else
                        $cooldown_pool_set = $this->jutsus[$jutsu_id]['cooldown_pool_set'];

                    //getting cooldown_pool_check
                    if(is_array($this->jutsus[$jutsu_id]['cooldown_pool_check']) && isset($this->jutsus[$jutsu_id]['cooldown_pool_check'][$this->users[$owner_username][parent::SPECIALIZATION]]))
                        $cooldown_pool_check = $this->jutsus[$jutsu_id]['cooldown_pool_check'][$this->users[$owner_username][parent::SPECIALIZATION]];
                    else
                        $cooldown_pool_check = $this->jutsus[$jutsu_id]['cooldown_pool_check'];

                    //getting reagent
                    if(is_array($this->jutsus[$jutsu_id]['reagents']) && isset($this->jutsus[$jutsu_id]['reagents'][$this->users[$owner_username][parent::SPECIALIZATION]]))
                        $reagents = $this->jutsus[$jutsu_id]['reagents'][$this->users[$owner_username][parent::SPECIALIZATION]];
                    else
                        $reagents = $this->jutsus[$jutsu_id]['reagents'];


                }

                //setting variables for when this is not a split jutsu
                else
                {
                    $cooldown_pool_set = $this->jutsus[$jutsu_id]['cooldown_pool_set'];
                    $cooldown_pool_check = $this->jutsus[$jutsu_id]['cooldown_pool_check'];
                    $reagents = $this->jutsus[$jutsu_id]['reagents'];
                }

     //         *
     //         * check cool down
                if( $this->checkJutsuCooldown($jutsu_id, $cooldown_pool_check, $owner_username) == false )
                {
     //             *
     //             * check for reagants
                    $result_and_names = $this->checkAndConsumeReagents($owner_username, $reagents);

                    if ( is_array($result_and_names) || $result_and_names === true)
                    {
     //                 *
     //                 * check for weapons

                        $weapon_power = 0; //used to see how much power should be added to the damaging tags value

                        $weapon_tags = array(); //array to hold the different groupings of weapon tags.

                        //if there are weapons
                        if($weapon_ids !== false)
                        {

                            //checking for cheating.
                            //making sure that the jutsu does require a weapon.
                            $temp = '';
                            if(is_array($this->jutsus[$jutsu_id]['weapons']))
                            {
                                if($this->users[$owner_username][parent::SPECIALIZATION] == "W")
                                    $this->users[$owner_username][parent::SPECIALIZATION] = 'B';
                                $weapons = array_flip($this->jutsus[$jutsu_id]['weapons'])[$this->users[$owner_username][parent::SPECIALIZATION]];
                            }
                            else
                                $weapons = $this->jutsus[$jutsu_id]['weapons'];

                            if($weapons === false || $temp == '')
                                $weapon_error = true;
                            else
                            {
                                //for each weapon
                                foreach( $weapons as $weapon_group_key => $required_weapon_group )
                                {
                                    //flag used for checking validity of weapons
                                    $found = false;

                                    //for each weapon
                                    foreach( $weapon_ids as $equipment_id )
                                    {
                                        //getting weapon data
                                        $equipment_data = $this->users[$owner_username]['equipment'][$equipment_id];

                                        //breaking out weapon types
                                        $weapon_types = explode(',', $equipment_data['weapon_classifications']);

                                        //check for error from previous run of the loop to save processing time.
                                        if($weapon_error === false)
                                        {
                                            //checking if this is asking for a specific item
                                            if( is_numeric($required_weapon_group) )
                                            {
                                                //if so check item iid
                                                if($required_weapon_group == $equipment_data['iid'])
                                                    $found = true;
                                            }

                                            //if not wanting a specific item
                                            else
                                            {
                                                //checking types to see if this weapon has one of the required types.
                                                $did_not_find = false;
                                                foreach( explode('/',$required_weapon_group) as $required_type )
                                                    if(!in_array($required_type, $weapon_types))
                                                        $did_not_find = true;


                                                //if a match was found mark found true
                                                if(!$did_not_find)
                                                    $found = true;
                                            }
                                        }

                                        //if there still hasnt not been an issue
                                        //checking element type
                                        if($weapon_error === false)
                                        {
                                            //getting elements for jutsu and weapon
                                            $jutsu_element = '';
                                            if(is_array($this->jutsus[$jutsu_id]['element']))
                                                $jutsu_element = array_flip($this->jutsus[$jutsu_id]['element'])[$this->users[$owner_username][parent::SPECIALIZATION]];
                                            else
                                                $jutsu_element = $this->jutsus[$jutsu_id]['element'];

                                            $weapon_element = $equipment_data['element'];

                                            //if weapon element is empty assume element none.
                                            if( $weapon_element === '' )
                                                $weapon_element = 'None';

                                            //check to make sure that you are past the em thresh hold to allow wlemental weapons.
                                            if( $jutsu_element != 'None' && $weapon_element != 'None' && $weapon_element != 'none' && $weapon_element != 'N' && ! $this->users[$owner_username]['jutsus'][$jutsu_id]['allow_elemental_weapons'] )
                                                $weapon_error = true;//flag that there was an error with this weapon

                                            //if elements do not match...
                                            if($jutsu_element != $weapon_element && $weapon_element != 'None' && $weapon_element != 'none' && $weapon_element != 'N')
                                                //if jutsu_element is not a key in element heritage aka it is a base element...
                                                if(!isset($this->ELEMENTHERITAGE[$jutsu_element]))
                                                    $weapon_error = true;//flag that there was an error with the weapon.

                                                //otherwise check to make sure that the jutsu's element is a child of the weapon's elements...
                                                else if(!in_array($weapon_element, $this->ELEMENTHERITAGE[$jutsu_element]))
                                                    $weapon_error = true;//if the jutsu's element is not a child of the weapon's element flag that there was an error with the weapon.
                                        }
                                    }

                                    //if a weapon was not found mark the error flag.
                                    if(!$found)
                                        $weapon_error = true;

                                }
                            }

                            //if there is no issue with the weapon types
                            if($weapon_error !== true)
                            {
                                //for each weapon
                                $weapon_count = count($weapon_ids);
                                foreach($weapon_ids as $weapon_inventory_id)
                                {
                                    //add weapon power as an argument to be passed to doDamage
                                    if($weapon_count == 1)
                                        $weapon_power += $this->users[$owner_username][self::EQUIPMENT][$weapon_inventory_id]['strength'];
                                    else if($weapon_count == 2)
                                        $weapon_power += $this->users[$owner_username][self::EQUIPMENT][$weapon_inventory_id]['strength'] * 0.6;
                                    else
                                        $weapon_power += $this->users[$owner_username][self::EQUIPMENT][$weapon_inventory_id]['strength'] * (1/$weapon_count + 0.05 );

                                    $weapon_power /= 2;

                                    //record weapon tags
                                    $weapon_tags[$weapon_inventory_id] = $this->users[$owner_username][self::EQUIPMENT][$weapon_inventory_id]['on_jutsu_tags'];

                                    $this->users[$owner_username]['equipment_used'][ $this->users[$owner_username][parent::EQUIPMENT][$weapon_inventory_id]['iid'] ]['uses']++;
                                }
                            }
                        }

                        //getting jutsu name
                        $jutsu_name = '';
                        if(is_array($this->jutsus[$jutsu_id]['name']))
                            $jutsu_name = array_flip($this->jutsus[$jutsu_id]['name'])[$this->users[$owner_username][parent::SPECIALIZATION]];
                        else
                            $jutsu_name = $this->jutsus[$jutsu_id]['name'];

                        //mark_action
                        if($respondent === true)
                            $this->recordAction( $owner_username, $target_username, 'respondent', $jutsu_id, $jutsu_name, $weapon_ids, $result_and_names);
                        else
                            $this->recordAction( $owner_username, $target_username, 'jutsu', $jutsu_id, $jutsu_name, $weapon_ids, $result_and_names);

                        //update pools and uses counter
                        $this->users[$owner_username][parent::HEALTH]  -= $this->users[$owner_username]['jutsus'][$jutsu_id]['hea_cost'];
                        $this->users[$owner_username][parent::STAMINA] -= $this->users[$owner_username]['jutsus'][$jutsu_id]['sta_cost'];
                        $this->users[$owner_username][parent::CHAKRA]  -= $this->users[$owner_username]['jutsus'][$jutsu_id]['cha_cost'];
                        $this->users[$owner_username]['jutsus'][$jutsu_id]['uses']++;

                        //making sure that no pools stay below zero
                        if($this->users[$owner_username][parent::HEALTH] < 1)
                            $this->users[$owner_username][parent::HEALTH] = 1;

                        if($this->users[$owner_username][parent::STAMINA] < 0)
                            $this->users[$owner_username][parent::STAMINA] = 0;

                        if($this->users[$owner_username][parent::CHAKRA] < 0)
                            $this->users[$owner_username][parent::CHAKRA] = 0;

                        //update cooldowns.
                        $this->setJutsuCooldown($jutsu_id, $cooldown_pool_set, $owner_username);

                        //getting tags
                        $temp_tags='';
                        if(is_array($this->jutsus[$jutsu_id]['tags']))
                        {
                            if($this->users[$owner_username][parent::SPECIALIZATION] == 'W')
                                $this->users[$owner_username][parent::SPECIALIZATION] = 'B';

                            $temp_tags = array_flip($this->jutsus[$jutsu_id]['tags'])[$this->users[$owner_username][parent::SPECIALIZATION]];
                        }
                        else
                            $temp_tags = $this->jutsus[$jutsu_id]['tags'];

                        if(is_array($temp_tags))
                        {
                            ob_start();
                            var_dump($temp_tags);
                            $result = ob_get_clean();
                            error_log('temp tags: '.$result);
                            error_log('');
                            ob_start();
                            var_dump($temp_tags);
                            $result = ob_get_clean();
                            error_log('before specialization"'.$this->users[$owner_username][parent::SPECIALIZATION].'": '.array_flip($this->jutsus[$jutsu_id]['tags']));
                        }

                        //adding tags to the system
                        if($jutsu_id == -1)
                            $origin = parent::DEFAULT_ATTACK;
                        else
                            $origin = parent::JUTSU;

                        $this->addTags($this->parseTags($temp_tags), $target_username, $owner_username, $origin, false, $this->users[$owner_username]['jutsus'][$jutsu_id]['level'], $this->jutsus[$jutsu_id]['targeting_type'], $weapon_power, $weapon_ids);

                        //recording priority for turn order display
                        $this->priority[$owner_username] = $this->jutsus[$jutsu_id]['priority'];

                        //if there are weapon tags
                        if(count($weapon_tags) != 0)
                            //for each set of weapon tags
                            foreach($weapon_tags as $id => $tags)
                            {
                                //get targeting type
                                $temp_targeting_type = '';
                                if(is_array($this->jutsus[$jutsu_id]['targeting_type']))
                                    $temp_targeting_type = array_flip($this->jutsus[$jutsu_id]['targeting_type'])[$this->users[$owner_username][parent::SPECIALIZATION]];
                                else
                                    $temp_targeting_type = $this->jutsus[$jutsu_id]['targeting_type'];

                                //add weapon tags to system
                                $this->addTags($this->parseTags($tags), $target_username, $owner_username, parent::WEAPON, $id, false, $temp_targeting_type);
                            }

                        //trigger weapon use event
                        //error_log(print_r($weapon_ids,true));
                        if(is_array($weapon_ids))
                        {
                            foreach($weapon_ids as $weapon_id)
                            {
                                $GLOBALS['Events']->acceptEvent('item_used', array('context'=>$this->users[$owner_username]['equipment'][$weapon_id]['iid'], 'old'=>$this->users[$owner_username]['equipment'][$weapon_id]['starting_times_used'], 'new'=>$this->users[$owner_username]['equipment_used'][ $this->users[$owner_username][parent::EQUIPMENT][$weapon_id]['iid'] ]['uses']));
                            }
                        }


                        //award exp for jutsu use
                        $this->awardJutsuExp($jutsu_id, $owner_username, $target_username);

                        if(!isset($this->balanceFlag))
                            $this->balanceFlag = false;

                        //record jutsu use for balance tracking
                        if($this->balanceFlag === true && $jutsu_id != -1)
                        {
                            if($this->jutsus[$jutsu_id]['splitJutsu'] != 'yes')
                                if(!isset($this->balance[$owner_username][$jutsu_id]))
                                    $this->balance[$owner_username][$jutsu_id] = 1;
                                else
                                    $this->balance[$owner_username][$jutsu_id]++;
                            else
                            {
                                if(!isset($this->balance[$owner_username][$jutsu_id]))
                                    $this->balance[$owner_username][$jutsu_id] = array();

                                if(!isset($this->balance[$owner_username][$jutsu_id][$this->users[$owner_username][parent::SPECIALIZATION]]))
                                    $this->balance[$owner_username][$jutsu_id][$this->users[$owner_username][parent::SPECIALIZATION]] = 1;
                                else
                                    $this->balance[$owner_username][$jutsu_id][$this->users[$owner_username][parent::SPECIALIZATION]]++;
                            }
                        }
                    }
                }
            }
        }
    }


    //grants exp to jutsu when a jutsu is used.
    //should be overridden by most children.
    //500/level for training
    //1000/level for ai battles
    //2000/level for pvp
    //we are treating this like pvp
    public function awardJutsuExp($jutsu_id, $username, $target)
    {
        //marking the jutsu for update
        if(!isset($this->users[$username]['update']['jutsus']))
            $this->users[$username]['update']['jutsus'] = array();

        if(!isset($this->users[$username]['update']['jutsus'][$jutsu_id]))
            $this->users[$username]['update']['jutsus'][$jutsu_id] = true;

        //counting this use of the jutsu
        if(!isset($this->users[$username]['jutsus'][$jutsu_id]['times_used']))
            $this->users[$username]['jutsus'][$jutsu_id]['times_used'] = 1;
        else
            $this->users[$username]['jutsus'][$jutsu_id]['times_used']++;

        //if the user is not already at their cap for the jutsu
        if($this->users[$username]['jutsus'][$jutsu_id]['level'] < $this->jutsus[$jutsu_id]['max_level'])
        {
            //get jutsu type
            $type = $this->jutsus[$jutsu_id]['jutsu_type'];

            //set the different exp requirements for the different jutsu types.
            $exp_per_level = array( 'normal'=>1000,
                                    'event'=>1000,
                                    'bloodline'=>1500,
                                    'clan'=>1500,
                                    'special'=>2000,
                                    'loyalty'=>2000,
                                    'village'=>3000,
                                    'forbidden'=>5000,
                                    'kage'=>5000);

            $mod = 1;
            if( in_array($this->users[$username]['location'], array_keys($this->village_locations ) ) || strpos($this->users[$username]['location'], 'Outskirts') !== false ){
                $event = functions::getGlobalEvent("DoubleJutsuExperience");

                if( isset( $event['data']) && is_numeric( $event['data']) ){
                    $mod = round($event['data'] / 100,2);
                }
            }

            //if the target is not an ai
            if(!isset($this->users[$target]['ai']) || !$this->users[$target]['ai'])
            {
                $this->users[$username]['jutsus'][$jutsu_id]['exp'] += round((2000/($this->users[$username]['jutsus'][$jutsu_id]['level']+1)) * $mod);

                if( !isset($this->users[$username]['update']['jutsus'][$jutsu_id]) || $this->users[$username]['update']['jutsus'][$jutsu_id] !== 'leveled')
                {
                    if(!isset($this->users[$username]['update']['jutsus'][$jutsu_id]) || $this->users[$username]['update']['jutsus'][$jutsu_id] === true)
                        $this->users[$username]['update']['jutsus'][$jutsu_id] = round((2000/($this->users[$username]['jutsus'][$jutsu_id]['level']+1)) * $mod);
                    else
                        $this->users[$username]['update']['jutsus'][$jutsu_id] += round((2000/($this->users[$username]['jutsus'][$jutsu_id]['level']+1)) * $mod);
                }
            }

            //if the target is an ai
            else
            {
                $this->users[$username]['jutsus'][$jutsu_id]['exp'] += round((2000/($this->users[$username]['jutsus'][$jutsu_id]['level']+1)) / 4);

                if( !isset($this->users[$username]['update']['jutsus'][$jutsu_id]) || $this->users[$username]['update']['jutsus'][$jutsu_id] !== 'leveled')
                {
                    if(!isset($this->users[$username]['update']['jutsus'][$jutsu_id])  || $this->users[$username]['update']['jutsus'][$jutsu_id] === true)
                        $this->users[$username]['update']['jutsus'][$jutsu_id] = round((2000/($this->users[$username]['jutsus'][$jutsu_id]['level']+1)) / 4);
                    else
                        $this->users[$username]['update']['jutsus'][$jutsu_id] += round((2000/($this->users[$username]['jutsus'][$jutsu_id]['level']+1)) / 4);
                }
            }

            //check if the justu has leveled
            if( $this->users[$username]['jutsus'][$jutsu_id]['exp'] >= $exp_per_level[$type] )
            {
                $this->users[$username]['jutsus'][$jutsu_id]['exp'] = 0;
                $this->users[$username]['jutsus'][$jutsu_id]['level']++;

                $this->users[$username]['update']['jutsus'][$jutsu_id] = 'leveled';
            }
        }
    }

    //check for existance of reagents
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //this needs to be changed to not consume from db untill end of battle
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function checkAndConsumeReagents($username, $reagents, $consume = true)
    {
        //make sure there are reagents
        if($reagents != '')
        {
            //if the passed in reagents are an array
            if(is_array($reagents))
            {
                if(count($reagents) === 0)
                    return true;

                //select to get relevant user invin
                $select_statement = 'SELECT `users_inventory`.`id`, `users_inventory`.`iid`, `users_inventory`.`stack`, `items`.`name` FROM users_inventory inner join `items` on (`items`.`id` = `users_inventory`.`iid`) WHERE uid = '.$this->users[$username]['uid'].' and iid in ('.implode(',',array_keys($reagents)).')';
                $query = '';
                //nested try catch for database query
                try { if(! $query = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception('query failed'); }
                catch (Exception $e)
                {
                    try { if(! $query = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed'); }
                    catch (Exception $e)
                    {
                        try { if(! $query = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed to update user information'); }
                        catch (Exception $e)
                        {
                            $GLOBALS['DebugTool']->push('','there was an error getting reagent information.', __METHOD__, __FILE__, __LINE__);
                            throw $e;
                        }
                    }
                }
                if(!is_array($query))
                    return false;
                //initializing variables the are used in the forloop
                $results = array();
                $return_flags = array();
                $names = array();
                //for each row of user inventory
                foreach($query as $result)
                {
                    //add up the item count.
                    if(isset($results[$result['iid']]))
                        $results[$result['iid']] += $result['stack'];
                    else
                        $results[$result['iid']]  = $result['stack'];
                    //check the item count against the requirement.
                    if($results[$result['iid']] < $reagents[$result['iid']])
                        $return_flags[$result['iid']] = false;
                    else
                        $return_flags[$result['iid']] = true;

                    $names[] = $result['name'];
                }
                //check the results of the above for each loop for failures.
                $return_flag = true;
                foreach($return_flags as $flag)
                    if($flag === false)
                        $return_flag = false;
                //if there was not enough of 1 of the items
                //do not consume anything
                if($return_flag === true && $consume)
                {
                    try
                    {
                        $GLOBALS['database']->transaction_start();
                        $for_delete = array();
                        $for_update = array();
                        foreach($reagents as $reagent_id => $reagent_count)
                        {
                            $thus_far_counter = 0;
                            foreach($query as $inventory_row)
                            {
                                if($inventory_row['iid'] == $reagent_id)
                                {
                                    if( $inventory_row['stack'] + $thus_far_counter <= $reagent_count)
                                    {
                                        $thus_far_counter += $inventory_row['stack'];
                                        $for_delete[] = $inventory_row['id'];

                                        //$this->recordEvent($owner_username, 'item_person', '!'.$inventory_row['iid']);
                                        
                                        $stack = 0;
                                        $quantity = 0;
                                        $quantity_removed = 0;
                                        foreach($query as $row)
                                        {
                                            if(isset($row['iid']) && $row['iid'] == $inventory_row['iid'])
                                            {
                                                $quantity += $row['stack'];
                                                $stack++;

                                                if($row['id'] == $inventory_row['id'])
                                                    $quantity_removed = $row['stack'];
                                            }
                                        }

                                        $GLOBALS['Events']->acceptEvent('item_person', array('data'=>'!'.$inventory_row['iid'], 'context'=>$inventory_row['iid'], 'new'=>$stack-1, 'old'=>$stack ) );
                                        $GLOBALS['Events']->acceptEvent('item_quantity_loss', array('context'=>$inventory_row['iid'], 'new'=>$quantity-$quantity_removed, 'old'=>$quantity));
                                    }
                                    else if ($inventory_row['stack'] + $thus_far_counter > $reagent_count && $thus_far_counter != $reagent_count)
                                    {
                                        $for_update[$inventory_row['id']] = $reagent_count - $thus_far_counter;
                                        $thus_far_counter = $reagent_count;
                                    }
                                }
                            }
                        }

                        if(count($for_delete) > 0)
                            $delete_query = 'DELETE FROM `users_inventory` WHERE `id` IN ('.implode(',',$for_delete).')';
                        else
                            $delete_query = false;

                        if(count($for_update) > 0)
                        {
                            $update_query = 'UPDATE `users_inventory` SET `stack` = CASE ';
                            foreach($for_update as $id => $reduction)
                            {
                                $update_query .= 'WHEN id = '.$id.' THEN `stack` - '.$reduction.' ';
                            }
                            $update_query .= 'END WHERE `id` IN ('.implode(',',array_keys($for_update)).')';
                        }
                        else
                            $update_query = false;

                        if($delete_query !== false)
                        {
                            try { if(!$GLOBALS['database']->execute_query($delete_query)) throw new Exception('query failed'); }
                            catch (Exception $e)
                            {
                                try { if(!$GLOBALS['database']->execute_query($delete_query)) throw new Exception ('query failed'); }
                                catch (Exception $e)
                                {
                                    try { if(!$GLOBALS['database']->execute_query($delete_query)) throw new Exception ('query failed to update user information'); }
                                    catch (Exception $e)
                                    {
                                        $GLOBALS['DebugTool']->push($delete_query,'there was an error deleting items.', __METHOD__, __FILE__, __LINE__);
                                        throw $e;
                                    }
                                }
                            }
                        }

                        if($update_query !== false)
                        {
                            try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception('query failed'); }
                            catch (Exception $e)
                            {
                                try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception ('query failed'); }
                                catch (Exception $e)
                                {
                                    try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception ('query failed to update user information'); }
                                    catch (Exception $e)
                                    {
                                        $GLOBALS['DebugTool']->push('','there was an error updating item counts for reagent consumption.', __METHOD__, __FILE__, __LINE__);
                                        throw $e;
                                    }
                                }
                            }
                            $GLOBALS['database']->transaction_commit();
                        }
                    }
                    catch (Exception $e)
                    {
                        $GLOBALS['database']->transaction_rollback();
                        return false;
                    }
                }
                //return the result
                if($return_flag !== false)
                    return $names;
                else
                    return $return_flag;
            }
            else
            {
                if( $this->debugging )
                    $GLOBALS['DebugTool']->push($reagents,'bad reagents data.', __METHOD__, __FILE__, __LINE__);
                return false;
            }
        }
        else
            return true;
    }

    // flee
    //this is called at direct request of the user
    //this adds the flee tag to the user to be processed by the tag system.
    public function tryFlee($username)
    {
        $this->addTags( $this->parseTags('flee:()'), $username, $username, parent::JUTSU);
    }

    //callForHelp
    //this is not implemented yet.
    public function callForHelp($username)
    {
        //check that we can call for help
        if( (!isset($this->users[$username]['no_cfh']) || $this->users[$username]['no_cfh'] !== true) &&
            (!isset($this->users[$username]['respondent']) || $this->users[$username]['respondent'] !== true)
             && $this->no_cfh !== true)
        {


            //getting friendly and non friendly dsrs
            $friendlyDSR = 0;
            $nonFriendlyDSR = 0;

            foreach($this->users as $userdata)
                if($userdata['team'] == $this->users[$username]['team'])
                {
                    $friendlyDSR += $userdata['update']['starting_dsr'];
                }
                else
                    $nonFriendlyDSR += $userdata['update']['starting_dsr'];

            //check that we should call for help
            if( $friendlyDSR < $nonFriendlyDSR && 1 - ($friendlyDSR / $nonFriendlyDSR) > 0.15 )
            {
                //update the database
                //set cfh in users to friendlyDSR,nonFriendlyDSR
                if($this->battle_type != '03')
                    $cfh = 'pvp|';
                else
                    $cfh = 'spar|';

                $cfh .= round($friendlyDSR).'|'.round($nonFriendlyDSR);
                $query = 'UPDATE `users` SET `cfh` = "'.$cfh.'" WHERE `id` = '.$this->users[$username]['uid'];


                try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed'); }
                catch (Exception $e)
                {
                    try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                    catch (Exception $e)
                    {
                        try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed to update user information'); }
                        catch (Exception $e)
                        {
                            $GLOBALS['DebugTool']->push('','there was an error updating call for help information.', __METHOD__, __FILE__, __LINE__);
                            throw $e;
                        }
                    }
                }

                //turn on no call for help
                $this->users[$username]['no_cfh'] = true;
            }
        }

        //allow the opponent to call for help when this call for help is answered?
        //on response change cfh in users to called
    }

    //updateAgeAndCheckDurationAndProcessCooldowns
    //the calling of this signals the end of the turn.
    //this handles cooldowns.
    //age and duration are handled by parent copy of this method.
    public function updateAgeAndCheckDurationAndProcessCooldowns()
    {
        //calling parent copy in Tags
        parent::updateAgeAndCheckDurationAndProcessCooldowns();

        //for each user...
        if(is_array($this->users))
            foreach($this->users as $username => $user_data)
                //for each cool down...
                if(isset($this->users[$username]['jutsus']['cooldowns']) && is_array($this->users[$username]['jutsus']['cooldowns']))
                    foreach( $this->users[$username]['jutsus']['cooldowns'] as $cooldown_key => $cooldown )
                        //if the cool down in greater than zero...
                        if($cooldown > 0)
                            $this->users[$username]['jutsus']['cooldowns'][$cooldown_key]--; //reduce cool down by 1, otherwise...

                        //if the cool down is less than zero...
                        else if ($cooldown < 0)
                            $this->users[$username]['jutsus']['cooldowns'][$cooldown_key] = 0; //set cool down to 0.
    }

    //checkJutsuCooldown
    //takes jutsu id and username
    //returns the category of the highest cool down and its value.
    //in case of a tie for highest cool down it will return tied categorys and its value.
    public function checkJutsuCooldown($jid, $cooldown_pool_check, $username)
    {
        $cooldowns = array();

        //add individual cool down to list of cooldowns
        $cooldowns[$jid] = $this->users[$username]['jutsus']['cooldowns'][$jid];


        //add each pool cool down to list of cooldowns
        $cooldown_pool_check = '';
        if(is_array($this->jutsus[$jid]['cooldown_pool_check']))
            if(isset(array_flip($this->jutsus[$jid]['cooldown_pool_check'])[$this->users[$username][parent::SPECIALIZATION]]))
                $cooldown_pool_check = array_flip($this->jutsus[$jid]['cooldown_pool_check'])[$this->users[$username][parent::SPECIALIZATION]];
            else
                $cooldown_pool_check = $this->jutsus[$jid]['cooldown_pool_check'];
        else
            $cooldown_pool_check = $this->jutsus[$jid]['cooldown_pool_check'];


        foreach($cooldown_pool_check as $cooldown_pool)
            if($cooldown_pool !== '')
                $cooldowns[$cooldown_pool] = $this->users[$username]['jutsus']['cooldowns'][$cooldown_pool];

        $maxs = array_keys($cooldowns, max($cooldowns));

        if($cooldowns[$maxs[0]] != 0)
            return array($maxs[0],$cooldowns[$maxs[0]]);
        else
            return false;
    }


    //setJutsuCooldown
    //takes jutsu id and username
    //sets the cooldown variables of a jutsu
    public function setJutsuCooldown($jid, $cooldown_pool_set, $username)
    {
        $cooldown_type_values = array('normal' => 2,
                                      'bloodline' => 3,
                                      'clan' => 3,
                                      'event' => 3,
                                      'special' => 4,
                                      'loyalty' => 4,
                                      'kage' => 4,
                                      'village' => 5,
                                      'forbidden' => 5);

        //getting the value that the cool downs should be set to.
        $override_cooldown = '';
        if(is_array($this->jutsus[$jid]['override_cooldown']))
            $override_cooldown = array_flip($this->jutsus[$jid]['override_cooldown'])[$this->users[$username][parent::SPECIALIZATION]];
        else
            $override_cooldown = $this->jutsus[$jid]['override_cooldown'];

        if(is_numeric($override_cooldown))
            $cooldown_value = $override_cooldown;
        else
            $cooldown_value = $cooldown_type_values[$this->jutsus[$jid]['jutsu_type']];

        //add individual cool down to list of cooldowns
        $this->users[$username]['jutsus']['cooldowns'][$jid] = $cooldown_value + 1;


        //add each pool cool down to list of cooldowns
        $cooldown_pool_set = '';
        if(is_array($this->jutsus[$jid]['cooldown_pool_set']))
            if(isset(array_flip($this->jutsus[$jid]['cooldown_pool_set'])[$this->users[$username][parent::SPECIALIZATION]]))
                $cooldown_pool_set = array_flip($this->jutsus[$jid]['cooldown_pool_set'])[$this->users[$username][parent::SPECIALIZATION]];
            else
                $cooldown_pool_set = $this->jutsus[$jid]['cooldown_pool_set'];
        else
            $cooldown_pool_set = $this->jutsus[$jid]['cooldown_pool_set'];

        foreach($cooldown_pool_set as $cooldown_pool)
            if($cooldown_pool !== '')
                $this->users[$username]['jutsus']['cooldowns'][$cooldown_pool] = $cooldown_value + 1;
    }

    //updateDurability
    //this is called by the doDamage method of Tags
    function updateDurability($tag, $final_damage_value, $rank)
    {
        $items_to_update = array();

        if(!$this->users[$tag->target]['ai'])
        {
            $durability_damage = $final_damage_value/$this->DURABILITYDAMAGESCALE[$rank];

            if($durability_damage > 9)
                $durability_damage = 9;

            $armor_damage = array();
            $armor_count = 0;
            $armor_application_count = 0;

            foreach($this->users[$tag->target][self::EQUIPMENT] as $item_key => $item)
            {
                if($item['type'] == 'armor')
                {
                    $armor_count++;
                }
            }

            if($armor_count != 0)
            {
                $durability_damage = $durability_damage / $armor_count;

                for($i = 0; $i < floor($armor_count / 2); $i++)
                {
                    $flux = random_int(0, round($durability_damage * 1000000));
                    $flux /= 2000000;

                    $armor_damage[] = $durability_damage + $flux;
                    $armor_damage[] = $durability_damage - $flux;
                }

                if($armor_count % 2 != 0)
                    $armor_damage[] = $durability_damage;

                shuffle($armor_damage);

                foreach($this->users[$tag->target][self::EQUIPMENT] as $item_key => $item)
                {
                //update armor durability
                    if($item['type'] == 'armor' && $item['infinity_durability'] != 1)
                    {
                        //update local information about durability damage

                        $this->users[$tag->target][self::EQUIPMENT][$item_key]['durability'] -= $armor_damage[$armor_application_count];

                        $armor_application_count++;

                        if($this->users[$tag->target][self::EQUIPMENT][$item_key]['durability'] < 0)
                            $this->users[$tag->target][self::EQUIPMENT][$item_key]['durability'] = 0;

                        $items_to_update[$item_key] = $this->users[$tag->target][self::EQUIPMENT][$item_key]['durability'];

                        if($this->users[$tag->target][self::EQUIPMENT][$item_key]['durability'] == 0)
                        {
                            $name = $this->users[$tag->target][self::EQUIPMENT][$item_key]['name'];
                            $iid = $this->users[$tag->target][self::EQUIPMENT][$item_key]['iid'];
                            $this->removeEquipmentById($item_key);

                            //marking item for removal from user at end of battle
                            if(!isset($this->users[$tag->target]['update']['remove']))
                                $this->users[$tag->target]['update']['remove'] = array();

                            if(!isset($this->users[$tag->target]['update']['remove_iid']))
                                $this->users[$tag->target]['update']['remove_iid'] = array();

                            $this->users[$tag->target]['update']['remove'][$item_key] = $name;
                            $this->users[$tag->target]['update']['remove_iid'][$iid] = $iid;

                            if(isset($this->users[$tag->target]['update']['durability'][$item_key]))
                                unset($this->users[$tag->target]['update']['durability'][$item_key]);
                        }
                        else
                        {
                            //marking item for update at end of battle
                            if(!isset($this->users[$tag->target]['update']['durability']))
                                $this->users[$tag->target]['update']['durability'] = array();

                            $this->users[$tag->target]['update']['durability'][$item_key] = true;
                        }
                    }
                }

                //update weapon durability
            // if there was weapons
                if(!is_array($tag->weapon_ids))
                {
                    //record weapon ids in tag for damage
                    $tag->weapon_ids = array($tag->weapon_ids);
                }

                //for each weapon id
                foreach($tag->weapon_ids as $weapon)
                {
                    //if this is a valid weapon and it does not belong to an ai
                    if($weapon != 0 && !$this->users[$tag->owner]['ai'] && isset($this->users[$tag->owner][self::EQUIPMENT][$weapon]) && $this->users[$tag->owner][self::EQUIPMENT][$weapon]['infinity_durability'] != 1)
                    {
                        //update its durability
                        $durability_damage = ($final_damage_value/$this->DURABILITYDAMAGESCALE[$rank])/count($tag->weapon_ids);

                        if($durability_damage > 9)
                            $durability_damage = 9;

                        $this->users[$tag->owner][self::EQUIPMENT][$weapon]['durability'] -= $durability_damage/2;

                        if($this->users[$tag->owner][self::EQUIPMENT][$weapon]['durability'] < 0)
                            $this->users[$tag->owner][self::EQUIPMENT][$weapon]['durability'] = 0;

                        //mark items to update after battle
                            //$items_to_update[$weapon] = $this->users[$tag->owner][self::EQUIPMENT][$weapon]['durability'];
                        if(isset($this->users[$tag->owner][self::EQUIPMENT][$weapon]['times_used']))
                            $this->users[$tag->owner][self::EQUIPMENT][$weapon]['times_used']++;
                        else
                            $this->users[$tag->owner][self::EQUIPMENT][$weapon]['times_used'] = 1;

                        //if weapon is broken remove it from the battle
                        if($this->users[$tag->owner][self::EQUIPMENT][$weapon]['durability'] == 0)
                        {
                            $name = $this->users[$tag->owner][self::EQUIPMENT][$weapon]['name'];
                            $iid = $this->users[$tag->owner][self::EQUIPMENT][$weapon]['iid'];
                            $this->removeEquipmentById($weapon);

                            //marking weapon for removal from user at end of battle
                            if(!isset($this->users[$tag->owner]['update']['remove']))
                                $this->users[$tag->owner]['update']['remove'] = array();

                            if(!isset($this->users[$tag->owner]['update']['remove_iid']))
                                $this->users[$tag->owner]['update']['remove_iid'] = array();

                            $this->users[$tag->owner]['update']['remove'][$weapon] = $name;

                            $this->users[$tag->owner]['update']['remove_iid'][$iid] = $iid;

                            if(isset($this->users[$tag->owner]['update']['durability'][$weapon]))
                                unset($this->users[$tag->owner]['update']['durability'][$weapon]);

                            if(isset($this->users[$tag->owner]['update']['times_used'][$weapon]))
                                unset($this->users[$tag->owner]['update']['times_used'][$weapon]);
                        }
                        else
                        {
                            //marking weapon for update at end of battle.
                            if(!isset($this->users[$tag->owner]['update']))
                                $this->users[$tag->owner]['update'] = array();

                            if(!isset($this->users[$tag->owner]['update']['durability']))
                                $this->users[$tag->owner]['update']['durability'] = array();

                            if(!isset($this->users[$tag->owner]['update']['times_used']))
                                $this->users[$tag->owner]['update']['times_used'] = array();

                            $this->users[$tag->owner]['update']['durability'][$weapon] = true;
                            $this->users[$tag->owner]['update']['times_used'][$weapon] = true;
                        }
                    }
                }
            }
        }
    }

    //this finds the turn order for this turn and returns it.
    //wip
    //using getSpeedRating to determin turn order.
    function getTurnOrder($use_priority = false, $turn_counter = false, $cache_udpate = false)
    {
        if($turn_counter === false)
            $turn_counter = $this->turn_counter;

        //if get turn order has been ran this turn just return the same result
        if(isset($this->turn_order[$turn_counter]))
        {
            return $this->turn_order[$turn_counter];
        }

        $usernames = array_keys($this->users); //turn order defined here

        $ordered_usernames = array();

        foreach($usernames as $username)
        {
            //if(!isset($this->turn_order_rng[$username]))

            //getting rng value for this turn
            $this->turn_order_rng[$username] = random_int(-51,51);

            $sr = round($this->getSpeedRating($username) + $this->turn_order_rng[$username]);

            if($use_priority === true)
            {
                if(isset($this->priority[$username]))
                {
                    if($this->priority[$username] == 1)
                        $sr -= 10000;
                    else if($this->priority[$username] == 3)
                        $sr += 10000;
                }
            }

            while(isset($ordered_usernames[$sr]))
            {
                if($turn_counter % 2 == 0)
                    $sr--;
                else
                    $sr++;
            }

            $ordered_usernames[$sr] = $username;
        }

        krsort($ordered_usernames);

        //saving turn order result for the rest of the turn.
        if(!isset($this->turn_order))
            $this->turn_order = array();

        $this->turn_order[$turn_counter] = $ordered_usernames;
        $this->display_turn_order[$turn_counter] = $ordered_usernames;

        if($cache_udpate === true)
            $this->updateCache();

        return $ordered_usernames;
    }

    //getSpeedRating
    //this method calculates a users speed rating
    function getSpeedRating($username)
    {
        $user_rank = $this->users[$username][parent::RANK];

        $speed_rating = 50 + 12.5 * $user_rank;

        $expertise_boost = (($this->users[$username][parent::EXPERTISE] / $this->EXPERTISECAP[$user_rank]) * 100);

        $armor_hindrance = (($this->users[$username][parent::ARMORBASE] / $this->ARMORCAP[$user_rank]) * 100);

        $speed_rating += $expertise_boost;

        $speed_rating -= $armor_hindrance;

        return round($speed_rating);
    }

    //this method is called to record an action made by a user.
    //the information recorded here will be used later to build the battle log for each turn.
    function recordAction($owning_username, $target_username, $action_type, $action_id, $action_name, $weapon_ids = false, $item_names = false)
    {
        if(!isset($this->users[$owning_username]['actions']))
            $this->users[$owning_username]['actions'] = array();

        $this->users[$owning_username]['actions'][$this->turn_counter] = array( 'target' => $target_username, 'type' => $action_type, 'id' => $action_id, 'name' => $action_name, 'weapon_ids' => $weapon_ids, 'item_names' => $item_names );
    }

    //this method is called to find the first user who has not taken an action yet and
    //this method is aclled to find if all users have taken an action yet.
    function findFirstUser($turn_number, $request_cache_update = false)
    {
        foreach( $this->getTurnOrder(false, false, $request_cache_update) as $username )
        {
            if( isset($this->users[$username]) && ! $this->checkForAction($username, $turn_number) && !$this->users[$username]['ai'])
                return $username;
        }
        return false;
    }

    //this method simply checks to see if a user took an action for a given turn.
    function checkForAction($username, $turn_number)
    {
        return isset($this->users[$username]['actions'][$turn_number]);
    }

    //this method is called after every turn to build the battle log for that turn.
    function processBattleLog( $turn_counter )
    {
        //if the battle log is not set for this turn set it.
        if( ! isset( $this->battle_log[$turn_counter] ) )
            $this->battle_log[$turn_counter] = array();

        //grab the turn order.
        if(isset($this->display_turn_order[$this->turn_counter-1]))
            $turn_order = $this->display_turn_order[$this->turn_counter-1];
        else
            $turn_order = $this->getTurnOrder(true,$this->turn_counter-1);

        $order = count($turn_order);
        foreach( $turn_order as $username )
        {
            //if battle log for user on this turn is not set, set it
            if(! isset($this->battle_log[$turn_counter][$username]))
            {
                if(isset($this->users[$username]['actions'][$turn_counter]))
                    $this->battle_log[$turn_counter][$username] = $this->users[$username]['actions'][$turn_counter];
            }
            //if battle log for user on this turn is set merge it with new data.
            else
                $this->battle_log[$turn_counter][$username] = array_merge($this->battle_log[$turn_counter][$username], $this->users[$username]['actions'][$turn_counter]);

            if(isset($this->battle_log[$turn_counter][$username]) && ($this->battle_log[$turn_counter][$username]['type'] == 'jutsu' || $this->battle_log[$turn_counter][$username]['type'] == 'respondent'))
                $this->battle_log[$turn_counter][$username]['jutsu_description'] = $this->processBattleDescription($this->battle_log[$turn_counter][$username], $username);

            if(isset($this->battle_log[$turn_counter][$username]))
                $this->battle_log[$turn_counter][$username]['team'] = $this->users[$username]['team'];

            $this->battle_log[$turn_counter][$username]['order'] = $order;

            if($order % 10 == 1 && ($order < 10 || $order > 20 ) )
                $this->battle_log[$turn_counter][$username]['order'] .= 'st';
            else if($order % 10 == 2 && ($order < 10 || $order > 20 ) )
                $this->battle_log[$turn_counter][$username]['order'] .= 'nd';
            else if($order % 10 == 3 && ($order < 10 || $order > 20 ) )
                $this->battle_log[$turn_counter][$username]['order'] .= 'rd';
            else
                $this->battle_log[$turn_counter][$username]['order'] .= 'th';
            $order--;

            if(isset($this->battle_log[$turn_counter][$username]['died']))
            {

                if(count($this->battle_log[$turn_counter][$username]) == 1)
                    $this->battle_log[$turn_counter][$username]['failure'] = "failure";
            }
        }
    }

    //this method processes battle descriptions
    //in replaces certain sub strings with the correct information.
    function processBattleDescription($action, $owner)
    {
        $description = $this->jutsus[$action['id']]['battle_description'];

        if(is_array($description))
        {
            if(isset(array_flip($description)[$this->users[$owner][parent::SPECIALIZATION]]))
                $description = array_flip($description)[$this->users[$owner][parent::SPECIALIZATION]];
            else if($this->users[$owner][parent::SPECIALIZATION] == 'B' && isset(array_flip($description)['W']))
                $description = array_flip($description)['W'];
            else
                $description = 'missing description for split jutsu: '.$action['id'].' '.$this->users[$owner][parent::SPECIALIZATION];
        }


        $target = $action['target'];
        if( isset($this->users[$action['target']]['show_count']) && $this->users[$action['target']]['show_count'] == 'no')
            if( strpos($target, '#') !== false )
                $target = (substr( $target, 0, strpos($target,'#') - 1 ));

        if( isset($this->users[$owner]['show_count']) && $this->users[$owner]['show_count'] == 'no')
            if( strpos($owner, '#') !== false )
                $target = (substr( $owner, 0, strpos($owner,'#') - 1 ));

        if(isset($this->users[$target]['gender']))
            $targets_gender = $this->users[$target]['gender'];
        else
            $targets_gender = '';

        $owners_gender = $this->users[$owner]['gender'];

        $owner_replace = array();
        $target_replace = array();

        if($owners_gender == 'male')
        {
            $owner_replace[] = 'he';
            $owner_replace[] = 'him';
            $owner_replace[] = 'himself';
            $owner_replace[] = 'his';
            $owner_replace[] = 'himself';
        }
        else if($owners_gender == 'female')
        {
            $owner_replace[] = 'she';
            $owner_replace[] = 'her';
            $owner_replace[] = 'herself';
            $owner_replace[] = 'her';
            $owner_replace[] = 'herself';
        }
        else
        {
            $owner_replace[] = 'it';
            $owner_replace[] = 'it';
            $owner_replace[] = 'itself';
            $owner_replace[] = 'its';
            $owner_replace[] = 'itself';
        }

        if($targets_gender == 'male')
        {
            $target_replace[] = 'he';
            $target_replace[] = 'him';
            $target_replace[] = 'himself';
            $target_replace[] = 'his';
            $target_replace[] = 'himself';
        }
        else if($targets_gender == 'female')
        {
            $target_replace[] = 'she';
            $target_replace[] = 'her';
            $target_replace[] = 'herself';
            $target_replace[] = 'her';
            $target_replace[] = 'herself';
        }
        else
        {
            $target_replace[] = 'it';
            $target_replace[] = 'it';
            $target_replace[] = 'itself';
            $target_replace[] = 'its';
            $target_replace[] = 'itself';
        }

        //handling owner
        $description = str_replace('%useritemself',   $owner_replace[4], $description);
        $description = str_replace('%useritem',       $owner_replace[3], $description);
        $description = str_replace('%usergenderself', $owner_replace[2], $description);
        $description = str_replace('%usergender',     $owner_replace[1], $description);
        $description = str_replace('%user1',          $owner_replace[0], $description);
        $description = str_replace('%user',           $owner,            $description);

        //handling target
        $description = str_replace('%opponentitemself',   $target_replace[4], $description);
        $description = str_replace('%opponentitem',       $target_replace[3], $description);
        $description = str_replace('%opponentgenderself', $target_replace[2], $description);
        $description = str_replace('%opponentgender',     $target_replace[1], $description);
        $description = str_replace('%opponent1',          $target_replace[0], $description);
        $description = str_replace('%opponent',           $target,            $description);

        //handling weapons
        if(isset($action['weapon_ids'][0]))
            if(isset($this->users[$owner]['equipment'][$action['weapon_ids'][0]]['name']))
                $description = str_replace('%jutsuWeapon_1', $this->users[$owner]['equipment'][$action['weapon_ids'][0]]['name'], $description);
            else
                $description = str_replace('%jutsuWeapon_1', $this->users[$owner]['update']['remove'][$action['weapon_ids'][0]], $description);

        if(isset($action['weapon_ids'][1]))
            if(isset($this->users[$owner]['equipment'][$action['weapon_ids'][1]]['name']))
                $description = str_replace('%jutsuWeapon_2', $this->users[$owner]['equipment'][$action['weapon_ids'][1]]['name'], $description);
            else
                $description = str_replace('%jutsuWeapon_2', $this->users[$owner]['update']['remove'][$action['weapon_ids'][1]], $description);

        if(isset($action['weapon_ids'][2]))
            if(isset($this->users[$owner]['equipment'][$action['weapon_ids'][2]]['name']))
                $description = str_replace('%jutsuWeapon_3', $this->users[$owner]['equipment'][$action['weapon_ids'][2]]['name'], $description);
            else
                $description = str_replace('%jutsuWeapon_3', $this->users[$owner]['update']['remove'][$action['weapon_ids'][2]], $description);

        if(isset($action['weapon_ids'][3]))
            if(isset($this->users[$owner]['equipment'][$action['weapon_ids'][3]]['name']))
                $description = str_replace('%jutsuWeapon_4', $this->users[$owner]['equipment'][$action['weapon_ids'][3]]['name'], $description);
            else
                $description = str_replace('%jutsuWeapon_4', $this->users[$owner]['update']['remove'][$action['weapon_ids'][3]], $description);

        if(isset($action['weapon_ids'][4]))
            if(isset($this->users[$owner]['equipment'][$action['weapon_ids'][4]]['name']))
                $description = str_replace('%jutsuWeapon_5', $this->users[$owner]['equipment'][$action['weapon_ids'][4]]['name'], $description);
            else
                $description = str_replace('%jutsuWeapon_5', $this->users[$owner]['update']['remove'][$action['weapon_ids'][4]], $description);


        //handling items
        if(isset($action['item_names'][0]))
            $description = str_replace('%jutsuItem_1', $action['item_names'][0], $description);
        if(isset($action['item_names'][1]))
            $description = str_replace('%jutsuItem_2', $action['item_names'][1], $description);
        if(isset($action['item_names'][2]))
            $description = str_replace('%jutsuItem_3', $action['item_names'][2], $description);
        if(isset($action['item_names'][3]))
            $description = str_replace('%jutsuItem_4', $action['item_names'][3], $description);
        if(isset($action['item_names'][4]))
            $description = str_replace('%jutsuItem_5', $action['item_names'][4], $description);

        //replacing target type for basic attack fist vs chakra
        if(isset($action['target_type']))
        {
            if($action['target_type'] == 'N' || $action['target_type'] == 'G')
                $description = str_replace('%target_type', 'chakra', $description);
            else if($action['target_type'] == 'B' || $action['target_type'] == 'T')
                $description = str_replace('%target_type', 'fists', $description);
        }

        return str_replace('\\', '', $description);
    }

    //replacement for part of sf this is damage raiting.
    //this runs the user throught a modified version of the battle formula to find a good indication of the damage the user can do.
    //this returns their damage raiting.
    function findDR($user)
    {
        $powers = array(1,1,1,131672.15 ,153319.35 ,181534 );
        $powers_per_level = array(1,1,1,138.436,164.5945,195.71);

        $jutsu_power = $powers[$user[self::RANK]] + ($powers_per_level[$user[self::RANK]] * $user['jutsu_level_weight']);

        $owners_rank = $user[self::RANK];

        $targets_armor          = 477;

        $owners_stability       = $user[self::STABILITY];

        $owners_accuracy        = $user[self::ACCURACY];

        $owners_expertise       = $user[self::EXPERTISE];

        $owners_chakra_power    = $user[self::CHAKRAPOWER];

        $owners_critical_strike = $user[self::CRITICALSTRIKE];

		$offense_array = array('T' => $user[self::OFFENSE.'T'], 'N' => $user[self::OFFENSE.'N'], 'G' => $user[self::OFFENSE.'G'], 'B' => $user[self::OFFENSE.'B']);

		$offense_key = array_search(max($offense_array), $offense_array);

        $user_offense           = $user[self::OFFENSE.$offense_key];

        $target_defense         = 206225;

		$user_gens_temp = array($user['strength'], $user['speed'], $user['intelligence'], $user['willpower']);

        $user_general_1         = max($user_gens_temp);

		unset($user_gens_temp[ array_search( $user_general_1 , $user_gens_temp ) ]);

        $user_general_2         = max($user_gens_temp);

        $target_general_1       = 43545;

        $target_general_2       = 43545;

        $power_boost = $jutsu_power * ( $owners_accuracy / $this->ACCURACYCAP[$owners_rank] ) / ( $this->ACCURACYIDENTIFIER[$owners_rank]  + $owners_rank );

        //$expertise_boost = $user_offense * ( $owners_expertise / ( $owners_rank * $this->EXPERTISEIDENTIFIER[$owners_rank] ) );

        //$target_defense = 351 * ($target_defense * (1 + $targets_armor / 5000))**0.88;
        $target_defense = 351 * ($target_defense)**0.88;

        $attack_power = ( $jutsu_power + $power_boost ) * 100;

        $user_generals = ($user_general_1*0.7) + ($user_general_2*0.3);

        $target_generals = 351 * (($target_general_1*0.7) + ($target_general_2*0.3))**0.48;

        //$offense_vs_defense = ( ( $user_offense + $expertise_boost ) / ( $target_defense + $target_generals ) )**0.1;
        if($target_defense < 1)
            $target_defense = 1;

        if($target_generals < 1)
            $target_generals = 1;

        $offense_vs_defense = ( $user_offense / ( $target_defense + $target_generals ) )**0.1;

        $gen_vs_gen = ( $user_generals / $target_generals )**0.1;

        $battle_factor = ( $offense_vs_defense * $gen_vs_gen );

        $pure_offense =  $user_offense + $user_generals * 10 + $attack_power;

        $critical_power = ($owners_critical_strike / 0.05) ** ($owners_rank / 10);

        $chakra_calculation = ( $owners_chakra_power / 0.05 ) ** ( $owners_rank / 10 );

        if($offense_key == self::TAIJUTSU || $offense_key == self::BUKIJUTSU)
				$critical_multiplier = 1.0 + (0.1 + (($critical_power * (10/3)) * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) / 100) * (1000 - (975 - ( $critical_power * 3 * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) * 5))/1000;
        else
                $critical_multiplier = 1.0 + (0.1 + (($chakra_calculation * (10/3)) * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) / 100) * (1000 - (975 - ( $chakra_calculation * 3 * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) * 5))/1000;

        if($owners_rank < 3)
        {
            $fluxMin = 75;
            $fluxMax = 110;
        }
        else
        {
            $fluxMin = 75  + ( ( $owners_stability / $this->FLUXIDENTIFIERMIN[$owners_rank] ) / 100);
            $fluxMax = 110 - ( ( $owners_stability / $this->FLUXIDENTIFIERMAX[$owners_rank] ) / 100);
        }

        $inital_damage = 0.1 * ($battle_factor ** 3.5) * ($pure_offense * 0.2);

        //accounting for armor and expertise.
       $expertiseBoost = (($owners_expertise**1.5)/($this->EXPERTISECAP[$owners_rank]**1.5) * 0.1);

       $armorBoost = (($targets_armor**1.5)/($this->ARMORCAP[4]**1.5) * -0.2) + 1;

       $inital_damage = $inital_damage * ($expertiseBoost + $armorBoost);

        $post_flux_damage = $inital_damage * ( $fluxMin + $fluxMax / 200 );

        $damage_value = $post_flux_damage * $critical_multiplier;

        $weight = 10/3; //this weight will roughly convert the weight to 10% stats and 90% damage value. its very messy sadly but this works okay.

        //getting weight from bloodline rarity
        if($user['rarity'] == 'S')
            $damage_value = $damage_value * 1.15;
        else if($user['rarity'] == 'A' || $user['rarity'] == 'H' )
            $damage_value = $damage_value * 1.15;
        else if($user['rarity'] == 'B')
            $damage_value = $damage_value * 1.125;
        else if($user['rarity'] == 'C')
            $damage_value = $damage_value * 1.1;


        return $damage_value; //+ array_sum(array($user['strength'], $user['speed'], $user['intelligence'], $user['willpower'])) / $weight;
    }

    //replacement for part of sf this is survivability raiting
    //this runs the user throught a modified version of the battle formula to find a good indication of the damage the user will take.
    //this returns their survivability raiting.
    function findSR($user)
    {
        $jutsu_power = 153319.35 + (164.5945 * 100);

        $owners_rank = 4;

        $targets_armor          = $user['armor'];

        $owners_stability       = 0;

        $owners_accuracy        = 394;

        $owners_expertise       = 477;

        $owners_chakra_power    = 10675;

        $owners_critical_strike = 17500;

        $user_offense           = 153913;

        $target_defense         = ($user[self::DEFENSE.'T'] + $user[self::DEFENSE.'N'] + $user[self::DEFENSE.'G'] + $user[self::DEFENSE.'B'])/4;

        $user_general_1         = 43545;

        $user_general_2         = 43545;

        $target_general_1       = ( $user['strength'] + $user['speed'] + $user['intelligence'] + $user['willpower'] )/4;

        $target_general_2       = $target_general_1 ;

        $power_boost = $jutsu_power * ( $owners_accuracy / $this->ACCURACYCAP[$owners_rank] ) / ( $this->ACCURACYIDENTIFIER[$owners_rank]  + $owners_rank );

        //$expertise_boost = $user_offense * ( $owners_expertise / ( $owners_rank * $this->EXPERTISEIDENTIFIER[$owners_rank] ) );

        //$target_defense = 351 * ($target_defense * (1 + $targets_armor / 5000))**0.88;
        $target_defense = 351 * ($target_defense)**0.88;

        $attack_power = ( $jutsu_power + $power_boost ) * 100;

        $user_generals = ($user_general_1*0.7) + ($user_general_2*0.3);

        $target_generals = 351 * (($target_general_1*0.7) + ($target_general_2*0.3))**0.48;

        //$offense_vs_defense = ( ( $user_offense + $expertise_boost ) / ( $target_defense + $target_generals ) )**0.1;
        if($target_defense < 1)
            $target_defense = 1;

        if($target_generals < 1)
            $target_generals = 1;

        $offense_vs_defense = ( $user_offense / ( $target_defense + $target_generals ) )**0.1;

        $gen_vs_gen = ( $user_generals / $target_generals )**0.1;

        $battle_factor = ( $offense_vs_defense * $gen_vs_gen );

        $pure_offense = ($user_offense + $user_generals * 10 + $attack_power);

        $critical_power = ($owners_critical_strike / 0.05) ** ($owners_rank / 10);

        $chakra_calculation = ( $owners_chakra_power / 0.05 ) ** ( $owners_rank / 10 );

        $critical_multiplier = 1.0 + ((0.1 + (($critical_power * (10/3)) * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) / 100) * (1000 - (975 - ( $critical_power * 3 * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) * 5))/1000)/2;

        $critical_multiplier += ( (0.1 + (($chakra_calculation * (10/3)) * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) / 100) * (1000 - (975 - ( $chakra_calculation * 3 * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) * 5))/1000 ) / 2;



        if($owners_rank < 3)
        {
            $fluxMin = 75;
            $fluxMax = 110;
        }
        else
        {
            $fluxMin = 75  + ( ( $owners_stability / $this->FLUXIDENTIFIERMIN[$owners_rank] ) / 100);
            $fluxMax = 110 - ( ( $owners_stability / $this->FLUXIDENTIFIERMAX[$owners_rank] ) / 100);
        }

        $inital_damage = 0.1 * ($battle_factor ** 3.5) * ($pure_offense * 0.2);

        //accounting for armor and expertise.
        $expertiseBoost = (($owners_expertise**1.5)/($this->EXPERTISECAP[$owners_rank]**1.5) * 0.1);

        $armorBoost = (($targets_armor**1.5)/($this->ARMORCAP[$user[self::RANK]]**1.5) * -0.2) + 1;

        $inital_damage = $inital_damage * ($expertiseBoost + $armorBoost);


        $post_flux_damage = $inital_damage * ( $fluxMin + $fluxMax / 200 );

        $damage_value = $post_flux_damage * $critical_multiplier;

        //getting weight from bloodline rarity
        if($user['rarity'] == 'S')
            $damage_value = $damage_value * 1.1;
        else if($user['rarity'] == 'A' || $user['rarity'] == 'H' )
            $damage_value = $damage_value * 1.1;
        else if($user['rarity'] == 'B')
            $damage_value = $damage_value * 1.075;
        else if($user['rarity'] == 'C')
            $damage_value = $damage_value * 1.05;

        return $damage_value;
    }


    //update SR and DR
    //calls find SR and DR and combines them.
    function updateDR_SR($username)
    {
         $user = $this->users[$username];

         $this->users[$username]['sr'] = (int)$this->findSR($user);
         $this->users[$username]['dr'] = (int)$this->findDR($user);
    }

    //findDSR
    function findDSR($username)
    {
        $user = $this->users[$username];

        $weight = 0.5; //this weight will convert the weight to 75% max health and 25% current health.

        $health = (( $user[parent::HEALTH] * $weight ) + ($user[parent::HEALTHMAX] * (1 - $weight)));

        return ( $health / ($user['sr']) ) * $user['dr'];
    }

    //updateDSRs
    //this method calls findDSR on all users in the battle to update their DSR
    //this is called after a round is over.
    function updateDSRs()
    {
        foreach($this->users as $username => $userdata)
        {
            $this->users[$username]['DSR'] = $this->findDSR($username);
        }
    }

    //checkUsersForRemove
    //this is called after a turn has been processed to see if a user has been removed
    //if the have remove them from the combat.
    public function checkUsersForRemove()
    {
        $users_removed = array();

        //foreach user
        foreach($this->users as $username => $userdata)
        {
            //if the user is marked to be removed
            if( isset($userdata['remove']) && $userdata['remove'])
            {
                //remove them from battle
                $this->removeUserFromBattle($username);

                //record that they were removed
                if(!isset($userdata['ai']) || $userdata['ai'] === false)
                    $users_removed[] = $username;
            }
        }

        //change each removed users status in the database to exiting_combat.
        $users = '';
        foreach($users_removed as $user)
        {
            if($users == '')
                $users = "'".$user."'";
            else
                $users .= ", '".$user."'";
        }

        if(strlen($users) > 3)
        {
            $query = "UPDATE `users` SET `status` = 'exiting_combat' WHERE `username` in (".$users.")";

            try { $GLOBALS['database']->execute_query($query); }
            catch (Exception $e)
            {
                try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                catch (Exception $e)
                {
                    try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed to update user information'); }
                    catch (Exception $e)
                    {
                        $GLOBALS['DebugTool']->push('','there was an error updating user status.', __METHOD__, __FILE__, __LINE__);
                        throw $e;
                    }
                }
            }
        }
    }

    //removeUserFromBattle
    //this is called at the end of a round to purge the user from the battle
    public function removeUserFromBattle($username)
    {
        $team = $this->users[$username][self::TEAM];

        if(!isset($this->users[$username]['ai']) || $this->users[$username]['ai'] !== true)
            $this->removed_users[$username] = $this->users[$username];

        //removing users arrays
        unset($this->users[$username]);

        //unset($this->teams[$team][array_search($username,$this->teams[$team])]);
        foreach($this->teams[$team] as $user_key => $user)
            if($user == $username)
                unset($this->teams[$team][$user_key]);

        if(count($this->teams[$team]) == 0)
            unset($this->teams[$team]);
    }

    //removeUserFromCombat
    //this is called by flee or when a user is killed
    public function removeUserFromCombat($username, $status, $recursion=false)
    {       
        $temp = $username.'/'.$this->users[$username][self::TEAM].'/human';

        //if(isset($this->turn_order[$this->turn_counter]) && $status != 'flee')
        //    unset($this->turn_order[$this->turn_counter][ array_search($username, $this->turn_order[$this->turn_counter]) ]);


        if ($status === 'flee')
        {
            $this->census = str_replace($temp.'/na',$temp.'/flee',$this->census);
            $status = false;
        }

        else if($status === true)
            $this->census = str_replace($temp.'/na',$temp.'/win',$this->census);

        else if($status === false)
            $this->census = str_replace($temp.'/na',$temp.'/loss',$this->census);

        $team = $this->users[$username][self::TEAM];

        $this->users[$username]['win_lose'] = $status;

        //handling summon stuff
        //if this user has summons marked as linked
        if(isset($this->users[$username]['summons']))
        {
            foreach($this->users[$username]['summons'] as $key => $summon_name)
            {
                $this->removeUserFromCombat($summon_name, $status, true);
            }
        }

        //if this is a summon that is linked to a user remove this summon from that user's list of summons.
        if(isset($this->users[$username]['summoned']) && !$recursion)
        {
            unset($this->users[$this->users[$username]['summoned']]['summons'][$username]);
        }

        //going through all users
        foreach($this->users as $user_key => $user)
        {
            //going through all tags
            foreach($user[self::TAGS] as $tags_key => $tag)
                if($tag->owner == $username && $tag->persistAfterDeath == false)
                {
                    unset($this->users[$user_key][self::TAGS][$tags_key]);
                    unset($this->users[$user_key][self::TAGSINEFFECT][$tags_key]);
                }

            //going through all tags
            foreach($this->run_ready_array as $tags_key => $tag)
                if($tag->owner == $username && $tag->persistAfterDeath == false)
                {
                    unset($this->run_ready_array[$tags_key]);
                }
        }

        //if tracking balance and this is a loss mark users jutsus as neg
        if(isset($this->balanceFlag) && $this->balanceFlag === true && $status === false)
        {
            foreach($this->balance[$username] as $jutsu_key => $jutsu_count)
                if(!is_array($jutsu_count))
                    $this->balance[$username][$jutsu_key] = -1 * $jutsu_count;
                else
                    foreach($jutsu_count as $spec => $new_count)
                        $this->balance[$username][$jutsu_key][$spec] = -1 * $new_count;
        }
    }

    //this function looks at all users and checks if they are currently stunned.
    //if they are currently stunned it will submit their "action" for the turn
    public function processStunnedUsers()
    {
        foreach($this->users as $username => $userdata)
            if(isset($userdata['status_effects']['stunned']) && $userdata['status_effects']['stunned'] != 0)
                $this->recordAction($username, $username, 'stunned', false, false);
    }

    //this function calls takeAiTurn for all ai in the battle.
    //this results in their turns being processed.
    public function processAI($turn_order)
    {
        foreach($turn_order as $username)
        {
            if( isset($this->users[$username]) && $this->users[$username]['ai'] && (!isset($this->users[$username]['status_effects']['stunned']) || $this->users[$username]['status_effects']['stunned'] == 0) )
            {
                $this->takeAiTurn($username);
            }
        }
    }

    //takeAiTurn is called by processAI on all ais
    //this is where the action is decided for the ai to take.
    public function takeAiTurn($owner_username)
    {
        $action = '';

        //finding action to take
        //doing optimised random
        if($this->users[$owner_username]['instructions'] == '' || $this->users[$owner_username]['instructions'] == 'random')
        {
            $found = false;

            //looking for jutsu
            $jutsus = array();
            //if there is a jutsu
            if( count($this->users[$owner_username]['ai_actions']['jutsu']) != 0)
            {
                //for each jutsu
                foreach($this->users[$owner_username]['ai_actions']['jutsu'] as $jutsu  )
                {
                    if( (!isset($this->users[$owner_username]['status_effects']['disabled']) || $this->users[$owner_username]['status_effects']['disabled'] == 0) && $jutsu['id'] != -1 )
                    {
                        //check if the jutsu is good, if so add it.
                        if( $this->AiCheckJutsu($owner_username, $jutsu['id']) )
                            $jutsus[] = $jutsu;
                    }
                }

                //if there are good jutsus
                if( count($jutsus) > 0 )
                {

                    //pick a random jutsu and get its information
                    $jutsu = $jutsus[ random_int(0, count($jutsus) - 1 ) ];
                    $action = 'jutsu';
                    $target_username = $this->getTargetForAi($jutsu['targeting'], $owner_username);
                    $id = $jutsu['id'];
                    $level = $jutsu['level'];

                    if(count($jutsu['weapons']) == 0)
                        $weapon_ids = false;
                    else
                        $weapon_ids = $Jutsu['weapons'];

                    $found = true;
                }
            }
            //looking for weapon if a jutsu was not found and there are weapons
            $weapons = array();
            if( count($this->users[$owner_username]['ai_actions']['weapon']) != 0 && !$found)
            {
                //for every weapon
                foreach($this->users[$owner_username]['ai_actions']['weapon'] as $weapon  )
                {
                    //check to see if weapon is good if so add it
                    if( $this->AiCheckWeapon($owner_username, $weapon['id']) )
                        $weapons[] = $weapon;
                }

                //if there is atleast 1 good weapon
                if( count($weapons) > 0 )
                {
                    //pick and random weapon and record its information.
                    $weapon = $weapons[ random_int(0, count($weapons) - 1 ) ];
                    $action = 'weapon';
                    $target_username = $this->getTargetForAi($weapon['targeting'], $owner_username);
                    $id = $weapon['id'];
                    $level = $weapon['level'];
                    $weapon_ids = false;
                    $found = true;
                }
            }
            //looking for item if a weapon or a jutsu were not found and there are items
            $items = array();
            if( count($this->users[$owner_username]['ai_actions']['item']) != 0 && !$found)
            {
                //for each item
                foreach($this->users[$owner_username]['ai_actions']['item'] as $item  )
                {
                    //check if the item is good, if so add it
                    if( $this->AiCheckItem($owner_username, $item['id']) )
                        $items[] = $item;
                }

                //if there is a good item
                if( count($items) > 0 )
                {
                    //pick a random good item and record its information.
                    $item = $items[ random_int(0, count($items) - 1 ) ];
                    $action = 'item';
                    $target_username = $this->getTargetForAi($item['targeting'], $owner_username);
                    $id = $item['id'];
                    $level = $item['level'];
                    $weapon_ids = false;
                    $found = true;
                }

            }
            //settling for basic attack if a jutsu or weapon or item was not found first.
            if(!$found)
            {
                //record information for basic attack
                $action = 'jutsu';
                $target_username = $this->getTargetForAi('opponent', $owner_username);
                $id = -1;
                $level = $this->users[$owner_username]['jutsus'][-1]['level'];
                $weapon_ids = false;
            }

        }

        //doing defined logic rather than random auto/random logic
        else
        {
            $found = false;
            //foeach instruction
            foreach($this->users[$owner_username]['instructions'] as $instruction_key => $instruction)
            {
                // set action to blank..
                $action = '';

                //if there is an active chain that should not be broken skip down to it.
                if( isset($this->users[$owner_username]['last_instruction']) &&
                    $this->users[$owner_username]['last_instruction']['action']['breaking'] != 'yes' &&
                    $this->users[$owner_username]['last_instruction']['instruction_key'] != $instruction_key && $this->users[$owner_username]['last_instruction']['finished'] === false)
                    continue;

                //process chain here
                //if there is a prior instruction and this instruction matches it
                if( isset($this->users[$owner_username]['last_instruction']) && $this->users[$owner_username]['last_instruction']['instruction_key'] == $instruction_key)
                {

                    //wrap around chain if last instruction was the end of the chain
                    if( $this->users[$owner_username]['last_instruction']['progression'] >= count($instruction['actions']) )
                    {
                        $this->users[$owner_username]['last_instruction']['progression'] = 0;
                    }

                    //if we are at the end of the progresion mark it as finished so it wont get stuck in an endless loop
                    else if($this->users[$owner_username]['last_instruction']['progression'] >= count($instruction['actions']) - 1)
                    {
                        $this->users[$owner_username]['last_instruction']['finished'] = true;
                    }

                    //if we are not at the end of the progresion mark it as not finished so it will continue the chain.
                    else
                    {
                        $this->users[$owner_username]['last_instruction']['finished'] = false;
                    }

                    //check if action sugested by instruction is avaliable
                    $check = false;

                    //for each action after and including the action next in line marked by progression
                    //continue if not at end and check is still false
                    for($i = $this->users[$owner_username]['last_instruction']['progression']; !$check && $i < count($instruction['actions']); $i++)
                    {
                        //if the suggested action is a jutsu
                        if($instruction['actions'][$i]['type'] == 'jutsu')
                        {
                            //check availability of action
                            if($this->AiCheckJutsu($owner_username, $instruction['actions'][$i]['id']))
                            {
                                //if this is a valid action record its information and mark check as true
                                $action = array();
                                $action['id'] = $instruction['actions'][$i]['id'];
                                $action['type'] = $instruction['actions'][$i]['type'];
                                $action['breaking'] = $instruction['breaking'];
                                $action['level'] = $this->users[$owner_username]['ai_actions'][$action['type']][$action['id']]['level'];
                                $action['weapons'] = $this->users[$owner_username]['ai_actions'][$action['type']][$action['id']]['weapons'];

                                $this->users[$owner_username]['last_instruction']['progression'] = $i;
                                $check = true;
                            }
                        }

                        //if the suggest action is a weapon
                        else if($instruction['actions'][$i]['type'] == 'weapon')
                        {
                            //check avaliablilty of action
                            if($this->AiCheckWeapon($owner_username, $instruction['actions'][$i]['id']))
                            {
                                //if this is a valid action record its information and mark check as true
                                $action = array();
                                $action['id'] = $instruction['actions'][$i]['id'];
                                $action['type'] = $instruction['actions'][$i]['type'];
                                $action['breaking'] = $instruction['breaking'];
                                $action['level'] = $this->users[$owner_username]['ai_actions'][$action['type']][$action['id']]['level'];
                                $action['weapons'] = false;

                                $this->users[$owner_username]['last_instruction']['progression'] = $i;
                                $check = true;
                            }
                        }

                        //if the suggest action is an item
                        else if($instruction['actions'][$i]['type'] == 'item')
                        {
                            //check avaliablity of action
                            if($this->AiCheckItem($owner_username, $instruction['actions'][$i]['id']))
                            {
                                //if this is a valid action record its information and mark check as true
                                $action = array();
                                $action['id'] = $instruction['actions'][$i]['id'];
                                $action['type'] = $instruction['actions'][$i]['type'];
                                $action['breaking'] = $instruction['breaking'];
                                $action['level'] = $this->users[$owner_username]['ai_actions'][$action['type']][$action['id']]['level'];
                                $action['weapons'] = false;

                                $this->users[$owner_username]['last_instruction']['progression'] = $i;
                                $check = true;
                            }
                        }


                    }

                    //if no actions can be taken between the current position in the chain and the end of the chain, wrap around
                    if(!$check)
                    {
                        //for every action from the first action in the chain up to and not including the action suggested by progression
                        for($i = 0; !$check && $i < $this->users[$owner_username]['last_instruction']['progression']; $i++)
                        {
                            //if the suggested action is a jutsu
                            if($instruction['actions'][$i]['type'] == 'jutsu')
                            {
                                //check availability of action
                                if($this->AiCheckJutsu($owner_username, $instruction['actions'][$i]['id']))
                                {
                                    //if this is a valid action record its information and mark check as true
                                    $action = array();
                                    $action['id'] = $instruction['actions'][$i]['id'];
                                    $action['type'] = $instruction['actions'][$i]['type'];
                                    $action['breaking'] = $instruction['breaking'];
                                    $action['level'] = $this->users[$owner_username]['ai_actions'][$action['type']][$action['id']]['level'];
                                    $action['weapons'] = $this->users[$owner_username]['ai_actions'][$action['type']][$action['id']]['weapons'];

                                    $this->users[$owner_username]['last_instruction']['progression'] = $i;
                                    $check = true;
                                }
                            }

                            //if the suggested action is a weapon
                            else if($instruction['actions'][$i]['type'] == 'weapon')
                            {
                                //check availability of action
                                if($this->AiCheckWeapon($owner_username, $instruction['actions'][$i]['id']))
                                {
                                    //if this is a valid action record its informaiton and mark check as true
                                    $action = array();
                                    $action['id'] = $instruction['actions'][$i]['id'];
                                    $action['type'] = $instruction['actions'][$i]['type'];
                                    $action['breaking'] = $instruction['breaking'];
                                    $action['level'] = $this->users[$owner_username]['ai_actions'][$action['type']][$action['id']]['level'];
                                    $action['weapons'] = false;

                                    $this->users[$owner_username]['last_instruction']['progression'] = $i;
                                    $check = true;
                                }
                            }

                            //if the suggest action is an item
                            else if($instruction['actions'][$i]['type'] == 'item')
                            {
                                //check availability of aciton
                                if($this->AiCheckItem($owner_username, $instruction['actions'][$i]['id']))
                                {
                                    //if this is a valid action record its information and mark check as true
                                    $action = array();
                                    $action['id'] = $instruction['actions'][$i]['id'];
                                    $action['type'] = $instruction['actions'][$i]['type'];
                                    $action['breaking'] = $instruction['breaking'];
                                    $action['level'] = $this->users[$owner_username]['ai_actions'][$action['type']][$action['id']]['level'];
                                    $action['weapons'] = false;

                                    $this->users[$owner_username]['last_instruction']['progression'] = $i;
                                    $check = true;
                                }
                            }
                        }
                    }




                    //break out if no actions in the chain can be taken.
                    if(!$check)
                        continue;
                }

                //if this instruction is not apart of the current chain
                else
                {
                    //if the first action of this instruction is a jutsu
                    if($instruction['actions'][0]['type'] == 'jutsu')
                    {
                        //check availability of action
                        if($this->AiCheckJutsu($owner_username, $instruction['actions'][0]['id']))
                        {
                            //if this is a valid action record its information
                            $action = array();
                            $action['id'] = $instruction['actions'][0]['id'];
                            $action['type'] = $instruction['actions'][0]['type'];
                            $action['breaking'] = $instruction['breaking'];
                            $action['level'] = $this->users[$owner_username]['ai_actions'][$action['type']][$action['id']]['level'];
                            $action['weapons'] = $this->users[$owner_username]['ai_actions'][$action['type']][$action['id']]['weapons'];
                        }
                        //if this action is not good skip to the next instruction
                        else
                        {
                            continue;
                        }
                    }

                    //if the first action of this instruction is a weapon
                    else if($instruction['actions'][0]['type'] == 'weapon')
                    {
                        //check availability of action
                        if($this->AiCheckWeapon($owner_username, $instruction['actions'][0]['id']))
                        {
                            //if this is a valid action record its infomation
                            $action = array();
                            $action['id'] = $instruction['actions'][0]['id'];
                            $action['type'] = $instruction['actions'][0]['type'];
                            $action['breaking'] = $instruction['breaking'];
                            $action['level'] = $this->users[$owner_username]['ai_actions'][$action['type']][$action['id']]['level'];
                            $action['weapons'] = false;
                        }
                        //if this action is not good skip to the next instruction
                        else
                        {
                            continue;
                        }
                    }

                    //if the first action of this instruction is an item
                    else if($instruction['actions'][0]['type'] == 'item')
                    {
                        //check availability of action
                        if($this->AiCheckItem($owner_username, $instruction['actions'][0]['id']))
                        {
                            //if this is a valid action record its information
                            $action = array();
                            $action['id'] = $instruction['actions'][0]['id'];
                            $action['type'] = $instruction['actions'][0]['type'];
                            $action['breaking'] = $instruction['breaking'];
                            $action['level'] = $this->users[$owner_username]['ai_actions'][$action['type']][$action['id']]['level'];
                            $action['weapons'] = false;
                        }
                        //if this action is not good skip to the next instruction
                        else
                        {
                            continue;
                        }
                    }

                }

                //get all targets together based on targeting type
                $targets = array();
                //for ever user and ai in the system
                foreach($this->users as $username => $userdata)
                {
                    //if targeting type of this is opponent
                    if($instruction['targeting'] == 'opponent')
                    {
                        //if this user is an opponent record it
                        if($userdata['team'] != $this->users[$owner_username]['team'])
                            $targets[] = $username;
                    }
                    //if targeting type of this is ally
                    else if ($instruction['targeting'] == 'ally')
                    {
                        //if this user is an ally record it
                        if($userdata['team'] == $this->users[$owner_username]['team'] && $owner_username != $username)
                            $targets[] = $username;
                    }
                    //if targeting type is not ally or opponent assume self
                    else if ($instruction['targeting'] == 'self')
                    {
                        //if targeting mode is self just set self as targets and break out of loop.
                        $targets[] = $owner_username;
                        break;
                    }

                }

                //if the control charecter for this instruction is <
                if($instruction['control'] == '<')
                {


                    //find most < target in set and check if it meets the requirement %
                    $target = '';
                    $temp = 999999999;
                    foreach($targets as $target_username)
                    {
                        if($this->users[$target_username][parent::HEALTH] < $temp)
                        {
                            $target = $target_username;
                            $temp = $this->users[$target_username][parent::HEALTH];
                        }
                    }

                    // if requirement is meet mark found and set information.
                    if(($this->users[$target][parent::HEALTH] / $this->users[$target][parent::HEALTHMAX]) * 100 <= $instruction['percentage'] )
                    {

                        //last action is set here
                        if(isset($this->users[$owner_username]['last_instruction']) && $this->users[$owner_username]['last_instruction']['instruction_key'] == $instruction_key )
                        {
                            if( isset($this->users[$owner_username]['last_instruction']['finished']) && $this->users[$owner_username]['last_instruction']['finished'] == true)
                                $this->users[$owner_username]['last_instruction'] = array('instruction_key' => $instruction_key, 'action' => $action, 'target' => $target, 'progression'=>1 + $this->users[$owner_username]['last_instruction']['progression'], 'finished'=>true);
                            else
                                $this->users[$owner_username]['last_instruction'] = array('instruction_key' => $instruction_key, 'action' => $action, 'target' => $target, 'progression'=>1 + $this->users[$owner_username]['last_instruction']['progression'], 'finished'=>false);
                        }
                        else
                            $this->users[$owner_username]['last_instruction'] = array('instruction_key' => $instruction_key, 'action' => $action, 'target' => $target, 'progression'=>1 ,'finished'=>false);
                        //setting found
                        $found = true;

                        //action is set here
                        $target_username = $target;
                        $id = $action['id'];
                        $level = $action['level'];
                        $weapon_ids = $action['weapons'];
                        $action = $action['type'];

                        //getting out of loop.
                        break;
                    }
                }

                //if the control charecter for this instruction is >
                else if($instruction['control'] == '>')
                {
                    //find most > target in set and check if it meets the requirement %
                    $target = '';
                    $temp = -1;
                    foreach($targets as $target_username)
                    {
                        if($this->users[$target_username][parent::HEALTH] > $temp)
                        {
                            $target = $target_username;
                            $temp = $this->users[$target_username][parent::HEALTH];
                        }
                    }

                    // if requirement is meet mark found and set information.
                    if(($this->users[$target][parent::HEALTH] / $this->users[$target][parent::HEALTHMAX]) * 100 >= $instruction['percentage'] )
                    {
                        //last action is set here
                        if(isset($this->users[$owner_username]['last_instruction']) && $this->users[$owner_username]['last_instruction']['instruction_key'] == $instruction_key )
                        {
                            if( isset($this->users[$owner_username]['last_instruction']['finished']) && $this->users[$owner_username]['last_instruction']['finished'] == true)
                                $this->users[$owner_username]['last_instruction'] = array('instruction_key' => $instruction_key, 'action' => $action, 'target' => $target, 'progression'=>1 + $this->users[$owner_username]['last_instruction']['progression'], 'finished'=>true);
                            else
                                $this->users[$owner_username]['last_instruction'] = array('instruction_key' => $instruction_key, 'action' => $action, 'target' => $target, 'progression'=>1 + $this->users[$owner_username]['last_instruction']['progression'], 'finished'=>false);
                        }
                        else
                            $this->users[$owner_username]['last_instruction'] = array('instruction_key' => $instruction_key, 'action' => $action, 'target' => $target, 'progression'=>1, 'finished'=>false);

                        //setting found
                        $found = true;

                        //action is set here
                        $target_username = $target;
                        $id = $action['id'];
                        $level = $action['level'];
                        $weapon_ids = $action['weapons'];
                        $action = $action['type'];

                        //getting out of loop.
                        break;
                    }
                }

            }

            //if no action was found
            if(!$found)
            {
                //set action as basic attack
                $action = 'jutsu';
                $target_username = $this->getTargetForAi('opponent', $owner_username);
                $id = -1;
                $level = $this->users[$owner_username]['jutsus'][-1]['level'];
                $weapon_ids = false;
            }

        }




        //calling action
        if($action == 'jutsu')
            $this->AiUseJutsu($target_username, $owner_username, $id, $level, $weapon_ids);
        else if( $action == 'weapon' )
            $this->AiUseWeapon($target_username, $owner_username, $id, $level);
        else if( $action == 'item' )
            $this->AiUseItem($target_username, $owner_username, $id, $level);

    }

    //getTargetForAi
    //this method is used by the ai to find a target when in auto mode/ random mode
    public function getTargetForAi($targeting, $owner_username)
    {
        //finding target
        $target_username = array();

        // for ever user
        foreach($this->users as $username => $userdata)
        {
            //if the targeting mode is opponent
            if ($targeting == 'opponent')
            {
                // if this is an opponent record it
                if($userdata['team'] != $this->users[$owner_username]['team'])
                {
                    $target_username[] = $username;
                }
            }
            //if the targeting mode is ally
            else if( $targeting == 'ally' )
            {
                // if this is an ally record it
                if($userdata['team'] == $this->users[$owner_username]['team'])
                {
                    $target_username[] = $username;
                }
            }
            //other wise assume targeting mode is self
            else
            {
                //if targeting mode is self set target as self and break out of loop
                $target_username[] = $owner_username;
                break;
            }
        }

        //if no target was found default to self
        if( count($target_username) == 0 )
            return $owner_username;

        //if 1 target was found use that target
        else if( count($target_username) == 1 )
            return $target_username[0];

        //if more that 1 target was found
        else
        {
            //find target with the least health
            $lowest = '';
            $temp = 9999999999;
            foreach($target_username as $username)
            {
                if($this->users[$username][parent::HEALTH] < $temp)
                {
                    $temp = $this->users[$username][parent::HEALTH];

                    $lowest = $username;
                }
                //taking care of special edge case for users with the exact same health
                else if($this->users[$username][parent::HEALTH] == $temp)
                {
                    $lowest .= '|'.$username;
                }
            }

            //explode on | to conver chosen targets into array.
            //there will only be one target unless two targets have the same health and
            //just so happen to have the least health
            $target_username = explode('|',$lowest);

            //pick a random target from the final list of targets.
            return $target_username[ random_int(0, count($target_username)-1 ) ];
        }
    }

    //aiJutsu is called by takeAiTurn
    //this does the jutsu for an ai.
    public function AiUseJutsu($target_username, $owner_username, $jutsu_id, $weapon_ids=false)
    {
        $weapon_power = 0;
        $weapon_tags = 0;

        //if there are weapons and it is an array
        if($weapon_ids !== false && is_array($weapon_ids))
        {
            $weapon_count = count($weapon_ids);
            //for each weapon
            foreach($weapon_ids as $weapon_inventory_id)
            {
                //add weapon power as an argument to be passed to doDamage
                if($weapon_count == 1)
                    $weapon_power += $this->users[$owner_username][self::EQUIPMENT][$weapon_inventory_id]['strength'] * 0.5;
                else if($weapon_count == 2)
                    $weapon_power += $this->users[$owner_username][self::EQUIPMENT][$weapon_inventory_id]['strength'] * 0.6 * 0.5;
                else
                    $weapon_power += $this->users[$owner_username][self::EQUIPMENT][$weapon_inventory_id]['strength'] * (1/$weapon_count + 0.05 ) * 0.5;

                //record weapon tags
                $weapon_tags[$weapon_inventory_id] = $this->users[$owner_username][self::EQUIPMENT][$weapon_inventory_id]['on_jutsu_tags'];

                //mark weapon as used
                $this->users[$owner_username]['equipment_used'][ $this->users[$owner_username][parent::EQUIPMENT][$weapon_inventory_id]['iid'] ]['uses']++;
            }
        }

        //checking for and processing health cost of jutsu
        $this->users[$owner_username][parent::HEALTH]  -= $this->users[$owner_username]['jutsus'][$jutsu_id]['hea_cost'];

        //making sure that health dosnt drop to zero
        if($this->users[$owner_username][parent::HEALTH] < 1)
            $this->users[$owner_username][parent::HEALTH] = 1;

        //update cooldowns.
        $this->setJutsuCooldown($jutsu_id, $this->jutsus[$jutsu_id]['cooldown_pool_set'], $owner_username);

        // applyJutsu
        $this->recordAction( $owner_username, $target_username, 'jutsu', $jutsu_id, $this->jutsus[$jutsu_id]['name']);

        //start here
        if(!is_array($this->jutsus[$jutsu_id]['tags']))
        {
            $this->addTags($this->parseTags($this->jutsus[$jutsu_id]['tags']), $target_username, $owner_username, parent::JUTSU, false, $this->users[$owner_username]['jutsus'][$jutsu_id]['ai_actions']['level'], $this->jutsus[$jutsu_id]['targeting_type'], $weapon_power, $weapon_ids);
        }
        else
        {
            if(isset(array_flip($this->jutsus[$jutsu_id]['tags'])[ $this->users[$owner_username][parent::SPECIALIZATION] ]))
                $this->addTags($this->parseTags(array_flip($this->jutsus[$jutsu_id]['tags'])[ $this->users[$owner_username][parent::SPECIALIZATION] ]), $target_username, $owner_username, parent::JUTSU, false, $this->users[$owner_username]['jutsus'][$jutsu_id]['ai_actions']['level'], $this->jutsus[$jutsu_id]['targeting_type'], $weapon_power, $weapon_ids);
            else
                error_log('bad split jutsu: '.$jutsu_id.' : '.$this->users[$owner_username][parent::SPECIALIZATION]);
        }

        if($weapon_tags !== 0 && count($weapon_tags) != 0)
            foreach($weapon_tags as $id => $tags)
                $this->addTags($this->parseTags($tags), $target_username, $owner_username, parent::WEAPON, $id, false, $this->jutsus[$jutsu_id]['targeting_type']);

        //mark this jutsu as having been used one more time.
        $this->users[$owner_username]['jutsus'][$jutsu_id]['uses']++;
    }

    //aiCheckJutsu
    //just checks cooldown and max uses to see if this jutsu is usable.
    public function AiCheckJutsu($username, $jid)
    {
        //if this jutsu has not been used its max number of times
        if( $this->users[$username]['jutsus'][$jid]['uses'] < $this->users[$username]['jutsus'][$jid]['max_uses'] || $this->users[$username]['jutsus'][$jid]['max_uses'] < 0)
        {
            //if this jutsu is not on cool down
            if($this->checkJutsuCooldown($jid, $this->jutsus[$jid]['cooldown_pool_check'], $username) == false || $this->users[$username]['ignoreCooldown'] == 'yes')
                return true; //return true
            //if this jutsu is on cool down
            else
                return false;//return false
        }
        //if this jutsu has been used its max number of times
        else
            return false;//return false
    }

    //aiWeapon
    //this method is called when a user attacks whith a weapon.
    //this method simply applys the weapons tags to the target
    public function AiUseWeapon($target_username, $owner_username, $weapon_id, $level)
    {
        $weapon = $this->users[$owner_username]['equipment'][$weapon_id];
        $owner = $this->users[$owner_username];

        //apply tags durability is done inside of this
        $this->recordAction( $owner_username, $target_username, 'weapon', $weapon_id, $this->users[$owner_username]['equipment'][$weapon_id]['name']);
        $this->addTags($this->parseTags($weapon['on_use_tags']), $target_username, $owner_username, parent::WEAPON, $weapon_id, $level, $weapon['targeting_type'], false, $weapon_id);
        $this->users[$owner_username]['equipment'][ $this->users[$owner_username]['equipment'][$weapon_id]['iid'] ]['uses']++;
    }

    //aiCheckWeapon
    //just checks the uses of the weapon to see if it is allowed.
    public function AiCheckWeapon($username, $weapon_id)
    {
        //if weapon has not been used its max amount of times
        if( $this->users[$username]['equipment'][$weapon_id]['uses'] < $this->users[$username]['equipment'][$weapon_id]['max_uses'] || $this->users[$username]['equipment'][$weapon_id]['max_uses'] < 0 )
            return true; //return true

        //if weapon has been used its max amout of times
        else
            return false; //return false
    }

    //aiItem
    //this method is called when a user users an item.
    //this method simply applys the items tags to the target
    public function AiUseItem($target_username, $owner_username, $item_id, $level)
    {
        $item = $this->users[$owner_username]['items'][$item_id];
        $owner = $this->users[$owner_username];

        //apply tags durability is done inside of this
        $this->recordAction( $owner_username, $target_username, 'item', $item_id, $this->users[$owner_username]['items'][$item_id]['name']);
        $this->addTags($this->parseTags($item['on_use_tags']), $target_username, $owner_username, parent::WEAPON, $item_id, $level, $item['targeting_type'], false, $item_id);
        $this->users[$owner_username]['items'][ $this->users[$owner_username]['items'][$item_id]['iid'] ]['uses']++;
    }

    //aiCheckItem
    //just checks the uses of the item to see if it is allowed.
    public function AiCheckItem($username, $item_id)
    {
        //if item has not been used its max number of times
        if( $this->users[$username]['items'][$item_id]['uses'] < $this->users[$username]['items'][$item_id]['max_uses'] || $this->users[$username]['items'][$item_id]['max_uses'] < 0 )
            return true; //return true

        //if item has been used its max number of times
        else
            return false; //return false.
    }

    //UpdateTurnTimer
    //this method is called after a turn is processed to update the turn timer.
    public function UpdateTurnTimer()
    {
        if($_SESSION['uid'] == 2013353)
            $this->turn_timer = time() + 55555; //dont forget to fix jailing on battlepage.php, and cache time tags.
        else
            $this->turn_timer = time() + 31;
    }

    //CheckForInactiveUsers
    //this method looks at all users at the end of a turn
    //if a user has not made an action they are defaulted to basic attack
    public function checkForInactiveUsers()
    {
        //for each user
        foreach($this->users as $username => $user)
        {
            //if the user has not taken an action this turn
            if( !isset($user['actions'][$this->turn_counter]) || ( isset($user['actions'][$this->turn_counter]) && !is_array($user['actions'][$this->turn_counter])))
            {
                //get all possible foes
                $target_username = false;
                $targets = array();
                foreach($this->users as $key => $value)
                {
                    if($user['team'] != $value['team'])
                    {
                        $targets[] = $key;
                    }
                }

                //pick random foe
                if(count($targets) > 0)
                {
                    $target = $targets[ random_int(0, count($targets) - 1 ) ];

                    //select basic attack against selected foe.
                    $this->recordAction( $username, $target, 'jutsu', -1, $this->jutsus[-1]['name']);
                    self::doJutsu( $target , $username, -1);
                }
            }
        }
    }

    //recording event for the quest system
    //function recordEvent($username, $event_type, $event_data, $extra_data = 0)
    //{
    //    if(isset($this->users[$username]))
    //    {
    //        if(!isset($this->users[$username]['events'][$event_type]))
    //            $this->users[$username]['events'][$event_type] = array(array('data'=>$event_data, 'extra'=>$extra_data));
    //        else if(!in_array($event_data, $this->users[$username]['events'][$event_type]))
    //            $this->users[$username]['events'][$event_type][] = array('eata'=>$event_data, 'extra'=>$extra_data);
    //    }
    //    else if(isset($this->removed_users[$username]))
    //    {
    //        if(!isset($this->removed_users[$username]['events'][$event_type]))
    //            $this->removed_users[$username]['events'][$event_type] = array(array('data'=>$event_data, 'extra'=>$extra_data));
    //        else if(!in_array($event_data, $this->removed_users[$username]['events'][$event_type]))
    //            $this->removed_users[$username]['events'][$event_type][] = array('data'=>$event_data, 'extra'=>$extra_data);
    //    }
    //}

    //checkForBattleEnd
    //this method checks for end of battle and processes it
    function checkForBattleEnd()
    {
        $battle_end = true;

        $temp_team = '';

        //check to see if only 1 team is left
        foreach($this->users as $username => $userdata)
        {
            if($temp_team == '')
            {
                $temp_team = $userdata['team'];
            }
            else
            {
                if($temp_team != $userdata['team'])
                {
                    $battle_end = false;
                }
            }
        }

        //check to see if only ai are left
        if($battle_end !== true)
        {
            $battle_end = true;
            foreach($this->users as $username => $userdata)
                if(!isset($userdata['ai']) || $userdata['ai'] === false)
                    $battle_end = false;
        }

        //if only 1 team is left set all current users to win
        if($battle_end === true)
        {
            $users_removed = array();

            foreach($this->users as $username => $userdata)
            {
                $this->removeUserFromCombat($username, true);
                $this->removeUserFromBattle($username);

                if(!isset($userdata['ai']) || $userdata['ai'] === false)
                    $users_removed[] = $username;
            }

            $users = '';

            foreach($users_removed as $user)
            {
                if($users == '')
                    $users = "'".$user."'";
                else
                    $users .= ", '".$user."'";
            }

            if(strlen($users) > 3)
            {
                $query = "UPDATE `users` SET `status` = 'exiting_combat' WHERE `username` in (".$users.")";

                try { $GLOBALS['database']->execute_query($query); }
                catch (Exception $e)
                {
                    try { $GLOBALS['database']->execute_query($query); }
                    catch (Exception $e)
                    {
                        try { $GLOBALS['database']->execute_query($query); }
                        catch (Exception $e)
                        {
                            $GLOBALS['DebugTool']->push('','there was an error updating user status.', __METHOD__, __FILE__, __LINE__);
                            throw $e;
                        }
                    }
                }
            }



            //injection point for anything special that needs to be done at end of combat for users
            $this->endOfCombatInjectionPoint();
            $this->recordBattleHistory();
            $this->recordBalanceData();
        }
    }



    //show battle page
	//manages the view of the battle page.
	function showBattlePage()
	{
		//handle battle panel stuffs
        if( !isset($this->acting_user) || $this->acting_user == "" || $this->acting_user == NULL || isset($this->users[$this->acting_user]) )
        {
            $users = array();
            //building jutsu list
            foreach($this->users as $username => $userdata)
            {
                $users[$username] = array();
                $jutsu_weapon_selects = '';
                $jutsu_weapon_selects_mobile = array();
                $jutsu_weapon_selects_new = array();

                //passing jutsu data to page
                $users[$username]['jutsus'] = array();
                if( isset($userdata['jutsus']) && (($username == $this->findFirstUser( $this->turn_counter, true ) || ( isset($users[$this->acting_user]) && $username == $this->acting_user && $this->checkForAction($this->acting_user, $this->turn_counter) !== true)  )))
                {

                    foreach($userdata['jutsus'] as $jutsu_key => $temp_jutsu_data)
                    {
                        $jutsu_weapon_selects_mobile[$jutsu_key] = '';
                        $jutsu_weapon_selects_new[$jutsu_key] = '';

                        if($jutsu_key != 'cooldowns')
                        {
                            $jutsu_data = array_merge($this->jutsus[$jutsu_key], $temp_jutsu_data);

                            $users[$username]['jutsus'][$jutsu_key] = array_merge($jutsu_data, $this->jutsus[$jutsu_key]);

                            $cooldown = $this->checkJutsuCooldown($jutsu_key, $jutsu_data['cooldown_pool_check'], $username);

                            if($cooldown === false || $cooldown[1] === 0 )
                                $users[$username]['jutsus'][$jutsu_key]['cooldown_status'] = 'off';
                            else
                                $users[$username]['jutsus'][$jutsu_key]['cooldown_status'] = $cooldown[1];

                            $users[$username]['jutsus'][$jutsu_key]['reagent_status'] = $this->checkAndConsumeReagents($username, $jutsu_data['reagents'], false);

                            if(is_array($users[$username]['jutsus'][$jutsu_key]['reagent_status']))
                                $users[$username]['jutsus'][$jutsu_key]['reagent_status'] = true;

                            if($jutsu_data['weapons'] != '')
                            {
                                if(is_array($jutsu_data['weapons']) && isset(array_flip($jutsu_data['weapons'])[$this->users[$username][parent::SPECIALIZATION]]))
                                    $temp = explode(',',(array_flip($jutsu_data['weapons'])[$this->users[$username][parent::SPECIALIZATION]]));
                                else if(is_array($jutsu_data['weapons']) && $this->users[$username][parent::SPECIALIZATION] == 'W' && isset(array_flip($jutsu_data['weapons'])['B']))
                                    $temp = explode(',',(array_flip($jutsu_data['weapons'])['B']));
                                else if(is_array($jutsu_data['weapons']))
                                {
                                    ob_start();
                                    var_dump($jutsu_data);
                                    $result = ob_get_clean();
                                    error_log($result.' if you see this and the above message please pass this on to koala. broken split jutsu data?');
                                }
                                else
                                    $temp = explode(',',$jutsu_data['weapons']);

                                foreach( $temp as $weapon_group_key => $required_weapon_group )
                                {

                                    if(!isset($GLOBALS['mf']) || $GLOBALS['mf'] != 'yes')
                                        $jutsu_weapon_selects .= '<select title="'.$required_weapon_group.'" style="width:100%;border:1px solid black;" class="tableColumns select-wrapper" size="1" name="jutsu_weapon_select-'.$jutsu_key.'-'.$weapon_group_key.'"'.' id ="'.$jutsu_key.'-'.$weapon_group_key.'"><option value="default" disabled>Select A Weapon</option>';
                                    else
                                        $jutsu_weapon_selects .= '<select title="'.$required_weapon_group.'" class="page-drop-down-fill-dark select-wrapper" name="jutsu_weapon_select-'.$jutsu_key.'-'.$weapon_group_key.'"'.' id ="'.$jutsu_key.'-'.$weapon_group_key.'"><option value="default" disabled>Select A Weapon</option>';

                                    if(!isset($GLOBALS['mf']) || $GLOBALS['mf'] != 'yes')
                                        $jutsu_weapon_selects_new[$jutsu_key] = '<select title="'.$required_weapon_group.'" style="width:100%;border:1px solid black;" class="tableColumns select-wrapper" size="1" name="jutsu_weapon_select-'.$jutsu_key.'-'.$weapon_group_key.'"'.' id ="'.$jutsu_key.'-'.$weapon_group_key.'"><option value="default" disabled>Select A Weapon</option>';
                                    else
                                        $jutsu_weapon_selects_new[$jutsu_key] = '<select title="'.$required_weapon_group.'" class="page-drop-down-fill-dark select-wrapper" name="jutsu_weapon_select-'.$jutsu_key.'-'.$weapon_group_key.'"'.' id ="'.$jutsu_key.'-'.$weapon_group_key.'"><option value="default" disabled>Select A Weapon</option>';

                                    $jutsu_weapon_selects_mobile[$jutsu_key] .= '<select name="jutsu_weapon_select-'.$jutsu_key.'-'.$weapon_group_key.'">';
                                    $count = 0;

                                    $first_weapon = true;
                                    foreach( $userdata['equipment'] as $equipment_id => $equipment_data )
                                    {

                                        $set_this_weapon = true;

                                        $weapon_types = explode(',', $equipment_data['weapon_classifications']);

                                        if($set_this_weapon === true)
                                        {
                                            if( is_numeric($required_weapon_group) )
                                            {
                                                if($required_weapon_group != $equipment_data['iid'])
                                                    $set_this_weapon = false;
                                            }

                                            else
                                            {
                                                foreach( explode('/',$required_weapon_group) as $required_type )
                                                    if(!in_array($required_type, $weapon_types))
                                                        $set_this_weapon = false;
                                            }
                                        }


                                        //checking element type
                                        if($set_this_weapon === true)
                                        {
                                            //getting elements for jutsu and weapon

                                            $jutsu_element =  $jutsu_data['element'];
                                            $weapon_element = $equipment_data['element'];

                                            if( $weapon_element == '' || $weapon_element == null)
                                                $weapon_element = 'None';

                                            //check to make sure that you are past the em thresh hold to allow elemental weapon use.
                                            if( $jutsu_element != 'None' && $weapon_element != 'None' && $weapon_element != 'none' && $weapon_element != 'N' && !$jutsu_data['allow_elemental_weapons'] )
                                                $set_this_weapon = false;//flag that this weapon can not be used

                                            //if elements do not match...
                                            if($jutsu_element != $weapon_element && $weapon_element != 'None' && $weapon_element != 'none' && $weapon_element != 'N')
                                                //if jutsu_element is not a key in element heritage aka it is a base element...
                                                if(!isset($this->ELEMENTHERITAGE[$jutsu_element]))
                                                    $set_this_weapon = false;//flag that this weapon can not be used

                                                //otherwise check to make sure that the jutsu's element is a child of the weapon's elements...
                                                else if(!in_array($weapon_element, $this->ELEMENTHERITAGE[$jutsu_element]))
                                                    $set_this_weapon = false;//flag that this weapon can not be used
                                        }

                                        if($set_this_weapon === true)
                                        {
                                            $uses_left = ( $this->users[$username]['equipment_used'][ $this->users[$username]['equipment'][$equipment_id]['iid'] ]['max_uses'] != -1 ? ($this->users[$username]['equipment_used'][ $this->users[$username]['equipment'][$equipment_id]['iid'] ]['max_uses'] - $this->users[$username]['equipment_used'][ $this->users[$username]['equipment'][$equipment_id]['iid'] ]['uses']) : 999);
                                            $count++;
                                            //
                                            if($first_weapon === true)
                                            {
                                                if( $uses_left > 0 )
                                                {
                                                    $jutsu_weapon_selects .= '<option class="default" title="uses left: '.$uses_left.'" value="'.$equipment_id.'" class="'.$equipment_id.'">'.$equipment_data['name'].'</option>';
                                                    $jutsu_weapon_selects_new[$jutsu_key] .= '<option class="default" value="'.$equipment_id.'" class="'.$equipment_id.'">'.$equipment_data['name'].($uses_left <= 5 ? " (uses left: {$uses_left})" : '').'</option>';
                                                    $jutsu_weapon_selects_mobile[$jutsu_key] .= '<option value="'.$equipment_id.'">'.$equipment_data['name'].'</option>';
                                                    $first_weapon = false;
                                                }
                                                else
                                                {
                                                    $jutsu_weapon_selects .= '<option disabled title="uses left: '.$uses_left.'" value="'.$equipment_id.'" class="'.$equipment_id.'">'.$equipment_data['name'].'</option>';
                                                    $jutsu_weapon_selects_new[$jutsu_key] .= '<option disabled value="'.$equipment_id.'" class="'.$equipment_id.'">'.$equipment_data['name'].($uses_left <= 5 ? " (uses left: {$uses_left})" : '').'</option>';
                                                    $jutsu_weapon_selects_mobile[$jutsu_key] .= '<option disabled value="'.$equipment_id.'">'.$equipment_data['name'].'</option>';
                                                }
                                            }
                                            else
                                            {
                                                if( $uses_left > 0 )
                                                {
                                                    $jutsu_weapon_selects .= '<option title="uses left: '.$uses_left.'" value="'.$equipment_id.'" class="'.$equipment_id.'">'.$equipment_data['name'].'</option>';
                                                    $jutsu_weapon_selects_new[$jutsu_key] .= '<option value="'.$equipment_id.'" class="'.$equipment_id.'">'.$equipment_data['name'].($uses_left <= 5 ? " (uses left: {$uses_left})" : '').'</option>';
                                                    $jutsu_weapon_selects_mobile[$jutsu_key] .= '<option value="'.$equipment_id.'">'.$equipment_data['name'].'</option>';
                                                }
                                                else
                                                {
                                                    $jutsu_weapon_selects .= '<option disabled title="uses left: '.$uses_left.'" value="'.$equipment_id.'" class="'.$equipment_id.'">'.$equipment_data['name'].'</option>';
                                                    $jutsu_weapon_selects_new[$jutsu_key] .= '<option disabled value="'.$equipment_id.'" class="'.$equipment_id.'">'.$equipment_data['name'].($uses_left <= 5 ? " (uses left: {$uses_left})" : '').'</option>';
                                                    $jutsu_weapon_selects_mobile[$jutsu_key] .= '<option disabled value="'.$equipment_id.'">'.$equipment_data['name'].'</option>';
                                                }
                                            }
                                        }

                                    }

                                    if($count == 0)
                                    {
                                        $jutsu_weapon_selects_mobile[$jutsu_key] .= 'no-weapon';
                                    }

                                    $jutsu_weapon_selects .= '</select>';
                                    $jutsu_weapon_selects_new[$jutsu_key] .= '</select>';
                                    $jutsu_weapon_selects_mobile[$jutsu_key] .= '</select>';
                                }
                            }
                        }
                    }
                }


                $users[$username]['health'] = $userdata['health'];
                $users[$username]['healthMax'] = $userdata['healthMax'];


                if(isset($userdata['stamina']))
                    $users[$username]['stamina'] = $userdata['stamina'];
                if(isset($userdata['staminaMax']))
                    $users[$username]['staminaMax'] = $userdata['staminaMax'];
                if(isset($userdata['chakra']))
                    $users[$username]['chakra'] = $userdata['chakra'];
                if(isset($userdata['chakraMax']))
                    $users[$username]['chakraMax'] = $userdata['chakraMax'];
                if(isset($userdata['equipment']))
                    $users[$username]['equipment'] = $userdata['equipment'];
                if(isset($userdata['equipment_used']))
                    $users[$username]['equipment_used'] = $userdata['equipment_used'];
                if(isset($userdata['avatar']))
                    $users[$username]['avatar'] = $userdata['avatar'];
                if(isset($userdata['display_rank']))
                    $users[$username]['display_rank'] = $userdata['display_rank'];
                if(isset($userdata['rank']))
                    $users[$username]['rank'] = $userdata['rank'];
                if(isset($userdata['village']))
                    $users[$username]['village'] = $userdata['village'];
                if(isset($userdata['B']))
                    $users[$username]['bloodline'] = $userdata['B'];

                $users[$username]['ai'] = $userdata['ai'];

                if(!isset($userdata['show_count']))
                    $users[$username]['show_count'] = 'yes';

                else
                    $users[$username]['show_count'] = $userdata['show_count'];

                if(isset($userdata['items']))
                {
                    $users[$username]['items'] = $userdata['items'];

                    if(isset($userdata['items_used']))
                        $users[$username]['items_used'] = $userdata['items_used'];
                }
                else
                {
                    $users[$username]['items'] = array();
                    $users[$username]['items_used'] = array();
                }

                $users[$username]['team'] = $userdata['team'];

                $users[$username][parent::ELEMENTMASTERIES] = $userdata[parent::ELEMENTMASTERIES];

                $users[$username][parent::ELEMENTS] = $userdata[parent::ELEMENTS];

                if(isset($jutsu_weapon_selects))
                    $users[$username]['jutsu_weapon_selects'] = $jutsu_weapon_selects;

                    if(isset($jutsu_weapon_selects_new))
                    $users[$username]['jutsu_weapon_selects_new'] = $jutsu_weapon_selects_new;

                if(isset($jutsu_weapon_selects_mobile))
                {
                    $users[$username]['jutsu_weapon_selects_mobile'] = $jutsu_weapon_selects_mobile;

                    ob_start();
                    var_dump($jutsu_weapon_selects_mobile);
                    $users[$username]['testing'] = ob_get_clean();
                }

                $users[$username]['DSR'] = $userdata['DSR'];

                if(isset($userdata['attacker']))
                    $users[$username]['attacker'] = $userdata['attacker'];

                if(isset($userdata['no_cfh']))
                    $users[$username]['no_cfh'] = $userdata['no_cfh'];

                if(isset($userdata['status_effects']['disabled']))
                    $users[$username]['disabled'] = $userdata['status_effects']['disabled'];

                if(isset($userdata['status_effects']['staggered']))
                    $users[$username]['staggered'] = $userdata['status_effects']['staggered'];

                if(isset($userdata['status_effects']['stunned']))
                    $users[$username]['stunned'] = $userdata['status_effects']['stunned'];
            }

            if( $this->acting_user != '' && isset($users[$this->acting_user]))
            {
                $temp = $users[ $this->acting_user ];
                $temp['name'] = $this->acting_user;
                $temp['waiting_for_next_turn'] = $this->checkForAction($this->acting_user, $this->turn_counter);


                if( !isset($this->users[ $this->acting_user ]['status_effects']['stunned']) || $this->users[$this->acting_user]['status_effects']['stunned'] == 0 )
                    $GLOBALS['template']->assign('stunned',false);
                else
                    $GLOBALS['template']->assign('stunned',$this->users[ $this->acting_user ]['status_effects']['stunned']);

                if( !isset($this->users[ $this->acting_user ]['status_effects']['disabled']) || $this->users[$this->acting_user]['status_effects']['disabled'] == 0 )
                    $GLOBALS['template']->assign('no_jutsu',false);
                else
                    $GLOBALS['template']->assign('no_jutsu',$this->users[ $this->acting_user ]['status_effects']['disabled']);
            }
            else
            {
                $temp = $users[ $this->findFirstUser( $this->turn_counter ) ];
                $temp['name'] = $this->findFirstUser( $this->turn_counter );
            }

            $GLOBALS['template']->assign('owner',$temp);

            $GLOBALS['template']->assign('users',$users);

            $GLOBALS['template']->assign('battle_log', $this->battle_log);

            $GLOBALS['template']->assign('turn_counter', $this->turn_counter);

            $GLOBALS['template']->assign('turn_timer', $this->turn_timer);

            $DSR = array();
            foreach($this->users as $temp_user_data)
            {
                if(!isset($DSR[$temp_user_data['team']]))
                    $DSR[$temp_user_data['team']] = 0;

                $DSR[$temp_user_data['team']] += $temp_user_data['DSR'];
            }

            $friendlyDSR = $DSR[$temp['team']];

            unset($DSR[$temp['team']]);

            if(count($DSR) > 0)
                $opponentDSR = max($DSR);
            else
                $opponentDSR = 0;

            $GLOBALS['template']->assign('friendlyDSR',$friendlyDSR);

            $GLOBALS['template']->assign('opponentDSR',$opponentDSR);

            //finding call for help range for display

            $DSR = array();
            foreach($this->users as $temp_user_data)
            {
                if(!isset($DSR[$temp_user_data['team']]))
                    $DSR[$temp_user_data['team']] = 0;

                $DSR[$temp_user_data['team']] += $temp_user_data['update']['starting_dsr'];
            }

            $friendlyDSR = $DSR[$temp['team']];

            unset($DSR[$temp['team']]);

            if(count($DSR) > 0)
                $opponentDSR = max($DSR);
            else
                $opponentDSR = 0;

            $gap = $opponentDSR - $friendlyDSR;

            if($gap <= 0 || $this->no_cfh || ($gap / $opponentDSR) <= 0.15 )
            {
                $GLOBALS['template']->assign('cfhRange1','N/A');
                $GLOBALS['template']->assign('cfhRange2','N/A');
            }
            else
            {
                $GLOBALS['template']->assign('cfhRange1', ($gap * 0.55173));
                $GLOBALS['template']->assign('cfhRange2', ($gap * 0.84784));
            }

            $GLOBALS['template']->assign('rng', $this->rng);

            $GLOBALS['template']->assign('no_flee', $this->no_flee);
            $GLOBALS['template']->assign('no_cfh', $this->no_cfh);
            $GLOBALS['template']->assign('link_code', $this->users[$this->acting_user]['link_code']);

	        if($this->debugging)
	        {
	        $this_dump =
	        '<details>'.
	        '<summary>all data</summary>'.
	        '<pre>'.
	        var_export($this, true).
	        '</pre>'.
	        '</details>'.
	        '<br>'.
	        '<br>';

	        $GLOBALS['template']->assign('this_dump',$this_dump);

	        $users_dump =
	        '<details>'.
	        '<summary>users-data</summary>'.
	        '<pre>'.
	        var_export($this->users, true).
	        '</pre>'.
	        '</details>'.
	        '<br>'.
	        '<br>';

	        $GLOBALS['template']->assign('kill_button','<input type="submit" name="button" value="killBattle">');

	        $GLOBALS['template']->assign('users_dump',$users_dump);
	        }

            //adding template
            $GLOBALS['template']->assign('turn_log_length',$GLOBALS['userdata'][0]['turn_log_length']);

            $GLOBALS['template']->assign('contentLoad', './templates/content/Battle/BattlePageNew.tpl');
        }
        //if the user has been removed show them the summary page
        else if( (!isset($this->users[$this->acting_user]) && $this->removed_users[$this->acting_user]) || $this->turn_counter == -1 )
        {
            $owner = array();
            $owner['name'] = $this->acting_user;

            $owner['team'] = $this->removed_users[$this->acting_user]['team'];

            $owner['win_lose'] = $this->removed_users[$this->acting_user]['win_lose'];

            if(isset($this->removed_users[$this->acting_user]['flee']))
                $owner['flee'] = $this->removed_users[$this->acting_user]['flee'];

            $GLOBALS['template']->assign('owner', $owner);

            $GLOBALS['template']->assign('village', $this->removed_users[$this->acting_user]['village']); //if changes should be hidden

            //this very compact line calls for the processing of end of combat for the user and collects..
            //..all the changes made to the user, it then takes those changes and assigns them to the template..
            //..for viewing on the summary page.

            $GLOBALS['template']->assign('changes', $changes = $this->processEndOfCombatForUser($this->acting_user));

            if( ( !isset( $changes['kage_replaced'] )              || $changes['kage_replaced'] !== false || $owner['team'] != 'kage' ) &&
				( !isset( $changes['kage_replaced'] )              || $changes['kage_replaced'] !== true  || $owner['team'] != 'challenger' ) &&
                ( !isset( $changes['clan_replaced'] )              || $changes['clan_replaced'] !== false || $owner['team'] != 'leader' ) &&
				( !isset( $changes['clan_replaced'] )              || $changes['clan_replaced'] !== true  || $owner['team'] != 'challenger' ) &&
				( !isset( $changes['pvp_experience'] )             || $changes['pvp_experience']             == NULL ) &&
				( !isset( $changes['pvp_streak'] )                 || $changes['pvp_streak']                 == NULL || $changes['pvp_streak'] === false ) &&
				( !isset( $changes['clan'] )                       || $changes['clan']                       == NULL ) &&
				( !isset( $changes['anbu'] )                       || $changes['anbu']                       == NULL ) &&
				( !isset( $changes['village_points'] )             || $changes['village_points']             == NULL ) &&
				( !isset( $changes['jutsus']['level'] )            || count($changes['jutsus']['level'] )    == 0 ) &&
				( !isset( $changes['jutsus']['exp'] )              || count($changes['jutsus']['exp'] )      == 0 ) &&
				( !isset( $changes['exp'] )                        || $changes['exp']                        == NULL ) &&
				( !isset( $changes['bounty'] )                     || $changes['bounty']                     == NULL ) &&
				( !isset( $changes['bounty_exp'] )                 || $changes['bounty_exp']                 == NULL ) &&
                ( !isset( $changes['health_gain'] )                || $changes['health_gain']                == NULL ) &&
                ( !isset( $changes['gen_pool_gain'] )              || $changes['gen_pool_gain']              == NULL ) &&
                ( !isset( $changes['ryo_gain'] )                   || $changes['ryo_gain']                   == NULL ) &&
                ( !isset( $changes['torn'] )                       || $changes['torn']                       == NULL || $changes['torn'] == false ) &&
                ( !isset( $changes['territory_battle_result'] )    || $changes['territory_battle_result']    == NULL || $changes['territory_battle_result'] != $owner['team'] ) &&
                ( !isset( $changes['territory_challenge_result'] ) || $changes['territory_challenge_result'] == NULL || $changes['territory_challenge_result'] != $owner['team'] )  &&
                ( !isset( $changes['money'] )                      || $changes['money']                      == NULL || $changes['money'] <= 0 ) )

                $GLOBALS['template']->assign('no_positive_changes', true);
            else
                $GLOBALS['template']->assign('no_positive_changes', false);

            $durability_warning = false;
            if(isset( $changes['durability']) )
            {
                $items_for_notification = array();
                foreach($changes['durability'] as $name => $amount)
                {
                    if($amount <= 50)
                    {
                        $durability_warning = true;
                        $items_for_notification[] = $name;
                    }
                }

                if(count($items_for_notification) > 0)
                {
                    $GLOBALS['NOTIFICATIONS']->addNotification(array(
                        'id' => 25,
                        'duration' => 'none',
                        'text' => count($items_for_notification) ? "This item '".$items_for_notification[0]."' has low durability." : "These items '".implode("','",$items_for_notification)."' have low durability.",
                        'dismiss' => 'yes'
                    ));
                }
            }

            if( ( !isset( $changes['kage_replaced'] )              || $changes['kage_replaced'] !== true  || $owner['team'] != 'kage' ) &&
				( !isset( $changes['kage_replaced'] )              || $changes['kage_replaced'] !== false || $owner['team'] != 'challenger' ) &&
                ( !isset( $changes['clan_replaced'] )              || $changes['clan_replaced'] !== true  || $owner['team'] != 'leader' ) &&
				( !isset( $changes['clan_replaced'] )              || $changes['clan_replaced'] !== false || $owner['team'] != 'challenger' ) &&
				( !isset( $changes['jailed'] )                     || $changes['jailed']                     != true ) &&
				( !isset( $changes['turn_outlaw'] )                || $changes['turn_outlaw']                != true ) &&
				( !isset( $changes['heal_time'] )                  || $changes['heal_time']                  == NULL ) &&
				( !isset( $changes['diplomacy'] )                  || $changes['diplomacy']                  == NULL ) &&
				( !isset( $changes['loyalty'] )                    || $changes['loyalty']                    == NULL ) &&
				( !isset( $changes['pvp_streak'] )                 || $changes['pvp_streak']                !== false ) &&
				( !isset( $changes['remove'] )                     || count($changes['remove'])              == 0 ) &&
				( !isset( $changes['durability'] )                 || count($changes['durability'])          == 0 || !$durability_warning) &&
				( !isset( $changes['stack']  )                     || $changes['stack']                      == NULL || max($changes['stack']) > 5) &&
                ( !isset( $changes['torn'])                        || $changes['torn']                       == NULL || $changes['torn'] == true ) &&
                ( !isset( $changes['territory_battle_result'] )    || $changes['territory_battle_result']    == NULL || $changes['territory_battle_result'] == $owner['team'] ) &&
                ( !isset( $changes['territory_challenge_result'] ) || $changes['territory_challenge_result'] == NULL || $changes['territory_challenge_result'] == $owner['team'] )  &&
                ( !isset( $changes['money'] )                      || $changes['money']                      == NULL || $changes['money'] >= 0 ) &&
                ( !isset( $changes['bounty_collected'])            || $changes['bounty_collected']           == NULL) )

                $GLOBALS['template']->assign('no_negative_changes', true);
            else
                $GLOBALS['template']->assign('no_negative_changes', false);


            $GLOBALS['template']->assign('battle_log', $this->battle_log);

            $GLOBALS['template']->assign('return_id', $this->getReturnId($changes['hospital']));

            $GLOBALS['template']->assign('return_name', $this->getReturnName($changes['hospital']));

            $GLOBALS['template']->assign('time', date('jS \of F Y h:i:s A'));

            $GLOBALS['template']->assign('userStatus', 'awake');

            //adding template
            $GLOBALS['template']->assign('contentLoad', './templates/content/Battle/BattleSummary.tpl');
        }
        //should never be able to reach this.
        else
        {
            //echo '<pre>';
            //var_dump(
            //    array(
            //        'if_result'=> ( !isset($this->acting_user) || $this->acting_user == "" || $this->acting_user == NULL || isset($this->users[$this->acting_user]) ),
            //        'if_report'=> "( ".!isset($this->acting_user)." || ".($this->acting_user == "")." || ".($this->acting_user == NULL)." || ".isset($this->users[$this->acting_user])." )",
            //        'else_if_result' => ( (!isset($this->users[$this->acting_user]) && $this->removed_users[$this->acting_user]) || $this->turn_counter == -1 ),
            //        'else_if_report' => "( (".!isset($this->users[$this->acting_user])." && ".$this->removed_users[$this->acting_user].") || ".($this->turn_counter == -1)." )"
            //    )
            //);
            //echo'</pre>';
        }
	}


   //processEndOfCombatForUser
    //this method updates the users database entries and...
    //this method returns an array detailing all the changes made.
    function processEndOfCombatForUser($username)
    {
        $this->updateInventory = false;

        try
        {
            //starting transaction
            $GLOBALS['database']->transaction_start();

            $this->return_data = array();

            $uid = $this->removed_users[$username]['uid'];

            $this->timeStamp = $GLOBALS['user']->load_time;

            //universal updates
            {
                //building battle information for query
                $set = $this->buildingBattleInformationUpdate($username);

                //building pool update information for query
                $set .= $this->buildingPoolUpdate($username);

                //building jutsu update information for query
                $set .= $this->buildingJutsuUpdate($username);

                //building durability update information for query
                $set .= $this->buildingDurabilityUpdate($username);

                //building stack update information for query
                $set .= $this->buildingStackUpdate($username);

                //building times used update information for query
                $set .= $this->buildingTimesUsedUpdate($username);

                //building exp update information for query
                $set .= $this->buildingExpUpdate($username);

                //building money update information for user
                $set .= $this->buildingMoneyUpdate($username);

                //update for traitor
                if(isset($this->removed_users[$username]['update']['traitor']) && $this->removed_users[$username]['update']['traitor'] !== false)
                {
                    //building loyalty update for query
                    $set .= $this->buildingLoyaltyUpdateForTraitor($username);

                    //bulding rep loss update for query
                    $set .= $this->buildingDiplomacyUpdateForTraitor($username);

                    //checking to see if the user should be made outlaw
                    if($this->removed_users[$username]['village'] != "Syndicate")
                    {
                        $this->checkDiplomacyForOutlawConversion($username);
                    }

                }
                else //update for non traitor
                {
                    //bulding rep loss update for query
                    if(isset($this->removed_users[$username]['attacker']) && $this->removed_users[$username]['attacker'] === true && $this->removed_users[$username]['team'] != 'Honorless')
                    {
                        $set .= $this->buildingDiplomacyUpdateForNonTraitor($username);
                    }
                }
            }



            //updates for win
            if($this->removed_users[$username]['win_lose'] === true && $this->removed_users[$username]['health'] > 0 )
            {
                //building status update information for query
                $set .= $this->buildingStatusUpdateForWin($username);

                //if bounty hunter update bounty hunter information for query
                if( $this->removed_users[$username]['bounty_hunter'] !== false && $this->removed_users[$username]['bounty_hunter'] != '' &&
                    isset($this->removed_users[$username]['update']['bounty_hunter']) )
                {
                    $set .= $this->buildingBountyHunterUpdateForWin($username);
                }

                //update for traitor
                if(isset($this->removed_users[$username]['update']['traitor']) && $this->removed_users[$username]['update']['traitor'] !== false)
                {/*nothing here yet*/}

                //update for non traitor
                else
                {
                    //building clap update information for query
                    $set .= $this->buildingClanUpdateForWinAndNonTraitor($username);

                    //building anbu update for query
                    $set .= $this->buildingAnbuUpdateForWinAndNonTraitor($username);

                    //building pvp exp update for query
                    $set .= $this->buildingPvpExpUpdateForWinAndNonTraitor($username);

                    //building village points update for query
                    $set .= $this->buildingVillagePointsUpdateForWinAndNonTraitor($username);

                }
            }



            //updates for loss
            else
            {
                //building status update information for query
                if( isset($this->removed_users[$username]['flee']) && $this->removed_users[$username]['flee'] == true)
                    $set .= $this->buildingStatusUpdateForWin($username); //special case for fleeing user.
                else
                    $set .= $this->buildingStatusUpdateForLoss($username);

                //building update for when a users bounty has been collected
                if( isset($this->removed_users[$username]['update']['bounty_collected']))
                    $set .= $this->buildingBountyCollectedUpdate($username);

                //building pvp_exp update information for query if this is a pvp match
                $set .= $this->buildingPvpExpUpdateForLoss($username);

                //update for traitor
                if(isset($this->removed_users[$username]['update']['traitor']) && $this->removed_users[$username]['update']['traitor'] !== false)
                {/*nothing here yet*/}

                else
                {
                    //building clap update information for query
                    $set .= $this->buildingClanUpdateForLoss($username);

                    //building anbu update for query
                    $set .= $this->buildingAnbuUpdateForLoss($username);

                    //building village points update for query
                    $set .= $this->buildingVillagePointsUpdateForLoss($username);
                }
            }


/////////////////////////////////////////////////////////////////////////////////////
            //next_battle
/////////////////////////////////////////////////////////////////////////////////////

            //building update query
            $query = "UPDATE
                `users`,
                `users_statistics`,
                `users_missions`,
                `users_timer`,
                `bingo_book`,
                `users_loyalty`,
                `users_occupations`,
                `villages`";

            if(isset($this->updateJutsu) && $this->updateJutsu === true)
                $query .= ",`users_jutsu` ";

            if($this->updateInventory === true)
                $query .= ",`users_inventory` ";

            //update template for clans if need be
            if( ($this->removed_users[$username]['win_lose'] === true || (isset($this->removed_users[$username]['update']['users_killed']) && count($this->removed_users[$username]['update']['users_killed']) != 0)) && $this->removed_users[$username]['clan'] !== false && isset($this->removed_users[$username]['clan_updated']) && $this->removed_users[$username]['clan_updated'] == true)
                $query .= ', `clans` ';

            //update template for anbu if need be
            if( ($this->removed_users[$username]['win_lose'] === true || (isset($this->removed_users[$username]['update']['users_killed']) && count($this->removed_users[$username]['update']['users_killed']) != 0)) && $this->removed_users[$username]['anbu'] !== false && isset($this->removed_users[$username]['anbu_updated']) && $this->removed_users[$username]['anbu_updated'] == true)
                $query .= ', `squads` ';

            $query .=

            "SET
                 ".$set."
             WHERE
                 `users`.`id` = '".$uid."' AND
                 `users`.`id` = `users_statistics`.`uid` AND
                 `users`.`id` = `users_missions`.`userid` AND
                 `users`.`id` = `users_timer`.`userid` AND
                 `users`.`id` = `users_loyalty`.`uid` AND
                 `users`.`id` = `bingo_book`.`userid` AND
                 `users`.`id` = `users_occupations`.`userid` AND
                 `users`.`village` = `villages`.`name` ";

            if(isset($this->updateJutsu) && $this->updateJutsu === true)
                $query .= " AND `users`.`id` = `users_jutsu`.`uid` ";

            if($this->updateInventory === true)
                $query .= " AND `users`.`id` = `users_inventory`.`uid` ";

            //update template for clans if need be
            if($this->removed_users[$username]['win_lose'] === true && $this->removed_users[$username]['clan'] !== false && isset($this->removed_users[$username]['clan_updated']) && $this->removed_users[$username]['clan_updated'] == true)
                $query .= ' AND `clans`.`id` = ' . $this->removed_users[$username]['clan'];

            //update template for anbu if need be
            if($this->removed_users[$username]['win_lose'] === true && $this->removed_users[$username]['anbu'] !== false && isset($this->removed_users[$username]['anbu_updated']) && $this->removed_users[$username]['anbu_updated'] == true)
                $query .= ' AND `squads`.`id` = ' . $this->removed_users[$username]['anbu'];

            //sending query to database to updated user status and location/lat/lon
            try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed'); }
            catch (Exception $e)
            {
                try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                catch (Exception $e)
                {
                    try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                    catch (Exception $e)
                    {
                            ob_start();
                            var_dump($query);
                            $result = ob_get_clean();
                            error_log($result);


                            throw new exception($e);
                    }
                }
            }

            //remove items
            if(isset($this->removed_users[$username]['update']['remove']) && is_array($this->removed_users[$username]['update']['remove']))
            {
                $this->return_data['remove'] = $this->removed_users[$username]['update']['remove'];

                $items = $GLOBALS['database']->fetch_data('SELECT * FROM `users_inventory` WHERE `uid` = '.$_SESSION['uid'].' AND `iid` IN ('.implode(',',array_keys($this->removed_users[$username]['update']['remove_iid'])).')');

                $query = 'DELETE FROM `users_inventory` WHERE `id` IN ('.implode(',',array_keys($this->removed_users[$username]['update']['remove'])).')';
                try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed to update user information'); }
                catch (Exception $e)
                {
                    try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed to update user information'); }
                    catch (Exception $e)
                    {
                        try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed to update user information'); }
                        catch (Exception $e)
                        {
                            $GLOBALS['DebugTool']->push('','there was an error updating user status.', __METHOD__, __FILE__, __LINE__);
                            throw $e;
                        }
                    }
                }


                foreach($this->removed_users[$username]['update']['remove_iid'] as $iid => $iid_alt)
                {
                    $quantity = 0;
                    $stacks = 0;
                    $quantity_removed = 0;

                    foreach($items as $item)
                    {
                        if($item['iid'] == $iid)
                        {
                            $quantity += $item['stack'];
                            $stacks++;

                            if(in_array($item['id'], array_keys($this->removed_users[$username]['update']['remove'])))
                                $quantity_removed += $item['stack'];
                        }
                    }

                    //$this->recordEvent($owner_username, 'item_person', '!'.$iid);
                    $GLOBALS['Events']->acceptEvent('item_person', array('data'=>'!'.$iid, 'context'=>$iid, 'new'=>$stacks-1, 'old'=>$stacks ));
                    $GLOBALS['Events']->acceptEvent('item_quantity_loss', array('context'=>$iid, 'new'=>$quantity-$quantity_removed, 'old'=>$quantity ));
                }
            }


            //move user to outlaw if flaged
            if(isset($this->return_data['turn_outlaw']) && $this->return_data['turn_outlaw'] == true)
            {
                $GLOBALS['page']->Message( 'Traitor' , 'Leaving Village');
                $this->turn_outlaw($username);
            }

            //grabing extra data compiled elsewhere
            if(isset($this->removed_users[$username]['update']['torn']))
            {
                $this->return_data['torn'] = $this->removed_users[$username]['update']['torn'];
                $this->return_data['torn_record'] = $this->removed_users[$username]['update']['torn_record'];
                $this->return_data['torn_attempt'] = $this->removed_users[$username]['update']['torn_attempt'];
            }

            if(isset($this->removed_users[$username]['update']['territory_battle_result']))
            {
                $this->return_data['territory_battle_result'] = $this->removed_users[$username]['update']['territory_battle_result'];
                $this->return_data['territory_battle_rank'] = $this->removed_users[$username]['update']['territory_battle_rank'];
            }

            if(isset($this->removed_users[$username]['update']['territory_challenge_result']))
            {
                $this->return_data['territory_challenge_result'] = $this->removed_users[$username]['update']['territory_challenge_result'];
                $this->return_data['territory_challenge_location'] = $this->removed_users[$username]['update']['territory_challenge_location'];
            }

            //removing user from log and destroying cache if all users have been processed.
            unset($this->removed_users[$username]);
            $this->updateCache();

            if(count($this->removed_users) == 0)
            {
                $count = 0;
                if(count($this->users) > 0)
                    foreach($this->users as $userdata)
                        if(!isset($userdata['ai']) || $userdata['ai'] === false)
                            $count++;

                if($count == 0)
                {
                    try
                    {
                        if(isset($GLOBALS['cache']) && $GLOBALS['cache'] !== false)
                        {
                            $GLOBALS['cache']->delete(Data::$target_site.$GLOBALS['userdata'][0]['battle_id'].self::TAGS);
                        }

                        $GLOBALS['database']->execute_query('DELETE FROM `battle_fallback` WHERE `id` = '.$GLOBALS['userdata'][0]['battle_id']);
                    }
                    catch (Exception $e)
                    {
                        throw new Exception('there was an issue purging the cache.');
                    }
                }
            }

            $update_query =    'UPDATE `battle_history` '.
                               'SET `changes` = CONCAT(`changes`, "'.$username.'|'.base64_encode(gzdeflate(serialize($this->return_data),6)).'~") '.
                               'WHERE `id` = '.$this->battle_history_id;

            try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception('query failed'); }
            catch (Exception $e)
            {
                try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception ('query failed'); }
                catch (Exception $e)
                {
                    try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception ('query failed to update user information'); }
                    catch (Exception $e)
                    {
                        $GLOBALS['DebugTool']->push($update_query,'there was an error inserting new battle history.', __METHOD__, __FILE__, __LINE__);
                    }
                }
            }

            $GLOBALS['database']->transaction_commit();

            return $this->return_data;
        }
        catch (Exception $e)
        {
            $GLOBALS['database']->transaction_rollback();
            $GLOBALS['userdata'][0]['status'] = 'exiting_combat';
            $GLOBALS['template']->assign('userStatus', 'exiting_combat');
            //$GLOBALS['DebugTool']->push('','there was an error updating user information. please try again.', __METHOD__, __FILE__, __LINE__);
            throw $e;
            return false;
        }
    }

    //simply builds the part of the query that updates reputation for traitors
    function buildingDiplomacyUpdateForTraitor($username)
    {
        $this->return_data['diplomacy'] = array( 'amount'=>($this->removed_users[$username]['diplomacy'] / 2) + 2500, 'village'=>$this->removed_users[$username]['village']);
        $GLOBALS['Events']->acceptEvent('diplomacy_loss', array('new'=>$this->removed_users[$username]['diplomacy'] - $this->return_data['diplomacy']['amount'], 'old'=>$this->removed_users[$username]['diplomacy'], 'context'=>$this->removed_users[$username]['village']));
        $this->removed_users[$username]['diplomacy'] -= $this->return_data['diplomacy']['amount'];
        return ', `'.$this->removed_users[$username]['village'].'` = '.$this->removed_users[$username]['diplomacy'];
    }

    //simply builds the part of the query that updates reptuation for non traitors
    function buildingDiplomacyUpdateForNonTraitor($username)
    {
        if(isset($this->removed_users[$username]['update']['opponents_allegiance']))
        {
            $this->return_data['diplomacy'] = array( 'amount'=>random_int(50,575), 'village'=>$this->removed_users[$username]['update']['opponents_allegiance']);
            $GLOBALS['Events']->acceptEvent('diplomacy_loss', array('new'=>$this->removed_users[$username]['diplomacy'] - $this->return_data['diplomacy']['amount'], 'old'=>$this->removed_users[$username]['diplomacy'], 'context'=>$this->removed_users[$username]['update']['opponents_allegiance']));
            $this->removed_users[$username]['diplomacy'] -= $this->return_data['diplomacy']['amount'];
            return ', `'.$this->removed_users[$username]['update']['opponents_allegiance'].'` = `'.$this->removed_users[$username]['update']['opponents_allegiance'].'` - '.$this->return_data['diplomacy']['amount'];
        }
        else
            return '';
    }

    //this checks diplomacy to see if the user should become an outlaw
    //if they should become an outlaw make it so.
    function checkDiplomacyForOutlawConversion($username)
    {
        if ($this->removed_users[$username]['diplomacy'] < 0)
        {
            $this->return_data['turn_outlaw'] = true;
            $GLOBALS['userdata'][0]['village'] = 'Syndicate';
        }
    }

    function turn_outlaw($username)
    {
        $GLOBALS['userdata'][0]['village'] = 'Syndicate';

        // Turn outlaw
        require_once(Data::$absSvrPath.'/libs/villageSystem/respectLib.php');
        $respectLib = new respectLib();
        $respectLib->turn_outlaw( $_SESSION['uid'] , true );
        // Log the change
        functions::log_village_changes
        (
            $_SESSION['uid'],
            $this->removed_users[$username]['village'],
            "Syndicate",
            "Kicked out of village after battle. Had ".$this->removed_users[$username]['diplomacy']." respect points."
        );


        $GLOBALS['NOTIFICATIONS']->addTempNotification(array( 'text' => 'You have been exiled from your village', 'popup' => 'yes', 'color' => 'red'));
    }

    //simply builds the part of the query that updates village loyalty.
    function buildingLoyaltyUpdateForTraitor($username)
    {
      	//taking away village loyalty points
        $loyalty = $this->removed_users[$username]['vil_loyal_pts'];

        if( $loyalty == 0 || ($loyalty > 0 && round($loyalty - (($loyalty / 2) + 25 )) <= 0 ) )
        {
            $this->return_data['loyalty'] = $loyalty;
            $GLOBALS['Events']->acceptEvent('village_loyalty_loss', array('new'=>$this->return_data['loyalty'], 'old'=>$loyalty ));
            return ", `vil_loyal_pts` = 0";
        }

        else if( $loyalty > 0 )
        {
            $this->return_data['loyalty'] = (($loyalty / 2) + 25 );
            $GLOBALS['Events']->acceptEvent('village_loyalty_loss', array('new'=>$this->return_data['loyalty'], 'old'=>$loyalty ));
            return ", `vil_loyal_pts` = " . round($loyalty - (($loyalty / 2) + 25 ));
        }
    }

    //simply builds the part of the query that updates clan points and activity
    function buildingClanUpdateForWinAndNonTraitor($username)
    {
        //clan win
        if($this->removed_users[$username]['clan'] !== false)
        {
            if( isset($this->removed_users[$username]['update']['users_killed']) && count($this->removed_users[$username]['update']['users_killed']) != 0)
            {
                $count = count($this->removed_users[$username]['update']['users_killed']);
            }
            else
                $count = 1;

            if( in_array($this->removed_users[$username]['location'], array_keys($this->village_locations ) ) || strpos($this->removed_users[$username]['location'], 'Outskirts') !== false )
            {
                $this->removed_users[$username]['clan_updated'] = true;
                $this->return_data['clan'] = 1;
                return ", `clans`.`points` = `clans`.`points` + ".$count."
                        , `clans`.`activity` = `clans`.`activity` + ".$count."
                        , `clan_activity` = `clan_activity` + ".$count." ";
            }
        }
    }

    //simply builds the part of the query that updates clan points and activity
    function buildingClanUpdateForLoss($username)
    {
        if( isset($this->removed_users[$username]['update']['users_killed']) && count($this->removed_users[$username]['update']['users_killed']) != 0)
        {
            $count = count($this->removed_users[$username]['update']['users_killed']);

            //clan win
            if($this->removed_users[$username]['clan'] !== false)
            {
                if( in_array($this->removed_users[$username]['location'], array_keys($this->village_locations ) ) || strpos($this->removed_users[$username]['location'], 'Outskirts') !== false )
                {
                    $this->removed_users[$username]['clan_updated'] = true;
                    $this->return_data['clan'] = 1;
                    return ", `clans`.`points` = `clans`.`points` + ".$count."
                            , `clans`.`activity` = `clans`.`activity` + ".$count."
                            , `clan_activity` = `clan_activity` + ".$count." ";
                }
            }
        }
    }

    //simply builds the part of the query that updates anbu pts
    function buildingAnbuUpdateForWinAndNonTraitor($username)
    {
        //anbu win
        if($this->removed_users[$username]['anbu'] !== false)
        {
            if( in_array($this->removed_users[$username]['location'], array_keys($this->village_locations ) ) || strpos($this->removed_users[$username]['location'], 'Outskirts') !== false )
            {
                if( isset($this->removed_users[$username]['update']['users_killed']) && count($this->removed_users[$username]['update']['users_killed']) != 0)
                {
                    $count = count($this->removed_users[$username]['update']['users_killed']);
                }
                else
                    $count = 1;

                //def
                if($this->village_locations[$this->removed_users[$username]['location']] == $this->removed_users[$username]['village'])
                {
                    $this->removed_users[$username]['anbu_updated'] = true;
                    $this->return_data['anbu'] = 'def';
                    return ", `squads`.`pt_def` = `squads`.`pt_def` + ".$count." ";
                }

                //rage
                else
                {
                    $this->removed_users[$username]['anbu_updated'] = true;
                    $this->return_data['anbu'] = 'rage';
                    return ", `squads`.`pt_rage` = `squads`.`pt_rage` + ".$count." ";
                }
            }
        }
    }

    //simply builds the part of the query that updates anbu pts
    function buildingAnbuUpdateForLoss($username)
    {
        if( isset($this->removed_users[$username]['update']['users_killed']) && count($this->removed_users[$username]['update']['users_killed']) != 0)
        {
            $count = count($this->removed_users[$username]['update']['users_killed']);

            //anbu win
            if($this->removed_users[$username]['anbu'] !== false)
            {
                if( in_array($this->removed_users[$username]['location'], array_keys($this->village_locations ) ) || strpos($this->removed_users[$username]['location'], 'Outskirts') !== false )
                {
                    //def
                    if($this->village_locations[$this->removed_users[$username]['location']] == $this->removed_users[$username]['village'])
                    {
                        $this->removed_users[$username]['anbu_updated'] = true;
                        $this->return_data['anbu'] = 'def';
                        return ", `squads`.`pt_def` = `squads`.`pt_def` + ".$count." ";
                    }

                    //rage
                    else
                    {
                        $this->removed_users[$username]['anbu_updated'] = true;
                        $this->return_data['anbu'] = 'rage';
                        return ", `squads`.`pt_rage` = `squads`.`pt_rage` + ".$count." ";
                    }
                }
            }
        }
    }

    //simply builds the part of the query that updates pvp_exp
    function buildingPvpExpUpdateForWinAndNonTraitor($username)
    {
        //if user is in a village
        if( in_array($this->removed_users[$username]['location'], array_keys($this->village_locations ) ) || strpos($this->removed_users[$username]['location'], 'Outskirts') !== false )
        {
            //if pvp_exp is set
            if(isset($this->removed_users[$username]['update']['pvp_exp']))
            {
                $total_pvp_exp = 0;

                //runs through each exsistance of pvp experience
                foreach($this->removed_users[$username]['update']['pvp_exp'] as $pvp_exp)
                {
                    if($pvp_exp < 1)
                        $total_pvp_exp += 1;
                    else if($pvp_exp > 20)
                        $total_pvp_exp += 20;
                    else
                        $total_pvp_exp += $pvp_exp;
                }

                if($total_pvp_exp < 1)
                    $total_pvp_exp = 1;

                //checking for pvp exp boost from event
                if( $event = functions::getGlobalEvent("IncreasedPVP") )
                    if( isset( $event['data']) && is_numeric( $event['data']) )
                        $total_pvp_exp *= $event['data'] / 100;


                $streak = $this->removed_users[$username]['pvp_streak'] + 1;
                $streak_count = floor($streak/10);
                $streak_step = $streak % 10;

                $streak_boost = 1.00;
                $streak_boost += $streak_count/0.5;

                if($streak_step == 1)
                    $streak_boost += 0.0;
                else if($streak_step == 2)
                    $streak_boost += 0.0;
                else if($streak_step == 3)
                    $streak_boost += 0.1;
                else if($streak_step == 4)
                    $streak_boost += 0.1;
                else if($streak_step == 5)
                    $streak_boost += 0.2;
                else if($streak_step == 6)
                    $streak_boost += 0.2;
                else if($streak_step == 7)
                    $streak_boost += 0.3;
                else if($streak_step == 8)
                    $streak_boost += 0.3;
                else if($streak_step == 9)
                    $streak_boost += 0.4;
                else if($streak_step == 0 && $streak != 0)
                    $streak_boost += 0.4;

                $this->return_data['pvp_streak'] = $this->removed_users[$username]['pvp_streak'] + 1;
                $this->return_data['pvp_experience'] = round( ($total_pvp_exp * $streak_boost) , 2);

                $GLOBALS['Events']->acceptEvent('pvp_experience', array('new'=>$GLOBALS['userdata'][0]['pvp_experience'] + round( ($total_pvp_exp * $streak_boost) , 2), 'old'=>$GLOBALS['userdata'][0]['pvp_experience'] ));
                $GLOBALS['Events']->acceptEvent('pvp_streak',     array('new'=>$GLOBALS['userdata'][0]['pvp_streak'] + 1, 'old'=>$GLOBALS['userdata'][0]['pvp_streak'] ));

                return ", `pvp_experience` = `pvp_experience` + '". round( ($total_pvp_exp * $streak_boost) , 2) ."'
                        , `pvp_streak` = `pvp_streak` + '1'";
            }
        }
    }

    //simply builds the part of the query that updates pvp_exp
    function buildingPvpExpUpdateForLoss($username)
    {
        if($this->removed_users[$username]['pvp_streak'] != 0 && (!isset($this->removed_users[$username]['update']['users_killed']) || count($this->removed_users[$username]['update']['users_killed']) == 0))
        {
            $this->return_data['pvp_streak'] = false;
            $GLOBALS['Events']->acceptEvent('pvp_streak',     array('new'=>0, 'old'=>$GLOBALS['userdata'][0]['pvp_streak'] ));
            return ', `pvp_streak` = 0';
        }
        else if( isset($this->removed_users[$username]['update']['users_killed']) && count($this->removed_users[$username]['update']['users_killed']) != 0)
        {
            //////////////////////////////
            ////////////////////////////
            /////////////////////////////
            //if user is in a village
            if( in_array($this->removed_users[$username]['location'], array_keys($this->village_locations ) ) || strpos($this->removed_users[$username]['location'], 'Outskirts') !== false )
            {
                //if pvp_exp is set
                if(isset($this->removed_users[$username]['update']['pvp_exp']))
                {
                    $total_pvp_exp = 0;

                    //runs through each exsistance of pvp experience
                    foreach($this->removed_users[$username]['update']['pvp_exp'] as $pvp_exp)
                    {
                        if($pvp_exp < 1)
                            $total_pvp_exp += 1;
                        else if($pvp_exp > 20)
                            $total_pvp_exp += 20;
                        else
                            $total_pvp_exp += $pvp_exp;
                    }

                    if($total_pvp_exp < 1)
                        $total_pvp_exp = 1;

                    //checking for pvp exp boost from event
                    if( $event = functions::getGlobalEvent("IncreasedPVP") )
                        if( isset( $event['data']) && is_numeric( $event['data']) )
                            $total_pvp_exp *= $event['data'] / 100;


                    $streak = $this->removed_users[$username]['pvp_streak'] + 1;
                    $streak_count = floor($streak/10);
                    $streak_step = $streak % 10;

                    $streak_boost = 1.00;
                    $streak_boost += $streak_count/0.5;

                    if($streak_step == 1)
                        $streak_boost += 0.0;
                    else if($streak_step == 2)
                        $streak_boost += 0.0;
                    else if($streak_step == 3)
                        $streak_boost += 0.1;
                    else if($streak_step == 4)
                        $streak_boost += 0.1;
                    else if($streak_step == 5)
                        $streak_boost += 0.2;
                    else if($streak_step == 6)
                        $streak_boost += 0.2;
                    else if($streak_step == 7)
                        $streak_boost += 0.3;
                    else if($streak_step == 8)
                        $streak_boost += 0.3;
                    else if($streak_step == 9)
                        $streak_boost += 0.4;
                    else if($streak_step == 0 && $streak != 0)
                        $streak_boost += 0.4;

                    $this->return_data['pvp_streak'] = floor($this->removed_users[$username]['pvp_streak'] / 2);
                    $this->return_data['pvp_experience'] = round( ($total_pvp_exp * $streak_boost) , 2);

                    $GLOBALS['Events']->acceptEvent('pvp_experience', array('new'=>$GLOBALS['userdata'][0]['pvp_experience'] + round( ($total_pvp_exp * $streak_boost) , 2), 'old'=>$GLOBALS['userdata'][0]['pvp_experience'] ));
                    $GLOBALS['Events']->acceptEvent('pvp_streak',     array('new'=>floor($GLOBALS['userdata'][0]['pvp_streak']/2), 'old'=>$GLOBALS['userdata'][0]['pvp_streak'] ));

                    return ", `pvp_experience` = `pvp_experience` + '". round( ($total_pvp_exp * $streak_boost) , 2) ."'
                            , `pvp_streak` = FLOOR(`pvp_streak` / 2)";
                }
            }
            /////////////////////////////
            /////////////////////////////
            ///////////////////////////////
        }
        else
            return '';
    }

    //simply builds the part of the query that updates village points for winner and non traitor
    function buildingVillagePointsUpdateForWinAndNonTraitor($username)
    {

        //if user is in a village
        if( in_array($this->removed_users[$username]['location'], array_keys($this->village_locations ) ) || strpos($this->removed_users[$username]['location'], 'Outskirts') !== false )
        {
            //if users killed is set
            if( isset($this->removed_users[$username]['update']['users_killed']) )
            {
                $kill_count = count($this->removed_users[$username]['update']['users_killed']);

                //default gain
                $gain = 1;


                // Increased funds from global event
                if( $event = functions::getGlobalEvent("VFgain") ){ //check for boost from event
                    if( isset( $event['data']) && is_numeric( $event['data']) ){
                        $gain += $event['data'];
                    }
                }


                //adding gain for village loyalty
                if( abs($this->removed_users[$username]['vil_loyal_pts']) >= 365 )//and check for active bonuses
                    $gain += 2;

                if( isset($this->removed_users[$username]['update']['users_killed']) && count($this->removed_users[$username]['update']['users_killed']) != 0)
                {
                    $count = count($this->removed_users[$username]['update']['users_killed']);
                }
                else
                    $count = 1;

                $this->return_data['village_points'] = $gain * $count;

                return ', `villages`.`points` = `villages`.`points` + '.$gain * $count;

            }
        }
    }

    //simply builds the part of the query that updates village points for winner and non traitor
    function buildingVillagePointsUpdateForLoss($username)
    {
        if( isset($this->removed_users[$username]['update']['users_killed']) && count($this->removed_users[$username]['update']['users_killed']) != 0)
        {
            //if user is in a village
            if( in_array($this->removed_users[$username]['location'], array_keys($this->village_locations ) ) || strpos($this->removed_users[$username]['location'], 'Outskirts') !== false )
            {
                //if users killed is set
                if( isset($this->removed_users[$username]['update']['users_killed']) )
                {
                    $kill_count = count($this->removed_users[$username]['update']['users_killed']);

                    //default gain
                    $gain = 1;


                    // Increased funds from global event
                    if( $event = functions::getGlobalEvent("VFgain") ){ //check for boost from event
                        if( isset( $event['data']) && is_numeric( $event['data']) ){
                            $gain += $event['data'];
                        }
                    }


                    //adding gain for village loyalty
                    if( abs($this->removed_users[$username]['vil_loyal_pts']) >= 365 )//and check for active bonuses
                        $gain += 2;

                    $count = count($this->removed_users[$username]['update']['users_killed']);

                    $this->return_data['village_points'] = $gain * $count;

                    return ', `villages`.`points` = `villages`.`points` + '.$gain * $count;

                }
            }
        }
    }


    //  this can be over written to inject the user into another battle after this
    //  buildingStatusUpdate will also need over written.
    function buildingBattleInformationUpdate($username)
    {
        if($this->removed_users[$username]['dr'] > 0 && $this->removed_users[$username]['sr'] > 0)
            return "`reinforcements` = 0, `battle_id` = 0, `last_battle` = '".$this->timeStamp."'".
                   ", `dr` = ".$this->removed_users[$username]['dr'].
                   ", `sr` = ".$this->removed_users[$username]['sr'].
                   ", `cfh` = ''";
        else
            return "`reinforcements` = 0, `battle_id` = 0, `last_battle` = '".$this->timeStamp."'".
                   ", `cfh` = ''";
    }

    //simply builds the part of the query that updates the arena cool down.
	//should only be used by mirror_battle, torn_battle, arena
    function buildingArenaUpdate()
    {
      	return ", `arena_cooldown` = '".$this->timeStamp."'";
    }

    // simply builds the part of the query that updates the users pools.
    // makes sure that the users health is never over max.
    function buildingPoolUpdate($username)
    {
        $health  = $this->removed_users[$username][ parent::HEALTH];
        $stamina = $this->removed_users[$username][parent::STAMINA];
        $chakra  = $this->removed_users[$username][ parent::CHAKRA];

        if( $health  > $this->removed_users[$username][parent::HEALTHMAX] )
            $health  = $this->removed_users[$username][ parent::HEALTHMAX];

        if( $stamina > $this->removed_users[$username][parent::STAMINAMAX] )
            $stamina = $this->removed_users[$username][parent::STAMINAMAX];

        if( $chakra  > $this->removed_users[$username][parent::CHAKRAMAX] )
            $chakra  = $this->removed_users[$username][ parent::CHAKRAMAX];

        if($GLOBALS['userdata'][0]['cur_health'] != $health)
            $GLOBALS['Events']->acceptEvent('stats_cur_health', array('new'=>$health, 'old'=>$GLOBALS['userdata'][0]['cur_health'] ));
            //$this->recordEvent($username, 'stats_cur_health', $health, $GLOBALS['userdata'][0]['cur_health']);

        if($GLOBALS['userdata'][0]['cur_sta'] != $stamina)
            $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>$stamina, 'old'=>$GLOBALS['userdata'][0]['cur_sta'] ));
            //$this->recordEvent($username, 'stats_cur_sta', $stamina, $GLOBALS['userdata'][0]['cur_sta']);

        if($GLOBALS['userdata'][0]['cur_cha'] != $chakra)
            $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=>$chakra, 'old'=>$GLOBALS['userdata'][0]['cur_cha'] ));
            //$this->recordEvent($username, 'stats_cur_cha', $chakra, $GLOBALS['userdata'][0]['cur_cha']);

        return  ", `users_statistics`.`cur_health` = ".$health.
                ", `users_statistics`.`cur_sta` = ".$stamina.
                ", `users_statistics`.`cur_cha` = ".$chakra;
    }

    //builds the update for the users jutsus
    function buildingJutsuUpdate($username)
    {
        //making sure that the user needs their jutsus updated.
        if(isset($this->removed_users[$username]['update']['jutsus']))
        {
            //templates for building the update.
            $level_start = ', `users_jutsu`.`level` = CASE `users_jutsu`.`jid` ';
            $level_mid = '';
            $level_end = ' ELSE `users_jutsu`.`level` END ';

            $exp_start = ', `users_jutsu`.`exp` = CASE `users_jutsu`.`jid` ';
            $exp_mid = '';
            $exp_end = ' ELSE `users_jutsu`.`exp` END ';

            $uses_start = ', `users_jutsu`.`times_used` = CASE `users_jutsu`.`jid` ';
            $uses_mid = '';
            $uses_end = ' ELSE `users_jutsu`.`times_used` END ';

            //flag used in making sure that a jutsu should be updated.
            $apply = false;

            //going through the users jutsus and starting to build the update.
            foreach($this->removed_users[$username]['update']['jutsus'] as $jid => $status)
            {
                if($status && $jid != -1)
                {
                    $apply = true;

                    if(!isset($this->return_data['jutsus']))
                        $this->return_data['jutsus'] = array();

                    if(!isset($this->return_data['jutsus']['level']))
                        $this->return_data['jutsus']['level'] = array();

                    if(!isset($this->return_data['jutsus']['exp']))
                        $this->return_data['jutsus']['exp'] = array();

                    if(true)//$status !== true)
                    {
                        if($status === 'leveled')
                        {
                            $this->return_data['jutsus']['level'][$this->jutsus[$jid]['name']] = $this->removed_users[$username]['jutsus'][$jid]['level'];//array('level' => $this->removed_users[$username]['jutsus'][$jid]['level'],'exp' => $this->removed_users[$username]['jutsus'][$jid]['exp']);
                            $GLOBALS['Events']->acceptEvent('jutsu_level', array('new'=>$this->removed_users[$username]['jutsus'][$jid]['level'], 'old'=>$this->removed_users[$username]['jutsus'][$jid]['level'] - 1, 'data'=>$jid, 'context'=>$jid));
                        }
                        else
                        {
                            $this->return_data['jutsus']['exp'][$this->jutsus[$jid]['name']] = $status;
                        }
                    }

                    $level_mid .= ' WHEN '.$jid.' THEN '.$this->removed_users[$username]['jutsus'][$jid]['level'];
                    $exp_mid   .= ' WHEN '.$jid.' THEN '.$this->removed_users[$username]['jutsus'][$jid]['exp'];
                    $uses_mid  .= ' WHEN '.$jid.' THEN '.$this->removed_users[$username]['jutsus'][$jid]['times_used'] . ' + `users_jutsu`.`times_used` ';

                    $GLOBALS['Events']->acceptEvent('jutsu_times_used', array('new'=>$this->removed_users[$username]['jutsus'][$jid]['total_times_used'] + $this->removed_users[$username]['jutsus'][$jid]['times_used'],'old'=>$this->removed_users[$username]['jutsus'][$jid]['total_times_used'],'data'=>$jid, 'context'=>$jid));

                }
            }

            //if atleast 1 jutsu for update was found build and return the update.
            if($apply === true)
            {
                $this->updateJutsu = true;

                return  $level_start . $level_mid . $level_end .
                        $exp_start   . $exp_mid   . $exp_end   .
                        $uses_start  . $uses_mid  . $uses_end  ;
            }
            else  //otherwise return ''
                return '';
        }
        else  //otherwise return ''
            return '';
    }

    //simply builds the update for item durability
    function buildingDurabilityUpdate($username)
    {
        //if there is durability numbers that need updated
        if(isset($this->removed_users[$username]['update']['durability']))
        {
            //template for update.
            $durability_start = ', `users_inventory`.`durabilityPoints` = CASE `users_inventory`.`id` ';
            $durability_mid = '';
            $durability_end = ' ELSE `users_inventory`.`durabilityPoints` END ';

            //flag used in checking that the update should be attempted.
            $apply = false;

            //starting to build the update
            foreach($this->removed_users[$username]['update']['durability'] as $inven_id => $status)
            {
                if($status)
                {
                    //start here. adding to return data for durability update.

                    if(!isset($this->return_data['durability']))
                        $this->return_data['durability'] = array();

                    $this->return_data['durability'][$this->removed_users[$username][self::EQUIPMENT][$inven_id]['name']] = $this->removed_users[$username][self::EQUIPMENT][$inven_id]['durability'];

                    //$this->recordEvent($username, 'item_durability_loss', $this->removed_users[$username][self::EQUIPMENT][$inven_id]['iid'], $this->removed_users[$username][self::EQUIPMENT][$inven_id]['durability'], $this->removed_users[$username][self::EQUIPMENT][$inven_id]['starting_durability']);
                    $GLOBALS['Events']->acceptEvent('item_durability_loss', array('context'=>$this->removed_users[$username][self::EQUIPMENT][$inven_id]['iid'],'new'=>$this->removed_users[$username][self::EQUIPMENT][$inven_id]['durability'], 'old'=> $this->removed_users[$username][self::EQUIPMENT][$inven_id]['starting_durability']));

                    $apply = true;
                    $durability_mid .= ' WHEN '.$inven_id.' THEN '.$this->removed_users[$username][self::EQUIPMENT][$inven_id]['durability'];
                }
            }

            //if atleast 1 item needs updated. build the update and return it.
            if($apply === true)
            {
                $this->updateInventory = true;
                return $durability_start . $durability_mid . $durability_end;
            }
            else
                return '';
        }
        else  //otherwise return ''
            return '';
    }

    //simply builds update for stack information for items
    function buildingStackUpdate($username)
    {
        //if there are items marked to have their stack count updated.
        if(isset($this->removed_users[$username]['update']['stack']))
        {
            //template for building update
            $stack_start = ', `users_inventory`.`stack` = CASE `users_inventory`.`id` ';
            $stack_mid = '';
            $stack_end = ' ELSE `users_inventory`.`stack` END ';

            //flag used in checking that an item was found for update
            $apply = false;

            //starting to build the update
            foreach( $this->removed_users[$username]['update']['stack'] as $inven_id => $status )
            {
                if($status)
                {
                    if(!isset($this->return_data['stack']))
                        $this->return_data['stack'] = array();

                    if(isset($this->removed_users[$username][self::EQUIPMENT][$inven_id]['name']))
                    {
                        $this->return_data['stack'][$this->removed_users[$username][self::EQUIPMENT][$inven_id]['name']] = $this->removed_users[$username][self::EQUIPMENT][$inven_id]['stack'];
                        $stack_mid = ' WHEN '.$inven_id.' THEN '.$this->removed_users[$username][self::EQUIPMENT][$inven_id]['stack'];
                        //$this->recordEvent($username, 'item_quantity_loss', $this->removed_users[$username][self::EQUIPMENT][$inven_id]['iid'], $this->removed_users[$username][self::EQUIPMENT][$inven_id]['stack']);
                        $GLOBALS['Events']->acceptEvent('item_quantity_loss', array('context'=>$this->removed_users[$username][self::EQUIPMENT][$inven_id]['iid'],'count'=>($this->removed_users[$username][self::EQUIPMENT][$inven_id]['stack'] - $this->removed_users[$username][self::EQUIPMENT][$inven_id]['starting_stack']), 'old'=>$this->removed_users[$username][self::EQUIPMENT][$inven_id]['starting_stack'], 'new'=>$this->removed_users[$username][self::EQUIPMENT][$inven_id]['stack']));
                    }
                    else
                    {
                        $this->return_data['stack'][$this->removed_users[$username]['items'][$inven_id]['name']] = $this->removed_users[$username]['items'][$inven_id]['stack'];
                        $stack_mid = ' WHEN '.$inven_id.' THEN '.$this->removed_users[$username]['items'][$inven_id]['stack'];
                        //$this->recordEvent($username, 'item_quantity_loss', $this->removed_users[$username]['items'][$inven_id]['iid'], $this->removed_users[$username]['items'][$inven_id]['stack']);
                        $GLOBALS['Events']->acceptEvent('item_quantity_loss', array('context'=>$this->removed_users[$username]['items'][$inven_id]['iid'],'count'=>($this->removed_users[$username]['items'][$inven_id]['stack'] - $this->removed_users[$username]['items'][$inven_id]['starting_stack']), 'old'=>$this->removed_users[$username]['items'][$inven_id]['starting_stack'], 'new'=>$this->removed_users[$username]['items'][$inven_id]['stack'] ));
                    }

                    $apply = true;
                }
            }

            //if atleast 1 item needs updated finish building update.
            if($apply == true)
            {
                $this->updateInventory = true;
                return $stack_start . $stack_mid . $stack_end;
            }
            else //otherwise return ''
                return '';
        }
        else //otherwise return ''
            return '';
    }

    //simply builds update for times used information for items
    function buildingTimesUsedUpdate($username)
    {
        //if there are items that need updated
        if(isset($this->removed_users[$username]['update']['times_used']))
        {
            //template for building update
            $times_used_start = ', `users_inventory`.`times_used` = CASE `users_inventory`.`id` ';
            $times_used_mid = '';
            $times_used_end = ' ELSE `users_inventory`.`times_used` END ';

            //flag used in checking that an item was found for update
            $apply = false;

            //start to build the update
            foreach( $this->removed_users[$username]['update']['times_used'] as $inven_id => $status )
            {
                if($status !== false)
                {
                    if(!isset($this->return_data['times_used']))
                        $this->return_data['times_used'] = array();

                    if(isset($this->removed_users[$username][self::EQUIPMENT][$inven_id]['name']))
                    {
                        $this->return_data['times_used'][$this->removed_users[$username][self::EQUIPMENT][$inven_id]['name']] = $this->removed_users[$username][self::EQUIPMENT][$inven_id]['times_used'];
                        $times_used_mid .= ' WHEN '.$inven_id.' THEN `users_inventory`.`times_used` + '.$this->removed_users[$username][self::EQUIPMENT][$inven_id]['times_used'];
                    }
                    else
                    {
                        $this->return_data['times_used'][$this->removed_users[$username]['items'][$inven_id]['name']] = $this->removed_users[$username]['items'][$inven_id]['times_used'];
                        $times_used_mid .= ' WHEN '.$inven_id.' THEN `users_inventory`.`times_used` + '.$status;
                    }

                    $apply = true;
                }
            }

            //if atleast 1 item needs updated finish building update
            if($apply === true)
            {
                $this->updateInventory = true;
                return $times_used_start . $times_used_mid . $times_used_end;
            }
            else //otherwise return ''
                return '';
        }
        else //otherwise return ''
            return '';
    }

    //simply builds update for exp information for user
    function buildingExpUpdate($username)
    {
        //if the user gained no exp give them 1 exp
        if(!isset($this->removed_users[$username]['update']['exp']))
            $this->removed_users[$username]['update']['exp'] = 1;

        //if the user gained 0 or less exp give them 1 exp
        if($this->removed_users[$username]['update']['exp'] <= 0)
            $this->removed_users[$username]['update']['exp'] = 1;

        $rand_factor = random_int(75,125)/100;
        $this->return_data['exp'] = intval($this->removed_users[$username]['update']['exp'] / $rand_factor);

        //building update.
        $value = intval($this->removed_users[$username]['update']['exp'] / $rand_factor);

        $GLOBALS['Events']->acceptEvent('experience', array('new' => $GLOBALS['userdata'][0]['experience'] + $value, 'old' => $GLOBALS['userdata'][0]['experience']));

        return ", `experience` = `experience` + '".$value."'";
    }

    //simply builds update for money information for user
    function buildingMoneyUpdate($username)
    {
        if( isset($this->removed_users[$username]['update']['money']) )
        {
            $this->return_data['money'] = $this->removed_users[$username]['update']['money'];
            return ", `money` = ".$this->removed_users[$username]['money'];
        }
    }

    // building status update based on the users alive/dead status and
    // Syndicate vs not status
    // this will also update win loss flee tie stats.
    // functions are used for this so they can be overridden by different...
    // battle types
    //
	// this need to be overwritten for kage and clan leader battles
	// add handling of jail
	// if( $status == "jailed" )
    // {
    //     $userQuery .= ", `jail_timer` = '".($this->timeStamp+12*3600)."'";
    // }
	//
    function buildingStatusUpdateForWin($username)
    {
        //$this->recordEvent($username, 'status', "awake", $GLOBALS['userdata'][0]['status']);
        $GLOBALS['Events']->acceptEvent('status', array('new'=>'awake', 'old'=>$GLOBALS['userdata'][0]['status'] ));

        $this->return_data['hospital'] = false;
        $temp = ", `status` = 'awake'";
        $GLOBALS['userdata'][0]['status'] = 'awake';
        $GLOBALS['template']->assign('userStatus', 'awake');


        // check for flee
        if( isset($this->removed_users[$username]['flee']) && $this->removed_users[$username]['flee'] === true)
            //mark flee
            $temp .= $this->buildingFleeUpdate($username);

        // else
        else
            //mark win
            $temp .= $this->buildingWinUpdate($username);

        return $temp;
    }


    // building status update based on the users alive/dead status and
    // Syndicate vs not status
    // this will also update win loss flee tie stats.
    // functions are used for this so they can be overridden by different...
    // battle types
    //
	// this need to be overwritten for kage and clan leader battles
	// add handling of jail
	// if( $status == "jailed" )
    // {
    //     $userQuery .= ", `jail_timer` = '".($this->timeStamp+12*3600)."'";
    // }
	//
    function buildingStatusUpdateForLoss($username)
    {
        //if the user is not Syndicate
        //set them to hospitalized and send them to their village
        if( $this->removed_users[$username]['village'] != 'Syndicate' )
        {
            $this->return_data['hospital'] = true;

            //mark loss
            $temp = $this->buildingLossUpdate($username);

            //set message for non Syndicate hospitalized
            //$this->recordEvent($username, 'status', "hospitalized", $GLOBALS['userdata'][0]['status']);
            $GLOBALS['Events']->acceptEvent('status', array('new'=>'hospitalized', 'old'=>$GLOBALS['userdata'][0]['status'] ));

            $GLOBALS['userdata'][0]['status'] = 'hospitalized';
            $GLOBALS['template']->assign('userStatus', 'hospitalized');


            $hospital = new hospitalFunctions();

            $healTime = $hospital->calculateHealtime();

            $this->return_data['heal_time'] = ceil($healTime/60);

            $GLOBALS['userdata'][0]['hospital_timer'] = ($GLOBALS['user']->load_time + $healTime);

            return $temp .", `users`.`status` = 'hospitalized',
                             `users`.`latitude` = `villages`.`latitude`,
                             `users`.`longitude` = `villages`.`longitude`,
                             `users`.`location` = `villages`.`name`,
                             `users_timer`.`hospital_timer` = ".($GLOBALS['user']->load_time + $healTime);
        }

        //if the user is Syndicate
        //set them to hospitalized and move them from their current location if
        //they are currently in a village
        else
        {
            $this->return_data['hospital'] = true;

            //mark loss
            $temp = $this->buildingLossUpdate($username);

            //check to see if the user is currently in a village
            //if so make sure that they will not be in a village when hospitalized.
            //set status to hospitalized and location to disoriented
            if( in_array($this->removed_users[$username]['location'], array_keys( $this->village_locations ) ) || strpos($this->removed_users[$username]['location'], 'Outskirts') !== false )
            {
                $new_latitude = $this->removed_users[$username]['latitude'];
                $new_longitude = $this->removed_users[$username]['longitude'];

                while( $new_latitude == $this->removed_users[$username]['latitude'] && $new_longitude == $this->removed_users[$username]['longitude'] )
                {
                    $new_latitude = $this->removed_users[$username]['latitude'] + random_int(-2,2);
                    $new_longitude = $this->removed_users[$username]['longitude'] + random_int(-2,2);
                }

                //set message for Syndicate hospitalized
                //$this->recordEvent($username, 'status', "hospitalized", $GLOBALS['userdata'][0]['status']);
                $GLOBALS['Events']->acceptEvent('status', array('new'=>'hospitalized', 'old'=>$GLOBALS['userdata'][0]['status'] ));

                $GLOBALS['userdata'][0]['status'] = 'hospitalized';
                $GLOBALS['template']->assign('userStatus', 'hospitalized');
                $GLOBALS['userdata'][0]['latitude'] = $new_latitude;
                $GLOBALS['userdata'][0]['longitude'] = $new_longitude;
                $GLOBALS['userdata'][0]['location'] = 'Disoriented';

                $hospital = new hospitalFunctions();

                $healTime = $hospital->calculateHealtime();

                $this->return_data['heal_time'] = ceil($healTime/60);

                $GLOBALS['userdata'][0]['hospital_timer'] = ($GLOBALS['user']->load_time + $healTime);

                return  $temp . ", `users`.`status` = 'hospitalized',
                                   `users`.`latitude` = ".$new_latitude.",
                                   `users`.`longitude` = ".$new_longitude.",
                                   `users`.`location` = 'Disoriented',
                                   `users_timer`.`hospital_timer` = ".($GLOBALS['user']->load_time + $healTime);
            }

            //if the Syndicate user is not currently in a village
            //just set status to hospitalized and location to disoriented
            else
            {
                //set message for Syndicate hospitalized
                //$this->recordEvent($username, 'status', "hospitalized", $GLOBALS['userdata'][0]['status']);
                $GLOBALS['Events']->acceptEvent('status', array('new'=>'hospitalized', 'old'=>$GLOBALS['userdata'][0]['status'] ));

                $GLOBALS['userdata'][0]['status'] = 'hospitalized';
                $GLOBALS['template']->assign('userStatus', 'hospitalized');
                $GLOBALS['userdata'][0]['location'] = 'Disoriented';

                $hospital = new hospitalFunctions();

                $healTime = $hospital->calculateHealtime();

                $this->return_data['heal_time'] = ceil($healTime/60);

                $GLOBALS['userdata'][0]['hospital_timer'] = ($GLOBALS['user']->load_time + $healTime);

                return  $temp. ", `users`.`status` = 'hospitalized',
                                  `users`.`location` = 'Disoriented',
                                  `users_timer`.`hospital_timer` = ".($GLOBALS['user']->load_time + $healTime);
            }
        }
    }

    //these are just place holders and should be overwritten by each battle type
    function buildingWinUpdate($username)
    {
        echo 'buildWinUpdate needs overridden';
        //return ", `AIwon` = `AIwon` + 1";
        //return ", `battles_won` = `battles_won` + 1";

        return '';
    }

    //these are just place holders and should be overwritten by each battle type
    function buildingLossUpdate($username)
    {
        echo 'buildLossUpdate needs overridden';
        //return ", `AIlost` = `AIlost` + 1";
        //return ", `battles_lost` = `battles_lost` + 1";

        return '';
    }

    //these are just place holders and should be overridden by each battle type
    function buildingFleeUpdate($username)
    {
        echo 'buildFleeUpdate needs overridden';
        //return ", `AIfled` = `AIfled` + 1";
        //return ", `battles_fled` = `battles_fled` + 1";

        return '';
    }

    //used to build the link for the end of combat summary
    //needs to be overridden
    function getReturnId($hospital)
    {
        echo 'get return id needs overrridden';

        if(!$hospital)
            return '2';
        else if($GLOBALS['userdata'][0]['village'] == 'Syndicate')//for Syndicate
            return '51';
        else//for non Syndicate
            return '34';
    }

    //used to show the name for the link at the end of combat summary
    function getReturnName($hospital)
    {
        echo 'get return name needs overridden';

        if(!$hospital)
            return 'your profile';
        else
            return 'the hospital';
    }

    //
    //this builds theupdate for bounty hunters on win
    function buildingBountyHunterUpdateForWin($username)
    {
        //get their bounty target's name
        $target = $this->removed_users[$username]['update']['bounty_hunter']['target'];
        $target_rank = $this->removed_users[$username]['update']['bounty_hunter']['target_rank'];

        //get the targets bounty.
        if($this->removed_users[$username]['village'] == 'Syndicate')
        {
            $query = "SELECT (`SpecialBounty` * -1) as 'total_bounty', `userId` FROM `bingo_book` inner join `users` on (`users`.`id` = `bingo_book`.`userId`) WHERE `users`.`username` = '".$target."'";
            $update_query = 'UPDATE `bingo_book` SET `SpecialBounty` = 0 WHERE `userID` = ';
        }
        else
        {
            $query = "SELECT (`".$this->removed_users[$username]['village']."` * -1) as 'total_bounty', `userId` FROM `bingo_book` inner join `users` on (`users`.`id` = `bingo_book`.`userId`) WHERE `users`.`username` = '".$target."'";
            $update_query = 'UPDATE `bingo_book` SET `'.$this->removed_users[$username]['village'].'` = 0 WHERE `userID` = ';
        }

        try { if(! $bounty = $GLOBALS['database']->fetch_data($query)) throw new Exception('query failed'); }
        catch (Exception $e)
        {
            try { if(! $bounty = $GLOBALS['database']->fetch_data($query)) throw new Exception ('query failed'); }
            catch (Exception $e)
            {
                try { if(! $bounty = $GLOBALS['database']->fetch_data($query)) throw new Exception ('query failed to update user information'); }
                catch (Exception $e)
                {
                    $GLOBALS['DebugTool']->push('','there was an error getting bounty information.', __METHOD__, __FILE__, __LINE__);
                    throw $e;
                }
            }
        }

        $hunted = $bounty[0]['userId'];
        $bounty = $bounty[0]['total_bounty'];
        error_log("bounty from database: ".$bounty);

        $update_query .= $hunted;

        $events = new Events($hunted);
        $events->acceptEvent('diplomacy_gain', array('new'=>0, 'old'=>($bounty*-1), 'context'=>$this->removed_users[$username]['village']));
        $events->closeEvents();

        try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception('query failed'); }
        catch (Exception $e)
        {
            try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception ('query failed'); }
            catch (Exception $e)
            {
                try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception ('query failed to update user information'); }
                catch (Exception $e)
                {
                    error_log('bounty update error.');
                }
            }
        }

        //check to see if you killed this user
        $killer = false;
        if( isset($this->removed_users[$username]['update']['users_killed']) && in_array($target, $this->removed_users[$username]['update']['users_killed']))
            $killer = true;


        //calculate bounty, bounty_exp, and update feature

      //bounty
        //make sure level is not over 500
        $bounty_hunter_level = $this->removed_users[$username]['bounty_hunter_exp']/1000;
        if($bounty_hunter_level > 500)
            $bounty_hunter_level = 500;

        //take the total bounty and multiply it by 1 + .0005 * level
        //also flipping the bounty to make it positive.
        $bounty = abs($bounty * (1 + (0.0005 * $bounty_hunter_level)));

        //calculate max bounty
        $max = 1000 + (75 * $bounty_hunter_level);


        //if the user is Syndicate increase max.
        if($this->removed_users[$username]['village'] == 'Syndicate')
            $max *= 100;


        //make sure the reward does not go over it
        if( $bounty > $max )
            $bounty = $max;

        //make sure that the user is earning a bounty
        if( $bounty < $max * 0.01 && $this->removed_users[$username]['village'] == 'Syndicate')
        {
            error_log("BOUNTY COLLECTED IS LESSTHAN 10% of max. bounty to collect: ".$bounty." --- bounty collecting now: ".($max * 0.01)." --- bounty from db: ".$bounty[0]['total_bounty']);
            $bounty = $max * 0.01;
        }
        else if( $bounty < $max * 0.10 && $this->removed_users[$username]['village'] != 'Syndicate')
        {
            error_log("BOUNTY COLLECTED IS LESSTHAN 10% of max. bounty to collect: ".$bounty." --- bounty collecting now: ".($max * 0.1)." --- bounty from db: ".$bounty[0]['total_bounty']);
            $bounty = $max * 0.10;
        }


        //if this bounty hunter did not get the kill halve the bounty reward
        if( $killer === false)
            $bounty *= 0.5;


        $this->return_data['bounty'] = $bounty;
        $set = ", `money` = `money` + '".$bounty."'";

        $GLOBALS['Events']->acceptEvent('money_gain', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] + $bounty));
        $GLOBALS['Events']->acceptEvent('bounty_collected', array('data' => $bounty));

        //bounty_exp
        if($this->removed_users[$username]['village'] == 'Syndicate')
        {
            if($this->removed_users[$username]['rank'] > $target_rank)
                $exp = 120 + random_int(-40,40);
            else if($this->removed_users[$username]['rank'] = $target_rank)
                $exp = 400 + random_int(-120,120);
            else if($this->removed_users[$username]['rank'] < $target_rank)
                $exp = 680 + random_int(-200,200);
        }
        else
        {
            if($this->removed_users[$username]['rank'] > $target_rank)
                $exp = 40 + random_int(-12,12);
            else if($this->removed_users[$username]['rank'] = $target_rank)
                $exp = 132 + random_int(-44,44);
            else if($this->removed_users[$username]['rank'] < $target_rank)
                $exp = 224 + random_int(-76,76);
        }

        // Check for global event modifications
        if( $event = functions::getGlobalEvent("IncreasedBountyHunterExp")){
            if( isset( $event['data']) && is_numeric( $event['data']) ){
                $exp *= round($event['data'] / 100,2);
                $exp = floor($exp);
            }
        }

        //updating cache
        $OccupationData = new OccupationData();
        $OccupationData->updateSpecialOccupationCache($this->removed_users[$username]['bounty_hunter_exp'] + $exp);

        //updating database
        $this->return_data['bounty_exp'] = $exp;
        $set .= ", `bountyHunter_exp` = `bountyHunter_exp` + '".$exp."'";

        //returning everything to be added to query.
        return $set;
    }

    //this builds the update for a user who has had their bounty collected.
    function buildingBountyCollectedUpdate($username)
    {
        $village = $this->removed_users[$username]['update']['bounty_collected'];

        //if handling a merc
        if($village == 'Syndicate')
        {

            $select_statement = "SELECT `userid` from `users_occupations` WHERE `feature` = '".$username."' AND `special_occupation` = '3'";

            try { if(! $user_list = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception('query failed'); }
            catch (Exception $e)
            {
                try { if(! $user_list = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed'); }
                catch (Exception $e)
                {
                    try { if(! $user_list = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed to update user information'); }
                    catch (Exception $e)
                    {
                        $GLOBALS['DebugTool']->push('','there was an error getting battle dodge information.', __METHOD__, __FILE__, __LINE__);
                        throw $e;
                    }
                }
            }

            foreach($user_list as $userid)
            {
                $OccupationData = new OccupationData($userid['userid']);
                $OccupationData->setFeature('',true);
            }


            //clearing this user from other users tracking.
            $query = "UPDATE `users_occupations` SET `feature` = '' WHERE `feature` = '".$username."' AND `special_occupation` = '3'";
            try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed'); }
            catch (Exception $e)
            {
                try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                catch (Exception $e)
                {
                    try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed to update user information'); }
                    catch (Exception $e)
                    {
                        $GLOBALS['DebugTool']->push('','there was an error updating user information.', __METHOD__, __FILE__, __LINE__);
                        throw $e;
                    }
                }
            }

            $this->return_data['bounty_collected'] = 'mercenary';
            return ', `special_bounty_timer` = '.(time());
        }

        //if handling a bounty hunter
        else
        {
            $select_statement = "SELECT `userid` from `users_occupations` WHERE `feature` = '".$username."' AND `special_occupation` = '2'";

            try { if(! $user_list = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception('query failed'); }
            catch (Exception $e)
            {
                try { if(! $user_list = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed'); }
                catch (Exception $e)
                {
                    try { if(! $user_list = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed to update user information'); }
                    catch (Exception $e)
                    {
                        $GLOBALS['DebugTool']->push('','there was an error getting battle dodge information.', __METHOD__, __FILE__, __LINE__);
                        throw $e;
                    }
                }
            }

            if(is_array($user_list))
            {
                foreach($user_list as $userid)
                {
                    $OccupationData = new OccupationData($userid['userid']);
                    $OccupationData->setFeature('',true);
                }

                //clearing this user from other users tracking
                $query = "UPDATE `users_occupations` SET `feature` = '' WHERE `feature` = '".$username."' AND `special_occupation` = '2'";
                try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed'); }
                catch (Exception $e)
                {
                    try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                    catch (Exception $e)
                    {
                        try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed to update user information: '.$query); }
                        catch (Exception $e)
                        {
                            $GLOBALS['DebugTool']->push('','there was an error updating user information.', __METHOD__, __FILE__, __LINE__);
                            throw $e;
                        }
                    }
                }
            }

            $this->return_data['bounty_collected'] = 'bounty hunter';
            return ', `'.$this->removed_users[$username]['update']['bounty_collected'].'` = 0 ';//
        }

    }

    //this function handles a user that has dodged a battle
    function battle_dodger()
    {
        if($GLOBALS['userdata'][0]['battle_id'] == 0 && $GLOBALS['userdata'][0]['status'] == 'exiting_combat')
        {
            $GLOBALS['database']->execute_query("UPDATE `users` SET `status` = 'awake' WHERE `id` = ".$_SESSION['uid']);
            return 1;
        }

        $battle_code = false;

        if(isset(strrev($GLOBALS['userdata'][0]['battle_id'])[1]))
            $battle_code = (strrev($GLOBALS['userdata'][0]['battle_id'])[1] . strrev($GLOBALS['userdata'][0]['battle_id'])[0]);

        if( $battle_code !== false && $battle_code == BattleStarter::pvp)
        {
            //get time
            $time = $GLOBALS['user']->load_time;

            //get history
            $select_statement = 'SELECT `battle_dodge` FROM `users_timer` WHERE `userid` = '.$_SESSION['uid'];

            try { if(! $history = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception('query failed'); }
            catch (Exception $e)
            {
                try { if(! $history = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed'); }
                catch (Exception $e)
                {
                    try { if(! $history = $GLOBALS['database']->fetch_data($select_statement)) throw new Exception ('query failed to update user information'); }
                    catch (Exception $e)
                    {
                        $GLOBALS['DebugTool']->push('','there was an error getting battle dodge information.', __METHOD__, __FILE__, __LINE__);
                        throw $e;
                    }
                }
            }

            $last24 = 0;
            $last168 = 0;
            $new_history = ($time.'|');

            //parse history
            if($history != '' && is_array($history) && $history[0]['battle_dodge'] != '')
                foreach( explode('|',$history[0]['battle_dodge']) as $dodge )
                    if( $time - $dodge <= 86400)//last 24hours
                    {
                        $last24++;
                        $new_history .= $dodge.'|';
                    }
                    else if( $time - $dodge <= 604800)//last week or 168
                    {
                        $last168++;
                        $new_history .= $dodge.'|';
                    }
                    else//old/expired
                    {}

            //caclulation
            $jail_time = $time + ((3 ** ( 1 + $last24 ) + ( (2 ** ( $last168 / 2)) - 1 ) ) * 40);


            //jail and update history
            $GLOBALS['userdata'][0]['status'] = 'jailed';
            $GLOBALS['template']->assign('userStatus', 'jailed');
            $GLOBALS['userdata'][0]['jail_timer'] = $jail_time;
            $query = 'UPDATE `users_timer`, `users`, `users_statistics` SET `jail_timer` = '.$jail_time.', `battle_dodge` = "'.$new_history.'", `status` = "jailed", `cfh` = "", `battle_id` = 0, `pvp_streak` = 0 WHERE `users_timer`.`userid` = '.$_SESSION['uid'].' AND `users`.`id` = '.$_SESSION['uid'].' AND `users_statistics`.`uid` = '.$_SESSION['uid'];
            $GLOBALS['Events']->acceptEvent('pvp_streak',     array('new'=>0, 'old'=>$GLOBALS['userdata'][0]['pvp_streak'] ));


            try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed'); }
            catch (Exception $e)
            {
                try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                catch (Exception $e)
                {
                    try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed to update user information'); }
                    catch (Exception $e)
                    {
                        $GLOBALS['DebugTool']->push('','there was an error updating user information.', __METHOD__, __FILE__, __LINE__);
                        throw $e;
                    }
                }
            }

            //give message
            $GLOBALS['page']->Message("You have been jailed for dodging the end of a combat. we apologize for the inconvenience. Please make sure to complete your battles in the future. Thank You!", 'Battle', 'id=38','Go to Jail. Do not pass Go. Do not collect $200.');
        }
        else
        {
            $query = 'UPDATE `users` SET `status` = "awake", `battle_id` = 0 WHERE `users`.`id` = '.$_SESSION['uid'];

            try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception('query failed'); }
            catch (Exception $e)
            {
                try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed'); }
                catch (Exception $e)
                {
                    try { if(!$GLOBALS['database']->execute_query($query)) throw new Exception ('query failed to update user information'); }
                    catch (Exception $e)
                    {
                        $GLOBALS['DebugTool']->push('','there was an error updating user information.', __METHOD__, __FILE__, __LINE__);
                        throw $e;
                    }
                }
            }

            //give message
            $GLOBALS['page']->Message("Please make sure to complete your battles in the future. Thank You!", 'Battle', 'id=2','Go to Profile.');

        }
    }

    //can be overridden by battle types for what ever purpose is needed.
    function startOfTurnInjectionPoint()
    {}

    //can be overridden by battle types for what ever purpose is needed.
    function endOfTurnInjectionPoint()
    {}

    //can be overridden by battle types for what ever purpose is needed.
    function endOfCombatInjectionPoint()
    {}

    //opening battle history record
    function openBattleHistory()
    {
        $result = false;
        $count = 0;
        $id = 0;
        while($result == false && $count < 5)
        {
            $id = random_int(1,9999999);
            $count++;

            $insert_query =     'INSERT INTO `battle_history` '.
                                   '(`id`, `time`, `type`, `census`, `battle_log`, `changes`,`latitude`,`longitude`,`requirement_ignore`) '.
                                'VALUES '.
                                   '("'.$id.'", "'.$GLOBALS['user']->load_time.'", "", "", "", "","'.$GLOBALS['userdata'][0]['latitude'].'", "'.$GLOBALS['userdata'][0]['longitude'].'","no")';

            $result = $GLOBALS['database']->execute_query($insert_query);
        }

        if($result === false)
            throw new Exception('failed to open battle history!');

        $this->battle_history_id = $id;
    }

    //recording the rest of the battle's history
    function recordBattleHistory()
    {
         $update_query =    'UPDATE `battle_history` '.
                               'SET `type` = "'.$this->battle_type.'", `census` = ",'.implode(',',$this->census).'", `battle_log` = "'.base64_encode(gzdeflate(serialize($this->battle_log),6)).'" '.
                               'WHERE `id` = '.$this->battle_history_id;

        $delete_query = 'DELETE FROM `battle_history` WHERE `time` < '.($GLOBALS['user']->load_time - 60*60*24*4).' OR (`type` = "01" AND `time` < '.($GLOBALS['user']->load_time - 60*60).') AND (`keep` = "no" OR '.($GLOBALS['user']->load_time - 60*60*24*90).')';

        try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception('query failed'); }
        catch (Exception $e)
        {
            try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception ('query failed'); }
            catch (Exception $e)
            {
                try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception ('query failed to update user information'); }
                catch (Exception $e)
                {
                    $GLOBALS['DebugTool']->push($update_query,'there was an error inserting new battle history.', __METHOD__, __FILE__, __LINE__);
                    throw new Exception($update_query);
                }
            }
        }

        try { if(!$GLOBALS['database']->execute_query($delete_query)) throw new Exception('query failed'); }
        catch (Exception $e)
        {
            try { if(!$GLOBALS['database']->execute_query($delete_query)) throw new Exception ('query failed'); }
            catch (Exception $e)
            {
                try { if(!$GLOBALS['database']->execute_query($delete_query)) throw new Exception ('query failed to update user information'); }
                catch (Exception $e)
                {
                    $GLOBALS['DebugTool']->push($delete_query,'there was an error deleting old battle history.', __METHOD__, __FILE__, __LINE__);
                    throw new Exception($delete_query);
                }
            }
        }
    }

    //recording conclusion result for quest system
    function recordConclusion($username, $type, $result)
    {
        $GLOBALS['Events']->acceptEvent('combat_conclusion', array('data' => $result));
        $GLOBALS['Events']->acceptEvent('combat_type', array('data' => $type));
        foreach($this->census as $record)
        {
            $record = explode('/', $record);
            if($record[1] == $this->removed_users[$username]['team'])
            {
                if($username != $record[0])
                    $GLOBALS['Events']->acceptEvent('combat_allies', array('data' => $record[0], 'extra'=>$record[1]));
            }
            else
            {
                $GLOBALS['Events']->acceptEvent('combat_opponents', array('data' => $record[0], 'extra'=>$record[1]));
            }
        }
    }

    //recording balance data
    function recordBalanceData()
    {
        //process information for balance tracking
        if($this->balanceFlag === true)
        {
            $average = $difference = $count = $weight = 0;

            foreach($this->balanceDSR as $teamKey =>$teamDSR)
            {
                $count ++;
                $average += $teamDSR;
            }

            $average /= $count;

            foreach($this->balanceDSR as $teamKey =>$teamDSR)
            {
                $difference = abs( $average - $teamDSR );
            }

            $difference /= $count;

            $weight = abs(($difference / $average) - 1);
            $skewedWeight = ($weight - 0.8) * 5;

            if($weight > 0.8)
            {
                $balance = array();

                //formating data
                foreach($this->balance as $user_key => $user_data_array)
                    foreach($user_data_array as $jutsu_key => $jutsu_data)
                    {
                        $balance[$jutsu_key] = array();
                        $balance[$jutsu_key]['all'] = array('win'=>0,'loss'=>0,'use'=>0);

                        if(!is_array($jutsu_data))
                        {
                            //no split jutsu
                            $balance[$jutsu_key]['all']['use'] += abs($jutsu_data);

                            if($jutsu_data > 0)
                                $balance[$jutsu_key]['all']['win'] += $skewedWeight * $jutsu_data;
                            else
                                $balance[$jutsu_key]['all']['loss'] += $skewedWeight * abs($jutsu_data);
                        }
                        else
                            //split jutsu
                            foreach($jutsu_data as $spec_key => $spec_data)
                            {
                                $balance[$jutsu_key]['all']['use'] += abs($spec_data);

                                if($spec_data > 0)
                                    $balance[$jutsu_key]['all']['win'] += $skewedWeight * $spec_data;
                                else
                                    $balance[$jutsu_key]['all']['loss'] += $skewedWeight * abs($spec_data);

                                if(!isset($balance[$jutsu_key][$spec_key]))
                                    $balance[$jutsu_key][$spec_key] = array('win'=>0,'loss'=>0,'use'=>0);

                                $balance[$jutsu_key][$spec_key]['use'] += abs($spec_data);

                                if($spec_data > 0)
                                    $balance[$jutsu_key][$spec_key]['win'] += $skewedWeight * $spec_data;
                                else
                                    $balance[$jutsu_key][$spec_key]['loss'] += $skewedWeight * abs($spec_data);

                            }
                    }

                //building query
                $win_count_all = '';
                $loss_count_all = '';
                $use_count_all = '';
                $win_count_T = '';
                $loss_count_T = '';
                $use_count_T = '';
                $win_count_N = '';
                $loss_count_N = '';
                $use_count_N = '';
                $win_count_G = '';
                $loss_count_G = '';
                $use_count_G = '';
                $win_count_B = '';
                $loss_count_B = '';
                $use_count_B = '';
                $id_list = '';
                $when = ' WHEN ';
                $then = ' THEN ';

                //logic for building when then statements
                foreach($balance as $jutsu_key => $spec_array)
                {
                    if($id_list != '')
                        $id_list .= ', '.$jutsu_key;
                    else
                        $id_list .= $jutsu_key;

                    foreach($spec_array as $spec_key => $status_array)
                    {
                        foreach($status_array as $status_key => $value)
                        {
                            ${$status_key.'_count_'.$spec_key} .= ' WHEN ' . $jutsu_key . ' THEN ' . round(abs($value),5) . ' + `'.$status_key.'_count_'.$spec_key.'` ';

                        }
                    }
                }



                //
                if($id_list != '')
                {
                    $update_query =
                    'UPDATE `jutsu`
                    SET `win_count_all` = case `id` WHEN 9999 THEN 0 '.$win_count_all.' ELSE `win_count_all` END,
                    	`loss_count_all` = case `id` WHEN 9999 THEN 0 '.$loss_count_all.' ELSE `loss_count_all` END,
                    	`use_count_all` = case `id` WHEN 9999 THEN 0 '.$use_count_all.' ELSE `use_count_all` END,
                        `win_count_T` = case `id` WHEN 9999 THEN 0 '.$win_count_T.' ELSE `win_count_T` END,
                    	`loss_count_T` = case `id` WHEN 9999 THEN 0 '.$loss_count_T.' ELSE `loss_count_T` END,
                    	`use_count_T` = case `id` WHEN 9999 THEN 0 '.$use_count_T.' ELSE `use_count_T` END,
                        `win_count_N` = case `id` WHEN 9999 THEN 0 '.$win_count_N.' ELSE `win_count_N` END,
                    	`loss_count_N` = case `id` WHEN 9999 THEN 0 '.$loss_count_N.' ELSE `loss_count_N` END,
                    	`use_count_N` = case `id` WHEN 9999 THEN 0 '.$use_count_N.' ELSE `use_count_N` END,
                        `win_count_G` = case `id` WHEN 9999 THEN 0 '.$win_count_G.' ELSE `win_count_G` END,
                    	`loss_count_G` = case `id` WHEN 9999 THEN 0 '.$loss_count_G.' ELSE `loss_count_G` END,
                    	`use_count_G` = case `id` WHEN 9999 THEN 0 '.$use_count_G.' ELSE `use_count_G` END,
                        `win_count_B` = case `id` WHEN 9999 THEN 0 '.$win_count_B.' ELSE `win_count_B` END,
                    	`loss_count_B` = case `id` WHEN 9999 THEN 0 '.$loss_count_B.' ELSE `loss_count_B` END,
                    	`use_count_B` = case `id` WHEN 9999 THEN 0 '.$use_count_B.' ELSE `use_count_B` END
                    WHERE `id` in ('.$id_list.')';

                    //running query
                    try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception('query failed'); }
                    catch (Exception $e)
                    {
                        try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception ('query failed'); }
                        catch (Exception $e)
                        {
                            try { if(!$GLOBALS['database']->execute_query($update_query)) throw new Exception ('query failed'); }
                            catch (Exception $e)
                            {
                                $GLOBALS['DebugTool']->push($update_query,'there was an error recording balance', __METHOD__, __FILE__, __LINE__);

                            }
                        }
                    }
                }
            }
        }
    }


    private function notifyUser($id, $message)
    {
        if($id == $_SESSION['uid'])
        {
            $GLOBALS['NOTIFICATIONS']->addNotification(array(
                'id' => 26,
                'duration' => 'none',
                'text' => $message,
                'dismiss' => 'yes'
            ));
        }
        else
        {
            $notifications = new NotificationSystem($GLOBALS['database']->fetch_data('
                SELECT `notifications`
                FROM `users`
                WHERE `id` = '.$id)[0]['notifications']);

                $notifications->addNotification(array(
                'id' => 26,
                'duration' => 'none',
                'text' => $message,
                'dismiss' => 'yes'
            ));
            
            $notifications->dismissNotification($_GET['notification_dismiss']);
        }
    }
}