<!-- //////////////////// page header \\\\\\\\\\\\\\\\\\\\-->
<a  fontSize="70" fontStyle="bold" href="?id=113" color="clear" fontcolor="soliddarkpurple">{$battle_type} Battleground</a>
<countdown align="center" time="{$turn_timer - time()}" reload="true" prepend="{$turn_counter + 1} :Round - Time Left: " postpend=""></countdown>
<!-- //////////////////// main body \\\\\\\\\\\\\\\\\\\\-->
        
<tr>  <!-- top -->
  <td contentFit="true"> <!-- top left -->

    <!-- //////////////////// display of this user \\\\\\\\\\\\\\\\\\\\-->
    <tr>
      <td contentFit="true">
        {if isset($owner['stunned']) || isset($owner['staggered']) || isset($owner['disabled']) }
          <img src="{$owner['avatar']}" color="0.5,0.5,0.5"></img>
        {else}
          <img src="{$owner['avatar']}"></img>
        {/if}

        <br>
        
        <text><b>{$owner['name']}</b></text>
        <br>
        <text>{$owner['display_rank']}, {$owner['team']}</text>
        <br>
        <bar curVal="{$owner['health']}" maxVal="{$owner['healthMax']}" title="" showValues="false" color="solidred" height="40"></bar>
        <bar curVal="{$owner['chakra']}" maxVal="{$owner['chakraMax']}" title="" showValues="false" color="solidblue" height="40"></bar>
        <bar curVal="{$owner['stamina']}" maxVal="{$owner['staminaMax']}" title="" showValues="false" color="solidgreen" height="40"></bar>
      </td>
    </tr>

    <!-- //////////////////// display of allies \\\\\\\\\\\\\\\\\\\\-->
    {foreach $users as $username => $userdata}
      {if $userdata['team'] == $owner['team'] && $owner['name'] != $username}
      
        <br>
        <br>
      
        <tr>
          <td contentFit="true"> <!-- team mate -->
            
            {if $userdata['ai'] == true}
              <text><b>AI</b></text>
            {else if isset($userdata['stunned']) || isset($userdata['staggered']) || isset($userdata['disabled']) }
              <img src="{$userdata['avatar']}" color="0.5,0.5,0.5"></img>
            {else}
              <img src="{$userdata['avatar']}"></img>
            {/if}
            <br>
              
            {if $userdata['show_count'] == 'yes'}
              <text><b>{$username}</b></text>
              <br>
              <text>{$userdata['display_rank']}, {$userdata['team']}</text>
            {else if $userdata['show_count'] == 'no'}
              {if strpos($username,'#') !== false}
                <text><b>{substr($username,0,strpos($username,'#') - 1)}</b></text>
                <br>
                <text>{$userdata['display_rank']}, {$userdata['team']}</text>
              {else}
                <text><b>{$username}</b></text>
                <br>
                <text>{$userdata['display_rank']}, {$userdata['team']}</text>
              {/if}
            {/if}
            
            <br>
            
            <bar curVal="{$userdata['health']}" maxVal="{$userdata['healthMax']}" title="" showValues="false" color="solidred" height="40"></bar>
            
          </td>
        </tr>
      {/if}
    {/foreach}
  </td>
  
  <!--
  <td>
    <text><b>VS</b></text>
  </td>
  -->

  <!-- //////////////////// display of opponents \\\\\\\\\\\\\\\\\\\\-->
  <td>
    
    {foreach $users as $username => $userdata} <!-- team mates -->
      {if $userdata['team'] != $owner['team']}
        <tr>
          <td contentFit="true"> <!-- team mate -->
            
            <tr>
              <td contentFit="true">
                {if $userdata['ai'] == true}
                  <text><b>AI</b></text>
                {else if isset($userdata['stunned']) || isset($userdata['staggered']) || isset($userdata['disabled']) }
                  <img src="{$userdata['avatar']}" color="0.5,0.5,0.5"></img>
                {else}
                  <img src="{$userdata['avatar']}"></img>
                {/if}
              </td>
            </tr>
            
            <br>
              
            {if $userdata['show_count'] == 'yes'}
              <text><b>{$username}</b></text>
              <br>
              <text>{$userdata['display_rank']}, {$userdata['team']}</text>
            {else if $userdata['show_count'] == 'no'}
              {if strpos($username,'#') !== false}
                <text><b>{substr($username,0,strpos($username,'#') - 1)}</b></text>
                <br>
                <text>{$userdata['display_rank']}, {$userdata['team']}</text>
              {else}
                <text><b>{$username}</b></text>
                <br>
                <text>{$userdata['display_rank']}, {$userdata['team']}</text>
              {/if}
            {/if}
            
            <br>
            
            <bar curVal="{$userdata['health']}" maxVal="{$userdata['healthMax']}" title="" showValues="false" color="solidred" height="40"></bar>
            
          </td>
        </tr>
    
        <br>
        <br>
        
      {/if}
    {/foreach}
    
  </td>
</tr>

<!-- //////////////////// actions \\\\\\\\\\\\\\\\\\\\-->
<br>

<tr>
  <td contentFit="true">
    {if (is_numeric($stunned) && $stunned > 0) || $stunned === true}
      <headerText>Stunned...</headerText>
      <text>You are now waiting on your opponents while stunned.</text>
      <text>Stunned for {$stunned} {if $stunned == 1}round{else}rounds{/if}.</text>
    
    {else if $owner['waiting_for_next_turn'] === true}
      <headerText>Waiting...</headerText>
      <text>You are now waiting on your opponents.</text>
    
    {else}
      <headerText>Actions</headerText>
      <select name="action_select" contentControl="true">
        <option action="selected" value="Jutsu" contentControl="jutsu_options">Jutsu</option>
        <option value="Weapons" contentControl="weapon_options">Weapons</option>
        <option value="Items" contentControl="item_options">Items</option>
        
        {if $no_flee != true && $owner['attacker'] != true}
          <option value="Flee" contentControl="flee_targets">Flee</option>
        {/if}
        {if $no_cfh != true && $owner['no_cfh'] != true && $cfhRange1 != 'N/A'}
          <option value="Call_For_Help" contentControl="call_for_help_target">Call For Help</option>
        {/if}
      </select>
    {/if}
    
    <!-- jutsu action here -->
    <contentElement name="jutsu_options" show="true">
      <select name="jutsu_select" contentControl="true">
        {foreach $owner['jutsus'] as $jutsu_id => $jutsu_data}
          <option 
          {if $jutsu_data['name'] == 'Basic Attack'}
            action="selected"
          {/if}

        {if strpos($owner['jutsu_weapon_selects_mobile'][$jutsu_id], "no-weapon") !== false}
        disable="jutsu_options_submit"
        {/if}

        {if ( (is_numeric($no_jutsu) && $no_jutsu > 0) || $no_jutsu === true) && $jutsu_data['name'] != 'Basic Attack'}
            contentControl="{$jutsu_data['targeting_type']}" value="{$jutsu_id}" disable="jutsu_options_submit">
    	      {$jutsu_data['name']} disable="jutsu_options_submit"({$no_jutsu})
    	      </option>
          {else if ($jutsu_data['cooldown_status'] == 'off' || $jutsu_data['cooldown_status'] == '') && $jutsu_data['reagent_status'] == true && $jutsu_data['max_uses'] > $jutsu_data['uses']}
            {if $jutsu_data['targeting_type'] == 'target'}
              contentControl="jutsu_options_target_alls/jutsu_options_weapons_{$jutsu_id}/jutsu_options_submit" value="{$jutsu_id}"> {$jutsu_data['name']}
            {else if $jutsu_data['targeting_type'] == 'opponent'}
              contentControl="jutsu_options_target_opponents/jutsu_options_weapons_{$jutsu_id}/jutsu_options_submit" value="{$jutsu_id}"> {$jutsu_data['name']}
            {else if $jutsu_data['targeting_type'] == 'self'}
              contentControl="jutsu_options_target_selfs/jutsu_options_weapons_{$jutsu_id}/jutsu_options_submit" value="{$jutsu_id}"> {$jutsu_data['name']}
            {else if $jutsu_data['targeting_type'] == 'other'}
              contentControl="jutsu_options_target_others/jutsu_options_weapons_{$jutsu_id}/jutsu_options_submit" value="{$jutsu_id}"> {$jutsu_data['name']}
            {else if $jutsu_data['targeting_type'] == 'ally_and_self'}
              contentControl="jutsu_options_target_ally_and_selfs/jutsu_options_weapons_{$jutsu_id}/jutsu_options_submit" value="{$jutsu_id}"> {$jutsu_data['name']}
            {else if $jutsu_data['targeting_type'] == 'ally'}
              contentControl="jutsu_options_target_allys/jutsu_options_weapons_{$jutsu_id}/jutsu_options_submit" value="{$jutsu_id}"> {$jutsu_data['name']}
            {else}
              contentControl="jutsu_options_target_alls/jutsu_options_weapons_{$jutsu_id}/jutsu_options_submit" value="{$jutsu_id}"> {$jutsu_data['name']}           
            {/if}
    	      </option>
        
          {else if $jutsu_data['reagent_status'] == false}
    	      contentControl="{$jutsu_data['targeting_type']}" value="{$jutsu_id}" disable="jutsu_options_submit">
    	      {$jutsu_data['name']} (out of reagents)
    	      </option>
          {else if $jutsu_data['max_uses'] <= $jutsu_data['uses']}
    	      contentControl="{$jutsu_data['targeting_type']}" value="{$jutsu_id}" disable="jutsu_options_submit">
    	      {$jutsu_data['name']} (no more uses)
    	      </option>
          {else}
    	      contentControl="{$jutsu_data['targeting_type']}" value="{$jutsu_id}" disable="jutsu_options_submit">
    	      {$jutsu_data['name']} cooldown({$jutsu_data['cooldown_status']})
    	      </option>
          {/if}
          
        {/foreach}
      </select>

      {foreach $owner['jutsus'] as $jutsu_id => $jutsu_data}
        <contentElement name="jutsu_options_weapons_{$jutsu_id}">
          {if strpos($owner['jutsu_weapon_selects_mobile'][$jutsu_id], "option") !== false}
            {$owner['jutsu_weapon_selects_mobile'][$jutsu_id]}
          {else if $owner['jutsu_weapon_selects_mobile'][$jutsu_id]}
            <headerText>Missing Required Weapons</headerText>
          {/if}
        </contentElement>
      {/foreach}

      <contentElement name="jutsu_options_target_opponents" show="true">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            {if $userdata['team'] != $owner['team']}
              <option value="{$username}"
                {if $userdata['show_count'] == 'yes'}
                  >{$username}
                {else if $userdata['show_count'] == 'no'}
                  {if strpos($username,'#') !== false}
                    >{substr($username,0,strpos($username,'#') - 1)}
                  {else}
                    >{$username}
                  {/if}
                {/if}
              </option>
            {/if}
          {/foreach}
        </select>
    
      </contentElement>
    
      <contentElement name="jutsu_options_target_allys">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            {if $userdata['team'] == $owner['team'] && $username != $owner['name']}
              <option value="{$username}"
                {if $userdata['show_count'] == 'yes'}
                  >{$username}
                {else if $userdata['show_count'] == 'no'}
                  {if strpos($username,'#') !== false}
                    >{substr($username,0,strpos($username,'#') - 1)}
                  {else}
                    >{$username}
                  {/if}
                {/if}
              </option>
            {/if}
          {/foreach}
        </select>
    
      </contentElement>
    
      <contentElement name="jutsu_options_target_ally_and_selfs">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            {if $userdata['team'] == $owner['team']}
              <option value="{$username}"
                {if $userdata['show_count'] == 'yes'}
                  >{$username}
                {else if $userdata['show_count'] == 'no'}
                  {if strpos($username,'#') !== false}
                    >{substr($username,0,strpos($username,'#') - 1)}
                  {else}
                    >{$username}
                  {/if}
                {/if}
              </option>
            {/if}
          {/foreach}
        </select>

      </contentElement>
    
      <contentElement name="jutsu_options_target_selfs">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            {if $username == $owner['name']}
              <option value="{$username}"
                {if $userdata['show_count'] == 'yes'}
                  >{$username}
                {else if $userdata['show_count'] == 'no'}
                  {if strpos($username,'#') !== false}
                    >{substr($username,0,strpos($username,'#') - 1)}
                  {else}
                    >{$username}
                  {/if}
                {/if}
              </option>
            {/if}
          {/foreach}
        </select>
        
      </contentElement>
    
      <contentElement name="jutsu_options_target_others">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            {if $username != $owner['name']}
              <option value="{$username}"
                {if $userdata['show_count'] == 'yes'}
                  >{$username}
                {else if $userdata['show_count'] == 'no'}
                  {if strpos($username,'#') !== false}
                    >{substr($username,0,strpos($username,'#') - 1)}
                  {else}
                    >{$username}
                  {/if}
                {/if}
              </option>
            {/if}
          {/foreach}
        </select>

      </contentElement>
    
      <contentElement name="jutsu_options_target_alls">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            <option value="{$username}"
              {if $userdata['show_count'] == 'yes'}
                >{$username}
              {else if $userdata['show_count'] == 'no'}
                {if strpos($username,'#') !== false}
                  >{substr($username,0,strpos($username,'#') - 1)}
                {else}
                  >{$username}
                {/if}
              {/if}
            </option>
         {/foreach}
        </select>
      </contentElement>

      <contentElement name="jutsu_options_submit" show="true">
          <submit type="submit" name="appButton" value="Submit       "></submit>
      </contentElement>
      
    </contentElement>
    
    <!-- weapons action here -->
    <contentElement name="weapon_options">
      <select name="weapon_attack_select" contentControl="true">
        {foreach $owner['equipment'] as $equipment_id => $equipment_data}
          {if $equipment_data['type'] == 'weapon'}
            {if $equipment_data['element'] == '' || $equipment_data['element'] == 'none' || $equipment_data['element'] == 'None' || (
            in_array($equipment_data['element'], $owner['elements']) && $owner['element_masteries'][ array_search($equipment_data['element'], $owner['elements']) ] > 25)}
              {if $owner['equipment_used'][ $equipment_data['iid'] ]['uses'] < $owner['equipment_used'][ $equipment_data['iid'] ]['max_uses']}
          	<option value="{$equipment_id}"
                        
                  {if $equipment_data['targeting_type'] == 'target'}
                    contentControl="weapon_options_target_alls/weapon_action_submit" > {$equipment_data['name']}
                  {else if $equipment_data['targeting_type'] == 'opponent'}
                    contentControl="weapon_options_target_opponents/weapon_action_submit" > {$equipment_data['name']}
                  {else if $equipment_data['targeting_type'] == 'self'}
                    contentControl="weapon_options_target_selfs/weapon_action_submit" > {$equipment_data['name']}
                  {else if $equipment_data['targeting_type'] == 'other'}
                    contentControl="weapon_options_target_others/weapon_action_submit" > {$equipment_data['name']}
                  {else if $equipment_data['targeting_type'] == 'ally_and_self'}
                    contentControl="weapon_options_target_ally_and_selfs/weapon_action_submit" > {$equipment_data['name']}
                  {else if $equipment_data['targeting_type'] == 'ally'}
                    contentControl="weapon_options_target_allys/weapon_action_submit" > {$equipment_data['name']}
                  {else}
                    contentControl="weapon_options_target_alls/weapon_action_submit" > {$equipment_data['name']}          
                  {/if}
                        
                </option>
              {else}
                <option disable="weapon_action_submit" contentControl="{$equipment_data['targeting_type']}" value="{$equipment_id}">{$equipment_data['name']} (no more uses)</option>
              {/if}
            {/if}
          {/if}
        {/foreach}
      </select>
      
      <contentElement name="weapon_options_target_opponents" show="true">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            {if $userdata['team'] != $owner['team']}
              <option value="{$username}"
                {if $userdata['show_count'] == 'yes'}
                  >{$username}
                {else if $userdata['show_count'] == 'no'}
                  {if strpos($username,'#') !== false}
                    >{substr($username,0,strpos($username,'#') - 1)}
                  {else}
                    >{$username}
                  {/if}
                {/if}
              </option>
            {/if}
          {/foreach}
        </select>
      </contentElement>
    
      <contentElement name="weapon_options_target_allys">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            {if $userdata['team'] == $owner['team'] && $username != $owner['name']}
              <option value="{$username}"
                {if $userdata['show_count'] == 'yes'}
                  >{$username}
                {else if $userdata['show_count'] == 'no'}
                  {if strpos($username,'#') !== false}
                    >{substr($username,0,strpos($username,'#') - 1)}
                  {else}
                    >{$username}
                  {/if}
                {/if}
              </option>
            {/if}
          {/foreach}
        </select>
      </contentElement>
    
      <contentElement name="weapon_options_target_ally_and_selfs">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            {if $userdata['team'] == $owner['team']}
              <option value="{$username}"
                {if $userdata['show_count'] == 'yes'}
                  >{$username}
                {else if $userdata['show_count'] == 'no'}
                  {if strpos($username,'#') !== false}
                    >{substr($username,0,strpos($username,'#') - 1)}
                  {else}
                    >{$username}
                  {/if}
                {/if}
              </option>
            {/if}
          {/foreach}
        </select>
      </contentElement>
    
      <contentElement name="weapon_options_target_selfs">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            {if $username == $owner['name']}
              <option value="{$username}"
                {if $userdata['show_count'] == 'yes'}
                  >{$username}
                {else if $userdata['show_count'] == 'no'}
                  {if strpos($username,'#') !== false}
                    >{substr($username,0,strpos($username,'#') - 1)}
                  {else}
                    >{$username}
                  {/if}
                {/if}
              </option>
            {/if}
          {/foreach}
        </select>
      </contentElement>
    
      <contentElement name="weapon_options_target_others">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            {if $username != $owner['name']}
              <option value="{$username}"
                {if $userdata['show_count'] == 'yes'}
                  >{$username}
                {else if $userdata['show_count'] == 'no'}
                  {if strpos($username,'#') !== false}
                    >{substr($username,0,strpos($username,'#') - 1)}
                  {else}
                    >{$username}
                  {/if}
                {/if}
              </option>
            {/if}
          {/foreach}
        </select>
      </contentElement>
    
      <contentElement name="weapon_options_target_alls">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            <option value="{$username}"
              {if $userdata['show_count'] == 'yes'}
                >{$username}
              {else if $userdata['show_count'] == 'no'}
                {if strpos($username,'#') !== false}
                  >{substr($username,0,strpos($username,'#') - 1)}
                {else}
                  >{$username}
                {/if}
              {/if}
            </option>
          {/foreach}
        </select>
      </contentElement>
      
      <contentElement name="weapon_action_submit">
        <submit type="submit" name="appButton" value="Submit     "></submit>
      </contentElement>
      
    </contentElement>
    
    <!-- item action here -->
    <contentElement name="item_options">
      <select name="item_attack_select" contentControl="true">
        {foreach $owner['items'] as $invin_id => $item}
          {if $item['stack'] != 0 }
            {if $item['max_uses'] - $owner['items_used'][$item['iid']] > 0}
              <option value="{$invin_id}"
                {if $item['targeting_type'] == 'target'}
                  contentControl="item_options_target_alls/item_action_submit" > {$item['name']}
                {else if $item['targeting_type'] == 'opponent'}
                  contentControl="item_options_target_opponents/item_action_submit" > {$item['name']}
                {else if $item['targeting_type'] == 'self'}
                  contentControl="item_options_target_selfs/item_action_submit" > {$item['name']}
                {else if $item['targeting_type'] == 'other'}
                  contentControl="item_options_target_others/item_action_submit" > {$item['name']}
                {else if $item['targeting_type'] == 'ally_and_self'}
                  contentControl="item_options_target_ally_and_selfs/item_action_submit" > {$item['name']}
                {else if $item['targeting_type'] == 'ally'}
                  contentControl="item_options_target_allys/item_action_submit" > {$item['name']}
                {else}
                  contentControl="item_options_target_alls/item_action_submit" > {$item['name']}
                {/if}
    
              </option>
            {else}
              <option disable="item_action_submit" contentControl="{$item['targeting_type']}" value="{$invin_id}">{$item['name']} (no more uses)</option>
            {/if}
          {/if}
        {/foreach}
      </select>
      
      <contentElement name="item_options_target_opponents" show="true">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            {if $userdata['team'] != $owner['team']}
              <option value="{$username}"
                {if $userdata['show_count'] == 'yes'}
                  >{$username}
                {else if $userdata['show_count'] == 'no'}
                  {if strpos($username,'#') !== false}
                    >{substr($username,0,strpos($username,'#') - 1)}
                  {else}
                    >{$username}
                  {/if}
                {/if}
              </option>
            {/if}
          {/foreach}
        </select>
      </contentElement>
    
      <contentElement name="item_options_target_allys">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            {if $userdata['team'] == $owner['team'] && $username != $owner['name']}
              <option value="{$username}"
                {if $userdata['show_count'] == 'yes'}
                  >{$username}
                {else if $userdata['show_count'] == 'no'}
                  {if strpos($username,'#') !== false}
                    >{substr($username,0,strpos($username,'#') - 1)}
                  {else}
                    >{$username}
                  {/if}
                {/if}
              </option>
            {/if}
          {/foreach}
        </select>
      </contentElement>
    
      <contentElement name="item_options_target_ally_and_selfs">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            {if $userdata['team'] == $owner['team']}
              <option value="{$username}"
                {if $userdata['show_count'] == 'yes'}
                  >{$username}
                {else if $userdata['show_count'] == 'no'}
                  {if strpos($username,'#') !== false}
                    >{substr($username,0,strpos($username,'#') - 1)}
                  {else}
                    >{$username}
                  {/if}
                {/if}
              </option>
            {/if}
          {/foreach}
        </select>
      </contentElement>
    
      <contentElement name="item_options_target_selfs">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            {if $username == $owner['name']}
              <option value="{$username}"
                {if $userdata['show_count'] == 'yes'}
                  >{$username}
                {else if $userdata['show_count'] == 'no'}
                  {if strpos($username,'#') !== false}
                    >{substr($username,0,strpos($username,'#') - 1)}
                  {else}
                    >{$username}
                  {/if}
                {/if}
              </option>
            {/if}
          {/foreach}
        </select>
      </contentElement>
    
      <contentElement name="item_options_target_others">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            {if $username != $owner['name']}
              <option value="{$username}"
                {if $userdata['show_count'] == 'yes'}
                  >{$username}
                {else if $userdata['show_count'] == 'no'}
                  {if strpos($username,'#') !== false}
                    >{substr($username,0,strpos($username,'#') - 1)}
                  {else}
                    >{$username}
                  {/if}
                {/if}
              </option>
            {/if}
          {/foreach}
        </select>
      </contentElement>
    
      <contentElement name="item_options_target_alls">
        <select name="target_select">
          {foreach $users as $username => $userdata}
            <option value="{$username}"
              {if $userdata['show_count'] == 'yes'}
                >{$username}
              {else if $userdata['show_count'] == 'no'}
                {if strpos($username,'#') !== false}
                  >{substr($username,0,strpos($username,'#') - 1)}
                {else}
                  >{$username}
                {/if}
              {/if}
            </option>
          {/foreach}
        </select>
      </contentElement>
      
      <contentElement name="item_action_submit">
        <submit type="submit" name="appButton" value="Submit     "></submit>
      </contentElement>
      
    </contentElement>
    
    
    <!-- flee targets here -->
    {$check = false}
    <contentElement name="flee_targets">
      <select name="target_select">
        {foreach $users as $username => $userdata}
          {if $userdata['team'] != $owner['team']}
            {$check = true}
            <option value="{$username}"
              {if $userdata['show_count'] == 'yes'}
                >{$username}
              {else if $userdata['show_count'] == 'no'}
                {if strpos($username,'#') !== false}
                  >{substr($username,0,strpos($username,'#') - 1)}
                {else}
                  >{$username}
                {/if}
              {/if}
            </option>
          {/if}
        {/foreach}
      </select>
    
      {if $check}
        <submit type="submit" name="appButton" value="Submit     "></submit>
      {/if}
    </contentElement>
    
    
    <!-- call for help targets here -->
    {$check = false}
    <contentElement name="call_fore_help_targets">
      <select name="target_select">
        {foreach $users as $username => $userdata}
          {if $userdata['team'] != $owner['team']}
            {$check = true}
            <option value="{$username}"
              {if $userdata['show_count'] == 'yes'}
                >{$username}
              {else if $userdata['show_count'] == 'no'}
                {if strpos($username,'#') !== false}
                  >{substr($username,0,strpos($username,'#') - 1)}
                {else}
                  >{$username}
                {/if}
              {/if}
            </option>
          {/if}
        {/foreach}
      </select>
    
      {if $check}
        <submit type="submit" name="appButton" value="Submit     "></submit>
      {/if}
    </contentElement>
  </td>
</tr>



<tr><td></td></tr>


<tr>
  <td contentFit="true">
    <!-- //////////////////// battle log \\\\\\\\\\\\\\\\\\\\ -->
    <a color="darkbrown" contentControl="battleLog">Battle Log</a>
    <contentElement name="battleLog">
      <!-- foreach round -->
      {foreach array_combine( array_reverse( array_keys( $battle_log )), array_reverse( array_values( $battle_log ) ) ) as $round_number => $round_users}
        {if $turn_counter - $round_number <= $turn_log_length}
          <tr>
            <td>
              <a color="fadedblack" contentControl="Round-{$round_number+1}">Round-{$round_number+1}</a>
              
              <contentElement name="Round-{$round_number+1}">
          
                <!-- foreach user -->
                {foreach $round_users as $username => $userdata}
                  <tr>
                    <td>
                      <a color="{if $username == $owner['name']}blue{else if $owner['team'] == $round_users[ $username ]['team']}green{else}red{/if}" contentControl="Round-{$round_number+1}-User-{$username}">{$username}</a>
                      
                      <contentElement name="Round-{$round_number+1}-User-{str_replace(' ','-',$username)}">
                        
                        <!-- displaying action informaiton -->
                        <tr color="dim">
                          <td>
                            {if $userdata['show_count'] == 'yes'}
                              {assign var=the_users_name value=$username}
                            {else}
                              {if strpos($username,'#') !== false}
                                {assign var=the_users_name value=substr($username,0,strpos($username,'#') - 1)}
                              {else}
                                {assign var=the_users_name value= $username}
                              {/if}
                            {/if}
                            
                            {if $round_users[$userdata['target']]['show_count'] == 'yes'}
                              {assign var=the_targets_name value=$userdata['target']}
                            {else}
                              {if strpos($userdata['target'],'#') !== false}
                                {assign var=the_targets_name value=substr($userdata['target'],0,strpos($userdata['target'],'#') - 1)}
                              {else}
                                {assign var=the_targets_name value=$userdata['target']}
                              {/if}
                            {/if}
          
                            {if $userdata['failure'] == 'failure'}
                            
                              <text>was defeated before they could take their action.</text>
                              
                            {else if $userdata['type'] == 'respondent'}
          
                              <text>{$userdata['order']}- {$the_users_name} has responded to the call for help and has attacked {$the_targets_name}!</text>
                            
                            {else if $userdata['name'] == 'Basic Attack'}
                            
                              <text>{$userdata['order']}- {$the_users_name} attacked {$the_targets_name}!</text>
                            
                            {else if $userdata['type'] == 'jutsu'}
            
                              <text>{$userdata['order']}- {$the_users_name} attacked {$the_targets_name} with the jutsu {$userdata['name']}!</text>
                            
                            {else if $userdata['type'] == 'weapon'}
                            
                              <text>{$userdata['order']}- {$the_users_name} attacked {$the_targets_name} with their {$userdata['name']}!</text>
                            
                            {else if $userdata['type'] == 'item'}
                            
                              <text>{$userdata['order']}- {$the_users_name} used {$userdata['name']} on {$the_targets_name}!</text>
                            
                            {else if $userdata['type'] == 'flee' }                                 
                              <text>{$userdata['order']}- {$the_users_name} tried to flee from the battle! </text>
                            {else if $userdata['type'] == 'call_for_help'}
                              <text>{$userdata['order']}- {$the_users_name} called for help! </text>
                            {else if $userdata['type'] == 'stunned'}
                              <text>{$userdata['order']}- {$the_users_name} was stunned this turn. </text>
                            {else}
                              <text>?- {$the_users_name} ?. </text>
                            {/if}
                          </td>
                        </tr>
          
                        <!-- displaying if a user was killed by this action -->
                        {if isset($userdata['killed'])}
                          <tr color="{if $owner['team'] != $round_users[ $userdata['killed'] ]['team']}blue{else}red{/if}">
                            <td>
                              {if $round_users[$userdata['killed']]['show_count'] == 'yes'}
                                <text>{$the_users_name} defeated {$userdata['killed']}</text>
                              {else}
                                {if strpos($userdata['killed'],'#') !== false}
                                  <text>{$the_users_name} defeated {substr($userdata['killed'],0,strpos($userdata['killed'],'#') - 1)}</text>
                                {else}
                                  <text>{$the_users_name} defeated {$userdata['killed']}</text>
                                {/if}
                              {/if}
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- if this user died -->
                        {if isset($userdata['died']) }
                          <tr color="{if $owner['team'] == $round_users[ $userdata['killed'] ]['team']}blue{else}red{/if}">
                            <td >
                              {if $round_users[$userdata['died']]['show_count'] == 'yes'}
                                <text>{$the_users_name} was defeated at the hands of {$userdata['died']}</text>
                              {else}
                                {if strpos($userdata['died'],'#') !== false}
                                  <text>{$the_users_name} was defeated at the hands of {substr($userdata['died'],0,strpos($userdata['died'],'#') - 1)}</text>
                                {else}
                                  <text>{$the_users_name} was defeated at the hands of {$userdata['died']}</text>
                                {/if}
                              {/if}
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- jutsu description -->
                        {if isset($userdata['jutsu_description']) && $userdata['failure'] != 'failure'}
                          <tr color="darkbrown">
                            <td>
                              <text>{$userdata['jutsu_description']}</text>
                            </td>
                          </tr>
                        {/if}
          
                        <!-- damage delt -->
                        {if isset($userdata['damage_delt'])}
                          {foreach $userdata['damage_delt'] as $damage_delt}
                            <tr color="{if $owner['team'] == $round_users[ $username ]['team']}blue{else}red{/if}">
                              <td>
                                <text {if $damage_delt['crit'] == true}color="orange"{/if}>{if $damage_delt['oneHitKill'] === true}one hit kill{else}{$damage_delt['type']} damage dealt{/if}{if $damage_delt['aoe']} from aoe{/if}: {$damage_delt['amount']}</text>
                              </td>
                            </tr>
                          {/foreach}
                        {/if}
                        
                        <!-- fled -->
                        {if isset($userdata['fled']) && $userdata['failure'] != 'failure'}
                          {if $userdata['fled'] == true}
                            <tr color="{if $owner['team'] == $round_users[ $userdata['killed'] ]['team']}blue{else}red{/if}">
                              <td>
                                <text>The Attempt was Successful.</text>
                              </td>
                            </tr>
                          {else}
                            <tr color="{if $owner['team'] != $round_users[ $userdata['killed'] ]['team']}blue{else}red{/if}">
                              <td>
                                <text>The Attempt Failed.</text>
                              </td>
                            </tr>
                          {/if}
                        {/if}
                        
                        <!-- damage over time -->
                        {if isset($userdata['damage_over_time_delt']) }
                          {foreach $userdata['damage_over_time_delt'] as $damage_over_time_delt}
                            <tr color="{if $owner['team'] == $round_users[ $username ]['team']}blue{else}red{/if}">
                              <td>
                                <text {if $damage_over_time_delt['crit'] == true}color="orange"{/if}>{$damage_over_time_delt['type']} damage over time dealt{if $damage_over_time_delt['aoe']} from aoe{/if}: {$damage_over_time_delt['amount']}</text>
                              </td>
                            </tr>
                          {/foreach}
                        {/if}
                        
                        <!-- leach -->
                        {if isset($userdata['leach'])}
                          <tr color="{if $owner['team'] != $round_users[ $username ]['team']}red{else}blue{/if}">
                            <td>
                              <text>health leached: {$userdata['leach']}</text>
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- heal_delt -->
                        {if isset($userdata['heal_delt'])}
                          <tr color="{if $owner['team'] != $round_users[ $username ]['team']}red{else}blue{/if}">
                            <td>
                              <text>health restored: {$userdata['heal_delt']}</text>
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- heal_over_time_delt -->
                        {if isset($userdata['heal_over_time_delt'])}
                          <tr color="{if $owner['team'] != $round_users[ $username ]['team']}red{else}blue{/if}">
                            <td>
                              <text>health restored over time: {$userdata['heal_over_time_delt']}</text>
                            </td>
                          </tr>
                        {/if}
          
                        <!-- recoil -->
                        {if isset($userdata['recoil'])}
                        <tr color="{if $owner['team'] == $round_users[ $username ]['team']}red{else}blue{/if}">
                          <td>
                            <text>recoil damage: {$userdata['recoil']}</text>
                          </td>
                        </tr>
                        {/if}
          
                        <!-- absorb -->
                        {if isset($userdata['absorb'])}
                          <tr color="{if $owner['team'] == $round_users[ $username ]['team']}red{else}blue{/if}">
                            <td>
                              <text>damage absorbed: {$userdata['absorb']}</text>
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- reflect -->
                        {if isset($userdata['reflect'])}
                          <tr color="{if $owner['team'] == $round_users[ $username ]['team']}red{else}blue{/if}">
                            <td>
                              <text>damage reflected: {$userdata['reflect']}</text>
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- rob -->
                        {if is_numeric($userdata['rob']) && $userdata['failure'] != 'failure'}
                          <tr color ="{if $owner['team'] == $round_users[ $username ]['team'] }blue{else}red{/if}">
                            <td>
                              <text>stole: {$userdata['rob']}</text>
                            </td>
                          </tr>
                        {else if $userdata['rob'] == 'fail' && $userdata['failure'] != 'failure'}
                          <tr color="{if $owner['team'] != $round_users[ $username ]['team'] }blue{else}red{/if}">
                            <td>
                              <text>Failed to rob anything.</text>
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- disable -->
                        {if isset($userdata['disable']) }
                          <tr color="{if $owner['team'] == $round_users[ $username ]['team'] }blue{else}red{/if}">
                            <td>
                              <text>disabled: {$userdata['disable']}</text>
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- stagger -->
                        {if isset($userdata['stagger']) }
                          <tr color="{if $owner['team'] == $round_users[ $username ]['team'] }blue{else}red{/if}">
                            <td>
                              <text>staggered: {$userdata['stagger']}</text>
                            </td>
                          </tr>
                        {/if}
                        
                        <!-- stun -->
                        {if isset($userdata['stun']) }
                          <tr color="{if $owner['team'] == $round_users[ $username ]['team'] }blue{else}red{/if}">
                            <td>
                              <text>stunned: {$userdata['stun']}</text>
                            </td>
                          </tr>
                        {/if}
          
                      </contentElement>
                      
                    </td>
                  </tr>
                {/foreach}
                
              </contentElement>
              
            </td>
          </tr>
        {/if}
      {/foreach}
    </contentElement>
  </td>
</tr>

<tr><td></td></tr>
<!-- //////////////////// dsr \\\\\\\\\\\\\\\\\\\\ -->
<tr>
  <td>
    <a color="fadedblack" contentControl="dsr">
      Damage by Survivability Raiting
    </a>
    <contentElement name="dsr">
      <br>
      <tr>
        <td>
          <text>
            Your DSR: <b>{base_convert(floor(sqrt($owner['DSR']+$rng+4)), 10, 9)}</b>
          </text>
          <br>
          <text>
            Your Team's DSR: <b>{base_convert(floor(sqrt($friendlyDSR+$rng+4)), 10, 9)}</b>
          </text>
          <br>
          <text>
            {if $cfhRange1 != 'N/A'}
              CFH Range: <b>{base_convert(floor(sqrt($cfhRange1+$rng+4)), 10, 9)}</b> to {base_convert(floor(sqrt($cfhRange2+$rng+4)), 10, 9)}</b>
            {else}
              CFH Range: N/A
            {/if}
          </text>
          <br>
          <text>
            Opponent Team's DSR: <b>{base_convert(floor(sqrt($opponentDSR+$rng+4)), 10, 9)}</b>
          </text>
          <br>
        </td>
      </tr>
    </contentElement>
  </td>
</tr>    
