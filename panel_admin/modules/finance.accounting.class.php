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
// Show All Error Reports
error_reporting(E_ALL);
ini_set('display_errors', 1);

class accounting {

    public function __construct() {
        try{

            if ($GLOBALS['userdata'][0]["username"] == "Terriator") {
                if (!isset($_GET['act'])) {
                    $this->main_page();
                } elseif ($_GET['act'] == 'show') {
                    if (!isset($_POST['Submit'])) {
                        $this->datePicker();
                    } else {
                        $this->createReport();
                    }
                } elseif ($_GET['act'] == 'analysis') {
                    $this->analysis();
                } elseif ($_GET['act'] == 'unusedRep') {
                    $this->unusedRep();
                } elseif ($_GET['act'] == 'eu_vat') {
                    if (!isset($_POST['Submit'])) {
                        $this->datePicker();
                    } else {
                        $this->eu_vat();
                    }
                }
            } else {
                $GLOBALS['page']->Message("You are not allowed access to this information", 'Accounting', 'id=' . $_GET['id']);
            }
            
         }  catch (Exception $ex) {
            $GLOBALS['page']->Message($ex->getMessage(), 'Accounting', 'id=' . $_GET['id'] );
        }
    }

    // Main page
    private function main_page() {
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/accounting/main.tpl');
    }
    
    // Unused reputation points sum
    private function unusedRep(){
        $test = $GLOBALS['database']->fetch_data("
               SELECT SUM(`rep_now`) as `total`
               FROM `users_statistics` 
               INNER JOIN `users_timer` ON (`users_statistics`.`uid` = `users_timer`.`userid`)
               WHERE `last_regen` > ".(time()-30*24*3600));
        $GLOBALS['page']->Message("For all the users who were active during the last 30 days, there are ".$test[0]['total']." unused reputation points", 'Accounting', 'id=' . $_GET['id']);                       
    }

    // Accounting
    private function datePicker() {
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/accounting/datePicker.tpl');
    }
    
    // Accounting Report
    private function eu_vat() {

        // Get the timestamps
        $start = strtotime($_POST["startDate"]);
        $end = strtotime($_POST["endDate"]);

        // Update the database
        $GLOBALS['database']->execute_query("UPDATE `ipn_payments` SET `date` = DATE( FROM_UNIXTIME( `time` ) )");

        // Countries
        $vatNumbers = array(
            "Denmark" => "25",
            "DK" => "25", // 1
            "Germany" => "19",
            "DE" => "19", 
            "United Kingdom" => "20",
            "GB" => "20",
            "Netherlands" => "21",
            "NL" => "21", 
            "Sweden" => "25",
            "SE" => "25",// 5
            "Greece" => "23",
            "GR" => "23",
            "Belgium" => "21",
            "BE" => "21", 
            "Bulgaria" => "20",
            "BG" => "20", 
            "Czech Republic" => "21",
            "CZ" => "21", 
            "Estonia" => "20",
            "EE" => "20", // 10
            "Ireland" => "21",
            "IE" => "21", 
            "Spain" => "18",
            "ES" => "18", 
            "France" => "20",
            "FR" => "20", 
            "Croatia" => "25",
            "HR" => "25", 
            "Italy" => "22",
            "IT" => "22", // 15 
            "Cyprus" => "16",
            "CY" => "16", 
            "Latvia" => "21",
            "LV" => "21", 
            "Lithuania" => "21",
            "LT" => "21", 
            "Luxembourg" => "15",
            "LU" => "15", 
            "Hungary" => "27",
            "HU" => "27", // 20
            "Malta" => "18",
            "MT" => "18", 
            "Austria" => "20",
            "AT" => "20", 
            "Poland" => "23",
            "PL" => "23", 
            "Portugal" => "23",
            "PT" => "23", 
            "Romania" => "24",
            "RO" => "24", // 25
            "Slovenia" => "20",
            "SI" => "20", 
            "Slovakia" => "20",
            "SK" => "20", 
            "Finland" => "24",
            "FI" => "24" // 28
        );
        
        // Select records
        $data = $GLOBALS['database']->fetch_data("
             SELECT * FROM `ipn_payments` 
             WHERE 
                `price` > 0 AND 
                `time` >= " . $start . " AND 
                `time` < " . $end . " AND
                `country` IN ('".implode("','", array_keys($vatNumbers) )."')
             ORDER BY `time` ASC");

        // Calculate
        if ($data != "0 rows") {
            $indkomst = 0;
            $countries = array();
            $i = 0;
            while ($i < count($data)) {
                if ($data[$i]['date'] !== "" && $data[$i]['txn_id'] !== "") {

                    // Set the countries 
                    if ($data[$i]['country'] == "Unknown" && $data[$i]['country'] == "N/A") {
                        //Unknown row
                    } else {
                        $indkomst = $indkomst + $data[$i]['price'];
                        if (!isset($countries['' . $data[$i]['country'] . ''])) {
                            $countries['' . $data[$i]['country'] . ''] = 0;
                        }
                        $countries['' . $data[$i]['country'] . ''] = $countries['' . $data[$i]['country'] . ''] + $data[$i]['price'];
                    }
                }
                $i++;
            }
            $GLOBALS['template']->assign('data', $data);
            $GLOBALS['template']->assign('VAT', $vatNumbers);
            $GLOBALS['template']->assign('countries', $countries);
            $GLOBALS['template']->assign('sum', array_sum($countries));
        }

        // Smarty
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/accounting/euVATreport.tpl');
    }

    // Accounting Report
    private function createReport() {

        // Get the timestamps
        $start = strtotime($_POST["startDate"]);
        $end = strtotime($_POST["endDate"]);

        // Update the database
        $GLOBALS['database']->execute_query("UPDATE `ipn_payments` SET `date` = DATE( FROM_UNIXTIME( `time` ) )");

        // Select records
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `ipn_payments` WHERE `price` > 0 AND `time` >= " . $start . " AND `time` < " . $end . " ORDER BY `time` ASC");

        // Calculate
        if ($data != "0 rows") {
            $indkomst = 0;
            $countries = array();
            $i = 0;
            while ($i < count($data)) {
                
                if ($data[$i]['date'] !== "" && $data[$i]['txn_id'] !== "") {
                    
                    // Get the country of the transaction
                    if ($data[$i]['country'] == "N/A" || $data[$i]['country'] == "") {
                        $test = $GLOBALS['database']->fetch_data("SELECT * FROM `ipn_tests` WHERE `vars` LIKE '%" . $data[$i]['txn_id'] . "%' LIMIT 1");
                        if ($test !== "0 rows") {
                            // Extract country
                    
                            preg_match("/\[residence_country\]\s=>\s(.+)/", $test[0]['vars'], $matches, PREG_OFFSET_CAPTURE);
                            if (isset($matches[1][0])) {
                                $data[$i]['country'] = $matches[1][0];
                                $GLOBALS['database']->execute_query("UPDATE `ipn_payments` SET `country` = '" . $data[$i]['country'] . "' WHERE `transaction_id` = '" . $data[$i]['transaction_id'] . "' LIMIT 1");
                            }
                        }
                        // Do stuff
                    }

                    // Set the countries 
                    if ($data[$i]['country'] == "Unknown" && $data[$i]['country'] == "N/A") {
                        //Unknown row
                    } else {
                        $indkomst = $indkomst + $data[$i]['price'];
                        if (!isset($countries['' . $data[$i]['country'] . ''])) {
                            $countries['' . $data[$i]['country'] . ''] = 0;
                        }
                        $countries['' . $data[$i]['country'] . ''] = $countries['' . $data[$i]['country'] . ''] + $data[$i]['price'];
                    }
                }
                $i++;
            }
            $GLOBALS['template']->assign('data', $data);
            $GLOBALS['template']->assign('countries', $countries);
            $GLOBALS['template']->assign('sum', array_sum($countries));
        }

        // Smarty
        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/accounting/accountingReport.tpl');
    }

    // User worth Analysis
    private function analysis() {

        // Set time
        if (!isset($_POST["days"])) {
            $_POST["days"] = 14;
        }
        $days = $_POST["days"];
        if ($days == "all") {
            $days = 9999;
        }

        // Convert to seconds
        $time = $days * 3600 * 24;

        // Variables
        $income = 0;
        $fee = 0;
        $newIncome = 0;
        $newFee = 0;
        $newLvlIncome = 0;
        $newLvlFee = 0;

        // Get the records
        $payments = $GLOBALS['database']->fetch_data("SELECT * FROM `ipn_payments` WHERE `time` > '" . (time() - $time) . "'");
        $newUserPayments = $GLOBALS['database']->fetch_data("SELECT * FROM `ipn_payments`,`users` WHERE 
                                                                `users`.`join_date` > '" . (time() - $time) . "' AND
                                                                `ipn_payments`.`time` > '" . (time() - $time) . "' AND
                                                                `ipn_payments`.`r_uid` = `users`.`id`
                                                            ");
        $newLvlUserPayments = $GLOBALS['database']->fetch_data("SELECT * 
                                                                FROM `ipn_payments`,`users`,`users_statistics` WHERE 
                                                                `uid` = `id` AND
                                                                `level_id` > 1 AND
                                                                `users`.`join_date` > '" . (time() - $time) . "' AND
                                                                `ipn_payments`.`time` > '" . (time() - $time) . "' AND
                                                                `ipn_payments`.`r_uid` = `users`.`id`
                                                            ");


        // Do the loop total
        $i = 0;
        while ($i < count($payments)) {
            if (isset($payments[$i]["price"])) {
                $fee += $payments[$i]["price"] * 0.032 + 0.46;
                $income += $payments[$i]["price"];
            }
            $i++;
        }

        // Do the loop users
        $i = 0;
        while ($i < count($newUserPayments)) {
            if (isset($newUserPayments[$i]["price"])) {
                $newFee += $newUserPayments[$i]["price"] * 0.032 + 0.46;
                $newIncome += $newUserPayments[$i]["price"];
            }
            $i++;
        }

        // Do the loop lvl2+users
        $i = 0;
        while ($i < count($newLvlUserPayments)) {
            if (isset($newLvlUserPayments[$i]["price"])) {
                $newLvlFee += $newLvlUserPayments[$i]["price"] * 0.032 + 0.46;
                $newLvlIncome += $newLvlUserPayments[$i]["price"];
            }
            $i++;
        }

        // Number of users
        $users_count = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) AS `users_o` FROM `users` WHERE `join_date` >= '" . (time() - $time) . "' ");

        $usersLvl_count = $GLOBALS['database']->fetch_data("
             SELECT COUNT(`id`) AS `users_o` 
             FROM (`users`,`users_statistics`) 
             WHERE `level_id` > 1 AND `join_date` >= '" . (time() - $time) . "' AND uid=id");

        $facebookUsers = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) AS `users_o` FROM `users` WHERE `fbID` != 0 AND `join_date` >= '" . (time() - $time) . "' ");
        $totalFacebookUsers = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) AS `users_o` FROM `users` WHERE `fbID` != 0 ");

        //echo $_POST["newUsers"];
        $GLOBALS['template']->assign('income', $income);
        $GLOBALS['template']->assign('fee', $fee);

        $GLOBALS['template']->assign('users_o', $users_count[0]["users_o"]);
        $GLOBALS['template']->assign('newIncome', $newIncome);
        $GLOBALS['template']->assign('newFee', $newFee);

        $GLOBALS['template']->assign('lvlusers_o', $usersLvl_count[0]["users_o"]);
        $GLOBALS['template']->assign('lvlnewIncome', $newLvlIncome);
        $GLOBALS['template']->assign('lvlnewFee', $newLvlFee);

        $GLOBALS['template']->assign('facebookPeriod', $facebookUsers[0]["users_o"]);
        $GLOBALS['template']->assign('facebookTotal', $totalFacebookUsers[0]["users_o"]);

        $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/accounting/userAnalysis.tpl');
    }

}

new accounting();