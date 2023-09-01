<div>
    <form id="form1" name="form1" method="post" action="?id={$smarty.get.id}&act=search">
    <table class="table" width="95%">
        <tr>
            <td colspan="2" class="subHeader">Search Entries</td>
        </tr>
        <tr>
            <td width="50%" style="padding-right:30px;text-align:right;">
                Name:<input name="name" type="text" class="textfield" />
            </td>
            <td width="50%" style="padding-left:0px;text-align:left;">
                Type:
                <select name="type" class="listbox">
                    <option selected>any</option>
                    <option>task</option>
                    <option>quest</option>
                    <option>achievement</option>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding-top:2px;padding-bottom:2px;"><br>
                <input name="Submit" type="submit" class="button" value="Submit" />
            </td>
        </tr>
    </table>
    </form>
</div>