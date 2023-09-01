{if isset($errorLoad)} {include file="file:{$absPath}/{$errorLoad}" title="Errors"} {/if} 
{if !isset($hideContent) || $hideContent != true} 
    {if isset($CONTENT)} {$CONTENT} {/if} 
    {if $mf == 'yes'}
        {if isset($contentLoad)}
            {if file_exists($absPath|cat:'/'|cat:str_replace('.tpl','_mf.tpl',$contentLoad))}
                <!--{str_replace('.tpl','_mf.tpl',$contentLoad)}<br/>-->
                {include file=('file:'|cat:$absPath|cat:'/'|cat:str_replace('.tpl','_mf.tpl',$contentLoad)) title="Content"}
            {else}
                <!--this is not a mobile friendly page.<br/>{$contentLoad}<br/>-->
                {include file="file:{$absPath}/{$contentLoad}" title="Content"} 
            {/if} 
        {/if}

        {if isset($extraContentLoad)}
            {if file_exists($absPath|cat:'/'|cat:str_replace('.tpl','_mf.tpl',$extraContentLoad))}
                {include file=('file:'|cat:$absPath|cat:'/'|cat:str_replace('.tpl','_mf.tpl',$extraContentLoad)) title="ExtraContent"} 
            {else}
                {include file="file:{$absPath}/{$extraContentLoad}" title="ExtraContent"} 
            {/if} 
        {/if}
    {else}
        {if isset($contentLoad)}{include file="file:{$absPath}/{$contentLoad}" title="Content"} {/if} 
        {if isset($extraContentLoad)}{include file="file:{$absPath}/{$extraContentLoad}" title="ExtraContent"}{/if} 
    {/if}
{/if}