{if isset($paypal) && !empty($paypal)}
    {$subSelect="paypal"}
    {include file="file:{$absPath}/{$paypal}" title="Paypal Payments Screen"}
 {/if}
 
 {if isset($mobile) && !empty($mobile)}
     {$subSelect="mobile"}
    {include file="file:{$absPath}/{$mobile}" title="Mobile Payments Screen"}
 {/if}
 
 <a href="?id={$smarty.get.id}" class="returnLink">Return</a><br><br>