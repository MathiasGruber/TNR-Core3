{if isset($data)}
    {$previous_user = 0}
    {foreach array_reverse($data) as $entry}
        <div class="tavern-message-box lazy" id="{$entry['tid']}">
            {if $previous_user != $entry.uid}
                <div class="tavern-message-info-wrapper"> 
                    <div class="tavern-message-info">
                        <div class="tavern-message-info-cell">
                            {if !$mod_panel}
		            	    	<a href="?id=13&amp;page=profile&amp;name={$entry.user}" class="tavern-message-name"
                                   {if strlen($entry.user) > 15}style="letter-spacing:0 !important;"{/if}>
                                    {$entry.color_user}
                                </a>
		            	    {else}
		            	    	<a href="../?id=13&amp;page=profile&amp;name={$entry.user}" class="tavern-message-name"
                                   {if strlen($entry.user) > 15}style="letter-spacing:0 !important;"{/if}>
                                    {$entry.color_user}
                                </a>
		            	    {/if}
                        </div>

                        {if !$mod_panel}
                            <div class="tavern-message-info-cell tavern-message-pm">
		            		    <a href="?id=3&amp;act=newMessage&amp;toUser={$entry.user}"><img src="./images/email.png" alt="PM" style="border:none;"></a>
                            </div>
		            	{else}
                            <div class="tavern-message-info-cell tavern-message-pm">
		            		    <a href="../?id=3&amp;act=newMessage&amp;toUser={$entry.user}"><img src="./images/email.png" alt="PM" style="border:none;"></a>
                            </div>
		            	{/if}

                        <div class="tavern-message-info-cell tavern-message-rank">
                           &nbsp;{$entry.rank}
                        </div>

                        <div class="tavern-message-info-cell  tavern-message-time text-right" title="{$entry.time|date_format:"%A, %B %e, %Y"}">
                            &nbsp;
                            {$entry.time|date_format:"%l:%M %p"}
                        </div>

                    </div>
                    <div class="tavern-message-info-texture"></div>
                </div>
            {/if}

            <div class="stiff-grid stiff-column-min-right-2 page-grid-justify-stretch">
                <div class="tavern-message">
                    {$entry.message}
                </div>
                <div class="tavern-message-options">
                    <div class="tavern-message-info-cell">
                        {if !$mod_panel}
		        	        <a href="?id=53&amp;act=tavern&amp;mt={$entry.time}&amp;uid={$entry.uid}&tavern={$tavernTable}"><img src="./images/report.gif" alt="Rpt" style="border:none;"></a>
		        	    {else}
		    		        <a href="../?id=53&amp;act=tavern&amp;mt={$entry.time}&amp;uid={$entry.uid}&tavern={$tavernTable}"><img src="./images/report.gif" alt="Rpt" style="border:none;"></a>
		        	    {/if}

                        {if isset($isAdmin) && $isAdmin == true}
                            <form style="display:inline;" action="" method="post" class="deletePost">
                                <input type="hidden" name="identifier" value="{$entry.time}:{$entry.uid}">
                                <input type="image" src="./images/trash.gif" alt="Dlt" />
                            </form>
                        {/if}
                    </div>

                </div>
            </div>
        </div>
        {$previous_user = $entry.uid}
    {/foreach}
{else}
    <div>
        No Messages
    </div>
{/if}