<tr>
<td>
    <headerText>Item Details</headerText>
</td>
</tr>
<tr>
    <td>
        
<text><b>Name:</b> {$item_data[0]['name']}<br>
<b>Stackable:</b> {if $item_data[0]['stack_size'] == 1}No{else}Yes ({$item_data[0]['stack_size']}){/if}<br>
<b>Type:</b>
{if $item_data[0]['type'] == 'armor'}
{$item_data[0]['armor_types']}
{elseif $item_data[0]['type'] == 'weapon'}
Weapon: {$item_data[0]['weapon_classifications']}
{else}
{$item_data[0]['type']}
{/if}
{if $item_data[0]['type'] == 'weapon' || $item_data[0]['type'] == 'armor'}
<br>
<b>Armor: </b>{$item_data[0]['armor']}<br>
<b>Accuracy: </b>{$item_data[0]['accuracy']}<br>
<b>Chakra Power: </b>{$item_data[0]['chakra_power']}<br>
<b>Mastery: </b>{$item_data[0]['mastery']}
{/if}
</text>

    </td>
    <td>
        
<text>
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
{/if}<br>
<b>Base Price: </b> {$item_data[0]['price']} Ryo<br>
<b>Crafted Durability:</b> {$item_data[0]['durability']}
{if $item_data[0]['type'] == 'weapon' || $item_data[0]['type'] == 'armor'}
<br>
<b>Stability: </b>{$item_data[0]['stability']}<br>
<b>Expertise: </b>{$item_data[0]['expertise']}<br>
<b>Critical Strike: </b>{$item_data[0]['critical_strike']}
{/if}
</text>

    </td>
    
    
{assign var="itemfile" value="{$s3}/items/{$item_data[0]['id']}.png"}
{if file_exists($itemfile)}
    <td><img src="{$s3}/items/{$smarty.get.iid}.png" /></td>
{/if}   
</tr>

<tr>
<td>
    <headerText>Item Description</headerText>
    <text>{$Details}</text>
</td>
</tr>

{assign var="itemfile" value="{$s3}/items/large{$smarty.get.iid}.png"}
{if file_exists($itemfile)}
    <tr>
        <td>
            <headerText>{$item_data[0]['name']}:</headerText>
        </td>
    </tr>
    <tr>
        <td>
            <br>
            <img src="{$s3}/items/large{$smarty.get.iid}.png" />
            <br>
            <br>
        </td>
    </tr>
{/if}

{if $item_data[0]['type'] == 'weapon' || $item_data[0]['type'] == 'armor'}
  {if strlen($effectsOnEquip) > 20}
    <tr>
        <td>
            <headerText>Item Effects When Equiped</headerText>
            <text><i>{$effectsOnEquip}</i></text>
        </td>
    </tr>
  {/if}

  {if strlen($effectsOnUse) > 20}
    <tr>
        <td>
            <headerText>Item Effects When Used</headerText>
            <text><i>{$effectsOnUse}</i></text>
        </td>
    </tr>
  {/if}
  
  {if strlen($effectsOnJutsu) > 20}
    <tr>
        <td>
            <headerText>Item Effects When Used with a jutsu</headerText>
            <text><i>{$effectsOnJutsu}</i></text>
        </td>
    </tr>
  {/if}
{/if}

<tr>
    <td>
        <text><br></text>
        <a href="?id={$smarty.get.id}">Return</a>
    </td>
</tr>
