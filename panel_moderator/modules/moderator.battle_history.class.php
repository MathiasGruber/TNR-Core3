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

class chats
{
    public function __construct()
    {
        if(isset($_POST['submit']) && $_POST['submit'] == "view_all_recent")
            $this->view_all_recent();
        else if(isset($_POST['submit']) && $_POST['submit'] == "view_as_user")
            $this->view_as_user($_POST['name_box']);
        else
            $GLOBALS['template']->assign('contentLoad', './panel_moderator/templates/battle_history/main.tpl');
    }

    function view_all_recent()
    {
        $select_query = "SELECT `id`,`time`,`type`,`census` FROM `battle_history` WHERE `time` > ".($GLOBALS['user']->load_time - 60*60*24*1)." AND `type` != '' ORDER BY `time` DESC";
        $this->view($select_query);
    }

    function view_as_user($username)
    {
        $select_query = "SELECT `id`,`time`,`type`,`census` FROM `battle_history` WHERE `census` like '%,".$username."/%' AND (`keep` = 'no' OR `time` > ".($GLOBALS['user']->load_time - 60*60*24*4).") ORDER BY `time` DESC";
        $this->view($select_query);
    }

    function view($select_query)
    {
        //running query to get battle histories
        try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception('query failed'); }
        catch (Exception $e)
        {
            try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception ('query failed'); }
            catch (Exception $e)
            {
                try { if(! $result = $GLOBALS['database']->fetch_data($select_query)) throw new Exception ('query failed to update user information'); }
                catch (Exception $e)
                {
                    $GLOBALS['DebugTool']->push('','there was an error getting battle history information.', __METHOD__, __FILE__, __LINE__);
                    throw $e;
                }
            }
        }
        
        //if nothing was found
        if($result == '0 rows')
        {
            // tell the user they do not have anything in their battle history
            if($base === true)
            {
                $GLOBALS['page']->Message("Your Battle History is empty.", 'Battle History', 'id=2','Return to Profile.');
                return;
            }
            else
            {
                $GLOBALS['page']->Message("Your Battle History is empty here.", 'Battle History', 'id=113','Return to Battle History.');
                return;
            }
        }

        //pulling out teams from census
        foreach($result as $key => $data)
        {
            $result[$key]['teams'] = array();

            foreach(explode(',',$data['census']) as $users)
                if($users != '')
                {
                    $username_team = explode('/',$users);
                    
                    if(!isset($username_team[1]))
                        continue;

                    if(!isset($result[$key]['teams'][$username_team[1]]))
                        $result[$key]['teams'][$username_team[1]] = array();

                    $result[$key]['teams'][$username_team[1]][] = array ($username_team[0], $username_team[2]);

                    if($_POST['submit'] == "view_as_user")
                    {
                        if($username_team[0] == $GLOBALS['userdata'][0]['username'])
                        {
                            $result[$key]['result'] = $username_team[3];
                        }
                    }
                    else
                    {
                        if(isset($username_team[3]) && $username_team[3] == 'win')
                        {
                            $result[$key]['result'] = $username_team[0];
                        }
                    }
                }
        }

        //sending data to page
        $GLOBALS['template']->assign('result',$result);


        if( strpos($_SERVER['HTTP_HOST'], 'development') !== false)
        {
            $GLOBALS['template']->assign('step_back_sort',true);
        }

        $GLOBALS['template']->assign('step_back_battle_link',true);

        //building page
        $GLOBALS['template']->assign('contentLoad', './templates/content/Battle/BattleHistory.tpl');
    }

}
new chats();