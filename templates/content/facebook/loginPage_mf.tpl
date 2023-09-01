<div class="page-box">
    <div class="page-title">
        Facebook Integration Panel
    </div>
    <div class="page-content">
        <div class="page-sub-title-top">
            Connecting your profile with facebook will get you several interesting benefits in the game
        </div>
        <div>
            {literal}
                <script>
                  function connectCheck() {
                    console.log('Checking Login!');
                    FB.getLoginStatus(function(response) {
                      if (response.status === 'connected') {
                        console.log("User is logged in, redirecting to login")  
                        window.location = "{/literal}{$domain}{literal}?id=82";                    
                      } else if (response.status === 'not_authorized') {
                        FB.login()
                      } else {
                        FB.login()
                      }
                     });                       
                  }
                </script>
            {/literal}
            <fb:login-button size="xlarge"  scope="publish_stream, email" onlogin="connectCheck();">
              Connect with Facebook
            </fb:login-button>
        </div>
    </div>
</div>

