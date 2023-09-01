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

class survey {

    public function __construct() {

        $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

        if (!isset($_GET['act'])) {
            $this->show_form();
        } elseif ($_GET['act'] == 'ninjaFarmer') {
            $this->showNinjaFarmers();
        } elseif ($_GET['act'] == 'checkCode') {
            $this->checkCode();
        }

        if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
            throw new Exception('There was an issue releasing the lock!');
        }

    }

    private function show_form() {
        $GLOBALS['template']->assign('contentLoad', './templates/content/surveys/overview.tpl');
    }

    private function showNinjaFarmers(){

        if( isset($_GET['option']) && $_GET['option'] == "Unlink" ){
            $GLOBALS['database']->execute_query("
                            UPDATE `ninja_farmer`
                            SET `deviceIDs` = ''
                            WHERE `user` = '".$GLOBALS['userdata'][0]['username']."'
                            LIMIT 1");
        }

        // Set the return link
        $GLOBALS['template']->assign('returnLink', "?id=" . $_GET['id'] );

        // Get how many points for next popularity
        $points = $reps = 0;
        $deviceID = "";
        $ninjaFarmerStats = $GLOBALS['database']->fetch_data("SELECT * FROM `ninja_farmer` WHERE `user` = '".$GLOBALS['userdata'][0]['username']."' LIMIT 1");
        if( $ninjaFarmerStats !== "0 rows" ){
            $points = $ninjaFarmerStats[0]["farmer_points"];
            $reps = $ninjaFarmerStats[0]["pop_points"];
            if( !empty($ninjaFarmerStats[0]["deviceIDs"]) ){
                $deviceID = "<br><br><i>You are currently linked with the device ID: ".$ninjaFarmerStats[0]["deviceIDs"].".</i> <a href='?id=".$_GET['id']."&act=ninjaFarmer&option=Unlink'>Unlink Now!</a>";
            }
        }

        // Save start points
        $startPoints = $points;

        // How many reps should that give
        $reps = 0;
        $reps_base = 1000000;
        $reps_power = 2;
        $pointsNeeded = $reps_base;

        while( $points > 0 ){
            $points -= ($reps_base + $reps*$reps_base + $reps_base*$reps*pow($reps_power,$reps));
            if( $points > 0 ){
                $reps += 1;
            }
            else{
                $pointsNeeded = -1 * $points;
            }
        }


        // Get table
        $min =  tableParser::get_page_min();

        $ninjafarmers = $GLOBALS['database']->fetch_data("SELECT * FROM `ninja_farmer` ORDER BY `farmer_points` DESC LIMIT ".$min.", 10");
        tableParser::show_list(
                'ninja_farmer', 'Top Ninja Farmers of TNR', $ninjafarmers, array(
            'user' => "User",
            'farmer_points' => "Points",
            'pop_points' => "Popularity Points"
                ),
                false ,
            true, // Send directly to contentLoad
            true, // No newer/older links
            false, // No top options links
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            'You currently have '.$startPoints.' ninja farmer points. <br>
             To get the next popularity point, you need to gather '.$pointsNeeded." more ninja farmer points".$deviceID.'<br>
             The maximum achieveable number of popularity points is 40!'
        );
    }

    private function checkCode(){
        if( ctype_alnum($_POST['code']) ){
            $ninjafarmers = $GLOBALS['database']->fetch_data("SELECT * FROM `promotionCodes` WHERE `code` = '".$_POST['code']."' LIMIT 1");
            if( $ninjafarmers !== "0 rows" ){
                if( $ninjafarmers[0]['collector'] == "Unclaimed" ){
                    $newOld = "";
                    if( $GLOBALS['userdata'][0]['rank'] == "Academy Student" ){
                        $newOld = "1:0";
                    }
                    else{
                        $newOld = "0:1";
                    }

                    // Update Code
                    $GLOBALS['database']->execute_query("
                        UPDATE `promotionCodes`
                        SET
                            `collector` = '".$GLOBALS['userdata'][0]['username']."',
                            `inputTimes` = '1',
                            `new:old_user` = '".$newOld."'
                        WHERE `code` = '".$_POST['code']."' LIMIT 1");

                    // Update User with Popularity point
                    $GLOBALS['database']->execute_query("
                        UPDATE `users_statistics`
                        SET
                            `pop_ever` = `pop_ever` + 1,
                            `pop_now` = `pop_now` + 1
                        WHERE `uid` = '".$_SESSION['uid']."' LIMIT 1");

                    $GLOBALS['Events']->acceptEvent('pop_gain', array('old'=>$GLOBALS['userdata'][0]['pop_now'],'new'=> $GLOBALS['userdata'][0]['pop_now'] + 1));

                    $GLOBALS['page']->Message("You claimed the code and gained 1 popularity point! Congratulations!", 'Promotion Code', 'id='.$_GET['id']);
                }
                else{
                    $GLOBALS['page']->Message("This code has already been claimed by ".$ninjafarmers[0]['collector'].". Sorry, better luck next time.", 'Promotion Code', 'id='.$_GET['id']);
                    $GLOBALS['database']->execute_query("UPDATE `promotionCodes` SET `inputTimes` = `inputTimes` + 1 WHERE `code` = '".$_POST['code']."' LIMIT 1");
                }
            }
            else{
                $GLOBALS['page']->Message("Code could not be found in the database.", 'Promotion Code', 'id='.$_GET['id']);
            }
        }
        else{
            $GLOBALS['page']->Message("Codes only consist of letters and numbers. Something weird is up.", 'Promotion Code', 'id='.$_GET['id']);
        }
    }

}

new survey();