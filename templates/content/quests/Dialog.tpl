<link href="https://fonts.googleapis.com/css?family=Asul" rel="stylesheet">

<div width="95%" style="border: 1px solid black;position:relative;top:-10px;" id="combat_page">
    <form action="" id="battle_form" method="post">
        <div id="summary" summary="no"></div>
        <table class="table" style="border:none;margin:0px;" width="100%">
            <tr>
                <td class="subHeader" align="center">
                    {$quest->name}
                </td>
            </tr>
            <tr>
                <td style="font-family: 'Asul', sans-serif;font-size:16px; -webkit-font-smoothing: subpixel-antialiased;text-align:left;">
                    <hr/>
                    {if $current_message['title'] != ''}
                        {$current_message['title']}
                    {else}
                        Dialog
                    {/if}
                    <hr/>
                </td>
            </tr>

            {if !is_array($current_message['message'])}
                <tr>
                    <td style="font-family: 'Asul', sans-serif;font-size:14px; -webkit-font-smoothing: subpixel-antialiased;text-align:center;">
                        {$current_message['message']}
                    </td>
                </tr>
            {else}
                {foreach $current_message['message'] as $key => $value}
                    {if !is_array($value)}
                        <tr>
                            <td style="font-family: 'Asul', sans-serif;font-size:14px; -webkit-font-smoothing: subpixel-antialiased; text-align:{if $key % 2 == 0}left{else}right{/if}; padding-{if $key % 2 == 1}left{else}right{/if}: 25%" >
                                {$value}
                            </td>
                        </tr>
                    {else}
                        {if $value['side'] != 'left' && $value['side'] != 'right'}<hr/>{/if}
                        <tr>
                            <td style="font-family: 'Asul', sans-serif;font-size:14px; -webkit-font-smoothing: subpixel-antialiased;
                                {if $value['side'] == 'left'}
                                    text-align:left; padding-right:25%"
                                {else if $value['side'] == 'right'}
                                    text-align:right; padding-left:25%"
                                {else}
                                    text-align:center; padding-left:12.5%;padding-right:12.5%"
                                {/if}
                            >
                                {if $value['bold'] == true}<b>{/if}
                                {if $value['italic'] == true}<i>{/if}

                                {$value['text']}

                                {if $value['bold'] == true}</b>{/if}
                                {if $value['italic'] == true}</i>{/if}
                            </td>
                        </tr>
                        {if $value['side'] != 'left' && $value['side'] != 'right'}<hr/>{/if}
                    {/if}
                {/foreach}
            {/if}

        </table>
        <table width="100%">
            {foreach $current_message['options'] as $option_key => $option}
                <tr>
                    <td>
                        <table width="100%">
                            <tr>
                                <td style="font-family: 'Asul', sans-serif;font-size:14px; -webkit-font-smoothing: subpixel-antialiased;" width="5%">
                                    ->
                                </td>
                                <td style="font-family: 'Asul', sans-serif;font-size:14px; -webkit-font-smoothing: subpixel-antialiased;text-align: left;" width="95%">
                                    <a href="?id=120&dialog_option={$option_key}" style="font-weight:600;">{$option['text']}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            {/foreach}
        </table>
    </form>
</div>