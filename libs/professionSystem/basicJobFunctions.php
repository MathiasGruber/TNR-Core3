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

class basicJobFunctions {
    
    // Get user data
    public function fetch_user( $lock = false ) {
        
        // Get the user
        $query = "
            SELECT 
                `users_occupations`.*,
                `occupations`.*
            FROM `users_occupations`
                LEFT JOIN `occupations` ON (`users_occupations`.`".$this->jobType."`=`occupations`.`id`)
            WHERE
                `users_occupations`.`userid` = '" . $_SESSION['uid'] . "'";
        if( $lock == true ){
            $query .= " FOR UPDATE ";
        }
        
        $this->user = $GLOBALS['database']->fetch_data( $query );
        return $this->user;
    }
    
    // Set jobType
    public function setJobType( $type ){
        $this->jobType = $type;
    }
    
    // Function for updating information in the users_occupations table
    protected function set_occupation_data( $params ){
        $query = "";
        foreach( $params as $key => $value ){
            $query .= ($query == "") ? "`".$key."` = ".$value."" : ", `".$key."` = ".$value."";
        }
        $query = "UPDATE `users_occupations` SET ".$query." WHERE `userid` = '" . $_SESSION['uid'] . "' LIMIT 1";
        if ($GLOBALS['database']->execute_query($query)) {
            return true;
        }            
        return false;
    }
    
    // Function for gettin occupation
    protected function get_occupation( $id ){
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `occupations` WHERE `id` = '" . $id . "' AND ".$this->queryLimitation." LIMIT 1");            
        return $data;
    }
    
    // Check if user can get a job
    protected function can_get_job($job = false){
        
        if($job === false)
            $job = $_GET['job'];

        // Only one job
        if ($this->user[0][ $this->jobType ] == 0) {
            
            // Get the requested job
            $job = $this->get_occupation( $job );
            if ($job != '0 rows') {
                
                // Check rank ID
                if ($job[0]['rankid'] <= $GLOBALS['userdata'][0]['rank_id']) {
                    
                    // Vicotyr
                    return $job;
                    
                } else {
                    throw new Exception("You are not the correct rank for this ".$this->jobType);
                }
            } else {
                throw new Exception("This ".$this->jobType." does not exist");
            }
        } else {
            throw new Exception("You cannot sign up for multiple ".$this->jobType."s at once");
        }
        return false;
    }
    
    // Get Profession name
    public function get_profession_name( $identifier ){
        switch( $identifier ){
            case "weaponCraft": $identifier = "Weapon Crafter"; break;
            case "armorCraft": $identifier = "Armor Crafter"; break;
            case "chefCook": $identifier = "Chef Cook"; break;
            case "miner": $identifier = "Miner"; break;
            default: $identifier = "N/A"; break;
        }
        return $identifier;
    }
    
}