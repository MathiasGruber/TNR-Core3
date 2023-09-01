<div align="center"><br>
    <table width="95%" class="table">
        <tr>
            <td class="subHeader">War Status  </td>
        </tr>
        <tr>
            <td style="padding-left:5px;"><br>   

                <div align="center">
                    <table border="0" cellpadding="0" cellspacing="0" class="table" width="95%" id="AutoNumber1">
                        <tr>
                            <td colspan="6" style="border-top:none;" class="subHeader">Structures Levels</td>
                        </tr>
                        <tr>
                            <td colspan="6" style="border-bottom:1px solid black;" >
                                These are the current levels of the structures/buildings in your village. Updated every 5 min.
                            </td>
                        </tr>
                        <tr>
                            <td class="tableColumns" width="20%"><b>Extra Anbu</b></td>
                            <td class="tableColumns" width="15%"><b>Hospital</b></td>
                            <td class="tableColumns" width="10%"><b>Shop</b></td>
                            <td class="tableColumns" width="15%"><b>Regen</b></td>
                            <td class="tableColumns" width="20%"><b>Rob Defences</b></td>
                            <td class="tableColumns" width="20%"><b>Wall Defences</b></td>
                        </tr>
                        <tr>
                            <td>{$villageVars['anbu_bonus_level']}</td>
                            <td>lvl. {$villageVars['hospital_level']}</td>
                            <td>lvl. {$villageVars['shop_level']}</td>
                            <td>lvl. {$villageVars['regen_level']}</td>
                            <td>lvl. {$villageVars['wall_rob_level']}</td>
                            <td>lvl. {$villageVars['wall_def_level']}</td>
                        </tr>
                    </table>
                </div>

                <div align="center">
                    <table class="table" width="95%">
                        <tr>
                            <td colspan="{$allianceData|@count}" align="center" style="border-top:none;" class="subHeader">Village Standing</td>
                        </tr>
                        <tr>
                        {foreach $allianceData as $ally}
                            <td class="tableColumns"><b>{$ally['village']}</b></td>
                        {/foreach}
                        </tr>
                        <tr>
                        {foreach $allianceData as $ally}
                            <td><b>{$ally['status']}</b></td>
                        {/foreach}
                        </tr>
                    </table>
                </div>
                        
                Your village is currently in war with {$warringVillages} village(s).
                        
                {if isset($destructionPercs) && !empty($destructionPercs)}
                    <div align="center">
                        <table class="table" width="95%">
                            <tr>
                                  <td colspan="{$destructionPercs|@count}" class="subHeader">Current Structure Points</td>
                            </tr>
                            <tr>
                                <td colspan="{$destructionPercs|@count}" >
                                    To sustain this war status costs {$warringVillages}% of the village funds every 24 hours!<br>
                                    Your opposing villages will seek to destroy your structures. <br>
                                    Out of an inital <b>{$villageVars['start_structurePoints']} structure points</b>, your village has <b>{$villageVars['cur_structurePoints']} points</b> left.
                                </td>
                            </tr>
                            <tr>
                                <td colspan="{$destructionPercs|@count}">
                                    Each time the opposing village kills a member of your village, structure points are deducted, 
                                    which will ultimately mean removal of village structures. Following shows who has destroyed the most of your villages structures.
                                </td>
                            </tr>
                            <tr>
                                {foreach $destructionPercs as $village => $perc}
                                    <td class="tableColumns"><b>{$village}</b></td>
                                {/foreach}
                            </tr>
                            <tr>
                                {foreach $destructionPercs as $village => $perc}
                                    <td><b>{$perc}%</b></td>
                                {/foreach}
                            </tr>
                        </table>
                    </div>
                                   
                    {if isset($warHeroes)}
                        {$subSelect="warHeroes"}
                        {include file="file:{$absPath}/{$warHeroes}" title="warHeroes"}
                    {/if} 
            ´   {else}
                    Your village is not currently in war with anyone.
                {/if}
            </td>
        </tr>
    </table>
     
    <a href="?id={$smarty.get.id}">Return</a>
</div>
                