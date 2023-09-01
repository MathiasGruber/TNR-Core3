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

// Define class
class reputation {

    // Show items in inventory
    public function __construct() {

        $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

        // Get reputation data
        $rep_data = $GLOBALS['database']->fetch_data('SELECT * FROM `bingo_book`
            WHERE `bingo_book`.`userid` = '.$_SESSION['uid'].' LIMIT 1');

        // Get information about all the villages
        $village_data = $GLOBALS['database']->fetch_data('
            SELECT `villages`.`name`, `villages`.`leader`, `villages`.`points` FROM `villages`');

        // Add the two together
        for($i = 0, $size = count($village_data); $i < $size; $i++) {
            $village_data[$i]['rep'] = $rep_data[0][ $village_data[$i]['name'] ];
        }

        // Set counts to be shown
        $memberCount = array();
        $village_count = cachefunctions::getVillageCount();
        $active_count = cachefunctions::getVillageACount();

        foreach(array("shroud", "konoki", "silence", "samui", "shine") as $val) {
            $memberCount[$val] = $village_count[0][$val.'_vcount'];
            $memberCount['a'.$val] = $active_count[0][$val.'_vcount'];
        }

        // Send to smarty
        $GLOBALS['template']->assign('memberCount', $memberCount);

        // Show the table of users
        tableParser::show_list(
            'reputation',
            "Your Diplomacy in Seichi",
            $village_data,
            array(
                'name' => "Village",
                'rep' => "Diplomacy",
                'leader' => "Kage / Leader",
                'points' => "Funds"
            ),
            false,
            false,   // Send directly to contentLoad
            false,   // Show previous/next links
            false,  // No links at the top to show
            false,   // Allow sorting on columns
            false,   // pretty-hide options
            false, // Top stuff
            false // Top information
        );

        // Send to content
        $GLOBALS['template']->assign('contentLoad', './templates/content/reputation/reputationMain.tpl');

        if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
            throw new Exception('There was an issue releasing the lock!');
        }
    }
}
new reputation();