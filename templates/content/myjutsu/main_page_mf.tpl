 <script type="text/javascript">
 $(document).ready(function() {
     $('.expandTaijutsu').click(function() {
        $("#taijutsu").slideToggle('slow');  
    });

     $('.expandNinjutsu').click(function() {
        $("#ninjutsu").slideToggle('slow');  
    });

     $('.expandGenjutsu').click(function() {
        $("#genjutsu").slideToggle('slow');  
    });

     $('.expandWeapon').click(function() {
        $("#weapon").slideToggle('slow');  
    });

     $('.expandHighest').click(function() {
        $("#highest").slideToggle('slow');  
    });
});
 </script>

<div class="page-box">
    <div class="page-title">
        My Jutsu <span class="toggle-button-info closed" data-target="#jutsu-info" />
    </div>

    <div class="page-content">
        <div class="toggle-target closed" id="jutsu-info">
            Any jutsu marked in red means you do not have the correct elemental affinity for it.
            <br/>
            <br/>
        </div>

        {$first = true}
        {foreach $displayArray as $type => $data}
            {if !empty($data)}
                <div class="page-sub-title{if $first}-top{/if} toggle-button-drop closed" data-target="#{$type}">{if $type == 'weapon'}Bukijutsu{else}{$type|capitalize}{/if}</div>
                {$first = false}

                <div class="table-grid table-column-6 toggle-target closed" id="{$type}">
                    <div class="lazy table-legend row-header column-1">
                        {if $type == 'weapon'}Bukijutsu{else}{$type|capitalize}{/if}
                    </div>

                    <div class="lazy table-legend row-header column-2">
                        Type
                    </div>
                    <div class="lazy table-legend row-header column-3">
                        Element
                    </div>
                    <div class="lazy table-legend row-header column-4">
                        Rank
                    </div>
                    <div class="lazy table-legend row-header column-5">
                        Level
                    </div>
                    <div class="lazy table-legend row-header column-6">
                        Action
                    </div>

                    {assign var=i value=0}
                    {foreach $data as $entry}

                        <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-1">
                            {if $type == 'weapon'}Bukijutsu{else}{$type|capitalize}{/if}
                        </div>

                        <div class="lazy table-cell table-alternate-{$i % 2 + 1} row-{$i} column-1">
                            <a href="?id=12&act=detail&jid={$entry[0]}" {if $entry[6] == "no"}style="color:red;"{/if}>{$entry[1]}</a>
                        </div>

                        <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-2">
                            Type
                        </div>

                        <div class="lazy table-cell table-alternate-{$i % 2 + 1} row-{$i} column-2">
                            {$entry[4]|capitalize}
                        </div>

                        <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-3">
                            Element
                        </div>

                        <div class="lazy table-cell table-alternate-{$i % 2 + 1} row-{$i} column-3">
                            {$entry[5]|capitalize}
                        </div>

                        <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-4">
                            Rank
                        </div>

                        <div class="lazy table-cell table-alternate-{$i % 2 + 1} row-{$i} column-4">
                            {$entry[2]}
                        </div>

                        <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-5">
                            Level
                        </div>

                        <div class="lazy table-cell table-alternate-{$i % 2 + 1} row-{$i} column-5">
                            {$entry[3]}
                        </div>

                        <div class="lazy table-legend-mobile table-alternate-{$i % 2 + 1} row-{$i} column-6">
                            Action
                        </div>

                        <div class="lazy table-cell table-alternate-{$i % 2 + 1} row-{$i} column-6">
                            <a href="?id=12&act=forget&jid={$entry[0]}" {if $entry[6] == "no"}style="color:red;"{/if}>Forget</a>
                            {if $user_rank_id == 1}
                                <a href="?jid={$entry[0]}&train=jutsu&id=18&backend=trainingBackend">Train</a>
                            {elseif $user_rank_id == 2}
                                <a href="?jid={$entry[0]}&train=jutsu&id=29&backend=trainingBackend">Train</a>
                            {else}
                                <a href="?jid={$entry[0]}&train=jutsu&id=39&backend=trainingBackend">Train</a>
                            {/if}
                        </div>

                        {$i = $i + 1}
                    {/foreach}            

                </div>
            {/if}
        {/foreach}

        {if $jutsuCount == 0 }
            <div>You don't have any jutsus yet</div>
        {/if}


        {if $show_loadouts}
            <form name="loadouts" method="post" action="" class="page-grid">

                <div class="page-sub-title">
                    Select Loadouts {$loadout_count}
                </div>

                <div class="page-grid grid-fill-columns">
                    <form name="selectLoadoutFormdefault" method="post" action="">
                        <input id="selectdefault" type="submit" name="selectLoadout" value="default" {if $current_loadout == 'default'}{assign var=found_loadout value='yes'}style="color:grey;"{/if} class="page-button-fill {if $current_loadout == 'default'}no-hover{/if}">
                    <form>
                    {foreach $taggedGroups as $loadout}
                        {if $loadout != 'default'}
                            <form name="selectLoadoutForm{$loadout}" method="post" action="">
                                <input id="select{$loadout}" type="submit" name="selectLoadout" value="{$loadout}" {if $current_loadout == $loadout}{assign var="found_loadout" value='yes'}style="color:grey;"{/if} class="page-button-fill {if $current_loadout == $loadout}no-hover{/if}">
                            </form>
                        {/if}
                    {/foreach}
                    {if $found_loadout != 'yes'}
                      <form name="selectLoadoutForm{$current_loadout}" method="post" action="">
                          <input id="select{$current_loadout}" type="submit" name="selectLoadout" value="{$current_loadout}" style="color:grey;" class="page-button-fill no-hover">
                      </form>
                    {/if}
                </div>

          
                {if $add_loadouts}
                    <div class="page-sub-title">
                        Add Loadout
                    </div>

                    <form name="addLoadoutForm" method="post" action="">
                        <div class="page-grid grid-fill-columns">
                            <div><input type="text" name="selectLoadout" class="page-text-input-fill" pattern="[a-zA-Z0-9 ]+"></div>
                            <div><input type="submit" name="Create New" value="Create New" class="page-button-fill"></div>
                        </div>
                    </form>
                {/if}
          
                {if $delete_loadouts}
                    <div class="page-sub-title">
                        Delete Loadout
                    </div>

                    <form name="deleteLoadoutForm" id="deleteLoadoutForm" method="post" action="">
                        <div class="page-grid grid-fill-columns">
                            <select name="deleteLoadout" form="deleteLoadoutForm" class="page-drop-down-fill">
                              {foreach $taggedGroups as $loadout}
                                {if $loadout != 'default'}
                                    <option value="{$loadout}">{$loadout}</option>
                                {/if}
                              {/foreach}
                            </select>
                            <input type="submit" name="Delete" value="Delete" class="page-button-fill">
                        </div>
                    </form>
                {/if}
          
            </form>
        {/if}
        
        {if $force_delete}
            <div class="page-sub-title">
              Delete Loadout (You have too many Loadouts {$loadout_count})
            </div>

            <form name="deleteLoadoutForm" id="deleteLoadoutForm" method="post" action="">
                <div class="page-grid grid-fill-columns">
                    <select name="deleteLoadout" form="deleteLoadoutForm" class="page-drop-down-fill">
                        {foreach $taggedGroups as $loadout}
                            {if $loadout != 'default'}
                                <option value="{$loadout}">{$loadout}</option>
                            {/if}
                        {/foreach}
                    </select>
                    <input type="submit" name="Delete" value="Delete" class="page-button-fill">
                </div>
            </form>
        {/if}

        {if !$force_delete}
            <div class="page-sub-title">
                Tagged jutsu
            </div>

            <form name="form1" method="post" action="">
                <div class="page-grid page-column-2">
                    {foreach $jutsuLists as $list_key => $list}
                        <div class="stiff-grid stiff-column-2">
                            <div>
                                Jutsu {$list_key+1}:
                            </div>
                            <select name="jutsu{$list_key+1}" class="page-drop-down-fill">
                                {foreach $list as $entry}
                                    {if $entry[2] == 1}
                                        <option selected value="{$entry[0]}">{$entry[1]}</option>
                                    {else}
                                        <option value="{$entry[0]}">{$entry[1]}</option>
                                    {/if}
                                {/foreach}
                            </select>
                        </div>
                    {/foreach}

                    <input type="submit" name="Update" value="Update" class="page-button-fill span-2"> 
                </div>
            </form>
        {/if}

    </div>
</div>


  
