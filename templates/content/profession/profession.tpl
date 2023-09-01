<div align="center">
    <table width="95%" class="table">
        <tr>
            <td colspan="2" class="subHeader">Profession System</td>
        </tr>
        <tr>
            <td width="50%" style="text-align: left;padding:10px;" valign="middle">
                Profession: {$data['name']}<br>
                Level: {$data['profession_exp']}<br>
                <b><a href="?id={$smarty.get.id}&act=quit">Quit job</a></b>
            </td>
            <td width="50%" style="text-align: left;padding:10px;" valign="middle">
                 {foreach $gains as $gain}
                     {if $gain['type'] == "item"}
                         {$gain['discount']}% off on {if isset($gain['subIdentifier'])}{$gain['subIdentifier']}{/if} {$gain['identifier']}s<br>
                     {elseif $gain['type'] == "info"}
                         {$gain['text']}<br>
                     {elseif $gain['type'] == "ramen"}
                         {$gain['discount']}% off on ramen<br>
                     {elseif $gain['type'] == "hospital"}
                         {$gain['discount']}% off hospital costs<br>
                     {/if}
                 {/foreach}
            </td>
        </tr>
    </table>
    {if isset($controlPanel)}
        {$subSelect="controlPanel"}
        {include file="file:{$absPath}/{$controlPanel}" title="Control Panel"}
    {/if}
</div>