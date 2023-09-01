<div align="center">
    <form name="form1" method="post" action="">
        <br>
        <table width="95%" class="table">
            <tr>
                <td colspan="2" class="subHeader">Trade details</td>
            </tr>
            <tr>
                <td>Username:</td>
                <td align="left"><a href="?id=13&page=profile&name={$trade[0]['username']}">{$trade[0]['username']}</a></td>
            </tr>
            <tr>
                <td>Made on:</td>
                <td align="left">{$trade[0]['time']|date_format:"%d-%m-%y, %H:%M"}</td>
            </tr>
            <tr>
                <td>Attached Message:</td>
                <td>{$trade[0]['message']}</td>
            </tr>
            <tr>
                <td colspan="2">
                    {if isset($items)}
                        {$subSelect="items"}
                        {include file="file:{$absPath}/{$items}" title="Items"}
                    {/if}
                </td>
            </tr>
        </table>
    </form>
    <a href="?id={$smarty.get.id}&act=browse">Return</a>
</div>