<div id="upperChat">
    {if isset($allowPost) && $allowPost == "yes"}
        {include file="./postBox.tpl" title="Post Box"}
    {elseif isset($allowPost) }  
        {assign "subHeader" "System Message"}
        {assign "msg" {$allowPost}}
        {assign "returnLink" ""}
        {assign "returnLabel" ""}
        {include file="../../message.tpl" title="System Messages"}   
    {/if}
</div>

<div id="lowerChat" align="center">
    <table width="95%" class="table">
        <tr>
            <td colspan="2" class="subHeader">{$welcomeMessage}</td>
        </tr>
        {if isset($subMessage)}
            <tr>
                <td colspan="2" class="tableColumns tdBorder">
                    {$subMessage}
                </td>
            </tr>
        {/if}
        <tr>
            <td colspan="2" align="center" style="border-top:1px solid #000000;padding-left:10px;padding-right:20px;">
                <form style="display:inline;" action="" method="post" id="prevPosts">
                    <input type="hidden" name="min" value="{$mins[0]}">
                    <input class="input_submit_btn" style="width:150px; line-height:20px;" data-inline="true" type="submit" value="&laquo; Newer" />
                </form>
                <form style="display:inline;" action="" method="post" id="refresh">
                    <input type="hidden" name="min" value="0">
                    <input id="quickRefreshButton" class="input_submit_btn" style="width:150px; line-height:20px;" data-inline="true" type="submit" value="Quick Refresh" />
                </form>
                <form style="display:inline;" action="" method="post" id="nextPosts">
                    <input type="hidden" name="min" value="{$mins[2]}">
                    <input class="input_submit_btn" style="width:150px; line-height:20px;" data-inline="true" type="submit" value="Older &raquo;" />
                </form>
            </td>
        </tr>   
        <tbody id="tavernMessage">
            {include file="./messages.tpl" title="Tavern Messages"}   
        </tbody>
    </table>
</div>