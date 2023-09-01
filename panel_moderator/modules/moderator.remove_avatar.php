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

    class module {

        public function __construct() {

            $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            try {
                // Choose Between Avatar User Interface and Removing Avatar Action
                (!isset($_POST['SearchUser'])) ? self::search_username() : self::remove_avatar();
            }
            catch (Exception $e) {
                $GLOBALS['page']->Message($e->getMessage(), "Remove Avatar System", 'id='.$_GET['id'], 'Return');
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
        }

        // Search username
        private function search_username() {

            $GLOBALS['page']->UserInput("Using this panel you can remove a user's avatar. Please note that there's no confirmation page after this one!", // Information
                "Search System", // Title
                array(
                    array(
                        "infoText" => "Search Username",
                        "inputFieldName" => "username",
                        "type" => "input",
                        "inputFieldValue" => ""
                    )
                ), // input fields
                array(
                    "href" => "?id=" . $_GET['id'] ,
                    "submitFieldName" => "SearchUser",
                    "submitFieldText" => "Remove Avatar"
                ), // Submit button
                false // Return link name
            );
        }

        // Main page
        private function remove_avatar() {

            $pic_types = Data::$IMG_TYPES; // Accepted File Type Extensions

            if (!($criminal = $GLOBALS['database']->fetch_data("SELECT `users`.`id`, `users`.`username`
                FROM `users` WHERE `users`.`username` = '" . $_POST['username'] . "' LIMIT 1"))) {
                throw new Exception("There was an error trying to receive the user's data!");
            }
            elseif ($criminal === '0 rows') {
                throw new Exception("The user, " . $_POST['username'] . ", doesn't exist on the site.");
            }

            // Remove the Avatar
            for ($i = 0, $size = count($pic_types); $i < $size; $i++) {
                if (file_exists(Data::$absSvrPath.'/images/avatars/' . $criminal[0]['id'] . $pic_types[$i])) {
                    if (unlink(Data::$absSvrPath.'/images/avatars/' . $criminal[0]['id'] . $pic_types[$i]) === false) {
                        throw new Exception("An error occurred in the avatar removal process. Please try again.");
                    }

                    $GLOBALS['page']->Message("The avatar was removed and replaced with the default avatar.", 'Avatar Removal', 'id=' . $_GET['id']);
                    break;
                }
                elseif ($i === $size - 1) {
                    throw new Exception("An error occurred or the avatar doesn't exist. Please try again.");
                }
            }
        }
    }

    new module();