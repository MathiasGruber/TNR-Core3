<tr>
<td>
    <headerText>Battle Summary</headerText>
</td>
</tr>

<tr>
    <td><text>{if isset($summary)}
{foreach name=summary from=$summary key=key item=item}
{if $key == "battle_conclusion"}
{if $item == "won"}
- You have emerged victorious from this battle<br>
{elseif $item == "fled"}
- You have fled the battle<br>
{elseif $item == "lost"}
- You have been defeated<br>
{/if}
{elseif $key == "next_battle" && isset($item) }
{if $item == "champ" }
- There are no more opponents for you in the torn battle arena! <br> &nbsp;&nbsp; Congratulations, you are the champion.. for now..<br>
{elseif $item !== "" && $item !== false}
- Get ready for your next opponent: {$item} .... <a href="?id=41">Fight!</a><br>
{/if}
{elseif $key == "bounty" && isset($item) && $item > 0}
- A bounty was collected from this battle. You earned {$item} ryo!<br>
{elseif $key == "bounty_experience" && isset($item) && $item > 0}
- You earned {$item} bounty hunter experience point{if $item|count > 1}s{/if}!<br>
{elseif $key == "structure" && isset($item) && $item > 0}
- Your team destroyed {$item} structure point{if $item|count > 1}s{/if} of the village you are located in!<br>
{elseif $key == "hstructure" && isset($item) && $item > 0}
- Your team restored {$item} structure point{if $item|count > 1}s{/if} of the village you are located in!<br>
{elseif $key == "pvp" && isset($item) && $item > 0}
- You receive {$item} PvP experience point{if $item|count > 1}s{/if} from this battle!<br>
{elseif $key == "opposing" && isset($item) && $item > 0}
- You have killed {$item} member{if $item|count > 1}s{/if} of an opposing village, increasing the funds of {$userVillage}!<br>
{elseif $key == "allied" && isset($item) && $item > 0}
- You have killed {$item} member{if $item|count > 1}s{/if} of an allied village, decreasing the funds of your {$userVillage}!<br>
{elseif $key == "syndicate" && isset($item) && $item > 0}
- You have killed {$item} member{if $item|count > 1}s{/if} of the Syndicate, increasing the funds of your {$userVillage}!<br>
{elseif $key == "ownFaction" && isset($item) && $item > 0}
- You have killed {$item} member{if $item|count > 1}s{/if} of your own village, decreasing the funds of your {$userVillage}!<br>
{elseif $key == "respectLoss" && isset($item) && $item > 0}
- Because of your actions you have lost {$item} respect points in {$userVillage}!<br>
{elseif $key == "squadA" && isset($item) && $item > 0}
- You have earned {$item} assault point{if $item|count > 1}s{/if} for your ANBU squad.<br>
{elseif $key == "squadD" && isset($item) && $item > 0}
- You have earned {$item} defence point{if $item|count > 1}s{/if} for your ANBU squad.<br>
{elseif $key == "clanpoints" && isset($item) && $item > 0}
- You have earned {$item} clan point{if $item|count > 1}s{/if} for your clan.<br>
{elseif $key == "clanActivity" && isset($item) && $item > 0}
- You have earned {$item} activity point{if $item|count > 1}s{/if} within your clan.<br>
{elseif $key == "kage" && isset($item) && $item > 0}
- You have claimed leadership of {$userVillage}.<br>
{elseif $key == "clanLeader" && isset($item) && $item > 0}
- You have claimed leadership of your clan.<br>
{elseif $key == "kageloss" && isset($item) && $item > 0}
- You have lost 2500 reputation in for losing a leader challenge.<br>
{elseif $key == "end_status" && isset($item) && $item == "hospitalized"}
{if $user_factionType == "Syndicate"}
- You manage to drag yourself away from the battle field, but collapse on the ground.<br>
{else}
- You have been transported to the hospital in your home village.<br>
{/if}
{elseif $key == "exp_gain" && isset($item) && $item > 0}
- You have gained {$item} experience point{if $item|count > 1}s{/if} from this fight.<br>
{elseif $key == "ryo_gain" && isset($item) && $item > 0}
- You have gained {$item} ryo from this fight.<br>
{elseif $key == "health_gain" && isset($item) && $item > 0}
- You have gained {$item} health from this fight.<br>
{elseif $key == "chakra_gain" && isset($item) && $item > 0}
- You have gained {$item} chakra from this fight.<br>
{elseif $key == "stamina_gain" && isset($item) && $item > 0}
- You have gained {$item} stamina from this fight.<br>
{elseif $key == "strength_gain" && isset($item) && $item > 0}
- You have gained {$item} strength from this fight.<br>
{elseif $key == "intelligence_gain" && isset($item) && $item > 0}
- You have gained {$item} intelligence from this fight.<br>
{elseif $key == "willpower_gain" && isset($item) && $item > 0}
- You have gained {$item} willpower from this fight.<br>
{elseif $key == "speed_gain" && isset($item) && $item > 0}
- You have gained {$item} speed from this fight.<br>
{elseif $key == "turnOutlaw" && isset($item) && $item > 0}
- You have become an outlaw <br>
{elseif $key == "mission" && isset($item) && $item !== ""}
{if $item == "success"}
- Winning over this enemy is part of one of your mission assignments.<br>
{elseif $item == "fail"}
- In losing to this enemy, you fail to complete your mission assignment.<br>
{else}
- {$item}<br>
{/if}
{elseif $key == "itemLosses" && isset($item[0]) }
{foreach name=itemLosses from=$item key=itemKey item=itemInfo}
- Your item {$itemInfo}<br>
{/foreach}
{elseif $key == "items" && isset($item[0]) }
{foreach name=itemsGained from=$item key=itemKey item=item2}
- {$item2} has been added to your inventory.<br>
{/foreach}
{/if}
{/foreach}
{/if}
        </text>
    </td>
</tr>
<tr>
    <td>
        <a href="?id=2">Go to Profile</a>
    </td>
</tr>