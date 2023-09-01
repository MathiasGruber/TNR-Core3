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

class NotificationSystem
{
    private $rawNotifications = '';
    private $newNotifications = '';
    private $tempNotifications = array();
    private $notifications = array();
    private $uid = '';

    //init variables
    public function __construct($DBNotifications, $uid_override = false)
    {
        if($uid_override !== false)
        {
            $this->uid = $uid_override;

            if($DBNotifications == '')
            {
                if(!($DBNotifications = $GLOBALS['database']->fetch_data('SELECT `notifications` FROM `users` where `id` = '.$this->uid)))
                    throw new exception('Failed to pull notifications.');
                else
                    $DBNotifications = $DBNotifications[0]['notifications'];
                    
            }
        }
        else if(isset($_SESSION['uid']))
            $this->uid = $_SESSION['uid'];

        if($DBNotifications != '' && $this->uid != '')
        {
            $this->rawNotifications = $DBNotifications;

            $this->notifications = explode('//', $this->rawNotifications);

            foreach($this->notifications as $notification_key => $notification) //for each notification
            {
                $this->notifications[$notification_key] = array();

                if($notification != '') //if the notification is not blank
                {
                    $notification_datas = explode(';', $notification); //break each piece of the notification down

                    foreach($notification_datas as $notification_data) //for each piece of notification data
                    {
                        if($notification_data != '') //if there is a piece of notification data
                        {
                            
                            $notification_data_exploded = explode('::', $notification_data); //break each piece of notification data down into name of data and actual data

                            foreach($notification_data_exploded as $piece_key => $piece) //for each piece of data
                            {
                                if($piece_key != 0 && $piece != '') //if this is not the name of the data and there is data here
                                {
                                    if (strpos($piece, ',') !== false) //if the data can be broken down more do so
                                    {
                                        $piece = explode(',',$piece);
                                    }

                                    if(!isset($this->notifications[$notification_key][$notification_data_exploded[0]]))
                                    {
                                        $this->notifications[$notification_key][$notification_data_exploded[0]] = $piece; //store data for the notification
                                    }
                                    else if(is_array($this->notifications[$notification_key][$notification_data_exploded[0]][0]))
                                    {
                                        $this->notifications[$notification_key][$notification_data_exploded[0]][] = $piece;
                                    }
                                    else
                                    {
                                        $this->notifications[$notification_key][$notification_data_exploded[0]] = array($this->notifications[$notification_key][$notification_data_exploded[0]]);
                                        $this->notifications[$notification_key][$notification_data_exploded[0]][] = $piece;                                    
                                    }
                                }
                            }
                        }
                    }

                }
                else // if the notification is blank remove it
                  unset($this->notifications[$notification_key]);
            }
        }
    }

    //add notifications
    // set "id" regestry
    // 1  = mail box full in pmActions.php
    // 2  = healed message occupationControl.php
    // 3  = blue message blue_message.inc.php
    // 4  = global event optActions.class.php
    // 5  = fed messages user.class.php
    // 6  = regen message users.class.php
    // 7  = ryo sent message bank.inc.php
    // 8  = marriage message marriage.inc.php
    // 9  = ryo stolen message rob_users.inc.php
    // 10 = account reset message userprefs.inc.php
    // 11 = anbu status message anbuLib.php / content.anbu_squad.class.php
    // 12 = clan status message clanLib.php / maintain.purge.class.php
    // 13 = village join message respectLib.php
    // 14 = jailed message respectLib.php
    // 15 = warning message moderatorLib.inc.php / moderator.warn_user.php
    // 16 = territory message map.inc.php / TerritoryBattle.php
    // 17 = village wide message clanLib.php
    // 18 = global message admin.monthly_mission.php
    // 19 = territory win message territoryBattle.php
    // 20 = trade in tradeLib.php
    // 21 = repair in reparirLib.php
    // 22 = quest system in Quests Control.php 
    // 23 = item collected from hook hooks.php
    // 24 = quest reward in QuestsData.php
    // 25 = item durability in battle.php
    // 26 = battle please untag messages and pvp bail? error message battle.php BattlePage.php
    public function addNotification($options)
    {
        $notification = array();

        if(isset($options['id']))
        {
            $notification['id'] = $options['id'];

            foreach($this->notifications as $current_notification_key => $current_notification)
            {
                if($current_notification['id'] == $notification['id'])
                    unset($this->notifications[$current_notification_key]);
            }
            
            $this->notifications = array_values($this->notifications);
        }
        else
        {
            $notification['id'] = time();

            $flag = true;

            while($flag)
            {
                $flag = false;

                foreach($this->notifications as $current_notification_key => $current_notification)
                {
                    if($current_notification['id'] == $notification['id'])
                    {
                        $flag = true;
                        $notification['id'] + 1;
                    }
                }
            }
        }

        if(isset($options['duration']))
            $notification['duration'] = $options['duration'];
        else
            $notification['duration'] = 'none';

        if(isset($options['dismiss']))
            $notification['dismiss'] = $options['dismiss'];
        else
            $notification['dismiss'] = 'no';

        if(isset($options['text']))
            $notification['text'] = $options['text'];
        else
            $notification['text'] = 'Empty.';

        if(isset($options['buttons']))
            $notification['buttons'] = $options['buttons'];
        else
            $notification['buttons'] = 'none';

        if(isset($options['select']))
            $notification['select'] = $options['select'];
        else
            $notification['select'] = 'none';

        if(isset($options['popup']))
            $notification['popup'] = $options['popup'];
        else
            $notification['popup'] = 'no';

        if(isset($options['hide']))
            $notification['hide'] = $options['hide'];
        else
            $notification['hide'] = 'no';

        if(isset($options['color']))
            $notification['color'] = $options['color'];
        else
            $notification['color'] = 'blue';

        if(is_array($notification['text']) && count($notification['text']) == 2)
            $notification['text'][] = 'no';

        array_unshift($this->notifications, $notification);
    }

    //add temp notification
    public function addTempNotification($options)//$text, $buttons = 'none', $select = 'none', $popup = 'no', $hide = 'no', $color = 'blue')
    {
        $notification = array();

        $notification['id'] = time() + (random_int(10,1010)*5);
        $notification['duration'] = 'none';
        $notification['dismiss'] = 'no';

        if(isset($options['text']))
            $notification['text'] = $options['text'];
        else
            $notification['text'] = 'Empty.';

        if(isset($options['buttons']))
            $notification['buttons'] = $options['buttons'];
        else
            $notification['buttons'] = 'none';

        if(isset($options['select']))
            $notification['select'] = $options['select'];
        else
            $notification['select'] = 'none';

        if(isset($options['popup']))
            $notification['popup'] = $options['popup'];
        else
            $notification['popup'] = 'no';

        if(isset($options['hide']))
            $notification['hide'] = $options['hide'];
        else
            $notification['hide'] = 'no';

        if(isset($options['color']))
            $notification['color'] = $options['color'];
        else
            $notification['color'] = 'blue';

        if(is_array($notification['text']) && count($notification['text']) == 2)
            $notification['text'][] = 'no';

        $this->tempNotifications[] = $notification;
    }

    //update user notifications
    public function recordNotifications()
    {
        if(is_array($this->notifications) && count($this->notifications) > 0)
        {   
            //var_dump(json_encode($this->notifications));
            foreach($this->notifications as $notifications_count => $notification_all_data)
            {
                if($notifications_count < 10)
                {
                    foreach($notification_all_data as $notification_data_key => $notification_data)
                    {
                        if(isset($notification_all_data['id']))
                        {
                            $this->newNotifications .= $notification_data_key.'::';

                            if(!is_array($notification_data))
                                $this->newNotifications .= $notification_data;
                            else if(!is_array($notification_data[0]))
                            {
                                $this->newNotifications .= implode(',',$notification_data);
                            }
                            else
                            {
                                foreach($notification_data as $temp_key => $temp)
                                    $notification_data[$temp_key] = implode(',',$temp);

                                $this->newNotifications .= implode('::',$notification_data);
                            }

                            $this->newNotifications .= ';';
                        }
                        else if($notification_all_data['duration'] !== "dismissed" && $notification_all_data['duration'] !== "done")
                        {
                            var_dump("Bad Notification Creation!");
                            echo'<br>';
                            var_dump($notification_all_data);
                            echo'<br>';
                            echo'<br>';
                        }
                    }

                    $this->newNotifications .= '//';
                }
            }
        }

        if($this->newNotifications != $this->rawNotifications)
        {
            $GLOBALS['database']->execute_query("UPDATE `users` SET `notifications` = '".str_replace("'","\'",$this->newNotifications)."' WHERE `id` = '".$this->uid."'");
        }
    }


    // show notifications
    public function showNotifications()
    {
        $notifications_for_show = array();
        $popup_notifications = array();
        $notifications = array();



        foreach( array_reverse($this->tempNotifications) as $notification_key => $temp_notification1)
            $notifications[] = $temp_notification1;

        $seen_ids = array();

        if(is_array($this->notifications) && count($this->notifications) > 0)
        {
            foreach($this->notifications as $notification_key => $temp_notification2)
            {
                //checking for id match and removing extras
                if( isset($temp_notification2['id']) && isset($seen_ids[$temp_notification2['id']]))
                {
                    unset($this->notifications[$notification_key]);
                    continue;
                }
                else if(isset($temp_notification2['id']))
                {
                    $seen_ids[$temp_notification2['id']] = $temp_notification2['id'];
                }

                $notifications[] = $temp_notification2;
            }
        }


        foreach($notifications as $notification_key => $notification)
        {
            if( is_array($notification) && isset($notification['text']))
            {
                //fixing comma issues and coloring text
                if(is_array($notification['text']))
                {

                    $new_text = array();

                    foreach($notification['text'] as $text_key => $text)
                    {
                        if($text_key == 0 && $text[0] == '?')
                        {
                            $new_text[] = $text;
                            $new_text[] = '';
                        }
                        else if($text_key == count($notification['text'])-1 && ($text == 'yes' || $text == 'no'))
                            $new_text[] = $text;
                        else if(count($new_text) == 0)
                        {
                            $new_text[] = "";
                            $new_text[] = $text;
                        }
                        else
                        {
                            if(is_array($new_text[count($new_text)-1]))
                                $new_text[count($new_text)-1] = implode(',',$new_text[count($new_text)-1]);

                            if($new_text[count($new_text)-1] == '')
                                $new_text[count($new_text)-1] = $text;                            

                            else
                                $new_text[count($new_text)-1] .= ','.$text;
                        }
                    }

                    if(isset($notification['text-color']) && is_array($notification['text-color']))
                        $new_text[1] = '<span style="color:'.implode(',',$notification['text-color']).';">'. $new_text[1] . '</span>';
                    else if(isset($notification['text-color']))
                        $new_text[1] = '<span style="color:'.$notification['text-color'].';">'. $new_text[1] . '</span>';

                    if(count($new_text) == 2 && $new_text[0] == "")
                        $new_text = $new_text[1];
                    else if(count($new_text) == 2)
                        $new_text[] = 'no';

                    $notification['text'] = $new_text;

                }
                else
                {
                    if(isset($notification['text-color']) && is_array($notification['text-color']))
                        $notification['text'] = '<span style="color:'.implode(',',$notification['text-color']).';">'. $notification['text'] . '</span>';
                    else if(isset($notification['text-color']))
                        $notification['text'] = '<span style="color:'.$notification['text-color'].';">'. $notification['text'] . '</span>';
                }

                if($notification['duration'] != 'none' && $notification['duration'] != 'done' && $notification['duration'] != 'dismissed')
                {
                    if($notification['duration'] <= 0)
                    {
                        $this->notifications[$notification_key]['duration'] = 'done';
                        $notification['duration'] = 'done';
                    }
                    else if($notification['duration'] < 1000)
                    {
                        $this->notifications[$notification_key]['duration'] -= 1;
                        $notification['duration'] -= 1;
                    }
                    else if($notification['duration'] < time())
                    {
                        $this->notifications[$notification_key]['duration'] = 'done';
                        $notification['duration'] = 'done';
                    }
                }

                if( ($notification['duration'] !== "dismissed" && $notification['duration'] !== "done") || (isset($_GET['show-all-notifications'])))
                {
                    if(!isset($notification['hide']) || $notification['hide'] != 'yes')
                        $notifications_for_show[] = $notification;

                    if(isset($notification['popup']) && $notification['popup'] == 'yes')
                    {
                        if(!isset($notification['theme']))
                            $notification['theme'] = $GLOBALS['userdata'][0]['popup'];

                        $popup_notifications[] = $notification;
                    }
                }
            }
            else if($notification !== [])
            {
                var_dump('notification error!');
                echo'<br>';
                var_dump($notification);
                echo'<br>';
                echo'<br>';
            }
        }


        $GLOBALS['template']->assign('notifications', $notifications_for_show);

        $GLOBALS['template']->assign('popups', $popup_notifications);
    }

    // hide notification
    public function dismissNotification($id)
    {
        $GLOBALS['database']->execute_query("UPDATE `users` SET `notifications` = REPLACE ( `notifications` , '".
            substr($this->rawNotifications, strpos($this->rawNotifications, 'id::'.$id.';'), (strpos($this->rawNotifications, ';', strpos($this->rawNotifications,';', strpos($this->rawNotifications, 'id::'.$id.';')) + 1) - strpos($this->rawNotifications, 'id::'.$id.';')))
            ."' , '".
            'id::'.$id.';duration::dismissed'
            ."' ) WHERE `id` = '".$this->uid."'");
    }
}