 <script type="text/javascript">
 $(document).ready(function() {
     $('.expandTaijutsu').click(function() {
        $("#taijutsu").slideToggle('slow');  
    });

     $('.expandNinjutsu').click(function() {
        $("#ninjutsu").slideToggle('slow');  
    });

     $('.expandGenjutsu').click(function() {
        $("#genjutsu").slideToggle('slow');  
    });

     $('.expandWeapon').click(function() {
        $("#weapon").slideToggle('slow');  
    });

     $('.expandHighest').click(function() {
        $("#highest").slideToggle('slow');  
    });
});
 </script>


<div align="center">
	<table class="table" style="width:95%;" >
    <tr>
        <td colspan="6" class="subHeader">My Jutsu</td>
    </tr>
    <tr>
        <td colspan="6">Any jutsu marked in red means you do not have the correct elemental affinity for it.</td>
    </tr>
    {foreach $displayArray as $type => $data}
        {if !empty($data)}
            <tr class="expand{$type|capitalize}" onclick="return false;">
                <td style="text-align:left;padding-left:15px;" width="35%" class="tableColumns">{if $type == 'weapon'}Bukijutsu{else}{$type|capitalize}{/if}</td>
                <td style="text-align:left;" width="10%" class="tableColumns">Type</td>
                <td style="text-align:left;" width="10%" class="tableColumns">Element</td>
                <td style="text-align:left;" width="25%" class="tableColumns">Rank</td>
                <td style="text-align:left;" width="10%" class="tableColumns">Level</td>
                <td style="text-align:left;" width="10%" class="tableColumns">Action</td>
            </tr> 
            <tr>
                <td colspan="6" style="padding:0px;margin:0px;">
                <div id="{$type}" style="display:none">
                    <table width="100%" style="padding:0px;margin:0px;border-collapse: collapse;">
                    {foreach $data as $entry}
                            <tr class="{cycle values="row1,row2"}" {if $entry[6] == "no"}style="color:red;"{/if}>
                                <td width="35%" style="text-align:left;padding-left:15px;" ><a href="?id=12&act=detail&jid={$entry[0]}" {if $entry[6] == "no"}style="color:red;"{/if}>{$entry[1]}</a></td>
                                <td width="10%" style="text-align:left;" >{$entry[4]|capitalize}</td>
                                <td width="10%" style="text-align:left;" >{$entry[5]|capitalize}</td>
                                <td width="25%" style="text-align:left;" >{$entry[2]}</td>
                                <td width="10%" style="text-align:left;" >{$entry[3]}</td>
                                <td width="10%" style="text-align:left;" >
                                    <a href="?id=12&act=forget&jid={$entry[0]}" {if $entry[6] == "no"}style="color:red;"{/if}>Forget</a>
                                    {if $user_rank_id == 1}
                                        <a href="?jid={$entry[0]}&train=jutsu&id=18&backend=trainingBackend">Train</a>
                                    {elseif $user_rank_id == 2}
                                        <a href="?jid={$entry[0]}&train=jutsu&id=29&backend=trainingBackend">Train</a>
                                    {else}
                                        <a href="?jid={$entry[0]}&train=jutsu&id=39&backend=trainingBackend">Train</a>
                                    {/if}
                                </td>
                            </tr>
                    {/foreach}            
                    </table>
                </div>
            </td>
            </tr>
        {/if}
    {/foreach}
    {if $jutsuCount == 0 }
        <tr class="expandSpecialization" onclick="return false;">
            <td colspan="6" class="tableColumns">You don't have any jutsus yet</td>
        </tr> 
    {/if}
</table>
      
<br>

{if $show_loadouts}
  <form name="loadouts" method="post" action="">
  <table class="table" style="width:95%;" >
      <tr>
          <td colspan="4" class="subHeader">Loadouts {$loadout_count}</td>
      </tr>
    
  <tr>
      <td class="tableColumns" style="border-right:solid 1px;">Select Loadout</td>
  </tr>
  
  <tr>
    <td style="border-right:solid 1px;">
      <table>
        <tr>
          <form name="selectLoadoutFormdefault" method="post" action="">
            <td><input id="selectdefault" type="submit" name="selectLoadout" value="default" {if $current_loadout == 'default'}{assign var=found_loadout value='yes'}style="color:blue;"{/if}></td>
          <form>
          {foreach $taggedGroups as $loadout}
            {if $loadout != 'default'}
              <form name="selectLoadoutForm{$loadout}" method="post" action="">
                <td><input id="select{$loadout}" type="submit" name="selectLoadout" value="{$loadout}" {if $current_loadout == $loadout}{assign var="found_loadout" value='yes'}style="color:blue;"{/if}></td>
              </form>
            {/if}
          {/foreach}
          {if $found_loadout != 'yes'}
            <form name="selectLoadoutForm{$current_loadout}" method="post" action="">
              <td>
                <input id="select{$current_loadout}" type="submit" name="selectLoadout" value="{$current_loadout}" style="color:blue;">
              </td>
            </form>
          {/if}
        </tr>
      </table>
    </td>
  </tr>
  
  {if $add_loadouts}
    <tr><td style="border-right:solid 1px;"></td></tr>
    <tr>
      <td class="tableColumns" style="border-right:solid 1px;">Add Loadout</td>
    </tr>
    <tr>
      <td style="border-right:solid 1px;">
        <form name="addLoadoutForm" method="post" action="">
          <table>
            <tr>
              <td width="100%">
                <input type="text" name="selectLoadout" style="width:100%;" pattern="[a-zA-Z0-9 ]+">
              </td>
              <td><input type="submit" name="Create New" value="Create New"></td>
            </tr>
          </table>
        </form>
      </td>
    </tr>
  {/if}
  
  {if $delete_loadouts}
    <tr><td style="border-right:solid 1px;"></td></tr>
    <tr>
      <td class="tableColumns" style="border-right:solid 1px;">Delete Loadout</td>
    </tr>
    <tr>
      <td style="border-right:solid 1px;">
        <form name="deleteLoadoutForm" id="deleteLoadoutForm" method="post" action="">
          <table>
            <tr>
              <td width="100%">
                <select name="deleteLoadout" form="deleteLoadoutForm" style="width:100%;">
                  {foreach $taggedGroups as $loadout}
                    {if $loadout != 'default'}
                        <option value="{$loadout}">{$loadout}</option>
                    {/if}
                  {/foreach}
                </select>
              </td>
              <td><input type="submit" name="Delete" value="Delete"></td>
            </tr>
          </table>
        </form>
      </td>
    </tr>
  {/if}
  
  </table>
  </form>
  <br>
{/if}

{if $force_delete}
  <tr><td style="border-right:solid 1px;"></td></tr>
    <tr>
      <td class="tableColumns" style="border-right:solid 1px;"> Delete Loadout (You have too many Loadouts {$loadout_count})</td>
    </tr>
    <tr>
      <td style="border-right:solid 1px;">
        <form name="deleteLoadoutForm" id="deleteLoadoutForm" method="post" action="">
          <table>
            <tr>
              <td width="100%">
                <select name="deleteLoadout" form="deleteLoadoutForm" style="width:100%;">
                  {foreach $taggedGroups as $loadout}
                    {if $loadout != 'default'}
                        <option value="{$loadout}">{$loadout}</option>
                    {/if}
                  {/foreach}
                </select>
              </td>
              <td><input type="submit" name="Delete" value="Delete"></td>
            </tr>
          </table>
        </form>
      </td>
    </tr>
{/if}
  
{if !$force_delete}
  <form name="form1" method="post" action="">
  <table class="table" style="width:95%;" >
      <tr>
          <td colspan="4" class="subHeader">Tagged jutsu</td>
      </tr>
      <tr>
          <td width="15%" style="padding-left:5px;">Jutsu 1:</td>
          <td style="padding-bottom:5px;" width="37%">
              <select name="jutsu1">
                  {foreach $jutsuLists[0] as $entry}
                      {if $entry[2] == 1}
                          <option selected value="{$entry[0]}">{$entry[1]}</option>
                      {else}
                          <option value="{$entry[0]}">{$entry[1]}</option>
                      {/if}
                  {/foreach}
                  </select>
          </td>
          <td width="11%" style="padding-left:5px;">Jutsu 2: </td>
          <td style="padding-bottom:5px;" width="37%">
              <select name="jutsu2">
                  {foreach $jutsuLists[1] as $entry}
                      {if $entry[2] == 1}
                          <option selected value="{$entry[0]}">{$entry[1]}</option>
                      {else}
                          <option value="{$entry[0]}">{$entry[1]}</option>
                      {/if}
                  {/foreach}
              </select>
          </td>
      </tr>
      <tr>
          <td style="padding-left:5px;">Jutsu 3: </td>
          <td style="padding-bottom:5px;">
              <select name="jutsu3">
                  {foreach $jutsuLists[2] as $entry}
                      {if $entry[2] == 1}
                          <option selected value="{$entry[0]}">{$entry[1]}</option>
                      {else}
                          <option value="{$entry[0]}">{$entry[1]}</option>
                      {/if}
                  {/foreach}
              </select>
          </td>
          <td style="padding-left:5px;">Jutsu 4: </td>
          <td style="padding-bottom:5px;">
              <select name="jutsu4">
                  {foreach $jutsuLists[3] as $entry}
                      {if $entry[2] == 1}
                          <option selected value="{$entry[0]}">{$entry[1]}</option>
                      {else}
                          <option value="{$entry[0]}">{$entry[1]}</option>
                      {/if}
                  {/foreach}
              </select>
          </td>
      </tr>
      <tr>
          <td style="padding-left:5px;">Jutsu 5: </td>
          <td style="padding-bottom:5px;">
              <select name="jutsu5">
                  {foreach $jutsuLists[4] as $entry}
                      {if $entry[2] == 1}
                          <option selected value="{$entry[0]}">{$entry[1]}</option>
                      {else}
                          <option value="{$entry[0]}">{$entry[1]}</option>
                      {/if}
                  {/foreach}
              </select>
          </td>
          <td style="padding-left:5px;">Jutsu 6: </td>
          <td style="padding-bottom:5px;">
              <select name="jutsu6">
                  {foreach $jutsuLists[5] as $entry}
                      {if $entry[2] == 1}
                          <option selected value="{$entry[0]}">{$entry[1]}</option>
                      {else}
                          <option value="{$entry[0]}">{$entry[1]}</option>
                      {/if}
                  {/foreach}
              </select>
          </td>
      </tr>
       <tr>
          <td style="padding-left:5px;">Jutsu 7: </td>
          <td style="padding-bottom:5px;">
              <select name="jutsu7">
                  {foreach $jutsuLists[6] as $entry}
                  {if $entry[2] == 1}
                      <option selected value="{$entry[0]}">{$entry[1]}</option>
                  {else}
                      <option value="{$entry[0]}">{$entry[1]}</option>
                  {/if}
              {/foreach}
              </select>
          </td>
          <td style="padding-left:5px;">Jutsu 8: </td>
          <td style="padding-bottom:5px;">
              <select name="jutsu8">
                  {foreach $jutsuLists[7] as $entry}
                  {if $entry[2] == 1}
                      <option selected value="{$entry[0]}">{$entry[1]}</option>
                  {else}
                      <option value="{$entry[0]}">{$entry[1]}</option>
                  {/if}
              {/foreach}
              </select>
          </td>
      </tr>
      {if isset($jutsuLists[8])}
          <tr>
              <td style="padding-left:5px;">Jutsu 9: </td>
              <td style="padding-bottom:5px;">
                  <select name="jutsu9">
                      {foreach $jutsuLists[8] as $entry}
                      {if $entry[2] == 1}
                          <option selected value="{$entry[0]}">{$entry[1]}</option>
                      {else}
                          <option value="{$entry[0]}">{$entry[1]}</option>
                      {/if}
                  {/foreach}
                  </select>
              </td>
              <td style="padding-left:5px;">Jutsu 10: </td>
              <td style="padding-bottom:5px;">
                  <select name="jutsu10">
                      {foreach $jutsuLists[9] as $entry}
                      {if $entry[2] == 1}
                          <option selected value="{$entry[0]}">{$entry[1]}</option>
                      {else}
                          <option value="{$entry[0]}">{$entry[1]}</option>
                      {/if}
                  {/foreach}
                  </select>
              </td>
          </tr>
      {/if}
      <tr>
          <td colspan="4" align="center" style="padding-bottom:5px;">
              <input type="submit" name="Update" value="Update">
          </td>
      </tr>
  </table>
  </form>
 {/if}
</div>