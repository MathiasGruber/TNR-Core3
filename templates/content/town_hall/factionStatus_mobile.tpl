<tr>
    <td>
        <headerText>{$user_data['village']} Status</headerText>
    </td>
</tr>

<tr color="fadedblack">
    <td><text color="white"><b>Symbol</b></text></td>
    <td><text color="white"><b>Groups</b></text></td>
    <td><text color="white"><b>Total</b></text></td>
    <td><text color="white"><b>Active</b></text></td>
</tr>
<tr>
    <td><img src="./images/villages/{$user_data['name']}.gif" height="300"></img></td>
    <td>
        <text>
Students:
<br>Genin:
<br>Chuunin:
<br>Jounin:
<br>Elite Jounin:
        </text>
    </td>
    <td>
        <text>
{$total['as_count']}<br>
{$total['genin_count']}<br>
{$total['chuunin_count']}<br>
{$total['jounin_count']}<br>
{$total['sj_count']}
        </text>
    </td>
    <td>
        <text>
{$active['as_count']}<br>
{$active['genin_count']}<br>
{$active['chuunin_count']}<br>
{$active['jounin_count']}<br>
{$active['sj_count']}
        </text>
    </td>
</tr>

<tr color="fadedblack">
    <td><text color="white"><b>Supplies</b></text></td>
    <td><text color="white"><b>Bonuses</b></text></td>
    <td><text color="white"><b>Village</b></text></td>
    <td><text color="white"><b>Other</b></text></td>
</tr>
<tr>
    <td>
        <text><b>Hospital:</b> {$hospitalSupply}<br><b>Ramen:</b> {$ramenSupply}</text>
    </td>
    <td>
        <text><b>Hospital:</b> {$hospitalBonus}<br><b>Ramen:</b> {$ramenBonus}</text>
    </td>
    <td>
        <text><b>Village Funds:</b> {$user_data['points']}</text>
        {if isset($DamageBonus)}
            <text><b>Damage bonus:</b> {$DamageBonus}</text>
        {/if}
    </td>
    <td>
        <text><b>{$leaderName|capitalize}:</b> {$user_data['leader']}<br><b>Avg. PvP Exp:</b> {$user_data['avg_pvp']}</text>
    </td>
</tr>


<tr><td><a href="?id={$smarty.get.id}">Return</a></td></tr>
