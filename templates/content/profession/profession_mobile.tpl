<tr>
    <td>
        <headerText>Profession System</headerText>
    </td>
</tr>

<tr>
    <td>
        <text>
Profession: {$data['name']}<br>
Experience: {$data['profession_exp']}            
        </text>   
        <text>
<b>Benefits</b><br>
{foreach $gains as $gain}
{if $gain['type'] == "item"}
-- {$gain['discount']}% off on {if isset($gain['subIdentifier'])}{$gain['subIdentifier']}{/if} {$gain['identifier']}s<br>
{elseif $gain['type'] == "info"}
-- {$gain['text']}<br>
{elseif $gain['type'] == "ramen"}
-- {$gain['discount']}% off on ramen<br>
{elseif $gain['type'] == "hospital"}
-- {$gain['discount']}% off hospital costs<br>
{/if}
{/foreach}
        </text>
    </td>
</tr>

{if isset($controlPanel)}
    {$subSelect="controlPanel"}
    {include file="file:{$absPath}/{$controlPanel|replace:'.tpl':'_mobile.tpl'}" title="Control Panel"}
{/if}

<tr>
    <td>
        <a href="?id={$smarty.get.id}&act=quit">Quit job</a>
    </td>
</tr>