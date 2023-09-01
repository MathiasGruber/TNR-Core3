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
 *Class: Tags
 *  this class defines the functions of a battle and controls what hapens in a battle.
 *  this call is extended by other classes to change how a battle functions for different
 *  types of battles. player vs ai, player vs player, spar, free for all(pit)
 */

require_once(Data::$absSvrPath.'/global_libs/Tags/Tags.php');
require_once(Data::$absSvrPath.'/global_libs/Tags/Tag.php');
require_once(Data::$absSvrPath.'/tools/DebugTool.php');
require_once(Data::$absSvrPath.'/libs/elements/Elements.php');

class Battle extends Tags
{
    //this defines how much durability damage is taken at each rank.
    //this can be overriden
    public $DURABILITYDAMAGESCALE = array(0,400000,400000,256000,320000,400000);

    //does nothing other than call Tags constructor at the moment
    function __construct($uid,$cache_time,$debugging)
    {
        //marking this a debugging.
        $this->debugging = $debugging;

        //calling parent constructor which is the Tags class
        parent::__construct($uid,$cache_time,$debugging);
    }


    //addAI
    //gets all data required by the ai and adds it to the cache.
    function addAI($usernames, $team)
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
        $select_statement = "SELECT * FROM `ai` where `name` IN (".$temp_names.")";

        //calling db to get ai and recurseive call to avoid issues.
        //aka it it hickups and fails it will try again up to twice.
        try{ $query = $GLOBALS['database']->fetch_data($select_statement); }
        catch(Exception $E)
        {
            try{ $query = $GLOBALS['database']->fetch_data($select_statement); }
            catch(Exception $E)
            {
                try{ $query = $GLOBALS['database']->fetch_data($select_statement); }
                catch(Exception $E)
                {
                    throw  new Exception('there was an issue collecting user data. (addUser:battle)');
                }
            }
        }

        //checking to make sure the query went well
        if(is_array($query) && isset($query[0]['id']))
        {
            foreach( $query as $user )
            {
                $username = $user['name'];

                //flag to mark if the ai was found currently in the battle.
                //this is for adding the ai then ai 2, ai 3, ai 4.
                //if it does not do this it will just overwrite the first ai and
                //you could only have 1 of any given ai.
                $found = false;

                //looking for the last ai with the same name to know what to name this ai
                for($i = 1; !$found ; $i++)
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

                //calling add user to add that name and team to the battle
                parent::addUser($username,$team);

                //marking the user as an ai
                $this->users[$username]['ai'] = true;

                //setting alot of this ai's misc. information
                $this->users[$username][parent::HEALTH] = $user['life'];
                $this->users[$username][parent::HEALTHMAX] = $user['life'];
                $this->users[$username]['display_name'] = $user['name'];
                $this->users[$username][parent::RANK] = $user['rank_id'];
                $this->users[$username]['display_rank'] = $user['rank'];
                $this->users[$username]['aid'] = $user['id'];
                $this->users[$username]['gender'] = $user['gender'];
                $this->users[$username]['show_count'] = $user['show_count'];



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

                //settin generals
                $this->users[$username][parent::STRENGTH]       = $user['strength'];
                $this->users[$username][parent::WILLPOWER]      = $user['willpower'];
                $this->users[$username][parent::INTELLIGENCE]   = $user['intelligence'];
                $this->users[$username][parent::SPEED]          = $user['speed'];
                $this->users[$username][parent::SPECIALIZATION] = $user['specialization'];

                //settin other stats
                $this->users[$username][parent::ARMORBASE]      = $user['armor'];
                $this->users[$username][parent::MASTERY]        = $user['mastery'];
                $this->users[$username][parent::STABILITY]      = $user['stability'];
                $this->users[$username][parent::ACCURACY]       = $user['accuracy'];
                $this->users[$username][parent::EXPERTISE]      = $user['expertise'];
                $this->users[$username][parent::CHAKRAPOWER]    = $user['chakraPower'];
                $this->users[$username][parent::CRITICALSTRIKE] = $user['criticalStrike'];

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
                                $type = $action_data[0];
                                $id = $action_data[1];
                                $level = $action_data[2];
                                $targeting = $action_data[3];

                                //pulling all weapons listed into an array
                                $weapons = array();
                                for($i = 4; $i < count($action_data); $i++)
                                {
                                    $weapons[] = $action_data[$i];
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
                                            `reagents`
                                             from `jutsu` where `id` in ('.$jids.')';

                            //getting jutsu data from database, nested for stability.
                            //if the query fails it will try again.
                            try { $jutsus = $GLOBALS['database']->fetch_data($query); }
                            catch (Exception $e)
                            {
                                try { $jutsus = $GLOBALS['database']->fetch_data($query); }
                                catch (Exception $e)
                                {
                                    try { $jutsus = $GLOBALS['database']->fetch_data($query); }
                                    catch (Exception $e)
                                    {
                                        throw new Exception('There was an issue with collecting jutsu data. (addUser:Battle)');
                                    }
                                }
                            }
                            //making sure that response from db was good.
                            if(is_array($jutsus) && count($jutsus) != 0)
                            {
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
                                                                                             'hea_cost' => $jutsu['hea_cost'], 'reagents' => $jutsu['reagents'],
                                                                                             'allow_elemental_weapons' => false);

                                    //check for elemental affinity and apply bonus's from elemental mastery.
                                    $found_mastery = -1;
                                    if($jutsu['element'] != '' && $jutsu['element'] != 'none' && $jutsu['element'] != 'NONE' && $jutsu['element'] != 'None')
                                    {
                                        if($jutsu['element'] == $this->users[$username][parent::ELEMENTS][0] && $this->users[$username]['rank'] >= 3)
                                            $found_mastery = 0;

                                        else if ($jutsu['element'] == $this->users[$username][parent::ELEMENTS][1] && $this->users[$username]['rank'] >= 4)
                                            $found_mastery = 1;

                                        else if ($jutsu['element'] == $this->users[$username][parent::ELEMENTS][3] && $this->users[$username]['rank'] >= 4)
                                            $found_mastery = 2;
                                    }

                                    // if a mastery fasfound record its information.
                                    if($found_mastery != -1)
                                    {
                                        $mastery_percentage = $this->users[$username][parent::ELEMENTMASTERIES][$found_mastery]; //getting matsery percentage

                                        //updating costs
                                        $this->users[$username]['jutsus'][$jutsu['jid']]['hea_cost'] -= $this->users[$username]['jutsus'][$jutsu['jid']]['hea_cost'] * (($mastery_percentage * 0.35) / 100);

                                        //updating max_uses
                                        if($mastery_percentage >= 100)
                                            $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 0;

                                        else if($mastery_percentage >= 70)
                                            $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 1;

                                        else if($mastery_percentage >= 40)
                                            $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 2;

                                        else if($mastery_percentage >= 10)
                                            $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 3;

                                        else
                                            $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 4;

                                        //making sure that max_uses is above zero
                                        if($this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] < 0)
                                            $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] = 0;

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
                                            `on_jutsu_tags`, `targeting_type`, `max_uses`
                                                FROM `items` where `id` in ('.$weapon_ids.')';

                            //getting jutsu data from database, nested for stability.
                            //if the query fails it will try again.
                            try { $weapons = $GLOBALS['database']->fetch_data($query); }
                            catch (Exception $e)
                            {
                                try { $weapons = $GLOBALS['database']->fetch_data($query); }
                                catch (Exception $e)
                                {
                                    try { $weapons = $GLOBALS['database']->fetch_data($query); }
                                    catch (Exception $e)
                                    {
                                        throw new Exception('There was an issue with collecting jutsu data. (addUser:Battle)');
                                    }
                                }
                            }

                            //double checking to make sure that db response was good
                            if(is_array($weapons) && count($weapons) != 0)
                            {
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
                                            `on_jutsu_tags`, `targeting_type`, `max_uses`
                                                FROM `items` where `id` in ('.$item_ids.')';

                            //getting jutsu data from database, nested for stability.
                            //if the query fails it will try again.
                            try { $items = $GLOBALS['database']->fetch_data($query); }
                            catch (Exception $e)
                            {
                                try { $items = $GLOBALS['database']->fetch_data($query); }
                                catch (Exception $e)
                                {
                                    try { $items = $GLOBALS['database']->fetch_data($query); }
                                    catch (Exception $e)
                                    {
                                        throw new Exception('There was an issue with collecting jutsu data. (addUser:Battle)');
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
                        $targeting = $this->users[$username]['instructions'][$key][0];
                        $control = $this->users[$username]['instructions'][$key][1];
                        $percentage = $this->users[$username]['instructions'][$key][2];
                        $breaking = $this->users[$username]['instructions'][$key][3];

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
                                               'battle_description' => '??????????????',
                                               'element' => 'None',
                                               'village' => '',
                                               'bloodline' => '',
                                               'clan' => '',
                                               'kage' => '',
                                               'jutsu_type' => 'normal',
                                               'splitJutsu' => 'no',
                                               'loyaltyRespectReq' => NULL,
                                               'tags' => 'damage:(value>(250,250);targetGenerals>(highest,highest);targetType>highest)',
                                               'cooldown_pool_set' => array(''),
                                               'cooldown_pool_check' => array(''),
                                               'reagents' => NULL,
                                               'weapons' => NULL,
                                               'max_level' => '1000',
                                               'override_cooldown' => '0',
                                               'targeting_type' => 'opponent');
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
                    'max_uses' => 1000,
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

                //checking for multiples of the same ai, if so clone them

                $clones_to_make = array_count_values($usernames);

                $restoring_username = explode('#',$username);
                $restoring_username[0] = strtolower(trim($restoring_username[0]));

                $clones_to_make = $clones_to_make[$restoring_username[0]] - 1;

                if( count($restoring_username) == 1 )
                    $start = 2;
                else
                    $start = ((int)$restoring_username[1])+1;

                for($i = 0; $i < $clones_to_make; $i++)
                {
                    $this->users[ucfirst($restoring_username[0]).' #'.($i+$start)] = $this->users[$username];
                }
            }
        }
        //if the query failed display an error message.
        else
            //if debugging show debugging message.
            if($this->debugging)
                $GLOBALS['DebugTool']->push('could not fetch user data. query: '."SELECT `users`.`id`, `users`.`bloodline`, `users_statistics`.`rank_id`, `users_statistics`.`cur_health`, `users_statistics`.`max_health`, `users_statistics`.`cur_sta`, `users_statistics`.`max_sta`, `users_statistics`.`cur_cha`, `users_statistics`.`max_cha`, `users_statistics`.`tai_off`, `users_statistics`.`tai_def`, `users_statistics`.`nin_off`, `users_statistics`.`nin_def`, `users_statistics`.`gen_off`, `users_statistics`.`gen_def`, `users_statistics`.`weap_off`, `users_statistics`.`weap_def`, `users_statistics`.`intelligence`, `users_statistics`.`willpower`, `users_statistics`.`speed`, `users_statistics`.`strength`, `users_statistics`.`specialization` from `users`inner join `users_statistics` on (`users`.`id` = `users_statistics`.`uid`) where `username` = '".$username."'", 'there is an issue with this user: '.$username, __METHOD__, __FILE__, __LINE__);
            //if not debugging throw exception.
            else
                throw new Exception ('could not fetch user data');

    }


    //addUser extends the addUser method of Tags
    //gets all data required by the user and adds it to the cache.
    function addUser($usernames, $team)
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

        //getting user data from database
        $select_statement = "SELECT
            `users`.`id`, `users`.`bloodline`, `users`.`village`, `users`.`username`,
            `users`.`latitude`, `users`.`longitude`,
            `users_statistics`.`rank_id`, `users_statistics`.`rank`,
            `users_statistics`.`cur_health`, `users_statistics`.`max_health`,
            `users_statistics`.`cur_sta`, `users_statistics`.`max_sta`,
            `users_statistics`.`cur_cha`, `users_statistics`.`max_cha`,

            `users_statistics`.`tai_off`, `users_statistics`.`tai_def`,
            `users_statistics`.`nin_off`, `users_statistics`.`nin_def`,
            `users_statistics`.`gen_off`, `users_statistics`.`gen_def`,
            `users_statistics`.`weap_off`, `users_statistics`.`weap_def`,

            `users_statistics`.`intelligence`, `users_statistics`.`willpower`,
            `users_statistics`.`speed`,
            `users_statistics`.`strength`, `users_statistics`.`specialization`,

            `bloodlines`.`tags`,

            `users_loyalty`.`vil_loyal_pts`

            from `users`
            inner join `users_statistics` on (`users`.`id` = `users_statistics`.`uid`)
            inner join `users_loyalty` on (`users_loyalty`.`uid` = `users`.`id`)
            left join `bloodlines` on (`users`.`bloodline` = `bloodlines`.`name`)

            where `username` IN (".$temp_names.")";

        //sending query to db and getting response.
        //nested for stability, will call up to 3 times if there are errors.
        try{ $query = $GLOBALS['database']->fetch_data($select_statement); }
        catch(Exception $E)
        {
            try{ $query = $GLOBALS['database']->fetch_data($select_statement); }
            catch(Exception $E)
            {
                try{ $query = $GLOBALS['database']->fetch_data($select_statement); }
                catch(Exception $E)
                {
                    throw  new Exception('there was an issue collecting user data. (addUser:battle)');
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

                //setting data
                {
                    //setting pools
                    $this->users[$username][parent::HEALTH] = $user['cur_health'];
                    $this->users[$username][parent::HEALTHMAX] = $user['max_health'];
                    $this->users[$username][parent::STAMINA] = $user['cur_sta'];
                    $this->users[$username][parent::STAMINAMAX] = $user['max_sta'];
                    $this->users[$username][parent::CHAKRA] = $user['cur_cha'];
                    $this->users[$username][parent::CHAKRAMAX] = $user['max_cha'];
                    $this->users[$username]['ai'] = false;

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
                    $this->users[$username]['display_rank'] = $user['rank'];
                    $this->users[$username]['village'] = $user['village'];

                    //getting lat and lon if syndicate
                    if($this->users[$username]['village'] == 'Syndicate')
                    {
                        $this->users[$username]['latitude'] = $user['latitude'];
                        $this->users[$username]['longitude'] = $user['longitude'];
                    }

                    //setting UID
                    $this->users[$username]['uid'] = $user['id'];

                    //setting elements
                    $elements = new Elements($user['id'], $user['rank_id']);
                    $this->users[$username][parent::ELEMENTS] = $elements->getUserElements();
                    $this->users[$username][parent::ELEMENTMASTERIES] = $elements->getUserElementMastery(NULL, $user['rank_id']);

                    //setting bloodline
                    $this->users[$username][parent::BLOODLINE] = $user['bloodline'];

                    //setting generals
                    $this->users[$username][parent::STRENGTH]       = $user['strength'];
                    $this->users[$username][parent::WILLPOWER]      = $user['willpower'];
                    $this->users[$username][parent::INTELLIGENCE]   = $user['intelligence'];
                    $this->users[$username][parent::SPEED]          = $user['speed'];
                    $this->users[$username][parent::SPECIALIZATION] = $user['specialization'];

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
                    $this->users[$username]['avatar'] = functions::getAvatar($user['id'], '.');

                    //priming the update location
                    $this->users[$username]['update'];
                }

                //adding bloodline tags
                if(isset($user['tags']) && $user['tags'] != '')
                    $this->addTags( $this->parseTags($user['tags']), $username, $username, parent::BLOODLINE);

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
                    `items`.`targeting_type`, `items`.`consumable`

                    FROM `users_inventory`
                    INNER JOIN `items` on (`users_inventory`.`iid` = `items`.`id`)
                    WHERE `users_inventory`.`trading` is null
                    and `users_inventory`.`finishProcessing` = 0
                    and `users_inventory`.`uid` = ".$this->users[$username]['uid'];

                    //getting query response from db
                    //nested calls for catching errors, will call 1 to 3 times.
                    try{ $items = $GLOBALS['database']->fetch_data($select_statement); }
                    catch ( Exception $E )
                    {
                        try{ $items = $GLOBALS['database']->fetch_data($select_statement); }
                        catch ( Exception $E )
                        {
                            try{ $items = $GLOBALS['database']->fetch_data($select_statement); }
                            catch ( Exception $E )
                            {
                                throw new Exception('There was an issue with getting user inventory data. (adduser:battle) '.$select_statement);
                            }
                        }
                    }


                    //if there is atleast one item
                    if(isset($items[0]['name']))
                    {
                        //makingsure that weapon used array is initialized
                        if(!isset($this->users[$username]['weapons_used']))
                            $this->users[$username]['weapons_used'] = array();

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
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['weapon_classifications'] = $item['weapon_classifications'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['name'] = $item['name'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['iid'] = $item['iid'];
                                $this->users[$username]['equipment_used'][$item['iid']] = array('uses' => 0, 'max_uses' => $item['max_uses']);

                                $this->users[$username][parent::EQUIPMENT][$item['id']]['element'] = $item['element'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['targeting_type'] = $item['targeting_type'];

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
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['on_use_tags']              = $item['on_use_tags'];
                                $this->users[$username][parent::EQUIPMENT][$item['id']]['on_jutsu_tags']              = $item['on_jutsu_tags'];

                                //recording armor
                                $this->users[$username][parent::ARMORBASE]      += $item[parent::ARMORBASE];

                                //if this would break the mastery cap fix it
                                if($this->users[$username][parent::MASTERY] + $item[parent::MASTERY] > $this->MASTERYCAP[$user['rank_id']])
                                {
                                    $diff = ($this->users[$username][parent::MASTERY] + $item[parent::MASTERY]) - $this->MASTERYCAP[$user['rank_id']];
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::MASTERY] = $diff;
                                    $this->users[$username][parent::MASTERY] = $this->MASTERYCAP[$user['rank_id']];
                                }
                                //if this would drop mastery less than 0
                                else if($this->users[$username][parent::MASTERY] + $item[parent::MASTERY] < 0)
                                {
                                    $diff = $this->users[$username][parent::MASTERY] * -1;
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::MASTERY] = $diff;
                                    $this->users[$username][parent::MASTERY] = 0;
                                }
                                else
                                    $this->users[$username][parent::MASTERY]    += $item[parent::MASTERY];



                                //if this would break the stability cap fix it
                                if($this->users[$username][parent::STABILITY] + $item[parent::STABILITY] > $this->STABILITYCAP[$user['rank_id']])
                                {
                                    $diff = ($this->users[$username][parent::STABILITY] + $item[parent::STABILITY]) - $this->STABILITYCAP[$user['rank_id']];
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::STABILITY] = $diff;
                                    $this->users[$username][parent::STABILITY] = $this->STABILITYCAP[$user['rank_id']];
                                }
                                //if this would drop stability less than 0
                                else if($this->users[$username][parent::STABILITY] + $item[parent::STABILITY] < 0)
                                {
                                    $diff = $this->users[$username][parent::STABILITY] * -1;
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::STABILITY] = $diff;
                                    $this->users[$username][parent::STABILITY] = 0;
                                }
                                else
                                    $this->users[$username][parent::STABILITY]      += $item[parent::STABILITY];



                                //if this would break the accuracy cap fix it
                                if($this->users[$username][parent::ACCURACY] + $item[parent::ACCURACY] > $this->ACCURACYCAP[$user['rank_id']])
                                {
                                    $diff = ($this->users[$username][parent::ACCURACY] + $item[parent::ACCURACY]) - $this->ACCURACYCAP[$user['rank_id']];
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::ACCURACY] = $diff;
                                    $this->users[$username][parent::ACCURACY] = $this->ACCURACYCAP[$user['rank_id']];
                                }
                                //if this would drop accuracy less than 0
                                else if($this->users[$username][parent::ACCURACY] + $item[parent::ACCURACY] < 0)
                                {
                                    $diff = $this->users[$username][parent::ACCURACY] * -1;
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::ACCURACY] = $diff;
                                    $this->users[$username][parent::ACCURACY] = 0;
                                }
                                else
                                    $this->users[$username][parent::ACCURACY]       += $item[parent::ACCURACY];



                                //if this would break the expertise cap fix it
                                if($this->users[$username][parent::EXPERTISE] + $item[parent::EXPERTISE] > $this->EXPERTISECAP[$user['rank_id']])
                                {
                                    $diff = ($this->users[$username][parent::EXPERTISE] + $item[parent::EXPERTISE]) - $this->EXPERTISECAP[$user['rank_id']];
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::EXPERTISE] = $diff;
                                    $this->users[$username][parent::EXPERTISE] = $this->EXPERTISECAP[$user['rank_id']];
                                }
                                //if this would drop accuracy less than 0
                                else if($this->users[$username][parent::EXPERTISE] + $item[parent::EXPERTISE] < 0)
                                {
                                    $diff = $this->users[$username][parent::EXPERTISE] * -1;
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::EXPERTISE] = $diff;
                                    $this->users[$username][parent::EXPERTISE] = 0;
                                }
                                else
                                    $this->users[$username][parent::EXPERTISE]      += $item[parent::EXPERTISE];



                                //if this would break the chakra power cap fix it
                                if($this->users[$username][parent::CHAKRAPOWER] + $item['chakra_power'] > $this->CHAKRAPOWERCAP[$user['rank_id']])
                                {
                                    $diff = ($this->users[$username][parent::CHAKRAPOWER] + $item['chakra_power']) - $this->CHAKRAPOWERCAP[$user['rank_id']];
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::CHAKRAPOWER] = $diff;
                                    $this->users[$username][parent::CHAKRAPOWER] = $this->CHAKRAPOWERCAP[$user['rank_id']];
                                }
                                //if this would drop accuracy less than 0
                                else if($this->users[$username][parent::CHAKRAPOWER] + $item['chakra_power'] < 0)
                                {
                                    $diff = $this->users[$username][parent::CHAKRAPOWER] * -1;
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::CHAKRAPOWER] = $diff;
                                    $this->users[$username][parent::CHAKRAPOWER] = 0;
                                }
                                else
                                    $this->users[$username][parent::CHAKRAPOWER]    += $item['chakra_power'];



                                //if this would break the critical strike cap fix it
                                if($this->users[$username][parent::CRITICALSTRIKE] + $item['critical_strike'] > $this->CRITICALSTRIKECAP[$user['rank_id']])
                                {
                                    $diff = ($this->users[$username][parent::CRITICALSTRIKE] + $item['critical_strike']) - $this->CRITICALSTRIKECAP[$user['rank_id']];
                                    $this->users[$username][parent::EQUIPMENT][$item['id']][parent::CRITICALSTRIKE] = $diff;
                                    $this->users[$username][parent::CRITICALSTRIKE] = $this->CRITICALSTRIKECAP[$user['rank_id']];
                                }
                                //if this would drop accuracy less than 0
                                else if($this->users[$username][parent::CRITICALSTRIKE] + $item['critical_strike'] < 0)
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
                            if($item['consumable'] == 'yes')
                            {
                                //record this item
                                $this->users[$username]['items'][$item['id']] = array( 'name' => $item['name'], 'targeting_type' => $item['targeting_type'],
                                                                                    'uses' => $item['uses'], 'times_used' => $item['times_used'],
                                                                                    'max_uses' => $item['max_uses'], 'stack' => $item['stack'],
                                                                                    'iid' => $item['iid'], 'on_use_tags'  => $item['on_use_tags']);

                                //mark it as un used
                                $this->users[$username]['items_used'][$item['iid']] = 0;
                            }
                        }
                    }

                }
                //processing jutsus
                //getting jutsu data
                $select_statement = "SELECT `jid`, `level`, `name`, `description`, `battle_description`, `element`, `village`,
                             `bloodline`, `clan`, `kage`, `cha_cost`, `sta_cost`, `hea_cost`, `targeting_type`,
                             `jutsu_type`, `splitJutsu`, `loyaltyRespectReq`, `tags`, `cooldown_pool_set`,
                             `cooldown_pool_check`, `reagents`, `weapons`, `max_level`, `exp`, `override_cooldown`, `max_uses`
                             from `users_jutsu` inner join `jutsu` on (`jid` = `id`) where locate(`users_statistics`.`Group`, `users_jutsu`.`tagged` ) > 0
                             and uid = ".$this->users[$username]['uid'];

                //getting dbs response to query
                //nested for stability. if there is an error it will try again up to 3 times.
                try { $jutsus = $GLOBALS['database']->fetch_data($select_statement); }
                catch (Exception $e)
                {
                    try { $jutsus = $GLOBALS['database']->fetch_data($select_statement); }
                    catch (Exception $e)
                    {
                        try { $jutsus = $GLOBALS['database']->fetch_data($select_statement); }
                        catch (Exception $e)
                        {
                            throw new Exception('There was an issue with collecting jutsu data. (addUser:Battle)');
                        }
                    }
                }

                //makeing sure jutsu data is good.
                if(is_array($jutsus))
                {
                    //foeach jutsu found
                    foreach($jutsus as $jutsu_key => $jutsu)
                    {

                        //check loyalty
                        if($jutsu['loyaltyRespectReq'] == '' || $jutsu['loyaltyRespectReq'] < $user['vil_loyal_pts'])
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

                            //processing reagent data
                            if($jutsu['reagents'] !== '')
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
                            }

                            //make sure user jutsu location is set
                            if(!isset($this->users[$username]['jutsus']))
                                $this->users[$username]['jutsus'] = array();

                            //set personal jutsu level for this jutsu to user information.
                            $this->users[$username]['jutsus'][$jutsu['jid']] = array('level'=>$jutsu['level'], 'exp'=>$jutsu['exp'],
                                                                                     'max_uses'=>$jutsu['max_uses'], 'uses' => 0,
                                                                                     'reagent_status'=>true, 'cha_cost' => $jutsu['cha_cost'],
                                                                                     'sta_cost' => $jutsu['sta_cost'], 'hea_cost' => $jutsu['hea_cost'],
                                                                                     'allow_elemental_weapons' => false);

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
                                if($mastery_percentage >= 100)
                                    $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 0;

                                else if($mastery_percentage >= 70)
                                    $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 1;

                                else if($mastery_percentage >= 40)
                                    $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 2;

                                else if($mastery_percentage >= 10)
                                    $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 3;

                                else
                                    $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] -= 4;

                                //making sure that max_uses is above zero
                                if($this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] < 0)
                                    $this->users[$username]['jutsus'][$jutsu['jid']]['max_uses'] = 0;

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

                //defining the basic attack jutsu
                if(!isset($this->jutsus[-1]))
                {
                    $this->jutsus[-1] = array( 'jid' => -1,
                                               'name' => 'Basic Attack',
                                               'description' => 'a basic attack.',//////
                                               'battle_description' => '??????????????',//////
                                               'element' => 'None',
                                               'village' => '',
                                               'bloodline' => '',
                                               'clan' => '',
                                               'kage' => '',
                                               'jutsu_type' => 'normal',
                                               'splitJutsu' => 'no',
                                               'loyaltyRespectReq' => NULL,
                                               'tags' => 'damage:(value>(250,250);targetGenerals>(highest,highest);targetType>highest)',
                                               'cooldown_pool_set' => array(''),
                                               'cooldown_pool_check' => array(''),
                                               'reagents' => NULL,
                                               'weapons' => NULL,
                                               'max_level' => '1000',
                                               'override_cooldown' => '0',
                                               'targeting_type' => 'opponent' );
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
                    'max_uses' => 1000,
                    'uses' => 0,
                    'reagent_status' => true,
                    'hea_cost' => 0,
                    'sta_cost' => 0,
                    'cha_cost' => 0,
                    'allow_elemental_weapons' => false
                    );

                //recording dsr
                $this->updateDR_SR($username);
                $this->users[$username]['DSR'] = $this->findDSR($username);
            }
        }

        //if the query failed display an error message.
        else
            //if debugging show debugging message.
            if($this->debugging)
                $GLOBALS['DebugTool']->push('could not fetch user data. query: '."SELECT `users`.`id`, `users`.`bloodline`, `users_statistics`.`rank_id`, `users_statistics`.`cur_health`, `users_statistics`.`max_health`, `users_statistics`.`cur_sta`, `users_statistics`.`max_sta`, `users_statistics`.`cur_cha`, `users_statistics`.`max_cha`, `users_statistics`.`tai_off`, `users_statistics`.`tai_def`, `users_statistics`.`nin_off`, `users_statistics`.`nin_def`, `users_statistics`.`gen_off`, `users_statistics`.`gen_def`, `users_statistics`.`weap_off`, `users_statistics`.`weap_def`, `users_statistics`.`intelligence`, `users_statistics`.`willpower`, `users_statistics`.`speed`, `users_statistics`.`strength`, `users_statistics`.`specialization` from `users`inner join `users_statistics` on (`users`.`id` = `users_statistics`.`uid`) where `username` = '".$username."'", 'there is an issue with this user: '.$username, __METHOD__, __FILE__, __LINE__);
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
        if( isset( $this->users[$owner_username]['equipment'][$weapon_id]) && $this->users[$owner_username]['equipment_used'][ $this->users[$owner_username]['equipment'][$weapon_id]['iid'] ]['uses'] < $this->users[$owner_username]['equipment_used'][ $this->users[$owner_username]['equipment'][$weapon_id]['iid'] ]['max_uses'] )
        {
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
        if( isset( $this->users[$owner_username]['items'][$invin_id]) && $this->users[$owner_username]['items'][$invin_id]['stack'] > 0 && $this->users[$owner_username]['items_used'][ $this->users[$owner_username]['items'][$invin_id]['iid'] ] < $this->users[$owner_username]['items'][$invin_id]['max_uses'])
        {
            //updating times used and marking at a used item
            $this->users[$owner_username]['items'][$invin_id]['times_used']++;
            $this->users[$owner_username]['items_used'][ $this->users[$owner_username]['items'][$invin_id]['iid'] ]++;

            if(!isset($this->users[$owner_username]['update']['times_used']))
                $this->users[$owner_username]['update']['times_used'] = array();

            $this->users[$owner_username]['update']['times_used'][$invin_id] = true;

            //getting item data.
            $item = $this->users[$owner_username]['items'][$invin_id];

            //adding tags to the system from the item.
            $this->addTags($this->parseTags($item['on_use_tags']), $target_username, $owner_username, parent::ITEM, $invin_id, false, $item['targeting_type']);

            //marks item as used
            //actuall db consumption does not occur until the battle is over.
            $this->itemConsumption( $invin_id );
        }
    }

    //itemConsumption
    //this method is called by useItem.
    //this method handles the consumption of items directly used in combat.
    public function itemConsumption( $invin_id )
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
            unset($this->users[$owner_username]['items'][$invin_id]);

            //marking item for removal from user at end of battle
            if(!isset($this->users[$owner_username]['update']['remove']))
                $this->users[$owner_username]['update']['remove'] = array();

            $this->users[$owner_username]['update']['remove'][$invin_id] = true;

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
    public function doJutsu($target_username, $owner_username, $jutsu_id, $weapon_ids = false)
    {

     // * check isset jutsu and checking pool cost
        if( isset( $this->users[$owner_username]['jutsus'][$jutsu_id] ) && $this->users[$owner_username]['jutsus'][$jutsu_id]['uses'] < $this->users[$owner_username]['jutsus'][$jutsu_id]['max_uses'] &&
            $this->users[$owner_username][parent::HEALTH]  > $this->users[$owner_username]['jutsus'][$jutsu_id]['hea_cost'] &&
            $this->users[$owner_username][parent::STAMINA] > $this->users[$owner_username]['jutsus'][$jutsu_id]['sta_cost'] &&
            $this->users[$owner_username][parent::CHAKRA]  > $this->users[$owner_username]['jutsus'][$jutsu_id]['cha_cost'])
        {
     //     *
     //     * check isset weapons
            if( !is_array($weapon_ids) && $weapon_ids !== false )
                $weapon_ids = array($weapon_ids);

            //flag for processing weapons.
            $weapon_error = false;

            //making sure that weapons exist
            if($weapon_ids !== false)
            {
                //for each weapon_id
                foreach( $weapon_ids as $id )
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
                    else if( $this->users[$owner_username]['equipment_used'][ $this->users[$owner_username][parent::EQUIPMENT][$id]['iid'] ]['uses'] >= $this->users[$owner_username]['equipment_used'][ $this->users[$owner_username][parent::EQUIPMENT][$id]['iid'] ]['max_uses'])
                    {
                        //mark the error
                        $weapon_error = true;

                        //if debugging show the error
                        if($this->debugging)
                            $GLOBALS['DebugTool']->push($weapon_ids,'no uses left', __METHOD__, __FILE__, __LINE__);
                    }
            }

            //if there has not been an error yet
            if( $weapon_error !== true )
            {
     //         *
     //         * check for split jutsu and process if so
                if($this->jutsus[$jutsu_id]['splitJutsu'] == 'yes')
                {
                    //check for specialization
                    if($this->users[$owning_username][parent::SPECIALIZATION] != '')
                    {
                        //get specialization
                        $specialization = explode(':',$this->users[$owning_username][parent::SPECIALIZATION]);
                        if(count($specialization) == 2)
                            //if specialization is good record it.
                            if($specialization[1] == 1)
                                $specialization = $specialization[0];

                            //if specialization is bad set it to default
                            else
                            {
                                if($this->debugging)
                                    $GLOBALS['DebugTool']->push('set specialization to N','splitJutsu used with no specialization ', __METHOD__, __FILE__, __LINE__);

                                $specialization = 'N';
                            }
                        //if specialization is bad set it to deault
                        else
                        {
                            if($this->debugging)
                                $GLOBALS['DebugTool']->push('set specialization to N','splitJutsu used with no specialization ', __METHOD__, __FILE__, __LINE__);

                            $specialization = 'N';
                        }
                    }
                    //if specialization is bad set it to default
                    else
                    {
                        if($this->debugging)
                            $GLOBALS['DebugTool']->push('set specialization to N','splitJutsu used with no specialization ', __METHOD__, __FILE__, __LINE__);

                        $specialization = 'N';
                    }

                    //getting cooldown_pool_set
                    if(is_array($this->jutsus[$jutsu_id]['cooldown_pool_set']))
                        $cooldown_pool_set = $this->jutsus[$jutsu_id]['cooldown_pool_set'][$specialization];
                    else
                        $cooldown_pool_set = $this->jutsus[$jutsu_id]['cooldown_pool_set'];

                    //getting cooldown_pool_check
                    if(is_array($this->jutsus[$jutsu_id]['cooldown_pool_check']))
                        $cooldown_pool_check = $this->jutsus[$jutsu_id]['cooldown_pool_check'][$specialization];
                    else
                        $cooldown_pool_check = $this->jutsus[$jutsu_id]['cooldown_pool_check'];

                    //getting reagent
                    if(is_array($this->jutsus[$jutsu_id]['reagents']))
                        $reagents = $this->jutsus[$jutsu_id]['reagents'][$specialization];
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
                    if ( $this->checkAndConsumeReagents($owner_username, $reagents) )
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
                            if($this->jutsus[$jutsu_id]['weapons'] === false || $this->jutsus[$jutsu_id]['weapons'] == '')
                                $weapon_error = true;
                            else
                            {
                                //for each weapon
                                foreach( explode(',',$this->jutsus[$jutsu_id]['weapons']) as $weapon_group_key => $required_weapon_group )
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
                                            $jutsu_element =  $this->jutsus[$jutsu_id]['element'];
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

     //                 *
     //                 * take cost of jutsu
                        //removing price of the jutsu use from the user
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


     //                 *
     //                 * applyJutsu
                        $this->addTags($this->parseTags($this->jutsus[$jutsu_id]['tags']), $target_username, $owner_username, parent::JUTSU, false, $this->users[$owner_username]['jutsus'][$jutsu_id]['level'], $this->jutsus[$jutsu_id]['targeting_type'], $weapon_power, $weapon_ids);
                        if(count($weapon_tags) != 0)
                            foreach($weapon_tags as $id => $tags)
                                $this->addTags($this->parseTags($tags), $target_username, $owner_username, parent::WEAPON, $id, false, $this->jutsus[$jutsu_id]['targeting_type']);
     //                 *
     //                 * call awwardJutsuExp
                        $this->awardJutsuExp($jutsu_id, $owner_username);
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
    public function awardJutsuExp($jutsu_id, $username)
    {
        //marking the jutsu for update
        if(!isset($this->users[$username]['update']['jutsus']))
            $this->users[$username]['update']['jutsus'] = array();

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

            $this->users[$username]['jutsus'][$jutsu_id]['exp'] += 2000/$this->users[$username]['jutsus'][$jutsu_id]['level'];

            if( $this->users[$username]['jutsus'][$jutsu_id]['exp'] >= $exp_per_level[$type] )
            {
                $this->users[$username]['jutsus'][$jutsu_id]['exp'] = 0;
                $this->users[$username]['jutsus'][$jutsu_id]['level']++;
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
                $select_statement = 'SELECT `id`, `iid`, `stack` from `users_inventory` where `uid` = '.$this->users[$username]['uid'].' and `iid` in ('.implode(',',array_keys($reagents)).')';
                $query = '';
                //nested try catch for database query
                try{ $query = $GLOBALS['database']->fetch_data($select_statement); }
                catch(Exception $E)
                {
                    try{ $query = $GLOBALS['database']->fetch_data($select_statement); }
                    catch(Exception $E)
                    {
                        try{ $query = $GLOBALS['database']->fetch_data($select_statement); }
                        catch(Exception $E)
                        {
                            throw  new Exception('there was an issue collecting user data. (addUser:battle)');
                        }
                    }
                }
                if(!is_array($query))
                    return false;
                //initializing variables the are used in the forloop
                $results = array();
                $return_flags = array();
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
                                    }
                                    else if ($inventory_row['stack'] + $thus_far_counter > $reagent_count && $thus_far_counter != $reagent_count)
                                    {
                                        $for_update[$inventory_row['id']] = $reagent_count - $thus_far_counter;
                                        $thus_far_counter = $reagent_count;
                                    }
                                }
                            }
                        }
                        $delete_query = 'DELETE FROM `users_inventory` WHERE `id` IN ('.implode(',',$for_delete).')';
                        $update_query = 'UPDATE `users_inventory` SET `stack` = CASE ';
                        foreach($for_update as $id => $reduction)
                        {
                            $update_query .= 'WHEN id = '.$id.' THEN `stack` - '.$reduction.' ';
                        }
                        $update_query .= 'END WHERE `id` IN ('.implode(',',array_keys($for_update)).')';
                        try { $GLOBALS['database']->execute_query($delete_query); }
                        catch (Exception $e)
                        {
                            try { $GLOBALS['database']->execute_query($delete_query); }
                            catch (Exception $e)
                            {
                                try { $GLOBALS['database']->execute_query($delete_query); }
                                catch (Exception $e)
                                {
                                    $GLOBALS['DebugTool']->push('','there was an error updating item counts for reagent consumption.', __METHOD__, __FILE__, __LINE__);
                                    throw new Exception();
                                }
                            }
                        }
                        try { $GLOBALS['database']->execute_query($update_query); }
                        catch (Exception $e)
                        {
                            try { $GLOBALS['database']->execute_query($update_query); }
                            catch (Exception $e)
                            {
                                try { $GLOBALS['database']->execute_query($update_query); }
                                catch (Exception $e)
                                {
                                    $GLOBALS['DebugTool']->push('','there was an error updating item counts for reagent consumption.', __METHOD__, __FILE__, __LINE__);
                                    throw new Exception();
                                }
                            }
                        }
                        $GLOBALS['database']->transaction_commit();
                    }
                    catch (Exception $e)
                    {
                        $GLOBALS['database']->transaction_rollback();
                        return false;
                    }
                }
                //return the result
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
        //record action for this will also need updated. it dosnt log an action so the users turn is not destroyed.
        echo 'not implemented yet please try something else';
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
        foreach($this->jutsus[$jid]['cooldown_pool_check'] as $cooldown_pool)
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
                                      'special' => 4,
                                      'loyalty' => 4,
                                      'kage' => 4,
                                      'village' => 5,
                                      'forbidden' => 5);

        //getting the value that the cool downs should be set to.
        if(is_numeric($this->jutsus[$jid]['override_cooldown']))
            $cooldown_value = $this->jutsus[$jid]['override_cooldown'];
        else
            $cooldown_value = $cooldown_type_values[$this->jutsus[$jid]['jutsu_type']];

        //add individual cool down to list of cooldowns
        $this->users[$username]['jutsus']['cooldowns'][$jid] = $cooldown_value + 1;


        //add each pool cool down to list of cooldowns
        foreach($this->jutsus[$jid]['cooldown_pool_set'] as $cooldown_pool)
            if($cooldown_pool !== '')
                $this->users[$username]['jutsus']['cooldowns'][$cooldown_pool] = $cooldown_value + 1;
    }

    //updateDurability
    //this is called by the doDamage method of Tags
    function updateDurability($tag, $final_damage_value, $rank)
    {
        $items_to_update = array();

        if(!$this->users[$tag->target]['ai'])
            foreach($this->users[$tag->target][self::EQUIPMENT] as $item_key => $item)
            {
            //update armor durability
                if($item['type'] == 'armor')
                {
                    //update local information about durability damage
                    $this->users[$tag->target][self::EQUIPMENT][$item_key]['durability'] -= $final_damage_value/$this->DURABILITYDAMAGESCALE[$rank];

                    if($this->users[$tag->target][self::EQUIPMENT][$item_key]['durability'] < 0)
                        $this->users[$tag->target][self::EQUIPMENT][$item_key]['durability'] = 0;

                    $items_to_update[$item_key] = $this->users[$tag->target][self::EQUIPMENT][$item_key]['durability'];

                    if($this->users[$tag->target][self::EQUIPMENT][$item_key]['durability'] == 0)
                    {
                        $this->removeEquipmentById($item_key);

                        //marking item for removal from user at end of battle
                        if(!isset($this->users[$tag->target]['update']['remove']))
                            $this->users[$tag->target]['update']['remove'] = array();

                        $this->users[$tag->target]['update']['remove'][$item_key] = true;

                        if(isset($this->users[$tag->target]['update']['durability'][$item_key]))
                            unset($this->users[$tag->target]['update']['durability'][$item_key]);
                    }
                    else
                    {
                        //marking item for removal from user at end of battle
                        if(!isset($this->users[$tag->target]['update']['durability']))
                            $this->users[$tag->target]['update']['durability'] = array();

                        $this->users[$tag->target]['update']['durability'][$item_key] = true;
                    }
                }

            //update weapon durability
                else if($item['type'] == 'weapon')
                {
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
                        if($weapon != 0 && !$this->users[$tag->owner]['ai'])
                        {
                            //update its durability
                            $this->users[$tag->owner][self::EQUIPMENT][$weapon]['durability'] -= ($final_damage_value/$this->DURABILITYDAMAGESCALE[$rank])/count($tag->weapon_ids);

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
                                $this->removeEquipmentById($weapon);

                                //marking weapon for removal from user at end of battle
                                if(!isset($this->users[$tag->owner]['update']['remove']))
                                    $this->users[$tag->owner]['update']['remove'] = array();

                                $this->users[$tag->owner]['update']['remove'][$weapon] = true;

                                if(isset($this->users[$tag->owner]['update']['durability'][$weapon]))
                                    unset($this->users[$tag->owner]['update']['durability'][$weapon]);

                                if(isset($this->users[$tag->owner]['update']['times_used'][$weapon]))
                                    unset($this->users[$tag->owner]['update']['times_used'][$weapon]);
                            }
                            else
                            {
                                //marking weapon for removal from user at end of battle
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


        /*
        //update durability
        $sql_start = 'UPDATE `users_inventory` set `durabilityPoints` = CASE ';

        $sql_when = '';
        $sql_where_in = '';
        $i = 0;
        $end = count($items_to_update) - 1;
        foreach($items_to_update as $id => $then)
        {
            $sql_when .= 'WHEN id = ' . $id . ' THEN ' . $then . ' ';

            if($i == 0) $sql_where_in .= '(';

            $sql_where_in .= $id;

            if($i == $end) $sql_where_in .= ')';
            else $sql_where_in .= ',';

            $i++;
        }

        $sql_end = 'END WHERE id in';

        $query = $sql_start . $sql_when . $sql_end . $sql_where_in;


        try { $GLOBALS['database']->execute_query($query); }
        catch (Exception $e)
        {
            try { $GLOBALS['database']->execute_query($query); }
            catch (Exception $e)
            {
                try { $GLOBALS['database']->execute_query($query); }
                catch (Exception $e)
                {
                    $GLOBALS['DebugTool']->push('','there was an error updating item durability.', __METHOD__, __FILE__, __LINE__);
                }
            }
        }*/



    }

    //this finds the turn order for this turn and returns it.
    //currently this is just a place holder and returns the usernames in alphabetic order
    function getTurnOrder()
    {
        $usernames = array_keys($this->users); //turn order defined here
        sort($usernames);
        return $usernames;
    }

    //this method is called to record an action made by a user.
    //the information recorded here will be used later to build the battle log for each turn.
    function recordAction($owning_username, $target_username, $action_type, $action_id, $action_name)
    {
        if(!isset($this->users[$owning_username]['actions']))
            $this->users[$owning_username]['actions'] = array();

        $this->users[$owning_username]['actions'][$this->turn_counter] = array( 'target' => $target_username, 'type' => $action_type, 'id' => $action_id, 'name' => $action_name );
    }

    //this method is called to find the first user who has not taken an action yet and
    //this method is aclled to find if all users have taken an action yet.
    function findFirstUser($turn_number)
    {
        foreach( $this->getTurnOrder() as $username )
        {
            if( ! $this->checkForAction($username, $turn_number) && !$this->users[$username]['ai'])
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
        foreach( $this->getTurnOrder() as $username )
        {
            //if battle log for user on this turn is not set, set it
            if(! isset($this->battle_log[$turn_counter][$username]))
                $this->battle_log[$turn_counter][$username] = $this->users[$username]['actions'][$turn_counter];
            //if battle log for user on this turn is set merge it with new data.
            else
                $this->battle_log[$turn_counter][$username] = array_merge($this->battle_log[$turn_counter][$username], $this->users[$username]['actions'][$turn_counter]);

            $this->battle_log[$turn_counter][$username]['team'] = $this->users[$username]['team'];
        }
    }

    //replacement for part of sf this is damage raiting.
    //this runs the user throught a modified version of the battle formula to find a good indication of the damage the user can do.
    //this returns their damage raiting.
    function findDR($user)
    {
        $jutsu_power = 1;

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

        $expertise_boost = $user_offense * ( $owners_expertise / ( $owners_rank * $this->EXPERTISEIDENTIFIER[$owners_rank] ) );

        $target_defense = 351 * ($target_defense * (1 + $targets_armor / 5000))**0.88;

        $attack_power = ( $jutsu_power + $power_boost ) * 100;

        $user_generals = ($user_general_1*0.7) + ($user_general_2*0.3);

        $target_generals = 351 * (($target_general_1*0.7) + ($target_general_2*0.3))**0.48;

        $offense_vs_defense = ( ( $user_offense + $expertise_boost ) / ( $target_defense + $target_generals ) )**0.1;

        $gen_vs_gen = ( $user_generals / $target_generals )**0.1;

        $battle_factor = ( $offense_vs_defense * $gen_vs_gen );

        $pure_offense =  $user_offense + $user_generals * 10 + $attack_power;

        $critical_power = ($owners_critical_strike / 30) ** ($owners_rank / 10);

        $chakra_calculation = ( $owners_chakra_power / 30 ) ** ( $owners_rank / 10 );

        if($offense_key == self::TAIJUTSU || $offense_key == self::BUKIJUTSU)
				$critical_multiplier = 1.0 + (0.1 + ($critical_power * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) / 100) * (1000 - (975 - ( $critical_power * 3 * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) * 5.4))/1000;
        else
                $critical_multiplier = 1.0 + (0.1 + ($chakra_calculation * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) / 100) * (1000 - (975 - ( $chakra_calculation * 3 * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) * 5.4))/1000;

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

        $post_flux_damage = $inital_damage * ( $fluxMin + $fluxMax / 200 );

        $damage_value = $post_flux_damage * $critical_multiplier;

        $weight = 10/3; //this weight will roughly convert the weight to 10% stats and 90% damage value. its very messy sadly but this works okay.

        return $damage_value + array_sum(array($user['strength'], $user['speed'], $user['intelligence'], $user['willpower'])) / $weight;
    }

    //replacement for part of sf this is survivability raiting
    //this runs the user throught a modified version of the battle formula to find a good indication of the damage the user will take.
    //this returns their survivability raiting.
    function findSR($user)
    {
        $jutsu_power = 1;

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

        $expertise_boost = $user_offense * ( $owners_expertise / ( $owners_rank * $this->EXPERTISEIDENTIFIER[$owners_rank] ) );

        $target_defense = 351 * ($target_defense * (1 + $targets_armor / 5000))**0.88;

        $attack_power = ( $jutsu_power + $power_boost ) * 100;

        $user_generals = ($user_general_1*0.7) + ($user_general_2*0.3);

        $target_generals = 351 * (($target_general_1*0.7) + ($target_general_2*0.3))**0.48;

        $offense_vs_defense = ( ( $user_offense + $expertise_boost ) / ( $target_defense + $target_generals ) )**0.1;

        $gen_vs_gen = ( $user_generals / $target_generals )**0.1;

        $battle_factor = ( $offense_vs_defense * $gen_vs_gen );

        $pure_offense = ($user_offense + $user_generals * 10 + $attack_power);

        $critical_power = ($owners_critical_strike / 30) ** ($owners_rank / 10);

        $chakra_calculation = ( $owners_chakra_power / 30 ) ** ( $owners_rank / 10 );

        $critical_multiplier = 1.0 + ((0.1 + ($critical_power * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) / 100) * (1000 - (975 - ( $critical_power * 3 * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) * 5.4))/1000)/2;

        $critical_multiplier += ( (0.1 + ($chakra_calculation * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) / 100) * (1000 - (975 - ( $chakra_calculation * 3 * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) * 5.4))/1000 ) / 2;

        

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

        $post_flux_damage = $inital_damage * ( $fluxMin + $fluxMax / 200 );

        $damage_value = $post_flux_damage * $critical_multiplier;

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

        $weight = 2/3; //this weight will roughly convert the weight to 50% max health and 50% current health.

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

        foreach($this->users as $username => $userdata)
        {
            if( isset($userdata['remove']) && $userdata['remove'])
            {
                $this->removeUserFromBattle($username);
                $users_removed[] = $username;
            }
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
            $query = "UPDATE `users` SET `status` = 'exiting_combat' where `username` in (".$users.")";

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
    public function removeUserFromCombat($username, $status)
    {
        $this->users[$username]['win_lose'] = $status;

        $team = $this->users[$username][self::TEAM];

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
    }

    //this function calls takeAiTurn for all ai in the battle.
    //this results in their turns being processed.
    public function processAI($turn_order)
    {
        foreach($turn_order as $username)
        {
            if( $this->users[$username]['ai'] )
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
                    //check if the jutsu is good, if so add it.
                    if( $this->AiCheckJutsu($owner_username, $jutsu['id']) )
                        $jutsus[] = $jutsu;
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
                    $weapon_power += $this->users[$owner_username][self::EQUIPMENT][$weapon_inventory_id]['strength'];
                else if($weapon_count == 2)
                    $weapon_power += $this->users[$owner_username][self::EQUIPMENT][$weapon_inventory_id]['strength'] * 0.6;
                else
                    $weapon_power += $this->users[$owner_username][self::EQUIPMENT][$weapon_inventory_id]['strength'] * (1/$weapon_count + 0.05 );

                $weapon_power /= 2;

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
        $this->addTags($this->parseTags($this->jutsus[$jutsu_id]['tags']), $target_username, $owner_username, parent::JUTSU, false, $this->users[$owner_username]['jutsus'][$jutsu_id]['ai_actions']['level'], $this->jutsus[$jutsu_id]['targeting_type'], $weapon_power, $weapon_ids);
        if(count($weapon_tags) != 0 && $weapon_tags !== 0)
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
        if( $this->users[$username]['jutsus'][$jid]['uses'] < $this->users[$username]['jutsus'][$jid]['max_uses'] )
        {
            //if this jutsu is not on cool down
            if($this->checkJutsuCooldown($jid, $this->jutsus[$jid]['cooldown_pool_check'], $username) == false)
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
        if( $this->users[$username]['equipment'][$weapon_id]['uses'] < $this->users[$username]['equipment'][$weapon_id]['max_uses'] )
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
        if( $this->users[$username]['items'][$item_id]['uses'] < $this->users[$username]['items'][$item_id]['max_uses'] )
            return true; //return true

        //if item has been used its max number of times
        else
            return false; //return false.
    }

    //UpdateTurnTimer
    //this method is called after a turn is processed to update the turn timer.
    public function UpdateTurnTimer()
    {
        $this->turn_timer = time() + 31;
    }

    //CheckForInactiveUsers
    //this method looks at all users at the end of a turn
    //if a user has not made an action they are defaulted to basic attack
    public function checkForInactiveUsers()
    {
        foreach($this->users as $username => $user)
        {
            if( !isset($user['actions'][$this->turn_counter]) || ( isset($user['actions'][$this->turn_counter]) && !is_array($user['actions'][$this->turn_counter])))
            {
                $target_username = false;

                $targets = array();

                foreach($this->users as $key => $value)
                {
                    if($user['team'] != $value['team'])
                    {
                        $targets[] = $key;
                    }
                }

                $target = $targets[ random_int(0, count($targets) - 1 ) ];

                $this->recordAction( $username, $target, 'jutsu', -1, $this->jutsus[-1]['name']);
                self::doJutsu( $target , $username, -1);
            }
        }
    }

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

        //if only 1 team is left set all current users to win 
        if($battle_end === true)
        {
            $users_removed = array();

            foreach($this->users as $username => $userdata)
            {
                $this->removeUserFromCombat($username, true);
                $this->removeUserFromBattle($username);
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
                $query = "UPDATE `users` SET `status` = 'exiting_combat' where `username` in (".$users.")";

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
                        }
                    }
                }
            }
        }
    }

    //processEndOfCombatForUser
    //this method updates the users database entries and...
    //this method returns an array detailing all the changes made.
    function processEndOfCombatForUser($username)
    {
        $return_data = array();

        $uid = $this->removed_users[$username]['uid'];

        $this->timeStamp = $GLOBALS['user']->load_time;

        $set = "`reinforcements` = 0, `battle_id` = 0, `last_battle` = '".$this->timeStamp."'";

        //update arena
        //pulled from old battle
        /*
        if( in_array($this->battle[0]['battle_type'], array("mirror_battle","torn_battle","arena") ) ){
                $userQuery .= ", `arena_cooldown` = '".$this->timeStamp."'";
            }
        */

        //check for loss of respect points
        //this should be marked on battle initiation
        //calculation is reduce by 50% and 25 points



        try
        {
            $GLOBALS['database']->transaction_start();

            //start here
            //updating the rest of user information

            //updating pools /////////////////////////////////////////////////////////////add pvp and normal experience here?
            $set .= ", `users_statistics`.`cur_health` = ".$this->removed_users[$username]['health'].",
                      `users_statistics`.`cur_sta` = ".$this->removed_users[$username]['stamina'].",
                      `users_statistics`.`cur_cha` = ".$this->removed_users[$username]['chakra'];

            //updating jutsu_exp
            if(isset($this->removed_users[$username]['update']['jutsus']))
            {
                $level_start = ', `users_jutsu`.`level` = CASE `users_jutsu`.`jid` ';
                $level_mid = '';
                $level_end = ' ELSE `users_jutsu`.`level` END ';

                $exp_start = ', `users_jutsu`.`exp` = CASE `users_jutsu`.`jid` ';
                $exp_mid = '';
                $exp_end = ' ELSE `users_jutsu`.`exp` END ';

                $uses_start = ', `users_jutsu`.`times_used` = CASE `users_jutsu`.`jid` ';
                $uses_mid = '';
                $uses_end = ' ELSE `users_jutsu`.`times_used` END ';

                $apply = false;

                foreach($this->removed_users[$username]['update']['jutsus'] as $jid => $status)
                {
                    if($status && $jid != -1)
                    {
                        $apply = true;

                        $level_mid .= ' WHEN '.$jid.' THEN '.$this->removed_users[$username]['jutsus'][$jid]['level'];
                        $exp_mid   .= ' WHEN '.$jid.' THEN '.$this->removed_users[$username]['jutsus'][$jid]['exp'];
                        $uses_mid  .= ' WHEN '.$jid.' THEN '.$this->removed_users[$username]['jutsus'][$jid]['times_used'] . ' + `users_jutsu`.`times_used` ';
                    }
                }

                if($apply === true)
                {
                    $set .= $level_start . $level_mid . $level_end .
                            $exp_start   . $exp_mid   . $exp_end   .
                            $uses_start  . $uses_mid  . $uses_end  ;
                }
            }

            //updating durability for armor and weapons
            if(isset($this->removed_users[$username]['update']['durability']))
            {
                $durability_start = ', `users_inventory`.`durabilityPoints` = CASE `users_inventory`.`id` ';
                $durability_mid = '';
                $durability_end = ' ELSE `users_inventory`.`durabilityPoints` END ';

                $apply = false;

                foreach($this->removed_users[$username]['update']['durability'] as $inven_id => $status)
                {
                    if($status)
                    {
                        $apply = true;
                        $durability_mid = ' WHEN '.$inven_id.' THEN '.$this->users[$tag->owner][self::EQUIPMENT][$inven_id]['durability'];
                    }
                }

                if($apply === true)
                {
                    $set .= $durability_start . $durability_mid . $durability_end;
                }
            }

            //stack
            if(isset($this->removed_users[$username]['update']['stack']))
            {
                $stack_start = ', `users_inventory`.`stack` = CASE `users_inventory`.`id` ';
                $stack_mid = '';
                $stack_end = ' ELSE `users_inventory`.`stack` END ';

                $apply = false;

                foreach( $this->removed_users[$username]['update']['stack'] as $inven_id => $status )
                {
                    if($status)
                    {
                        $apply = true;
                        $stack_mid = ' WHEN '.$inven_id.' THEN '.$this->users[$owner_username]['items'][$inven_id]['stack'];
                    }
                }

                if($apply == true)
                {
                    $set .= $stack_start . $stack_mid . $stack_end;
                }
            }

            //times used
            if(isset($this->removed_users[$username]['update']['times_used']))
            {
                $times_used_start = ', `users_inventory`.`times_used` = CASE `users_inventory`.`id` ';
                $times_used_mid = '';
                $times_used_end = ' ELSE `users_inventory`.`times_used` END ';

                $apply = false;

                foreach( $this->removed_users[$username]['update']['times_used'] as $inven_id => $status )
                {
                    if($status)
                    {
                        $apply = true;
                        $times_used_mid = ' WHEN '.$inven_id.' THEN '.$this->users[$owner_username]['items'][$inven_id]['times_used'];
                    }
                }

                if($apply == true)
                {
                    $set .= $times_used_start . $times_used_mid . $times_used_end;
                }
            }

            //respect
            if(isset($this->removed_users[$username]['update']['traitor']))
            {
                $set .= ", `vil_loyal_pts` = `vil_loyal_pts` - ((ABS(`vil_loyal_pts`) * 0.50) + 25)";
            }

            //clan activity
            //", `clan_activity` = `clan_activity` + '".$value."'";


            //exp gain
            {
                if(!isset($this->removed_users[$username]['update']['exp']))
                    $this->removed_users[$username]['update']['exp'] = 1;

                if($this->removed_users[$username]['update']['exp'] <= 0)
                    $this->removed_users[$username]['update']['exp'] = 1;

                $value = intval($this->removed_users[$username]['update']['exp']);
                $set .= ", `experience` = `experience` + '".$value."'";
            }


            //pvp gain
            //pvp stream
            //ai fled
            //ai lost
            //ai won
            //ai draw
            //pvp fled
            //pvp lost
            //pvp won
            //pvp draw
            //bounty_experience
            //feature
            //bounty
            //next_battle
            //rep loss
            //respect loss


            //updating user location and status
            //if user is not dead just set them to awake
            if( $this->removed_users[$username]['health'] > 0 )
            {
                $return_data['hospital'] = false;
                $set .= ", `status` = 'awake'";
            }

            ///////////jail?

            //if the user is not syndicate and they are dead
            //set them to hospitalized and send them to their village
            else if( $this->removed_users[$username]['village'] != 'Syndicate' )
            {
                $return_data['hospital'] = true;
                $set .= ", `users`.`status` = 'hospitalized',
                          `users`.`latitude` = `villages`.`latitude`,
                          `users`.`longitude` = `villages`.`longitude`,
                          `users`.`location` = `villages`.`name`";
            }

            //if the user is syndicate
            else
            {
                $return_data['hospital'] = true;
                $village_locations = array("8.3", "23.4", "4.12", "21.12", "11.17", "26.11");

                //check to see if the user is currently in a village
                //if so make sure that they will not be in a village when hospitalized.
                //set status to hospitalized and location to disoriented
                if( in_array($this->removed_users[$username]['location'], $village_locations ) )
                {
                    $new_latitude = $this->removed_users[$username]['latitude'];
                    $new_longitude = $this->removed_users[$username]['longitude'];

                    while( $new_latitude == $this->removed_users[$username]['latitude'] && $new_longitude == $this->removed_users[$username]['longitude'] )
                    {
                        $new_latitude = $this->removed_users[$username]['latitude'] + random_int(-2,2);
                        $new_longitude = $this->removed_users[$username]['longitude'] + random_int(-2,2);
                    }

                    $set .= ", `users`.`status` = 'hospitalized',
                            	`users`.`latitude` = ".$new_latitude.",
                                `users`.`longitude` = ".$new_longitude.",
                                `users`.`location` = 'Disoriented'";
                }

                //if the syndicate user is not currently in a village
                //just set status to hospitalized and location to disoriented
                else
                {
                    $set .= ", `users`.`status` = 'hospitalized',
                                `users`.`location` = 'Disoriented'";
                }
            }
            
            $query = "UPDATE
                `users`,
                `users_statistics`,
                `users_missions`,
                `users_timer`,
                `bingo_book`,
                `users_loyalty`,
                `users_occupations`,
                `users_jutsu`,
                `villages`, 
                `users_inventory`
            SET
                ".$set."
            WHERE
                `users`.`id` = '".$uid."' AND
                `users`.`id` = `users_statistics`.`uid` AND
                `users`.`id` = `users_missions`.`userid` AND
                `users`.`id` = `users_timer`.`userid` AND
                `users`.`id` = `users_loyalty`.`uid` AND
                `users`.`id` = `bingo_book`.`userid` AND
                `users`.`id` = `users_occupations`.`userid` AND
                `users`.`id` = `users_jutsu`.`uid` AND
                `users`.`village` = `villages`.`name` AND
                `users`.`id` = `users_inventory`.`uid`";

            var_dump($query);

            //sending query to database to updated user status and location/lat/lon
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

            //remove items
            if(isset($this->removed_users[$username]['update']['remove']) && is_array($this->removed_users[$username]['update']['remove']))
            {
                $query = 'DELETE FROM `users_inventory` WHERE `id` IN ('.implode(',',array_keys($this->removed_users[$username]['update']['remove'])).')';
                try { $GLOBALS['database']->execute_query($query); }
                catch (Exception $e)
                {
                    try { $GLOBALS['database']->execute_query($query); }
                    catch (Exception $e)
                    {
                        try { $GLOBALS['database']->execute_query($query); }
                        catch (Exception $e)
                        {
                            //$GLOBALS['DebugTool']->push('','there was an error updating user status.', __METHOD__, __FILE__, __LINE__);
                            throw $e;
                        }
                    }
                }
            }

            $GLOBALS['database']->transaction_commit();

            return $return_data;
        }
        catch (Exception $e)
        {
            $GLOBALS['database']->transaction_rollback();
            //$GLOBALS['DebugTool']->push('','there was an error updating user information. please try again.', __METHOD__, __FILE__, __LINE__);
            throw $e;
            return false;
        }
    }
}