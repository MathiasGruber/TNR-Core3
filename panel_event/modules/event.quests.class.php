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

require_once($_SERVER['DOCUMENT_ROOT'].'/libs/notificationSystem/notificationSystem.php');

class blueMessage {

    public function __construct() {
        if(isset($_GET['qid']) && !is_numeric($_GET['qid']))
            $_GET['qid'] = '';

        if(isset($_GET['status']) && !is_numeric($_GET['status']))
            $_GET['qstatusd'] = '';

        if(isset($_GET['uid']) && !is_numeric($_GET['uid']))
            $_GET['uid'] = '';


        if (!isset($_GET['act'])) {
            $this->quest_screen();
        } elseif ($_GET['act'] == 'new_quest') {
            if (!isset($_POST['Submit'])) {
                $this->quest_new_form();
            } else {
                $this->quest_upload_new();
            }
        } elseif ($_GET['act'] == 'edit_quest') {
            if (!isset($_POST['Submit'])) {
                $this->quest_edit_form();
            } else {
                $this->quest_do_edit();
            }
        } elseif ($_GET['act'] == 'delete_quest') {
            if (!isset($_POST['Submit'])) {
                $this->quest_confirm_delete();
            } else {
                $this->quest_do_delete();
            }
        } else if ($_GET['act'] == 'stats_and_actions') {

            $this->actions_screen();

        } else if ($_GET['act'] == 'who_has') {

            $this->who_has_screen();

        } elseif ($_GET['act'] == 'give_quest') {

            if (!isset($_GET['username'])) {
                $this->give_quest_screen();
            } else {
                $this->do_give_quest();
            }
            
        } elseif ($_GET['act'] == 'clean_quest') {

            if (!isset($_POST['Submit'])) {
                $this->confirm_action('clean');
            } else {
                $this->do_clean_quest();
            }
            
        } elseif ($_GET['act'] == 'know_quest') {

            if (!isset($_POST['Submit'])) {
                $this->confirm_action('know');
            } else {
                $this->do_know_quest();
            }
            
        } elseif ($_GET['act'] == 'activate_quest') {

            if (!isset($_POST['Submit'])) {
                $this->confirm_action('active');
            } else {
                $this->do_activate_quest();
            }
            
        } elseif ($_GET['act'] == 'complete_quest') {

            if (!isset($_POST['Submit'])) {
                $this->confirm_action('completed');
            } else {
                $this->do_complete_quest();
            }
            
        } elseif ($_GET['act'] == 'close_quest') {

            if (!isset($_POST['Submit'])) {
                $this->confirm_action('closed');
            } else {
                $this->do_close_quest();
            }
            
        } elseif ($_GET['act'] == 'kill_quest') {

            if (!isset($_POST['Submit'])) {
                $this->confirm_action('dead');
            } else {
                $this->do_kill_quest();
            }
            
        } elseif ($_GET['act'] == 'delete_quest') {

            if (!isset($_POST['Submit'])) {
                $this->confirm_action('deleted');
            } else {
                $this->do_delete_quest();
            }
            
        }
    }

    //quest management
    private function actions_screen() {
        $quest = $GLOBALS['database']->fetch_data("SELECT `name`, `users_quests`.`qid`, `status`, count(`status`) as 'count' FROM `users_quests` inner join `quests` on (`users_quests`.`qid` = `quests`.`qid`) where `users_quests`.`qid` = ".$_GET['qid']." group by `status`");
        tableParser::show_list('quest',
                                $quest[0]['name'],
                                $quest,
                                array('status'=>'status','count'=>'count'), 
                                array( 
                                    array('name'=>'who_has',
                                          'act'=>'who_has',
                                          'qid'=>'table.qid', 'status'=>'table.status'),

                                    array('name'=>'Make Known', 
                                          'act'=>'know_quest', 
                                          'qid'=>'table.qid', 'status'=>'table.status'),

                                    array('name'=>'Clear Failure', 
                                          'act'=>'clean_quest', 
                                          'qid'=>'table.qid', 'status'=>'table.status'),

                                    array('name'=>'Make Active', 
                                          'act'=>'activate_quest', 
                                          'qid'=>'table.qid', 'status'=>'table.status'),

                                    array('name'=>'Make Completed', 
                                          'act'=>'complete_quest', 
                                          'qid'=>'table.qid', 'status'=>'table.status'),

                                    array('name'=>'Make Closed', 
                                          'act'=>'close_quest', 
                                          'qid'=>'table.qid', 'status'=>'table.status'),

                                    array('name'=>'Make Dead', 
                                          'act'=>'kill_quest', 
                                          'qid'=>'table.qid', 'status'=>'table.status'),

                                    array('name'=>'Delete', 
                                          'act'=>'delete_quest', 
                                          'qid'=>'table.qid', 'status'=>'table.status') ), 

                                true, false, false, false, false, false, 
                                "Status Key: 0=known, 1=active, 2=completed, 3=closed, 4=dead" );

        $GLOBALS['template']->assign('returnLink', "?id={$_GET["id"]}");
    }

    //quest management
    private function who_has_screen()
    {
        $users = $GLOBALS['database']->fetch_data(" SELECT `users_quests`.`qid`, `users_quests`.`status`, `users_quests`.`uid`,
                                                           `users_quests`.`attempts`, `users_quests`.`failed`, `users_quests`.`turned_in`,
                                                           `users_quests`.`timestamp_learned`, `users_quests`.`timestamp_updated`, `users_quests`.`timestamp_turned_in`,

                                                           `quests`.`name`, 

                                                           `users`.`username`,

                                                           `users_statistics`.`rank`


                                                    FROM `users_quests` 
                                                        inner join `quests` on (`users_quests`.`qid` = `quests`.`qid`) 
                                                        inner join `users` on (`users_quests`.`uid` = `users`.`id`)
                                                        inner join `users_statistics` on (`users`.`id` = `users_statistics`.`uid`)

                                                    WHERE `users_quests`.`qid` = ".$_GET['qid']." 
                                                          and 
                                                          `users_quests`.`status` = ".$_GET['status']);
        tableParser::show_list('users',
                                $users[0]['name'].' where status: '.$_GET['status'],
                                $users,
                                array('username'=>'User',
                                      'rank'=>'Rank',
                                      'attempts'=>'Attempts',
                                      'failed'=>'Failed',
                                      'turned_in'=>'Turned In',
                                      'timestamp_learned'=>'Learned',
                                      'timestamp_updated'=>'Updated',
                                      'timestamp_turned_in'=>'Turned In'), 
                                array( 
                                    array('name'=>'Make Known', 
                                          'act'=>'know_quest', 
                                          'qid'=>'table.qid', 'status'=>'table.status', 'uid'=>'table.uid'),

                                    array('name'=>'Clear Failure', 
                                          'act'=>'clean_quest', 
                                          'qid'=>'table.qid', 'status'=>'table.status', 'uid'=>'table.uid'),

                                    array('name'=>'Make Active', 
                                          'act'=>'activate_quest', 
                                          'qid'=>'table.qid', 'status'=>'table.status', 'uid'=>'table.uid'),

                                    array('name'=>'Make Completed', 
                                          'act'=>'complete_quest', 
                                          'qid'=>'table.qid', 'status'=>'table.status', 'uid'=>'table.uid'),

                                    array('name'=>'Make Closed', 
                                          'act'=>'close_quest', 
                                          'qid'=>'table.qid', 'status'=>'table.status', 'uid'=>'table.uid'),

                                    array('name'=>'Make Dead', 
                                          'act'=>'kill_quest', 
                                          'qid'=>'table.qid', 'status'=>'table.status', 'uid'=>'table.uid'),

                                    array('name'=>'Delete', 
                                          'act'=>'delete_quest', 
                                          'qid'=>'table.qid', 'status'=>'table.status', 'uid'=>'table.uid') ), 

                                true, false, false, false, false, false, 
                                "Time stamp converter: <a href='https://www.unixtimestamp.com/'>https://www.unixtimestamp.com/</a> " );

        $GLOBALS['template']->assign('returnLink', "?id={$_GET["id"]}&act=stats_and_actions&qid={$_GET['qid']}");
    }

    // quest creation
    private function quest_screen() {

        // Show form
        $min = tableParser::get_page_min();

        if(isset($_POST['search']) && $_POST['search'])
        {
            if($_POST['search'] != 'qid')
                $where = "WHERE `{$_POST['search']}` LIKE '%{$_POST[$_POST['search']]}%'";
            else
                $where = "WHERE `{$_POST['search']}` = {$_POST[$_POST['search']]}";
        }
        else
            $where = '';

        $query = "SELECT * FROM `quests` {$where} ORDER BY `qid` DESC LIMIT {$min},25";

        $quests = $GLOBALS['database']->fetch_data($query);


        tableParser::show_list(
                'quest',
                'Quest admin',
                $quests,
                array(
                    'qid' => 'qid',
                    'state' => "state",
                    'name' => "name",
                    'level' => "level",
                    'category' => "category",
                    'category_skin' => "category_skin",
                    'notes' => 'notes'
                ), 
                array(
                    array("name" => "Stats & Actions", 'act'=>'stats_and_actions', 'qid'=> 'table.qid'),
                    array('name'=>'Give', 'act'=>'give_quest', 'qid'=>'table.qid'),
                    array("name" => "Modify", "act" => "edit_quest", "qid" => "table.qid"),
                    array("name" => "Delete", "act" => "delete_quest", "qid" => "table.qid")
                ), 
                true, // Send directly to contentLoad
                true, 
                array(
                    array("name" => "New quest", "href" => "?id=" . $_GET["id"] . "&act=new_quest")
                ),
                true,
                false,
                array(
                    array(
                        'infoText'=>'Qid',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'qid',
                        'postIdentifier'=>'search',
                        'inputName'=>'qid'
                    ),
                    array(
                        'infoText'=>'Name',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'name',
                        'postIdentifier'=>'search',
                        'inputName'=>'name'
                    ),
                    array(
                        'infoText'=>'Level',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'level',
                        'postIdentifier'=>'search',
                        'inputName'=>'level'
                    ),
                    array(
                        'infoText'=>'Category',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'category',
                        'postIdentifier'=>'search',
                        'inputName'=>'category'
                    ),
                    array(
                        'infoText'=>'Notes',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'notes',
                        'postIdentifier'=>'search',
                        'inputName'=>'notes'
                    )
                )
        );
    }


    private function quest_confirm_delete() {
        if (isset($_GET['qid'])) {
            $GLOBALS['page']->Confirm("Delete this quest?", 'Quest System', 'Delete now!');
        } else {
            $GLOBALS['page']->Message("No valid quest ID was specified.", 'Quests System', 'id=' . $_GET['id']);
        }
    }


    private function confirm_action($status) {
        if (isset($_GET['qid']) && isset($_GET['status']) && !isset($_GET['uid'])) {
            $GLOBALS['page']->Confirm("Set all users quest status:".$_GET['status']." to ".$status."?", 'Quest System', 'set All users quest as: '.$status.' now!');
        } else if(isset($_GET['qid']) && isset($_GET['status']) && isset($_GET['uid'])) {
            $GLOBALS['page']->Confirm("Set user:".$_GET['uid']." quest status:".$_GET['status']." to ".$status."?", 'Quest System', 'set User quest as: '.$status.' now!');
        } else {
            $GLOBALS['page']->Message("No valid status or quest ID was specified.", 'Quests System', 'id=' . $_GET['id']);
        }
    }

    private function do_clean_quest()
    {
        $query = "UPDATE `users_quests`
                  SET `failure` = 0,
                      `attempts` = 0,
                      `timestamp_updated` = ".time().",
                      `dialog_chain` = '',
                      `data` = ''
                  WHERE `qid` = ".$_GET['qid']." AND `status` = ".$_GET['status'];
                  
        $return_link = "?id={$_GET["id"]}&qid={$_GET['qid']}&act=";

        if(isset($_GET['uid']))
        {
            $query .= " AND `uid` = ".$_GET['uid'];
            $return_link .= 'who_has';
        }
        else
        {
            $_GET['uid'] = 'all';
            $return_link .= 'stats_and_actions';
        }

        if($GLOBALS['database']->execute_query($query))
        {
            $GLOBALS['page']->setLogEntry("Quest Change", 'quest: '. $_GET['qid'] .' status: '. $_GET['status'] . 'user: '. $_GET['uid'] . ' clear attempts and failure ', $_GET['qid'] );
            $GLOBALS['page']->Message("Success", 'Quests System', $return_link);
        }
        else
            $GLOBALS['page']->Message("FAILURE! FAILURE! FAILURE!<br>".$query, 'Quests System', $return_link);
    }

    private function do_know_quest()
    {
        $query = "UPDATE `users_quests`
                  SET `status` = 0,
                      `reset_status` = 0,
                      `timestamp_updated` = ".time().",
                      `dialog_chain` = '',
                      `data` = ''
                  WHERE `qid` = ".$_GET['qid']." AND `status` = ".$_GET['status'];

        $return_link = "?id={$_GET["id"]}&qid={$_GET['qid']}&act=";

        if(isset($_GET['uid']))
        {
            $query .= " AND `uid` = ".$_GET['uid'];
            $return_link .= 'who_has';
        }
        else
        {
            $_GET['uid'] = 'all';
            $return_link .= 'stats_and_actions';
        }

        if($GLOBALS['database']->execute_query($query))
        {
            $GLOBALS['page']->setLogEntry("Quest Change", 'quest: '. $_GET['qid'] .' status: '. $_GET['status'] . 'user: '. $_GET['uid'] . ' set_to: known', $_GET['qid'] );
            $GLOBALS['page']->Message("Success", 'Quests System', $return_link);
        }
        else
            $GLOBALS['page']->Message("FAILURE! FAILURE! FAILURE!<br>".$query, 'Quests System', $return_link);
    }

    private function do_activate_quest()
    {
        $query = "UPDATE `users_quests`
                  SET `status` = 1,
                      `reset_status` = 1,
                      `timestamp_updated` = ".time().",
                      `dialog_chain` = '',
                      `data` = ''
                  WHERE `qid` = ".$_GET['qid']." AND `status` = ".$_GET['status'];

        $return_link = "?id={$_GET["id"]}&qid={$_GET['qid']}&act=";

        if(isset($_GET['uid']))
        {
            $query .= " AND `uid` = ".$_GET['uid'];
            $return_link .= 'who_has';
        }
        else
        {
            $_GET['uid'] = 'all';
            $return_link .= 'stats_and_actions';
        }

        if($GLOBALS['database']->execute_query($query))
        {
            $GLOBALS['page']->setLogEntry("Quest Change", 'quest: '. $_GET['qid'] .' status: '. $_GET['status'] . 'user: '. $_GET['uid'] . ' set_to: active', $_GET['qid'] );
            $GLOBALS['page']->Message("Success", 'Quests System', $return_link);
        }
        else
            $GLOBALS['page']->Message("FAILURE! FAILURE! FAILURE!<br>".$query, 'Quests System', $return_link);
    }

    private function do_complete_quest()
    {
        $query = "UPDATE `users_quests`
                  SET `status` = 2,
                      `reset_status` = 0,
                      `timestamp_updated` = ".time()."
                  WHERE `qid` = ".$_GET['qid']." AND `status` = ".$_GET['status'];

        $return_link = "?id={$_GET["id"]}&qid={$_GET['qid']}&act=";

        if(isset($_GET['uid']))
        {
            $query .= " AND `uid` = ".$_GET['uid'];
            $return_link .= 'who_has';
        }
        else
        {
            $_GET['uid'] = 'all';
            $return_link .= 'stats_and_actions';
        }

        if($GLOBALS['database']->execute_query($query))
        {
            $GLOBALS['page']->setLogEntry("Quest Change", 'quest: '. $_GET['qid'] .' status: '. $_GET['status'] . 'user: '. $_GET['uid'] . ' set_to: complete', $_GET['qid'] );
            $GLOBALS['page']->Message("Success", 'Quests System', $return_link);
        }
        else
            $GLOBALS['page']->Message("FAILURE! FAILURE! FAILURE!<br>".$query, 'Quests System', $return_link);
    }

    private function do_close_quest()
    {
        $query = "UPDATE `users_quests`
                  SET `status` = 3,
                      `reset_status` = 0,
                      `timestamp_updated` = ".time().",
                      `data` = ''
                  WHERE `qid` = ".$_GET['qid']." AND `status` = ".$_GET['status'];

        $return_link = "?id={$_GET["id"]}&qid={$_GET['qid']}&act=";

        if(isset($_GET['uid']))
        {
            $query .= " AND `uid` = ".$_GET['uid'];
            $return_link .= 'who_has';
        }
        else
        {
            $_GET['uid'] = 'all';
            $return_link .= 'stats_and_actions';
        }

        if($GLOBALS['database']->execute_query($query))
        {
            $GLOBALS['page']->setLogEntry("Quest Change", 'quest: '. $_GET['qid'] .' status: '. $_GET['status'] . 'user: '. $_GET['uid'] . ' set_to: closed', $_GET['qid'] );
            $GLOBALS['page']->Message("Success", 'Quests System', $return_link);
        }
        else
            $GLOBALS['page']->Message("FAILURE! FAILURE! FAILURE!<br>".$query, 'Quests System', $return_link);
    }

    private function do_kill_quest()
    {
        $query = "UPDATE `users_quests`
                  SET `status` = 3,
                      `reset_status` = 0,
                      `timestamp_updated` = ".time().",
                      `dialog_chain` = '',
                      `data` = ''
                  WHERE `qid` = ".$_GET['qid']." AND `status` = ".$_GET['status'];

        $return_link = "?id={$_GET["id"]}&qid={$_GET['qid']}&act=";

        if(isset($_GET['uid']))
        {
            $query .= " AND `uid` = ".$_GET['uid'];
            $return_link .= 'who_has';
        }
        else
        {
            $_GET['uid'] = 'all';
            $return_link .= 'stats_and_actions';
        }

        if($GLOBALS['database']->execute_query($query))
        {
            $GLOBALS['page']->setLogEntry("Quest Change", 'quest: '. $_GET['qid'] .' status: '. $_GET['status'] . 'user: '. $_GET['uid'] . ' set_to: dead', $_GET['qid'] );
            $GLOBALS['page']->Message("Success", 'Quests System', $return_link);
        }
        else
            $GLOBALS['page']->Message("FAILURE! FAILURE! FAILURE!<br>".$query, 'Quests System', $return_link);
    }

    private function do_delete_quest()
    {
        $query = "DELETE FROM `users_quests` WHERE `qid` = ".$_GET['qid']." AND `status` = ".$_GET['status'];

        $return_link = "?id={$_GET["id"]}&qid={$_GET['qid']}&act=";

        if(isset($_GET['uid']))
        {
            $query .= " AND `uid` = ".$_GET['uid'];
            $return_link .= 'who_has';
        }
        else
        {
            $_GET['uid'] = 'all';
            $return_link .= 'stats_and_actions';
        }

        if($GLOBALS['database']->execute_query($query))
        {
            $GLOBALS['page']->setLogEntry("Quest Change", 'quest: '. $_GET['qid'] .' status: '. $_GET['status'] . 'user: '. $_GET['uid'] . ' deleted', $_GET['qid'] );
            $GLOBALS['page']->Message("Success", 'Quests System', $return_link);
        }
        else
            $GLOBALS['page']->Message("FAILURE! FAILURE! FAILURE!<br>".$query, 'Quests System', $return_link);
    }

    private function quest_do_delete() {
        if (isset($_GET['qid'])) {
            $query = "SELECT `qid`,`name` FROM `quests` WHERE `qid` = '" . $_GET['qid'] . "' LIMIT 1";
            $data = $GLOBALS['database']->fetch_data($query);
            if ($data != '0 rows') {
                
                    $GLOBALS['database']->execute_query("DELETE FROM `quests` WHERE `qid` = '" . $data[0]['qid'] . "' LIMIT 1");
                    $GLOBALS['page']->setLogEntry("Quest Change", 'quest "'. $data[0]['name'] .'" Deleted', $_GET['qid'] );

            } else {
                $GLOBALS['page']->Message("Quest could not be found.", 'Quests System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("No valid quest ID was specified.", 'Quests System', 'id=' . $_GET['id']);
        }
    }

    private function quest_edit_form() {
        if (isset($_GET['qid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `quests` WHERE `qid` = '" . $_GET['qid'] . "' LIMIT 1");

            if ($data != '0 rows') {
                tableParser::parse_form('quests', 'Edit quest', array('quest', 'qid'), $data, null, "", false);
            } else {
                $GLOBALS['page']->Message("This quest does not exist.", 'Quests System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid quest ID specified.", 'Quests System', 'id=' . $_GET['id']);
        }
    }

    private function quest_do_edit() {
        if (isset($_GET['qid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `quests` WHERE `qid` = '" . $_GET['qid'] . "'  LIMIT 1");
            if ($data != '0 rows') {
                $changed = tableParser::check_data('quests', 'qid', $_GET['qid'], array());
                if (tableParser::update_data('quests', 'qid', $_GET['qid'])) {

                    $GLOBALS['page']->Message("The quest has been updated.", 'Quests System', 'id=' . $_GET['id']);

                    $GLOBALS['page']->setLogEntry("Quest Change", "Quest name:" . $_POST['name'] . " Changed:<br>" . $changed , $_GET['qid'] );

                } else {
                    $GLOBALS['page']->Message("An error occurred while updating the quest.", 'Quests System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This quest does not exist.", 'Quests System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid quest ID specified.", 'Quests System', 'id=' . $_GET['id']);
        }
    }

    private function quest_new_form() {
        tableParser::parse_form('quests', 'Insert new quest', array('quest','qid'));
    }

    private function quest_upload_new() {
        if (tableParser::insert_data('quests')) {
            $GLOBALS['page']->Message("The quest has been inserted.", 'Quests System', 'id=' . $_GET['id']);
            $GLOBALS['page']->setLogEntry("Quest Change", 'quest name: <i>' . $_POST['name'] . '</i> Created'
                                                             .'<br>name: '.$_POST['name'],"");

        } else {
            $GLOBALS['page']->Message("An error occurred while inserting the quest.", 'Quests System', 'id=' . $_GET['id']);
        }
    }

    private function give_quest_screen()
    {
        $form = "
        <form method=\"get\">
            <label>Username: </label>
            <input name=\"username\" type=\"text\">
            <input type=\"hidden\" name=\"id\" value=\"".$_GET['id']."\">
            <input type=\"hidden\" name=\"act\" value=\"give_quest\">
            <button type=\"submit\" name=\"qid\" value=\"".$_GET['qid']."\">Give</button>
        </form>
        ";

        $GLOBALS['page']->Message($form, 'Quests System', 'id=' . $_GET['id']);
    }

    private function do_give_quest()
    {
        $query = "SELECT `id` FROM `users` where `username` = '".$_GET['username']."';";
        $users = $GLOBALS['database']->fetch_data($query);
        if(isset($users[0]['id']))
        {
            require_once(Data::$absSvrPath.'/global_libs/Quests/QuestsControl.php');
            require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
            require_once($_SERVER['DOCUMENT_ROOT'].'/libs/notificationSystem/notificationSystem.php');
            $GLOBALS['Events'] = new Events($users[0]['id']);
            $GLOBALS['NOTIFICATIONS'] = new NotificationSystem($GLOBALS['database']->fetch_data('
                SELECT `notifications`
                FROM `users`
                WHERE `id` = '.$users[0]['id'])[0]['notifications']);
            $quest_system = new QuestsControl($users[0]['id']);
            $quest_system->learnQuest($_GET['qid'],0);
            $GLOBALS['Events']->closeEvents();
            $GLOBALS['page']->Message("Quest has been given to the user.", 'Quests System', 'id=' . $_GET['id']);
        }
        else
            $GLOBALS['page']->Message("Invalid username.", 'Quests System', 'id=' . $_GET['id']);
    }
}

new blueMessage();