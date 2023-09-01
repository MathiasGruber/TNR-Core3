<div align="center">
	<form name="form1" method="post" action="">
  		<table width="95%" class="table" >
		<tr>
            <td class="subHeader">Unban User: {$banned[0]['username']}</td>
		</tr>
		<tr>
			<td style="font-weight:bold;">Reason for unbanning: </td>
		</tr>
		<tr>
			<td>
			<textarea name="override_reason" rows="5" cols="35"></textarea></td>
		</tr>
    	<tr>
			<td>
			    <input type="submit" name="Submit" value="Unban">
			    <input name="time" type="hidden" value="{$smarty.get.time}">
			    <input name="uid" type="hidden" value="{$smarty.get.uid}">
            </td>
		</tr>
  		</table>
	</form>
</div>              