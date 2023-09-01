 <script type="text/javascript">
 $(document).ready(function() {
     $('.switchSpecial').click(function() {
        $("#switchSpecial").slideToggle('slow');  
    });
    $('.professionMaterial').click(function() {
        $("#professionMaterial").slideToggle('slow');  
    });
    $('.ExtraRegen').click(function() {
        $("#ExtraRegen").slideToggle('slow');  
    });
    $('.CharacterChanges').click(function() {
        $("#CharacterChanges").slideToggle('slow');  
    });
    $('.specialsurprise').click(function() {
        $("#specialsurprise").slideToggle('slow');  
    });
    $('.bloodlineItem').click(function() {
        $("#bloodlineItem").slideToggle('slow');  
    });
    
    
    
});
 </script>

<div align="center">
    <table width="95%" class="table">
        <tr>
            <td colspan="4" class="subHeader">Black Market Services</td>
        </tr>        
        <tr>
            <td colspan="4" style="color:#990000;border-bottom: 1px solid black;">
                You can purchase a series of special services using either reputation points or popularity points. 
                Click the icons below to get more information.<br>
            </td>
        </tr>
            
        <tr>
            <td colspan="4">
                <table width="100%" style="padding:0px;margin:0px;border-collapse: collapse;">
                    <tr>
                        <td style="width:25%;">
                            <span class="bloodlineItem">
                                <img style="width:75px;margin:5px;" alt="New Bloodline" src="images/icons/chemistry.png"></img><br>
                                <b>New Bloodline</b>
                            </span>
                         </td>
                        <td style="width:25%;">
                            <span class="switchSpecial">
                                <img style="width:75px;margin:5px;" alt="Change Specialization" src="images/icons/settings.png"></img><br>
                                <b>Change Specialization</b>
                            </span>
                         </td>
                         <td style="width:25%;">
                            <span class="professionMaterial">
                                <img style="width:75px;margin:5px;" alt="Profession Materials" src="images/icons/package.png"></img><br>
                                <b>Profession Materials</b>
                            </span>
                         </td>
                         <td style="width:25%;">
                            <span class="ExtraRegen">
                                <img style="width:75px;margin:5px;" alt="Extra Regeneration" src="images/icons/diamond.png"></img><br>
                                <b>Extra Regeneration</b>
                            </span>
                         </td>
                    </tr>
                    <tr>
                        <td></td>
                         <td>
                            <span class="CharacterChanges">
                                <img style="width:75px;margin:5px;" alt="Character Changes" src="images/icons/man.png"></img><br>
                                <b>Character Changes</b>
                            </span>
                         </td>
                         <td>
                            <span class="specialsurprise">
                                <div style="position: relative; left: 0; top: 0;">
                                    <img style="width:75px;margin:5px;position:relative;top:0;left:0;" alt="Special Surprises" src="images/icons/open-box.png" />
                                </div>                                
                                <b>Special Surprises</b>
                                
                            </span>
                         </td>
                         <td></td>
                    </tr>
                </table>
            </td>            
        </tr> 
        <tr>
            <td colspan="4" style="margin:0px;padding:0px;">
                <div id="bloodlineItem" style="display:none">
                    <table width="100%" style="padding:0px;margin:0px;border-collapse: collapse;">
                        <tr>
                            <td colspan="4" class="tableColumns tdBorder">Buy Bloodline Items</td>
                        </tr>
                        <tr>
                            <td colspan="4" style="color:#990000;border-bottom: 1px solid black;">
                                If you buy a bloodline item you will be given an item to bring to the bloodline clinic in the village hall.<br>
                                Once there, the doctors will seal the contents of the item inside you, granting you the bloodline. 
                                <br>Costs are given in <b>reputation points</b>. You currently have <b>{$user[0]['rep_now']}</b> Reputation points<br>
                            </td>
                        </tr>                
                        
                        {if $current_bloodline_type != '' && $current_bloodline_type != 'Highest'}
                            {if $current_bloodline_type != 'Taijutsu'}
                                <tr class="row{cycle values="1,2"}">
                                    <td><b>Chakra Onix</b></td>
                                    <td>Changes your bloodline's type to Taijutsu</td>
                                    <td>120 pts</td>
                                    <td><a href="?id={$smarty.get.id}&act=buy&iid=1032">Buy</a></td>
                                </tr>
                            {/if}
                            {if $current_bloodline_type != 'Bukijutsu'}
                                <tr class="row{cycle values="1,2"}">
                                    <td><b>Chakra Sapphire</b></td>
                                    <td>Changes your bloodline's type to Bukijutsu</td>
                                    <td>120 pts</td>
                                    <td><a href="?id={$smarty.get.id}&act=buy&iid=1033">Buy</a></td>
                                </tr>
                            {/if}
                            {if $current_bloodline_type != 'Ninjutsu'}
                                <tr class="row{cycle values="1,2"}">
                                    <td><b>Chakra Ruby</b></td>
                                    <td>Changes your bloodline's type to Ninjutsu</td>
                                    <td>120 pts</td>
                                    <td><a href="?id={$smarty.get.id}&act=buy&iid=1034">Buy</a></td>
                                </tr>
                            {/if}
                            {if $current_bloodline_type != 'Genjutsu'}
                                <tr class="row{cycle values="1,2"}">
                                    <td><b>Chakra Amber</b></td>
                                    <td>Changes your bloodline's type to Genjutsu</td>
                                    <td>120 pts</td>
                                    <td><a href="?id={$smarty.get.id}&act=buy&iid=1035">Buy</a></td>
                                </tr>
                            {/if}
                        {/if}
                        
                        {if $village != 'Syndicate'}
                          {if $village == 'Shine'}
                            <tr class="row{cycle values="1,2"}">
                                <td><b>Sulfur Wing</b></td>
                                <td>Give you a random Shine bloodline</td>
                                <td>50 pts</td>
                                <td><a href="?id={$smarty.get.id}&act=buy&iid=982">Buy</a></td>
                            </tr>
                          {else if $village == 'Silence'}
                            <tr class="row{cycle values="1,2"}">
                                <td><b>Swirling Spike</b></td>
                                <td>Give you a random Silence bloodline</td>
                                <td>50 pts</td>
                                <td><a href="?id={$smarty.get.id}&act=buy&iid=983">Buy</a></td>
                            </tr>
                          {else if $village == 'Samui'}
                            <tr class="row{cycle values="1,2"}">
                                <td><b>Static Pelt</b></td>
                                <td>Give you a random Samui bloodline</td>
                                <td>50 pts</td>
                                <td><a href="?id={$smarty.get.id}&act=buy&iid=984">Buy</a></td>
                            </tr>
                          {else if $village == 'Konoki'}
                            <tr class="row{cycle values="1,2"}">
                                <td><b>Scorpion Stinger</b></td>
                                <td>Give you a random Konoki bloodline</td>
                                <td>50 pts</td>
                                <td><a href="?id={$smarty.get.id}&act=buy&iid=985">Buy</a></td>
                            </tr>
                          {else if $village == 'Shroud'}
                            <tr class="row{cycle values="1,2"}">
                                <td><b>Slithering Tentacle</b></td>
                                <td>Give you a random Shroud bloodline</td>
                                <td>50 pts</td>
                                <td><a href="?id={$smarty.get.id}&act=buy&iid=986">Buy</a></td>
                            </tr>
                          {/if}
                        {/if}
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Skyfire Fist</b></td>
                            <td>Gives you a random Taijutsu A-rank bloodline</td>
                            <td>50 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=987">Buy</a></td>
                        </tr>
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Skyfire Shard</b></td>
                            <td>Gives you a random Ninjutsu A-rank bloodline</td>
                            <td>50 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=990">Buy</a></td>
                        </tr>
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Skyfire Blade</b></td>
                            <td>Gives you a random Bukijutsu A-rank bloodline</td>
                            <td>50 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=993">Buy</a></td>
                        </tr>
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Skyfire Ring</b></td>
                            <td>Gives you a random Genjutsu A-rank bloodline</td>
                            <td>50 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=996">Buy</a></td>
                        </tr>
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Skyfire Diamond</b></td>
                            <td>Gives you a random A-rank bloodline</td>
                            <td>40 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=1">Buy</a></td>
                        </tr>
                      
                      
                      
                      
                      
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Sapphire Wave Fist</b></td>
                            <td>Gives you a random Taijutsu B-rank bloodline</td>
                            <td>40 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=988">Buy</a></td>
                        </tr> 
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Sapphire Wave Shard</b></td>
                            <td>Gives you a random Ninjutsu B-rank bloodline</td>
                            <td>40 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=991">Buy</a></td>
                        </tr>
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Sapphire Wave Blade</b></td>
                            <td>Gives you a random Bukijutsu B-rank bloodline</td>
                            <td>40 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=994">Buy</a></td>
                        </tr> 
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Sapphire Wave Ring</b></td>
                            <td>Gives you a random Genjutsu B-rank bloodline</td>
                            <td>40 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=997">Buy</a></td>
                        </tr>
                      
                      
                      
                      
                      
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Sapphire Wave Pendant </b></td>
                            <td>Gives you a random B-rank bloodline</td>
                            <td>30 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=2">Buy</a></td>
                        </tr>
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Ruby Star Fist</b></td>
                            <td>Gives you a random Taijutsu C-rank bloodline</td>
                            <td>30 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=989">Buy</a></td>
                        </tr>
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Ruby Star Shard</b></td>
                            <td>Gives you a random Ninjutsu C-rank bloodline</td>
                            <td>30 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=992">Buy</a></td>
                        </tr>
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Ruby Star Blade</b></td>
                            <td>Gives you a random Bukijutsu C-rank bloodline</td>
                            <td>30 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=995">Buy</a></td>
                        </tr>
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Ruby Star Signet Ring</b></td>
                            <td>Gives you a random Genjutsu C-rank bloodline</td>
                            <td>30 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=998">Buy</a></td>
                        </tr>
                      
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Ruby Star Ring</b></td>
                            <td>Gives you a random C-rank bloodline</td>
                            <td>20 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=3">Buy</a></td>
                        </tr>
                      
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Chakra Laced Quartz</b></td>
                            <td>Gives you a random D-rank bloodline</td>
                            <td>10 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=4">Buy</a></td>
                        </tr>
                      
                        <tr class="row{cycle values="1,2"}">
                            <td><b>Stone of Heraldry</b></td>
                            <td>Removes your bloodline</td>
                            <td>5 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=5">Buy</a></td>
                        </tr>
                    </table>
                </div>                
                <div id="switchSpecial" style="display:none">
                    <table width="100%" style="padding:0px;margin:0px;border-collapse: collapse;">
                        <tr>
                            <td colspan="4" class="tableColumns tdBorder">Change Specialization</td>
                        </tr>                        
                        <tr>
                            <td colspan="4" style="color:#990000;border-bottom: 1px solid black;">
                               Chose the wrong specialization? no worries, you can change it here.
                               <br>Costs are given in <b>reputation points</b>. You currently have <b>{$user[0]['rep_now']}</b> Reputation points<br>
                            </td>
                        </tr>
                        <tr class="row2">
                            <td><b>Taijutsu</b></td>
                            <td>Change your Speciality to Taijutsu</td>
                            <td>15 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=6">Buy</a></td>
                        </tr>  
                        <tr class="row1">
                            <td><b>Ninjutsu</b></td>
                            <td>Change your Speciality to Ninjutsu</td>
                            <td>15 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=7">Buy</a></td>
                        </tr>
                        <tr class="row2">
                            <td><b>Genjutsu</b></td>
                            <td>Change your Speciality to Genjutsu</td>
                            <td>15 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=8">Buy</a></td>
                        </tr>
                        <tr class="row1">
                            <td><b>Bukijutsu</b></td>
                            <td>Change your Speciality to Bukijutsu (weapons)</td>
                            <td>15 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=9">Buy</a></td>
                        </tr>
                    </table>
                </div>
                <div id="professionMaterial" style="display:none">
                    <table width="100%" style="padding:0px;margin:0px;border-collapse: collapse;">
                        <tr>
                            <td colspan="4" class="tableColumns tdBorder">Buy Profession Materials</td>
                        </tr>                                              
                        <tr>
                            <td colspan="4" style="color:#990000;border-bottom: 1px solid black;">
                               Don't want to run around gathering resources? Buy a pack and speed up your crafting.
                               <br>Costs are given in <b>popularity points</b>. You currently have <b>{$user[0]['pop_now']}</b> popularity points<br>
                            </td>
                        </tr>
                        {if !empty($professionPacks)}
                            {foreach $professionPacks as $key => $surprise}                                
                                <tr class="{cycle values="row1,row2"}" >
                                    <td>
                                        {foreach $surprise as $key2 => $item}
                                            {if !empty($item.name)} 
                                                {$item.name}
                                                {break}
                                            {/if}
                                        {/foreach}
                                    </td>
                                    <td>
                                        {foreach $surprise as $key2 => $item}
                                            {if !empty($item.description)} 
                                                {$item.description}
                                                {break}
                                            {/if}
                                        {/foreach}
                                    </td>
                                    <td>
                                        {if $surprise[0]["cost_type"] == "item"}
                                            {$surprise[0]["cost_item_Number"]} {$surprise[0]["cost_amount"]}
                                        {else}
                                            {$surprise[0]["cost_amount"]} {$surprise[0]["cost_type"]}
                                        {/if}
                                    </td>
                                    <td><a href="?id={$smarty.get.id}&act=buy&iid=32&pack={$key}">Buy</a></td>
                                </tr>                                    
                            {/foreach}
                        {else}
                             <tr class="row1">
                                <td colspan="4">
                                   No profession packs are available for you at this moment
                                </td>
                            </tr>
                        {/if}
                    </table>
                </div> 
                <div id="ExtraRegen" style="display:none">
                    <table width="100%" style="padding:0px;margin:0px;border-collapse: collapse;">
                        <tr>
                            <td colspan="4" class="tableColumns tdBorder">Temporary Extra Regeneration</td>
                        </tr>
                                                                      
                        <tr>
                            <td colspan="4" style="color:#990000;border-bottom: 1px solid black;">
                               Everything a bit too slow for you? Buy extra regen to speed things up.
                               <br>Costs are given in <b>reputation points</b>. You currently have <b>{$user[0]['rep_now']}</b> Reputation points<br>
                            </td>
                        </tr>
                        {if !isset($regenBoostTimer)}
                            <tr class="row2">
                                <td><b>Minor Regen</b></td>
                                <td>Increases your regen. rate by 7.5% for 7, 15 or 30 days</td>
                                <td>~></td>
                                <td><a href="?id={$smarty.get.id}&act=buy&iid=13">Buy</a></td>
                            </tr>
                            <tr class="row1">
                                <td><b>Moderate Regen</b></td>
                                <td>Increases your regen. rate by 10% for 7, 15 or 30 days</td>
                                <td>~></td>
                                <td><a href="?id={$smarty.get.id}&act=buy&iid=14">Buy</a></td>
                            </tr>
                            <tr class="row2">
                                <td><b>Major Regen</b></td>
                                <td>Increases your regen. rate by 12.5% for 7, 15 or 30 days</td>
                                <td>~></td>
                                <td><a href="?id={$smarty.get.id}&act=buy&iid=15">Buy</a></td>
                            </tr>
                            <tr class="row1">
                                <td><b>Giant Regen</b></td>
                                <td>Increases your regen. rate by 15% for 7, 15 or 30 days</td>
                                <td>~></td>
                                <td><a href="?id={$smarty.get.id}&act=buy&iid=16">Buy</a></td>
                            </tr>
                        {else}
                            <tr class="row1">
                                <td colspan="4">You currently have a regeneration boost of <b>{$regenBoostAmount}</b> regen. <br>
                                    Time till it expires:
                                    {$regenBoostTimer}
                                </td>
                            </tr>
                        {/if}
                    </table>
                </div>
                <div id="CharacterChanges" style="display:none">
                    <table width="100%" style="padding:0px;margin:0px;border-collapse: collapse;">
                        <tr>
                            <td colspan="4" class="tableColumns tdBorder">Character Changes</td>
                        </tr>
                                                                 
                        <tr>
                            <td colspan="4" style="color:#990000;border-bottom: 1px solid black;">
                               Sometimes new is better; here you have the option of changing various things about your character.
                               <br>Costs are given in <b>reputation points</b>. You currently have <b>{$user[0]['rep_now']}</b> Reputation points<br>
                            </td>
                        </tr>
                        <tr class="row2">
                            <td><b>1 Namechange</b></td>
                            <td>Used to change username in preferences menu.</td>
                            <td>20 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=18">Buy</a></td>
                        </tr>
                        <tr class="row1">
                            <td><b>Gender Change</b></td>
                            <td>Changes you to the opposite gender (Male or Female)</td>
                            <td>5 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=19">Buy</a></td>
                        </tr>
                        {if isset($villageChanging) && $villageChanging == true }
                            <tr class="row1">
                                <td><b>Village Change</b></td>
                                <td>Change villages without penalties</td>
                                <td>~> pts</td>
                                <td><a href="?id={$smarty.get.id}&act=buy&iid=20">Buy</a></td>
                            </tr>
                        {/if}
                        <tr class="row1">
                            <td><b>Element Re-Roll</b></td>
                            <td>Re-roll one of your elemental affinities</td>
                            <td>5 pts</td>
                            <td><a href="?id={$smarty.get.id}&act=buy&iid=30">Buy</a></td>
                        </tr>
                    </table>
                </div>
                <div id="specialsurprise" style="display:none">
                    <table width="100%" style="padding:0px;margin:0px;border-collapse: collapse;">
                        <tr>
                            <td colspan="4" class="tableColumns tdBorder">Special Surprises</td>
                        </tr>   

                        <tr>
                            <td colspan="4" style="color:#990000;border-bottom: 1px solid black;">
                                The special surprise packs can either contain specific items, or reward one item from a list of entries. See each entry
                                for more details. Besides the "Popularity Pack", which is always available, occasionally we also have limited edition 
                                surprise packs, which are only there for a short time.  Costs can be either reputation points, popularity points, ryo, or even another item!                                    
                                <br>You currently have <b>{$user[0]['rep_now']}</b> Reputation points and <b>{$user[0]['pop_now']}</b> Popularity points<br>
                            </td>
                        </tr> 
                        <tr class="row2">
                            <td class="tdTop">Pack Name</td>
                            <td class="tdTop" style="width:45%;">Description</td>
                            <td class="tdTop">Price</td>
                            <td class="tdTop">Action</td>
                        </tr>
                        {if !empty($specialsurprises)}
                            {foreach $specialsurprises as $key => $surprise}                                
                                <tr class="{cycle values="row1,row2"}" >
                                    <td>
                                        {foreach $surprise as $key2 => $item}
                                            {if !empty($item.name)} 
                                                {$item.name}
                                                {break}
                                            {/if}
                                        {/foreach}
                                    </td>
                                    <td>
                                        {foreach $surprise as $key2 => $item}
                                            {if !empty($item.description)} 
                                                {$item.description}
                                                {break}
                                            {/if}
                                        {/foreach}
                                    </td>
                                    <td>
                                        {if $surprise[0]["cost_type"] == "item"}
                                            {$surprise[0]["cost_item_Number"]} {$surprise[0]["cost_amount"]}
                                        {else}
                                            {$surprise[0]["cost_amount"]} {$surprise[0]["cost_type"]}
                                        {/if}
                                    </td>
                                    <td><a href="?id={$smarty.get.id}&act=buy&iid=31&pack={$key}">Buy</a></td>
                                </tr>                                    
                            {/foreach}
                        {/if}
                    </table>
                </div>                
            </td>
        </tr>    
    </table>
</div>