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
 *Class: Events
 *  this class is called on to handle events
 *
 */

require_once(Data::$absSvrPath.'/global_libs/Quests/Hooks.php');
require_once(Data::$absSvrPath.'/global_libs/Quests/QuestsControl.php');



class Events
{
    //list of event only .... events
    public static $event_only = array(          'combat_conclusion',            'combat_type',
                                                'combat_allies',                'combat_opponents',
                                                'ai_death',                     'user_death',
                                                'profession_craft',             'crime',
                                                'errand',                       'tavern_send',
                                                'tavern_receive',               'pm_send',
                                                'pm_receive',                   'quest_status');

    //list of user data only events
    public static $userdata_events = array(     'stats_max_health',             'stats_max_sta',
                                                'stats_max_cha',                'stats_cur_health',
                                                'stats_cur_sta',                'stats_cur_cha',
                                                'stats_strength',               'stats_intelligence',
                                                'stats_willpower',              'stats_speed',
                                                'stats_nin_def',                'stats_nin_off',
                                                'stats_gen_def',                'stats_gen_off',
                                                'stats_tai_def',                'stats_tai_off',
                                                'stats_weap_def',               'stats_weap_off',
                                                'rank_id',                      'level',
                                                'level_id',                     'experience',
                                                'village_loyalty_loss',         'village_loyalty_gain',
                                                'money_gain',                   'money_loss',
                                                'rep_gain',                     'rep_loss',
                                                'pop_gain',                     'pop_loss',
                                                'deposit',                      'withdraw',
                                                'login_streak',                 'pvp_experience',
                                                'pvp_streak',                   'location_name',
                                                'location_region',              'location_x',
                                                'location_y',                   'bloodline',
                                                'status',                       'village',
                                                'clan',                         'anbu',
                                                'rank',                         'specialization',
                                                'home',                         'location_owner',
                                                'location_claimable');

    //list of database only events
    public static $non_userdata_events = array( 'stats_element_mastery_1',      'stats_element_mastery_2',
                                                'profession_exp',               'occupation_level',
                                                'surgeon_sp_exp',               'surgeon_cp_exp',
                                                'bounty_hunter_exp',            'bounty_collected',
                                                'diplomacy_gain',               'diplomacy_loss',
                                                'jutsu_level',                  'jutsu_times_used',
                                                'item_person',                  'item_home',
                                                'item_furniture',               'item_equip',
                                                'item_repair',                  'item_durability_gain',
                                                'item_durability_loss',         'item_used',
                                                'item_quantity_gain',           'item_quantity_loss',
                                                'elements_primary',             'elements_secondary',
                                                'elements_bloodline_primary',   'elements_bloodline_secondary',
                                                'elements_bloodline_special',   'elements_active_primary',
                                                'elements_active_secondary',    'elements_active_special',
                                                'profession_change',
                                                'occupation_change',            'special_occupation_change',
                                                'surgeon_heal',                 'bounty_hunter_tracking',
                                                'page',                         'jutsu_learned');

    //list of time events
    public static $time_events = array(         'year',                         'month',
                                                'day_numeric',                  'hour',
                                                'minute',                       'second',
                                                'unix_time',                    'day' );

    //list of quest events
    public static $quest_events = array( 'quest_status' );

    //list of the int events
    public static $int_events = array(  // ///in userdata\\\  ---------------------------------------------------------------------------------------
                                        
                                        'stats_max_health', // valid new() old()
                                        'stats_max_sta', // valid new() old()
                                        'stats_max_cha', // valid new() old()

                                        'stats_cur_health', // valid new() old()
                                        'stats_cur_sta', // valid new() old()
                                        'stats_cur_cha', // valid new() old()

	                                    'stats_strength', // valid new() old()
                                        'stats_intelligence', // valid new() old()
                                        'stats_willpower', // valid new() old()
                                        'stats_speed', // valid new() old()

	                                    'stats_nin_def', // valid new() old()
                                        'stats_nin_off', // valid new() old()

                                        'stats_gen_def', // valid new() old()
                                        'stats_gen_off', // valid new() old()

	                                    'stats_tai_def', // valid new() old()
                                        'stats_tai_off', // valid new() old()

                                        'stats_weap_def', // valid new() old()
                                        'stats_weap_off', // valid new() old()

                                        'rank_id', // valid new() old()
                                        'level', // valid new() old()
                                        'level_id', // valid new() old()
                                        'experience', // valid new() old()

                                        'village_loyalty_loss',  // valid new() old()
                                        'village_loyalty_gain', // valid new() old()

                                        'money_gain', // valid new() old()
                                        'money_loss', // valid new() old()

                                        'rep_gain', // valid new() old()
                                        'rep_loss', // valid new() old()

                                        'pop_gain', // valid new() old()
                                        'pop_loss', // valid new() old()

                                        'deposit', // valid data(#)
                                        'withdraw', // valid data(#)

                                        'login_streak', // valid old() new()

                                        'pvp_experience', //valid new(#) old(#)
                                        'pvp_streak', // valid new(#) old(#)

                                        // \\\in user data/// ---------------------------------------------------------------------------------------


                                        // ///not in user data\\\ -----------------------------------------------------------------------------------

	                                    'stats_element_mastery_1', // valid new() old()
                                        'stats_element_mastery_2', // valid new() old()

                                        'profession_exp', // valid new() old()
                                        'occupation_level', // valid new() old()
                                        'surgeon_sp_exp', // valid new() old()
                                        'surgeon_cp_exp', // valid new() old()

                                        'bounty_hunter_exp', // valid new() old()
                                        'bounty_collected', // valid data()

                                        'diplomacy_gain', // valid new() old() village()
                                        'diplomacy_loss', // valid new() old() village()

                                        'jutsu_level',  // valid new(lvl) old(lvl) then data(id) which is used by "jutsu_leveled"
                                        'jutsu_times_used', // valid new(#) old(#) then data(id) which is used by "jutsu_used"

                                        'year', // valid data()
                                        'month', // valid data() 
                                        'day_numeric', // valid data()
                                        'hour', // valid data()
                                        'minute', // valid data()
                                        'second', // valid data()
                                        'unix_time' // valid data()

                                        // \\\not in user data/// -----------------------------------------------------------------------------------
                                     );
    
    //list of the var char events
    public static $varchar_events = array(  // ///in userdata\\\  -----------------------------------------------------------------------------------

                                            'location_name', //valid (new and old)
                                            'location_region', //valid (new and old)
                                            'location_owner', //valid (new and old)
                                            'location_claimable', //valid (new and old)
                                            'location_x', //valid (new and old)
                                            'location_y', //valid (new and old)

                                            'bloodline', // valid ( data('None', bloodline) extra('None', 'Taijutsu', 'Highest', ))
                                            'status', // valid( new old )

                                            'village', //valid new() old()
                                            'clan', //valid new('_none') old()
                                            'anbu', // valid new('_none', 'disabled') old()
                                            'rank', // valid new() old()

                                            'specialization', //valid new() old('None')

                                            // \\\in userdata///  -----------------------------------------------------------------------------------



                                            // ///not in user data\\\ -------------------------------------------------------------------------------

                                            'item_person', //valid (data(!) and count(1, -1, all))
                                            'item_home', //valid (data(!) and count(1, -1, all))
                                            'item_furniture', //valid (data(!) and count(1, -1, all))

                                            'item_equip', // valid (data(!) extra( armor(['armor_types'])  weapon(['weapon_classifications']) ) )

                                            'item_repair', // for the smith, valid (data(iid), new(new durability), old(old durability))
                                            'item_durability_gain', //for the owner, valid (data(iid), new(new durability), old(old durability))
                                            'item_durability_loss', //for owner during combat, valid (data(iid), new(new durability), old(old durability))

                                            'item_used', /// valid (data(iid) (count(#) for weapons) OR (new/old(#) for other) )

                                            'item_quantity_gain', // valid( data(iid) count(#) from(shop['shopName'], 'buyAdminPack', 'harvest', 'processed', 'crafting', '') )
                                            'item_quantity_loss', // valid( data(iid) count(-#) new(#) old(#) )

                                            'combat_conclusion', // valid data('won','loss','fled')
                                            'combat_type', // valid data('arena','clan','kage'...)
                                            'combat_allies', // data(username) extra(team)
                                            'combat_opponents', // data(username) extra(team)
                                            'ai_death', //data(id) 
                                            'user_death', //data(id) 

                                            'elements_primary', // valid new() old()
                                            'elements_secondary', // valid new() old()

                                            'elements_bloodline_primary', //valid new(removed) old()
                                            'elements_bloodline_secondary', //valid new(removed) old()
                                            'elements_bloodline_special', //valid new(removed) old()

                                            'elements_active_primary',
                                            'elements_active_secondary',
                                            'elements_active_special',

                                            'day', //valid data(date('l'))
                                            'profession_change', //valid new(0) old(0)
                                            'occupation_change', //valid new() old()
                                            'special_occupation_change', //valid new() old()

                                            'profession_craft', //valid data(id) count()
                                            'surgeon_heal', //valid data(username) extra(amount healed)
                                            'bounty_hunter_tracking', //valid new(current target) old(old target) // NULL for no target (need to look into how this plays out)

                                            'page', //valid new(page id) old(page id)
                                            'crime', //  data(success,failure) count() ryo() diplomacy(points() village())
                                            'errand', // data(success) count() ryo() diplomacy(points() village())
                                            'home', // data(sold,lost,id)
                                            'jutsu_learned', // data(id)

                                            'tavern_send', //data(message) username(username)
                                            'tavern_receive', //data(message) username(username)
                                            'pm_send', //data(message) username(username)
                                            'pm_receive', //data(message) username(username)

                                            'jutsu_leveled', //comes from jutsu_level['data']
                                            'jutsu_used', //comes from jutsu_times_used['data']
                                            'message_owner', //comes from tavern/pm_send/receive['username']
                                            'quest_id', // comes from data
                                            'quest_status' // comes from new

                                            // \\\not in user data/// -------------------------------------------------------------------------------
                                        );

    //this is a maping of how to apply some events to the hooks table
    public static $mod_list_for_events_to_hooks_table = array(
                                                        'jutsu_level'=>array(
                                                            'jutsu_level'=>'new',
                                                            'jutsu_leveled'=>'context',
                                                            'alt_key'=>'jutsu_leveled'),

                                                        'jutsu_times_used'=>array(
                                                            'jutsu_times_used'=>'new',
                                                            'jutsu_used'=>'context',
                                                            'alt_key'=>'jutsu_used'),

                                                       'tavern_send'=>array(
                                                            'tavern_send'=>'data',
                                                            'message_owner'=>'context',
                                                            'alt_key'=>'message_owner'),

                                                       'tavern_receive'=>array(
                                                            'tavern_receive'=>'data',
                                                            'message_owner'=>'context',
                                                            'alt_key'=>'message_owner'),

                                                       'pm_send'=>array(
                                                            'pm_send'=>'data',
                                                            'message_owner'=>'context',
                                                            'alt_key'=>'message_owner'),

                                                       'pm_receive'=>array(
                                                            'pm_receive'=>'data',
                                                            'message_owner'=>'context',
                                                            'alt_key'=>'message_owner'), 

                                                        'quest_status'=>array(
                                                            'quest_status'=>'new',
                                                            'quest_id'=>'context',
                                                            'alt_key'=>'quest_id')
                                                       );

    //main variables for the class
    public  $events = array();
    private $buffer = array();
    private $db_buffer = array();
    private $uid = '';

    function __construct($uid = NULL)
    {
        if($uid == false && isset($_SESSION['uid'])) //if no user id was passed in use this sessions uid
            $uid = $_SESSION['uid'];
        else if(!is_null($uid))
            $uid = $uid;
        else
        {
            error_log('can not start quest data, no uid');
            return false;
        }

        if(is_numeric($uid)) // the uid we have so far is a number let it be
            $this->uid = $uid;
        else // if the uid we have so far is not a number assume it is a username and get the uid from the database
        {
            $result = $GLOBALS['database']->fetch_data('SELECT `id` FROM `users` WHERE `username` = \''.$uid.'\'');
            if(isset($result[0]['id']))
                $uid = $result[0]['id'];
            else
            {
                error_log('events init issue, bad target: '.print_r($uid,true));
                $uid = 0;
            }

            $this->uid = $uid;
        }

        if( !isset($_SESSION['uid']) || $uid != $_SESSION['uid']) // if the uid we have now is not the same as the current session
        {
            //get the event buffer from the user
            $result = $GLOBALS['database']->fetch_data('SELECT `event_buffer` FROM `users_event_buffers` WHERE `uid` = '.$uid);

            if(is_array($result))
                $this->db_buffer = $result[0]['event_buffer'];
        }
        else //otherwise get the event buffer from globals
        {
            $this->db_buffer = $GLOBALS['userdata'][0]['event_buffer'];
        }
    }

    //called to finish proccessing of the quests system at the end of the page load.
    public function closeEvents()
    {   
        //if session and uid match and status != combat
        if( isset($_SESSION['uid']) && $this->uid == $_SESSION['uid'] && $GLOBALS['userdata'][0]['status'] != 'combat')
        {
            //trigger time events
            $this->acceptEvent('unix_time', array('data'=>time()));
            $this->acceptEvent('year', array('data'=>date('Y')));
            $this->acceptEvent('month', array('data'=>date('n')));
            $this->acceptEvent('day_numeric', array('data'=>date('j')));
            $this->acceptEvent('day', array('data'=>date('l')));
            $this->acceptEvent('hour', array('data'=>date('H')));
            $this->acceptEvent('minute', array('data'=>date('i')));
            $this->acceptEvent('second', array('data'=>date('s')));

            //purge the event buffer
            $this->purgeBuffer();
            
            //if QuestsControl has not bet started do so....
            if(!isset($GLOBALS['QuestsControl']))
            {
                $GLOBALS['QuestsControl'] = new QuestsControl();//temp
            }

            //have QuestsControl digest the events and update any quests if need be.
            $GLOBALS['QuestsControl']->recordEvents($this->events);
            
            //checking quests
            $GLOBALS['QuestsControl']->QuestsData->updateQuests();
            $GLOBALS['QuestsControl']->checkForFailureAndCompletion();

            //checing hooks
            $hooks = new Hooks($this->uid, $this->events);
            $hooks->checkHooks();
        }
        else if(isset($_SESSION['uid']))// if session and uid do not match or the users status is combat
            $this->recordBuffer(); // then record all events to the users event buffer.

    }


    //taking in events from outside the quests system
    public function acceptEvent($event_key, $event_data)
    {
        //if new vs old is same throw away
        if(    (isset($event_data['new']) && isset($event_data['old']) && ($event_data['new'] !== $event_data['old'] || in_array($event_key, array('location_x','location_y','location_name','location_region','location_owner','location_claimable'))))
            || ( !isset($event_data['new']) && !isset($event_data['old']) && (isset($event_data['data']) || isset($event_data['context'])) ))
        {
            //if event can be processed this turn and by this user place in events variable
                //if event can not be processed this turn or by this user place events in buffer
            if( isset($_SESSION['uid']) && $this->uid == $_SESSION['uid'] && $GLOBALS['userdata'][0]['status'] !== 'combat')
                $this->recordEvent($event_key, $event_data);
            else if(isset($_SESSION['uid']))
                $this->placeEventInBuffer($event_key, $event_data);
        }
    }

    //places event data in the events variable with some processing.
    private function recordEvent($event_key, $event_data)
    {
        //if new/old check to see if there is another new/old of the same key and replace the new with this new and leave the old old
        if(isset($event_data['new']) && isset($event_data['old']) && isset($this->events[$event_key]) && $this->events[$event_key])
        {
            $replace = false;
            foreach($this->events[$event_key] as $key => $existing_event)
            {
                //if( (!isset($existing_event['context']) || !isset($event_data['context'])) || $existing_event['context'] == $event_data['context'])
                if( (isset($existing_event['context']) && isset($event_data['context']) && $existing_event['context'] == $event_data['context']) || !isset($existing_event['context']) || !isset($event_data['context']) )
                {
                    $matching_key = $key;
                    $replace = true;
                }
            }

            if($replace)
            {
                if(isset($this->events[$event_key][$key]['old']))
                {
                    $event_data['old'] = $this->events[$event_key][$key]['old'];
                }

                unset($this->events[$event_key][$key]);
            }
        }
        
        //check for duplicate events and add them if possible if not ignore the second copy
        if(!isset($event_data['count']) && isset($this->events[$event_key]) && in_array($event_data, $this->events[$event_key]) && !in_array($event_key, Events::$time_events))
        {
            //combine duplicate?//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //error_log('duplicate events what do?: '.$event_key);
            //var_dump('duplicate event error. get koala.');
        }
        //check for counts and combine them?//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        else if(isset($event_data['count']) && isset($this->events[$event_key]))
        {
            foreach($this->events[$event_key] as $checking_events_key => $checking_events_data)
            {
                if($checking_events_data['data'] == $event_data['data'])
                {
                    $event_data['count'] += $this->events[$event_key][$checking_events_key]['count'];
                    unset($this->events[$event_key][$checking_events_key]);
                }
            }

        }

        $this->events[$event_key][] = $event_data;
    }

    //logging event in the database when it can not be handled this turn.
    //called by accept event if event cant be proccessed this turn
    public function placeEventInBuffer($event_key, $event_data)
    {
        //just add the event to $this->buffer
        $this->buffer[$event_key][] = $event_data;
    }

    //storing buffer in database
    public function recordBuffer()
    {
        //if buffer is not empty
        if(is_array($this->buffer) && count($this->buffer) != 0)
        {
            //pull stored buffer
            if($this->db_buffer != '' && !is_array($this->db_buffer))
            {
                //deserialize events
                $this->db_buffer = unserialize(gzinflate(str_replace("backslash","\\",$this->db_buffer)));

                //pull buffer and add to $this->buffer
                foreach($this->db_buffer as $event_key => $events)
                {
                    foreach($events as $event_data)
                    {
                        $this->placeEventInBuffer($event_key, $event_data);
                    }
                }

                //delete buffer
                $GLOBALS['database']->execute_query('DELETE FROM `users_event_buffers` WHERE `uid` = '.$this->uid);
                $this->db_buffer = '';
            }

            //if we are recordingBuffer then we should purge events to the buffer aswell
            if(is_array($this->events) && count($this->events) != 0)
            {
                foreach($this->events as $event_key => $events)
                {
                    foreach($events as $event_data)
                    {
                        $this->placeEventInBuffer($event_key, $event_data);
                    }
                }
            }

            $this->events = array(0);

            //stringify the data and combine the data then store it back in the database
            $GLOBALS['database']->execute_query('REPLACE INTO `users_event_buffers` VALUES ('.$this->uid.', \''.str_replace("'","\'",str_replace("\\","backslash",gzdeflate(serialize($this->buffer),1))).'\')',false,true,true);

            //unset the buffer
            $this->buffer = array();
        }
    }





    //proccessing and purging buffer from database
    //proccess items in buffer and add them to events
    private function purgeBuffer()
    {
        if($this->db_buffer != '' && !is_array($this->db_buffer))
        {
            //deserialize events
            $this->db_buffer = unserialize(gzinflate(str_replace("backslash","\\",$this->db_buffer)));

            //pull buffer and add to $this->buffer
            foreach($this->db_buffer as $event_key => $events)
            {
                foreach($events as $event_data)
                {
                    $this->placeEventInBuffer($event_key, $event_data);
                }
            }

            //delete buffer
            $GLOBALS['database']->execute_query('DELETE FROM `users_event_buffers` WHERE `uid` = '.$this->uid);
            $this->db_buffer = '';
        }

        if(is_array($this->buffer) && count($this->buffer) != 0)
        {
            //pulling events into temp container
            $temp_container = $this->events;
            $this->events = array();

            foreach($this->buffer as $event_key => $events)
            {
                foreach($events as $event_data)
                {
                    $this->recordEvent($event_key, $event_data);
                }
            }

            //re adding events from temp container to $this->events
            //this is needed to make sure that events are in the correct order.
            if(is_array($temp_container) && count($temp_container) != 0)
            {
                foreach($temp_container as $event_key => $events)
                {
                    foreach($events as $event_data)
                    {
                        $this->recordEvent($event_key, $event_data);
                    }
                }
            }

            $this->buffer = array();
        }
    }

//   ......................................................................................................................................................................................................
//  /////////////////////////////////////////////////////////////////////////////////////////////////ending\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||EVENT SYSTEM|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
//  \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ending/////////////////////////////////////////////////////////////////////////////////////////////////
//   ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

}