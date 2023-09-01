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

/*Author: Tyler Smith
 *Class: HooksData
 *  this class is used by HooksControl to interact with the cache and database as needed.
 *
 */

class HooksData
{
    function __construct($uid = $_SESSION['uid'])
    {
        $this->uid = $uid;
    }
}