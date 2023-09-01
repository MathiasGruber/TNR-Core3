<head>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="description" content="A free browser based online game set in the ninja world of the Seichi!" /> 
<meta name="keywords" content="mmorpg, online, rpg, game, anime, manga, strategy, multiplayer, ninja, community, core 3, theninja-rpg" />
<meta name="resource-type" content="document" /> 
<meta name="distribution" content="Global" /> 
<meta name="copyright" content="TheNinja-RPG.com" /> 
<meta name="revisit-after" content="1 day" /> 
<title>The Ninja-RPG.com - a free browser based mmorpg</title> 
<link rel="stylesheet" type="text/css" href="files/{$layoutDir}/style.min.css" />
<link rel="stylesheet" type="text/css" media href="files/{$layoutDir}/themes/{trim($themeDir,"''")}/theme.min.css" />

{if $layoutDir == "layout_default"}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
{else}
    <script type="text/javascript" src="files/javascript/general.js"></script>
{/if}

<link rel="stylesheet" type="text/css" media href="files/general.css"/>
<script defer type="text/javascript" src="files/javascript/sorttable.js"></script>

  <!-- For Hiding things when javascript is enabled -->
{literal}<script defer language="JavaScript" type="text/javascript"> document.write('<style type="text/css">.jsHide {display:none; }</style>'); </script>
<script defer> $(document).on('mobileinit', function () { $.mobile.ajaxEnabled = false; }); </script>
{/literal}

{if isset($mobileLayout)}
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <link rel="stylesheet" media href="files/layout_mobile/jquery.mobile-1.4.5.min.css" />
    <script async src="files/layout_mobile/jquery.mobile-1.4.5.min.js"></script>
    <script async >
        $( document ).ready(function() {
            jQuery('#mobileContentContainer').scrollbar({
                "showArrows": true,
                "type": "advanced",
                "ignoreMobile": false
            });
        });
    </script>
{/if}
{if isset($addHtmlHeaderInfo)}{$addHtmlHeaderInfo}{/if} 
{if isset($extraJava)}{$extraJava}{/if}

{literal}
<link rel="stylesheet" media href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script>

<!-- Begin Cookie Consent plugin by Silktide - http://silktide.com/cookieconsent -->
<script defer type="text/javascript">
    window.cookieconsent_options = {"message":"This website uses cookies to ensure you get the best experience on our website.","dismiss":"Got it!","learnMore":"More info","link":"http://www.google.com/intl/en/policies/privacy/partners/","theme":"light-bottom"};
</script>

<script defer type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/1.0.9/cookieconsent.min.js"></script>
<!-- End Cookie Consent plugin -->
{/literal}

</head>