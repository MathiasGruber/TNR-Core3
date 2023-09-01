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

require_once($_SERVER['DOCUMENT_ROOT'].'/libs/notificationSystem/notificationSystem.php');

class blueMessage {

    public function __construct() {
        if (!isset($_GET['act'])) {
            $this->hook_screen();
        } elseif ($_GET['act'] == 'new_hook') {
            if (!isset($_POST['Submit'])) {
                $this->hook_new_form();
            } else {
                $this->hook_upload_new();
            }
        } elseif ($_GET['act'] == 'edit_hook') {
            if (!isset($_POST['Submit'])) {
                $this->hook_edit_form();
            } else {
                $this->hook_do_edit();
            }
        } elseif ($_GET['act'] == 'delete_hook') {
            if (!isset($_POST['Submit'])) {
                $this->hook_confirm_delete();
            } else {
                $this->hook_do_delete();
            }
        }
    }

    // hook creation
    private function hook_screen() {

        // Show form
        $min = tableParser::get_page_min();

        if(isset($_POST['search']) && $_POST['search'])
        {
            $where = "WHERE `". ($_POST['search'] == 'hid' ? 'id' : $_POST['search']) ."` like '%{$_POST[$_POST['search']]}%'";
        }
        else
            $where = '';

        $query = "SELECT * FROM `hooks` {$where} ORDER BY `id` DESC LIMIT " . $min . ",25";

        $hooks = $GLOBALS['database']->fetch_data($query);

        tableParser::show_list(
                'hook', 
                'Hook admin', 
                $hooks, 
                array(
                    'id' => 'ID',
                    'state' => 'state',
                    'description' => "description"
                ), 
                array(
                    array("name" => "Modify", "act" => "edit_hook", "hid" => "table.id"),
                    array("name" => "Delete", "act" => "delete_hook", "hid" => "table.id")
                ), 
                true, // Send directly to contentLoad
                true,
                array(
                    array("name" => "New hook", "href" => "?id=" . $_GET["id"] . "&act=new_hook")
                ),
                true,
                false,
                array(
                    array(
                        'infoText'=>'Hid',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'hid',
                        'postIdentifier'=>'search',
                        'inputName'=>'hid'
                    ),
                    array(
                        'infoText'=>'Description',
                        'href'=>"?id=" . $_GET["id"],
                        'postField'=>'description',
                        'postIdentifier'=>'search',
                        'inputName'=>'description'
                    )
                )
        );
    }


    private function hook_confirm_delete() {
        if (isset($_GET['hid'])) {
            $GLOBALS['page']->Confirm("Delete this hook?", 'Hook System', 'Delete now!');
        } else {
            $GLOBALS['page']->Message("No valid hook ID was specified.", 'Hooks System', 'id=' . $_GET['id']);
        }
    }

    private function hook_do_delete() {
        if (isset($_GET['hid'])) {
            $query = "SELECT `id`,`description` FROM `hooks` WHERE `id` = '" . $_GET['hid'] . "' LIMIT 1";
            $data = $GLOBALS['database']->fetch_data($query);
            if ($data != '0 rows') {
                
                    $GLOBALS['database']->execute_query("DELETE FROM `hooks` WHERE `id` = '" . $data[0]['id'] . "' LIMIT 1");

                    $GLOBALS['page']->setLogEntry("Hook Change", 'hook '. $data[0]['description'] .' Deleted', $_GET['hid'] );

            } else {
                $GLOBALS['page']->Message("Hook could not be found.", 'Hooks System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("No valid hook ID was specified.", 'Hooks System', 'id=' . $_GET['id']);
        }
    }

    private function hook_edit_form() {
        if (isset($_GET['hid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `hooks` WHERE `id` = '" . $_GET['hid'] . "' LIMIT 1");
            if ($data != '0 rows') {
                tableParser::parse_form(    'hooks', 
                                            'Edit hook', 
                                            array('hook', 'id'), 
                                            $data, 
                                            null, 
                                            "", 
                                            false, 
                                            array( 
                                                'Restraints'=>array('user_data_restraints','item_restraints','quest_restraints'),
                                                'User'=>array('bloodline','status','village','kage','specialization','marriage','rank','rank id <=','rank id >=','level <=','level >=','level id <=','level id >=','experience <=','experience >=','home','login streak <=','login streak >='),
                                                'Travel & Pages'=>array('location name','location region','location owner','location claimable','location x','location y','page'),
                                                'Combat'=>array('combat conclusion','combat type','combat allies','combat opponents','ai death','user death','pvp experience <=','pvp experience >=','pvp streak <=','pvp streak >='),
                                                'Quests'=>array('quest status','quest id'),
                                                'Inventory'=>array('item person','item home','item furniture','item equip','item repair','item durability gain','item durability loss','item used','item quantity gain','item quantity loss'),
                                                'Elements'=>array('elements primary','elements secondary','elements bloodline primary','elements bloodline secondary','elements bloodline special','elements active primary','elements active secondary','elements active special','stats element mastery 1 <=','stats element mastery 1 >=','stats element mastery 2 <=','stats element mastery 2 >='),
                                                'Jutsu'=>array('jutsu learned','jutsu leveled','jutsu level <=','jutsu level >=','jutsu used','jutsu times used <=','jutsu times used >='),
                                                'Pools'=>array('stats max health <=','stats max health >=','stats max sta <=','stats max sta >=','stats max cha <=','stats max cha >=','stats cur health <=','stats cur health >=','stats cur sta <=','stats cur sta >=','stats cur cha <=','stats cur cha >='),
                                                'Generals'=>array('stats strength <=','stats strength >=','stats intelligence <=','stats intelligence >=','stats willpower <=','stats willpower >=','stats speed <=','stats speed >='),
                                                'Offenses & Defenses'=>array('stats nin off <=','stats nin off >=','stats gen off <=','stats gen off >=','stats tai off <=','stats tai off >=','stats weap off <=','stats weap off >=','stats nin def <=','stats nin def >=','stats gen def <=','stats gen def >=','stats tai def <=','stats tai def >=','stats weap def <=','stats weap def >='),
                                                'Village'=>array('village loyalty gain <=','village loyalty gain >=','village loyalty loss <=','village loyalty loss >=','diplomacy gain <=','diplomacy gain >=','diplomacy loss <=','diplomacy loss >=','crime','errand'),
                                                'Profession & Occupation'=>array('profession change','occupation change','special occupation change','occupation level <=','occupation level >=','profession exp <=','profession exp >=','surgeon sp exp <=','surgeon sp exp >=','surgeon cp exp <=','surgeon cp exp >=','bounty hunter exp >=','bounty hunter exp <=','bounty hunter tracking','profession craft','surgeon heal','bounty collected <=','bounty collected >='),
                                                'Currencies'=>array('money gain <=','money gain >=','money loss <=','money loss >=','rep gain <=','rep gain >=','rep loss <=','rep loss >=','pop gain <=','pop gain >=','pop loss <=','pop loss >=','deposit <=','deposit >=','withdraw <=','withdraw >='),
                                                'Groups'=>array('clan','clan leader','anbu'),
                                                'Communications'=>array('message owner','tavern send','tavern receive','pm send','pm receive'),
                                                'Time'=>array('day','year <=','year >=','month <=','month >=','day numeric <=','day numeric >=','hour <=','hour >=','minute <=','minute >=','second <=','second >=','unix time <=','unix time >=')
                                            ));
            } else {
                $GLOBALS['page']->Message("This hook does not exist.", 'Hooks System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid hook ID specified.", 'Hooks System', 'id=' . $_GET['id']);
        }
    }

    private function hook_do_edit() {
        if (isset($_GET['hid'])) {
            $data = $GLOBALS['database']->fetch_data("SELECT * FROM `hooks` WHERE `id` = '" . $_GET['hid'] . "'  LIMIT 1");
            if ($data != '0 rows') {
                $changed = tableParser::check_data('hooks', 'id', $_GET['hid'], array());
                if (tableParser::update_data('hooks', 'id', $_GET['hid'])) {

                    $GLOBALS['page']->Message("The hook has been updated.", 'Hooks System', 'id=' . $_GET['id']);

                    $GLOBALS['page']->setLogEntry("Hook Change", "Hook description:" . $_POST['description'] . " Changed:<br>" . $changed , $_GET['hid'] );

                } else {
                    $GLOBALS['page']->Message("An error occured while updating the hook.", 'Hooks System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This hook does not exist.", 'Hooks System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("Invalid hook ID specified.", 'Hooks System', 'id=' . $_GET['id']);
        }
    }

    private function hook_new_form() {
        tableParser::parse_form('hooks', 
                                'Insert new hook', 
                                array('hook','id'),
                                null,
                                null,
                                "",
                                true,
                                array( 
                                    'Restraints'=>array('user_data_restraints','item_restraints','quest_restraints'),
                                    'User'=>array('bloodline','status','village','kage','specialization','marriage','rank','rank id <=','rank id >=','level <=','level >=','level id <=','level id >=','experience <=','experience >=','home','login streak <=','login streak >='),
                                    'Travel & Pages'=>array('location name','location region','location owner','location claimable','location x','location y','page'),
                                    'Combat'=>array('combat conclusion','combat type','combat allies','combat opponents','ai death','user death','pvp experience <=','pvp experience >=','pvp streak <=','pvp streak >='),
                                    'Quests'=>array('quest status','quest id'),
                                    'Inventory'=>array('item person','item home','item furniture','item equip','item repair','item durability gain','item durability loss','item used','item quantity gain','item quantity loss'),
                                    'Elements'=>array('elements primary','elements secondary','elements bloodline primary','elements bloodline secondary','elements bloodline special','elements active primary','elements active secondary','elements active special','stats element mastery 1 <=','stats element mastery 1 >=','stats element mastery 2 <=','stats element mastery 2 >='),
                                    'Jutsu'=>array('jutsu learned','jutsu leveled','jutsu level <=','jutsu level >=','jutsu used','jutsu times used <=','jutsu times used >='),
                                    'Pools'=>array('stats max health <=','stats max health >=','stats max sta <=','stats max sta >=','stats max cha <=','stats max cha >=','stats cur health <=','stats cur health >=','stats cur sta <=','stats cur sta >=','stats cur cha <=','stats cur cha >='),
                                    'Generals'=>array('stats strength <=','stats strength >=','stats intelligence <=','stats intelligence >=','stats willpower <=','stats willpower >=','stats speed <=','stats speed >='),
                                    'Offenses & Defenses'=>array('stats nin off <=','stats nin off >=','stats gen off <=','stats gen off >=','stats tai off <=','stats tai off >=','stats weap off <=','stats weap off >=','stats nin def <=','stats nin def >=','stats gen def <=','stats gen def >=','stats tai def <=','stats tai def >=','stats weap def <=','stats weap def >='),
                                    'Village'=>array('village loyalty gain <=','village loyalty gain >=','village loyalty loss <=','village loyalty loss >=','diplomacy gain <=','diplomacy gain >=','diplomacy loss <=','diplomacy loss >=','crime','errand'),
                                    'Profession & Occupation'=>array('profession change','occupation change','special occupation change','occupation level <=','occupation level >=','profession exp <=','profession exp >=','surgeon sp exp <=','surgeon sp exp >=','surgeon cp exp <=','surgeon cp exp >=','bounty hunter exp >=','bounty hunter exp <=','bounty hunter tracking','profession craft','surgeon heal','bounty collected <=','bounty collected >='),
                                    'Currencies'=>array('money gain <=','money gain >=','money loss <=','money loss >=','rep gain <=','rep gain >=','rep loss <=','rep loss >=','pop gain <=','pop gain >=','pop loss <=','pop loss >=','deposit <=','deposit >=','withdraw <=','withdraw >='),
                                    'Groups'=>array('clan','clan leader','anbu'),
                                    'Communications'=>array('message owner','tavern send','tavern receive','pm send','pm receive'),
                                    'Time'=>array('day','year <=','year >=','month <=','month >=','day numeric <=','day numeric >=','hour <=','hour >=','minute <=','minute >=','second <=','second >=','unix time <=','unix time >=')
                                ));
    }

    private function hook_upload_new() {
        if (tableParser::insert_data('hooks')) {
            $GLOBALS['page']->Message("The hook has been inserted.", 'Hooks System', 'id=' . $_GET['id']);
            $GLOBALS['page']->setLogEntry("Hook New", 'hook description: <i>' . $_POST['description'] . '</i> Created'
                                                             .'<br>description: '.$_POST['description'],"");

        } else {
            $GLOBALS['page']->Message("An error occured while inserting the hook.", 'Hooks System', 'id=' . $_GET['id']);
        }
    }


}

new blueMessage();