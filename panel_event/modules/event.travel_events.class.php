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

class tileEvents {

    public function __construct() {
        if (!isset($_GET['act'])) {
            $this->currentEvents();
        }
        elseif( $_GET['act'] == "instructions" ){
            $this->instructions();
        }
        elseif( $_GET['act'] == "log" ){
            $this->showLog();
        }
        elseif ($_GET['act'] == "new") {
            if (!isset($_POST['Submit'])) {
                $this->addEventForm();
            } else {
                $this->addEventDo();
            }
        }
        elseif ($_GET['act'] == 'edit') {
            if (!isset($_POST['Submit'])) {
                $this->editEventForm();
            } else {
                $this->editEventDo();
            }
        } elseif ($_GET['act'] == 'delete') {
            if (!isset($_POST['Submit'])) {
                $this->deleteEventForm();
            } else {
                $this->deleteEventDo();
            }
        }
    }

    private function currentEvents(){

        // Events
        $events = $GLOBALS['database']->fetch_data("SELECT * FROM `events_tiles` ORDER BY `id` ASC");

        // Go therough events
        if( $events !== "0 rows" ){
            foreach( $events as $key => $entry ){

                // Set end time
                $events[ $key ]['temp_time'] = $events[ $key ]['end_time'];
                $events[ $key ]['end_time'] = ( $events[ $key ]['end_time'] > 0) ?
                         date("d-m-y, h:i", $events[ $key ]['start_time'] + $events[ $key ]['end_time']*3600) :
                        "Indefinite";

                // Set the color of endtime
                if( $events[ $key ]['temp_time'] <= 0 || $events[ $key ]['start_time']+$events[ $key ]['temp_time']*3600 > $GLOBALS['page']->load_time ){
                    $events[ $key ]['end_time'] = "<font color='green'>".$events[ $key ]['end_time']."</font>";
                }
                else{
                    $events[ $key ]['end_time'] = "<font color='red'>".$events[ $key ]['end_time']."</font>";
                }

                // Set the color of data
                $events[ $key ]['data'] = "<font color='".$this->checkData($entry['data'])."'>". $entry['data'] ."</font>";

                // Strip slashes
                $events[ $key ]['data'] = stripslashes($events[ $key ]['data']);

                // Set the color of area
                $events[ $key ]['area'] = "<font color='".$this->checkArea($entry['area'])."'>". $entry['area'] ."</font>";


            }
        }

        // Show results
        tableParser::show_list(
                'events',
                'Automated Travel Events', $events,
                array(
            'id' => "Internal ID",
            'name' => "Internal Name",
            'event_type' => "Type",
            'area' => "Area",
            'chance' => "Chance (%)",
            'data' => "Info",
            'enable_log' => "Actions Logged?",
            'redoable' => "Redoable?",
            'start_time' => "Start Time",
            'end_time' => "End"
                ), array(
            array("name" => "Modify", "act" => "edit", "type" => "en", "eid" => "table.id"),
            array("name" => "Delete", "act" => "delete", "type" => "en", "eid" => "table.id")
                ),
                true, // Send directly to contentLoad
                false,
                array(
            array("name" => "Instructions", "href" => "?id=" . $_GET["id"] . "&act=instructions"),
            array("name" => "New Event", "href" => "?id=" . $_GET["id"] . "&act=new"),
            array("name" => "User Action Log", "href" => "?id=" . $_GET["id"] . "&act=log")
                )
        );

    }

    private function showLog(){

        $min = tableParser::get_page_min();
        $num = tableParser::set_items_showed(50);
        $edits = $GLOBALS['database']->fetch_data("
              SELECT `events_log`.*, `users`.`username`
              FROM `events_log`
              LEFT JOIN `users` ON `users`.`id` = `events_log`.`uid`
              ORDER BY `time` DESC
              LIMIT " . $min . ",".$num);
        tableParser::show_list(
                'log', "Latest Logged User Actions ", $edits, array(
            'username' => "Username",
            'title' => "Event",
            'info' => "Information",
            'time' => "Time",
                ), false,
                true, // Send directly to contentLoad
                true,
            false,
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'Here you can review the latest logged user interactions with the travel events.
             Please do not log too much unneccesary information.'
        );

        $GLOBALS['template']->assign('returnLink',  true);
    }

    private function instructions(){
        $GLOBALS['template']->assign('contentLoad', './templates/content/eventPanel/autoEventInstructions.tpl');
    }

    private function checkArea( $tag ){
        $tag = preg_replace('/\s+/', '', $tag);
        if( $tag !== "" ){
            if( preg_match("/^(REGION\(\D+\)|TERRITORY\(\D+\)|AREA\(-?\d+\.-?\d+\.-?\d+\.-?\d+\))$/", $tag) ){

                // Check AREA tags more carefully
                preg_match( "/\(([^)]+)\)/" , $tag , $match );
                if( !empty($match) && preg_match( "/(AREA)/", $tag ) ){
                    $area = explode( ".", $match[1] );
                    if( $area[0] > $area[1] || $area[2] > $area[3] ){
                        return "red";
                    }
                }

                // Return green and valid as default
                return "green";
            }
        }
        return "red";
    }

    private function checkData( $tag ){
        $tag = preg_replace('/\s+/', '', $tag);
        if( $tag !== "" ){
            $tags = explode(";", $tag);
            foreach( $tags as $tag ){
                if( $tag !== "" ){
                    $temp = explode(":", $tag);
                    switch( $temp[0] ){
                        case "ITM":
                        case "JUT":
                        case "DROP":
                        case "GIVEITM":
                            if( !is_numeric($temp[1]) ){return "red";}
                        break;
                        case "user":
                            if( !preg_match("/^user:(rank_id):\d+$/", $tag) ){return "red";}
                        break;
                        case "DUP":
                        case "REP":
                            if( $temp[1] !== "yes" && $temp[1] !== "no" ){return "red";}
                        break;
                        case "AI":
                            if( !preg_match("/^AI:(\d+\.?)+$/", $tag) ){return "red";}
                        break;
                        case "LOC":
                        case "MOVE":
                            if( !preg_match("/^-?\d+\.-?\d+$/", $temp[1]) ){return "red";}
                        break;
                        case "MSG":
                            if( !preg_match("/^.+$/", $temp[1]) ){return "red";}
                        break;
                        case "OPTION":
                            if( !preg_match("/^.+$/", $temp[1]) || !is_numeric($temp[2]) ){return "red";}
                        break;
                        default: return "red"; break;
                    }
                }
            }
        }
        return "green";
    }

    private function addEventForm(){
        tableParser::parse_form('events_tiles', 'New Automated Event', array('id','start_time'));
    }

    private function addEventDo(){
        $data['start_time'] = time();
        if (tableParser::insert_data('events_tiles', $data)) {
            cachefunctions::deleteEvents();
            $GLOBALS['page']->Message("The entry has been added to the table. Cache has been cleared.", 'New Automated Event', 'id=' . $_GET['id']);
            $GLOBALS['page']->setLogEntry("Auto Event", 'New Automated Event named: <i>' . $_POST['name'] . '</i> Created'
                                                        .'<br>event_type: '.$_POST['event_type']
                                                        .'<br>area: '.$_POST['area']
                                                        .'<br>data: '.$_POST['data']
                                                        .'<br>chance: '.$_POST['chance']
                                                        .'<br>name: '.$_POST['name'],"");
        } else {
            $GLOBALS['page']->Message("An error occured while adding the entry to the table.", 'New Automated Event', 'id=' . $_GET['id']);
        }
    }

    private function editEventForm(){
        if (isset($_GET['eid']) && is_numeric($_GET['eid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `events_tiles` WHERE `id` = '" . $_GET['eid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                tableParser::parse_form('events_tiles', 'Edit Entry', array('id','start_time'), $data);
            } else {
                $GLOBALS['page']->Message("Entry could not be found.", 'Edit Automated Event', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid entry ID.", 'Edit Automated Event', 'id=' . $_GET['id']);
        }
    }

    private function editEventDo(){
        if (isset($_GET['eid']) && is_numeric($_GET['eid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `events_tiles` WHERE `id` = '" . $_GET['eid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                $changed = tableParser::check_data('events_tiles', 'id', $_GET['eid'], array());
                if (tableParser::update_data('events_tiles', 'id', $_GET['eid'])) {
                    $GLOBALS['page']->Message("The entry has been updated. Cache has been cleared. ", 'Event Quest System', 'id=' . $_GET['id']);
                    $GLOBALS['page']->setLogEntry("Auto Event", 'Entry ID: '. $_GET['eid'] .' Changed:<br>' . $changed , $_GET['eid'] );
                    cachefunctions::deleteEvents();
                } else {
                    $GLOBALS['page']->Message("An error occured while updating the entry. Possibly nothing was changed.", 'Edit Automated Event', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("Entry could not be found.", 'Edit Automated Event', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid entry ID.", 'Edit Automated Event', 'id=' . $_GET['id']);
        }
    }

    private function deleteEventForm(){
        if (isset($_GET['eid']) && is_numeric($_GET['eid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `events_tiles` WHERE `id` = '" . $_GET['eid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                $GLOBALS['page']->Confirm("Delete this Entry and all related logs!?", 'Delete Automated Event', 'Delete event & log!');
            } else {
                $GLOBALS['page']->Message("Entry could not be found.", 'Delete Automated Event', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid entry ID.", 'Delete Automated Event', 'id=' . $_GET['id']);
        }
    }

    private function deleteEventDo(){
        if (isset($_GET['eid']) && is_numeric($_GET['eid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `events_tiles` WHERE `id` = '" . $_GET['eid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                $GLOBALS['database']->execute_query("DELETE FROM `events_tiles` WHERE `id` = '" . $_GET['eid'] . "' LIMIT 1");
                $GLOBALS['database']->execute_query("DELETE FROM `events_log` WHERE `event_id` = '" . $_GET['eid'] . "'");
                cachefunctions::deleteEvents();
                $GLOBALS['page']->setLogEntry("Auto Event", 'Entry ID: <i>'. $_GET['eid'] .'</i> was deleted' ,$_GET['eid'] );
                $GLOBALS['page']->Message("The entry was removed.", 'Delete Automated Event', 'id=' . $_GET['id']);
            } else {
                $GLOBALS['page']->Message("Entry could not be found.", 'Delete Automated Event', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid entry ID.", 'Delete Automated Event', 'id=' . $_GET['id']);
        }
    }
}

new tileEvents();