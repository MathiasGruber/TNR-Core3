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

    class pmOutbox {
        
        // Inbox Main Screen
        public function outbox_screen($actionMessage = '') { 
            
            // Format message
            $actionMessage = "<span class='red'><b>".$actionMessage."</b></span>";
            
            // Obtain Necessary User Information
            if(!($outbox_data = $GLOBALS['database']->fetch_data('SELECT `users_outbox`.*,
                `users`.`username` AS `receiver_username`, `receiver`.`user_rank` AS `receiver_rank`,
                `receiver`.`federal_level` AS `receiver_fed_lv`
                FROM `users_statistics`
                    LEFT JOIN `users_outbox` ON (`users_outbox`.`sender_uid` = `users_statistics`.`uid`)
                    LEFT JOIN `users` ON (`users`.`id` = `users_outbox`.`receiver_uid`)
                    LEFT JOIN `users_statistics` AS `receiver` ON (`receiver`.`uid` = `users`.`id`)
                WHERE `users_statistics`.`uid` = '.$GLOBALS['userdata'][0]['id'].' ORDER BY `users_outbox`.`pm_id`
                        DESC LIMIT '.tableParser::get_page_min().', 10'))) { 
                throw new Exception('There was an error trying to obtain necessary user information!'); 
            }
            elseif($outbox_data === '0 rows') { throw new Exception('There are no saved Outbox PMs within this range!'); }

            // Obtain Necessary User Information & Gather Message Count from Returned Entries
            if($outbox_data[0]['sender_uid'] !== null) {
                if(!($msgCount = $GLOBALS['database']->fetch_data('SELECT COUNT(`users_outbox`.`pm_id`) AS `msgCount` 
                    FROM `users_outbox` WHERE `users_outbox`.`sender_uid` = '.$GLOBALS['userdata'][0]['id'].' LIMIT 1'))) { 
                    throw new Exception('There was an error trying to obtain inbox limitations!'); 
                }
                $msgcount = $msgCount[0]['msgCount'];
                unset($msgCount);
            }
            else { $msgcount = 0; }

            $pmMax = 50;

            // Parse the messages present in the database
            $messages = array();
            if($outbox_data[0]['sender_uid'] !== null) { 
                for($i = 0, $size = count($outbox_data); $i < $size; $i++) {
                    $userGroup = in_array($outbox_data[$i]['receiver_rank'], array("Paid", "EventMod", "Event"), true) ?
                        $outbox_data[$i]['receiver_fed_lv'] : $outbox_data[$i]['receiver_rank'];
                    
                    $outbox_data[$i]['time'] = date('F j, Y G:i:s', $outbox_data[$i]['time']);
                    $outbox_data[$i]['parsed_pm_time'] = functions::convert_PM_time($GLOBALS['user']->load_time - $outbox_data[$i]['time']);
                    $outbox_data[$i]['subject'] = functions::parse_BB($outbox_data[$i]['subject']);
                    $outbox_data[$i]['message'] = functions::parse_BB($outbox_data[$i]['message']);
                    $outbox_data[$i]['receiver_username'] = '<a href="?id=13&page=profile&name='.$outbox_data[$i]['receiver_username'].'">'.functions::username_color($userGroup, $outbox_data[$i]['receiver_username']).'</a>';
                    $messages[] = $outbox_data[$i];
                }
            }
            else { $messages = "0 rows"; }
            
            unset($outbox_data);
            
            // Top Options
            $topOptions = array(
                array(
                    "name" => "Clear Outbox", 
                    "href" => "?id=" . $_REQUEST['id'] . "&amp;act=clear"
                )
            );

            // Show the table of users
            tableParser::show_list(
                'outbox',
                'Outbox Messages', 
                $messages,
                array(
                    'subject' => "Subject",
                    'receiver_username' => "Receiver",
                    'time' => "Sent"
                ), 
                array( 
                    array( 
                        "name" => "Read", 
                        "id" => $_REQUEST['id'], 
                        "act" => "read", 
                        "pmid" => "table.pm_id"
                    ),
                    array( 
                        "parseType"=> "select", 
                        "name" => "Delete", 
                        "formName" => "pmIDs", 
                        "value" => "table.pm_id", 
                        "href" => "?id=".$_REQUEST['id']."&amp;act=deletePMlist", 
                        "submitName" => "Delete Selected"
                    )
                ) ,
                true, // Send directly to contentLoad
                true, // No newer/older links
                $topOptions, // No top options links
                false, // No sorting on columns
                false, // No pretty options
                false, // No top search field
                'Your outbox currently holds '.$msgcount.' / '.$pmMax." messages!".$actionMessage
            );      
        }
        
        // Read PM form
        public function read_pm($message_data = NULL) {
            
            if(!isset($message_data)) { throw new Exception('Read PM Message Data Not Provided!'); }

            $userGroup = in_array($message_data[0]['receiver_rank'], array("Paid", "EventMod", "Event"), true) ?
                $message_data[0]['receiver_fed_lv'] : $message_data[0]['receiver_rank'];
            
            // Parse message
            $message_data[0]['parsed_time'] = date('F j, Y G:i:s', $message_data[0]['time']);
            $message_data[0]['parsed_pm_time'] = functions::convert_PM_time($GLOBALS['user']->load_time - $message_data[0]['time']);
            $message_data[0]['receiver_color'] = functions::username_color($userGroup, $message_data[0]['receiver']);
            $message_data[0]['message'] = BBCode2Html($message_data[0]['message']);

            // Send to smarty
            $GLOBALS['template']->assign('message', $message_data);

            // Show the main template
            $GLOBALS['template']->assign('contentLoad', './templates/content/PM/Outbox/pm_show_message.tpl');
        }
        
    }