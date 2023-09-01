    <a href="?id=1">News</a> - 
    <a href="http://www.theninja-forum.com/index.php?app=nexus&module=support">Contact</a> - 
    <a href="?id=15">Rules</a> - 
    <a href="?id=7">Terms of Service</a> - 
    <a href="{$manualLink}">Manual</a> - 
    <a href="?id=75">About TNR</a> - 
    <a href="?id=76">Event History</a> - 
    <a href="?id=93">Changelog</a><br>    
    TheNinja-RPG © by <a href="https://www.facebook.com/pages/Studie-Tech-ApS/269047403131303">Studie-Tech ApS</a> 2005-{$YEAR} <br>
    Core 3.8 by <span title="Mostly Koala">Koala</span> &amp; Wolfpack16 &amp; Terriator - Layout by {$layoutCreator}<br>                                                                        
    Loaded in {$parseTime} seconds with {$queries} queries. Memory usage: {$memory}    
    {if isset($smarty.session.uid)}
        <span id="footerUserID" name="{$smarty.session.uid}"></span> 
    {/if}
    
 <!-- Facebook Pixel Code -->
{literal}
<script defer >
function lazyFacebookPixel() {
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','//connect.facebook.net/en_US/fbevents.js');

fbq('init', '1699842153567758');
fbq('track', "PageView");
{/literal}
{if isset($facebookEvents)}
    {foreach $facebookEvents as $entry}
        {if empty($entry[1])}
            fbq('track', "{$entry[0]}");
        {else}
            fbq('track', "{$entry[0]}", {literal}{value: {/literal}'{$entry[1]}'{literal}, currency: 'USD'}{/literal});
        {/if}
    {/foreach}
{/if}
{literal}
}
if (window.addEventListener)
window.addEventListener("load", lazyFacebookPixel, false);
else if (window.attachEvent)
window.attachEvent("onload", lazyFacebookPixel);
else window.onload = lazyFacebookPixel;
</script>

<noscript>
    <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=1699842153567758&ev=PageView&noscript=1"/>
</noscript>
{/literal}
<!-- End Facebook Pixel Code -->