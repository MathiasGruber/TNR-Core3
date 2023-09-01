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
 *  this class contains methods that handle all of the tags that can be used.
 *  classes that use these tags are to extend this class.
 *  methods in the class do not return anything they create and make changes to
 *  the variables listed at the top of this class.
 */

//external syntax for a tag can be found here.
//internal syntax for a tag can be found in class globals.
/*Syntax: tag:origin:(field>value;field>(values,values,values);field>value)
 *  tag:        name of the effect
 *  origin:     where the tag comes from  BLOODLINE, ARMOR, LOCATION, JUTSU, WEAPON, ITEM
 *
 * fields
 *  target:             SELF,OPPONENT,ALL,ALLIES,ENEMIES
 *  value:              format(base,baseType,increment,incrementType) -> baseType = F(flat), P(percentage), incrementType = F(flat), PP(principal percentage), BP(base percentage)
 *  duration:           format (min,max) or (duration)
 *  targetType:         T,N,G,B(any mix) or S
 *  targetElement:      Special data needed by the tag
 *  delay:              prevents the tag from taking effect 0 does nothing 1 next turn 2 turn over next....
 *  persistAfterDeath:  true vs false. decides if a tag should stay in effect after a user leaves the battle.
 */

class Tags
{
    //---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---
    //***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** *****
    //constants

    private $debugging = false;

    //tag fields
        const ALL = 'X';
        const YES = true;
        const NO = false;

        const TAG = 'tag';
        const TAGS = 'tags';
        const TAGSINEFFECT = 'tags_in_effect';
        const USERS = 'users';
        const TEAMS = 'teams';
        const ELEMENT = 'element';
        const ELEMENTS = 'elements';
        const ELEMENTMASTERIES = 'element_masteries';
        const EQUIPMENT = 'equipment';

        const OWNER = 'owner';

        const ORIGIN = 'origin';
            const BLOODLINE = 'B';
            const ARMOR = 'A';
                const CHEST = 'C';
                const HELMET = 'H';
                const HEAD = 'H';
                const GLOVES = 'G';
                const HANDS = 'G';
                const BELT = 'B';
                const WAIST = 'B';
                const SHOES = 'F';
                const FEET = 'F';
                const PANTS = 'L';
                const LEGS = 'L';
                public $ALLEQUIPMENT = array(self::CHEST, self::HEAD, self::HANDS, self::WAIST, self::FEET, self::LEGS, self::ARMOR, self::WEAPON);

            const LOCATION = 'L';
            const JUTSU = 'J';
            const DEFAULT_ATTACK = 'D';
            const WEAPON = 'W';
            const ITEM = 'I';
            public $ALLORIGIN = array(self::BLOODLINE, self::ARMOR, self::LOCATION, self::JUTSU, self::WEAPON, self::ITEM, self::DEFAULT_ATTACK);

        const PERSISTAFTERDEATH = 'persistAfterDeath';
            //yes no true false 1 0

        const TARGET = 'target';
            const OPPONENT = 'opponent';
            const ALLY = 'ally';
            const OTHER = 'other';
            const TARGETSELF = 'self';
            const ALLOTHER = 'allOther';
            const TEAM = 'team';
            const RIVALTEAMS = 'rivalTeams';

        const VALUE = 'value';
        const INCREMENT = 'increment';
            const FLATBOOST = 'FB';
            const PRINCIPALPERCENTAGE = 'PP';
            const BOOSTPERCENTAGE = 'BP';
            public $ALLVALUETYPE = array(self::FLATBOOST, self::PRINCIPALPERCENTAGE, self::BOOSTPERCENTAGE);


        const DURATION = 'duration';
            //numeric

        const DELAY = 'delay';
            //numeric

        const AGE = 'age';
            //numeric

        const TARGETTYPE = 'targetType';
            const TAIJUTSU = 'T';
            const NINJUTSU = 'N';
            const GENJUTSU = 'G';
            const BUKIJUTSU = 'B';
            const SPECIALTY = 'S';
            public $ALLTYPE = array(self::TAIJUTSU, self::NINJUTSU, self::GENJUTSU, self::BUKIJUTSU, 'highest');


        const TARGETELEMENT = 'targetElement';
            const WATER = 'W';
            const EARTH = 'E';
            const LIGHTNING = 'L';
            const FIRE  = 'F';
            const AIR = 'A';
            const NONE = 'N';

            const ICE = 'IC';
            const LIGHT = 'LG';
            const DUST = 'DS';
            const STORM = 'SR';
            const LAVA = 'LV';

            const SCORCHING = 'SC';
            const TEMPEST = 'TM';
            const MAGNETISM = 'MG';
            const WOOD = 'WD';
            const STEAM = 'ST';

            public $ALLELEMENT = array(self::WATER, self::EARTH, self::LIGHTNING, self::FIRE, self::AIR, self::NONE,
                                      self::ICE, self::LIGHT, self::DUST, self::STORM, self::LAVA,
                                      self::SCORCHING, self::TEMPEST, self::MAGNETISM, self::WOOD, self::STEAM);

            public $ELEMENTEXPAND = array( 'W'  => 'water',
                                           'E'  => 'earth',
                                           'L'  => 'lightning',
                                           'F'  => 'fire',
                                           'A'  => 'wind',
                                           'N'  => '',
                                           'IC' => 'ice',
                                           'LG' => 'light',
                                           'DS' => 'dust',
                                           'SR' => 'storm',
                                           'LV' => 'lava',
                                           'SC' => 'scorching',
                                           'TM' => 'tempest',
                                           'MG' => 'magnetism',
                                           'WD' => 'wood',
                                           'ST' => 'steam' );

            public $ELEMENTWEAKNESS = array
                (
                self::WATER=>self::EARTH, 'water'=>self::EARTH,
                self::FIRE=>self::WATER, 'fire'=>self::WATER,
                self::AIR=>self::FIRE, 'wind'=>self::FIRE,
                self::LIGHTNING=>self::AIR, 'lightning'=>self::AIR,
                self::EARTH=>self::LIGHTNING, 'earth'=>self::LIGHTNING,

                self::ICE=>self::LAVA, 'ice'=>self::LAVA,
                self::LIGHT=>self::ICE, 'light'=>self::ICE,
                self::DUST=>self::LIGHT, 'dust'=>self::LIGHT,
                self::STORM=>self::DUST, 'storm'=>self::DUST,
                self::LAVA=>self::STORM, 'lava'=>self::STORM,

                self::STEAM=>self::WOOD, 'steam'=>self::WOOD,
                self::SCORCHING=>self::STEAM, 'scorching'=>self::STEAM,
                self::TEMPEST=>self::SCORCHING, 'tempest'=>self::SCORCHING,
                self::MAGNETISM=>self::TEMPEST, 'magnetism'=>self::TEMPEST,
                self::WOOD=>self::MAGNETISM, 'wood'=>self::MAGNETISM,

                self::NONE=>'', 'none'=>''
                );

            public $ELEMENTHERITAGE = array
                (
                self::ICE=>array(self::WATER, self::AIR, 'water', 'wind'), 'ice'=>array(self::WATER, self::AIR, 'water', 'wind'),
                self::LIGHT=>array(self::FIRE, self::LIGHTNING, 'fire', 'lightning'), 'light'=>array(self::FIRE, self::LIGHTNING, 'fire', 'lightning'),
                self::DUST=>array(self::AIR, self::EARTH, 'wind', 'earth'), 'dust'=>array(self::AIR, self::EARTH, 'wind', 'earth'),
                self::STORM=>array(self::LIGHTNING, self::WATER, 'lightning', 'water'), 'storm'=>array(self::LIGHTNING, self::WATER, 'lightning', 'water'),
                self::LAVA=>array(self::EARTH, self::FIRE, 'earth', 'fire'), 'lava'=>array(self::EARTH, self::FIRE, 'earth', 'fire'),

                self::STEAM=>array(self::WATER, self::FIRE, 'water', 'fire'), 'steam'=>array(self::WATER, self::FIRE, 'water', 'fire'),
                self::SCORCHING=>array(self::FIRE, self::AIR, 'fire', 'wind'), 'scorching'=>array(self::FIRE, self::AIR, 'fire', 'wind'),
                self::TEMPEST=>array(self::AIR, self::LIGHTNING, 'wind', 'lightning'), 'tempest'=>array(self::AIR, self::LIGHTNING, 'wind', 'lightning'),
                self::MAGNETISM=>array(self::LIGHTNING, self::EARTH, 'lightning', 'earth'), 'magnetism'=>array(self::LIGHTNING, self::EARTH, 'lightning', 'earth'),
                self::WOOD=>array(self::EARTH, self::WATER, 'earth', 'water'), 'wood'=>array(self::EARTH, self::WATER, 'earth', 'water')
                );

        const ARMORBASE = 'armor';
            public $ARMORCAP = array(1,100,2500,10000,17500,25000);

        const OFFENSE = 'offense';
        const DEFENSE = 'defense';

        const STRENGTH = 'strength';
        const INTELLIGENCE = 'intelligence';
        const WILLPOWER = 'willpower';
        const SPEED = 'speed';
        const SPECIALIZATION = 'specialization';

        public $ALLGENERALS = array(self::STRENGTH, self::INTELLIGENCE, self::WILLPOWER, self::SPEED, 'Strength', 'Intelligence', 'Willpower', 'Speed');

        const CHAKRA = 'chakra';
        const CHAKRAMAX = 'chakraMax';
        const CHAKRACOST = 'chakraCost';
        const STAMINA = 'stamina';
        const STAMINAMAX = 'staminaMax';
        const STAMINACOST = 'staminaCost';
        const HEALTH = 'health';
        const HEALTHMAX = 'healthMax';
        const HEALTHCOST = 'healthCost';
        const BASE = 'Base';

        const MASTERY = 'mastery';
            public $MASTERYCAP = array(1,1,1,1375,2250,3125);

        const STABILITY = 'stability';
            public $STABILITYCAP = array(1,1,1,1600,2000,2500);

        const ACCURACY = 'accuracy';
            public $ACCURACYIDENTIFIER = array(1,1,1,6.19,5.09,3.97);
            public $ACCURACYCAP = array(1,1,1,1375,2250,3125);

        const EXPERTISE = 'expertise';
            //public $EXPERTISEIDENTIFIER = array(1,1,1,2162,1707,1424);
            public $EXPERTISECAP = array(1,1,1,10000,17500,25000);

        const CHAKRAPOWER = 'chakraPower';
            //public $CHAKRAPOWERIDENTIFIER = array(1,1,1,5.083,3.27,1.933);
            //public $CHAKRAPOWERIDENTIFIER = array(1,1,1,3.32,1.286,0.5);
            public $CHAKRAPOWERIDENTIFIER = array(1,1,1, 0.314 , 0.0728 , 0.0144 );
            public $CHAKRAPOWERCAP = array(1,1,1,19250,30500,54500);
            //public $CHAKRAPOWERCAP = array(1,1,1,25000,50000,75000);

        const CRITICALPOWER = 'criticalPower';
        const CRITICALSTRIKE = 'criticalStrike';
            //public $CRITICALSTRIKEIDENTIFIER = array(1,1,1,3.32,1.286,0.5);
            public $CRITICALSTRIKEIDENTIFIER = array(1,1,1, 0.314 , 0.0728 , 0.0144 );
            public $CRITICALSTRIKECAP = array(1,1,1,19250,30500,54500);


        const FLUX = 'flux';
            public $FLUXIDENTIFIERMIN = array(1,1,1,.8,1,1.25);
            public $FLUXIDENTIFIERMAX = array(1,1,1,1.6,3.2,5);

        const RANK = 'rank';

        const IMMUNITY = 'immunity';
            const DAMAGE = 'damage';
            const DAMAGEOVERTIME = 'damageOverTime';
            const RECOIL = 'recoil';
            const REFLECT = 'reflect';
            const ABSORB = 'absorb';
            const LEACH = 'leach';
            public $ALLIMMUNITY = array(self::DAMAGE, self::DAMAGEOVERTIME, self::RECOIL, self::REFLECT, self::ABSORB, self::LEACH);

        const DAMAGEIN = 'damageIn';
        const DAMAGEOUT = 'damageOut';

        const HEAL = 'heal';
        const HEALIN = 'healIn';
        const HEALOUT = 'healOut';

        const NOROB = 'noRob';
        const NOFLEE = 'noFlee';
        const NOSTUN = 'noStun';
        const NOSTAGGER = 'noStagger';
        const NODISABLE = 'noDisable';


        const NOONEHITKILL = 'noOneHitKill';

        //tags that cant be copied or mirrored
        private $noCopyTags = array('copyOrigin', 'mirrorOrigin', 'copyPreviousJutsu');


        private $ALLOWEDTAGS = array(
        //stat tags
            'effectArmor','effectMastery','effectStability','effectAccuracy','effectExpertise','effectChakraPower','effectCriticalStrike','effectOffense','effectDefense','effectGeneralStat','copyOrigin','mirrorOrigin','copyStats',
        //pools tags
            'effectChakra','effectChakraCost','effectStamina','effectStaminaCost','effectHealth','effectHealthCost',
        //damage related tags
            'damage','damageOverTime','effectDamageIn','effectDamageOut','immunity','reflectDamage','absorbDamage','oneHitKill',
        //healing tags
            'heal','effectHealOut','effectHealIn','healOverTime',
        //action tags
            'noRob','yesRob','rob','noFlee','yesFlee', 'effectFleeChance','noStun','yesStun','noStagger','yesStagger','noDisable','yesDisable','disarm','flee','stun','stagger','disable','summon',
        //utility tags
            'clear', 'noClear', 'yesClear', 'delay', 'noDelay', 'yesDelay', 'noOneHitKill', 'yesOneHitKill'
            );
    //_____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____
    //---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---



    //---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---
    //***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** *****
    //class globals

    //holds all users and their information.
    //element tags holds all tags for a user
    //element tags_in_effect holds all tags that are not delayed or paused.
    public $users = array();
    public $teams = array();
    public $location_tags = array();
    public $battle_log = array();
    public $rng = 0;
    public $extra = false;
    public $census = array();
    public $battle_history_id = 0;
    public $balance = array();
    public $balanceDSR = array();

    //cache data
    private $cache_id;
    private $cache_time;

    //all variables manipulated by this class.

    //_____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____
    //---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---



    //---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---
    //***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** *****
    //control methods

    //constructor
    //takes an id to find its cache data at and a variabe to define how long to keep that chace open for
    // also takes a bool flag to set it to debugging mode.
    // when in debugging mode caching is removed.
    //loads in all the data from the cache
    function __construct($id, $time, $debugging = false, $starting = false)
    {
        //setting debugging flag to this
        $this->debugging = $debugging;
        $this->convert_to_database = false;

        //battle flag         $this->force_database_fallback
        //globals flag        $GLOBALS['userdata'][0]['database_fallback']
        //data flag           $this->users[$user_key]['database_fallback']
        //convert flag        $this->convert_to_database



        //checking for force database fallback from battle type
        if( (isset($this->force_database_fallback) && $this->force_database_fallback === true && $GLOBALS['userdata'][0]['database_fallback'] != 1) || !isset($GLOBALS['cache']))
        {
            $GLOBALS['userdata'][0]['database_fallback'] = 1;
            $this->convert_to_database = true;
        }

        //if fallback flag is set
        if($GLOBALS['userdata'][0]['database_fallback'] != 1 && $starting === false)
        {
            // getting data from the cache
            for($i = 0; $i < 4; $i++)
            {
                 try{ $data = $GLOBALS['cache']->get(Data::$target_site.$id.self::TAGS); } //if it fails try again...
                 catch (Exception $e)
                 {
                     try { $data = $GLOBALS['cache']->get(Data::$target_site.$id.self::TAGS); }//if it fails try again...
                     catch (Exception $e)
                     {
                         try { $data = $GLOBALS['cache']->get(Data::$target_site.$id.self::TAGS); }//if it fails again throw exception.
                         catch (Exception $e)
                         {
                             //throw new Exception('there was an issue with reading the cache.');
                             $data = false;
                         }
                     }
                 }


                if($data !== false)
                    $i = 4;
                else
                    usleep(250000 * 1 + $i);
            }

            //if there was a firm failure to get data from the cache
            if($data = false)
            {
                $GLOBALS['userdata'][0]['database_fallback'] = 1;
                $this->convert_to_database = true;
            }
        }
        else
            $data = false;

        if($data === false && $starting === false)
        {
            //getting data from the database
            for($i = 0; $i < 4; $i++)
            {
                $query = "SELECT * FROM `battle_fallback` WHERE `id` = ".$id;
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

                if($data !== "0 rows" && isset($data[0]['data']))
                {
                    $data = unserialize(gzinflate(str_replace("backslash","\\",$data[0]['data'])));

                }
                else
                    $data = false;


                if($data !== false)
                    $i = 4;
                else
                    usleep(250000 * 1 + $i);
            }
        }

        //setting up data and setting it to this
        {
            //if debugging is on get rough cache size
            if($this->debugging)
                $this->cache_size = strlen(serialize($data));
                
            //if data is an array
            if(is_array($data) && !isset($data['purged']))
            {
                //for each piece
                foreach($data as $key => $value)
                    //set that piece to this
                    $this->{$key} = $value;
            }

            //if data is not an array
            else
            {
                //set users and turn counter to empty.
                $this->users = array();
                $this->teams = array();
                $this->jutsus = array();
                $this->location_tags = array();
                $this->battle_log = array();
                $this->removed_users = array();
                $this->balance = array();
                $this->balanceDSR = array();
                $this->rng = 0;
                $this->turn_counter = 0;
                $this->turn_timer = time() + 45;
                $this->user_index = array();
                $this->extra = false;
                $this->census = array();
                $this->battle_history_id = 0;

                ob_start();
                var_dump($data);
                $result = ob_get_clean();
                //error_log('this-> '.$result." && ".$GLOBALS['cache']->getResultCode());
            }

            $this->data = array();

            //set cache_id and cache_time to this just in case its needed.
            $this->cache_id = $id;
            $this->cache_time = $time;

            //update user activity
            if(isset($this->users[$GLOBALS['userdata'][0]['username']]))
            {
                if(isset($this->users[$GLOBALS['userdata'][0]['username']]['visit_time']))
                    $this->users[$GLOBALS['userdata'][0]['username']]['previous_visit_time'] = $this->users[$GLOBALS['userdata'][0]['username']]['visit_time'];
                else
                    $this->users[$GLOBALS['userdata'][0]['username']]['previous_visit_time'] = time();

                $this->users[$GLOBALS['userdata'][0]['username']]['visit_time'] = time();
            }
            else if(isset($this->removed_users[$GLOBALS['userdata'][0]['username']]))
            {
                if(isset($this->removed_users[$GLOBALS['userdata'][0]['username']]['visit_time']))
                    $this->removed_users[$GLOBALS['userdata'][0]['username']]['previous_visit_time'] = $this->removed_users[$GLOBALS['userdata'][0]['username']]['visit_time'];
                else
                    $this->removed_users[$GLOBALS['userdata'][0]['username']]['previous_visit_time'] = time();

                $this->removed_users[$GLOBALS['userdata'][0]['username']]['visit_time'] = time();
            }
        }

        //check to see if flag in data <= flag in database
        if( (isset( $this->users[$GLOBALS['userdata'][0]['username']]['database_fallback']) && $GLOBALS['userdata'][0]['database_fallback'] != $this->users[$GLOBALS['userdata'][0]['username']]['database_fallback']) || (isset( $this->removed_users[$GLOBALS['userdata'][0]['username']]['database_fallback']) && $GLOBALS['userdata'][0]['database_fallback'] != $this->removed_users[$GLOBALS['userdata'][0]['username']]['database_fallback']) )
        {
            $this->convert_to_database = true; //if they do not match flag this combat for conversion
        }

        //make sure data flag matches database flag
        if(isset($this->users[$GLOBALS['userdata'][0]['username']]['database_fallback']))
            $this->users[$GLOBALS['userdata'][0]['username']]['database_fallback'] = $GLOBALS['userdata'][0]['database_fallback'];
        else if(isset($this->removed_users[$GLOBALS['userdata'][0]['username']]['database_fallback']))
            $this->removed_users[$GLOBALS['userdata'][0]['username']]['database_fallback'] = $GLOBALS['userdata'][0]['database_fallback'];

        //if not already converting check to see if we need to convert
        if(! $this->convert_to_database)
        {
            $users = array_merge($this->users,$this->removed_users);

            foreach($users as $users_key_1 => $users_data_1)
            {
                if($users_data_1['database_fallback'] == 1)
                {
                    foreach($users as $users_key_2 => $users_data_2)
                    {
                        if($users_data_2['database_fallback'] != 1)
                        {
                            $this->convert_to_database = 1;
                            break;
                        }
                    }
                    break;
                }
            }
        }

        //if the convert flag was set handle the conversion
        if($this->convert_to_database)
        {
            $uids = array();

            $users = array_merge($this->users,$this->removed_users);


            foreach($users as $user_key_1 => $user_data_1)
            {
                if(isset($this->users[$user_key_1]['database_fallback']))
                    $this->users[$user_key_1]['database_fallback'] = 1;
                else if(isset($this->removed_users[$user_key_1]['database_fallback']))
                    $this->removed_users[$user_key_1]['database_fallback'] = 1;

                if(isset($user_data_1['uid']) && $user_data_1['uid'])
                    $uids[] = $user_data_1['uid'];
            }

            try{ $GLOBALS['database']->execute_query("UPDATE `users` SET `database_fallback` = 1 WHERE `status` like '%combat' AND `id` IN (".implode(',',$uids).")"); }
            catch (Exception $e)
            {
                try{ $GLOBALS['database']->execute_query("UPDATE `users` SET `database_fallback` = 1 WHERE `status` like '%combat' AND `id` IN (".implode(',',$uids).")"); }
                catch (Exception $e)
                {
                    try{ $GLOBALS['database']->execute_query("UPDATE `users` SET `database_fallback` = 1 WHERE `status` like '%combat' AND `id` IN (".implode(',',$uids).")"); }
                    catch (Exception $e)
                    {
                        throw $e;
                    }
                }
            }
        }
    }

    //updateCache
    //this method simply updates the cache with the current data held by this class.
    //only to be called at end. purges in effect data
    public function updateCache()
    {

        //dumping tagsineffect. no need for this data.
        foreach($this->users as $key => $user)
            unset($this->users[$key][self::TAGSINEFFECT]);

        if(!isset($this->turn_order))
            $this->turn_order = array();

        //error_log('update cache-> users: '.count($this->users).' && removed_users: '.count($this->removed_users));

        $cache = array(self::USERS=>$this->users, 'user_index'=>$this->user_index, self::TEAMS=>$this->teams, 
                        'jutsus'=>$this->jutsus, 'location_tags'=>$this->location_tags, 'turn_counter'=>$this->turn_counter,
                        'turn_timer'=>$this->turn_timer, 'battle_log'=>$this->battle_log, 'rng'=>$this->rng, 
                        'removed_users'=>$this->removed_users, 'extra'=>$this->extra, 'census'=>$this->census,
                        'battle_history_id'=>$this->battle_history_id, 'balance'=>$this->balance, 'balanceDSR'=>$this->balanceDSR,
                        'turn_order'=>$this->turn_order);

        //setting database
        //error_log('SET: '.gzdeflate(serialize($cache),1));
        $package = gzdeflate(serialize($cache),1);
        try
        {
            $text_result = unserialize(gzinflate($package));
            if(!is_array($text_result))
                throw new Exception('packing failure.');

            if(!$GLOBALS['database']->execute_query("REPLACE INTO `battle_fallback` (`id`, `time`, `data`) VALUES(".$this->cache_id.", ".time()." ,'".str_replace("'","\'",         str_replace("\\","backslash",$package)         )."')",false,true,true))
                throw new Exception('database update failure: '."REPLACE INTO `battle_fallback` (`id`, `time`, `data`) VALUES(".$this->cache_id.", ".time()." ,'".str_replace("'","\'",         str_replace("\\","backslash",$package)         )."')");
        }
        catch (exception $e)
        {
            
            error_log('1:'.serialize($cache));
            error_log('2:'.unserialize(serialize($cache)));

            error_log('3'.gzdeflate(serialize($cache),1));
            error_log('4'.unserialize(gzinflate(gzdeflate(serialize($cache),1))));

            throw new Exception('packing failure.');
        }


        //setting cache
        if($this->cache_time !== false && $GLOBALS['userdata'][0]['database_fallback'] != 1)
        {
            if(isset($GLOBALS['cache']) && $GLOBALS['cache'] !== false)
            {
                try { $GLOBALS['cache']->set(Data::$target_site.$this->cache_id.self::TAGS, $cache, MEMCACHE_COMPRESSED, $this->cache_time*60); } //if seting the cache fails try again...
                catch (Exception $e)
                {
                    try { $GLOBALS['cache']->set(Data::$target_site.$this->cache_id.self::TAGS, $cache, MEMCACHE_COMPRESSED, $this->cache_time*60); } //if setting the cache fails try again...
                    catch (Exception $e)
                    {
                        try { $GLOBALS['cache']->set(Data::$target_site.$this->cache_id.self::TAGS, $cache, MEMCACHE_COMPRESSED, $this->cache_time*60); } //if setting the cache fails again throw exception.
                        catch (Exception $e)
                        {
                            throw new Exception('there was an issue with updating the cache.');
                        }
                    }
                }
            }
        }
    }


    //purgeCache
    //this method simply updates the cache with an empty array
    public function purgeCache()
    {
        $cache = array('purged'=>'yes');

        if($this->cache_time !== false)
        {
            try { $GLOBALS['cache']->set(Data::$target_site.$this->cache_id.self::TAGS, $cache, MEMCACHE_COMPRESSED, $this->cache_time*60); } //if seting the cache fails try again...
            catch (Exception $e)
            {
                try { $GLOBALS['cache']->set(Data::$target_site.$this->cache_id.self::TAGS, $cache, MEMCACHE_COMPRESSED, $this->cache_time*60); } //if seting the cache fails try again...
                catch (Exception $e)
                {
                    try { $GLOBALS['cache']->set(Data::$target_site.$this->cache_id.self::TAGS, $cache, MEMCACHE_COMPRESSED, $this->cache_time*60); } //if setting the cache fails again throw exception.
                    catch (Exception $e)
                    {
                        throw new Exception('there was an issue with clearing the cache.');
                    }
                }
            }
        }

        $GLOBALS['database']->execute_query('DELETE FROM `battle_fallback` WHERE `id` = '.$this->cache_id);

        $this->users = array();
        $this->battle_log = array();
        $this->removed_users = array();
        $this->balance = array();
        $this->balanceDSR = array();
        $this->turn_counter = 0;
        $this->turn_timer = time() + 60;
        $this->extra = false;
        $this->census = array();
        $this->battle_history_id = 0;
    }


    //addUser
    //initializes array locations for the new user
    //takes a username
    public function addUser($username, $team)
    {
        if(!isset($this->users[$username]))
        {
            $this->users[$username] = array();
            $this->users[$username][self::TEAM] = $team;
            $this->users[$username][self::TAGS] = array();
            $this->users[$username][self::TAGSINEFFECT] = array();

            //saving team data for easy access
            if(!isset($this->teams[$team]))
                $this->teams[$team] = array();

            $this->teams[$team][] = $username;

            $this->data[$username] = array();

            $this->balance[$username] = array();
        }

        if(!empty($this->location_tags))
            $this->addTags($this->location_tags, $username, $username, self::LOCATION, self::LOCATION);
    }

    //removeUser
    //removes array locations for the user and removes tags they owned from other users.
    //takes a username
    public function removeUser($username)
    {
        $team = $this->users[$username][self::TEAM];

        //removing users arrays
        unset($this->users[$username]);

        //unset($this->teams[$team][array_search($username,$this->teams[$team])]);
        foreach($this->teams[$team] as $user_key => $user)
            if($user == $username)
                unset($this->teams[$team][$user_key]);

        if(count($this->teams[$team]) == 0)
            unset($this->teams[$team]);

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
        }
    }

    //addTags
    //adds Tags to the system and to the correct place/user.
    //takes an instance of the tag class or an array of the tag class
    public function addTags($tags, $target_user, $owning_user, $origin, $equipment_id = false, $effect_level = false, $targeting_type = false, $weapon_boost = false, $weapon_ids = false)
    {
        $original_target_user = $target_user;

        //if the tag passed in is not in an array make it so. that way the for each loop can use it.
        if(!is_array($tags))
            $tags = array($tags);

        //check for valid users
        if(isset($this->users[$target_user]) && isset($this->users[$owning_user]) || $owning_user === 'location')
        {
            if($owning_user === 'location')
                $owning_user = $target_user;

            //for every tag that was passed in
            $saved_target_user = $target_user;
            foreach($tags as $tag)
            {
                $target_user = $saved_target_user;//restoring target user if it was changed for the last tag.

                //checking targetRestriction
                $check = true;
                if( $tag->targetRestriction !== false || ( is_array($tag->targetRestriction) && $tag->targetRestriction[0] !== false) )
                {
                    if(!is_array($tag->targetRestriction))
                        $tag->targetRestriction = array($tag->targetRestriction);

                    if( count($tag->targetRestriction) == 1 && ($tag->targetRestriction[0] == 'AI' || $tag->targetRestriction[0] == 'HUMAN' ) )
                    {
                        if($tag->targetRestriction[0] == 'AI' && ( !isset($this->users[$target_user]['ai']) || $this->users[$target_user]['ai'] !== true ))
                            $check = false;
                        else if($tag->targetRestriction[0] == 'HUMAN' && ( isset($this->users[$target_user]['ai']) && $this->users[$target_user]['ai'] !== false ))
                            $check = false;
                    }
                    else
                    {
                        foreach($tag->targetRestriction as $restriction)
                        {
                            $cleaned_name = explode(' ,', $target_user);
                            $cleaned_name = $cleaned_name[0];

                            if(( $restriction != $cleaned_name ) && //check for name
                               ( !isset($this->users[$target_user]['uid']) || $restriction != $this->users[$target_user]['uid'] ) && //check for uid
                               ( !isset($this->users[$target_user]['aid']) || $restriction != $this->users[$target_user]['aid'] ) ) //check for aid
                                $check = false;
                        }
                    }
                }


                if($check)
                {
                    //checking for targeting mode set from jutsu or weapon
                //if targeting mode is set and the target mode is default...
                //override default targeting mode.
                //extra parsing here is to pull out extra information needed...
                //by aoe tags.
                    if($targeting_type !== false && $tag->target === true)
                    {
                        $checking_for_array = explode(',',$targeting_type);

                        if( count($checking_for_array) == 1)
                            $tag->target = $targeting_type;
                        else
                            $tag->target = $checking_for_array;
                    }

                    //check target against targeting type if miss match fix.
                    if(($tag->target == self::TARGETSELF || $tag->target === true) && $owning_user != $target_user )
                        $target_user = $owning_user;

                    //if the tag's target mode is opponent and target is not an opponent get one
                    else if($tag->target == self::OPPONENT && ($this->users[$target_user][self::TEAM] == $this->users[$owning_user][self::TEAM] || $target_user == $owning_user))
                    {
                        $temp = array();
                        foreach($this->users as $username => $userdata)
                        {
                            if ($userdata[self::TEAM] != $this->users[$owning_user][self::TEAM])
                                $temp[] = $username;
                        }

                        if(count($temp) != 0)
                            $target_user = $temp[random_int(0,count($temp) -1)];
                    }

                    //if the tag's target mode is ally
                    else if($tag->target == self::ALLY && $this->users[$target_user][self::TEAM] != $this->users[$owning_user][self::TEAM])
                    {
                        $temp = array();
                        foreach($this->users as $username => $userdata)
                            if ($userdata[self::TEAM] == $this->users[$owning_user][self::TEAM])
                                $temp[] = $username;

                        if(count($temp) != 0)
                            $target_user = $temp[random_int(0,count($temp) -1)];
                    }
                    
                    //if the tag's target mode is other
                    else if($tag->target == self::OTHER && $target_user == $owning_user)
                    {
                        $temp = array();
                        foreach($this->users as $username => $userdata)
                            if ($username != $owning_user)
                                $temp[] = $username;

                        if(count($temp) != 0)
                            $target_user = $temp[random_int(0,count($temp) -1)];
                    }

                    //if this is a damaging tag and weapon boost is set do so
                    if($weapon_boost !== false && $this->is_damaging_tag($tag->name))
                    {
                        $tag->weapon_boost = $weapon_boost;
                        $tag->weapon_ids = $weapon_ids;
                    }
                    else if( $weapon_boost === false && $origin == $this::WEAPON && $weapon_ids !== false )
                    {
                        $tag->weapon_ids = $weapon_ids;
                    }


                    //checking miss target chance
                    if(is_array($tag->missTargetChance) && count($tag->missTargetChance) == 2)
                        $tag->missTargetChance = $tag->missTargetChance[0] + $tag->missTargetChance[1] * $tag->effect_level;
                    else
                        $tag->missTargetChance = $tag->missTargetChance;

                    if($tag->missTargetChance !== false)
                        if(random_int(1,100) <= round($tag->missTargetChance))
                            $miss = true;
                        else
                            $miss = false;
                    else
                        $miss = false;

                    //checking wrong target chance
                    if(is_array($tag->wrongTargetChance) && count($tag->wrongTargetChance) == 2)
                        $tag->wrongTargetChance = $tag->wrongTargetChance[0] + $tag->wrongTargetChance[1] * $tag->effect_level;
                    else
                        $tag->wrongTargetChance = $tag->wrongTargetChance;

                    if($miss === true && $tag->wrongTargetChance !== false)
                        if(random_int(1,100) <= round($tag->wrongTargetChance))
                            $wrongTarget = true;
                        else
                            $wrongTarget = false;
                    else
                        $wrongTarget = false;

                    //checking back fire chance
                    if(is_array($tag->backfireChance) && count($tag->backfireChance) == 2)
                        $tag->backfireChance = $tag->backfireChance[0] + $tag->backfireChance[1] * $tag->effect_level;
                    else
                        $tag->backfireChance = $tag->backfireChance;

                    if($tag->backfireChance !== false)
                        if(random_int(1,100) <= round($tag->backfireChance))
                            $backfire = true;
                        else
                            $backfire = false;
                    else
                        $backfire = false;

                    //checking polarity switch chance
                    if(is_array($tag->polaritySwitchChance) && count($tag->polaritySwitchChance) == 2)
                        $tag->polaritySwitchChance = $tag->polaritySwitchChance[0] + $tag->polaritySwitchChance[1] * $tag->effect_level;
                    else
                        $tag->polaritySwitchChance = $tag->polaritySwitchChance;

                    if($tag->polaritySwitchChance !== false)
                        if(random_int(1,100) <= round($tag->polaritySwitchChance))
                        {
                            //handle array less value
                            if(!is_array($tag->value) && is_numeric($tag->value))
                                $tag->value *= -1;

                            //handle arrayed value
                            else
                                foreach($tag->value as $key_value => $value_value)
                                    if(is_numeric($value_value))
                                        $tag->value[$key_value] *= -1;
                        }


                    //respond to miss chance vs wrong target chance
                    if($miss === true && $wrongTarget === false)
                        continue;

                    if(is_array($tag->noStack) && count($tag->noStack) == 1)
                    {
                        $tag->noStack = $tag->noStack[0];
                    }

                    //check for noStack on this tag
                    if( ( !is_array($tag->noStack) && ($tag->noStack === true || $tag->noStack == '+' || $tag->noStack == '-' || $tag->noStack === 'exclusive' ) ) ||
                         is_array($tag->noStack) )
                    {
                        //checking all of users tags
                        foreach($this->users[$target_user][self::TAGS] as $key => $user_tag)
                        {
                            if( ($user_tag->noStack !== false || ( (is_array($tag->noStack) && in_array('exclusive', $tag->noStack)) || $tag->noStack === 'exclusive' ) )  && $user_tag->name == $tag->name)
                            {
                                if( (!is_array($tag->noStack) && $tag->noStack !== '+' && $tag->noStack !== '-') || (is_array($tag->noStack) && !in_array('+', $tag->noStack) && !in_array('-', $tag->noStack) ) )
                                {
                                    if( $tag->delay < $this->users[$target_user][self::TAGS][$key]->duration )
                                    {
                                        unset($this->users[$target_user][self::TAGS][$key]);

                                        if( (!is_array($tag->noStack) || !in_array('exclusive', $tag->noStack)) && $tag->noStack !== 'exclusive')
                                           break;
                                    }
                                }
                                else
                                {
                                    $value = $this->parseValue($user_tag->value, $user_tag->effect_level, $user_tag);
                                    $total = 0;
                                    foreach($value as $thing)
                                        $total += $thing;

                                    if( ( ( (!is_array($tag->noStack) && $tag->noStack == '+') || (is_array($tag->noStack) && in_array('+', $tag->noStack))) && $total > 0) || 
                                        ( ( (!is_array($tag->noStack) && $tag->noStack == '-') || (is_array($tag->noStack) && in_array('-', $tag->noStack))) && $total < 0) )
                                    {
                                        if( $tag->delay < $this->users[$target_user][self::TAGS][$key]->duration )
                                        {
                                            unset($this->users[$target_user][self::TAGS][$key]);

                                            if( (!is_array($tag->noStack) || !in_array('exclusive', $tag->noStack)) && $tag->noStack !== 'exclusive')
                                                break;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    //check for noStack exclusive on other tags.
                    $no_stack = false;
                    foreach($this->users[$target_user][self::TAGS] as $key => $user_tag)
                    {
                        if($user_tag->name == $tag->name && ( $user_tag->noStack === 'exclusive' || (is_array($user_tag->noStack) && in_array('exclusive', $user_tag->noStack)) ))
                        {
                            if( !is_array($user_tag->noStack) || ( !in_array('+', $user_tag->noStack) && !in_array('-', $user_tag->noStack) ) )
                            {
                                $no_stack = true;
                                break;
                            }
                            else if(is_array($user_tag->noStack) && ( in_array('+', $user_tag->noStack) || in_array('-', $user_tag->noStack) ))
                            {
                                $value = $this->parseValue($tag->value, $tag->effect_level, $tag);
                                $total = 0;
                                foreach($value as $thing)
                                    $total += $thing;

                                if( (in_array('+', $user_tag->noStack) && $total > 0) || (in_array('-', $user_tag->noStack) && $total < 0) )
                                {
                                    $no_stack = true;
                                    break;
                                }
                            }
                        }
                    }

                    //if there is an exclusive tag dont apply this tag.
                    if($no_stack === true)
                        continue;

                    //set owner information to tag.
                    $tag->owner = $owning_user;
                    $tag->origin = $origin;
                    $tag->effect_level = $effect_level;
                    $tag->equipment_id = $equipment_id;

                    //check for duration range
                    if(is_array($tag->duration))
                    {
                        if(count($tag->duration) == 2)
                            $tag->duration = random_int($tag->duration[0],$tag->duration[1]);
                        else if(count($tag->duration) == 1)
                            $tag->duration = $tag->duration[0];
                        else
                        {
                            $tag->duration = $tag->duration[0];
                            if($this->debugging)
                                $GLOBALS['DebugTool']->push($tag->duration, 'duration was a bad array. has been set to: ', __METHOD__, __FILE__, __LINE__);
                        }
                    }

                    //if the tag's target mode self
                    if($tag->target == self::TARGETSELF || $tag->target === true)
                    {
                        //if the target is self
                        if($target_user == $owning_user)
                        {
                            //checking wrongTarget
                            if($wrongTarget === true)
                            {
                                $keys = array_keys($this->users);
                                $tag->target = $keys[ random_int(0, count($keys) - 1) ];
                                $this->users[$tag->target][self::TAGS][] = $tag; //set the tag
                            }

                            //add tag normal
                            else
                            {
                                //destroying target data no longer needed.
                                $tag->target = $target_user;
                                $this->users[$target_user][self::TAGS][] = $tag; //set the tag

                                //back fire not avaliable for target mode self
                                if($backfire && $this->debugging)
                                    $GLOBALS['DebugTool']->push('', 'backfire is not available for target mode self', __METHOD__, __FILE__, __LINE__);
                            }
                        }

                        //if the target is not self and debugging is on
                        else if($this->debugging)
                            $GLOBALS['DebugTool']->push('this tags target is '.$tag->target.' and the target is not the tags owner. '.$target_user.' != '.$owning_user, 'there is an issue with this tag: broken target', __METHOD__, __FILE__, __LINE__);
                    }

                    //if the tag's target mode is opponent
                    else if($tag->target == self::OPPONENT)
                    {
                        //if the target is an opponent of the owner
                        if($this->users[$target_user][self::TEAM] != $this->users[$owning_user][self::TEAM])
                        {
                            //if the tag is set to hit the wrong target
                            if($wrongTarget === true)
                            {
                                //getting targets
                                $possible_new_targets = array();
                                foreach($this->teams as $team_key => $team)
                                    if($team_key != $this->users[$owning_user][self::TEAM])
                                        foreach($team as $user)
                                            $possible_new_targets[] = $user;

                                $tag->target = $possible_new_targets[ random_int(0, count($possible_new_targets) - 1)];
                                $this->users[$tag->target][self::TAGS][] = $tag; //set the tag
                            }

                            //if the tag is set to backfire
                            else if($backfire === true)
                            {
                                $tag->target = $owning_user;
                                $this->users[$tag->target][self::TAGS][] = $tag;
                            }

                            //normal add tag
                            else
                            {
                                //destroying target data no longer needed.
                                $tag->target = $target_user;
                                $this->users[$target_user][self::TAGS][] = $tag; //set the tag
                            }
                        }

                        //if the target is not an opponent and debugging is on
                        else if($this->debugging)
                            $GLOBALS['DebugTool']->push('this tags target\'s team is '.$this->users[$target_user][self::TEAM].' and the target\'s team matches the tags owner\'s team. '.$this->users[$target_user][self::TEAM].' == '.$this->users[$owning_user][self::TEAM], 'there is an issue with this tag: broken target', __METHOD__, __FILE__, __LINE__);
                    }

                    //if the tag's target mode is ally
                    else if($tag->target == self::ALLY)
                    {
                        //if the target is an opponent of the owner
                        if($this->users[$target_user][self::TEAM] == $this->users[$owning_user][self::TEAM])
                        {
                            //if tag is set to hit the wrong target
                            if($wrongTarget === true)
                            {
                                //getting targets
                                $possible_new_targets = $this->teams[$this->users[$owning_user][self::TEAM]];

                                $tag->target = $possible_new_targets[ random_int(0, count($possible_new_targets) - 1)];
                                $this->users[$tag->target][self::TAGS][] = $tag; //set the tag
                            }

                            //if the tag is set to backfire
                            if($backfire === true)
                            {
                                //getting all users that are not allys
                                $possible_new_targets = array();
                                foreach($this->teams as $team_key => $team)
                                    if($team_key != $this->users[$target][self::TEAM])
                                        foreach($team as $user)
                                            $possible_new_targets[] = $user;

                                //setting target to random non ally
                                $target = $possible_new_targets[ random_int(0, count($possible_new_targets) - 1) ];
                            }

                            //normal add tag
                            else
                            {
                                //destroying target data no longer needed.
                                $tag->target = $target_user;
                                $this->users[$target_user][self::TAGS][] = $tag; //set the tag
                            }
                        }

                        //if the target is not an opponent and debugging is on
                        else if($this->debugging)
                            $GLOBALS['DebugTool']->push('this tags target\'s team is '.$this->users[$target_user][self::TEAM].' and the target\'s team does not matche the tags owner\'s team. '.$this->users[$target_user][self::TEAM].' != '.$this->users[$owning_user][self::TEAM], 'there is an issue with this tag: broken target', __METHOD__, __FILE__, __LINE__);
                    }

                    //if the tag's target mode is other
                    else if($tag->target == self::OTHER)
                    {
                        //if the tags target is not self
                        if($target_user != $owning_user)
                        {
                            //checking wrongTarget
                            if($wrongTarget === true)
                            {
                                $keys = array_keys($this->users);
                                unset($keys[array_search($owning_user,$keys)]);
                                $keys = array_values($keys);

                                $tag->target = $keys[ random_int(0, count($keys) - 1) ];
                                $this->users[$tag->target][self::TAGS][] = $tag; //set the tag
                            }

                            //if tag is set to back fire
                            else if($backfire)
                            {
                                $tag->target = $owning_user;
                                $this->users[$tag->target][self::TAGS][] = $tag;
                            }

                            //normal add tag
                            else
                            {
                                //destroying target data no longer needed.
                                $tag->target = $target_user;
                                $this->users[$target_user][self::TAGS][] = $tag; //set the tag
                            }
                        }

                        //if the tags target is self and debugging is on
                        else if($this->debugging)
                            $GLOBALS['DebugTool']->push('this tags target is other and the target is the tags owner. '.$target_user.' == '.$owning_user, 'there is an issue with this tag: broken target', __METHOD__, __FILE__, __LINE__);

                    }

                    //if the tag's target is TARGET
                    else if($tag->target == self::TARGET)
                    {
                        //checking wrongTarget
                        if($wrongTarget === true)
                        {
                            $keys = array_keys($this->users);
                            $tag->target = $keys[ random_int(0, count($keys) - 1) ];
                            $this->users[$tag->target][self::TAGS][] = $tag; //set the tag
                        }

                        //if tag is set to backfire
                        else if($backfire === true)
                        {
                            //if the target is not the owner set it to the owner.
                            if($target_user != $owning_user)
                            {
                                $tag->target = $owning_user;
                                $this->users[$tag->target][self::TAGS][] = $tag;
                            }
                            //if the target is the owner set it to anyone but the owner.
                            else
                            {
                                $keys = array_keys($this->users);
                                unset($keys[array_search($owning_user,$keys)]);
                                $keys = array_values($keys);

                                $tag->target = $keys[ random_int(0, count($keys) - 1) ];
                                $this->users[$tag->target][self::TAGS][] = $tag; //set the tag
                            }
                        }

                        //add tag normal
                        else
                        {
                            //destroying target data no longer needed.
                            $tag->target = $target_user;
                            $this->users[$target_user][self::TAGS][] = $tag; //set the tag
                        }
                    }

                    //if the tag's is an array with 0 set and the target is team or all
                    else if((isset($tag->target[0]) && ($tag->target[0] == self::TEAM || $tag->target[0] == self::ALL || $tag->target[0] == self::ALLOTHER || $tag->target[0] == self::RIVALTEAMS)) || ($tag->target == self::TEAM || $tag->target == self::ALL || $tag->target == self::ALLOTHER || $tag->target == self::RIVALTEAMS)) //checking target
                    {
                        //if target data came with just all or team set defaults.
                        if(!is_array($tag->target))
                        {
                            $tag->target = array($tag->target);//setting target type
                            $tag->target[] = 999; //setting max targets
                            $tag->target[] = 100; //setting chance to hit
                            $tag->target[] = 100; //setting min % effect
                            $tag->target[] = 100; //setting max % effect
                        }


                        if( count($tag->target) == 5 && $tag->target[1] > 0 && //checking if target data is good
                            $tag->target[2] > 0 &&  //checking for percentage
                            $tag->target[3] >= 0 && //checking for percentage with zero
                            $tag->target[4] > 0) //checking for percentage and min < max
                        {


                            if($backfire === true)
                                $GLOBALS['DebugTool']->push('', 'backfire is not available for aoe target modes', __METHOD__, __FILE__, __LINE__);


                            $target = $target_user;
                            //changing target if wrongTarget
                            if($wrongTarget === true)
                            {
                                //if all
                                if($tag->target[0] == self::ALL)
                                {
                                    $possible_new_targets = array_keys($this->users);
                                    $target = $possible_new_targets[ random_int(0, count($possible_new_targets) - 1) ];
                                }

                                //if allOther
                                else if($tag->target[0] == self::ALLOTHER)
                                {
                                    $possible_new_targets = array_keys($this->users);
                                    $target = $possible_new_targets[ random_int(0, count($possible_new_targets) - 1) ];
                                }

                                //if rivalTeams
                                else if($tag->target[0] == self::RIVALTEAMS)
                                {
                                    $possible_new_targets = array();
                                    foreach($this->teams as $team_key => $team)
                                        if($team_key == $this->users[$target][self::TEAM])
                                            foreach($team as $user)
                                                $possible_new_targets[] = $user;


                                    $GLOBALS['DebugTool']->push($possible_new_targets, 'possible_new_targets in rival teams', __METHOD__, __FILE__, __LINE__);


                                    $target = $possible_new_targets[ random_int(0, count($possible_new_targets) - 1) ];
                                }

                                //if team
                                else if($tag->target[0] == self::TEAM)
                                {
                                    $possible_new_targets = $this->teams[$this->users[$target][self::TEAM]];
                                    $target = $possible_new_targets[ random_int(0, count($possible_new_targets) - 1) ];
                                }

                            }

                            //if the target mode is all set result of aoe early.
                            if($tag->target[0] == self::ALL)
                                $tag->resultOfAoe = true;

                            //dont give the tag to the target if the targeting mode is rival teams
                            if($tag->target[0] != self::RIVALTEAMS && $tag->target[0] != self::ALLOTHER)
                            {
                                $quick_tag = clone $tag;
                                $quick_tag->target = $target;

                                if(is_array($quick_tag->noStackAoe) && count($quick_tag->noStackAoe) == 1)
                                {
                                    $quick_tag->noStackAoe = $quick_tag->noStackAoe[0];
                                }

                                $apply = false;

                                //check for no stack aoe
                                if( ( !is_array($quick_tag->noStackAoe) && $quick_tag->resultOfAoe && ($quick_tag->noStackAoe === true || $quick_tag->noStackAoe == '+' || $quick_tag->noStackAoe == '-' || $quick_tag->noStackAoe === 'exclusive' ) ) ||
                                     is_array($quick_tag->noStackAoe) )
                                {
                                    $noStack = false;

                                    //check each of users tags
                                    foreach($this->users[$target][self::TAGS] as $key => $user_tag)
                                        if( ($user_tag->noStackAoe !== false || ( (is_array($quick_tag->noStackAoe) && in_array('exclusive', $quick_tag->noStackAoe)) || $quick_tag->noStackAoe === 'exclusive' ) )  && $user_tag->name == $quick_tag->name)
                                        {
                                            //if no + or -
                                            if( (!is_array($quick_tag->noStackAoe) && $quick_tag->noStackAoe !== '+' && $quick_tag->noStackAoe !== '-') || (is_array($quick_tag->noStackAoe) && !in_array('+', $quick_tag->noStackAoe) && !in_array('-', $quick_tag->noStackAoe) ) )
                                            {
                                                unset($this->users[$target][self::TAGS][$key]);

                                                if( (!is_array($quick_tag->noStackAoe) || !in_array('exclusive', $quick_tag->noStackAoe)) && $quick_tag->noStackAoe !== 'exclusive')
                                                    break;
                                            }

                                            //handling + and -
                                            else
                                            {
                                                $value = $this->parseValue($user_tag->value, $user_tag->effect_level, $user_tag);
                                                $total = 0;
                                                foreach($value as $thing)
                                                    $total += $thing;
                                                
                                                if( ( ( (!is_array($quick_tag->noStackAoe) && $quick_tag->noStackAoe == '+') || (is_array($quick_tag->noStackAoe) && in_array('+', $quick_tag->noStackAoe))) && $total > 0) || 
                                                    ( ( (!is_array($quick_tag->noStackAoe) && $quick_tag->noStackAoe == '-') || (is_array($quick_tag->noStackAoe) && in_array('-', $quick_tag->noStackAoe))) && $total < 0) )
                                                {
                                                    unset($this->users[$target][self::TAGS][$key]);

                                                    if( (!is_array($quick_tag->noStackAoe) || !in_array('exclusive', $quick_tag->noStackAoe)) && $quick_tag->noStackAoe !== 'exclusive')
                                                        break;
                                                }
                                            }
                                        }
                                
                                    //if this can be applied... apply it.
                                    if(!$noStack)
                                        $apply = true;
                                }
                                else
                                    $apply = true;


                                //check for noStackAoe exclusive on other tags.
                                if($apply === true)
                                {
                                    $no_stack = false;
                                    foreach($this->users[$target][self::TAGS] as $key => $user_tag)
                                    {
                                        if($user_tag->name == $quick_tag->name && ( $user_tag->noStackAoe === 'exclusive' || (is_array($user_tag->noStackAoe) && in_array('exclusive', $user_tag->noStackAoe)) ))
                                        {
                                            if( !is_array($user_tag->noStackAoe) || ( !in_array('+', $user_tag->noStackAoe) && !in_array('-', $user_tag->noStackAoe) ) )
                                            {
                                                $no_stack = true;
                                                break;
                                            }
                                            else if(is_array($user_tag->noStackAoe) && ( in_array('+', $user_tag->noStackAoe) || in_array('-', $user_tag->noStackAoe) ))
                                            {
                                                $value = $this->parseValue($quick_tag->value, $quick_tag->effect_level, $quick_tag);
                                                $total = 0;
                                                foreach($value as $thing)
                                                    $total += $thing;
                                    
                                                if( (in_array('+', $user_tag->noStackAoe) && $total > 0) || (in_array('-', $user_tag->noStackAoe) && $total < 0) )
                                                {
                                                    $no_stack = true;
                                                    break;
                                                }
                                            }
                                        }
                                    }


                                    //if there is an exclusive tag dont apply this tag.
                                    if($no_stack !== true)
                                        $this->users[$target][self::TAGS][] = $quick_tag;
                                }
                            }

                            //marking the tag as being the result of aoe after this point.
                            $tag->resultOfAoe = true;

                            //getting team id
                            $team = $this->users[$target][self::TEAM];

                            //numbers to keep track of during this loop
                            $effected_users = 0;
                            $user_count = count($this->users);

                            //randomizing the order of the users
                            $this->users = $this->shake($this->users);

                            if(is_array($tag->noStackAoe) && count($tag->noStackAoe) == 1)
                            {
                                $tag->noStackAoe = $tag->noStackAoe[0];
                            }

                            //for each user
                            foreach($this->users as $user_key => $user)
                            {

                                //if there is an exclusive tag dont apply this tag.
                                if($no_stack === true)
                                    continue;


                                //if the tag's target is all or the tag's target is team and this is a team member and this is not the target and we have not hit max effected users yet
                                if( ($tag->target[0] == self::ALL || $tag->target[0] == self::ALLOTHER || ($tag->target[0] == self::TEAM && $user[self::TEAM] == $team) || ($tag->target[0] == self::RIVALTEAMS && $user[self::TEAM] != $team)) && $user_key != $target && $effected_users < $tag->target[1] )
                                {

                                    //check chance
                                    if(random_int(1, 100) <= ($tag->target[2]))
                                    {
                                        //updating value for aoe effect
                                        $temp_tag = clone $tag;




                                        ////check for noStackAoe on this tag
                                        if( ( !is_array($tag->noStackAoe) && $tag->resultOfAoe === true && ($tag->noStackAoe === true || $tag->noStackAoe == '+' || $tag->noStackAoe == '-' || $tag->noStackAoe === 'exclusive' ) ) ||
                                             is_array($tag->noStackAoe) )
                                        {
                                            //checking all of users tags
                                            foreach($this->users[$user_key][self::TAGS] as $key => $user_tag)
                                            {
                                                if( ($user_tag->noStackAoe !== false || ( (is_array($tag->noStackAoe) && in_array('exclusive', $tag->noStackAoe)) || $tag->noStackAoe === 'exclusive' ) )  && $user_tag->name == $tag->name)
                                                {
                                                    if( (!is_array($tag->noStackAoe) && $tag->noStackAoe !== '+' && $tag->noStackAoe !== '-') || (is_array($tag->noStackAoe) && !in_array('+', $tag->noStackAoe) && !in_array('-', $tag->noStackAoe) ) )
                                                    {
                                                        unset($this->users[$user_key][self::TAGS][$key]);
                                        
                                                        if( (!is_array($tag->noStackAoe) || !in_array('exclusive', $tag->noStackAoe)) && $tag->noStackAoe !== 'exclusive')
                                                            break;
                                                    }
                                                    else
                                                    {
                                                        $value = $this->parseValue($user_tag->value, $user_tag->effect_level, $user_tag);
                                                        $total = 0;
                                                        foreach($value as $thing)
                                                            $total += $thing;
                                        
                                                        if( ( ( (!is_array($tag->noStackAoe) && $tag->noStackAoe == '+') || (is_array($tag->noStackAoe) && in_array('+', $tag->noStackAoe))) && $total > 0) || 
                                                            ( ( (!is_array($tag->noStackAoe) && $tag->noStackAoe == '-') || (is_array($tag->noStackAoe) && in_array('-', $tag->noStackAoe))) && $total < 0) )
                                                        {
                                                            unset($this->users[$user_key][self::TAGS][$key]);
                                        
                                                            if( (!is_array($tag->noStackAoe) || !in_array('exclusive', $tag->noStackAoe)) && $tag->noStackAoe !== 'exclusive')
                                                                break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                            
                                        
                                        //check for noStackAoe exclusive on other tags.
                                        $no_stack = false;
                                        foreach($this->users[$user_key][self::TAGS] as $key => $user_tag)
                                        {
                                            if($user_tag->name == $tag->name && $tag->resultOfAoe === true && ( $user_tag->noStackAoe === 'exclusive' || (is_array($user_tag->noStackAoe) && in_array('exclusive', $user_tag->noStackAoe)) ))
                                            {
                                                if( !is_array($user_tag->noStackAoe) || ( !in_array('+', $user_tag->noStackAoe) && !in_array('-', $user_tag->noStackAoe) ) )
                                                {
                                                    $no_stack = true;
                                                    break;
                                                }
                                                else if(is_array($user_tag->noStackAoe) && ( in_array('+', $user_tag->noStackAoe) || in_array('-', $user_tag->noStackAoe) ))
                                                {
                                                    $value = $this->parseValue($tag->value, $tag->effect_level, $tag);
                                                    $total = 0;
                                                    foreach($value as $thing)
                                                        $total += $thing;
                                        
                                                    if( (in_array('+', $user_tag->noStackAoe) && $total > 0) || (in_array('-', $user_tag->noStackAoe) && $total < 0) )
                                                    {
                                                        $no_stack = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        }

                                        if($no_stack === true)
                                            continue;


                                        $end_of_line_position = $temp_tag->target[1] -1;

                                        //if the max targeted is greater than max targetable then use the later
                                        if( ($tag->target[0] == self::ALL  || $tag->target[0] == self::ALLOTHER) && (count($this->users) - 2) < $end_of_line_position)
                                            $end_of_line_position = (count($this->users) - 2);

                                        //if the targeting mode is team or rivalteams
                                        else if( $tag->target[0] == self::TEAM || $tag->target[0] == self::RIVALTEAMS)
                                        {
                                            //count the number of targeted users
                                            $count = 0;
                                            foreach($this->users as $a)
                                                if(($a[self::TEAM] == $team && $tag->target[0] == self::TEAM) || ($a[self::TEAM] != $team && $tag->target[0] == self::RIVALTEAMS))
                                                    $count += 1;

                                            //subtract 1 for the maths and 1 for having already targeted the user.
                                            if($tag->target[0] == self::TEAM)
                                                $end_of_line_position = $count - 2;

                                            //subtract 1 for the maths
                                            else if ($tag->target[0] == self::RIVALTEAMS)
                                                $end_of_line_position = $count - 1;
                                        }

                                        //if this is the first user or end of line position is 0 set the ratio to the max
                                        if($effected_users == 0 || $end_of_line_position == 0)
                                            $ratio = $temp_tag->target[4] / 100;

                                        //if this is the last user set the ratio to the min
                                        else if($effected_users == $end_of_line_position)
                                            $ratio = $temp_tag->target[3] / 100;

                                        //other wise find the ratio.
                                        else
                                            $ratio = ((($end_of_line_position - $effected_users) / $end_of_line_position) * (($temp_tag->target[4] / 100) - ($temp_tag->target[3] / 100))) + ($temp_tag->target[3] / 100);

                                        //updating the tags value
                                    //if the value field is not an array
                                        if(!is_array($temp_tag->value))
                                        {
                                            if(is_numeric($temp_tag->value))
                                                $temp_tag->value = ( $temp_tag->value * $ratio);//cutting down to int
                                        }
                                        //if the field is an array
                                        else
                                        {
                                            foreach($temp_tag->value as $temp_key => $temp_value)
                                                if(is_numeric($temp_value))
                                                    $temp_tag->value[$temp_key] = $temp_tag->value[$temp_key] * $ratio;
                                        }


                                        //destroying target data no longer needed.
                                        $temp_tag->target = $user_key;

                                        //set tag to this user
                                        $this->users[$user_key][self::TAGS][] = $temp_tag;

                                        //update effected users
                                        $effected_users += 1;
                                    }

                                }
                            }
                        }

                        else if ($this->debugging) //if debugging is on and there was an issue with
                        {
                            //if the error is caused by missing data
                            if(count($tag->target) != 5)
                                $GLOBALS['DebugTool']->push('target all and team require 5 pieces of information (team or all, max_targets, % to hit, min % effect, max % effect)',
                                                            'there is an issue with this tag: broken target', __METHOD__, __FILE__, __LINE__);

                            //if the error is not caused by mizzing data.
                            else
                                $GLOBALS['DebugTool']->push('','target values must be greater than zero or atleast zero in the case of min',__METHOD__, __FILE__, __LINE__);
                        }
                    }
                    else if ($this->debugging) //bad target type or format
                    {
                        if(!is_array($tag->target))
                            $GLOBALS['DebugTool']->push('target type '.$tag->target.' does not exist.', 'there is an issue with this tag: broken target', __METHOD__, __FILE__, __LINE__);
                        else
                            $GLOBALS['DebugTool']->push($tag->target, 'there is an issue with this tag: broken target', __METHOD__, __FILE__, __LINE__);
                    }
                }
            }
        }
        else if($this->debugging)
        {
            if(!isset($this->users[$target_user]))
                $GLOBALS['DebugTool']->push($target_user, 'target user was not valid', __METHOD__, __FILE__, __LINE__);
            else
                $GLOBALS['DebugTool']->push($owning_user, 'owning user was not valid', __METHOD__, __FILE__, __LINE__);
        }


    }


    //addLocationTags adds tags to locationTags
    public function addLocationTags($tags)
    {
        $this->location_tags = array_merge($tags, $this->location_tags);

        foreach(array_keys($this->users) as $username)
        {
            if(!empty($this->location_tags))
                $this->addTags($tags, $username, $username, self::LOCATION, self::LOCATION);

            foreach($tags as $tag)
                $tag->target = self::TARGETSELF;
        }
    }


    public function changeLocationTags($tags)
    {
        $this->location_tags = $tags;

        //location tags have a fake location equipment id
        $this->removeEquipmentById(self::LOCATION);

        foreach(array_keys($this->users) as $username)
        {
            if(!empty($this->location_tags))
                $this->addTags($tags, $username, $username, self::LOCATION, self::LOCATION);

            foreach($tags as $tag)
                $tag->target = self::TARGETSELF;
        }
    }


    //processTags
    //handles all of the tags
    public function processTags($user_order)
    {
        //move all tags that are in effect to tags_in_effect
        $this->updateTagsInEffect();

        //build array of all tags from all tags that are currently in effect.
        $this->run_ready_array = array();
        foreach($user_order as $key => $username)
        {
            if(isset($this->users[$username]))
            {
                foreach($this->users[$username][self::TAGSINEFFECT] as $tag_key => $tag)
                {
                    $tag->turn_order = $key;
                    $this->run_ready_array[$username.$tag_key] = $tag; //setting key here so clear and delay can address this array and function correctly.
                }
            }
        }

        $reset = true;
        $endless_catch = 0;
        while($reset === true && $endless_catch < 2)
        {
            $reset = false;

            //sorting array so that tags will be ran in the correct order.
            //must keep keys or the clear and delay tags will no longer function.
            uasort($this->run_ready_array, array($this, 'tagOrder'));

            //call all tags in order.
            //for each order group of tags...
            foreach($this->run_ready_array as $tag_key => $tag)

                //if a tag method exists...
                if(method_exists($this, $tag->name) && in_array($tag->name, $this->ALLOWEDTAGS))
                {

                    //foreach runs over a copy of the array check for changes made by clear and delay
                    if(isset($this->run_ready_array[$tag_key]))
                    {
                        //run the method and pass the tag to it
                        $tag->key = $tag_key;

                        //if failure chance is a array
                        if( is_array($tag->failureChance ) && count($tag->failureChance) == 2)
                            $tag->failureChance = $tag->failureChance[0] + ($tag->failureChance[1] * $tag->effect_level);
                        else
                            $tag->failureChance = $tag->failureChance;

                        //if failure chance
                        if( $tag->failureChance === false || random_int(1,100) > $tag->failureChance)
                        {
                            $this->{$tag->name}($tag);

                            if($tag->name == 'copyOrigin' || $tag->name == 'mirrorOrigin' || $tag->name == 'copyPreviousJutsu')
                            {
                                $reset = true;
                                $endless_catch++;
                                break;
                            }
                        }

                    }

                    //else do when a tag has been cleared or delayed
                    else if($this->debugging)
                    {
                        //$GLOBALS['DebugTool']->push($tag->name, 'tag cleared or delayed: ', __METHOD__, __FILE__, __LINE__);
                    }
                }

                //if a tag method does not exist
                //and debugging is on
                else if($this->debugging)

                    //push message to debug tool
                    $GLOBALS['DebugTool']->push($tag->name, 'there is an issue with this tag: un-known tag', __METHOD__, __FILE__, __LINE__);
        }

    //updating age of tags and removing if at duration limit.
        $this->updateAgeAndCheckDurationAndProcessCooldowns();
    }

    //checkStart
    //fills $tagsInEffect with tags that are currently in effect.
    //called by processTags
    public function updateTagsInEffect()
    {
        //foreach user
        if(count($this->users) != 0)
        foreach($this->users as $user_key => $user)
        {
            if($this->debugging)
                $this->users[$user_key][self::TAGSINEFFECT] = array();

            //for each tag
            foreach($user[self::TAGS] as $tag_key => $tag)
            {
                //if tag delay is at 0 and age !> duration
                if($tag->delay == 0 && ($tag->age <= $tag->duration || $tag->duration === false))
                    //move to tags_in_effect
                    $this->users[$user_key][self::TAGSINEFFECT][$tag_key] = clone $tag;
            }
        }
    }

    //updateAgeAndCheckDuration
    //updates the age of all effects that are in tagsInEffect
    //removes all finished tags
    //processing of cool downs occurs in parent.
    public function updateAgeAndCheckDurationAndProcessCooldowns()
    {
        //updating turn counter
        $this->turn_counter += 1;

        //for each user
        foreach($this->users as $user_key => $user)
        {
            //for each status effect
            foreach($user['status_effects'] as $effect_key => $effect)
            {
                if($effect !== -1 && $effect !== -9)//if effect should expire
                {
                    $this->users[$user_key]['status_effects'][$effect_key] -= 1;//age the effect

                    if($this->users[$user_key]['status_effects'][$effect_key] == 0)//if effect is due to expire
                        unset($this->users[$user_key]['status_effects'][$effect_key]); //do so
                }
            }

            //for each tag
            foreach($user[self::TAGS] as $tag_key => $tag)
            {
                //if this tag is delayed...
                if($this->users[$user_key][self::TAGS][$tag_key]->delay > 0)
                    //reduce the delay counter.
                    $this->users[$user_key][self::TAGS][$tag_key]->delay -= 1;

                //if this tag is currently in effect
                if(isset($this->users[$user_key][self::TAGSINEFFECT][$tag_key]))
                {
                    //update age of tag in both tagsineffect and tags
                    $this->users[$user_key][self::TAGSINEFFECT][$tag_key]->age += 1;
                    $this->users[$user_key][self::TAGS][$tag_key]->age += 1;

                    //if this tag has expired
                    if( $tag->duration !== false && $tag->age >= $tag->duration)
                    {
                        //remove tag from both tagsineffect and tags
                        unset($this->users[$user_key][self::TAGSINEFFECT][$tag_key]);
                        unset($this->users[$user_key][self::TAGS][$tag_key]);
                    }
                    else
                    {
                    //if this tag has not expired and is flaged for degrade
                        //update the tags priority.(if it is a lasting effect from a jutsu it needs to start imediately not later on the following turns )
                        if($tag->priority <=3 && $tag->priority >=1)
                            $this->users[$user_key][self::TAGS][$tag_key]->priority = '999';

                        //handle cadence
                        if($tag->cadence !== false)
                            //if cadence is an array
                            if(is_array($tag->cadence))
                            {
                                $this->users[$user_key][self::TAGSINEFFECT][$tag_key]->delay = $tag->cadence[0];
                                $this->users[$user_key][self::TAGS][$tag_key]->delay = $tag->cadence[0];
                                array_shift($this->users[$user_key][self::TAGS][$tag_key]->cadence);

                                if(count($this->users[$user_key][self::TAGS][$tag_key]->cadence) === 1)
                                    $this->users[$user_key][self::TAGS][$tag_key]->cadence = $this->users[$user_key][self::TAGS][$tag_key]->cadence[0];
                            }

                            //if cadence is not an array
                            else
                            {
                                $this->users[$user_key][self::TAGSINEFFECT][$tag_key]->delay = $tag->cadence;
                                $this->users[$user_key][self::TAGS][$tag_key]->delay = $tag->cadence;
                            }


                        if($tag->degrade !== false)
                        {
                            if(is_array($tag->degrade))
                            {
                                //checking for the limit of the degrade, if the limit is a percent update it
                                $limit = $tag->degrade[count($tag->degrade) - 1];
                                if(substr($limit,-1) == '%')
                                {
                                    if(is_array($tag->value))
                                    {
                                        foreach($tag->value as $value)
                                            if(is_numeric($value))
                                            {
                                                $tag->degrade[count($tag->degrade) - 1] = $value * (100 - (rtrim($limit,'%'))) / 100;
                                                $limit = $value * (100 - (rtrim($limit,'%'))) / 100;
                                                break;
                                            }
                                    }
                                    else
                                    {
                                        $tag->degrade[count($tag->degrade) - 1] = $tag->value * (100 - (rtrim($limit,'%'))) / 100;
                                        $limit = $tag->value * (100 - (rtrim($limit,'%'))) / 100;
                                    }
                                }


                                $degrade = $tag->degrade[0];

                                if(count($tag->degrade) > 2)
                                    array_shift($this->users[$user_key][self::TAGS][$tag_key]->degrade);
                            }
                            else
                            {
                                $limit = 0;
                                $degrade = $tag->degrade;
                            }

                            //if degrage is a percentage
                            if(substr($degrade,-1) == '%')
                            {
                                $ratio = (100 - (rtrim($degrade,'%'))) / 100;

                                //checking for valid ratio
                                if($ratio >= 0 && $ratio <= 1)
                                    //if value is an array
                                    if(is_array($tag->value))
                                    {
                                        foreach($tag->value as $key => $value) // for each entry in value
                                        {
                                            if(is_numeric($value)) //if the entry is numeric
                                                if(($tag->value[$key] > $limit && ($tag->value[$key] * $ratio) < $limit) ||
                                                    $tag->value[$key] < $limit && ($tag->value[$key] * $ratio) > $limit)
                                                    $tag->value[$key] = $limit;
                                                else if($tag->value[$key] != $limit)
                                                    $tag->value[$key] *= $ratio;
                                            if(is_float($tag->value[$key]) && $tag->value[$key] != $limit)
                                                $tag->value[$key] = round($tag->value[$key], 2);
                                        }
                                    }
                                    //if value is not an array
                                    else
                                    {
                                        if(is_numeric($value)) //if the entry is numeric
                                            if(($tag->value > $limit && ($tag->value * $ratio) < $limit) ||
                                                $tag->value < $limit && ($tag->value * $ratio) > $limit)
                                                $tag->value = $limit;
                                            else if($tag->value != $limit)
                                                $tag->value *= $ratio;
                                        if(is_float($tag->value) && $tag->value != $limit)
                                            $tag->value = round($tag->value, 2);
                                    }

                                else if($this->debugging)
                                    $GLOBALS['DebugTool']->push($degrade, 'there is an issue with this tag: bad degrade value', __METHOD__, __FILE__, __LINE__);
                            }

                            //if degrade is not a percentage
                            else if( (is_numeric($degrade) || $degrade === true || $degrade === false) && $degrade >= 0 )
                            {
                                //if value is an array
                                if(is_array($tag->value))
                                {
                                    foreach($tag->value as $key => $value) // for each entry in value
                                        if(is_numeric($value)) //if the entry is numeric
                                            if( $value > $limit && $value - $degrade > $limit)// check if it should stay positive and will.
                                                $tag->value[$key] -= $degrade;
                                            else if( $value < $limit && $value + $degrade < $limit)// check if it should stay negative and will
                                                $tag->value[$key] += $degrade;
                                            else//if it wont keep its plarity set it to zero.
                                                $tag->value[$key] = $limit;
                                }
                                //if value is not an array
                                else if( $tag->value > $limit && $tag->value - $degrade > $limit) // check if it should stay positive and will.
                                    $tag->value -= $degrade;
                                else if( $tag->value < $limit && $tag->value + $degrade < $limit) // check if it should stay negative and will.
                                    $tag->value += $degrade;
                                else //if it wont keep its polarity set it to zero.
                                    $tag->value = $limit;
                            }
                            else if($this->debugging && $degrade != 0)
                            {
                                $GLOBALS['DebugTool']->push($degrade, 'there is an issue with this tag: bad degrade value', __METHOD__, __FILE__, __LINE__);

                            }

                            //setting the new value we generated.
                            $this->users[$user_key][self::TAGS][$tag_key]->value = $tag->value;
                        }

                        ///////////////////////

                        //if this tag has not expired and is flaged for amplify
                        if($tag->amplify !== false)
                        {
                            if(is_array($tag->amplify))
                            {
                                $limit = $tag->amplify[count($tag->amplify) - 1];
                                $amplify = $tag->amplify[0];

                                if(count($tag->amplify) > 2)
                                    array_shift($this->users[$user_key][self::TAGS][$tag_key]->amplify);
                            }
                            else
                            {
                                $limit = 9999999999;
                                $amplify = $tag->amplify;
                            }

                            //if amplify is a percentage
                            if(substr($amplify,-1) == '%')
                            {
                                $ratio = 1 + (rtrim($amplify,'%') / 100);

                                //checking for valid ratio
                                if($ratio > 1)
                                    //if value is an array
                                    if(is_array($tag->value))
                                    {
                                        foreach($tag->value as $key => $value) // for each entry in value
                                        {
                                            if(($limit > 0 && $value < 0 ) || ($limit < 0 && $value > 0))
                                                $limit *= -1;

                                            if(is_numeric($value)) //if the entry is numeric
                                                if(($tag->value[$key] > $limit && ($tag->value[$key] * $ratio) < $limit) ||
                                                    $tag->value[$key] < $limit && ($tag->value[$key] * $ratio) > $limit)
                                                    $tag->value[$key] = $limit;
                                                else if($tag->value[$key] != $limit)
                                                    $tag->value[$key] *= $ratio;
                                            if(is_float($tag->value[$key]) && $tag->value[$key] != $limit)
                                                $tag->value[$key] = round($tag->value[$key], 2);
                                        }
                                    }
                                    //if value is not an array
                                    else
                                    {
                                        if(($limit > 0 && $tag->value < 0 ) || ($limit < 0 && $tag->value > 0))
                                            $limit *= -1;

                                        if(is_numeric($value)) //if the entry is numeric
                                            if(($tag->value > $limit && ($tag->value * $ratio) < $limit) ||
                                                $tag->value < $limit && ($tag->value * $ratio) > $limit)
                                                $tag->value = $limit;
                                            else if($tag->value != $limit)
                                                $tag->value *= $ratio;
                                        if(is_float($tag->value) && $tag->value != $limit)
                                            $tag->value = round($tag->value, 2);
                                    }

                                else if($this->debugging && $amplify != 0)
                                    $GLOBALS['DebugTool']->push($amplify, 'there is an issue with this tag: bad amplify value', __METHOD__, __FILE__, __LINE__);
                            }

                            //if amplify is not a percentage
                            else if( (is_numeric($amplify) || $amplify === true ) && $amplify >= 0)
                            {
                                //if value is an array
                                if(is_array($tag->value))
                                {
                                    foreach($tag->value as $key => $value) // for each entry in value
                                        if(is_numeric($value)) //if the entry is numeric
                                        {
                                            if(($value > 0 && $limit < 0) || ($value < 0 && $limit > 0))
                                                $limit *= -1;

                                            if( $value > $limit && $value - $amplify > $limit)// check if it should stay negative and will.
                                                $tag->value[$key] -= $amplify;
                                            else if( $value < $limit && $value + $amplify < $limit)// check if it should stay positive and will
                                                $tag->value[$key] += $amplify;
                                            else//if it wont keep its plarity set it to zero.
                                                $tag->value[$key] = $limit;
                                        }
                                }
                                //if value is not an array
                                else
                                {
                                    if(($tag->value > 0 && $limit < 0) || ($tag->value < 0 && $limit > 0))
                                        $limit *= -1;

                                    if( $tag->value > $limit && $tag->value - $amplify > $limit) // check if it should stay negative and will.
                                        $tag->value -= $amplify;
                                    else if( $tag->value < $limit && $tag->value + $amplify < $limit) // check if it should stay positive and will.
                                        $tag->value += $amplify;
                                    else //if it wont keep its polarity set it to zero.
                                        $tag->value = $limit;
                                }
                            }
                            else if($this->debugging && $amplify != 0)
                                $GLOBALS['DebugTool']->push($amplify, 'there is an issue with this tag: bad amplify value', __METHOD__, __FILE__, __LINE__);

                            //setting the new value we generated.
                            $this->users[$user_key][self::TAGS][$tag_key]->value = $tag->value;
                        }
                    }

                }
            }
        }
    }

    //removeEquipmentById
    //removes all Tags originating from a piece of equipment
    public function removeEquipmentById($equipment_id)
    {
        //for each user...
        foreach($this->users as $user_key => $user)
        {
            //if this is the user who owns the equipment remove equipment data and update user stats
            if(isset($this->users[$user_key][self::EQUIPMENT][$equipment_id]))
            {
                $this->users[$user_key][self::ARMORBASE]      -= $this->users[$user_key][self::EQUIPMENT][$equipment_id][self::ARMORBASE];
                $this->users[$user_key][self::MASTERY]        -= $this->users[$user_key][self::EQUIPMENT][$equipment_id][self::MASTERY];
                $this->users[$user_key][self::STABILITY]      -= $this->users[$user_key][self::EQUIPMENT][$equipment_id][self::STABILITY];
                $this->users[$user_key][self::ACCURACY]       -= $this->users[$user_key][self::EQUIPMENT][$equipment_id][self::ACCURACY];
                $this->users[$user_key][self::EXPERTISE]      -= $this->users[$user_key][self::EQUIPMENT][$equipment_id][self::EXPERTISE];
                $this->users[$user_key][self::CHAKRAPOWER]    -= $this->users[$user_key][self::EQUIPMENT][$equipment_id][self::CHAKRAPOWER];
                $this->users[$user_key][self::CRITICALSTRIKE] -= $this->users[$user_key][self::EQUIPMENT][$equipment_id][self::CRITICALSTRIKE];

                $this->users[$user_key]['dr'] = $this->findDR($this->users[$user_key]);
                $this->users[$user_key]['sr'] = $this->findSR($this->users[$user_key]);
            }
            unset($this->users[$user_key][self::EQUIPMENT][$equipment_id]);
            $this->updateDR_SR($user_key);

            //for each tag...
            foreach($user[self::TAGS] as $tag_key => $tag)
            {
                //if equipment_ids match then...
                if($tag->equipment_id == $equipment_id && $tag->persistAfterDeath == false)
                {
                    //remove that tag.
                    unset($this->users[$user_key][self::TAGS][$tag_key]);

                    //if this tag is set in TAGSINEFFECT...
                    if(isset($this->users[$user_key][self::TAGSINEFFECT][$tag_key]))
                        //remove that tag.
                        unset($this->users[$user_key][self::TAGSINEFFECT][$tag_key]);

                }
            }
        }
    }

    //_____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____
    //---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---





    //---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---
    //***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** *****
    ////utility function

    //this function will randomize the order of an array while keeping the original keys intact.
    public function shake($array)
    {
        $shuffled_array = array();
        $shuffled_keys = array_keys($array);
        shuffle($shuffled_keys);
        foreach ( $shuffled_keys AS $shuffled_key )
        {
            $shuffled_array[  $shuffled_key  ] = $array[  $shuffled_key  ];
        }
        return $shuffled_array;
    }

    //this function will return true if that name supplied is that of a tag that does damage.
    //needs updated if more damaging tags are added.
    public function is_damaging_tag($tagName)
    {
        if( $tagName == 'damage' ||
            $tagName == 'damageOverTime' ||
            $tagName == 'oneHitKill')
        {
            return true;
        }
        else
            return false;
    }

    //this function will return true if the name supplied is that of a tag that is an action.
    public function is_action_tag($tagName)
    {
        if( $tagName == 'damage' ||
            $tagName == 'damageOverTime' ||
            $tagName == 'oneHitKill' ||
            $tagName == 'flee')
        {
            return true;
        }
        else
            return false;
    }

    //this function will be used to sort all tags before processing controlling their order.
    public function tagOrder($a, $b)
    {
        //fixing priority
        //if a priority is false
        if($a->priority === false)
            //if a is damaging set 2
            if($this->is_action_tag($a->name))
                $a->priority = 2;

            //else set to 999
            else
                $a->priority = 999;

        //converting priority type.
        if($a->priority === true)
            $a->priority = 1;

        //if b priority is false
        if($b->priority === false)
            //if b is damaging set 2
            if($this->is_action_tag($b->name))
                $b->priority = 2;

            //else set to 999
            else
                $b->priority = 999;

        //converting priority type.
        if($b->priority === true)
            $b->priority = 1;

        //sorting by priority
        //if a is higher than b then return -1
        if($a->priority > $b->priority)
            return -1;

        //if b is higher than a then return 1
        else if($b->priority > $a->priority)
            return 1;

        //else
        else

            //sorting by damaging
            //if a is non damaging and b is return -1
            if(!$this->is_damaging_tag($a->name) && $this->is_damaging_tag($b->name))
                return -1;

            //if b is non damaging and a is return 1
            else if(!$this->is_damaging_tag($b->name) && $this->is_damaging_tag($a->name))
                return 1;

            //else
            else
            {
                //set origin rank lower goes first
                $origin = array(self::BLOODLINE=>0, self::ARMOR=>1, self::LOCATION=>2, self::DEFAULT_ATTACK=>3, self::JUTSU=>4, self::WEAPON=>5, self::ITEM=>6);

                //sorting by origin
                //if a is lower in origin rank than b is return -1
                if($origin[$a->origin] < $origin[$b->origin])
                    return -1;

                //if b is lower in origin rank than a is return 1
                else if($origin[$b->origin] < $origin[$a->origin])
                    return 1;

                //else if priority is not 999 and not 9999
                else if($a->priority != 999 && $a->priority != 9999)
                    //sorting by turn order
                    //if a is lower in turn order than b is return -1
                    if($a->turn_order < $b->turn_order)
                        return -1;

                    //if b is lower in turn order rhan b is return 1
                    else if($b->turn_order < $b->turn_order)
                        return 1;

                    //this is impossible, but if turn_order is the same
                    else
                        return 0;

                //else
                else
                    //give up
                    return 0;
            }

    }

    //this function applys the value of a tag to its target data
    //calls addressTOEM for the final addressing
    //this function does nearly all of the work for the effect tags
    //along with addressTOEM
    //takes the name of the user that the tag is targeting
    //takes the name of the data that the tag is targeting(armor, chakra)
    //takes the value field of the tag
    //takes the effect level of the tag
    function applyEffectValue($target, $target_data, $TOEM, $value, $effect_level, $owner = false, $age = false)//$base, $base_type = self::FLAT, $increment = false, $increment_type = self::FLAT, $effect_level = false)//
    {
        //checking target data for data type issue
        if( is_array($target_data))
            $target_data = $target_data[0];

        //pares the value passed in
        $parsed_value = $this->parseValue($value,$effect_level,'from apply effect value :(');//

        //if parsing failed either display a debugging message or do nothing
        if($parsed_value[self::FLATBOOST] === false && $parsed_value[self::PRINCIPALPERCENTAGE] === false && $parsed_value[self::BOOSTPERCENTAGE] === false)
        {
            if($this->debugging)
                $GLOBALS['DebugTool']->push($value, 'bad value type: ', __METHOD__, __FILE__, __LINE__);
        }

        //if value is good...
        else
        {
            //for each sub value in parsed value
            $message = '';
            foreach($parsed_value as $value_type => $value)
            {
                $temp_TOEM = $TOEM;
                $temp_TOEM[] = array($value_type);

                //call addressTOEM
                $this->addressTOEM($target, $target_data, $temp_TOEM, 'add', $value);

                //recording effect
                if($owner !== false)
                {
                    if($value > 0 )
                        if($message == '' || $message == '+')
                            $message = '+';
                        else
                            $message = '?';
                    else if($value < 0)
                        if($message == '' || $message == '-')
                            $message = '-';
                        else
                            $message = '?';
                    else if($value === false)
                        $message = $message;
                    else
                        $message .= '?';
                }
            }
            $this->addEffectToBattleLog($owner, $target, $age, 'effect_'.$target_data, $message);
        }
    }


    //this method takes a username, target_data(armor, chakra), $TOEM, and $value
    //it then uses these to set the data correctly according to TOEM targeting.
    //TOEM == type, origin, element, value
    //value is not passed in this method takes care of value.
    public function addressTOEM($target_user, $target_data, $TOEM , $action, $value = false)
    {
        if($action == 'add' || $action == 'subtract' || $action == 'get' || $action == 'set') // check action
        {

            $TOEM_depth = count($TOEM); //check TOEM depth

            $return = array();

            //if target_data is not an array make it so
            if(!is_array($target_data))
                $target_data = array($target_data);

            //if TOEM depth is 1
            if($TOEM_depth == 1)
            {
                foreach($target_data as $d)//for each target data
                    foreach($TOEM[0] as $T)//depth 1

                        if($action == 'add')//if adding
                            if(isset($this->data[$target_user][$d.$T]))//if location is set
                                $this->data[$target_user][$d.$T] += $value;
                            else//if location is not set
                                $this->data[$target_user][$d.$T] = $value;

                        else if($action == 'subtract')//if subtracting
                            if(isset($this->data[$target_user][$d.$T]))//if location is set
                                $this->data[$target_user][$d.$T] -= $value;
                            else//if location is not set
                                $this->data[$target_user][$d.$T] = 0 - $value;

                        else if($action == 'get')// if getting
                            if(isset($this->data[$target_user][$d.$T]))//if location is set
                                $return[$d.$T] = $this->data[$target_user][$d.$T];
                            else//if location is not set
                                $return[$d.$T] = false;

                        else if($action == 'set')// if setting
                            $this->data[$target_user][$d.$T] = $value;
            }

            //if TOEM depth is 2
            else if($TOEM_depth == 2)
            {
                foreach($target_data as $d)//for each target data
                    foreach($TOEM[0] as $T)//depth 1
                        foreach($TOEM[1] as $O)//depth 2

                            if($action == 'add')//if adding
                                if(isset($this->data[$target_user][$d.$T.$O]))//if location is set
                                    $this->data[$target_user][$d.$T.$O] += $value;
                                else//if location is not set
                                    $this->data[$target_user][$d.$T.$O] = $value;

                            else if($action == 'subtract')//if subtracting
                                if(isset($this->data[$target_user][$d.$T.$O]))//if location is set
                                    $this->data[$target_user][$d.$T.$O] -= $value;
                                else//if location is not set
                                    $this->data[$target_user][$d.$T.$O] = 0 - $value;

                            else if($action == 'get')// if getting
                                if(isset($this->data[$target_user][$d.$T.$O]))//if location is set
                                    $return[$d.$T.$O] = $this->data[$target_user][$d.$T.$O];
                                else//if location is not set
                                    $return[$d.$T.$O] = false;

                            else if($action == 'set')// if setting
                                $this->data[$target_user][$d.$T.$O] = $value;
            }

            //TOEM depth is 3
            else if($TOEM_depth == 3)
            {
                foreach($target_data as $d)//for each target data
                    foreach($TOEM[0] as $T)//depth 1
                        foreach($TOEM[1] as $O)//depth 2
                            foreach($TOEM[2] as $E)//depth 3

                                if($action == 'add')//if adding
                                    if(isset($this->data[$target_user][$d.$T.$O.$E]))//if location is set
                                        $this->data[$target_user][$d.$T.$O.$E] += $value;
                                    else//if location is not set
                                        $this->data[$target_user][$d.$T.$O.$E] = $value;

                                else if($action == 'subtract')//if subtracting
                                    if(isset($this->data[$target_user][$d.$T.$O.$E]))//if location is set
                                        $this->data[$target_user][$d.$T.$O.$E] -= $value;
                                    else//if location is not set
                                        $this->data[$target_user][$d.$T.$O.$E] = 0 - $value;

                                else if($action == 'get')// if getting
                                    if(isset($this->data[$target_user][$d.$T.$O.$E]))//if location is set
                                        $return[$d.$T.$O.$E] = $this->data[$target_user][$d.$T.$O.$E];
                                    else//if location is not set
                                        $return[$d.$T.$O.$E] = false;

                                else if($action == 'set')// if setting
                                    $this->data[$target_user][$d.$T.$O.$E] = $value;
            }

            //TOEM depth is 4
            else if($TOEM_depth == 4)
            {
                foreach($target_data as $d)//for each target data
                    foreach($TOEM[0] as $T)//depth 1
                        foreach($TOEM[1] as $O)//depth 2
                            foreach($TOEM[2] as $E)//depth 3
                                foreach($TOEM[3] as $M)//depth 4

                                    if($action == 'add')//if adding
                                        if(isset($this->data[$target_user][$d.$T.$O.$E.$M]))//if location is set
                                            $this->data[$target_user][$d.$T.$O.$E.$M] += $value;
                                        else//if location is not set
                                            $this->data[$target_user][$d.$T.$O.$E.$M] = $value;

                                    else if($action == 'subtract')//if subtracting
                                        if(isset($this->data[$target_user][$d.$T.$O.$E.$M]))//if location is set
                                            $this->data[$target_user][$d.$T.$O.$E.$M] -= $value;
                                        else//if location is not set
                                            $this->data[$target_user][$d.$T.$O.$E.$M] = 0 - $value;

                                    else if($action == 'get')// if getting
                                        if(isset($this->data[$target_user][$d.$T.$O.$E.$M]))//if location is set
                                            $return[$d.$T.$O.$E.$M] = $this->data[$target_user][$d.$T.$O.$E.$M];
                                        else//if location is not set
                                            $return[$d.$T.$O.$E.$M] = false;

                                    else if($action == 'set')// if setting
                                        $this->data[$target_user][$d.$T.$O.$E.$M] = $value;
            }
        }
        //debugging
        else if($this->debugging)
            $GLOBALS['DebugTool']->push($action, 'bad action', __METHOD__, __FILE__, __LINE__);

        //return for action get
        if($action == 'get')
            return $return;
    }


    //this function builds the TOEM targeting array that is taken by applyEffectValue or addressTOEM
    function parseTOEM($type = false, $origin = false, $element = false, $forGet = false)
    {
        $TOEM = array();
        $depth = -1;

        //if type is not a array
        if(!is_array($type))

            //if type is set to all or true set all type for T
            if($type == self::ALL || $type === true)
            {
                $TOEM[] = array(self::ALL);
                $depth++;//updating depth of TOEM array
            }

            //if type is false T is empty
            else if($type === false)
            {
                //do nothing leaving T out of TOEM
            }

            //if not all or true
            else
                //if type is in the accepted list of types
                if(in_array($type, $this->ALLTYPE))
                {
                    //set t to teyp
                    $TOEM[] = array($type);
                    $depth++;//updating depth of TOEM array//updating depth of TOEM array
                }

                //if not in the accepted list and debugging is active
                else if($this->debugging)
                {
                    //give message and set T to all types
                    if($type != 'X' && $type != '')
                        $GLOBALS['DebugTool']->push($this->ALLTYPE, 'bad targetType. cannot be: "'.$type.'" targetType was set to all. value must be: ', __METHOD__, __FILE__, __LINE__);

                    $TOEM[] = $this->ALLTYPE;
                    $depth++;//updating depth of TOEM array
                }

                //if not debugging
                else
                {
                    //just set T to all types
                    $TOEM[] = $this->ALLTYPE;
                    $depth++;//updating depth of TOEM array
                }

        //if type is an array
        else
        {
            //initialize location for T
            $TOEM[] = array();
            $depth++;//updating depth of TOEM array

            //for each posssible T as t
            foreach($type as $t)

                //if t is an accepted type
                if(in_array($t,$this->ALLTYPE))

                    //set t in T
                    $TOEM[$depth][] = $t;

                //if t is not an accpeted type and debugging is on
                else if($this->debugging)
                {
                    //send debugging message, set T to all types, and break out of loop
                    if($t != 'X')
                        $GLOBALS['DebugTool']->push($this->ALLTYPE, 'bad targetType. cannot be: "'.$t.'" targetType was set to all. value must be: ', __METHOD__, __FILE__, __LINE__);

                    $TOEM[$depth] = $this->ALLTYPE;
                    break;
                }

                //if debugging is not on
                else
                {
                    //set T to all types, and break out of loop
                    $TOEM[$depth] = $this->ALLTYPE;
                    break;
                }
        }


        ////////////////////////////////////////////////////////////////////

        //if origin is not a array
        if(!is_array($origin))

            //if origin is set to all or true set all type for O
            if($origin == self::ALL || $origin === true)
            {
                $TOEM[] = array(self::ALL);
                $depth++;//updating depth of TOEM array
            }

            //if origin is false O is empty
            else if($origin === false)
            {
                //do nothing at all leaving origing out of TOEM array
            }

            //if not all or true
            else
                //if origin is in the accepted list of origin
                if(in_array($origin, $this->ALLORIGIN))
                {
                    //set o to origin
                    $TOEM[] = array($origin);
                    $depth++;//updating depth of TOEM array
                }

                //if not in the accepted list and debugging is active
                else if($this->debugging)
                {
                    //give message and set O to all types
                    $GLOBALS['DebugTool']->push($this->ALLORIGIN, 'bad targetOrigin. cannot be: "'.$origin.'" targetOrigin was set to all. value must be: ', __METHOD__, __FILE__, __LINE__);
                    $TOEM[] = $this->ALLORIGIN;
                    $depth++;//updating depth of TOEM array
                }

                //if not debugging
                else
                {
                    //just set O to all types
                    $TOEM[] = $this->ALLORIGIN;
                    $depth++;//updating depth of TOEM array
                }

        //if origin is an array
        else
        {
            //initialize location for O
            $TOEM[] = array();
            $depth++;//updating depth of TOEM array

            //for each posssible O as o
            foreach($origin as $o)

                //if o is an accepted origin
                if(in_array($o,$this->ALLORIGIN))

                    //set o in O
                    $TOEM[$depth][] = $o;

                //if o is not an accpeted origin and debugging is on
                else if($this->debugging)
                {
                    //send debugging message, set O to all origins, and break out of loop
                    $GLOBALS['DebugTool']->push($this->ALLORIGIN, 'bad targetOrigin. cannot be: "'.$o.'" targetOrigin was set to all. value must be: ', __METHOD__, __FILE__, __LINE__);
                    $TOEM[$depth] = $this->ALLORIGIN;
                    break;
                }

                //if debugging is not on
                else
                {
                    //set O to all origin, and break out of loop
                    $TOEM[$depth] = $this->ALLORIGIN;
                    break;
                }
        }

        ////////////////////////////////////////////////////////////////////

        //if element is not a array
        if(!is_array($element))

            //if element is set to all or true set all type for E
            if($element == self::ALL || $element === true)
            {
                $TOEM[] = array(self::ALL);
                $depth++;//updating depth of TOEM array
            }

            //if element is false E is empty
            else if($element === false)
            {
                //do nothing at all leaving element out of TOEM array
            }

            //if not all or true
            else
                //if element is in the accepted list of element
                if(in_array($element, $this->ALLELEMENT))
                {
                    //set E to element
                    $TOEM[] = array($element);
                    $depth++;//updating depth of TOEM array
                }

                //if not in the accepted list and debugging is active
                else if($this->debugging)
                {
                    //give message and set E to all types
                    $GLOBALS['DebugTool']->push($this->ALLELEMENT, 'bad targetType. cannot be: "'.$element.'" targetElement was set to all. value must be: ', __METHOD__, __FILE__, __LINE__);
                    $TOEM[] = $this->ALLELEMENT;
                    $depth++;//updating depth of TOEM array
                }

                //if not debugging
                else
                {
                    //just set E to all types
                    $TOEM[] = $this->ALLELEMENT;
                    $depth++;//updating depth of TOEM array
                }

        //if element is an array
        else
        {
            //initialize location for E
            $TOEM[] = array();
            $depth++;//updating depth of TOEM array

            //for each posssible E as e
            foreach($element as $e)

                //if e is an accepted element
                if(in_array($e,$this->ALLELEMENT))

                    //set e in E
                    $TOEM[$depth][] = $e;

                //if e is not an accpeted type and debugging is on
                else if($this->debugging)
                {
                    //send debugging message, set E to all element, and break out of loop
                    if($e != "X" && $e != '')
                        $GLOBALS['DebugTool']->push($this->ALLELEMENT, 'bad targetType. cannot be: "'.$e.'" targetElement was set to all. value must be: ', __METHOD__, __FILE__, __LINE__);

                    $TOEM[$depth] = $this->ALLELEMENT;
                    break;
                }

                //if debugging is not on
                else
                {
                    //set E to all element, and break out of loop
                    $TOEM[$depth] = $this->ALLELEMENT;
                    break;
                }
        }

        if($forGet)
            foreach($TOEM as $key => $value)
                $TOEM[$key][] = self::ALL;

        return $TOEM;
    }



    //parse value takes the value of a tag and parses it out.
    //checking for true and false because when a tag is created in converts 1's and 0's into true and false.
    //i know that is weird, but it saves storage space in the cache.
    function parseValue($value, $effect_level, $tag)
    {
        if(!is_array($value))
            $value = array($value);

        //get array length
        $value_count = count($value);

        $return = array();

        //if the array is 4 long and all values are good
        if($value_count == 4 && (is_numeric($value[0]) || substr($value[0],-1) == '%' || $value[0] === true || $value[0] === false) && (is_numeric($value[2]) || $value[2] === true  || $value[2] === false) && in_array($value[1], $this->ALLVALUETYPE) && in_array($value[3], $this->ALLVALUETYPE))
        {
            //if the value types do not equal.
            if($value[1] != $value[3])
            {
                //set them
                $return[$value[1]] = $value[0];
                $return[$value[3]] = $value[2] * $effect_level;
            }
            //if value types are equal merge them.
            else
                $return[$value[1]] = $value[0] + ($value[2] * $effect_level);
        }

        //else if length is two and all values are good
        else if($value_count == 2 && (is_numeric($value[0]) || substr($value[0],-1) == '%' || $value[0] === true || $value[0] === false) && (is_numeric($value[1]) || $value[1] === true || $value[1] === false))
            $return[self::FLATBOOST] = $value[0] + ($value[1] * $effect_level);

        //else if length is two and all values are good
        else if($value_count == 2 && (is_numeric($value[0]) || substr($value[0],-1) == '%'  || $value[0] === true || $value[0] === false) && in_array($value[1], $this->ALLVALUETYPE))
            $return[$value[1]] = $value[0];

        //else if length is three and all values are good
        else if($value_count == 3 && (is_numeric($value[0]) || substr($value[0],-1) == '%'  || $value[0] === true || $value[0] === false) && (is_numeric($value[2])  || $value[0] === true  || $value[0] === false ) && in_array($value[1], $this->ALLVALUETYPE) )
        {
            //if the value types dont match
            if($value[1] != self::FLATBOOST)
            {
                //set them
                $return[$value[1]] = $value[0];
                $return[self::FLATBOOST] = $value[2] * $effect_level;
            }
            //if the value types match merge them.
            else
                $return[self::FLATBOOST] = $value[0] + ($value[2] * $effect_level);

        }

        //if length is one and the value is good.
        else if($value_count == 1 && (is_numeric($value[0]) || $value[0] === true || substr($value[0],-1) == '%'))
            $return[self::FLATBOOST] = $value[0];

        else if ( isset($tag->name) && $tag->name == 'rob')
            $return[self::FLATBOOST] = 1;

        else if ($this->debugging)
        {
            $GLOBALS['DebugTool']->push($value, 'this value is bad: ', __METHOD__, __FILE__, __LINE__);
            ob_start();
            var_dump($tag);
            var_dump(' value: ');
            var_dump($value);
            $result = ob_get_clean();
                
            error_log('dumping tag with bad value: '.$result);//
        }

        //if there was no FB value set it as false
        //makes processing the results of this function easier.
        if(!isset($return[self::FLATBOOST]))
            $return[self::FLATBOOST] = false;

        //if there was no PP value set it to false
        if(!isset($return[self::PRINCIPALPERCENTAGE]))
            $return[self::PRINCIPALPERCENTAGE] = false;

        //if ther was no BP value set it to false.
        if(!isset($return[self::BOOSTPERCENTAGE]))
            $return[self::BOOSTPERCENTAGE] = false;

        return $return;
    }


    //used after addressTOEM to get the final result of a effectSomething.
    function parseM($modifications, $principal)
    {
        //pull data out of modifications
        $FB = $PP = $BP = 0;
        foreach($modifications as $key => $M)
        {
            if(substr($key,-2) == self::FLATBOOST)
                $FB += $M;
            else if(substr($key,-2) == self::PRINCIPALPERCENTAGE)
                $PP += $M;
            else if(substr($key,-2) == self::BOOSTPERCENTAGE)
                $BP += $M;
        }

        //calculate damageOut
        return $FB +
               ($FB * ($BP/100)) +
               ($principal * ($PP / 100));
    }



    //this method is used to take the written string version of a tag and convert it into the class form.
    function parseTags($tag_groups)
    {
        //break tag groups apart
        if(is_string($tag_groups))
            $tag_groups = explode('|', preg_replace('/\s+/', '', $tag_groups));
        else
        {
            ob_start();
            var_dump($tag_groups);
            $result = ob_get_clean();
            error_log('isue with parse tags blowing up. called by: '.debug_backtrace()[1]['function'].' :: '.$result);
        }

        //place for all tags to be kept
        $tag_pool = array();

        //for each tag group
        foreach($tag_groups as $value)
        {
            //if the tag group is not empty
            if($value != '')
            {
                //break out universal fields
                $universalFields = explode('}',$value);

                //if no universal fields
                if(count($universalFields) == 1)
                {
                    //break apart individual tags
                    $temp = explode('~', $value);

                    //mark no universal fields
                    $universalFields = false;
                }
                else if(count($universalFields) == 2)
                {
                    //break apart individual tags
                    $temp = explode('~', $universalFields[1]);

                    //mark universal fields
                    $universalFields = ltrim($universalFields[0],'{');
                }

                //for each tag
                foreach($temp as $key => $tag)
                {
                    if($tag != '')
                    {
                        //break name away from fields
                        $temp_tag = explode(':', $tag);

                        if(isset($temp_tag[1]))
                            //pass all data to Tag constructor and save new Tag in tag pool
                            $tag_pool[] = new Tag($temp_tag[0], $temp_tag[1], $universalFields, true);
                        else
                            throw new Exception('looks like there is a missing ":" at the start of a tag. ');

                    }
                }
            }
        }

        //return the tag pool
        return $tag_pool;
    }

    //adds a discription to show what effects a user's action had
    function addEffectToBattleLog($owner, $target, $age, $effect, $message)
    {
        //if this is the first time this tag has ever ran
        if(true)//($age == 0)
        {
            //priming effects
            if(!isset($this->battle_log[$this->turn_counter][$owner]['effects']))
                $this->battle_log[$this->turn_counter][$owner]['effects'] = array();

            //priming target
            if(!isset($this->battle_log[$this->turn_counter][$owner]['effects'][$target]))
                $this->battle_log[$this->turn_counter][$owner]['effects'][$target] = array();

            //priming effect
            if(!isset($this->battle_log[$this->turn_counter][$owner]['effects'][$target][$effect]))
                $this->battle_log[$this->turn_counter][$owner]['effects'][$target][$effect] = array();

            //setting message
            $this->battle_log[$this->turn_counter][$owner]['effects'][$target][$effect][] = $message;
        }
    }

    //_____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____
    //---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---





    //---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---
    //***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** ***** *****


    //tag methods
    //these are all called by process tags and represent the effects tags should have.



    //\\//\\//\\stat tags//\\//\\//\\

    //effectArmor changes armor{V} (armorFB, armorPP, armorBP)
    //calls applyEffectValue
    //nearly all work is done by that method and applyTOEM
    function effectArmor($tag)
    {


        $this->applyEffectValue($tag->target,
                                self::ARMORBASE,
                                array(), //passing empty TOEM armor is always universal
                                $tag->value,
                                $tag->effect_level,
                                $tag->owner);
    }


    //effectMastery changes masteryTOEM
    //calls applyEffectValue
    //calls parseTOEM
    //all work is done by those methods and applyTOEM
    //sets masteryTOEM
    function effectMastery($tag)
    {
        $this->applyEffectValue($tag->target,
                                self::MASTERY,
                                $this->parseTOEM($this->getHighestTypeForEffectTag($tag), $tag->targetOrigin, $tag->targetElement),
                                $tag->value,
                                $tag->effect_level,
                                $tag->owner);
    }


    //effectStability changes stabilityTOEM
    //calls applyEffectValue
    //calls parseTOEM
    //all work is done by those methods and applyTOEM
    //sets stabilityTOEM
    function effectStability($tag)
    {
        $this->applyEffectValue($tag->target,
                                self::STABILITY,
                                $this->parseTOEM($this->getHighestTypeForEffectTag($tag), $tag->targetOrigin, $tag->targetElement),
                                $tag->value,
                                $tag->effect_level,
                                $tag->owner);
    }

    //effectAccuracy changes accuracyTOEM
    //calls applyEffectValue
    //calls parseTOEM
    //all work is done by those methods and applyTOEM
    //sets accuracyTOEM
    function effectAccuracy($tag)
    {
        $this->applyEffectValue($tag->target,
                                self::ACCURACY,
                                $this->parseTOEM($this->getHighestTypeForEffectTag($tag), $tag->targetOrigin, $tag->targetElement),
                                $tag->value,
                                $tag->effect_level,
                                $tag->owner);
    }


    //effectExpertise changes expertiseTOEM
    //calls applyEffectValue
    //calls parseTOEM
    //all work is done by those methods and applyTOEM
    //sets expertiseTOEM
    function effectExpertise($tag)
    {
        $this->applyEffectValue($tag->target,
                                self::EXPERTISE,
                                $this->parseTOEM($this->getHighestTypeForEffectTag($tag), $tag->targetOrigin, $tag->targetElement),
                                $tag->value,
                                $tag->effect_level,
                                $tag->owner);
    }


    //effectChakraPower changes chakraPowerTOEM
    //calls applyEffectValue
    //calls parseTOEM
    //all work is done by those methods and applyTOEM
    //sets chakraPowerTOEM
    function effectChakraPower($tag)
    {
        $this->applyEffectValue($tag->target,
                                self::CHAKRAPOWER,
                                $this->parseTOEM($this->getHighestTypeForEffectTag($tag), $tag->targetOrigin, $tag->targetElement),
                                $tag->value,
                                $tag->effect_level,
                                $tag->owner);
    }


    //effectCriticalStrike changes criticalStrikeTOEM
    //calls applyEffectValue
    //calls parseTOEM
    //all work is done by those methods and applyTOEM
    //sets criticalStrikeTOEM
    function effectCriticalStrike($tag)
    {
        $this->applyEffectValue($tag->target,
                                self::CRITICALSTRIKE,
                                $this->parseTOEM($this->getHighestTypeForEffectTag($tag), $tag->targetOrigin, $tag->targetElement),
                                $tag->value,
                                $tag->effect_level,
                                $tag->owner);
    }


    //effectOffense changes offenseTOEM
    //calls applyEffectValue
    //calls parseTOEM
    //all work is done by those methods and applyTOEM
    //sets offenseTOEM
    function effectOffense($tag)
    {
        $this->applyEffectValue($tag->target,
                                self::OFFENSE,
                                $this->parseTOEM($this->getHighestTypeForEffectTag($tag), $tag->targetOrigin, $tag->targetElement),
                                $tag->value,
                                $tag->effect_level,
                                $tag->owner);
    }

    //effectDefense changes defenseTOEM
    //calls applyEffectValue
    //calls parseTOEM
    //all work is done by those methods and applyTOEM
    //sets defenseTOEM
    function effectDefense($tag)
    {
        $this->applyEffectValue($tag->target,
                                self::DEFENSE,
                                $this->parseTOEM($this->getHighestTypeForEffectTag($tag), $tag->targetOrigin, $tag->targetElement),
                                $tag->value,
                                $tag->effect_level,
                                $tag->owner);
    }


    //effectGeneralStat
    //calls applyEffectValue
    //calls parseTOEM
    //all work is done by those methods and applyTOEM
    //sets {general}TOEM
    function effectGeneralStat($tag)
    {
        //check Generals
        $bad_general = false; //error flag

        //there is an array of generals
        if(is_array($tag->targetGeneral))
        {
            //foreach general
            foreach($tag->targetGeneral as $general)

                //check if valid
                if(!in_array($general, $this->ALLGENERALS))
                    $bad_general = true;
        }

        //if there is not an array of generals
        else
        {
            //check if valid
            if(!in_array($tag->targetGeneral, $this->ALLGENERALS))
                $bad_general = true;
        }

        //if there was a bad general
        if($bad_general)
        {
            //if debugging give message
            if($this->debugging)
                $GLOBALS['DebugTool']->push($tag->targetGeneral, 'in correct field targetGeneral in tag. effectGeneralStat not applied. targetGeneral cant be:', __METHOD__, __FILE__, __LINE__);
        }
        //if generals were good.
        else
            $this->applyEffectValue($tag->target,
                                    $tag->targetGeneral,
                                    array(), //passing empty TOEM generals are always universal
                                    $tag->value,
                                    $tag->effect_level,
                                    $tag->owner);
    }




    //copyOrigin
    //this method copies the targets tags in the given origin that are curently
    //active in the run_ready_array and gives them to the owner of the tag.
    //these tags are set in the run_ready_array, then in $this->user[username][TAGS] and [TAGSINEFFECT]
    //also this function removes all tags already processed this turn and resorts the array.
    //after this tag runs processTags will automaticaly restart its for loop and run with the new tags.
    function copyOrigin($the_copy_tag)
    {
        //making sure targetOrigin is in array format for the if statement below
        if(!is_array($the_copy_tag->targetOrigin) && $the_copy_tag->targetOrigin != self::ALL)
            $the_copy_tag->targetOrigin = array($the_copy_tag->targetOrigin);
        else if($the_copy_tag->targetOrigin == self::ALL)
            $the_copy_tag->targetOrigin = $this->ALLORIGIN;

        if(is_array($the_copy_tag) && in_array(self::ALL, $the_copy_tag))
            $the_copy_tag->targetOrigin = $this->ALLORIGIN;

        $copies = array(); //holds tags being copied
        $pastCopyTag = false; //marks when we should stop removing tags from run_ready_array

        $this->addEffectToBattleLog($the_copy_tag->owner, $the_copy_tag->target, $the_copy_tag->age, 'copied tags from', implode(', ', $the_copy_tag->targetOrigin));

        //sorting these tags to match order they they would be ran in.
        uasort($this->users[$the_copy_tag->target][self::TAGSINEFFECT], array($this, 'tagOrder'));

        //if overide remove all tags that match whats being copied
        if($the_copy_tag->override === true)
            foreach($this->users[$the_copy_tag->owner][self::TAGSINEFFECT] as $tag_key => $tag_being_deleted) //for each tag in effect
                if(in_array($tag_being_deleted->origin, $the_copy_tag->targetOrigin) && !in_array($tag_being_deleted->name, $this->noCopyTags) && ( //if origin is good
                    (!is_array($the_copy_tag->targetAge) && ($the_copy_tag->targetAge === false || $the_copy_tag->targetAge == $tag_being_deleted->age)) || //if ages match
                    ( is_array($the_copy_tag->targetAge) && ($tag_being_deleted->age >= $the_copy_tag->targetAge[0] && $tag_being_deleted <= $the_copy_tag->targetAge[1])))) //if age matches age range
                {
                    //remove this tag from tags from tagsineffect from run ready array
                }

        //for each tag in the system
        foreach($this->users[$the_copy_tag->target][self::TAGSINEFFECT] as $tag_key => $tag_being_copied)
        {
            //if this should be copied
            if(in_array($tag_being_copied->origin, $the_copy_tag->targetOrigin) && !in_array($tag_being_copied->name,$this->noCopyTags) && ( //checking origin of tag and if it is in the no copy list
                (!is_array($the_copy_tag->targetAge) && ($the_copy_tag->targetAge === false || $the_copy_tag->targetAge == $tag_being_copied->age)) || //checking if the tag is the target age
                (is_array($the_copy_tag->targetAge) && ($tag_being_copied->age >= $the_copy_tag->targetAge[0] && $tag_being_copied->age <= $the_copy_tag->targetAge[1])))) //checking if the tag is in the target age range
            {


                //make a copy of the tag
                $copied_tag = clone $tag_being_copied;

                //update the tags owner
                $copied_tag->target = $the_copy_tag->owner;

                //udpate the tags key
                $copied_tag->key = $copied_tag->target.'COPY:'.$the_copy_tag->target.$tag_key;

                //dont know why but tags copied are picking up an extra age so reducing by one
                $copied_tag->age -= 1;

                //add this tag to the user.
                $this->run_ready_array[$copied_tag->key] = $copied_tag;
                $this->users[$copied_tag->target][self::TAGS]['COPY:'.$the_copy_tag->target.$tag_key] = $copied_tag;
                $this->users[$copied_tag->target][self::TAGSINEFFECT]['COPY:'.$the_copy_tag->target.$tag_key] = $copied_tag;
            }

            //if this tag should be removed because of override

            //if this tag should be removed because it comes before the current tag
            if($pastCopyTag === false)
            {
                //if we have not reached or are at the copy tag unset this tag.
                unset($this->run_ready_array[$tag_being_copied->target.$tag_key]);

                //if we are at the copy tag turn on past copy tag so that tags that
                //have not been proccessed yet will still be processed.
                if($tag_being_copied->target.$tag_key === $the_copy_tag->key)
                    $pastCopyTag = true;
            }
        }

    }

    //this method will copy/add/subtract the targets stats and apply them to the tag's owner
    //what is copied is set by
    //targetGeneral
    //targetType
    //targetPool
    //targetStat
    //target polarity sets what is done +,-,=
    //value sets what percentage to do these things by
    function copyStats($tag)
    {
        // 'health' && 'healthMax'
        if($tag->targetPool === true || $tag->targetPool === 'health' || (is_array($tag->targetPool) && in_array('health', $tag->targetPool)))
            if($tag->targetPolarity === '=' || $tag->targetPolarity === false)
            {
                $this->users[$tag->owner]['health'] = $this->users[$tag->target]['health'] * $tag->value;
                $this->users[$tag->owner]['healthMax'] = $this->users[$tag->target]['healthMax'] * $tag->value;
            }

            else if($tag->targetPolarity === '=/=')
            {
                $ratio = $this->users[$tag->owner]['health'] / $this->users[$tag->owner]['healthMax'];

                $this->users[$tag->owner]['healthMax'] = $this->users[$tag->target]['healthMax'] * $tag->value;
                $this->users[$tag->owner]['health'] = $this->users[$tag->owner]['healthMax'] * $ratio;
            }

            else if($tag->targetPolarity === '+')
            {
                $this->users[$tag->owner]['health'] += $this->users[$tag->target]['health'] * $tag->value;
                $this->users[$tag->owner]['healthMax'] += $this->users[$tag->target]['healthMax'] * $tag->value;
            }

            else if($tag->targetPolarity === '+/+')
            {
                $ratio = $this->users[$tag->owner]['health'] / $this->users[$tag->owner]['healthMax'];

                $this->users[$tag->owner]['healthMax'] += $this->users[$tag->target]['healthMax'] * $tag->value;
                $this->users[$tag->owner]['health'] += $this->users[$tag->owner]['healthMax'] * $ratio;
            }

            else if($tag->targetPolarity === '-')
            {
                $this->users[$tag->owner]['health'] -= $this->users[$tag->target]['health'] * $tag->value;
                $this->users[$tag->owner]['healthMax'] -= $this->users[$tag->target]['healthMax'] * $tag->value;
            }

            
            else if($tag->targetPolarity === '-/-')
            {
                $ratio = $this->users[$tag->owner]['health'] / $this->users[$tag->owner]['healthMax'];

                $this->users[$tag->owner]['healthMax'] -= $this->users[$tag->target]['healthMax'] * $tag->value;
                $this->users[$tag->owner]['health'] -= $this->users[$tag->owner]['healthMax'] * $ratio;
            }

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);
                        

        // 'stamina' && 'staminaMax'
        if(($tag->targetPool === true || $tag->targetPool === 'stamina' || (is_array($tag->targetPool) && in_array('stamina', $tag->targetPool))) && isset($this->users[$tag->owner]['stamina']) && isset($this->users[$tag->owner]['staminaMax']))
            if($tag->targetPolarity === '=' || $tag->targetPolarity === false)
            {
                $this->users[$tag->owner]['stamina'] = $this->users[$tag->target]['stamina'] * $tag->value;
                $this->users[$tag->owner]['staminaMax'] = $this->users[$tag->target]['staminaMax'] * $tag->value;
            }

            else if($tag->targetPolarity === '=/=')
            {
                $ratio = $this->users[$tag->owner]['stamina'] / $this->users[$tag->owner]['staminaMax'];

                $this->users[$tag->owner]['staminaMax'] = $this->users[$tag->target]['staminaMax'] * $tag->value;
                $this->users[$tag->owner]['stamina'] = $this->users[$tag->owner]['staminaMax'] * $ratio;
            }

            else if($tag->targetPolarity === '+')
            {
                $this->users[$tag->owner]['stamina'] += $this->users[$tag->target]['stamina'] * $tag->value;
                $this->users[$tag->owner]['staminaMax'] += $this->users[$tag->target]['staminaMax'] * $tag->value;
            }

            else if($tag->targetPolarity === '+/+')
            {
                $ratio = $this->users[$tag->owner]['stamina'] / $this->users[$tag->owner]['staminaMax'];

                $this->users[$tag->owner]['staminaMax'] += $this->users[$tag->target]['staminaMax'] * $tag->value;
                $this->users[$tag->owner]['stamina'] += $this->users[$tag->owner]['staminaMax'] * $ratio;
            }

            else if($tag->targetPolarity === '-')
            {
                $this->users[$tag->owner]['stamina'] -= $this->users[$tag->target]['stamina'] * $tag->value;
                $this->users[$tag->owner]['staminaMax'] -= $this->users[$tag->target]['staminaMax'] * $tag->value;
            }

            else if($tag->targetPolarity === '-/-')
            {
                $ratio = $this->users[$tag->owner]['stamina'] / $this->users[$tag->owner]['staminaMax'];

                $this->users[$tag->owner]['staminaMax'] -= $this->users[$tag->target]['staminaMax'] * $tag->value;
                $this->users[$tag->owner]['stamina'] -= $this->users[$tag->owner]['staminaMax'] * $ratio;
            }

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);

        // 'chakra' && 'chakraMax'
        if(($tag->targetPool === true || $tag->targetPool === 'chakra' || (is_array($tag->targetPool) && in_array('chakra', $tag->targetPool))) && isset($this->users[$tag->owner]['stamina']) && isset($this->users[$tag->owner]['staminaMax']))
            if($tag->targetPolarity === '=' || $tag->targetPolarity === false)
            {
                $this->users[$tag->owner]['chakra'] = $this->users[$tag->target]['chakra'] * $tag->value;
                $this->users[$tag->owner]['chakraMax'] = $this->users[$tag->target]['chakraMax'] * $tag->value;
            }

            else if($tag->targetPolarity === '=/=')
            {
                $ratio = $this->users[$tag->owner]['chakra'] / $this->users[$tag->owner]['chakraMax'];

                $this->users[$tag->owner]['chakraMax'] = $this->users[$tag->target]['chakraMax'] * $tag->value;
                $this->users[$tag->owner]['chakra'] = $this->users[$tag->owner]['chakraMax'] * $ratio;
            }

            else if($tag->targetPolarity === '+')
            {
                $this->users[$tag->owner]['chakra'] += $this->users[$tag->target]['chakra'] * $tag->value;
                $this->users[$tag->owner]['chakraMax'] += $this->users[$tag->target]['chakraMax'] * $tag->value;
            }

            else if($tag->targetPolarity === '+/+')
            {
                $ratio = $this->users[$tag->owner]['chakra'] / $this->users[$tag->owner]['chakraMax'];

                $this->users[$tag->owner]['chakraMax'] += $this->users[$tag->target]['chakraMax'] * $tag->value;
                $this->users[$tag->owner]['chakra'] += $this->users[$tag->owner]['chakraMax'] * $ratio;
            }

            else if($tag->targetPolarity === '-')
            {
                $this->users[$tag->owner]['chakra'] -= $this->users[$tag->target]['chakra'] * $tag->value;
                $this->users[$tag->owner]['chakraMax'] -= $this->users[$tag->target]['chakraMax'] * $tag->value;
            }

            else if($tag->targetPolarity === '-/-')
            {
                $ratio = $this->users[$tag->owner]['chakra'] / $this->users[$tag->owner]['chakraMax'];

                $this->users[$tag->owner]['chakraMax'] -= $this->users[$tag->target]['chakraMax'] * $tag->value;
                $this->users[$tag->owner]['chakra'] -= $this->users[$tag->owner]['chakraMax'] * $ratio;
            }

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);

        
        
        // 'strength'
        if($tag->targetGeneral === true || $tag->targetGeneral === 'strength' || (is_array($tag->targetGeneral) && in_array('strength', $tag->targetGeneral)))
            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['strength'] = $this->users[$tag->target]['strength'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['strength'] += $this->users[$tag->target]['strength'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['strength'] -= $this->users[$tag->target]['strength'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);

        // 'willpower'
        if($tag->targetGeneral === true || $tag->targetGeneral === 'willpower' || (is_array($tag->targetGeneral) && in_array('willpower', $tag->targetGeneral)))
            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['willpower'] = $this->users[$tag->target]['willpower'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['willpower'] += $this->users[$tag->target]['willpower'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['willpower'] -= $this->users[$tag->target]['willpower'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);

        // 'intelligence'
        if($tag->targetGeneral === true || $tag->targetGeneral === 'intelligence' || (is_array($tag->targetGeneral) && in_array('intelligence', $tag->targetGeneral)))
            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['intelligence'] = $this->users[$tag->target]['intelligence'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['intelligence'] += $this->users[$tag->target]['intelligence'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['intelligence'] -= $this->users[$tag->target]['intelligence'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);

        // 'speed'
        if($tag->targetGeneral === true || $tag->targetGeneral === 'speed' || (is_array($tag->targetGeneral) && in_array('speed', $tag->targetGeneral)))
            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['speed'] = $this->users[$tag->target]['speed'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['speed'] += $this->users[$tag->target]['speed'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['speed'] -= $this->users[$tag->target]['speed'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);

        
        
        // 'offenseT'
        if($tag->targetType === true || $tag->targetType === 'offenseTaijutsu' || (is_array($tag->targetType) && in_array('offenseTaijutsu', $tag->targetType)))

            //$this->users[$tag->owner][self::SPECIALIZATION] = $this->users[$tag->target][self::SPECIALIZATION];

            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['offenseT'] = $this->users[$tag->target]['offenseT'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['offenseT'] += $this->users[$tag->target]['offenseT'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['offenseT'] -= $this->users[$tag->target]['offenseT'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);

        // 'offenseN'
        if($tag->targetType === true || $tag->targetType === 'offenseNinjutsu' || (is_array($tag->targetType) && in_array('offenseNinjutsu', $tag->targetType)))
            
            //$this->users[$tag->owner][self::SPECIALIZATION] = $this->users[$tag->target][self::SPECIALIZATION];

            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['offenseN'] = $this->users[$tag->target]['offenseN'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['offenseN'] += $this->users[$tag->target]['offenseN'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['offenseN'] -= $this->users[$tag->target]['offenseN'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);

        // 'offenseG'
        if($tag->targetType === true || $tag->targetType === 'offenseGenjutsu' || (is_array($tag->targetType) && in_array('offenseGenjutsu', $tag->targetType)))

            //$this->users[$tag->owner][self::SPECIALIZATION] = $this->users[$tag->target][self::SPECIALIZATION];

            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['offenseG'] = $this->users[$tag->target]['offenseG'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['offenseG'] += $this->users[$tag->target]['offenseG'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['offenseG'] -= $this->users[$tag->target]['offenseG'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);

        // 'offenseB'
        if($tag->targetType === true || $tag->targetType === 'offenseBukijutsu' || (is_array($tag->targetType) && in_array('offenseBukijutsu', $tag->targetType)))

            //$this->users[$tag->owner][self::SPECIALIZATION] = $this->users[$tag->target][self::SPECIALIZATION];

            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['offenseB'] = $this->users[$tag->target]['offenseB'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['offenseB'] += $this->users[$tag->target]['offenseB'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['offenseB'] -= $this->users[$tag->target]['offenseB'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);

        // 'defenseT'
        if($tag->targetType === true || $tag->targetType === 'defenseTaijutsu' || (is_array($tag->targetType) && in_array('defenseTaijutsu', $tag->targetType)))
            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['defenseT'] = $this->users[$tag->target]['defenseT'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['defenseT'] += $this->users[$tag->target]['defenseT'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['defenseT'] -= $this->users[$tag->target]['defenseT'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);

        // 'defenseN'
        if($tag->targetType === true || $tag->targetType === 'defenseNinjutsu' || (is_array($tag->targetType) && in_array('defenseNinjutsu', $tag->targetType)))
            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['defenseN'] = $this->users[$tag->target]['defenseN'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['defenseN'] += $this->users[$tag->target]['defenseN'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['defenseN'] -= $this->users[$tag->target]['defenseN'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);

        // 'defenseG'
        if($tag->targetType === true || $tag->targetType === 'defenseGenjutsu' || (is_array($tag->targetType) && in_array('defenseGenjutsu', $tag->targetType)))
            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['defenseG'] = $this->users[$tag->target]['defenseG'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['defenseG'] += $this->users[$tag->target]['defenseG'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['defenseG'] -= $this->users[$tag->target]['defenseG'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);

        // 'defenseB'
        if($tag->targetType === true || $tag->targetType === 'defenseBukijutsu' || (is_array($tag->targetType) && in_array('defenseBukijutsu', $tag->targetType)))
            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['defenseB'] = $this->users[$tag->target]['defenseB'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['defenseB'] += $this->users[$tag->target]['defenseB'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['defenseB'] -= $this->users[$tag->target]['defenseB'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);

        
        // 'armor'
        if($tag->targetStat === true || $tag->targetStat === 'armor' || (is_array($tag->targetStat) && in_array('armor', $tag->targetStat)))
        {
            $this->users[$tag->owner]['rank'] = $this->users[$tag->target]['rank'];

            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['armor'] = $this->users[$tag->target]['armor'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['armor'] += $this->users[$tag->target]['armor'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['armor'] -= $this->users[$tag->target]['armor'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);
        }

        // 'mastery'
        if($tag->targetStat === true || $tag->targetStat === 'mastery' || (is_array($tag->targetStat) && in_array('mastery', $tag->targetStat)))
        {
            $this->users[$tag->owner]['rank'] = $this->users[$tag->target]['rank'];

            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['mastery'] = $this->users[$tag->target]['mastery'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['mastery'] += $this->users[$tag->target]['mastery'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['mastery'] -= $this->users[$tag->target]['mastery'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);
        }

        // 'stability'
        if($tag->targetStat === true || $tag->targetStat === 'stability' || (is_array($tag->targetStat) && in_array('stability', $tag->targetStat)))
        {
            $this->users[$tag->owner]['rank'] = $this->users[$tag->target]['rank'];
            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['stability'] = $this->users[$tag->target]['stability'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['stability'] += $this->users[$tag->target]['stability'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['stability'] -= $this->users[$tag->target]['stability'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);
        }

        // 'accuracy'
        if($tag->targetStat === true || $tag->targetStat === 'accuracy' || (is_array($tag->targetStat) && in_array('accuracy', $tag->targetStat)))
        {
            $this->users[$tag->owner]['rank'] = $this->users[$tag->target]['rank'];

            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['accuracy'] = $this->users[$tag->target]['accuracy'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['accuracy'] += $this->users[$tag->target]['accuracy'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['accuracy'] -= $this->users[$tag->target]['accuracy'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);
        }

        // 'expertise'
        if($tag->targetStat === true || $tag->targetStat === 'expertise' || (is_array($tag->targetStat) && in_array('expertise', $tag->targetStat)))
        {
            $this->users[$tag->owner]['rank'] = $this->users[$tag->target]['rank'];
 
            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['expertise'] = $this->users[$tag->target]['expertise'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['expertise'] += $this->users[$tag->target]['expertise'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['expertise'] -= $this->users[$tag->target]['expertise'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);
        }

        // 'chakraPower'
        if($tag->targetStat === true || $tag->targetStat === 'chakraPower' || (is_array($tag->targetStat) && in_array('chakraPower', $tag->targetStat)))
        {
            $this->users[$tag->owner]['rank'] = $this->users[$tag->target]['rank'];

            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['chakraPower'] = $this->users[$tag->target]['chakraPower'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['chakraPower'] += $this->users[$tag->target]['chakraPower'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['chakraPower'] -= $this->users[$tag->target]['chakraPower'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);
        }

        // 'criticalStrike'
        if($tag->targetStat === true || $tag->targetStat === 'criticalStrike' || (is_array($tag->targetStat) && in_array('criticalStrike', $tag->targetStat)))
        {
            $this->users[$tag->owner]['rank'] = $this->users[$tag->target]['rank'];

            if($tag->targetPolarity === '=' || $tag->targetPolarity === '=/=' || $tag->targetPolarity === false)
                $this->users[$tag->owner]['criticalStrike'] = $this->users[$tag->target]['criticalStrike'] * $tag->value;

            else if($tag->targetPolarity === '+' || $tag->targetPolarity === '+/+')
                $this->users[$tag->owner]['criticalStrike'] += $this->users[$tag->target]['criticalStrike'] * $tag->value;

            else if($tag->targetPolarity === '-' || $tag->targetPolarity === '-/-')
                $this->users[$tag->owner]['criticalStrike'] -= $this->users[$tag->target]['criticalStrike'] * $tag->value;

            else
                $GLOBALS['DebugTool']->push($tag->targetPolarity, 'there is an issue with targetPolarity', __METHOD__, __FILE__, __LINE__);
        }

    }


    //copyOrigin
    //this method copies the targets tags in the given origin that are curently
    //active in the run_ready_array and gives them to the owner of the tag.
    //these tags are set in the run_ready_array, then in $this->user[username][TAGS] and [TAGSINEFFECT]
    //also this function removes all tags already processed this turn and resorts the array.
    //after this tag runs processTags will automaticaly restart its for loop and run with the new tags.
    function mirrorOrigin($the_mirror_tag)
    {
        //making sure targetOrigin is in array format for the if statement below
        if(!is_array($the_mirror_tag->targetOrigin) && $the_mirror_tag->targetOrigin != self::ALL)
            $the_mirror_tag->targetOrigin = array($the_mirror_tag->targetOrigin);
        else if($the_mirror_tag->targetOrigin == self::ALL)
            $the_mirror_tag->targetOrigin = $this->ALLORIGIN;

        if(is_array($the_mirror_tag) && in_array(self::ALL, $the_mirror_tag))
            $the_mirror_tag->targetOrigin = $this->ALLORIGIN;

        $this->addEffectToBattleLog($the_mirror_tag->owner, $the_mirror_tag->target, $the_mirror_tag->age, 'mirrored tags from', implode(', ', $the_mirror_tag->targetOrigin));

        //changing priority of this tag to 9999 so that it will always go first.
        $this->users[$the_mirror_tag->target][self::TAGS][str_replace($the_mirror_tag->target, "", $the_mirror_tag->key)]->priority = 9999;

        $pastMirrorTag = false; //marks when we should stop removing tags from run_ready_array

        //for each tag in the system
        foreach($this->run_ready_array as $tag_key => $tag_being_mirrored)
        {
            //if this should be copied
            if( $pastMirrorTag === true && $tag_being_mirrored->target === $the_mirror_tag->target && in_array($tag_being_mirrored->origin, $the_mirror_tag->targetOrigin) && !in_array($tag_being_mirrored->name,$this->noCopyTags) && ( // checking origin and no copy list
                (!is_array($the_mirror_tag->targetAge) && ($the_mirror_tag->targetAge === false || $the_mirror_tag->targetAge == $tag_being_mirrored->age)) || //checking to see if age matches
                is_array($the_mirror_tag->targetAge) && ($tag_being_mirrored->age >= $the_mirror_tag->targetAge[0] && $tag_being_mirrored->age <= $the_mirror_tag->targetAge[1]))) //checking to see if age matches age range
            {
                //make a copy of the tag
                $copied_tag = clone $tag_being_mirrored;

                //update the tags owner
                $copied_tag->target = $the_mirror_tag->owner;

                //udpate the tags key
                $copied_tag->key = $copied_tag->target.'COPY:'.$tag_key;

                //add this tag to the user.
                $this->run_ready_array[$copied_tag->key] = $copied_tag;
            }

            //if this tag should be removed because it came before this tag
            if($pastMirrorTag === false)
            {
                //if we have not reached or are at the copy tag unset this tag.
                unset($this->run_ready_array[$tag_key]);

                //if we are at the copy tag turn on past copy tag so that tags that
                //have not been proccessed yet will still be processed.
                if($tag_being_mirrored->key === $the_mirror_tag->key)
                    $pastMirrorTag = true;
            }
        }

    }







    //\\//\\//\\ pool tags //\\//\\//\\



    //effectChakra
    //calls applyEffectValue
    //all work is done by those methods and applyTOEM
    //sets chakraFB chakraPP charaBP
    function effectChakra($tag)
    {
        $chakraM = $this->parseValue($tag->value, $tag->effect_level, $tag);

        $chakra_value = $chakraM[self::FLATBOOST] +
                      ($chakraM[self::FLATBOOST] * ($chakraM[self::BOOSTPERCENTAGE] / 100)) +
                      ($this->users[$tag->target][self::CHAKRAMAX] * ($chakraM[self::PRINCIPALPERCENTAGE] / 100));

        $this->users[$tag->target][self::CHAKRA] += $chakra_value;

        $message = '';
        if($chakra_value > 0)
            $message = '+';
        else if($chakra_value < 0)
            $message = '-';
        else
            $message = '+/-';

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'effect_chakra', $message);
    }



    //effectStamina
    //calls applyEffectValue
    //all work is done by those methods and applyTOEM
    //sets staminaFB staminaPP staminaBP
    function effectStamina($tag)
    {
        $staminaM = $this->parseValue($tag->value, $tag->effect_level, $tag);

        $stamina_value = $staminaM[self::FLATBOOST] +
                      ($staminaM[self::FLATBOOST] * ($staminaM[self::BOOSTPERCENTAGE] / 100)) +
                      ($this->users[$tag->target][self::STAMINAMAX] * ($staminaM[self::PRINCIPALPERCENTAGE] / 100));

        $this->users[$tag->target][self::STAMINA] += $stamina_value;

        $message = '';
        if($chakra_value > 0)
            $message = '+';
        else if($chakra_value < 0)
            $message = '-';
        else
            $message = '+/-';

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'effect_chakra', $message);
    }


    //effectHealth
    //calls applyEffectValue
    //all work is done by those methods and applyTOEM
    //sets healthFB healthPP healthBP
    function effectHealth($tag)
    {
        //get heal M's
        $healthM = $this->parseValue($tag->value, $tag->effect_level, $tag);

        //calc heal
        $health_value = $healthM[self::FLATBOOST] +
                      ($healthM[self::FLATBOOST] * ($healthM[self::BOOSTPERCENTAGE] / 100)) +
                      ($this->users[$tag->target][self::HEALTHMAX] * ($healthM[self::PRINCIPALPERCENTAGE] / 100));

        $this->users[$tag->target][self::HEALTH] += $health_value;

        $message = '';
        if($chakra_value > 0)
            $message = '+';
        else if($chakra_value < 0)
            $message = '-';
        else
            $message = '+/-';

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'effect_chakra', $message);
    }


    //effectChakraCost
    //calls applyEffectValue
    //all work is done by those methods and applyTOEM
    //sets chakraCostFB chakraCostPP charaCostBP
    function effectChakraCost($tag)
    {
        $this->applyEffectValue($tag->target,
                                self::CHAKRACOST,
                                array(),
                                $tag->value,
                                $tag->effect_level,
                                $tag->owner);
    }



    //effectStaminaCost
    //calls applyEffectValue
    //all work is done by those methods and applyTOEM
    //sets staminaCostFB staminaCostPP staminaCostBP
    function effectStaminaCost($tag)
    {
        $this->applyEffectValue($tag->target,
                                self::STAMINACOST,
                                array(),
                                $tag->value,
                                $tag->effect_level,
                                $tag->owner);
    }


    //effectHealthCost
    //calls applyEffectValue
    //all work is done by those methods and applyTOEM
    //sets healthCostFB healthCostPP healthCostBP
    function effectHealthCost($tag)
    {
        $this->applyEffectValue($tag->target,
                                self::HEALTHCOST,
                                array(),
                                $tag->value,
                                $tag->effect_level,
                                $tag->owner);
    }




    //\\//\\//\\ damage related tags //\\//\\//\\


    //damage deals damage to the target user
    //must have a duration of one
    //must have targetType(1) and targetGeneral(2) set
    //if statBased is set to true it will call doDamageStatBased
    //if statBased is set to false it will call doDamageFlat
    function damage($tag)
    {
        if($this->users[$tag->target]['health'] > 0)
        {
            if($tag->duration != 1)
            {
                $tag->duration = 1;

                if($this->debugging)
                    $GLOBALS['DebugTool']->push('', 'duration on damage must be 1', __METHOD__, __FILE__, __LINE__);
            }

            if(is_array($tag->targetType) && $tag->statBased)
            {
                if($this->debugging && count($tag->targetType) != 1)
                    $GLOBALS['DebugTool']->push('target type cant be array. set to first value.', 'bad data in damage tag', __METHOD__, __FILE__, __LINE__);

                $tag->targetType = $tag->targetType[0];
            }

            if(!is_array($tag->targetGeneral)  && $tag->statBased)
            {
                $tag->targetGeneral = array($tag->targetGeneral, $tag->targetGeneral);
                //if($this->debugging)
                //    $GLOBALS['DebugTool']->push('target general must be an array. converted to array.', 'bad data in damage tag', __METHOD__, __FILE__, __LINE__);
            }

            if(!is_array($tag->targetElement) && $tag->statBased)
            {
                $tag->targetElement = array($tag->targetElement);
            }

            if( ($tag->targetType === true || !in_array($tag->targetType, $this->ALLTYPE)) && $tag->statBased)
            {
                if($tag->targetType === 'highest')
                {
                    $specialization = explode(':',$this->users[$tag->owner][self::SPECIALIZATION]);

                    if(isset($specialization[1]) && $specialization[1] == 0)
                    {

                        $temp_types = array( self::NINJUTSU => $this->users[$tag->owner][self::OFFENSE.self::NINJUTSU],
                                             self::GENJUTSU => $this->users[$tag->owner][self::OFFENSE.self::GENJUTSU],
                                             self::TAIJUTSU => $this->users[$tag->owner][self::OFFENSE.self::TAIJUTSU],
                                             self::BUKIJUTSU => $this->users[$tag->owner][self::OFFENSE.self::BUKIJUTSU]);

                        $max = max($temp_types);
                        $options = array();
                        foreach($temp_types as $type => $value)
                        {
                            if($value == $max)
                                $options[] = $type;
                        }

                        $tag->targetType = $options[random_int(0,(count($options) - 1))];
                    }
                    else
                    {
                        if($specialization[0] == '0')
                        {
                            $temp_types = array( self::NINJUTSU => $this->users[$tag->owner][self::OFFENSE.self::NINJUTSU],
                                             self::GENJUTSU => $this->users[$tag->owner][self::OFFENSE.self::GENJUTSU],
                                             self::TAIJUTSU => $this->users[$tag->owner][self::OFFENSE.self::TAIJUTSU],
                                             self::BUKIJUTSU => $this->users[$tag->owner][self::OFFENSE.self::BUKIJUTSU]);

                            $max = max($temp_types);
                            $options = array();
                            foreach($temp_types as $type => $value)
                            {
                                if($value == $max)
                                    $options[] = $type;
                            }

                            $tag->targetType = $options[random_int(0,(count($options) - 1))];
                        }
                        else
                            if($specialization[0] == 'W')
                                $tag->targetType = 'B';
                            else
                                $tag->targetType = $specialization[0];
                    }
                }
                else
                {
                    if($this->debugging && $tag->targetType !== true)
                        $GLOBALS['DebugTool']->push($tag->targetType, 'target type not accepted. set to ninjutsu. bad data in damage tag', __METHOD__, __FILE__, __LINE__);
                    $tag->targetType = self::NINJUTSU;
                }
            }

            if(count($tag->targetGeneral) == 2 && $tag->statBased)
            {
                if( $tag->targetGeneral[0] === true || !in_array($tag->targetGeneral[0], $this->ALLGENERALS))
                {
                    $temp_generals = array( self::STRENGTH => $this->users[$tag->owner][self::STRENGTH],
                                            self::SPEED => $this->users[$tag->owner][self::SPEED],
                                            self::INTELLIGENCE => $this->users[$tag->owner][self::INTELLIGENCE],
                                            self::WILLPOWER => $this->users[$tag->owner][self::WILLPOWER],
                                            );

                    if($tag->targetGeneral[0] === 'highest')
                    {
                        $tag->targetGeneral[0] = array_search(max($temp_generals), $temp_generals);
                    }
                    else
                    {
                        if($this->debugging && $tag->targetGeneral[0] !== true)
                            $GLOBALS['DebugTool']->push($tag->targetGeneral[0], 'first target general not accepted. set to strength. bad data in damage tag', __METHOD__, __FILE__, __LINE__);
                        $tag->targetGeneral[0] = self::STRENGTH;
                    }
                }

                if( $tag->targetGeneral[1] === true || !in_array($tag->targetGeneral[1], $this->ALLGENERALS))
                {
                    if($tag->targetGeneral[1] === 'highest')
                    {
                        unset($temp_generals[$tag->targetGeneral[0]]);
                        $tag->targetGeneral[1] = array_search(max($temp_generals), $temp_generals);
                    }
                    else
                    {
                        if($this->debugging && $tag->targetGeneral[1] !== true)
                            $GLOBALS['DebugTool']->push($tag->targetGeneral[1], 'second target general not accepted. set to strength. bad data in damage tag', __METHOD__, __FILE__, __LINE__);
                        $tag->targetGeneral[1] = self::INTELLIGENCE;
                    }
                }
            }
            else if($tag->statBased)
            {
                $tag->targetGeneral = array(self::STRENGTH, self::STRENGTH);
                if($this->debugging)
                    $GLOBALS['DebugTool']->push($tag->targetGeneral, 'target general must have 2 items. bad data in damage tag.', __METHOD__, __FILE__, __LINE__);
            }

            if($tag->statBased)
                foreach($tag->targetElement as $element_key => $element)
                {
                    if( !in_array($element, $this->ALLELEMENT))
                    {
                        $tag->targetElement[$element_key] = self::NONE;
                        if($this->debugging)
                        {
                            $temp_array = $this->ALLELEMENT;
                            $temp_array['vs'] = $element;
                            $GLOBALS['DebugTool']->push($temp_array, 'bad element data found in damage tag. was set to none.', __METHOD__, __FILE__, __LINE__);
                        }
                    }
                    else if ( $element === true )
                    {
                        $tag->targetElement[$element_key] = self::NONE;
                    }
                }

            $this->doDamage($tag,false);
        }
    }


    //damageOverTime deals damage to the target user
    //must have a duration of 2 or more
    //must have targetType(1) and targetGeneral(2) set
    //if statBased is set to true it will call doDamageStatBased
    //if statbased is set to false it will call doDamageFlat
    function damageOverTime($tag)
    {
        if($this->users[$tag->target]['health'] > 0)
        {
            if(($tag->duration < 2 && $tag->duration != 0) || $tag->duration === true)
            {
                $tag->duration = 2;

                if($this->debugging)
                    $GLOBALS['DebugTool']->push('', 'duration on damageOverTime must be atleast 2', __METHOD__, __FILE__, __LINE__);
            }

            if(is_array($tag->targetType) && $tag->statBased)
            {
                if($this->debugging && count($tag->targetType) != 1)
                    $GLOBALS['DebugTool']->push('target type cant be array. set to first value.', 'bad data in damage over time tag', __METHOD__, __FILE__, __LINE__);
                    
                $tag->targetType = $tag->targetType[0];
            }

            if(!is_array($tag->targetGeneral) && $tag->statBased)
            {
                $tag->targetGeneral = array($tag->targetGeneral, $tag->targetGeneral);
                if($this->debugging)
                    $GLOBALS['DebugTool']->push('target general must be an array. converted to array.', 'bad data in damage over time tag', __METHOD__, __FILE__, __LINE__);
            }

            if(!is_array($tag->targetElement) && $tag->statBased)
            {
                $tag->targetElement = array($tag->targetElement);
            }

            if( ($tag->targetType === true || !in_array($tag->targetType, $this->ALLTYPE)) && $tag->statBased)
            {
                if($tag->targetType === 'highest')
                {
                    $specialization = explode(':',$this->users[$tag->owner][self::SPECIALIZATION]);

                    if(isset($specialization[1]) && $specialization[1] == 0)
                    {

                        $temp_types = array( self::NINJUTSU => $this->users[$tag->owner][self::OFFENSE.self::NINJUTSU],
                                             self::GENJUTSU => $this->users[$tag->owner][self::OFFENSE.self::GENJUTSU],
                                             self::TAIJUTSU => $this->users[$tag->owner][self::OFFENSE.self::TAIJUTSU],
                                             self::BUKIJUTSU => $this->users[$tag->owner][self::OFFENSE.self::BUKIJUTSU]);

                        $max = max($temp_types);
                        $options = array();
                        foreach($temp_types as $type => $value)
                        {
                            if($value == $max)
                                $options[] = $type;
                        }

                        $tag->targetType = $options[random_int(0,(count($options) - 1))];
                    }
                    else
                    {
                        if($specialization[0] == '0')
                        {
                            $temp_types = array( self::NINJUTSU => $this->users[$tag->owner][self::OFFENSE.self::NINJUTSU],
                                             self::GENJUTSU => $this->users[$tag->owner][self::OFFENSE.self::GENJUTSU],
                                             self::TAIJUTSU => $this->users[$tag->owner][self::OFFENSE.self::TAIJUTSU],
                                             self::BUKIJUTSU => $this->users[$tag->owner][self::OFFENSE.self::BUKIJUTSU]);

                            $max = max($temp_types);
                            $options = array();
                            foreach($temp_types as $type => $value)
                            {
                                if($value == $max)
                                    $options[] = $type;
                            }

                            $tag->targetType = $options[random_int(0,(count($options) - 1))];
                        }
                        else
                            if($specialization[0] == 'W')
                                $tag->targetType = 'B';
                            else
                                $tag->targetType = $specialization[0];
                    }
                }
                else
                {
                    if($this->debugging && $tag->targetType !== true)
                        $GLOBALS['DebugTool']->push($tag->targetType, 'target type not accepted. set to ninjutsu. bad data in damage over time tag', __METHOD__, __FILE__, __LINE__);
                    $tag->targetType = self::NINJUTSU;
                }
            }

            if(count($tag->targetGeneral) == 2 && $tag->statBased)
            {
                if( $tag->targetGeneral[0] === true || !in_array($tag->targetGeneral[0], $this->ALLGENERALS))
                {
                    $temp_generals = array( self::STRENGTH => $this->users[$tag->owner][self::STRENGTH],
                                            self::SPEED => $this->users[$tag->owner][self::SPEED],
                                            self::INTELLIGENCE => $this->users[$tag->owner][self::INTELLIGENCE],
                                            self::WILLPOWER => $this->users[$tag->owner][self::WILLPOWER],
                                            );

                    if($tag->targetGeneral[0] === 'highest')
                    {
                        $tag->targetGeneral[0] = array_search(max($temp_generals), $temp_generals);
                    }
                    else
                    {
                        if($this->debugging && $tag->targetGeneral[0] !== true)
                            $GLOBALS['DebugTool']->push($tag->targetGeneral[0], 'first target general not accepted. set to strength. bad data in damage over time tag', __METHOD__, __FILE__, __LINE__);
                        $tag->targetGeneral[0] = self::STRENGTH;
                    }
                }

                if( $tag->targetGeneral[1] === true || !in_array($tag->targetGeneral[1], $this->ALLGENERALS))
                {
                    if($tag->targetGeneral[1] === 'highest')
                    {
                        unset($temp_generals[$tag->targetGeneral[0]]);
                        $tag->targetGeneral[1] = array_search(max($temp_generals), $temp_generals);
                    }
                    else
                    {
                        if($this->debugging && $tag->targetGeneral[1] !== true)
                            $GLOBALS['DebugTool']->push($tag->targetGeneral[1], 'second target general not accepted. set to strength. bad data in damage over time tag', __METHOD__, __FILE__, __LINE__);
                        $tag->targetGeneral[1] = self::INTELLIGENCE;
                    }
                }
            }
            else if($tag->statBased)
            {
                $tag->targetGeneral = array(self::STRENGTH, self::STRENGTH);
                if($this->debugging)
                    $GLOBALS['DebugTool']->push($tag->targetGeneral, 'target general must have 2 items. bad data in damage over time tag.', __METHOD__, __FILE__, __LINE__);
            }

            if($tag->statBased)
                foreach($tag->targetElement as $element_key => $element)
                {
                    if( !in_array($element, $this->ALLELEMENT))
                    {
                        $tag->targetElement[$element_key] = self::NONE;
                        if($this->debugging)
                        {
                            $temp_array = $this->ALLELEMENT;
                            $temp_array['vs'] = $element;
                            $GLOBALS['DebugTool']->push($temp_array, 'bad element data found in damage over time tag. was set to none.', __METHOD__, __FILE__, __LINE__);
                        }
                    }
                    else if ( $element === true )
                    {
                        $tag->targetElement[$element_key] = self::NONE;
                    }
                }

            $this->doDamage($tag,true);
        }
    }


    //doDamageStatBased is called by damage and damageOverTime
    //this deals damage to a user based on the damage equation
    function doDamage($tag,$over_time)
    {
        if($tag->targetType == 'highest')
        {
            $temp_value = $this->users[$tag->owner] [self::OFFENSE.self::TAIJUTSU];
            $temp_type = self::TAIJUTSU;

            if( $temp_value < $this->users[$tag->owner] [self::OFFENSE.self::NINJUTSU] )
            { 
                $temp_value = $this->users[$tag->owner] [self::OFFENSE.self::NINJUTSU];
                $temp_type = self::NINJUTSU;
            }

            if( $temp_value < $this->users[$tag->owner] [self::OFFENSE.self::GENJUTSU] )
            { 
                $temp_value = $this->users[$tag->owner] [self::OFFENSE.self::GENJUTSU];
                $temp_type = self::GENJUTSU;
            }

            if( $temp_value < $this->users[$tag->owner] [self::OFFENSE.self::BUKIJUTSU] )
            { 
                $temp_value = $this->users[$tag->owner] [self::OFFENSE.self::BUKIJUTSU];
                $temp_type = self::BUKIJUTSU;
            }

            $tag->targetType = $temp_type;
        }

        //getting toe for this attack
        $TOE = $this->parseTOEM($tag->targetType, $tag->origin, $tag->targetElement, true); //getting TOEM for get

        $TOEM = $TOE;
        $TOEM[] = $this->ALLVALUETYPE; //adding M to TOEM

        $M = array();
        $M[] = $this->ALLVALUETYPE;

        //getting immunities
        $owner_TOEI = $TOE;
        $owner_TOEI[] = array(self::RECOIL,self::REFLECT,self::ABSORB);
        $owner_immunities = $this->addressTOEM($tag->owner, self::IMMUNITY, $owner_TOEI, 'get');

        $target_TOEI = $TOE;
        $target_TOEI[] = array(self::DAMAGE, self::DAMAGEOVERTIME, self::LEACH);
        $target_immunities = $this->addressTOEM($tag->target, self::IMMUNITY, $target_TOEI, 'get');

        $immunities = array_merge($owner_immunities, $target_immunities);

        $immune = array();
        $immune[self::DAMAGE] = false;
        $immune[self::DAMAGEOVERTIME] = false;
        $immune[self::RECOIL] = false;
        $immune[self::REFLECT] = false;
        $immune[self::ABSORB] = false;
        $immune[self::LEACH] = false;

        //for every immunity entry...
        foreach($immunities as $key => $value)
            //if the immunity is true...
            if($value === true)
                //for each type of immunity...
                foreach($this->ALLIMMUNITY as $i)
                    //if immunity types match...
                    if(strpos($key, $i) !== false)
                        //record that it was set to true.
                        $immune[$i] = true;


        //get damage M's
        $strength_M = $this->parseValue($tag->value, $tag->effect_level, $tag);

        $this->crit_status = false;

        //calc damage
        if($tag->statBased)
        {
            $jutsu_power = $strength_M[self::FLATBOOST] +
                          ($strength_M[self::FLATBOOST] * ($strength_M[self::BOOSTPERCENTAGE] / 100)) +
                          ($this->users[$tag->target][self::HEALTHMAX] * ($strength_M[self::PRINCIPALPERCENTAGE] / 100)) +
                          $tag->weapon_boost;

            $owners_rank = $this->users[$tag->owner][self::RANK];

            //getting all stats and updating boosts for them.
            if( !isset($this->users[$tag->target]['status_effects']['staggered']) || $this->users[$tag->target]['status_effects']['staggered'] == 0)
            {
                $targets_armor          = $this->users[$tag->target][self::ARMORBASE]                + $this->parseM( $this->addressTOEM( $tag->target, self::ARMORBASE,        $M,      'get' ), $this->users[$tag->target][self::ARMORBASE]                );
                if($targets_armor < 0) $targets_armor = 0;
            }
            else
                $targets_armor = 0;

            if( !isset($this->users[$tag->owner]['status_effects']['staggered']) || $this->users[$tag->owner]['status_effects']['staggered'] == 0)
            {
                $owners_stability       = $this->users[$tag->owner] [self::STABILITY]                + $this->parseM( $this->addressTOEM( $tag->owner,  self::STABILITY,        $TOEM,   'get' ), $this->users[$tag->owner] [self::STABILITY]                );
                if($owners_stability < 0) $owners_stability = 0;
                if($owners_stability > $this->STABILITYCAP[$owners_rank]) $owners_stability = $this->STABILITYCAP[$owners_rank];
            }
            else
                $owners_stability = 0;

            if( !isset($this->users[$tag->owner]['status_effects']['staggered']) || $this->users[$tag->owner]['status_effects']['staggered'] == 0)
            {
                $owners_accuracy        = $this->users[$tag->owner] [self::ACCURACY]                 + $this->parseM( $this->addressTOEM( $tag->owner,  self::ACCURACY,         $TOEM,   'get' ), $this->users[$tag->owner] [self::ACCURACY]                 );
                if($owners_accuracy < 0) $owners_accuracy = 0;
            }
            else
                $owners_accuracy = 0;

            if( !isset($this->users[$tag->owner]['status_effects']['staggered']) || $this->users[$tag->owner]['status_effects']['staggered'] == 0)
            {
                $owners_expertise       = $this->users[$tag->owner] [self::EXPERTISE]                + $this->parseM( $this->addressTOEM( $tag->owner,  self::EXPERTISE,        $TOEM,   'get' ), $this->users[$tag->owner] [self::EXPERTISE]                );
                if($owners_expertise < 0) $owners_expertise = 0;
            }
            else
                $owners_expertise = 0;

            if( !isset($this->users[$tag->owner]['status_effects']['staggered']) || $this->users[$tag->owner]['status_effects']['staggered'] == 0)
            {
                $owners_chakra_power    = $this->users[$tag->owner] [self::CHAKRAPOWER]              + $this->parseM( $this->addressTOEM( $tag->owner,  self::CHAKRAPOWER,      $TOEM,   'get' ), $this->users[$tag->owner] [self::CHAKRAPOWER]              );
                if($owners_chakra_power < 0) $owners_chakra_power = 0;
            }
            else
                $owners_chakra_power = 0;

            if( !isset($this->users[$tag->owner]['status_effects']['staggered']) || $this->users[$tag->owner]['status_effects']['staggered'] == 0)
            {
                $owners_critical_strike = $this->users[$tag->owner] [self::CRITICALSTRIKE]           + $this->parseM( $this->addressTOEM( $tag->owner,  self::CRITICALSTRIKE,   $TOEM,   'get' ), $this->users[$tag->owner] [self::CRITICALSTRIKE]           );
                if($owners_critical_strike < 0) $owners_critical_strike = 0;
            }
            else
                $owners_critical_strike = 0;

            $user_offense           = $this->users[$tag->owner] [self::OFFENSE.$tag->targetType] + $this->parseM( $this->addressTOEM( $tag->owner,  self::OFFENSE,          $TOEM,   'get' ), $this->users[$tag->owner] [self::OFFENSE.$tag->targetType] );
            if($user_offense < 0) $user_offense = 0;

            $target_defense         = $this->users[$tag->target][self::DEFENSE.$tag->targetType] + $this->parseM( $this->addressTOEM( $tag->target, self::DEFENSE,          $TOEM,   'get' ), $this->users[$tag->target][self::DEFENSE.$tag->targetType] );
            if($target_defense < 0) $target_defense = 0;

            $user_general_1         = $this->users[$tag->owner] [$tag->targetGeneral[0]]         + $this->parseM( $this->addressTOEM( $tag->owner,  $tag->targetGeneral[0], $M,      'get' ), $this->users[$tag->owner] [$tag->targetGeneral[0]]         );
            if($user_general_1 < 0) $user_general_1 = 0;

            $user_general_2         = $this->users[$tag->owner] [$tag->targetGeneral[1]]         + $this->parseM( $this->addressTOEM( $tag->owner,  $tag->targetGeneral[1], $M,      'get' ), $this->users[$tag->owner] [$tag->targetGeneral[0]]         );
            if($user_general_2 < 0) $user_general_2 = 0;

            $target_general_1       = $this->users[$tag->target][$tag->targetGeneral[0]]         + $this->parseM( $this->addressTOEM( $tag->target, $tag->targetGeneral[0], $M,      'get' ), $this->users[$tag->target][$tag->targetGeneral[0]]         );
            if($target_general_1 < 0) $target_general_1 = 0;

            $target_general_2       = $this->users[$tag->target][$tag->targetGeneral[1]]         + $this->parseM( $this->addressTOEM( $tag->target, $tag->targetGeneral[1], $M,      'get' ), $this->users[$tag->target][$tag->targetGeneral[1]]         );
            if($target_general_2 < 0) $target_general_2 = 0;


            //runing calculations
            $power_boost = $jutsu_power * ( $owners_accuracy / $this->ACCURACYCAP[$owners_rank] ) / ( $this->ACCURACYIDENTIFIER[$owners_rank]  + $owners_rank );

            $user_offense = $user_offense;

            //$expertise_boost = $user_offense * ( $owners_expertise / ( $owners_rank * $this->EXPERTISEIDENTIFIER[$owners_rank] ) );

            $target_defense = 351 * ($target_defense)**0.88;

            $attack_power = ( $jutsu_power + $power_boost ) * 100;

            $user_generals = ($user_general_1*0.7) + ($user_general_2*0.3);

            $target_generals = 351 * (($target_general_1*0.7) + ($target_general_2*0.3))**0.48;

            if($target_defense < 1)
                $target_defense = 1;

            if($target_generals < 1)
                $target_generals = 1;

            $offense_vs_defense = ( $user_offense / ( $target_defense + $target_generals ) )**0.1;

            $gen_vs_gen = ( $user_generals / $target_generals )**0.1;

            $battle_factor = ( $offense_vs_defense * $gen_vs_gen );

            $pure_offense =  $user_offense + $user_generals * 10 + $attack_power;

            $critical_roll = false;
            $critical_power = false;

            if($tag->targetType == self::TAIJUTSU || $tag->targetType == self::BUKIJUTSU)
            {
                $critical_roll = random_int(1,1000);

                $critical_power = ($owners_critical_strike / 0.05) ** ($owners_rank / 10);


                if($critical_roll > 975 - (( $critical_power * 3 * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank] ) * 5))
                {
                    $damage_multiplier = 1.1 + (($critical_power*(10/3)) * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank]) / 100;
                    $this->crit_status = true;

                    $GLOBALS['template']->append("damage_multiplier",'crit damage boost: '.$damage_multiplier.' ('.$tag->owner.') Chance:'.$critical_roll.' > '.(975 - (( $critical_power * 3 * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank] ) * 5)));
                }
                else
                {
                    $damage_multiplier = 1.0;
                    $this->crit_status = false;
                    $GLOBALS['template']->append("damage_multiplier",'crit damage boost: no crit / chance: %'.round(100 - (975 - (( $critical_power * 3 * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank] ) * 5))/10, 1).'('.$tag->owner.') Chance: '.$critical_roll.' > '.(975 - (( $critical_power * 3 * $this->CRITICALSTRIKEIDENTIFIER[$owners_rank] ) * 5)));
                }
            }
            else 
            {
                $chakra_calculation = ( $owners_chakra_power / 0.05 ) ** ( $owners_rank / 10 );
                $damage_multiplier = 1.0 + (0.1 + (($chakra_calculation*(10/3)) * $this->CHAKRAPOWERIDENTIFIER[$owners_rank]) / 100) * (1000 - (975 - ( $chakra_calculation * 3 * $this->CHAKRAPOWERIDENTIFIER[$owners_rank]) * 5))/1000;
                $GLOBALS['template']->append("damage_multiplier",'chakra damage boost: '.$damage_multiplier.' ('.$tag->owner.') ');
            }

            //removing flux
            $fluxMin = 9011;
            $fluxMax = 10579;
            //$fluxMin = 10000;
            //$fluxMax = 10000;


            //if($owners_rank < 3)
            //{
            //    $fluxMin = 75;
            //    $fluxMax = 110;
            //}
            //else
            //{
            //    $fluxMin = 75  + ( ( $owners_stability / $this->FLUXIDENTIFIERMIN[$owners_rank] ) / 100);
            //    $fluxMax = 110 - ( ( $owners_stability / $this->FLUXIDENTIFIERMAX[$owners_rank] ) / 100);
            //}

            $inital_damage = 0.1 * ($battle_factor ** 3.5) * ($pure_offense * 0.2);

            //accounting for armor and expertise.
            $expertise_boost = (($owners_expertise**1.5)/($this->EXPERTISECAP[$owners_rank]**1.5) * 0.1);

            $armorBoost = (($targets_armor**1.5)/($this->ARMORCAP[$this->users[$tag->target]['rank']]**1.5) * -0.2) + 1;

            $damage_after_expertise_and_armor = $inital_damage * ($expertise_boost + $armorBoost);


            $post_flux_damage = $damage_after_expertise_and_armor * ( random_int($fluxMin, $fluxMax) / 10000 );
            //$post_flux_damage = $damage_after_expertise_and_armor;

            $damage_value = $post_flux_damage * $damage_multiplier;
        }
        else
        {
            $owners_rank = $this->users[$tag->owner][self::RANK];

            $damage_value = $strength_M[self::FLATBOOST] +
                          ($strength_M[self::FLATBOOST] * ($strength_M[self::BOOSTPERCENTAGE] / 100)) +
                          ($this->users[$tag->target][self::HEALTHMAX] * ($strength_M[self::PRINCIPALPERCENTAGE] / 100));
        }

        //get damageOut_value
        $damageOut_value = $this->parseM($this->addressTOEM($tag->owner, self::DAMAGEOUT, $TOEM, 'get'),$damage_value);


        //get damageIn_value
        $damageIn_value = $this->parseM($this->addressTOEM($tag->target, self::DAMAGEIN, $TOEM, 'get'),$damage_value);


        //check elements
        $elemental_bonus = 1;
        foreach($this->users[$tag->target][self::ELEMENTS] as $element)
        {
            if( $element !== '' && (( !is_array($tag->targetElement) && $this->ELEMENTWEAKNESS[$element] === $tag->targetElement) || (is_array($tag->targetElement) && in_array($this->ELEMENTWEAKNESS[$element], $tag->targetElement) )))
                $elemental_bonus += 0.05;
        }

        foreach($this->users[$tag->owner][self::ELEMENTS] as $element)
        {
            if($element !== '' && $element !== 'none' && array_flip($this->ELEMENTEXPAND)[$element] === $tag->targetElement)
                $elemental_bonus += 0.05;
        }


        //caclulate final damage value
        $final_damage_value = ($damage_value * $elemental_bonus ) + $damageOut_value + $damageIn_value;

        //if degrade or amplify effect is set process them.
        if($tag->degradeEffect !== false)
        {
            $limit = array_pop($tag->degradeEffect);
            $degradeEffect = 0;

            if(isset($tag->degradeEffect[$tag->age]))
                $degradeEffect = $tag->degradeEffect[$tag->age];
            else
                $degradeEffect = array_pop($tag->degradeEffect);

            if(substr($degradeEffect, -1) == '%')
            {
                $degradeEffect = rtrim($degradeEffect,'%');

                $degradeEffect = ( 100 - $degradeEffect ) / 100;

                if( $final_damage_value * $degradeEffect > $limit )
                    $final_damage_value *= $degradeEffect;
                else
                    $final_damage_value = $limit;
            }
            else
            {
                if($final_damage_value - $degradeEffect > $limit )
                    $final_damage_value -= $degradeEffect;
                else
                    $final_damage_value = $limit;
            }
        }

        if($tag->amplifyEffect !== false)
        {
            $limit = array_pop($tag->amplifyEffect);
            $amplifyEffect = 0;

            if(isset($tag->amplifyEffect[$tag->age]))
                $amplifyEffect = $tag->amplifyEffect[$tag->age];
            else
                $amplifyEffect = array_pop($tag->amplifyEffect);

            if(substr($amplifyEffect, -1) == '%')
            {
                $amplifyEffect = rtrim($amplifyEffect,'%');

                $amplifyEffect = ( 100 + $amplifyEffect ) / 100;

                if( $final_damage_value * $amplifyEffect < $limit )
                    $final_damage_value *= $amplifyEffect;
                else
                    $final_damage_value = $limit;
            }
            else
            {
                if($final_damage_value + $amplifyEffect < $limit )
                    $final_damage_value += $amplifyEffect;
                else
                    $final_damage_value = $limit;
            }
        }


        //manage reflect
        $reflect = array_sum($this->addressTOEM($tag->target, self::REFLECT, $TOE, 'get'));
        $reflect_damage = 0;
        if($reflect != 0 && !$immune[self::REFLECT])
        {
            $reflect_damage = round($final_damage_value * ($reflect / 100));
            $this->users[$tag->owner][self::HEALTH] = $this->users[$tag->owner][self::HEALTH] - $reflect_damage;
            if($this->users[$tag->owner][self::HEALTH] < 0)
                $this->users[$tag->owner][self::HEALTH] = 0;

            if(!isset($this->battle_log[$this->turn_counter][$tag->owner]['reflect']))
                $this->battle_log[$this->turn_counter][$tag->owner]['reflect'] = round($reflect_damage);
            else
                $this->battle_log[$this->turn_counter][$tag->owner]['reflect'] += round($reflect_damage);
        }


        //manage recoil
        $recoil_damage = 0;
        if($tag->recoil !== false && !$immune[self::RECOIL])
        {
            //parsing recoil value
            $recoil_M = $this->parseValue($tag->recoil, $tag->effect_level, $tag);

            //processing recoil value
            $recoil_damage = round($recoil_M[self::FLATBOOST] +
                          ($recoil_M[self::FLATBOOST] * ($recoil_M[self::BOOSTPERCENTAGE] / 100)) +
                          ($final_damage_value * ($recoil_M[self::PRINCIPALPERCENTAGE] / 100)));

            //if recoil is less than 0 set it to zero
            if($recoil_damage < 0)
                $recoil_damage = 0;

            //update owners health
            $this->users[$tag->owner][self::HEALTH] = $this->users[$tag->owner][self::HEALTH] - $recoil_damage;

            $effect = '';

            if($over_time)
                $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'damage_over_time', 'recoil');
            else
                $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'damage', 'recoil');

            if(!isset($this->battle_log[$this->turn_counter][$tag->owner]['recoil']))
                $this->battle_log[$this->turn_counter][$tag->owner]['recoil'] = round($recoil_damage);
            else
                $this->battle_log[$this->turn_counter][$tag->owner]['recoil'] += round($recoil_damage);

            //if owners health is less than zero set it to zero
            if($this->users[$tag->owner][self::HEALTH] < 0)
                $this->users[$tag->owner][self::HEALTH] = 0;
        }


        //manage leach
        $leach_gain = 0;
        if($tag->leach !== false && !$immune[self::LEACH])
        {
            //parsing leach value
            $leach_M = $this->parseValue($tag->leach, $tag->effect_level, $tag);

            //processing leach value
            $leach_gain = round($leach_M[self::FLATBOOST] +
                          ($leach_M[self::FLATBOOST] * ($leach_M[self::BOOSTPERCENTAGE] / 100)) +
                          ($final_damage_value * ($leach_M[self::PRINCIPALPERCENTAGE] / 100)));

            //if leach is less than 0 set it to zero
            if($leach_gain < 0)
                $leach_gain = 0;

            //update owners health
            $this->users[$tag->owner][self::HEALTH] = $this->users[$tag->owner][self::HEALTH] + $leach_gain;

            if($over_time)
                $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'damage_over_time', 'leach');
            else
                $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'damage', 'leach');

            if(!isset($this->battle_log[$this->turn_counter][$tag->owner]['leach']))
                $this->battle_log[$this->turn_counter][$tag->owner]['leach'] = round($leach_gain);
            else
                $this->battle_log[$this->turn_counter][$tag->owner]['leach'] += round($leach_gain);

            //if owners heal is over their health cap set it to their health cap
            if($this->users[$tag->owner][self::HEALTH] > $this->users[$tag->owner][self::HEALTHMAX])
                $this->users[$tag->owner][self::HEALTH] = $this->users[$tag->owner][self::HEALTHMAX];
        }


        //manage absorb
        $absorb = array_sum($this->addressTOEM($tag->target, self::ABSORB, $TOE, 'get'));
        $absorb_gain = 0;
        if($absorb != 0 && !$immune[self::ABSORB])
        {
            $absorb_gain = round($final_damage_value*($absorb/100));
            $this->users[$tag->target][self::HEALTH] = round($this->users[$tag->target][self::HEALTH] + $absorb_gain);

            if($this->users[$tag->target][self::HEALTH] > $this->users[$tag->target][self::HEALTHMAX])
                $this->users[$tag->target][self::HEALTH] = $this->users[$tag->target][self::HEALTHMAX];

            $final_damage_value = 0;

            if(!isset($this->battle_log[$this->turn_counter][$tag->owner]['absorb']))
                $this->battle_log[$this->turn_counter][$tag->owner]['absorb'] = round($absorb_gain);
            else
                $this->battle_log[$this->turn_counter][$tag->owner]['absorb'] += round($absorb_gain);
        }


        //set damage value
        if( ($over_time && !$immune[self::DAMAGEOVERTIME]) || (!$over_time && !$immune[self::DAMAGE]) )
        {
            //deal damage
            $this->users[$tag->target][self::HEALTH] = round(($this->users[$tag->target][self::HEALTH] - $final_damage_value));

            //log damage for battle_log
            if($over_time)
            {
                if(!isset($this->battle_log[$this->turn_counter][$tag->owner]['damage_over_time_delt'][0]))
                    $this->battle_log[$this->turn_counter][$tag->owner]['damage_over_time_delt'] = array();

                $display_element = '';
                if(count($tag->targetElement) == 1)
                    $display_element = $this->ELEMENTEXPAND[$tag->targetElement[0]];
                else
                    $display_element = $this->ELEMENTEXPAND[$tag->targetElement[0]].' & '.$this->ELEMENTEXPAND[$tag->targetElement[1]];

                $this->battle_log[$this->turn_counter][$tag->owner]['damage_over_time_delt'][] = array('amount' => round($final_damage_value), 'type' => $display_element, 'aoe' => $tag->resultOfAoe, 'crit' => $this->crit_status);

                $message = '';

                if($tag->statBased === false)
                    $message .= 'flat, ';
                else
                    $message .= 'normal, ';

                $message .= $tag->duration.' round';
                if($tag->duration > 1)
                    $message .='s';

                if(isset($this->users[$tag->owner]['status_effects']['staggered']) && $this->users[$tag->owner]['status_effects']['staggered'] != 0)
                    $message .= " (staggered)";

                $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'damage_over_time', $message);
            }
            else
            {
                //$this->battle_log[$this->turn_counter][$tag->owner]['damage_delt'] = round($final_damage_value);
                $display_element = '';
                if(count($tag->targetElement) == 1)
                {
                    if($tag->targetElement === true)
                        $display_element = '';
                    else if(!is_string($tag->targetElement))
                        $display_element = $this->ELEMENTEXPAND[$tag->targetElement[0]];
                    else
                        $display_element = $this->ELEMENTEXPAND[$tag->targetElement];
                }
                else
                    $display_element = $this->ELEMENTEXPAND[$tag->targetElement[0]].' & '.$this->ELEMENTEXPAND[$tag->targetElement[1]];

                if(!isset($this->battle_log[$this->turn_counter][$tag->owner]['damage_delt']))
                    $this->battle_log[$this->turn_counter][$tag->owner]['damage_delt'] = array();

                $this->battle_log[$this->turn_counter][$tag->owner]['damage_delt'][] = array('amount' => round($final_damage_value), 'type' => $display_element, 'aoe' => $tag->resultOfAoe, 'crit' => $this->crit_status);

                $message = '';

                if($tag->statBased === false)
                    $message .= 'flat';
                else
                    $message .= 'normal';

                if(isset($this->users[$tag->owner]['status_effects']['staggered']) && $this->users[$tag->owner]['status_effects']['staggered'] != 0)
                    $message .= " (staggered)";

                $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'damage', $message);
            }

            //recording attack type
            $this->battle_log[$this->turn_counter][$tag->owner]['target_type'] = $tag->targetType;

            //check to make sure health didnt drop undero zero
            if($this->users[$tag->target][self::HEALTH] < 0)
                $this->users[$tag->target][self::HEALTH] = 0;

            //update durability
            //this method is held by the battle class or possibly a class extending it.
            $this->updateDurability($tag, $final_damage_value, $owners_rank);
        }

        if($this->users[$tag->owner][self::HEALTH] <= 0)
        {
            $this->users[$tag->owner]['remove'] = true;
            $this->removeUserFromCombat($tag->owner, false);

            if(!isset($this->users[$tag->owner]['ai']))
                $GLOBALS['Events']->acceptEvent('user_death', array('data'=>$this->users[$tag->owner]['uid'], 'context'=>$this->users[$tag->owner]['uid'], 'count'=>1 ));
            else if(isset($this->users[$tag->owner]['aid']))
                $GLOBALS['Events']->acceptEvent('ai_death', array('data'=>$this->users[$tag->owner]['aid'], 'context'=>$this->users[$tag->owner]['aid'], 'count'=>1 ));

            $this->battle_log[$this->turn_counter][$tag->owner]['died'] = $tag->target;
            $this->battle_log[$this->turn_counter][$tag->target]['killed'] = $tag->owner;

            //checking to see if this user is an ai
            if(!isset($this->users[$tag->target]['ai']) || $this->users[$tag->target]['ai'] !== true)
            {
                if(!isset($this->users[$tag->target]['update']['exp']) )
                    $this->users[$tag->target]['update']['exp'] = 0;

                $this->users[$tag->target]['update']['exp'] += $this->users[$tag->owner]['rank'] * 150 - $this->users[$tag->target]['rank'] * 50;

                if(isset($this->users[$tag->owner]['ai']) || $this->users[$tag->owner]['ai'] !== true)
                {
                    if(!isset($this->users[$tag->target]['update']['pvp_exp']))
                            $this->users[$tag->target]['update']['pvp_exp'] = array();

                    if(isset($this->users[$tag->owner]['update']['starting_dsr']))
                        $this->users[$tag->target]['update']['pvp_exp'][] = ((((($this->users[$tag->owner]['update']['starting_dsr']/$this->users[$tag->target]['update']['starting_dsr']) - 1 ) * 3 ) + 1) * ( 4 / 5 ) ) * 10;
                }

                $this->users[$tag->target]['update']['users_killed'][] = $tag->owner;
            }

            foreach($this->users as $username => $userdata)
            {
                if($userdata['team'] == $this->users[$tag->target]['team'] && $username != $tag->target && (!isset($this->users[$username]['ai']) || $this->users[$username]['ai'] !== true))
                {
                    if(!isset($this->users[$username]['update']['exp']))
                        $this->users[$username]['update']['exp'] = ($userdata['rank'] * 150 - $this->users[$tag->target]['rank'] * 50) * 0.5;
                    else
                        $this->users[$username]['update']['exp'] += ($userdata['rank'] * 150 - $this->users[$tag->target]['rank'] * 50) * 0.5;

                    if(isset($this->users[$tag->owner]['ai']) || $this->users[$tag->owner]['ai'] !== true)
                    {
                        if(!isset($this->users[$username]['update']['pvp_exp']))
                            $this->users[$username]['update']['pvp_exp'] = array();

                        if(isset($this->users[$tag->owner]['update']['starting_dsr']))
                            $this->users[$username]['update']['pvp_exp'][] = ((((($this->users[$tag->owner]['update']['starting_dsr']/$this->users[$username]['update']['starting_dsr']) - 1 ) * 3 ) + 1) * ( 4 / 5 ) ) * 5;
                    }
                }
            }
        }

        if($this->users[$tag->target][self::HEALTH] <= 0)
        {
            $this->users[$tag->target]['remove'] = true;
            $this->removeUserFromCombat($tag->target, false);

            if(!isset($this->users[$tag->target]['ai']) || !$this->users[$tag->target]['ai'])
                $GLOBALS['Events']->acceptEvent('user_death', array('data'=>$this->users[$tag->target]['uid'], 'context'=>$this->users[$tag->target]['uid'], 'count'=>1 ));
            else
                $GLOBALS['Events']->acceptEvent('ai_death', array('data'=>$this->users[$tag->target]['aid'], 'context'=>$this->users[$tag->target]['aid'], 'count'=>1 ));

            $this->battle_log[$this->turn_counter][$tag->owner]['killed'] = $tag->target;
            $this->battle_log[$this->turn_counter][$tag->target]['died'] = $tag->owner;

            //for each user in this turns battle log
            foreach($this->getTurnOrder() as $key => $user_key)
            {
                //if this user is the owner or target
                if($user_key == $tag->owner || $user_key == $tag->target)
                {
                    //if the first one found is the owner
                    //that means that the target has not taken their turn yet and wont get to do so.
                    //update battle log to show that they failed to take their action due to their death.
                    if($user_key != $tag->owner)    
                    {
                        $this->battle_log[$this->turn_counter][$tag->target]['failure'] = 'failure';
                    }

                    break; //break out of loop on the first one found
                }
            }

            if(!isset($this->users[$tag->owner]['ai']) || $this->users[$tag->owner]['ai'] !== true)
            {
                if(!isset($this->users[$tag->owner]['update']['exp']))
                    $this->users[$tag->owner]['update']['exp'] = 0;

                $this->users[$tag->owner]['update']['exp'] += $this->users[$tag->target]['rank'] * 150 - $this->users[$tag->owner]['rank'] * 50;

                if(isset($this->users[$tag->target]['ai']) || $this->users[$tag->target]['ai'] !== true)
                {
                    if(!isset($this->users[$tag->owner]['update']['pvp_exp']))
                            $this->users[$tag->owner]['update']['pvp_exp'] = array();

                    if(isset($this->users[$tag->target]['update']['starting_dsr']))
                        $this->users[$tag->owner]['update']['pvp_exp'][] = ((((($this->users[$tag->target]['update']['starting_dsr']/$this->users[$tag->owner]['update']['starting_dsr']) - 1 ) * 3 ) + 1) * ( 4 / 5 ) ) * 10;
                }

                $this->users[$tag->owner]['update']['users_killed'][] = $tag->target;
            }

            foreach($this->users as $username => $userdata)
            {
                if($userdata['team'] == $this->users[$tag->owner]['team'] && $username != $tag->owner && (!isset($this->users[$username]['ai']) || $this->users[$username]['ai'] !== true))
                {
                    if(!isset($this->users[$username]['update']['exp']))
                        $this->users[$username]['update']['exp'] = ($userdata['rank'] * 150 - $this->users[$tag->owner]['rank'] * 50) * 0.5;
                    else
                        $this->users[$username]['update']['exp'] += ($userdata['rank'] * 150 - $this->users[$tag->owner]['rank'] * 50) * 0.5;

                    if(isset($this->users[$tag->target]['ai']) || $this->users[$tag->target]['ai'] !== true)
                    {
                        if(!isset($this->users[$username]['update']['pvp_exp']))
                            $this->users[$username]['update']['pvp_exp'] = array();

                        if(isset($this->users[$tag->target]['update']['starting_dsr']))
                            $this->users[$username]['update']['pvp_exp'][] = ((((($this->users[$tag->target]['update']['starting_dsr']/$this->users[$username]['update']['starting_dsr']) - 1 ) * 3 ) + 1) * ( 4 / 5 ) ) * 5;
                    }
                }

                if( $this->users[$tag->owner]['team'] == $userdata['team'])
                    if( isset($userdata['bounty_hunter']) && $userdata['bounty_hunter'] !== false && $userdata['bounty_hunter'] == $tag->target)
                    {
                        $this->users[$username]['update']['bounty_hunter'] = array('target'=>$tag->target, 'target_rank'=>$this->users[$username]['rank']);
                        $this->users[$tag->target]['update']['bounty_collected'] = $this->users[$username]['village'];
                    }
            }
        }
        //$thang = array(
        //    'owner'=>$tag->owner,
        //    `target`=>$tag->target,
        //    'jutsu_power'=>$jutsu_power,
        //    'power_boost'=>$power_boost,
        //    'user_offense'=>$user_offense,
        //    'expertise_boost'=>$expertise_boost,
        //    'armor_boost'=>(($targets_armor**1.5)/($this->ARMORCAP[$this->users[$tag->target]['rank']]**1.5) * -0.2) + 1,
        //    'targets_armor'=>$targets_armor,
        //    'targets_armor ** 1.5'=>$targets_armor**1.5,
        //    'armor cap'=>$this->ARMORCAP[$this->users[$tag->target]['rank']],
        //    'armor cap ** 1.5'=>$this->ARMORCAP[$this->users[$tag->target]['rank']]**1.5,
        //    'division'=>($targets_armor**1.5)/($this->ARMORCAP[$this->users[$tag->target]['rank']]**1.5),
        //    'multiplication'=>(($targets_armor**1.5)/($this->ARMORCAP[$this->users[$tag->target]['rank']]**1.5) * -0.2),
        //    'addition'=>(($targets_armor**1.5)/($this->ARMORCAP[$this->users[$tag->target]['rank']]**1.5) * -0.2) + 1,
        //    'target_defense'=>$target_defense,
        //    'attack_power'=>$attack_power,
        //    'user_generals'=>$user_generals,
        //    'target_generals'=>$target_generals,
        //    'offense_vs_defense'=>$offense_vs_defense,
        //    'gen_vs_gen'=>$gen_vs_gen,
        //    'battle_factor'=>$battle_factor,
        //    'pure_offense'=>$pure_offense,
        //    'critical_power'=>$critical_power,
        //    'critical_roll'=>$critical_roll,
        //    '$damage_multiplier'=>$damage_multiplier,
        //    'fluxMin'=>$fluxMin,
        //    'fluxMax'=>$fluxMax,
        //    'initial_damage'=>$inital_damage,
        //    '$damage_after_expertise_and_armor'=>$damage_after_expertise_and_armor,
        //    'damage_value'=>$damage_value,
        //    'final_damage_value'=>$final_damage_value,
        //    'absorb'=>$absorb,
        //    'absorb_gain'=>$absorb_gain,
        //    'reflect'=>$reflect,
        //    'reflect_damage'=>$reflect_damage,
        //    'recoil_damage'=>$recoil_damage,
        //    'leach_gain'=>$leach_gain,
        //    'immune'=>$immune
        //        );
        //
        //ob_start();
        //var_dump($thang);
        //$result = ob_get_clean();
        //    
        //error_log($result);

       //$GLOBALS['DebugTool']->push($thang, 'damage stat based data dump', __METHOD__, __FILE__, __LINE__);
    }


    //effectDamageIn changes damageInTOEM
    //calls applyEffectValue
    //calls parseTOEM
    //all work is done by those methods and applyTOEM
    function effectDamageIn($tag)
    {
        $this->applyEffectValue($tag->target,
                                self::DAMAGEIN,
                                $this->parseTOEM($this->getHighestTypeForEffectTag($tag), $tag->targetOrigin, $tag->targetElement),
                                $tag->value,
                                $tag->effect_level,
                                $tag->owner);
    }



    //effectDamageOut changes damageOutTOEM
    //calls applyEffectValue
    //calls parseTOEM
    //all work is done by those methods and applyTOEM
    function effectDamageOut($tag)
    {
        $this->applyEffectValue($tag->target,
                                self::DAMAGEOUT,
                                $this->parseTOEM($this->getHighestTypeForEffectTag($tag), $tag->targetOrigin, $tag->targetElement),
                                $tag->value,
                                $tag->effect_level,
                                $tag->owner);
    }


    //reflectDamage
    //this method processes the value sent in with the tag and
    //using addressTOEM sets data that is used by the damaging tags to
    //handle reflection of damage back to the attacker.
    //this sets reflect{TOEM}
    function reflectDamage($tag)
    {
        //processing tags value
        $value = $this->parseValue($tag->value, $tag->effect_level, $tag);

        //error message for if PP was passed in. it has no use here.
        if($value[self::PRINCIPALPERCENTAGE] != false && $this->debugging)
            $GLOBALS['DebugTool']->push($value, 'value set is not compatable with reflectDamage. principal percentage is not avaliable here. value: ', __METHOD__, __FILE__, __LINE__);

        //calculating the value of the reflection
        $value = $value[self::FLATBOOST] + ($value[self::FLATBOOST] * ($value[self::BOOSTPERCENTAGE]/100));

        //setting the reflection too TOEM
        $this->addressTOEM($tag->target, self::REFLECT, $this->parseTOEM($tag->targetType, $tag->targetOrigin, $tag->targetElement), 'add', $value);
        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'reflect', '+');
    }



    //absorbDamage
    //this method processes the value sent in with the tag and
    //using addressTOEM sets data that is used by the damaging tags to
    //handle absorbtion of damage from the attacker.
    //this sets absorb{TOEM}
    function absorbDamage($tag)
    {
        //processing tags value
        $value = $this->parseValue($tag->value, $tag->effect_level, $tag);

        //error message for if PP was passed in. it has no use here.
        if($value[self::PRINCIPALPERCENTAGE] != false && $this->debugging)
            $GLOBALS['DebugTool']->push($value, 'value set is not compatable with absorbDamage. principal percentage is not avaliable here. value: ', __METHOD__, __FILE__, __LINE__);

        //calculating the value of the reflection
        $value = $value[self::FLATBOOST] + ($value[self::FLATBOOST] * ($value[self::BOOSTPERCENTAGE]/100));

        //setting the reflection too TOEM
        $this->addressTOEM($tag->target, self::ABSORB, $this->parseTOEM($tag->targetType, $tag->targetOrigin, $tag->targetElement), 'add', $value);
        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'absorb', '+');
    }


    //this method sets immunity flags
    //uses parseTOEM and addressTOEM
    //sets immunityTOEM
    public function immunity($tag)
    {
        //getting TOEM for this tag
        $TOEM = $this->parseTOEM($tag->targetType, $tag->targetOrigin, $tag->targetElement);

        //if target immunity is not an array
        if(!is_array($tag->targetImmunity))
        {
            //if true set target immunity to all immunities
            if($tag->targetImmunity === true)
                $tag->targetImmunity = $this->ALLIMMUNITY;
            else
                $tag->targetImmunity = array($tag->targetImmunity);
        }

        //for each immunity targeted
        $M = array();
        foreach($tag->targetImmunity as $immunity)
        {
            //if immunity is not a valid type
            if( $immunity != self::ALL && !in_array($immunity, $this->ALLIMMUNITY))
                $GLOBALS['DebugTool']->push($tag->targetImmunity, 'in correct field targetImmunity in tag. immunity not applied. targetImmunity cant be:'.$immunity, __METHOD__, __FILE__, __LINE__);

            //if immunity is value add it to M
            else
                $M[] = $immunity;
        }

        //if M is not empty
        if(count($M) > 0)
        {
            $TOEM[] = $M; // add M to TOEM

            $this->addressTOEM($tag->target, self::IMMUNITY, $TOEM, 'set', true); //set the values.
        }
    }


    //\\//\\//\\ healing tags //\\//\\//\\

    //this function is called for the heal tag.
    //it makes sure that the duration on the heal tag is set to 1 then...
    //it calls doHeal to do all the work. doHeal is also used by healOverTime
    function heal($tag)
    {
        if($tag->duration != 1)
        {
            $tag->duration = 1;

            if($this->debugging)
                $GLOBALS['DebugTool']->push('', 'duration on heal must be 1', __METHOD__, __FILE__, __LINE__);
        }

        if(( $tag->targetType === true  || !in_array($tag->targetType, $this->ALLTYPE)) && $tag->statBased)
        {
            $tag->targetType = self::NINJUTSU;
            if($this->debugging)
                $GLOBALS['DebugTool']->push('target type not accepted. set to ninjutsu.', 'bad data in heal tag', __METHOD__, __FILE__, __LINE__);
        }

        if(count($tag->targetGeneral) == 2 && $tag->statBased)
        {
            if( $tag->targetGeneral[0] === true || !in_array($tag->targetGeneral[0], $this->ALLGENERALS))
            {
                $tag->targetGeneral[0] = self::STRENGTH;
                if($this->debugging)
                    $GLOBALS['DebugTool']->push('first target general not accepted. set to strength.', 'bad data in heal tag', __METHOD__, __FILE__, __LINE__);

            }

            if( $tag->targetGeneral[1] === true || !in_array($tag->targetGeneral[1], $this->ALLGENERALS))
            {
                $tag->targetGeneral[1] = self::STRENGTH;
                if($this->debugging)
                    $GLOBALS['DebugTool']->push('second target general not accepted. set to strength.', 'bad data in heal tag', __METHOD__, __FILE__, __LINE__);
            }
        }
        else if($tag->statBased)
        {
            $tag->targetGeneral = array(self::STRENGTH, self::STRENGTH);
            if($this->debugging)
                $GLOBALS['DebugTool']->push('target general must have 2 items', 'bad data in heal tag', __METHOD__, __FILE__, __LINE__);
        }

        if($tag->statBased)
        {
            if(!is_array($tag->targetElement))
                $tag->targetElement = array($tag->targetElement);

            foreach($tag->targetElement as $element_key => $element)
            {
                if( $element === true || !in_array($element, $this->ALLELEMENT))
                {
                    $tag->targetElement[$element_key] = self::NONE;
                    if($this->debugging)
                        $GLOBALS['DebugTool']->push('bad element data was set to none', 'bad data in heal tag', __METHOD__, __FILE__, __LINE__);
                }
            }
        }

        $this->doHeal($tag);
    }


    //this function is called for the heal over time tag.
    //it makes sure that the duration on the heal tag is set to 2 ore more then...
    //it calls doHeal to do all the work. doHeal is also used by heal
    function healOverTime($tag)
    {
        if(($tag->duration < 2 && $tag->duration != 0) || $tag->duration === true)
        {
            $tag->duration = 2;

            if($this->debugging)
                $GLOBALS['DebugTool']->push('', 'duration on heal must be greater than 1', __METHOD__, __FILE__, __LINE__);
        }

        if( ($tag->targetType === true || !in_array($tag->targetType, $this->ALLTYPE)) && $tag->statBased)
        {
            if($this->debugging)
                $GLOBALS['DebugTool']->push($tag->targetType, 'bad data in heal over time tag. target type not accepted. set to ninjutsu.', __METHOD__, __FILE__, __LINE__);
            $tag->targetType = self::NINJUTSU;
        }

        if(count($tag->targetGeneral) == 2 && $tag->statBased)
        {
            if($tag->targetGeneral[0] === true || !in_array($tag->targetGeneral[0], $this->ALLGENERALS))
            {
                if($this->debugging)
                    $GLOBALS['DebugTool']->push($tag->targetGeneral[0], 'bad data in heal over time tag: '.'first target general not accepted. set to strength.', __METHOD__, __FILE__, __LINE__);
                $tag->targetGeneral[0] = self::STRENGTH;

            }

            if($tag->targetGeneral[1] === true || !in_array($tag->targetGeneral[1], $this->ALLGENERALS))
            {
                if($this->debugging)
                    $GLOBALS['DebugTool']->push($tag->targetGeneral[1], 'bad data in heal over time tag: '.'second target general not accepted. set to strength.', __METHOD__, __FILE__, __LINE__);
                $tag->targetGeneral[1] = self::STRENGTH;
            }
        }
        else if($tag->statBased)
        {
            if($this->debugging)
                $GLOBALS['DebugTool']->push($tag->targetGeneral, 'bad data in heal over time tag: '.'target general must have 2 items', __METHOD__, __FILE__, __LINE__);
            $tag->targetGeneral = array(self::STRENGTH, self::STRENGTH);
        }

        if($tag->statBased)
        {
            if(!is_array($tag->targetElement))
                $tag->targetElement = array($tag->targetElement);

            foreach($tag->targetElement as $element_key => $element)
            {
                if( $element === true || !in_array($element, $this->ALLELEMENT))
                {
                    if($this->debugging && $element !== true)
                        $GLOBALS['DebugTool']->push($tag->targetElement, 'bad data in heal over time tag. bad element data was set to none', __METHOD__, __FILE__, __LINE__);

                    $tag->targetElement[$element_key] = self::NONE;
                }
            }
        }

        $this->doHeal($tag, true);
    }


    //doHealStatBased is called by heal and healOverTime
    //this heals a user based on the healing equation
    function doHeal($tag, $over_time = false)
    {
        $OM = $this->parseTOEM(false, $tag->origin, false, true); //getting TOEM for get
        $OM[] = $this->ALLVALUETYPE; //adding M to TOEM

        //get heal M's
        $healM = $this->parseValue($tag->value, $tag->effect_level, $tag);

        //heal formula
        if($tag->statBased)
        {
            if($tag->targetType == 'highest')
            {
                $temp_value = $this->users[$tag->owner] [self::OFFENSE.self::TAIJUTSU];
                $temp_type = self::TAIJUTSU;

                if( $temp_value < $this->users[$tag->owner] [self::OFFENSE.self::NINJUTSU] )
                { 
                    $temp_value = $this->users[$tag->owner] [self::OFFENSE.self::NINJUTSU];
                    $temp_type = self::NINJUTSU;
                }

                if( $temp_value < $this->users[$tag->owner] [self::OFFENSE.self::GENJUTSU] )
                { 
                    $temp_value = $this->users[$tag->owner] [self::OFFENSE.self::GENJUTSU];
                    $temp_type = self::GENJUTSU;
                }

                if( $temp_value < $this->users[$tag->owner] [self::OFFENSE.self::BUKIJUTSU] )
                { 
                    $temp_value = $this->users[$tag->owner] [self::OFFENSE.self::BUKIJUTSU];
                    $temp_type = self::BUKIJUTSU;
                }

                $Tag->targetType = $temp_type;
            }

            $TOEM = $this->parseTOEM($tag->targetType, $tag->origin, $tag->targetElement, true);
            $TOEM[] = $this->ALLVALUETYPE;

            $M = array();
            $M[] = $this->ALLVALUETYPE;

            //calc heal
            $jutsu_power = $healM[self::FLATBOOST] +
                          ($healM[self::FLATBOOST] * ($healM[self::BOOSTPERCENTAGE] / 100)) +
                          ($this->users[$tag->target][self::HEALTHMAX] * ($healM[self::PRINCIPALPERCENTAGE] / 100));

            $owners_rank = $this->users[$tag->owner][self::RANK];

            if( !isset($this->users[$tag->owner]['status_effects']['staggered']) || $this->users[$tag->owner]['status_effects']['staggered'] == 0)
            {
                $owners_mastery   = $this->users[$tag->owner] [self::MASTERY]                  + $this->parseM( $this->addressTOEM( $tag->owner,  self::MASTERY,          $TOEM,   'get' ), $this->users[$tag->owner] [self::MASTERY]                  );
                if($owners_mastery < 0) $owners_mastery = 0;
                if($owners_mastery > $this->MASTERYCAP[$owners_rank]) $owners_mastery = $this->MASTERYCAP[$owners_rank];
            }
            else
                $owners_mastery = 0;

            $user_offense     = $this->users[$tag->owner] [self::OFFENSE.$tag->targetType] + $this->parseM( $this->addressTOEM( $tag->owner,  self::OFFENSE,          $TOEM,   'get' ), $this->users[$tag->owner] [self::OFFENSE.$tag->targetType] );
            if($user_offense < 0) $user_offense = 0;

            $user_general_1   = $this->users[$tag->owner] [$tag->targetGeneral[0]]         + $this->parseM( $this->addressTOEM( $tag->owner,  $tag->targetGeneral[0], $M, 'get' ), $this->users[$tag->owner] [$tag->targetGeneral[0]]         );
            if($user_general_1 < 0) $user_general_1 = 0;

            $user_general_2   = $this->users[$tag->owner] [$tag->targetGeneral[1]]         + $this->parseM( $this->addressTOEM( $tag->owner,  $tag->targetGeneral[1], $M, 'get' ), $this->users[$tag->owner] [$tag->targetGeneral[0]]         );
            if($user_general_2 < 0) $user_general_2 = 0;

            $owners_stability = $this->users[$tag->owner] [self::STABILITY]                + $this->parseM( $this->addressTOEM( $tag->owner,  self::STABILITY,        $TOEM,   'get' ), $this->users[$tag->owner] [self::STABILITY]                );
            if($owners_stability < 0) $owners_stability = 0;
            if($owners_stability > $this->STABILITYCAP[$owners_rank]) $owners_stability = $this->STABILITYCAP[$owners_rank];


            //doing calculations
            $mastery_boost = $jutsu_power * ($owners_mastery /$this->MASTERYCAP[$owners_rank]) / $owners_rank;

            $user_offense = $user_offense;

            $user_generals = ($user_general_1*0.7) + ($user_general_2*0.3);

            $jutsu_power_rank_effect = ($jutsu_power+$mastery_boost)*$owners_rank;

            $attack_power = $jutsu_power + $mastery_boost * ($jutsu_power/100);

            $init_heal = ((($user_offense + ($user_generals * 5))*100) + $jutsu_power_rank_effect + $attack_power) / 1300;

            //if($owners_rank < 3)
            //{
            //    $fluxMin = 75;
            //    $fluxMax = 110;
            //}
            //else
            //{
            //    $fluxMin = 75  + ( ( $owners_stability / $this->FLUXIDENTIFIERMIN[$owners_rank] ) / 100);
            //    $fluxMax = 110 - ( ( $owners_stability / $this->FLUXIDENTIFIERMAX[$owners_rank] ) / 100);
            //}
            $fluxMin = 9011;
            $fluxMax = 10579;

            $flux_heal = $init_heal * (( random_int($fluxMin, $fluxMax) )/100);

            //$thang = array('jutsu_power'=>$jutsu_power,
            //    'mastery_boost'=>$mastery_boost,
            //    'user_offense'=>$user_offense,
            //    'user_generals'=>$user_generals,
            //    'jutsu_power_rank_effect'=>$jutsu_power_rank_effect,
            //    'attack_power'=>$attack_power,
            //    'init_heal'=>$init_heal,
            //    'fluxMin'=>$fluxMin,
            //    'fluxMax'=>$fluxMax,
            //    'flux_heal'=>$flux_heal
            //    );
            //$GLOBALS['DebugTool']->push($thang, 'heal stat based data dump', __METHOD__, __FILE__, __LINE__);


            $heal_value = $flux_heal; // final heal value
        }
        else
        {
            $heal_value = $healM[self::FLATBOOST] +
                          ($healM[self::FLATBOOST] * ($healM[self::BOOSTPERCENTAGE] / 100)) +
                          ($this->users[$tag->target][self::HEALTHMAX] * ($healM[self::PRINCIPALPERCENTAGE] / 100));
        }

        //get healOut_value
        $healOut_value = $this->parseM($this->addressTOEM($tag->owner, self::HEALOUT, $OM, 'get'),$heal_value);

        //get healInM
        $healIn_value = $this->parseM($this->addressTOEM($tag->target, self::HEALIN, $OM, 'get'),$heal_value);

        //caclulate final heal value
        $final_heal_value = $heal_value + $healOut_value + $healIn_value;

        //set heal value
        $this->users[$tag->target][self::HEALTH] = round($this->users[$tag->target][self::HEALTH] + $final_heal_value);

        if($over_time)
        {
            $message = $tag->duration.' round';

            if($tag->duration > 1)
                $message .= 's';

            $this->battle_log[$this->turn_counter][$tag->owner]['heal_over_time_delt'] = $final_heal_value;
            $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'heal_over_time', $message);
        }
        else
        {
            $this->battle_log[$this->turn_counter][$tag->owner]['heal_delt'] = $final_heal_value;
            $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'heal', '+');
        }

        if($this->users[$tag->target][self::HEALTH] > $this->users[$tag->target][self::HEALTHMAX])
            $this->users[$tag->target][self::HEALTH] = $this->users[$tag->target][self::HEALTHMAX];
    }



    //effectHealIn changes healInTOM
    //calls applyEffectValue
    //calls parseTOEM
    //all work is done by those methods and applyTOEM
    function effectHealIn($tag)
    {
        $this->applyEffectValue($tag->target,
                                self::HEALIN,
                                $this->parseTOEM(false, $tag->targetOrigin),
                                $tag->value,
                                $tag->effect_level,
                                $tag->owner);
    }



    //effectHealOut changes healOutTOM
    //calls applyEffectValue
    //calls parseTOEM
    //all work is done by those methods and applyTOEM
    function effectHealOut($tag)
    {
        $this->applyEffectValue($tag->target,
                                self::HEALOUT,
                                $this->parseTOEM(false, $tag->targetOrigin),
                                $tag->value,
                                $tag->effect_level,
                                $tag->owner);
    }



    //\\//\\//\\ action tags //\\//\\//\\

    //noRob sets user flat NOROB to true
    function noRob($tag)
    {
        //$this->data[$tag->target][self::NOROB]=true;
        if(is_array($tag->value))
            $this->users[$tag->target]['status_effects'][self::NOROB] = random_int($tag->value[0], $tag->value[1]);

        else
            $this->users[$tag->target]['status_effects'][self::NOROB] = $tag->value;

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'robbing', 'allow');
    }

    //yesRob sets user flat NOROB to false
    function yesRob($tag)
    {
        //$this->data[$tag->target][self::NOROB]=false;
        if($this->users[$tag->target]['status_effects'][self::NOROB] !== -9)
            unset($this->users[$tag->target]['status_effects'][self::NOROB]);

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'robbing', 'prevent');
    }

    //rob takes 4 values from tag->value and uses those to calculate rob chance and rob amount
    function rob($tag)
    {
        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'robbing', 'attempt');

        if(is_array($tag->value) && count($tag->value) == 4)
        {
            //check chance
            $chance = $tag->value[0] + ($tag->value[1] * $tag->effect_level);
            if( random_int(0,100) <= $chance && ( !isset($this->users[$tag->target]['status_effects'][self::NOROB]) || $this->users[$tag->target]['status_effects'][self::NOROB] == 0))
            {
                //get rob percentage and amount
                $rob_percentage = $tag->value[2] + ($tag->value[3] * $tag->effect_level);
                $amount = round($this->users[$tag->target]['money'] * ($rob_percentage/100));

                if($amount > $this->users[$tag->target]['money'])
                    $amount = $this->users[$tag->target]['money'];

                if($amount + $this->users[$tag->owner]['money'] > 200000000)
                    $amount = 200000000 - $this->users[$tag->owner]['money'];

                //take away and give from user
                $this->users[$tag->target]['money'] -= $amount;
                $this->users[$tag->owner]['money'] += $amount;

                //mark update
                if(!isset($this->users[$tag->target]['update']['money']))
                    $this->users[$tag->target]['update']['money'] = 0;

                $this->users[$tag->target]['update']['money'] -= $amount;


                if(!isset($this->users[$tag->owner]['update']['money']))
                    $this->users[$tag->owner]['update']['money'] = 0;

                $this->users[$tag->owner]['update']['money'] += $amount;


                //rob success message
                $this->users[$tag->owner]['actions'][$this->turn_counter]['rob'] = $amount;
            }
            else
            {
                //rob failed message
                $this->users[$tag->owner]['actions'][$this->turn_counter]['rob'] = 'fail';
            }
        }
        else if($this->debugging)
            $GLOBALS['DebugTool']->push('missing entries in value field. must have 4 values.', 'bad data in rob tag', __METHOD__, __FILE__, __LINE__);
    }

    //noFlee sets user flat NOFLEE to true
    function noFlee($tag)
    {
        //$this->data[$tag->target][self::NOFLEE]=true;
        if(is_array($tag->value) && count($tag->value) != 1)
        {
            if($tag->value[0] < $tag->value[1])
            {
                $this->users[$tag->target]['status_effects'][self::NOFLEE] = random_int($tag->value[0], $tag->value[1]);
            }
            else
            {
                $this->users[$tag->target]['status_effects'][self::NOFLEE] = random_int($tag->value[1], $tag->value[0]);
            }
        }
        else if(is_array($tag->value))
        {
            $this->users[$tag->target]['status_effects'][self::NOFLEE] = $tag->value[0];
        }
        else
        {
            $this->users[$tag->target]['status_effects'][self::NOFLEE] = $tag->value;
        }

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'fleeing', 'prevent');
    }

    //yesFlee sets user flat NOFLEE to false
    function yesFlee($tag)
    {
        //$this->data[$tag->target][self::NOFLEE]=false;
        if($this->users[$tag->target]['status_effects'][self::NOFLEE] !== -9)
            unset($this->users[$tag->target]['status_effects'][self::NOFLEE]);

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'fleeing', 'allow');
    }

    //effectFleeChance will modify this users chance to flee.
    function effectFleeChance($tag)
    {
        $this->data[$tag->target]['flee_chance_modifier'] = $tag->value;

        if($tag->value > 0)
            $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'fleeing', '+');
        else if($tag->value < 0)
            $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'fleeing', '-');
        else
            $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'fleeing', '+/-');
    }

    function flee($tag)
    {
        $roll = random_int(1,100);

        $owner_dsr = $this->users[$tag->owner]['DSR'];

        $opponents_dsr = array();

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'fleeing', 'attempt');

        //if everyone is trying to flee, just go ahead and go for it.
        $all_fleeing = true;
        foreach($this->users as $username => $userdata)
        {
            $found = false;
            if( isset($this->battle_log[$this->turn_counter][$username]['effects']) )
            {
                foreach($this->battle_log[$this->turn_counter][$username]['effects'] as $target_name => $effects)
                {
                    if(isset($effects['fleeing']) && ((is_array($effects['fleeing']) && in_array('attempt',$effects['fleeing'])) || $effects['fleeing'] == 'attempt'))
                    {
                        $found = true;
                    }
                }
            }

            if($found === false)
            {
                $all_fleeing = false;
                break;
            }
        }

        $DSR = array();
        $opponent_count = 0;
        foreach($this->users as $temp_user_data)
        {
            if(!isset($DSR[$temp_user_data['team']]))
                $DSR[$temp_user_data['team']] = 0;

            $DSR[$temp_user_data['team']] += $temp_user_data['DSR'];

            if($temp_user_data['team'] != $this->users[$tag->owner]['team'])
                $opponent_count ++;
        }

        $friendlyDSR = $DSR[$this->users[$tag->owner]['team']];

        unset($DSR[$this->users[$tag->owner]['team']]);

        $opponentDSR = max($DSR);

        $friendlyDSR = ($friendlyDSR + $owner_dsr) /2;

        if($friendlyDSR > $opponentDSR)
            $friendlyDSR = $opponentDSR;

        $chance_modifier = 100;

        if(isset($this->data[$tag->target]['flee_chance_modifier']))
            $chance_modifier = $this->data[$tag->target]['flee_chance_modifier'];

        if($tag->statBased === true)
        {
            $chance = (0.05 + ( 0.15 * ($friendlyDSR)/($opponentDSR) )) / (sqrt($opponent_count -1) + 1)*$chance_modifier;
            $GLOBALS['template']->assign("flee_1", '%'.$chance.' =  (0.05 + ( 0.15 * '.($friendlyDSR)/($opponentDSR).' )) / ('.sqrt($opponent_count -1).' + 1)*'.$chance_modifier);
            $GLOBALS['template']->assign("flee_2", 'stat component: ' . ($friendlyDSR)/($opponentDSR) );
            $GLOBALS['template']->assign("flee_3", 'opponent count component: ' . sqrt($opponent_count -1) );
        }
        else
        {
            $chance = $this->parseValue($tag->value, $tag->effect_level, $tag);

            $chance = ($chance[self::FLATBOOST] / 100 +
                      ($chance[self::FLATBOOST] * ($chance[self::BOOSTPERCENTAGE] / 100)) +
                      ( (0.05 + ( 0.15 * ($friendlyDSR)/($opponentDSR) )) / (sqrt($opponent_count -1) + 1) * ($chance[self::PRINCIPALPERCENTAGE] / 100))) * $chance_modifier;
        }

        if( ($roll <= $chance && !isset($this->users[$tag->target]['status_effects'][self::NOFLEE])) || $all_fleeing === true)
        {
            //update battle log
            if( isset($this->users[$tag->owner]['actions'][$this->turn_counter]) )
                $this->users[$tag->owner]['actions'][$this->turn_counter] = array_merge(array('fled'=>true), $this->users[$tag->owner]['actions'][$this->turn_counter]);
            else
                $this->users[$tag->owner]['actions'][$this->turn_counter] = array('fled'=>true);

            //mark user for removal
            $this->users[$tag->owner]['remove'] = true;
            $this->users[$tag->owner]['flee'] = true;
            $this->removeUserFromCombat($tag->owner, 'flee');

            //marking as exiting combat.
            $GLOBALS['Events']->acceptEvent('status', array('new'=>'exiting_combat', 'old'=>$GLOBALS['userdata'][0]['status'] ));

            $query = "UPDATE `users` SET `status` = 'exiting_combat' where `username` = '".$tag->owner."'";

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
        else
        {
            //update the battle log
            if( isset($this->users[$tag->owner]['actions'][$this->turn_counter]) )
                $this->users[$tag->owner]['actions'][$this->turn_counter] = array_merge(array('fled'=>false), $this->users[$tag->owner]['actions'][$this->turn_counter]);
            else
                $this->users[$tag->owner]['actions'][$this->turn_counter] = array('fled'=>false);
        }
    }

    //no stun prevents stunning
    function noStun($tag)
    {
        //$this->data[$tag->target][self::NOSTUN]=true;
        if(is_array($tag->value) && count($tag->value) == 2)
            $this->users[$tag->target]['status_effects'][self::NOSTUN] = random_int($tag->value[0], $tag->value[1]);

        else if(is_array($tag->value))
            $this->users[$tag->target]['status_effects'][self::NOSTUN] = $tag->value[0];

        else
            $this->users[$tag->target]['status_effects'][self::NOSTUN] = $tag->value;

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'stunning', 'prevent');
    }

    //yes stun allows for stunning
    function yesStun($tag)
    {
        //$this->data[$tag->target][self::NOSTUN]=false;
        if($this->users[$tag->target]['status_effects'][self::NOSTUN] !== -9)
            unset($this->users[$tag->target]['status_effects'][self::NOSTUN]);

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'stunning', 'allow');
    }

    //stun sets a flag stunning a user for a duration set by value
    function stun($tag)
    {
        if( !isset($this->users[$tag->target]['status_effects'][self::NOSTUN]) || $this->users[$tag->target]['status_effects'][self::NOSTUN] == 0 )
        {
            if(is_array($tag->value) && count($tag->value) == 2)
                $tag->value = random_int($tag->value[0],$tag->value[1]);

            else if(is_array($tag->value))
                $tag->value = $tag->value[0];

            $this->users[$tag->target]['status_effects']['stunned'] = floor($tag->value + 1);
            $this->users[$tag->owner]['actions'][$this->turn_counter]['stun'] = $tag->target;
        }

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'stunning', 'attempt');
    }

    //no stagger prevents stunning
    function noStagger($tag)
    {
        if(is_array($tag->value) && count($tag->value) == 2)
            $this->users[$tag->target]['status_effects'][self::NOSTAGGER] = random_int($tag->value[0], $tag->value[1]);

        else if (is_array($tag->value))
            $this->users[$tag->target]['status_effects'][self::NOSTAGGER] = $Tag->value[0];

        else
            $this->users[$tag->target]['status_effects'][self::NOSTAGGER] = $tag->value;

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'staggering', 'prevent');
    }

    //yes stagger allows for stunning
    function yesStagger($tag)
    {
        if($this->users[$tag->target]['status_effects'][self::NOSTAGGER] !== -9)
            unset($this->users[$tag->target]['status_effects'][self::NOSTAGGER]);

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'staggering', 'allow');
    }

    //disable sets a flag staggering a user for a duration set by value
    function stagger($tag)
    {
        if( !isset($this->users[$tag->target]['status_effects'][self::NOSTAGGER]) || $this->users[$tag->target]['status_effects'][self::NOSTAGGER] == 0 )
        {
            if(is_array($tag->value) && count($tag->value) == 2)
                $tag->value = random_int($tag->value[0],$tag->value[1]);

            else if (is_array($tag->value))
                $tag->value = $tag->value[0];

            $this->users[$tag->target]['status_effects']['staggered'] = floor($tag->value);
            $this->users[$tag->owner]['actions'][$this->turn_counter]['stagger'] = $tag->target;
        }

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'staggering', 'attempt');
    }

    //no disable prevents stunning
    function noDisable($tag)
    {
        if(is_array($tag->value) && count($tag->value) == 2)
            $this->users[$tag->target]['status_effects'][self::NODISABLE] = random_int($tag->value[0], $tag->value[1]);

        else if(is_array($tag->value))
            $this->users[$tag->target]['status_effects'][self::NODISABLE] = $tag->value[0];

        else
            $this->users[$tag->target]['status_effects'][self::NODISABLE] = $tag->value;

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'disabling', 'prevent');
    }

    //yes disable allows for stunning
    function yesDisable($tag)
    {
        if($this->users[$tag->target]['status_effects'][self::NODISABLE] !== -9)
            unset($this->users[$tag->target]['status_effects'][self::NODISABLE]);

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'disabling', 'allow');
    }

    //disable sets a flag disabling a user for a duration set by value
    function disable($tag)
    {
        if( !isset($this->users[$tag->target]['status_effects'][self::NODISABLE]) || $this->users[$tag->target]['status_effects'][self::NODISABLE] == 0 )
        {
            if(is_array($tag->value) && count($tag->value) == 2)
                $tag->value = random_int($tag->value[0], $tag->value[1]);

            else if(is_array($tag->value))
                $tag->value = $tag->value[0];

            $this->users[$tag->target]['status_effects']['disabled'] = floor($tag->value + 1);
            $this->users[$tag->owner]['actions'][$this->turn_counter]['disable'] = $tag->target;
        }

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'disabling', 'attempt');
    }

    //disarm removes equipment from the target user
    //takes targetEquipment to define what should be removed.
    //bound weapon needs to be added
    function disarm($tag)
    {
        //converting non-array value to array value for foreach loop
        if(!is_array($tag->targetEquipment))
            $tag->targetEquipment = array($tag->targetEquipment);

        //foreach targeted equipment type
        foreach($tag->targetEquipment as $equipmentType)
        {
            //if this is a valid quipment type
            if(in_array($equipmentType, $this->ALLEQUIPMENT))
            {
                $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'disarming', $equipmentType);

                //if the target is all armor
                if($equipmentType == self::ARMOR)
                {
                    foreach($this->users[$tag->target][self::EQUIPMENT] as $item_key => $item)
                        if($item['type'] == 'armor')
                            $this->removeEquipmentById($item_key);
                }

                //if the target is all weapon
                else if($equipmentType == self::WEAPON)
                {
                    foreach($this->users[$tag->target][self::EQUIPMENT] as $item_key => $item)
                        if($item['type'] == 'weapon')
                            $this->removeEquipmentById($item_key);
                }

                //if the target is a specific type of armor
                else
                {
                    foreach($this->users[$tag->target][self::EQUIPMENT] as $item_key => $item)
                        if($item['armor_type'] == $equipmentType)
                            $this->removeEquipmentById($item_key);
                }
            }
            else
                $GLOBALS['DebugTool']->push($equipmentType, 'this is not a valid equipmentType: ', __METHOD__, __FILE__, __LINE__);
        }
    }

    //\\//\\//\\ utility tags //\\//\\//\\



    //clear effects tags and tagsineffect
    //a tag that clears other tags based on:
    //tag names, origins, if the tag is in effect,
    //the polarity of the tags value, tag category.
    function clear($clear_tag)
    {
        //for each tag
        foreach($this->users[$clear_tag->target][self::TAGS] as $tag_key => $tag)
        {
            $value = $this->parseValue($tag->value, $tag->effect_level, $tag);
            $total = 0;
            foreach($value as $thing)
                $total += $thing;

            //big if checks all conditions for clear
            if( $tag->noClear === false &&
                ($clear_tag->targetTag === false || (!is_array($clear_tag->targetTag) && $clear_tag->targetTag == $tag->name) || (is_array($clear_tag->targetTag) && in_array($tag->name, $clear_tag->targetTag)) ) &&
                ($clear_tag->targetOrigin === false || (!is_array($clear_tag->targetOrigin) && $clear_tag->targetOrigin == $tag->origin) || (is_array($clear_tag->targetOrigin) && in_array($tag->origin, $clear_tag->targetOrigin)) ) &&
                ($clear_tag->targetTagCategory === false || false) &&
                ($clear_tag->targetInEffect === 'X' || ($clear_tag->targetInEffect === false && !isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key])) || ($clear_tag->targetInEffect === true && isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key])) ) &&
                ($clear_tag->targetElement === true || $tag->targetElement === true || $clear_tag->targetElement == $tag->targetElement || ( is_array($clear_tag->targetElement) && !is_array($tag->targetElement) && in_array($tag->targetElement, $clear_tag->targetElement) ) || ( is_array($clear_tag->targetElement) && is_array($tag->targetElement) && count($tag->targetElement) == count(array_intersect($tag->targetElement,$clear_tag->targetElement)) ) )&&
                ($clear_tag->targetPolarity === false || ($clear_tag->targetPolarity == '+' && $total >= 0) || ($clear_tag->targetPolarity == '-' && $total < 0))
                )
            {
                //removeing it from the in effect list.
                if(isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key]))
                    unset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key]);

                //removing it from the main list.
                unset($this->users[$tag->target][self::TAGS][$tag_key]);

                unset($this->run_ready_array[$tag->target.$tag_key]);
            }
        }

        $this->addEffectToBattleLog($clear_tag->owner, $clear_tag->target, $clear_tag->age, 'clearing', '+');
    }




    //noClear effects tags and tagsineffect
    //turns the flag noClear on.
    //a tag that clears other tags based on:
    //tag names, origins, if the tag is in effect,
    //the polarity of the tags value, tag category.
    function noClear($noClear_tag)
    {
        //for each tag
        foreach($this->users[$noClear_tag->target][self::TAGS] as $tag_key => $tag)
        {
            $value = $this->parseValue($tag->value, $tag->effect_level, $tag);
            $total = 0;
            foreach($value as $thing)
                $total += $thing;

            //big if checks all conditions for clear
            if( $tag->noClear === false &&
                ($noClear_tag->targetTag === false || (!is_array($noClear_tag->targetTag) && $noClear_tag->targetTag == $tag->name) || (is_array($noClear_tag->targetTag) && in_array($tag->name, $noClear_tag->targetTag)) ) &&
                ($noClear_tag->targetOrigin === false || (!is_array($noClear_tag->targetOrigin) && $noClear_tag->targetOrigin == $tag->origin) || (is_array($noClear_tag->targetOrigin) && in_array($tag->origin, $noClear_tag->targetOrigin)) ) &&
                ($noClear_tag->targetTagCategory === false || false) &&
                ($noClear_tag->targetInEffect === 'X' || ($noClear_tag->targetInEffect === false && !isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key])) || ($noClear_tag->targetInEffect === true && isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key])) ) &&
                ($noClear_tag->targetElement === true || $tag->targetElement === true || $noClear_tag->targetElement == $tag->targetElement || ( is_array($noClear_tag->targetElement) && !is_array($tag->targetElement) && in_array($tag->targetElement, $noClear_tag->targetElement) ) || ( is_array($noClear_tag->targetElement) && is_array($tag->targetElement) && count($tag->targetElement) == count(array_intersect($tag->targetElement,$noClear_tag->targetElement)) ) )&&
                ($noClear_tag->targetPolarity === false || ($noClear_tag->targetPolarity == '+' && $total >= 0) || ($noClear_tag->targetPolarity == '-' && $total < 0))
                )
            {
                //removeing it from the in effect list.
                if(isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key]))
                    $this->users[$tag->target][self::TAGSINEFFECT][$tag_key]->noClear = true;

                //removing it from the main list.
                $this->users[$tag->target][self::TAGS][$tag_key]->noClear = true;

                //updating current running array
                if(isset($this->run_ready_array[$tag->target.$tag_key]->noClear))
                    $this->run_ready_array[$tag->target.$tag_key]->noClear = true;
            }
        }

        $this->addEffectToBattleLog($noClear_tag->owner, $noClear_tag->target, $noClear_tag->age, 'clearing', 'prevent');
    }


    //yesClear effects tags and tagsineffect
    //turns the flag noClear off.
    //a tag that clears other tags based on:
    //tag names, origins, if the tag is in effect,
    //the polarity of the tags value, tag category.
    function yesClear($yesClear_tag)
    {
        //for each tag
        foreach($this->users[$yesClear_tag->target][self::TAGS] as $tag_key => $tag)
        {
            $value = $this->parseValue($tag->value, $tag->effect_level, $tag);
            $total = 0;
            foreach($value as $thing)
                $total += $thing;

            //big if checks all conditions for clear
            if( $tag->noClear === true &&
                ($yesClear_tag->targetTag === false || (!is_array($yesClear_tag->targetTag) && $yesClear_tag->targetTag == $tag->name) || (is_array($yesClear_tag->targetTag) && in_array($tag->name, $yesClear_tag->targetTag)) ) &&
                ($yesClear_tag->targetOrigin === false || (!is_array($yesClear_tag->targetOrigin) && $yesClear_tag->targetOrigin == $tag->origin) || (is_array($yesClear_tag->targetOrigin) && in_array($tag->origin, $yesClear_tag->targetOrigin)) ) &&
                ($yesClear_tag->targetTagCategory === false || false) &&
                ($yesClear_tag->targetInEffect === 'X' || ($yesClear_tag->targetInEffect === false && !isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key])) || ($yesClear_tag->targetInEffect === true && isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key])) ) &&
                ($yesClear_tag->targetElement === true || $tag->targetElement === true || $yesClear_tag->targetElement == $tag->targetElement || ( is_array($yesClear_tag->targetElement) && !is_array($tag->targetElement) && in_array($tag->targetElement, $yesClear_tag->targetElement) ) || ( is_array($yesClear_tag->targetElement) && is_array($tag->targetElement) && count($tag->targetElement) == count(array_intersect($tag->targetElement,$yesClear_tag->targetElement)) ) )&&
                ($yesClear_tag->targetPolarity === false || ($yesClear_tag->targetPolarity == '+' && $total >= 0) || ($yesClear_tag->targetPolarity == '-' && $total < 0))
                )
            {
                //removeing it from the in effect list.
                if(isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key]))
                    $this->users[$tag->target][self::TAGSINEFFECT][$tag_key]->noClear = false;

                //removing it from the main list.
                $this->users[$tag->target][self::TAGS][$tag_key]->noClear = false;

                if(isset($this->run_ready_array[$tag->target.$tag_key]->noClear))
                    $this->run_ready_array[$tag->target.$tag_key]->noClear = false;
            }
        }

        $this->addEffectToBattleLog($yesClear_tag->owner, $yesClear_tag->target, $yesClear_tag->age, 'clearing', 'allow');
    }



    //delay effects tags and tagsineffect
    //a tag that sets the delay field of tags
    //tag names, origins, if the tag is in effect,
    //the polarity of the tags value, tag category.
    function delay($delay_tag)
    {
        if(is_array($delay_tag->value))
            $delay_tag->value = $delay_tag->value[0];

        if(!is_int($delay_tag->value) && $delay_tag->value !== true)
            if($this->debugging)
            {
                $GLOBALS['DebugTool']->push($tag->value, 'in correct field value in delay tag. value set to 1. value cant be:'.$immunity, __METHOD__, __FILE__, __LINE__);
                $delay_tag->value = 1;
            }
            else
                $delay_tag->value = 1;

        //for each tag
        foreach($this->users[$delay_tag->target][self::TAGS] as $tag_key => $tag)
        {
            $value = $this->parseValue($tag->value, $tag->effect_level, $tag);
            $total = 0;
            foreach($value as $thing)
                $total += $thing;

            //big if checks all conditions for clear
            if( $tag->noDelay === false &&
                ($delay_tag->targetTag === false || (!is_array($delay_tag->targetTag) && $delay_tag->targetTag == $tag->name) || (is_array($delay_tag->targetTag) && in_array($tag->name, $delay_tag->targetTag)) ) &&
                ($delay_tag->targetOrigin === false || (!is_array($delay_tag->targetOrigin) && $delay_tag->targetOrigin == $tag->origin) || (is_array($delay_tag->targetOrigin) && in_array($tag->origin, $delay_tag->targetOrigin)) ) &&
                ($delay_tag->targetTagCategory === false || false) &&
                ($delay_tag->targetInEffect === 'X' || ($delay_tag->targetInEffect === false && !isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key])) || ($delay_tag->targetInEffect === true && isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key])) ) &&
                ($delay_tag->targetElement === true || $tag->targetElement === true || $delay_tag->targetElement == $tag->targetElement || ( is_array($delay_tag->targetElement) && !is_array($tag->targetElement) && in_array($tag->targetElement, $delay_tag->targetElement) ) ) &&
                ($delay_tag->targetPolarity === false || ($delay_tag->targetPolarity == '+' && $total >= 0) || ($delay_tag->targetPolarity == '-' && $total < 0))
                )
            {
                //if tag being delayed has not gone remove it from in effect.
                if(array_search($tag_key, array_keys($this->run_ready_array)) > array_search($delay_tag->key, array_keys($this->run_ready_array)))
                    //removeing it from the in effect list.
                    if(isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key]))
                        unset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key]);
                //if tag being delayed has already gone age it.
                else
                    if(isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key]))
                        $this->users[$tag->target][self::TAGS][$tag_key]->age += 1;


                //unset running array
                unset($this->run_ready_array[$tag->target.$tag_key]);

                //adding to delay (the 1 + accounts for the automatic reduction of delay at the end of this turn)
                $this->users[$tag->target][self::TAGS][$tag_key]->delay += 1 + $delay_tag->value;

            }
        }

        $this->addEffectToBattleLog($delay_tag->owner, $delay_tag->target, $delay_tag->age, 'delaying', 'attempt');
    }



    //noDelay effects tags and tagsineffect
    //turns the flag noDelay on.
    //a tag that clears other tags based on:
    //tag names, origins, if the tag is in effect,
    //the polarity of the tags value, tag category.
    function noDelay($noDelay_tag)
    {
        //for each tag
        foreach($this->users[$noDelay_tag->target][self::TAGS] as $tag_key => $tag)
        {
            $value = $this->parseValue($tag->value, $tag->effect_level, $tag);
            $total = 0;
            foreach($value as $thing)
                $total += $thing;

            //big if checks all conditions for clear
            if( $tag->noDelay === false &&
                ($noDelay_tag->targetTag === false || (!is_array($noDelay_tag->targetTag) && $noDelay_tag->targetTag == $tag->name) || (is_array($noDelay_tag->targetTag) && in_array($tag->name, $noDelay_tag->targetTag)) ) &&
                ($noDelay_tag->targetOrigin === false || (!is_array($noDelay_tag->targetOrigin) && $noDelay_tag->targetOrigin == $tag->origin) || (is_array($noDelay_tag->targetOrigin) && in_array($tag->origin, $noDelay_tag->targetOrigin)) ) &&
                ($noDelay_tag->targetTagCategory === false || false) &&
                ($noDelay_tag->targetInEffect === 'X' || ($noDelay_tag->targetInEffect === false && !isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key])) || ($noDelay_tag->targetInEffect === true && isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key])) ) &&
                ($noDelay_tag->targetElement === true || $tag->targetElement === true || $noDelay_tag->targetElement == $tag->targetElement || ( is_array($noDelay_tag->targetElement) && !is_array($tag->targetElement) && in_array($tag->targetElement, $noDelay_tag->targetElement) ) ) &&
                ($noDelay_tag->targetPolarity === false || ($noDelay_tag->targetPolarity == '+' && $total >= 0) || ($noDelay_tag->targetPolarity == '-' && $total < 0))
                )
            {
                //removeing it from the in effect list.
                if(isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key]))
                    $this->users[$tag->target][self::TAGSINEFFECT][$tag_key]->noDelay = true;

                //removing it from the main list.
                $this->users[$tag->target][self::TAGS][$tag_key]->noDelay = true;

                if(isset($this->run_ready_array[$tag->target.$tag_key]->noDelay))
                    $this->run_ready_array[$tag->target.$tag_key]->noDelay = true;
            }
        }

        $this->addEffectToBattleLog($noDelay_tag->owner, $noDelay_tag->target, $noDelay_tag->age, 'delaying', 'prevent');
    }


    //yesDelay effects tags and tagsineffect
    //turns the flag noDelay off.
    //a tag that clears other tags based on:
    //tag names, origins, if the tag is in effect,
    //the polarity of the tags value, tag category.
    function yesDelay($yesDelay_tag)
    {
        //for each tag
        foreach($this->users[$yesDelay_tag->target][self::TAGS] as $tag_key => $tag)
        {
            $value = $this->parseValue($tag->value, $tag->effect_level, $tag);
            $total = 0;
            foreach($value as $thing)
                $total += $thing;

            //big if checks all conditions for clear
            if( $tag->noDelay === true &&
                ($yesDelay_tag->targetTag === false || (!is_array($yesDelay_tag->targetTag) && $yesDelay_tag->targetTag == $tag->name) || (is_array($yesDelay_tag->targetTag) && in_array($tag->name, $yesDelay_tag->targetTag)) ) &&
                ($yesDelay_tag->targetOrigin === false || (!is_array($yesDelay_tag->targetOrigin) && $yesDelay_tag->targetOrigin == $tag->origin) || (is_array($yesDelay_tag->targetOrigin) && in_array($tag->origin, $yesDelay_tag->targetOrigin)) ) &&
                ($yesDelay_tag->targetTagCategory === false || false) &&
                ($yesDelay_tag->targetInEffect === 'X' || ($yesDelay_tag->targetInEffect === false && !isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key])) || ($yesDelay_tag->targetInEffect === true && isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key])) ) &&
                ($yesDelay_tag->targetElement === true || $tag->targetElement === true || $yesDelay_tag->targetElement == $tag->targetElement || ( is_array($yesDelay_tag->targetElement) && !is_array($tag->targetElement) && in_array($tag->targetElement, $yesDelay_tag->targetElement) ) ) &&
                ($yesDelay_tag->targetPolarity === false || ($yesDelay_tag->targetPolarity == '+' && $total >= 0) || ($yesDelay_tag->targetPolarity == '-' && $total < 0))
                )
            {
                //removeing it from the in effect list.
                if(isset($this->users[$tag->target][self::TAGSINEFFECT][$tag_key]))
                    $this->users[$tag->target][self::TAGSINEFFECT][$tag_key]->noDelay = false;

                //removing it from the main list.
                $this->users[$tag->target][self::TAGS][$tag_key]->noDelay = false;

                if(isset($this->run_ready_array[$tag->target.$tag_key]->noDelay))
                    $this->run_ready_array[$tag->target.$tag_key]->noDelay = false;
            }
        }

        $this->addEffectToBattleLog($yesDelay_tag->owner, $yesDelay_tag->target, $yesDelay_tag->age, 'delaying', 'allow');
    }



    //noOneHitKill sets user flat NOONEHITKILL to true
    function noOneHitKill($tag)
    {
        //$this->data[$tag->target][self::NOONEHITKILL]=true;
        if(is_array($tag->value))
            $this->users[$tag->target]['status_effects'][self::NOONEHITKILL] = random_int($tag->value[0], $tag->value[1]);

        else
            $this->users[$tag->target]['status_effects'][self::NOONEHITKILL] = $tag->value;

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'KO', 'prevent');
    }

    //noOneHitKill sets user flat NOONEHITKILL to true
    function yesOneHitKill($tag)
    {
        //$this->data[$tag->target][self::NOONEHITKILL]=false;
        if($this->users[$tag->target]['status_effects'][self::NOONEHITKILL] !== -9)
            unset($this->users[$tag->target]['status_effects'][self::NOONEHITKILL]);

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'KO', 'allow');
    }

    //oneHitKill kills a user in one hit
    function oneHitKill($tag)
    {
        if( !isset($this->users[$tag->target]['status_effects'][self::NOONEHITKILL]) || $this->users[$tag->target]['status_effects'][self::NOONEHITKILL] == 0 )
        {
            //getting "damage" done and setting health to zero
            $damage = $this->users[$tag->target][self::HEALTH];
            $this->users[$tag->target][self::HEALTH] = 0;

            $this->users[$tag->target]['remove'] = true;
            $this->removeUserFromCombat($tag->target, false);

            if(!isset($this->users[$tag->target]['ai']))
                $GLOBALS['Events']->acceptEvent('user_death', array('data'=>$this->users[$tag->target]['uid'], 'context'=>$this->users[$tag->target]['uid'], 'count'=>1 ));
            else
                $GLOBALS['Events']->acceptEvent('ai_death', array('data'=>$this->users[$tag->target]['aid'], 'context'=>$this->users[$tag->target]['aid'], 'count'=>1 ));

            $this->battle_log[$this->turn_counter][$tag->owner]['killed'] = $tag->target;
            $this->battle_log[$this->turn_counter][$tag->target]['died'] = $tag->owner;

            if(!isset($this->battle_log[$this->turn_counter][$tag->owner]['damage_delt']))
                $this->battle_log[$this->turn_counter][$tag->owner]['damage_delt'] = array();

            $this->battle_log[$this->turn_counter][$tag->owner]['damage_delt'][] = array('amount' => round($damage), 'type' => '', 'aoe'=>$tag->resultOfAoe, 'oneHitKill'=>true);
            $this->battle_log[$this->turn_counter][$tag->owner]['oneHitKill'] = true;


            if(!isset($this->users[$tag->owner]['ai']) || $this->users[$tag->owner]['ai'] !== true)
            {
                if(!isset($this->users[$tag->owner]['update']['exp']))
                    $this->users[$tag->owner]['update']['exp'] = 0;

                $this->users[$tag->owner]['update']['exp'] += $this->users[$tag->target]['rank'] * 150 - $this->users[$tag->owner]['rank'] * 50;

                if(isset($this->users[$tag->target]['ai']) || $this->users[$tag->target]['ai'] !== true)
                {
                    if(!isset($this->users[$tag->owner]['update']['pvp_exp']))
                            $this->users[$tag->owner]['update']['pvp_exp'] = array();

                    if(isset($this->users[$tag->target]['update']['starting_dsr']))
                        $this->users[$tag->owner]['update']['pvp_exp'][] = ((((($this->users[$tag->target]['update']['starting_dsr']/$this->users[$tag->owner]['update']['starting_dsr']) - 1 ) * 3 ) + 1) * ( 4 / 5 ) ) * 10;
                }

                $this->users[$tag->owner]['update']['users_killed'][] = $tag->target;
            }

            foreach($this->users as $username => $userdata)
            {
                if($userdata['team'] == $this->users[$tag->owner]['team'] && $username != $tag->owner && (!isset($this->users[$username]['ai']) || $this->users[$username]['ai'] !== true))
                {
                    if(!isset($this->users[$username]['update']['exp']))
                        $this->users[$username]['update']['exp'] = ($userdata['rank'] * 150 - $this->users[$tag->owner]['rank'] * 50) * 0.5;
                    else
                        $this->users[$username]['update']['exp'] += ($userdata['rank'] * 150 - $this->users[$tag->owner]['rank'] * 50) * 0.5;

                    if(isset($this->users[$tag->target]['ai']) || $this->users[$tag->target]['ai'] !== true)
                    {
                        if(!isset($this->users[$username]['update']['pvp_exp']))
                            $this->users[$username]['update']['pvp_exp'] = array();

                        if(isset($this->users[$tag->target]['update']['starting_dsr']))
                            $this->users[$username]['update']['pvp_exp'][] = ((((($this->users[$tag->target]['update']['starting_dsr']/$this->users[$username]['update']['starting_dsr']) - 1 ) * 3 ) + 1) * ( 4 / 5 ) ) * 5;
                    }
                }

                if( $this->users[$tag->owner]['team'] == $userdata['team'])
                    if( isset($userdata['bounty_hunter']) && $userdata['bounty_hunter'] !== false && $userdata['bounty_hunter'] == $tag->target)
                        $this->users[$username]['update']['bounty_hunter'] = array('target'=>$tag->target, 'target_rank'=>$this->users[$username]['rank']);
            }
        }

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'KO', 'attempt');
    }

    function summon($tag)
    {
        //checking to see if tag is valid
        if($tag->aiName === true && $tag->aiId === true)
            if($this->debugging)
                $GLOBALS['DebugTool']->push('', 'either the field aiName or aiId needs to be set for this tag to work.', __METHOD__, __FILE__, __LINE__);
                
            else { return; }    
        else
        {
            //loading ai
            if($tag->aiName !== true)
                $ais = $this->addAI($tag->aiName,$this->users[$tag->target]['team'], true);

            else
                $ais = $this->addAI($tag->aiId,$this->users[$tag->target]['team']);

            //getting value and converting it to a percentage 0.00-1.00 format
            $value = $this->parseValue($tag->value, $tag->effect_level, $tag);
            $value = $value['FB']/100;

            //if value is positive then copy stats.
            if($value > 0)
            {
                $copyStatsTag = $this->parseTags('copyStats:(value>'.$value.';)');
                $copyStatsTag[0]->owner = $ais[0];
                $copyStatsTag[0]->target = $tag->target;
                $this->copyStats($copyStatsTag[0]);
                $this->users[$ais[0]]['health'] = $this->users[$ais[0]]['healthMax'];

                if(!isset($this->users[$ais[0]]['ai']) || $this->users[$ais[0]]['ai'] !== true)
                {
                    $this->users[$ais[0]]['chakra'] = $this->users[$ais[0]]['chakraMax'];
                    $this->users[$ais[0]]['stamina'] = $this->users[$ais[0]]['staminaMax'];
                }

                $this->updateDR_SR($ais[0]);
                $this->users[$ais[0]]['DSR'] = $this->findDSR($ais[0]);
            }

            if($tag->persistAfterDeath === false)
            {
                if(!isset($this->users[$tag->owner]['summons']))
                    $this->users[$tag->owner]['summons'] = array();

                $this->users[$tag->owner]['summons'][$ais[0]] = $ais[0];
                $this->users[$ais[0]]['summoned'] = $tag->owner;
            }
        }

        $this->addEffectToBattleLog($tag->owner, $tag->target, $tag->age, 'summon', 'attempt');
    }

    public function getHighestTypeForEffectTag($tag)
    {
        if($tag->targetType === 'highest')
        {
            $specialization = explode(':',$this->users[$tag->target][self::SPECIALIZATION]);

            if(isset($specialization[1]) && $specialization[1] == 0)
            {

                $temp_types = array( self::NINJUTSU => $this->users[$tag->target][self::OFFENSE.self::NINJUTSU],
                                     self::GENJUTSU => $this->users[$tag->target][self::OFFENSE.self::GENJUTSU],
                                     self::TAIJUTSU => $this->users[$tag->target][self::OFFENSE.self::TAIJUTSU],
                                     self::BUKIJUTSU => $this->users[$tag->target][self::OFFENSE.self::BUKIJUTSU]);

                $max = max($temp_types);
                $options = array();
                foreach($temp_types as $type => $value)
                {
                    if($value == $max)
                        $options[] = $type;
                }

                $tag->targetType = $options[random_int(0,(count($options) - 1))];
            }
            else
            {
                if($specialization[0] == '0')
                {
                    $temp_types = array( self::NINJUTSU => $this->users[$tag->target][self::OFFENSE.self::NINJUTSU],
                                     self::GENJUTSU => $this->users[$tag->target][self::OFFENSE.self::GENJUTSU],
                                     self::TAIJUTSU => $this->users[$tag->target][self::OFFENSE.self::TAIJUTSU],
                                     self::BUKIJUTSU => $this->users[$tag->target][self::OFFENSE.self::BUKIJUTSU]);

                    $max = max($temp_types);
                    $options = array();
                    foreach($temp_types as $type => $value)
                    {
                        if($value == $max)
                            $options[] = $type;
                    }

                    $tag->targetType = $options[random_int(0,(count($options) - 1))];
                }
                else
                    if($specialization[0] == 'W')
                        $tag->targetType = 'B';
                    else
                        $tag->targetType = $specialization[0];
            }
        }

        return $tag->targetType;
    }

    //_____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____ _____
    //---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---   ---



}