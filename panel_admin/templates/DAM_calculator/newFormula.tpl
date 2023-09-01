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
                                <td colspan="3" class="tdTop">Calculations to Run</td>
                            </tr>
                            <tr>
                                <td>User Rank</td>
                                <td width="61%" colspan="2" align="left" class="c1">
                                    <select name="userRank" class="listbox">
                                        <option value="1">Academy Student</option>
                                        <option value="2">Genin</option>
                                        <option value="3">Chunin</option>
                                        <option value="4">Jounin</option>
                                        <option value="5">Elite Jounin</option>
                                        {if isset($smarty.post['userRank'])}<option selected value="{$smarty.post['userRank']}">Previous Selection</option>{/if}
                                    </select></td>
                            </tr>
                            <tr>
                                <td>Battle Formula to User</td>
                                <td colspan="2" align="left" class="c1">
                                    <select name="formula" class="listbox" id="stat1">
                                        <option value="old">Old Formula</option>
                                        <option value="terrs">Terr's Formula</option>
                                        {if isset($smarty.post['formula'])}<option selected value="{$smarty.post['formula']}">Previous Selection</option>{/if}
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Analysis to run</td>
                                <td colspan="2" align="left" class="c1">
                                    <select name="analysis" class="listbox" id="stat1">
                                        <option value="standardActions">Standardaction Battles</option>
                                        <option value="weaponActions">Weapon Battles</option>
                                        <option value="jutsuActions">Jutsu Battles</option>
                                        <option value="rsfAnalysis">RSF Analysis</option>
                                        {if isset($smarty.post['analysis'])}<option selected value="{$smarty.post['analysis']}">Previous Selection</option>{/if}
                                    </select>
                                </td>
                            </tr>                            
                            <tr>
                                <td>How to arrange fights</td>
                                <td colspan="2" align="left" class="c1">
                                    <select name="userArrange" class="listbox" id="stat1">
                                        <option value="random">Randomly</option>
                                        <option value="bestVsworst">Top10% vs. Crap10%</option>
                                        {if isset($smarty.post['userArrange'])}<option selected value="{$smarty.post['userArrange']}">Previous Selection</option>{/if}
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Test Accuracy <br>(1 is faster but less accurate, 2000 is max and more accurate, but also slower)</td>
                                <td colspan="2" align="left" class="c1">
                                    <input name="accuracy" type="text" class="textfield" id="jutTests" size="5" value="{if isset($smarty.post['accuracy'])}{$smarty.post['accuracy']}{else}50{/if}">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="c3"><input name="CalculationRun" type="submit" class="button" id="Submit" value="Run Calculations"></td>
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
                                        {foreach $description as $statement}
                                           &nbsp; ~ {$statement}<br>
                                        {/foreach}
                                    {/if}
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</form>