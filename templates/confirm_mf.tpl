<div class="lazy page-box">
    <div class="lazy page-title">
        {$subHeader}
    </div>

    <form id="form1" name="form1" method="post" action="" class="lazy page-content {if $storage_name_2 != 'n/a' && $storave_value_2 != 'n/a'}page-column-2{/if}">
        <div class="{if $storage_name_2 != 'n/a' && $storave_value_2 != 'n/a'}span-2{/if}">
            {$msg}
        </div>
        {if $storage_name_1 != 'n/a' && $storave_value_1 != 'n/a'}
              <input type="hidden" name="{$storage_name_1}" value="{$storage_value_1}">

            {if $storage_name_2 != 'n/a' && $storave_value_2 != 'n/a'}
              <input type="hidden" name="{$storage_name_2}" value="{$storage_value_2}">
            {/if}

        {/if}

        <input name="Submit" type="submit" id="Submit" value="{$returnLink}" class="lazy page-button-fill {if $storage_name_2 != 'n/a' && $storave_value_2 != 'n/a'}span-2{/if}"/>
    </form>
</div>