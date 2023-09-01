<div class="page-box">
    <div class="page-title">
        Home
    </div>
    <div class="page-content page-column-2">
        <div>

            <b>Home:</b> {$house['name']}<br>
            <b>Comfort Rate:</b> {$house['regen']}
            <br><br>
            <b>Home options available to you:</b><br>
            {if $user['status'] == 'awake'} 
                 - <a href="?id={$smarty.get.id}&act=sleep">Sleep</a><br>
                 - <a href="?id={$smarty.get.id}&act=sell">Sell home</a>
            {elseif $user['status'] == 'asleep'}
                 - <a href="?id={$smarty.get.id}&act=wake">Wake up</a>
            {/if}
            <br>
             - <a href="?id={$smarty.get.id}&act=list">Home list</a>
            <br>
             - <a href="?id={$smarty.get.id}&act=furniture">Manage Furniture</a>
            <br>
             - <a href="?id={$smarty.get.id}&act=inventory">Home Inventory</a>

        </div>
        <div>

            {$uservil = {$user['village']|lower}}
            <img class="home-image" src="{$house_image}" alt="{$house['name']}" />            
            <br>
        </div>
    </div>
</div>