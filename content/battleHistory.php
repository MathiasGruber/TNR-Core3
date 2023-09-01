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

// Show history of battle
class battleHistory {

    private $__filters = array(
        'all' => array('name' => 'All', 'types' => array()),
        'pvp' => array('name' => 'PvP', 'types' => array('spar', 'kage', 'clan', 'combat')),
        'mission' => array('name' => 'Mission', 'types' => array('mission', 'crime')),
        'torn' => array('name' => 'Torn Battle Ring', 'types' => array('torn_battle')),
        'mirror' => array('name' => 'Mirror Arena', 'types' => array('mirror_battle')),
        'arena' => array('name' => 'Battle Arena', 'types' => array('arena')),
        'other' => array('name' => 'Other', 'types' => array('event', 'territory')),
    );

    // Show people in the area you can attack
    public function __construct() {

        $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

        $topOptions = array();

        foreach ($this->__filters as $key => $value) {
            $topOptions[] = array(
                'name' => $value['name'],
                'href' => '?id=' . $_GET['id'] . '&filter=' . $key,
            );
        }

        // Decide on which filter to use
        $filter = "all";
        if( isset($_GET['filter']) && preg_match( '/(' . implode('|', array_keys($this->__filters)) . ')/i', $_GET['filter'] ) ){
            $filter = $_GET['filter'];
        }

        // Get the log & filter it
        $combatLog = cachefunctions::getCombatLog($_SESSION['uid']);
        $combatLog = $this->__filter($combatLog, $filter);

        // Variable for total rows
        $totalRows = 0;

        // Go threough the log
        if( !empty($combatLog) ){

            // Only select min/max rows
            $totalRows = count($combatLog);
            $showRows = 30;
            $min =  tableParser::get_page_min();
            $number = tableParser::set_items_showed( $showRows );
            $offset = - ($min + $showRows);

            // Set max offset
            if( abs($offset) > $totalRows ){
                $offset = -$totalRows;
            }

            // Select only rows to show
            $combatLog = array_slice( $combatLog , $offset, $showRows );

            $i = 1 + $totalRows + $offset;
            foreach( $combatLog as $key => $logEntry ){

                // Set ID
                $combatLog[ $key ]['id'] = $i;

                // Set status text
                switch( $combatLog[ $key ][2] ){
                    case "wins": $combatLog[ $key ][2] = "Won"; break;
                    case "losses": $combatLog[ $key ][2] = "Lost"; break;
                    case "draws": $combatLog[ $key ][2] = "Draw"; break;
                    case "fled": $combatLog[ $key ][2] = "Fled"; break;
                }

                // Capitalize type
                $combatLog[ $key ][0] = ucfirst($combatLog[ $key ][0]);

                $i++;
            }
            krsort($combatLog);
        }
        else{
            $combatLog = "0 rows";
        }

        // Show the table of users
        tableParser::show_list(
             'users',
             'Battle History: ' . $this->__filters[$filter]['name'],
            $combatLog,
                array(
            'id' => "Battle ID",
            '0' => "Battle Type",
            '3' => "Opponent Name",
            '2' => "Battle Status"
                ),
                false,
            true, // Send directly to contentLoad
            true, // No newer/older links
            $topOptions,
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            "This list contains your past ".$totalRows." battles, which you can account for. Battles are not remembered forever, and the list is cleared every 12 hours of inactivity if it hasn't been updated. It may also be cleared after performing missions and the likes. If you are on a mission, order or quest where you are assigned to killing a certain NPC, you must be able to account for your kill and therefore it must be on this list. Note that your character can only remember up to 300 PVP battles and 125 Non-PVP battles at a time."
        );

        if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
            throw new Exception('There was an issue releasing the lock!');
        }
    }

    private function __filter($combatLog, $filter) {

        $filtered = array();

        if (empty($combatLog)) {
            return $filtered;
        }

        foreach ($combatLog as $combat) {

            if (empty($this->__filters[$filter]['types']) || in_array($combat[0], $this->__filters[$filter]['types'])) {
                $filtered[] = $combat;
            }
        }

        return $filtered;
    }



}

new battleHistory();