<tr><td><headerText>{$orderTitle}</headerText></td></tr>
<tr>
    <td>
        <text>{$orders|strip_tags}</text>
    </td>
</tr>
<tr>
    <td>
        <a href="{$reportLink}">Report Orders</a>
        <a href="?id={$smarty.get.id}">Return</a>
    </td>
</tr>