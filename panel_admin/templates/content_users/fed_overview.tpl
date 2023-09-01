<form id="form1" name="form1" method="post" action="">
  <div align="center">
  <table width="95%" class="table">
    <tr>
      <td colspan="2" class="subHeader">Fed Support Status</td>
    </tr>
    <tr>
      <td colspan="2" style="color:darkred;">Warning: This panel alters a person's fed support subscription!<br>
    Note: When manually updating someone's subscription, do not forget the Subscription ID, 
    otherwise their fed support status will not update automatically.</td>
    </tr>
    <tr>
      <td width="50%" style="text-align:right;">Subscription ID:</td>
      <td width="50%" style="text-align:left;">
        <input name="subscr_id" value="{$fed_data[0]["subscr_id"]}" type="text" class="textfield" id="timeout" size="50" />
      </td>
    </tr>
    
    <tr>
      <td style="text-align:right;">Ends at:</td>
      <td style="text-align:left;">
        {if $fed_data[0]["federal_timer"] == 0}
            No running subscription
        {else}
            {$fed_data[0]["federal_timer"]|date_format:"%d-%m-%y, %H:%M"}
        {/if}
      </td>
    </tr>
    <tr>
      <td  style="text-align:right;">
        <input name="Submit" type="submit" class="button" id="Submit" value="Update subscription ID" />
      </td>
      <td  style="text-align:left;">
        <input name="Submit" type="submit" class="button" id="button" value="Give Fed for a Month" />
      </td>
    </tr>
  </table>
  <a href="?id={$smarty.get.id}&act=mod&uid={$smarty.get.uid}">Return</a>
</div>
</form>