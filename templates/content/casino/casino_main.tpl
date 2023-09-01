<div align="center">
    <table width="95%" class="table">
        <tr>
            <td class="subHeader">Casino &quot;The Empty Pocket&quot;</td>
        </tr>
        <tr>
            <td align="center">Welcome to The empty pocket, the worlds only casino!<br>
                Our security staff will try to make sure that you will not get robbed while you're inside the casino.<br>
                However, a lot of thieves are already in there, so don't feel too safe.</td>
        </tr>
        <tr>
            <td align="center" class="subHeader">Available Games</td>
        </tr>
        {for $i = 0 to ($games|@count)-1}
            <tr>
                <td align="center">
                    <a href="?id={$smarty.get.id}&game={$i}">{$names[$i]}</a>
                </td>
            </tr>
        {/for}
    </table>
</div>