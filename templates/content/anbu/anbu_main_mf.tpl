{if $squad != "0 rows"}
    {if $squad['leader_uid_last_activity'] > ($serverTime - 120)}
        {$status = '<font color="#008000">Online</font>'}
    {else}
        {$status = '<font color="#FF0000">Offline</font>'}
    {/if}
{/if}

<div class="page-box">
    <div class="page-title">
        ANBU Squad
    </div>
    <div class="page-content">

        <div class="page-sub-title-top">
            Details
        </div>

        <div class="page-grid page-column-2">
            <div>
                Name: {$squad['name']}
            </div>
            <div>
                Rank: {$squad['rank']}
            </div>
            <div>
                Assault Points: {$squad['pt_rage']}
            </div>
            <div>
                Defense Points: {$squad['pt_def']}
            </div>
        </div>

        {if isset($anbuSquad)}
            {$subSelect="anbuSquad"}
            {include file="file:{$absPath}/{$anbuSquad}" title="Squad Members"}
        {/if}


        {if ($squad['leader_uid']) == ($smarty.session.uid)}
            <div class="page-sub-title">
                Special Options
            </div>
            <div class="page-grid page-column-3">
                <a href="?id={$smarty.get.id}&act=invite" class="page-button-fill">Invite member</a>
                <a href="?id={$smarty.get.id}&act=orders" class="page-button-fill">Squad Orders</a>
                <a href="?id={$smarty.get.id}&act=kick" class="page-button-fill">Kick member</a>
            </div>
        {/if}

        <div class="page-sub-title">
            Options
        </div>
        <div class="page-grid page-column-{if $squad['rank'] != 'Trainees'}3{else}2{/if}">
                <a href="?id={$smarty.get.id}&act=resign" class="page-button-fill">Resign</a>
                <a href="?id=95" class="page-button-fill">Chat</a>
            {if $squad['rank'] != 'Trainees'}
                <a href="?id={$smarty.get.id}&act=shop" class="page-button-fill">Shop</a>
            {/if}
        </div>

    </div>
</div>