<div align="center">
    <table width="95%" class="table" >
      <tr>
        <td colspan="3" class="subHeader" >Battleground</td>
      </tr>
      <tr>
        <td style="padding-top:10px;">
            <table class="table" style="border:none;" width="95%" border="0" cellspacing="0" cellpadding="0">
                {if $userList}
                    {foreach $userList as $entry}
                        <tr>
                            <td style="text-align:right;">
                                {if $entry['avatar'] == "AI"}
                                    <font size="+3"><b>AI</b></font>
                                {else}
                                    <img src="{$entry['avatar']}" height="60" width="60"/>
                                {/if}
                            </td>
                            <td style="padding-left:10px;text-align:left;">
                                
                                <!-- Set the texts -->
                                {if isset($entry['name']['href'])}
                                    <a href="{$entry['name']['href']}"><b>{$entry['name']['text']}</b></a>
                                {else}
                                    <b>{$entry['name']['text']}</b>
                                {/if}
                                <br>{$entry['rank']}, {$entry['village']}<br>
                                
                                <!-- Set the br(s) -->
                                {if isset($entry['lifeperc'])} <div align="left" style="height:5px; width:125px; border: 1px solid #000000;"><img src="./images/life_bar.jpg" style="border-right:1px solid #000000;" height="5px" width="{$entry['lifeperc']}%" /></div> {/if}
                                {if isset($entry['chaperc'])} <div align="left" style="height:5px; width:125px; border: 1px solid #000000;"><img src="./images/cha_bar.jpg" style="border-right:1px solid #000000;" height="5px" width="{$entry['chaperc']}%" /></div> {/if}
                                {if isset($entry['staperc'])} <div align="left" style="height:5px; width:125px; border: 1px solid #000000;"><img src="./images/sta_bar.jpg" style="border-right:1px solid #000000;" height="5px" width="{$entry['staperc']}%" /></div> {/if}
                                
                            </td>
                        </tr>
                    {/foreach}
                {else}
                    <tr><td>Nobody Active</td></tr>
                {/if} 
            </table>
            
        </td>
        <td valign="middle" style="padding-top:10px;vertical-align:middle;">
            <font size="+1"><b>VS.</b></font>
        </td>
        <td style="padding-top:10px;">
            <table class="table" style="border:none;" width="95%" border="0" cellspacing="0" cellpadding="0">
                {if $opponentList}
                    {foreach $opponentList as $entry}
                        <tr>
                            <td style="text-align:right;">
                                {if $entry['avatar'] == "AI"}
                                    <font size="+3"><b>AI</b></font>
                                {else}
                                    <img src="{$entry['avatar']}" height="60" width="60"/>
                                {/if}
                            </td>
                            <td style="padding-left:10px;text-align:left;">
                                
                                <!-- Set the texts -->
                                {if isset($entry['name']['href'])}
                                    <a href="{$entry['name']['href']}"><b>{$entry['name']['text']}</b></a>
                                {else}
                                    <b>{$entry['name']['text']}</b>
                                {/if}
                                <br>{$entry['rank']}, {$entry['village']}<br>
                                <!-- Set the br(s) -->
                                {if isset($entry['lifeperc'])} <div align="left" style="height:5px; width:125px; border: 1px solid #000000;"><img src="./images/life_bar.jpg" style="border-right:1px solid #000000;" height="5px" width="{$entry['lifeperc']}%" /></div> {/if}
                                {if isset($entry['chaperc'])} <div align="left" style="height:5px; width:125px; border: 1px solid #000000;"><img src="./images/cha_bar.jpg" style="border-right:1px solid #000000;" height="5px" width="{$entry['chaperc']}%" /></div> {/if}
                                {if isset($entry['staperc'])} <div align="left" style="height:5px; width:125px; border: 1px solid #000000;"><img src="./images/sta_bar.jpg" style="border-right:1px solid #000000;" height="5px" width="{$entry['staperc']}%" /></div> {/if}
                                
                            </td>
                        </tr>
                    {/foreach}
                {else}
                    <tr><td>Nobody Active</td></tr>
                {/if} 
            </table>
        </td>
      </tr> 
      <tr>
        <td colspan="3" align="center" style="padding-bottom:5px;" >
            <font size="+1"><b>Round Time: {$timeLeftJs}</b></font><br>
            <font size="-1"><i>Game Time: {$serverTime|date_format:"jS \of F Y, h:i A"} </i></font>
        </td>
      </tr>
      {if isset($canCFH)}
        <tr>
          <td colspan="3" class="tableColumns" >Strength Factors (SF)</td>
        </tr>            
        <tr>
          <td><b>Your Team SF:</b> {$yourSF}</td>
          <td></td>
          <td><b>RSF: Your SF &#247; Opponent SF:</b> {$relativeSF}</td>
        </tr>
        {if $relativeSF < 1}
            <tr>
              <td colspan="3"><i>Players with Strength Factor of {$joinSFlimit} or less may join your side if you Call for Help</i></td>
            </tr>
        {/if}
      {/if}
    </table>
</div>
        
{if isset($battleDebug)}
    <br>
    {foreach $battleDebug as $entry}
        {$entry} <br>
    {/foreach}
{/if}