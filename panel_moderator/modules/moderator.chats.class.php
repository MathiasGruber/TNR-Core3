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

class chats
{
    public function __construct()
    {
        $GLOBALS['user']->load_time = time();

        try{
            if(!isset($_GET['act']) && !isset($_POST['submit'])){
                $this->show_main();
            }
            else if(isset($_GET['act']) && $_GET['act']=='kage')
            {
                $this->show_kage();
            }
            else if(isset($_GET['act']) && 0 === strpos($_GET['act'], 'anbu'))
            {
                $this->show_anbu();
            }
            else if(isset($_GET['act']) && 0 === strpos($_GET['act'], 'clan'))
            {
                $this->show_clan();
            }
            else if(isset($_GET['act']) && 0 === strpos($_GET['act'], 'marriage'))
            {
                $this->show_marriage();
            }
            else if(isset($_GET['act']) && 0 === strpos($_GET['act'], 'village'))
            {
                $this->show_village();
            }
            else if(isset($_POST['submit']))
                if(isset($_POST['anbu_option']) && $_POST['anbu_option'] != "")
                {
                    $this->link_anbu();
                }
                elseif(isset($_POST['clan_option']) && $_POST['clan_option'] != "")
                {
                    $this->link_clan();
                }
                elseif(isset($_POST['marriage_option']) && $_POST['marriage_option'] != "")
                {
                    $this->link_marriage();
                }
                elseif(isset($_POST['village_option']) && $_POST['village_option'] != "")
                {
                    $this->link_village();
                }
                else
                    $this->show_main();
            else
                $this->show_main();
        }
        catch (Exception $ex) {
            $GLOBALS['page']->Message($ex->getMessage(), 'chats', 'id='.$_GET['id']);
        }
    }

    private function show_main()
    {
        $option_shell = array('<option value = "','">');
        $anbu_options = "";
        $clan_options = "";
        $marriage_options = "";
        $village_options = "";

        //anbu stuffs
        $anbu_data = $GLOBALS['database']->fetch_data("SELECT `name` FROM  `squads`");
        foreach($anbu_data as $squad)
            $anbu_options .= $option_shell[0].$squad['name'].$option_shell[1];

        //clan stuffs
        $clan_data = $GLOBALS['database']->fetch_data("SELECT `name` FROM `clans`");
        foreach($clan_data as $clan)
            $clan_options .= $option_shell[0].$clan['name'].$option_shell[1];

        //marriage stuffs
        $marriage_data = array();

        $marriage_data_1 = $GLOBALS['database']->fetch_data("SELECT `mid`, `username` FROM `marriages` inner join `users` on (`uid` = `id`)");
        foreach($marriage_data_1 as $value)
            $marriage_data[$value['mid']]['name_1'] = $value['username'];

        $marriage_data_2 = $GLOBALS['database']->fetch_data("SELECT `mid`, `username` FROM `marriages` inner join `users` on (`oid` = `id`)");
        foreach($marriage_data_2 as $value)
            $marriage_data[$value['mid']]['name_2'] = $value['username'];

        foreach($marriage_data as $key => $value)
            if(isset($value['name_1']) && isset($value['name_2']))
            $marriage_options .= $option_shell[0].$value['name_1'].' - '.$value['name_2'].':'.$key.$option_shell[1];

        //village stuffs
        foreach(Data::$VILLAGES as $name)
            $village_options .= $option_shell[0].$name.$option_shell[1];


        $GLOBALS['template']->assign('anbu_options', $anbu_options);
        $GLOBALS['template']->assign('clan_options', $clan_options);
        $GLOBALS['template']->assign('marriage_options', $marriage_options);
        $GLOBALS['template']->assign('village_options', $village_options);
        $GLOBALS['template']->assign('contentLoad', './panel_moderator/templates/chats/main.tpl');
    }

    private function link_anbu()
    {
        $link = '<a href="?id='.$_GET['id'].'&act=anbu:'.$_POST['anbu_option'].'">special magic wonder link of awesome</>';
        $GLOBALS['page']->Message($link, 'chats', 'id='.$_GET['id']);
    }

    private function show_anbu()
    {
        $act_split = explode(':',$_GET['act']);
        $anbu_data = $GLOBALS['database']->fetch_data("SELECT `id`,`name` FROM  `squads` where `name` = '".$act_split[1]."'");

        // Get libraries
        require_once(Data::$absSvrPath.'/libs/chatSystem/chatLib.inc.php');
        require_once(Data::$absSvrPath.'/ajaxLibs/staticLib/markitup.bbcode-parser.php');

        // Instantiate chat class
        $anbuChat = new chatLib('tavern_anbu', true);

        $anbuChat->setupChatSystem(
            array(
                "userTitleOverwrite" => $anbuChat->getUserRank("ANBU Leader"),
                "tavernTable" => "tavern_anbu",
                "tableColumn" => "anbu_name",
                "tableSelect" => $anbu_data[0]['id'],
                "chatName" => $anbu_data[0]['name']." Chat",
                "canCombat" => true,
                "smartyTemplate" => "contentLoad"
            )
        );

    }

    private function link_village()
    {
        $link = '<a href="?id='.$_GET['id'].'&act=village:'.$_POST['village_option'].'">special magic wonder link of awesome</>';
        $GLOBALS['page']->Message($link, 'chats', 'id='.$_GET['id']);
    }

    private function show_village()
    {
        $act_split = explode(':',$_GET['act']);

        // Get libraries
        require_once(Data::$absSvrPath.'/libs/chatSystem/chatLib.inc.php');
        require_once(Data::$absSvrPath.'/ajaxLibs/staticLib/markitup.bbcode-parser.php');

        // Instantiate chat class
        $villageChat = new chatLib('tavern', true);

        $villageChat->setupChatSystem(
            array(
                "userTitleOverwrite" => $villageChat->getUserRank(),
                "tavernTable" => "tavern",
                "tableColumn" => "village_name",
                "tableSelect" => $act_split[1],
                "chatName" => $villageChat->getTavernName(),
                "smartyTemplate" => "contentLoad"
            )
        );

    }

    private function link_clan()
    {
        $link = '<a href="?id='.$_GET['id'].'&act=clan:'.$_POST['clan_option'].'">special magic wonder link of awesome</>';
        $GLOBALS['page']->Message($link, 'chats', 'id='.$_GET['id']);
    }

    private function show_clan()
    {

        $act_split = explode(':',$_GET['act']);

        // Get libraries
        require_once(Data::$absSvrPath.'/libs/chatSystem/chatLib.inc.php');
        require_once(Data::$absSvrPath.'/ajaxLibs/staticLib/markitup.bbcode-parser.php');

        // Get the clan & check its existence
        $clan = $GLOBALS['database']->fetch_data("
                SELECT `clans`.*, `users`.`username` as `leaderName`
                FROM `clans`
                LEFT JOIN `users` ON (`users`.`id` = `clans`.`leader_uid`)
                WHERE `clans`.`name` = '".$act_split[1]."' LIMIT 1");

        // Instantiate chat class
        $clanChat = new chatLib('tavern_clan', true);

        $clanChat->setupChatSystem(
            array(
                "userTitleOverwrite" => $clanChat->getUserRank("Clan Leader"),
                "tavernTable" => "tavern_clan",
                "tableColumn" => "clan_name",
                "tableSelect" => $clan[0]['id'],
                "chatName" => $clan[0]['name']." Chat",
                "smartyTemplate" => "contentLoad"
            )
        );

    }

    private function link_marriage()
    {
        $link = '<a href="?id='.$_GET['id'].'&act=marriage:'.$_POST['marriage_option'].'">special magic wonder link of awesome</>';
        $GLOBALS['page']->Message($link, 'chats', 'id='.$_GET['id']);
    }

    private function show_marriage()
    {
        $act_split = explode(':',$_GET['act']);

        require_once(Data::$absSvrPath.'/libs/chatSystem/chatLib.inc.php');
        require_once(Data::$absSvrPath.'/ajaxLibs/staticLib/markitup.bbcode-parser.php');

        // Instantiate chat class
        $marChat = new chatLib('tavern_marriage', true);

        $marChat->setupChatSystem(
            array(
                "userTitleOverwrite" => 'Third wheel',
                "tavernTable" => "tavern_marriage",
                "tableColumn" => "marriage_id",
                "tableSelect" => $act_split[2],
                "chatName" => "Marriage Chat",
                "subMessage" => "third wheel mode engage",
                "canCombat" => true,
                "smartyTemplate" => "contentLoad"
            )
        );
    }

    private function show_kage()
    {
        // Get libraries
        require_once(Data::$absSvrPath.'/libs/chatSystem/chatLib.inc.php');
        require_once(Data::$absSvrPath.'/ajaxLibs/staticLib/markitup.bbcode-parser.php');

        // Instantiate chat class
        $kageChat = new chatLib('tavern_leaders', true);

        $kageChat->setupChatSystem(
            array(
                "userTitleOverwrite" => $kageChat->getUserRank(),
                "tavernTable" => "tavern_leaders",
                "tableColumn" => "village",
                "tableSelect" => "KageUser",
                "chatName" => "Kage Chat",
                "smartyTemplate" => "contentLoad"
            )
        );
    }

}
new chats();