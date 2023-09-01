<tr>
    <td>
        <headerText screenWidth="true">Kick Member</headerText>
        <select name="memberID">
            {for $i = 1 to 9}
                {if isset($squad["member_{$i}_uid_username"]) && $squad["member_{$i}_uid_username"] != ''}
                    <option value="{$i}">{$squad["member_{$i}_uid_username"]}</option>
                {/if}
            {/for}
        </select>
        <submit type="submit" name="Submit" value="Submit"></submit>
    </td>
</tr>