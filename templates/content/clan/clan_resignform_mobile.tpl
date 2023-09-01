<tr>
    <td>
        <headerText>Resign from Clan</headerText>
        <text>By leaving the clan you will no longer have access to any of the clans benefits.</text>
    </td>
</tr>

{if isset($isLeader) && $isLeader == true && isset($hasMembers) && $hasMembers == true }
    <tr>
        <td>
            <text>Leave the Clan leadership to:</text>
            <select name="newLeader">
                {for $i = 1 to 5}
                    {if isset($clan["coleader{$i}_uid_username"]) && $clan["coleader{$i}_uid_username"] != ''}
                        <option value="{$i}">{$clan["coleader{$i}_uid_username"]}</option>
                    {/if}
                {/for}
            </select>
        </td>
    </tr>    
{/if}

<tr><td><submit type="submit" name="Submit" value="Submit"></submit></td></tr>


<tr><td><a href="?id={$smarty.get.id}">Return</a></td></tr>
