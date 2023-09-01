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

if (isset($_POST['test']) && ($_POST['test'] === "user")) {
    $result = username_check($_POST['username'], true);
    echo '<span class="' . $result[2] . '">' . $result[0] . '</div>';
} elseif (isset($_POST['test']) && ($_POST['test'] === "mail")) {
    $result = email_check($_POST['mail'], true);
    echo '<span class="' . $result[2] . '">' . $result[0] . '</div>';
} elseif (isset($_POST['test']) && ($_POST['test'] === "vmail")) {
    $result = email_check_confirm($_POST['mail_v'], $_POST['mail']);
    echo '<span class="' . $result[2] . '">' . $result[0] . '</div>';
} elseif (isset($_POST['test']) && ($_POST['test'] === "pass")) {
    $result = password_check($_POST['pass']);
    echo '<span class="' . $result[2] . '">' . $result[0] . '</div>';
} elseif (isset($_POST['test']) && ($_POST['test'] === "vpass")) {
    $result = password_check_confirm($_POST['pass_v'], $_POST['pass']);
    echo '<span class="' . $result[2] . '">' . $result[0] . '</div>';
}

// Validate username
function username_check($username, $callDB = false) {
    $blacklist = array('\'', 'admin', 'moderator', 'sex', 'pussy', 'shit', 'penis', 'jezus',
        'cock', 'niggah', 's3x', 'c0ck', 'horny', 'h0rny', 'gay', 'fuck', 'butthole',
        'butth0le', 'hentai', 'terriator', 'kanu', 'aeterno', 'siteowner', 'serverowner',
        'gameowner', 'hentai', 'pervert', 'Syndicate','Akedemi','Kyoushi','Susanowo',
        'Kammu','Genmei','Kinmei','Oujin','Kan Yamato','Ryujin','Ao Kuang','Ao Qin',
        'Ao Run','Ao Shun','Iza','Byakko','Emmaho','Akedemi Kyoushi','Volcan',
        'Hayay Ji','Kumanai','Naino','Owata','Ora','Hissar','Hachi','Kegareki',
        'Konoki', 'Samui', 'Silence', 'Shroud', 'Shine', 'Syndicate');

    $username = trim($username);
    $pass = 0;
    $result = "";

    if ($username !== "") {
        $username = str_replace(" ", "", $username);
        if (strlen($username) >= 3 && strlen($username) <= 20) {
            if (preg_match('/^[\w-]+$/', $username) && ctype_alnum($username)) {
                $continue = true;
                foreach ($blacklist as $string) {
                    if (stristr($username, $string)) {
                        $continue = false;
                        break;
                    }
                }
                if ($continue === true) {
                    // Essential db and cache connectors
                    if ($callDB === true) {
                        require('../global_libs/Site/database.class.php');
                        $GLOBALS['database'] = new database;
                    }

                    if (isset($GLOBALS['database'])) {
                        // Check all servers
                        $users = $GLOBALS['database']->fetch_data("SELECT `username` FROM `users` WHERE `username` LIKE '" . $username . "' LIMIT 1");
                        if (
                            $users === "0 rows" || 
                            ( isset($GLOBALS['userdata'][0]['username']) && 
                              strcasecmp( $GLOBALS['userdata'][0]['username'] , $username) == 0 )
                        ){
                            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
                                // HTTP_X_FORWARDED_FOR can result in comma-separated IP address list
                                $ips = explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]);

                                // Ideally, the first one is the client and the rest is the proxies travelled
                                // However, we check to see if some variables are not empty and hopefully 
                                // return the first one it sees which should be the client
                                for ($i = 0; $i < count($ips); $i++) {
                                    if (trim($ips[$i]) != '') {
                                        $ip = trim($ips[$i]);
                                        break;
                                    }
                                }
                            } else {
                                // REMOTE_ADDR can result in comma-separated IP address list
                                $ips = explode(",", $GLOBALS['user']->real_ip_address() );

                                // Ideally, the first one is the client and the rest is the proxies travelled
                                // However, we check to see if some variables are not empty and hopefully 
                                // return the first one it sees which should be the client
                                for ($i = 0; $i < count($ips); $i++) {
                                    if (trim($ips[$i]) != '') {
                                        $ip = trim($ips[$i]);
                                        break;
                                    }
                                }
                            }

                            $ip_check = $GLOBALS['database']->fetch_data("SELECT COUNT(`id`) AS `count` FROM `users` WHERE `join_ip` = '" . $ip . "'");

                            if ($ip_check[0]['count'] < 15) {
                                $mods = $GLOBALS['database']->fetch_data("SELECT `username` FROM `users_statistics`, `users` WHERE `id` = `uid` AND (`user_rank` = 'Admin' OR `user_rank` = 'Moderator' OR `user_rank` = 'Supermod' OR `user_rank` = 'Eventmod' OR `user_rank` = 'ContentAdmin')");

                                for ($i = 0; $i < count($mods); $i++) {
                                    $test = levenshtein($mods[$i]['username'], $username);
                                    if ($test < 3) {
                                        $proceed = false;
                                        break;
                                    }
                                    $proceed = true;
                                }
                                if ($proceed == true) {
                                    $result = 'Accepted!';
                                    $color = "green";
                                    $pass = 1;
                                } else {
                                    $result = 'Too similar to staff member "' . $mods[$i]['username'] . '"';
                                    $color = "red";
                                }
                            } else {
                                $result = $ip_check[0]['count'].' other users are already using this IP: '.$ip ;
                                $color = "red";
                            }
                        } else {
                            $result = 'Username '.$username.' already picked by someone else!';
                            $color = "red";
                        }
                    } else {
                        $result = 'Database connection could not be established.';
                        $color = "red";
                    }
                } else {
                    $result = 'May not contain the word: "' . $string . '"';
                    $color = "red";
                }
            } else {
                $result = 'Contains illegal characters!';
                $color = "red";
            }
        } else {
            $result = 'Must be between 3 and 20 characters long!';
            $color = "red";
        }
    } else {
        $result = 'You must pick a username!';
        $color = "red";
    }
    return array($result, $pass, $color);
}

// Validate email        
function email_check($email, $callDB = false) {
    $email = trim($email);
    $pass = 0;
    $result = "";
    if ($email !== "") {
        if (preg_match('/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+$/', $email) && $email != '') {
            if (!strstr($email, 'mailinator') && !strstr($email, 'whyspam') && !strstr($email, 'WHYSPAM') && !strstr($email, 'sogetthis') && !strstr($email, 'binkmail') && !strstr($email, 'amilegit') && !strstr($email, 'zippymail')) {
                // Essential db and cache connectors
                if ($callDB === true) {
                    require('../global_libs/Site/database.class.php');
                    $GLOBALS['database'] = new database;
                }

                if (isset($GLOBALS['database'])) {
                    $mail = $GLOBALS['database']->fetch_data("
                        SELECT 
                            COUNT(`id`) AS `count` 
                        FROM `users` 
                            WHERE 
                                LOWER(
                                    CONCAT(
                                        REPLACE(
                                            SUBSTRING(
                                                `mail`,
                                                1,
                                                (LOCATE('@',`mail`) - 1)
                                            ),
                                            '.',
                                            ''
                                        ),
                                        '@',
                                        SUBSTRING(
                                            `mail`,
                                            (LOCATE('@',`mail`) + 1)
                                        )
                                    )
                                ) = 
                                LOWER(
                                    CONCAT(
                                        REPLACE(
                                            SUBSTRING(
                                                '$email',
                                                1,
                                                (LOCATE('@','$email') - 1)
                                            ),
                                            '.',
                                            ''
                                        ),
                                        '@',
                                        SUBSTRING(
                                            '$email',
                                            (LOCATE('@','$email') + 1)
                                        )
                                    )
                                )
                     ");

                    if ($mail[0]['count'] == 0) {
                        $result = 'Accepted!';
                        $color = "green";
                        $pass = 1;
                    } else {
                        $result = 'Already in database.';
                        $color = "red";
                    }
                } else {
                    $result = 'Could not connect to database.';
                    $color = "red";
                }
            } else {
                $result = 'Email provider is banned.';
                $color = "red";
            }
        } else {
            $result = 'Not recognized as email.';
            $color = "red";
        }
    } else {
        $result = 'Required field!';
        $color = "red";
    }
    return array($result, $pass, $color);
}

// Confirm email        
function email_check_confirm($data, $mail) {
    $pass = 0;
    $result = "";
    if ($data !== "") {
        if (trim($mail) === trim($data)) {
            $result = 'Accepted!';
            $color = "green";
            $pass = 1;
        } else {
            $result = 'Not matching!';
            $color = "red";
        }
    } else {
        $result = 'Required field!';
        $color = "red";
    }
    return array($result, $pass, $color);
}

// Password check
function password_check($password) {
    $pass = 0;
    $result = "";
    if ($password !== "") {
        // Strength check
        $strength = 0;
        $password = str_replace("'", "", $password);
        $password = str_replace('"', "", $password);
        $patterns = array('#[a-z]#', '#[A-Z]#', '#[0-9]#', '/[�!"�$%^&*()`{}\[\]:@~;\'#<>?,.\/\\-=_+\|]/');
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $password, $matches)) {
                $strength++;
            }
        }
        switch ($strength) {
            case 1: $color = "red";
                $result = 'Weak Password. Not accepted!';
                $pass = 0;
                break;
            case 2: $color = "orange";
                $result = 'Weak Password';
                $pass = 1;
                break;
            case 3: $color = "orange";
                $result = 'Acceptable Password';
                $pass = 1;
                break;
            case 4: $color = "green";
                $result = 'Strong Password!';
                $pass = 1;
                break;
            default: $color = "red";
                $result = 'Not Acceptable!';
                $pass = 0;
                break;
        }
    } else {
        $color = "red";
        $result = 'Required field!';
    }
    return array($result, $pass, $color);
}

// Password check
function password_check_confirm($data, $password) {
    $pass = 0;
    $result = "";
    if ($data !== "") {
        if (trim($password) === trim($data)) {
            $result = 'Accepted!';
            $color = "green";
            $pass = 1;
        } else {
            $result = 'Not matching!';
            $color = "red";
        }
    } else {
        $result = 'Required field!';
        $color = "red";
    }
    return array($result, $pass, $color);
}

// Gender check
function gender_check($gender) {
    if ($gender !== "Male" && $gender !== "Female") {
        switch (random_int(1, 2)) {
            case 1: $gender = "Male";
                break;
            case 2: $gender = "Female";
                break;
            default: $gender = "Male";
                break;
        }
    }
    return $gender;
}

// Village check
function village_check($village) {
    $villages = $GLOBALS['database']->fetch_data("SELECT `name` FROM `villages`");
    $pass = false;
    foreach ($villages as $iVillage) {
        if ($iVillage['name'] === $village) {
            $pass = true;
        }
    }
    if ($pass === true) {
        return $village;
    } else {
        return "Konoki";
    }
}