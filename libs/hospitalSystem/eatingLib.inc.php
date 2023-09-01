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

class eatingLib {

    // Function for showing a ramen menu
    protected function showRamenMenu( $description = "" ){

        tableParser::show_list(
            'ramenMenu',
            "Ramen Shop",
            $this->menu,
            array(
                'name' => "Name",
                'heal' => "Heals",
                'cost' => "Cost"
            ),
            array(
                array("name" => "Order", "id" => $_GET['id'], "act" => "order", "orderID" => "table.id")
            ),
            true,   // Send directly to contentLoad
            false,   // Show previous/next links
            false,  // No links at the top to show
            false,   // Allow sorting on columns
            false,   // pretty-hide options
            false, // Top stuff
            $description // Top information
        );
    }

    // Buy ramen from the menu
    protected function buyRamenFromMenu( $itemID ){

        // Find item in the menu
        $buyItem = false;
        foreach( $this->menu as $menuItem ){
            if( $menuItem['id'] == $itemID ){
                $buyItem = $menuItem;
            }
        }

        // Is found?
        if( $buyItem !== false ){

            // Update user if he has the money
            if( $GLOBALS['userdata'][0]['money'] >= $buyItem['cost'] ){

                // Heal the user
                $this->heal_user( $buyItem['heal'], $buyItem['cost'] );

                // Message
                $GLOBALS['page']->Message( 'You purchased and ate some ramen, which has healed you for '.$buyItem['heal'].' HP' , 'Ramen Shop', 'id='.$_GET['id'],'Return');

            }
            else{
                throw new Exception("You cannot afford this ramen");
            }
        }
        else{
            throw new Exception("This item is not on the menu");
        }
    }

    protected function heal_user( $healAmount, $cost ){

        // Calculate new health
        $new_health = $GLOBALS['userdata'][0]['cur_health'] + $healAmount;
        if ($new_health > $GLOBALS['userdata'][0]['max_health']) {
            $new_health = $GLOBALS['userdata'][0]['max_health'];
        }

        // Update user
        $GLOBALS['database']->execute_query("
            UPDATE `users_statistics`
            SET
                `money` = `money` - '" . $cost . "',
                `cur_health` = '" . $new_health . "'
            WHERE `uid` = '" . $_SESSION['uid'] . "'
            LIMIT 1");

        $GLOBALS['Events']->acceptEvent('stats_cur_health', array('new'=>$new_health, 'old'=>$GLOBALS['userdata'][0]['cur_health'] ));
        $GLOBALS['Events']->acceptEvent('money_loss', array('old'=>$GLOBALS['userdata'][0]['money'],'new'=> $GLOBALS['userdata'][0]['money'] - $cost));

        // Update now
        $GLOBALS['userdata'][0]['cur_health'] = $new_health;

    }

}