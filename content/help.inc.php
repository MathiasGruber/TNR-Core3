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

class GameHelp {

    public function __construct() {

        try {
            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            if (isset($_GET['act']) && $_GET['act'] === 'crewlist') {
                $this->crewlist();
            } else {
                $this->main_screen();
            }

            if ($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        } catch (Exception $e) {
            $GLOBALS['page']->Message($e->getMessage(), "Game Help", 'id=' . $_GET['id'], 'Return');
        }
    }

    private function main_screen() {
        $GLOBALS['template']->assign('contentLoad', './templates/content/help/help.tpl');
    }

    private function crewlist() {

        // Get the table parser
        require_once(Data::$absSvrPath . '/global_libs/General/tableparser.inc.php');

        // Create the moderator list

        $staff_members = $GLOBALS['database']->fetch_data('SELECT `users`.`id`, `users`.`username`,
            `users_timer`.`last_activity`,
            `users_statistics`.`user_rank`
            FROM `users_statistics`
                INNER JOIN `users` ON (`users`.`id` = `users_statistics`.`uid`)
                INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
            WHERE
                ( `users_statistics`.`user_rank` IN ("Moderator", "Supermod") OR `username` = "AlbaficaPisces")
                ORDER BY `users_statistics`.`user_rank` ASC, `users_timer`.`last_activity` DESC');

        foreach ($staff_members as $key => $member) {
            if ($member['user_rank'] == "Supermod") {
                $staff_members[$key]['user_rank'] = "Head Moderator";
            }
            if ($member['username'] == "AlbaficaPisces") {
                $staff_members[$key]['user_rank'] = "Staff Admin";
            }
        }


        // Show form
        tableParser::show_list(
                'moderators', 'Moderator Team', $staff_members, array(
            'username' => "Username",
            'user_rank' => "Rank",
            'last_activity' => "Online Status"
                ), array(
            array("id" => 13, "name" => "Profile", "page" => "profile", "profile" => "table.username")
                ), false, // Send directly to contentLoad
                false, // No newer/older links
                false, // No top options links
                false, // No sorting on columns
                false, // No pretty options
                false, // No top search field
                'Moderators are the ones who manage the law and order of TNR. Head moderators control and manage the moderators, and have additional privileges to activate accounts, change emails, etc. '
        );
        $moderators = $supermoderators = $admins = array();


        $balanceTeam = $GLOBALS['database']->fetch_data('SELECT `users`.`id`, `users`.`username`,
            `users_timer`.`last_activity`,
            "Team Member" as user_rank
            FROM `users_statistics`
                INNER JOIN `users` ON (`users`.`id` = `users_statistics`.`uid`)
                INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
            WHERE
                ( `users_statistics`.`user_rank` = ("ContentMember") )
                ORDER BY `users_statistics`.`user_rank` ASC, `users_timer`.`last_activity` DESC');
        if( $balanceTeam == "0 rows" ){
            $balanceTeam = array();
        }

        //$contentTeam[] = array("username" => "Pana", "user_rank" => "Lead Content Developer");


        tableParser::show_list(
                'balanceTeam', 'Balance Team', $balanceTeam, 
                array(
                    'username' => "Username",
                    'user_rank' => "Rank"
                ),
                array(
                    array("id" => 13, "name" => "Profile", "page" => "profile", "profile" => "table.username")
                ),
                false, // Send directly to contentLoad
                false, // No newer/older links
                false, // No top options links
                false, // No sorting on columns
                false, // No pretty options
                false, // No top search field
                'Balance Team members are in charge of making sure the game is "Balanced", in the sense that the pvp and pve aspects of the game should be balanced and fun. The team is led by <b>Noah</b>'
        );

        $eventTeam = $GLOBALS['database']->fetch_data('SELECT `users`.`id`, `users`.`username`,
            `users_timer`.`last_activity`,
            "Team Member" as user_rank
            FROM `users_statistics`
                INNER JOIN `users` ON (`users`.`id` = `users_statistics`.`uid`)
                INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
            WHERE
                ( `users_statistics`.`user_rank` = ("EventMod") )
                ORDER BY `users_statistics`.`user_rank` ASC, `users_timer`.`last_activity` DESC');
        if( $eventTeam == "0 rows" ){
            $eventTeam = array();
        }

        $eventTeam[] = array("username" => "Kiira", "user_rank" => "Event Admin");

        tableParser::show_list(
            'eventTeam', 'Event Team', $eventTeam, 
            array(
                'username' => "Username",
                'user_rank' => "Rank"
            ),
            array(
                array("id" => 13, "name" => "Profile", "page" => "profile", "profile" => "table.username")
            ), 
            false, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Event Team is responsible for all events and text related features. It is their job to keep things interesting and fun for the userbase.'
        );




        $prTeam = $GLOBALS['database']->fetch_data('SELECT `users`.`id`, `users`.`username`,
            `users_timer`.`last_activity`,
            "Team Member" as user_rank
            FROM `users_statistics`
                INNER JOIN `users` ON (`users`.`id` = `users_statistics`.`uid`)
                INNER JOIN `users_timer` ON (`users_timer`.`userid` = `users`.`id`)
            WHERE
                ( `users_statistics`.`user_rank` = ("PRmanager") )
                ORDER BY `users_statistics`.`user_rank` ASC, `users_timer`.`last_activity` DESC');
        if( $prTeam == "0 rows" ){
            $prTeam = array();
        }

        tableParser::show_list(
            'prTeam', 'PR Team', $prTeam, 
            array(
                'username' => "Username",
                'user_rank' => "Rank"
            ),
            array(
                array("id" => 13, "name" => "Profile", "page" => "profile", "profile" => "table.username")
            ), 
            false, // Send directly to contentLoad
            false, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'PR team is responsible for public relations and advertisement - it is their job to keep the users happy and facilitate better communication efforts.'
        );

        // Start smarty template
        $GLOBALS['template']->assign('contentLoad', './templates/content/help/help_crew.tpl');
    }

}

new GameHelp();
