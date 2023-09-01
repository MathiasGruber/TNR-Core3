<div align="center"><table border="0" cellspacing="0" class="table" width="95%">
        <tr>
            <td class="subHeader">Facebook Error</td>
        </tr>
        <tr>
            <td>The facebook account <b>{$username}</b> you're logged into is not the one connected to this user. <br>
               Only one facebook account can be connected to each user.
               Please log out and log in with the proper facebook account.</td>
        </tr>
        <tr>
            <td>
                {literal}
                    <script>
                      function connectCheck() {
                        console.log('Passing to login:');
                        FB.login();
                        FB.Event.subscribe('auth.authResponseChange', function(response) {
                            console.log('Event reponse: '+response.status);
                            if (response.status === 'connected') {
                              window.location = "{/literal}{$domain}{literal}?id=82";                    
                            } else if (response.status === 'not_authorized') {
                              FB.login();
                            } else {
                              FB.login();
                            }
                          });
                      };                       
                      
                    </script>
                {/literal}
                <fb:login-button size="xlarge" autologoutlink="true" scope="publish_stream, email" onlogin="connectCheck();">
                  Connect with Facebook
                </fb:login-button>  
        </td>
        </tr>
    </table>
</div>

