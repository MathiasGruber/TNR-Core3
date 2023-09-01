 <script type="text/javascript">
 $(document).ready(function() {
     $('.switchSpecial').click( function() {
        $(".drawers").not('#switchSpecial').fadeOut(200);
        $("#switchSpecial").delay(200).fadeToggle();
    });
    $('.professionMaterial').click(function() {
        $(".drawers").not('#professionMaterial').fadeOut(200);
        $("#professionMaterial").delay(200).fadeToggle();  
    });
    $('.ExtraRegen').click(function() {
        $(".drawers").not('#ExtraRegen').fadeOut(200);
        $("#ExtraRegen").delay(200).fadeToggle();  
    });
    $('.CharacterChanges').click(function() {
        $(".drawers").not('#CharacterChanges').fadeOut(200);
        $("#CharacterChanges").delay(200).fadeToggle();  
    });
    $('.specialsurprise').click(function() {
        $(".drawers").not('#specialsurprise').fadeOut(200);
        $("#specialsurprise").delay(200).fadeToggle();  
    });
    $('.bloodlineItem').click(function() {
        $(".drawers").not('#bloodlineItem').fadeOut(200);
        $("#bloodlineItem").delay(200).fadeToggle();  
    });
});
 </script>

<div class="page-box">
    <div class="page-title">
        Black Market Services <span class="toggle-button-info closed" data-target="#black-market-info"/>
    </div>
    <div class="page-content">
        <div class="page-sub-title-top toggle-target closed" id="black-market-info">
            You can purchase a series of special services using either reputation points or popularity points. 
            Click the icons below to get more information.
        </div>
        <div class="page-grid page-column-3">
            
            <div class="bloodlineItem">
                <img style="width:75px;margin:5px;" alt="New Bloodline" src="images/icons/chemistry.png"/><br>
                <b>New Bloodline</b>
            </div>

            <div class="switchSpecial">
                <img style="width:75px;margin:5px;" alt="Change Specialization" src="images/icons/settings.png"/><br>
                <b>Change Specialization</b>
            </div>

            <div class="professionMaterial">
                <img style="width:75px;margin:5px;" alt="Profession Materials" src="images/icons/package.png"/><br>
                <b>Profession Materials</b>
            </div>

            <div class="ExtraRegen">
                <img style="width:75px;margin:5px;" alt="Extra Regeneration" src="images/icons/diamond.png"/><br>
                <b>Extra Regeneration</b>
            </div>

            <div class="CharacterChanges">
                <img style="width:75px;margin:5px;" alt="Character Changes" src="images/icons/man.png"/><br>
                <b>Character Changes</b>
            </div>

            <div class="specialsurprise">
                <img style="width:75px;margin:5px;" alt="Special Surprises" src="images/icons/open-box.png"/><br>
                <b>Special Surprises</b>
            </div>
        </div>

        <div id="bloodlineItem" class="drawers" style="display:none;">
            <div class="page-sub-title">
                Buy Bloodline Items
            </div>

            <div>
                If you buy a bloodline item you will be given an item to bring to the bloodline clinic in the village hall.<br>
                Once there, the doctors will seal the contents of the item inside you, granting you the bloodline. <br>
                Costs are given in <b>reputation points</b>. You currently have <b>{$user[0]['rep_now']}</b> Reputation points<br>
                <br>
            </div>







            {$i = 0}
            <div class="table-grid table-column-4 font-small text-left">
                {if $current_bloodline_type != '' && $current_bloodline_type != 'Highest'}
                    {if $current_bloodline_type != 'Taijutsu'} 
                            <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Chakra Onix</b></div>
                            <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Changes your bloodline's type to Taijutsu</div>
                            <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>120 pts</div>
                            <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=1032">Buy</a></div>
                    {/if}
                    {if $current_bloodline_type != 'Bukijutsu'}
                            <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Chakra Sapphire</b></div>
                            <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Changes your bloodline's type to Bukijutsu</div>
                            <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>120 pts</div>
                            <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=1033">Buy</a></div>
                    {/if}
                    {if $current_bloodline_type != 'Ninjutsu'}
                            <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Chakra Ruby</b></div>
                            <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Changes your bloodline's type to Ninjutsu</div>
                            <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>120 pts</div>
                            <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=1034">Buy</a></div>
                    {/if}
                    {if $current_bloodline_type != 'Genjutsu'}
                            <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Chakra Amber</b></div>
                            <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Changes your bloodline's type to Genjutsu</div>
                            <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>120 pts</div>
                            <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=1035">Buy</a></div>
                    {/if}
                {/if}
                
                {if $village != 'Syndicate'}
                  {if $village == 'Shine'}
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Sulfur Wing</b></div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Give you a random Shine bloodline</div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>50 pts</div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=982">Buy</a></div>
                  {else if $village == 'Silence'}
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Swirling Spike</b></div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Give you a random Silence bloodline</div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>50 pts</div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=983">Buy</a></div>
                  {else if $village == 'Samui'}
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Static Pelt</b></div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Give you a random Samui bloodline</div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>50 pts</div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=984">Buy</a></div>
                  {else if $village == 'Konoki'}
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Scorpion Stinger</b></div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Give you a random Konoki bloodline</div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>50 pts</div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=985">Buy</a></div>
                  {else if $village == 'Shroud'}
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Slithering Tentacle</b></div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Give you a random Shroud bloodline</div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>50 pts</div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=986">Buy</a></div>
                  {/if}
                {/if}
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Skyfire Fist</b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Gives you a random Taijutsu A-rank bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>50 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=987">Buy</a></div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Skyfire Shard</b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Gives you a random Ninjutsu A-rank bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>50 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=990">Buy</a></div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Skyfire Blade</b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Gives you a random Bukijutsu A-rank bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>50 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=993">Buy</a></div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Skyfire Ring</b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Gives you a random Genjutsu A-rank bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>50 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=996">Buy</a></div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Skyfire Diamond</b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Gives you a random A-rank bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>40 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=1">Buy</a></div>
              
              
              
              
              
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Sapphire Wave Fist</b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Gives you a random Taijutsu B-rank bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>40 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=988">Buy</a></div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Sapphire Wave Shard</b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Gives you a random Ninjutsu B-rank bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>40 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=991">Buy</a></div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Sapphire Wave Blade</b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Gives you a random Bukijutsu B-rank bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>40 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=994">Buy</a></div>
                    
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Sapphire Wave Ring</b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Gives you a random Genjutsu B-rank bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>40 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=997">Buy</a></div>
                    
                    
                    
                    
                    
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Sapphire Wave Pendant </b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Gives you a random B-rank bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>30 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=2">Buy</a></div>
                    
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Ruby Star Fist</b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Gives you a random Taijutsu C-rank bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>30 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=989">Buy</a></div>
                    
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Ruby Star Shard</b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Gives you a random Ninjutsu C-rank bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>30 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=992">Buy</a></div>
                    
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Ruby Star Blade</b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Gives you a random Bukijutsu C-rank bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>30 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=995">Buy</a></div>
                    
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Ruby Star Signet Ring</b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Gives you a random Genjutsu C-rank bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>30 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=998">Buy</a></div>
                    
                    
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Ruby Star Ring</b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Gives you a random C-rank bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>20 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=3">Buy</a></div>
                    
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Chakra Laced Quartz</b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Gives you a random D-rank bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>10 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=4">Buy</a></div>
                    
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><b>Stone of Heraldry</b></div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>Removes your bloodline</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>5 pts</div>
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}><a href="?id={$smarty.get.id}&act=buy&iid=5">Buy</a></div>
            </div>

        </div>



        <div id="switchSpecial" class="drawers" style="display:none;">
            <div class="page-sub-title">
                Change Specialization
            </div>

            <div>
                Chose the wrong specialization? no worries, you can change it here.<br>
                Costs are given in <b>reputation points</b>. You currently have <b>{$user[0]['rep_now']}</b> Reputation points<br>
                <br>
            </div>

            {$i = 0}
            <div class="table-grid table-column-4 font-small text-left">
                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    <b>Taijutsu</b>
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    Change your Speciality to Taijutsu
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    15 pts
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    <a href="?id={$smarty.get.id}&act=buy&iid=6">Buy</a>
                </div>



                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    <b>Ninjutsu</b>
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    Change your Speciality to Ninjutsu
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    15 pts
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    <a href="?id={$smarty.get.id}&act=buy&iid=7">Buy</a>
                </div>



                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    <b>Genjutsu</b>
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    Change your Speciality to Genjutsu
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    15 pts
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    <a href="?id={$smarty.get.id}&act=buy&iid=8">Buy</a>
                </div>



                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    <b>Bukijutsu</b>
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    Change your Speciality to Bukijutsu (weapons)
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    15 pts
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    <a href="?id={$smarty.get.id}&act=buy&iid=9">Buy</a>
                </div>
            </div>

        </div>



        <div id="professionMaterial" class="drawers" style="display:none;">
            <div class="page-sub-title">
                Buy Profession Materials
            </div>

            <div>
                Don't want to run around gathering resources? Buy a pack and speed up your crafting.<br>
                Costs are given in <b>popularity points</b>. You currently have <b>{$user[0]['pop_now']}</b> popularity points<br>
                <br>
            </div>

            {$i = 0}
            <div class="table-grid table-column-4 font-small text-left">
                {if !empty($professionPacks)}
                    {foreach $professionPacks as $key => $surprise}                                
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                            {foreach $surprise as $key2 => $item}
                                {if !empty($item.name)} 
                                    {$item.name}
                                    {break}
                                {/if}
                            {/foreach}
                        </div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                            {foreach $surprise as $key2 => $item}
                                {if !empty($item.description)} 
                                    {$item.description}
                                    {break}
                                {/if}
                            {/foreach}
                        </div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                            {if $surprise[0]["cost_type"] == "item"}
                                {$surprise[0]["cost_item_Number"]} {$surprise[0]["cost_amount"]}
                            {else}
                                {$surprise[0]["cost_amount"]} {$surprise[0]["cost_type"]}
                            {/if}
                        </div>
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                            <a href="?id={$smarty.get.id}&act=buy&iid=32&pack={$key}">Buy</a>
                        </div>
                    {/foreach}
                {else}
                    <div class="table-span-4">
                       No profession packs are available for you at this moment
                    </div>
                {/if}
            </div>

        </div>



        <div id="ExtraRegen" class="drawers" style="display:none;">
            <div class="page-sub-title">
                Temporary Extra Regeneration
            </div>

            <div>
                Everything a bit too slow for you? Buy extra regen to speed things up.<br>
                Costs are given in <b>reputation points</b>. You currently have <b>{$user[0]['rep_now']}</b> Reputation points<br>
                <br>
            </div>

            {$i = 0}
            <div class="table-grid table-column-4 font-small text-left">
                {if !isset($regenBoostTimer)}
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        <b>Minor Regen</b>
                    </div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        Increases your regen. rate by 7.5% for 7, 15 or 30 days
                    </div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        ~>
                    </div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        <a href="?id={$smarty.get.id}&act=buy&iid=13">Buy</a>
                    </div>



                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        <b>Moderate Regen</b>
                    </div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        Increases your regen. rate by 10% for 7, 15 or 30 days
                    </div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        ~>
                    </div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        <a href="?id={$smarty.get.id}&act=buy&iid=14">Buy</a>
                    </div>


                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        <b>Major Regen</b>
                    </div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        Increases your regen. rate by 12.5% for 7, 15 or 30 days
                    </div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        ~>
                    </div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        <a href="?id={$smarty.get.id}&act=buy&iid=15">Buy</a>
                    </div>



                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        <b>Giant Regen</b>
                    </div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        Increases your regen. rate by 15% for 7, 15 or 30 days
                    </div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        ~>
                    </div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        <a href="?id={$smarty.get.id}&act=buy&iid=16">Buy</a>
                    </div>
                {else}
                    <div class="talbe-span-4">
                        You currently have a regeneration boost of <b>{$regenBoostAmount}</b> regen. <br>
                        Time till it expires:
                        {$regenBoostTimer}
                    </div>
                {/if}
            </div>

        </div>



        <div id="CharacterChanges" class="drawers" style="display:none;">
            <div class="page-sub-title">
                Character Changes
            </div>

            <div>
                Sometimes new is better; here you have the option of changing various things about your character.<br>
                Costs are given in <b>reputation points</b>. You currently have <b>{$user[0]['rep_now']}</b> Reputation points<br>
                <br>
            </div>

            {$i = 0}
            <div class="table-grid table-column-4 font-small text-left">
                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    <b>1 Namechange</b>
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    Used to change username in preferences menu.
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    20 pts
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    <a href="?id={$smarty.get.id}&act=buy&iid=18">Buy</a>
                </div>



                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    <b>Gender Change</b>
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    Changes you to the opposite gender (Male or Female)
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    5 pts
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    <a href="?id={$smarty.get.id}&act=buy&iid=19">Buy</a>
                </div>



                {if isset($villageChanging) && $villageChanging == true }
                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        <b>Village Change</b>
                    </div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        Change villages without penalties
                    </div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        ~> pts
                    </div>

                    <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                        <a href="?id={$smarty.get.id}&act=buy&iid=20">Buy</a>
                    </div>
                {/if}



                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    <b>Element Re-Roll</b>
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    Re-roll one of your elemental affinities
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    5 pts
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    <a href="?id={$smarty.get.id}&act=buy&iid=30">Buy</a>
                </div>
            </div>

        </div>



        <div id="specialsurprise" class="drawers" style="display:none;">
            <div class="page-sub-title">
                Special Surprises
            </div>

            <div>
                The special surprise packs can either contain specific items, or reward one item from a list of entries. See each entry
                for more details. Besides the "Popularity Pack", which is always available, occasionally we also have limited edition 
                surprise packs, which are only there for a short time.  Costs can be either reputation points, popularity points, ryo, or even another item!<br>
                You currently have <b>{$user[0]['rep_now']}</b> Reputation points and <b>{$user[0]['pop_now']}</b> Popularity points<br>
                <br>
            </div>

            {$i = 0}
            <div class="table-grid table-column-4 font-small text-left">
                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    Pack Name
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    Description
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    Price
                </div>

                <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                    Action
                </div>

                {if !empty($specialsurprises)}
                    {foreach $specialsurprises as $key => $surprise}                                
                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                            {foreach $surprise as $key2 => $item}
                                {if !empty($item.name)} 
                                    {$item.name}
                                    {break}
                                {/if}
                            {/foreach}
                        </div>

                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                            {foreach $surprise as $key2 => $item}
                                {if !empty($item.description)} 
                                    {$item.description}
                                    {break}
                                {/if}
                            {/foreach}
                        </div>

                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                            {if $surprise[0]["cost_type"] == "item"}
                                {$surprise[0]["cost_item_Number"]} {$surprise[0]["cost_amount"]}
                            {else}
                                {$surprise[0]["cost_amount"]} {$surprise[0]["cost_type"]}
                            {/if}
                        </div>

                        <div class="table-cell-no-border table-alternate-{$i % 2 + 1}"{$i = $i + 0.25}>
                            <a href="?id={$smarty.get.id}&act=buy&iid=31&pack={$key}">Buy</a>
                        </div>
                    {/foreach}
                {/if}
            </div>

        </div>

    </div>
</div>