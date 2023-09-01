<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml"> 
    {include file="file:{$absPath}/files/general_includes/html_head.tpl" title="HTML header"}
    
    <body class="lazy {if $_COOKIE['right-bar-open'] == true && !$hide_right_bar}menu-open-right{/if} {if $_COOKIE['left-bar-open'] == true && !$hide_left_bar}menu-open-left{/if}">

        <!--libs-->
        <script type="text/javascript" src="./files/layout_default/libs.js"></script>
        <!--key bindings settings js-->
        {include file='./js.tpl'}
        <!--layout js-->
        <script async type="text/javascript" src="./files/layout_default/layout.min.js"></script>

        <div id="page-wrapper-1">
        <div id="page-wrapper-2">

            <style>
                :root
                {
                    {if $userdata['layout_font'] != 'Default'}
                        --font: "{$userdata['layout_font']}";
                    {else if $userdata['theme'] == 'light'}
                        --font: 'Arial', cursive;
                    {else if $userdata['theme'] == 'core2'}
                        --font: 'Kalam', cursive;
                    {else if $userdata['theme'] == 'dark'}
                        --font: 'Nova Mono', monospace;
                    {else if $userdata['theme'] == 'default'}
                        --font: 'Mali', cursive;
                    {/if}

                    {if $userdata['theme'] == 'light'}
                        {foreach $userdata['layout_colors'] as $field => $color}
                            {str_replace('DASHDASH','--',$field)}: {$color};
                        {/foreach}
                    {else if $user_name == '' && $themeDir == 'light'}
                        --body-background:       rgb(241,224,186);
                        --accent-color:          rgb(255,249,230);
                        --accent-color-dim:      rgb(128,121,102);
                        --accent-color-dark:     rgb(38,36,31);
                        --accent-color-light:    rgb(242,236,218);
                        --accent-border-color:   rgb(166,157,133);
                        --background-light:      rgb(243,232,205);
                        --background-light-alt:  rgb(255,255,255);
                        --background-normal:     rgb(253,246,227);
                        --background-normal-alt: rgb(253,246,227);
                        --background-dark:       rgb(146,69,57);
                        --background-dark-alt:   rgb(146,69,57);
                    {/if}
                }
            </style>

            {if isset($popups) && count($popups) != 0}
                <script defer type="text/javascript" id="confirm_popups">
            
                  {foreach $popups as $popup }
                  
                    $.confirm({
                    
                      useBootstrap: false,
                      closeIcon:  true,
                      
                      title:      {if $popup['title'] != ''}
                                    "{$popup['title']}"
                                  {else}
                                    "Hey{if random_int(1,1000) == 1} listen{/if}!"
                                  {/if},
            
                      boxWidth:   {if $popup['boxWidth'] != ''}
                                    "{$popup['boxWidth']}"
                                  {else}
                                    "75%"
                                  {/if},
            
            
                      type:       {if $popup['color'] != ''}
                                    "{$popup['color']}"
                                  {else}
                                    "blue"
                                  {/if},
            
                      theme:      {if $popup['theme'] != ''}
                                    "{$popup['theme']}"
                                  {else}
                                    "light"
                                  {/if},
            
                      content:    {if $popup['text'] != '' &&  !is_array($popup['text'])}
                                    "{str_replace('"', '&quot;',preg_replace("/[\n\r]/","",$popup['text']))}"
                                  {else if $popup['text'] != '' && is_array($popup['text'])}
                                    "<a href='{$popup['text'][0]}'>{str_replace('"', '&quot;',preg_replace("/[\n\r]/","",$popup['text'][1]))}</a>"
                                  {else}
                                    "Empty."
                                  {/if},
            
                      buttons:    {if $popup['buttons'] != '' && $popup['buttons'] != 'none' && !is_array($popup['buttons'][0])}
                                    {
                                      "{$popup['buttons'][1]}": { btnClass: "btn-{if $popup['color'] != ''}{$popup['color']}{else}blue{/if}", action: function(){ location.href = "{$popup['buttons'][0]}"; } }
                                    }
                                  {else if $popup['buttons'] != '' && is_array($popup['buttons'][0])}
                                    {
                                      {foreach $popup['buttons'] as $button}
                                      
                                        "{$button[1]}": { btnClass: "btn-{if $popup['color'] != ''}{$popup['color']}{else}blue{/if}", action: function(){ location.href = "{$button[0]}"; } },
                                        
                                      {/foreach}
                                    }
                                  {else}
                                    {
                                      ok: function(){}
                                    }
                                  {/if}
                  });
            
                  {/foreach}
            
                </script>
            {/if}

            <div id="main-grid" class="{if $hide_left_bar && $hide_right_bar}neither-bars{elseif $hide_left_bar}no-left-bar{elseif $hide_right_bar}no-right-bar{else}both-bars{/if}">

                <!-- ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
                <!-- header header header header header header header header header header header header header header header header header header header header header header -->
                <div id="header-bar">
                    <div class="header-bar-link header-bar-extra"></div>

                    <div id="header-bar-title">
                        <a href="\{if isset($_SESSION['uid'])}?id=103{/if}" style="text-decoration:none;">The Ninja-Rpg</a>
                    </div>

                    <div class="header-bar-link header-bar-extra"></div>

                    <div class="header-bar-link header-bar-extra"></div>
                    <div class="header-bar-link first"><a href="?id=1">News</a></div>
                    <div class="header-bar-link"><a href="http://www.theninja-forum.com/index.php?app=nexus&amp;module=support">Contact</a></div>
                    <div class="header-bar-link"><a href="https://theninja-forum.com/">Forum</a></div>
                    <div class="header-bar-link"><a href="http://www.theninja-forum.com/index.php?/page/index.html">Manual</a></div>
                    <div class="header-bar-link"><a href="https://discord.gg/ZkB89wp">Discord</a></div>
                    <div class="header-bar-link"><a href="?id=15">Rules</a></div> 
                    <div class="header-bar-link"><a href="?id=7">ToS</a></div>
                    {if  $user_name != ''}
                        <div class="header-bar-link last"><a href="?id=42">Online</a></div>
                    {else}
                        <div class="header-bar-link fadded last" title="must be logged in">Online</div>
                    {/if}
                    <div class="header-bar-link header-bar-extra"></div>

                    {if  $user_name != '' && $userdata['layout_menu_location'] == 'top'}
                        {include file="file:./widgets/widget-top-menu.tpl" title="user-menu"}
                    {/if}

                </div>

                <!--<script>
                    window.onresize = displayWindowSize;
                    window.onload = displayWindowSize;
                
                    function displayWindowSize() {
                        myWidth = window.innerWidth;
                        myHeight = window.innerHeight;
                        document.getElementById("dimensions").innerHTML = "screen size: " + myWidth + "x" + myHeight;
                    };
                </script>-->

                <div style="position:fixed;bottom:0;left:1%;right:1%;text-align:center;text-shadow:0px 0px 1px white, 0px 0px 2px white, 0px 0px 3px white;" id="dimensions"></div>
                <!-- header header header header header header header header header header header header header header header header header header header header header header -->
                <!-- \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ -->





                <!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
                <!-- left left left left left left left left left left left left left left left left left left left left left left left left left left left left left left left -->
				{if !$hide_left_bar}
					<div id="left-bar" {if $_COOKIE['left-bar-open'] == true}class="active"{/if}>
					    <div id="left-bar-top-spacer"></div>

					    {if $user_name != ''}

							{foreach $left_widgets as $widget}
								{include file="file:./widgets/"|cat:$widget title="user-portrait"}
							{/foreach}
						{else}
					        {include file="file:./widgets/widget-login.tpl"}
					        {include file="file:./widgets/widget-register.tpl"}
					    {/if}

					    <div id="left-bar-bottom-spacer"></div>
					</div>
				{/if}
                <!-- left left left left left left left left left left left left left left left left left left left left left left left left left left left left left left left -->
                <!-- \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ -->





                <!-- ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
                <!-- center center center center center center center center center center center center center center center center center center center center center center -->
                <div id="center-bar" class="{if ($_COOKIE['right-bar-open'] == true && !$hide_right_bar) || ($_COOKIE['left-bar-open'] == true && !$hide_left_bar)}menu-open {/if}{if $hide_left_bar && $hide_right_bar}neither-bars{elseif $hide_left_bar}no-left-bar{elseif $hide_right_bar}no-right-bar{else}both-bars{/if}">

                    {include file="file:{$absPath}/files/general_includes/contentInclude.tpl" title="Content Inclusion"}
                    {if $themeDir != 'core2'}
					    {if (isset($_SESSION['uid']) && (($userdata['status']) != 'combat') || $_GET['id'] != 113) || $_GET['id'] == 72}
						    <div id="page-footer">
						        <div id="page-footer-return-button" class="page-footer-button" onclick="loadPage('{if isset($_SESSION['uid'])}{$_SESSION['previous_page_url'][count($_SESSION['previous_page_url'])-2]}{else}/{/if}','all','step_back:yes');">Return</div>
						    </div>
					    {/if}
                    {else}
                            <div id="page-footer">
                                {if (isset($_SESSION['uid']) && (($userdata['status']) != 'combat') || $_GET['id'] != 113) || $_GET['id'] == 72}
						            <div id="page-footer-return-button" class="page-footer-button" onclick="loadPage('{if isset($_SESSION['uid'])}{$_SESSION['previous_page_url'][count($_SESSION['previous_page_url'])-2]}{else}/{/if}','all','step_back:yes');">Return</div>
                                {else}
						            <div id="page-footer-return-button" class="page-footer-button" ></div>
                                {/if}
						    </div>
                    {/if}
                    <br>         

					<div class="ads-box">
						{$ADS}
					</div>
                    <br>

                </div>
                <!-- center center center center center center center center center center center center center center center center center center center center center center -->
                <!-- \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ -->





                <!-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
                <!-- right right right right right right right right right right right right right right right right right right right right right right right right right right -->
				{if !$hide_right_bar}
					<div id="right-bar" {if $_COOKIE['right-bar-open'] == true}class="active"{/if}>
					    <div id="right-bar-top-spacer"></div>

					    {if $user_name != ''}

					        {foreach $right_widgets as $widget}
								{include file="file:./widgets/"|cat:$widget title=$widget}
							{/foreach}

					    {else}

					        {include file="file:./widgets/widget-top-players.tpl" title="top-players"}
					        
					        {include file="file:./widgets/widget-screenshots.tpl" title="screenshots"}

					    {/if}

					    <div id="right-bar-bottom-spacer"></div>
					</div>
				{/if}
                <!-- right right right right right right right right right right right right right right right right right right right right right right right right right right -->
                <!-- \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ -->





                <!-- //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
                <!-- footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer -->
                <div id="footer-bar">
                    {assign var='layoutCreator' value='Koala'}                      
                    {include file="file:{$absPath}/files/general_includes/html_footer.tpl" title="HTML footer"}

                    <div>Layout Inspiration: Kayume & Amedot</div>
                    {if $themeDir == 'default' || $themeDir == ''}
                        <div title="Taverns, Portrait Frames, Homes, Background">Various Art: <a href="https://www.deviantart.com/kleinerei">Rei</a></div>
                        <div> Layout Art: Amedot & Koala<a></a> </div>
                    {else if $themeDir == 'dark'}
                        <div title="Taverns, Portrait Frames, Homes">Various Art: <a href="https://www.deviantart.com/kleinerei">Rei</a></div>
                        <div> Layout Art: <a href="https://www.artstation.com/artquest07">ArtQuest @ ArtStation</a> & Koala </div>
                    {else if $themeDir == 'core2'}
                        <div title="Taverns, Portrait Frames, Homes, Background">Various Art: <a href="https://www.deviantart.com/kleinerei">Rei</a></div>
                        <div>Layout Art: <a href="http://hamex.deviantart.com/art/Ninja-20023606">Hamex @ DeviantArt</a>, <a href="https://www.artstation.com/artquest07">ArtQuest @ ArtStation</a></div>
                    {else}
                        <div title="Taverns, Portrait Frames, Homes, Background">Various Art: <a href="https://www.deviantart.com/kleinerei">Rei</a></div>
                    {/if}


                    <div class="modal"></div>
                    
                    <!-- Facebook Layer -->
                    {include file="file:{$absPath}/files/general_includes/facebook.tpl" title="Facebook Header"}
                    
                    {if isset($dataLayer)}<!-- Set the data layer -->
                        <script defer>{$dataLayer}</script>
                    {/if}
                    
                    {literal}<!-- Google Tag Manager -->
                    <noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-5TK3T9"
                    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
                    <script defer>
                    function lazyGoogleTag() {
                    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                    '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                    })(window,document,'script','dataLayer','GTM-5TK3T9');
                    }
                    if (window.addEventListener)
                    window.addEventListener("load", lazyGoogleTag, false);
                    else if (window.attachEvent)
                    window.attachEvent("onload", lazyGoogleTag);
                    else window.onload = lazyGoogleTag;
                    </script>
                    <!-- End Google Tag Manager -->
                    {/literal}
                </div>
                <!-- footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer footer -->
                <!-- \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ -->

            </div>

			{if !$hide_left_bar}
				<div id="ui-button-top-left" class="ui-button {if $_COOKIE['left-bar-open'] == true} active {/if}">
                    {if in_array('widget-notifications.tpl',$left_widgets)}
                        <div id="widget-notifications-title-span-title-floating-counter-left">({count($notifications)})</div>
                    {/if}
					{if !isset($_SESSION['uid'])}
						<div id="ui-button-top-left-login">Login/Register</div>
					{/if}
				</div>
			{/if}

			{if !$hide_right_bar}
                {if in_array('widget-notifications.tpl',$right_widgets)}
                    <div id="widget-notifications-title-span-title-floating-counter-right">({count($notifications)})</div>
                {/if}
				<div id="ui-button-top-right" class="ui-button {if $_COOKIE['right-bar-open'] == true} active {/if}">
				</div>
			{/if}


            {if $user_name != ''}

				{if $userdata['layout_travel_mobile'] == 'on'}
					<!--this is the mobile travel button-->
					<div id="ui-button-bottom-left" class="ui-button {if $_COOKIE['widget-popup-travel-open'] == true} active {/if}"></div>
                    {include file="file:./widgets/widget-popup-travel.tpl" title="travel"}
				{/if}

                <!-- this is the mobile quick link menu -->
                {if $userdata['layout_quick_mobile'] == 'on'}
                    <div class="ui-button" id="ui-button-bottom-center"></div>
                    {include file="file:./widgets/widget-popup-quick-links.tpl" title="travel"}
                {/if}

				<!--this is the mobile menu button-->
				<div class="ui-button" id="ui-button-bottom-right"></div>
                {include file="file:./widgets/widget-mobile-menu.tpl" title="main-menu"}

            {/if}

            {if isset($GLOBALS['userdata'][0]['username']) && $GLOBALS['userdata'][0]['username'] == 'Koala'}
                {debug}
            {/if}

        </div>
        </div>
    </body>
</html>