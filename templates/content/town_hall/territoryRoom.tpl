<div align="center">  
    <form method="post" action="">
        <table width="95%" class="table">
            <tr>
                <td colspan="2" class="subHeader">Challenge for Territories</td>
            </tr>
            <tr>
                <td colspan="2">
                    Here you can challenge others for their territories. You can not challenge any of your own allies. 
                    Mobilizing the challenge costs {$challengeCost} points!
                    <br>
                </td>
            </tr>
            <tr style="font-weight:bold;">
                <td width="50%"  colspan="2">Challenge for</td>
            </tr>
            <tr>
            {if isset($avail_terr) && $avail_terr !== "0 rows"}
                <td style="text-align:right;">
                    <select name="challenge" id="challenge">';
                        {foreach $avail_terr as $territory}
                            <option value="{$territory['id']}">{$territory['owner']}: {$territory['name']}</option>
                        {/foreach}
                    </select>
                </td>
                <td style="text-align:left;">
                    <input type="submit" name="Submit" id="button" value="Challenge">
                </td>
            {else}
                <td colspan="2">None Available</td>
            {/if} 
            </tr>
        </table>
    </form>

    {if isset($allianceData)}
        {include file="file:{$absPath}/templates/content/alliance/alliances.tpl" title="Alliance Data"}
    {/if}

    <a href="?id={$smarty.get.id}&act={$smarty.get.act}">Return</a>
</div>