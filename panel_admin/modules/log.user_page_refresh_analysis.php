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

class pageRefreshes{

    function __construct(){
        
        try{
            // Load table parser
            $min =  tableParser::get_page_min();
            $number = tableParser::set_items_showed( 50 );
            $order =  tableParser::get_page_order( array(
                "pageTrack_session_sampleSize",
                "pageTrack_lifetime_sampleSize",
                "sessionRefreshRate", 
                "lifetimeRefreshRate",
                "sessionMean", 
                "lifeMean") 
            );
            
            $edits = $GLOBALS['database']->fetch_data("
                 SELECT 
                    `users`.`id`, `users`.`username`, 
                    `users_timer`.`last_login`, `users_timer`.`last_activity`,
                    `users_timer`.`pageTrack_session_sampleSize`, `users_timer`.`pageTrack_session_sumSamples`, `users_timer`.`pageTrack_session_sumSamplesSquared`,
                    `users_timer`.`pageTrack_lifetime_sampleSize`, `users_timer`.`pageTrack_lifetime_sumSamples`, `users_timer`.`pageTrack_lifetime_sumSamplesSquared`,
                    `users_timer`.`pageTrack_lifetime_seconds`,
                    (`users_timer`.`pageTrack_session_sampleSize` / (`users_timer`.`last_activity` - `users_timer`.`last_login`)) as `sessionRefreshRate`,
                    (`users_timer`.`pageTrack_lifetime_sampleSize` / `users_timer`.`pageTrack_lifetime_seconds`) as `lifetimeRefreshRate`,
                    (`users_timer`.`pageTrack_session_sumSamples` / `users_timer`.`pageTrack_session_sampleSize`) as `sessionMean`,
                    (`users_timer`.`pageTrack_lifetime_sumSamples` / `users_timer`.`pageTrack_lifetime_sampleSize`) as `lifeMean`
                 FROM `users`, `users_timer`
                 WHERE 
                    `users`.`id` = `users_timer`.`userid` AND 
                    `pageTrack_lifetime_sampleSize` > 50
                 ".$order."
                 LIMIT " . $min . "," . $number . "
            ");
            
            // Present data properly
            foreach( $edits as $key => $entry ){
                
                // Newly logged in users don't have session refresh rate yet.
                if( empty($entry['sessionRefreshRate']) ){
                    $edits[$key]['sessionRefreshRate'] = "N/A";
                }
                
                // Calculate deviations
                $sessionDeviation = sqrt( $entry[ "pageTrack_session_sumSamplesSquared" ]/$entry[ "pageTrack_session_sampleSize" ] - $entry[ "sessionMean" ]*$entry[ "sessionMean" ] );
                $edits[$key]['sessionMean'] = round($entry[ "sessionMean" ],2)." &plusmn; ".round($sessionDeviation,2)." secs";
                
                $lifeDeviation = sqrt( $entry[ "pageTrack_lifetime_sumSamplesSquared" ]/$entry[ "pageTrack_lifetime_sampleSize" ] - $entry[ "lifeMean" ]*$entry[ "lifeMean" ] );
                $edits[$key]['lifeMean'] = round($entry[ "lifeMean" ],2)." &plusmn; ".round($lifeDeviation,2)." secs";
            }

            // Run the show_list function            
            tableParser::show_list(
                'log',
                "User Page Refresh Rate during Last Session", 
                $edits,
                array(
                    'id' => "UID", 
                    'username' => "Username",
                    'last_login' => "Login Time",
                    'last_activity' => "Last Active Time",
                    'pageTrack_session_sampleSize' => "Session: Pageloads",
                    'sessionRefreshRate' => "Session: Pages / Second",
                    'sessionMean' => "Session: Seconds / Page",
                    'pageTrack_lifetime_sampleSize' => "Lifetime: Pageloads",
                    'lifetimeRefreshRate' => "Lifetime: Refresh Rate",
                    'lifeMean' => "Lifetime: Seconds / Page"
                ), 
                false ,
                true, // Send directly to contentLoad
                true, // No newer/older links
                false, // No top options links
                true, //  sorting on columns
                false, // No pretty options
                false // No top search field
            ); 
        }  catch (Exception $ex) {
            $GLOBALS['page']->Message($ex->getMessage(), 'Page Tracking System', 'id=' . $_GET['id'] );
        }
        
        
    }
}

new pageRefreshes();