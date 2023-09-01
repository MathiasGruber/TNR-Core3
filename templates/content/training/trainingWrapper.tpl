<script type="text/javascript">
    {include file="./Scripts/trainScripts.js"}
</script>

<div align="center">
    <table class="table" width="95%">
        <tr>
            <td colspan="4" class="subHeader">Training Overview</td>
        </tr>
        <tr>
            <td colspan="4" class="tableColumns tdBorder">User Stats</td>
        </tr>
        <tr>
            <td width="30%" style="text-align:left;">
                <b>Rank:</b><br>
                <b>Level:</b><br><br>
                <b>Taijutsu strength:</b><br>
                <b>Ninjutsu strength:</b><br>
                <b>Genjutsu strength:</b><br>
                <b>Bukijutsu strength:</b><br>
                <br>
                <b>Primary element:</b> <br>
                <b>Secondary element:</b> <br>
            </td>
            <td width="23%" style="text-align:left;">
                {$user['rank']}<br>
                {$user['level']}<br>
                <br>
                <span id="tai_off">{$user['tai_off']}</span><br>
                <span id="nin_off">{$user['nin_off']}</span><br>
                <span id="gen_off">{$user['gen_off']}</span><br>
                <span id="weap_off">{$user['weap_off']}</span><br>
                <br>
                <span id="element_mastery_1">{$user['element_mastery_1']}</span><br>
                <span id="element_mastery_2">{$user['element_mastery_2']}</span><br>
                <br>
            </td>
            <td colspan="2" valign="top"><img src="{$avatar}" /></img></td>
        </tr>

        <tr>
            <td style="text-align:left;">
                <b>Taijutsu defense:</b><br>
                <b>Ninjutsu defense:</b><br>
                <b>Genjutsu defense:</b><br>
                <b>Bukijutsu defense:</b>
            </td>
            <td style="text-align:left;">
                <span id="tai_def">{$user['tai_def']}</span><br>
                <span id="nin_def">{$user['nin_def']}</span><br>
                <span id="gen_def">{$user['gen_def']}</span><br>
                <span id="weap_def">{$user['weap_def']}</span>
            </td>
            <td style="text-align:left;">
                <b>Strength:</b><br>
                <b>Intelligence:</b><br>
                <b>Willpower:</b><br>
                <b>Speed:</b>
            </td>
            <td style="text-align:left;">
                <span id="strength">{$user['strength']}</span><br>
                <span id="intelligence">{$user['intelligence']}</span><br>
                <span id="willpower">{$user['willpower']}</span><br>
                <span id="speed">{$user['speed']}</span>
            </td>
        </tr> 
        {if isset($release)}
            <tr><td colspan="4" class="tableColumns tdBorder" >Time until release from jail: {$release}</td></tr>
        {/if}
    </table>
            
    <div id="pageWrapper">
        {if isset($wrapLoad)}
            {include file="file:{$absPath}/{$wrapLoad}" title="Training options"}
        {/if}
    </div>
</div>