<div>
    <table class="table" width="95%">
        <tr>
            <td class="subHeader">Instructions</td>
        </tr>
        <tr>
            <td style="padding:20px;text-align:left;">
                
<ul>
    <h1>Common Fields</h1>
    Several travel events can be specified in this panel, which are all described here. All different events share the following columns:
    <br><br>
    <li>
        <b>Area</b><br>
        Specified the region where the event occurs. The tag format is: REGION(void), TERRITORY(territoryName), AREA(xmin.xmax.ymin.ymax)
    </li>
    <br>
    <li>
        <b>End Time</b><br>
        The event will automatically be deleted after this time (please specify in hours!)
    </li>
    <br>
    <li>
        <b>Chance</b><br>
        The chance that the event will trigger upon moving into the field
    </li>
    <br>
    <li>
        <b>Redoable</b><br>
        Sets whether the event can be fired more than once. <b>Note:</b> this requires that logging is enabled. Please also use only when neccesary, 
        since it introduces an additional query.
    </li>
    <br>
    <li>
        <b>Name</b><br>
        Just for internal use, not seen by the users
    </li>
    
    <br>
    <h1>Data Field</h1>
    The specific format of the <b><i>data-field</i></b> depends on the type of event being defined. The data
    field consists of a series of tags, that defines its behaviour. Certain tags are common for all event types, 
    and some are specific for given event types. Multiple tags for each field can be defined by concaterating 
    them with a semicolon. 
    
    <br><br>
    
    <li>
        <b>Common Tags</b><br>
        <i>These tags can be used on all event types: </i>
        <br><br>
        <ul>
            <li>
                "JUT:jutid;" ~ The field is only activated if the user has specific jutsu.
            </li>
            <li>
                "ITM:itmid;" ~ The field is only activated if the user has specific item.
            </li>
            <li>
                "user:rank_id:X;" ~ The field is only activated if the user's rank ID is above X
            </li>
        </ul>
    </li>
    
    <br><br>
    
    <li>
        <b>Special Tags</b><br>
        <i>Here are the specifics and deviations for the different event types: </i>
        <br><br>
        
        <li>
            Event type: <b>block</b><br>
            <i>This event type blocks a user from entering a given square. Data field format: </i>
            <br><br>
            <ul>
                <li>
                    Jutsu & item requirements are here for "inactivating" the block, i.e. allowing the user to enter
                </li>
                <li>
                    If you want to block a square/area for everyone, please use: user:rank_id:6. This is the fastest way!
                </li>
            </ul>
        </li>
        <br>
        <li>
            Event type: <b>itemdrop</b><br>
            <i>This event type drops a given item on a square. Data field format: </i>
            <br><br>
            <ul>
                <li>
                    DROP:itemID ~ Gives the user item with itemID.
                </li>
                <li>
                    DUP:yes ~ Allows the user to get multiple of item. Default = yes.<br><br>
                    <i>Note this would allow the user to fire the event several times, as long as the item is used/removed from inventory.</i>
                </li>
            </ul>
        </li>
        <br>
        <li>
            Event type: <b>message</b><br>
            <i>Send a notification message to the user. Data field format: </i>
            <br><br>
            <ul>
                <li>
                    "MSG: you have entered death!;" ~ Simply the message to be shown
                </li>
            </ul>
        </li>
        <br>
        <li>
            Event type: <b>battle</b><br>
            <i>Send the user to an AI battle. Data field format: </i>
            <br><br>
            <ul>
                <li>
                    "AI:aiList;" ~ .-separated list of AIs to go into battle with
                </li>
                <li>
                    "GIVEITM:itemID;" ~ item to award upon won battle
                </li>
            </ul>
        </li>
        <br>
        <li>
            Event type: <b>teleport</b><br>
            <i>Teleport the user to specific location. Data field format: </i>
            <br><br>
            <ul>
                <li>
                    "LOC:x.y;" ~ location to teleport to, e.g. "loc:8.3"
                </li>
                <li>
                    "MSG: You fall into portal and teleport to Konoki;" ~ Simply the message to be shown
                </li>
            </ul>
        </li>
        <br>
        <li>
            Event type: <b>forcemove</b><br>
            <i>Move the user x,y fields from where he is now. Data field format: </i>
            <br><br>
            <ul>
                <li>
                    "MOVE:x.y;" ~ move user x,y fields, e.g. "move:-8.3"
                </li>
                <li>
                    "MSG: Angry ai kicks your ass and you fly 5 fields north;" ~ Simply the message to be shown
                </li>
            </ul>
        </li>
        <br>
        <li>
            Event type: <b>dialogue</b><br>
            <i>Force a dialogue with options for the user to pick. Data field format: </i>
            <br><br>
            <ul>
                <li>
                    "MSG: I am mysterious event person, I have mysterious event questions for you!;" ~ Simply the message to be shown
                </li>
                <li>
                    "OPTION:I have your money here:4;" ~ first response option. Fires event with id 4, which could e.g. be an itemdrop.<br>
                    "OPTION:I have some money for you:5;" ~ second response option. Fires event with id 5, which could e.g. be a message.<br>
                    "OPTION:I have no money for you:6" ~ third response option. Fires event with id 6, which could be teleport<br><br>
                    <i>Please note that this tag effectively gives a message & dropdown. Upon choice, it then goes to the travel page and forces 
                    a move action for the user (without any direction/magnitude), forcing the chosen event to a 100% chance and all other events to
                    a 0% chance. Event-linking dialogue events is likely not going to work.</i>
                </li>
                
            </ul>
        </li>
    </li>
</ul>
                
            </td>
        </tr>
    </table>
</div>