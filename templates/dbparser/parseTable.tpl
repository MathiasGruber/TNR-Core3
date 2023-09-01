<div align="center">
<form id="form1" name="form1" method="post" action="{$formAction}">
<table width="95%" class="table">
  <tr>
    <td colspan="2" class="subHeader">{$subHeader}</td>
  </tr>
    {if $data}

        {if is_array($collapsedRegions)}
            {$counter_end = count($collapsedRegions) }
        {else}
            {$counter_end = 0}
        {/if}

        {for $counter = 0 to $counter_end}
            {if $counter != 0}
                {$regions = array_keys($collapsedRegions)}
                {$region_name = $regions[$counter - 1]}
                {$region = $collapsedRegions[$region_name]}

                <tr>
                    <td colspan="2">
                        <details style="padding-bottom:16px;">
                            <summary class="subHeader" style="padding:8px;filter:brightness(80%);font-size:16px;margin-bottom:16px;">{$region_name}</summary>
                            <table width="100%" class="table">
            {/if}

            {foreach $data as $entry}
                {if (
                        $counter == 0 && 
                        ( 
                            (
                                !is_array($entry[1]) && 
                                !in_array($entry[1],$collapsedContent)
                            )
                            || 
                            (
                                is_array($entry[1]) && 
                                !in_array(ltrim($entry[1]['name'],'old'),$collapsedContent)
                            )
                        )
                    )
                    ||
                    (
                        $counter != 0 &&
                        (
                            (
                                !is_array($entry[1]) && 
                                in_array($entry[1], $region )
                            )
                            ||
                            (
                                is_array($entry[1]) && 
                                in_array(ltrim($entry[1]['name'],'old'), $region )
                            )
                        )
                    )
                }
                    <tr class="{cycle values="row1,row2"}" >
                        {if $entry[0] == "enum"}
                            <td width="30%" style="text-align:left; padding-left:15px;">{$entry[1]}</td>
                            <td width="70%" style="text-align:left; padding:5px;">                
                                <select class="listbox" name="{$entry[1]}">
                                    {foreach $entry[2] as $subEntry}
                                        <option {$subEntry[0]} value="{$subEntry[1]}">{$subEntry[2]}</option>
                                    {/foreach} 
                                </select>
                            </td>
                        {elseif $entry[0] == "text"}
                                <td colspan="2" style="padding-left:15px;" class="subHeader">{$entry[1]}</td>
                            </tr>
                            <tr>
                                <td colspan="2" >                                    
                                    <textarea class="textfield" cols="50" rows="5" name="{$entry[2]["name"]}" style="width:90%;">{$entry[2]["value"]}</textarea>
                                </td>     
                        {elseif $entry[1] == "description" || 
                                $entry[1] == "battle description" ||
                                $entry[1] == "tags" ||
                                $entry[1] == "when equipped tags" ||
                                $entry[1] == "on use tags" ||
                                $entry[1] == "on jutsu tags" ||
                                $entry[1] == "content" ||
                                $entry[1] == "tasks" ||
                                $entry[1] == "nindo" ||
                                $entry[1] == "simpleGuide" ||
                                $entry[1] == "requirements" ||
                                $entry[1] == "restrictions" ||
                                $entry[1] == "rewards" ||
                                $entry[1] == "data" ||
                                $entry[1] == "notes" ||
                                $entry[1] == "value" ||
                                $entry[1] == "action"}
                                <td colspan="2" style="padding-left:15px;" class="subHeader">{$entry[1]}</td>
                            </tr>
                            <tr>
                                <td colspan="2" >                                    
                                    <textarea class="textfield" cols="50" rows="5" name="{$entry[2][0]}" style="width:90%;">{$entry[2][1]}</textarea>
                                </td> 
                        {elseif $entry[1] == "start date" ||
                                $entry[1] == "end date"}  
                            <td width="30%" style="text-align:left; padding-left:15px;">{$entry[1]}</td>
                            <td width="70%" style="text-align:left; padding:5px;">      
                                {literal}
                                <script>
                                $(function() {
                                    $( "{/literal}#{$entry[2][0]}{literal}" ).datepicker();
                                });
                                </script>
                                {/literal}
                                <input type="text" name="{$entry[1]}" id="{$entry[2][0]}" value="{str_replace('"','&quot;',$entry[2][1])}" />
                            </td>
                        {elseif $entry[0] == "input"}                                                                                    
                            <td width="30%" style="text-align:left;  padding-left:15px;">{$entry[1]}</td>
                            <td width="70%" style="text-align:left; padding:5px;">                
                                <input type="text" size="40" name="{$entry[2][0]}" value="{str_replace('"','&quot;',$entry[2][1])}" class="textfield">
                            </td>
                        {/if}
                    </tr>
                {/if}
            {/foreach}

            {if $counter != 0}
                            </table>
                        </details>
                    </td>
                </tr>
            {/if}
        {/for}
    {else}
        <tr><td colspan="2">No users found</td></tr>
    {/if} 
  <tr> 
    <td colspan="2" style="padding:5px;" align="center">
        {foreach $data as $entry}    
            {if $entry[0] == "hidden" && 
                $entry[1]["name"] != "oldsimpleGuide"}                       
                <input type="hidden" name="{$entry[1]["name"]}" id="{$entry[1]["id"]}" value="{str_replace('"','&quot;',$entry[1]["value"])}">
            {/if}               
        {/foreach}
        <input name="Submit" type="submit" class="button" value="Submit" />
    </td>
  </tr>
</table>
</form>
{if isset($returnLink)}
    {if $returnLink === true}
        <a href="?id={$smarty.get.id}">Return</a>
    {else}
        <a href="{$returnLink}">Return</a>
    {/if}
{/if} 
</div>