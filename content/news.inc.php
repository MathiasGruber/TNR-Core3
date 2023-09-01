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

    class news {
        
        public function __construct() {
            
            try {
                
                $newsQuery = 'WHERE news.id > 0 ORDER BY news.time DESC LIMIT 3';
                
                // How manu news items to show
                if (isset($_GET['nid']) && ctype_digit($_GET['nid'])) {
                    $newsQuery = 'WHERE news.id = '.$_GET['nid'].' LIMIT 1';
                } 

                // Get news items
                if(!($news = $GLOBALS['database']->fetch_data('SELECT `news`.* FROM `news` '.$newsQuery))) {
                    throw new Exception('There was an issue loading the news stories!');
                }
                elseif ($news === '0 rows') {
                    throw new Exception('There is no news existing or by this ID!');
                }
                
                $GLOBALS['template']->assign('news', $news);
                $GLOBALS['template']->assign('contentLoad', './templates/content/news/allNews.tpl');
                
            } 
            catch (Exception $e) {
                $GLOBALS['database']->transaction_rollback($e->getMessage());
                $GLOBALS['page']->Message($e->getMessage(), $this->system_name, 'id='.$_GET['id']);
            }
        }
    }
    
    new news();