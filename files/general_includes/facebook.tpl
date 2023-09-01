{if (!isset($fbData) || empty($fbData)) || (isset($loadFB_JS) && $loadFB_JS == true) } 
    {literal}<script defer type="text/javascript">
        function lazyFacebookLayer() {
          window.fbAsyncInit = function() {
             FB.init({
               appId      : '{/literal}{$fbAppId}{literal}',
               xfbml      : true,
               version    : 'v2.6',
               cookie     : true, // enable cookies to allow the server to access the session
               status     : true, // check login status
             });
           };
    
           (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
          }(document, 'script', 'facebook-jssdk'));
        }
        if (window.addEventListener)
        window.addEventListener("load", lazyFacebookLayer, false);
        else if (window.attachEvent)
        window.attachEvent("onload", lazyFacebookLayer);
        else window.onload = lazyFacebookLayer;
        function checkLogin() {
            console.log('Checking Login!');
            FB.getLoginStatus(function(response) {
                if (response.status === 'connected') { 
                    console.log("User is logged in, redirecting to login"); 
                    window.location = "{/literal}{$domain}{literal}?id=63&act=facebookLogin"; } 
                else { 
                    FB.login(); 
                } 
            });
        } 
    </script>
    {/literal}
{/if}