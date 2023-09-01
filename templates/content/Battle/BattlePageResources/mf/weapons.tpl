{function canUseWeapon}
	{if $weapon['durability'] <= 0}
		{assign var="usable" value="Broken" scope=parent}
	{else if $owner['equipment_used'][$weapon['iid']]['max_uses'] != -1 && $owner['equipment_used'][$weapon['iid']]['max_uses'] <= $owner['equipment_used'][$weapon['iid']]['uses']}
		{assign var="usable" value="Out of Uses" scope=parent}
	{else}
		{assign var="usable" value=true scope=parent}
	{/if}
{/function}

{function printWeaponRecord}
	<div id="weapon-{$weapon['iid']}-record" class="option-record weapon-record r0{if $usable !== true} disabled{/if}" data-invid="{$invid}">

		<div class="color-layer {if $weapon['element'] != ''}{$weapon['element']}{else}None{/if} {$weapon['targeting_type']}">

			{if $weapon['infinity_durability'] != 0}
				<div class="top-bar durability" style="width: calc(100% + 16px)"></div>
				<div class="top-bubble font-tiny">
					∞
				</div>
			{else}
				<div class="top-bar durability" style="width: calc({($weapon['durability']/$weapon['max_durability'])*100}% + {16 * ($weapon['durability']/$weapon['max_durability'])}px)"></div>
				<div class="top-bubble font-tiny" title="{$weapon['durability']}/{$weapon['max_durability']}">
					Dura: {floor(($weapon['durability']/$weapon['max_durability']) * 100)}%
				</div>
			{/if}

			

			<div class="weapon-record-title">
				<div class="weapon-title option-title text-large">
					{$weapon['name']}
				</div>

				<div class="cancel-button" onclick="optionCancel('weapon')">
                    X
                </div>
			</div>

			<div class="option-description weapon-description">
                {$weapon['description']}
            </div>

			<div class="weapon-record-body font-small text-left">
				{if $usable !== true}
					{$usable}
				{/if}
			</div>

			<div class="option-effects weapon-effects">
                <details>
                    <summary>
                        Effects
                    </summary>
                    <div class="text-left font-small weapon-effects option-effects">

                        {if strlen($weapon['on_use_effects']) > 15}
                            <div>
                            {implode('</div><div>',
                                explode('-new-line-',
                                    trim(
                                        trim(
                                            trim(
                                                str_replace(
                                                    '-new-line-    ',
                                                    ", ",
                                                    $weapon['on_use_effects']
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

			{if $owner['equipment_used'][$weapon['iid']]['max_uses'] == -1}
				<div class="bottom-bar" style="width:0px;"></div>
				<div class="bottom-bubble font-tiny" style="margin-top:-8px;">∞</div>
			{else}
				<div class="bottom-bar" style="width: calc({(($owner['equipment_used'][$weapon['iid']]['max_uses']-$owner['equipment_used'][$weapon['iid']]['uses'])/$owner['equipment_used'][$weapon['iid']]['max_uses'])*100}% + {16 * (($owner['equipment_used'][$weapon['iid']]['max_uses']-$owner['equipment_used'][$weapon['iid']]['uses'])/$owner['equipment_used'][$weapon['iid']]['max_uses'])}px)"></div>
				<div class="bottom-bubble font-tiny">{$owner['equipment_used'][$weapon['iid']]['max_uses']-$owner['equipment_used'][$weapon['iid']]['uses']} / {$owner['equipment_used'][$weapon['iid']]['max_uses']}</div>
			{/if}
		</div>
	</div>
{/function}

{function printWeaponButton}
	<div id="weapon-{$weapon['iid']}-button" class="option-button weapon-button r0{if $usable !== true} disabled{/if}"
			data-targeting_type="{$weapon['targeting_type']}" onclick="optionclick('weapon','{$weapon['iid']}')">

		<div class="color-layer {if $weapon['element'] != ''}{$weapon['element']}{else}None{/if} {$weapon['targeting_type']}">

			{if $weapon['infinity_durability'] != 0}
				<div class="top-bar durability" style="width: calc(100% + 16px)"></div>
				<div class="top-bubble font-tiny">
					∞
				</div>
			{else}
				<div class="top-bar durability" style="width: calc({($weapon['durability']/$weapon['max_durability'])*100}% + {16 * ($weapon['durability']/$weapon['max_durability'])}px)"></div>
				<div class="top-bubble font-tiny" title="{$weapon['durability']}/{$weapon['max_durability']}">
					Dura: {floor(($weapon['durability']/$weapon['max_durability']) * 100)}%
				</div>
			{/if}

			

			<div class="weapon-button-title">
				<div class="weapon-title option-title text-large">
					{$weapon['name']}
				</div>
			</div>

			<div class="weapon-button-body font-small text-left">
				{if $usable !== true}
					{$usable}
				{/if}
			</div>

			{if $owner['equipment_used'][$weapon['iid']]['max_uses'] == -1}
				<div class="bottom-bar" style="width:0px;"></div>
				<div class="bottom-bubble font-tiny" style="margin-top:-8px;">∞</div>
			{else}
				<div class="bottom-bar" style="width: calc({(($owner['equipment_used'][$weapon['iid']]['max_uses']-$owner['equipment_used'][$weapon['iid']]['uses'])/$owner['equipment_used'][$weapon['iid']]['max_uses'])*100}% + {16 * (($owner['equipment_used'][$weapon['iid']]['max_uses']-$owner['equipment_used'][$weapon['iid']]['uses'])/$owner['equipment_used'][$weapon['iid']]['max_uses'])}px)"></div>
				<div class="bottom-bubble font-tiny">{$owner['equipment_used'][$weapon['iid']]['max_uses']-$owner['equipment_used'][$weapon['iid']]['uses']} / {$owner['equipment_used'][$weapon['iid']]['max_uses']}</div>
			{/if}
		</div>
	</div>
{/function}

{if (is_numeric($stunned) && $stunned > 0) || $stunned === true}
    {stunned}

{elseif $owner['waiting_for_next_turn'] === true}
    {waiting}

{else}
	{foreach $owner['equipment'] as $invid => $item}
		{if $item['type'] == 'weapon'}
	    	{canUseWeapon weapon=$item owner=$owner}
	    	{printWeaponRecord invid=$invid weapon=$item useable=$usable owner=$owner}
	    	{printWeaponButton weapon=$item useable=$usable owner=$owner}
		{/if}
	{/foreach}
{/if}