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

class ninja_farmer {        

    function __construct() {    
        if (!isset($_GET['act'])) {
            $this->main_screen();
        } 
        elseif ($_GET['act'] == 'search') {
            if(!isset($_POST['Submit'])){
                $this->search_form();
            }
            else{
                $this->execute_search();
            }            
        }
    }
       
    // Main Menu
    function main_screen() {
        
        $min = tableParser::get_page_min();
        $items = $GLOBALS['database']->fetch_data("SELECT * FROM `ninja_farmer` LIMIT ".$min.",10 ");
        tableParser::show_list(
            'items',
            'Ninja Farmer Records', 
            $items,
            array(
                'uid' => "User ID", 
                'user' => "Username", 
                'farmer_points' => "Farmer Points",
                'pop_points' => "Pop Points",
                'last_update' => "Last Update Time",
            ), 
            array( 
                array("name" => "Profile", "act" => "modify", "iid" => "table.id"), 
            ) ,
            true, // Send directly to contentLoad
            false,
            array(
                array("name" => "Search", "href" =>"?id=".$_GET["id"]."&act=search")
            )
        );
        
        // Set a return link for the page
        $GLOBALS['template']->assign('returnLink', true);
    }
        

    // Search transactions
    private function search_form(){
        
        // Create the fields to be shown
        $inputFields = array(
            array(
                "inputFieldName"=>"account",
                "type"=>"select",
                "inputFieldValue"=> array(
                    "Core3" => "Core3",
                    "Core2" => "Core2"
                )
            ),
            array("infoText"=>"Lookup transaction ID","inputFieldName"=>"txtid", "type" => "input", "inputFieldValue" => "")
        );

        // Show user prompt
        $GLOBALS['page']->UserInput(
            "Search transaction ID against Paypal Database (and compares to TNR database).", // Information
            "Paypal DB Lookup", // Title
            $inputFields, // input fields
            array("href" => "?id=" . $_GET['id'] , "submitFieldName" => "Submit","submitFieldText" => "Search"), // Submit button
            "Return" // Return link name
        );
    }  
    

    function execute_search() {
        $query = "SELECT `name`,`required_rank`,`type`,`in_shop`,`id` FROM `items` ";
        if ($_POST['name'] != '') {
            $query .= "WHERE `name` LIKE '%" . $_POST['name'] . "%'";
            $preset = 1;
        }
        if ($_POST['in_shop'] != 'any') {
            if ($preset == 1) {
                $query .= " AND ";
            } else {
                $query .= "WHERE";
            }
            $query .= "`in_shop` = '" . $_POST['in_shop'] . "'";
        }
        if ($_POST['armor_type'] != 'any') {
            if ($preset == 1) {
                $query .= " AND ";
            } else {
                $query .= "WHERE";
            }
            $query .= "`armor_types` = '" . $_POST['armor_type'] . "'";
        }
        if ($_POST['item_type'] != 'any') {
            if ($preset == 1) {
                $query .= " AND ";
            } else {
                $query .= "WHERE";
            }
            $query .= "`item_type` = '" . $_POST['item_type'] . "'";
        }
        if ($_POST['price_int'] != '') {
            if ($preset == 1) {
                $query .= " AND ";
            } else {
                $query .= "WHERE";
            }
            $query .= "`price` " . $_POST['price_type'] . " '" . $_POST['price_int'] . "'";
        }
        if ($_POST['rank_id'] != '1' || $_POST['rank_type'] != '>=') {
            if ($preset == 1) {
                $query .= " AND ";
            } else {
                $query .= "WHERE";
            }
            $query .= "`required_rank` " . $_POST['rank_type'] . " '" . $_POST['rank_id'] . "'";
        }
        
        $items = $GLOBALS['database']->fetch_data($query);
        tableParser::show_list(
            'items',
            'Item admin', 
            $items,
            array(
                'id' => "ID", 
                'name' => "Name", 
                'required_rank' => "Rank",
                'type' => "Type",
                'in_shop' => "Shop",
            ), 
            array( 
                array("name" => "Users", "act" => "showUsers", "iid" => "table.id"), 
                array("name" => "Picture", "act" => "picture", "iid" => "table.id"), 
                array("name" => "Modify", "act" => "modify", "iid" => "table.id"), 
                array("name" => "Delete", "act" => "delete", "iid" => "table.id")
            ) ,
            true, // Send directly to contentLoad
            false,
            array(
                array("name" => "New item", "href" =>"?id=".$_GET["id"]."&act=new"),
                array("name" => "Armors", "href" =>"?id=".$_GET["id"]."&type=arm"),
                array("name" => "Weapons", "href" =>"?id=".$_GET["id"]."&type=wea"),
                array("name" => "Special", "href" =>"?id=".$_GET["id"]."&type=spc"),
                array("name" => "Items", "href" =>"?id=".$_GET["id"]."&type=ite"),
                array("name" => "Artifact", "href" =>"?id=".$_GET["id"]."&type=art"),
                array("name" => "Search", "href" =>"?id=".$_GET["id"]."&act=search")
            )
        );
        
        // Set a return link for the page
        $GLOBALS['template']->assign('returnLink', true);
    }
    

}

new ninja_farmer();