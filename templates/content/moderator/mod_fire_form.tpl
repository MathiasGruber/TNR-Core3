<div align="center" style="display:none;" id="div_fire">
	<table width="95%" border="0" class="table" cellspacing="0" cellpadding="0">
    <tr>
		<td width="100%" align="center" style="border-top:none;" class="subHeader">Fire Moderator</td>
    </tr>
	<tr>
		<td align="center" style="padding:2px;">
		<form name="FireForm" method="post" action="">
			{if $mods != "0 rows"} 
				<select name="fire_user" id="select_fire">
				{for $i = 0 to ($mods|@count)-1}
					<option value="{$mods[$i]['id']}">{$mods[$i]['username']}</option>
				{/for}
				</select>
				<input type="submit" name="Fire_Mod" id="submit_fire" value="Submit">
			{else}
				<div align="center">There is currently no moderators available or hired.<br></div>
			{/if}
		</form></td>
	</tr>
	</table>
</div>