{if $userdata['key_bindings_status'] == 1}
    <script id="key_bindings" type="text/javascript">

        /*MOUSE TRAP*/
        {literal}
        (function(p,t,h){function u(a,b,d){a.addEventListener?a.addEventListener(b,d,!1):a.attachEvent("on"+b,d)}function y(a){if("keypress"==a.type){var b=String.fromCharCode(a.which);a.shiftKey||(b=b.toLowerCase());return b}return m[a.which]?m[a.which]:q[a.which]?q[a.which]:String.fromCharCode(a.which)       .toLowerCase()}function E(a){var b=[];a.shiftKey&&b.push("shift");a.altKey&&b.push("alt");a.ctrlKey&&b.push("ctrl");a.metaKey&&b.push("meta");return b}function v(a){return"shift"==a||"ctrl"==a||"alt"==a||"meta"==a}function z(a,b){var d,e=[];var c=a;"+"===c?c=["+"]:(c=c.replace(/\+{2}/g,"+plus"),    c=c.split("+"));for(d=0;d<c.length;++d){var k=c[d];A[k]&&(k=A[k]);b&&"keypress"!=b&&B[k]&&(k=B[k],e.push("shift"));v(k)&&e.push(k)}c=k;d=b;if(!d){if(!n){n={};for(var h in m)95<h&&112>h||m.hasOwnProperty(h)&&(n[m[h]]=h)}d=n[c]?"keydown":"keypress"}"keypress"==d&&e.length&&(d="keydown");return{key:k,      modifiers:e,action:d}}function C(a,b){return null===a||a===t?!1:a===b?!0:C(a.parentNode,b)}function e(a){function b(a){a=a||{};var b=!1,l;for(l in n)a[l]?b=!0:n[l]=0;b||(w=!1)}function d(a,b,r,g,F,e){var l,D=[],h=r.type;if(!f._callbacks[a])return[];"keyup"==h&&v(a)&&(b=[a]);for(l=0;l<f._callbacks     [a].length;++l){var d=f._callbacks[a][l];if((g||!d.seq||n[d.seq]==d.level)&&h==d.action){var c;(c="keypress"==h&&!r.metaKey&&!r.ctrlKey)||(c=d.modifiers,c=b.sort().join(",")===c.sort().join(","));c&&(c=g&&d.seq==g&&d.level==e,(!g&&d.combo==F||c)&&f._callbacks[a].splice(l,1),D.push(d))}}return D}      function h(a,b,d,g){f.stopCallback(b,b.target||b.srcElement,d,g)||!1!==a(b,d)||(b.preventDefault?b.preventDefault():b.returnValue=!1,b.stopPropagation?b.stopPropagation():b.cancelBubble=!0)}function c(a){"number"!==typeof a.which&&(a.which=a.keyCode);var b=y(a);b&&("keyup"==a.type&&x===b?      x=!1:f.handleKey(b,E(a),a))}function k(a,d,r,g){function l(d){return function(){w=d;++n[a];clearTimeout(p);p=setTimeout(b,1E3)}}function e(d){h(r,d,a);"keyup"!==g&&(x=y(d));setTimeout(b,10)}for(var c=n[a]=0;c<d.length;++c){var f=c+1===d.length?e:l(g||z(d[c+1]).action);m(d[c],f,g,a,c)}}function m(a,     b,c,g,e){f._directMap[a+":"+c]=b;a=a.replace(/\s+/g," ");var h=a.split(" ");1<h.length?k(a,h,b,c):(c=z(a,c),f._callbacks[c.key]=f._callbacks[c.key]||[],d(c.key,c.modifiers,{type:c.action},g,a,e),f._callbacks[c.key][g?"unshift":"push"]({callback:b,modifiers:c.modifiers,action:c.action,seq:g,level:e,       combo:a}))}var f=this;a=a||t;if(!(f instanceof e))return new e(a);f.target=a;f._callbacks={};f._directMap={};var n={},p,x=!1,q=!1,w=!1;f._handleKey=function(a,c,e){var g=d(a,c,e),f;c={};var l=0,k=!1;for(f=0;f<g.length;++f)g[f].seq&&(l=Math.max(l,g[f].level));for(f=0;f<g.length;++f)g[f].seq?g[f]    .level==l&&(k=!0,c[g[f].seq]=1,h(g[f].callback,e,g[f].combo,g[f].seq)):k||h(g[f].callback,e,g[f].combo);g="keypress"==e.type&&q;e.type!=w||v(a)||g||b(c);q=k&&"keydown"==e.type};f._bindMultiple=function(a,b,c){for(var d=0;d<a.length;++d)m(a[d],b,c)};u(a,"keypress",c);u(a,"keydown",c);u(a,"keyup",c)}    if(p){var m={8:"backspace",9:"tab",13:"enter",16:"shift",17:"ctrl",18:"alt",20:"capslock",27:"esc",32:"space",33:"pageup",34:"pagedown",35:"end",36:"home",37:"left",38:"up",39:"right",40:"down",45:"ins",46:"del",91:"meta",93:"meta",224:"meta"},q={106:"*",107:"+",109:"-",110:".",111:"/",186:";",       187:"=",188:",",189:"-",190:".",191:"/",192:"`",219:"[",220:"\\",221:"]",222:"'"},B={"~":"`","!":"1","@":"2","#":"3",$:"4","%":"5","^":"6","&":"7","*":"8","(":"9",")":"0",_:"-","+":"=",":":";",'"':"'","<":",",">":".","?":"/","|":"\\"},A={option:"alt",command:"meta","return":"enter",escape:"esc",    plus:"+",mod:/Mac|iPod|iPhone|iPad/.test(navigator.platform)?"meta":"ctrl"},n;for(h=1;20>h;++h)m[111+h]="f"+h;for(h=0;9>=h;++h)m[h+96]=h.toString();e.prototype.bind=function(a,b,d){a=a instanceof Array?a:[a];this._bindMultiple.call(this,a,b,d);return this};e.prototype.unbind=function(a,b){return     this.bind.call(this,a,function(){},b)};e.prototype.trigger=function(a,b){if(this._directMap[a+":"+b])this._directMap[a+":"+b]({},a);return this};e.prototype.reset=function(){this._callbacks={};this._directMap={};return this};e.prototype.stopCallback=function(a,b){return-1<(" "+b.className+" ")        .indexOf(" mousetrap ")||C(b,this.target)?!1:"INPUT"==b.tagName||"SELECT"==b.tagName||"TEXTAREA"==b.tagName||b.isContentEditable};e.prototype.handleKey=function(){return this._handleKey.apply(this,arguments)};e.addKeycodes=function(a){for(var b in a)a.hasOwnProperty(b)&&(m[b]=a[b]);n=null};     e.init=function(){var a=e(t),b;for(b in a)"_"!==b.charAt(0)&&(e[b]=function(b){return function(){return a[b].apply(a,arguments)}}(b))};e.init();p.Mousetrap=e;"undefined"!==typeof module&&module.exports&&(module.exports=e);"function"===typeof define&&define.amd&&define(function(){return e})}})       ("undefined"!==typeof window?window:null,"undefined"!==typeof window?document:null);
        {/literal}
        /*MOUSE TRAP*/
        
        function resetMouseTrap(){ Mousetrap.reset(); }

        function prepBinding(string){ return string.toLowerCase().split(' ').join('').replace(',','komma').replace('comma',',').split('komma') }

        function bindKeys()
        {
            var bindings=$.parseJSON('{$userdata['key_bindings']}');
            Mousetrap.bind(prepBinding(bindings['move-north']),function(e){ if(!$(".jconfirm").length){ loadPage(null,'all','doTravel:N'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['move-east']),function(e){ if(!$(".jconfirm").length){ loadPage(null,'all','doTravel:E'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['move-south']),function(e){ if(!$(".jconfirm").length){ loadPage(null,'all','doTravel:S'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['move-west']),function(e){ if(!$(".jconfirm").length){ loadPage(null,'all','doTravel:W'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['move-north-west']),function(e){ if(!$(".jconfirm").length){ loadPage(null,'all','doTravel:NW'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['move-north-east']),function(e){ if(!$(".jconfirm").length){ loadPage(null,'all','doTravel:NE'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['move-south-west']),function(e){ if(!$(".jconfirm").length){ loadPage(null,'all','doTravel:SW'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['move-south-east']),function(e){ if(!$(".jconfirm").length){ loadPage(null,'all','doTravel:SE'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-anbu']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=95'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-clan']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=94'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-combat']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=50'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-home-inventory']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=23&act=inventory'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-inbox']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=3'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-inventory']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=11'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-jutsu']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=12'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-marriage']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=17'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-missions']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=121'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-occupation']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=36'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-preferences']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=4'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-profession']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=86'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-profile']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=2'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-quests']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=120'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-rob']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=49'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-scout']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=30'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-travel']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=8'); }e.preventDefault() });

            if(bindings['link-territory'] != undefined)
                Mousetrap.bind(prepBinding(bindings['link-territory']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=8&map=territory'); }e.preventDefault() });
            else
                Mousetrap.bind(prepBinding("shift+k"),function(e){ if(!$(".jconfirm").length){ loadPage('?id=8&map=territory'); }e.preventDefault() });

            Mousetrap.bind(prepBinding(bindings['link-global-trade']),function(e){ if(!$(".jconfirm").length){ loadPage('?id=68'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-sleep']),function(e){ if(!$(".jconfirm").length){ loadPage(null,'all','doSleep:sleep'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-wakeup']),function(e){ if(!$(".jconfirm").length){ loadPage(null,'all','doSleep:wakeup'); }e.preventDefault() });

            //alt bindings for syndicate
            Mousetrap.bind(prepBinding(bindings['link-tavern']),function(e){ if(!$(".jconfirm").length){ loadPage('?id={if $userdata['village'] != 'Syndicate'}24{else}46{/if}'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-missions']),function(e){ if(!$(".jconfirm").length){ loadPage('?id={if $userdata['village'] != 'Syndicate'}121{else}122{/if}'); }e.preventDefault() });
            Mousetrap.bind(prepBinding(bindings['link-bank']), function(e) { {if $userdata['village'] != 'Syndicate'} if(!$(".jconfirm").length){ loadPage('?id=20');    }e.preventDefault(); {else} if(!$(".jconfirm").length){ loadPage('?id=45');  }e.preventDefault(); {/if} });
            Mousetrap.bind(prepBinding(bindings['link-errands']), function(e){ {if $userdata['village'] != 'Syndicate'} if(!$(".jconfirm").length){ loadPage('?id=21'); {else}  if(!$(".jconfirm").length){ loadPage('?id=22'); {/if}  }e.preventDefault(); });
            Mousetrap.bind(prepBinding(bindings['link-ramen']), function(e){ {if $userdata['village'] != 'Syndicate' && $userdata['village'] == $userdata['location']} if(!$(".jconfirm").length){ loadPage('?id=25'); {else if $userdata['village'] != 'Syndicate' && $userdata['village'] != $userdata['location']    } if(!$(".jconfirm").length){ loadPage('?id=57'); {else} if(!$(".jconfirm").length){ loadPage('?id=97'); {/if}  }e.preventDefault(); });

            //alt bindings for rank
            Mousetrap.bind(prepBinding(bindings['link-training']), function(e){ {if $userdata['rank_id'] == 1} if(!$(".jconfirm").length){ loadPage('?id=18'); {else if $userdata['rank_id'] == 2} if(!$(".jconfirm").length){ loadPage('?id=29'); {else if $userdata['rank_id'] >= 3} if(!$(".jconfirm").length){ loadPage('?id=39'); {else} console.log('how do you have a rank id of: {$userdata['rank_id']}?'); {/if}  }e.preventDefault(); });

            //new bindings
            Mousetrap.bind(prepBinding(bindings['link-ramen-full-heal']), function(e){ {if $userdata['village'] != 'Syndicate' && $userdata['village'] == $userdata['location']} if(!$(".jconfirm").length){ loadPage('?id=25&act=order&orderID=8'); {else if $userdata['village'] != 'Syndicate' && $userdata['village'] != $userdata['location']    } if(!$(".jconfirm").length){ loadPage('?id=57'); {else} if(!$(".jconfirm").length){ loadPage('?id=97&act=order&orderID=8'); {/if}  }e.preventDefault(); });

            //mission each rank
            {if $userdata['rank_id'] >= 5}
                Mousetrap.bind(prepBinding(bindings['link-missions-a']),function(e){ if(!$(".jconfirm").length){ loadPage('?id={if $userdata['village'] != 'Syndicate'}121{else}122{/if}&qid=a'); }e.preventDefault() });
            {/if}

            {if $userdata['rank_id'] >= 4}
                Mousetrap.bind(prepBinding(bindings['link-missions-b']),function(e){ if(!$(".jconfirm").length){ loadPage('?id={if $userdata['village'] != 'Syndicate'}121{else}122{/if}&qid=b'); }e.preventDefault() });
            {/if}

            {if $userdata['rank_id'] >= 3}
                Mousetrap.bind(prepBinding(bindings['link-missions-c']),function(e){ if(!$(".jconfirm").length){ loadPage('?id={if $userdata['village'] != 'Syndicate'}121{else}122{/if}&qid=c'); }e.preventDefault() });
            {/if}

            {if $userdata['rank_id'] >= 2}
                Mousetrap.bind(prepBinding(bindings['link-missions-d']),function(e){ if(!$(".jconfirm").length){ loadPage('?id={if $userdata['village'] != 'Syndicate'}121&qid=d{else}122{/if}'); }e.preventDefault() });
            {/if}
        }

        bindKeys();

        $( document ).ready(function() {
            if($('.jconfirm').length != 0)
            {
                Mousetrap.bind("esc",   function(e){ if($(".jconfirm").length){ $('.jconfirm').last().find(".jconfirm-closeIcon").click();         resetMouseTrap(); bindKeys(); } e.preventDefault() });
                Mousetrap.bind("return",function(e){ if($(".jconfirm").length){ $('.jconfirm').last().find(".jconfirm-buttons .btn-blue").click(); resetMouseTrap(); bindKeys(); }e.preventDefault() });
                Mousetrap.bind("space", function(e){ if($(".jconfirm").length){ $('.jconfirm').last().find(".jconfirm-buttons .btn-blue").click(); resetMouseTrap(); bindKeys(); }e.preventDefault() });
            }
        });

        document.addEventListener('DOMContentLoaded', (event) => {
            console.log()
        });
        
    </script>
{/if}