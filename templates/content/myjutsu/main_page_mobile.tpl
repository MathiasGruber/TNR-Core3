<tr>
    <td>
        <headerText>My Jutsu</headerText>
    </td>
</tr>


<bg color="clear">
    <tr>
        <td>
            <text color="red" style="text-shadow: 0px 0px 2px black, 1px 1px #323232;">Any jutsu marked in red means you do not have the correct elemental affinity for it.</text>
        </td>
    </tr>

    {foreach $displayArray as $type => $data}
        {if !empty($data)}
            <td>
                <a color="yellow" contentControl="{$type}">
                    Show {if $type == "weapon"}Bukijutsu{else}{$type|capitalize}{/if}
                </a> 
                <contentElement name="{$type}">
                    <tr>
                        <td>
                            {foreach $data as $entry}
                                <bg color="{cycle values="dim,clear"}">
                                    <tr>
                                        <td childExpand="false">
                                            <text {if $entry[6] == "no"}color="red"{/if}>Name: {$entry[1]}</text>
                                            <text {if $entry[6] == "no"}color="red"{/if}>Element: {$entry[5]|capitalize}</text>
                                            <text {if $entry[6] == "no"}color="red"{/if}>Type: {$entry[4]|capitalize}</text>                                    
                                        </td>
                                        <td childExpand="false">
                                            <text {if $entry[6] == "no"}color="red"{/if}>Rank: {$entry[2]}</text>                                            
                                            <text {if $entry[6] == "no"}color="red"{/if}>Level: {$entry[3]}</text>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><a href="?id=12&amp;act=detail&amp;jid={$entry[0]}">Details</a></td>
                                        <td><a href="?id=12&amp;act=forget&amp;jid={$entry[0]}">Forget</a></td>
                                        <td>

                                            {if $user_rank_id == 1}
                                                <a href="?jid={$entry[0]}&amp;train=jutsu&amp;id=18&amp;backend=trainingBackend">Train</a>
                                            {elseif $user_rank_id == 2}
                                                <a href="?jid={$entry[0]}&amp;train=jutsu&amp;id=29&amp;backend=trainingBackend">Train</a>
                                            {else}
                                                <a href="?jid={$entry[0]}&amp;train=jutsu&amp;id=39&amp;backend=trainingBackend">Train</a>
                                            {/if}
                                        </td>
                                    </tr>
                                </bg>
                            {/foreach}            
                        </td>
                    </tr>
                </contentElement>
            </td>
        {/if}
    {/foreach}
    {if $jutsuCount == 0 }
        <tr>
            <td>You don't have any jutsus yet</td>
        </tr> 
    {/if}
</bg>
      
<text><br></text>

{if $show_loadouts}
   <tr>
     <td>
       <headerText>
         Loadouts {$loadout_count}
       </headerText>
     </td>
   </tr>

  <tr>
      <td>
        <text>
          Select Loadout
        </text>
      </td>
  </tr>
  
  <tr>
    <td>
        <tr>
          <td>
            <submit type="submit" name="selectLoadout|default" value="default" {if $current_loadout == 'default'}{assign var=found_loadout value='yes'}color="blue"{/if}></submit>
          </td>
          {foreach $taggedGroups as $loadout}
            {if $loadout != 'default'}
              <td>
                <submit type="submit" name="selectLoadout|{$loadout}" value="{$loadout}" {if $current_loadout == $loadout}{assign var="found_loadout" value='yes'}color="blue"{/if}></submit>
              </td>
            {/if}
          {/foreach}
          {if $found_loadout != 'yes'}
            <td>
              <submit type="submit" name="selectLoadout|{$current_loadout}" value="{$current_loadout}" color="blue"></submit>
            </td>
          {/if}
        </tr>
    </td>
  </tr>
  
  {if $add_loadouts}
    <tr><td></td></tr>
    <tr>
      <td>
        <headerText>
          Add Loadout
        </headerText>
      </td>
    </tr>
    <tr>
      <td>
        <tr>
          <td>
            <input type="textarea" name="selectLoadout"></input>
          </td>
          <td>
            <submit type="submit" name="Create New" value="Create New"></submit>
          </td>
        </tr>
      </td>
    </tr>
  {/if}
  
  {if $delete_loadouts}
    <tr><td></td></tr>
    <tr>
      <td>
        <headerText>
          Delete Loadout
        </headerText>
      </td>
    </tr>
    <tr>
      <td>
        <tr>
          <td>
            <select name="deleteLoadout">
              {foreach $taggedGroups as $loadout}
                {if $loadout != 'default'}
                  <option value="{$loadout}">{$loadout}</option>
                {/if}
              {/foreach}
            </select>
          </td>
          <td>
            <submit type="submit" name="Delete" value="Delete"></submit>
          </td>
        </tr>
      </td>
    </tr>
  {/if}

  <br>
{/if}

{if $force_delete}
<tr><td></td></tr>
    <tr>
      <td>
        <headerText>
          Delete Loadout (You have too many Loadouts {$loadout_count})
        </headerText>
      </td>
    </tr>
    <tr>
      <td>
        <tr>
          <td>
            <select name="deleteLoadout">
              {foreach $taggedGroups as $loadout}
                {if $loadout != 'default'}
                    <option value="{$loadout}">{$loadout}</option>
                {/if}
              {/foreach}
            </select>
          </td>
          <td><submit type="submit" name="Delete" value="Delete"></submit></td>
        </tr>
      </td>
    </tr>
{/if}

<text><br></text>

{if !$force_delete}
  <tr>
      <td>
          <headerText>Tagged jutsu</headerText>
      </td>
  </tr>
  <tr>
      <td>
          <text>Jutsu 1:</text>
          <select name="jutsu1">
          {foreach $jutsuLists[0] as $entry}
              {if $entry[2] == 1}
                  <option action="selected" value="{$entry[0]}">{$entry[1]}</option>
              {else}
                  <option value="{$entry[0]}">{$entry[1]}</option>
              {/if}
          {/foreach}
          </select>
          
          <text>Jutsu 2:</text>
          <select name="jutsu2">
              {foreach $jutsuLists[1] as $entry}
                  {if $entry[2] == 1}
                      <option action="selected" value="{$entry[0]}">{$entry[1]}</option>
                  {else}
                      <option value="{$entry[0]}">{$entry[1]}</option>
                  {/if}
              {/foreach}
          </select>
          
          <text>Jutsu 3:</text>
          <select name="jutsu3">
              {foreach $jutsuLists[2] as $entry}
                  {if $entry[2] == 1}
                      <option action="selected" value="{$entry[0]}">{$entry[1]}</option>
                  {else}
                      <option value="{$entry[0]}">{$entry[1]}</option>
                  {/if}
              {/foreach}
          </select>
          
          <text>Jutsu 4:</text>
          <select name="jutsu4">
              {foreach $jutsuLists[3] as $entry}
                  {if $entry[2] == 1}
                      <option action="selected" value="{$entry[0]}">{$entry[1]}</option>
                  {else}
                      <option value="{$entry[0]}">{$entry[1]}</option>
                  {/if}
              {/foreach}
          </select>
          
          <text>Jutsu 5:</text>
          <select name="jutsu5">
              {foreach $jutsuLists[4] as $entry}
                  {if $entry[2] == 1}
                      <option action="selected" value="{$entry[0]}">{$entry[1]}</option>
                  {else}
                      <option value="{$entry[0]}">{$entry[1]}</option>
                  {/if}
              {/foreach}
          </select>
          
          <text>Jutsu 6:</text>
          <select name="jutsu6">
              {foreach $jutsuLists[5] as $entry}
                  {if $entry[2] == 1}
                      <option action="selected" value="{$entry[0]}">{$entry[1]}</option>
                  {else}
                      <option value="{$entry[0]}">{$entry[1]}</option>
                  {/if}
              {/foreach}
          </select>
  
          <text>Jutsu 7:</text>
          <select name="jutsu7">
              {foreach $jutsuLists[6] as $entry}
              {if $entry[2] == 1}
                  <option action="selected" value="{$entry[0]}">{$entry[1]}</option>
              {else}
                  <option value="{$entry[0]}">{$entry[1]}</option>
              {/if}
          {/foreach}
          </select>
  
          <text>Jutsu 8:</text>
          <select name="jutsu8">
              {foreach $jutsuLists[7] as $entry}
              {if $entry[2] == 1}
                  <option action="selected" value="{$entry[0]}">{$entry[1]}</option>
              {else}
                  <option value="{$entry[0]}">{$entry[1]}</option>
              {/if}
          {/foreach}
          </select>
          
          {if isset($jutsuLists[8])}
              <text>Jutsu 9:</text>
              <select name="jutsu9">
                  {foreach $jutsuLists[8] as $entry}
                  {if $entry[2] == 1}
                      <option action="selected" value="{$entry[0]}">{$entry[1]}</option>
                  {else}
                      <option value="{$entry[0]}">{$entry[1]}</option>
                  {/if}
              {/foreach}
              </select>
              <text>Jutsu 10:</text>
              <select name="jutsu10">
                  {foreach $jutsuLists[9] as $entry}
                  {if $entry[2] == 1}
                      <option action="selected" value="{$entry[0]}">{$entry[1]}</option>
                  {else}
                      <option value="{$entry[0]}">{$entry[1]}</option>
                  {/if}
              {/foreach}
              </select>
          {/if}
      </td>
  </tr>

  <text><br></text>
  <submit type="submit" name="Update" value="Update Tagged Jutsu"></submit>
{/if}