<div class="page-box">
    <div class="page-title">
        Camp
    </div>
    <div class="page-content page-column-2">
        <div>
            You can rest in your comfortable tent. <br>
            <b>Regeneration will be increased by: </b>{$increase}
            <br><br>
            <b>Options available to you:</b><br>
            {if $status == 'awake'}
                - <a href="?id={$smarty.get.id}&act=sleep">Set Up Camp</a>
            {else}
                {if $syndicate_mode}
                  - <a href="?id=23&act=inventory" >Camp Inventory</a>
                  <br>
                {/if}
                - <a href="?id={$smarty.get.id}&act=wake" >Wake Up</a>
            {/if}
        </div>

        <div>
            {$uservil = {$village|lower}}
            <img class="home-image" src="{$s3}/homes/tent.png" alt="{$house['name']}" />
            <br>
        </div>
    </div>
</div>