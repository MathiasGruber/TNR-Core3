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
	</script>
{/literal}


<script type="text/javascript" src="files/javascript/BattlePageNew.js" id="BattlePageJS"></script>

{include file="./BattlePageResources/mf/style.tpl"}

<div class="page-box" id="battle_page">
	<div class="page-title">
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
		<div class="page-sub-title-top stiff-grid stiff-column-2 page-grid-justify-stretch" id="battle-clock">
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