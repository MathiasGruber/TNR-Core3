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

class HomeHelper
{

	//call to the db that gets the cost of a piece of furniture
	public static function getFurnitureCost($fid)
	{
		$cost;
        if(!($cost = $GLOBALS['database']->fetch_data("SELECT `price_type`, `price` FROM `home_furniture` WHERE `id` = '".$fid."'")))
        {
            throw new Exception("sql query to find furniture cost failed.");
        }

        if($cost[0]['price_type'] == 'money')
		    return $cost[0]['price'];
        else if($cost[0]['price_type'] == 'item')
        {
            $temp = explode(';',$cost[0]['price']);
            $result = array();
            foreach($temp as $value)
                $result[] = explode(':',$value);

            return $result;
        }
        else
            throw new Exception('false price type');
	}

    //call to the db that gets the quantity owned of an item from a user's inventory.
    public static function getItemCountFromUserInventory($item_name)
    {
        $count;
        if(!($count = $GLOBALS['database']->fetch_data("SELECT SUM(`stack`) as 'count' FROM `users_inventory` INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid`) WHERE `users_inventory`.`uid` = '".$_SESSION['uid']."' and `items`.`name` = '".str_replace("'","\'",$item_name)."'")))
            throw new Exception('failed to get user item count.');

        return $count[0]['count'];
    }

	//call to the db that subtracts a given value from the users balance
	public static function subtractBalance($value)
	{
		if ($GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `money`= `money` - ".$value." WHERE `uid` = '".$_SESSION['uid']."'") === false)
			{
			    throw new Exception("sql query to charge user for furniture failed: "."UPDATE `users_statistics` SET `money`= `money` - ".$value." WHERE `uid` = '".$_SESSION['uid']."'");
			}
	}

    //call to the db that subtracts a given number of items from a users balance
    public static function subtractItems($item_name, $count_needed)
    {
        if(!($rows = $GLOBALS['database']->fetch_data('SELECT `users_inventory`.`id`,`users_inventory`.`stack`,`users_inventory`.`iid` FROM `users_inventory` INNER JOIN `items` ON (`items`.`id` = `users_inventory`.`iid`) where `users_inventory`.`uid` = '.$_SESSION['uid'].' and `items`.`name` = "'.$item_name.'"')))
            throw new Exception('sql query to get user_inventory rows for sale has failed');

        $current_count = 0;
        $stack = 0;
        $quantity = 0;
        $stack_removed = 0;
        $quantity_removed = 0;
        foreach($rows as $key => $value)
        {
            $stack++;
            $quantity =+ $value['stack'];

            if($current_count != $count_needed)
            {
                if($value['stack'] > ($count_needed - $current_count))
                {
                    //make call to db
                    if ($GLOBALS['database']->execute_query("UPDATE `users_inventory` SET `stack` = (`stack` - ".($count_needed-$current_count).") where `id` = ".$value['id']) === false)
                        throw new Exception('sql query to remove stacks from item during furniture purchase has failed.');

                    $current_count = $count_needed;
                    $quantity_removed = $current_count;
                }
                else
                {
                    $current_count += $value['stack'];
                    //make call to db
                    if ($GLOBALS['database']->execute_query("DELETE FROM `users_inventory` where `id` = ".$value['id']) === false)
                        throw new Exception('sql query to remove item row during furniture purchase has failed.');

                    $stack_removed++;
                    $quantity_removed += $value['stack'];
                }
            }
            else
                break;
        }

        $GLOBALS['Events']->acceptEvent('item_person', array('data'=>'!'.$value['iid'], 'context'=>$value['iid'], 'new'=>$stack-$stack_removed, 'old'=>$stack ));
        $GLOBALS['Events']->acceptEvent('item_quantity_loss', array('context'=>$value['iid'], 'new'=>$quantity-$quantity_removed, 'old'=>$quantity ));
    }

	//call to the db that adds a given value to the users balance
	public static function addBalance($value)
	{
		if ($GLOBALS['database']->execute_query("UPDATE `users_statistics` SET `money`= `money` + ".$value." WHERE `uid` = '".$_SESSION['uid']."'") === false)
			{
			    throw new Exception("sql query to charge user for furniture failed");
			}
	}

	//call to the db that adds a given piece of furniture to the users home inventory
	public function addFurniture($fid)
	{
		if ($GLOBALS['database']->execute_query("INSERT INTO `home_inventory`(`id`, `uid`, `iid`, `fid`, `stack`, `durabilityPoints`, `canRepair`, `timekey`, `trading`, `trade_type`, `tradeValue`, `tempModifier`, `times_used`, `finishProcessing`, `equipped`)
			VALUES (NULL,".$_SESSION['uid'].",NULL,".$fid.",'0','0','no','0',NULL,NULL,NULL,NULL,'0','0','no')") === false)
			{
			    throw new Exception("sql query to buy furniture failed.");
			}

        $count = count(self::getFurnitureKeys($fid));
        $GLOBALS['Events']->acceptEvent('item_furniture', array('data'=>$fid, 'context'=>$fid, 'new'=>$count, 'old'=>$count-1 ));
	}

	//call to the db that gets the keys to the pieces of a type of furniture in a users home inventory
	public static function getFurnitureKeys($fid)
	{
		$current_ids;
                if(!($current_ids = $GLOBALS['database']->fetch_data("SELECT `id`, `fid` FROM `home_inventory` WHERE `uid` = '".$_SESSION['uid']."' AND `in_storage` = 'no' AND `fid` = '".$fid."'")))
                {
                    throw new Exception("sql query to search for current furniture count failed.");
                }
		return $current_ids;
	}

    public static function getUserFurnitureByType($type)
    {
        $furniture;
        if(!($furniture = $GLOBALS['database']->fetch_data("SELECT * FROM `home_inventory` INNER JOIN `home_furniture` ON (`home_furniture`.`id` = `home_inventory`.`fid`) WHERE `uid` = '".$_SESSION['uid']."' AND `storage_type` = '".$type."'")))
        {
            throw new Exception("sql query to search for current furniture count failed.");
        }
		return $furniture;
    }

	//call to the db that gets the furniture table and appends to each row how manny of that piece the user has.
	public static function getFurniture($fid = false)
	{
        if($fid === false)
            $where = "";
        else
            $where = " WHERE `id` = ".$fid;


		$furniture;
        if(!($furniture = $GLOBALS['database']->fetch_data("SELECT * FROM `home_furniture`".$where." ORDER BY `event_furniture` ASC, `storage_type` ASC, `storage` ASC")))
        {
            throw new Exception("sql query to collect home_furniture failed.");
        }

		for($i = 0; $i < count($furniture); $i++)
            {
                //find out how manny pieces of the type of furniture the user has
                $owned;
                if(!($owned = $GLOBALS['database']->fetch_data("SELECT COUNT(*) FROM `home_inventory` WHERE `in_storage` = 'no' AND `uid` = '".$_SESSION['uid']."' AND `fid` = '".$furniture[$i]['id']."'")))
                {
                    throw new Exception("sql query to search for current furniture count failed.");
                }

                //then add that number to the furniture array for that type of furniture.
                $furniture[$i]['owned'] = $owned[0]['COUNT(*)'];
            }

		return $furniture;
	}

	public function deleteFurniture($inv_id, $fid)
	{
		if ($GLOBALS['database']->execute_query("DELETE FROM `home_inventory` WHERE `id` = '".$inv_id."'") === false)
			{
			    throw new Exception("sql query to delete furniture has failed.");
			}

        $count = count(self::getFurnitureKeys($fid));
        $GLOBALS['Events']->acceptEvent('item_furniture', array('data'=>'!'.$fid, 'context'=>$fid, 'old'=>$count-1, 'new'=>$count ));
	}

	public static function getHome()
	{
		$home;
        if(!($home = $GLOBALS['database']->fetch_data("SELECT `homes`.* FROM `users` INNER JOIN `homes` ON (`homes`.`id` = `users`.`apartment` ) WHERE `users`.`id` = '".$_SESSION['uid']."'")))
        {
            throw new Exception("sql query to get home data failed.");
        }

        return $home;
	}

	public static function getHomeInventory()
	{

		$items;
        if(!($items = $GLOBALS['database']->fetch_data("SELECT `items`.*, `items`.`durability` as `max_durability`, `home_inventory`.*, `home_furniture`.`name` AS `furniture_name` FROM `home_inventory` LEFT JOIN `items` ON (`items`.`id` = `home_inventory`.`iid`) LEFT JOIN `home_furniture` ON (`home_furniture`.`id` = `home_inventory`.`fid`) where (`iid` IS NOT NULL OR `in_storage` = 'yes') AND `uid` = '".$_SESSION['uid']."'")))
        {
            throw new Exception('There was an error trying to receive necessary information.');
        }

		return $items;
	}

    public static function getHomeItem($id)
	{

		$items;
        if(!($items = $GLOBALS['database']->fetch_data("SELECT `items`.*, `items`.`durability` as `max_durability`, `home_inventory`.*, `home_furniture`.`name` AS `furniture_name` FROM `home_inventory` LEFT JOIN `items` ON (`items`.`id` = `home_inventory`.`iid`) LEFT JOIN `home_furniture` ON (`home_furniture`.`id` = `home_inventory`.`fid`) where (`iid` IS NOT NULL OR `in_storage` = 'yes') AND `home_inventory`.`id` = '".$id."' AND `uid` = '".$_SESSION['uid']."'")))
        {
            throw new Exception('There was an error trying to receive necessary information.');
        }

		return $items[0];
	}

    public static function getHomeInventoryByType($type)
	{
		$items;
        if(!($items = $GLOBALS['database']->fetch_data("SELECT `items`.*, `items`.`durability` as `max_durability`, `home_inventory`.* FROM `home_inventory` INNER JOIN `items` ON (`items`.`id` = `home_inventory`.`iid`) where `iid` IS NOT NULL AND `uid` = '".$_SESSION['uid']."' and `items`.`content_type` = '".$type."'")))
        {
            throw new Exception('There was an error trying to receive necessary information.');
        }

		return $items;
	}

    public static function transferItemFromHomeToUser($inventoryId)
    {
        $item;
        if(!($item = $GLOBALS['database']->fetch_data("SELECT * FROM `home_inventory` where `id` = '".$inventoryId."'")))
        {
            throw new Exception('There was an error trying to receive necessary information.');
        }

        if(!($home_inventory = $GLOBALS['database']->fetch_data("SELECT * FROM `home_inventory` where `iid` = '".$item[0]['iid']."' AND `uid` = ".$_SESSION['uid'])))
            throw new Exception('There was an error trying to recieve necessary information.');

        if(!($user_inventory = $GLOBALS['database']->fetch_data("SELECT * FROM `users_inventory` where `iid` = '".$item[0]['iid']."' AND `uid` = ".$_SESSION['uid'])))
            throw new Exception('There was an error trying to recieve necessary information.');

        $home_new_stack =
        $home_old_stack =
        $home_new_quantity =
        $home_old_quantity =
        $user_new_stack =
        $user_old_stack =
        $user_new_quantity =
        $user_old_quantity = 0;

        if(is_array($home_inventory))
        {
            foreach($home_inventory as $nick)
            {
                if(isset($nick['stack']) && $nick['stack'] != '')
                {
                    $home_old_stack++;
                    $home_old_quantity += $nick['stack'];
                }
            }
        }

        $home_new_stack = $home_old_stack - 1;
        $home_new_quantity = $home_old_quantity - $item[0]['stack'];
        
        if(is_array($user_inventory))
        {
            foreach($user_inventory as $nack)
            {
                if(isset($nack['stack']) && $nack['stack'] != '')
                {
                    $user_old_stack++;
                    $user_old_quantity += $nack['stack'];
                }
            }
        }

        $user_new_stack = $user_old_stack + 1;
        $user_new_quantity = $user_old_quantity + $item[0]['stack'];

        if(isset($item[0]['in_storage']))
            if($item[0]['in_storage'] == 'no')
            {
                if ($GLOBALS['database']->execute_query("INSERT INTO `users_inventory`(`id`,           `uid`,                  `iid`,                 `stack`,                 `durabilityPoints`,                 `canRepair`,                 `timekey`,    `trading`, `trade_type`, `tradeValue`, `tempModifier`,             `times_used`,                 `finishProcessing`,     `equipped`)
                                                                             VALUES (NULL, '".$item[0]['uid']."', '".$item[0]['iid']."', '".$item[0]['stack']."', '".$item[0]['durabilityPoints']."', '".$item[0]['canRepair']."', '".$item[0]['timekey']."', NULL,     NULL,         NULL,         NULL,           '".$item[0]['times_used']."', '".$item[0]['finishProcessing']."', 'no')") === false ||
                    $GLOBALS['database']->execute_query("DELETE FROM `home_inventory` WHERE `id` = '".$inventoryId."'") === false)
                {
                    throw new Exception('there was an issue with transfering an item to the user');
                }
                $GLOBALS['Events']->acceptEvent('item_person', array('data'=>$item[0]['iid'], 'context'=>$item[0]['iid'], 'old'=>$user_old_stack, 'new'=>$user_new_stack ));
                $GLOBALS['Events']->acceptEvent('item_quantity_gain', array('context'=>$item[0]['iid'], 'old'=>$user_old_quantity, 'new'=>$user_new_quantity ));
                $GLOBALS['Events']->acceptEvent('item_home', array('data'=>'!'.$item[0]['iid'], 'context'=>$item[0]['iid'], 'old'=>$home_old_stack, 'new'=>$home_new_stack ));
            }
            else
                throw new Exception('this item is in storage');
        else
            throw new Exception('item does not exist');
    }
    public static function transferItemFromUserToHome($inventoryId)
    {
        if($GLOBALS['page']->isAsleep && ($GLOBALS['page']->isHome || $GLOBALS['page']->isOutlaw))
        {
            $item;
            if(!($item = $GLOBALS['database']->fetch_data("SELECT * FROM `users_inventory` where `id` = '".$inventoryId."'")))
            {
                throw new Exception('There was an error trying to receive necessary information.');
            }

            if(!($home_inventory = $GLOBALS['database']->fetch_data("SELECT * FROM `home_inventory` where `iid` = '".$item[0]['iid']."' AND `uid` = ".$_SESSION['uid'])))
                throw new Exception('There was an error trying to recieve necessary information.');

            if(!($user_inventory = $GLOBALS['database']->fetch_data("SELECT * FROM `users_inventory` where `iid` = '".$item[0]['iid']."' AND `uid` = ".$_SESSION['uid'])))
                throw new Exception('There was an error trying to recieve necessary information.');

            $home_new_stack =
            $home_old_stack =
            $home_new_quantity =
            $home_old_quantity =
            $user_new_stack =
            $user_old_stack =
            $user_new_quantity =
            $user_old_quantity = 0;

            if(is_array($home_inventory))
            {
                foreach($home_inventory as $nick)
                {
                    if(isset($nick['stack']) && $nick['stack'] != '')
                    {
                        $home_old_stack++;
                        $home_old_quantity += $nick['stack'];
                    }
                }
            }

            $home_new_stack = $home_old_stack + 1;
            $home_new_quantity = $home_old_quantity + $item[0]['stack'];

            if(is_array($user_inventory))
            {
                foreach($user_inventory as $nack)
                {
                    if(isset($nack['stack']) && $nack['stack'] != '')
                    {
                        $user_old_stack++;
                        $user_old_quantity += $nack['stack'];
                    }
                }
            }

            $user_new_stack = $user_old_stack - 1;
            $user_new_quantity = $user_old_quantity - $item[0]['stack'];

            if(isset($item[0]['finishProcessing']))
                if($item[0]['finishProcessing'] == 0)
                {
                    if ($GLOBALS['database']->execute_query("INSERT INTO `home_inventory`(`id`,              `uid`,                 `iid`,        `equipped`,          `stack`,                 `durabilityPoints`,                 `canRepair`,                 `timekey`,     `trading`, `trade_type`, `tradeValue`, `tempModifier`,             `times_used`,                 `finishProcessing`)
                                                                                   VALUES (NULL, '".$item[0]['uid']."', '".$item[0]['iid']."', '".'no'."', '".$item[0]['stack']."', '".$item[0]['durabilityPoints']."', '".$item[0]['canRepair']."', '".$item[0]['timekey']."',      NULL,         NULL,         NULL,           NULL, '".$item[0]['times_used']."', '".$item[0]['finishProcessing']."')") === false ||
                    $GLOBALS['database']->execute_query("DELETE FROM `users_inventory` WHERE `id` = '".$inventoryId."'") === false)
                    {
                        throw new Exception('there was an issue with transferin an item to the users home');
                    }

                    $GLOBALS['Events']->acceptEvent('item_person', array('data'=>'!'.$item[0]['iid'], 'context'=>$item[0]['iid'], 'old'=>$user_old_stack, 'new'=>$user_new_stack ));
                    $GLOBALS['Events']->acceptEvent('item_quantity_loss', array('context'=>$item[0]['iid'], 'old'=>$user_old_quantity, 'new'=>$user_new_quantity ));
                    $GLOBALS['Events']->acceptEvent('item_home', array('data'=>$item[0]['iid'], 'context'=>$item[0]['iid'], 'old'=>$home_old_stack, 'new'=>$home_new_stack ));
                }
                else
                    throw new Exception('this item is still processing');
            else
                throw new Exception('item does not exist');
        }
    }

    public static function removeFromStorageBox($id)
	{
		if ($GLOBALS['database']->execute_query("UPDATE `home_inventory` SET `in_storage` = 'no' WHERE `id` = '".$id."'") === false)
        {
            throw new Exception("sql query to remove form storage box has failed.: "."UPDATE `home_inventory` SET `in_storage` = 'no' WHERE `id` = '".$id."'");
        }
	}

    public static function MoveAllToStorageBox($uid)
    {
        $items;
        if(!($items = $GLOBALS['database']->fetch_data("SELECT COUNT(*) FROM `home_inventory` where `in_storage` = 'no' && `uid` = '".$uid."'")))
        {
            throw new Exception('There was an error trying to receive necessary information.');
        }

        if(isset($items[0]['COUNT(*)']))
        if($items[0]['COUNT(*)'] != 0)
            if ($GLOBALS['database']->execute_query("UPDATE `home_inventory` SET `in_storage` = 'yes' WHERE `uid` = ".$uid) === false)
            {
                throw new Exception("sql query to add to storage box has failed. : "."UPDATE `home_inventory` SET `in_storage` = 'yes' WHERE `uid` = ".$uid);
            }
    }

    public static function getProfessionName($uid)
    {
        $item;
        if(!($item = $GLOBALS['database']->fetch_data("SELECT `name` FROM `users_occupations` INNER JOIN `occupations` ON (`occupations`.`id` = `users_occupations`.`profession`) WHERE `users_occupations`.`userid` = '".$uid."'")))
        {
            throw new Exception('There was an error trying to receive Profession name information.');
        }

        if(isset($item[0]['name']))
            return $item[0]['name'];
        else
            return "NONE";
    }

}