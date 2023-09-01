<tr>
    <td>
        <headerText>Camp</headerText>
    </td>
</tr>
<tr>
    <td>
<text>
You can rest in your comfortable tent<br>
<b>Regeneration increased:</b> {$increase}
<br><br>
<b>Your options: </b><br>
</text>

{if $status == 'awake'} 
    <a href="?id={$smarty.get.id}&amp;act=sleep">Set up camp</a><text><br></text>
{elseif $status == 'asleep'}
    <a href="?id={$smarty.get.id}&amp;act=wake">Wake up</a><text><br></text>
{/if}
    
</td>
<td>
    {if $village == "Konoki"}
        <img src="./images/homes/tent_konoki.png" />
    {elseif $village == "Silence"}
        <img src="./images/homes/tent_silence.png" />
    {elseif $village == "Shine"}
        <img src="./images/homes/tent_shine.png" />
    {elseif $village == "Samui"}
        <img src="./images/homes/tent_current.png" />
    {elseif $village == "Shroud"}
        <img src="./images/homes/tent_shroud.png" />
    {else}
        <img src="./images/homes/tent.png" />
    {/if}
</td>
</tr>