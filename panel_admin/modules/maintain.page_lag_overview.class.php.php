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

// Test class
class logOverview {

    // Constructor
    public function __construct() {
        
        // Vars
        $this->slowPageTime = 1;
        $this->slowPageDays = 1;
        
        // For defining a lag period
        $this->lagMinLoads = 20;
        $this->noMoreLagSecs = 10;
        
        // Gather data in intervals
        $this->intervalMin = 10;
        
        // Cron job entries
        $this->cronJobs = array("5 Min Cron","1 Hour Cron","1 Day Cron","Optimize Cron");
        
        // Overwrite default settings
        if( isset($_POST['submit']) ){
            if( isset($_POST['days']) && $_POST['days'] !== "" ){
                $this->slowPageDays = $_POST['days'] / 24;
            }
            if( isset($_POST['cluster']) && $_POST['cluster'] !== "" ){
                $this->intervalMin = $_POST['cluster'] / 60;
            }
        }
        
        // Try it all
        try{
            
            // Get the log entries
            $this->logs = $this->retrieveSlowPages();
            
            // Check entries
            $this->totalEntries = 0;
            if( $this->logs !== "0 rows" ){
                $this->totalEntries = count($this->logs);
            }
            
            // Retrieve data to plot
            $this->loadTimesData();
            
            // Analyze major lag times
            $this->analyzeLagTimes();

            // Load the template wrapper
            $GLOBALS['template']->assign('contentLoad', './panel_admin/templates/page_lag/main.tpl');
            
        } catch (Exception $ex) {
            $GLOBALS['page']->Message($ex->getMessage(), 'New Battle Formula System', 'id=' . $_GET['id'] );
        }
    }
    
    // Load load-times for the plot
    private function loadTimesData(){
        
        // Add log data        
        if( $this->logs !== "0 rows" ){
            foreach( $this->logs as $entry ){

                // Get time
                $time = floor( ($entry['time'] - $this->logs[0]['time']) / 60 / $this->intervalMin );
                
                if( !in_array($entry['type'], $this->cronJobs) ){
                    $this->addHistogramData( "loadTimes", "Page Load Times > ".$this->slowPageTime." seconds", $time );
                    $this->addRoundData("LoadTimes", "Page Loads > ".$this->slowPageTime." seconds", $time );
                }
                else{
                    $this->addHistogramData( "loadTimes", $entry['type'] , $time );
                    $this->addRoundData("LoadTimes", $entry['type'], $time );
                }                
            }
        }
        
        
        // Send plot to be shown
        /* 
        $GLOBALS['template']->append('calcDebug', $this->getGoogleChart().$this->getDistributionPlotHtml( 
            "loadTimes","loadTimes","Time [min]","Counts","PageViews with loadtime > ".$this->slowPageTime." seconds"
        ) ); */
        
        $GLOBALS['template']->append('calcDebug', $this->getRoundPlotHtml( "LoadTimes" ) );
    }
    
    private function retrieveSlowPages(){
        $GLOBALS['database']->execute_query("DELETE FROM `log_tempObjectLogger` WHERE `time` < ".( time() - 3*24*3600 )." ");
        return $GLOBALS['database']->fetch_data("
            SELECT *
            FROM `log_tempObjectLogger`           
            WHERE 
                `name` = 'PageLoadTime' AND 
                `objectSize` > '".$this->slowPageTime."' AND
                `time` > ".( time() - $this->slowPageDays*24*3600 )." 
            ORDER BY `time` ASC");        
    }
        
    private function analyzeLagTimes(){
        
        // Log times to plot
        $logTimes = array();
        
        // Go through the log entries
        $startTime = $prevTime = $sequence = 0; 
        if( $this->logs !== "0 rows" ){
            foreach( $this->logs as $entry ){
                if( $prevTime != 0 ){

                    // Set start time every time sequence is 0
                    if( $sequence == 0 && $startTime == 0){
                        $startTime = $entry['time'];
                    }

                    // Check sequence
                    if( $prevTime + $this->noMoreLagSecs >= $entry['time'] ){

                        $sequence += 1;
                    }
                    else{
                        // If we have a lag period, report it, otherwise just reset
                        if( $sequence > $this->lagMinLoads ){
                            $logTimes[] = array(
                                "id" => count($logTimes),
                                "date" => ($startTime+$prevTime) / 2,
                                "period" => $prevTime - $startTime,
                                "startTime" => $startTime,
                                "endTime" => $entry['time'],
                                "pageLoads" => $sequence
                            );
                        }
                        $startTime = 0;
                        $sequence = 0;
                    }
                }
                $prevTime = $entry['time'];            
            }
            
             // Add time since last
            $i = 0;
            while( $i < count($logTimes) ){
                if( $i > 0 ){
                    $logTimes[$i]['sinceLast'] = round(($logTimes[$i]['date'] - $logTimes[$i-1]['date']) / 60,2);
                }
                else{
                    $logTimes[$i]['sinceLast'] = "-";
                }
                $i++;
            }

            // Estimated next lag
            $avgTime = 0;
            if( !empty($logTimes) ){
                foreach($logTimes as $entry){
                    if( $entry['sinceLast'] !== "-" ){
                        $avgTime += $entry['sinceLast'];
                    }
                }
                $avgTime /= count($logTimes);
            }
            $nextTime = "Unknown";
            if(  count($logTimes) > 0 ){
                $nextTime = ( ($logTimes[ count($logTimes) -1 ]['endTime'] + $avgTime * 60) - time() ) / 60;
            }

            // Present table
            tableParser::show_list(
                'lagIncidents', 'Identified Lag Periods. Current Time: '.date("d-m-y, h:m:s")." - Next Estimated Lag: ".round($nextTime,2)." min - Average Lag: ".round($avgTime,2), $logTimes, 
                array(
                    'id' => "Lag ID",
                    'date' => "Lag Time",
                    'startTime' => "Start Time",
                    'endTime' => "End Time",
                    'period' => "Period [s]",
                    'pageLoads' => "PageLoads in Lag",
                    'sinceLast' => "Since last lag [min]"
                ), array(), false, false
            );
        }
    }
    
    // Function for adding data to be plotted
    // Types: roundData
    private function addHistogramData( $name, $set, $damage ){
        
        // Check if var exists
        if( !isset( $this->{$name} ) ){
            $this->{$name} = array( $set => array() );
        }
        
        // Check if set exists
        if( !isset( $this->{$name}[ $set ] ) ){
            $this->{$name}[$set] = array();
        }
        
        // Add this damage to data
        $this->{$name}[$set][] = $damage;
        
    }
    
    // Get damage distribution plot html
    private function getDistributionPlotHtml( 
        $dataName , 
        $key = "dam", 
        $hAxis = "%", 
        $vAxis = "Counts",
        $title = "Damage in % of opponent max health."
    ){
        if( isset( $this->{$dataName} ) ){
            
            // Set the data columns
            $dataString = "['".implode("','", array_keys($this->{$dataName}))."']";
            
            // Order the data properly
            $foundData = true;
            $i = 0;
            while( $foundData == true ){
                
                // Go through each set
                $roundValues = array();
                $foundData = false;
                foreach( $this->{$dataName} as $set => $data ){
                    if( isset($data[$i]) ){
                        $roundValues[] = $data[$i];
                        $foundData = true;
                    }
                    else{
                        $roundValues[] = "null";
                    }
                }
                
                // Start data string
                $dataString .= ", [ ".implode(",", $roundValues )." ]";
                
                // Increment
                $i++;                
            }

            return "<script type='text/javascript'>
                      google.setOnLoadCallback(draw".$key."Chart);
                      function draw".$key."Chart() {
                        var ".$key."Data = google.visualization.arrayToDataTable([
                            ".$dataString."
                        ]);

                        var ".$key."Options = {
                          title: '".$title."',
                          legend: { position: 'bottom' },
                          hAxis: {title: '".$hAxis."'},
                          vAxis: {title: '".$vAxis."'},
                          bar: {groupWidth: '70%' }
                        };

                        var ".$key."Chart = new google.visualization.Histogram(document.getElementById('".$key."ChartDiv'));
                        ".$key."Chart.draw(".$key."Data, ".$key."Options);
                      }
                    </script>
                    <div id='".$key."ChartDiv' style='width: 1400px; height: 400px;'></div>";
        }
        else{
            return "No Data";
        }
    }
    
    // Load google chart code
    private function getGoogleChart(  ){
        return "<script type='text/javascript'>
          google.load('visualization', '1.1', {packages:['bar','corechart']});
          google.setOnLoadCallback(drawChart);</script>";
    }
    
    // Function for adding data to be plotted
    // Types: roundData
    private function addRoundData( $name, $set, $rounds ){
        
        // Check if var exists
        if( !isset( $this->{$name} ) ){
            $this->{$name} = array( $set => array() );
        }
        
        // Check if set exists
        if( !isset( $this->{$name}[ $set ] ) ){
            $this->{$name}[$set] = array(0=>0);
        }
        
        // Initialize rounds to each set
        foreach( $this->{$name} as $setI => $data ){           
            $countInDataset = count($data);
            if( $rounds >= $countInDataset ){
                for( $i = $countInDataset; $i <= $rounds; $i++ ){
                    $this->{$name}[$setI][ $i ] = 0;
                }
            }
        }
        
        // Add this round to data
        $this->{$name}[$set][ $rounds ]++;
        
        // Get the max number of rounds for any set
        $maxRounds = 0;
        foreach( $this->{$name} as $setI => $data ){
            $countInDataset = count($data);
            if( $countInDataset > $maxRounds ){
                $maxRounds = $countInDataset;
            }
        }
        
        // If any set is missing rounds, add them
        foreach( $this->{$name} as $setI => $data ){           
            $countInDataset = count($data);
            if( $maxRounds > $countInDataset ){
                for( $i = $countInDataset; $i < $maxRounds; $i++ ){
                    $this->{$name}[$setI][ $i ] = 0;
                }
            }
        }
    }
    
    // Get plot html
    private function getRoundPlotHtml( $dataName ){
        
        if( isset( $this->{$dataName} ) ){
            
            // Set the data columns
            $dataString = "['".$this->intervalMin." Min Intervals','".implode("','", array_keys($this->{$dataName}))."']";
            
            // Order the data properly
            $orderedData = array();
            $count = array(0,0,0,0,0);
            foreach( $this->{$dataName} as $set => $data ){
                foreach( $data as $round => $value ){
                    if( !isset($orderedData[$round]) ){
                        $orderedData[$round] = array();
                    }
                    $orderedData[$round][] = $value;
                    $dataSetNr = count($orderedData[$round])-1;
                    $count[ $dataSetNr ] += $value;
                }
            }
                        
            // Add ordered data
            foreach($orderedData as $round => $setsData ){
                $len = count($setsData);    
                $i = 0;
                while( $i < $len ){
                    $setsData[$i] = round($setsData[$i] / $count[$i],2);
                    $i++;
                }
                $string = "".implode(",", $setsData)."";
                // $string = str_replace("0","",$string);
                $dataString .= "\n,['".$round."',".$string."]";
            }
            
            //echo"<pre />";
            //echo $dataString;
            //print_r($count);


            return "<script type='text/javascript'>
          google.load('visualization', '1.1', {packages:['bar','corechart']});
          google.setOnLoadCallback(drawChart);
          
          function drawChart() {
            var data = google.visualization.arrayToDataTable([
              ".$dataString."
            ]);

            var options = {
              title: 'Identification of Lag Times',
              bar: {groupWidth: '95%'},
              chart: {
                subtitle: '".$dataName.". Total Entries: ".$this->totalEntries.". Clusters: ".$this->intervalMin." min, Data Shown: ".$this->slowPageDays." days',
              },
              width: 1600,
              bars: 'vertical',
              legend: { position: 'bottom' }
            };

            var chart = new google.charts.Bar(document.getElementById('columnchart_material'));

            chart.draw(data, google.charts.Bar.convertOptions(options) );
          }
        </script>
        <div id='columnchart_material' style='width: 1600px; height: 400px;'></div>";
        }
        else{
            return "No Data";
        }
        
        
    }
    
    
}

new logOverview();