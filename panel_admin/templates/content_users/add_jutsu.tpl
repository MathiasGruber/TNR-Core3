<form id="form1" name="form1" method="post" action="">
<table width="95%" class="table">
  <tr>
    <td colspan="2" class="subHeader">Add Jutsu to User</td>
  </tr>
  <tr>
    <td width="50%" style="text-align:right"><b>User id</b></td>
    <td width="50%" style="text-align:left">{$smarty.get.uid}</td>
  </tr>
  <tr>
    <td style="text-align:right" >Jutsu:</td>
    <td style="text-align:left" >
        <select name="jid" class="listbox" id="select">';
            {foreach $items as $item}
                <option value="{$item['id']}">{$item['name']}</option>
            {/foreach}
        </select>    
    </td>
  </tr>
  <tr>
        <td colspan="2" >
            <input name="Submit" type="submit" class="button" value="Submit" />
        </td>
    </tr>
</table>
</form>