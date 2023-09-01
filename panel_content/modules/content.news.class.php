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

    function __construct() {
        if (!isset($_GET['act'])) {
            $this->main_screen();
        } elseif ($_GET['act'] == 'new') {
            if (!isset($_POST['Submit'])) {
                $this->new_form();
            } else {
                $this->insert_new();
            }
        } elseif ($_GET['act'] == 'modify' && is_numeric($_GET['nid'])) {
            if (!isset($_POST['Submit'])) {
                $this->update_form();
            } else {
                $this->update_news();
            }
        } elseif ($_GET['act'] == 'delete' && is_numeric($_GET['nid'])) {
            if (!isset($_POST['Submit'])) {
                $this->verify_delete();
            } else {
                $this->do_delete();
            }
        } elseif ($_GET['act'] == 'clear') {
            if (!isset($_POST['Submit'])) {
                $this->verify_clear();
            } else {
                $this->do_clear();
            }
        }
    }

    //		Main screen:
    function main_screen() {

        // Show form
        $news = $GLOBALS['database']->fetch_data("SELECT * FROM `news` ORDER BY `time` DESC");
        tableParser::show_list(
                'news', 'Event News', $news, array(
            'title' => "Title",
            'posted_by' => "Author",
            'time' => "Time"
                ), array(
            array("name" => "Modify", "act" => "modify", "nid" => "table.id"),
            array("name" => "Remove", "act" => "delete", "nid" => "table.id")
                ), true, // Send directly to contentLoad
                false, array(
            array("name" => "New Item", "href" => "?id=" . $_GET["id"] . "&act=new"),
            array("name" => "Clear News Item", "href" => "?id=" . $_GET["id"] . "&act=clear")
                )
        );

        // Set a return link for the page
        $GLOBALS['template']->assign('returnLink', true);
    }

    //		New news item:
    private function new_form() {
        tableParser::parse_form('news', 'New news item', array('id', 'time'));
    }

    private function insert_new() {
        $data['time'] = time();
        if (tableParser::insert_data('news', $data)) {
            $GLOBALS['page']->Message("The news item has been added", 'News System', 'id=' . $_GET['id']);
        } else {
            $GLOBALS['page']->Message("An error occured when adding the news item", 'News System', 'id=' . $_GET['id']);
        }
    }

    //		Update news item:
    function update_form() {
        $data = $GLOBALS['database']->fetch_data("SELECT * FROM `news` WHERE `id` = '" . $_GET['nid'] . "'");
        if ($data != '0 rows') {
            tableParser::parse_form('news', 'Update news item', array('id', 'time'), $data);
        } else {
            $GLOBALS['page']->Message("This news item does not exist", 'News System', 'id=' . $_GET['id']);
        }
    }

    function update_news() {
        if (tableParser::update_data('news', 'id', $_GET['nid'])) {
            $GLOBALS['page']->Message("The news item has been updated", 'News System', 'id=' . $_GET['id']);
        } else {

            $GLOBALS['page']->Message("An error occured while updating the news item ", 'News System', 'id=' . $_GET['id']);
        }
    }

    //		Delete news item
    function verify_delete() {
        $GLOBALS['page']->Confirm("Delete this news item?", 'News System', 'Delete now!');
    }

    function do_delete() {
        if ($GLOBALS['database']->execute_query("DELETE FROM `news` WHERE `id` = '" . $_GET['nid'] . "' LIMIT 1")) {
            $GLOBALS['page']->Message("The news item and has been deleted", 'News System', 'id=' . $_GET['id']);
        }
    }

    // Clear news
    function verify_clear() {
        $GLOBALS['page']->Confirm("Clear All News", 'News System', 'Clear now!');
    }

    function do_clear() {
        if ($GLOBALS['database']->execute_query("TRUNCATE TABLE `news`")) {
            if ($GLOBALS['database']->execute_query("TRUNCATE TABLE `news_comments`")) {
                $GLOBALS['page']->Message("All news items and related comments have been deleted", 'Note System', 'id=' . $_GET['id']);
            }
        }
    }

}

new news();