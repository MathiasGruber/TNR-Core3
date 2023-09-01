<div class="page-box">
    <div class="page-title">
        Uncharted Ocean
    </div>
    <div class="page-content">
        <div>
            You are fighting for your life.  Exhausted and no longer able to stay a float, you lose consciousness, resigned to the whims of the current.
        <div>
        </div>
            {if $serverTime >= $user[0]['hospital_timer']}
                <b>Washing ashore you begin to re-gain consciousness.</b>
            {else}
                <b>Your body continues to drift.</b><br>{$waiting_time}
            {/if}
        </div>
        {if $serverTime >= $user[0]['hospital_timer']}
            <a href="?id={$smarty.get.id}&act=release" class="page-button-fill">Wake up!</a>
        {/if}
    </div>
</div>