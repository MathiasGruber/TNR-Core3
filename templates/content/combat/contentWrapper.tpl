<div id="topScreen">
     {if isset($topScreen) && !empty($topScreen)}
        {include file="file:{$absPath}/{$topScreen}" title="Main Screen"}
     {/if}
</div>

<div id="optionalScreen">
     {if isset($optionalScreen) && !empty($optionalScreen)}
        {include file="file:{$absPath}/{$optionalScreen}" title="Optional Screen"}
     {/if}
</div>

<table id="battleOptionWrapper" style="width:95%;border-collapse:collapse;">
    <tr>
        <td width="50%" style="text-align:left;padding:0px;" valign="top">
            <div align="left" id="secondaryScreen">
                {if isset($secondaryScreen) && !empty($secondaryScreen)}
                   {include file="file:{$absPath}/{$secondaryScreen}" title="Secondary Screen"}
                {/if}
           </div>
        </td>
        <td width="50%" style="text-align:right;padding:0px;" valign="top">
            <div align="right" id="tertiaryScreen">
                {if isset($tertiaryScreen) && !empty($tertiaryScreen)}
                   {include file="file:{$absPath}/{$tertiaryScreen}" title="Tertiary Screen"}
                {/if}
           </div>
        </td>
    </tr>
</table>