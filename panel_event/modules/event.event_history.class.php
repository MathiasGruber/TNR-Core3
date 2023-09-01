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

class globalTexts {
    public function __construct() {
        
        // Show the correct page
        if (!isset($_POST['Submit'])) {
                
            // Show the current page form
            $this->editPageForm();
        } else {

            // Do edit
            $this->editPageDo();
        }
    }

    
    private function editPageForm(){
        $page = $GLOBALS['database']->fetch_data("SELECT `content` FROM `information_pages` WHERE `id` = 4");
        if( $page !== "0 rows" ){
            $GLOBALS['page']->UserInput( 
                    "Edit the content of the page below. HTML is not allowed, but feel free to use bbcode.", 
                    "Edit Page", 
                    array(
                        array("infoText"=>"","inputFieldName"=>"content","type"=>"textarea","inputFieldValue"=> $page[0]['content'] )
                    ), 
                    array(
                        "href"=>"?id=".$_GET['id'] ,
                        "submitFieldName"=>"Submit", 
                        "submitFieldText"=>"Submit Changes"),
                    "Return" 
             );
        }
    }
    
    private function editPageDo(){
        $page = $GLOBALS['database']->fetch_data("SELECT `id`, `content` FROM `information_pages` WHERE `id` = 4");
        if( $page !== "0 rows" ){
            
            // Store message
            $message = functions::store_content($_POST['content']);
            
            // Run update
            $GLOBALS['database']->execute_query("
            UPDATE `information_pages` 
            SET `content` = '".$message."',
                `time` = UNIX_TIMESTAMP()
            WHERE 
                `id` = 4 LIMIT 1");
            
            // Give message
            $GLOBALS['page']->Message( "Page information has been updated" , 'GlobalText System', 'id='.$_REQUEST['id'],'Return');
        }
    }

    // Get & Save file data
    private function getFileContent($filename) {
        if (is_file($filename)) {
            $fp = fopen($filename, 'r');
            $fileData = stripslashes(fread($fp, filesize($filename)));
            fclose($fp);
            return $fileData;
        } else {
            return 0;
        }
    }

    private function saveFileContent($filename, $content) {
        $fp = fopen($filename, 'w');
        if (fwrite($fp, $content)) {
            fclose($fp);
            return 1;
        } else {
            fclose($fp);
            return 0;
        }
    }

}

new globalTexts();