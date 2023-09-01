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

//the class that manages the data for occupations
class OccupationData
{
    //flag that turns on or off caching(does not clear cache)
    private $cache = false;//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////turn back on

    //these three arrays holds all of the data.
    public $special_occupation = array();
    public $normal_occupation = array();
    public $profession = array();


    //constructor builds the 3 arrays listed above.
    public function __construct($uid = NULL)
    {
        if($uid == NULL) $this->uid = $_SESSION['uid'];
        else $this->uid = $uid;

        //init all needed positions in arrays
        $this->special_occupation['none'] = true;
        $this->special_occupation['id'] = 0;
        $this->special_occupation['name'] = '';
        $this->special_occupation['description'] = '';
        $this->special_occupation['villlage'] = '';
        $this->special_occupation['rankid'] = '';
        $this->special_occupation['surgeonSP_exp'] = '';
        $this->special_occupation['surgeonCP_exp'] = '';
        $this->special_occupation['bountyHunter_exp'] = '';
        $this->special_occupation['feature'] = '';

        $this->normal_occupation['none'] = true;
        $this->normal_occupation['id'] = 0;
        $this->normal_occupation['name'] = '';
        $this->normal_occupation['description'] = '';
        $this->normal_occupation['village'] = '';
        $this->normal_occupation['rankid'] = '';
        $this->normal_occupation['gain_1'] = '';
        $this->normal_occupation['gain_2'] = '';
        $this->normal_occupation['gain_3'] = '';
        $this->normal_occupation['proffessionSupport'] = '';
        $this->normal_occupation['level'] = '';
        $this->normal_occupation['promotion'] = '';
        $this->normal_occupation['last_gain'] = '';
        $this->normal_occupation['collect_count'] = 0;

        $this->profession['none'] = true;
        $this->profession['name'] = '';
        $this->profession['description'] = '';
        $this->profession['rankid'] = '';
        $this->profession['required_item_id'] = '';
        $this->profession['exp'] = '';

        //checking for memcache
        if($this->cache)
        {
            $temp_normal_occupation = @$GLOBALS['cache']->get(Data::$target_site.$this->uid.'normal_occupation');
            $temp_special_occupation = @$GLOBALS['cache']->get(Data::$target_site.$this->uid.'special_occupation');
            $temp_profession = @$GLOBALS['cache']->get(Data::$target_site.$this->uid.'profession');
        }

        //check normal_occupation
        if(isset($temp_normal_occupation['name']))
        {
            $this->normal_occupation = $temp_normal_occupation;
        }
        else
        {
            //call to database and fill
            $temp_normal_occupation = $GLOBALS['database']->fetch_data('SELECT * FROM `users_occupations` INNER JOIN `occupations` on (`occupation` = `id`) where `userid` = '.$this->uid);

            if(!is_array($temp_normal_occupation[0]))
                $this->normal_occupation['none'] = true;

            else
            {
                $this->normal_occupation['none'] = false;
                $this->normal_occupation['id'] = $temp_normal_occupation[0]['id'];
                $this->normal_occupation['name'] = $temp_normal_occupation[0]['name'];
                $this->normal_occupation['description'] = $temp_normal_occupation[0]['description'];
                $this->normal_occupation['village'] = $temp_normal_occupation[0]['village'];
                $this->normal_occupation['rankid'] = $temp_normal_occupation[0]['rankid'];
                $this->normal_occupation['gain_1'] = $temp_normal_occupation[0]['gain_1'];
                $this->normal_occupation['gain_2'] = $temp_normal_occupation[0]['gain_2'];
                $this->normal_occupation['gain_3'] = $temp_normal_occupation[0]['gain_3'];
                $this->normal_occupation['professionSupport'] = $temp_normal_occupation[0]['professionSupport'];
                $this->normal_occupation['level'] = $temp_normal_occupation[0]['level'];
                $this->normal_occupation['promotion'] = $temp_normal_occupation[0]['promotion'];
                $this->normal_occupation['last_gain'] = $temp_normal_occupation[0]['last_gain'];
                $this->normal_occupation['collect_count'] = $temp_normal_occupation[0]['collect_count'];
            }

            $this->syncNormalOccupationToCache();
        }

        //check special_occupation
        if(isset($temp_special_occupation['name']))
        {
            $this->special_occupation = $temp_special_occupation;

        }
        else
        {
            //call to database and fill
            $temp_special_occupation = $GLOBALS['database']->fetch_data('SELECT * FROM `users_occupations` INNER JOIN `occupations` on (`special_occupation` = `id`) where `userid` = '.$this->uid);
            if(!is_array($temp_special_occupation[0]))
                $this->special_occupation['none'] = true;

            else
            {
                $this->special_occupation['none'] = false;
                $this->special_occupation['id'] = $temp_special_occupation[0]['id'];
                $this->special_occupation['name'] = $temp_special_occupation[0]['name'];
                $this->special_occupation['description'] = $temp_special_occupation[0]['description'];
                $this->special_occupation['villlage'] = $temp_special_occupation[0]['village'];
                $this->special_occupation['rankid'] = $temp_special_occupation[0]['rankid'];
                $this->special_occupation['surgeonSP_exp'] = $temp_special_occupation[0]['surgeonSP_exp'];
                $this->special_occupation['surgeonCP_exp'] = $temp_special_occupation[0]['surgeonCP_exp'];
                $this->special_occupation['bountyHunter_exp'] = $temp_special_occupation[0]['bountyHunter_exp'];
                $this->special_occupation['feature'] = $temp_special_occupation[0]['feature'];
            }

            $this->syncSpecialOccupationToCache();

        }

        //check profession
        if(isset($temp_profession['name']))
        {
            $this->profession = $temp_profession;
        }
        else
        {
            //call to database and fill
            $temp_profession = $GLOBALS['database']->fetch_data('SELECT * FROM `users_occupations` INNER JOIN `occupations` on (`profession` = `id`) where `userid` = '.$this->uid);

            if(!is_array($temp_profession[0]) || !isset($temp_profession[0]['name']))
                $this->profession['none'] = true;


            else
            {
                $this->profession['none'] = false;
                $this->profession['name'] = $temp_profession[0]['name'];
                $this->profession['description'] = $temp_profession[0]['description'];
                $this->profession['rankid'] = $temp_profession[0]['rankid'];
                $this->profession['required_item_id'] = $temp_profession[0]['required_item_id'];
                $this->profession['exp'] = $temp_profession[0]['profession_exp'];
            }

            $this->syncProfessionToCache();
        }
    }

    //simply sends this objects data to the mem cache
    private function syncNormalOccupationToCache()
    {
        if($this->cache)
            @$GLOBALS['cache']->set(Data::$target_site.$this->uid.'normal_occupation',  $this->normal_occupation, MEMCACHE_COMPRESSED, 60*60);
    }

    //simply sends this objects data to the mem cache
    private function syncSpecialOccupationToCache()
    {
        if($this->cache)
            @$GLOBALS['cache']->set(Data::$target_site.$this->uid.'special_occupation',  $this->special_occupation, MEMCACHE_COMPRESSED, 60*60);
    }

    //simply sends this objects data to the mem cache
    private function syncProfessionToCache()
    {
        if($this->cache)
            @$GLOBALS['cache']->set(Data::$target_site.$this->uid.'profession',  $this->profession, MEMCACHE_COMPRESSED, 60*60);
    }

    public function hasNormalOccupation()
    {
        return !$this->normal_occupation['none'];
    }

    public function hasSpecialOccupation()
    {
        return !$this->special_occupation['none'];
    }

    public function hasProfession()
    {
        return !$this->profession['none'];
    }

    public function getNormalOccupation()
    {
        return $this->normal_occupation;
    }

    public function getSpecialOccupation()
    {
        return $this->special_occupation;
    }

    public function getProfession()
    {
        return $this->profession;
    }

    public function getNextNormalOccupation($rankid)
    {
        if($rankid == $this->normal_occupation['rankid'])
            return false;

        $occupations = $this->getNormalOccupations($rankid);

        foreach($occupations as $key => $occupation)
            if($occupation['professionSupport'] == $this->normal_occupation['professionSupport'] && $occupation['rankid'] == ($this->normal_occupation['rankid'] + 1))
                return $occupation;

        return false;
    }

    public function setNormalOccupation($occupation, $level = 1)
    {
        if(!$GLOBALS['database']->execute_query("UPDATE `users_occupations` SET `occupation` = ".$occupation.", `last_gain` = ".$GLOBALS['user']->load_time.", `promotion` = ".$GLOBALS['user']->load_time.", `level` = ".$level." WHERE `userid` = ".$_SESSION['uid']))
            throw new Exception ('there was an issue aquring that job');

        $temp_normal_occupation = $GLOBALS['database']->fetch_data('SELECT * FROM `users_occupations` INNER JOIN `occupations` on (`occupation` = `id`) where `userid` = '.$this->uid);

        if(!is_array($temp_normal_occupation[0]))
            $this->normal_occupation['none'] = true;

        else
        {
            $this->normal_occupation['none'] = false;
            $this->normal_occupation['id'] = $temp_normal_occupation[0]['id'];
            $this->normal_occupation['name'] = $temp_normal_occupation[0]['name'];
            $this->normal_occupation['description'] = $temp_normal_occupation[0]['description'];
            $this->normal_occupation['village'] = $temp_normal_occupation[0]['village'];
            $this->normal_occupation['rankid'] = $temp_normal_occupation[0]['rankid'];
            $this->normal_occupation['gain_1'] = $temp_normal_occupation[0]['gain_1'];
            $this->normal_occupation['gain_2'] = $temp_normal_occupation[0]['gain_2'];
            $this->normal_occupation['gain_3'] = $temp_normal_occupation[0]['gain_3'];
            $this->normal_occupation['professionSupport'] = $temp_normal_occupation[0]['professionSupport'];
            $this->normal_occupation['level'] = $temp_normal_occupation[0]['level'];
            $this->normal_occupation['promotion'] = $temp_normal_occupation[0]['promotion'];
            $this->normal_occupation['last_gain'] = $temp_normal_occupation[0]['last_gain'];
            $this->normal_occupation['collect_count'] = $temp_normal_occupation[0]['collect_count'];
        }

        $this->syncNormalOccupationToCache();
    }

    public function setSpecialOccupation($occupation, $temp_special_occupation = null)
    {
        if(!$GLOBALS['database']->execute_query("UPDATE `users_occupations` SET `special_occupation` = ".$occupation." WHERE `userid` = ".$this->uid))
            throw new Exception('there was an issue aquring that job');

        if(!isset($temp_special_occupation))
            $temp_special_occupation = $GLOBALS['database']->fetch_data('SELECT * FROM `users_occupations` INNER JOIN `occupations` on (`special_occupation` = `id`) where `userid` = '.$this->uid);

        if(!is_array($temp_special_occupation[0]))
            $this->special_occupation['none'] = true;

        else
        {
            $this->special_occupation['none'] = false;
            $this->special_occupation['id'] = $temp_special_occupation[0]['id'];
            $this->special_occupation['name'] = $temp_special_occupation[0]['name'];
            $this->special_occupation['description'] = $temp_special_occupation[0]['description'];
            $this->special_occupation['villlage'] = $temp_special_occupation[0]['village'];
            $this->special_occupation['rankid'] = $temp_special_occupation[0]['rankid'];
            $this->special_occupation['surgeonSP_exp'] = $temp_special_occupation[0]['surgeonSP_exp'];
            $this->special_occupation['surgeonCP_exp'] = $temp_special_occupation[0]['surgeonCP_exp'];
            $this->special_occupation['bountyHunter_exp'] = $temp_special_occupation[0]['bountyHunter_exp'];
            $this->special_occupation['feature'] = $temp_special_occupation[0]['feature'];
        }

        $this->syncSpecialOccupationToCache();

    }

    public function setFeature($user, $skip_database_update = false)
    {
        if(!$skip_database_update)
            if(!$GLOBALS['database']->execute_query("UPDATE `users_occupations` SET `feature` = '".$user."' WHERE `userid` = ".$this->uid))
                throw new Exception('there was an issue updating target to: '."UPDATE `users_occupations` SET `feature` = '".$user."' WHERE `userid` = ".$this->uid);

        $GLOBALS['Events']->acceptEvent('bounty_hunter_tracking', array('new'=>$user, 'old'=> $this->special_occupation['feature'] ));

        $this->special_occupation['feature'] = $user;

        $this->syncSpecialOccupationToCache();
    }

    public function setProfession()
    {
        $GLOBALS['DebugTool']->push('not built yet', 'set profession', __METHOD__, __FILE__, __LINE__);
    }

    public function quitNormalOccupation()
    {
        if(!$GLOBALS['database']->execute_query("UPDATE `users_occupations` SET `occupation` = 0, `level` = 0 WHERE `userid` = ".$this->uid))
            throw new Exception ('there was an issue quiting that job: '."UPDATE `users_occupations` SET `occupation` = 0, `level` = 0 WHERE `userid` = ".$this->uid);

        $this->normal_occupation['occupation'] = 0;
        $this->normal_occupation['none'] = true;
        $this->normal_occupation['name'] = '';
        $this->normal_occupation['level'] = 0;

        $this->syncNormalOccupationToCache();
    }

    public function swapSpecialOccupation($village)
    {
        $id = 0;
        if($this->special_occupation['name'] == 'Surgeon' || $this->special_occupation['name'] == '"Veterinarian"')
        {
            $occupations = $GLOBALS['database']->fetch_data('SELECT * FROM `users_occupations` INNER JOIN `occupations` on (`name` in (\'Surgeon\',\'"Veterinarian"\')) where `userid` = '.$this->uid);
            foreach($occupations as $occupation_data)
            {
                if($this->special_occupation['name'] != $occupation_data['name'] && $village == $occupation_data['village'])
                {
                    $occupations = [$occupation_data];
                    $this->setSpecialOccupation($occupations[0]['id'],$occupations);
                    $id=$occupations[0]['id'];
                    break;
                }   
            }
        }
        else if($this->special_occupation['name'] == 'Bounty Hunter' || $this->special_occupation['name'] == 'Mercenary')
        {
            $occupations = $GLOBALS['database']->fetch_data('SELECT * FROM `users_occupations` INNER JOIN `occupations` on (`name` in (\'Bounty Hunter\',\'Mercenary\')) where `userid` = '.$this->uid);

            foreach($occupations as $occupation_data)
            {
                if($this->special_occupation['name'] != $occupation_data['name'] && $village == $occupation_data['village'])
                {
                    $occupations = [$occupation_data];
                    $this->setSpecialOccupation($occupations[0]['id'],$occupations);
                    $this->special_occupation['feature'] = NULL;
                    $id=$occupations[0]['id'];
                    break;
                }   
            }
        }
        else if($this->special_occupation['name'] != '')
            throw new Exception('your special profession does not match a known special profession:"'.$this->special_occupation['name'].'" swapping failed.');

        return $id;
    }

    public function quitSpecialOccupation()
    {
        if($this->special_occupation['name'] == 'Surgeon' || $this->special_occupation['name'] == '"Veterinarian"')
            if(!$GLOBALS['database']->execute_query("UPDATE `users_occupations` SET `special_occupation` = 0, `surgeonSP_exp` = `surgeonSP_exp` * 0.85, `surgeonCP_exp` = `surgeonCP_exp` * 0.85 WHERE `userid` = ".$this->uid))
                throw new Exception ('there was an issue aquring that job');
            else
            {
                $GLOBALS['Events']->acceptEvent('surgeon_sp_exp', array('new'=>$this->special_occupation['surgeonSP_exp'] * 0.85, 'old'=> $this->special_occupation['surgeonSP_exp'] ));
                $GLOBALS['Events']->acceptEvent('surgeon_cp_exp', array('new'=>$this->special_occupation['surgeonCP_exp'] * 0.85, 'old'=> $this->special_occupation['surgeonCP_exp'] ));
                $this->special_occupation['surgeonSP_exp'] *= 0.85;
                $this->special_occupation['surgeonCP_exp'] *= 0.85;
            }

        else if($this->special_occupation['name'] == 'Bounty Hunter' || $this->special_occupation['name'] == 'Mercenary')
            if(!$GLOBALS['database']->execute_query("UPDATE `users_occupations` SET `special_occupation` = 0, `bountyHunter_exp` = `bountyHunter_exp` * 0.85, `feature` = NULL WHERE `userid` = ".$this->uid))
                throw new Exception ('there was an issue aquring that job');
            else
            {
                $GLOBALS['Events']->acceptEvent('bounty_hunter_exp', array('new'=>$this->special_occupation['bountyHunter_exp']*0.85, 'old'=> $this->special_occupation['bountyHunter_exp'] ));
                $this->special_occupation['bountyHunter_exp'] *= 0.85;
                $this->special_occupation['feature'] = NULL;
            }
        else if($this->special_occupation['name'] != '')
            throw new Exception('your special profession does not match a known special profession:"'.$this->special_occupation['name'].'" quiting failed.');

        $this->special_occupation['name'] = '';
        $this->special_occupation['none'] = true;
        $this->special_occupation['special_occupation'] = 0;

        $this->syncSpecialOccupationToCache();
    }

    public function quitProfession()
    {
        $GLOBALS['DebugTool']->push('not built yet', 'quit profession', __METHOD__, __FILE__, __LINE__);
    }

    public function levelNormalOccupation()
    {
        if(!$GLOBALS['database']->execute_query("UPDATE `users_occupations` SET `collect_count` = 0, `level` = (1 + `level`), `last_gain` = ".$GLOBALS['user']->load_time.", `promotion` = ".$GLOBALS['user']->load_time." WHERE `userid` = ".$this->uid))
            throw new Exception ('there was an issue aquring that job');

        $this->normal_occupation['level'] += 1;
        $this->normal_occupation['promotion'] = $GLOBALS['user']->load_time;
        $this->normal_occupation['collect_count'] = 0;
        $this->syncNormalOccupationToCache();
    }

    public function respondToGainsCollection($profession_exp_gain)
    {
        if($GLOBALS['database']->execute_query( "UPDATE `users_occupations` SET `collect_count` = (`collect_count` + 1), `last_gain` = ".$GLOBALS['user']->load_time." where `userid` = ".$this->uid) === false )
            throw new Exception('there was an issue setting your gain timer. '. "UPDATE `users_occupations` SET `last_gain` = ".$GLOBALS['user']->load_time." where `userid` = ".$this->uid);


        $this->normal_occupation['last_gain'] = $GLOBALS['user']->load_time;
        $this->normal_occupation['collect_count']++;
        $this->syncNormalOccupationToCache();

        $this->profession['exp'] += $profession_exp_gain;
        $this->syncProfessionToCache();
    }

    public function updateSpecialOccupationCache($bountyHunter_exp, $surgeonSP_exp = NULL, $surgeonCP_exp = NULL)
    {
        if($bountyHunter_exp != NULL)
        {
            $GLOBALS['Events']->acceptEvent('bounty_hunter_exp', array('new'=>$bountyHunter_exp, 'old'=> $this->special_occupation['bountyHunter_exp'] ));
            $this->special_occupation['bountyHunter_exp'] = $bountyHunter_exp;
        }

        if($surgeonSP_exp != NULL)
        {
            $GLOBALS['Events']->acceptEvent('surgeon_sp_exp', array('new'=>$surgeonSP_exp, 'old'=> $this->special_occupation['surgeonSP_exp'] ));
            $this->special_occupation['surgeonSP_exp'] = $surgeonSP_exp;
        }

        if($surgeonCP_exp != NULL)
        {
            $GLOBALS['Events']->acceptEvent('surgeon_cp_exp', array('new'=>$surgeonCP_exp, 'old'=> $this->special_occupation['surgeonCP_exp'] ));
            $this->special_occupation['surgeonCP_exp'] = $surgeonCP_exp;
        }

        $this->syncSpecialOccupationToCache();
    }

    public function updateCacheForJoinVillage($occupation, $special_occupation, $last_gain, $promotion, $level)
    {
        $this->normal_occupation['id'] = $occupation;
        if($occupation == 0)
        {
            $this->normal_occupation['none'] = true;
            $this->normal_occupation['name'] = '';
        }

        $this->special_occupation['id'] = $special_occupation;
        if($special_occupation == 0)
        {
            $this->special_occupation['none'] = true;
            $this->special_occupation['name'] = '';
        }

        $this->normal_occupation['last_gain'] = $last_gain;
        $this->normal_occupation['promotion'] = $promotion;
        $this->normal_occupation['level'] = $level;

        $this->syncSpecialOccupationToCache();
        $this->syncNormalOccupationToCache();
    }

    public function updateCacheForJail($last_gain, $promotion)
    {
        $this->normal_occupation['last_gain'] = $last_gain;
        $this->normal_occupation['promotion'] = $promotion;

        $this->syncNormalOccupationToCache();
    }

    //<><<>><<<>>><<<<>>>><<<<<>>>>><<<<<<>>>>>><<<<<>>>>><<<<>>>><<<>>><<>><>
    //static functions

    public static function getOccupations()
    {
        //checking for memcache
        $occupations = @$GLOBALS['cache']->get(Data::$target_site.'occupations');

        if(isset($occupations[0]['id']))
        {
            return $occupations;
        }
        else
        {
            $occupations = $GLOBALS['database']->fetch_data("SELECT * FROM `occupations`");
            if(!is_array($occupations))
                throw new Exception('error collecting occupation data!');

            @$GLOBALS['cache']->set(Data::$target_site.'occupations',  $occupations, MEMCACHE_COMPRESSED, 60);
            return $occupations;
        }

    }

    public static function getNormalOccupations($rankid, $occupation_id = NULL)
    {
        $occupations = self::getOccupations();



        foreach($occupations as $key => $occupation)
            if($occupation['type'] != 'occupation' || !($occupation['rankid'] <= $rankid))
            {
                unset($occupations[$key]);
            }
            else if($occupation_id != NULL && $occupation['id'] == $occupation_id)
            {
                return $occupation;
            }

        if($occupation_id != NULL)
            return false;
        else
            return $occupations;
    }

    public static function getSpecialOccupations($rankid, $village = NULL, $occupation_id = NULL)
    {
        $occupations = self::getOccupations();

        foreach($occupations as $key => $occupation)
            if($occupation['type'] != 'special' || !($occupation['rankid'] <= $rankid))
                unset($occupations[$key]);
            else if($village != NULL && $village != 'Syndicate' && $occupation['village'] == 'Syndicate')
                unset($occupations[$key]);
            else if($village != NULL && $village == 'Syndicate' && $occupation['village'] != 'Syndicate')
                unset($occupations[$key]);
            else if($occupation_id != NULL && $occupation['id'] == $occupation_id)
                return $occupation;

        if($occupation_id != NULL)
            return false;
        else
            return $occupations;
    }

    public static function getProfessions($rankid, $occupation_id = NULL)
    {
        $occupations = self::getProfession();

        foreach($occupations as $key => $occupation)
            if($occupation['type'] != 'profession' || $occupation['rankid'] <= $rankid)
                unset($occupations[$key]);
            else if($occupation_id != NULL && $occupation['id'] == $occupation_id)
                return $occupation;

        if($occupation_id != NULL)
            return false;
        else
            return $occupations;
    }

    public function dumpCache()
    {
        @$GLOBALS['cache']->delete(Data::$target_site.$this->uid.'normal_occupation');
        @$GLOBALS['cache']->delete(Data::$target_site.$this->uid.'special_occupation');
        @$GLOBALS['cache']->delete(Data::$target_site.$this->uid.'profession');
    }
}