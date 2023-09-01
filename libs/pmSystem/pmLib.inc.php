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

class pmBasicFunctions {

    // Setup Inbox System
    public function set_inbox_system() {

        try {
            $close = false;
            if(!isset($GLOBALS['Events']))
            {
                require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
                $close = true;
                $GLOBALS['Events'] = new Events();
            }

            // Load Inbox File
            require_once(Data::$absSvrPath.'/libs/pmSystem/Inbox/pmInbox.php');
            $UserInbox = new pmInbox;

            // Load Inbox Actions File
            require_once(Data::$absSvrPath.'/libs/pmSystem/Inbox/pmActions.php');
            $SysAction = new pmActions;

            // Traversing Inbox Pages
            if(isset($_REQUEST['min'])) {
                unset($_REQUEST['act']);
            }

            if(!isset( $_REQUEST['act'])) {
                // Load User Inbox
                $UserInbox->inbox_screen();

                // Reset Global PM Notification
                $SysAction->resetReadNotification();
            }
            else {
                switch($_REQUEST['act']) {
                    case('newMessage'): {
                        if(!isset($_REQUEST['to'])) {
                            // Load New PM Form
                            $UserInbox->new_pm_form();
                        }
                        else {
                            // Send New PM Message
                            $SysAction->new_pm_send();

                            // Reload Main Inbox Screen
                            $UserInbox->inbox_screen("<br>Message was sent successfully");

                            // Reset Global PM Notification
                            $SysAction->resetReadNotification();
                        }
                    } break;
                    case('reply'): {
                        if(!isset($_REQUEST['message'])) {
                            // Load PM Reply Form
                            $UserInbox->reply_form($SysAction->get_user_pm($_REQUEST['pmid']));
                        }
                        else {
                            // Send PM Reply
                            $SysAction->reply_send();

                            // Reload Main Inbox Screen
                            $UserInbox->inbox_screen("<br>Message was sent successfully");

                            // Reset Global PM Notification
                            $SysAction->resetReadNotification();
                        }
                    } break;
                    case('read'): {
                        // Read Selected PM
                        $UserInbox->read_pm($SysAction->get_user_pm($_REQUEST['pmid']));
                    } break;
                    case('delete'): {
                        // Delete PM
                        $SysAction->delete_single_pm();

                        // Show inbox
                        $UserInbox->inbox_screen("<br>The message was deleted");

                        // Reset Global PM Notification
                        $SysAction->resetReadNotification();
                    } break;
                    case('markasread'): {
                        // Mark PMs as Read
                        $SysAction->mark_PM_read();

                        // Show the inbox screen
                        $UserInbox->inbox_screen("<br>All messages have been marked as read");

                        // Reset Global PM Notification
                        $SysAction->resetReadNotification();
                    } break;
                    case('clear'): {
                        // Delete Inbox
                        $SysAction->delete_inbox();

                        // Show Inbox
                        $UserInbox->inbox_screen("<br>Your inbox has been purged.");

                        // Reset Global PM Notification
                        $SysAction->resetReadNotification();
                    } break;
                    case('deletePMlist'): {
                        // Delete Selected PMs
                        $SysAction->delete_list_pms();

                        // Show Inbox
                        $UserInbox->inbox_screen("<br>All selected messages have been deleted");

                        // Reset Global PM Notification
                        $SysAction->resetReadNotification();
                    } break;
                    default: { // Unauthorized Request Action
                        throw new Exception('Could not determine action!');
                    } break;
                }
            }

            if($close)
                $GLOBALS['Events']->closeEvents();
        }
        catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            $GLOBALS['page']->Message($e->getMessage(), "Inbox System", 'id='.$_REQUEST['id'], 'Return');
        }
    }

    // Setup Outbox System
    public function set_outbox_system() {

        try {

            $close = false;
            if(!isset($GLOBALS['Events']))
            {
                require_once(Data::$absSvrPath.'/global_libs/Quests/Events.php');
                $close = true;
                $GLOBALS['Events'] = new Events();
            }

            // Load Outbox File
            require_once(Data::$absSvrPath.'/libs/pmSystem/Outbox/pmOutbox.php');
            $UserOutbox = new pmOutbox;

            // Load Inbox Actions File
            require_once(Data::$absSvrPath.'/libs/pmSystem/Outbox/pmOutboxActions.php');
            $SysAction = new pmOutboxActions;

            // Traversing Inbox Pages
            if(isset($_REQUEST['min'])) {
                unset($_REQUEST['act']);
            }

            if(!isset( $_REQUEST['act'])) {
                // Load User Inbox
                $UserOutbox->outbox_screen();
            }
            else {
                switch($_REQUEST['act']) {
                    case('read'): {
                        // Read Selected PM
                        $UserOutbox->read_pm($SysAction->get_user_pm($_REQUEST['pmid']));
                    } break;
                    case('delete'): {
                        // Delete PM
                        $SysAction->delete_single_pm();

                        // Show inbox
                        $UserOutbox->outbox_screen("<br>The message was deleted!");
                    } break;
                    case('deletePMlist'): {
                        // Delete Selected PMs
                        $SysAction->delete_list_pms();

                        // Show Inbox
                        $UserOutbox->outbox_screen("<br>All selected messages have been deleted!");
                    } break;
                    case('clear'): {
                        // Delete Inbox
                        $SysAction->delete_inbox();

                        // Show Inbox
                        $UserOutbox->outbox_screen("<br>Your outbox has been purged!");
                    } break;
                    default: { // Unauthorized Request Action
                        throw new Exception('Could not determine action!');
                    } break;
                }
            }



            if($close)
                $GLOBALS['Events']->closeEvents();
        }
        catch (Exception $e) {
            $GLOBALS['database']->transaction_rollback($e->getMessage());
            $GLOBALS['page']->Message($e->getMessage(), "Outbox System", 'id='.$_REQUEST['id'], 'Return');
        }
    }
}