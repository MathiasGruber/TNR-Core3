<div align="center">
    <form name="form1" method="post" action="">
    <table width="95%" class="table">
        <tr>
            <td class="subHeader" >Admin Ban System</td>
        </tr>        
        <tr>
            <td>
                <textarea rows="4" cols="50" name="message" >{$value}</textarea><br>
                <input type="submit" name="Submit" value="Submit" class="button">
            </td>
        </tr>
    </table></form>
    <a href="?id={$smarty.get.id}&act=mod&uid={$smarty.get.uid}&type=ban">Return</a>
</div>