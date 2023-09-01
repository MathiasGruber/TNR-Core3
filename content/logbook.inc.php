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

class logbook {

    public function __construct() {

        try {
            // Get the task library and instantiate
            require_once(Data::$absSvrPath.'/libs/taskQuestMission.inc.php');
            $this->taskLibrary = new tasks;

            if( !isset($_GET['act']) ){
                $this->main_logbook();
            }
            elseif( $_GET['act'] == "details"){
                $this->showDetails();
            }
            else{
                $this->main_logbook();
            }
        }
        catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Logbook System', 'id=2','Return to Profile');
        }

    }


    private function main_logbook() {



        // Get user tasks
        $userTasks = cachefunctions::getUserTasks( $_SESSION['uid'] );
        $userTasks = json_decode($userTasks[0]['tasks'], true);

        // Get All Entries
        $allEntries = cachefunctions::getTasksQuestsMissions( );

        // Get and check for completes tasks, missions, orders and quests
        $check = $this->taskLibrary->checkTasks(
                array(
                    "hook"=>"logbook",
                    "allTasks" => $allEntries,
                    "userTasks" => $userTasks
                )
        );



        // Update user cache
        if( $check ){
            unset($userTasks);
            $userTasks = cachefunctions::getUserTasks( $_SESSION['uid'] );
            $userTasks = json_decode($userTasks[0]['tasks'], true);

            // Check if we are to show stuff also
            if( !empty( $this->taskLibrary->rewardInfo ) ){

                // To show
                $toShow = array();
                foreach( $this->taskLibrary->rewardInfo as $entry ){
                    $toShow[] = array("id" => $entry['id'], "name" => $entry['name'], "rewards" => implode(", ", $entry['rewards']) );
                }

                // Show form
                tableParser::show_list(
                    'updates',
                    'LogBook Updates',
                    $toShow,
                    array("name" => "Entry Name", "rewards" => "Rewards for completion"),
                    array(
                        array("name" => "Details", "act" => "details", "eid" => "table.id")
                    ),
                    false,
                    false,
                    false, // Not top links
                    false, // No sorting on columns
                    false, // No pretty options
                    false, // No top search field
                    "Since your last visit, the following logbook entries have been completed, and rewards added accordingly"
                );

            }
        }


        // Decide on which filter to use
        $filter = "active";
        if( isset($_GET['filter']) && preg_match( "/(active|completed|quests|orders|special)/i", $_GET['filter'] ) ){
            $filter = $_GET['filter'];
        }

        // Check for reward information
        $rewardInfo = $this->taskLibrary->rewardInformation;

        // Entries to show
        $columns = array(
            'name' => "Name",
            'type' => "Entry Type",
            'status' => "Status",
            'levelReq' => "Lvl Req",
            'levelMax' => "Lvl Max"
        );

        // Get data
        $min =  tableParser::get_page_min();
        $allEntries = $this->taskLibrary->filterEntries( $allEntries, $userTasks, $filter , $min );

        // Time left for quests
        foreach( $allEntries as $key => $entry ){

            if(
                $entry['type'] == "Quest" &&
                $entry['status'] !== "Completed" &&
                $entry['status'] !== "Failed" &&
                $entry['timeLeft'] !== "disabled"
            ){
                if( $entry['timeLeft'] > 0 ){
                    $allEntries[$key]['name'] .= "<br>Deadline: ".functions::convert_time( $entry['timeLeft'], "questTime".$entry['id']);
                }
                elseif( $entry["questRepeatable"] == "yes" ){
                    $this->taskLibrary->removeQuest($userTasks, $entry['id']);
                    $allEntries[$key]['name'] .= "<br><i>Now deactivating quest</i>";
                }
                else{
                    $allEntries[$key]['status'] = "Failed";
                }
            }
            elseif(
                (($allEntries[$key]['status'] == "Completed" && $entry["questRepeatable"] == "yes" ) ||
                 ($allEntries[$key]['status'] == "Failed" && $entry["questRepeatable"] == "yes" ) ||
                 ($allEntries[$key]['status'] == "Failed" && $entry["failedRepeatable"] == "yes" ) )  &&
                isset($entry["timeStamp"])
            ){
                // Check max level of entry
                if( $GLOBALS['userdata'][0]['level_id'] <= $entry['levelMax'] && $GLOBALS['userdata'][0]['level_id'] >= $entry['levelReq'] ){

                    $toReactivate = $entry["timeStamp"] + 24*3600 - $GLOBALS['user']->load_time;
                    if( $toReactivate > 0 ){
                        $allEntries[$key]['name'] .= "<br>Reactivate: ".functions::convert_time( $toReactivate, "questTime".$entry['id']);
                    }
                    else{
                        if( !isset($_GET['act2'],$_GET['eid']) || $_GET['eid'] !== $entry['id']){
                            $allEntries[$key]['name'] .= "<br><i><a href='?id=".$_GET['id']."&amp;filter=quests&amp;act2=activate&amp;eid=".$entry['id']."'>Reactivate Quest</a></i>";
                        }
                        else{
                            $this->taskLibrary->activateQuest($userTasks, $entry['id']);
                            $allEntries[$key]['name'] .= "<br><i>Quest has been reactivated</i>";
                        }
                    }
                }
            }
        }


        // Show form
        tableParser::show_list(
            'entries',
            'LogBook: '.  ucfirst($filter),
            $allEntries,
            $columns,
            array(
                array("name" => "Details", "act" => "details", "eid" => "table.id")
            ),
            false,
            true, // No newer/older links
            array(
                array("name" => "Active", "href" =>"?id=".$_GET["id"]."&amp;filter=active"),
                array("name" => "Orders", "href" =>"?id=".$_GET["id"]."&amp;filter=orders"),
                array("name" => "Completed", "href" =>"?id=".$_GET["id"]."&amp;filter=completed"),
                array("name" => "Quests", "href" =>"?id=".$_GET["id"]."&amp;filter=quests"),
                array("name" => "Special", "href" =>"?id=".$_GET["id"]."&amp;filter=special")
            ),
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            $rewardInfo
        );

        // Show log
        $GLOBALS['template']->assign('contentLoad', './templates/content/logbook/main.tpl');


    }

    private function showDetails(){
        $this->taskLibrary->setEntryDetails( $_GET['eid'] );
    }

}

new logbook();