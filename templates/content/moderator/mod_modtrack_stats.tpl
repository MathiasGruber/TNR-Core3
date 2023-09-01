<div align="center">
	<form name="form1" method="post" action=""><br>
		<table width="90%" border="0" cellspacing="0" cellpadding="0" class="table">
		<tr>
			<td colspan="4" align="center" class="subHeader" style="border-top:none;" >Moderator Log</td>
		</tr>
		<tr>
			<td width="31%" align="center">Moderator name:</td>
			<td colspan="3" align="center">{$mod_data[0]['username']}</td>
		</tr>
		<tr>
			<td align="center">Current Village:</td>
			<td colspan="3" align="center">{$mod_data[0]['village']}</td>
		</tr>
		<tr>
			<td colspan="4" align="center" class="subHeader">Moderator Statistics</td>
		</tr>
		<tr>
			<td align="center">&nbsp;</td>
			<td width="23%" align="center" style="font-weight:bold;">Total</td>
			<td width="23%" align="center" style="font-weight:bold;">Last Week</td>
			<td width="23%" align="center" style="font-weight:bold;">Last Month</td>
		</tr>
		<tr>
			<td align="center" style="font-weight:bold;">Reports Handled</td>
			<td align="center">{$report_data[0]['reports_ever']}</td>
			<td align="center">{$week_report_data[0]['reports_ever']}</td>
			<td align="center">{$month_report_data[0]['reports_ever']}</td>
		</tr>
		<tr>
			<td align="center" style="font-weight:bold;">Bans</td>
			<td align="center">{$count_data[0]['bans_ever']}</td>
			<td align="center">{$week_count_data[0]['bans_ever']}</td>
			<td align="center">{$month_count_data[0]['bans_ever']}</td>
		</tr>
		<tr>
			<td align="center" style="font-weight:bold;">Tavern Bans</td>
			<td align="center">{$count_data[0]['tbans_ever']}</td>
			<td align="center">{$week_count_data[0]['tbans_ever']}</td>
			<td align="center">{$month_count_data[0]['tbans_ever']}</td>
		</tr>
		<tr>
			<td align="center" style="font-weight:bold;">Warnings</td>
			<td align="center">{$count_data[0]['warning_ever']}</td>
			<td align="center">{$week_count_data[0]['warning_ever']}</td>
			<td align="center">{$month_count_data[0]['warning_ever']}</td>
		</tr>
		<tr>
			<td align="center" colspan="4" class="subHeader">&nbsp;</td>
		</tr>
		<tr>
			<td align="center">&nbsp;</td>
			<td align="center"><a href="?id={$smarty.get.id}&amp;act=trackbans&amp;mid={$smarty.post.moderator_track}">List Bans</a></td>
			<td align="center"><a href="?id={$smarty.get.id}&amp;act=trackwarnings&amp;mid={$smarty.post.moderator_track}">List Warnings</a></td>
			<td align="center"><a href="?id={$smarty.get.id}&amp;act=trackreports&amp;mid={$smarty.post.moderator_track}">List Handled Reports</a></td>
		</tr>
		</table>
	</form>
</div> 
