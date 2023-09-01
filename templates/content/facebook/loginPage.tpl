<div align="center"><table border="0" cellspacing="0" class="table" width="95%">
        <tr>
            <td class="subHeader">Facebook Integration Panel</td>
        </tr>
        <tr>
            <td>Connecting your profile with facebook will get you several interesting benefits in the game</td>
        </tr>
        <tr>
            <td>
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
        </td>
        </tr>
    </table>
</div>

