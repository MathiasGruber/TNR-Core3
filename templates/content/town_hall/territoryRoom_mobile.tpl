<tr>
    <td>
        <headerText>Challenge for Territories</headerText>
        <text>Here you can challenge others for their territories. You can not challenge any of your own allies. Mobilizing the challenge costs {$challengeCost} points!</text>
        <text><b>Challenge for</b></text>
        {if isset($avail_terr) && $avail_terr !== "0 rows"}
            <select name="challenge">';
                {foreach $avail_terr as $territory}
                    <option value="{$territory['id']}">{$territory['owner']}: {$territory['name']}</option>
                {/foreach}
            </select>
            <input type="submit" name="Submit" id="button" value="Challenge">
        {else}
            <text>No Territories Available</text>
        {/if} 
    </td>
</tr>

{if isset($allianceData)}
    {include file="file:{$absPath}/templates/content/alliance/alliances.tpl" title="Alliance Data"}
{/if}

<tr>
    <td>
        <a href="?id={$smarty.get.id}&act={$smarty.get.act}">Return</a>
    </td>
</tr>
