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

class notes {

    public function __construct() {
        $this->main_page();
    }

    // Main page
    private function main_page() {

        // Get list of current moderators
        $admins = $GLOBALS['database']->fetch_data("
            SELECT `username` 
            FROM `users`, `users_statistics` 
            WHERE 
                `users`.`id` = `users_statistics`.`uid` AND 
                (`users_statistics`.`user_rank` = 'Admin' OR 
                `users_statistics`.`user_rank` = 'ContentMember' OR 
                `users_statistics`.`user_rank` = 'ContentAdmin')
        ");
        $GLOBALS['template']->assign('admins', $admins);
        
        $adminNames = array();
        foreach( $admins as $admin ){
            $adminNames[] = $admin['username'];
        }
        
        // Show form
        if (isset($_GET['type'])) {
            switch ($_GET['type']) {
                case "ai": $type = 'Event AI';
                    break;
                case "chars": $type = 'Event Character';
                    break;
                case "item": $type = 'Item Change';
                    break;
                case "blood": $type = 'Bloodline Change';
                    break;
                case "jutsu": $type = 'Jutsu Change';
                    break;
                case "news": $type = 'News Post';
                    break;
                case "quest": $type = 'Event Quest';
                    break;
                case "travel": $type = 'Auto Event';
                    break;
                case "automated": $type = 'Automated Events';
                    break;
                case "location": $type = "Location Change"; 
                    break;
            }
        } else {
            $type = 'Bloodline Change';
        }

        $min = tableParser::get_page_min();
        $edits = $GLOBALS['database']->fetch_data("SELECT * FROM `content_edits` WHERE `title` = '" . $type . "' ORDER BY `time` DESC LIMIT " . $min . ",10");
        tableParser::show_list(
                'log', "Content Log System: ".$type, $edits, array(
            'time' => "Time",
            'aid' => "User",
            'changes' => "Information",
                ), false, 
                true, // Send directly to contentLoad
                true, 
                array(
            array("name" => "Event AI", "href" => "?id=" . $_GET["id"] . "&act=logs&type=ai"),
            array("name" => "Event Characters", "href" => "?id=" . $_GET["id"] . "&act=logs&type=chars"),
            array("name" => "Item Change", "href" => "?id=" . $_GET["id"] . "&act=logs&type=item"),
            array("name" => "Bloodlines", "href" => "?id=" . $_GET["id"] . "&act=logs&type=blood"),
            array("name" => "Jutsus", "href" => "?id=" . $_GET["id"] . "&act=logs&type=jutsu"),
            array("name" => "News Posts", "href" => "?id=" . $_GET["id"] . "&act=logs&type=news"),
            array("name" => "Automated Events", "href" => "?id=" . $_GET["id"] . "&act=logs&type=automated"),
            array("name" => "Quests", "href" => "?id=" . $_GET["id"] . "&act=logs&type=quest"),
            array("name" => "Travel Events", "href" => "?id=" . $_GET["id"] . "&act=logs&type=travel"),
            array("name" => "Location Traits", "href" => "?id=" . $_GET["id"] . "&act=logs&type=location"),
                ),
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'The following is a log of recent content changes. <br><br>
             Current members with access to this panel are: <br><i>'.implode(", ", $adminNames)."</i>"
        );
    }

}

new notes();