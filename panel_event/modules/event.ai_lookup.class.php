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

class aiPanel {

    public function __construct()
    {
        if ( !isset($_GET['act']) )
            $this->ai_screen();      

        else if( $_GET['act'] == 'view' )
            $this->view();

    }

    // ai Functions
    function ai_screen() {

        // Show form
        $query = "SELECT * FROM `ai` ";


        $where = array();
        
        if( isset($_POST['search']) )
            $where[] = " `". ($_POST['search'] == 'aid' ? 'id' : $_POST['search']) ."` LIKE '%{$_POST[$_POST['search']]}%' ";

        if( count($where) >= 1 )
            $query .= " WHERE ".implode(' AND ',$where);
            
        $result = $GLOBALS['database']->fetch_data($query);

        tableParser::show_list(
                'ai', 
                'Ai Lookup', 
                $result, 
                array(
                    'id' => "AI id", 
                    'name' => "Name", 
                    'type' => "Type", 
                    'rank' => "Rank",
                    'level' => "Level",                
                    'life' => "HP",
                    'strength' => "Str",                
                    'nin_off' => "Ninjutsu Offence",
                    'nin_def' => "Ninjutsu Defence",
                    'notes' => 'Notes'
                ),
                array(
                    array("name" => "View", "act" => "view", "aid" => "table.id")
                ),
                true, // Send directly to contentLoad
                false,
                false,
                true,
                false,
                array(
                    array(
                        'infoText'=>'Aid',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'aid',
                        'postIdentifier'=>'search',
                        'inputName'=>'aid'
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
        tableParser::parse_form('ai', 'AI Details', array(), $GLOBALS['database']->fetch_data("SELECT * FROM `ai` WHERE `id` = {$_GET['aid']}"));

        $GLOBALS['template']->assign('returnLink', "javascript:history.back()");
    }

}

new aiPanel();