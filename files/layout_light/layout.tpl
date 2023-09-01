<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 

<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml"> 
    {include file="file:{$absPath}/files/general_includes/html_head.tpl" title="HTML header"}

    <body>
        
        <!-- Facebook Layer -->
        {include file="file:{$absPath}/files/general_includes/facebook.tpl" title="Facebook Header"}

        <script type="text/javascript">
            var disabled_flag = false;

            $(document).ready(function() {
                readyTravelWidget();
            });

            function readyTravelWidget()
            {
                travelOn();
            }

            function travelOff()
            {
                $('#N').off('click');
                $('#NW').off('click');
                $('#NE').off('click');
                $('#ENTER').off('click');
                $('#W').off('click');
                $('#E').off('click');
                $('#S').off('click');
                $('#SW').off('click');
                $('#SE').off('click');
            }

            function travelOn()
            {
                travelOff();
                if(!disabled_flag)
                {
                    $('#N').on('click', function(){   doTravel("N");   });
                    $('#NW').on('click', function(){   doTravel("NW");   });
                    $('#NE').on('click', function(){   doTravel("NE");   });
                    $('#ENTER').on('click', function(){   doTravel("ENTER");   });
                    $('#W').on('click', function(){   doTravel("W");   });
                    $('#E').on('click', function(){   doTravel("E");   });
                    $('#S').on('click', function(){   doTravel("S");   });
                    $('#SW').on('click', function(){   doTravel("SW");   });
                    $('#SE').on('click', function(){   doTravel("SE");   });
                }
            }

            function doTravel( instruction )
            {
                $("#mainContent").first().load( (window.location.href.replace(/&act=wake|&act=sleep|&act=gather|&act=equip|&act=delete|&process=split|&process=merge|&process=take_out|&forget=\d*|&start=\d*|&quit=\d*|&track=\d*|&invID=\d*|&turn_in=\d*|&dialog_option=[^&]*/ig, '')) + " #mainContent", { doTravel: instruction }, function(response) {       readyMenu(); readyTravelWidget(); if($(response).find('#confirm_popups').length) eval($(response).find('#confirm_popups')[0]       .innerHTML); });
                disabled_flag = true;
                travelOff();
                setTimeout(function(){
                    disabled_flag = false;
                    travelOn();
                },250);
            }

        </script>
    
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
        <div align="center" id="mainContent">
            <table width="100%" class="main_table" style="width:100%;" border="0" cellspacing="0" cellpadding="0">
                
                <tr>
                    <td valign="top" style="padding:10px;text-align:center;border-bottom: 3px solid #000000;" align="center">
                        {if  !isset($smarty.session.uid)}
                            {if isset($USERNAME)}
                                {if !isset($smarty.post.lgn_usr_stpd)}
                                    <div align="center">
                                        <form id="login_form" name="login_form" method="post" action="">
                                            <table style="padding:0px;border:0px solid #580000;margin-bottom: 5px;border-spacing:0px;border-collapse: collapse;" >
                                                <tr>
                                                    <td style="font-size:200%;">Username:</td>
                                                    <td style="font-size:200%;">Password:</td>
                                                    <td rowspan="2" style="text-align:left;vertical-align:middle;">{$SUBMIT}</td>
                                                </tr>
                                                <tr>
                                                    <td>{$USERNAME}</td>
                                                    <td>{$PASSWORD}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" style="text-align:center">
                                                        {literal}
                                                        <fb:login-button size="xlarge"  scope="email" onlogin="checkLogin();" width="150px">
                                                            Login with Facebook
                                                        </fb:login-button>
                                                        {/literal}
                                                    </td>
                                                </tr>
                                            </table>
                                        </form>
                                    </div>
                                {else}
                                    To login, please solve the shown CAPTCHA. 
                                    This code prevents automated scripts (so-called 'bots') from logging in and playing the game. 
                                    The reCAPTCHA service is produced and provided by Google. For more information visit 
                                    <a href='http://www.google.com/recaptcha'>reCAPTCHA website</a> 
                                {/if}
                            {/if}
                        {else}
                            {include file="file:{$absPath}/files/general_includes/menuInclude.tpl" title="Menu Inclusion"}
                        {/if}
                              
                    </td>
                </tr>
                <tr>
                    <td valign="top" align="center" style="width:100%;">
                        <div align="center" style="padding-top:20px;">
                            {include file="file:{$absPath}/files/general_includes/contentInclude.tpl" title="Content Inclusion"} 
                            <br>  
                            {$ADS}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td valign="top" align="center" style="padding-top:5px;width:100%;border-top: 3px solid #000000;">
                        <p class="footLinks" style="font-size:50%;">
                            {assign var='layoutCreator' value='Kayume'}                      
                            {include file="file:{$absPath}/files/general_includes/html_footer.tpl" title="HTML footer"}        
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>