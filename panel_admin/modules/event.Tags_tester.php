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

/*Author: Tyler Smith
 *Class: TagsTester
 *  this class lets users test the tag system
 *
 */

require_once(Data::$absSvrPath.'/global_libs/Tags/Tags.php');
require_once(Data::$absSvrPath.'/global_libs/Tags/Tag.php');
require_once(Data::$absSvrPath.'/libs/Battle/Battle.php');
require_once(Data::$absSvrPath.'/tools/DebugTool.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/libs/notificationSystem/notificationSystem.php');
class TagsTester extends Battle
{
    function __construct()
    {
        $GLOBALS['NOTIFICATIONS'] = new NotificationSystem('');

        $GLOBALS['DebugTool'] = new DebugTool();

        parent::__construct($_SESSION['uid'].'0',20,true);

        if(isset($_POST['reset']))
        {
            $this->purgeCache();
        }



        if((isset($_POST['addUserForm']) && isset($_POST['addUserButton'])) || (isset($_POST['do_all']) && isset($_POST['do_all'])))
        {
            $addUser = explode('|', trim($_POST['addUserForm']));

            foreach($addUser as $value)
            {
                if($value != '')
                {
                    $temp = explode(',',trim($value));
                    $temp[0] = trim($temp[0]);
                    $temp[1] = trim($temp[1]);

                    if(count($this->addAI($temp[0],$temp[1],true)) < 1)
                        $this->addUser($temp[0],$temp[1],true);
                }
            }
        }


        if((isset($_POST['doJutsuButton']) && isset($_POST['doJutsuButton'])) )
        {
            $jutsu =  $_POST['doJutsuForm'];
            $owner =  $_POST['owningUserForm'];
            $target = $_POST['targetUserForm'];
            $weapon1 = $_POST['weapon1Form'];
            $weapon2 = $_POST['weapon1Form'];
            $weapons = array();

            if($weapon1 != '')
                $weapons[] = $weapon1;

            if($weapon2 != '')
                $weapons[] = $weapon2;

            $this->doJutsu($target, $owner, $jutsu, $weapons);
        }


        if((isset($_POST['addTagsForm']) && isset($_POST['addTagsButton']))  || (isset($_POST['do_all']) && isset($_POST['do_all'])) )
        {
            $addTags = explode('|', preg_replace('/\s+/', '', $_POST['addTagsForm']));

            foreach($addTags as $value)
            {
                if($value != '')
                {
                    $universalFields = explode('}',$value);

                    if(count($universalFields) == 1)
                    {
                        $temp = explode('~', $value);
                        $universalFields = false;
                    }
                    else if(count($universalFields) == 2)
                    {
                        $temp = explode('~', $universalFields[1]);
                        $universalFields = ltrim($universalFields[0],'{');
                    }

                    $tags = array();
                    foreach($temp as $key => $tag)
                    {
                        if($key < count($temp) - 1)
                        {
                            $temp_tag = explode(':', $tag);
                            if(isset($temp_tag[1]))
                                $tags[] = new Tag($temp_tag[0], $temp_tag[1], $universalFields, true);
                            else
                                new Exception('looks like there is a missing ":" at the end of a tags name: '.$temp_tag[0]);
                        }
                        else
                        {
                            $control = explode(',', $tag);
                        }
                    }

                    $convert = array ('bloodline'=>'B', 'armor'=>'A', 'location'=>'L', 'jutsu'=>'J', 'weapon'=>'W', 'item'=>'I', 'basic attack'=>'D');

                    if(count($control) >= 3 && count($control) <= 5)
                    {

                        if(isset($convert[$control[2]]))
                            $control[2] = $convert[$control[2]];

                        else if(!in_array($control[2],$convert))
                        {
                            $GLOBALS['DebugTool']->push($convert,'broken tag origin. can not be: '.$control[2].' must be: ',__METHOD__, __FILE__, __LINE__);
                            $control[2] = 'B';
                        }

                        if(count($control) == 3)
                            $this->addTags($tags, $control[0], $control[1], $control[2]);
                        else if (count($control) == 4)
                            $this->addTags($tags, $control[0], $control[1], $control[2], $control[3]);
                        else
                            $this->addTags($tags, $control[0], $control[1], $control[2], $control[3], $control[4]);
                    }
                    else
                        $GLOBALS['DebugTool']->push('','wrong number of control arguments. up to 5 and atleast 3. (target_user, owning_user, origin, equipment_id, effective_level)',__METHOD__, __FILE__, __LINE__);
                }
            }
        }



        if((isset($_POST['addLocationTagsForm']) && isset($_POST['addLocationTagsButton']))  || (isset($_POST['do_all']) && isset($_POST['do_all'])) )
        {
            $addLocationTags = explode('|', preg_replace('/\s+/', '', $_POST['addLocationTagsForm']));

            foreach($addLocationTags as $value)
            {
                if($value != '')
                {
                    $universalFields = explode('}',$value);

                    if(count($universalFields) == 1)
                    {
                        $temp = explode('~', $value);
                        $universalFields = false;
                    }
                    else if(count($universalFields) == 2)
                    {
                        $temp = explode('~', $universalFields[1]);
                        $universalFields = ltrim($universalFields[0],'{');
                    }

                    $tags = array();
                    foreach($temp as $key => $tag)
                    {
                        $temp_tag = explode(':', $tag);
                        if(isset($temp_tag[1]))
                            $tags[] = new Tag($temp_tag[0], $temp_tag[1], $universalFields, true);
                        else
                            throw new Exception('looks like there is a missing : at the end of a tag name: '.$tmp_tag[0]);
                    }

                    if(isset($_POST['addLocationTagsOverride']) && $_POST['addLocationTagsOverride'] == true)
                        $this->changeLocationTags($tags);
                    else
                        $this->addLocationTags($tags);
                }
            }
        }



        if((isset($_POST['removeUserForm']) && isset($_POST['removeUserButton']))  || (isset($_POST['do_all']) && isset($_POST['do_all'])) )
        {
            $removeUser = explode('|', preg_replace('/\s+/', '', $_POST['removeUserForm']));

            foreach($removeUser as $value)
            {
                if($value != '')
                {
                    $this->removeUser($value);
                }
            }
        }

        if((isset($_POST['removeEquipmentByIdForm']) && isset($_POST['removeEquipmentByIdButton']))  || (isset($_POST['do_all']) && isset($_POST['do_all'])) )
        {
            $removeEquipmentById = explode('|', preg_replace('/\s+/', '', $_POST['removeEquipmentByIdForm']));

            foreach($removeEquipmentById as $value)
            {
                if($value != '')
                {
                    $this->removeEquipmentById($value);
                }
            }
        }




        if((isset($_POST['show_turn']))  || (isset($_POST['do_all']) && isset($_POST['do_all'])) )
        {
            $thang = array_keys($this->users);
            sort($thang);
            $this->processTags($thang);
        }
        else
        {
            $this->updateTagsInEffect();
        }



        $userList = array_keys($this->users);

        $jutsuList = array();
        foreach($this->users as $user_key => $user)
            foreach($user['jutsus'] as $jutsu_key => $jutsu)
                if($jutsu_key != 'cooldowns')
                    $jutsuList[] = array($jutsu_key, $user_key.'<=>'.$this->jutsus[$jutsu_key]['name']);

        $temp = '';
        foreach($userList as $user)
            $temp .= '<option value="'.$user.'">'.$user.'</option>';

        $userList = $temp;

        //$temp = '';
        //foreach($jutsuList as $jutsu)
        //    $temp .= '<option value="'.$jutsu[0].'">'.$jutsu[1].'</option>';
//
        //$jutsuList = $temp;
//
        //$temp = '<option value =""></option>';
        //foreach($this->users as $username => $userdata)
        //    foreach($userdata[parent::EQUIPMENT] as $equipment_id => $equipment_data)
        //        if($equipment_data['type'] == 'weapon')
        //            $temp .= '<option value ="'.$equipment_id.'">'.$username.'<=>'.$equipment_data['name'].'</option>';
//
        //$weaponList = $temp;
//
        //$GLOBALS['template']->assign('doJutsuOptions', $jutsuList);
//
        //$GLOBALS['template']->assign('weaponList', $weaponList);

        $GLOBALS['template']->assign('userList', $userList);

        //recording output
        ob_start();

        echo'users: ';
        echo '<details>';
        echo '<summary>user data: </summary>';

        if(count($this->users) != 0)
        foreach($this->users as $username => $user)
        {
            echo 'username: '.$username.'<br>';
            echo 'team: '.$user['team'].'<br>';
            echo 'health: '.$user['health'].' / '.$user['healthMax'].' (down: '.($user['healthMax']-$user['health']).')<br>';
            if(isset($user['stamina']))
            {
                echo 'stamina: '.$user['stamina'].' / '.$user['staminaMax'].' (down: '.($user['staminaMax']-$user['stamina']).')<br>';
                echo 'chakra: '.$user['chakra'].' / '.$user['chakraMax'].' (down: '.($user['chakraMax']-$user['chakra']).')<br>';
            }
            echo '<details>';
            echo '<summary>other data: </summary>';

            echo 'strength: '.$this->users[$username][parent::STRENGTH].'<br>';
            echo 'willpower: '.$this->users[$username][parent::WILLPOWER].'<br>';
            echo 'intelligence: '.$this->users[$username][parent::INTELLIGENCE].'<br>';
            echo 'speed: '.$this->users[$username][parent::SPEED].'<br>';
            echo 'specialization: '.$this->users[$username][parent::SPECIALIZATION].'<br>';
            echo '<br>';
            echo 'armor base: '.$this->users[$username][parent::ARMORBASE].'<br>';
            echo '<br>';
            echo 'offense'.'<br>';
            echo 'taijutsu: '.$this->users[$username][parent::OFFENSE.parent::TAIJUTSU].'<br>';
            echo 'ninjutsu: '.$this->users[$username][parent::OFFENSE.parent::NINJUTSU].'<br>';
            echo 'genjutsu: '.$this->users[$username][parent::OFFENSE.parent::GENJUTSU].'<br>';
            echo 'bukijutsu: '.$this->users[$username][parent::OFFENSE.parent::BUKIJUTSU].'<br>';
            echo '<br>';
            echo 'defense'.'<br>';
            echo 'taijutsu: '.$this->users[$username][parent::DEFENSE.parent::TAIJUTSU].'<br>';
            echo 'ninjutsu: '.$this->users[$username][parent::DEFENSE.parent::NINJUTSU].'<br>';
            echo 'genjutsu: '.$this->users[$username][parent::DEFENSE.parent::GENJUTSU].'<br>';
            echo 'bukijutsu: '.$this->users[$username][parent::DEFENSE.parent::BUKIJUTSU].'<br>';
            echo '<br>';
            echo 'rank: '.$this->users[$username][parent::RANK].'<br>';
            echo '<br>';
            echo 'uid: '.$this->users[$username]['uid'].'<br>';
            echo '<br>';
            echo 'elements: ';
            var_dump($this->users[$username][parent::ELEMENTS]);
            echo '<br>';
            echo '<br>';
            echo 'bloodline: '.$this->users[$username][parent::BLOODLINE].'<br>';
            echo '<br>';
            echo 'master: '.$this->users[$username][parent::MASTERY].'<br>';
            echo 'stability: '.$this->users[$username][parent::STABILITY].'<br>';
            echo 'accuracy: '.$this->users[$username][parent::ACCURACY].'<br>';
            echo 'expertise: '.$this->users[$username][parent::EXPERTISE].'<br>';
            echo 'chakraPower: '.$this->users[$username][parent::CHAKRAPOWER].'<br>';
            echo 'criticalString: '.$this->users[$username][parent::CRITICALSTRIKE].'<br>';
            echo '</details>';

            echo '<br>';

            echo 'status effects: <br>';
            var_dump($this->users[$username]['status_effects']);
            echo'<br>';
            echo'<br>';

            echo 'tags: <br>';
            foreach($user[self::TAGS] as $tag_key => $tag)
            {
                echo '<div style="margin-left:50px">';
                echo '<br>';
                echo 'tag name: '.$tag->name.'<br>';

                //print if active
                if(isset($user[self::TAGSINEFFECT][$tag_key]))
                    echo 'tag was active: yes<br>';
                else
                    echo 'tag was active: no<br>';

                echo '<details>';
                echo '<summary>raw tag data: </summary>';
                echo '<pre>';
                var_dump($tag);
                echo '</pre>';
                echo '</details>';
                echo '<br>';
                echo '</div>';
            }

            echo '<br>';
            echo '<br>';
        }

        echo '</details>';

        if(!isset($_POST['reset']))
        {
            $this->updateCache();
        }

        echo 'data: <br>';
        echo '<details>';
        echo '<summary>raw system data: </summary>';
        echo '<pre>';
        var_dump($this->data);
        echo '</pre>';
        echo '</details>';
        echo '<br>';
        echo '<br>';

        if(isset($this->run_ready_array))
        {
            echo 'run_ready_array<br>';
            echo '<details>';
            echo '<summary>post tags running: </summary>';
            echo '<pre>';
            var_dump($this->run_ready_array);
            echo '</pre>';
            echo '</details>';
            echo '<br>';
            echo '<br>';
        }

        echo 'everything: <br>';
        echo '<details>';
        echo '<summary>raw system data: </summary>';
        echo '<pre>';
        var_dump(get_object_vars($this));
        echo '</pre>';
        echo '</details>';


        //saving out put
        $result = ob_get_clean();

        if(isset($_POST['addUserForm']) && !(isset($_POST['clearForm']) && $_POST['clearForm'] && isset($_POST['reset'])) )
            $GLOBALS['template']->assign('addUser', $_POST['addUserForm']);

        if(isset($_POST['addTagsForm']) && !(isset($_POST['clearForm']) && $_POST['clearForm'] && isset($_POST['reset'])) )
            $GLOBALS['template']->assign('addTags', $_POST['addTagsForm']);

        if(isset($_POST['addLocationTagsForm']) && !(isset($_POST['clearForm']) && $_POST['clearForm'] && isset($_POST['reset'])) )
            $GLOBALS['template']->assign('addLocationTags', $_POST['addLocationTagsForm']);

        if(isset($_POST['removeUserForm']) && !(isset($_POST['clearForm']) && $_POST['clearForm'] && isset($_POST['reset'])) )
            $GLOBALS['template']->assign('removeUser', $_POST['removeUserForm']);

        if(isset($_POST['removeEquipmentByIdForm']) && !(isset($_POST['clearForm']) && $_POST['clearForm'] && isset($_POST['reset'])) )
            $GLOBALS['template']->assign('removeEquipmentById', $_POST['removeEquipmentByIdForm']);

        $GLOBALS['template']->assign('turn_counter', $this->turn_counter);
        $GLOBALS['template']->assign('cache_size', $this->cache_size);


        $GLOBALS['template']->assign('result',$result);
        $GLOBALS['template']->assign('contentLoad', './panel_event/templates/TagsTester/TagsTester.tpl');


        $GLOBALS['DebugTool']->popAll();

    }
}

new TagsTester();
