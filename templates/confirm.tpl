<div align="center">
  <table width="95%" class="table" >
    <tr>
      <td align="center" style="border-top:none;" class="subHeader"> {$subHeader} </td>
    </tr>
    <tr>
      <td align="center">
        {$msg}<br>
        <form id="form1" name="form1" method="post" action="">
        {if $storage_name_1 != 'n/a' && $storave_value_1 != 'n/a'}
          <input type="hidden" name="{$storage_name_1}" value="{$storage_value_1}">
            {if $storage_name_2 != 'n/a' && $storave_value_2 != 'n/a'}
              <input type="hidden" name="{$storage_name_2}" value="{$storage_value_2}">
            {/if}
        {/if}
        <input name="Submit" type="submit" class="input_submit_btn" id="Submit" value="{$returnLink}" style="line-height:15px;margin:10px;" />
        </form>
        </td>
    </tr>
  </table><br>
</div>