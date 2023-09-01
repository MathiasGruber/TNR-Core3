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
 *Class: PerformanceTesting
 *  this class is used to help with testing how much time things take.
 */

class PerformanceTesting
{
    public $initTime;

    public $timeStamps;

    public function __construct(){
        $this->initTime = $this->mt();
        $this->timeStamps = [];
    }

    //shorthand for micro time
    public function mt(){
        return microtime(true);
    }

    public function recordTimeStamp($label, $category = 'default', $extra = false)
    {
        if(isset($GLOBALS['database']->queriesRun))
            $Q = $GLOBALS['database']->queriesRun;
        else
            $Q = 0;

        //check to see if category has been started
        if(!isset($this->timeStamps[$category]))
        {
            $this->timeStamps[$category] = [ 'init'=>['Ttotal'=>$this->initTime,'Qtotal'=>$Q,'extra'=>$extra], $label=>['Ttotal'=>$this->mt(),'Qtotal'=>$Q,'extra'=>$extra] ];
        }
        else if(!isset($this->timeStamps[$category][$label]))
        {
            $this->timeStamps[$category][$label] = ['Ttotal'=>$this->mt(),'Qtotal'=>$Q,'extra'=>$extra];
        }
        else
        {
            $mod = 2;
            while(isset($this->timeStamps[$category][$label.'#'.$mod]))
                $mod++;

            $this->timeStamps[$category][$label.'#'.$mod] = ['Ttotal'=>$this->mt(),'Qtotal'=>$Q,'extra'=>$extra];
        }
    }

    public function printCategory($category, $screen)
    {
        $time_stamps = $this->timeStamps[$category];
        
        $Tprevious=0;
        $Qprevious=0;
        $init = 0;

        $TDsum = 0;
        $QDsum = 0;
        foreach($time_stamps as $label => $data)
        {
            $T = $data['Ttotal'];
            $Q = $data['Qtotal'];

            if($label == 'init')
            {
                $init = $T;
                $time_stamps[$label] = ['Tdiff'=>0,'Qdiff'=>0,'Ttotal'=>0,'Qtotal'=>0,'extra'=>$data['extra']];
            }
            else
            {
                $T -= $init;
                $T *= pow(10,5);
                $TD = $T-$Tprevious;
                $QD = $Q-$Qprevious;

                if(substr(explode('#',$label)[0], -1) !== '+')
                {
                    $TDsum += $TD;
                    $QDsum += $QD;
                }

                $time_stamps[$label] = ['Tdiff'=>$TD,'Qdiff'=>$QD,'Ttotal'=>$T,'Qtotal'=>$Q,'extra'=>$data['extra']];
                $Tprevious = $T;
                $Qprevious = $Q;
            }
        }

        error_log('');
        error_log($category." start ");
        error_log('TDsum: '.$TDsum);
        error_log('QDsum: '.$QDsum);
        error_log(print_r($time_stamps,true));
        error_log($category." end ");
        error_log('');

        if($screen)
        {
            var_dump('<br><pre>');
            var_dump($category." start ");
            var_dump('TDsum: '.$TDsum);
            var_dump('QDsum: '.$QDsum);
            var_dump($time_stamps);
            var_dump($category." end ");
            var_dump('</pre><br>');
        }
    }

    public function printAll($screen = true)
    {
        foreach($this->timeStamps as $category => $data)
        {
            $this->printCategory($category, $screen);
        }
    }
}