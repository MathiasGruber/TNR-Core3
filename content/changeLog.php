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

class changeLog {

    // Show log
    public function __construct() {

        // Get lock if logged in
        if(isset($_SESSION['uid']))
            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);
        else
            $GLOBALS['database']->get_lock(__METHOD__,123,__METHOD__);


        // Show form
        $min =  tableParser::get_page_min();
        $changes = $GLOBALS['database']->fetch_data("SELECT * FROM `log_changeLog` ORDER BY `time` DESC LIMIT ".$min.", 10");

        if( $changes !== "0 rows" ){
            foreach( $changes as $key => $value ){
                $changes[$key]['id'] += 1000;
                $changes[$key]['id'] /= 1000;
                $changes[$key]['version'] = "v.".$changes[$key]['id'];
            }
        }


        // Show the table of changes
        tableParser::show_list(
            'log',
            'TNR ChangeLog',
            $changes,
            array(
                'version' => "Change ID",
                'author' => "Author",
                'time' => "Date",
                'info' => "Info"
            ),
            false,
            true, // Send directly to contentLoad
            true,   // Show previous/next links
            false,  // No links at the top to show
            false,   // Allow sorting on columns
            false,   // pretty-hide options
            false, // Top stuff
            "Following entries reflect the latest changes to the game.
             The list may be incomplete, but we strive to post an entry everytime we change something.
             Changes marked with '[Code]' reflect changes in the code - such changes are not always
             immidiately uploaded to the main server, but rather reflect changes being implemented on our
             development environment; i.e. soon to be released updates." // Top information
        );

        if( isset($_SESSION['uid']) && $GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
            throw new Exception('There was an issue releasing the lock!');
        }
    }
}

new changeLog();