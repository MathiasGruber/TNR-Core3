<tr color="darkgrey">
<td>
    <headerText color="white">Battle Options</headerText>
</td>
</tr>
<tr>
    <td>
        <select name="action">
            
            <option value=""><b><i>Jutsus</i></b></option>
            {if isset($userJutsus)}
                {foreach $userJutsus as $entry}
                    <option value="{$entry['value']}" {if !empty({$entry['check']})}action="selected"{/if}> - {$entry['name']} {if isset($entry['curCooldown']) && $entry['curCooldown'] > 0}(<i>Cooldown: {$entry['curCooldown']} turns</i>){/if}</option>
                {/foreach}
            {else}
                <option value=""> - None Tagged</option>
            {/if}
            
            
            <option value=""><b><i>Items and Weapons</i></b></option>
            {if isset($userItems)}
                {foreach $userItems as $entry}
                    <option value="{$entry['value']}" {if !empty({$entry['check']})}action="selected"{/if}> - {$entry['name']}{if isset($entry['durabilityPoints']) && $entry['durabilityPoints'] > 0} (<i>Durability: {if $entry['infinity_durability']} &infin; {else} {$entry['durabilityPoints']}{/if}</i>)
                        {/if}</option>
                {/foreach}
            {else}
                <option value=""> - None Equipped</option>
            {/if}
            
            
            <option value=""><b><i>Standard Actions</i></b></option>
            <option value="STTAI|1" {if !empty($simpleChecks[0])}action="selected"{/if}> - Fist</option>
            <option value="STCHA|2" {if !empty($simpleChecks[1])}action="selected"{/if} > - Chakra Attack</option>
            {if isset($canFLEE)}
                <option value="FLEE|3" {if !empty($simpleChecks[2])}action="selected"{/if}> - Flee</option>
            {/if}
            {if isset($canCFH)}
                {if $canCFHinfo == "true"}
                    <option value="HELP|4" {if !empty($simpleChecks[3])}action="selected"{/if}> - Call for help</option>
                {else}
                    <option value=""> - CFH: {$canCFHinfo}</option>
                {/if}
            {/if}
            
            
        </select>        
    </td>
</tr>

<tr>
<td>
    <headerText>Action Target</headerText>
</td>
</tr>
<tr>
    <td>
        <select name="target">
            
            {if isset($friendSide)}
                <option value=""><b><i>Friendly Targets</i></b></option>
                {foreach $friendSide as $entry}
                    <option value="{$entry['value']}" {if !empty({$entry['check']})}action="selected"{/if}> - {$entry['name']}</option>
                {/foreach}
            {/if}
            
            {if isset($enemySide)}
                <option value=""><b><i>Enemy Targets</i></b></option>
                {foreach $enemySide as $entry}
                    <option value="{$entry['value']}" {if !empty({$entry['check']})}action="selected"{/if}> - {$entry['name']}</option>
                {/foreach}
            {/if}
        </select>        
    </td>
</tr>
<tr>
    <td>
        <hidden name="form_token" value="{$form_token}"></hidden>
        <submit type="submit" name="Submit" value="Submit Action" href="?id={$smarty.get.id}&amp;act=do"></submit>
    </td>
</tr>


<tr>
  <td>
      <countdown time="{$timeLeft}" reload="true" prepend="Round Time: " postpend=" seconds"></countdown>
      <text>Game Time: {$serverTime|date_format:"jS \of F Y, h:i A"}</text>
      <text>Relative Strength Factor: {$relativeSF}</text>
  </td>
</tr>


{if isset($canCFH)}  
  {if $relativeSF < 1}
      <tr>
          <td><text><i>Players with Strength Factor of {$joinSFlimit} or less may join your side if you Call for Help</i></text></td>
      </tr>
  {/if}
{/if}

