<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">    
 
<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml"> 
{include file="file:{$absPath}/files/general_includes/html_head.tpl" title="HTML header"}
    
<body bgcolor="#00091a"> 
    
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
    <center id="mainContent"> 
        <div align="center" style="width: 900px;">  

        <div style="float: right;width:250px;padding-top:75px;"> 
            <div class="topHeader"></div>
            <div class="scrollBG" style="width: 100%;"> 
                <div style="width: 179px;" id="div_menuInclude"> 
                    {include file="file:{$absPath}/files/general_includes/menuInclude.tpl" title="Menu Inclusion"}   
                </div>
            </div>
            <div class="scrollBottom"></div>        
        </div> 

        <div style="float: left;width:650px;">
            <div style="height:195px;">
                <img src="./files/layout_default/images/title.png" border="0" usemap="#Map" alt="Title Image"></img>
            </div>
            <div class="headTitle">
                <span class="wlinks">
                     <a href="?id=42">Online Users</a>
                </span> 
            </div>
            <div class="contentDiv">
                <div style="width:580px;padding-top:10px;">
                 {include file="file:{$absPath}/files/general_includes/contentInclude.tpl" title="Content Inclusion"}   
                 <br>  
                 {$ADS}
                 </div>
            </div>
            <div class="footerTitle"></div>
            <p class="footLinks">
                {assign var='layoutCreator' value='Kayume'}                      
                {include file="file:{$absPath}/files/general_includes/html_footer.tpl" title="HTML footer"}
                 Layout Art by: <a style="color: #ACACAC;" href="http://hamex.deviantart.com/art/Ninja-20023606">Hamex @ DeviantArt</a> 
            </p>
        </div>

        </div>
        <map name="Map" id="Map"> 
          <area shape="rect" alt="News" coords="153,180,190,193" href="?id=1" /> 
          <area shape="rect" alt="Contact" coords="198,180,257,193" href="http://www.theninja-forum.com/index.php?app=nexus&module=support" /> 
          <area shape="rect" alt="Art" coords="263,179,347,193" href="http://www.theninja-forum.com/album.php?albumid=235" /> 
          <area shape="rect" alt="Forum" coords="354,179,399,193" href="{$forumLink}" /> 
          <area shape="rect" alt="Manual" coords="406,179,460,193" href="{$manualLink}" /> 
          <area shape="rect" alt="Rules" coords="466,179,505,193" href="?id=15" /> 
          <area shape="rect" alt="ToS" coords="512,179,625,193" href="?id=7" /> 
          <area shape="rect" alt="Home" coords="180,115,630,180" href="/" /> 
        </map>
    </center> 
</body> 
</html> 