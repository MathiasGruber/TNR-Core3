<doStretch></doStretch>

<!-- Show heal message -->
{if isset($heal_message)}
  <tr>
    <td>
        <headerText>Heal Results</headerText>
        <text>{$heal_message}</text>
        <a href="?id={$smarty.get.id}">Quit job</a>
    </td>
  </tr>

<!-- Show menu for special occupation -->
{else if isset($special_occupations)}
  <tr>
    <td>
        <headerText>Special Occupations</headerText>
    </td>
  </tr>
    
    <!-- Column Names -->
    <tr color="fadedblack">
        <td><text color="white"><b>Name</b></text></td>
        <td><text color="white"><b>Description</b></text></td>
        <td><text color="white"><b>Action</b></text></td>
    </tr>
    
    <!-- Show special occupations -->
    {foreach $special_occupations as $occupation}
        <tr color="{cycle values="dim,clear"}">
            <td><text>{$occupation['name']}</text></td>
            <td><text>{$occupation['description']}</text></td>
            <td>
                <a href="?id={$smarty.get.id}&act=specialgetoccupation&job={$occupation['id']}">Take!</a>
            </td>
      </tr>
    {/foreach}
{else if isset($users)}
    {$subSelect="users"}
    {include file="file:{$absPath}/{$users|replace:'.tpl':'_mobile.tpl'}" title="Surgeon Occupations"}
{else if isset($bounties)}
    {$subSelect="bounties"}
    {include file="file:{$absPath}/{$bounties|replace:'.tpl':'_mobile.tpl'}" title="Interesting bounties"}
{/if}

{if isset($normal_occupations)}
    
    <tr>
        <td>
            <headerText>Occupations List</headerText>
        </td>
    </tr>
    
    <!-- Column Names -->
    <tr color="fadedblack">
        <td><text color="white"><b>Name</b></text></td>
        <td><text color="white"><b>Supports Profession</b></text></td>
        <td><text color="white"><b>Stat 1</b></text></td>
        <td><text color="white"><b>Stat 2</b></text></td>
        <td><text color="white"><b>Stat 3</b></text></td>
        <td><text color="white"><b>Action</b></text></td>
    </tr>
    
    <!-- Show occupations -->
    {foreach $normal_occupations as $occupation}
        <tr color="{cycle values="dim,clear"}">
            <td><text>{$occupation['name']}</text></td>
            <td><text>{$occupation['professionSupport']}</text></td>
            <td><text>{$occupation['gain_1']}</text></td>
            <td><text>{$occupation['gain_2']}</text></td>
            <td><text>{$occupation['gain_3']}</text></td>
            <td>
                {if $occupation['rankid'] == 2}
                    <a class="showTableLink" href="?id={$smarty.get.id}&act=normalgetoccupation&job={$occupation['id']}">Take!</a>
                {else}
                    <text>n/a</text>
                {/if}
            </td>
      </tr>
    {/foreach}

{else if isset($normal_occupation)}
    
     <tr>
        <td>
            <headerText>Occupation</headerText>
        </td>
    </tr>
    
    <!-- Column Names -->
    <tr color="fadedblack">
        <td><text color="white"><b>Status</b></text></td>
        <td><text color="white"><b>Options</b></text></td>
    </tr>
    <tr>
        <td>
            <text>
Job: <b>{$normal_occupation['name']}</b><br>
Lvl: <b>{$normal_occupation['level']}</b><br>
<br>
Gain every 24 hours:<br>
Profession Experience: <b>{$gains['profGain']}</b><br>
Experience: <b>{$gains['expGain']}</b><br>
Stats: <b>{$gains['statGain']} ({$gains['stats']})</b><br>
Ryo: <b>{$gains['ryoGain']}</b>
            </text>
        </td>
        <td>
            {if $check_promotion == 'promotion'}
              <a href="?id={$smarty.get.id}&act=normalpromotion">Promotion!</a>
            {else if $check_promotion == 'level_up'}
              <a href="?id={$smarty.get.id}&act=normallevelup">Level Up!</a>
            {else}
              {$check_promotion}
            {/if}
            
            {if $claim_time !== false}
                <text><b>Time to gain: </b>{$claim_time}</text>
            {else}
              <a href="?id={$smarty.get.id}&act=normalcollect">Collect gain</a>
            {/if}
            {if isset($newJob)}
                {$newJob}
            {/if}
            <a href="?id={$smarty.get.id}&act=normalquit">Quit job</a>
        </td>
    </tr>
{/if}
    