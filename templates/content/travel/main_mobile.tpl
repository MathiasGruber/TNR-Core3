<tr>
    <td>
        {include file="./messages_mobile.tpl" title="MessageMenu"}
    </td>
</tr>

<travelWidget 
    name="map" 
    x="{$x}" 
    y="{$y}" 
    location="{$location|stripslashes}" 
    owner="{$owner}"
    north="?id={$smarty.get.id}&move=north"
    west="?id={$smarty.get.id}&move=west"
    south="?id={$smarty.get.id}&move=south"
    east="?id={$smarty.get.id}&move=east"
></travelWidget>

