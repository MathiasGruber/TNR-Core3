<form id="form1" name="form1" method="post" action="">
    <div align="center" id="showTable">  
        <table width="95%" class="table">
            <tr>
                <td class="subHeader">Page Load Times</td></tr><tr>
                <td width="100%" valign="top">
                    <div>
                        {if isset($calcDebug)}
                            {foreach $calcDebug as $statement}
                               &nbsp; ~ {$statement}<br>
                            {/foreach}
                        {/if}
                    </div>                    
                </td>                
            </tr>
        </table>
    </div>
    <div align="center" id="showTable">  
        <table width="95%" class="table">
            <tr>
                <td class="subHeader">Settings for Graph</td></tr><tr>
                <td width="100%" valign="top">
                    <div>
                        <form id="settingsForm" class="autoInput" action="" method="POST" enctype="multipart/form-data" >
                            Days to Show: 
                            <select name="days">
                                <option value="6">6 Hours</option>
                                <option value="12">12 Hours</option>
                                <option value="24">1 Day</option>
                                <option value="48">2 Days</option>
                                <option value="96">3 Days</option>
                            </select>
                            
                            Cluster Interval: 
                            <select name="cluster">
                                <option value="60">1 min</option>
                                <option value="300">5 min</option>
                                <option value="600">10 min</option>
                            </select>
                            <input class="input_submit_btn" style="line-height:15px;margin:10px;" type="submit" name="submit" value="submit">
                        </form>
                    </div>                    
                </td>                
            </tr>
        </table>        
    </div>
    <div>
        {if isset($lagIncidents)}
            {$subSelect="lagIncidents"}
            {include file="file:{$absPath}/{$lagIncidents}" title="Lag Incidents"}
        {/if}
    </div>
</form>