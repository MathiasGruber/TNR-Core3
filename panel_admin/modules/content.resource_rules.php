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

class admin_resource_rules {
                       
    public function __construct() {   
        
        $GLOBALS['userdata'] = $GLOBALS['database']->load_user($_SESSION['uid']);
        $GLOBALS['user'] = new user();

        try{
            if (!isset($_GET['act'])) 
            {
                $this->main_screen();
            }
            
            elseif ($_GET['act'] == 'modify_rule' && is_numeric($_GET['rid']))
            {
                if (!isset($_POST['Submit']))
                {
                    $this->update_form();
                }
                
                else
                {
                    $this->update_rule();
                }
            }
            
            elseif ($_GET['act'] == 'delete_rule' && is_numeric($_GET['rid']))
            {
                if (!isset($_POST['Submit']))
                {
                    $this->verify_delete();
                }
                
                else
                {
                    $this->do_rule_delete();
                }
            }
            
            elseif ($_GET['act'] == 'new_rule')
            {
                if (!isset($_POST['Submit']))
                {
                    $this->new_form();
                }
                
                else
                {
                    $this->insert_rule();
                }
            } 
        } catch (Exception $ex) {
            $GLOBALS['page']->Message( $ex->getMessage() , 'Bloodline System', 'id=' . $_GET['id']);
        }                     
    }
    
    // Main function: shows jutsus overview
    public function main_screen( $queryType = "normal" ) {
        
        $query = "SELECT * FROM `resource_rules`";
        $rules = $GLOBALS['database']->fetch_data($query);
        
        // Create options
        $topOptions = array(
            array("name" => "New Rule", "href" => "?id=".$_GET["id"]."&act=new_rule")
        );
        
        $options[] = array("name" => "Modify", "act" => "modify_rule", "rid" => "table.id");
        $options[] = array("name" => "Delete", "act" => "delete_rule", "rid" => "table.id");
        
        tableParser::show_list(
            'resource rules',
            'resource rules admin', 
            $rules,
            array(
                'id' => 'rule ID',
                'notes' => "Notes"
            ), 
            $options ,
            true, // Send directly to contentLoad
            false,
            $topOptions,
            false, // No sorting on columns
            false, // No pretty options
            false, // No top search field
            '<b>System notes: </b> wasdf'
        
        );        
    }

    public function verify_delete() {
        if (isset($_GET['rid']) ) {
              $GLOBALS['page']->Confirm("Delete this Resource Rule?", 'Resource Rule System', 'Delete now!'); 
        } else {
            throw new Exception("No valid Resource Rule ID was specified");
        }
    }

    public function do_rule_delete() {
        if ($GLOBALS['database']->execute_query("DELETE FROM `resource_rules` WHERE `id` = '" . $_GET['rid'] . "' LIMIT 1"))
        {
            $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                (UNIX_TIMESTAMP(),'".$GLOBALS['userdata'][0]['username']."','".$GLOBALS['user']->real_ip_address()."', 'Resource Rule Change','','Rule ID: <i>".$_GET['id']."</i> was Deleted')");            

            $GLOBALS['page']->Message("The Resource Rule has been deleted.", 'Resource Rule System', 'id='.$_GET['id']); 
        } 
        else
        {
            throw new Exception("An error occured while deleting the Resource Rule.");
        }
    }
    
    public function new_form()
    {
        tableParser::parse_form('resource_rules', 'New rule', array('id'));
    }

    public function insert_rule() {
        if (tableParser::insert_data('resource_rules')) {
            
             $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
            (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
            (UNIX_TIMESTAMP(),'".$GLOBALS['userdata'][0]['username']."','".$GLOBALS['user']->real_ip_address()."', 'Resource Rule Change','','Rule Notes: <i>".$_POST['notes']."</i> Created')");            
            
            $GLOBALS['page']->Message("The resource rule has been added.", 'Resource Rule System', 'id='.$_GET['id']); 
        } else {
            throw new Exception("An error occured and the Resource Rule has not been added.");
        }
    }
    
    public function update_form() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `resource_rules` WHERE `id` = '" . $_GET['rid'] . "'");
        if ($data != '0 rows') {
            tableParser::parse_form('resource_rules', 'Update Resource Rule', array('id'), $data);
        } else {
            throw new Exception("This jutsu does not exist.");
        }
    }

    public function update_rule() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `resource_rules` WHERE `id` = '" . $_GET['rid'] . "' LIMIT 1");
        if ($data != '0 rows') {

            $changed = tableParser::check_data('resource_rules','id',$_GET['rid'],array() );  
            
            if (tableParser::update_data('resource_rules', 'id', $_GET['rid'])) {
                
                $GLOBALS['database']->execute_query("INSERT INTO `content_edits` 
                (`time`,`aid`,`ip`,`title`,`contentID`,`changes`) VALUES
                (UNIX_TIMESTAMP(),'".$GLOBALS['userdata'][0]['username']."','".$GLOBALS['user']->real_ip_address()."', 'Resource Rule Change','".$_GET['rid']."','Resource Rule ID:".$_GET['rid']." Changed:<br>".$changed."')");            

                $GLOBALS['page']->Message("The Resource Rule has been updated.", 'Resource Rule System', 'id='.$_GET['id']); 
            } else {
                throw new Exception("An error occured while updating the Resoure Rule.");
            }
        }
        else{
            throw new Exception("Resource Rule could not be found in the system.");
        }
    }

}

new admin_resource_rules();