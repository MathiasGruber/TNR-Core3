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
    if( isset($_SESSION['uid']) ){

        // Get votes
        $timers = $GLOBALS['database']->fetch_data("SELECT * FROM `votes` WHERE `userID` = '" . $GLOBALS['userdata'][0]['id'] . "' LIMIT 1");
        if ($timers == '0 rows') {
            $GLOBALS['database']->execute_query("INSERT INTO `votes` ( `userID` ) VALUES (" . $GLOBALS['userdata'][0]['id'] . ")");
            $timers = $GLOBALS['database']->fetch_data("SELECT * FROM `votes` WHERE `userID` = '" . $GLOBALS['userdata'][0]['id'] . "' LIMIT 1");
        }
        $valid = array(
            "AWG" => array("Apex Web Gaming", "http://apexwebgaming.com/in/982/".$GLOBALS['userdata'][0]['id']),
            "TWG" => array("Top Web Games", "http://www.topwebgames.com/in.asp?id=3575&amp;alwaysreward=1&amp;vuser=".$GLOBALS['userdata'][0]['id']),
            "DOG" => array("DirectoryOfGames", "http://www.directoryofgames.com/main.php?view=topgames&amp;action=vote&amp;v_tgame=1487&amp;votedef=".$GLOBALS['userdata'][0]['id'] ),
            "GALAXY" => array("Galaxy News", "?id=71&amp;act=GALAXY"),
            "OGLAB" => array("OG Labs", "?id=71&amp;act=OGLAB")
        );
        $votingLinks = array();
        foreach( $valid as $key => $value ){
            if( $timers[0][ $key ] < (time() - 86400) ){
                $votingLinks[] = array( "title" => $value[0], "link" => $value[1] );
            }
        }
        if( !empty($votingLinks) ){
            $GLOBALS['template']->assign('votingLinks', $votingLinks );
        }

        // Blue messages
        $GLOBALS['template']->assign('blueMessages', cachefunctions::getLatestBluemessages() );

        // News
        $GLOBALS['template']->assign('newsItem', cachefunctions::getLatestNewsItem() );

        // Global Events
        $GLOBALS['template']->assign('globalEvents', $GLOBALS['globalevents'] );

        // Content changes
        $GLOBALS['template']->assign('contentChanges', cachefunctions::getLatestChanges() );

        // Template
        $GLOBALS['template']->assign('contentLoad','./templates/content/welcome/gameOverview.tpl');

    }
    else{
        $GLOBALS['template']->assign('newsItems', cachefunctions::getLatestNews() );
        $GLOBALS['template']->assign('contentLoad','./templates/content/welcome/welcome.tpl');
    }