{literal}
    <style type="text/css">
        .battleOption td{text-align:left;padding-left:20px;}
    </style>
{/literal}

<form id="battleForm" class="battleForm" method="post" action="?id={$smarty.get.id}&act=do">
    <table width="95%" class="table battleOption" cellpadding="0">
        <tr>
            <td class="subHeader"> Battle options</td>
        </tr>
        <tr>
            <td class="tableColumns actionEntry">
                <b><a id="h_standardAction">Standard Actions</a></b>
            </td>
        </tr>
        <tr>
            <td class="row2 standardAction jsHide">
                <input name="action" type="radio" value="STTAI|1" {$simpleChecks[0]} data-role="none" /> Fist
            </td>
        </tr>
        <tr>
            <td class="row1 standardAction jsHide">
                <input name="action" type="radio" value="STCHA|2" {$simpleChecks[1]} data-role="none" /> Chakra attack
            </td>
        </tr>
        {if isset($canFLEE)}
        <tr>
            <td class="row2 standardAction jsHide">
                <input name="action" type="radio" value="FLEE|3" {$simpleChecks[2]} data-role="none" /> Flee
            </td>
        </tr>
        {/if}
        {if isset($canCFH)}
            <tr>
                <td class="row1 standardAction jsHide">
                    {if $canCFHinfo == "true"}
                        <input name="action" type="radio" value="HELP|4" {$simpleChecks[3]} data-role="none" /> Call for help<br>
                    {else}
                        CFH: {$canCFHinfo}
                    {/if}
                </td>
            </tr>
        {/if}
        <tr>
            <td class="tableColumns actionEntry">
                <b><a id="h_itemAction">Items &amp; weapons</a></b>
            </td>
        </tr>
        {if isset($userItems)}
            {foreach $userItems as $entry}
                <tr>
                    <td class="{cycle values="row2,row1"} itemAction jsHide" >
                        <input name="action" type="radio" value="{$entry['value']}" data-role="none" {$entry['check']} /> {$entry['name']}
                        {if isset($entry['durabilityPoints']) && $entry['durabilityPoints'] > 0}
                            <br>(<i>Durability: {if $entry['infinity_durability']} &infin; {else} {$entry['durabilityPoints']}{/if}</i>)
                        {/if}
                    </td>
                </tr>
            {/foreach}
        {else}
            <tr>
                <td class="row2">
                    None equipped
                </td>
            </tr>
        {/if}
        <tr>
            <td class="tableColumns actionEntry">
                <b><a id="h_jutsuAction">Jutsu</a></b>
            </td>
        </tr>
        {if isset($userJutsus)}
            {foreach $userJutsus as $entry}
                <tr>
                    <td class="{cycle values="row2,row1"} jutsuAction jsHide">
                        <input name="action" type="radio" value="{$entry['value']}" data-role="none" {$entry['check']} /> {$entry['name']}
                        {if isset($entry['curCooldown']) && $entry['curCooldown'] > 0}
                            <br>(<i>Cooldown: {$entry['curCooldown']} turns</i>)
                        {/if}
                    </td>
                </tr>
            {/foreach}
        {else}
            <tr>
                <td class="row2">
                    None tagged
                </td>
            </tr>
        {/if}
        <tr>
            <td class="tableColumns targetEntry">
                <b><a id="h_friendTarget">Friendly Targets</a></b>
            </td>
        </tr>
        {if isset($friendSide)}
            {foreach $friendSide as $entry}
                <tr>
                    <td class="{cycle values="row2,row1"} friendTarget jsHide" >
                        <input name="target" type="radio" value="{$entry['value']}" data-role="none" {$entry['check']} /> {$entry['name']}
                    </td>
                </tr>
            {/foreach}
        {/if}
        <tr>
            <td class="tableColumns targetEntry">
                <b><a id="h_enemyTarget">Enemy Targets</a></b>
            </td>
        </tr>
        {if isset($enemySide)}
            {foreach $enemySide as $entry}
                <tr>
                    <td class="{cycle values="row2,row1"} enemyTarget jsHide" >
                        <input name="target" type="radio" value="{$entry['value']}" data-role="none" {$entry['check']} /> {$entry['name']}
                    </td>
                </tr>
            {/foreach}
        {/if}

        <tr>
            <td class="tableColumns" style="border-top:1px solid #000;"><b>Submit Action</b></td>
        </tr>
        <tr>
            <td style="padding:10px;" class="row2" align="center">                
                <input type="hidden" name="form_token" value="{$form_token}" />
                <input id="submit" type="submit" name="Submit" value="Submit">
            </td>
        </tr>
    </table>
</form>
