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

class itemPanel {

    public function __construct()
    {
        if ( !isset($_GET['act']) )
            $this->item_screen();      

        else if( $_GET['act'] == 'view' )
            $this->view();

    }

    // Item Functions
    function item_screen() {

        // Show form
        if (isset($_GET['type']))
        {
            switch ($_GET['type'])
            {
                case "arm": $type = 'armor';
                    break;
                case "wea": $type = 'weapon';
                    break;
                case "spc": $type = 'special';
                    break;
                case "ite": $type = 'item';
                    break;
                case "art": $type = 'artifact';
                    break;
                case "mat": $type = 'material';
                    break;
                case "pro": $type = 'process';
                    break;
            }
        }

        $query = "SELECT * FROM `items`";

        $where = array();

        if( isset($type) && $type != '' )
            $where[] = " `type` = '{$type}' ";
        
        if( isset($_POST['search']) )
            $where[] = " `". ($_POST['search'] == 'iid' ? 'id' : $_POST['search']) ."` LIKE '%{$_POST[$_POST['search']]}%' ";

        if( count($where) >= 1 )
            $query .= " WHERE ".implode(' AND ',$where);
            
        $result = $GLOBALS['database']->fetch_data($query);

        tableParser::show_list(
                'items', 
                'Item Lookup', 
                $result, 
                array(
                    'id' => "ID",
                    'name' => "Name",
                    'notes' => "Notes",
                    'required_rank' => "Rank",
                    'type' => "Type",
                    'in_shop' => "Shop",
                ),
                array(
                    array("name" => "View", "act" => "view", "iid" => "table.id")
                ),
                true, // Send directly to contentLoad
                false,
                array(
                    array("name" => "Armors", "href" => "?id=" . $_GET["id"] . "&type=arm"),
                    array("name" => "Weapons", "href" => "?id=" . $_GET["id"] . "&type=wea"),
                    array("name" => "Special", "href" => "?id=" . $_GET["id"] . "&type=spc"),
                    array("name" => "Items", "href" => "?id=" . $_GET["id"] . "&type=ite"),
                    array("name" => "Material", "href" => "?id=" . $_GET["id"] . "&type=mat"),
                    array("name" => "Process", "href" => "?id=" . $_GET["id"] . "&type=pro"),
                    array("name" => "Artifact", "href" => "?id=" . $_GET["id"] . "&type=art")
                ),
                true,
                false,
                array(
                    array(
                        'infoText'=>'Iid',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'iid',
                        'postIdentifier'=>'search',
                        'inputName'=>'iid'
                    ),
                    array(
                        'infoText'=>'Name',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'name',
                        'postIdentifier'=>'search',
                        'inputName'=>'name'
                    ),
                    array(
                        'infoText'=>'Notes',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'notes',
                        'postIdentifier'=>'search',
                        'inputName'=>'notes'
                    )
                )
        );

        // Set a return link for the page
        if( isset($_POST['search']) && $_POST['search'] != '' )
            $GLOBALS['template']->assign('returnLink', "?id=" . $_GET["id"] . "&act=search");
        else
            $GLOBALS['template']->assign('returnLink', true);
    }

    function view()
    {
        tableParser::parse_form('items', 'Item Details', array(), $GLOBALS['database']->fetch_data("SELECT * FROM `items` WHERE `id` = {$_GET['iid']}"));

        $GLOBALS['template']->assign('returnLink', "javascript:history.back()");
    }

}

new itemPanel();