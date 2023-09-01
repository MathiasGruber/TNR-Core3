<script defer type="text/javascript">
    {include file={$scriptFile}}
</script>

<div id="pageWrapper">
    {$full_page = true}
    {if isset($mainScreen)}
        {if strpos($mainScreen, '_mf') === false && file_exists($absPath|cat:'/'|cat:str_replace('.tpl','_mf.tpl',$mainScreen))}
            {$mainScreen = str_replace('.tpl','_mf.tpl',$mainScreen)}
        {/if}
        <!--page wrapper is using: {var_dump($mainScreen)}-->
        {include file="file:{$absPath}/{$mainScreen}" title="Shop Screen"}
    {/if}
</div>
