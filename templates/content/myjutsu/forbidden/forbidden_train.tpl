{if $scroll != '0 rows'}
	{if $jutsu != '0 rows' && ($jutsu[0]['jutsu_type'] == 'forbidden')}
		{if $new_jutsu}
			{if ($jutsu[0]['bloodline'] == $user[0]['bloodline']) || $jutsu[0]['bloodline'] == null}
				{if $jutsu[0]['required_rank'] <= $user[0]['rankid']}
					{if ($user[0]['max_sta'] >= $jutsu[0]['price'] + 100) && ($user[0]['max_cha'] >= $jutsu[0]['price'] + 100)}
						<div align="center" style="padding:15px;">The mysterious man helps you learn {$jutsu[0]['name']|stripslashes}.<br>
							Training this jutsu has cost you: <br>
							<b>{$jutsu[0]['price']}</b> Maximum Chakra<br>
							<b>{$jutsu[0]['price']}</b> Maximum Stamina <br> 
							On top of the normal chakra / stamina cost.<br>
							<a href="?id={$smarty.get.id}">Return</a>
						</div>
					{else}
						<div align="center" style="padding:15px;">You are not strong enough to endure the training for this jutsu, come back when you are stronger.<br>
						<a href="?id={$smarty.get.id}">Return</a><div>
					{/if}
				{else}
					{if $jutsu[0]['required_rank'] == 2}
						{$rank = 'Genin'}
					{elseif $jutsu[0]['required_rank'] == 3}
						{$rank = 'Chuunin'}
					{elseif $jutsu[0]['required_rank'] == 4}
						{$rank = 'Jounin'}
					{elseif $jutsu[0]['required_rank'] == 5}
						{$rank = 'Elite Jounin'}
					{elseif $jutsu[0]['required_rank'] == 6}
						{$rank = 'Commander'}
					{else}
						{$rank = 'a Genius'}
					{/if}
					<div align="center">You must be at least {$rank} to train the jutsu associated with this scroll.<br>
					<a href="?id={$smarty.get.id}">Return</a></div>
				{/if}
			{else}
				<div align="center" style="padding:5px;">Unfortunately, the jutsu associated with this jutsu scroll is a bloodline jutsu of a different bloodline than yours.<br>
				<a href="?id={$smarty.get.id}">Return</a></div>
			{/if}
		{else}
			<div align="center" style="padding:5px;">You already know this forbidden jutsu, {$jutsu[0]['name']}.<br>
			<a href="?id={$smarty.get.id}">Return</a></div>
		{/if}
	{else}
		<div align="center" style="padding:5px;">The training could not be completed because the jutsu associated with this scroll does not exist or is not a forbidden jutsu.<br>Please contact the content administrator with this error, and the name of the item.<br>
		<a href="?id={$smarty.get.id}">Return</a></div>
	{/if}
{else}
	<div align="center">The training could not be completed for one of the following reasons<br>- This item is not a jutsu scroll<br>- You do not own this item<br>- This item does not exist<br><br>
	<a href="?id={$smarty.get.id}">Return</a></div>
{/if}