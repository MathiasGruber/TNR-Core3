<doStretch></doStretch>

<tr>
    <td>
        <headerText>Structures Levels</headerText>
        <text>These are the current levels of the structures/buildings in your village. Updated every 5 min.</text>
    </td>
</tr>

<tr color="fadedblack">
    <td><text color="white"><b>Extra Anbu</b></text></td>
    <td><text color="white"><b>Hospital</b></text></td>
    <td><text color="white"><b>Shop</b></text></td>
    <td><text color="white"><b>Regen</b></text></td>
    <td><text color="white"><b>Rob Defences</b></text></td>
    <td><text color="white"><b>Wall Defences</b></text></td>
</tr>
<tr color="clear">
    <td><text>{$villageVars['anbu_bonus_level']}</text></td>
    <td><text>lvl. {$villageVars['hospital_level']}</text></td>
    <td><text>lvl. {$villageVars['shop_level']}</text></td>
    <td><text>lvl. {$villageVars['regen_level']}</text></td>
    <td><text>lvl. {$villageVars['wall_rob_level']}</text></td>
    <td><text>lvl. {$villageVars['wall_def_level']}</text></td>
</tr>

<tr>
    <td>
        <headerText>Village Standing</headerText>
        <text>Your village is currently in war with {$warringVillages} village(s).</text>
    </td>
</tr>

<tr color="fadedblack">
    {foreach $allianceData as $ally}
        <td><text color="white"><b>{$ally['village']}</b></text></td>
    {/foreach}
</tr>
<tr color="clear">
    {foreach $allianceData as $ally}
        <td><text><b>{$ally['status']}</b></text></td>
    {/foreach}
</tr>
                        
{if isset($destructionPercs) && !empty($destructionPercs)}
    <tr>
        <td>
            <headerText>Current Structure Points</headerText>
            <text>To sustain this war status costs {$warringVillages}% of the village funds every 24 hours! Your opposing villages will seek to destroy your structures. Out of an inital <b>{$villageVars['start_structurePoints']} structure points</b>, your village has <b>{$villageVars['cur_structurePoints']} points</b> left.<br><br>Each time the opposing village kills a member of your village, structure points are deducted, which will ultimately mean removal of village structures. Following shows who has destroyed the most of your villages structures.</text>
        </td>
    </tr>

    <tr color="fadedblack">
        {foreach $destructionPercs as $village => $perc}
            <td><text color="white"><b>{$village}</b></text></td>
        {/foreach}
    </tr>
    <tr color="clear">
        {foreach $destructionPercs as $village => $perc}
            <td><text><b>{$perc}%</b></text></td>
        {/foreach}
    </tr>

    {if isset($warHeroes)}
        {$subSelect="warHeroes"}
        {include file="file:{$absPath}/{$warHeroes}" title="warHeroes"}
    {/if} 
{/if}


<tr>
    <td>
        <a href="?id={$smarty.get.id}">Return</a>
    </td>
</tr>

                