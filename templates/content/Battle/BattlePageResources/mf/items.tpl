{function canUseitem}
	{if $item['stack'] <= 0}
		{assign var="usable" value="Expended" scope=parent}
	{else}
		{assign var="usable" value=true scope=parent}
	{/if}
{/function}

{function printitemRecord}
	<div id="item-{$item['iid']}-record" class="option-record item-record r0{if $usable !== true} disabled{/if}" data-invid="{$invid}">

		<div class="color-layer None {$item['targeting_type']}">

			<div class="item-record-title">
				<div class="item-title option-title text-large">
					{$item['name']}
				</div>

				<div class="cancel-button" onclick="optionCancel('item')">
                    X
                </div>
			</div>

			<div class="option-description item-description">
                {$item['description']}
            </div>

			<div class="item-record-body font-small text-left">
				{if $usable !== true}
					{$usable}
				{/if}
			</div>

			<div class="option-effects item-effects">
                <details>
                    <summary>
                        Effects
                    </summary>
                    <div class="text-left font-small item-effects option-effects">

                        {if strlen($item['effects']) > 15}
                            <div>
                            {implode('</div><div>',
                                explode('-new-line-',
                                    trim(
                                        trim(
                                            trim(
                                                str_replace(
                                                    '-new-line-    ',
                                                    ", ",
                                                    $item['effects']
                                                ),
                                                "</pre>"
                                            ),
                                            "<pre>"
                                        ),
                                        '-new-line-'
                                    )
                                )
                            )}
                            </div>
                        {else}
                            N/A
                        {/if}
                    </div>
                </details>
            </div>


			<div class="bottom-bar" style="width: calc({($item['stack']/$item['stack_size'])*100}% + {16 * ($item['stack']/$item['stack_size'])}px)"></div>
			<div class="bottom-bubble font-tiny">{$item['stack']}/{$item['stack_size']}</div>
		</div>
	</div>
{/function}

{function printitemButton}
	<div id="item-{$item['iid']}-button" class="option-button item-button r0{if $usable !== true} disabled{/if}"
			data-targeting_type="{$item['targeting_type']}" onclick="optionclick('item','{$item['iid']}')">

		<div class="color-layer None {$item['targeting_type']}">

			<div class="item-button-title">
				<div class="item-title option-title text-large">
					{$item['name']}
				</div>
			</div>

			<div class="item-button-body font-small text-left">
				{if $usable !== true}
					{$usable}
				{/if}
			</div>

			
			<div class="bottom-bar" style="width: calc({($item['stack']/$item['stack_size'])*100}% + {16 * ($item['stack']/$item['stack_size'])}px)"></div>
			<div class="bottom-bubble font-tiny">{$item['stack']}/{$item['stack_size']}</div>
		</div>
	</div>
{/function}

{if (is_numeric($stunned) && $stunned > 0) || $stunned === true}
    {stunned}

{elseif $owner['waiting_for_next_turn'] === true}
    {waiting}

{else}
	{foreach $owner['items'] as $invid => $item}
		{canUseitem item=$item}
		{printitemRecord invid=$invid item=$item useable=$usable}
		{printitemButton item=$item useable=$usable}
	{/foreach}
{/if}