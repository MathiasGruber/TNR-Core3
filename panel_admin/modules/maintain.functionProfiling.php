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

class profiler {

    public function profiler() {
        if (!isset($_GET['act'])) {
            $this->main_screen();   
        } 
        elseif ($_GET['act'] == 'doTest') {
            $this->doTest();
        }               
    }

    private function main_screen() {        
        $GLOBALS['page']->Message( "Run Profiling on Functions specified in code. (Only for coders testing things out)" , 'Profile System', 'id='.$_GET['id']."&act=doTest", "Test now");       
    }
    
    private function doTest(){
        
        // How many times to run test
        $testTimes = 1000;
        
        // Output var
        $output = "";
        
        // Run tests
        $time1 = $this->runTest($testTimes, "function1");
        $time2 = $this->runTest($testTimes, "function2");
        
        // Add to output
        $output = "Function 1 finished in: ".$time1." seconds<br>";
        $output .= "Function 2 finished in: ".$time2." seconds";
        
        // Show result
        $GLOBALS['page']->Message( $output , 'Profile System', 'id='.$_GET['id']);       
    }   
    
    private function runTest( $testTimes, $runFunction ){
        $i = 0;
        $mtimeStart = explode(" ", microtime());
        $startTime = $mtimeStart[1] + $mtimeStart[0];
        while( $i < $testTimes ){
            call_user_func(array('profiler', $runFunction));
            $i++;
        }
        
        $mtimeEnd = explode(" ", microtime());
        $endtime = $mtimeEnd[1] + $mtimeEnd[0];
        return round(($endtime - $startTime), 4);
    }
    
    // Run file_exist on known file
    private static function function2(){ 
        file_exists("./content/academy.inc.php");
    }
    
    // Checks for a non-existand user picture
    private static function function1(){ 
        functions::getUserImage("/avatars/", random_int(1,10000));
    }
}

new profiler();