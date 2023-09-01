<div align="center">
    <form name="form1" method="post" action="">
        <table width="90%" class="table">
            <tr>
                <td class="subHeader">Resign from Clan</td>
            </tr>
            <tr>
                <td>By leaving the clan you will no longer have access to any of the clans benefits. </td>
            </tr>
            <tr>
                <td>
                    {if isset($isLeader) && $isLeader == true && isset($hasMembers) && $hasMembers == true }
                        Leave the Clan leadership to: <br>
                        <select name="newLeader">
                            {for $i = 1 to 5}
                                {if isset($clan["coleader{$i}_uid_username"]) && $clan["coleader{$i}_uid_username"] != ''}
                                    <option value="{$i}">{$clan["coleader{$i}_uid_username"]}</option>
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