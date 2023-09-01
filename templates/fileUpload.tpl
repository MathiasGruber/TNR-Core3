<div align="center">
    <table width="95%" class="table">
        <tr>
            <td class="subHeader">{$subTitle}</td>
        </tr>
        <tr>
            <td>
                <img style="border:1px solid #000000;" src="{$image}">
            </td>
        </tr>
        <tr>
            <td>
                {$description}<br>
                Maximum dimensions: {$dimX} x {$dimY} pixels, {$maxsize}
            </td>
        </tr>
        <tr>
            <td>
                <form action="" method="post" enctype="multipart/form-data" name="form1">
                    <input type="file" name="userfile">&nbsp;<input type="submit" name="Submit" value="Upload">
                </form>
            </td>
        </tr>
    </table>
    {if isset($returnLink)}
        <a href="?id={$smarty.get.id}">Return</a>
    {/if} 
</div>