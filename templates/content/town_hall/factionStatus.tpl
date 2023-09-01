<div>
    <table width="95%" class="table">
        <tr>
            <td colspan="4" class="subHeader">{$user_data['village']} Status</td>
        </tr>
        <tr>
            <td width="25%" class="tdTop">Symbol</td>
            <td width="25%" class="tdTop">&nbsp;</td>
            <td width="25%" class="tdTop">Total</td>
            <td width="25%" class="tdTop">Active</td>
        </tr>
        <tr>
            <td rowspan="6" valign="top" style="padding-top:5px;padding-bottom:5px;">
                <img style="border: 2px solid #000000;" src="./images/villages/{$user_data['name']}.gif" width="100" height="100" />
            </td>
            <td><b>Academy students:</b></td>
            <td>{$total['as_count']}</td>
            <td>{$active['as_count']}</td>
        </tr>
        <tr>
            <td><b>Genin:</b></td>
            <td>{$total['genin_count']}</td>
            <td>{$active['genin_count']}</td>
        </tr>
        <tr>
            <td><b>Chuunin:</b></td>
            <td>{$total['chuunin_count']}</td>
            <td>{$active['chuunin_count']}</td>
        </tr>
        <tr>
            <td><b>Jounin:</b></td>
            <td>{$total['jounin_count']}</td>
            <td>{$active['jounin_count']}</td>
        </tr>
        <tr>
            <td><b>Elite Jounin:</b></td>
            <td>{$total['sj_count']}</td>
            <td>{$active['sj_count']}</td>
        </tr>
        <tr>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td class="tdTop" style="border-top: 1px solid black;">Supplies</td>
            <td class="tdTop" style="border-top: 1px solid black;">Bonuses</td>     
            <td class="tdTop" style="border-top: 1px solid black;">Village</td>
            <td class="tdTop" style="border-top: 1px solid black;">Other</td>

        </tr>
        <tr>
            <td>
                <b>Hospital:</b> <a href="?id=9&act=hospitalSupply">{$hospitalSupply}/{$active['as_count'] + $active['genin_count'] + $active['chuunin_count'] + $active['jounin_count'] + $active['sj_count']}</a><br>
                <b>Ramen:</b> <a href="?id=9&act=ramenSupply">{$ramenSupply}/{$active['as_count'] + $active['genin_count'] + $active['chuunin_count'] + $active['jounin_count'] + $active['sj_count']}</a>
            </td>
            <td>
                <b>Hospital:</b> {$hospitalBonus}<br>
                <b>Ramen:</b> {$ramenBonus}
            </td>
            <td>
                <b>Village Funds:</b> {$user_data['points']}
            </td>
            <td>
                <b>{$leaderName|capitalize}:</b> {$user_data['leader']}<br>
                <b>Avg. PvP Exp:</b> {$user_data['avg_pvp']}
            </td>
        </tr>
        {if isset($DamageBonus)}
            <tr>
                <td colspan="4">
                    <b>Damage bonus:</b> {$DamageBonus}
                </td>
            </tr>
        {/if}
    </table>
            <a href="?id={$smarty.get.id}">Return</a>
</div>