<script type="text/javascript">
    {literal}
        /* mark it up */
        (function($){$.fn.markItUp=function(settings,extraSettings){var method,params,options,ctrlKey,shiftKey,altKey;ctrlKey=shiftKey=altKey=!1;if(typeof settings=="string"){method=settings;params=extraSettings}options={id:"",nameSpace:"",root:"",previewHandler:!1,previewInWindow:"",previewInElement:"",previewAutoRefresh:!0,previewPosition:"after",previewTemplatePath:"~/templates/preview.html",previewParser:!1,previewParserPath:"",previewParserVar:"data",resizeHandle:!0,beforeInsert:"",afterInsert:"",onEnter:{},onShiftEnter:{},onCtrlEnter:{},onTab:{},markupSet:[{}]};$.extend(options,settings,extraSettings);if(!options.root){$("script").each(function(a,tag){miuScript=$(tag).get(0).src.match(/(.*)jquery\.markitup(\.pack)?\.js$/);if(miuScript!==null){options.root=miuScript[1]}})}var uaMatch=function(ua){ua=ua.toLowerCase();var match=/(chrome)[ \/]([\w.]+)/.exec(ua)||/(webkit)[ \/]([\w.]+)/.exec(ua)||/(opera)(?:.*version|)[ \/]([\w.]+)/.exec(ua)||/(msie) ([\w.]+)/.exec(ua)||ua.indexOf("compatible")<0&&/(mozilla)(?:.*? rv:([\w.]+)|)/.exec(ua)||[];return{browser:match[1]||"",version:match[2]||"0"}};var matched=uaMatch(navigator.userAgent);var browser={};if(matched.browser){browser[matched.browser]=!0;browser.version=matched.version}if(browser.chrome){browser.webkit=!0}else{if(browser.webkit){browser.safari=!0}}return this.each(function(){var $$,textarea,levels,scrollPosition,caretPosition,caretOffset,clicked,hash,header,footer,previewWindow,template,iFrame,abort;$$=$(this);textarea=this;levels=[];abort=!1;scrollPosition=caretPosition=0;caretOffset=-1;options.previewParserPath=localize(options.previewParserPath);options.previewTemplatePath=localize(options.previewTemplatePath);if(method){switch(method){case"remove":remove();break;case"insert":markup(params);break;default:$.error("Method "+method+" does not exist on jQuery.markItUp")}return}function localize(data,inText){if(inText){return data.replace(/("|')~\//g,"$1"+options.root)}return data.replace(/^~\//,options.root)}function init(){id="";nameSpace="";if(options.id){id='id="'+options.id+'"'}else{if($$.attr("id")){id='id="markItUp'+($$.attr("id").substr(0,1).toUpperCase())+($$.attr("id").substr(1))+'"'}}if(options.nameSpace){nameSpace='class="'+options.nameSpace+'"'}$$.wrap("<div "+nameSpace+"></div>");$$.wrap("<div "+id+' class="markItUp"></div>');$$.wrap('<div class="markItUpContainer"></div>');$$.addClass("markItUpEditor");header=$('<div class="markItUpHeader"></div>').insertBefore($$);$(dropMenus(options.markupSet)).appendTo(header);footer=$('<div class="markItUpFooter"></div>').insertAfter($$);if(options.resizeHandle===!0&&browser.safari!==!0){resizeHandle=$('<div class="markItUpResizeHandle"></div>').insertAfter($$).bind("mousedown.markItUp",function(e){var h=$$.height(),y=e.clientY,mouseMove,mouseUp;mouseMove=function(e){$$.css("height",Math.max(20,e.clientY+h-y)+"px");return!1};mouseUp=function(e){$("html").unbind("mousemove.markItUp",mouseMove).unbind("mouseup.markItUp",mouseUp);return!1};$("html").bind("mousemove.markItUp",mouseMove).bind("mouseup.markItUp",mouseUp)});footer.append(resizeHandle)}$$.bind("keydown.markItUp",keyPressed).bind("keyup",keyPressed);$$.bind("insertion.markItUp",function(e,settings){if(settings.target!==!1){get()}if(textarea===$.markItUp.focused){markup(settings)}});$$.bind("focus.markItUp",function(){$.markItUp.focused=this});if(options.previewInElement){refreshPreview()}}function dropMenus(markupSet){var ul=$("<ul></ul>"),i=0;$("li:hover > ul",ul).css("display","block");$.each(markupSet,function(){var button=this,t="",title,li,j;title=(button.key)?(button.name||"")+" [Ctrl+"+button.key+"]":(button.name||"");key=(button.key)?'accesskey="'+button.key+'"':"";if(button.separator){li=$('<li class="markItUpSeparator">'+(button.separator||"")+"</li>").appendTo(ul)}else{i++;for(j=levels.length-1;j>=0;j--){t+=levels[j]+"-"}li=$('<li class="markItUpButton markItUpButton'+t+(i)+" "+(button.className||"")+'"><a href="" '+key+' title="'+title+'">'+(button.name||"")+"</a></li>").bind("contextmenu.markItUp",function(){return!1}).bind("click.markItUp",function(e){e.preventDefault()}).bind("focusin.markItUp",function(){$$.focus()}).bind("mouseup",function(){if(button.call){eval(button.call)()}setTimeout(function(){markup(button)},1);return!1}).bind("mouseenter.markItUp",function(){$("> ul",this).show();$(document).one("click",function(){$("ul ul",header).hide()})}).bind("mouseleave.markItUp",function(){$("> ul",this).hide()}).appendTo(ul);if(button.dropMenu){levels.push(i);$(li).addClass("markItUpDropMenu").append(dropMenus(button.dropMenu))}}});levels.pop();return ul}function magicMarkups(string){if(string){string=string.toString();string=string.replace(/\(\!\(([\s\S]*?)\)\!\)/g,function(x,a){var b=a.split("|!|");if(altKey===!0){return(b[1]!==undefined)?b[1]:b[0]}else{return(b[1]===undefined)?"":b[0]}});string=string.replace(/\[\!\[([\s\S]*?)\]\!\]/g,function(x,a){var b=a.split(":!:");if(abort===!0){return!1}value=prompt(b[0],(b[1])?b[1]:"");if(value===null){abort=!0}return value});return string}return""}function prepare(action){if($.isFunction(action)){action=action(hash)}return magicMarkups(action)}function build(string){var openWith=prepare(clicked.openWith);var placeHolder=prepare(clicked.placeHolder);var replaceWith=prepare(clicked.replaceWith);var closeWith=prepare(clicked.closeWith);var openBlockWith=prepare(clicked.openBlockWith);var closeBlockWith=prepare(clicked.closeBlockWith);var multiline=clicked.multiline;if(replaceWith!==""){block=openWith+replaceWith+closeWith}else{if(selection===""&&placeHolder!==""){block=openWith+placeHolder+closeWith}else{string=string||selection;var lines=[string],blocks=[];if(multiline===!0){lines=string.split(/\r?\n/)}for(var l=0;l<lines.length;l++){line=lines[l];var trailingSpaces;if(trailingSpaces=line.match(/ *$/)){blocks.push(openWith+line.replace(/ *$/g,"")+closeWith+trailingSpaces)}else{blocks.push(openWith+line+closeWith)}}block=blocks.join("\n")}}block=openBlockWith+block+closeBlockWith;return{block:block,openBlockWith:openBlockWith,openWith:openWith,replaceWith:replaceWith,placeHolder:placeHolder,closeWith:closeWith,closeBlockWith:closeBlockWith}}function markup(button){var len,j,n,i;hash=clicked=button;get();$.extend(hash,{line:"",root:options.root,textarea:textarea,selection:(selection||""),caretPosition:caretPosition,ctrlKey:ctrlKey,shiftKey:shiftKey,altKey:altKey});prepare(options.beforeInsert);prepare(clicked.beforeInsert);if((ctrlKey===!0&&shiftKey===!0)||button.multiline===!0){prepare(clicked.beforeMultiInsert)}$.extend(hash,{line:1});if((ctrlKey===!0&&shiftKey===!0)){lines=selection.split(/\r?\n/);for(j=0,n=lines.length,i=0;i<n;i++){if($.trim(lines[i])!==""){$.extend(hash,{line:++j,selection:lines[i]});lines[i]=build(lines[i]).block}else{lines[i]=""}}string={block:lines.join("\n")};start=caretPosition;len=string.block.length+((browser.opera)?n-1:0)}else{if(ctrlKey===!0){string=build(selection);start=caretPosition+string.openWith.length;len=string.block.length-string.openWith.length-string.closeWith.length;len=len-(string.block.match(/ $/)?1:0);len-=fixIeBug(string.block)}else{if(shiftKey===!0){string=build(selection);start=caretPosition;len=string.block.length;len-=fixIeBug(string.block)}else{string=build(selection);start=caretPosition+string.block.length;len=0;start-=fixIeBug(string.block)}}}if((selection===""&&string.replaceWith==="")){caretOffset+=fixOperaBug(string.block);start=caretPosition+string.openBlockWith.length+string.openWith.length;len=string.block.length-string.openBlockWith.length-string.openWith.length-string.closeWith.length-string.closeBlockWith.length;caretOffset=$$.val().substring(caretPosition,$$.val().length).length;caretOffset-=fixOperaBug($$.val().substring(0,caretPosition))}$.extend(hash,{caretPosition:caretPosition,scrollPosition:scrollPosition});if(string.block!==selection&&abort===!1){insert(string.block);set(start,len)}else{caretOffset=-1}get();$.extend(hash,{line:"",selection:selection});if((ctrlKey===!0&&shiftKey===!0)||button.multiline===!0){prepare(clicked.afterMultiInsert)}prepare(clicked.afterInsert);prepare(options.afterInsert);if(previewWindow&&options.previewAutoRefresh){refreshPreview()}shiftKey=altKey=ctrlKey=abort=!1}function fixOperaBug(string){if(browser.opera){return string.length-string.replace(/\n*/g,"").length}return 0}function fixIeBug(string){if(browser.msie){return string.length-string.replace(/\r*/g,"").length}return 0}function insert(block){if(document.selection){var newSelection=document.selection.createRange();newSelection.text=block}else{textarea.value=textarea.value.substring(0,caretPosition)+block+textarea.value.substring(caretPosition+selection.length,textarea.value.length)}}function set(start,len){if(textarea.createTextRange){if(browser.opera&&browser.version>=9.5&&len==0){return!1}range=textarea.createTextRange();range.collapse(!0);range.moveStart("character",start);range.moveEnd("character",len);range.select()}else{if(textarea.setSelectionRange){textarea.setSelectionRange(start,start+len)}}textarea.scrollTop=scrollPosition;textarea.focus()}function get(){textarea.focus();scrollPosition=textarea.scrollTop;if(document.selection){selection=document.selection.createRange().text;if(browser.msie){var range=document.selection.createRange(),rangeCopy=range.duplicate();rangeCopy.moveToElementText(textarea);caretPosition=-1;while(rangeCopy.inRange(range)){rangeCopy.moveStart("character");caretPosition++}}else{caretPosition=textarea.selectionStart}}else{caretPosition=textarea.selectionStart;selection=textarea.value.substring(caretPosition,textarea.selectionEnd)}return selection}function preview(){if(typeof options.previewHandler==="function"){previewWindow=!0}else{if(options.previewInElement){previewWindow=$(options.previewInElement)}else{if(!previewWindow||previewWindow.closed){if(options.previewInWindow){previewWindow=window.open("","preview",options.previewInWindow);$(window).unload(function(){previewWindow.close()})}else{iFrame=$('<iframe class="markItUpPreviewFrame"></iframe>');if(options.previewPosition=="after"){iFrame.insertAfter(footer)}else{iFrame.insertBefore(header)}previewWindow=iFrame[iFrame.length-1].contentWindow||frame[iFrame.length-1]}}else{if(altKey===!0){if(iFrame){iFrame.remove()}else{previewWindow.close()}previewWindow=iFrame=!1}}}}if(!options.previewAutoRefresh){refreshPreview()}if(options.previewInWindow){previewWindow.focus()}}function refreshPreview(){renderPreview()}function renderPreview(){var phtml;if(options.previewHandler&&typeof options.previewHandler==="function"){options.previewHandler($$.val())}else{if(options.previewParser&&typeof options.previewParser==="function"){var data=options.previewParser($$.val());writeInPreview(localize(data,1))}else{if(options.previewParserPath!==""){$.ajax({type:"POST",dataType:"text",global:!1,url:options.previewParserPath,data:options.previewParserVar+"="+encodeURIComponent($$.val()),success:function(data){writeInPreview(localize(data,1))}})}else{if(!template){$.ajax({url:options.previewTemplatePath,dataType:"text",global:!1,success:function(data){writeInPreview(localize(data,1).replace(/<!-- content -->/g,$$.val()))}})}}}}return!1}function writeInPreview(data){if(options.previewInElement){$(options.previewInElement).html(data)}else{if(previewWindow&&previewWindow.document){try{sp=previewWindow.document.documentElement.scrollTop}catch(e){sp=0}previewWindow.document.open();previewWindow.document.write(data);previewWindow.document.close();previewWindow.document.documentElement.scrollTop=sp}}}function keyPressed(e){shiftKey=e.shiftKey;altKey=e.altKey;ctrlKey=(!(e.altKey&&e.ctrlKey))?(e.ctrlKey||e.metaKey):!1;if(e.type==="keydown"){if(ctrlKey===!0){li=$('a[accesskey="'+((e.keyCode==13)?"\\n":String.fromCharCode(e.keyCode))+'"]',header).parent("li");if(li.length!==0){ctrlKey=!1;setTimeout(function(){li.triggerHandler("mouseup")},1);return!1}}if(e.keyCode===13||e.keyCode===10){if(ctrlKey===!0){ctrlKey=!1;markup(options.onCtrlEnter);return options.onCtrlEnter.keepDefault}else{if(shiftKey===!0){shiftKey=!1;markup(options.onShiftEnter);return options.onShiftEnter.keepDefault}else{markup(options.onEnter);return options.onEnter.keepDefault}}}if(e.keyCode===9){if(shiftKey==!0||ctrlKey==!0||altKey==!0){return!1}if(caretOffset!==-1){get();caretOffset=$$.val().length-caretOffset;set(caretOffset,0);caretOffset=-1;return!1}else{markup(options.onTab);return options.onTab.keepDefault}}}}function remove(){$$.unbind(".markItUp").removeClass("markItUpEditor");$$.parent("div").parent("div.markItUp").parent("div").replaceWith($$);$$.data("markItUp",null)}init()})};$.fn.markItUpRemove=function(){return this.each(function(){$(this).markItUp("remove")})};$.markItUp=function(settings){var options={target:!1};$.extend(options,settings);if(options.target){return $(options.target).each(function(){$(this).focus();$(this).trigger("insertion",[options])})}else{$("textarea").trigger("insertion",[options])}}})(jQuery);$(document).ready(function(){mySettings={previewParserPath:"./ajaxLibs/staticLib/markitup.bbcode-parser.php",markupSet:[{name:"Bold",key:"B",openWith:"[b]",closeWith:"[/b]"},{name:"Italic",key:"I",openWith:"[i]",closeWith:"[/i]"},{name:"Underline",key:"U",openWith:"[u]",closeWith:"[/u]"},{separator:"---------------"},{name:"Link",key:"L",openWith:"[url=[![Url]!]]",closeWith:"[/url]",placeHolder:"Your text to link here..."},{separator:"---------------"},{name:"Bulleted list",openWith:"[list]\n",closeWith:"\n[/list]"},{name:"Numeric list",openWith:"[list=[![Starting number]!]]\n",closeWith:"\n[/list]"},{name:"List item",openWith:"   [*] "},{separator:"---------------"},{name:"Code",openWith:"[code]",closeWith:"[/code]"},{separator:"---------------"},{name:"Preview",className:"preview",call:"preview"}]};var $textarea=$("textarea.markItUp");if(!$textarea.hasClass('markItUpEditor')){$textarea.markItUp(mySettings);var $elem=$(".js-chars-counter");if($elem.length){$(".js-text-limiter").limiter($textarea.attr('maxlength'),$elem)}}

//colorTIP
(function($){
	$.fn.colorTip = function(settings){

		var defaultSettings = {
			color		: 'default',
			timeout		: 333
		}
		
		var supportedColors = ['default'];//,'konoki','samui','shroud','silence','shine','syndicate'];
		
		settings = $.extend(defaultSettings,settings);

		return this.each(function(){

			var elem = $(this);
			
			if(!elem.attr('title') || element[0].classList.contains('noColorTip') ) return true;
			
			var scheduleEvent = new eventScheduler();
			var tip = new Tip(elem.attr('title'));

			elem.append(tip.generate()).addClass('colorTipContainer');

			var hasClass = false;
			for(var i=0;i<supportedColors.length;i++)
			{
				if(elem.hasClass(supportedColors[i])){
					hasClass = true;
					break;
				}
			}
			
			if(!hasClass){
				elem.addClass(settings.color);
			}
			
			elem.hover(function(){
                
				scheduleEvent.clear();
                
                scheduleEvent.set(function(){
					tip.show();
				},settings.timeout * 2);

			},function(){

                scheduleEvent.clear();

				scheduleEvent.set(function(){
					tip.hide();
				},settings.timeout);

			});
			
			elem.removeAttr('title');
		});
		
	}


	function eventScheduler(){}
	
	eventScheduler.prototype = {
		set	: function (func,timeout){
			this.timer = setTimeout(func,timeout);
		},
		clear: function(){
			clearTimeout(this.timer);
		}
	}

	function Tip(txt){
		this.content = txt;
		this.shown = false;
	}
	
	Tip.prototype = {
		generate: function(){
			return this.tip || (this.tip = $('<span class="colorTip">'+this.content+
											 '<span class="pointyTipShadow"></span><span class="pointyTip"></span></span>'));
		},
		show: function(){
			if(this.shown) return;
            this.tip.css('margin-top',($(this.tip).height() + 30) * -1 ).fadeIn();

            if( $(this.tip)[0].getBoundingClientRect().y < 0)
            {
                this.tip.css('transform','translateY( '+( ($(this.tip)[0].getBoundingClientRect().y * -1) + 25 )+'px )');
                $(this.tip).find(".pointyTipShadow").hide();
                $(this.tip).find(".pointyTip").hide();
            }
            else
            {
                this.tip.css('transform','');
                $(this.tip).find(".pointyTipShadow").show();
                $(this.tip).find(".pointyTip").show();
            }

			this.shown = true;
		},
		hide: function(){
			this.tip.fadeOut();
			this.shown = false;
		}
	}
	
})(jQuery);

$(document).ready(function(){ $('[title]').colorTip(); });
//colorTIP

$(document).on('click','#ui-button-top-right',function(){if(!$('#right-bar').hasClass('active')){$('#right-bar').addClass('active');$('#ui-button-top-right').addClass('active');$('#widget-notifications-floating-counter').hide();if($(window).width()>500){$('body').addClass('menu-open-right');$('#center-bar').addClass('menu-open');setCookie('right-bar-open',!0);setCookie('left-bar-open',!1)}}
else{$('#right-bar').removeClass('active');$('#ui-button-top-right').removeClass('active');$('#widget-notifications-floating-counter').fadeIn();if($('body').hasClass('menu-open-right')){$('body').removeClass('menu-open-right');$('#center-bar').removeClass('menu-open')}
setCookie('right-bar-open',!1)}
$('body').removeClass('menu-open-left');if($('#left-bar').hasClass('active')){$('#left-bar').toggleClass('active');$('#ui-button-top-left').toggleClass('active');setCookie('left-bar-open',!1)}});$(document).on('click','#ui-button-top-left',function(){if(!$('#left-bar').hasClass('active')){$('#left-bar').addClass('active');$('#ui-button-top-left').addClass('active');if($(window).width()>500){$('body').addClass('menu-open-left');$('#center-bar').addClass('menu-open');setCookie('left-bar-open',!0);setCookie('right-bar-open',!1)}}
else{$('#left-bar').removeClass('active');$('#ui-button-top-left').removeClass('active');if($('body').hasClass('menu-open-left')){$('body').removeClass('menu-open-left');$('#center-bar').removeClass('menu-open')}
setCookie('left-bar-open',!1)}
$('body').removeClass('menu-open-right');if($('#right-bar').hasClass('active')){$('#right-bar').toggleClass('active');$('#ui-button-top-right').toggleClass('active');setCookie('right-bar-open',!1)}});var widget_names=['notifications','quests','side-menu','user-portrait','user-details','travel','quick-links'];widget_names.forEach(function(name){$(document).on('click','#widget-'+name+'-title',function(event){if(event.originalEvent.target.id.endsWith('title'))
{$('#widget-'+name+'-title').toggleClass('closed');$('#widget-'+name+'-content').slideToggle();if($('#widget-'+name+'-title').hasClass('closed'))
{setCookie('widget-'+name+'-closed',!0)}
else{setCookie('widget-'+name+'-closed',!1)}}})});$(document).on('click','#widget-side-menu .side-menu-button',function(event){if($('#widget-side-menu-content '+'#side-menu-'+$('#'+event.target.id).data('menu')+'-box').hasClass('active'))
return!1;$('.side-menu-type-box.active').removeClass('active');$('#widget-side-menu-content '+'#side-menu-'+$('#'+event.target.id).data('menu')+'-box').addClass('active');setCookie('menu-open-tab',$('#'+event.target.id).data('menu'))});$(document).on('click','#widget-top-menu .top-menu-button',function(event){if($('#widget-top-menu '+'#top-menu-'+$('#'+event.target.id).data('menu')+'-box').hasClass('active'))
return!1;$('.top-menu-type-box.active').removeClass('active');$('#widget-top-menu '+'#top-menu-'+$('#'+event.target.id).data('menu')+'-box').addClass('active');setCookie('menu-open-tab',$('#'+event.target.id).data('menu'))});$(document).on('click','.mobile-menu-button',function(event){console.log('this 1');if($('#widget-mobile-menu '+'#mobile-menu-'+$('#'+event.target.id).data('menu')+'-box').hasClass('active'))
return!1;console.log('this 2');$('.mobile-menu-type-box.active').removeClass('active');console.log('this 3');$('#mobile-menu-'+$('#'+event.target.id).data('menu')+'-box').addClass('active');console.log('this 4');setCookie('menu-open-tab',$('#'+event.target.id).data('menu'));console.log('this 5')});$(document).on('click','#ui-button-bottom-right',function(){$('#widget-mobile-menu').fadeToggle(250);$('#ui-button-bottom-right').toggleClass('active')});$(document).on('click','#ui-button-bottom-left',function(){$('#widget-popup-travel-wrapper').fadeToggle(250);$('#ui-button-bottom-left').toggleClass('active');if($('#ui-button-bottom-left').hasClass('active')){setCookie('widget-popup-travel-open',!0)}
else{setCookie('widget-popup-travel-open',!1)}});$(document).on('click','#ui-button-bottom-center',function(){$('#widget-popup-quick-links-wrapper').fadeToggle(250);$('#ui-button-bottom-center').toggleClass('active')});$(document).on('click','.toggle-button-info, .toggle-button-drop',function(event){event.stopPropagation();$(event.currentTarget).toggleClass("closed");if($(event.currentTarget).data('target').charAt(0)!='#'&&$(event.currentTarget).data('target').charAt(0)!='.')
$('#'+$(event.currentTarget).data('target')).toggleClass("closed");else $($(event.currentTarget).data('target')).toggleClass("closed")});manageScrollBars();$('.count-down').each(function(key,value){updateTimer(value.attributes.id.nodeValue)});var checking_for_new_timers=setInterval(function(){$('.count-down').each(function(key,value){if($(value).data('active')!='yes')
{setTimeout(function(){if($(value).data('active')!='yes')
{updateTimer(value.attributes.id.nodeValue)}},2000)}})},1000);$(document).on('click','.table-legend, .table-legend-mobile',function(event){var table=event.target.parentElement
var columnID=0;$(event.target.classList).each(function(keyTemp,valueTemp){if(valueTemp.startsWith('column-'))
columnID=valueTemp});if($(event.target).data('direction')=='up')
$('.'+columnID+'.table-legend, .'+columnID+'.table-legend-mobile').data('direction','down');else $('.'+columnID+'.table-legend, .'+columnID+'.table-legend-mobile').data('direction','up');var direction=0;if($(event.target).data('direction')=='up')
direction=1;else direction=-1;var toBeSorted={};$('.'+columnID).not('.table-legend').not('.table-legend-mobile').not('.page-pages').not('table-footer').each(function(key,value){var rowClass='';$(value.classList).each(function(keyTemp,valueTemp){if(valueTemp.startsWith('row-'))
rowClass=valueTemp});while(typeof(value.children[0])!="undefined"&&value.children[0]!==null)
value=value.children[0];toBeSorted[rowClass]=value.textContent.replace(/[^0-9a-z]/gi,'').toLowerCase()});var sorted_keys=Object.keys(toBeSorted).sort(function(a,b){if(toBeSorted[a]<toBeSorted[b])return 1;if(toBeSorted[a]>toBeSorted[b])return-1;return 0});var divs=$(table).find('div').not('.table-legend').not('.page-pages').not('.table-footer');divs.sort(function(a,b){aColumn='';bColumn='';aRow='';bRow='';$(a.classList).each(function(keyTemp,valueTemp){if(valueTemp.startsWith('row-'))
aRow=valueTemp;if(valueTemp.startsWith('column-'))
aColumn=valueTemp});$(b.classList).each(function(keyTemp,valueTemp){if(valueTemp.startsWith('row-'))
bRow=valueTemp;if(valueTemp.startsWith('column-'))
bColumn=valueTemp});var returnCode=0;if(aRow==bRow){if(aColumn>bColumn)return 1;if(aColumn<bColumn)return-1}
else{$(sorted_keys).each(function(key,value){if(value==aRow){returnCode=direction;return!1}
if(value==bRow){returnCode=direction*-1;return!1}})}
return returnCode})
$lastRow='nope';$toggle='table-alternate-2';$(divs).each(function(key,value){$(value).removeClass('table-alternate-1');$(value).removeClass('table-alternate-2');if(!$(value).hasClass($lastRow)){$(value.classList).each(function(keyTemp,valueTemp){if(valueTemp.startsWith('row-'))
$lastRow=valueTemp});if($toggle=='table-alternate-2')
$toggle='table-alternate-1';else $toggle='table-alternate-2'}
if(!$(value).hasClass('table-legend'))
$(value).addClass($toggle);$(value).appendTo(table)});$('.page-pages').appendTo(table);$('.table-footer').appendTo(table)});$.each($('.scroll-to-bottom'),function(key,value){$(value).scrollTop($(value)[0].scrollHeight-$(value)[0].clientHeight)})});(function($){$.fn.extend({limiter:function(limit,elem){$(this).on("keyup focus",function(){setCount(this,elem)});function setCount(src,elem){var chars=src.value.length;if(chars>limit){src.value=src.value.substr(0,limit);chars=limit}
elem.html(limit-chars)}
setCount($(this)[0],elem)}})})(jQuery);function autoUpdateChat(){}
function updateTimer(id){if(!$('#'+id).length)
return!1;if($('#'+id).data('timer-seconds')=='n/a'){$('#'+id).html('n/a');return!1}
var timeStart=Math.round(new Date().getTime()/1000);var seconds=parseInt($('#'+id).data('timer-seconds'));var timeEnd=timeStart+seconds;var hr=Math.floor((seconds/3600)).toString();var min=Math.floor(((seconds-(hr*3600))/60)).toString();var sec=(seconds-(hr*3600)-(min*60)).toString();if(hr<1&&min<1)
$('#'+id).html(sec+"s");else if(hr<1)
$('#'+id).html(min+"m : "+sec+"s");else $('#'+id).html(hr+"h : "+min+"m : "+sec+"s");$('#'+id).data('active','yes');if(seconds>1)
{var interval=setInterval(function(){$('#'+id).data('active','yes');seconds=timeEnd-Math.round(new Date().getTime()/1000);hr=Math.floor((seconds/3600)).toString();min=Math.floor(((seconds-(hr*3600))/60)).toString();sec=(seconds-(hr*3600)-(min*60)).toString();if(hr<1&&min<1)
$('#'+id).html(sec+"s");else if(hr<1)
$('#'+id).html(min+"m : "+sec+"s");else $('#'+id).html(hr+"h : "+min+"m : "+sec+"s");if(seconds<=0.99999999){clearInterval(interval);if(typeof window[$('#'+id).data('callback')]==="function")
{window[$('#'+id).data('callback')].apply(null,null)}
else{window.location.href=window.location.href}}},1000)}
if(seconds < 1){if(typeof window[$('#'+id).data('callback')]==="function")
{window[$('#'+id).data('callback')].apply(null,null)}
else{window.location.href=window.location.href}}}
function manageScrollBars()
{$('#left-bar').scrollTop(getCookie('left-bar-scroll-top'));$('#right-bar').scrollTop(getCookie('right-bar-scroll-top'));var left_bar_timeout;$('#left-bar').scroll(function(event){if(left_bar_timeout)
{clearTimeout(left_bar_timeout)}
left_bar_timeout=setTimeout(function()
{setCookie('left-bar-scroll-top',event.target.scrollTop)},100)});var right_bar_timeout;$('#right-bar').scroll(function(event){if(right_bar_timeout)
{clearTimeout(right_bar_timeout)}
right_bar_timeout=setTimeout(function()
{setCookie('right-bar-scroll-top',event.target.scrollTop)},100)})}
function refreshPage(){window.location.href=window.location.href}
function setCookie(name,value,days){var expires="";if(days){var date=new Date();date.setTime(date.getTime()+(days*24*60*60*1000));expires="; expires="+date.toUTCString();console.log('Set expiration date: '+date.toUTCString())}
document.cookie=name+"="+(value||"")+expires+"; path=/"}
function getCookie(name){var value="; "+document.cookie;var parts=value.split("; "+name+"=");if(parts.length==2)return parts.pop().split(";").shift()}





function loadPage(url = null, target = 'all', data = null, method = 'post', hide = !1) {
    if (!buttons_disabled) {
        if (url == null) {
            url = window.location.href.replace(/&act=wake|&act=sleep|&act=gather|&act=equip|&act=delete|&process=split|&process=merge|&forget=\d*|&start=\d*|&quit=\d*|&track=\d*|&turn_in=\d*/ig, '')
        } else history.pushState(null, null, url);
        if (data != null && $.type(data) === "string") {
            tempData = data.split(',');
            data = {};
            $.each(tempData, function(index, value) {
                moreTempData = value.split(':');
                data[moreTempData[0]] = moreTempData[1]
            });
            if (!hide)
                data.ajaxRequest = 'yes'
        } else if (!hide) {
            data = {};
            data.ajaxRequest = 'yes'
        }
        if (target == 'all') {
            target = '#page-wrapper-2'
        }
        if (method == 'get') {} else if (method == 'post') {
            waiting_for_response = !0;
            $(target).load(url + " " + target, data, function(response) {
                var response_temp = response.split('<!DOCTYPE');
                var error = response_temp[0];
                response_temp = response_temp.splice(1);
                response = '';
                response_temp.forEach(function(element) {
                    response = response + '<!DOCTYPE' + element
                });
                if (error.length > 0) {
                    console.log(error);
                    alert(error)
                }
                $(target).children(':first').unwrap();
                if ($('#page-wrapper-2')['length'] == 0) {
                    $('#page-wrapper-1').children().wrapAll('<div id="page-wrapper-2" />')
                }
                enableButtons();
                waiting_for_response = !1;
                manageScrollBars();
                if (typeof summary_script === 'function')
                    summary_script();
                if ($(response).find('#confirm_popups').length) {
                    eval($(response).find('#confirm_popups')[0].innerHTML);
                }


				if ($(response).find('#BattlePageJS').length && (!("timer" in window) || !timer)) {
					var battle_page_fix = setInterval(function(){
                		if ($(response).find('#BattlePageJS').length && (!("timer" in window) || !timer)) {
							var script = $(response).find('#the_id')[0].outerHTML;
                	    	$('body').append(script);
                	    	var script = $(response).find('#BattlePageJS')[0].outerHTML;
                	    	$('body').append(script);
							clearInterval(battle_page_fix);
                		}
					}, 50);
				}

                if ($(response).find('#key_bindings').length) {
                    resetMouseTrap();
                    eval($(response).find('#key_bindings')[0].innerHTML)
                }
            });
            force_button_disable = !0;
            disableButtons();
            i = 0;
            var release_buttons = setInterval(function(i = 0) {
                if (!waiting_for_response && i < 4) {
                    force_button_disable = !1;
                    enableButtons();
                    clearInterval(release_buttons)
                }
                i = i + 1
            }, 250)
        }
        disableButtons();
        setTimeout(function() {
            $('[title]').colorTip();
            if (typeof setUpTerritoryMap == 'function') {
                setUpTerritoryMap();
            }
        }, 1000);
    }
}





var waiting_for_response=!1;var force_button_disable=!1;var buttons_disabled=!1;function disableButtons()
{buttons_disabled=!0}
function enableButtons()
{disableButtons();if(!force_button_disable)
{buttons_disabled=!1}}
$(document).ready(function(){$('textarea').on("keyup",function(event){var maxChar=event.target.maxLength;if(event.target.value.length>maxChar){return!1}
if(maxChar-event.target.value.length<=maxChar*0.75){$(event.target.parentElement).find(".textAreaCounter").html("("+event.target.value.length+"/"+maxChar+")");$(event.target.parentElement).find("textarea").addClass('active')}
else{$(event.target.parentElement).find(".textAreaCounter").html("");$(event.target.parentElement).find("textarea").removeClass('active')}})})

    {/literal}
</script>

{if $userdata['key_bindings_status'] == 1}
    <script type="text/javascript" id="key_bindings">
        
function resetMouseTrap()
{ Mousetrap.reset(); }

function prepBinding(string)
{ return string.toLowerCase().split(' ').join('').replace(',','komma').replace('comma',',').split('komma') }

        {literal}
            //MOUSE TRAP
            (function(p,t,h){function u(a,b,d){a.addEventListener?a.addEventListener(b,d,!1):a.attachEvent("on"+b,d)}function y(a){if("keypress"==a.type){var b=String.fromCharCode(a.which);a.shiftKey||(b=b.toLowerCase());return b}return m[a.which]?m[a.which]:q[a.which]?q[a.which]:String.fromCharCode(a.which).toLowerCase()}function E(a){var b=[];a.shiftKey&&b.push("shift");a.altKey&&b.push("alt");a.ctrlKey&&b.push("ctrl");a.metaKey&&b.push("meta");return b}function v(a){return"shift"==a||"ctrl"==a||"alt"==a||"meta"==a}function z(a,b){var d,e=[];var c=a;"+"===c?c=["+"]:(c=c.replace(/\+{2}/g,"+plus"),c=c.split("+"));for(d=0;d<c.length;++d){var k=c[d];A[k]&&(k=A[k]);b&&"keypress"!=b&&B[k]&&(k=B[k],e.push("shift"));v(k)&&e.push(k)}c=k;d=b;if(!d){if(!n){n={};for(var h in m)95<h&&112>h||m.hasOwnProperty(h)&&(n[m[h]]=h)}d=n[c]?"keydown":"keypress"}"keypress"==d&&e.length&&(d="keydown");return{key:k,modifiers:e,action:d}}function C(a,b){return null===a||a===t?!1:a===b?!0:C(a.parentNode,b)}function e(a){function b(a){a=a||{};var b=!1,l;for(l in n)a[l]?b=!0:n[l]=0;b||(w=!1)}function d(a,b,r,g,F,e){var l,D=[],h=r.type;if(!f._callbacks[a])return[];"keyup"==h&&v(a)&&(b=[a]);for(l=0;l<f._callbacks[a].length;++l){var d=f._callbacks[a][l];if((g||!d.seq||n[d.seq]==d.level)&&h==d.action){var c;(c="keypress"==h&&!r.metaKey&&!r.ctrlKey)||(c=d.modifiers,c=b.sort().join(",")===c.sort().join(","));c&&(c=g&&d.seq==g&&d.level==e,(!g&&d.combo==F||c)&&f._callbacks[a].splice(l,1),D.push(d))}}return D}function h(a,b,d,g){f.stopCallback(b,b.target||b.srcElement,d,g)||!1!==a(b,d)||(b.preventDefault?b.preventDefault():b.returnValue=!1,b.stopPropagation?b.stopPropagation():b.cancelBubble=!0)}function c(a){"number"!==typeof a.which&&(a.which=a.keyCode);var b=y(a);b&&("keyup"==a.type&&x===b?x=!1:f.handleKey(b,E(a),a))}function k(a,d,r,g){function l(d){return function(){w=d;++n[a];clearTimeout(p);p=setTimeout(b,1E3)}}function e(d){h(r,d,a);"keyup"!==g&&(x=y(d));setTimeout(b,10)}for(var c=n[a]=0;c<d.length;++c){var f=c+1===d.length?e:l(g||z(d[c+1]).action);m(d[c],f,g,a,c)}}function m(a,b,c,g,e){f._directMap[a+":"+c]=b;a=a.replace(/\s+/g," ");var h=a.split(" ");1<h.length?k(a,h,b,c):(c=z(a,c),f._callbacks[c.key]=f._callbacks[c.key]||[],d(c.key,c.modifiers,{type:c.action},g,a,e),f._callbacks[c.key][g?"unshift":"push"]({callback:b,modifiers:c.modifiers,action:c.action,seq:g,level:e,combo:a}))}var f=this;a=a||t;if(!(f instanceof e))return new e(a);f.target=a;f._callbacks={};f._directMap={};var n={},p,x=!1,q=!1,w=!1;f._handleKey=function(a,c,e){var g=d(a,c,e),f;c={};var l=0,k=!1;for(f=0;f<g.length;++f)g[f].seq&&(l=Math.max(l,g[f].level));for(f=0;f<g.length;++f)g[f].seq?g[f].level==l&&(k=!0,c[g[f].seq]=1,h(g[f].callback,e,g[f].combo,g[f].seq)):k||h(g[f].callback,e,g[f].combo);g="keypress"==e.type&&q;e.type!=w||v(a)||g||b(c);q=k&&"keydown"==e.type};f._bindMultiple=function(a,b,c){for(var d=0;d<a.length;++d)m(a[d],b,c)};u(a,"keypress",c);u(a,"keydown",c);u(a,"keyup",c)}if(p){var m={8:"backspace",9:"tab",13:"enter",16:"shift",17:"ctrl",18:"alt",20:"capslock",27:"esc",32:"space",33:"pageup",34:"pagedown",35:"end",36:"home",37:"left",38:"up",39:"right",40:"down",45:"ins",46:"del",91:"meta",93:"meta",224:"meta"},q={106:"*",107:"+",109:"-",110:".",111:"/",186:";",187:"=",188:",",189:"-",190:".",191:"/",192:"`",219:"[",220:"\\",221:"]",222:"'"},B={"~":"`","!":"1","@":"2","#":"3",$:"4","%":"5","^":"6","&":"7","*":"8","(":"9",")":"0",_:"-","+":"=",":":";",'"':"'","<":",",">":".","?":"/","|":"\\"},A={option:"alt",command:"meta","return":"enter",escape:"esc",plus:"+",mod:/Mac|iPod|iPhone|iPad/.test(navigator.platform)?"meta":"ctrl"},n;for(h=1;20>h;++h)m[111+h]="f"+h;for(h=0;9>=h;++h)m[h+96]=h.toString();e.prototype.bind=function(a,b,d){a=a instanceof Array?a:[a];this._bindMultiple.call(this,a,b,d);return this};e.prototype.unbind=function(a,b){return this.bind.call(this,a,function(){},b)};e.prototype.trigger=function(a,b){if(this._directMap[a+":"+b])this._directMap[a+":"+b]({},a);return this};e.prototype.reset=function(){this._callbacks={};this._directMap={};return this};e.prototype.stopCallback=function(a,b){return-1<(" "+b.className+" ").indexOf(" mousetrap ")||C(b,this.target)?!1:"INPUT"==b.tagName||"SELECT"==b.tagName||"TEXTAREA"==b.tagName||b.isContentEditable};e.prototype.handleKey=function(){return this._handleKey.apply(this,arguments)};e.addKeycodes=function(a){for(var b in a)a.hasOwnProperty(b)&&(m[b]=a[b]);n=null};e.init=function(){var a=e(t),b;for(b in a)"_"!==b.charAt(0)&&(e[b]=function(b){return function(){return a[b].apply(a,arguments)}}(b))};e.init();p.Mousetrap=e;"undefined"!==typeof module&&module.exports&&(module.exports=e);"function"===typeof define&&define.amd&&define(function(){return e})}})("undefined"!==typeof window?window:null,"undefined"!==typeof window?document:null);
            //MOUSE TRAP
        {/literal}

var bindings=$.parseJSON('{$userdata['key_bindings']}');
Mousetrap.bind(prepBinding(bindings['move-north']),function(e){ loadPage(null,'all','doTravel:N');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['move-east']),function(e){ loadPage(null,'all','doTravel:E');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['move-south']),function(e){ loadPage(null,'all','doTravel:S');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['move-west']),function(e){ loadPage(null,'all','doTravel:W');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['move-north-west']),function(e){ loadPage(null,'all','doTravel:NW');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['move-north-east']),function(e){ loadPage(null,'all','doTravel:NE');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['move-south-west']),function(e){ loadPage(null,'all','doTravel:SW');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['move-south-east']),function(e){ loadPage(null,'all','doTravel:SE');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-anbu']),function(e){ loadPage('?id=95');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-clan']),function(e){ loadPage('?id=94');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-combat']),function(e){ loadPage('?id=50');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-home-inventory']),function(e){ loadPage('?id=23&act=inventory');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-inbox']),function(e){ loadPage('?id=3');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-inventory']),function(e){ loadPage('?id=11');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-jutsu']),function(e){ loadPage('?id=12');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-marriage']),function(e){ loadPage('?id=17');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-missions']),function(e){ loadPage('?id=121');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-occupation']),function(e){ loadPage('?id=36');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-preferences']),function(e){ loadPage('?id=4');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-profession']),function(e){ loadPage('?id=86');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-profile']),function(e){ loadPage('?id=2');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-quests']),function(e){ loadPage('?id=120');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-rob']),function(e){ loadPage('?id=49');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-scout']),function(e){ loadPage('?id=30');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-tavern']),function(e){ loadPage('?id=24');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-travel']),function(e){ loadPage('?id=8');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-global-trade']),function(e){ loadPage('?id=68');e.preventDefault() });

Mousetrap.bind(prepBinding(bindings['link-bank']), function(e) { {if $userdata['village'] != 'Syndicate'} loadPage('?id=20'); e.preventDefault(); {else} loadPage('?id=45'); e.preventDefault(); {/if} });

Mousetrap.bind(prepBinding(bindings['link-errands']), function(e){ {if $userdata['village'] != 'Syndicate'} loadPage('?id=21'); {else} loadPage('?id=22'); {/if} e.preventDefault(); });

Mousetrap.bind(prepBinding(bindings['link-ramen']), function(e){ {if $userdata['village'] != 'Syndicate' && $userdata['village'] == $userdata['location']} loadPage('?id=25'); {else if $userdata['village'] != 'Syndicate' && $userdata['village'] != $userdata['location']} loadPage('?id=57'); {else} loadPage('?id=97'); {/if} e.preventDefault(); });

Mousetrap.bind(prepBinding(bindings['link-training']), function(e){ {if $userdata['rank_id'] == 1} loadPage('?id=18'); {else if $userdata['rank_id'] == 2} loadPage('?id=29'); {else if $userdata['rank_id'] >= 3} loadPage('?id=39'); {else} console.log('how do you have a rank id of: {$userdata['rank_id']}?'); {/if} e.preventDefault(); });

Mousetrap.bind(prepBinding(bindings['link-sleep']),function(e){ loadPage(null,'all','doSleep:sleep');e.preventDefault() });
Mousetrap.bind(prepBinding(bindings['link-wakeup']),function(e){ loadPage(null,'all','doSleep:wakeup');e.preventDefault() });

    </script>
{/if}