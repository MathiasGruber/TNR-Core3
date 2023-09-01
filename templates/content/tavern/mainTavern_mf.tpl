<div class="page-box">
    <div class="page-title">
        {$welcomeMessage}
    </div>
    <div class="page-content tavern-page-content">

        {if isset($subMessage)}
            <div class="page-sub-title-no-margin">
                {$subMessage}
            </div>
        {/if}

        <div class="tavern-messages-box-wrapper">

            <div class="tavern-messages-box-art {$userdata['village']}"></div>
            <div class="tavern-messages-box-texture"></div>

            <div class="tavern-messages-box">

                <div class="tavern-top-box scroll-to-bottom">
                    {include file="./messages_mf.tpl" title="Tavern Messages"}   
                </div>

                <div class="tavern-bottom-editor">
                    {if isset($allowPost) && $allowPost == "yes"}
                        {include file="./postBox_mf.tpl" title="Post Box"}
                    {elseif isset($allowPost) }  
                        {assign "subHeader" "System Message"}
                        {assign "msg" {$allowPost}}
                        {assign "returnLink" ""}
                        {assign "returnLabel" ""}
                        {include file="../../message_mf.tpl" title="System Messages"}   
                    {/if}
                </div>
            </div>
        </div>

    </div>
</div>

