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

class eventNews {

    public function __construct() {

        // Show the correct page
        if (!isset($_GET['act'])) {
            $this->show_event_news();
        } elseif ($_GET['act'] == 'newnews') {
            if (!isset($_POST['Submit'])) {
                $this->new_event_news_form();
            } else {
                $this->do_insert_news();
            }
        } elseif ($_GET['act'] == 'editnews') {
            if (!isset($_POST['Submit'])) {
                $this->edit_event_news_form();
            } else {
                $this->do_edit_event_news();
            }
        } elseif ($_GET['act'] == 'delnews') {
            if (!isset($_POST['Submit'])) {
                $this->verify_remove_event_news();
            } else {
                $this->do_remove_event_news();
            }
        }
    }
    
    //  Event news items
    private function show_event_news() {

        // Show form
        $news = $GLOBALS['database']->fetch_data("SELECT * FROM `news` WHERE `category` = 'events' ORDER BY `time` DESC");
        tableParser::show_list(
                'news', 'Event News', $news, array(
            'title' => "News Title",
            'posted_by' => "Author",
            'time' => "Time"
                ), array(
            array("name" => "Modify", "act" => "editnews", "nid" => "table.id"),
            array("name" => "Delete", "act" => "delnews", "nid" => "table.id")
                ), true, // Send directly to contentLoad
                false, array(
            array("name" => "New news item", "href" => "?id=" . $_GET["id"] . "&act=newnews")
                )
        );
    }

    private function new_event_news_form() {
        tableParser::parse_form('news', 'New news item', array('id', 'category', 'time'));
    }

    private function do_insert_news() {
        $data['time'] = $GLOBALS['page']->load_time;
        $data['category'] = 'events';
        if (tableParser::insert_data('news', $data)) {
            $GLOBALS['page']->setLogEntry("News Post", "Posted news item: <i>" . $_POST['title'] . "</i> was created");
            $GLOBALS['page']->Message("The news item has been added.", 'News System', 'id=' . $_GET['id']);
        } else {
            $GLOBALS['page']->Message("An error occured when adding the news item.", 'News System', 'id=' . $_GET['id']);
        }
    }

    private function edit_event_news_form() {
        if (is_numeric($_GET['nid']) && $_GET['nid'] > 0) {
            $newsdata = $GLOBALS['database']->fetch_data("SELECT * FROM `news` WHERE `id` = '" . $_GET['nid'] . "' LIMIT 1");
            if ($newsdata != '0 rows') {
                if ($newsdata[0]['category'] == 'events') {
                    tableParser::parse_form('news', 'Edit news item', array('id', 'time', 'category'), $newsdata);
                } else {
                    $GLOBALS['page']->Message("This is not an event news item.", 'News System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This news item does not exist.", 'News System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("An invalid news item has been specified.", 'News System', 'id=' . $_GET['id']);
        }
    }

    private function do_edit_event_news() {
        if (is_numeric($_GET['nid']) && $_GET['nid'] > 0) {
            $newsdata = $GLOBALS['database']->fetch_data("SELECT * FROM `news` WHERE `id` = '" . $_GET['nid'] . "' LIMIT 1");
            if ($newsdata != '0 rows') {
                if ($newsdata[0]['category'] == 'events') {
                    if (tableParser::update_data('news', 'id', $_GET['nid'])) {
                        
                        // get what changed
                        $changed = tableParser::check_data('news', 'id', $_GET['nid'], array('id'));

                        // Set log
                        $GLOBALS['page']->setLogEntry("News Post", "Edited news item ".$newsdata[0]['title'].": <i>" . $changed . "</i>");

                        // Show message
                        $GLOBALS['page']->Message("The news item has been updated.", 'News System', 'id=' . $_GET['id']);
                    } else {
                        $GLOBALS['page']->Message("An error occured and the news item has not been updated.", 'News System', 'id=' . $_GET['id']);
                    }
                } else {
                    $GLOBALS['page']->Message("This is not an event news item.", 'News System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This news item does not exist.", 'News System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("An invalid news item has been specified.", 'News System', 'id=' . $_GET['id']);
        }
    }

    private function verify_remove_event_news() {
        if (isset($_GET['nid']) && is_numeric($_GET['nid'])) {
            $GLOBALS['page']->Confirm("Delete this News Item?", 'News System', 'Delete now!');
        } else {
            $GLOBALS['page']->Message("No valid news ID was specified.", 'News System', 'id=' . $_GET['id']);
        }
    }

    private function do_remove_event_news() {
        if (is_numeric($_GET['nid']) && $_GET['nid'] > 0) {
            $newsdata = $GLOBALS['database']->fetch_data("SELECT * FROM `news` WHERE `id` = '" . $_GET['nid'] . "' LIMIT 1");
            if ($newsdata != '0 rows') {
                if ($newsdata[0]['category'] == 'events') {
                    if ($GLOBALS['database']->execute_query("DELETE FROM `news` WHERE `id` = '" . $_GET['nid'] . "' AND `category` = 'events' LIMIT 1")) {
                        
                        $GLOBALS['page']->setLogEntry("News Post", "Deleted news item ".$newsdata[0]['title']."");

                        $GLOBALS['page']->Message("The news item has been removed.", 'News System', 'id=' . $_GET['id']);
                    } else {
                        $GLOBALS['page']->Message("An error occured while removing the news item.", 'News System', 'id=' . $_GET['id']);
                    }
                } else {
                    $GLOBALS['page']->Message("This is not an event news item.", 'News System', 'id=' . $_GET['id']);
                }
            } else {
                $GLOBALS['page']->Message("This news item does not exist.", 'News System', 'id=' . $_GET['id']);
            }
        } else {
            $GLOBALS['page']->Message("An invalid news item has been specified.", 'News System', 'id=' . $_GET['id']);
        }
    }

}

new eventNews();