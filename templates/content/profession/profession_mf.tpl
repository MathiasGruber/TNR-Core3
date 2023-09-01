<div class="page-box">
    <div class="page-title">
        Profession System
    </div>
    <div class="page-content">
        <div class="page-grid page-column-2">
            <div>
                Profession: {$data['name']}<br/>
                Level: {$data['profession_exp']}<br/>
                <b><a href="?id={$smarty.get.id}&act=quit">Quit job</a></b>
            </div>

            <div>
                {foreach $gains as $gain}
                    {if $gain['type'] == "item"}
                        {$gain['discount']}% off on {if isset($gain['subIdentifier'])}{$gain['subIdentifier']}{/if} {$gain['identifier']}s<br>
                    {elseif $gain['type'] == "info"}
                        {$gain['text']}<br>
                    {elseif $gain['type'] == "ramen"}
                        {$gain['discount']}% off on ramen<br>
                    {elseif $gain['type'] == "hospital"}
                        {$gain['discount']}% off hospital costs<br>
                    {/if}
                {/foreach}
            </div>
        </div>

        {if isset($controlPanel)}
            {$subSelect="controlPanel"}
            {include file="file:{$absPath}/{$controlPanel}" title="Control Panel"}
        {/if}

    </div>
</div>