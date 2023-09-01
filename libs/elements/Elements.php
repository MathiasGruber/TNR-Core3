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

class Elements
{
    //array that holds all of the classes information
        private $elements = array();

    //turn this to true to get extra information from exceptions thrown by this class.
        private $debug = false;

        private $cache = true;

        public function __construct($uid = NULL, $rank = NULL)
        {
             $this->cache = $GLOBALS['memOn'];

            if($uid == NULL || $uid == $_SESSION['uid'])
            {

                $uid = $_SESSION['uid'];
                $this->user_rank = $GLOBALS['userdata'][0]['rank_id'];
                $this->allow_sync = true;
            }
            else
            {
                if($rank == NULL)
                    $rank = $GLOBALS['userdata'][0]['rank_id'];

                $this->allow_sync = false;
                $this->user_rank = $rank;
            }

            //initalizing the keys in the elements property
            $this->elements['element_affinity_1'] = '';
            $this->elements['element_affinity_2'] = '';
            $this->elements['bloodline_affinity_1'] = '';
            $this->elements['bloodline_affinity_2'] = '';
            $this->elements['bloodline_affinity_special'] = '';
            $this->elements['element_mastery_1'] = '';
            $this->elements['element_mastery_2'] = '';
            $this->elements['element_mastery_special'] = ''; //not in database calculated based on other masterys

            //checking for memcache
            if($this->cache)
                $temp_elements = @$GLOBALS['cache']->get(Data::$target_site.$uid.'elements');

            if(isset($temp_elements['element_affinity_1']))
            {
                $this->elements = $temp_elements;
            }

            //if no memcache then pull data from database.
            else
            {
                //call to the database
                $temp_elements = $GLOBALS['database']->fetch_data("SELECT `element_affinity_1`, `element_affinity_2`, `element_mastery_1`, `element_mastery_2`, `bloodline_affinity_1`, `bloodline_affinity_2`, `bloodline_affinity_special`, `rank_id` FROM `users_statistics` WHERE `uid` = ".$uid);
                if(!is_array($temp_elements))
                {
                    //if there was an issue send an error to screen. if this->debug is on add the sql query to the error message.
                    if($this->debug)
                        $extra = "SELECT `element_affinity_1`, `element_affinity_2`, `element_mastery_1`, `element_mastery_2`, `bloodline_affinity_1`, `bloodline_affinity_2`, `rank_id` FROM `users_statistics` WHERE `uid` = ".$uid;
                    else
                        $extra = "";
                    throw new Exception('There was an error trying to receive elements data. '.$extra);
                }

                $mastery_cap = array( '1' => PHP_INT_MAX, '2' => PHP_INT_MAX, '3' => 160000, '4' => 200000, '5' => 250000 );

                //making sure masteries are not over_cap
                if($temp_elements[0]['element_mastery_1'] > $mastery_cap[$temp_elements[0]['rank_id']] )
                    $temp_elements[0]['element_mastery_1'] = $mastery_cap[$temp_elements[0]['rank_id']];

                if($temp_elements[0]['element_mastery_2'] > $mastery_cap[$temp_elements[0]['rank_id']] )
                    $temp_elements[0]['element_mastery_2'] = $mastery_cap[$temp_elements[0]['rank_id']];

                //making sure user has elements
                if($temp_elements[0]['element_affinity_1'] == '' || $temp_elements[0]['element_affinity_2'] == '')
                {
                    $possible_elements = array('fire', 'water', 'earth', 'wind', 'lightning');
                    $temp_elements[0]['element_affinity_1'] = $possible_elements[random_int(0,4)];
                    for($temp_elements[0]['element_affinity_2'] = $possible_elements[random_int(0,4)];
                        $temp_elements[0]['element_affinity_1'] == $temp_elements[0]['element_affinity_2'];
                        $temp_elements[0]['element_affinity_2'] = $possible_elements[random_int(0,4)]);

                    $this->setUserElementAffinityPrimary($temp_elements[0]['element_affinity_1']);
                    $this->setUserElementAffinitySecondary($temp_elements[0]['element_affinity_2']);
                }

                //setting the results of the database call to the instance
                $this->elements = $temp_elements[0];
                //setting special element mastery value
                $this->setelementMasterySpecial();

                //adding the data to the memcache
                $this->syncToCache();
            }

        }


    //simply sends this objects data to the mem cache
    private function syncToCache()
    {
        if($this->allow_sync && $this->cache)
            @$GLOBALS['cache']->set(Data::$target_site.$_SESSION['uid'].'elements',  $this->elements, MEMCACHE_COMPRESSED, 60*60);
    }
    
    public function dumpCache()
    {
        if($this->cache)
            @$GLOBALS['cache']->delete(Data::$target_site.$_SESSION['uid'].'elements',  $this->elements, MEMCACHE_COMPRESSED, 60*60);
    }

    //gets the users current elements
        //returns the elements in an array
            //primary at 0, secondary at 1, and special at 3 (some may not be set)
        //returns false if the user dosnt have any elements currently.
    public function getUserElements()
    {
    //if there are no bloodline affinities set
        if($this->elements['bloodline_affinity_1'] == '' && $this->elements['bloodline_affinity_2'] == '')
        {
        //if the user is atleast a jounin return both elements as an array
            if( $this->user_rank >= 4)
                return array($this->elements['element_affinity_1'], $this->elements['element_affinity_2'], '', 'none');

        //if the user is atleast a chuunin return the first element as a string
            else if( $this->user_rank >= 3)
                return array($this->elements['element_affinity_1'], '', '', 'none');

        //if the user is not atleast a chuunin return false
            else
                return array('','','', 'none');
        }

    //if one bloodline affinitie is set
        else if($this->elements['bloodline_affinity_1'] != '' && $this->elements['bloodline_affinity_2'] == '')
        {

        //if the user is atleast a jounin
            if( $this->user_rank >= 4)
            {
            //if the first blood line affinity is the same as the second standard affinity return the first standard affinity along with the first bloodline affinity instead as an array
                if($this->elements['bloodline_affinity_1'] == $this->elements['element_affinity_2'])
                    return array($this->elements['bloodline_affinity_1'], $this->elements['element_affinity_1'], '', 'none');

            //else return the first bloodline affinity and the second standard affinity as an array
                else
                    return array($this->elements['bloodline_affinity_1'], $this->elements['element_affinity_2'], '', 'none');
            }

        //if the user is atleast a chuunin return the first blood line element as an array
            if( $this->user_rank >= 3)
                return array($this->elements['bloodline_affinity_1'], '', '', 'none');

        //if the user is not alteast a chuunin return false
            else
                return array('','','', 'none');
        }

    //if both bloodline affinities are set
        else
        {

        //if the user is atleast a jounin
            if( $this->user_rank >= 4)
            {

            //if the user's bloodline has a special affinity return an array holding both blood line affinities and the special bloodline affinity
                if($this->elements['bloodline_affinity_special'] != '')
                    return array($this->elements['bloodline_affinity_1'], $this->elements['bloodline_affinity_2'], $this->elements['bloodline_affinity_special'], 'none');

            //otherwise return the user's bloodline affinities
                else
                    return array($this->elements['bloodline_affinity_1'], $this->elements['bloodline_affinity_2'], '', 'none');

            }

        //if the user is atleast a chuunin
            if( $this->user_rank >= 3)
            {

            //if the user's bloodline has a special affinity  return the users first bloodline affinity and the users bloodline special affinity
                if($this->elements['bloodline_affinity_special'] != '')
                    return array($this->elements['bloodline_affinity_1'], '', $this->elements['bloodline_affinity_special'], 'none');

            //otherwise return the users first bloodline affinity
                else
                    return array($this->elements['bloodline_affinity_1'], '', '', 'none');
            }
        //if the user is not atleast a chuunin return false
            else
                return array('','','', 'none');
        }
    }

    //function that returns the users element mastery value
    //if the users inputs 1 or inputs 2 or inputs 3 it will return that specific element mastery
    //if a rank number is supplied return the percentage raiting of the mastery based on rank.
    public function getUserElementMastery($key = NULL, $rank = NULL)
    {

        $percent_scale = array( '1' => PHP_INT_MAX, '2' => PHP_INT_MAX, '3' => 1600, '4' => 2000, '5' => 2500 );

        if($rank == NULL)
        {
            if($key == NULL)
                return array($this->elements['element_mastery_1'], $this->elements['element_mastery_2'], $this->elements['element_mastery_special']);
            else if($key == 1)
                return $this->elements['element_mastery_1'];
            else if($key == 2)
                return $this->elements['element_mastery_2'];
            else if($key == 3)
                return $this->elements['element_mastery_special'];
            else
                throw new Exception("bad ussage of getUserelementMastery. (bad key)");
        }
        else if (isset($percent_scale[$rank]))
        {
            if($key == NULL)
                return array((int)($this->elements['element_mastery_1'] / $percent_scale[$rank]), (int)($this->elements['element_mastery_2'] / $percent_scale[$rank]), (int)($this->elements['element_mastery_special'] / $percent_scale[$rank]));
            else if($key == 1)
                return (int)($this->elements['element_mastery_1'] / $percent_scale[$rank]);
            else if($key == 2)
                return (int)($this->elements['element_mastery_2'] / $percent_scale[$rank]);
            else if($key == 3)
                return (int)($this->elements['element_mastery_special'] / $percent_scale[$rank]);
            else
                throw new Exception("bad ussage of getUserelementMastery. (bad key)");
        }
        else
        {
            throw new Exception("bad ussage of getUserelementMastery. (bad rank)");
        }
    }

    //function that returns the users bloodline affinity values
    //if the users inputs 1 or inputs 2 or inputs 3 it will return that specific value
    public function getUserBloodlineAffinities($key = NULL)
    {
        if($key == NULL)
            return array($this->elements['bloodline_affinity_1'], $this->elements['bloodline_affinity_2'], $this->elements['bloodline_affinity_special']);
        else if($key == 1)
            return $this->elements['bloodline_affinity_1'];
        else if($key == 2)
            return $this->elements['bloodline_affinity_2'];
        else if($key == 3)
            return $this->elements['bloodline_affinity_special'];
        else
            throw new Exception("bad ussage of getUserBloodlineAffinities.");
    }


    //function that sets the user's primary element affinity
    public function setUserElementAffinityPrimary($affinity)
    {
        if($affinity != $this->elements['element_affinity_1'])
        {
            //updating this objects data
            $old = $this->elements['element_affinity_1'];
            $this->elements['element_affinity_1'] = $affinity;

            //updating cache
            $this->syncToCache();

            //updating database
            if ($GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `element_affinity_1` = '".$this->elements['element_affinity_1']."' WHERE `uid` = '".$_SESSION['uid']."'") === false)
            {
                //if there was an issue send an error to screen. if this->debug is on add the sql query to the error message.
                if($this->debug)
                    $extra = "UPDATE `users_statistics` SET `element_affinity_1` = '".$this->elements['element_affinity_1']."' WHERE `uid` = '".$_SESSION['uid']."'";
                else
                    $extra = "";
                throw new Exception('There was an error trying to update users primary element affinity'.$extra);
            }
            else
            {
                if($affinity == '')
                    $affinity = 'removed';

                $GLOBALS['Events']->acceptEvent('elements_primary', array('new'=>$affinity, 'old'=>$old ));

                if($this->elements['bloodline_affinity_1'] == '')
                    $GLOBALS['Events']->acceptEvent('elements_active_primary', array('new'=>$affinity, 'old'=>$old ));
            }
        }
    }

    //function that sets the user's secondary element affinity
    public function setUserElementAffinitySecondary($affinity)
    {
        if($affinity != $this->elements['element_affinity_2'])
        {
            //updating this objects data
            $old = $this->elements['element_affinity_2'];
            $this->elements['element_affinity_2'] = $affinity;

            //updating cache
            $this->syncToCache();

            //updating database
            if ($GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `element_affinity_2` = '".$this->elements['element_affinity_2']."' WHERE `uid` = '".$_SESSION['uid']."'") === false)
            {
                //if there was an issue send an error to screen. if this->debug is on add the sql query to the error message.
                if($this->debug)
                    $extra = "UPDATE `users_statistics` SET `element_affinity_2` = '".$this->elements['element_affinity_2']."' WHERE `uid` = '".$_SESSION['uid']."'";
                else
                    $extra = "";
                throw new Exception('There was an error trying to update users primary element affinity'.$extra);
            }
            else
            {
                if($affinity == '')
                    $affinity = 'removed';

                $GLOBALS['Events']->acceptEvent('elements_secondary', array('new'=>$affinity, 'old'=>$old ));

                if($this->elements['bloodline_affinity_2'] == '')
                    $GLOBALS['Events']->acceptEvent('elements_active_secondary', array('new'=>$affinity, 'old'=>$old ));
            }
        }
    }

    //if primary bloodline affinity is set return true else flase
    public function isBloodlinePrimaryAffinityActive()
    {
        if( isset($this->elements['bloodline_affinity_1']) && $this->elements['bloodline_affinity_1'] != '' &&  $this->user_rank >= 3)
            return true;
        else
            return false;
    }

    //if secondary bloodline affinity is set return true else flase
    public function isBloodlineSecondaryAffinityActive()
    {
        if( isset($this->elements['bloodline_affinity_2']) && $this->elements['bloodline_affinity_2'] != '' &&  $this->user_rank >= 4)
            return true;
        else
            return false;
    }

    //if special bloodline affinity is set return true else flase
    public function isBloodlineSpecialAffinityActive()
    {
        if( isset($this->elements['bloodline_affinity_special']) && $this->elements['bloodline_affinity_special'] != '' &&  $this->user_rank >= 3)
            return true;
        else
            return false;
    }

    //function that set the user's bloodline element affinities.
        //takes an array. [0] is the first affinity and [1] is the second affinity
    public function setUserBloodlineAffinities($affinities)
    {
        //checking to make sure that there will be a change made when the database is updated.
        $flag = array(false, false, false);

        //updating this objects data
        if(isset($affinities[0]))
            if($this->elements['bloodline_affinity_1'] != $affinities[0])
            {
                if($affinities[0] == '')
                {
                    $GLOBALS['Events']->acceptEvent('elements_bloodline_primary', array('new'=>'removed', 'old'=>$this->elements['bloodline_affinity_1'] ));

                    $GLOBALS['Events']->acceptEvent('elements_active_primary', array('new'=>$this->elements['element_affinity_1'], 'old'=>$this->elements['bloodline_affinity_1'] ));
                }
                else
                {
                    $GLOBALS['Events']->acceptEvent('elements_bloodline_primary', array('new'=>$affinities[0], 'old'=>$this->elements['bloodline_affinity_1'] ));

                    $GLOBALS['Events']->acceptEvent('elements_active_primary', array('new'=>$affinities[0], 'old'=>$this->elements['bloodline_affinity_1'] ));
                }
                $this->elements['bloodline_affinity_1'] = $affinities[0];
            }
            else
                $flag[0] = true;
        else
            $flag[0] = true;

        if(isset($affinities[1]))
            if($this->elements['bloodline_affinity_2'] != $affinities[1])
            {
                if($affinities[1] == '')
                {
                    $GLOBALS['Events']->acceptEvent('elements_bloodline_secondary', array('new'=>'removed', 'old'=>$this->elements['bloodline_affinity_2'] ));

                    $GLOBALS['Events']->acceptEvent('elements_active_secondary', array('new'=>$this->elements['element_affinity_2'], 'old'=>$this->elements['bloodline_affinity_2'] ));
                }
                else
                {
                    $GLOBALS['Events']->acceptEvent('elements_bloodline_secondary', array('new'=>$affinities[1], 'old'=>$this->elements['bloodline_affinity_2'] ));

                    $GLOBALS['Events']->acceptEvent('elements_active_secondary', array('new'=>$affinities[1], 'old'=>$this->elements['bloodline_affinity_2'] ));
                }
                $this->elements['bloodline_affinity_2'] = $affinities[1];
            }
            else
                $flag[1] = true;
        else
            $flag[1] = true;

        if(isset($affinities[2]))
            if($this->elements['bloodline_affinity_special'] != $affinities[2])
            {
                if($affinities[2] == '')
                {
                    $GLOBALS['Events']->acceptEvent('elements_bloodline_special', array('new'=>'removed', 'old'=>$this->elements['bloodline_affinity_special'] ));

                    $GLOBALS['Events']->acceptEvent('elements_active_special', array('new'=>'removed', 'old'=>$this->elements['bloodline_affinity_special'] ));
                }
                else
                {
                    $GLOBALS['Events']->acceptEvent('elements_bloodline_special', array('new'=>$affinities[2], 'old'=>$this->elements['bloodline_affinity_special'] ));

                    $GLOBALS['Events']->acceptEvent('elements_active_special', array('new'=>$affinities[2], 'old'=>$this->elements['bloodline_affinity_special'] ));
                }
                $this->elements['bloodline_affinity_special'] = $affinities[2];
            }
            else
                $flag[2] = true;
        else
            $flag[2] = true;

        if(!$flag[0] || !$flag[1] || !$flag[2])
        {

        //updating cache
            $this->syncToCache();

        //updating database
            if ($GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `bloodline_affinity_1` = '".$this->elements['bloodline_affinity_1']."',`bloodline_affinity_2` = '".$this->elements['bloodline_affinity_2']."',`bloodline_affinity_special` = '".$this->elements['bloodline_affinity_special']."' WHERE `uid` = '".$_SESSION['uid']."'") === false)
            {
            //if there was an issue send an error to screen. if this->debug is on add the sql query to the error message.
                if($this->debug)
                    $extra = "UPDATE `users_statistics` SET `bloodline_affinity_1` = '".$this->elements['bloodline_affinity_1']."',`bloodline_affinity_2` = '".$this->elements['bloodline_affinity_2']."',`bloodline_affinity_special` = '".$this->elements['bloodline_affinity_special']."' WHERE `uid` = '".$_SESSION['uid']."'";
                else
                    $extra = "";
                throw new Exception('There was an error trying to update user bloodline affinities'.$extra);
            }
        }
    }

    //function that set the user's bloodline element affinities.
    //takes an array. [0] is the first affinity and [1] is the second affinity
    public function removeUserBloodlineAffinities()
    {
        if($this->elements['bloodline_affinity_1'] != '' &&
            $this->elements['bloodline_affinity_2'] != '' &&
            $this->elements['bloodline_affinity_special'] != '')
        {
            if($this->elements['bloodline_affinity_1'] != '')
            {
                $GLOBALS['Events']->acceptEvent('elements_bloodline_primary', array('new'=>'removed', 'old'=>$this->elements['bloodline_affinity_1'] ));

                $GLOBALS['Events']->acceptEvent('elements_active_primary', array('new'=>$this->elements['element_affinity_1'], 'old'=>$this->elements['bloodline_affinity_1'] ));
            }

            if($this->elements['bloodline_affinity_2'] != '')
            {
                $GLOBALS['Events']->acceptEvent('elements_bloodline_secondary', array('new'=>'removed', 'old'=>$this->elements['bloodline_affinity_2'] ));

                $GLOBALS['Events']->acceptEvent('elements_active_secondary', array('new'=>$this->elements['element_affinity_2'], 'old'=>$this->elements['bloodline_affinity_2'] ));
            }

            if($this->elements['bloodline_affinity_special'] != '')
            {
                $GLOBALS['Events']->acceptEvent('elements_bloodline_special', array('new'=>'removed', 'old'=>$this->elements['bloodline_affinity_special'] ));

                $GLOBALS['Events']->acceptEvent('elements_active_special', array('new'=>'removed', 'old'=>$this->elements['bloodline_affinity_special'] ));
            }

            //updating this objects data
            $this->elements['bloodline_affinity_1'] = '';
            $this->elements['bloodline_affinity_2'] = '';
            $this->elements['bloodline_affinity_special'] = '';

            //updating cache
            $this->syncToCache();

            //updating database
            if ($GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `bloodline_affinity_1` = '',`bloodline_affinity_2` = '',`bloodline_affinity_special` = '' WHERE `uid` = '".$_SESSION['uid']."'") === false)
            {
                //if there was an issue send an error to screen. if this->debug is on add the sql query to the error message.
                if($this->debug)
                    $extra = "UPDATE `users_statistics` SET `bloodline_affinity_1` = '',`bloodline_affinity_2` = '',`bloodline_affinity_special` = '' WHERE `uid` = '".$_SESSION['uid']."'";
                else
                    $extra = "";
                throw new Exception('There was an error trying to update user bloodline affinities'.$extra);
            }
        }
    }

    //function that updates the user's element mastery
        //takes a value to add to the users mastery
        //and takes an number defining which mastery to update
            //1 for primary and 2 for secondary and nothing for both.
    public function updateUserElementMastery($adding, $target = null, $skip_db_update = false)
    {
    //updating this objects data
        $mastery_cap = array( '1' => PHP_INT_MAX, '2' => PHP_INT_MAX, '3' => 160000, '4' => 200000, '5' => 250000 );

        if($target == 1 && $this->elements['element_mastery_1'] == $mastery_cap[ $this->user_rank])
            return;

        if($target == 2 && $this->elements['element_mastery_2'] == $mastery_cap[ $this->user_rank])
            return;

        if($target == null && $this->elements['element_mastery_1'] == $mastery_cap[ $this->user_rank] && $this->elements['element_mastery_2'] == $mastery_cap[ $this->user_rank])
            return;

        if($target == 1 || $target == null)
            if(($this->elements['element_mastery_1'] + $adding) <= $mastery_cap[ $this->user_rank])
            {
                $GLOBALS['Events']->acceptEvent('stats_element_mastery_1', array('new'=>$this->elements['element_mastery_1'] + $adding, 'old'=>$this->elements['element_mastery_1'] ));
                $this->elements['element_mastery_1'] += $adding;
            }
            else
            {
                $GLOBALS['Events']->acceptEvent('stats_element_mastery_1', array('new'=>$mastery_cap[ $this->user_rank], 'old'=>$this->elements['element_mastery_1'] ));
                $this->elements['element_mastery_1'] = $mastery_cap[ $this->user_rank];
            }

        if($target == 2 || $target == null)
            if(($this->elements['element_mastery_2'] + $adding) <= $mastery_cap[ $this->user_rank ])
            {
                $GLOBALS['Events']->acceptEvent('stats_element_mastery_2', array('new'=>$this->elements['element_mastery_2'] + $adding, 'old'=>$this->elements['element_mastery_2'] ));
                $this->elements['element_mastery_2'] += $adding;
            }
            else
            {
                $GLOBALS['Events']->acceptEvent('stats_element_mastery_2', array('new'=>$mastery_cap[ $this->user_rank], 'old'=>$this->elements['element_mastery_2'] ));
                $this->elements['element_mastery_2'] = $mastery_cap[ $this->user_rank];
            }
        
        if($target != 1 && $target != 2 && $target != null)
            throw new Exception("bad user of updateUserelementMastery.");

    //setting special element mastery value
        $this->setelementMasterySpecial();

    //updating cache
        $this->syncToCache();

    //updating database
        if($skip_db_update == false && $adding > 0)
        {
            if ($GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `element_mastery_1` = '".$this->elements['element_mastery_1']."',`element_mastery_2` = '".$this->elements['element_mastery_2']."' WHERE `uid` = '".$_SESSION['uid']."'") === false)
            {
            //if there was an issue send an error to screen. if this->debug is on add the sql query to the error message.
                if($this->debug)
                    $extra = "UPDATE `users_statistics` SET `element_mastery_1` = '".$this->elements['element_mastery_1']."',`element_mastery_2` = '".$this->elements['element_mastery_2']."' WHERE `uid` = '".$_SESSION['uid']."'";
                else
                    $extra = "";

                throw new Exception('There was an error trying to update user mastery values'.$extra);
            }
        }
    }

    //function that sets the element mastery for the special affinity
    //if the user is atleas a jounin then it should be equal to the average of the 2 normal element masteries
    //otherwise it is equal to the primary element mastery
    private function setElementMasterySpecial()
    {
        if( $this->user_rank > 3 && $this->elements['element_mastery_2'] != 0 && $this->elements['element_mastery_2'] != '')
            $this->elements['element_mastery_special'] = floor(($this->elements['element_mastery_1'] + $this->elements['element_mastery_2'])/2);
        else
            $this->elements['element_mastery_special'] = $this->elements['element_mastery_1'];
    }


    //returns the mastery bonus based on what the situation is.
    public function checkMasteryBonus($element, $check, $actionRank ){

        $affinities = $this->getUserElements();
        $masteries = $this->getUserElementMastery();

        // Figure out the active value
        $masteryValue = 0;
        switch( $element ){
            case $affinities[0]:
                $masteryValue = $this->elements['element_mastery_1'];
                break;
            case $affinities[1]:
                $masteryValue = $this->elements['element_mastery_2'];
                break;
            case $affinities[2]:
                $masteryValue = $this->elements['element_mastery_special'];
                break;
            default:
                return false;
                break;
        }

        // Set requirements
        $percentage = 0;
        switch( $actionRank ){
            case 3:
                if( $masteryValue >= 160000 ){ $percentage = 100; }
                elseif( $masteryValue >= 144000 ){ $percentage = 90; }
                elseif( $masteryValue >= 128000 ){ $percentage = 80; }
                elseif( $masteryValue >= 112000 ){ $percentage = 70; }
                elseif( $masteryValue >= 96000 ){ $percentage = 60; }
                elseif( $masteryValue >= 80000 ){ $percentage = 50; }
                elseif( $masteryValue >= 64000 ){ $percentage = 40; }
                elseif( $masteryValue >= 48000 ){ $percentage = 30; }
                elseif( $masteryValue >= 32000 ){ $percentage = 20; }
                elseif( $masteryValue >= 16000 ){ $percentage = 10; }
                break;
            case 4:
                if( $masteryValue >= 200000 ){ $percentage = 100; }
                elseif( $masteryValue >= 180000 ){ $percentage = 90; }
                elseif( $masteryValue >= 160000 ){ $percentage = 80; }
                elseif( $masteryValue >= 140000 ){ $percentage = 70; }
                elseif( $masteryValue >= 120000 ){ $percentage = 60; }
                elseif( $masteryValue >= 100000 ){ $percentage = 50; }
                elseif( $masteryValue >= 80000 ){ $percentage = 40; }
                elseif( $masteryValue >= 60000 ){ $percentage = 30; }
                elseif( $masteryValue >= 40000 ){ $percentage = 20; }
                elseif( $masteryValue >= 20000 ){ $percentage = 10; }
                break;
            case 5:
                if( $masteryValue >= 250000 ){ $percentage = 100; }
                elseif( $masteryValue >= 225000 ){ $percentage = 90; }
                elseif( $masteryValue >= 200000 ){ $percentage = 80; }
                elseif( $masteryValue >= 175000 ){ $percentage = 70; }
                elseif( $masteryValue >= 150000 ){ $percentage = 60; }
                elseif( $masteryValue >= 125000 ){ $percentage = 50; }
                elseif( $masteryValue >= 100000 ){ $percentage = 40; }
                elseif( $masteryValue >= 75000 ){ $percentage = 30; }
                elseif( $masteryValue >= 50000 ){ $percentage = 20; }
                elseif( $masteryValue >= 25000 ){ $percentage = 10; }
                break;
        }

        // Figure out if the user has this element
        switch( $check ){
            case "STACHACOST":
                if( $percentage === 100 ){ return 50; }
                elseif( $percentage >= 80 ){ return 35; }
                elseif( $percentage >= 60 ){ return 20; }
                elseif( $percentage >= 40 ){ return 10; }
                elseif( $percentage >= 20 ){ return 5; }
                break;
            case "SpecialJutsu":
                if( $percentage >= 90 ){ return (45*60); }
                elseif( $percentage >= 60 ){ return (30*60); }
                elseif( $percentage >= 30 ){ return (15*60); }
                break;
            case "BloodlineJutsu":
                if( $percentage >= 80 ){ return (30*60); }
                elseif( $percentage >= 50 ){ return (20*60); }
                elseif( $percentage >= 20 ){ return (10*60); }
                break;
            case "RyoReduction":
                if( $percentage >= 90 ){ return 25; }
                elseif( $percentage >= 70 ){ return 20; }
                elseif( $percentage >= 50 ){ return 15; }
                elseif( $percentage >= 30 ){ return 10; }
                elseif( $percentage >= 10 ){ return 5; }
                break;
            case "WeaponUnlock":
                if( $percentage >= 40 ){ return 1; }
                break;
            case "MaxUses":
                if( $percentage >= 90 ){ return 0; }
                elseif( $percentage >= 70 ){ return 1; }
                elseif( $percentage >= 40 ){ return 2; }
                else{ return 3; }
                break;
        }
        return false;
    }






















    //////////////////////////////////////////////////////////////////////////////////////////////////////
    //static element data and functions

    public static function checkMasteryBonusAi( $userData, $element, $check, $actionRank ){

        // Figure out the active value
        $masteryValue = 0;
        switch( $element ){
            case $userData['element_affinity_1']:
                $masteryValue = $userData['element_mastery_1'];
                break;
            case $userData['element_affinity_2']:
                $masteryValue = $userData['element_mastery_1'];
                break;
            case $userData['element_affinity_special']:
                $masteryValue = $userData['element_mastery_1'];
                break;
            default:
                return false;
                break;
        }
    }

    public static function createRandomUserAffinities() {

        // Convenience variable for the elements
        $elements = Elements::$mainElements;

        // Create the primary element
        $primary = $elements[ random_int(0, count($elements)-1 ) ];

        // Create the second element
        $secondary = $elements[ random_int(0, count($elements)-1 ) ];
        while( $primary == $secondary ){
            $secondary = $elements[ random_int(0, count($elements)-1 ) ];
        }

        // Return element affinities as string
        return array($primary,$secondary);
    }

    //returns random element
    public static function getRandomElement(){
        return Elements::$mainElements[ random_int(0, count(Elements::$mainElements)-1) ];
    }

    // Returns the element and its weakness
    public static function elementAndWeakness( $element ){
        return array( $element, Elements::$strengthsWeaknesses[ strtolower($element) ]['weak'] );
    }

    public static $mainElements = array( "fire", "wind", "lightning", "earth", "water" );

    public static $specialElements = array(
        "scorching"     => array("wind", "fire"),
        "tempest"       => array("lightning", "wind"),
        "magnetism"     => array("earth", "lightning"),
        "wood"          => array("water", "earth"),
        "steam"         => array("water", "fire"),
        "light"         => array("fire", "lightning"),
        "dust"          => array("earth", "wind"),
        "storm"         => array("water", "lightning"),
        "lava"          => array("fire", "earth"),
        "ice"           => array("water", "wind"),

        "fire"          => array("fire", "fire"),
        "wind"          => array("wind", "wind"),
        "lightning"     => array("lightning", "lightning"),
        "earth"         => array("earth", "earth"),
        "water"         => array("water", "water")
    );

    public static $strengthsWeaknesses = array(
        "fire"      => array("strong" => "wind", "weak" => "water"),
        "wind"      => array("strong" => "lightning", "weak" => "fire"),
        "lightning" => array("strong" => "earth", "weak" => "wind"),
        "earth"     => array("strong" => "water", "weak" => "lightning"),
        "water"     => array("strong" => "fire", "weak" => "earth"),

        "scorching" => array("strong" => "tempest", "weak" => "steam"),
        "tempest"   => array("strong" => "magnetism", "weak" => "scorching"),
        "magnetism" => array("strong" => "wood", "weak" => "tempest"),
        "wood"      => array("strong" => "steam", "weak" => "magnetism"),
        "steam"     => array("strong" => "scorching", "weak" => "wood"),
        "light"     => array("strong" => "dust", "weak" => "ice"),
        "dust"      => array("strong" => "storm", "weak" => "light"),
        "storm"     => array("strong" => "lava", "weak" => "dust"),
        "lava"      => array("strong" => "ice", "weak" => "storm"),
        "ice"       => array("strong" => "light", "weak" => "lava")
    );
}