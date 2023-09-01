<div align="center">
  {if isset($heal_message)}
  <table align="center" width ="100%">
    <tr>
      <td align="center" class="subHeader">Heal Results</td>
    </tr>
  </table>

  <table class="sortable" style="border-left:none;border-right:none" width="100%">
    <thead>
      <tr>
        <td>
          {$heal_message}
        </td>
      </tr>
      <tr>
        <a href="?id={$smarty.get.id}">Return</a>
      </tr>
    </thead>
  </table>
  {else if isset($special_occupations)}
  <table align="center" width ="100%">
    <tr>
      <td align="center" class="subHeader">Special Occupations</td>
    </tr>
  </table>
  {if $userdata['rank_id'] >= 3}
    <table class="sortable" style="border-left:none;border-right:none" width="100%">
      <thead>
        <tr>
          <td class="tdTop" width="40%">Name</td>
          <td class="tdTop" width="40%">Description</td>
          <td class="tdTop" width="20%">Free</td>
        </tr>
      </thead>

      <tbody>
        {foreach $special_occupations as $occupation}
        <tr>
          <td width="40%">
            {$occupation['name']}<br><br>
          </td>
          <td width="40%">{$occupation['description']}</td>
          <td width="20%">
            <a class="showTableLink" href="?id={$smarty.get.id}&act=specialgetoccupation&job={$occupation['id']}">Take!</a>
          </td>
        </tr>
        {/foreach}
      </tbody>
    </table>
  {/if}
  {else if isset($users)}
  {$subSelect="users"}
  {include file="file:{$absPath}/{$users}" title="Surgeon Occupations"}
  {else if isset($bounties)}
  {$subSelect="bounties"}
  {include file="file:{$absPath}/{$bounties}" title="Interesting bounties"}
  {/if}
  <br>
  <br>
  {if isset($normal_occupations)}
  <table align="center" width ="100%">
        <tr>
          <td align="center" class="subHeader">Occupations List</td>
        </tr>
      </table>
  
      <table class="sortable" style="border-left:none;border-right:none" width="100%">
        <thead>
          <tr>
            <td class="tdTop" width="20%">Name</td>
            <td class="tdTop" width="25%">Supports Profession</td>
            <td class="tdTop" width="15%">Stat 1</td>
            <td class="tdTop" width="15%">Stat 2</td>
            <td class="tdTop" width="15%">Stat 3</td>
            <td class="tdTop" width="10%">Free</td>
          </tr>
        </thead>

        <tbody>
          {foreach $normal_occupations as $occupation}
            <tr>
              <td width="20%">{$occupation['name']}<br><br></td>
              <td width="25%">{$occupation['professionSupport']}</td>
              <td width="15%">{$occupation['gain_1']}</td>
              <td width="15%">{$occupation['gain_2']}</td>
              <td width="15%">{$occupation['gain_3']}</td>
              <td width="10%">
                {if $occupation['rankid'] == 2}
                  <a class="showTableLink" href="?id={$smarty.get.id}&act=normalgetoccupation&job={$occupation['id']}">Take!</a>
              {else}
              n/a
              {/if}
            </td>
            </tr>
          {/foreach}
        </tbody>
      </table>
  
    {else if isset($normal_occupation)}
        <div align="center">
          <table width="100%" class="table" style="border-left:none;border-right:none">
            <tr>
              <td colspan="2" class="subHeader">Occupation</td>
            </tr>
            <tr>
              <td width="43%" class="tableColumns tdBorder">Status</td>
              <td width="57%" class="tableColumns tdBorder">Options</td>
            </tr>
            <tr>
              <td style="text-align: left;padding:10px;">
                Job: <b>{$normal_occupation['name']}</b><br>
                Lvl: <b>{$normal_occupation['level']}</b><br>
                Gains collected every 24 hours:<br>
                Profession Experience: <b>{$gains['profGain']}</b> (if applicable)<br>
                Experience: <b>{$gains['expGain']}</b><br>
                Stats: 
                <b>
                  {$gains['statGain']} 
                  ({$gains['stats']})
                </b>
                <br>
                Ryo: <b>{$gains['ryoGain']}</b><br>
        
        
              </td>
              <td style="text-align: left;padding:10px;">
                Promotion:
                <b>
                {if $check_promotion == 'promotion'}
                  <a href="?id={$smarty.get.id}&act=normalpromotion">Promotion!</a>
                {else if $check_promotion == 'level_up'}
                  <a href="?id={$smarty.get.id}&act=normallevelup">Level Up!</a>
                {else}
                  {$check_promotion}
                {/if}
                
                </b><br>
                
                
                Claim:
                <b>
                {if $claim_time !== false}
                  {$claim_time}
                {else}
                  <a href="?id={$smarty.get.id}&act=normalcollect">Collect</a>
                {/if}
                </b><br>
                {if isset($newJob)}
                {$newJob}
                {/if}
                Status: <b>
                  <a href="?id={$smarty.get.id}&act=normalquit">Quit job</a>
                </b>
              </td>
            </tr>
          </table>
        </div>
    {/if}
</div>