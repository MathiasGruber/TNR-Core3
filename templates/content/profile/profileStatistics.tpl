<table width="100%" style="border-spacing:0px;border-collapse: collapse;" >
<tr>
    <td valign="top" style="text-align:left;width:50%;">
         <b>General Info:</b><br>
            Name: {$charStats.username} <br>
            Gender:  {$charStats.gender} <br>
            E-mail: {$charStats.mail} <br>
            {$charStats.marriedTo}
            <br>
            <b>Missions</b><br>
            D-missions: {$charStats.d_mission}<br>
            C-missions: {$charStats.c_mission} <br>
            B-missions: {$charStats.b_mission}<br>
            A-missions: {$charStats.a_mission}<br>
            S-missions: {$charStats.s_mission}<br>
            <br>
            <b>Crimes</b><br>
            C crimes: {$charStats.c_crime}<br>
            B crimes: {$charStats.b_crime}<br>
            A crimes: {$charStats.a_crime}<br>
            <br>
            <b>PVP Battles</b><br>
            Battles won: {$charStats.battles_won} <br>
            Battles lost: {$charStats.battles_lost} <br>
            Battles fled: {$charStats.battles_fled}<br>
            Battle draws: {$charStats.battles_draws}<br>
            Battles fought: {math equation="won+lost+fled+draws" won=$charStats.battles_won lost=$charStats.battles_lost fled=$charStats.battles_fled draws=$charStats.battles_draws}<br>
            Win Percentage: <font class="{$charStats.color}">{$charStats.percentage}%</font><br>
            <br>
            <b>AI Battles</b><br>
            Battles won: {$charStats.AIwon} <br>
            Battles lost: {$charStats.AIlost} <br>
            Battles fled: {$charStats.AIfled} <br>
            Battle draws: {$charStats.AIdraw} <br>
            Arena Record: {$charStats.torn_record}<br>
            <br>
            <b>War Activity</b><br>
            Structures Destroyed: {$charStats.structureDestructionPoints} <br>
            Structures Restored: {$charStats.structureGatherPoints} <br>
            Structure Point Activity : {$charStats.structurePointsActivity}<br>
    </td>
    <td valign="top" style="text-align:left;width:50%;border-left:1px solid">
            <b>Character Activity</b><br>
            Errands run: {$charStats.errands}<br>
            Small crimes: {$charStats.scrimes} <br>
            Times arrested: {$charStats.arrested}<br>
            <br>
            <b>Offensive Skills:</b><br>
            Taijutsu strength: {$charStats.tai_off}<br>
            Ninjutsu strength: {$charStats.nin_off}<br>
            Genjutsu strength: {$charStats.gen_off}<br>
            Bukijutsu strength: {$charStats.weap_off}<br>
            <br>
            <b>Defensive Skills:</b><br>
            Taijutsu defense: {$charStats.tai_def}<br>
            Ninjutsu defense: {$charStats.nin_def}<br>
            Genjutsu defense: {$charStats.gen_def}<br>
            Bukijutsu defense: {$charStats.weap_def}<br>
            <br>
            <b>Elemental Masteries:</b><br>
                Primary: {$charStats.element_mastery_1}<br>
                Secondary: {$charStats.element_mastery_2}<br>
                Special: {$charStats.element_mastery_special}<br>
                <br>
            <b>Generals:</b><br>
            Strength: {$charStats.strength}<br>
            Intelligence: {$charStats.intelligence}<br>
            Speed: {$charStats.speed}<br>
            Willpower: {$charStats.willpower}<br>
            <br>
            <b>Site Support:</b><br>
            Federal support: {$charStats.federal}<br>
            Federal level: {$charStats.federal_level}<br>
            Reputation points ever: {$charStats.rep_ever}<br>
            Reputation points now: {$charStats.rep_now}<br>
            Popularity points ever: {$charStats.pop_ever}<br>
            Popularity points now: {$charStats.pop_now}<br>
            <br>                                
    </td>
</tr>
</table>