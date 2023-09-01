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
class optimize {             
    
    public function __construct(){
        if(!isset($_POST['Submit'])){
            $this->purge_form();
        }
        else{
            $this->do_optimize();
        }                       
    }

    private function purge_form(){        
        $GLOBALS['page']->Confirm("Running the optimization will help clear up overhead in MySQL manually. Should not be neccesary, since this is also done by the regular maintainance.", 'Optimization System', 'Run Now!');         
    }
    
    private function do_optimize(){
        /* $data = $GLOBALS['database']->fetch_data("OPTIMIZE TABLE 
            `adminIpLog`, `admin_edits`, `admin_notes`, `ai`, `alliances`, `alliance_requests`, 
            `backups`, `bingo_book`, `bloodlines`, `bloodline_rolls`, `blueMessages`, `clans`, 
            `content_edits`, `crimes`,
            `fbRequests`, `homes`, `IDS_log`, `ipn_errors`, 
            `ipn_payments`, `ipn_test`, `items`, `jutsu`, `lead_notifications`, `levels`, `locations`, 
            `lottery`, `market_purchases`, `marriages`, `missions`, `moderator_log`, `multi_battle`, 
            `news`, `news_comments`, `ninja_farmer`, `occupations`, `overCapLog`, `pages`, `parsetime_log`, 
            `pass_request`, `ryo_track`, `site_information`, `site_timer`, `spar_challenges`, `squads`, 
            `tavern`, `tavern_anbu`, `tavern_clan`, `tavern_mod`, `territory_challenges`, 
            `tnr_map`, `trades`, `trade_log`, `trade_offers`, `unlock`, `users`, `users_events`, 
            `users_inventory`, `users_jutsu`, `users_loyalty`, `users_missions`, `users_occupations`, 
            `users_pm`, `users_preferences`, `users_statistics`, `users_timer`, `user_notes`, `user_reports`, 
            `villages`, `village_structures`, `votes` "
        ); */
        /*
        if($GLOBALS['database']->execute_query('UPDATE `site_information`
            SET site_information.value = "0" 
            WHERE site_information.option = "site_status" LIMIT 1') === false) {
            echo 'Maintenance Site Status Off Update Failed!';
        }
        
        sleep(3);
        
        if($GLOBALS['database']->execute_query('UPDATE `site_information`
            SET site_information.value = "1" 
            WHERE site_information.option = "site_status" LIMIT 1') === false) {
            echo 'Maintenance Site Status On Update Failed!';
        }*/
        
        
        $GLOBALS['page']->Message("The tables have been optimized.", 'Optimization System', 'id='.$_GET['id']); 
    }
}
new optimize();