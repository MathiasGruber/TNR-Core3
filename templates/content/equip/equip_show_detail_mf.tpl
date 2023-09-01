{assign var="itemfile" value="{$s3}/items/{$item_data[0]['id']}.png"}

<div class="page-box">
    <div class="page-title">
        Item Details
    </div>
    <div class="page-content page-column-{if file_exists($itemfile)}3{else}fr-2{/if}">

        <div class="text-left font-large anti-shrink">
            <b>Name:</b> {$item_data[0]['name']}<br><br>
            <b>Stackable:</b> {if $item_data[0]['stack_size'] == 1}No{else}Yes ({$item_data[0]['stack_size']}){/if}<br><br>
            <b>Type:</b>
            {if $item_data[0]['type'] == 'armor'}
                {$item_data[0]['armor_types']}

            {elseif $item_data[0]['type'] == 'weapon'}
                Weapon: {$item_data[0]['weapon_classifications']}
                
            {else}
                {$item_data[0]['type']}
            {/if}

            {if $item_data[0]['type'] == 'weapon' || $item_data[0]['type'] == 'armor'}
                <br><br>
                <b>Armor: </b>{$item_data[0]['armor']}<br><br>
                <b>Accuracy: </b>{$item_data[0]['accuracy']}<br><br>
                <b>Chakra Power: </b>{$item_data[0]['chakra_power']}<br><br>
                <b>Mastery: </b>{$item_data[0]['mastery']}
            {/if}
        </div>

        <div class="text-left font-large anti-shrink">
            <b>Required Rank:</b>   
            {if $item_data[0]['required_rank'] == '1'}
                Academy Student

            {elseif $item_data[0]['required_rank'] == '2'}
                Genin

            {elseif $item_data[0]['required_rank'] == '3'}
                Chuunin

            {elseif $item_data[0]['required_rank'] == '4'}
                Jounin

            {elseif $item_data[0]['required_rank'] == '5'}
                Elite Jounin
            {/if}<br><br>

            <b>Price: </b> {$item_data[0]['price']} Ryo<br><br>
            <b>Durability:</b> {$item_data[0]['durabilityPoints']}
            {if $item_data[0]['type'] == 'weapon' || $item_data[0]['type'] == 'armor'}
                <br><br>
                <b>Stability: </b>{$item_data[0]['stability']}<br><br>
                <b>Expertise: </b>{$item_data[0]['expertise']}<br><br>
                <b>Critical Strike: </b>{$item_data[0]['critical_strike']}<br><br>

                {if strpos($item_data[0]['use'], 'REPEL') !== false }
                    {$explode = explode("REPEL:",$item_data[0]['use'])}
                    {$repel = $explode[1]}
                    {$explode = explode(";",$repel)}
                    {$repel = $explode[0]}
                {elseif strpos($item_data[0]['use2'], 'REPEL') !== false}
                    {$explode = explode("REPEL:",$item_data[0]['use2'])}
                    {$repel = $explode[1]}
                    {$explode = explode(";",$repel)}
                    {$repel = $explode[0]}
                {else}
                    {$repel = '0'}
                {/if}
                
                {if $repel > 0}
                    <b>Repel: </b> {$repel}%
                {else}
                    <b>Attract: </b> {ABS($repel)}%
                {/if}
            {/if}
        </div>



        {if file_exists($itemfile)}
            <div>
                <img style="border:0px solid #000000;max-height:100px;max-width:100px;height:auto;width:auto;" src="{$s3}/items/{$item_data[0]['id']}.png"><br>
            </div>
            {$span_count = 3}
        {else}
            {$span_count = 2}
        {/if}



        {assign var="itemfile" value="{$s3}/items/large{$item_data[0]['id']}.png"}
        {if file_exists($itemfile)}
            <div class="page-sub-title span-{$span_count}">
                {$item_data[0]['name']}
            </div>
            <div class="span-{$span_count}">
                <img style="border:0px solid #000000;max-width:500px;height:auto;width:auto;" src="{$s3}/items/large{$item_data[0]['id']}.png">
            </div>
        {/if}



        <div class="page-sub-title span-{$span_count}">
            Item Description
        </div>

        <div class="span-{$span_count}">
            {$Details}
        </div>


        {if $item_data[0]['type'] == 'weapon' || $item_data[0]['type'] == 'armor'}
            {if strlen($effectsOnEquip) > 20}
                <div class="page-sub-title span-{$span_count}">
                    Item Effects When Equipped
                </div>

                <div class="span-{$span_count} text-left">
                    <ul>
                        {str_replace('-new-line-', '\r\n', $effectsOnEquip)}
                    </ul>
                </div>
            {/if}


            {if strlen($effectsOnUse) > 20}
                <div class="page-sub-title span-{$span_count}">
                    Item Effects When
                </div>

                <div class="span-{$span_count} text-left">
                    <ul>
                        {str_replace('-new-line-', "\r\n", $effectsOnUse)}
                    </ul>
                </div>
            {/if}


            {if strlen($effectsOnJutsu) > 20}
                <div class="page-sub-title span-{$span_count}">
                    Item Effects When
                </div>

                <div class="span-{$span_count} text-left">
                    <ul>
                        {str_replace('-new-line-', "\r\n", $effectsOnJutsu)}
                    </ul>
                </div>
            {/if}
        {/if}
    </div>
</div>