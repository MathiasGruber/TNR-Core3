{if isset($paypal) && !empty($paypal)}
    {$subSelect="paypal"}
    {include file="file:{$absPath}/{$paypal|replace:'.tpl':'_mobile.tpl'}" title="Paypal Payments Screen"}
 {/if}
 
 {if isset($mobile) && !empty($mobile)}
     {$subSelect="mobile"}
    {include file="file:{$absPath}/{$mobile|replace:'.tpl':'_mobile.tpl'}" title="Mobile Payments Screen"}
 {/if}
 
 <tr><td><a href="?id={$smarty.get.id}">Return</a></td></tr>