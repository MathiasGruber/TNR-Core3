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

    class pmInbox {

        // Inbox Main Screen
        public function inbox_screen($actionMessage = "") {

            // Format message
            $actionMessage = "<span class='red'><b>".$actionMessage."</b></span>";

            // Obtain Necessary User Information
            if(!($message_data = $GLOBALS['database']->fetch_data('
                SELECT `receiver`.`sender_uid`, `receiver`.`time`,
                    `receiver`.`message`, `receiver`.`subject`, `receiver`.`read`, `receiver`.`pm_id`,
                    `users`.`username` AS `sender_username`,
                    `sender`.`user_rank` AS `sender_rank`,
                    `sender`.`federal_level` AS `sender_fed_lv`,
                    `users_statistics`.`user_rank` AS `receiver_rank`,
                    `users_statistics`.`federal_level` AS `receiver_fed_lv`
                FROM `users_statistics`
                    LEFT JOIN `users_pm` AS `receiver` ON (`receiver`.`receiver_uid` = `users_statistics`.`uid`)
                    LEFT JOIN `users_statistics` AS `sender` ON (`sender`.`uid` = `receiver`.`sender_uid`)
                    LEFT JOIN `users` ON (`users`.`id` = `sender`.`uid`)
                WHERE `users_statistics`.`uid` = '.$GLOBALS['userdata'][0]['id'].'
                ORDER BY `receiver`.`read` DESC, `receiver`.`pm_id` DESC
                LIMIT '.tableParser::get_page_min().', 10'))
            ) {
                throw new Exception('There was an error trying to obtain necessary user information!');
            }
            elseif($message_data === '0 rows') { throw new Exception('There are no more PMs within this range'); }

            // Obtain Necessary User Information
            if($message_data[0]['sender_uid'] !== null ) {
                if(!($msgCount = $GLOBALS['database']->fetch_data('SELECT COUNT(`users_pm`.`pm_id`) AS `msgCount`
                    FROM `users_pm` WHERE `receiver_uid` = '.$GLOBALS['userdata'][0]['id'].' LIMIT 1'))) {
                    throw new Exception('There was an error trying to obtain inbox limitations!');
                }
            }

            // Gather Message Count from Returned Entries
            $msgcount = ($message_data[0]['sender_uid'] === null) ? 0 : $msgCount[0]['msgCount'];
            $pmMax = ($message_data[0]['receiver_rank'] !== 'Member') ? 75 : 50;
            if( $message_data[0]['receiver_fed_lv'] == "Gold" ){
                $pmMax += 25;
            }

            // Increase for staff
            if(in_array($message_data[0]['receiver_rank'], Data::$STAFF_RANKS, true)) { $pmMax = 500; }

            // Parse the messages present in the database
            $messages = array();
            if($message_data[0]['sender_uid'] !== null) {
                for($i = 0, $size = count($message_data); $i < $size; $i++) {
                    $userGroup = in_array($message_data[$i]['sender_rank'], array("Paid", "EventMod", "Event"), true) ?
                        $message_data[$i]['sender_fed_lv'] : $message_data[$i]['sender_rank'];

                    $message_data[$i]['parsed_pm_time'] = functions::convert_PM_time($GLOBALS['user']->load_time - $message_data[$i]['time']);
                    $message_data[$i]['time'] = date('F j, Y G:i:s', $message_data[$i]['time']);
                    $message_data[$i]['subject'] = functions::parse_BB($message_data[$i]['subject']);
                    $message_data[$i]['message'] = functions::parse_BB($message_data[$i]['message']);

                    if(is_null($message_data[$i]['sender_username'] ))
                    {
                        $message_data[$i]['sender_username'] = $message_data[$i]['sender_uid'];
                    }
                    else
                        $message_data[$i]['sender_username'] = '<a href="?id=13&page=profile&name='.$message_data[$i]['sender_username'].'">'.functions::username_color($userGroup, $message_data[$i]['sender_username']).'</a>';

                    if($message_data[$i]['read'] === "no")
                    {
                        $message_data[$i]['time'] = "<b>".$message_data[$i]['time']."</b>";
                        $message_data[$i]['parsed_pm_time'] = "<b>".$message_data[$i]['parsed_pm_time']."</b>";

                        if( !isset($GLOBALS['returnJson']) || $GLOBALS['returnJson'] != true )
                        {
                            $message_data[$i]['sender_username'] = '<b style="font-size:14px">'.$message_data[$i]['sender_username']."</b>";
                            $message_data[$i]['subject'] = '<b style="font-size:14px">'.$message_data[$i]['subject']."</b>";
                        }
                        else
                        {
                            $message_data[$i]['sender_username'] = str_replace('<a','<a fontStyle="bold"',$message_data[$i]['sender_username']);
                            $message_data[$i]['subject'] = '<text fontStyle="bold">'.$message_data[$i]['subject']."</text>";
                        }

                    }

                    $messages[] = $message_data[$i];
                }
            }
            else { $messages = "0 rows"; }

            // Top Options
            $topOptions = array(
                array(
                    "name" => "New Message",
                    "href" => "?id=" . $_REQUEST['id'] . "&amp;act=newMessage"
                ),
                array(
                    "name" => "Mark all as read",
                    "href" => "?id=" . $_REQUEST['id'] . "&amp;act=markasread"
                ),
                array(
                    "name" => "Clear inbox",
                    "href" => "?id=" . $_REQUEST['id'] . "&amp;act=clear"
                )
            );

            // Show the table of users
            tableParser::show_list('inbox', 'Inbox Messages',
                $messages,
                array(
                    'subject' => "Subject",
                    'sender_username' => "Sender",
                    'time' => "Received"
                ),
                array(
                    array(
                        "name" => "Read",
                        "id" => $_REQUEST['id'],
                        "act" => "read",
                        "pmid" => "table.pm_id"
                    ),
                    array(
                        "parseType" => "select",
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
                'Your inbox currently holds '.$msgcount.' / '.$pmMax." messages".$actionMessage
            );
        }

        // Read PM form
        public function read_pm($message_data = NULL) {

            if(!isset($message_data)) { throw new Exception('Read PM Message Data Not Provided!'); }


            // Reply PM has been read, Update PM Notification
            if($message_data[0]['read'] === "no") {
                if(($GLOBALS['database']->execute_query("UPDATE `users_pm` SET `read` = 'yes' WHERE `pm_id` = ".$_REQUEST['pmid']." LIMIT 1")) === false) {
                    throw new Exception("There was an error setting this message to 'read'-status");
                }
            }

            $userGroup = in_array($message_data[0]['sender_rank'], array("Paid", "EventMod", "Event"), true) ?
                $message_data[0]['sender_fed_lv'] : $message_data[0]['sender_rank'];

            // Parse message
            $message_data[0]['parsed_time'] = date('F j, Y G:i:s', $message_data[0]['time']);
            $message_data[0]['parsed_pm_time'] = functions::convert_PM_time($GLOBALS['user']->load_time - $message_data[0]['time']);

            if(is_null($message_data[0]['sender']))
            {
                $message_data[0]['sender_color'] = $message_data[0]['sender_uid'];
            }
            else
                $message_data[0]['sender_color'] = functions::username_color($userGroup, $message_data[0]['sender']);

            $message_data[0]['message'] = BBCode2Html($message_data[0]['message']);

            if($message_data[0]['sender'] != $GLOBALS['userdata'][0]['username'])
            {
                $GLOBALS['Events']->acceptEvent('pm_receive', array('data'=>preg_replace('/([^\\\\])(\\\\{2}\')/m', '$1\\\\\\\\\\\'', str_replace('\'','\\\'',strip_tags($message_data[0]['message']))), 'context'=>$message_data[0]['sender']));
            }

            // Send to smarty
            $GLOBALS['template']->assign('message', $message_data);

            // Show the main template
            $GLOBALS['template']->assign('contentLoad', './templates/content/PM/pm_show_message.tpl');
        }

        // New pm form
        public function new_pm_form() {
            // ToUser Field
            $toUser = isset($_GET['toUser']) ? $_GET['toUser'] : "";

            // Create the fields to be shown
            $inputFields = array(
                array(
                    "infoText" => "Send To",
                    "inputFieldName" => "to",
                    "type" => "input",
                    "inputFieldValue" => $toUser
                ),
                array(
                    "infoText" => "Subject",
                    "inputFieldName" => "subject",
                    "type" => "input",
                    "inputFieldValue" => ""
                ),
                array(
                    "infoText" => "Message",
                    "inputFieldName" => "message",
                    "type" => "textarea",
                    "inputFieldValue" => ""
                )
            );

            // Show user prompt
            $GLOBALS['page']->UserInput(
                "", // Information
                "Send Message", // Title
                $inputFields, // input fields
                array(
                    "href" => "?id=" . $_REQUEST['id'] . "&amp;act=" . $_REQUEST['act'],
                    "submitFieldName" => "Submit",
                    "submitFieldText" => "Send Now"
                ), // Submit button
                "Return", // Return link name
                "sendForm"
            );
        }

        // Reply form
        public function reply_form($message_data = NULL) {

            if(!isset($message_data)) { throw new Exception('Reply PM Message Data Not Provided!'); }

            // Create the fields to be shown
            $inputFields = array(
                array(
                    "infoText" => "Your Reply",
                    "inputFieldName" => "message",
                    "type" => "textarea",
                    "inputFieldValue" => ""
                )
            );

            // Show user prompt
            $GLOBALS['page']->UserInput(
                "<b>Original Message</b>: ".$message_data[0]['message'], // Information
                "Reply to Message", // Title
                $inputFields, // input fields
                array(
                    "href" => "?id=".$_REQUEST['id']."&amp;act=".$_REQUEST['act']."&amp;pmid=".$_REQUEST['pmid'],
                    "submitFieldName" => "Submit",
                    "submitFieldText" => "Send Now"
                ), // Submit button
                "Return", // Return link name
                "sendForm"
            );
        }
    }