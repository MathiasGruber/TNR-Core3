<form id="login_form" name="login_form" method="post" action="">
    <div class="menuContainer">
        <div class="menuBg1 menuBg1-right">
            <div class="menuPerforationVertical menuPerforationVerticalRight">
                <div class="perforationMenuHorizontal perforationMenuHorizontalTop"></div>		 
                <div class="menuBg2 menuBg2-left">
                    <div class="wrapper_outer">
                        <div class="wrapper_inner">
                            <table width="100%" style="padding:0px;border:0px solid #580000;margin-bottom: 5px;border-spacing:0px;border-collapse: collapse;" >
                                <tr>
                                    <td width="30%">Username:</td>
                                    <td>{$USERNAME}</td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom:0px;padding-top:0px;">Password:</td>
                                    <td style="padding-bottom:0px;padding-top:0px;">{$PASSWORD}</td>
                                </tr>
                                <tr>
                                    <td >&nbsp;</td>
                                    <td style="text-align:left">{$SUBMIT}</td>
                                </tr>
                                <tr>
                                    <td colspan="2">

                                        {literal}
                                    <fb:login-button size="medium"  scope="email" onlogin="checkLogin();" width="150px">
                                        Login with Facebook
                                    </fb:login-button>

                                {/literal}
                                </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div> 
                <div class="perforationMenuHorizontal perforationMenuHorizontalBottom"></div>
            </div>
        </div>
        <div class="ribbonStats ribbonStats-left">
            <div class="ribbonText" style="text-align:center">Log-in &nbsp;</div>
        </div>
    </div>
</form>

<div class="menuContainer">
        <div class="menuBg1 menuBg1-right">
            <div class="menuPerforationVertical menuPerforationVerticalRight">
                <div class="perforationMenuHorizontal perforationMenuHorizontalTop"></div>		 
                <div class="menuBg2 menuBg2-left">
                    <div class="wrapper_outer">
                        <div class="wrapper_inner">
                        <table width="100%" style="padding:0px;border:0px solid #580000;border-spacing:0px;border-collapse: collapse;" >
                            <tr>
                                <td>
                                    <a style="font-size:20px;" href="?id=63">Register an Account!</a><br>
                                    <a href="?id=63&amp;act=forgot">Forgot your password?</a><br>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>    
            <div class="perforationMenuHorizontal perforationMenuHorizontalBottom"></div>
        </div>
    </div>
    <div class="ribbonStats ribbonStats-left">
            <div class="ribbonText" style="text-align:center">Join now &nbsp;</div>
        </div>
</div>
