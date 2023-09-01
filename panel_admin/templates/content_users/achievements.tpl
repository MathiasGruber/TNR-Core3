<form id="form1" name="form1" method="post" action="">
    <div align="center">
        <table border="0" cellpadding="0" cellspacing="0" class="table" width="95%">
            <tr>
                <td align="center" colspan="3" style="border-top:none;" class="subHeader">Admin Achievements</td>
            </tr>
            <tr>
                <td width="33%" class="tableColumns tdBorder" ><b>Achievement ID</b></td>
                <td width="33%" class="tableColumns tdBorder" ><b>Name</b></td>
                <td width="33%" class="tableColumns tdBorder" ><b>Active/Inactive</b></td>
            </tr>

            {if $adminTasks}
                {foreach $adminTasks as $entry}
                    {strip}
                        <tr class="{cycle values="row1,row2"}">
                            <td>{$entry['id']}</td>
                            <td>{$entry['name']}</td>
                            <td>
                                <select name="achievementID:::{$entry["id"]}">

                                    {if $userTasks[ $entry.id ]  }
                                        <option selected="selected" value="yes">yes</option>
                                        <option value="no">no</option>
                                    {else}
                                        <option selected="selected" value="no">no</option>
                                        <option value="yes">yes</option>
                                    {/if}
                                </select>
                            </td>

                        </tr>
                    {/strip}
                {/foreach}
            {else}
                <tr>
                    <td colspan="3">No admin achievements found</td>
                </tr>
            {/if}  
            <tr>
                <td colspan="3" class="tdBottom">
                    <input name="Submit" type="submit" class="button" value="Submit" />
                </td>
            </tr>

        </table>
    </div>
</form>
