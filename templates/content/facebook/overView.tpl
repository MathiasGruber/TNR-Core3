{literal}
    <div id="fb-root"></div>
    <script>
      // Additional JS functions here

      function fbCallback(response) {
        console.log(response);
        $.post("./ajaxLibs/staticLib/fbConnect.php", { 'username': {/literal}"{$username}"{literal},  'data[]': response["to"] });
      }    
          
      function sendInvites() {
        FB.ui({method: 'apprequests',
            title: 'Play TheNinja-RPG with me!',
            message: 'Come play this awesome online ninja game with me',
        }, fbCallback);
      }             
      
     
      // Load the SDK
      (function(d){
         var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
         if (d.getElementById(id)) {return;}
         js = d.createElement('script'); js.id = id; js.async = true;
         js.src = "//connect.facebook.net/en_US/all.js";
         ref.parentNode.insertBefore(js, ref);
       }(document));
    </script>

{/literal}

<div align="center"><table border="0" cellspacing="0" class="table" width="95%">
        <tr>
            <td colspan="3" class="subHeader">Facebook Integration Panel</td>
        </tr>
        <tr>
            <td width="33%" style="padding-bottom:0px;"><a onclick="sendInvites()">
                <img src="./images/icons/inviteUser.png" width="110" alt="Invite User"></img><br>
                <b><h1>Invite</h1></b>
            </a></td>
            <td width="33%" style="padding-bottom:0px;"><a href="?id=82&act=seeFriends">
                <img src="./images/icons/otherUser.png" width="110" alt="Friends"></img><br>
                <b><h1>Friends</h1></b>
            </a></td>
            <td style="padding-bottom:0px;"><a href="?id=82&act=overView">
                <img src="./images/icons/userNetwork.png" width="110" alt="User Network"></img><br>
                <b><h1>Overview</h1></b>
            </a></td>
        </tr>
        <tr>
            <td valign="top" style="padding-top:0px;" width="33%">2 popularity points for each friend who joins TheNinja-RPG (and connects with facebook).</td>
            <td valign="top" style="padding-top:0px;" width="33%">Click here to see the list of facebook friends who are playing this game.</td>
            <td valign="top" style="padding-top:0px;" >See the list of facebook friends invited to play TNR, and which ones have signed up so far.</td>
        </tr>
        
    </table>
</div>                                                       

<br>
    
