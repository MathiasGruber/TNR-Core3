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
/*
*							Forbidden mountain
*		Allows users to train forbidden jutsu, IF they have the scroll
*		only needed for the first level of the jutsu, subsequent levels can be
*		trained "normally".
*/

class forbidden_jutsu
{
	private $user_data;

	public function __construct()
	{
        $GLOBALS['database']->get_lock(__METHOD__,$_SESSION['uid'],__METHOD__);

            $this->getUserData();
            if(!isset($_POST['scroll_id']))
            {
                    $this->start_screen();
            }
            else
            {
                    $this->jutsu_train();
            }

            if($GLOBALS['database']->release_lock(__METHOD__,$_SESSION['uid'],__METHOD__) === false) {
                throw new Exception('There was an issue releasing the lock!');
            }
	}

	private function getUserData()
	{
		$this->user_data = $GLOBALS['database']->fetch_data("SELECT * FROM `users` WHERE `id` = '".$_SESSION['uid']."'");
	}

	//		Select a jutsu to train
	private function start_screen()
	{
		$items = $this->find_jutsu();
		$GLOBALS['template']->assign('item', $items);
		$GLOBALS['template']->assign('contentLoad', './templates/content/myjutsu/forbidden/forbidden_main.tpl');
	}

	//		Train the jutsu
	private function jutsu_train()
	{
		$scroll = $GLOBALS['database']->fetch_data("SELECT * FROM `items`,`users_inventory` WHERE `items`.`id` = '".$_POST['scroll_id']."' AND `items`.`id` = `users_inventory`.`iid` AND `users_inventory`.`uid` = '".$_SESSION['uid']."' AND `use` LIKE 'JUT:%' LIMIT 1");
		$GLOBALS['template']->assign('scroll', $scroll);

		if($scroll != '0 rows')
		{
			$temp = explode(':',$scroll[0]['use']);
			$jutsu = $GLOBALS['database']->fetch_data("SELECT * FROM `jutsu` WHERE `id` = '".$temp[1]."' LIMIT 1");
			$GLOBALS['template']->assign('jutsu', $jutsu);
			$GLOBALS['template']->assign('user', $this->user_data);

			if($jutsu != '0 rows' && $jutsu[0]['jutsu_type'] == 'forbidden')
			{
                if($GLOBALS['database']->fetch_data("SELECT * FROM `users_jutsu` WHERE `uid` = '".$_SESSION['uid']."' AND `jid` = '".$jutsu[0]['id']."' LIMIT 1") == '0 rows')
				{
					$GLOBALS['template']->assign('new_jutsu', true);
				    if($jutsu[0]['bloodline'] == $this->user_data[0]['bloodline'] || $jutsu[0]['bloodline'] == null)
					{
					    if($jutsu[0]['required_rank'] <= $this->user_data[0]['rankid'])
						{
						    /*				Do the actual training			*/
						    if($this->user_data[0]['max_sta'] >= $jutsu[0]['price'] + 100 && $this->user_data[0]['max_cha'] >= $jutsu[0]['price'] + 100)
							{
							    if($this->user_data[0]['cur_cha'] < $this->user_data[0]['max_cha'] - $jutsu[0]['price'])
								{
								    $new_cha = $this->user_data[0]['cur_cha'] - 90;
							    }
							    else
								{
								    $new_cha = $this->user_data[0]['max_cha'] - $jutsu[0]['price'] - 90;
							    }
							    if($this->user_data[0]['cur_sta'] < $this->user_data[0]['max_sta'] - $jutsu[0]['price'])
								{
								    $new_sta = $this->user_data[0]['cur_sta'] - 90;
							    }
							    else
								{
								    $new_sta = $this->user_data[0]['max_sta'] - $jutsu[0]['price'] - 90;
							    }

							    $GLOBALS['database']->execute_query("UPDATE `users` SET `max_sta` = `max_sta` - '".$jutsu[0]['price']."', `max_cha` = `max_cha` - '".$jutsu[0]['price']."' , `cur_cha` = ".$new_cha.", `cur_sta` = '".$new_sta."' WHERE `id` = '".$_SESSION['uid']."' LIMIT 1");

                                $GLOBALS['Events']->acceptEvent('stats_max_sta', array('new'=>$this->user_data[0]['max_sta'] - $jutsu[0]['price'], 'old'=>$this->user_data[0]['max_sta'] ));
                                $GLOBALS['Events']->acceptEvent('stats_max_cha', array('new'=>$this->user_data[0]['max_cha'] - $jutsu[0]['price'], 'old'=>$this->user_data[0]['max_cha'] ));
                                $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>$newsta, 'old'=>$this->user_data[0]['cur_sta'] ));
                                $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=>$newcha, 'old'=>$this->user_data[0]['cur_cha'] ));

								$GLOBALS['Events']->acceptEvent('jutsu_learned', array('data'=>$jutsu[0]['id'], 'context'=>$jutsu[0]['id']));
								$GLOBALS['Events']->acceptEvent('jutsu_level',   array('new'=>1, 'old'=>0, 'data'=>$jutsu[0]['id'], 'context'=>$jutsu[0]['id']));


							    $GLOBALS['database']->execute_query("INSERT INTO `users_jutsu` ( `uid` , `jid` , `level` , `exp` , `tagged` )VALUES ('".$_SESSION['uid']."', '".$jutsu[0]['id']."', '1', '0', 'no');");
                                $items = $GLOBALS['database']->fetch_data("SELECT * FROM `users_inventory` WHERE `iid` = ".$_POST['scroll_id']." AND `uid` = ".$_SESSION['uid']);
							    $GLOBALS['database']->execute_query("DELETE FROM `users_inventory` WHERE `iid` = '".$_POST['scroll_id']."' AND `uid` = '".$_SESSION['uid']."' LIMIT 1");

                                $quantity = 0;
                                $stack = 0;
                                foreach($items as $item)
                                {
                                    if(isset($item['stack']) && $item['stack'] != '')
                                    {
                                        $stack++;
                                        $quantity += $item['stack'];
                                    }
                                }

                                $GLOBALS['Events']->acceptEvent('item_person', array('data'=>'!'.$_POST['scroll_id'], 'context'=>$_POST['scroll_id'], 'new'=>$stack-1 ,'old'=>$stack ));
                                $GLOBALS['Events']->acceptEvent('item_quantity_loss', array('context'=>$_POST['scroll_id'], 'new'=>$quantity-1 ,'old'=>$quantity ));
							}
					    }
				    }
                }
                else
				{
					$GLOBALS['template']->assign('new_jutsu', false);
                }
			}
		}
		$GLOBALS['template']->assign('contentLoad', './templates/content/myjutsu/forbidden/forbidden_train.tpl');
	}

	private function jutsu_handler($jutsu, $newcha, $newsta)
	{
		$GLOBALS['database']->execute_query("UPDATE `users` SET `max_sta` = `max_sta` - '".$jutsu['price']."', `max_cha` = `max_cha` - '".$jutsu['price']."' , `cur_cha` = ".$newcha.", `cur_sta` = '".$newsta."' WHERE `id` = '".$_SESSION['uid']."' LIMIT 1");

        $GLOBALS['Events']->acceptEvent('stats_max_sta', array('new'=>$this->user_data[0]['max_sta'] - $jutsu[0]['price'], 'old'=>$this->user_data[0]['max_sta'] ));
        $GLOBALS['Events']->acceptEvent('stats_max_cha', array('new'=>$this->user_data[0]['max_cha'] - $jutsu[0]['price'], 'old'=>$this->user_data[0]['max_cha'] ));
        $GLOBALS['Events']->acceptEvent('stats_cur_sta', array('new'=>$newsta, 'old'=>$this->user_data[0]['cur_sta'] ));
        $GLOBALS['Events']->acceptEvent('stats_cur_cha', array('new'=>$newcha, 'old'=>$this->user_data[0]['cur_cha'] ));

		$GLOBALS['Events']->acceptEvent('jutsu_learned', array('data'=>$jutsu['id'], 'context'=>$jutsu['id']));
		$GLOBALS['Events']->acceptEvent('jutsu_level',   array('new'=>1, 'old'=>0, 'data'=>$jutsu['id'], 'context'=>$jutsu['id']));

		$GLOBALS['database']->execute_query("INSERT INTO `users_jutsu` ( `uid` , `jid` , `level` , `exp` , `tagged` )VALUES ('".$_SESSION['uid']."', '".$jutsu['id']."', '1', '0', 'no');");
        $items = $GLOBALS['database']->fetch_data("SELECT * FROM `users_inventory` WHERE `iid` = ".$_POST['scroll_id']." AND `uid` = ".$_SESSION['uid']);
		$GLOBALS['database']->execute_query("DELETE FROM `users_inventory` WHERE `iid` = '".$_POST['scroll_id']."' AND `uid` = '".$_SESSION['uid']."' LIMIT 1");

        $quantity = 0;
        $stack = 0;
        foreach($items as $item)
        {
            if(isset($item['stack']) && $item['stack'] != '')
            {
                $stack++;
                $quantity += $item['stack'];
            }
        }

        $GLOBALS['Events']->acceptEvent('item_person', array('data'=>'!'.$_POST['scroll_id'], 'context'=>$_POST['scroll_id'], 'new'=>$stack-1 ,'old'=>$stack ));
        $GLOBALS['Events']->acceptEvent('item_quantity_loss', array('context'=>$_POST['scroll_id'], 'new'=>$quantity-1 ,'old'=>$quantity ));

	}
	//		Return a list of forbidden jutsu the user has a scroll for
	private function find_jutsu()
	{
		$items = $GLOBALS['database']->fetch_data("SELECT DISTINCT `items`.`name`, `items`.`id` FROM `users_inventory`,`items` WHERE `items`.`id` = `users_inventory`.`iid` AND `users_inventory`.`uid` = '".$_SESSION['uid']."' AND `use` LIKE 'JUT:%' AND `trading` IS NULL ORDER BY `items`.`id` ASC");
		return $items;
	}
}
new forbidden_jutsu();