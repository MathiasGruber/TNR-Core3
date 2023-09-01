<div><br>
    <table width="95%" class="table">
        <tr>
            <td colspan="2" class="subHeader">Uncharted Ocean</td>
        </tr>
        <tr>
            <td colspan="2">You are fighting for your life.  Exhausted and no longer able to stay a float, you lose consciousness, resigned to the whims of the current. </td>
        </tr>
        <tr>
            <td width="50%" style="padding-left:5px;padding-right:5px;">
                {if $serverTime >= $user[0]['hospital_timer']}
                    Washing ashore you begin to re-gain consciousness.<br>
                    <a href="?id={$smarty.get.id}&act=release">Wake up!</a>
                {else}
                    <b>Your body continues to drift.</b><br>{$waiting_time}<br>
                {/if}
            </td>
        </tr>
    </table>
</div>