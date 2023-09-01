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
    abstract class Data {

        /*
         * GENERAL SERVER VARIABLES
         */
        public static $domainName;
        public static $absSvrPath;
        public static $target_site;
        public static $fbAppID;
        public static $fbAppSecret;

        /*
         * UNSURE...
         */
        public static $user;
        public static $ally;

        // Define smarty folder
        public static $smartyDir = 'Smarty-3.1.33/libs';

        // Debug object sizes
        public static $debugObjectSizes = false;
        public static $debugObjectTimes = true;
        public static $debugSessionSizes = true;
        public static $objectSizeLimit = 100000;
        public static $objectTimeLimit = 1;
        public static $objectSessionLimit = 3000;

        // Maximum pageloads / second
        public static $pageLoadLimit = 4;

        /*
         * MEMCACHE VARIABLES
         */
//        public static $memcache_server_hostname = '162.209.73.4';
//        public static $memcache_port = 39462;

        /*
         * CONSTANT DB AND SERVER VARIABLES
         */
        public static $max_level_id = 50;     //    Max User Level ID Within Database

        public static $MAX_0 = 1000;           //    AS Maximum Chakra/Stamina
        public static $MAX_1 = 1000;           //    AS Maximum Chakra/Stamina
        public static $MAX_2 = 32000;         //    Genin Maximum Chakra/Stamina
        public static $MAX_3 = 160000;        //    Chuunin Maximum Chakra/Stamina
        public static $MAX_4 = 200000;        //    Jounin Maxmum Chakra/Stamina
        public static $MAX_5 = 250000;        //    Elite Jounin Maximum Chakra/Stamina

        public static $JUT_MAX_0 = 5;         //    AS Maximum Jutsu Level
        public static $JUT_MAX_1 = 5;         //    AS Maximum Jutsu Level
        public static $JUT_MAX_2 = 100;       //    Genin Maximum Jutsu Level
        public static $JUT_MAX_3 = 100;       //    Chuunin Maximum Jutsu Level
        public static $JUT_MAX_4 = 100;       //    Jounin Maximum Jutsu Level
        public static $JUT_MAX_5 = 100;	  //    Elite Jounin Maximum Jutsu Level

        public static $ST_MAX_0 = 2500;       //    AS Maximum Stats
        public static $ST_MAX_1 = 2500;       //    AS Maximum Stats
        public static $ST_MAX_2 = 80000;      //    Genin Maximum Stats
        public static $ST_MAX_3 = 800000;     //    Chuunin Maximum Stats
        public static $ST_MAX_4 = 1000000;     //    Jounin Maximum Stats
        public static $ST_MAX_5 = 1250000;	  //    Elite Jounin Maximum Stats

        public static $GEN_MAX_0 = 500;       //    AS Maximum Generals
        public static $GEN_MAX_1 = 500;       //    AS Maximum Generals
        public static $GEN_MAX_2 = 16000;     //    Genin Maximum Generals
        public static $GEN_MAX_3 = 160000;    //    Chuunin Maximum Generals
        public static $GEN_MAX_4 = 200000;    //    Jounin Maximum Generals
        public static $GEN_MAX_5 = 250000;	  //    Elite Jounin Maximum Generals

        public static $MAX_HP_0 = 5000;       //    AS Maximum HP
        public static $MAX_HP_1 = 5000;       //    AS Maximum HP
        public static $MAX_HP_2 = 160000;     //    Genin Maximum HP
        public static $MAX_HP_3 = 1600000;    //    Chuunin Maximum HP
        public static $MAX_HP_4 = 2000000;    //    Jounin Maximum HP
        public static $MAX_HP_5 = 2500000;    //    Elite Jounin Maximum HP

        public static $EM_0 = 75000;    //    AS EM mastery
        public static $EM_1 = 75000;    //    AS EM mastery
        public static $EM_2 = 75000;    //    Genin EM mastery
        public static $EM_3 = 160000;    //    Chuunin EM mastery
        public static $EM_4 = 200000;   //    Jounin EM mastery
        public static $EM_5 = 250000;   //    Elite Jounin EM mastery

        // REPUTATION SHOP DISCOUNT (in decimal, e.g. 0.2 = 20% extra)
        public static $REP_EXTRA = 0;

        // DEFINE WHICH RANKS BELONG TO STAFF
        public static $STAFF_RANKS = array(
            'Admin',
            'Supermod',
            'Moderator',
            'Event',
            'EventMod',
            'ContentAdmin',
            'ContentMember',
            'PRmanager',
            'EventMod'
        );

        public static $MOD_STAFF_RANKS = array(
            'Admin',
            'Supermod',
            'Moderator'
        );

        public static $USERNAME_COLORS = array(
            'Kage'          => '<b><font color="#240048">%USERNAME%</font></b>',
            'Admin'         => '<b><font color="#8B0000">%USERNAME%</font></b>',
            'Moderator'     => '<b><font color="#006400">%USERNAME%</font></b>',
            'Supermod'      => '<b><font color="#008080">%USERNAME%</font></b>',
            'PRmanager'     => '<b><font color="#800080">%USERNAME%</font></b>',
            'EventMod'      => '<b><font color="#e800ae">%USERNAME%</font></b>',
            'baby'          => '<b><font color="#ff7fdf">%USERNAME%</font></b>',
            'Gold'          => '<b><font color="#DAA520">%USERNAME%</font></b>',
            'Silver'        => '<b><font color="#C0C0C0">%USERNAME%</font></b>',
            'Normal'        => '<b><font color="#191970">%USERNAME%</font></b>',
            'Tester'        => '<b><font color="#191970">%USERNAME%</font></b>',
            'ContentMember' => '<b><font color="#FFA500">%USERNAME%</font></b>',
            'ContentAdmin'  => '<b><font color="#FFA500">%USERNAME%</font></b>',
            'Rainbow' => '<b style="background-image: -webkit-gradient( linear, left top, right top, color-stop(0, #f22), color-stop(0.15, #f2f), color-stop(0.3, #22f), color-stop(0.45, #2ff), color-stop(0.6, #2f2),color-stop(0.75, #2f2), color-stop(0.9, #ff2), color-stop(1, #f22) ) !important;background-image: linear-gradient( to right, #f22, #f2f, #22f, #2ff, #2f2, #2f2, #ff2, #f22 ) !important;color:rgba(0, 0, 0, 0.5) !important;-webkit-background-clip: text !important;background-clip: text !important;font-weight:900 !important; text-shadow: none !important; ">%USERNAME%</b>'
        );

        // Accepted File Type Extensions
        public static $IMG_TYPES = array('.gif','.png');

        // My Little Entertainment Corner when shit breaks or updating (Wolfpack16)
        public static $PAGE_CLOSED_MESSAGES = array(
            1 => 'Because the system does not love me right now. ~Wolfy',
            2 => 'Because the system is playing hard to get right now. ~Wolfy',
            3 => 'Because the system is into BDSM and I am not feeling it. ~Wolfy',
            4 => 'Because the system loves to see me crying right now. ~Wolfy',
            5 => 'Because the system is being a total wanker. ~Wolfy',
            6 => 'Because the system does not like trolls. ~Wolfy',
            7 => 'Because Manly Santa is checking the list and you were not invited. ~Wolfy',
            8 => 'Because the system is causing me to have a mental breakdown. ~Wolfy',
            9 => 'Because the system cannot handle my Manly Chest Hair. ~Wolfy',
            10 => 'Because I am on a top secret mission and they almost have me. ~Wolfy',
            11 => 'Because I am not getting any attention from the ladies. ~Wolfy',
            12 => 'Because I cannot reach that unreachable spot on my back to scratch. ~Wolfy',
            13 => 'Because I am a masochist and I love it when people abuse me. ~Wolfy',
            14 => 'Because I am trying to defuse a bomb, save Timmy from the Well and Prom is tomorrow. ~Wolfy',
            15 => 'Because I am fantasizing that I am saving the ladies from an evil dragon. ~Wolfy'
        );

        // Jutsu experience gain / level
        public static $JUTSU_EXP_PER_LEVEL = array(
            "normal" => 1000,
            "clan" => 1500,
            "village" => 2000,
            "bloodline" => 1500,
            "special" => 2000,
            "loyalty" => 2000,
            "forbidden" => 5000
        );

        // Regular Village Kages Array
        public static $VILLAGE_KAGENAMES = array(
            'Konoki' => "Kan Yamato",
            'Samui' => "Oujin",
            'Silence' => "Genmei",
            'Shroud' => "Kinmei",
            'Shine' => "Kammu",
            'Syndicate' => "Susanowo"
        );

        // Regular Village Titles Array
        public static $VILLAGE_KAGETITLES = array(
            'Shine' => 'Sunakage',
            'Samui' => 'Kusakage',
            'Konoki' => 'Morikage',
            'Shroud' => 'Suikage',
            'Silence' => 'Yamakage',
            'Syndicate' => 'Warukage'
        );

        // Regular Villages Array
        public static $VILLAGE_GUARDIANS = array(
            'Konoki' => array(109,110,111),
            'Samui' => array(106,107,108),
            'Silence' => array(112,113,114),
            'Shroud' => array(115,116,117),
            'Shine' => array(103,104,105),
            'Syndicate' => array(118,119,120)
        );

        // Regular Villages Array
        public static $VILLAGES = array(
            1 => 'Konoki',
            2 => 'Samui',
            3 => 'Silence',
            4 => 'Shroud',
            5 => 'Shine',
            6 => 'Syndicate'
        );

        // Months Array
        public static $MONTHS = array(
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        );

        // Rank Names
        public static $RANKNAMES = array(
            1 => 'Academy Student',
            2 => 'Genin',
            3 => 'Chuunin',
            4 => 'Jounin',
            5 => 'Elite Jounin',
            6=>'',
            7=>'',
        );

        // ANBU squad ranks
        public static $ANBURANKS = array(
            'Trainees',
            'Rookies',
            'Veterans',
            'Elite',
            'Defenders',
            'Warbirds'
        );

        // Set the structures that can be decremented
        public static $STRUCTURENAMES = array(
            "shop",
            "hospital",
            "wall_rob",
            "wall_def"
        );

        // Max bank
        public static $MAX_BANK = 200000000;

        // Structure setting
        public static $STRUCTURES = array(
            "anbu" => 50,
            "hospital" => 200,
            "shop" => 200,
            "regen" => 100,
            "wall_rob" => 100,
            "wall_def" => 250
        );

        // Max help in PVP battles
        public static $MAX_HELP = 4;

        // Regeneration gained for each rank ID
        public static $RANK_REGEN_GAIN = array(
            2 => 130,
            3 => 235,
            4 => 105,
            5 => 160
        );

        // PvP Bonus Hour Time Periods
        // Hour Time Slot => Indicator if Time Slot Used
        public static $PVP_HOURS = array(
            0 => false, // 0:00 Hour Time
            1 => false, // 1:00 Hour Time
            2 => false, // 2:00 Hour Time
            3 => false, // 3:00 Hour Time
            4 => false, // 4:00 Hour Time
            5 => false, // 5:00 Hour Time
            6 => false, // 6:00 Hour Time
            7 => false, // 7:00 Hour Time
            8 => false, // 8:00 Hour Time
            9 => false, // 9:00 Hour Time
            10 => false, // 10:00 Hour Time
            11 => false, // 11:00 Hour Time
            12 => false, // 12:00 Hour Time
            13 => false, // 13:00 Hour Time
            14 => false, // 14:00 Hour Time
            15 => false, // 15:00 Hour Time
            16 => false, // 16:00 Hour Time
            17 => false, // 17:00 Hour Time
            18 => false, // 18:00 Hour Time
            19 => false, // 19:00 Hour Time
            20 => false, // 20:00 Hour Time
            21 => false, // 21:00 Hour Time
            22 => false, // 22:00 Hour Time
            23 => false // 23:00 Hour Time
        );
    }

    if(isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
        Data::$absSvrPath = $_SERVER['DOCUMENT_ROOT'];
        if (strpos($_SERVER['HTTP_HOST'], 'www.theninja-development.') !== false) {
            Data::$target_site = 'TND_';
            Data::$domainName = 'https://www.theninja-development.com';
            Data::$fbAppID = 867507733355549;
            Data::$fbAppSecret = "5b9056de944b033d0c449ee92d34ab6e";
        } elseif (strpos($_SERVER['HTTP_HOST'], 'www.theninja-rpg.') !== false) {
            Data::$target_site = 'TNR_';
            Data::$domainName = 'https://www.theninja-rpg.com';
            Data::$fbAppID = 327306013991565;
            Data::$fbAppSecret = "b0f36a6c50844659707593c9d934e8a5";
        } elseif (strpos($_SERVER['HTTP_HOST'], 'www.theninja-core3.') !== false) {
            Data::$target_site = 'temporary';
            Data::$domainName = 'https://www.theninja-core3.com';
            Data::$fbAppID = 327306013991565;
            Data::$fbAppSecret = "b0f36a6c50844659707593c9d934e8a5";
        } else {
            Data::$target_site = 'LOC_';
            Data::$domainName = 'https://' . $_SERVER['HTTP_HOST'];
            Data::$fbAppID = 327306013991565;
            Data::$fbAppSecret = "b0f36a6c50844659707593c9d934e8a5";
        }
    }
    else { // Change Default when on Different Servers (To Be Safe...)
        Data::$absSvrPath = '/var/app/current/';
        Data::$target_site = 'TNR_';
        Data::$domainName = 'https://www.theninja-rpg.com';
        Data::$fbAppID = 327306013991565;
        Data::$fbAppSecret = "b0f36a6c50844659707593c9d934e8a5";
    }

    // Set environment variable
    switch (Data::$target_site) {
        case 'TND_':
            $env = 'stage';
            break;

        case 'TNR_':
            $env = 'production';
            break;

        default:
            $env = 'local';
            break;
    }

    define('ENV', $env);

    require_once(Data::$absSvrPath.'/global_libs/Site/config.php');