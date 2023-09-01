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

// define class
class repDistribution {

    public function __construct() {

        // Encapsulate in a try-catch
        try{
            
            // Settings
            $this->days = 30;
            
            // Show the screens
            if (!isset($_GET['act'])) {
                $this->main_screen();
            } 
        } catch (Exception $ex) {
            $GLOBALS['database']->transaction_rollback( $e->getMessage() );
            $GLOBALS['page']->Message( $e->getMessage() , 'Rep Distribution Charts', 'id='.$_GET['id']);            
        }
        
    }

    // Main Screen
    private function main_screen() {
        
        // Get active users rep distribution
        $repUsers = $GLOBALS['database']->fetch_data("
            SELECT `rep_now`
            FROM `users_statistics`,`users_timer`
            WHERE 
                `users_timer`.`userid` = `users_statistics`.`uid` AND
                `users_statistics`.`rep_now` > 20 AND
                `users_timer`.`last_login` > '".(time()-$this->days*24*3600)."'");
        
        // Go through them all - separate below 100 and above 100
        foreach( $repUsers as $repEntry ){
            if( $repEntry['rep_now'] < 100 ){
                $this->addHistogramData( "repDataBelow", "Below 100 Reps, active within ".$this->days." days", $repEntry['rep_now'] );
            }
            else{
                $this->addHistogramData( "repDataAbove", "Above 100 Reps, active within ".$this->days." days", $repEntry['rep_now'] );
            }            
        }
        
        // Send to html
        $GLOBALS['page']->Message( 
            "<script type='text/javascript'>
                google.load('visualization', '1.1', {packages:['bar','corechart']});
            </script>".                
            $this->getDistributionPlotHtml( "repDataBelow", "below", "reps", "users", "Reputation Points below 100 reps" ).
            $this->getDistributionPlotHtml( "repDataAbove", "above", "reps", "users", "Reputation Points above 100 reps" ), 
            'Rep Distribution Charts', 
            'id='.$_GET['id']
        );            

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
        $key = "reps", 
        $hAxis = "%", 
        $vAxis = "Reps",
        $title = "Reputation Points"
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
                          hAxis: {title: '".$hAxis."', gridlines: {count:50}},
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

}

new repDistribution();