<div>
    <table class="table" width="95%">
        <tr>
            <td class="subHeader">Instructions</td>
        </tr>
        <tr>
            <td style="padding:20px;text-align:left;">
                
<ul>
    <li> 
        <b>Hook Points</b><br>
        Hook points are the places in the code where the game checks if any entries are completed. This is just for reference:
        <ul>
            <li>Training: any time a jutsu or stat is trained.</li>
            <li>Errand: any time a errand or crimes are performed.</li>
            <li>Lottery: called when lottery ticket is bought.</li>
            <li>Items: called when item is equipped.</li>
            <li>ItemsShop: called when item is purchased.</li>
            <li>Combat: called when combat is finished.</li>
            <li>Map: any time a user has moved.</li>
        </ul>
    </li>
    <br>
    <li> 
        <b>levelReq, levelMax, locationReq, and finishTime Fields</b><br>
        <ul>
            <li>
                <i>locationReq-format:</i> REGION(void), TERRITORY(territoryName), AREA(xmin.xmax.ymin.ymax)
            </li>
            <li>
                <i>questTime:</i> number of seconds to finish a quest.
            </li>
            <li>
                <i>questChance:</i> number between 1 and 100.
            </li>
            <br>
            <li><b>Orders</b>: 
                An order is mandatory, and levelReq signifies the level, 
                i.e. to become lvl2, you must have performed the order 
                with levelReq=1. <i>locationReq is not relevant.</i>
            </li>
            <li><b>Tasks</b>: 
                A task is an optional entry, and levelReq marks the level 
                required to see the entry. locationReq determines
                where the user must be to get the task (task is retrieved by going to the logbook)
            </li>
            <li><b>Quests</b>: 
                For a quest levelReq marks the level required to "activate" a 
                quest when entering the position marked by the locationReq-field. 
                questTime signifies the time the user has to complete the quest.
                questChance signifies the chance of getting the quest when moving within the given locationReq
            </li>
            <li><b>Mission & Crime</b>: 
                For these entries levelReq marks the level required to "activate" 
                the mission (through mission menu). locationReq-field is <b>irrelevant</b>.
                Only 1 of these entries can be "activated" at a time. For these entries, 
                the hook requirement "initiateCombat(...)" can be used (read below).
            </li>
            <li><b>Admin</b>: 
                To be used for achievements, which can only be accuired from admins.
            </li>
            <li>
                <b>levelMax</b>: is the max level at which the given entry is complete-able
            </li>
        </ul>
    </li>
    <br>
    <li> 
        <b>simpleGuide</b><br>
        <ul>
            <li>The simpleGuide column contains a ;-separated list of guides. Each guide has the format "[identifier]:Text"</li>
            <li>Valid identifiers are "req" for requirements, "rew" for rewards, "info" for messages to 
                show the user when action occur on the travel map, "battle" to show when winning a task-related AI battle, and "complete" for what to show to the user after a finished mission or quest.</li>
        </ul>
    </li>
    <br>
    <li>
        <b>Hook Requirements</b>
        <ul>
            <br>
            
            <li>
                <b><i>General Stats & Strenghs/Defenses</i></b>
                <ul>
                    <li>Format is "stats,[identifier-calculation][operator][value]"</li>
                    <li>Following identifiers are used in calculations: <br>
                        nin_def, gen_def, tai_def, weap_def, nin_off, gen_off, tai_off, weap_off, strength, intelligence, willpower, speed, element_mastery_1, element_mastery_2</li>
                    <li>Valid operators are >, =,  or both. You can also use ||.</li>
                    <li>Example: "stats,nin_def+gen_def+tai_off+weap_off+intelligence>35" </li>
                    <li>Example: "stats,nin_def>10 || gen_def+tai_off>=10" </li>
                </ul>
            </li>
            
            <br>
            
            <li>
                <b><i>Errands & Small Crimes</i></b>
                <ul>
                    <li>Format is "errands,[identifier-calculation][operator][value]"</li>
                    <li>Following identifiers are used: <br>
                        errands, scrimes, lcrimes (scrimes = small, lcrimes = large)</li>
                    <li>Valid operators are >, =,  or both.</li>
                    <li>Example: "errands,scrimes>50; errands,lcrimes>10; " </li>
                </ul>
            </li>
            
            <br>
            
            <li>
                <b><i>Missions & Crimes</i></b>
                <ul>
                    <li>Format is "missions,type,rank,subCondition[operator][value]"</li>
                    <li>Following types are used: mission, crime, any</li>
                    <li>Following ranks are used: S, A, B, C, D</li>
                    <li>Sub-conditions used: win, lose</li>
                    <li>Valid operators are >, =,  or both.</li>
                    <li>Example: "missions,mission,A>=5;" </li>
                </ul>
            </li>
            
            <br>
            
            <li>
                <b><i>Combat (needs combat system finished first & battle-log saving system)</i></b>
                <ul>
                    <li>Note that all combat logging is stored in a cache, 
                        and the cache is cleared every 3 hours (or randomly in case of overflow)! 
                        This means nothing will be tracked for longer than 3 hours!
                    </li>
                    <li>Format is "combat,[identifiers],[sub-identifier],[conditions][operator][sub_condition_value]"</li>
                    <li>Following identifiers are used: <br>
                        "anyAI", "mission", "crime", "quest", "normalArena", "tornArena", "mirrorArena", "anyArena", "mapAI", "eventAI", "PVP", "leaderPVP", "spars", "territory"</li>
                    <li>Following sub-identifier are used: <br>
                        any, AIid:id</li>
                    <li>Following conditions are used: <br>
                        wins, losses, draws, beatAID</li>
                    <li>Valid operators are > and =, or both.</li>
                    <li>Example for winning 3 or more arena fights, any AI id: "combat,normalArena,any,wins>=3" </li>
                    <li>Example for beating Ai #5 in arena battle 5 times: "combat,arena,AIid:5,wins>=5" </li>
                </ul>
            </li>
            
            <br>
            
            <li>
                <b><i>Jutsus</i></b>
                <ul>
                    <li>Format is "jutsu,[jutsuID|any],action[operator][value]"</li>
                    <li>Available actions are: level, times_used</li>
                    <li>Valid operators are > and =, or both.</li>
                    <li>Example for jutsuID 42 over level 100: "jutsu,42,level>=100" </li>
                </ul>
            </li>
            
            <br>
            
            <li>
                <b><i>Items</i></b>
                <ul>
                    <li>Format is "item,[item ID|any],action([opeator][value])"</li>
                    <li>own, equip, times_used (note: no operator&value can be used for for equip and own)</li>
                    <li>Valid operators are > and =, or both.</li>
                    <li>Example for equipping any item and using item #1 5 times: "item,any,equip; item,1,times_used>=5;" </li>
                </ul>
            </li>
            
            <br>
            
            <li>
                <b><i>Lottery</i></b>
                <ul>
                    <li>Format is "lottery,tickets[operator][value]"</li>
                    <li>Valid operators are > and =, or both.</li>
                    <li>Example for buying 20 or more tickets: "lottery,tickets>=20" </li>
                </ul>
            </li>
            
            <br>
            
            <li>
                <b><i>Factions</i></b>
                <ul>
                    <li>Format is "factions,faction,(join | action[operator][value])"</li>
                    <li>Valid factions are: anbu, kage, clan, surgeon, hunter, bhunter, armorCraft, weaponCraft, chefCook, miner, herbalist</li>
                    <li>Valid action for occupations are: level and join (which would be any level)</li>
                    <li>Valid action for kage, anbu and clan are: village and join (which would be any village)</li>
                    <li>Example for joining an anbu: "factions,anbu,join" </li>
                    <li>Example for lvl10 med ninja: "factions,surgeon,level,10" </li>
                    <li>Example for Konoki kage: "factions,kage,village,Konoki" </li>
                </ul>
            </li>
            
            <br>
            
            <li>
                <b><i>Movement</i></b>
                <ul>
                    <li>Used for tracking travel movements.</li>
                    <li>Note that all movements are stored in a cache, 
                        and the cache is cleared every 2 hours (or randomly in case of overflow)!</li>
                    <li>If you've implemented a "move" requirement, you can use the "info" in the simpleGuide field. 
                        The text will be shown when the move requirement is completed, i.e. on the map</li>
                    <li>Format is "move,locationIdentifier[operator]times"</li>
                    <li>Valid operators are > and =, or both. Using only = is senseless, since no hook point is on the move function.</li>
                    <li>Valid locationIdentifiers are: REGION(void), TERRITORY(territoryName), AREA(xmin.xmax.ymin.ymax)</li>
                    <li>Example for walking 5 steps or more in map: "move,REGION(wasteland)>=5" </li>
                    <li>Example for walking 7 steps or more in Fireheart Fores: "move,TERRITORY(Fireheart Fores)>=7" </li>
                    <li>Example for walking 1 step or more in square 5,6: "move,AREA(5.5.6.6)>=1" </li>
                </ul>
            </li>
            
            <br>
            
            <li>
                <b><i>Page Visits</i></b>
                <ul>
                    <li>Used for tracking user page navigation. Note that all page navigation are stored in a cache, 
                        and the cache is cleared every 30 minutes (or randomly in case of overflow)!</li>
                    <li>Format is "page,pageID"</li>
                    <li>Example for visiting profile page (ID #2): "page,2" </li>
                </ul>
            </li>
            
            <br>
            
            <li>
                <b><i>Initiate Combat (Mission & Crimes only)</i></b>
                <ul>
                    <li>Used for instantly starting a combat with AI</li>
                    <li>If you've implemented a "initiateCombat" requirement, you can use the "info" in the simpleGuide field. 
                        The text will be shown when the user has defeated the AI</li>
                    <li>Format is "initiateCombat,aiList,[.-separated IDlist]"</li>
                    <li>Example for initiating MPvP with Ai 30 and 55: "initiateCombat,aiList,30.55" </li>
                </ul>
            </li>
            
            <br>
            
            <li>
                <b><i>Create AI on map and Beat it (Mission & Crimes only)</i></b>
                <ul>
                    <li>Used for creating an AI on the map. Requirement fulfilled when user has found & beat the AI.</li>
                    <li>If you've implemented a "createAI" requirement, you can use the "info" in the simpleGuide field. 
                        The text will be shown when the user has defeated the AI</li>
                    <li>Format is "createAI,[.-separated IDlist for single battle],locationIdentifiers,chance"</li>
                    <li>Valid locationIdentifiers are: REGION(void), TERRITORY(territoryName), AREA(xmin.xmax.ymin.ymax)</li>
                    <li>Use the combat tag to determine how many times you want this AI killed</li>
                    <li>Example for 50% chance initiating MPvP with Ai 30 and 55 at x3, y5: "createAI,30.55,Haunted Forest,50" </li>
                </ul>
            </li>
             
        </ul>
    </li>
    <br>
    <li> 
        <b>Rewards</b>
        <ul>
            <br>
            
            <li>
                <b><i>Activate Quest</i></b>
                <ul>
                    <li>Can be used for quest chains</li>
                    <li>Format is "quest,id"</li>
                    <li>Example for activating quest ID #5: "quest,5"</li>
                </ul>
            </li>
            <br>
            <li>
                <b><i>Simple stats</i></b>
                <ul>
                    <li>Format is "stats,stat,amount"</li>
                    <li>Following stats are supported: <br>
                        nin_def, gen_def, tai_def, weap_def, nin_off, gen_off, tai_off, weap_off, 
                        strength, intelligence, willpower, speed, experience, money, bank, 
                        max_health, max_cha, max_sta, experience
                    </li>
                    <li>Example: "stats,experience,1000;stats,ryo,500"</li>
                </ul>
            </li>
            <br>
            <li>
                <b><i>Items (Add & Remove)</i></b>
                <ul>
                    <li>Format is "item,itemID,action"</li>
                    <li>actions are: add, remove_once and remove_all. </li>
                    <li>Remove only removes in case user has the item. In case the user doesn't have the item, the entry will not fail.</li>
                    <li>Example for adding 2x itemID #30: "item,30,add"</li>
                    <li>Example for removing one item itemID #30: "item,30,remove"</li>
                </ul>
            </li>
            <br>
            <li>
                <b><i>Jutsus</i></b>
                <ul>
                    <li>Format is "jutsu,jutsuID,lvl"</li>
                    <li>In case the user already has the jutsu, it will add to his previous level!</li>
                    <li>Example for jutsuID #30, lvl.50: "jutsu,30,50"</li>
                </ul>
            </li>
            <br>
            <li>
                <b><i>Tickets</i></b>
                <ul>
                    <li>Format is "tickets,amount"</li>
                    <li>Example for 50 tickets: "tickets,50"</li>
                </ul>
            </li>
            
        </ul>
    </li>
    <br>
    <li> 
        <b>Restrictions</b>
        <ul>
            <br>
            
            <li>
                <b><i>Core element restriction</i></b>
                <ul>
                    <li>Restricts a quest to a given core element</li>
                    <li>Format is "element,[all|pri|sec|spe],elementList"</li>
                    <li>Example for restricting to users having the earth, water or fire as primary element: "element,pri,earth.water.fire"</li>
                </ul>
            </li>
            <br>
            <li>
                <b><i>Other Task/Order/Quest</i></b>
                <ul>
                    <li>Can be used for quest chains</li>
                    <li>Format is "quest,id"</li>
                    <li>Example for requiring quest ID #5: "quest,5"</li>
                </ul>
            </li>
            <br>
            <li>
                <b><i>Occupations</i></b>
                <ul>
                    <li>Format is "occupation,occupationIdentifier[operator][value]"</li>
                    <li>Valid operators are > and =, or both.</li>
                    <li>Occupation identifiers are:  surgeon, hunter, bhunter, armorCraft, weaponCraft, chefCook, miner, herbalist</li>
                    <li>Example requiring lvl1 of occupation bounty hunter: "occupation,bhunter>=1"</li>
                </ul>
            </li>
            <br>
            <li>
                <b><i>Village</i></b>
                <ul>
                    <li>Format is "village,[.-separated list of village names]"</li>
                    <li>Note: use Capital name identifiers!</li>
                    <li>Name identifiers are: Samui, Konoki, Syndicate, Silence, Shroud, Shine</li>
                    <li>Example requiring konoki or syndicate: "village,Konoki.Syndicate"</li>
                </ul>
            </li>
            <br>
            <li>
                <b><i>Item</i></b>
                <ul>
                    <li>Requires the user to own an item to perform quest, i.e. quest items</li>
                    <li>Format is "item,[itemID]"</li>
                    <li>Example requiring item ID 42: "item.42"</li>
                </ul>
            </li>
            <br>
            <li>
                <b><i>War</i></b>
                <ul>
                    <li>Require the user village to be in war with another village. Always true for syndicate</li>
                    <li>Format is "war,[any | .-separated list of village-identifiers]"</li>
                    <li>Note: use Capital name identifiers!</li>
                    <li>Name identifiers are: Samui, Konoki, Syndicate, Silence, Shroud, Shine</li>
                    <li>Example requiring war with Konoki or Shine: "war,Konoki.Shine"</li>
                    <li>Example requiring war with any village: "war,any"</li>
                </ul>
            </li>
            
        </ul>
    </li>
</ul>
                
            </td>
        </tr>
    </table>
</div>