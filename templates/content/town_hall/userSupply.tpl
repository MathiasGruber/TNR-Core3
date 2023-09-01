<div style="border: 1px solid black;position:relative;top:-10px;" width="95%">

	<table align="center" width="100%">
		<tbody>
			<tr>
				<td align="center" class="subHeader">{ucfirst($type)} Supply</td>
			</tr>
	
			<tr>
        {if $type == 'hospital'}
          <td>These people have contributed to the well being of their fellow villagers.</td>
        {else if $type == 'ramen'}
          <td>These people are responsible for that delicious ramen we sell at discount prices!</td>
        {/if}
      </tr>
		</tbody>
	</table >

	<table class="sortable" style="border-left:none;border-right:none" width="100%">
		<thead>
			<tr>
				<td class="tdTop" width="50%">Name</td>
				<td class="tdTop" width="50%">Supply</td>
			</tr>
		</thead>
   
		
		<tbody>
			{foreach $supplyList as $username => $supply}
				<tr>
					<td>
						<a class="showTableLink" href="?id=13&page=profile&name={$username}">
							{$username}
						</a>
					</td>

					<td>
						{$supply}
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>

</div>
<div align="center">
	<a href="?id={$smarty.get.id}">Return</a>
</div>
