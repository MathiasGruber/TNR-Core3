<div align="center">
    <table width="95%" class="table">
        <tr>
            <td style="border-top:none;" class="subHeader" colspan="2">Camp</td>
        </tr>
        <tr>
            <td width="60%" style="text-align:left;padding:15px;" valign="top">
                You can rest in your comfortable tent. <br>
                <b>Regeneration will be increased by: </b>{$increase}
                <br><br>
                <b>Options available to you:</b><br>
                {if $status == 'awake'}
                    - <a href="?id={$smarty.get.id}&act=sleep" style="size:14px;">Set Up Camp</a>
                {else}
                    {if $syndicate_mode}
                      - <a href="?id=23&act=inventory" style="size:14px;">Camp Inventory</a>
                      <br>
                    {/if}
                    - <a href="?id={$smarty.get.id}&act=wake" style="size:14px;">Wake Up</a>
                {/if}
            </td>
            <td width="40%" align="center">
                {$uservil = {$village|lower}}
                <img class="home-image" src="{$s3}/homes/tent.png" alt="{$house['name']}" />
                <br>
            </td>
        </tr>
    </table><br>
</div>