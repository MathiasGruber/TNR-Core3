<tr>
    <td>
        <headerText screenWidth="true">Trade details</headerText>
        <text>
<b>Username:</b> {$trade[0]['username']}<br>
<b>Made on:</b> {$trade[0]['time']|date_format:"%d-%m-%y, %H:%M"}<br>
<b>Attached Message:</b> {$trade[0]['message']}
        </text>
        <a href="?id=13&page=profile&name={$trade[0]['username']}">Seller Profile</a>
    </td>
</tr>

{if isset($items)}
    {$subSelect="items"}
    {include file="file:{$absPath}/{$items|replace:'.tpl':'_mobile.tpl'}" title="Items"}
{/if}

<tr><td><a href="?id={$smarty.get.id}&act=browse">Return</a></td></tr>