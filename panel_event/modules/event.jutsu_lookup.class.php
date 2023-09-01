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

class jutsuPanel {

    public function __construct()
    {
        if ( !isset($_GET['act']) )
            $this->jutsu_screen();      

        else if( $_GET['act'] == 'view' )
            $this->view();

    }

    // Jutsu Functions
    function jutsu_screen() {

        // Show form
        if (isset($_GET['type'])) {
            switch ($_GET['type']) {
                case "gen": $type = 'genjutsu';
                    break;
                case "tai": $type = 'taijutsu';
                    break;
                case "wea": $type = 'weapon';
                    break;
                case "nin": $type = 'ninjutsu';
                    break;
                case "high": $type = 'highest';
                    break;
            }
        }

        $query = "SELECT * FROM `jutsu` ";


        $where = array();

        if( isset($type) && $type != '' )
            $where[] = " `attack_type` = '{$type}' ";
        
        if( isset($_POST['search']) )
            $where[] = " `". ($_POST['search'] == 'jid' ? 'id' : $_POST['search']) ."` LIKE '%{$_POST[$_POST['search']]}%' ";

        if( count($where) >= 1 )
            $query .= " WHERE ".implode(' AND ',$where);
            
        $result = $GLOBALS['database']->fetch_data($query);

        tableParser::show_list(
                'jutsu', 
                'Jutsu Lookup', 
                $result, 
                array(
                    'id' => 'JutsuID',
                    'name' => "Name",
                    'required_rank' => "Rank",
                    'attack_type' => "Type",
                    'jutsu_type' => "Jutsu Type",
                    'tags' => 'Tags',
                    'notes' => "Notes"
                ),
                array(
                    array("name" => "View", "act" => "view", "jid" => "table.id")
                ),
                true, // Send directly to contentLoad
                false,
                array(
                    array("name" => "Ninjutsu", "href" => "?id=" . $_GET["id"] . "&type=nin"),
                    array("name" => "Genjutsu", "href" => "?id=" . $_GET["id"] . "&type=gen"),
                    array("name" => "Taijutsu", "href" => "?id=" . $_GET["id"] . "&type=tai"),
                    array("name" => "Weapon", "href" => "?id=" . $_GET["id"] . "&type=wea"),
                    array("name" => "Highest", "href" => "?id=" . $_GET["id"] . "&type=high")
                ),
                true,
                false,
                array(
                    array(
                        'infoText'=>'Jid',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'jid',
                        'postIdentifier'=>'search',
                        'inputName'=>'jid'
                    ),
                    array(
                        'infoText'=>'Name',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'name',
                        'postIdentifier'=>'search',
                        'inputName'=>'name'
                    ),
                    array(
                        'infoText'=>'Rank (1-5)',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'required_rank',
                        'postIdentifier'=>'search',
                        'inputName'=>'required_rank'
                    ),
                    array(
                        'infoText'=>'Description',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'description',
                        'postIdentifier'=>'search',
                        'inputName'=>'description'
                    ),
                    array(
                        'infoText'=>'Notes',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'notes',
                        'postIdentifier'=>'search',
                        'inputName'=>'notes'
                    ),
                    array(
                        'infoText'=>'Tags',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'tags',
                        'postIdentifier'=>'search',
                        'inputName'=>'tags'
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
        tableParser::parse_form('jutsu', 'Jutsu Details', array(), $GLOBALS['database']->fetch_data("SELECT * FROM `jutsu` WHERE `id` = {$_GET['jid']}"));

        $GLOBALS['template']->assign('returnLink', "javascript:history.back()");
    }

}

new jutsuPanel();