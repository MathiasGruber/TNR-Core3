<div align="center">
    <table width="95%" class="table">
        <tr>
            <td colspan="3" class="subHeader">Item Details</td>
        </tr>
        <tr>
            <td style="text-align:left;vertical-align:middle;">
                <b>Name:</b> {$item_data[0]['name']}<br><br>
                <b>Stackable:</b> {if $item_data[0]['stack_size'] == 1}No{else}Yes ({$item_data[0]['stack_size']}){/if}<br><br>
                <b>Type:</b>
                {if $item_data[0]['type'] == 'armor'}
                    {$item_data[0]['armor_types']}
                {elseif $item_data[0]['type'] == 'weapon'}
                    Weapon: {$item_data[0]['weapon_classifications']}
                {else}
                    {$item_data[0]['type']}
                {/if}
                {if $item_data[0]['type'] == 'weapon' || $item_data[0]['type'] == 'armor'}
                  <br><br>
                  <b>Armor: </b>{$item_data[0]['armor']}<br><br>
                  <b>Accuracy: </b>{$item_data[0]['accuracy']}<br><br>
                  <b>Chakra Power: </b>{$item_data[0]['chakra_power']}<br><br>
                  <b>Mastery: </b>{$item_data[0]['mastery']}
                {/if}
          </td>
            <td style="text-align:left;vertical-align:middle;">
                <b>Required Rank:</b> {if $item_data[0]['required_rank'] == '1'}
                                        Academy Student
                                    {elseif $item_data[0]['required_rank'] == '2'}
                                        Genin
                                    {elseif $item_data[0]['required_rank'] == '3'}
                                        Chuunin
                                    {elseif $item_data[0]['required_rank'] == '4'}
                                        Jounin
                                    {elseif $item_data[0]['required_rank'] == '5'}
                                        Elite Jounin
                                    {/if}<br><br>
                <b>Base Price: </b> {$item_data[0]['price']} Ryo<br><br>
                <b>Crafted Durability:</b> {$item_data[0]['durability']}
                {if $item_data[0]['type'] == 'weapon' || $item_data[0]['type'] == 'armor'}
                  <br><br>
                  <b>Stability: </b>{$item_data[0]['stability']}<br><br>
                  <b>Expertise: </b>{$item_data[0]['expertise']}<br><br>
                  <b>Critical Strike: </b>{$item_data[0]['critical_strike']}<br><br>
                  {if strpos($item_data[0]['use'], 'REPEL') !== false }
                    {$explode = explode("REPEL:",$item_data[0]['use'])}
                    {$repel = $explode[1]}
                    {$explode = explode(";",$repel)}
                    {$repel = $explode[0]}
                  {elseif strpos($item_data[0]['use2'], 'REPEL') !== false}
                    {$explode = explode("REPEL:",$item_data[0]['use2'])}
                    {$repel = $explode[1]}
                    {$explode = explode(";",$repel)}
                    {$repel = $explode[0]}
                  {else}
                    {$repel = '0'}
                  {/if}

                    {if $repel > 0}
                        <b>Repel: </b> {$repel}%
                    {else}
                        <b>Attract: </b> {ABS($repel)}%
                    {/if}
                {/if}
          </td>
            <td>
                {assign var="itemfile" value="{$s3}/items/{$smarty.get.iid}.png"}
                {if file_exists($itemfile)}
                    <img style="border:0px solid #000000;max-height:100px;max-width:100px;height:auto;width:auto;" src="{$s3}/items/{$smarty.get.iid}.png">
                {else}
                    &nbsp;
                {/if}
            </td>
        </tr>
        <tr>
            <td colspan="3" class="tableColumns tdBorder">Description: </td>
        </tr>
        <tr>
            <td colspan="3" align="center">{$Details}</td>
        </tr>
    </table>
  
    
    {assign var="itemfile" value="{$s3}/items/large{$smarty.get.iid}.png"}
    {if file_exists($itemfile)}
        <table width="95%" class="table">
            <tr>
                <td class="tableColumns tdBorder">
                    {$item_data[0]['name']}:
                </td>
            </tr>
            <tr>
                <td>
                    <br>
                    <img style="border:0px solid #000000;max-width:500px;height:auto;width:auto;" src="{$s3}/items/large{$smarty.get.iid}.png">
                    <br>
                </td>
            </tr>
        </table>
    {/if}
  
    {if $item_data[0]['type'] == 'weapon' || $item_data[0]['type'] == 'armor'}
      {if strlen($effectsOnEquip) > 20}
        <table width="95%" class="table">
          <tr>
              <td colspan="4" class="tableColumns" >
                  Item Effects When Equiped
              </td>
          </tr>
          <tr>
              <td colspan="4"  style="text-align:left;font-size:10px;">
                  <ul>
                      {str_replace('-new-line-', '\r\n', $effectsOnEquip)}
                  </ul>

              </td>
          </tr>
        </table>
      {/if}
    
      {if strlen($effectsOnUse) > 20}
        <table width="95%" class="table">
          <tr>
              <td colspan="4" class="tableColumns" >
                  Item Effects When Used
              </td>
          </tr>
          <tr>
              <td colspan="4"  style="text-align:left;font-size:10px;">
                  <ul>
                      {str_replace('-new-line-', "\r\n", $effectsOnUse)}
                  </ul>

              </td>
          </tr>
        </table>
      {/if}
      
      {if strlen($effectsOnJutsu) > 20}
        <table width="95%" class="table">
          <tr>
              <td colspan="4" class="tableColumns" >
                  Item Effects When Used with a jutsu
              </td>
          </tr>
          <tr>
              <td colspan="4"  style="text-align:left;font-size:10px;">
                  <ul>
                      {str_replace('-new-line-', '\r\n', $effectsOnJutsu)}
                  </ul>

              </td>
          </tr>
        </table>
      {/if}
    {/if}
  
    <a href="?id={$smarty.get.id}" class="returnLink">Return</a><br><br>
</div>