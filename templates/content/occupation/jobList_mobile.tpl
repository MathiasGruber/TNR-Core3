{if isset($occupationlist)}
    {$subSelect="occupationlist"}
    {include file="file:{$absPath}/{$occupationlist|replace:'.tpl':'_mobile.tpl'}" title="Normal Occupations"}
{/if}

{if isset($specialList)}
    {$subSelect="specialList"}
    {include file="file:{$absPath}/{$specialList|replace:'.tpl':'_mobile.tpl'}" title="Special Occupations"}
{/if}