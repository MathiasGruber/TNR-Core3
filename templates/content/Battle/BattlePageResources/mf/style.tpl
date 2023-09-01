{literal}
<!--make sure to put cursor pointer on anything that can be touched/clicked-->
<!--convert class grids to custom grids to prevent player records from changing on small screens-->

<!--css vars-->
<style>
    :root{
        --static: url(data:image/png;base64,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);

        --cd:  url('data:image/png;base64,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');

        --scroll:  url('data:image/png;base64,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');
    }
</style>

<style>
#battle_page details{
    position:relative;
    border-top: 1px solid;
    border-left: 1px dashed;
    border-right: 1px dashed;
    border-color: var(--accent-color-dark);
    border-radius: 5px 5px 0px 0px;
}
#battle_page details[open]{
    border:1px solid black;
}

#battle_page details > summary {
    list-style: none;
    padding: 0px 20px;
    background: var(--accent-color);
    border-radius:4px;
    cursor:pointer;
}
#battle_page details > summary::-webkit-details-marker {
    display: none;
}
#battle_page summary:after {
    position: absolute;
    left: 4px;
    top: 0;
    content: "+";
    font-size: 200%;
    line-height: 85%;
    font-weight: bold; 
    margin: -5px 10px 0 0;
    text-align: center; 
    cursor:pointer;
}
#battle_page details[open] summary:after {
    content: "-";
}

#battle_page details > div{
    outline: 1px solid var(--accent-border-color);
    background: var(--accent-color-light);
    padding: 4px;
}
</style>

<!--structure-->
<style>
    .page-title{
        max-height: 6vh;
    }

    #battle-clock{
        grid-row:3;
    }

    /*battle_field*/

        /*developed spacing on ff, have to fix for other browsers*/
        /*chrome*/
        #battle_form{
            grid-template-rows: repeat(3,min-content) calc(50vh + 30px) calc(32vh) min-content;
        }
        #jutsus, #weapons, #items, #battle_log{min-height:32vh}

        @media screen  and (max-height:991px) and (orientation: portrait){
            #battle_form{
                grid-template-rows: repeat(3,min-content) calc(50vh + 30px) calc(30vh) min-content;
            }
            #jutsus, #weapons, #items, #battle_log{min-height:30vh}
        }
        @media screen  and (max-height:991px) and (orientation: landscape){
            #battle_form{
                grid-template-rows: repeat(3,min-content) calc(45vh + 30px) calc(35vh) min-content;
            }
            #jutsus, #weapons, #items, #battle_log{min-height:35vh}
        }
        @media screen  and (max-height:855px) and (orientation: landscape){
            #battle_form{
                grid-template-rows: repeat(3,min-content) calc(40vh + 30px) calc(40vh - 30px) min-content;
            }
            #jutsus, #weapons, #items, #battle_log{min-height:calc(40vh - 30px)}
        }
        @media screen  and (max-height:720px) and (orientation: landscape){
            #battle_form{
                grid-template-rows: repeat(3,min-content) calc(35vh + 30px) calc(45vh - 30px) min-content;
            }
            #jutsus, #weapons, #items, #battle_log{min-height:calc(45vh - 30px)}
        }
        @media screen and (max-width:991px) and (max-height:720px) and (orientation: landscape){
            #battle_form{
                grid-template-rows: repeat(3,min-content) 81vh min-content;
            }
            #jutsus, #weapons, #items, #battle_log{min-height:81vh}
            #control-panel, #battle_field{max-height:81vh}
        }
        @media screen and (max-width:991px) and (max-height:600px) and (orientation: landscape){
            #battle_form{
                grid-template-rows: repeat(3,min-content) 78vh min-content;
            }
            #jutsus, #weapons, #items, #battle_log{min-height:78vh}
            #control-panel, #battle_field{max-height:78vh}
        }
        @media screen and (max-width:991px) and (max-height:480px) and (orientation: landscape){
            #battle_form{
                grid-template-rows: repeat(3,min-content) 75vh min-content;
            }
            #jutsus, #weapons, #items, #battle_log{min-height:75vh}
            #control-panel, #battle_field{max-height:75vh}
        }

        #battle_form{
            max-height: 95vh;
            overflow-x: hidden;
            grid-gap:0px;
            padding-bottom:0px;
        }
        #battle_field{
            grid-template-columns: 1fr min-content 1fr;
            overflow-y: scroll;
            overflow-x: hidden;
            width: calc(100% + 34px);
            margin: -8px -8px 0px -8px;
            padding: 12px 10px 0px 10px;
            grid-row:4;
            height:100%;
        }
    /*battle_field*/

    /*record*/
        /*general*/
            .record{
                margin: -5px -5px 21px -5px;
                max-width:335px;
            }

            .record, .record-title{
                padding:2px;
                transition: all 0.1s ease;
                display: grid;
                grid-gap: 2px;
                align-items: center;
                justify-content: stretch;
            }

            .record:not(.self) .record-title{
                grid-column: span 2;
                grid-template-columns: 1fr min-content;
            }
            .record:not(.self) .record-title.left {grid-template-columns: min-content 1fr;}
            .record:not(.self) .record-title.right{grid-template-columns: 1fr min-content;}

            .record.left, .record-title.left{grid-template-columns: min-content 1fr;}
            .record.right, .record-title.right{grid-template-columns: 1fr min-content;}

            .record.targetable:not(.targeted)
	        {
	        	cursor:pointer;
	        	transition: all 0.1s ease;
	        }
        /*general*/

        /*title*/
            .record-title{grid-column: span 2;}
            .record-title{
	        	margin:-4px -4px 0px -4px;
	        	white-space:nowrap;
	        }
        /*title*/

        /*portrait*/
            .record-portrait.right{margin: -2px -2px 0px 0px;}
            .record-portrait.left{margin: -2px 0px 0px -2px;}
            .self .record-portrait{margin: 2px 2px 0px 0px;}
            .record.self > .record-portrait{width:75px; height: 75px;}
	        .record-portrait{width:56px; height:56px;}
        /*portrait*/

        /*bar*/
            .record-bar
            {
                height:17px;
                margin:0px 0px;
                position:relative;
            }

            div .record-bar:nth-of-type(2n) {margin:8px 0px;}
            .record-bar .fill, .record-bar .backer{height:15px;}
            .left .fill, .right .backer{display:inline-block;}
	        .left .backer, .right .fill{float:right;}
            .floater{
                position:absolute;
                top:0;
                right:0;
                left:0;
                line-height:1.1;
            }

            .floater .colorTip{
                left: 0;
                right: 0;
                bottom: calc(100% + 7px);
            }
        /*bar*/

        /*details*/
            .record-details{
                margin-top: -4px;
                margin-right: 4px;
            }

            .self .record-details{margin:0px;}
            .record-details{margin-top:1px;}
		    .record.self > .record-details{margin-bottom:-0px;}

            .record-extra-details{
                grid-column: span 2;
            }
        /*details*/
    /*record*/

    /*control*/
        #control-panel{
            margin: -9px -9px 0px -9px;
            overflow-y: scroll;
            overflow-x: hidden;
            width: calc(100% + 34px);
            grid-row:5;
        }
        /*tabs*/
            #tabs{
                grid-template-columns: repeat(4,1fr);
                display: grid;
                justify-content: center;
                align-items: center;
                grid-gap: 0px;
                position:sticky;
                top:0;
                z-index:1;
            }

            #jutsus, #weapons, #items{
                display: grid;
                grid-template-columns: repeat(3,1fr);
                justify-content: center;
                align-items: start;
                width: 100%;
                height: min-content;
                text-align: center;
			    grid-gap: 0px 8px;
			    padding: 4px 8px 4px 8px;
            }
        /*tabs*/

        /*options*/
            .option-button{
                cursor:pointer;
            }
            .option-button, .option-record{
			    padding:0px;
                width:100%;
		    }

            .option-record{
                grid-column: span 3;
                justify-content: stretch;
            }

            .color-layer{
		    	padding:8px;
		    	display:flow-root;
		    	margin:-1px;
		    }

            .top-bar{
                height: 2px;
                margin: -8px -8px 6px -8px;
            }

            .top-bubble
		    {
		    	margin-top: -16px;
		    	margin-bottom:4px;
		    	margin-left: calc(50% - 28px);
		    	margin-right: calc(50% - 28px);
		    	border-radius: 99%;
		    }

            .bottom-bar{
			    height:2px;
			    margin: 7px -8px -7px -8px;
		    }

            .bottom-bubble{
			    margin-bottom: -14px;
			    margin-left: calc(50% - 15px);
			    margin-right: calc(50% - 15px);
			    border-radius: 5px;
		    }

            .option-button, .option-record{
			    margin-top:8px;
			    margin-bottom:8px;
			    height:min-content;
		    }

            .option-title{
			    white-space:nowrap;
		    }

            .option-effects{}
            .option-effects > div{
                white-space:normal;
                padding-left: 2em;
                padding-bottom: 1em;
                text-indent: -2em;
            }

            .cancel-button{
		    	height: 30px;
		    	width: 30px;
		    	font-weight: 700;
		    	margin: -7px -7px 0px 0px;
                cursor:pointer;
		    }
        /*options*/

        /*jutsu*/
            .jutsu-cooldown{
		    	margin-top: -16px;
		    	margin-left: -7px;
		    	width: 21px;
		    	height: 21px;
		    }

            .jutsu-record{
                width:100%;
            }

            .jutsu-record-title, .jutsu-button-title{
		    	display: grid;
		    	justify-content: stretch;
		    	align-items: center;
		    	grid-gap: 8px;
		    	grid-template-columns: min-content 1fr min-content;
		    }

		    .jutsu-button-body{
		    	white-space:nowrap;
		    	min-height:16px;
		    }

            .jutsu-record-body{
		    	padding:8px 0px;
		    }

            .jutsu-effects{}
        /*jutsu*/

        /*weapons & items*/
            .weapon-record, .item-record{
                width:100%;
            }

            .weapon-record-title, .item-record-title{
                display:grid;
                justify-content: stretch;
                align-items: center;
                grid-gap: 8px;
                grid-template-columns: 1fr max-content 1fr;
		    }

            .weapon-record-title .weapon-title, .item-record-title .item-title{
                grid-column: 2/3;
            }

            .weapon-record-title .cancel-button, .item-record-title .cancel-button{
                grid-column: 3/4;
                justify-self: end;
            }

		    .weapon-button-body, .item-button-body{
		    	white-space:nowrap;
		    	min-height:16px;
		    }

            .weapon-record-body, .item-record-body{
		    	padding:8px 0px;
		    }
        /*weapons & items*/

        /*submit*/
            .submit-button-wrapper{
                margin: 0px -9px;
                padding: 16px;
                display: grid;
                grid-template-columns: auto min-content min-content;
                grid-gap: 4px;
                white-space: nowrap;
                grid-row:6;
                z-index:25;
            }
		    .submit-button-wrapper button{
                padding: 8px;
                width: 100%;
                height: 100%;
            }
		    .submit-button-wrapper button:not(.disabled){
		    	cursor:pointer;
		    }
		    .submit-button-wrapper button.disabled{
		    	cursor:no-drop;
		    }
        /*submit*/
    /*control*/

    @media screen  and (max-width:720px) and (max-height:991px) and (orientation:portrait) {
        #battle_page{
            min-height:100vh;
        }

        #battle_form{
            min-height:94vh;
        }

        #battle_field{
            display:initial;
            width: calc(100% + 16px);
            max-height: calc(50vh + 30px);
        }

        #control-panel{
            width: calc(100% + 16px);
            max-height: calc(32);
        }

        #friends{
            padding-right:10%;
        }

        #vs{
            margin-top: -24px;
            padding:8px
        }

        #foes{
            padding-left:10%;
            display: grid;
            justify-content: end;
        }

        .option-record{
            grid-column: span 2;
        }

        .option-button, .option-record{
            width: calc(100% - 16px);
            margin-left: auto;
            margin-right: auto;
        }

        #jutsus, #weapons, #items{
            grid-gap:0px;
            padding:0px;
            grid-template-columns: repeat(2,1fr);
        }
    }

    @media screen and (max-width:991px) and (max-height:720px) and (orientation: landscape) {
        .font-large:not(.anti-shrink) {
		    font-size: 14px!important
	    }
	    .font-larger:not(.anti-shrink) {
	    	font-size: 16px!important
	    }
	    .font-giant:not(.anti-shrink) {
	    	font-size: 24px!important
	    }
        #center-bar{
		    font-size: 12px
	    }

        #battle_page{
            min-height:100vh;
        }

        #battle_form{
            min-height:94vh;
        }

        .page-title, #battle-clock, #battle-information-title{
            padding:0px;
        }

        #battle_form{
            grid-template-columns: auto auto;
        }

        #battle-clock, #battle-information-title, #battle-information-content, .submit-button-wrapper{
            grid-column: span 2;
        }

        #battle_field{
            grid-column: 1;
            display: initial;
            width: calc(100% + 24px);
            padding: 8px 8px 0px 8px;
            margin: 0px;
            position: relative;
            left: -10px;
        }

        #friends{
            padding-right:10%;
        }

        #vs{
            margin-top: -24px;
            padding:8px
        }

        #foes{
            padding-left:10%;
            display: grid;
            justify-content: end;
        }

        #control-panel{
            grid-column: 2;
            grid-row: 4;
            width: 100%;
            padding: 0px;
            margin: 0px -10px 0px 10px;
            border-left: 2px ridge var(--accent-border-color);
            border-radius: 7px 0px 0px 0px;
        }

        #controls{
            height:100%;
        }

        .option-record{
            grid-column: span 2;
        }

        .option-button, .option-record{
            width: calc(100% - 16px);
            margin-left: auto;
            margin-right: auto;
        }

        #jutsus, #weapons, #items{
            grid-gap:0px;
            padding:0px;
            grid-template-columns: repeat(2,1fr);
            grid-auto-rows: min-content;
        }

        .submit-button-wrapper{
            grid-row: 5;
            padding:8px;
        }
        
        .submit-button-wrapper button{
            padding:6px;
        }
    }
</style>

<!--theme-->
<style>
    /*battle_field*/
    #battle_field{
        background: linear-gradient( to bottom, rgba(16,0,32,0), rgba(16, 0, 32, 0.15) calc(100% - 12px), rgba(16, 0, 32, 0.5));
        background-attachment: local;
    }
    /*battle_field*/

    /*record*/
        /*general*/
            .record
            {
                border: 1px outset grey;

                background: var(--background-normal);
                border-bottom: 1px dotted var(--accent-border-color);
                box-shadow: 0 4px 2px -2px rgba(0,0,0,.5);
            }
            .record.untargetable{filter:brightness(40%);}
            .record.targetable:not(.targeted){filter:brightness(80%);}

            .record-title{
                background: var(--background-dark);
                color: var(--accent-color);
                border-top: 1px dotted var(--accent-border-color);
                border-bottom: 1px dotted var(--accent-border-color);
                margin-bottom: -.1px;
                box-shadow: 0 4px 2px -2px rgba(0,0,0,.5);
            }
        /*general*/

        /*title*/
        /*title*/

        /*portrait*/
            .ai-portrait{background:black;}
            .flip-portrait{}
            .dim-portrait{}
            .r0 {border: 3px ridge var(--accent-color-dim)}
            .r1 {border: 3px ridge #7f553b}
            .r2 {border: 3px ridge #e49977}
            .r3 {border: 3px ridge #d9d9dd}
            .r4 {border: 3px ridge #d8b66c}
            .r5 {border: 3px ridge #a9d1d5}
        /*portrait*/

        /*bar*/
            .record-bar
            {
                border:1px solid black;
                border-radius:7px;
                background-color:black;
            }

            .fill, .backer{border-radius:6px;}

            .floater{
                color:ghostwhite;
                text-shadow: var(--ui-text-outline-shadow);
            }

            .health .fill{background:  var(--static), linear-gradient(to bottom, #f88 0%, #f00 20%, #800 90%, #200 100%);}
            .chakra .fill{background:  var(--static), linear-gradient(to bottom, #88f 0%, #00f 20%, #008 90%, #002 100%);}
            .stamina .fill{background: var(--static), linear-gradient(to bottom, #8f8 0%, #0f0 20%, #080 90%, #020 100%);}

            .left.health  .fill{border-right:1px solid #f44;}
	        .left.chakra  .fill{border-right:1px solid #44f;}
	        .left.stamina .fill{border-right:1px solid #4f4;}

            .right.health  .fill{border-left:1px solid #f44;}
        /*bar*/

        /*details*/
        /*
        .record-extra-details{
			border-top: 1px solid black;
			border-left: 1px dashed black;
			border-right: 1px dashed black;
			border-radius: 3px 3px 0px 0px;
		}

        .record-extra-details[open]{
			border:1px solid black;
			border-radius:3px;
		}
        */
        /*details*/
    /*record*/

    /*control*/
        /*tabs*/
            #tabs{
                background-image: var(--background-normal-alt);
                border-bottom: 1px solid var(--accent-border-color);
            }
            .tab-button.opened{
                padding:8px 0px;
                border:1px solid var(--accent-color-dark);
                border-bottom:none;
                border-radius:5px 5px 0px 0px;
                background:var(--background-normal);
            }
            .tab-button.closed
            {
                padding:8px 0px;
                border:1px solid var(--accent-border-color);
                border-bottom:none;
                border-radius:5px 5px 0px 0px;
                background:var(--background-normal-alt);
                cursor:pointer;
            }
            .tab-button.closed.empty
            {
                filter:brightness(75%);
                cursor:no-drop;
            }
            .tab-button.closed:not(.empty):hover{
                filter:brightness(125%);
            }

            #jutsus, #weapons, #items{
                background: linear-gradient( to bottom, rgba(16,0,32,0), rgba(16, 0, 32, 0.15) calc(100% - 12px), rgba(16, 0, 32, 0.5));
                background-attachment: local;
            }
        /*tabs*/

        /*options*/
            .option-button{
                background: var(--background-normal-alt);
                box-shadow: 0 0 6px rgba(0,0,0,.75);
                color: var(--accent-color-dark);
                transition: all 0.3s ease-in-out;
            }

            .option-button:not(.disabled):hover{
                border-style: inset;
                box-shadow: inset 0 0 6px rgba(0,0,0,0.75);
                transition: all 0.3s ease-in-out;
            }

            .option-button.disabled{
			    filter: brightness(50%);
		    }

		    .option-record.disabled{
			    filter: brightness(75%);
		    }

		    .color-layer{
		    	--see-through:rgba(0,0,0,0.0);
		    	--None:rgba(192,192,192,0.75);
		    	--fire:rgba(256,128,128,0.5);
		    	--earth:rgba(224,192,128,0.5);
		    	--lightning:rgba(256,256,128,0.5);
		    	--wind:rgba(192,256,256,0.5);
		    	--water:rgba(128,128,256,0.5);
		    }

		    .color-layer.None{background-image: linear-gradient(var(--see-through), var(--None));}
		    .color-layer.fire{background-image: linear-gradient(var(--see-through), var(--fire));}
		    .color-layer.earth{background-image: linear-gradient(var(--see-through), var(--earth));}
		    .color-layer.lightning{background-image: linear-gradient(var(--see-through), var(--lightning));}
		    .color-layer.wind{background-image: linear-gradient(var(--see-through), var(--wind));}
		    .color-layer.water{background-image: linear-gradient(var(--see-through), var(--water));}
		    .color-layer.tempest{background-image: linear-gradient(to bottom right, var(--wind), var(--lightning));}
		    .color-layer.dust{background-image: linear-gradient(to bottom right, var(--wind), var(--earth));}
		    .color-layer.ice{background-image: linear-gradient(to bottom right, var(--wind), var(--water));}
		    .color-layer.scorching{background-image: linear-gradient(to bottom right, var(--wind), var(--fire));}
		    .color-layer.lava{background-image: linear-gradient(to bottom right, var(--fire), var(--earth));}
		    .color-layer.steam{background-image: linear-gradient(to bottom right, var(--fire), var(--water));}
		    .color-layer.light{background-image: linear-gradient(to bottom right, var(--fire), var(--lightning));}
		    .color-layer.storm{background-image: linear-gradient(to bottom right, var(--water), var(--lightning));}
		    .color-layer.wood{background-image: linear-gradient(to bottom right, var(--water), var(--earth));}
		    .color-layer.magnetism{background-image: linear-gradient(to bottom right, var(--earth), var(--lightning));}

            .top-bubble, .bottom-bubble{
			    border: 1px solid black;
			    background-color: #fff2f9;
		    }

            .top-bar.exp{background-image: linear-gradient(gold, goldenrod);}
            .top-bar.durability{background-image: linear-gradient(ghostwhite, slategray);}
            .bottom-bar{background-image: linear-gradient(rgb(32,0,64), rgb(96,64,192));}

            .option-effects{}

            .cancel-button{
                border-radius: 3px 0px 3px 15px;
                box-shadow: 0 0 6px rgba(0,0,0,.75);
                color: var(--accent-color-dark);
                background: var(--background-normal-alt);
                border: 1px dotted var(--accent-border-color);
                transition: all 0.3s ease-in-out;
            }

            .cancel-button:hover{
                border-style:inset;
                box-shadow: inset 0 0 6px rgba(0,0,0,.75);
                line-height: 175%;
                transition: all 0.3s ease-in-out;
            }
        /*options*/

        /*jutsu*/
            .jutsu-cooldown{
		    	line-height: 2.4;
		    	background-color: #fff2f9;
		    	border-radius: 100%;
		    }

            .jutsu-cooldown.normal{
			    background: var(--cd);
                background-size: cover;
		    }

		    .jutsu-cooldown:not(.normal){
			    background: var(--scroll);
                background-size: cover;
		    }

            .jutsu-record{
                background: var(--background-normal-alt);
                box-shadow: 0 0 6px rgba(0,0,0,0.75);
                color: var(--accent-color-dark);
            }

            .jutsu-effects{}
        /*jutsu*/

        /*weapons & items*/
            .weapon-record, .item-record{
                background: var(--background-normal-alt);
                box-shadow: 0 0 6px rgba(0,0,0,0.75);
                color: var(--accent-color-dark);
            }
        /*weapons & items*/

        /*submit*/
            .submit-button-wrapper{
                background: var(--background-dark-alt);
                border-top: 1px dotted var(--accent-border-color);
                border-bottom: 1px dotted var(--accent-border-color);
                box-shadow: 0 -4px 2px -2px rgba(0,0,0,.5);
            }
            .submit-button-wrapper button{
                background: var(--background-normal);
                border: 1px dotted var(--accent-border-color);
                box-shadow: 0 0 6px rgba(0,0,0,.75);
                color: var(--accent-color-dark);
                transition: all 0.3s ease-in-out;
            }
            .submit-button-wrapper button:not(.disabled):hover{
                border-style:inset;
                box-shadow: inset 0 0 6px rgba(0,0,0,.75);
                transition: all 0.3s ease-in-out;
            }
            .submit-button-wrapper button.disabled{
                filter: brightness(50%);
            }

            #cfh{
                border-color:goldenrod;
            }

            #flee{
                border-color:red;
            }

            #cfh, #flee{
                border-style:outset;
                border-width:2px;
                position: relative;
            }

            #cfh > .colorTip, #flee > .colorTip{
                left: -50%;
                right: -50%;
                min-width: 100%;
            }

            #cfh:hover, #flee:hover{
                border-style:inset;
            }
        /*submit*/
    /*control*/
</style>
<!--theme-->

{/literal}