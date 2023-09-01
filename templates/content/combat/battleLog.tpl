<form name="form1" method="post" action="?id={$smarty.get.id}&act=do">
    <table width="95%" class="table" cellpadding="0">
        <tr>
            <td class="subHeader"> 
                {if isset($battleLogTitle)}
                    {$battleLogTitle}
                {else}
                    Battle Log
                {/if}
            </td>
        </tr>
        {if isset($battleLog[1])}
            
            {foreach name=battleLog from=$battleLog key=key item=round}
                
                <tr>
                    <td class="tableColumns logEntry">
                        {if isset( $battleLog[ $key ][0]['time'] )}{$battleLog[ $key ][0]['time']|date_format:"h:i A"} - {/if}
                        <b><a id="h_battleRound{$key}">Round {$key}</a></b>
                    </td>
                </tr>
                
                <tr{if $smarty.foreach.battleLog.iteration > 1} class="hidden"{/if}>
                    <td style="padding:0px;">
                        <table width="100%" cellpadding="0">
                            {foreach $round as $entry}

                                {if $entry['type'] == "main"}

                                    <tr>
                                        <td class="mainLogMessage logMain" >
                                            {$entry['message']}
                                        </td>
                                    </tr>
                                    
                                    {if $entry['tempLongMessage'] !== ""}
                                        <tr {if $smarty.foreach.battleLog.iteration > 1} class="hidden2"{/if}>
                                            <td class="mainLogMessage logMainSub" >
                                                <b>Description: </b> <i>{$entry['tempLongMessage']}</i>
                                            </td>
                                        </tr>
                                    {else}
                                        <tr><td style="display:none;"></td></tr>
                                    {/if}

                                {elseif $entry['type'] == "subEntry"}

                                    <tr>
                                        <td class="mainLogMessage {$entry['cssClass']}" >
                                            -> {$entry['message']}
                                        </td>
                                    </tr>

                                {/if}

                            {/foreach}
                        </table>
                    </td>
                </tr>
            {/foreach}
            
        {else}
            
            <tr>
                <td class="tableColumns">
                    <b>Waiting for someone to make the first move...</b>
                </td>
            </tr>
            
        {/if}
        
    </table>
</form>