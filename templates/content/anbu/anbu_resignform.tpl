<div align="center">
    <form name="form1" method="post" action="">
        <table width="90%" class="table">
            <tr>
                <td class="subHeader">Resign from ANBU</td>
            </tr>
            <tr>
                <td>By leaving the anbu squad you will no longer have access to the ANBU only items. </td>
            </tr>
            <tr>
                <td>
                    {if isset($isLeader) && $isLeader == true && isset($hasMembers) && $hasMembers == true }
                        Leave the ANBU leadership to: <br>
                        <select name="newLeader">
                            {for $i = 1 to 9}
                                {if isset($squad["member_{$i}_uid_username"]) && $squad["member_{$i}_uid_username"] != ''}
                                    <option value="{$i}">{$squad["member_{$i}_uid_username"]}</option>
                                {/if}
                            {/for}
                        </select>
                    {/if}
                </td>
            </tr>

            <tr>
                <td>
                    <p><input type="submit" name="Submit" style="height:22px;" value="Submit"></p>
                </td>
            </tr>
        </table>
    </form>
</div>