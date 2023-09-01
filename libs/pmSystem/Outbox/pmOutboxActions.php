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

    class pmOutboxActions {
        
        public function saveOutboxMessage($pm_id = NULL, $s_uid = NULL, $r_uid = NULL, $subject = NULL, $message = NULL) {
            
            if(!isset($pm_id, $s_uid, $r_uid, $message, $subject)) { 
                throw new Exception('Failed to load necessary outbox parameters!'); 
            }
            
            self::outboxMaintainer($s_uid);
                
            // Insert PM into User PM Table
            if($GLOBALS['database']->execute_query('INSERT INTO 
                `users_outbox` 
                    (`pm_id`, `sender_uid`, `receiver_uid`, `time`, `subject`, `message`) 
                VALUES 
                    ('.$pm_id.', '.$s_uid.', '.$r_uid.', '.$GLOBALS['user']->load_time.', "'.$subject.'", "'.$message.'")') === false) { 
                throw new Exception('There was an error trying to save the PM to the outbox!'); 
            }
            
        }
        
        public function outboxMaintainer($uid = NULL) {
            
            if(!isset($uid)) { 
                throw new Exception('Failed to load necessary outbox maintaining parameters!'); 
            }
            
            if(!($pm_data = $GLOBALS['database']->fetch_data('SELECT `users_outbox`.`pm_id` FROM `users_outbox` 
                WHERE `users_outbox`.`sender_uid` = '.$uid.' ORDER BY `users_outbox`.`pm_id` ASC'))) {
                throw new Exception('There was an issue trying to obtain the outbox!');
            }
            
            if(count($pm_data) >= 50) {       
                $obs_pms = '';            
                for($i = 0, $size = count($pm_data) - 49; $i < $size; $i++) {
                    $obs_pms .= $pm_data[$i]['pm_id'] . (($i !== $size - 1) ? ', ' : '');
                }
                
                if($GLOBALS['database']->execute_query('DELETE FROM `users_outbox`
                    WHERE `users_outbox`.`pm_id` IN ('.$obs_pms.')') === false) {
                    throw new Exception('There was an error trying to delete and maintain the outbox!');
                }
            }
            
        }
        
        // Function for getting a PM related to the user
        public function get_user_pm($pmID) {
            // Check ID
            if(!ctype_digit($pmID)) { throw new Exception('Message ID is corrupt'); }

            // Return result from database
            if(!($dbResult = $GLOBALS['database']->fetch_data('SELECT `users_outbox`.`sender_uid`, `users_outbox`.`time`, 
                `users_outbox`.`message`, `users_outbox`.`subject`, `users`.`username` AS `receiver`,
                `users_statistics`.`user_rank` AS `receiver_rank`, `users_statistics`.`federal_level` AS `receiver_fed_lv`
                FROM `users_outbox`
                    LEFT JOIN `users` ON (`users`.`id` = `users_outbox`.`receiver_uid`)
                    LEFT JOIN `users_statistics` ON (`users_statistics`.uid = `users_outbox`.`receiver_uid`)
                WHERE `users_outbox`.`pm_id` = '.$pmID.' AND `users_outbox`.`sender_uid` = '.$GLOBALS['userdata'][0]['id'].' LIMIT 1'))) {
                throw new Exception("There was an error finding the PM in the database");
            }
            elseif($dbResult === '0 rows') { throw new Exception("The message doesn't exist!"); }

            // Convert entities back to special characters
            $dbResult[0]['subject'] = htmlspecialchars_decode($dbResult[0]['subject']);

            // Return result
            return $dbResult;
        }
        
        // Delete a single PM
        public function delete_single_pm() {
            // Delete the pm
            self::do_delete_pms(array($_REQUEST['pmid']));            
        }

        // Delete a list of PMs
        public function delete_list_pms() {
            // The list of PMs
            $pmList = array();

            // Check that list is specified
            if(!isset($_REQUEST['pmIDs']) || empty($_REQUEST['pmIDs']) || count($_REQUEST['pmIDs']) === 0) {
                throw new Exception('No PMs selected for deletion');
            }

            // Add ids to list
            foreach($_REQUEST['pmIDs'] as $value) {
                if(!ctype_digit($value)) { throw new Exception('Invalid PM id: '.$value ); }
                else { $pmList[] = $value; }
            }       

            // Do delete the pm
            self::do_delete_pms($pmList);  
        }

        // Clear the inbox
        public function delete_inbox() {
            // Do deletion
            self::do_delete_pms("ALL");    
        }
        
        // A function for deleting PMs
        public function do_delete_pms($pmList) {
            // Do delete
            $GLOBALS['database']->transaction_start();

            // Selector of deletion
            $selector = ($pmList === "ALL") ? '' : 'AND `users_outbox`.`pm_id` IN (\''.implode("','", $pmList).'\') LIMIT '.count($pmList).' ';

            // Select the pms in question
            if(!($pm_data = $GLOBALS['database']->fetch_data('SELECT `users_outbox`.`pm_id` FROM `users_outbox`
                WHERE `users_outbox`.`sender_uid` = '.$GLOBALS['userdata'][0]['id'].' '.$selector.' FOR UPDATE'))) {
                throw new Exception('Could not retrieve any PMs to delete');
            }

            // Nothing found
            if($pm_data === '0 rows') { throw new Exception('Could not find any PMs to delete'); }

            // Get the retrieved ids (those are the ones that actually belong to the user
            $deleteList = array();
            foreach($pm_data as $pm) { $deleteList[] = $pm['pm_id']; }

            // Check that some were found
            if(empty($deleteList)) { throw new Exception("No PMs belonging to you were found"); }

            // Do delete
            if(($GLOBALS['database']->execute_query('DELETE FROM `users_outbox`
                WHERE `users_outbox`.`sender_uid` = '.$GLOBALS['userdata'][0]['id'].' 
                    AND `users_outbox`.`pm_id` IN (\''.implode("', '", $deleteList).'\') LIMIT '.count($deleteList))) === false) {
                throw new Exception('There was an error deleting the PMs');
            }

            // Commit the transaction
            $GLOBALS['database']->transaction_commit();    
        }
        
    }