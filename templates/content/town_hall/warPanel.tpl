<script type="text/javascript">
    $(document).ready(function() {
        var active = new Array();
        active["war"] = "";
        active["peace"] = "";
        var linkClasses = new Array("war", "peace");

        // Attack links to all the classes in the array
        for (var i = 0; i < linkClasses.length; i++) {
            // anonymous function to fix closures
            (function() {
                var index = i; // also needed to fix closures

                $(document).on("click", "." + linkClasses[index] + "InfoEntry", function(event) {
                    if (active[linkClasses[index]] != "") {
                        $("." + active[linkClasses[index]]).toggleClass('jsHide')
                    }
                    event.preventDefault();
                    name = $(event.target).attr('name');
                    console.log('Toggling hidden class: ' + name);
                    active[linkClasses[index]] = name + linkClasses[index];
                    $("." + active[linkClasses[index]]).toggleClass('jsHide');
                });

                $(document).on("change", '#' + linkClasses[index] + 'Selector', function(event) {
                    if (active[linkClasses[index]] != "") {
                        $("." + active[linkClasses[index]]).toggleClass('jsHide')
                    }
                    name = $("#" + linkClasses[index] + "Selector option:selected").val();
                    console.log('Toggling hidden class: ' + name);
                    active[linkClasses[index]] = name + linkClasses[index];
                    $("." + active[linkClasses[index]]).toggleClass('jsHide');
                });
                
                // Unhide the first elements
                var name = $("#"+linkClasses[index]+"Selector").val() + linkClasses[index]
                $("." + name ).toggleClass('jsHide');
                active[linkClasses[index]] = name;
            })();
        }        
    });
</script>

<div align="center">  

    <table class="table" width="95%">
        <tr>
            <td class="subHeader">War Control</td>
        </tr>
        <tr>
            <td>
                {if isset($otherVillages)}
                    With this form you may declare war on other villages or surrender active wars. Beware, wars cost village points to sustain and may break alliances, 
                    so be sure to check out war information on the different villages before doing anything rash.<br>
                    <form method="post" action="" style="display:inline;">
                        <select name="wardeclaration" id="warSelector">';
                            {foreach $otherVillages as $village}
                                <option value="{$village['name']}">{$village['name']}</option>
                            {/foreach}
                        </select>
                        &nbsp;
                        <input type="submit" name="Submit" value="Declare War" />
                    </form>
                {/if}

                {if !empty($userEnemies)}   
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <form method="post" action="" style="display:inline;">
                        <select name="surrenderrequest">
                            {foreach $userEnemies as $village}
                                <option value="{$village}">{$village}</option>
                            {/foreach}
                        </select>&nbsp;
                        <input type="submit" name="Submit" value="Request Surrender" />
                    </form>
                {/if} 
            </td>
        </tr>

        {if isset($otherVillages)}
            <tr>
                <td class="tableColumns" style="border-top:1px solid #580000;">
                    {foreach $otherVillages as $village}
                        <a class="warInfoEntry" name="{$village['name']}" href="#">{$village['name']}</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    {/foreach}
                </td>
            </tr>
            {foreach $otherVillages as $village}
                <tr>
                    <td class="{cycle values="row1,row2"} jsHide {$village['name']}war">
                        {$village['warInfo']['info']}
                    </td>
                </tr>
            {/foreach}
        {/if}
        <tr>
            <td class="tableColumns" style="border-top:1px solid #580000;">
                &nbsp;
            </td>
        </tr>
    </table> 

    <table class="table" width="95%">
        <tr>
            <td class="subHeader">Alliance Control</td>
        </tr>
        <tr>
            <td> 
                With this form you may request or break alliances with other villages. Forming alliances or breaking certain alliances may have consequences in regards to other alliances, so be careful.<br>
                <form method="post" action="" style="display:inline;">
                    <select name="requestalliance"  id="peaceSelector">
                        {foreach $otherVillages as $village}
                            <option value="{$village['name']}">{$village['name']}</option>
                        {/foreach}
                    </select>&nbsp;<input type="submit" name="Submit" value="Request Alliance" />
                </form>
                {if !empty($userAllies)}  
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <form method="post" action="" style="display:inline;">
                        <select name="breakalliance">
                            {foreach $userAllies as $village}
                                <option value="{$village}">{$village}</option>
                            {/foreach}
                        </select>&nbsp;<input type="submit" name="Submit" value="Break Alliance" />
                    </form>
                {/if}                                                        
            </td>
        </tr>

        {if isset($otherVillages)}
            <tr>
                <td class="tableColumns" style="border-top:1px solid #580000;">
                    {foreach $otherVillages as $village}
                        <a class="peaceInfoEntry" name="{$village['name']}" href="#">{$village['name']}</a>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    {/foreach}
                </td>
            </tr>
            {foreach $otherVillages as $village}
                <tr>
                    <td class="{cycle values="row1,row2"} jsHide {$village['name']}peace">
                        {$village['peaceInfo']['info']}
                    </td>
                </tr>
            {/foreach}
        {/if}

        <tr>
            <td class="tableColumns" style="border-top:1px solid #580000;">
                &nbsp;
            </td>
        </tr>
    </table>
    
   
    {if isset($requests)}
        {$subSelect="requests"}
        {include file="file:{$absPath}/{$requests}" title="Requests"}
    {/if}     
        
    {if isset($allianceData)}
        {include file="file:{$absPath}/templates/content/alliance/alliances.tpl" title="Alliance Data"}
    {/if}
    
    <a href="?id={$smarty.get.id}&act={$smarty.get.act}">Return</a>
</div>