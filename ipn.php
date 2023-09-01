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

// let's see all errors
error_reporting(E_ALL);
ini_set('display_errors', 'On');

// Server File Path
require_once($_SERVER['DOCUMENT_ROOT'].'/global_libs/Site/data.class.php');

require('./global_libs/Site/database.class.php');
$GLOBALS['database'] = new database;

require('./libs/payment.inc.php');
$payment = new payment();   