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
 *Class: QuestContainer
 *  this class is just an object used to hold quest data in a nice package
 *
 */

class QuestContainer
{
    //different status options can be listed here
    public static $known = 0; //forgotten is done just by removing the record in users_quests
    public static $active = 1;
    public static $completed = 2;
    public static $closed = 3;
    public static $hard_failure = 4;
    public static $e = 5;
    public static $f = 6;
    public static $g = 7;
    public static $h = 8;
    public static $i = 9;
    public static $j = 10;
    public static $k = 11;

    public static $statuses = array(0=>'known',1=>'active',2=>'completed',
                                    3=>'closed',4=>'dead');



    public $uid;
    public $qid;
    public $status;
    public $track;
    public $attempts;
    public $failed;
    public $turned_in;
    public $timestamp_learned;
    public $timestamp_updated;
    public $timestamp_turned_in;
    public $dialog_chain;
    public $data;
    public $name;
    public $description;
    public $hide_description_until;
    public $image;
    public $category;
    public $category_skin;
    public $mission_chain;
    public $level;

    public $hide_starting_requirements;
    public $starting_requirements;

    public $hide_starting_requirements_post_failure;
    public $starting_requirements_post_failure;

    public $hide_completion_requirements;
    public $completion_requirements;

    public $hide_completion_requirements_post_failure;
    public $completion_requirements_post_failure;

    public $hide_failure_requirements;
    public $failure_requirements;

    public $hide_failure_requirements_post_failure;
    public $failure_requirements_post_failure;

    public $hide_turn_in_requirements;
    public $turn_in_requirements;

    public $hide_turn_in_requirements_post_failure;
    public $turn_in_requirements_post_failure;

    public $hide_rewards;
    public $rewards;
    public $rewards_write_up;

    public $hide_rewards_post_failure;
    public $rewards_post_failure;
    public $rewards_post_failure_write_up;

    public $hide_punishments;
    public $punishments;
    public $punishments_write_up;

    public $hide_punishments_post_failure;
    public $punishments_post_failure;
    public $punishments_post_failure_write_up;

    public $repeatable;
    public $time_gap_requirement;
    public $time_gap_requirement_text;
    public $failable;
    public $chances;
    public $hard_fail;
    public $forgettable;
    public $dialog_history;

    public $message_learn_alert;
    public $message_learn_quiet;
    public $message_fail_alert;
    public $message_fail_quiet;
    public $message_fail_try_again_alert;
    public $message_fail_try_again_quiet;
    public $message_fail_post_failure_alert;
    public $message_fail_post_failure_quiet;
    public $message_complete_alert;
    public $message_complete_quiet;
    public $message_complete_post_failure_alert;
    public $message_complete_post_failure_quiet;

    public $dialog_start;
    public $dialog_start_post_failure;
    public $dialog_forget;
    public $dialog_forget_post_failure;
    public $dialog_fail;
    public $dialog_fail_post_failure;
    public $dialog_quit;
    public $dialog_quit_post_failure;
    public $dialog_complete;
    public $dialog_complete_post_failure;
    public $dialog_turn_in;
    public $dialog_turn_in_post_failure;

    public $update = false;

    function __construct($data)
    {
        //basic quest data
        $this->qid                      = $data['qid'];
        $this->name                     = $data['name'];
        $this->description              = $data['description'];
        $this->hide_description_until   = $data['hide_description_until'];
        $this->image                    = $data['image'];
        $this->category                 = $data['category'];
        $this->category_skin            = $data['category_skin'];
        $this->level                    = $data['level'];
        $this->mission_chain            = $data['mission_chain'];
        $this->repeatable               = $data['repeatable'];
        $this->time_gap_requirement     = $data['time_gap_requirement'];
        $this->failable                 = $data['failable'];
        $this->chances                  = $data['chances'];
        $this->hard_fail                = $data['hard_fail'];
        $this->forgettable              = $data['forgettable'];
        $this->dialog_history           = $data['dialog_history'];

        //requirements
        $this->starting_requirements                    = $this->readInformation($data['starting_requirements'], true);
        $this->starting_requirements_post_failure       = $this->readInformation($data['starting_requirements_post_failure'], true);
        $this->completion_requirements                  = $this->readInformation($data['completion_requirements'], true);
        $this->completion_requirements_post_failure     = $this->readInformation($data['completion_requirements_post_failure'], true);
        $this->failure_requirements                     = $this->readInformation($data['failure_requirements'], true);
        $this->failure_requirements_post_failure        = $this->readInformation($data['failure_requirements_post_failure'], true);
        $this->turn_in_requirements                     = $this->readInformation($data['turn_in_requirements'], true);
        $this->turn_in_requirements_post_failure        = $this->readInformation($data['turn_in_requirements_post_failure'], true);

        //rewards and punishments
        $this->rewards                      = $this->readInformation($data['rewards']);
        $this->rewards_post_failure         = $this->readInformation($data['rewards_post_failure']);
        $this->punishments                  = $this->readInformation($data['punishments']);
        $this->punishments_post_failure     = $this->readInformation($data['punishments_post_failure']);

        //hides
        $this->hide_starting_requirements                   = $data['hide_starting_requirements'];
        $this->hide_starting_requirements_post_failure      = $data['hide_starting_requirements_post_failure'];
        $this->hide_completion_requirements                 = $data['hide_completion_requirements'];
        $this->hide_completion_requirements_post_failure    = $data['hide_completion_requirements_post_failure'];
        $this->hide_failure_requirements                    = $data['hide_failure_requirements'];
        $this->hide_failure_requirements_post_failure       = $data['hide_failure_requirements_post_failure'];
        $this->hide_turn_in_requirements                    = $data['hide_turn_in_requirements'];
        $this->hide_turn_in_requirements_post_failure       = $data['hide_turn_in_requirements_post_failure'];
        $this->hide_rewards                                 = $data['hide_rewards'];
        $this->hide_rewards_post_failure                    = $data['hide_rewards_post_failure'];
        $this->hide_punishments                             = $data['hide_punishments'];
        $this->hide_punishments_post_failure                = $data['hide_punishments_post_failure'];
        
        //messages
        $this->message_learn_alert                  = $this->readInformation($data['message_learn_alert']);
        $this->message_learn_quiet                  = $this->readInformation($data['message_learn_quiet']);
        $this->message_fail_alert                   = $this->readInformation($data['message_fail_alert']);
        $this->message_fail_quiet                   = $this->readInformation($data['message_fail_quiet']);
        $this->message_fail_try_again_alert         = $this->readInformation($data['message_fail_try_again_alert']);
        $this->message_fail_try_again_quiet         = $this->readInformation($data['message_fail_try_again_quiet']);
        $this->message_fail_post_failure_alert      = $this->readInformation($data['message_fail_post_failure_alert']);
        $this->message_fail_post_failure_quiet      = $this->readInformation($data['message_fail_post_failure_quiet']);
        $this->message_complete_alert               = $this->readInformation($data['message_complete_alert']);
        $this->message_complete_quiet               = $this->readInformation($data['message_complete_quiet']);
        $this->message_complete_post_failure_alert  = $this->readInformation($data['message_complete_post_failure_alert']);
        $this->message_complete_post_failure_quiet  = $this->readInformation($data['message_complete_post_failure_quiet']);

        //flags for processing quest specific events.
        $this->fail = false;
        $this->complete = false;
        $this->update = false;

        //pull write_up out of requirements and rewards/punishments
        $fields = array(
                        'rewards', 'rewards_post_failure', 
                        'punishments', 'punishments_post_failure'); 

        foreach($fields as $field)
        {
            if(isset($this->{$field}['write_up']))
            {
                $this->{$field.'_write_up'} = $this->{$field}['write_up'];
                foreach($this->{$field}['write_up'] as $key => $value)
                {
                    $this->{$field.'_write_up'}[$key] = $value;
                }
                unset($this->{$field}['write_up']);
            }
            else
            {
                $this->{$field.'_field'} = 'unknown';
            }
        }

        //user specific information
        if(isset($data['uid']))
        {
            $this->uid                      = $data['uid'];
            $this->attempts                 = $data['attempts'];
            $this->failed                   = $data['failed'];
            $this->turned_in                = $data['turned_in'];
            $this->timestamp_learned        = $data['timestamp_learned'];
            $this->timestamp_updated        = $data['timestamp_updated'];
            $this->timestamp_turned_in      = $data['timestamp_turned_in'];
            $this->status                   = $data['status'];
            $this->track                    = $data['track'];

            if($this->hide_description_until > $this->status)
                $this->description = 'N/A';

            $this->dialog_chain            = array();
            foreach(explode('|',$data['dialog_chain']) as $link)
            {
                if($link != '')
                {
                    $temp_link = array();
                    foreach(explode(',',$link) as $piece)
                    {
                        if($piece != '')
                        {
                            $temp_piece = explode(':',$piece);
                            if(count($temp_piece) == 2)
                                $temp_link[$temp_piece[0]] = $temp_piece[1];
                        }
                    }

                    $this->dialog_chain[] = $temp_link;
                }
            }

            if($data['data'] != '')
                $this->data                = unserialize(gzinflate(str_replace("backslash","\\",str_replace("hashtag","#",$data['data']))));
            else
                $this->data = '';
        }
        
        
        //dialogs
        if($data['did'] != 0 && $data['did'] != '')
        {
            $this->dialog_start                 = $this->readInformation($data['dialog_start']);
            $this->dialog_start_post_failure    = $this->readInformation($data['dialog_start_post_failure']);
            $this->dialog_forget                = $this->readInformation($data['dialog_forget']);
            $this->dialog_forget_post_failure   = $this->readInformation($data['dialog_forget_post_failure']);
            $this->dialog_fail                  = $this->readInformation($data['dialog_fail']);
            $this->dialog_fail_post_failure     = $this->readInformation($data['dialog_fail_post_failure']);
            $this->dialog_quit                  = $this->readInformation($data['dialog_quit']);
            $this->dialog_quit_post_failure     = $this->readInformation($data['dialog_quit_post_failure']);
            $this->dialog_complete              = $this->readInformation($data['dialog_complete']);
            $this->dialog_complete_post_failure = $this->readInformation($data['dialog_complete_post_failure']);
            $this->dialog_turn_in               = $this->readInformation($data['dialog_turn_in']);
            $this->dialog_turn_in_post_failure  = $this->readInformation($data['dialog_turn_in_post_failure']);
        }

        

        //updating time gap text
        if( $this->repeatable && $this->status == 0 && $this->timestamp_turned_in != 0 && $this->time_gap_requirement != '' && strpos(rtrim($this->time_gap_requirement, ';'), ';') === false && strpos($this->time_gap_requirement, 'eval') === false && !eval(str_replace('time()', ' ( time() - 18000 ) ',str_replace(" = "," == ","return ".str_replace('"value"', str_replace('\'','\\\'',$this->timestamp_turned_in), str_replace('\'value\'', str_replace('\'','\\\'',$this->timestamp_turned_in - 18000), $this->time_gap_requirement)).";"))))
        {
            $this->time_gap_requirement_text = $this->findTimeTillRepeat();
        }
        else
        {
            $this->time_gap_requirement_text = '';
        }
    }

    function findTimeTillRepeat()
    {
        $times = array(
            
            array(  'seconds'   =>  0,
                    'text'      =>  '+1s'),
            
                array(  'seconds'   =>  15,
                        'text'      =>  '+15s'),
            
                    array(  'seconds'   =>  30,
                            'text'      =>  '+30s'),
            
                        array(  'seconds'   =>  45,
                                'text'      =>  '+45s'),
            
            array(  'seconds'   =>  60,
                    'text'      =>  '+1m'),

                array(  'seconds'   =>  60*3,
                        'text'      =>  '+3m'),

                    array(  'seconds'   =>  60*5,
                            'text'      =>  '+5m'),

                        array(  'seconds'   =>  60*10,
                                'text'      =>  '+10m'),

                            array(  'seconds'   =>  60*20,
                                    'text'      =>  '+20m'),

                                array(  'seconds'   =>  60*30,
                                        'text'      =>  '+30m'),

                                    array(  'seconds'   =>  60*40,
                                            'text'      =>  '+40m'),

                                        array(  'seconds'   =>  60*50,
                                                'text'      =>  '+50m'),

            array(  'seconds'   =>  60*60,
                    'text'      =>  '+1h'),

                    array(  'seconds'   =>  60*60*2,
                            'text'      =>  '+2h'),

                        array(  'seconds'   =>  60*60*3,
                                'text'      =>  '+3h'),

                            array(  'seconds'   =>  60*60*4,
                                    'text'      =>  '+4h'),

                                array(  'seconds'   =>  60*60*6,
                                        'text'      =>  '+6h'),

                                    array(  'seconds'   =>  60*60*7,
                                            'text'      =>  '+7h'),

                                        array(  'seconds'   =>  60*60*8,
                                                'text'      =>  '+8h'),

                                            array(  'seconds'   =>  60*60*9,
                                                    'text'      =>  '+9h'),

                                                array(  'seconds'   =>  60*60*10,
                                                        'text'      =>  '+10h'),

                                                    array(  'seconds'   =>  60*60*11,
                                                            'text'      =>  '+11h'),

                                                        array(  'seconds'   =>  60*60*12,
                                                                'text'      =>  '+12h'),

                                                            array(  'seconds'   =>  60*60*15,
                                                                    'text'      =>  '+15h'),

                                                                array(  'seconds'   =>  60*60*18,
                                                                        'text'      =>  '+18h'),

            array(  'seconds'   =>  60*60*24,
                    'text'      =>  '+1d'),

                array(  'seconds'   =>  60*60*24*2,
                        'text'      =>  '+2d'),

                    array(  'seconds'   =>  60*60*24*3,
                            'text'      =>  '+3d'),

                        array(  'seconds'   =>  60*60*24*4,
                                'text'      =>  '+4d'),

                            array(  'seconds'   =>  60*60*24*5,
                                    'text'      =>  '+5d'),

                                array(  'seconds'   =>  60*60*24*6,
                                        'text'      =>  '+6d'),

            array(  'seconds'   =>  60*60*24*7,
                    'text'      =>  '+1w'),

                array(  'seconds'   =>  60*60*24*7*2,
                        'text'      =>  '+2w'),

                    array(  'seconds'   =>  60*60*24*7*4,
                            'text'      =>  '+4w'),

                        array(  'seconds'   =>  60*60*24*7*12,
                                'text'      =>  '+12w'),

                            array(  'seconds'   =>  60*60*24*7*24,
                                    'text'      =>  '+24w'),

                                array(  'seconds'   =>  60*60*24*7*52,
                                        'text'      =>  '+52w')
        );

        
        $last_good_key = count($times) - 1;
        $last_bad_key = 0;
        $first_run = true;

        while( abs($last_good_key - $last_bad_key) > 1 )
        {
            //finding the time we want to check
            if($first_run)
            {
                $current_key = 12; //this is the 9th spot in the times array and marks the 1 hour spot
                $first_run = false;
            }
            else
            {
                $current_key = floor( ($last_good_key+$last_bad_key)/2 );
            }

            //prepare the time_gap_requirement for check
            $stamp = str_replace('\'','\\\'',$this->timestamp_turned_in - 18000);
            $time_gap_requirement = str_replace('"value"', $stamp, str_replace('\'value\'', $stamp, $this->time_gap_requirement)); //update value
            $time_gap_requirement = str_replace('time()', ' ( time() + '.$times[$current_key]['seconds'].' - 18000 ) ', $time_gap_requirement); //update time
            
            //check the time_gap_requirement
            if( eval(str_replace(" = "," == ","return ".$time_gap_requirement.";") ) )
                $last_good_key = $current_key;
            else
                $last_bad_key = $current_key;
        }

        return $times[$last_bad_key]['text'];
    }

	function readInformation($json, $check_for_joins = false)
	{
		try
		{
            $result = json_decode($json, true);

            if(is_null($result) && strlen($json) > 6)
                throw new Exception('JSON returned null!');

            if($check_for_joins && is_array($result))
            {
                foreach($result as $join_key => $joined_items)
                {
                    if( substr( $join_key, 0, 4 ) === "join" )
                    {
                        $sticky_join = false;
                        if( isset($joined_items['sticky']) && $joined_items['sticky'] == true)
                        {
                            $sticky_join = true;
                            unset($result[$join_key]['sticky']);
                            unset($joined_items['sticky']);
                        }

                        $or_join = false;
                        if( isset($joined_items['or']) && $joined_items['or'] == true)
                        {
                            $or_join = true;
                            unset($result[$join_key]['or']);
                            unset($joined_items['or']);
                        }

                        $join_alert = false;
                        if( isset($joined_items['alert']) && $joined_items['alert'] == true)
                        {
                            $join_alert = $joined_items['alert'];
                            unset($result[$join_key]['alert']);
                            unset($joined_items['alert']);
                        }

                        foreach($joined_items as $joined_item_key => $joined_item_value)
                        {
                            $result[$joined_item_key] = $joined_item_value;
                            $result[$joined_item_key]['joined'] = $join_key;

                            if($sticky_join)
                                $result[$joined_item_key]['sticky'] = $join_key;

                            if($or_join)
                                $result[$joined_item_key]['or'] = true;

                            if($join_alert)
                            {
                                $result[$joined_item_key]['join_alert'] = $join_alert;
                                $result[$joined_item_key]['join_alert_sent'] = false;
                            }
                        }

                        unset($result[$join_key]);
                    }
                }

            }

			return $result;
		}
		catch (Exception $e)
		{
			echo'<pre>';
			var_dump(array('bad json: ',$json));
			error_log('bad json: '.$json);
			echo'</pre>';
		}
	}

	/*
    //recursive function for parsing a dialog
    // syntax for dialog
    //  keygoeshere>(contentsgohere)<
    //  contents can be the exact same as above
    //  no commas for multiple pieces of content aka...
    //    aaa>( 111>(wasdf)< 222>(wasdf)< )<
    function readInformation($part)
    {
		if(debug_backtrace()[1]['function'] != 'readInformation') //if this is the first time this has been called
            $part = preg_replace('/(\/{2,}.*)/', '', $part); //trim out comments

        $array = array();
	
	    $part = trim($part); //clearning this part of leading and tailing white space
	    
	    $open_count = 0; //count for the number of opens ">("
	    $open_start = 0; //location of the first open
	    $close_count = 0; //count for the number of closes ")<"
	    $close_start = 0; //location of the last close
	    $base_for_new_array = 0; //marks where the next array starts

	    for($i = 0; $i < strlen($part); $i++) //for each charecter in this part
	    {
	    	if($part[$i] == '>' && isset($part[$i+1]) && $part[$i+1] == '(') //if this is the start of an open ">("
	    	{
	    		$open_count++; //record that an open was found
	    		$i+=1; //move the loop forward an extra step to account for the 2nd piece of the open
	    		
	    		if($open_start == 0) //if this is the first open found...
	    			$open_start = $i+2; //...mark where it started (dont ask me why +2 is needed was added during debugging to make it work as it should)
	    	}

	    	else if($part[$i] == ')' && isset($part[$i+1]) && $part[$i+1] == '<') //if this is the start of an close ")<"
	    	{
	    		$close_count++; //record that a close was found
	    		$close_start = $i; //mark this as the staring point of the close
	    		$i+=1; //move the loop forward an extra step to account for the 2nd piece of the close
	    	}
	    	
	    	if($open_count == $close_count && $open_count != 0 && $open_start != 0) //if there is an equal number of opens and closes(not including zero)
	    	{
                //first time for this key
                if(!isset($array[trim(substr($part,$base_for_new_array,$open_start-$base_for_new_array-3))]) && !isset($array[trim(substr($part,$base_for_new_array,$open_start-$base_for_new_array-3)).'~0']))
                {
                    $array[trim(substr($part,$base_for_new_array,$open_start-$base_for_new_array-3))] = $this->readInformation(trim(substr($part,$open_start-1,$close_start-$open_start+1)));
                }

                //not the first time for this key
                else
                {
                    //if this is the 2nd time for this key clean things up a bit
                    if(isset($array[trim(substr($part,$base_for_new_array,$open_start-$base_for_new_array-3))]))
                    {
                        $array[trim(substr($part,$base_for_new_array,$open_start-$base_for_new_array-3)).'~0'] = $array[trim(substr($part,$base_for_new_array,$open_start-$base_for_new_array-3))];
                        unset($array[trim(substr($part,$base_for_new_array,$open_start-$base_for_new_array-3))]);
                    }

                    //find the first key~# not in use
                    for($not_i = 0; isset($array[trim(substr($part,$base_for_new_array,$open_start-$base_for_new_array-3)).'~'.$not_i]) && $not_i < 100; $not_i++);

                    //set it
                    $array[trim(substr($part,$base_for_new_array,$open_start-$base_for_new_array-3)).'~'.$not_i] = $this->readInformation(trim(substr($part,$open_start-1,$close_start-$open_start+1)));
                }
	    		
                //reset open start to 0 so if another array is found it can use the new start location
	    		$open_start = 0;
	    		
                //set the base for a new array to this location
	    		$base_for_new_array = $i+1;
	    	}
	    }
	    
        //if no array was found return the full part
	    if(count($array) == 0)
	    	return $part;
	    
        //otherwise return the found and processed array
	    return $array;
    }
	*/
}