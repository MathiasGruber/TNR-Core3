<tr>
    <td>
        <headerText>Resign from ANBU</headerText>
        <text>By leaving the anbu squad you will no longer have access to the ANBU only items.</text>
    </td>
</tr>

{if isset($isLeader) && $isLeader == true && isset($hasMembers) && $hasMembers == true }
     <tr>
        <td>
            <text>Leave the ANBU leadership to:</text>
            <select name="newLeader">
                {for $i = 1 to 9}
                    {if isset($squad["member_{$i}_uid_username"]) && $squad["member_{$i}_uid_username"] != ''}
                        <option value="{$i}">{$squad["member_{$i}_uid_username"]}</option>
                    {/if}
                {/for}
            </select>
        </td>
    </tr> 
{/if}

<tr><td><submit type="submit" name="Submit" value="Submit"></submit></td></tr>


<tr><td><a href="?id={$smarty.get.id}">Return</a></td></tr>
