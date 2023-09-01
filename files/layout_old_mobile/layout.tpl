<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 

<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml"> 
    
    {assign var="mobileLayout" value="Yep"}
    {include file="file:{$absPath}/files/general_includes/html_head.tpl" title="HTML header"}

    <body>
        
        <!-- Facebook Layer -->
        {include file="file:{$absPath}/files/general_includes/facebook.tpl" title="Facebook Header"}
    
        {if isset($dataLayer) }
            <!-- Set the data layer -->
            <script>
                {$dataLayer}
            </script>
        {/if}
        
        {literal}
        <!-- Google Tag Manager -->
        <noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-5TK3T9"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-5TK3T9');</script>
        <!-- End Google Tag Manager -->
        {/literal}
        
        <div id="fb-root"></div> 
        
        <div data-role="page">
            
            <!-- Main menu -->
            <div data-role="panel" id="mainMenuPanel" data-position="left" data-display="overlay">
                <ul data-role="listview">
                    <li><a href="?id=1">News</a></li>
                    <li><a href="{$forumLink}index.php?app=nexus&module=support">Contact</a></li>
                    <li><a href="{$forumLink}">Forum</a></li>
                    <li><a href="{$manualLink}">Manual</a></li>
                    <li><a href="?id=76">Event History</a></li>
                    <li><a href="?id=75">About TNR</a></li>
                    <li><a href="?id=15">Rules</a></li>
                    <li><a href="?id=7">ToS</a></li>
                    <li><a href="?id=42">Online</a></li>
                </ul>
            </div>
               
            <!-- Login menu -->    
            {if  !isset($smarty.session.uid)}
                {if isset($USERNAME)}
                    {if !isset($smarty.post.lgn_usr_stpd)}
                        <div data-ajax="false" data-role="panel" id="loginPanel" data-position="right" data-display="overlay">
                            <form id="login_form" name="login_form" method="post" action="">
                            <label for="basic">Username:</label>
                            {$USERNAME}
                            <label for="basic">Password:</label>
                            {$PASSWORD}
                            {$SUBMIT}
                            {literal}
                            <fb:login-button size="large"  scope="email" onlogin="checkLogin();" width="150px">
                                Login with Facebook
                            </fb:login-button>
                            {/literal}
                            </form>
                        </div>
                    {else}
                        To login, please solve the shown CAPTCHA. 
                        This code prevents automated scripts (so-called 'bots') from logging in and playing the game. 
                        The reCAPTCHA service is produced and provided by Google. For more information visit 
                        <a href='http://www.google.com/recaptcha'>reCAPTCHA website</a> 
                    {/if}
                {/if}
            {/if}
                    
            <!-- Game Menu -->
            {if isset($smarty.session.uid)}
                <div id="div_menuInclude">
                    {include file="file:{$absPath}/files/general_includes/menuInclude.tpl" title="Menu Inclusion"}
                </div>
            {else}
                <!-- Top title image -->
                <a href="/"><img class="mobileTitleImage" src="/files/layout_mobile/images/banner.png" alt="The Ninja-RPG" /></a>
            {/if}
            
            <div data-role="header" class="tnrMobileHeader" >
                <a href="#mainMenuPanel" class="ui-btn-left" data-rel="popup" data-role="button" data-inline="true" data-transition="fade">Main Menu</a>
                {if isset($smarty.session.uid)}
                    <a href="#gameMenuPanel" class="ui-btn-right" data-rel="popup" data-role="button" data-inline="true" data-transition="fade">Game Menu</a>
                {elseif isset($USERNAME)}
                    <a href="#loginPanel" class="ui-btn-right" data-rel="popup" data-role="button" data-inline="true" data-transition="fade">Login</a>
                {/if}
                <h1>TheNinja-RPG</h1>
            </div>
            <div data-role="content" align="center" style="padding:0px;overflow:auto;" id="mobileContentContainer">
                <div class="leftDiagonal" style="min-width:540px;">
                    <div class="perforationVertical1">
                        <div class="perforationVertical2">			 
                            <div class="perforationHorizontal"></div>		 
                            <div class="leftDiagonal2" id="div_contentInclude" data-enhanced="false">
                                {include file="file:{$absPath}/files/general_includes/contentInclude.tpl" title="Content Inclusion"}   
                                <br>         
                                {$ADS}

                            </div>
                            <div class="perforationHorizontal"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div data-role="footer" class="tnrMobileHeader">
                <a href="?forceLayout=light" data-icon="grid" class="ui-btn-right" data-inline="true" data-role="button" style="margin: 1px;">Light</a>
                <h3>© Studie-Tech ApS 2005-{$YEAR}</h3>            
            </div>
            {if isset($smarty.session.uid)}
                <span id="footerUserID" name="{$smarty.session.uid}"></span> 
            {/if}
        </div>
    </body>
</html>