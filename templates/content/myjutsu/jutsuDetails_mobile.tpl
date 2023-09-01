<tr>
    <td>
        <headerText>Jutsu Details</headerText>
    </td>
</tr>
<tr>
    <td>
<text>
<b>Name:</b> {$data['name']}<br>
<b>Attack type:</b> {$data['attack_type']|capitalize}<br>
<b>Type:</b> {$data['jutsu_type']|capitalize}<br>
<b>Element:</b> {$data['element']|capitalize}<br>
<b>Experience:</b> {$data['exp']}
</text>
    </td>
    <td>
<text>
<b>Required Weapons:</b> {$data['required_weapons']}<br>
<b>Required Reagents:</b> {$data['required_reagents']}<br>
<b>Required Rank:</b> {$data['required_rank']}<br>
<b>Uses / Battle:</b> {$data['max_uses']|capitalize}<br>
<b>Village:</b> {$data['village']}<br>
<b>Level:</b> {$data['level']}
</text>
    </td>
</tr>
{if isset($data['specialNote'])}
   <tr>
       <td><text>{$data['specialNote']}</text></td>
    </tr>
{/if}
<tr>
    <td>
        <headerText>Description</headerText>
        <text>{$data['description']}<br></text>
    </td>
</tr>
<tr>
    <td>
        <headerText>Jutsu Effects</headerText>
        <text><i>{$effects}</i></text>
    </td>
</tr>

{if isset($returnLink)}
    <a href="?id={$smarty.get.id}">Return</a>
{/if} 