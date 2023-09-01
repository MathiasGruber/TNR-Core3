<form id="form1" name="form1" method="post" action="">
    <table class="table" width="95%">
        <tr><td colspan="4" class="subHeader">Item admin</td></tr>
        <tr>
            <td>Item ID:</td>
            <td><input name="itemID" type="text" class="textfield" id="itemID" /></td>
            <td></td>
            <td>
            </td>
        </tr>
        <tr>
            <td width="22%">Name:</td>
            <td width="30%"><input name="name" type="text" class="textfield" id="name" /></td>
            <td width="11%">In shop: </td>
            <td width="37%">
                <select name="in_shop" class="listbox" id="in_shop">
                    <option>yes</option>
                    <option>no</option>
                    <option selected="selected">any</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Type:</td>
            <td>
                <select name="item_type" class="listbox" id="item_type">
                    <option>item</option>
                    <option>artifact</option>
                    <option>armor</option>
                    <option>weapon</option>
                    <option>special</option>
                    <option>process</option>
                    <option>material</option>
                    <option>tool</option>
                    <option>consumable</option>
                    <option>reduction</option>
                    <option>repair</option>
                    <option selected="selected">any</option>
                </select>
            </td>
            <td>Rank: </td>
            <td>
                <select name="rank_type" class="listbox" id="rank_type">
                    <option>&lt;</option>
                    <option>&gt;</option>
                    <option>&lt;=</option>
                    <option selected="selected">&gt;=</option>
                    <option>=</option>
                </select>
                <select name="rank_id" class="listbox" id="rank_id">
                    <option value="1" selected="selected">Academy Student</option>
                    <option value="2">Genin</option>
                    <option value="3">Chuunin</option>
                    <option value="4">Jounin</option>
                    <option value="5">Elite Jounin</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Armor type: </td>
            <td>
                <select name="armor_type" class="listbox" id="armor_type">
                    <option>armor</option>
                    <option>helmet</option>
                    <option>gloves</option>
                    <option>belt</option>
                    <option>shoes</option>
                    <option>pants</option>
                    <option selected="selected">any</option>
                </select>
            </td>
            <td>Price</td>
            <td>
                <select name="price_type" class="listbox" id="price_type">
                    <option>&lt;</option>
                    <option>&gt;</option>
                    <option>&lt;=</option>
                    <option>&gt;=</option>
                    <option>=</option>
                </select>
                <input name="price_int" type="text" class="textfield" id="price_int" size="15" />
            </td>
        </tr>
        <tr>        
            <td colspan="4" >
                <input name="Submit" type="submit" class="button" value="Submit" />
            </td>
        </tr>
    </table>
</form>