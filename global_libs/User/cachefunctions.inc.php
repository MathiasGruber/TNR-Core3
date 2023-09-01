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

require_once(Data::$absSvrPath . '/libs/Battle/BattleStarter.php');

class cacheWrapper {

    public function __construct() {

        // Memcache or memcached
        $this->type = class_exists('Memcached') ? "Memcached" : "Memcache";

        // Instantiate
        switch ($this->type) {
            case "Memcached": $this->cache = new Memcached();
                break;
            case "Memcache": $this->cache = new Memcache();
                break;
        }

        // If memcached, then no COMPRESSED exists
        //if ($this->type == "Memcached") {
        //    define("MEMCACHE_COMPRESSED", "empty");
        //} 
    }

    public function getResultCode()
    {
        return $this->cache->getResultCode();
    }
    
    public function getResultMessage()
    {
        return $this->cache->getResultMessage();
    }

    public function set($key, $value, $flag, $expire) {

        // Different depending on type
        try
        {
            switch ($this->type) {
                case "Memcached": $this->cache->set($key, $value, $expire);
                    break;
                case "Memcache": $this->cache->set($key, $value, $flag, $expire);
                    break;
            }
        }
        catch (exception $e)
        {
            error_log($e->getMessage());
            throw new Exception('cache set error.');
        }

        // Debug: print cache status
        // echo"<br> Set ".$key." - ".$this->cache->getResultCode();
    }

    public function get($key) {

        // Get the data to return
        try
        {
            $return = $this->cache->get($key);
        }
        catch (exception $e)
        {
            error_log($e->getMessage());
            throw new Exception('cache get error.');
        }

        // Debug: print cache status
        // echo"<br> Set ".$key." - ".$this->cache->getResultCode();
        // Return info
        return $return;
    }

    public function addServer($host, $port) {
        return $this->cache->addServer($host, $port);
    }
    
    public function getStats() {
        return $this->cache->getStats();
    }

    public function close() {
        return $this->cache->close();
    }

    public function flush() {
        return $this->cache->flush();
    }
    
    public function delete($key) {
        return $this->cache->delete($key);
    }
}

abstract class cachefunctions {

    private static $__pvpTypes = array('spar', 'combat', 'kage', 'territory');
    private static $__pvpCombatLogLimit = 300;
    private static $__nonPvpCombatLogLimit = 125;
    private static $__missionLogLimit = 250;
    private static $__travelLogLimit = 100;

    //  Create Memcache Connection
    public static function hook_up_memcache($id = 1, $force = false) {
        try {
            if(isset($_SESSION['uid']) && $_SESSION['uid'] == '3819'){
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);        
                error_reporting(E_ALL);
            }

            if( (!isset($_SERVER['SERVER_NAME']) || $_SERVER['SERVER_NAME'] == 'localhost') && !$force)
            {
                $GLOBALS['memOn'] = false;
            }
            else
            {
                $GLOBALS['memOn'] = true;
                $GLOBALS['cache'] = new cacheWrapper;
                if ($id === 1) {
                    if (!$GLOBALS['cache']->addServer(MEMCACHE_HOST, MEMCACHE_PORT)) {
                        if($_SESSION['uid'] == '3819'){
                            echo"TEST: ".MEMCACHE_HOST." - ".MEMCACHE_PORT."<br>";
                            echo"Failed to connect to ".$GLOBALS['cache']->type."<br>";
                            echo"Result code: ". $GLOBALS['cache']->getResultCode()." - ".$GLOBALS['cache']->getResultMessage();
                        }                    
                        $GLOBALS['memOn'] = false;
                    }
                }
            }
        } catch (Exception $e) {
            $GLOBALS['memOn'] = false;
        }
    }

    // Function for logging returned object sizes (used for debugging lag)
    public static function logObjectSizes($key, $data) {
        if (Data::$debugObjectSizes == true) {
            $size = strlen(serialize($data));
            if ($size > Data::$objectSizeLimit) {
                $id = isset($_GET['id']) ? $_GET['id'] : 0;
                $GLOBALS['database']->execute_query("
                        INSERT INTO `log_tempObjectLogger` 
                            (`name`, `objectSize`, `time`, `pageID`,`type`) 
                        VALUES 
                            ('" . $key . "', " . $size . ", " . time() . ", " . $id . ", 'CacheVariable');");
            }
        }
    }

    // Close the Cache
    public static function close_down_memcache() {
        $GLOBALS['cache']->close();
    }

    // Flush the Cache
    public static function flushCache() {
        $GLOBALS['cache']->flush();
    }

    // Generic Static Queries Function
    public static function obtainStaticQuery($request) {
        switch ($request) {
            case('allMenus'): {
                    return $GLOBALS['database']->fetch_data('SELECT * FROM `pages` WHERE `pages`.`require_login` = "yes" ORDER BY `pages`.`id` ASC');
                } break;
            case('latestChanges'): {
                    return $GLOBALS['database']->fetch_data("SELECT `log_changeLog`.* FROM `log_changeLog` ORDER BY `log_changeLog`.`time` DESC LIMIT 5");
                } break;
            case('latestBlue'): {
                    return $GLOBALS['database']->fetch_data("SELECT `blueMessages`.* FROM `blueMessages` ORDER BY `blueMessages`.`time` DESC LIMIT 3");
                } break;
            case('latestNews'): {
                    return $GLOBALS['database']->fetch_data('SELECT `news`.`id`, `news`.`title`, `news`.`category`, `news`.`time`, `news`.`posted_by` 
                        FROM `news` WHERE `news`.`id` > 0 ORDER BY `news`.`time` DESC LIMIT 5');
                } break;
            case('latestNewsItem'): {
                    return $GLOBALS['database']->fetch_data('SELECT `news`.* FROM `news` WHERE `news`.`id` > 0 ORDER BY `news`.`time` DESC LIMIT 1');
                } break;
            case('GameVersion'): {
                    return $GLOBALS['database']->fetch_data('SELECT `log_changeLog`.* FROM `log_changeLog` WHERE id > 0 ORDER BY time DESC LIMIT 1');
                } break;
            case('allGlobalEvents'): {
                    return $GLOBALS['database']->fetch_data('SELECT `global_events`.* FROM `global_events` ORDER BY `global_events`.`id`');
                } break;
            case('topPlayers'): {
                    return $GLOBALS['database']->fetch_data('SELECT `users`.`id`, `users`.`username`, `users_statistics`.`pvp_experience`, `users_statistics`.`rank` FROM `users` 
                        INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`) ORDER BY `users_statistics`.`pvp_experience` DESC LIMIT 5');
                } break;
            case('allAlliances'): {
                    return $GLOBALS['database']->fetch_data('SELECT `village_structures`.*, `alliances`.* FROM `alliances`
                        INNER JOIN `village_structures` ON (`village_structures`.`name` = `alliances`.`village`) LIMIT ' . count(array_keys(Data::$VILLAGES)));
                } break;
            case('allMissionCrimes'): {
                    return $GLOBALS['database']->fetch_data('SELECT `tasksAndQuests`.`name`, `tasksAndQuests`.`id`, `tasksAndQuests`.`type`, 
                        `tasksAndQuests`.`tnrAchievment`, `tasksAndQuests`.`facebookAchievment`, `tasksAndQuests`.`requirements`, `tasksAndQuests`.`restrictions`, 
                        `tasksAndQuests`.`levelReq`, `tasksAndQuests`.`levelMax`, `tasksAndQuests`.`rewards`, `tasksAndQuests`.`locationReq`, `tasksAndQuests`.`questTime`, 
                        `tasksAndQuests`.`questChance`,`tasksAndQuests`.`start_date`,`tasksAndQuests`.`end_date`, `tasksAndQuests`.`questRepeatable`, `tasksAndQuests`.`failedRepeatable`
                        FROM `tasksAndQuests` WHERE `tasksAndQuests`.`id` > 0 AND `tasksAndQuests`.`type` IN ("mission_d", "mission_c", "mission_b", 
                            "mission_a", "mission_s", "crime_c", "crime_b", "crime_a") ORDER BY `tasksAndQuests`.`levelReq` ASC');
                } break;
            case('allTasks'): {
                    return $GLOBALS['database']->fetch_data('SELECT `tasksAndQuests`.`name`, `tasksAndQuests`.`id`, `tasksAndQuests`.`type`, 
                        `tasksAndQuests`.`tnrAchievment`, `tasksAndQuests`.`facebookAchievment`, `tasksAndQuests`.`requirements`, `tasksAndQuests`.`simpleGuide`, 
                        `tasksAndQuests`.`restrictions`, `tasksAndQuests`.`levelReq`, `tasksAndQuests`.`levelMax`, `tasksAndQuests`.`rewards`, `tasksAndQuests`.`locationReq`, 
                        `tasksAndQuests`.`questTime`, `tasksAndQuests`.`questChance`, `tasksAndQuests`.`start_date`, `tasksAndQuests`.`end_date`, `tasksAndQuests`.`questRepeatable`, `tasksAndQuests`.`failedRepeatable`
                        FROM `tasksAndQuests` WHERE `tasksAndQuests`.`id` > 0 ORDER BY `tasksAndQuests`.`levelReq` ASC');
                } break;
            case('allResources'): {
                    return $GLOBALS['database']->fetch_data('SELECT `resourceMap`.* FROM `resourceMap` ORDER BY `resourceMap`.`id` ASC');
                } break;
            case('craftingItems'): {
                    return $GLOBALS['database']->fetch_data('SELECT 
                        `id`,`name`,`craft_stack`,`craftable`,
                        `professionRestriction`, `village_restriction`,
                        `profession_level`, `craft_recipe`,
                        `craftProcessMinutes`,`price`,`type`
                    FROM `items` WHERE `items`.`id` > 0 ORDER BY `items`.`profession_level` ASC');
                } break;
            case('allEventQuests'): {
                    return $GLOBALS['database']->fetch_data('SELECT `tasksAndQuests`.`locationReq`, `tasksAndQuests`.`restrictions`, `tasksAndQuests`.`questTime`, 
                        `tasksAndQuests`.`questChance`, `tasksAndQuests`.`start_date`, `tasksAndQuests`.`end_date`, `tasksAndQuests`.`questRepeatable`, 
                        `tasksAndQuests`.`failedRepeatable`, `tasksAndQuests`.`id`, `tasksAndQuests`.`name`, 
                        `tasksAndQuests`.`description`, `tasksAndQuests`.`levelReq`, `tasksAndQuests`.`levelMax` 
                        FROM `tasksAndQuests` WHERE `tasksAndQuests`.`type` = "quest"');
                } break;
            case('allTravelEvents'): {
                    return $GLOBALS['database']->fetch_data('SELECT `events_tiles`.* FROM `events_tiles` ORDER BY `events_tiles`.`id` ASC');
                } break;
            case('allLocations'): {
                    return $GLOBALS['database']->fetch_data('SELECT `locations`.* FROM `locations` ORDER BY `locations`.`id` ASC');
                } break;
            case('villageCount'): {
                    return $GLOBALS['database']->fetch_data('SELECT COUNT(`konoki`.`uid`) AS `konoki_vcount`, COUNT(`samui`.`uid`) AS `samui_vcount`,
                        COUNT(`silence`.`uid`) AS `silence_vcount`, COUNT(`shine`.`uid`) AS `shine_vcount`, COUNT(`shroud`.`uid`) AS `shroud_vcount`,
                        COUNT(`syndicate`.`uid`) AS `syndicate_vcount`
                        FROM `users_timer`
                            LEFT JOIN `users_loyalty` AS `konoki` ON (`konoki`.`uid` = `users_timer`.`userid` AND `konoki`.`village` = "Konoki")
                            LEFT JOIN `users_loyalty` AS `samui` ON (`samui`.`uid` = `users_timer`.`userid` AND `samui`.`village` = "Samui")
                            LEFT JOIN `users_loyalty` AS `silence` ON (`silence`.`uid` = `users_timer`.`userid` AND `silence`.`village` = "Silence")
                            LEFT JOIN `users_loyalty` AS `shine` ON (`shine`.`uid` = `users_timer`.`userid` AND `shine`.`village` = "Shine")
                            LEFT JOIN `users_loyalty` AS `shroud` ON (`shroud`.`uid` = `users_timer`.`userid` AND `shroud`.`village` = "Shroud")
                            LEFT JOIN `users_loyalty` AS `syndicate` ON (`syndicate`.`uid` = `users_timer`.`userid` AND `syndicate`.`village` = "Syndicate")
                        LIMIT 1');
                } break;
            case('villageACount'): {
                    return $GLOBALS['database']->fetch_data('SELECT COUNT(`konoki`.`uid`) AS `konoki_vcount`, COUNT(`samui`.`uid`) AS `samui_vcount`,
                        COUNT(`silence`.`uid`) AS `silence_vcount`, COUNT(`shine`.`uid`) AS `shine_vcount`, COUNT(`shroud`.`uid`) AS `shroud_vcount`,
                        COUNT(`syndicate`.`uid`) AS `syndicate_vcount`
                        FROM `users_timer`
                            LEFT JOIN `users_loyalty` AS `konoki` ON (`konoki`.`uid` = `users_timer`.`userid` AND `konoki`.`village` = "Konoki")
                            LEFT JOIN `users_loyalty` AS `samui` ON (`samui`.`uid` = `users_timer`.`userid` AND `samui`.`village` = "Samui")
                            LEFT JOIN `users_loyalty` AS `silence` ON (`silence`.`uid` = `users_timer`.`userid` AND `silence`.`village` = "Silence")
                            LEFT JOIN `users_loyalty` AS `shine` ON (`shine`.`uid` = `users_timer`.`userid` AND `shine`.`village` = "Shine")
                            LEFT JOIN `users_loyalty` AS `shroud` ON (`shroud`.`uid` = `users_timer`.`userid` AND `shroud`.`village` = "Shroud")
                            LEFT JOIN `users_loyalty` AS `syndicate` ON (`syndicate`.`uid` = `users_timer`.`userid` AND `syndicate`.`village` = "Syndicate")
                        WHERE `users_timer`.`last_regen` > (UNIX_TIMESTAMP() - 3628800) LIMIT 1');
                } break;
        }
    }

    public static function obtainSpecQuery($request, $data) {
        switch ($request) {
            case('locationInformation'): {
                    return $GLOBALS['database']->fetch_data('SELECT `locations`.* FROM `locations` 
                        WHERE `locations`.`name` = "' . $data['name'] . '"
                            OR (`locations`.`owner` = "' . $data['name'] . '" AND `locations`.`identifier` = "village") LIMIT 1');
                } break;
            case('userTasks'): {
                    return $GLOBALS['database']->fetch_data('SELECT `users_missions`.`tasks` FROM `users_missions`
                        WHERE `users_missions`.`userid` = ' . $data['uid'] . ' LIMIT 1');
                } break;
            case('tasksQuestsMission'): {
                    return $GLOBALS['database']->fetch_data('SELECT `tasksAndQuests`.* FROM `tasksAndQuests`
                        WHERE `tasksAndQuests`.`id` = ' . $data['id'] . ' LIMIT 1');
                } break;
            case('Order'): {
                    return $GLOBALS['database']->fetch_data('SELECT `tasksAndQuests`.* FROM `tasksAndQuests`
                        WHERE `tasksAndQuests`.`levelReq` = ' . $data['id'] . ' AND `tasksAndQuests`.`type` = "order" LIMIT 1');
                } break;
            case('Page'): {
                    return $GLOBALS['database']->fetch_data('SELECT `pages`.* FROM `pages` WHERE `pages`.`id` = ' . $data['id'] . ' LIMIT 1');
                } break;
            case('territoryCount'): {
                    return $GLOBALS['database']->fetch_data('SELECT COUNT(`locations`.`id`) AS `count` FROM `locations`
                        WHERE `locations`.`owner` = "' . $data['name'] . '" LIMIT 1');
                } break;
            case('Alliance'): {
                    return $GLOBALS['database']->fetch_data('SELECT `village_structures`.*, `alliances`.* FROM `alliances`
                        INNER JOIN `village_structures` ON (`village_structures`.`name` = `alliances`.`village`) AND `alliances`.`village` = "' . $data['name'] . '" LIMIT 1');
                } break;
            case('Village'): {
                    return $GLOBALS['database']->fetch_data('SELECT `villages`.*, `village_structures`.* FROM `villages`
                            INNER JOIN `village_structures` ON (`village_structures`.`name` = `villages`.`name`)
                        WHERE `villages`.`name` = "' . $data['name'] . '" LIMIT 1');
                } break;
            case('VAMC'): {
                    return $GLOBALS['database']->fetch_data('SELECT COUNT(`as`.`uid`) AS `as_count`, COUNT(`genin`.`uid`) AS `genin_count`,
                        COUNT(`chuunin`.`uid`) AS `chuunin_count`, COUNT(`jounin`.`uid`) AS `jounin_count`, COUNT(`sj`.`uid`) AS `sj_count`
                        FROM `users`
                            INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id` AND `users_timer`.`last_regen` > (UNIX_TIMESTAMP() - 3628800))
                            INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users_timer`.`userid` AND `users_loyalty`.`village` = "' . $data['name'] . '")
                            LEFT JOIN `users_statistics` AS `as` ON (`as`.`uid` = `users_loyalty`.`uid` AND `as`.`rank_id` = 1)
                            LEFT JOIN `users_statistics` AS `genin` ON (`genin`.`uid` = `users_loyalty`.`uid` AND `genin`.`rank_id` = 2)
                            LEFT JOIN `users_statistics` AS `chuunin` ON (`chuunin`.`uid` = `users_loyalty`.`uid` AND `chuunin`.`rank_id` = 3)
                            LEFT JOIN `users_statistics` AS `jounin` ON (`jounin`.`uid` = `users_loyalty`.`uid` AND `jounin`.`rank_id` = 4)
                            LEFT JOIN `users_statistics` AS `sj` ON (`sj`.`uid` = `users_loyalty`.`uid` AND `sj`.`rank_id` = 5)
                        WHERE (`users`.`activation` = "1" || `users`.`activation` = 1) LIMIT 1');
                } break;
            case('VTMC'): {
                    return $GLOBALS['database']->fetch_data('SELECT COUNT(`as`.`uid`) AS `as_count`, COUNT(`genin`.`uid`) AS `genin_count`,
                        COUNT(`chuunin`.`uid`) AS `chuunin_count`, COUNT(`jounin`.`uid`) AS `jounin_count`, COUNT(`sj`.`uid`) AS `sj_count`
                        FROM `users`
                            INNER JOIN `users_loyalty` ON (`users_loyalty`.`uid` = `users`.`id` AND `users_loyalty`.`village` = "' . $data['name'] . '")
                            LEFT JOIN `users_statistics` AS `as` ON (`as`.`uid` = `users_loyalty`.`uid` AND `as`.`rank_id` = 1)
                            LEFT JOIN `users_statistics` AS `genin` ON (`genin`.`uid` = `users_loyalty`.`uid` AND `genin`.`rank_id` = 2)
                            LEFT JOIN `users_statistics` AS `chuunin` ON (`chuunin`.`uid` = `users_loyalty`.`uid` AND `chuunin`.`rank_id` = 3)
                            LEFT JOIN `users_statistics` AS `jounin` ON (`jounin`.`uid` = `users_loyalty`.`uid` AND `jounin`.`rank_id` = 4)
                            LEFT JOIN `users_statistics` AS `sj` ON (`sj`.`uid` = `users_loyalty`.`uid` AND `sj`.`rank_id` = 5)
                        WHERE (`users`.`activation` = "1" || `users`.`activation` = 1) LIMIT 1');
                } break;
            case('clanCount'): {
                    return $GLOBALS['database']->fetch_data('SELECT COUNT(`users_preferences`.`uid`) AS `count` 
                        FROM `users_preferences` WHERE `users_preferences`.`clan` = "' . $data['id'] . '" LIMIT 1');
                } break;
        }
    }

    // Servers may be out of sync, so we store a local timestamp in the cache
    // This will always have the highest value of all nodes
    public static function getHighestTimestamp() {
        $thisNodeTime = time();
        if ($GLOBALS['memOn'] !== true) {
            return $thisNodeTime;
        }

        // Get the timstamp in the cache
        $data = $GLOBALS['cache']->get(Data::$target_site . "time");

        // Get the timestamp of the memcached server
        $stats = $GLOBALS['cache']->getStats();
        if (isset($stats[MEMCACHE_HOST . ':' . MEMCACHE_PORT]['time'])) {
            $thisNodeTime = $stats[MEMCACHE_HOST . ':' . MEMCACHE_PORT]['time'];
        }

        // Send the node time to the server in case it's greater than what's currently there
        if (!$data || $thisNodeTime > $data) {
            $data = $thisNodeTime;
            $GLOBALS['cache']->set(Data::$target_site . "time", $data, MEMCACHE_COMPRESSED, 60);
        }

        return $data;
    }

    // Get game version information
    public static function getGameVersion($overwrite = false) {

        // Get the data
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainStaticQuery('GameVersion');
        } elseif ($overwrite) {
            $data = self::obtainStaticQuery('GameVersion');
            $GLOBALS['cache']->set(Data::$target_site . "GameVersion", $data, MEMCACHE_COMPRESSED, 60);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "GameVersion"))) {
                $data = self::obtainStaticQuery('GameVersion');
                $GLOBALS['cache']->set(Data::$target_site . "GameVersion", $data, MEMCACHE_COMPRESSED, 60);
            }
        }

        // Rewrite to just return version number
        $data[0]['id'] += 1000;
        $data[0]['id'] /= 1000;
        $version = "v." . $data[0]['id'];

        // Return version number
        return $version;
    }

    // Core 3 Functions    
    public static function getBlueMsgViews($uid) {
        if (!isset($uid)) {
            return false;
        } elseif ($GLOBALS['memOn'] === true) {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Blue_Messages:" . $uid))) {
                $data = 0;
                $GLOBALS['cache']->set(Data::$target_site . "Blue_Messages:" . $uid, $data, MEMCACHE_COMPRESSED, 60);
            }
        } else {
            return false;
        }
        $data++;
        $GLOBALS['cache']->set(Data::$target_site . "Blue_Messages:" . $uid, $data, MEMCACHE_COMPRESSED, 60);
        self::logObjectSizes("Blue_Messages", $data);
        return $data;
    }

    public static function deleteBlueMsgViews($uid) {
        if (isset($uid)) {
            $GLOBALS['cache']->delete(Data::$target_site . "Blue_Messages:" . $uid);
        }
    }

    public static function deleteAllGlobalEvents() {
        if(!isset($GLOBALS['cache']))
            self::hook_up_memcache(1,true);

        $GLOBALS['cache']->delete(Data::$target_site . "allGlobalEvents");
    }

    // Get all data displayed on welcome page
    public static function getAllGlobalEvents($overwrite = false) {
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainStaticQuery('allGlobalEvents');
        } elseif ($overwrite) {
            $data = self::obtainStaticQuery('allGlobalEvents');
            $GLOBALS['cache']->set(Data::$target_site . "allGlobalEvents", $data, MEMCACHE_COMPRESSED, 60);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "allGlobalEvents"))) {
                $data = self::obtainStaticQuery('allGlobalEvents');
                $GLOBALS['cache']->set(Data::$target_site . "allGlobalEvents", $data, MEMCACHE_COMPRESSED, 60);
            }
        }
        self::logObjectSizes("allGlobalEvents", $data);
        return $data;
    }

    // Get all data displayed on welcome page
    public static function getLatestNewsItem($overwrite = false) {
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainStaticQuery('latestNewsItem');
        } elseif ($overwrite) {
            $data = self::obtainStaticQuery('latestNewsItem');
            $GLOBALS['cache']->set(Data::$target_site . "latestNewsItem", $data, MEMCACHE_COMPRESSED, 60);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "latestNewsItem"))) {
                $data = self::obtainStaticQuery('latestNewsItem');
                $GLOBALS['cache']->set(Data::$target_site . "latestNewsItem", $data, MEMCACHE_COMPRESSED, 60);
            }
        }
        self::logObjectSizes("latestNewsItem", $data);
        return $data;
    }

    // Get latest news messages
    public static function getLatestNews($overwrite = false) {
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainStaticQuery('latestNews');
        } elseif ($overwrite) {
            $data = self::obtainStaticQuery('latestNews');
            $GLOBALS['cache']->set(Data::$target_site . "latestNews", $data, MEMCACHE_COMPRESSED, 60);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "latestNews"))) {
                $data = self::obtainStaticQuery('latestNews');
                $GLOBALS['cache']->set(Data::$target_site . "latestNews", $data, MEMCACHE_COMPRESSED, 60);
            }
        }
        self::logObjectSizes("latestNews", $data);
        return $data;
    }

    // Get latest blue messages
    public static function getLatestBluemessages($overwrite = false) {
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainStaticQuery('latestBlue');
        } elseif ($overwrite) {
            $data = self::obtainStaticQuery('latestBlue');
            $GLOBALS['cache']->set(Data::$target_site . "latestBlue", $data, MEMCACHE_COMPRESSED, 60);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "latestBlue"))) {
                $data = self::obtainStaticQuery('latestBlue');
                $GLOBALS['cache']->set(Data::$target_site . "latestBlue", $data, MEMCACHE_COMPRESSED, 60);
            }
        }
        self::logObjectSizes("latestBlue", $data);
        return $data;
    }

    // Get latest change log entries
    public static function getLatestChanges($overwrite = false) {
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainStaticQuery('latestChanges');
        } elseif ($overwrite) {
            $data = self::obtainStaticQuery('latestChanges');
            $GLOBALS['cache']->set(Data::$target_site . "latestChanges", $data, MEMCACHE_COMPRESSED, 60);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "latestChanges"))) {
                $data = self::obtainStaticQuery('latestChanges');
                $GLOBALS['cache']->set(Data::$target_site . "latestChanges", $data, MEMCACHE_COMPRESSED, 60);
            }
        }
        self::logObjectSizes("latestChanges", $data);
        return $data;
    }

    // Get latest news messages
    public static function getTopPlayers($overwrite = false) {
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainStaticQuery('topPlayers');
        } elseif ($overwrite) {
            $data = self::obtainStaticQuery('topPlayers');
            $GLOBALS['cache']->set(Data::$target_site . "topPlayers", $data, MEMCACHE_COMPRESSED, 60);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "topPlayers"))) {
                $data = self::obtainStaticQuery('topPlayers');
                $GLOBALS['cache']->set(Data::$target_site . "topPlayers", $data, MEMCACHE_COMPRESSED, 60);
            }
        }
        self::logObjectSizes("topPlayers", $data);
        return $data;
    }

    // Get All locations
    public static function getLocations($overwrite = false) {
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainStaticQuery('allLocations');
        } elseif ($overwrite) {
            $data = self::obtainStaticQuery('allLocations');
            $GLOBALS['cache']->set(Data::$target_site . "All_Locations", $data, MEMCACHE_COMPRESSED, 300);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "All_Locations"))) {
                $data = self::obtainStaticQuery('allLocations');
                $GLOBALS['cache']->set(Data::$target_site . "All_Locations", $data, MEMCACHE_COMPRESSED, 300);
            }
        }
        $newData = array(); // Rearrange to make name index
        foreach ($data as $entry) {
            $newData[$entry['name']] = $entry;
        }
        self::logObjectSizes("All_Locations", $newData);
        return $newData;
    }

    public static function deleteLocations($name) {
        if (isset($name)) {
            $GLOBALS['cache']->delete(Data::$target_site . "All_Locations");
        }
    }

    // Get Specific Location
    public static function getLocationInformation($name, $overwrite = false) {
        if (!isset($name)) {
            return false;
        }

        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainSpecQuery('locationInformation', array('name' => $name));
        } elseif ($overwrite) {
            $data = self::obtainSpecQuery('locationInformation', array('name' => $name));
            $GLOBALS['cache']->set(Data::$target_site . "Location_Data:" . trim($name), $data, MEMCACHE_COMPRESSED, 60);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Location_Data:" . trim($name)))) {
                $data = self::obtainSpecQuery('locationInformation', array('name' => $name));
                $GLOBALS['cache']->set(Data::$target_site . "Location_Data:" . trim($name), $data, MEMCACHE_COMPRESSED, 60);
            }
        }
        self::logObjectSizes("Location_Data", $data);
        return $data;
    }

    public static function deleteLocationInformation($name) {
        if (isset($name)) {
            $GLOBALS['cache']->delete(Data::$target_site . "Location_Data:" . trim($name));
        }
    }

    // Retrieves column from users_missions table containing all information on tasks, quests and orders
    public static function getUserTasks($uid, $overwrite = false) {
        if (!isset($uid)) {
            return false;
        }

        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainSpecQuery('userTasks', array('uid' => $uid));
        } elseif ($overwrite) {
            $data = self::obtainSpecQuery('userTasks', array('uid' => $uid));
            $GLOBALS['cache']->set(Data::$target_site . "User_Tasks_ID:" . $uid, $data, MEMCACHE_COMPRESSED, 60);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "User_Tasks_ID:" . $uid))) {
                $data = self::obtainSpecQuery('userTasks', array('uid' => $uid));
                $GLOBALS['cache']->set(Data::$target_site . "User_Tasks_ID:" . $uid, $data, MEMCACHE_COMPRESSED, 60);
            }
        }
        if ($data !== "0 rows") {
            $data[0]['tasks'] = stripslashes($data[0]['tasks']);
        }
        self::logObjectSizes("User_Tasks_ID", $data);
        return $data;
    }

    // Retrieves column from users_missions table containing all information on tasks, quests and orders
    public static function deleteUserTasks($uid) {
        if (isset($uid)) {
            $GLOBALS['cache']->delete(Data::$target_site . "User_Tasks_ID:" . $uid);
        }
    }

    // Retrieves single task/quest/mission
    public static function getTasksQuestsMission($id, $overwrite = false) {
        if (!isset($id) || empty($id)) {
            return false;
        }

        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainSpecQuery('tasksQuestsMission', array('id' => $id));
        } elseif ($overwrite) {
            $data = self::obtainSpecQuery('tasksQuestsMission', array('id' => $id));
            $GLOBALS['cache']->set(Data::$target_site . "Task_ID:" . $id, $data, MEMCACHE_COMPRESSED, 60);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Task_ID:" . $id))) {
                $data = self::obtainSpecQuery('tasksQuestsMission', array('id' => $id));
                $GLOBALS['cache']->set(Data::$target_site . "Task_ID:" . $id, $data, MEMCACHE_COMPRESSED, 60);
            }
        }
        self::logObjectSizes("Task_ID", $data);
        return $data;
    }

    // deleted single task/quest/mission
    public static function deleteTasksQuestsMission($id) {
        if (isset($id)) {
            $GLOBALS['cache']->delete(Data::$target_site . "Task_ID:" . $id);
        }
    }

    // Retrieves single task/quest/mission
    public static function getOrder($levelId, $overwrite = false) {
        if (!isset($levelId)) {
            return false;
        }

        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainSpecQuery('Order', array('id' => $levelId));
        } elseif ($overwrite) {
            $data = self::obtainSpecQuery('Order', array('id' => $levelId));
            $GLOBALS['cache']->set(Data::$target_site . "Order_ID:" . $levelId, $data, MEMCACHE_COMPRESSED, 60);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Order_ID:" . $levelId))) {
                $data = self::obtainSpecQuery('Order', array('id' => $levelId));
                $GLOBALS['cache']->set(Data::$target_site . "Order_ID:" . $levelId, $data, MEMCACHE_COMPRESSED, 60);
            }
        }
        self::logObjectSizes("Order_ID", $data);
        return $data;
    }

    // Retrieves All Database Missions
    public static function getTasksQuestsMissions($overwrite = false) {
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainStaticQuery('allTasks');
        } elseif ($overwrite) {
            $data = self::obtainStaticQuery('allTasks');
            $GLOBALS['cache']->set(Data::$target_site . "Tasks_Quests_Missions", $data, MEMCACHE_COMPRESSED, 300);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Tasks_Quests_Missions"))) {
                $data = self::obtainStaticQuery('allTasks');
                $GLOBALS['cache']->set(Data::$target_site . "Tasks_Quests_Missions", $data, MEMCACHE_COMPRESSED, 300);
            }
        }
        self::logObjectSizes("Tasks_Quests_Missions", $data);
        return $data;
    }

    // Delete all database tasks
    public static function deleteTasksQuestsMissions() {
        $GLOBALS['cache']->delete(Data::$target_site . "Tasks_Quests_Missions");
    }

    // Get all missions and crimes
    public static function getMissionsAndCrimes($overwrite = false) {
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainStaticQuery('allMissionCrimes');
        } elseif ($overwrite) {
            $data = self::obtainStaticQuery('allMissionCrimes');
            $GLOBALS['cache']->set(Data::$target_site . "All_Mission_Crimes", $data, MEMCACHE_COMPRESSED, 300);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "All_Mission_Crimes"))) {
                $data = self::obtainStaticQuery('allMissionCrimes');
                $GLOBALS['cache']->set(Data::$target_site . "All_Mission_Crimes", $data, MEMCACHE_COMPRESSED, 300);
            }
        }
        self::logObjectSizes("All_Mission_Crimes", $data);
        return $data;
    }

    // Delete all missions and crimes
    public static function deleteMissionsAndCrimes() {
        $GLOBALS['cache']->delete(Data::$target_site . "All_Mission_Crimes");
    }

    // Retrieve all menu pages
    public static function getMenu($overwrite = false) {
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainStaticQuery('allMenus');
        } elseif ($overwrite) {
            $data = self::obtainStaticQuery('allMenus');
            $GLOBALS['cache']->set(Data::$target_site . "All_Menus", $data, MEMCACHE_COMPRESSED, 300);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "All_Menus"))) {
                $data = self::obtainStaticQuery('allMenus');
                $GLOBALS['cache']->set(Data::$target_site . "All_Menus", $data, MEMCACHE_COMPRESSED, 300);
            }
        }
        self::logObjectSizes("All_Menus", $data);
        return $data;
    }

    // Get page information
    public static function getPage($pageID, $overwrite = false) {
        if (!isset($pageID)) {
            return false;
        }
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainSpecQuery('Page', array('id' => $pageID));
        } elseif ($overwrite) {
            $data = self::obtainSpecQuery('Page', array('id' => $pageID));
            $GLOBALS['cache']->set(Data::$target_site . "Page_ID:" . $pageID, $data, MEMCACHE_COMPRESSED, 60);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Page_ID:" . $pageID))) {
                $data = self::obtainSpecQuery('Page', array('id' => $pageID));
                $GLOBALS['cache']->set(Data::$target_site . "Page_ID:" . $pageID, $data, MEMCACHE_COMPRESSED, 60);
            }
        }
        self::logObjectSizes("Page_ID", $data);
        return $data;
    }

    // Count the number of territories belonging to a specific village
    public static function territory_count($villageName, $overwrite = false) {
        if (!isset($villageName)) {
            return false;
        }

        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainSpecQuery('territoryCount', array('name' => $villageName));
        } elseif ($overwrite) {
            $data = self::obtainSpecQuery('territoryCount', array('name' => $villageName));
            $GLOBALS['cache']->set(Data::$target_site . "Territory_Count:" . $villageName, $data, MEMCACHE_COMPRESSED, 60);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Territory_Count:" . $villageName))) {
                $data = self::obtainSpecQuery('territoryCount', array('name' => $villageName));
                $GLOBALS['cache']->set(Data::$target_site . "Territory_Count:" . $villageName, $data, MEMCACHE_COMPRESSED, 60);
            }
        }
        return $data[0]['count'];
    }

    // Get all alliances
    public static function getAlliances($overwrite = false) {
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainStaticQuery('allAlliances');
        } elseif ($overwrite) {
            $data = self::obtainStaticQuery('allAlliances');
            $GLOBALS['cache']->set(Data::$target_site . "All_Alliances", $data, MEMCACHE_COMPRESSED, 300);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "All_Alliances"))) {
                $data = self::obtainStaticQuery('allAlliances');
                $GLOBALS['cache']->set(Data::$target_site . "All_Alliances", $data, MEMCACHE_COMPRESSED, 300);
            }
        }
        self::logObjectSizes("All_Alliances", $data);
        return $data;
    }

    // Delete all alliances
    public static function deleteAlliances() {
        $GLOBALS['cache']->delete(Data::$target_site . "All_Alliances");
    }

    // Get alliance information
    public static function getAlliance($villageName, $overwrite = false) {
        if (!isset($villageName)) {
            return false;
        }

        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainSpecQuery('Alliance', array('name' => $villageName));
        } elseif ($overwrite) {
            $data = self::obtainSpecQuery('Alliance', array('name' => $villageName));
            $GLOBALS['cache']->set(Data::$target_site . "Village_Alliance:" . $villageName, $data, MEMCACHE_COMPRESSED, 60);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Village_Alliance:" . $villageName))) {
                $data = self::obtainSpecQuery('Alliance', array('name' => $villageName));
                $GLOBALS['cache']->set(Data::$target_site . "Village_Alliance:" . $villageName, $data, MEMCACHE_COMPRESSED, 60);
            }
        }
        self::logObjectSizes("Village_Alliance", $data);
        return $data;
    }

    // Delete chached alliance data
    public static function deleteAlliance($villageName) {
        if (isset($villageName)) {
            $GLOBALS['cache']->delete(Data::$target_site . "Village_Alliance:" . $villageName);
        }
    }

    // Get village information
    public static function getVillage($villageName, $overwrite = false) {
        if (!isset($villageName)) {
            return false;
        }

        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainSpecQuery('Village', array('name' => $villageName));
        } elseif ($overwrite) {
            $data = self::obtainSpecQuery('Village', array('name' => $villageName));
            $GLOBALS['cache']->set(Data::$target_site . "Village_Data:" . $villageName, $data, MEMCACHE_COMPRESSED, 60);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Village_Data:" . $villageName))) {
                $data = self::obtainSpecQuery('Village', array('name' => $villageName));
                $GLOBALS['cache']->set(Data::$target_site . "Village_Data:" . $villageName, $data, MEMCACHE_COMPRESSED, 60);
            }
        }
        self::logObjectSizes("Village_Data", $data);
        return $data;
    }

    // Get all resources - carefull with this one, it's heavy and should only be used in special places
    public static function getAllResources($overwrite = false) {
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainStaticQuery('allResources');
        } elseif ($overwrite) {
            $data = self::obtainStaticQuery('allResources');
            $GLOBALS['cache']->set(Data::$target_site . "Resource_Map", $data, MEMCACHE_COMPRESSED, 300);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Resource_Map"))) {
                $data = self::obtainStaticQuery('allResources');
                $GLOBALS['cache']->set(Data::$target_site . "Resource_Map", $data, MEMCACHE_COMPRESSED, 300);
            }
        }
        self::logObjectSizes("Resource_Map", $data);
        return $data;
    }

    // Delete all database tasks
    public static function deleteAllResources() {
        $GLOBALS['cache']->delete(Data::$target_site . "Resource_Map");
    }

    // Get information on all items
    public static function getCraftingItems($overwrite = false) {
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainStaticQuery('craftingItems');
        } elseif ($overwrite) {
            $data = self::obtainStaticQuery('craftingItems');
            $GLOBALS['cache']->set(Data::$target_site . "Crafting_Items", $data, MEMCACHE_COMPRESSED, 300);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "All_Items"))) {
                $data = self::obtainStaticQuery('craftingItems');
                $GLOBALS['cache']->set(Data::$target_site . "Crafting_Items", $data, MEMCACHE_COMPRESSED, 300);
            }
        }
        self::logObjectSizes("Crafting_Items", $data);
        return $data;
    }

    // Delete all database tasks
    public static function deleteItems() {
        $GLOBALS['cache']->delete(Data::$target_site . "All_Items");
    }

    // Retrieve all travel events
    public static function getEvents($overwrite = false) {
        if ($GLOBALS['memOn'] !== true) {
            // Collect data array
            $data = array(
                "quest_events" => self::obtainStaticQuery('allEventQuests'),
                "tile_events" => self::obtainStaticQuery('allTravelEvents')
            );
        } elseif ($overwrite) {
            // Collect data array
            $data = array(
                "quest_events" => self::obtainStaticQuery('allEventQuests'),
                "tile_events" => self::obtainStaticQuery('allTravelEvents')
            );
            $GLOBALS['cache']->set(Data::$target_site . "Travel_Events", $data, MEMCACHE_COMPRESSED, 300);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Travel_Events"))) {
                // Collect data array
                $data = array(
                    "quest_events" => self::obtainStaticQuery('allEventQuests'),
                    "tile_events" => self::obtainStaticQuery('allTravelEvents')
                );
                $GLOBALS['cache']->set(Data::$target_site . "Travel_Events", $data, MEMCACHE_COMPRESSED, 300);
            }
        }
        self::logObjectSizes("Travel_Events", $data);
        return $data;
    }

    // Delete all database tasks
    public static function deleteEvents() {
        $GLOBALS['cache']->delete(Data::$target_site . "Travel_Events");
    }

    // Get pd (page data): Page timer and page loads. Used to limit user pageload, to prevent super-speed bots
    // Also get cpd (captcha page data): Page timer and page loads. Used to prevent macros on certain pages in game
    // such that when a certain amount of pageloads on those pages are reached, the user will have to input a captcha.
    public static function getPageData($userID, $overwrite = false) {

        // Define the default value
        $defaultValue = array(
            "timer" => time(),
            "loads" => 0,
            "captchaTimer" => time(),
            "captchaLoads" => 0
        );

        // Get the data
        if (!isset($userID)) {
            return false;
        } elseif ($GLOBALS['memOn'] !== true) {
            $data = $defaultValue;
        } elseif ($overwrite) {
            $data = $defaultValue;
            $GLOBALS['cache']->set(Data::$target_site . "Page_ID:" . $userID, $data, MEMCACHE_COMPRESSED, 30);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Page_ID:" . $userID))) {
                $data = $defaultValue;
                $GLOBALS['cache']->set(Data::$target_site . "Page_ID:" . $userID, $data, MEMCACHE_COMPRESSED, 30);
            }
        }
        self::logObjectSizes("Page_ID", $data);
        return $data;
    }

    public static function setPageData($userID, $userPageData) {
        if (!isset($userID)) {
            return false;
        } else {
            $GLOBALS['cache']->set(Data::$target_site . "Page_ID:" . $userID, $userPageData, MEMCACHE_COMPRESSED, 30);
        }
    }

    // Getting and setting travel tooltips
    public static function getTravelTooltips() {
        $data = false;
        if ($GLOBALS['memOn'] == true) {
            $data = $GLOBALS['cache']->get(Data::$target_site . "TravelTooltip");
        }
        return $data;
    }

    public static function setTravelTooltips($htmlData) {
        $GLOBALS['cache']->set(Data::$target_site . "TravelTooltip", $htmlData, MEMCACHE_COMPRESSED, 3600);
    }

    // Sets a flag that this user has a captcha lock
    public static function getCaptchaLock($userID) {
        $data = false;
        if (!isset($userID)) {
            return false;
        } elseif ($GLOBALS['memOn'] == true) {
            $data = $GLOBALS['cache']->get(Data::$target_site . "CaptchaLock:" . $userID);
        }
        self::logObjectSizes("CaptchaLock", $data);
        return $data;
    }

    public static function setCaptchaLock($userID, $value) {
        if (!isset($userID)) {
            return false;
        } else {
            $GLOBALS['cache']->set(Data::$target_site . "CaptchaLock:" . $userID, $value, MEMCACHE_COMPRESSED, 600);
        }
    }

    public static function deleteCaptchaLock($userID) {
        if (isset($userID)) {
            $GLOBALS['cache']->delete(Data::$target_site . "CaptchaLock:" . $userID);
        }
    }

    // Add travel movement to user
    public static function updateUserMovement($uid, $x, $y, $region, $terrName) {
        if (isset($uid, $x, $y, $region, $terrName)) {

            // Get current movement list and go through it
            $currentMovements = json_decode(self::getMovements($uid), true);
            if (!empty($currentMovements)) {

                // Go through current list of movements, Check if we need to update entry time
                $size = count($currentMovements);
                for ($i = 0; $i < $size; $i++) {
                    if ($currentMovements[$i]['x'] == $x && $currentMovements[$i]['y'] == $y) {
                        $currentMovements[$i]['time'] = $GLOBALS['user']->load_time;
                        break;
                    }
                }
            }

            // Insert new entry
            $currentMovements[] = array("x" => $x, "y" => $y, "region" => $region, "terr" => $terrName, "time" => $GLOBALS['user']->load_time);

            // Sort array based on time
            function timecomp($a, $b) {
                return $b["time"] - $a["time"];
            }

            uasort($currentMovements, 'timecomp');

            // Limit entries in movement array
            $currentMovements = array_slice($currentMovements, 0, self::$__travelLogLimit);

            $GLOBALS['cache']->set(Data::$target_site . "User_Movement_Data:" . $uid, json_encode($currentMovements), MEMCACHE_COMPRESSED, 10800);
        }
    }

    // Get user location stuff
    public static function getMovements($uid) {
        if (!isset($uid)) {
            return false;
        } elseif (!($data = $GLOBALS['cache']->get(Data::$target_site . "User_Movement_Data:" . $uid))) {
            $data = json_encode(array());
            $GLOBALS['cache']->set(Data::$target_site . "User_Movement_Data:" . $uid, $data, MEMCACHE_COMPRESSED, 10800);
        }
        self::logObjectSizes("User_Movement_Data", $data);
        return $data;
    }

    // Get user location stuff
    public static function deleteUserMovements($uid) {
        if (isset($uid)) {
            $GLOBALS['cache']->delete(Data::$target_site . "User_Movement_Data:" . $uid);
        }
    }

    // Update the combat log - global function
    public static function updateCombatLog($uid, $type, $aid, $status, $defeatedName) {
        if (isset($uid, $type, $aid, $status, $defeatedName)) {
            if (in_array($type, self::$__pvpTypes)) {
                $currentLog = self::getPVPCombatLog($uid);
                $time = time();
                $currentLog[] = array($type, $aid, $status, $defeatedName, $time);
                $GLOBALS['cache']->set(Data::$target_site . "User_PVP_Combat_Log:" . $uid, json_encode($currentLog), MEMCACHE_COMPRESSED, 86400);
            } else {
                $currentLog = self::getOtherCombatLog($uid);
                $time = time();
                $currentLog[] = array($type, $aid, $status, $defeatedName, $time);
                $GLOBALS['cache']->set(Data::$target_site . "User_Combat_Log:" . $uid, json_encode($currentLog), MEMCACHE_COMPRESSED, 86400);
            }
        }
    }

    // Get the global combat log
    public static function getCombatLog($uid) {

        if (!isset($uid)) {
            return array();
        }

        // Get PVP & other battles
        $pvpData = self::getPVPCombatLog($uid);
        $otherData = self::getOtherCombatLog($uid);
        $data = array_merge($pvpData, $otherData);

        // Sort the battles based on their timestamp index (index 4)
        usort($data, 'cachefunctions::sortBattles');

        // Return data to user
        return $data;
    }

    // Get PVP combat log
    public static function getPVPCombatLog($uid) {
        if (!isset($uid)) {
            return array();
        }
        $data = $GLOBALS['cache']->get(Data::$target_site . "User_PVP_Combat_Log:" . $uid);
        $data = json_decode($data, true);
        $data = self::cleanCombatLog($data, self::$__pvpCombatLogLimit);
        if (empty($data)) {
            $GLOBALS['cache']->set(Data::$target_site . "User_PVP_Combat_Log:" . $uid, json_encode($data), MEMCACHE_COMPRESSED, 86400);
            $data = array();
        }
        self::logObjectSizes("User_PVP_Combat_Log", $data);
        return $data;
    }

    // Get non-PVP combat log
    public static function getOtherCombatLog($uid) {
        if (!isset($uid)) {
            return array();
        }
        $data = $GLOBALS['cache']->get(Data::$target_site . "User_Combat_Log:" . $uid);
        $data = json_decode($data, true);
        $data = self::cleanCombatLog($data, self::$__nonPvpCombatLogLimit);
        if (empty($data)) {
            $data = array();
            $GLOBALS['cache']->set(Data::$target_site . "User_Combat_Log:" . $uid, json_encode($data), MEMCACHE_COMPRESSED, 86400);
        }
        self::logObjectSizes("User_Combat_Log", $data);
        return $data;
    }

    // Function for limiting the combat log
    public static function cleanCombatLog($data, $limit) {
        if (!empty($data)) {

            // Remove entries above limit
            $toBeRemoved = count($data) - $limit;
            if ($toBeRemoved > 0) {
                $data = array_slice($data, $toBeRemoved);
            }

            // Add missing time-stamps
            $time = time();
            foreach ($data as $k => $v) {
                if (!isset($v[4])) {
                    $data[$k][4] = $time;
                }
            }
        }

        // Return data
        return $data;
    }

    // Function for sorting the battle log
    public static function sortBattles($a, $b) {
        if ($a[4] == $b[4]) {
            return 0;
        }
        return ($a[4] < $b[4]) ? -1 : 1;
    }

    // Delete Combat Log
    public static function deleteCombatLog($uid) {
        if (isset($uid)) {
            $GLOBALS['cache']->delete(Data::$target_site . "User_Combat_Log:" . $uid);
        }
    }

    // Delete PVP combat log
    public static function deletePVPCombatLog($uid) {
        if (isset($uid)) {
            $GLOBALS['cache']->delete(Data::$target_site . "User_PVP_Combat_Log:" . $uid);
        }
    }

    // Delete mission entries from combat log
    public static function deleteTypesFromCombatLog($uid, $types) {

        //// Delete PVP combat log
        //$combatLog = self::getPVPCombatLog($uid);
        //if(!empty($combatLog)) {
        //    $newLog = array();
        //    foreach($combatLog as $entry) {
        //        if(!in_array($entry[0] , $types)) { $newLog[] = $entry; }
        //    }
        //    $GLOBALS['cache']->set(Data::$target_site."User_PVP_Combat_Log:".$uid,  json_encode($newLog), MEMCACHE_COMPRESSED, 43200);
        //}
        //
            //// Delete AI combat log
        //$combatLog = self::getOtherCombatLog($uid);
        //if(!empty($combatLog)) {
        //    $newLog = array();
        //    foreach($combatLog as $entry) {
        //        if(!in_array($entry[0] , $types)) { $newLog[] = $entry; }
        //    }
        //    $GLOBALS['cache']->set(Data::$target_site."User_Combat_Log:".$uid,  json_encode($newLog), MEMCACHE_COMPRESSED, 43200);
        //}

        $types = array_values($types);

        $new_types = array();

        foreach ($types as $type) {
            if ($type == 'mapAI') {
                $new_types[] = BattleStarter::travel;
            } else if ($type == 'eventAI') {
                $new_types[] = BattleStarter::event;
            } else if ($type == 'spars') {
                $new_types[] = BattleStarter::spar;
            } else if ($type == 'PVP') {
                $new_types[] = BattleStarter::pvp;
            } else if ($type == 'mission' || $type == 'crime') {
                $new_types[] = BattleStarter::mission;
            } else if ($type == 'leaderPVP') {
                $new_types[] = BattleStarter::kage;
                $new_types[] = BattleStarter::clan;
            } else if ($type == 'normalArena') {
                $new_types[] = BattleStarter::arena;
            } else if ($type == 'mirrorArena') {
                $new_types[] = BattleStarter::mirror;
            } else if ($type == 'tornArena') {
                $new_types[] = BattleStarter::torn;
            } else if ($type == 'territory') {
                $new_types[] = BattleStarter::territory;
            } else if ($type == 'quest') {
                $new_types[] = BattleStarter::quest;
            } else if ($type == 'anyAI') {
                $new_types[] = BattleStarter::travel;
                $new_types[] = BattleStarter::event;
                $new_types[] = BattleStarter::small_crimes;
                $new_types[] = BattleStarter::mission;
                $new_types[] = BattleStarter::arena;
                $new_types[] = BattleStarter::mirror;
                $new_types[] = BattleStarter::torn;
                $new_types[] = BattleStarter::quest;
            } else if ($type == 'anyPVP') {
                $new_types[] = BattleStarter::spar;
                $new_types[] = BattleStarter::pvp;
                $new_types[] = BattleStarter::kage;
                $new_types[] = BattleStarter::clan;
                $new_types[] = BattleStarter::territory;
            } else
                $new_types[] = $type;
        }

        $cleaned_types = '';

        foreach ($new_types as $key => $type) {
            if ($key != 0)
                $cleaned_types .= ',';

            $cleaned_types .= '"' . $type . '"';
        }

        $query = "UPDATE `battle_history` SET `requirement_ignore` = 'yes' WHERE `type` in (" . $cleaned_types . ") AND `census` like '%," . $GLOBALS['userdata'][0]['username'] . "/%' ORDER BY `time` DESC";

        try {
            if (!$GLOBALS['database']->execute_query($query))
                throw new Exception('query failed');
        } catch (Exception $e) {
            try {
                if (!$GLOBALS['database']->execute_query($query))
                    throw new Exception('query failed');
            } catch (Exception $e) {
                try {
                    if (!$GLOBALS['database']->execute_query($query))
                        throw new Exception('query failed to update user information. ');
                } catch (Exception $e) {
                    //dosnt need exception or anything thrown here. usualy fails if there is nothing to update.
                }
            }
        }
    }

    // Add mission type to user log, e.g. mission_d, crime_c etc.
    public static function updateMissionLog($uid, $type, $rank, $name) {
        if (isset($uid, $type)) {
            $currentLog = json_decode(self::getMissionLog($uid), true);
            $currentLog = self::cleanMissionLog($currentLog);
            $currentLog[] = array($type, $rank, $name, time());
            for ($i = 0, $size = count($currentLog); $i < $size; $i++) {
                $currentLog[$i][0] = strtolower($currentLog[$i][0]);
            }
            $GLOBALS['cache']->set(Data::$target_site . "User_Mission_Log:" . $uid, json_encode($currentLog), MEMCACHE_COMPRESSED, 86400);
        }
    }

    // Function for limiting the mission log
    public static function cleanMissionLog($data) {

        // Remove entries above limit
        if (!empty($data)) {
            $toBeRemoved = count($data) - self::$__missionLogLimit;
            if ($toBeRemoved > 0) {
                $data = array_slice($data, $toBeRemoved);
            }
        }

        // Return data
        return $data;
    }

    // Get user Mission log
    public static function getMissionLog($uid) {
        if (!isset($uid)) {
            return false;
        } elseif (!($data = $GLOBALS['cache']->get(Data::$target_site . "User_Mission_Log:" . $uid))) {
            $data = json_encode(array());
            $GLOBALS['cache']->set(Data::$target_site . "User_Mission_Log:" . $uid, $data, MEMCACHE_COMPRESSED, 86400);
        }
        self::logObjectSizes("User_Mission_Log", $data);
        return $data;
    }

    // Delete Mission Log
    public static function deleteMissionLog($uid) {
        if (isset($uid)) {
            $GLOBALS['cache']->delete(Data::$target_site . "User_Mission_Log:" . $uid);
        }
    }

    // Inactivate mission log (i.e. make sure they can't award quest completions anymore)
    public static function setAllMissionsAsRewarded($uid) {
        if (isset($uid)) {
            $currentLog = json_decode(self::getMissionLog($uid), true);
            foreach ($currentLog as $key => $logEntry) {
                $currentLog[$key]["reward"] = "yes";
            }
            $GLOBALS['cache']->set(Data::$target_site . "User_Mission_Log:" . $uid, json_encode($currentLog), MEMCACHE_COMPRESSED, 86400);
        }
    }

    // Track what pages the user has viewed lately
    public static function updateUserPages($uid, $pageID) {
        if (isset($uid, $pageID)  && $GLOBALS['memOn'] !== false) {
            $currentRecord = json_decode(self::getUserPages($uid), true);
            $currentRecord[$pageID] = 1;
            $GLOBALS['cache']->set(Data::$target_site . "User_Page_Data:" . $uid, json_encode($currentRecord), MEMCACHE_COMPRESSED, 60);
        }
    }

    // Get the pages the user has viewed lately
    public static function getUserPages($uid) {
        if (!isset($uid) || $GLOBALS['memOn'] === false) {
            return false;
        } elseif (!($data = $GLOBALS['cache']->get(Data::$target_site . "User_Page_Data:" . $uid))) {
            $data = json_encode(array());
            $GLOBALS['cache']->set(Data::$target_site . "User_Page_Data:" . $uid, $data, MEMCACHE_COMPRESSED, 60);
        }
        self::logObjectSizes("User_Page_Data", $data);
        return $data;
    }

    // Member count functions
    public static function getVillageActiveMemberCount($village, $overwrite = false) {
        if (!isset($village)) {
            return false;
        }

        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainSpecQuery('VAMC', array('name' => $village));
        } elseif ($overwrite) {
            $data = self::obtainSpecQuery('VAMC', array('name' => $village));
            $GLOBALS['cache']->set(Data::$target_site . "Active_Village_Members_Count:" . $village, $data, MEMCACHE_COMPRESSED, 300);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Active_Village_Members_Count:" . $village))) {
                $data = self::obtainSpecQuery('VAMC', array('name' => $village));
                $GLOBALS['cache']->set(Data::$target_site . "Active_Village_Members_Count:" . $village, $data, MEMCACHE_COMPRESSED, 300);
            }
        }
        self::logObjectSizes("Active_Village_Members_Count", $data);
        return $data;
    }

    // Delete village counter
    public static function deleteVillageActiveMemberCount($village) {
        if (isset($village)) {
            $GLOBALS['cache']->delete(Data::$target_site . "Active_Village_Members_Count:" . $village);
        }
    }

    // Member count functions
    public static function getVillageTotalMemberCount($village, $overwrite = false) {
        if (!isset($village)) {
            return false;
        }

        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainSpecQuery('VTMC', array('name' => $village));
        } elseif ($overwrite) {
            $data = self::obtainSpecQuery('VTMC', array('name' => $village));
            $GLOBALS['cache']->set(Data::$target_site . "Village_Members_Count:" . $village, $data, MEMCACHE_COMPRESSED, 300);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Village_Members_Count:" . $village))) {
                $data = self::obtainSpecQuery('VTMC', array('name' => $village));
                $GLOBALS['cache']->set(Data::$target_site . "Village_Members_Count:" . $village, $data, MEMCACHE_COMPRESSED, 300);
            }
        }
        self::logObjectSizes("Village_Members_Count", $data);
        return $data;
    }

    // Get the amount of users in a village
    public static function getVillageCount($overwrite = false) {
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainStaticQuery('villageCount');
        } elseif ($overwrite) {
            $data = self::obtainStaticQuery('villageCount');
            $GLOBALS['cache']->set(Data::$target_site . "Villagers_Count", $data, MEMCACHE_COMPRESSED, 300);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Villagers_Count"))) {
                $data = self::obtainStaticQuery('villageCount');
                $GLOBALS['cache']->set(Data::$target_site . "Villagers_Count", $data, MEMCACHE_COMPRESSED, 300);
            }
        }
        self::logObjectSizes("Villagers_Count", $data);
        return $data;
    }

    public static function getVillageACount($overwrite = false) {
        $query = '';
        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainStaticQuery('villageACount');
        } elseif ($overwrite) {
            $data = self::obtainStaticQuery('villageACount');
            $GLOBALS['cache']->set(Data::$target_site . "Active_Villagers_Count", $data, MEMCACHE_COMPRESSED, 300);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Active_Villagers_Count"))) {
                $data = self::obtainStaticQuery('villageACount');
                $GLOBALS['cache']->set(Data::$target_site . "Active_Villagers_Count", $data, MEMCACHE_COMPRESSED, 300);
            }
        }
        self::logObjectSizes("Active_Villagers_Count", $data);
        return $data;
    }

    // Get the amount of users in a clan
    public static function getClanCount($clanID, $overwrite = false) {
        if (!isset($clanID)) {
            return false;
        }

        if ($GLOBALS['memOn'] !== true) {
            $data = self::obtainSpecQuery('clanCount', array('id' => $clanID));
        } elseif ($overwrite) {
            $data = self::obtainSpecQuery('clanCount', array('id' => $clanID));
            $GLOBALS['cache']->set(Data::$target_site . "Clan_User_Count:" . $clanID, $data, MEMCACHE_COMPRESSED, 60);
        } else {
            if (!($data = $GLOBALS['cache']->get(Data::$target_site . "Clan_User_Count:" . $clanID))) {
                $data = self::obtainSpecQuery('clanCount', array('id' => $clanID));
                $GLOBALS['cache']->set(Data::$target_site . "Clan_User_Count:" . $clanID, $data, MEMCACHE_COMPRESSED, 60);
            }
        }
        self::logObjectSizes("Clan_User_Count", $data);
        return $data;
    }

    // Start harvesting for user
    public static function startHarvest($uid, $x, $y, $time) {
        if (!isset($uid, $x, $y, $time)) {
            return false;
        }
        $harvest = array("x" => $x, "y" => $y, "time" => $time);
        $GLOBALS['cache']->set(Data::$target_site . "User_Harvest:" . $uid, $harvest, MEMCACHE_COMPRESSED, 300);
        return $harvest;
    }

    // End harvesting
    public static function endHarvest($uid) {
        if (isset($uid) && isset($GLOBALS['cache'])) {
            $GLOBALS['cache']->delete(Data::$target_site . "User_Harvest:" . $uid);
        }
    }

    // Get harvest
    public static function getHarvest($uid) {
        if(isset($GLOBALS['cache']))
            return ((isset($uid)) ? $GLOBALS['cache']->get(Data::$target_site . "User_Harvest:" . $uid) : null);
        else
            return null;
    }

    // Set regeneration cache
    public static function setUserRegeneration($uid, $regeneration) {
        if (isset($uid, $regeneration) && $GLOBALS['memOn'] !== false) {
            $GLOBALS['cache']->set(Data::$target_site . "User_Regen:" . $uid, $regeneration, MEMCACHE_COMPRESSED, 60);
        }
    }

    // Get regeneration from cache
    public static function getUserRegeneration($uid) {
        if($GLOBALS['memOn'] !== false)
            return ((isset($uid)) ? $GLOBALS['cache']->get(Data::$target_site . "User_Regen:" . $uid) : null);
        else
            return null;
    }

}
