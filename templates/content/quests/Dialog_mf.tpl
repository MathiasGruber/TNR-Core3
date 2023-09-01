<link href="https://fonts.googleapis.com/css?family=Asul" rel="stylesheet">

<div class="page-box" id="combat_page">
    <div class="page-title">
        {$quest->name}
    </div>
    <form action="" id="battle_form" method="post">
        <div id="summary" summary="no"></div>
        <div class="page-content">
            <div class="page-sub-title-top">
                <hr/>
                {if $current_message['title'] != ''}
                    {$current_message['title']}
                {else}
                    Dialog
                {/if}
                <hr/>
            </div>

            {if !is_array($current_message['message'])}
                <div class="table-cell-padded table-alternate-2">
                    {$current_message['message']}
                </div>
            {else}
                {foreach $current_message['message'] as $key => $value}
                    {if !is_array($value)}
                        <div class="table-cell-padded fancy-box table-alternate-{(($key+1) % 2) + 1} text-{if $key % 2 == 0}left{else}right{/if} " style="margin-{if $key % 2 == 1}left{else}right{/if}: 25%" >
                            {$value}
                        </div>
                    {else}
                        {if $value['side'] != 'left' && $value['side'] != 'right'}<hr/>{/if}
                        <div class=" 
                            {if $value['side'] == 'left'}
                                table-cell-padded fancy-box table-alternate-1 text-left" style="margin-right:25%"
                            {else if $value['side'] == 'right'}
                                table-cell-padded fancy-box table-alternate-2 text-right" style="margin-left:25%"
                            {else}
                                text-center" style="margin-left:12.5%;margin-right:12.5%"
                            {/if}
                        >
                            {if $value['bold'] == true}<b>{/if}
                            {if $value['italic'] == true}<i>{/if}

                            {$value['text']}

                            {if $value['bold'] == true}</b>{/if}
                            {if $value['italic'] == true}</i>{/if}
                        </div>
                        {if $value['side'] != 'left' && $value['side'] != 'right'}<hr/>{/if}
                    {/if}
                {/foreach}
            {/if}

            <hr/>

            {foreach $current_message['options'] as $option_key => $option}

                <div class="page-grid page-grid-justify-stretch">
                    <a href="?id=120&dialog_option={$option_key}" class="text-left page-button-fill">
                        {$option['text']}
                    </a>
                </div>

            {/foreach}

        </div>
    </form>
</div>