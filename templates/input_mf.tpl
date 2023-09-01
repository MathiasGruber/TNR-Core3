{if isset($inputMsg['hidden']) && $inputMsg['hidden'] == 'yes'}
    {$hidden = true}
{else}
    {$hidden = false}
{/if}

{if $input_full_page !== false}<div class="lazy page-box">{/if}
    <div class="lazy {if $input_full_page !== false}page-title{else}page-sub-title{/if}">
        {$inputsubHeader} {if $hidden}<span class="toggle-button-info closed" data-target="#input-message"/>{/if}
    </div>


    {if isset($formData)}
        <form id="{$formID}" class="{if $input_full_page !== false}page-content{else}page-grid{/if}" action="{$formData['href']}" method="{$formInputType}" enctype="multipart/form-data" >

            <div class="{if $input_full_page !== false && !$hidden}page-sub-title-top{/if} {if $hidden}toggle-target closed{/if}" id="input-message">
                {if isset($inputMsg['message'])}
                    {$inputMsg['message']}
                {else}
                    {$inputMsg}
                {/if}

                {if $hidden}
                    <br/><br/>
                {/if}
            </div>

            {foreach $inputFields as $key => $entry}
                {if isset($entry['type'])}
                    {if isset($entry['nextLine']) && $entry['nextLine'] eq true}
                        <br>
                    {/if}
                  
                    {if $entry['type'] eq "textarea"}

                        {if isset($entry['infoText'])}
                            <div class="page-sub-title{if $key == 0 && ($hidden || $inputMsg == '')}-top{/if}">
                                {$entry['infoText']}:
                            </div>
                        {/if}

                        <div class="textAreaWrapper">
                            <div class='textAreaCounter'></div>
                            <textarea class="markItUp page-text-area-fill" name="{$entry['inputFieldName']}" maxlength="{$entry['maxlength']}" rows="{ceil($entry['maxlength']/100)}">{$entry['inputFieldValue']}</textarea>
                        </div>
                    {elseif $entry['type'] eq "input"}
                        <div class="page-grid {if isset($entry['infoText'])}page-column-fr-2{else}page-column-fr-1{/if}">

                            {if isset($entry['infoText'])}
                                <div class="light-solid-box table-cell bold <!--page-sub-title{if $key == 0 && ($hidden || $inputMsg == '')}-top{/if}-->">
                                    {$entry['infoText']}: &#160;&#160;&#160;
                                </div>
                            {/if}

                            <input class="lazy page-text-input" name="{$entry['inputFieldName']}" type="text" size="30" value="{$entry['inputFieldValue']}">

                        </div>
                    {elseif $entry['type'] eq "file"}
                        <div class="page-grid {if isset($entry['infoText'])}page-column-fr-2{else}page-column-fr-1{/if}">
                            {if isset($entry['infoText'])}
                                <div class="light-solid-box table-cell bold <!--page-sub-title{if $key == 0 && ($hidden || $inputMsg == '')}-top{/if}-->">
                                    {$entry['infoText']}: &#160;&#160;&#160;
                                </div>
                            {/if}

                            <input class="lazy page-text-input" type="file" name="{$entry['inputFieldName']}" size="30">
                        </div>
                    {elseif $entry['type'] eq "password"}
                        <div class="page-grid {if isset($entry['infoText'])}page-column-fr-2{else}page-column-fr-1{/if}">
                            {if isset($entry['infoText'])}
                                <div class="light-solid-box table-cell bold <!--page-sub-title{if $key == 0 && ($hidden || $inputMsg == '')}-top{/if}-->">
                                    {$entry['infoText']}: &#160;&#160;&#160;
                                </div>
                            {/if}

                            <input class="lazy page-text-input" name="{$entry['inputFieldName']}" type="password" size="30" value="{$entry['inputFieldValue']}">
                        </div>
                    {elseif $entry['type'] eq "checkBox"}
                        <div class="page-sub-title{if $key == 0 && ($hidden || $inputMsg == '')}-top{/if}">
                            {$entry['infoText']}
                        </div>
                        <div class="table-grid table-column-3">
                            {if !empty($entry['inputFieldValue'])}
                                <div class="lazy table-legend row-header column-1">Name</div>
                                <div class="lazy table-legend row-header column-2">Description</div>
                                <div class="lazy table-legend row-header column-3">Select</div>

                                {foreach $entry['inputFieldValue'] as $key => $checkBox}
                                    <div class="lazy table-legend-mobile table-alternate-{$key % 2 + 1} row-{$key} column-1">Name</div>
                                    <div class="lazy table-cell table-alternate-{$key % 2 + 1} column-1 row-{$key}">{$checkBox['name']}</div>
                                    <div class="lazy table-legend-mobile table-alternate-{$key % 2 + 1} row-{$key} column-2">Description</div>
                                    <div class="lazy table-cell table-alternate-{$key % 2 + 1} column-2 row-{$key}">{$checkBox['description']}</div>
                                    <div class="lazy table-legend-mobile table-alternate-{$key % 2 + 1} row-{$key} column-3">Select</div>
                                    <div class="lazy table-cell table-alternate-{$key % 2 + 1} column-3 row-{$key}"><input type="checkbox" name="{$checkBox['id']}" id="checkbox"></div>
                                {/foreach}
                            {else}
                                <div class="span-3">No items could be found</div>
                            {/if}
                        </div>
                    {elseif $entry['type'] eq "select"}
                        <div class="page-grid {if isset($entry['infoText']) && !empty($entry['infoText'])}page-column-fr-2{else}page-column-fr-1{/if}">
                            {if isset($entry['infoText']) && !empty($entry['infoText'])}
                                <div class="light-solid-box table-cell bold <!--page-sub-title{if $key == 0 && ($hidden || $inputMsg == '')}-top{/if}-->">
                                    {$entry['infoText']}
                                </div>
                            {/if}
                            <select class="lazy page-drop-down-fill" name="{$entry['inputFieldName']}">
                                {if !empty($entry['inputFieldValue'])}
                                    {foreach $entry['inputFieldValue'] as $value => $name}
                                        <option {if isset($entry['selected']) && $entry['selected'] == $name }selected{/if} value="{if isset($entry['name_as_value']) && $entry['name_as_value'] == true}{$name}{else}{$value}{/if}">{$name}</option>
                                    {/foreach}
                                {else}
                                    <option value="">N/A</option>
                                {/if}
                            </select>
                        </div>
                    {elseif $entry['type'] eq "hidden"}

                        <input type="hidden" name="{$entry['inputFieldName']}" value="{$entry['inputFieldValue']}">

                    {elseif $entry['type'] eq "range"}
                        {if isset($entry['infoText'])}
                            <div class="page-sub-title{if $key == 0 && ($hidden || $inputMsg == '')}-top{/if}">
                                {$entry['infoText']}:
                            </div>
                        {/if}

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
                    {else if $entry['type'] eq "fill-text"}
                        {if isset($entry['infoText'])}
                            <div class="page-sub-title{if $key == 0 && ($hidden || $inputMsg == '')}-top{/if}">
                                {$entry['infoText']}
                            </div>
                        {/if}
                    {/if}
                {/if}
            {/foreach}

            <input class="lazy page-button-fill" type="submit" name="{$formData['submitFieldName']}" value="{$formData['submitFieldText']}">

        </form>
    {/if}
{if $input_full_page !== false}</div>{/if}
        
        