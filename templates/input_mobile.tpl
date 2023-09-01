<tr>
    <td>
        <headerText>{$inputsubHeader}</headerText>
    </td>
</tr>
{if !empty($inputMsg)}
<tr>
    <td>
        <text>{$inputMsg}</text>
    </td>
</tr>
{/if}

{if isset($formData)}  
  {foreach $inputFields as $entry}
      {if isset($entry['type'])}
        <tr><td>
            {if isset($entry['infoText']) && !empty($entry['infoText'])}<text>{$entry['infoText']}</text>{/if}
            {if $entry['type'] eq "textarea"}
                  {if empty($entry['maxlength']) }
                      {$entry['maxlength'] = 500}
                  {/if}
                  <tr color="dim">
                    <td>
                      <input name="{$entry['inputFieldName']}" type="textarea" value="{$entry['inputFieldValue']}">{$entry['inputFieldValue']}</input>
                    </td>
                  </tr>
            {elseif $entry['type'] eq "input"}
                  <tr color="dim">
                    <td>
                      <input name="{$entry['inputFieldName']}" type="text" value="{$entry['inputFieldValue']}">{$entry['inputFieldValue']}</input>
                    </td>
                  </tr>
            {elseif $entry['type'] eq "file"}
                  <fileUpload name="{$entry['inputFieldName']}" type="file"></fileUpload>
            {elseif $entry['type'] eq "password"}
                  <tr color="dim">
                    <td>
                      <input name="{$entry['inputFieldName']}" type="password" value="{$entry['inputFieldValue']}"></input>
                    </td>
                  </tr>
            {elseif $entry['type'] eq "checkBox"}                
                {if !empty($entry['inputFieldValue'])}

                    <tr color="darkgrey">
						<td><text color="white">Name</text></td>
                        <td><text color="white">Description</text></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
                        <td><text color="white">Select</text></td>
                    </tr>
                    {foreach $entry['inputFieldValue'] as $checkBox}
                    <tr>
						<td><text>{$checkBox['name']}</text></td>
						<td><text>{$checkBox['description']}</text></td>
						<tr>
							<td><text><input type="checkbox" name="{$checkBox['id']}" value="on"></input></text></td>
							<td></td>
							<td></td>
						</tr>
                    </tr>
                    {/foreach}
                {else}
                    <text>No items could be found</text>
                {/if}                
            {elseif $entry['type'] eq "select"}
                <select name="{$entry['inputFieldName']}">
                    {if !empty($entry['inputFieldValue'])}
                        {foreach $entry['inputFieldValue'] as $value => $name}
                            <option value="{$value}">{$name}</option>
                        {/foreach}
                    {else}
                        <option value="">N/A</option>
                    {/if}
                </select>
            {elseif $entry['type'] eq "hidden"}
                <hidden type="hidden" name="{$entry['inputFieldName']}" value="{$entry['inputFieldValue']}"></hidden>
            {elseif $entry['type'] eq "range"}
                <slider
                  id="{$entry['inputFieldName']}"
                  name="{$entry['inputFieldName']}"
                  value="{$entry['inputFieldValue']}"
                  min="{$entry['inputFieldMin']}"
                  data-postfix=" times"
                  {if $entry['inputFieldMax'] < 1000}
                      data-step="1"
                  {elseif $entry['inputFieldMax'] < 10000}
                      data-step="100"
                  {else}
                      data-step="1000"
                  {/if}
                  max="{$entry['inputFieldMax']}"
                  data-disable="{$entry['inputFieldDisabled']}"></slider>
            {/if}
            </td></tr>  
        {/if}
  {/foreach}
{/if}
<tr>
    <td>
        <submit type="submit" name="{$formData['submitFieldName']}" value="{$formData['submitFieldText']}" href="{$formData['href']}" method="{$formInputType}" id="{$formID}"></submit>
    </td>
</tr>

{if isset($returnLink)}    
    {if $returnLink === true}
        <tr><td><button href="?id={$smarty.get.id}">Return</button></td></tr>
    {elseif $returnLink !== false}
        <tr><td><button href="{$returnLink}">Return</button></td></tr>
    {/if}    
{/if} 
        
        