<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">    
 
<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml"> 
    {include file="file:{$absPath}/files/general_includes/html_head.tpl" title="HTML header"}

    <body style="margin-top:0; margin-bottom:0;">
        <div id="fb-root"></div>             
        <center>
            <table class="contentTable">
                <tr>
                    <td colspan="3">
                        <div id="mainGameTitle" style="margin-left:0; margin-right:0;  padding:0px; width:950px; text-align:center;">
                            <h1><a href="/?id=103" style="color:#FFFFFF; font-family:fontasia; font-size:100px; letter-spacing:5px; opacity:0.95;text-shadow: 2px 2px 1px #222222; text-decoration: none;">the Ninja-rpg</a></h1>
                            <!--<h1><a href="/?id=103" style="color:#222222; font-family:fontasia; font-size:100px; letter-spacing:5px; opacity:0.95;text-shadow: 4px 4px 2px #631a1a; text-decoration: none;">KoALALAnD</a></h1>-->
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top;width:193px;" id="div_widgets">
                            <!-- LEFT CONTAINER 230 -->
                            {if isset($widgetLoad)}
                                {include file="file:{$absPath}/{$widgetLoad}" title="Widgets"} 
                            {/if}
                    </td>
                    <td style="vertical-align: top;" align="center">
                        <!-- CENTER CONTAINER 660 --> 
                        <div id="centerContainer" style="width:570px;box-shadow: 0px 0px 10px rgba(0,0,0,.5);" align="center">
                            <!-- TOP LINKS -->
                            <div class="ribbonLink">
                                <div class="topmenulinks">
                                    <h2><a href="?id=1">News</a></h2>&nbsp; 
                                    <h2><a href="{$forumLink}index.php?app=nexus&module=support">Contact</a></h2>&nbsp; 
                                    <h2><a href="{$forumLink}">Forum</a></h2>&nbsp; 
                                    <h2><a href="{$manualLink}">Manual</a></h2>&nbsp; 
                                    <h2><a href="?id=15">Rules</a></h2>&nbsp; 
                                    <h2><a href="?id=7">ToS</a></h2>&nbsp; 
                                    <h2><a href="?id=42">Online</a></h2>
                                </div>
                            </div>	 
                            <div class="leftDiagonal">
                                <div class="perforationVertical1">
                                    <div class="perforationVertical2">			 
                                        <div class="perforationHorizontal"></div>		 
                                        <div class="leftDiagonal2" style="padding-top:10px;" id="div_contentInclude">
                                            {include file="file:{$absPath}/files/general_includes/contentInclude.tpl" title="Content Inclusion"}   
                                            <br>         
                                            {$ADS}<br>
                                        </div>
                                        <div class="perforationHorizontal"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style="vertical-align: top;width:193px;" id="div_menuInclude">
                        <!-- RIGHT CONTAINER -->
                        {if isset($menuLoad)} 
                            {include file="file:{$absPath}/{$menuLoad}" title="Menu"} 
                        {/if}
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <p class="footLinks">
                            {assign var='layoutCreator' value='Kayume & Amedot'}                      
                            {include file="file:{$absPath}/files/general_includes/html_footer.tpl" title="HTML footer"}
                        </p>
                    </td>
                </tr>
            </table>
        </center>
        <div class="modal"></div>
        
        <!-- Facebook Layer -->
        {include file="file:{$absPath}/files/general_includes/facebook.tpl" title="Facebook Header"}
        
        {if isset($dataLayer)}<!-- Set the data layer -->
            <script>{$dataLayer}</script>
        {/if}
        
        {literal}<!-- Google Tag Manager -->
        <noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-5TK3T9"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-5TK3T9');</script>
        <!-- End Google Tag Manager -->
        {/literal}
    </body>
</html>