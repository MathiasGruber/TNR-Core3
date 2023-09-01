<div align="center">
  <table width="95%" class="table" >
    <tr>
      <td style="border-top:none;" class="subHeader"> {$inputsubHeader} </td>
    </tr>
    <tr>
      <td>
        {if isset($inputMsg['message'])}
            {$inputMsg['message']}
        {else}
            {$inputMsg}
        {/if}
        <br>
      </td>
    </tr>
    <tr>
      <td>
          {if isset($formData)}
            <form id="{$formID}" class="autoInput" action="{$formData['href']}" method="{$formInputType}" enctype="multipart/form-data" >
            {foreach $inputFields as $entry}
                {if isset($entry['type'])}
                    {if isset($entry['nextLine']) && $entry['nextLine'] eq true}
                        <br>
                    {/if}
                    
                    {if $entry['type'] eq "textarea"}
                        <div width="100%" style="margin:10px;">
                            {if isset($entry['infoText'])}{$entry['infoText']}: &#160;&#160;&#160;{/if}
                            {if empty($entry['maxlength']) }
                                {$entry['maxlength'] = 500}
                            {/if}
                            <textarea class="markItUp js-text-limiter" name="{$entry['inputFieldName']}" maxlength="{$entry['maxlength']}" rows="5" cols="30">{$entry['inputFieldValue']}</textarea>
                            <div class="js-chars-counter" style="font-size:10px;padding:5px;float:right;">{$entry['maxlength']}</div><div style="font-size:10px;padding:5px;float:right;">Remaining Character Count:</div>
                            {*<textarea rows="4" cols="50" name="{$entry['inputFieldName']}" style="width:90%;height:200px;border:1px solid #000000;margin:3px;">{$entry['inputFieldValue']}</textarea>*}
                        </div>
                    {elseif $entry['type'] eq "input"}
                        <div width="45%" style="display:inline-block;">
                            {if isset($entry['infoText'])}{$entry['infoText']}: &#160;&#160;&#160;{/if}
                            <input name="{$entry['inputFieldName']}" type="text" size="15" value="{$entry['inputFieldValue']}">&#160;&#160;&#160;
                        </div>
                    {elseif $entry['type'] eq "file"}
                        <div width="45%" style="display:inline-block;">
                            {if isset($entry['infoText'])}{$entry['infoText']}: &#160;&#160;&#160;{/if}
                            <input type="file" name="{$entry['inputFieldName']}" size="15">&#160;&#160;&#160;
                        </div>
                    {elseif $entry['type'] eq "password"}
                        <div width="45%" style="display:inline-block;">
                            {if isset($entry['infoText'])}{$entry['infoText']}: &#160;&#160;&#160;{/if}
                            <input name="{$entry['inputFieldName']}" type="password" size="15" value="{$entry['inputFieldValue']}">&#160;&#160;&#160;
                        </div>
                    {elseif $entry['type'] eq "checkBox"}
                        <div width="100%" style="display:inline;">
                            <table class="sortable" width="100%">
                                <tr>
                                    <td colspan="3" class="subHeader">{$entry['infoText']}</td>
                                </tr>
                                    
                                {if !empty($entry['inputFieldValue'])}
                                    <tr>
                                         <td class="tableColumns">Name</td>
                                         <td class="tableColumns">Description</td>
                                         <td class="tableColumns sorttable_nosort">Select</td>
                                    </tr>
                                    {foreach $entry['inputFieldValue'] as $checkBox}
                                        <tr>
                                            <td>{$checkBox['name']}</td>
                                            <td>{$checkBox['description']}</td>
                                            <td><input type="checkbox" name="{$checkBox['id']}" id="checkbox"></td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr><td colspan="3">No items could be found</td></tr>
                                {/if}
                            </table>
                        </div>
                    {elseif $entry['type'] eq "select"}
                        {if isset($entry['infoText']) && !empty($entry['infoText'])}{$entry['infoText']}: &#160;&#160;&#160;{/if}
                        <select name="{$entry['inputFieldName']}">
                            {if !empty($entry['inputFieldValue'])}
                                {foreach $entry['inputFieldValue'] as $value => $name}
                                    <option {if isset($entry['selected']) && $entry['selected'] == $name }selected{/if} value="{if isset($entry['name_as_value']) && $entry['name_as_value'] == true}{$name}{else}{$value}{/if}">{$name}</option>
                                {/foreach}
                            {else}
                                <option value="">N/A</option>
                            {/if}
                        </select>
                    {elseif $entry['type'] eq "hidden"}
                        <input type="hidden" name="{$entry['inputFieldName']}" value="{$entry['inputFieldValue']}">
                    {elseif $entry['type'] eq "range"}
                        {if isset($entry['infoText'])}{$entry['infoText']}: &#160;&#160;&#160;{/if}
                        <div class="irs_wrapper">
                            
                            <noscript>{$entry['inputFieldMin']}</noscript>
                            <input
                                type="range"
                                id="{$entry['inputFieldName']}"
                                class="js-range-slider"
                                name="{$entry['inputFieldName']}"
                                value="{$entry['inputFieldValue']}"
                                data-min="{$entry['inputFieldMin']}"
                                min="{$entry['inputFieldMin']}"
                                data-postfix=" times"
                                {if $entry['inputFieldMax'] < 1000}
                                    data-step="1"
                                {elseif $entry['inputFieldMax'] < 10000}
                                    data-step="100"
                                {else}
                                    data-step="1000"
                                {/if}
                                data-max="{$entry['inputFieldMax']}"
                                max="{$entry['inputFieldMax']}"
                                data-disable="{$entry['inputFieldDisabled']}"
                                data-role="none"
                                keyboard="true"
                                keyboard_step="10"
                            >
                            <noscript>{$entry['inputFieldMax']}<br></noscript>
                            <!-- <a id="reduceButton">&#8612; Reduce</a> | <a id="addButton">Add &#8614;</a>-->
                            
                            <script language="JavaScript" type="text/javascript">
                                $(document).ready(function() { 

                                    // Get the slider
                                    var $range = $("#{$entry['inputFieldName']}");
                                    var slider = $range.data("ionRangeSlider");
                                    var curValue = {$entry['inputFieldValue']};

                                    // Log changes
                                    $range.on("change", function () {
                                        curValue = parseInt( $(this).prop("value") );
                                    });

                                    $("#reduceButton").click(function(event) {
                                        event.preventDefault();
                                        slider.update({
                                            from: curValue-1
                                        });
                                    });
                                    $("#addButton").click(function(event) {
                                        event.preventDefault();
                                        slider.update({
                                            from: curValue+1
                                        });
                                    });
                                });
                            </script>
                        </div>
                {/if}

                {/if}
            {/foreach}
          {/if}
          <div style="width:100%;">
              <input class="input_submit_btn" style="line-height:15px;margin:10px;" type="submit" name="{$formData['submitFieldName']}" value="{$formData['submitFieldText']}">
          </div>
          
          </form>
        </td>
    </tr>
  </table>
  {if isset($returnLink)}
        {if $returnLink === true}
            <a href="?id={$smarty.get.id}" class="returnLink">Return</a>
        {elseif $returnLink !== false}
            <a href="{$returnLink}" class="returnLink">Return</a>
        {/if}
    {/if} 
</div>
        
        