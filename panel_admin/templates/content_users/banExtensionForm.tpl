<form id="form1" name="form1" method="post" action="">
  <div align="center">
  <table width="95%" class="table">
    <tr>
      <td colspan="2" class="subHeader">Ban Extension</td>
    </tr>
    <tr>
        <td width="50%" style="text-align:right;">Reason:</td>
        <td width="50%" style="text-align:left;">
            <input name="reason" type="text" id="reason" size="35">
        </td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold;">
            The message below will be stored and shown to the user: 
        </td>
    </tr><tr>
        <td colspan="2" style="padding:2px;">Message:</td>
    </tr><tr>
        <td colspan="2" style="padding:2px;">
            <textarea name="message" rows="8" style="width:95%;"></textarea>
        </td>
    </tr>
    <tr>
      <td style="padding:2px;"><select name="length" class="listbox" id="length">
        <option value="1">1 day</option>
        <option value="2">1 week</option>
        <option value="3">2 weeks</option>
        <option value="4">1 month</option>
        <option value="5">2 months</option>
        <option value="6">6 months</option>
      </select>
      </td>
      <td align="center"><span style="padding:2px;">
        <input name="Submit" type="submit" class="button" id="Submit" value="Submit" />
      </span>
      </td>
    </tr>
  </table>
  <a href="?id={$smarty.get.id}&act=mod&uid={$smarty.get.uid}&type=ban">Return</a>
</div>
</form>