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

// include validation library
require("../libs/profileFunctions/emailValidationClass.php");

// define class
class mailSystem {

    public function __construct() {

        // Enable adding
        $this->enable = true;

        if (!isset($_GET['act'])) {
            $this->main_screen();
        } elseif ($_GET['act'] == "addTNR") {
            $this->addTNR();
        } elseif ($_GET['act'] == "manual") {
            if (!isset($_POST['submit'])) {
                $this->addManualForm();
            } else {
                $this->addManualDo();
            }
        } elseif ($_GET['act'] == "see") {
            $this->seeList();
        } elseif ($_GET['act'] == "validate") {
            $this->validateList();
        }
    }

    // Main Screen
    private function main_screen() {
        $menu = array(
            array("name" => "Add TNR emails to list", "href" => "?id=" . $_GET['id'] . "&act=addTNR"),
            array("name" => "Manual add to list", "href" => "?id=" . $_GET['id'] . "&act=manual"),
            array("name" => "See list", "href" => "?id=" . $_GET['id'] . "&act=see"),
            array("name" => "Validate Emails", "href" => "?id=" . $_GET['id'] . "&act=validate")
        );
        $GLOBALS['template']->assign('subHeader', 'Add to the TNR email list');
        $GLOBALS['template']->assign('nCols', 2);
        $GLOBALS['template']->assign('nRows', 2);
        $GLOBALS['template']->assign('subTitle', "For managing the list of emails. Does not sync with mailgun.");
        $GLOBALS['template']->assign('linkMenu', $menu);
        $GLOBALS['template']->assign('contentLoad', './templates/menu/linkMenu.tpl');
    }

    private function insertEmail($email, $username = "") {
        $GLOBALS['database']->execute_query("
            INSERT INTO `email_list`
            (`email`, `lastUsername`) VALUES
            ( '" . $email . "', '" . $username . "' ) ");
    }
    
    private function deleteEmail( $email ){
        $GLOBALS['database']->execute_query("
            DELETE FROM `email_list`
            WHERE `email` = '".$email."' LIMIT 1");
    }
    
    private function updateEmail( $email ){
        $GLOBALS['database']->execute_query("
            UPDATE `email_list`
            SET `validated` = 'yes'
            WHERE `email` = '".$email."' LIMIT 1");
    }

    private function addTNR() {

        // Students with a sensei set, where sensei doesn't have the students
        $users = $GLOBALS['database']->fetch_data("
            SELECT `users`.`mail`, `users`.`username`
            FROM  `users`
            LEFT JOIN `email_list` ON (`email_list`.`email` = `users`.`mail`)
            WHERE `email_list`.`email` IS NULL
        ");

        if ($users !== "0 rows") {
            foreach ($users as $user) {
                $this->insertEmail($user['mail'], $user['username']);
            }

            $GLOBALS['page']->Message("Emails added", 'Mail list system', 'id=' . $_GET['id']);
        } else {
            $GLOBALS['page']->Message("All emails already on list", 'Mail list system', 'id=' . $_GET['id']);
        }
    }

    private function addManualForm($info = "Enter the list of emails to add below") {
        $GLOBALS['page']->UserInput(
                $info, "Mail List System", array(
            array("infoText" => "", "inputFieldName" => "list", "type" => "textarea", "inputFieldValue" => "")
                ), array(
            "href" => "?id=" . $_GET['id'] . "&act=" . $_GET['act'],
            "submitFieldName" => "submit",
            "submitFieldText" => "Submit"), "Return"
        );
    }

    private function addManualDo() {
        if (isset($_POST['list'])) {
            $emails = explode("\n", $_POST['list']);
            $action = "";
            foreach ($emails as $email) {
                $dbMail = $GLOBALS['database']->fetch_data("SELECT `id` FROM `email_list` WHERE `email` = '" . $email . "' LIMIT 1");
                if ($dbMail == "0 rows") {
                    $this->insertEmail($email);
                    $action .= "<br>" . $email . " has been added";
                } else {
                    $action .= "<br>" . $email . " already in DB";
                }
            }
            $this->addManualForm($action);
        } else {
            $GLOBALS['page']->Message("No emails specified", 'Mail list system', 'id=' . $_GET['id']);
        }
    }

    private function seeList() {

        // Count
        $count = $GLOBALS['database']->fetch_data("
             SELECT COUNT(`id`) as `volume`
            FROM  `email_list`
            WHERE `validated` = 'yes'
        ");
        
        // Count
        $count2 = $GLOBALS['database']->fetch_data("
             SELECT COUNT(`id`) as `volume`
            FROM  `email_list`
            WHERE `validated` = 'no'
        ");
        
        // Students with a sensei set, where sensei doesn't have the students
        $min = tableParser::get_page_min();
        $list = $GLOBALS['database']->fetch_data("
             SELECT *
            FROM  `email_list`
            WHERE `validated` = 'yes'
            LIMIT " . $min . ",10
        ");

        // Show form
        tableParser::show_list(
                'log', 'Email List', $list, array(
            'id' => "ID",
            'email' => "Email",
            'lastUsername' => "Last Username"
                ), false, true, // Send directly to contentLoad
                true, // No newer/older links
                false, // No top options links
                false, // No sorting on columns
                false, // No pretty options
                false, // No top search field
                'Browsing the mail list, oh yeah. Validated emails: '.$count[0]['volume']." Not validated: ".$count2[0]['volume']
        );

        // Return Link
        $GLOBALS['template']->assign("returnLink", true);
    }

    // Validate 100 emails at a time
    private function validateList() {

        // instantiate the class
        $SMTP_Validator = new SMTP_validateEmail();

        // turn on debugging if you want to view the SMTP transaction
        $SMTP_Validator->debug = false;

        // Get records
        $list = $GLOBALS['database']->fetch_data("
            SELECT *
            FROM  `email_list`
            WHERE `validated` = 'no'
            LIMIT 50
        ");

        if ($list !== "0 rows") {
            $emails = array();
            $message = "";

            // Attach emails to array
            foreach ($list as $data) {
                $emails[] = $data['email'];
            }

            // Validate emails
            $results = $SMTP_Validator->validate($emails);

            // Loop through results
            foreach ($results as $email => $result) {
                if ($result) {
                    $this->updateEmail($email);
                    $message .= $email . ' is valid<br>';
                } else {
                    $this->deleteEmail($email);
                    $message .= $email . ' is invalid<br>';
                }
            }

            $message .= $data['email'] . ' is ' . ($results[$data['email']] ? 'valid' : 'invalid') . "<br>";
            $GLOBALS['page']->Message($message, 'Mail list system', 'id=' . $_GET['id']);
        } else {
            $GLOBALS['page']->Message("No emails left", 'Mail list system', 'id=' . $_GET['id']);
        }
    }

}

new mailSystem();