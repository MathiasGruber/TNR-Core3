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

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of supermod
 *
 * @author Wolfpack16
 */
    class module {

        function __construct() {

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            try {
                // Choose Between IP Check Form or IP Check Submission
                (!isset($_POST['Submit'])) ? self::isoIPForm() : self::isoIPsearch();
            }
            catch (Exception $e) {
                $GLOBALS['page']->Message($e->getMessage(), "IP Check", 'id='.$_REQUEST['id'], 'Return');
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }

        }

        private function isoIPForm() {
            // Create the input form
            $GLOBALS['page']->UserInput("Here you can do an extensive IP check on all users to isolate multiple accounts and/or account sharing.", "Extensive and Isolated IP Check",
                array(
                    // IP Check Search Value
                    array(
                        "infoText" => "Enter a filtering number for isolating accounts",
                        "inputFieldName" => "iso_value",
                        "type" => "input",
                        "inputFieldValue" => ""
                    )
                ),
                array(
                    "href" => "?id=".$_REQUEST['id'],
                    "submitFieldName" => "Submit",
                    "submitFieldText" => "Submit"
                ),
                "?id=".$_GET['id'],
                "isoIPForm"
            );
        }

        // Isolated and Extensive IP Search
        private function isoIPsearch () {

            if(!isset($_POST['iso_value'])) {
                throw new Exception('You did not enter a filteration number!');
            }
            elseif(!ctype_digit($_POST['iso_value'])) {
                throw new Exception('It must be a numeric value!');
            }
            elseif($_POST['iso_value'] < 2) {
                throw new Exception('It must be a value greater than 1');
            }
            else { // Ensure it's a whole number
                $_POST['iso_value'] = floor($_POST['iso_value']);
            }

            $unique_ips = $ip_filter = array();

            // Gather all IP Addresses from User Table
            if(!($ip_results = $GLOBALS['database']->fetch_data('SELECT `users`.`username`, `users`.`id`, `users`.`join_ip`,
                `users`.`last_ip`, `users`.`past_IPs`, `users`.`perm_ban`
                FROM `users`
                INNER JOIN `users_statistics` ON (`users_statistics`.`uid` = `users`.`id`)'))) {
                throw new Exception('Trying to obtain all Past IPs Failed!');
            }
            elseif($ip_results === '0 rows') {
                throw new Exception('There was no results gathered from the search!');
            }

            // Start Separating IP Addresses
            for($i = 0, $size = count($ip_results); $i < $size; $i++) {

                $user_ips = array();

                // Gather Past IPs
                if(!empty($ip_results[$i]['past_IPs'])) {
                    foreach(explode('|||', $ip_results[$i]['past_IPs']) as $val) {
                        if(!empty($val)) { array_push($user_ips, $val); }
                    }
                }

                // Gather Last and Join IPs
                array_push($user_ips, $ip_results[$i]['last_ip']);
                array_push($user_ips, $ip_results[$i]['join_ip']);

                unset($ip_results[$i]['past_IPs'], $ip_results[$i]['last_ip'], $ip_results[$i]['join_ip']);

                // Push All IP Addresses into an Array
                for($j = 0, $size2 = count($user_ips); $j < $size2; $j++) {
                    array_push($unique_ips, $user_ips[$j]);
                }

                // Resubmit User's IP Addresses as an Array
                $ip_results[$i]['ip_addresses'] = functions::mergesort(array_values(array_unique($user_ips)));
            }


            // Create a Hash Table of IP Addresses
            foreach(functions::mergesort(array_values(array_unique($unique_ips))) as $val) {
                $ip_filter[$val] = array(
                    'Matches' => 0,
                    'User_Data' => array()
                );
            }

            // Filter through User IP Addresses and Edit IP Hash Table
            for($i = 0, $size = count($ip_results); $i < $size; $i++) {
                foreach($ip_results[$i]['ip_addresses'] as $val) {
                    if(isset($ip_filter[$val]) && !empty($val)) {
                        $ip_filter[$val]['Matches']++;
                        array_push($ip_filter[$val]['User_Data'], array('Username' => $ip_results[$i]['username'], 'UID' => $ip_results[$i]['id']));
                    }
                }
            }

            // Filter out Accounts below the threshold
            foreach($ip_filter as $key => $val) {
                foreach($val as $key2 => $val2) {
                    if($key2 === 'Matches') {
                        if($val2 < $_POST['iso_value']) { unset($ip_filter[$key]); break; }
                    }
                }
            }

            if(empty($ip_filter)) { $ip_filter = '0 rows'; }

            $GLOBALS['template']->assign('ip_filter', $ip_filter);
            $GLOBALS['template']->assign('contentLoad', './panel_moderator/templates/ip_check/showISOData.tpl');
            // For Later Stuff to show username hidden or dropped down menus ~Wolfy
            /*
             *
            foreach($ip_filter as $key => $val) {
                echo 'IP Address: '.$key.'<br>Matches: '.$val['Matches'].'<br><br>';
                echo 'Users under '.$key.'<br><br>';
                foreach($val['User_Data'] as $key2) {
                    foreach($key2 as $key3 => $val3) {
                        echo $key3.': '.$val3.', '.(($key3 === 'UID') ? '<br>' : '');
                    }
                }
                echo '<br>';
            }
    <table width="90%" class="table">
        <tr>
            <td class="subHeader" colspan="2">Users on Last IP ({$user[0]['last_ip']})</td>
        </tr>
        {if $last_IPs != '0 rows'}
            <tr>
                <td width="71" style="border-bottom:1px solid #000000;font-weight:bold;">User ID</td>
                <td width="127" style="border-bottom:1px solid #000000;font-weight:bold;">Username</td>
            </tr>
            {foreach from=$last_IPs key=k item=v}
                <tr class="row{({$k} % 2) + 1}">
                    <td align="center">{$v['id']}</td>
                    <td align="center">{$v['username']}{if $v['perm_ban'] == 1}*{/if}</td>
                </tr>
            {/foreach}
        {else}<tr><td colspan="2">No Matches Were Found!</td></tr>{/if}
    </table>
             */
        }
    }

    new module();