<div class="page-box">
    <div class="page-title">
        Territory Challenges
    </div>
    <div class="page-content">
        <div>
            {$information}
        </div>

        {if isset($terrStatusMessage)}
            {$subSelect="terrStatusMessage"}
            {include file="file:{$absPath}/{$terrStatusMessage}" title="Admin Notes"}
        {/if}
        {if isset($showLogs)}
            <script type="text/javascript">
                {include file="../combat/Scripts/battleScripts.js"}
            </script>
            <table width="95%" class="table">
                <tr>
                    <td colspan="3" class="subHeader">Territory Battle Logs</td>
                </tr>
                <tr>
                    <td colspan="3">
                        Each territory battle is split into three parts; Chunins, Jounins and Elite Jounins.
                    </td>
                </tr>
                <tr>
                    <td width="33%" class="tableColumns" style="border-top:1px solid black;">
                        <a href="?id={$smarty.get.id}&log=chuuninWinner">Chuunin</a>
                    </td>
                    <td width="33%" class="tableColumns" style="border-top:1px solid black;">
                        <a href="?id={$smarty.get.id}&log=jouninWinner">Jounin</a>
                    </td>
                    <td width="33%" class="tableColumns" style="border-top:1px solid black;">
                        <a href="?id={$smarty.get.id}&log=specialjouninWinner">Elite Jounin</a>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div align="center" style="padding-top:10px;">
                        {if isset($logInclude)}
                            {include file="file:{$absPath}/{$logInclude}" title="{$logInclude}"}
                        {elseif isset($logText)}
                            {$logText}
                        {/if}
                        </div>
                    </td>
                </tr>
            </table>
        {/if}
    </div>
</div>