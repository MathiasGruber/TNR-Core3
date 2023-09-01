{function waiting}
	<div class="span-3 waiting">
		You are now waiting on your opponents.
		<br><br>
		Please wait for them to choose their action or for the round to end.
		<br><br>
		When appropriate, this page will automatically refresh.
		<hr>
	</div>
{/function}

{function stunned}
	<div class="span-3 waiting">
		You are now waiting on your opponents while stunned.
		<br><br>
		Stunned for {$stunned} {if $stunned == 1}round{else}rounds{/if}.
		<br><br>
		Please wait for them to choose their action or for the round to end.
		<br><br>
		{if $battle_type_pve === true}
			<a id='RefreshPage' href="http://www.theninja-rpg.com/">Go to the next round.</a>
		{else}
			When appropriate, this page will automatically refresh.
		{/if}
		<hr>
	</div>
{/function}

<script id="the_id">
	var the_id={$the_id};
	var battle_id={$userdata['battle_id']};
</script>

{literal}
	<script>
		!function(e){var n;if("function"==typeof define&&define.amd&&(define(e),n=!0),"object"==typeof exports&&(module.exports=e(),n=!0),!n){var t=window.Cookies,o=window.Cookies=e();	o.noConflict=function(){return window.Cookies=t,o}}}(function(){function e(){for(var e=0,n={};e<arguments.length;e++){var t=arguments[e];for(var o in t)n[o]=t[o]}return n}function n(e){return 	e.replace(/(%[0-9A-Z]{2})+/g,decodeURIComponent)}return function t(o){function r(){}function i(n,t,i){if("undefined"!=typeof document){"number"==typeof(i=e({path:"/"},r.defaults,i)).expires&&	(i.expires=new Date(1*new Date+864e5*i.expires)),i.expires=i.expires?i.expires.toUTCString():"";try{var c=JSON.stringify(t);/^[\{\[]/.test(c)&&(t=c)}catch(e){}t=o.write?o.write(t,n)	:encodeURIComponent(String(t)).replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g,decodeURIComponent),n=encodeURIComponent(String(n)).replace(/%(23|24|26|2B|5E|60|7C)/g,	decodeURIComponent).replace(/[\(\)]/g,escape);var f="";for(var u in i)i[u]&&(f+="; "+u,!0!==i[u]&&(f+="="+i[u].split(";")[0]));return document.cookie=n+"="+t+f}}function c(e,t){if	("undefined"!=typeof document){for(var r={},i=document.cookie?document.cookie.split("; "):[],c=0;c<i.length;c++){var f=i[c].split("="),u=f.slice(1).join("=");t||'"'!==u.charAt(0)||(u=u.slice(1,	-1));try{var a=n(f[0]);if(u=(o.read||o)(u,a)||n(u),t)try{u=JSON.parse(u)}catch(e){}if(r[a]=u,e===a)break}catch(e){}}return e?r[e]:r}}return r.set=i,r.get=function(e){return c(e,!1)},	r.getJSON=function(e){return c(e,!0)},r.remove=function(n,t){i(n,"",e(t,{expires:-1}))},r.defaults={},r.withConverter=t,r}(function(){})});

		$(document).on(
      		"click",
      		".toggle-button-info, .toggle-button-drop",
      		function(t) {
      		  t.stopPropagation(),
      		    $(t.currentTarget).toggleClass("closed"),
      		    "#" !=
      		      $(t.currentTarget)
      		        .data("target")
      		        .charAt(0) &&
      		    "." !=
      		      $(t.currentTarget)
      		        .data("target")
      		        .charAt(0)
      		      ? $("#" + $(t.currentTarget).data("target")).toggleClass("closed")
      		      : $($(t.currentTarget).data("target")).toggleClass("closed");
      		}
    	);
	</script>
{/literal}


<script type="text/javascript" src="files/javascript/BattlePageNew.js" id="BattlePageJS"></script>

<style>
	#battle_page{
		margin-top:-10px !important;
	}

	.toggle-button-info {
	    font-weight: 500 !important;
	    width: 18px !important;
	    height: 18px !important;
	    display: inline-block !important;
	    border-radius: 50% !important;
	    border: 1px solid black !important;
	    font-size: 14px !important;
	    line-height: 1.1 !important;
	}

	#battle_clock{
		border-bottom:none !important;
	}

	#battle-information-content{
		padding-bottom:8px !important;
	}

	.left .fill, .right .backer {
    	display: inline-block !important;
    	position: fixed !important;
    	left: 0px !important;
	}

	.record-bar .fill, .record-bar .backer {
    	height: 17px !important;
	}

	summary::after {
		top: 4px !important;
	}

	.option-button, .option-record {
	    height: calc(100% - 22px) !important;
	}

	#battle_field, #control-panel {
    	width: calc(100% + 15px)  !important;
	}

	#control-panel{
		padding-right:20px;
	}

	#tabs{
		margin-right:-20px;
	}
</style>

<style>
#turn_timer_box {
	font-weight: 500
}

#player_information {
	grid-template-columns: 11fr 1fr 10fr
}

@media screen and (max-width:500px) {
	#player_information {
		grid-template-columns: 1fr
	}
}

#vs-pane {
	font-size: 40px;
	line-height: 45px;
	display: flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	height: 100%
}

#bottom-page-box {
	align-items: start;
	grid-template-columns: 1fr 2fr
}

@media screen and (max-width:500px) {
	#bottom-page-box {
		grid-template-columns: 1fr
	}
}

.flip-portrait {
	filter: hue-rotate(180deg);
	transform: scaleX(-1)
}

.dim-portrait {
	filter: brightness(50%)
}

.opponent-portrait,
.self-portrait,
.team-portrait {
	margin: -3px
}

.ai-portrait {
	width: 56px!important;
	height: 56px!important;
	font-size: 40px;
	line-height: 45px;
	color: grey;
}
#friends{
	margin-right:8px;
}
#vs{
	margin-left:-16px;
}
#foes{
	margin-right:16px;
	margin-left:-8px;
}

.self-card {
	margin-bottom: 24px
}

.self-portrait-box {
	width: 65px;
	height: 65px
}

.self-portrait {
	width: 65px;
	height: 65px
}

.self-portrait-button {
	position: relative;
	top: -64px;
	z-index: 10;
	left: 58px
}

.self-details-box {
	width: 100%;
	display: grid
}

.self-info-box {
	max-height: 65px;
	overflow-y: auto;
	align-items: start!important;
	justify-content: start!important
}

.self-bars-box {
	width: 100%;
	align-self: end;
	margin-bottom: 1px
}

.self-chakra-box,
.self-health-box,
.self-stamina-box {
	height: 8px;
	width: 100%
}

.self-chakra-bar,
.self-health-bar,
.self-stamina-bar {
	float: left;
	height: 8px
}

.self-chakra-backer,
.self-health-backer,
.self-stamina-backer {
	float: right;
	height: 8px
}

.self-chakra-box {
	margin-top: 5px;
	margin-bottom: 5px
}

.team-card {
	margin-bottom: 16px
}

.team-portrait-box {
	width: 50px;
	height: 50px
}

.team-portrait {
	width: 56px;
	height: 56px
}

.team-portrait-button {
	position: relative;
	top: -49px;
	z-index: 10;
	left: 55px
}

.team-details-box {
	display: grid
}

.team-info-box {
	max-height: 50px;
	overflow-y: auto;
	align-items: start!important;
	justify-content: start!important
}

.team-bars-box {
	margin-bottom: 1px;
	align-self: end
}

.team-health-box {
	height: 8px;
	width: 100%
}

.team-health-bar {
	float: left;
	height: 8px
}

.team-health-backer {
	float: right;
	height: 8px
}

.opponent-card {
	margin-bottom: 16px
}

.opponent-portrait-box {
	width: 50px;
	height: 50px
}

.opponent-portrait {
	width: 56px;
	height: 56px
}

.opponent-portrait-button {
	position: relative;
	top: -49px;
	z-index: 10;
	right: 52px
}

.opponent-portrait-button.closed {
	right: 39px
}

.opponent-details-box {
	display: grid
}

.opponent-info-box {
	max-height: 50px;
	overflow-y: auto;
	align-items: start!important;
	justify-content: start!important
}

.opponent-bars-box {
	margin-bottom: 1px;
	align-self: end
}

.opponent-health-box {
	height: 8px;
	width: 100%
}

.opponent-health-bar {
	float: right;
	height: 8px
}

.opponent-health-backer {
	float: left;
	height: 8px
}

#battle_log div{
	cursor: pointer
}

.bracket-blue,
.bracket-brown,
.bracket-green,
.bracket-grey,
.bracket-orange,
.bracket-red,
.bracket-teal {
	padding: 4px;
	padding-left: 8px;
	padding-right: 8px;
	margin: 4px
}

.bracket-brown,
.bracket-green,
.bracket-grey,
.bracket-red {
	margin: 16px
}

.page-box {
	width: 100%;
	height: 100%;
	display: grid;
	text-align: center
}

.page-title {
	padding: 8px;
	font-size: 16px
}

.page-sub-title,
.page-sub-title-no-margin,
.page-sub-title-top {
	margin-left: -8px;
	margin-right: -8px;
	padding: 4px;
	margin-top: 16px;
	font-size: 16px
}

.page-sub-title-inside {
	margin-left: 0!important;
	margin-right: 0!important
}

.page-sub-title-no-margin {
	margin-top: 0
}

.page-sub-title-top {
	margin-top: -17px
}

.page-content {
	padding: 8px;
	display: grid;
	padding-bottom: 16px;
	padding-top: 16px
}

.page-content-addon {
	padding: 8px;
	padding-bottom: 16px;
	padding-top: 0;
	margin-top: -1px;
	text-align: center
}

#page-footer {
	display: grid;
	padding: 16px;
	margin-top: -1px
}

.page-footer-button {
	padding: 8px;
	width: 100%;
	height: 100%
}

.page-grid {
	display: grid;
	grid-gap: 16px
}

.page-grid-justify-center {
	justify-content: center!important
}

.page-grid-justify-stretch {
	justify-content: stretch!important
}

.grid-center-center {
	justify-content: center;
	align-items: center
}

.grid-start-start {
	justify-content: start;
	align-items: start
}

.self-end {
	justify-self: end
}

.grid-gap {
	grid-gap: 16px
}

.grid-gap-none {
	grid-gap: 0!important
}

.page-seperate {
	padding-bottom: 16px;
	margin-bottom: 16px
}

.no-wrap {
	white-space: nowrap
}

.font-tiny {
	font-size: 9px!important
}

.font-small {
	font-size: 12px!important
}

.font-large {
	font-size: 16px!important
}

.font-larger {
	font-size: 18px!important
}

.font-giant {
	font-size: 36px!important
}

.stiff-grid {
	display: grid;
	justify-content: center;
	align-items: center;
	grid-gap: unset;
	grid-gap: 8px
}

.stiff-column-1 {
	grid-template-columns: 1fr
}

.stiff-column-2 {
	grid-template-columns: repeat(2, auto)
}

.stiff-column-3 {
	grid-template-columns: repeat(3, auto)
}

.stiff-column-4 {
	grid-template-columns: repeat(4, auto)
}

.stiff-column-5 {
	grid-template-columns: repeat(5, auto)
}

.stiff-column-6 {
	grid-template-columns: repeat(6, auto)
}

.stiff-column-7 {
	grid-template-columns: repeat(7, auto)
}

.stiff-column-8 {
	grid-template-columns: repeat(8, auto)
}

.stiff-column-9 {
	grid-template-columns: repeat(9, auto)
}

.stiff-column-10 {
	grid-template-columns: repeat(10, auto)
}

.stiff-column-fr-1 {
	grid-template-columns: repeat(1, 1fr)
}

.stiff-column-fr-2 {
	grid-template-columns: repeat(2, 1fr)
}

.stiff-column-fr-3 {
	grid-template-columns: repeat(3, 1fr)
}

.stiff-column-fr-4 {
	grid-template-columns: repeat(4, 1fr)
}

.stiff-column-fr-5 {
	grid-template-columns: repeat(5, 1fr)
}

.stiff-column-fr-6 {
	grid-template-columns: repeat(6, 1fr)
}

.stiff-column-fr-7 {
	grid-template-columns: repeat(7, 1fr)
}

.stiff-column-fr-8 {
	grid-template-columns: repeat(8, 1fr)
}

.stiff-column-fr-9 {
	grid-template-columns: repeat(9, 1fr)
}

.stiff-column-fr-10 {
	grid-template-columns: repeat(10, 1fr)
}

.stiff-column-min-left-1 {
	grid-template-columns: min-content
}

.stiff-column-min-left-2 {
	grid-template-columns: min-content 1fr
}

.stiff-column-min-left-3 {
	grid-template-columns: min-content repeat(2, auto)
}

.stiff-column-min-left-4 {
	grid-template-columns: min-content repeat(3, auto)
}

.stiff-column-min-left-5 {
	grid-template-columns: min-content repeat(4, auto)
}

.stiff-column-min-left-6 {
	grid-template-columns: min-content repeat(5, auto)
}

.stiff-column-min-left-7 {
	grid-template-columns: min-content repeat(6, auto)
}

.stiff-column-min-left-8 {
	grid-template-columns: min-content repeat(7, auto)
}

.stiff-column-min-left-9 {
	grid-template-columns: min-content repeat(8, auto)
}

.stiff-column-min-left-10 {
	grid-template-columns: min-content repeat(9, auto)
}

.stiff-column-min-right-1 {
	grid-template-columns: min-content
}

.stiff-column-min-right-2 {
	grid-template-columns: 1fr min-content
}

.stiff-column-min-right-3 {
	grid-template-columns: repeat(2, auto) min-content
}

.stiff-column-min-right-4 {
	grid-template-columns: repeat(3, auto) min-content
}

.stiff-column-min-right-5 {
	grid-template-columns: repeat(4, auto) min-content
}

.stiff-column-min-right-6 {
	grid-template-columns: repeat(5, auto) min-content
}

.stiff-column-min-right-7 {
	grid-template-columns: repeat(6, auto) min-content
}

.stiff-column-min-right-8 {
	grid-template-columns: repeat(7, auto) min-content
}

.stiff-column-min-right-9 {
	grid-template-columns: repeat(8, auto) min-content
}

.stiff-column-min-right-10 {
	grid-template-columns: repeat(9, auto) min-content
}

.page-rows-min {
	grid-template-rows: min-content
}

.page-column-1 {
	grid-template-columns: auto
}

.page-column-2 {
	grid-template-columns: auto auto
}

.page-column-3 {
	grid-template-columns: auto auto auto
}

.page-column-4 {
	grid-template-columns: auto auto auto auto
}

.page-column-5 {
	grid-template-columns: auto auto auto auto auto
}

.page-column-6 {
	grid-template-columns: auto auto auto auto auto auto
}

.page-column-7 {
	grid-template-columns: auto auto auto auto auto auto auto
}

.page-column-fr-1 {
	grid-template-columns: 1fr
}

.page-column-fr-2 {
	grid-template-columns: 1fr 1fr
}

.page-column-fr-3 {
	grid-template-columns: 1fr 1fr 1fr
}

.page-column-fr-4 {
	grid-template-columns: 1fr 1fr 1fr 1fr
}

.page-column-fr-5 {
	grid-template-columns: 1fr 1fr 1fr 1fr 1fr
}

.page-column-fr-6 {
	grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr
}

.page-column-fr-7 {
	grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr 1fr
}

.page-column-min-left-1 {
	grid-template-columns: min-content
}

.page-column-min-left-2 {
	grid-template-columns: min-content 1fr
}

.page-column-min-left-3 {
	grid-template-columns: min-content auto auto
}

.page-column-min-left-4 {
	grid-template-columns: min-content auto auto auto
}

.page-column-min-left-5 {
	grid-template-columns: min-content auto auto auto auto
}

.page-column-min-left-6 {
	grid-template-columns: min-content auto auto auto auto auto
}

.page-column-min-left-7 {
	grid-template-columns: min-content auto auto auto auto auto auto
}

.page-column-min-right-1 {
	grid-template-columns: min-content
}

.page-column-min-right-2 {
	grid-template-columns: auto min-content
}

.page-column-min-right-3 {
	grid-template-columns: auto auto min-content
}

.page-column-min-right-4 {
	grid-template-columns: auto auto auto min-content
}

.page-column-min-right-5 {
	grid-template-columns: auto auto auto auto min-content
}

.page-column-min-right-6 {
	grid-template-columns: auto auto auto auto auto min-content
}

.page-column-min-right-7 {
	grid-template-columns: auto auto auto auto auto auto min-content
}

.span-1 {
	grid-column: span 1
}

.span-2 {
	grid-column: span 2
}

.span-3 {
	grid-column: span 3
}

.span-4 {
	grid-column: span 4
}

.span-5 {
	grid-column: span 5
}

.span-6 {
	grid-column: span 6
}

.span-7 {
	grid-column: span 7
}

.span-all {
	grid-column-start: 1;
	grid-column-end: -1
}

.table-grid {
	display: grid
}

.grid-fill-columns {
	grid-auto-flow: column
}

.table-column-1 {
	grid-template-columns: 1fr
}

.table-column-2 {
	grid-template-columns: repeat(2, auto)
}

.table-column-3 {
	grid-template-columns: repeat(3, auto)
}

.table-column-4 {
	grid-template-columns: repeat(4, auto)
}

.table-column-5 {
	grid-template-columns: repeat(5, auto)
}

.table-column-6 {
	grid-template-columns: repeat(6, auto)
}

.table-column-7 {
	grid-template-columns: repeat(7, auto)
}

.table-column-8 {
	grid-template-columns: repeat(8, auto)
}

.table-column-9 {
	grid-template-columns: repeat(9, auto)
}

.table-column-10 {
	grid-template-columns: repeat(10, auto)
}

.table-column-min-left-1 {
	grid-template-columns: min-content
}

.table-column-min-left-2 {
	grid-template-columns: min-content 1fr
}

.table-column-min-left-3 {
	grid-template-columns: min-content repeat(2, auto)
}

.table-column-min-left-4 {
	grid-template-columns: min-content repeat(3, auto)
}

.table-column-min-left-5 {
	grid-template-columns: min-content repeat(4, auto)
}

.table-column-min-left-6 {
	grid-template-columns: min-content repeat(5, auto)
}

.table-column-min-left-7 {
	grid-template-columns: min-content repeat(6, auto)
}

.table-column-min-left-8 {
	grid-template-columns: min-content repeat(7, auto)
}

.table-column-min-left-9 {
	grid-template-columns: min-content repeat(8, auto)
}

.table-column-min-left-10 {
	grid-template-columns: min-content repeat(9, auto)
}

.table-span-1 {
	grid-column: span 1
}

.table-span-2 {
	grid-column: span 2
}

.table-span-3 {
	grid-column: span 3
}

.table-span-4 {
	grid-column: span 4
}

.table-span-5 {
	grid-column: span 5
}

.table-span-6 {
	grid-column: span 6
}

.table-span-7 {
	grid-column: span 7
}

.table-span-8 {
	grid-column: span 8
}

.table-span-9 {
	grid-column: span 9
}

.table-span-10 {
	grid-column: span 10
}

.table-break-early-column-1 {
	grid-template-columns: 1fr
}

.table-break-early-column-2 {
	grid-template-columns: repeat(2, auto)
}

.table-break-early-column-3 {
	grid-template-columns: repeat(3, auto)
}

.table-break-early-column-4 {
	grid-template-columns: repeat(4, auto)
}

.table-break-early-column-5 {
	grid-template-columns: repeat(5, auto)
}

.table-break-early-column-6 {
	grid-template-columns: repeat(6, auto)
}

.table-break-early-column-7 {
	grid-template-columns: repeat(7, auto)
}

.table-break-early-column-8 {
	grid-template-columns: repeat(8, auto)
}

.table-break-early-column-9 {
	grid-template-columns: repeat(9, auto)
}

.table-break-early-column-10 {
	grid-template-columns: repeat(10, auto)
}

.table-break-early-column-min-left-1 {
	grid-template-columns: min-content
}

.table-break-early-column-min-left-2 {
	grid-template-columns: min-content 1fr
}

.table-break-early-column-min-left-3 {
	grid-template-columns: min-content repeat(2, auto)
}

.table-break-early-column-min-left-4 {
	grid-template-columns: min-content repeat(3, auto)
}

.table-break-early-column-min-left-5 {
	grid-template-columns: min-content repeat(4, auto)
}

.table-break-early-column-min-left-6 {
	grid-template-columns: min-content repeat(5, auto)
}

.table-break-early-column-min-left-7 {
	grid-template-columns: min-content repeat(6, auto)
}

.table-break-early-column-min-left-8 {
	grid-template-columns: min-content repeat(7, auto)
}

.table-break-early-column-min-left-9 {
	grid-template-columns: min-content repeat(8, auto)
}

.table-break-early-column-min-left-10 {
	grid-template-columns: min-content repeat(9, auto)
}

.table-break-early-span-1 {
	grid-column: span 1
}

.table-break-early-span-2 {
	grid-column: span 2
}

.table-break-early-span-3 {
	grid-column: span 3
}

.table-break-early-span-4 {
	grid-column: span 4
}

.table-break-early-span-5 {
	grid-column: span 5
}

.table-break-early-span-6 {
	grid-column: span 6
}

.table-break-early-span-7 {
	grid-column: span 7
}

.table-break-early-span-8 {
	grid-column: span 8
}

.table-break-early-span-9 {
	grid-column: span 9
}

.table-break-early-span-10 {
	grid-column: span 10
}

.table-break-early-legend,
.table-legend {
	padding: 8px
}

.table-break-early-legend-mobile,
.table-legend-mobile {
	display: none;
	padding: 8px 4px
}

.table-cell,
.table-cell-no-border {
	display: flex;
	align-items: center;
	justify-content: center;
	overflow-wrap: break-word;
	word-wrap: break-word;
	-ms-word-break: break-all;
	padding: 8px 4px
}

.table-cell-padded {
	display: flex;
	align-items: center;
	justify-content: center;
	overflow-wrap: break-word;
	word-wrap: break-word;
	-ms-word-break: break-all;
	padding: 16px 8px !important
}

.table-cell-small-no-border {
	justify-content: center;
	overflow-wrap: break-word;
	word-wrap: break-word;
	-ms-word-break: break-all;
	padding: 4px
}

p {
	overflow-wrap: break-all;
	word-wrap: break-all;
	-ms-word-break: break-all
}

.toggle-button-info {
	cursor: pointer
}

.toggle-button-info:not(.closed)::after {
	content: "I";
	cursor:pointer
}

.toggle-button-info.closed::after {
	content: "i";
	cursor:pointer
}

.toggle-button-drop {
	cursor: pointer
}

.toggle-button-drop:not(.closed)::before {
	content: "\25b2\00a0";
	cursor: pointer
}

.toggle-button-drop.closed::before {
	content: "\25bc\00a0";
	cursor: pointer
}

.toggle-target.closed {
	opacity: 0;
	height: 0;
	padding: 0;
	margin: 0;
	position: absolute;
	z-index: -1;
	transition: opacity 0s ease, transform 0s ease;
	transform: scaleY(0);
	transform-origin: top
}

.toggle-target {
	opacity: 1;
	transition: opacity .5s ease, transform .15s ease;
	transform: scaleY(1);
	transform-origin: top
}

.page-button-fill {
	padding: 8px;
	width: 100%;
	height: 100%;
	cursor: pointer
}

.page-text-input-fill {
	width: 100%;
	padding: 8px;
	font-size: inherit;
	display: inline
}

.page-text-input {
	padding: 8px;
	font-size: inherit
}

.page-drop-down-fill,
.page-drop-down-fill-dark {
	width: 100%;
	padding: 8px
}

.page-image {
	width: 100%;
	max-width: 700px;
	max-height: 700px;
	object-fit: contain
}

.textAreaWrapper {
	position: relative;
	float: left
}

.textAreaCounter {
	position: absolute;
	right: 15px;
	bottom: 0;
	font-size: 12px
}

.page-text-area-fill {
	width: 100%;
	height: 100%;
	font-size: inherit;
	padding: 4px
}

@media screen and (max-width:990px) {
	.font-shrink-early {
		font-size: 12px!important
	}
	#center-bar {
		grid-row: 2/3;
		justify-self: center;
		width: 720px;
		transition: .25s ease
	}
	#center-bar.both-bars {
		grid-column: 1/2
	}
	#center-bar.neither-bars {
		grid-column: 1/2
	}
	#center-bar.no-left-bar {
		grid-column: 1/2
	}
	#center-bar.no-right-bar {
		grid-column: 1/2
	}
	#center-bar.menu-open {
		width: 100%;
		transition: .25s ease
	}
	.page-column-4 {
		grid-template-columns: auto auto auto
	}
	.page-column-fr-4 {
		grid-template-columns: 1fr 1fr 1fr
	}
	.page-column-min-left-4 {
		grid-template-columns: min-content auto auto
	}
	.page-column-min-right-4 {
		grid-template-columns: auto auto min-content
	}
	.span-4 {
		grid-column: span 3
	}
	.page-column-5 {
		grid-template-columns: auto auto auto
	}
	.page-column-fr-5 {
		grid-template-columns: 1fr 1fr 1fr
	}
	.page-column-min-left-5 {
		grid-template-columns: min-content auto auto
	}
	.page-column-min-right-5 {
		grid-template-columns: auto auto min-content
	}
	.span-5 {
		grid-column: span 3
	}
}

@media screen and (max-width:720px) {
	#center-bar {
		width: 100%
	}
	.page-column-3 {
		grid-template-columns: auto auto auto
	}
	.page-column-fr-3 {
		grid-template-columns: 1fr 1fr 1fr
	}
	.page-column-min-left-3 {
		grid-template-columns: min-content auto auto
	}
	.page-column-min-right-3 {
		grid-template-columns: auto auto min-content
	}
	.span-3 {
		grid-column: span 3
	}
	.page-column-4 {
		grid-template-columns: auto auto auto
	}
	.page-column-fr-4 {
		grid-template-columns: 1fr 1fr 1fr
	}
	.page-column-min-left-4 {
		grid-template-columns: min-content auto auto
	}
	.page-column-min-right-4 {
		grid-template-columns: auto auto min-content
	}
	.span-4 {
		grid-column: span 3
	}
	.page-column-5 {
		grid-template-columns: auto auto auto
	}
	.page-column-fr-5 {
		grid-template-columns: 1fr 1fr 1fr
	}
	.page-column-min-left-5 {
		grid-template-columns: min-content auto auto
	}
	.page-column-min-right-5 {
		grid-template-columns: auto auto min-content
	}
	.span-5 {
		grid-column: span 3
	}
	.page-column-6 {
		grid-template-columns: auto auto auto
	}
	.page-column-fr-6 {
		grid-template-columns: 1fr 1fr 1fr
	}
	.page-column-min-left-6 {
		grid-template-columns: min-content auto auto
	}
	.page-column-min-right-6 {
		grid-template-columns: auto auto min-content
	}
	.span-6 {
		grid-column: span 3
	}
	.page-column-7 {
		grid-template-columns: auto auto auto auto
	}
	.page-column-fr-7 {
		grid-template-columns: 1fr 1fr 1fr 1fr
	}
	.page-column-min-left-7 {
		grid-template-columns: min-content auto auto auto
	}
	.page-column-min-right-7 {
		grid-template-columns: auto auto auto min-content
	}
	.span-7 {
		grid-column: span 4
	}
	.page-image {
		max-width: calc(100% -16px);
		max-height: calc(100% -16px)
	}
	.table-break-early-column-2 {
		grid-template-columns: auto
	}
	.table-break-early-column-3 {
		grid-template-columns: min-content auto
	}
	.table-break-early-column-4 {
		grid-template-columns: min-content auto
	}
	.table-break-early-column-5 {
		grid-template-columns: min-content auto
	}
	.table-break-early-column-6 {
		grid-template-columns: min-content auto
	}
	.table-break-early-column-7 {
		grid-template-columns: min-content auto
	}
	.table-break-early-column-8 {
		grid-template-columns: min-content auto
	}
	.table-break-early-column-9 {
		grid-template-columns: min-content auto
	}
	.table-break-early-column-10 {
		grid-template-columns: min-content auto
	}
	.table-break-early-column-min-left-2 {
		grid-template-columns: auto
	}
	.table-break-early-column-min-left-3 {
		grid-template-columns: min-content auto
	}
	.table-break-early-column-min-left-4 {
		grid-template-columns: min-content auto
	}
	.table-break-early-column-min-left-5 {
		grid-template-columns: min-content auto
	}
	.table-break-early-column-min-left-6 {
		grid-template-columns: min-content auto
	}
	.table-break-early-column-min-left-7 {
		grid-template-columns: min-content auto
	}
	.table-break-early-column-min-left-8 {
		grid-template-columns: min-content auto
	}
	.table-break-early-column-min-left-9 {
		grid-template-columns: min-content auto
	}
	.table-break-early-column-min-left-10 {
		grid-template-columns: min-content auto
	}
	.table-break-early-span-2 {
		grid-column: span 1
	}
	.table-break-early-span-3 {
		grid-column: span 2
	}
	.table-break-early-span-4 {
		grid-column: span 2
	}
	.table-break-early-span-5 {
		grid-column: span 2
	}
	.table-break-early-span-6 {
		grid-column: span 2
	}
	.table-break-early-span-7 {
		grid-column: span 2
	}
	.table-break-early-span-8 {
		grid-column: span 2
	}
	.table-break-early-span-9 {
		grid-column: span 2
	}
	.table-break-early-span-10 {
		grid-column: span 2
	}
	.table-break-early-legend {
		display: none
	}
	.table-break-early-legend-mobile {
		display: initial
	}
}

@media screen and (max-width:500px) {
	.font-large:not(.anti-shrink) {
		font-size: 14px!important
	}
	.font-larger:not(.anti-shrink) {
		font-size: 16px!important
	}
	.font-giant:not(.anti-shrink) {
		font-size: 24px!important
	}
	.grid-fill-columns {
		grid-auto-flow: unset
	}
	.page-column-2 {
		grid-template-columns: auto
	}
	.page-column-fr-2 {
		grid-template-columns: 1fr
	}
	.page-column-min-left-2 {
		grid-template-columns: auto
	}
	.span-2 {
		grid-column: span 1
	}
	.page-column-3 {
		grid-template-columns: auto
	}
	.page-column-fr-3 {
		grid-template-columns: 1fr
	}
	.page-column-min-left-3 {
		grid-template-columns: auto
	}
	.span-3 {
		grid-column: span 1
	}
	.page-column-4 {
		grid-template-columns: auto
	}
	.page-column-fr-4 {
		grid-template-columns: 1fr
	}
	.page-column-min-left-4 {
		grid-template-columns: auto
	}
	.span-4 {
		grid-column: span 1
	}
	.page-column-5 {
		grid-template-columns: auto
	}
	.page-column-fr-5 {
		grid-template-columns: 1fr
	}
	.page-column-min-left-5 {
		grid-template-columns: auto
	}
	.span-5 {
		grid-column: span 1
	}
	.page-column-6 {
		grid-template-columns: auto
	}
	.page-column-fr-6 {
		grid-template-columns: 1fr
	}
	.page-column-min-left-6 {
		grid-template-columns: auto
	}
	.span-6 {
		grid-column: span 1
	}
	.page-column-7 {
		grid-template-columns: auto
	}
	.page-column-fr-7 {
		grid-template-columns: 1fr
	}
	.page-column-min-left-7 {
		grid-template-columns: auto
	}
	.span-7 {
		grid-column: span 1
	}
	.table-column-2 {
		grid-template-columns: auto
	}
	.table-column-3 {
		grid-template-columns: min-content auto
	}
	.table-column-4 {
		grid-template-columns: min-content auto
	}
	.table-column-5 {
		grid-template-columns: min-content auto
	}
	.table-column-6 {
		grid-template-columns: min-content auto
	}
	.table-column-7 {
		grid-template-columns: min-content auto
	}
	.table-column-8 {
		grid-template-columns: min-content auto
	}
	.table-column-9 {
		grid-template-columns: min-content auto
	}
	.table-column-10 {
		grid-template-columns: min-content auto
	}
	.table-column-min-left-2 {
		grid-template-columns: auto
	}
	.table-column-min-left-3 {
		grid-template-columns: min-content auto
	}
	.table-column-min-left-4 {
		grid-template-columns: min-content auto
	}
	.table-column-min-left-5 {
		grid-template-columns: min-content auto
	}
	.table-column-min-left-6 {
		grid-template-columns: min-content auto
	}
	.table-column-min-left-7 {
		grid-template-columns: min-content auto
	}
	.table-column-min-left-8 {
		grid-template-columns: min-content auto
	}
	.table-column-min-left-9 {
		grid-template-columns: min-content auto
	}
	.table-column-min-left-10 {
		grid-template-columns: min-content auto
	}
	.table-span-2 {
		grid-column: span 1
	}
	.table-span-3 {
		grid-column: span 2
	}
	.table-span-4 {
		grid-column: span 2
	}
	.table-span-5 {
		grid-column: span 2
	}
	.table-span-6 {
		grid-column: span 2
	}
	.table-span-7 {
		grid-column: span 2
	}
	.table-span-8 {
		grid-column: span 2
	}
	.table-span-9 {
		grid-column: span 2
	}
	.table-span-10 {
		grid-column: span 2
	}
	.table-legend {
		display: none
	}
	.table-legend-mobile {
		display: initial
	}
}
.toggle-button-info {
	cursor: pointer
}

.toggle-button-info:not(.closed)::after {
	content: "I";
	cursor:pointer
}

.toggle-button-info.closed::after {
	content: "i";
	cursor:pointer
}

.toggle-button-drop {
	cursor: pointer
}

.toggle-button-drop:not(.closed)::before {
	content: "\25b2\00a0";
	cursor: pointer
}

.toggle-button-drop.closed::before {
	content: "\25bc\00a0";
	cursor: pointer
}

.toggle-target.closed {
	opacity: 0;
	height: 0;
	padding: 0;
	margin: 0;
	position: absolute;
	z-index: -1;
	transition: opacity 0s ease, transform 0s ease;
	transform: scaleY(0);
	transform-origin: top
}

.toggle-target {
	opacity: 1;
	transition: opacity .5s ease, transform .15s ease;
	transform: scaleY(1);
	transform-origin: top
}

.text-left {
	text-align: left;justify-content: left;
}

.text-right {
	text-align: right;justify-content: right;
}

.text-center {
	text-align: center;justify-content: center;
}
</style>

{include file="./BattlePageResources/mf/style.tpl"}

<div class="page-box" id="battle_page">
	<div class="page-title subHeader">
        {$battle_type} Battleground <span class="toggle-button-info closed" data-target=".battle-information"></span>
    </div>

	<form action="" id="battle_form" method="post" class="page-content">
		<div id="summary" summary="no" style="display:none;"></div>

		<!--DSR INFO-->
		<div class="page-sub-title-top toggle-target closed battle-information" id="battle-information-title">
                Damage by Survivability Rating (DSR)
		</div>

		<div class="toggle-target closed battle-information" id="battle-information-content">
			Your DSR: <b>{base_convert(floor(sqrt($owner['DSR']+$rng+4)), 10, 9)}</b>
			<br>
			Your Team's DSR: <b>{base_convert(floor(sqrt($friendlyDSR+$rng+4)), 10, 9)}</b>
			<br>
			Opponent Team's DSR: <b>{base_convert(floor(sqrt($opponentDSR+$rng+4)), 10, 9)}</b>
			<br>
			{if $cfhRange1 != 'N/A'}
				CFH Range: <b>{base_convert(floor(sqrt($cfhRange1+$rng+4)), 10, 9)}</b> to {base_convert(floor(sqrt($cfhRange2+$rng+4)), 10, 9)}</b>
				<br>
			{else}
				CFH Range: N/A
				<br>
			{/if}
			<br>
		</div>
		<!--DSR INFO-->



		<!--turn timer and counter-->
		<div class="page-sub-title-top stiff-grid stiff-column-2 page-grid-justify-stretch tableColumns" id="battle-clock">
			<div class="text-left font-small">
				{$time}
			</div>
			<div class="text-right">
				--<span id="turn_timer_box">(<span title="round timer" id="turn_timer">{$turn_timer - time()}</span>s)</span> - #<span title="round counter" class="turn_counter">{$turn_counter + 1}</span>
			</div>
		</div>
		<!--turn timer and counter-->



		<!--battle field-->
		<div id="battle_field" class="page-grid page-grid-justify-stretch">
			{include file="./BattlePageResources/mf/battlefield.tpl"}
		</div>
		<!--battle field-->



		<!--control_panel-->
		<div id="control-panel">

			<!--control tabs-->
			<div id="tabs">
				<div id="jutsus-button" class="tab-button" onclick="tabClick('jutsus')">
					Jutsus
				</div>
				<div id="weapons-button" class="tab-button" onclick="tabClick('weapons')">
					Weapons
				</div>
				<div id="items-button" class="tab-button" onclick="tabClick('items')">
					Items
				</div>
				<div id="battle_log-button" class="tab-button {if count($battle_log) < 1}empty{/if}" onclick="tabClick('battle_log')">
					Battle Log
				</div>
			</div>
			<!--control tabs-->

			<!-- control options -->
			<div id="controls">

				<!-- jutsu options -->
				<div id="jutsus_wrapper" class="plain-box">
					<div id="jutsus" class="tab-content  fancy-box">
						{include file="./BattlePageResources/mf/jutsus.tpl"}
					</div>
				</div>

				<!-- weapon options -->
				<div id="weapons_wrapper" class="plain-box">
					<div id="weapons" class="tab-content  fancy-box">
						{include file="./BattlePageResources/mf/weapons.tpl"}
					</div>
				</div>

				<!-- item options -->
				<div id="items_wrapper" class="plain-box">
					<div id="items" class="tab-content fancy-box">
						{include file="./BattlePageResources/mf/items.tpl"}
					</div>
				</div>

				<!--this is the user battle log box-->
				<div id="battle_log" class="tab-content dark-plain-box fancy-box page-box">
					{include file="./BattlePageResources/mf/battlelog.tpl"}
				</div>
                <!--this is the user battle log box-->

			</div>
			<!-- control options -->
			
		</div>
		<!--control_panel-->

		<!--submit button-->
		{if ((is_numeric($stunned) && $stunned > 0) || $stunned === true) || 
			$owner['waiting_for_next_turn'] === true}
			{$no_cfh = true}
			{$no_flee = true}
		{/if}

		{if $owner['no_cfh'] == true || $cfhRange1 == 'N/A'}
			{$no_cfh = true}
		{/if}

		{if $owner['attacker'] == true}
			{$no_flee = true}
		{/if}

		<div class="submit-button-wrapper">
			<button class="submit-button{if $no_cfh && $no_flee} span-3{else if $no_cfh || $no_flee} span-2{/if}" 
					id="button" name="button" data-code="{$link_code}">
				{if (is_numeric($stunned) && $stunned > 0) || $stunned === true}
				    Stunned...
				{elseif $owner['waiting_for_next_turn'] === true}
				    Waiting...
				{else}
					GO!
				{/if}
			</button>

			{if !$no_cfh}
				<button class="cfh-button" id="cfh" name="button" data-code="{$link_code}" title="Call for Help"
						onclick="return confirm('Are you sure you want to Call for help?')">
					Help!
				</button>
			{/if}

			{if !$no_flee}
				<button class="flee-button" id="flee" name="button" data-code="{$link_code}" title="Try to Flee"
						onclick="return confirm('Are you sure you want to Try to Flee?')">
					Flee!
				</button>
			{/if}
		</div>
	</form>
</div>

{if in_array($_SESSION['uid'], [2015883, 2486, 1986872, 2001381])}
	<div>
		<pre>
			{var_dump($damage_multiplier)}
		</pre>

		<br/>
		<br/>

		{$flee_1}    

		<br/>

		{$flee_2}    

		<br/>

		{$flee_3}  
	</div>
{/if}

{literal}
<!--{if $owner['name'] == 'Koala'}
<div>
	{$this_dump}

	{$users_dump}
	
	{$kill_button}
</div>
{/if}-->
{/literal}

{if $owner['name'] == 'Koala'}
	{debug}
{/if}