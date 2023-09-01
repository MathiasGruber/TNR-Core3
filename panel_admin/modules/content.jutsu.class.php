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
                       
require_once(Data::$absSvrPath.'/global_libs/User/user.class.php');

class admin_jutsu {
                       
    public function __construct() {   
        
        // Set permissions
        $this->canEdit = array("Admin", "ContentAdmin", "ContentMember");
        $this->canRead = array("Admin", "ContentAdmin", "ContentMember");
        
        $GLOBALS['userdata'] = $GLOBALS['database']->load_user($_SESSION['uid']);
        $GLOBALS['user'] = new user();

        try{
            if (!isset($_GET['act'])) {
                $this->main_screen();
            } elseif ($_GET['act'] == 'search') {
                if(!isset($_POST['Submit'])){
                    $this->search();
                }
                else{
                    $this->main_screen("search");
                } 
            } elseif ($_GET['act'] == 'read_jutsu') {
                $this->showJutsu();
            } elseif ($_GET['act'] == 'jutsu_modify' && is_numeric($_GET['jid'])) {
                if (!isset($_POST['Submit'])) {
                    $this->update_form();
                } else {
                    $this->update_jutsu();
                }
            } elseif ($_GET['act'] == 'jutsu_delete' && is_numeric($_GET['jid'])) {
                if (!isset($_POST['Submit'])) {
                    $this->verify_delete();
                } else{
                    $this->do_jutsu_delete();
                }
            } elseif ($_GET['act'] == 'jutsu_new') {
                if (!isset($_POST['Submit'])) {
                    $this->new_form();
                } else {
                    $this->insert_jutsu();
                }
            } 
        } catch (Exception $ex) {
            $GLOBALS['page']->Message( $ex->getMessage() , 'Bloodline System', 'id=' . $_GET['id']);
        }                     
    }
    
    // Check if user can view this
    private function hasReadPermission(){
        if( in_array($GLOBALS['userdata'][0]['user_rank'], $this->canRead ) ){
            return true;
        }
        return false;
    }
    
    // Check if user can edit this
    private function hasEditPermission(){
        if( in_array($GLOBALS['userdata'][0]['user_rank'], $this->canEdit ) ){
            return true;
        }
        return false;
    }
    
    // Check the edit permission
    private function requireEditPermission(){
        if( $this->hasEditPermission() ){
            return true;
        }
        throw new Exception("You do not have permission to do edit data in this module");
    }
    
    // Show bloodline data
    private function showJutsu(){
        
        // Check if we can find the bloodline
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `jutsu` WHERE `id` = '" . $_GET['jid'] . "'");
        if ($data != '0 rows') {

            $displayRows = tableParser::parseDatarowForDisplay($data[0]);

            // Parse table
            tableParser::show_list(
                    'item', 'Jutsu: ' . $data[0]['name'], $displayRows, array(
                'key' => 'Key',
                'value' => "Value"
                    ),false, true, // Send directly to contentLoad
                    false, false, false, // No sorting on columns
                    false, // No pretty options
                    false, // No top search field
                    ''
            );

            $GLOBALS['template']->assign("returnLink", true);
        } else {
            throw new Exception("This jutsu does not exist.");
        }
    }
    
    // Main function: shows jutsus overview
    public function main_screen( $queryType = "normal" ) {
        
        // Handle either normal viewing or search results
        $query = "SELECT * FROM `jutsu` ";

        $where = array();

        if( isset($type) && $type != '' )
            $where[] = " `attack_type` = '{$type}' ";

        if( isset($_POST['search']) )
            $where[] = " `". ($_POST['search'] == 'jid' ? 'id' : $_POST['search']) ."` LIKE '%{$_POST[$_POST['search']]}%' ";

        if( isset($_GET['type']) )
        switch( $_GET['type'] ){
            case "gen": $where[] = "`attack_type` = 'genjutsu' "; break;
            case "tai": $where[] = "`attack_type` = 'taijutsu' "; break;
            case "wea": $where[] = "`attack_type` = 'weapon' "; break;
            case "nin": $where[] = "`attack_type` = 'ninjutsu' "; break;
            case "high": $where[] = "`attack_type` = 'highest' "; break;
            case "clan": $where[] = "`jutsu_type` = 'clan' "; break;
            case "blood": $where[] = "`jutsu_type` = 'bloodline' "; break;
            case "loyal": $where[] = "`jutsu_type` = 'loyalty' "; break;
        }

        if( count($where) >= 1 )
            $query .= " WHERE ".implode(' AND ',$where)." ORDER BY `id` DESC";

        $result = $GLOBALS['database']->fetch_data($query);
        
        // Create options
        $topOptions = array(
            array("name" => "Ninjutsu", "href" =>"?id=".$_GET["id"]."&type=nin"),
            array("name" => "Genjutsu", "href" =>"?id=".$_GET["id"]."&type=gen"),
            array("name" => "Taijutsu", "href" =>"?id=".$_GET["id"]."&type=tai"),
            array("name" => "Weapon", "href" =>"?id=".$_GET["id"]."&type=wea"),
            array("name" => "Highest", "href" =>"?id=".$_GET["id"]."&type=high"),
            array("name" => "Clan", "href" =>"?id=".$_GET["id"]."&type=clan"),
            array("name" => "Bloodline", "href" =>"?id=".$_GET["id"]."&type=blood"),
            array("name" => "Loyalty", "href" =>"?id=".$_GET["id"]."&type=loyal")
        );
        
        // For users with edit permissions
        if( $this->hasEditPermission() ){
            $options[] = array("name" => "Modify", "act" => "jutsu_modify", "jid" => "table.id");
            $options[] = array("name" => "Delete", "act" => "jutsu_delete", "jid" => "table.id");
            $topOptions[] = array("name" => "New Jutsu", "href" =>"?id=".$_GET["id"]."&act=jutsu_new");
        }
        elseif( $this->hasReadPermission() ){
            $options[] = array("name" => "Read", "act" => "read_jutsu", "jid" => "table.id");
        }
        
        
        tableParser::show_list(
            'jutsu',
            'Jutsu admin', 
            $result,
            array(
                'id' => 'JutsuID',
                'name' => "Name",
                'required_rank' => "Rank",
                'attack_type' => "Type",
                'jutsu_type' => "Jutsu Type",
                'tags' => 'Tags',
                'notes' => "Notes"
            ), 
            $options ,
            true, // Send directly to contentLoad
            false,
            $topOptions,
            true, // No sorting on columns
            false, // No pretty options
            array(
                array(
                    'infoText'=>'Jid',
                    'href'=>"?id=" . $_GET["id"],
                    'postField'=>'jid',
                    'postIdentifier'=>'search',
                    'inputName'=>'jid'
                ),
                array(
                    'infoText'=>'Name',
                    'href'=>"?id=" . $_GET["id"],
                    'postField'=>'name',
                    'postIdentifier'=>'search',
                    'inputName'=>'name'
                ),
                array(
                    'infoText'=>'Rank (1-5)',
                    'href'=>"?id=" . $_GET["id"],
                    'postField'=>'required_rank',
                    'postIdentifier'=>'search',
                    'inputName'=>'required_rank'
                ),
                array(
                    'infoText'=>'Description',
                    'href'=>"?id=" . $_GET["id"],
                    'postField'=>'description',
                    'postIdentifier'=>'search',
                    'inputName'=>'description'
                ),
                array(
                    'infoText'=>'Notes',
                    'href'=>"?id=" . $_GET["id"],
                    'postField'=>'notes',
                    'postIdentifier'=>'search',
                    'inputName'=>'notes'
                ),
                array(
                    'infoText'=>'Tags',
                    'href'=>"?id=" . $_GET["id"],
                    'postField'=>'tags',
                    'postIdentifier'=>'search',
                    'inputName'=>'tags'
                )
            ),
            '<b>System notes: </b> If you set splitJutsu to "yes", then you must create 4 entries for the fields description, battle_description and effect. These entries must be on the form:<br>
             N{ninjutsu-specialization-data}, T{taijutsu-specialization-data}, G{genjutsu-specialization-data}, and W{weapon-specialization-data},'
        
        );        
    }
  
    public function verify_delete() {
        $this->requireEditPermission();
        if (isset($_GET['jid']) ) {
              $GLOBALS['page']->Confirm("Delete this jutsu?", 'Jutsu System', 'Delete now!'); 
        } else {
            throw new Exception("No valid jutsu ID was specified");
        }
    }

    public function do_jutsu_delete() {
        $this->requireEditPermission();
        if ($GLOBALS['database']->execute_query("DELETE FROM `jutsu` WHERE `id` = '" . $_GET['jid'] . "' LIMIT 1")) {
            if ($GLOBALS['database']->execute_query("DELETE FROM `users_jutsu` WHERE `jid` = '" . $_GET['jid'] . "'")) {
                
                $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                (UNIX_TIMESTAMP(),'".$GLOBALS['userdata'][0]['username']."','".$GLOBALS['user']->real_ip_address()."', 'Jutsu Change','','Jutsu ID: <i>".$_GET['jid']."</i> was Deleted')");            

                $GLOBALS['page']->Message("The jutsu has been deleted from the jutsu table, and all users.", 'Jutsu System', 'id='.$_GET['id']); 
            } else {
                throw new Exception("The jutsu has been deleted from the jutsu table but a problem occured when deleting the jutsu from all the users, user jutsu data is probably broken, contact an administrator with PMA access.");
            }
        } else {
            throw new Exception("An error occured while deleting the jutsu.");
        }
    }
    
    public function new_form() {
        $this->requireEditPermission();
        tableParser::parse_form('jutsu', 'New jutsu', array('id'));
    }

    public function insert_jutsu() {
        $this->requireEditPermission();
        if (tableParser::insert_data('jutsu')) {
            
             $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
            (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
            (UNIX_TIMESTAMP(),'".$GLOBALS['userdata'][0]['username']."','".$GLOBALS['user']->real_ip_address()."', 'Jutsu Change','','Jutsu named: <i>".$_POST['name']."</i> Created')");            
            
            $GLOBALS['page']->Message("The event jutsu has been added.", 'Jutsu System', 'id='.$_GET['id']); 
        } else {
            throw new Exception("An error occured and the jutsu has not been added.");
        }
    }
    
    public function update_form() {
        $this->requireEditPermission();
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `jutsu` WHERE `id` = '" . $_GET['jid'] . "'");
        if ($data != '0 rows') {
            tableParser::parse_form('jutsu', 'Update jutsu', array('id'), $data);
        } else {
            throw new Exception("This jutsu does not exist.");
        }
    }

    public function update_jutsu() {
        $this->requireEditPermission();
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `jutsu` WHERE `id` = '" . $_GET['jid'] . "' LIMIT 1");
        if ($data != '0 rows') {

            $changed = tableParser::check_data('jutsu','id',$_GET['jid'],array() );  
            
            if (tableParser::update_data('jutsu', 'id', $_GET['jid'])) {
                
                $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                (UNIX_TIMESTAMP(),'".$GLOBALS['userdata'][0]['username']."','".$GLOBALS['user']->real_ip_address()."', 'Jutsu Change','".$_GET['jid']."','Jutsu ID:".$_GET['jid']." Changed:<br>".$changed."')");            

                $GLOBALS['page']->Message("The jutsu has been updated.", 'Jutsu System', 'id='.$_GET['id']); 
            } else {
                throw new Exception("An error occured while updating the jutsu.");
            }
        }
        else{
            throw new Exception("Jutsu could not be found in the system.");
        }
    }

    public function search() {  
        $GLOBALS['template']->assign('contentLoad', 'panel_admin/templates/content_jutsu/search.tpl');        
    }
}

new admin_jutsu();