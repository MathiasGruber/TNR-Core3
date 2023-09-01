<div align="center">
	<table width="95%" class="table">
	<tr>
		<td colspan="4" class="subHeader">Warnings Issued By {$mod_data[0]['username']}</td>
	</tr>
	{if $banned != '0 rows'} 
		<tr>
			<td width="20%" style="text-align:left;border-bottom:1px solid #000000;font-weight:bold;">Username:</td>
			<td width="20%" style="text-align:left;border-bottom:1px solid #000000;font-weight:bold;">Time:</td>
			<td width="40%" style="text-align:left;border-bottom:1px solid #000000;font-weight:bold;">Reason</td>
			<td width="20%" style="text-align:left;border-bottom:1px solid #000000;font-weight:bold;">&nbsp;</td>
		</tr>
		{for $i = 0 to ($banned|@count)-1}
			<tr class="row{($i % 2) + 1}">
				<td style="text-align:left;">{$temp[$i][0]['username']}</td>
				<td style="text-align:left;">{$banned[$i]['time']|date_format:"%d-%m-%Y %H:%M:%S"}</td>
				<td style="text-align:left;">{$banned[$i]['reason']}</td>
				<td style="text-align:left;">
				    <a href="?id={$smarty.get.id}&amp;act=details&amp;time={$banned[$i]['time']}&amp;uid={$banned[$i]['uid']}">Details</a>
                </td>
			</tr>
		{/for}
	{else} 
		<tr>
			<td colspan="4" style="border-top:1px solid #000000;">This mod has not issued any warnings yet.</td>
		</tr>
	{/if}
	<tr>
		<td colspan="4" style="border-top:1px solid #000000;">
		<a href="?id={$smarty.get.id}">Return</a></td>
	</tr>
	</table>
</div>