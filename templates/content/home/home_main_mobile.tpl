<tr>
    <td>
        <headerText>Home</headerText>
    </td>
</tr>
<tr>
    <td>
<text>
<b>Home:</b> {$house['name']}<br>
<b>Comfort Rate:</b> {$house['regen']}
<br><br>
<b>Your options: </b><br>
</text>

{if $user['status'] == 'awake'} 
    <a href="?id={$smarty.get.id}&amp;act=sleep">Sleep</a>
    <a href="?id={$smarty.get.id}&amp;act=sell">Sell home</a>
{elseif $user['status'] == 'asleep'}
    <a href="?id={$smarty.get.id}&amp;act=wake">Wake up</a>
{/if}
<a href="?id={$smarty.get.id}&amp;act=list">Home list</a>
<a href="?id={$smarty.get.id}&act=furniture">Manage Furniture</a>
<a href="?id={$smarty.get.id}&act=inventory">Home Inventory</a>
</td>
<td>
    {$uservil = {$user['village']|lower}}
    <img class="home-image" src="{$house_image}" alt="{$house['name']}" />            
</td>
</tr>
