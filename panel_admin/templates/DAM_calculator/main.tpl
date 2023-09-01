<script language="javascript" type="text/javascript">
    $(document).ready(function() {
        $('.fillLink').click(function(){
            
            // Set side
            side = "none";
            if( $(this).hasClass('attacker') ){ side = "attacker"; }
            else if ( $(this).hasClass('target') ){ side = "target"; }
            
            // Stats
            var hp = $( "input[name*="+side+"_max_health]" ).val();
            var stat = $( "input[name*="+side+"_tai_off]" ).val();
            var gen = $( "input[name*="+side+"_strength]" ).val();
            
            // Set Rank ID
            if( $(this).hasClass('rankid1') ){ hp = {$HP_1}; stat = {$ST_1}; gen = {$GEN_1}; }
            else if ( $(this).hasClass('rankid2') ){ hp = {$HP_2}; stat = {$ST_2}; gen = {$GEN_2}; }
            else if ( $(this).hasClass('rankid3') ){ hp = {$HP_3}; stat = {$ST_3}; gen = {$GEN_3}; }
            else if ( $(this).hasClass('rankid4') ){ hp = {$HP_4}; stat = {$ST_4}; gen = {$GEN_4}; }
            else if ( $(this).hasClass('rankid5') ){ hp = {$HP_5}; stat = {$ST_5}; gen = {$GEN_5}; }
            
            // Set increment/decreases
            var incHP, incStat, incGen;
            if ( $(this).hasClass('incRankID1') ){ incHP = {$HP_3}; incStat={$ST_3}; incGen={$GEN_3} }
            else if ( $(this).hasClass('incRankID2') ){ incHP = {$HP_4}; incStat={$ST_4}; incGen={$GEN_4} }
            else if ( $(this).hasClass('incRankID3') ){ incHP = {$HP_5}; incStat={$ST_5}; incGen={$GEN_5} }
            
            // Convert to int
            hp = parseInt(hp)
            stat = parseInt(stat)
            gen = parseInt(gen)
            incHP = parseInt(incHP)
            incStat = parseInt(incStat)
            incGen = parseInt(incGen)
            
            // Do the increments if needed
            if( $(this).hasClass('minus10') ){      hp -= 0.1*incHP;    stat -= 0.1*incStat;    gen -= 0.1*incGen; }
            else if( $(this).hasClass('minus5') ){  hp -= 0.05*incHP;   stat -= 0.05*incStat;   gen -= 0.05*incGen; }
            else if( $(this).hasClass('plus5') ){   hp += 0.05*incHP;   stat += 0.05*incStat;   gen += 0.05*incGen; }
            else if( $(this).hasClass('plus10') ){  hp += 0.1*incHP;    stat += 0.1*incStat;    gen += 0.1*incGen; }
            
            // Rounf value
            hp = Math.round(hp)
            stat = Math.round(stat)
            gen = Math.round(gen)
            
            // Update stats
            $( "input[name*="+side+"_cur_health]" ).val( hp );
            $( "input[name*="+side+"_max_health]" ).val( hp );
            $( "input[name*="+side+"_tai_off]" ).val( stat );
            $( "input[name*="+side+"_nin_off]" ).val( stat );
            $( "input[name*="+side+"_gen_off]" ).val( stat );
            $( "input[name*="+side+"_weap_off]" ).val( stat );
            $( "input[name*="+side+"_tai_def]" ).val( stat );
            $( "input[name*="+side+"_nin_def]" ).val( stat );
            $( "input[name*="+side+"_gen_def]" ).val( stat );
            $( "input[name*="+side+"_weap_def]" ).val( stat );
            $( "input[name*="+side+"_strength]" ).val( gen );
            $( "input[name*="+side+"_willpower]" ).val( gen );
            $( "input[name*="+side+"_intelligence]" ).val( gen );
            $( "input[name*="+side+"_speed]" ).val( gen );
            
        });
    });
</script>

<form id="form1" name="form1" method="post" action="">
    <div>
        <table width="95%" class="table">
            <tr>
                <td colspan="2" class="subHeader">Battle damage test </td></tr><tr>
                <td width="50%" valign="top">
                    <div>
                        <table width="100%" class="table">
                            <tr>
                                <td colspan="3" class="subHeader">Jutsu damage test</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="tdTop" style="text-align:left; color:white;">
                                    Jutsu power (the entry used in battle formula as "attackPower") is calculated as Base_Power + Jutsu_level * Increment_per_level<br>
                                    The values for current jutsus in the system are as follows:
                                    <br><br>
                                    <u>Academy Students</u><br>
                                    {$jutsu_1_power}<br>
                                    {$jutsu_1_inc}<br>
                                    
                                    <br><br>
                                    <u>Genin</u><br>
                                    {$jutsu_2_power}<br>
                                    {$jutsu_2_inc}<br>
                                    
                                    <br><br>
                                    <u>Chuunin</u><br>
                                    {$jutsu_3_power}<br>
                                    {$jutsu_3_inc}<br>
                                    
                                    <br><br>
                                    <u>Jounin</u><br>
                                    {$jutsu_4_power}<br>
                                    {$jutsu_4_inc}<br>
                                    
                                    <br><br>
                                    <u>Elite Jounin</u><br>
                                    {$jutsu_5_power}<br>
                                    {$jutsu_5_inc}<br>

                                </td>
                            </tr>
                            <tr>
                                <td>Jutsu type</td>
                                <td width="61%" colspan="2" align="left" class="c1">
                                    <select name="statSelect" class="listbox">
                                        <option value="nin">
                                            ninjutsu
                                        </option>
                                        <option value="gen">
                                            genjutsu
                                        </option>
                                        <option value="tai">
                                            taijutsu
                                        </option>
                                        <option value="weap">
                                            bukijutsu
                                        </option>
                                    </select></td>
                            </tr>
                            <tr>
                                <td>Stat influence</td>
                                <td colspan="2" align="left" class="c1">
                                    <select name="stat1" class="listbox" id="stat1">
                                        <option>
                                            strength
                                        </option>
                                        <option>
                                            intelligence
                                        </option>
                                        <option>
                                            willpower
                                        </option>
                                        <option>
                                            speed
                                        </option>
                                    </select><select name="stat2" class="listbox" id="stat2">
                                        <option>
                                            strength
                                        </option>
                                        <option>
                                            intelligence
                                        </option>
                                        <option>
                                            willpower
                                        </option>
                                        <option>
                                            speed
                                        </option>
                                    </select></td>
                            </tr>
                            <tr>
                                <td width="39%">Jutsu power</td>
                                <td colspan="2" align="left" class="c1">
                                    
                                    <input name="jutPower" type="text" class="textfield" id="jutPower" size="6" value="{if isset($smarty.post['jutPower'])}{$smarty.post['jutPower']}{else}0{/if}"/>
                                </td>
                            </tr>
                           <tr>
                                <td><span class="c2">increase / level</span></td>
                                <td colspan="2" align="left" class="c1"><input name="jutIncrease" type="text" class="textfield" id="jutIncrease" size="6" value="{if isset($smarty.post['jutIncrease'])}{$smarty.post['jutIncrease']}{else}0{/if}"></td>
                            </tr>
                            <tr>
                                <td>Jutsu level</td>
                                <td colspan="2" align="left" class="c1"><input name="jutLvl" type="text" class="textfield" id="jutLvl" size="5" maxlength="3" value="{if isset($smarty.post['jutLvl'])}{$smarty.post['jutLvl']}{else}0{/if}"></td>
                            </tr>
                             <tr>
                                <td width="39%">Required Rank</td>
                                <td colspan="2" align="left" class="c1">
                                    
                                    <input name="jutRank" type="text" class="textfield" id="jutRank" size="6" value="{if isset($smarty.post['jutRank'])}{$smarty.post['jutRank']}{else}1{/if}"/>
                                </td>
                            </tr>
                            <tr>
                                <td>Amount of Tests <br>(1-10, Avg. will be shown)</td>
                                <td colspan="2" align="left" class="c1"><input name="jutTests" type="text" class="textfield" id="jutTests" size="5" value="{if isset($smarty.post['jutTests'])}{$smarty.post['jutTests']}{else}0{/if}"></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="c3"><input name="JutsuSubmit" type="submit" class="button" id="Submit" value="Jutsu"></td>
                            </tr>
                        </table>
                    </div>
                    <br>
                    <div align="center">
                        <table width="100%" class="table">
                            <tr>
                                <td colspan="3" align="center" class="subHeader">Weapon damage test </td></tr><tr>
                                <td colspan="3" class="tdTop" style="text-align:left; color:white;">
                                    The power of a weapon scales with the user offence, such that if the weapon is based on "offensive weapon" stat, the power is calculated as
                                    power = base_power * ( 1 + user_offence / 5000000 ). (NOTE: some weapons deal static/percentage amounts of damage. Those are not included here!)
                                    <br><br>
                                    <u>Academy Students</u><br>
                                    {$item_1_power}<br>
                                    
                                    <br><br>
                                    <u>Genin</u><br>
                                    {$item_2_power}<br>
                                    
                                    <br><br>
                                    <u>Chuunin</u><br>
                                    {$item_3_power}<br>
                                    
                                    <br><br>
                                    <u>Jounin</u><br>
                                    {$item_4_power}<br>
                                    
                                    <br><br>
                                    <u>Elite Jounin</u><br>
                                    {$item_5_power}<br>
                                    
                                </td>
                            </tr><tr>
                                <td align="center">Weapon type</td>
                                <td width="61%" colspan="2" align="left" style="padding:2px;">
                                    <select name="weapSelect" class="listbox">
                                        <option value="nin">ninjutsu</option><option value="gen">genjutsu</option><option value="tai">taijutsu</option>
                                        <option value="weap">weapon</option>
                                    </select>
                                </td>
                            </tr><tr>
                                <td width="39%" align="center">Weapon Power (strength)</td>
                                <td colspan="2" align="left" style="padding:2px;"><input name="weapPower" type="text" class="textfield" id="weapPower" size="6" value="{if isset($smarty.post['weapPower'])}{$smarty.post['weapPower']}{else}0{/if}"/></td>
                            </tr>
                            <tr>
                                <td>Amount of Tests <br>(1-10, Avg. will be shown)</td>
                                <td colspan="2" align="left" class="c1"><input name="weapTests" type="text" class="textfield" id="weapTests" size="5" value="{if isset($smarty.post['weapTests'])}{$smarty.post['weapTests']}{else}0{/if}"></td>
                            </tr>
                            <tr>
                                <td colspan="3" align="center" style="padding:5px;"><input name="WeaponSubmit" type="submit" class="button" id="Submit" value="Weapon" /></td>
                            </tr><tr>
                                <td colspan="3" align="center">&nbsp;</td>
                            </tr>
                        </table>
                    </div>
                    <br>
                    <div align="center">
                        <table width="100%" class="table">
                            <tr>
                                <td colspan="2" class="subHeader">Standard Attacks </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="tdTop" style="text-align:left; color:white;">
                                    The power of a basic attack scales with the user offence and a base power of 1000, such that if it is based on "offensive weapon" stat, the power is calculated as
                                    power = 1000 * ( 1 +  0.1 * user_offence / 5000000 ).                                    
                                </td>
                            </tr><tr>
                                <td width="50%" style="padding:3px;"><input name="AttSubmit" type="submit" class="button" value="Tai" /></td>
                                <td width="50%" style="padding:3px;"><input name="AttSubmit" type="submit" class="button" id="Submit" value="Chakra" /></td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="50%" valign="top">
                    <div align="center">
                        <table width="100%" class="table">
                            <tr>
                                <td class="subHeader">Battle Calculation Log</td>
                            </tr><tr>
                                <td style="padding:3px;text-align:left;">
                                    {if isset($calcDebug)}
                                        {foreach $calcDebug as $statement}
                                           &nbsp; ~ {$statement}<br>
                                        {/foreach}
                                    {/if}
                                </td>
                            </tr>
                        </table>
                    </div>
                    <br>
                    <div align="center">
                        <table width="100%" class="table">
                            <tr>
                                <td class="subHeader">Formulas Being Used</td>
                            </tr><tr>
                                <td style="padding:3px;text-align:left;">
                                    {if isset($description)}
                                        {$description}
                                    {/if}
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <br>

    <div>

        <table width="95%" class="table">
            <tr>
                <td colspan="2" class="subHeader">Set Data </td>
            </tr>
            <tr>
                <td colspan="2" class="tdTop" style="text-align:left; color:white;">Armor values give a percentage increase/decrease of the [Target_Defence] variable. I.e. a value of 100 means 100% increase of target defence (before it's down-scaled in its exponential). Current armor values are: {$armors_power}</td>
            </tr>
            <tr>
                <td class="tdTop">Attacker</td>
                <td class="tdTop">Target</td>
            </tr><tr>
                <td width="50%">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:none;">
                        <tr><td width="40%" style="padding-left:5px;">Maximum health </td>
                            <td width="60%"><input name="attacker_max_health" type="text" class="textfield" value="{$attacker_data['max_health']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Current health </td>
                            <td><input name="attacker_cur_health" type="text" class="textfield" value="{$attacker_data['cur_health']}" /></td>
                        </tr><tr><td style="padding-left:5px;">&nbsp;</td><td>&nbsp;</td>
                        </tr><tr>
                            <td style="padding-left:5px;">Taijutsu offense </td>
                            <td><input name="attacker_tai_off" type="text" class="textfield" value="{$attacker_data['tai_off']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Ninjutsu offense </td>
                            <td><input name="attacker_nin_off" type="text" class="textfield" value="{$attacker_data['nin_off']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Genjutsu offense </td>
                            <td><input name="attacker_gen_off" type="text" class="textfield" value="{$attacker_data['gen_off']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Weapon offense </td>
                            <td><input name="attacker_weap_off" type="text" class="textfield"  value="{$attacker_data['weap_off']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr><tr>
                            <td style="padding-left:5px;">Taijutsu defense </td>
                            <td><input name="attacker_tai_def" type="text" class="textfield" value="{$attacker_data['tai_def']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Ninjutsu defense </td>
                            <td><input name="attacker_nin_def" type="text" class="textfield" value="{$attacker_data['nin_def']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Genjutsu defense </td>
                            <td><input name="attacker_gen_def" type="text" class="textfield" value="{$attacker_data['gen_def']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Weapon defense </td>
                            <td><input name="attacker_weap_def" type="text" class="textfield" value="{$attacker_data['weap_def']}" /></td>
                        </tr><tr><td style="padding-left:5px;">&nbsp;</td><td>&nbsp;</td></tr><tr>
                            <td style="padding-left:5px;">Strength</td>
                            <td><input name="attacker_strength" type="text" class="textfield" value="{$attacker_data['strength']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Willpower</td>
                            <td><input name="attacker_willpower" type="text" class="textfield" value="{$attacker_data['willpower']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Intelligence</td>
                            <td><input name="attacker_intelligence" type="text" class="textfield" value="{$attacker_data['intelligence']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Speed</td>
                            <td><input name="attacker_speed" type="text" class="textfield" value="{$attacker_data['speed']}" /></td>
                        </tr>
                        <tr>
                            <td style="padding-left:5px;">Armor</td>
                            <td><input name="attacker_armor" type="text" class="textfield" value="{$attacker_data['armor']}" /></td>
                        </tr>
                        <tr>
                            <td style="padding-left:5px;">Rank ID</td>
                            <td><input name="attacker_rank_id" type="text" class="textfield" value="{$attacker_data['rank_id']}" /></td>
                        </tr>
                        
                    </table>

                </td>
                <td width="49%">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:none;">
                        <tr>
                            <td width="40%" style="padding-left:5px;">Maximum health </td>
                            <td width="60%"><input name="target_max_health" type="text" class="textfield" value="{$target_data['max_health']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Current health </td>
                            <td><input name="target_cur_health" type="text" class="textfield" value="{$target_data['cur_health']}" /></td>
                        </tr><tr><td style="padding-left:5px;">&nbsp;</td><td>&nbsp;</td></tr><tr>
                            <td style="padding-left:5px;">Taijutsu offense </td>
                            <td><input name="target_tai_off" type="text" class="textfield" value="{$target_data['tai_off']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Ninjutsu offense </td>
                            <td><input name="target_nin_off" type="text" class="textfield" value="{$target_data['nin_off']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Genjutsu offense </td>
                            <td><input name="target_gen_off" type="text" class="textfield" value="{$target_data['gen_off']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Weapon offense </td>
                            <td><input name="target_weap_off" type="text" class="textfield" value="{$target_data['weap_off']}" /></td>
                        </tr><tr><td style="padding-left:5px;">&nbsp;</td><td>&nbsp;</td></tr><tr>
                            <td style="padding-left:5px;">Taijutsu defense </td>
                            <td><input name="target_tai_def" type="text" class="textfield" value="{$target_data['tai_def']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Ninjutsu defense </td>
                            <td><input name="target_nin_def" type="text" class="textfield" value="{$target_data['nin_def']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Genjutsu defense </td>
                            <td><input name="target_gen_def" type="text" class="textfield" value="{$target_data['gen_def']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Weapon defense </td>
                            <td><input name="target_weap_def" type="text" class="textfield" value="{$target_data['weap_def']}" /></td>
                        </tr><tr><td style="padding-left:5px;">&nbsp;</td><td>&nbsp;</td></tr><tr>
                            <td style="padding-left:5px;">Strength</td>
                            <td><input name="target_strength" type="text" class="textfield" value="{$target_data['strength']}"/></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Willpower</td>
                            <td><input name="target_willpower" type="text" class="textfield" value="{$target_data['willpower']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Intelligence</td>
                            <td><input name="target_intelligence" type="text" class="textfield" value="{$target_data['intelligence']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Speed</td>
                            <td><input name="target_speed" type="text" class="textfield" value="{$target_data['speed']}" /></td>
                        </tr><tr>
                            <td style="padding-left:5px;">Armor</td>
                            <td><input name="target_armor" type="text" class="textfield" value="{$target_data['armor']}" /></td>
                        </tr>
                        <tr>
                            <td style="padding-left:5px;">Rank ID</td>
                            <td><input name="target_rank_id" type="text" class="textfield" value="{$target_data['rank_id']}" /></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="tdTop">Fill With Capped Stats</td>
                <td class="tdTop">Fill With Capped Stats</td>
            </tr>
            <tr>
                <td >
                    <a class="fillLink attacker rankid1">Academy Student</a> &mdash; 
                    <a class="fillLink attacker rankid2">Genin</a> &mdash; 
                    <a class="fillLink attacker rankid3">Chunin</a> &mdash; 
                    <a class="fillLink attacker rankid4">Jounin</a> &mdash; 
                    <a class="fillLink attacker rankid5">Elite Jounin</a>
                </td>
                <td >
                    <a class="fillLink target rankid1">Academy Student</a> &mdash; 
                    <a class="fillLink target rankid2">Genin</a> &mdash; 
                    <a class="fillLink target rankid3">Chunin</a> &mdash; 
                    <a class="fillLink target rankid4">Jounin</a> &mdash; 
                    <a class="fillLink target rankid5">Elite Jounin</a>
                </td>
            </tr>
            <tr>
                <td >
                    <b>Change entries based on rank: </b>
                </td>
                <td >
                    <b>Change entries based on rank: </b>
                </td>
            </tr>
            <tr>
                <td >
                    <b>C:</b> 
                    <a class="fillLink attacker minus10 incRankID1">&divide;10%</a> &mdash; 
                    <a class="fillLink attacker minus5 incRankID1">&divide;5%</a> &mdash; 
                    <a class="fillLink attacker plus5 incRankID1">+5%</a> &mdash; 
                    <a class="fillLink attacker plus10 incRankID1">+10%</a><br>
                    <b>J:</b> 
                    <a class="fillLink attacker minus10 incRankID2">&divide;10%</a> &mdash; 
                    <a class="fillLink attacker minus5 incRankID2">&divide;5%</a> &mdash; 
                    <a class="fillLink attacker plus5 incRankID2">+5%</a> &mdash; 
                    <a class="fillLink attacker plus10 incRankID2">+10%</a><br>
                    <b>EJ:</b> 
                    <a class="fillLink attacker minus10 incRankID3">&divide;10%</a> &mdash; 
                    <a class="fillLink attacker minus5 incRankID3">&divide;5%</a> &mdash; 
                    <a class="fillLink attacker plus5 incRankID3">+5%</a> &mdash; 
                    <a class="fillLink attacker plus10 incRankID3">+10%</a><br>
                </td>
                <td >
                    <b>C:</b> 
                    <a class="fillLink target minus10 incRankID1">&divide;10%</a> &mdash; 
                    <a class="fillLink target minus5 incRankID1">&divide;5%</a> &mdash; 
                    <a class="fillLink target plus5 incRankID1">+5%</a> &mdash; 
                    <a class="fillLink target plus10 incRankID1">+10%</a><br>
                    <b>J:</b> 
                    <a class="fillLink target minus10 incRankID2">&divide;10%</a> &mdash; 
                    <a class="fillLink target minus5 incRankID2">&divide;5%</a> &mdash; 
                    <a class="fillLink target plus5 incRankID2">+5%</a> &mdash; 
                    <a class="fillLink target plus10 incRankID2">+10%</a><br>
                    <b>EJ:</b> 
                    <a class="fillLink target minus10 incRankID3">&divide;10%</a> &mdash; 
                    <a class="fillLink target minus5 incRankID3">&divide;5%</a> &mdash; 
                    <a class="fillLink target plus5 incRankID3">+5%</a> &mdash; 
                    <a class="fillLink target plus10 incRankID3">+10%</a><br>
                </td>
            </tr>
        </table>    
    </div>
</form>