<link href='//fonts.googleapis.com/css?family=Calligraffitti' rel='stylesheet'>
<style>{literal}
	.healthBar{background-color:#FF0000; background-image: 
				radial-gradient(circle at 100% 150%, #800000 24%, #FF0000 25%, #FF0000 28%, #800000 29%, #800000 36%, #FF0000 36%, #FF0000 40%, transparent 40%, transparent),
				radial-gradient(circle at 0    150%, #800000 24%, #FF0000 25%, #FF0000 28%, #800000 29%, #800000 36%, #FF0000 36%, #FF0000 40%, transparent 40%, transparent),
				radial-gradient(circle at 50%  100%, #FF0000   10%,  #800000 11%, #800000 23%, #FF0000 24%, #FF0000 30%, #800000 31%, #800000 43%, #FF0000 44%, #FF0000 50%, #800000 51%, #800000 63%, #FF0000 64%, #FF0000 71%, transparent 71%, transparent),
				radial-gradient(circle at 100% 50%,  #FF0000   5%,   #800000 6%, #800000 15%, #FF0000 16%, #FF0000 20%, #800000 21%, #800000 30%, #FF0000 31%, #FF0000 35%, #800000 36%, #800000 45%, #FF0000 46%, #FF0000 49%, transparent 50%, transparent),
				radial-gradient(circle at 0    50%,  #FF0000   5%,   #800000 6%, #800000 15%, #FF0000 16%, #FF0000 20%, #800000 21%, #800000 30%, #FF0000 31%, #FF0000 35%, #800000 36%, #800000 45%, #FF0000 46%, #FF0000 49%, transparent 50%, transparent);
				background-size:20px 10px;}
	.chakraBar{background-color:#4848FF; background-image: 
				radial-gradient(circle at 100% 150%, #000080 24%, #4848FF 25%, #4848FF 28%, #000080 29%, #000080 36%, #4848FF 36%, #4848FF 40%, transparent 40%, transparent),
				radial-gradient(circle at 0    150%, #000080 24%, #4848FF 25%, #4848FF 28%, #000080 29%, #000080 36%, #4848FF 36%, #4848FF 40%, transparent 40%, transparent),
				radial-gradient(circle at 50%  100%, #4848FF   10%,  #000080 11%, #000080 23%, #4848FF 24%, #4848FF 30%, #000080 31%, #000080 43%, #4848FF 44%, #4848FF 50%, #000080 51%, #000080 63%, #4848FF 64%, #4848FF 71%, transparent 71%, transparent),
				radial-gradient(circle at 100% 50%,  #4848FF   5%,   #000080 6%, #000080 15%, #4848FF 16%, #4848FF 20%, #000080 21%, #000080 30%, #4848FF 31%, #4848FF 35%, #000080 36%, #000080 45%, #4848FF 46%, #4848FF 49%, transparent 50%, transparent),
				radial-gradient(circle at 0    50%,  #4848FF   5%,   #000080 6%, #000080 15%, #4848FF 16%, #4848FF 20%, #000080 21%, #000080 30%, #3232FF 31%, #3232FF 35%, #000080 36%, #000080 45%, #3232FF 46%, #3232FF 49%, transparent 50%, transparent);
				background-size:20px 10px;}
	.staminaBar{background-color:#00AA00; background-image: 
				radial-gradient(circle at 100% 150%, #008000 24%, #00AA00 25%, #00AA00 28%, #008000 29%, #008000 36%, #00AA00 36%, #00AA00 40%, transparent 40%, transparent),
				radial-gradient(circle at 0    150%, #008000 24%, #00AA00 25%, #00AA00 28%, #008000 29%, #008000 36%, #00AA00 36%, #00AA00 40%, transparent 40%, transparent),
				radial-gradient(circle at 50%  100%, #00AA00   10%,  #008000 11%, #008000 23%, #00AA00 24%, #00AA00 30%, #008000 31%, #008000 43%, #00AA00 44%, #00AA00 50%, #008000 51%, #008000 63%, #00AA00 64%, #00AA00 71%, transparent 71%, transparent),
				radial-gradient(circle at 100% 50%,  #00AA00   5%,   #008000 6%, #008000 15%, #00AA00 16%, #00AA00 20%, #008000 21%, #008000 30%, #00AA00 31%, #00AA00 35%, #008000 36%, #008000 45%, #00AA00 46%, #00AA00 49%, transparent 50%, transparent),
				radial-gradient(circle at 0    50%,  #00AA00   5%,   #008000 6%, #008000 15%, #00AA00 16%, #00AA00 20%, #008000 21%, #008000 30%, #00AA00 31%, #00AA00 35%, #008000 36%, #008000 45%, #00AA00 46%, #00AA00 49%, transparent 50%, transparent);
				background-size:20px 10px;}

    select::-ms-expand {
        display: none;
    }

    .select-wrapper
    {
        border: 0 !important;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        text-overflow:'';
        text-indent: 0.01px;
        overflow:hidden;
        margin-right: 20px;
        overflow-y:hidden;
        text-align: center;
    }
	option:hover
	{
		background-color: #d2b48c;
	}

	option:checked
	{
		background: linear-gradient(#d2b48c, #d2b48c);
	}
{/literal}</style>

{literal}
<style>
blockquote,blockquote span {
	background-repeat:no-repeat
}
.mainLogMessage,caption,th {
	text-align:left
}
.menuBg1,.menuBg2 {
	position:relative;
	box-shadow:0 0 10px rgba(0,0,0,.5)
}
.leftDiagonal2,.menuBg1,.menuBg2 {
	box-shadow:0 0 10px rgba(0,0,0,.5)
}
.ribbonRank,.tavernMessageBox {
	word-wrap:break-word
}
.tooltipster-fall,.tooltipster-grow-show {
	-webkit-transition-timing-function:cubic-bezier(.175,.885,.32,1.15);
	-moz-transition-timing-function:cubic-bezier(.175,.885,.32,1.15);
	-ms-transition-timing-function:cubic-bezier(.175,.885,.32,1.15);
	-o-transition-timing-function:cubic-bezier(.175,.885,.32,1.15)
}
blockquote,body,dd,div,dl,dt,fieldset,form,h1,h2,h3,h4,h5,h6,hr,html,input,li,ol,p,pre,ul {
	margin:0;
	padding:0
}
address,caption,cite,code,em,h1,h2,h3,h4,h5,h6,pre,strong,th {
	font-size:1em;
	font-weight:400;
	font-style:normal;
	display:inline
}
ol,ul {
	list-style:none
}
fieldset,hr,img {
	border:none
}
table {
	border-collapse:collapse;
	border-spacing:0
}
td {
	vertical-align:top
}
.bar,.hpcpsp,.newsItem,.tavernMessageBox,.topPlayersRow,.widgetText {
	vertical-align:middle
}
p {
	padding-top:3px;
	padding-bottom:3px
}
blockquote {
	font:14px/20px italic Times,serif;
	padding:8px;
	background-color:#faebbc;
	border-top:1px solid #e1cc89;
	border-bottom:1px solid #e1cc89;
	margin:5px;
	background-image:url(data:image/gif;base64,R0lGODlhGQAZALMPAOzXqurVpO3YruXRlu7asebSmujUoOTPk+LNjPDcuOPOkOvWp+fTne/btOHMifHduyH5BAEAAA8ALAAAAAAZABkAAARe8MlJq7046827/2AocsBxCUeyJYUTWIy7HghhDY7AtXZlOACOwGGwNByFTuvlI3YUjqhjMcFJmRio1NGzSoMYb5T6iG2xFsDW0ZAQ1roMAOFAgCUCLXnE7/v/gIGBEQA7);
	background-position:top left;
	text-indent:23px
}
.actionEntry,.logEntry,.mainLogMessage,.targetEntry {
	border-top:1px solid #000
}
blockquote span {
	display:block;
	background-image:url(data:image/gif;base64,R0lGODlhGQAZALMNAO7aseTPk+XRlvDcuO3Yru/btOPOkOLNjOjUoOfTnerVpObSmuHMifHduwAAAAAAACH5BAEAAA0ALAAAAAAZABkAAARisMlJq7046827/2AoYoXAMAhVnslmnswyvaecwfAgGTgDZIEeQUI7DTGAIOzXSOKOmgLjoKMMTlUNgdGqALidgCE7CRwKm4HgwKSYoEhDAE1JGtoZhOKiSI3+gIGCg4SFFBEAOw==);
	background-position:bottom right
}
input[type=checkbox],input[type=radio] {
	margin-right:5px
}
@font-face {
	font-family:sakura;
	src:url(fonts/sakura-webfont.eot);
	src:url(fonts/sakura-webfont.eot?#iefix) format('embedded-opentype'),url(fonts/sakura-webfont.woff) format('woff'),url(fonts/sakura-webfont.ttf) format('truetype'),url(fonts/sakura-webfont.svg#sakuraalpregular) format('svg');
	font-weight:400;
	font-style:normal
}
@font-face {
	font-family:fontasia;
	src:url(fonts/fontasia-webfont.eot);
	src:url(fonts/fontasia-webfont.eot?#iefix) format('embedded-opentype'),url(fonts/fontasia-webfont.woff) format('woff'),url(fonts/fontasia-webfont.ttf) format('truetype'),url(fonts/fontasia-webfont.svg#fontasiaregular) format('svg');
	font-weight:400;
	font-style:normal
}
.toplistNumbers {
	float:left;
	font-size:30px;
	padding-right:10px;
	color:#686868;
	font-weight:700
}
.mainLogMessage {
	padding-left:10px;
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAMAAAADCAYAAABWKLW/AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAxJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NTAyNTZDNUY5RkM2MTFFMzgwQkRENTM0Njc5QzBGOTAiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NTAyNTZDNUU5RkM2MTFFMzgwQkRENTM0Njc5QzBGOTAiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiBNYWNpbnRvc2giPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0iMzFCQjBEM0I5QkZGMjI2QzIxQjg5RDQ1OTdDODk5OEYiIHN0UmVmOmRvY3VtZW50SUQ9IjMxQkIwRDNCOUJGRjIyNkMyMUI4OUQ0NTk3Qzg5OThGIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+W1KwegAAACdJREFUeNpiuHlhv+/////FgJiBAcS4cfEgUOCvKIjDAGKABAACDAAN5Bpkyaq4PQAAAABJRU5ErkJggg==)
}
.logMain {
	background-color:grey
}
.logMainSub {
	background-color:#D0D0D0
}
.logMain:hover {
	background-color:#A0A0A0
}
.actionEntry:hover,.logEntry:hover,.targetEntry:hover {
	background-color:#DFDCAD
}
.logEntry {
	border-bottom:0 solid #000
}
.logBlue {
	background-color:navy;
	color:#fff
}
.logRed {
	background-color:maroon;
	color:#fff
}
.logGreen {
	background-color:green;
	color:#fff
}
.logBlack {
	background-color:#202020;
	color:#fff
}
.hidden,.hidden2 {
	display:none
}
.imageHover:hover {
	opacity:.6
}
.input_submit_btn {
	background-color:#b4ad92;
	display:inline-block;
	border-radius:8px;
	border:2px solid #9c936f;
	color:#fff;
	padding:2px 12px 3px;
	text-decoration:none;
	text-align:center;
	cursor:pointer;
	width:130px
}
.input_submit_btn:hover {
	background-color:#9c936f;
	cursor:pointer
}
.leftDiagonal,.menuBg1,.wrapper_outer {
	background-color:#fdf2e0
}
.menuBg1 {
	background-image:url(data:image/jpeg;base64,/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAZAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQAAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQICAgICAgICAgICAwMDAwMDAwMDAwEBAQEBAQECAQECAgIBAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMD/8AAEQgAAwADAwERAAIRAQMRAf/EAaIAAAAGAgMBAAAAAAAAAAAAAAcIBgUECQMKAgEACwEAAAYDAQEBAAAAAAAAAAAABgUEAwcCCAEJAAoLEAACAQMEAQMDAgMDAwIGCXUBAgMEEQUSBiEHEyIACDEUQTIjFQlRQhZhJDMXUnGBGGKRJUOhsfAmNHIKGcHRNSfhUzaC8ZKiRFRzRUY3R2MoVVZXGrLC0uLyZIN0k4Rlo7PD0+MpOGbzdSo5OkhJSlhZWmdoaWp2d3h5eoWGh4iJipSVlpeYmZqkpaanqKmqtLW2t7i5usTFxsfIycrU1dbX2Nna5OXm5+jp6vT19vf4+foRAAIBAwIEBAMFBAQEBgYFbQECAxEEIRIFMQYAIhNBUQcyYRRxCEKBI5EVUqFiFjMJsSTB0UNy8BfhgjQlklMYY0TxorImNRlUNkVkJwpzg5NGdMLS4vJVZXVWN4SFo7PD0+PzKRqUpLTE1OT0laW1xdXl9ShHV2Y4doaWprbG1ub2Z3eHl6e3x9fn90hYaHiImKi4yNjo+DlJWWl5iZmpucnZ6fkqOkpaanqKmqq6ytrq+v/aAAwDAQACEQMRAD8A3bv/AEEfsf7o/wC+/wCzh/2XX+8P++/gvsi/5xaPC/1f0vpdX+rwujH/AHvVr/1fLxqf6tfX/9k=);
	padding:0 0 5px;
	width:170px
}
.menuBg1-left {
	left:0;
	top:0;
	margin-right:10px
}
.menuBg1-right {
	left:13px;
	top:0;
	margin-left:10px
}
.menuBg2 {
	background-image:url(data:image/jpeg;base64,/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAZAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQAAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQICAgICAgICAgICAwMDAwMDAwMDAwEBAQEBAQECAQECAgIBAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMD/8AAEQgAAwADAwERAAIRAQMRAf/EAaIAAAAGAgMBAAAAAAAAAAAAAAcIBgUECQMKAgEACwEAAAYDAQEBAAAAAAAAAAAABgUEAwcCCAEJAAoLEAACAQMEAQMDAgMDAwIGCXUBAgMEEQUSBiEHEyIACDEUQTIjFQlRQhZhJDMXUnGBGGKRJUOhsfAmNHIKGcHRNSfhUzaC8ZKiRFRzRUY3R2MoVVZXGrLC0uLyZIN0k4Rlo7PD0+MpOGbzdSo5OkhJSlhZWmdoaWp2d3h5eoWGh4iJipSVlpeYmZqkpaanqKmqtLW2t7i5usTFxsfIycrU1dbX2Nna5OXm5+jp6vT19vf4+foRAAIBAwIEBAMFBAQEBgYFbQECAxEEIRIFMQYAIhNBUQcyYRRxCEKBI5EVUqFiFjMJsSTB0UNy8BfhgjQlklMYY0TxorImNRlUNkVkJwpzg5NGdMLS4vJVZXVWN4SFo7PD0+PzKRqUpLTE1OT0laW1xdXl9ShHV2Y4doaWprbG1ub2Z3eHl6e3x9fn90hYaHiImKi4yNjo+DlJWWl5iZmpucnZ6fkqOkpaanqKmqq6ytrq+v/aAAwDAQACEQMRAD8A3sv/ADz+D+D/AO+/6uH+jn+If777L2g/3mmn/V8/Cr/q09Kf96rq/wBX+3/1cev/2Q==);
	padding-top:45px;
	padding-bottom:10px;
	width:175px
}
.characterRibbon,.ribbonStats {
	background-repeat:no-repeat;
	position:absolute
}
.menuBg2-left {
	left:-17px
}
.menuBg2-right {
	left:7px
}
.ribbonStats {
	background-position:bottom left;
	height:38px;
	width:193px;
	top:20px
}
.ribbonStats-left {
	left:0;
	background-image:url(data:image/png;base64,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)
}
.ribbonStats-right {
	left:0;
	background-image:url(data:image/png;base64,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)
}
.characterRibbon {
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACsAAAAkCAYAAAAQC8MVAAAACXBIWXMAAAsTAAALEwEAmpwYAAAKT2lDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAHjanVNnVFPpFj333vRCS4iAlEtvUhUIIFJCi4AUkSYqIQkQSoghodkVUcERRUUEG8igiAOOjoCMFVEsDIoK2AfkIaKOg6OIisr74Xuja9a89+bN/rXXPues852zzwfACAyWSDNRNYAMqUIeEeCDx8TG4eQuQIEKJHAAEAizZCFz/SMBAPh+PDwrIsAHvgABeNMLCADATZvAMByH/w/qQplcAYCEAcB0kThLCIAUAEB6jkKmAEBGAYCdmCZTAKAEAGDLY2LjAFAtAGAnf+bTAICd+Jl7AQBblCEVAaCRACATZYhEAGg7AKzPVopFAFgwABRmS8Q5ANgtADBJV2ZIALC3AMDOEAuyAAgMADBRiIUpAAR7AGDIIyN4AISZABRG8lc88SuuEOcqAAB4mbI8uSQ5RYFbCC1xB1dXLh4ozkkXKxQ2YQJhmkAuwnmZGTKBNA/g88wAAKCRFRHgg/P9eM4Ors7ONo62Dl8t6r8G/yJiYuP+5c+rcEAAAOF0ftH+LC+zGoA7BoBt/qIl7gRoXgugdfeLZrIPQLUAoOnaV/Nw+H48PEWhkLnZ2eXk5NhKxEJbYcpXff5nwl/AV/1s+X48/Pf14L7iJIEyXYFHBPjgwsz0TKUcz5IJhGLc5o9H/LcL//wd0yLESWK5WCoU41EScY5EmozzMqUiiUKSKcUl0v9k4t8s+wM+3zUAsGo+AXuRLahdYwP2SycQWHTA4vcAAPK7b8HUKAgDgGiD4c93/+8//UegJQCAZkmScQAAXkQkLlTKsz/HCAAARKCBKrBBG/TBGCzABhzBBdzBC/xgNoRCJMTCQhBCCmSAHHJgKayCQiiGzbAdKmAv1EAdNMBRaIaTcA4uwlW4Dj1wD/phCJ7BKLyBCQRByAgTYSHaiAFiilgjjggXmYX4IcFIBBKLJCDJiBRRIkuRNUgxUopUIFVIHfI9cgI5h1xGupE7yAAygvyGvEcxlIGyUT3UDLVDuag3GoRGogvQZHQxmo8WoJvQcrQaPYw2oefQq2gP2o8+Q8cwwOgYBzPEbDAuxsNCsTgsCZNjy7EirAyrxhqwVqwDu4n1Y8+xdwQSgUXACTYEd0IgYR5BSFhMWE7YSKggHCQ0EdoJNwkDhFHCJyKTqEu0JroR+cQYYjIxh1hILCPWEo8TLxB7iEPENyQSiUMyJ7mQAkmxpFTSEtJG0m5SI+ksqZs0SBojk8naZGuyBzmULCAryIXkneTD5DPkG+Qh8lsKnWJAcaT4U+IoUspqShnlEOU05QZlmDJBVaOaUt2ooVQRNY9aQq2htlKvUYeoEzR1mjnNgxZJS6WtopXTGmgXaPdpr+h0uhHdlR5Ol9BX0svpR+iX6AP0dwwNhhWDx4hnKBmbGAcYZxl3GK+YTKYZ04sZx1QwNzHrmOeZD5lvVVgqtip8FZHKCpVKlSaVGyovVKmqpqreqgtV81XLVI+pXlN9rkZVM1PjqQnUlqtVqp1Q61MbU2epO6iHqmeob1Q/pH5Z/YkGWcNMw09DpFGgsV/jvMYgC2MZs3gsIWsNq4Z1gTXEJrHN2Xx2KruY/R27iz2qqaE5QzNKM1ezUvOUZj8H45hx+Jx0TgnnKKeX836K3hTvKeIpG6Y0TLkxZVxrqpaXllirSKtRq0frvTau7aedpr1Fu1n7gQ5Bx0onXCdHZ4/OBZ3nU9lT3acKpxZNPTr1ri6qa6UbobtEd79up+6Ynr5egJ5Mb6feeb3n+hx9L/1U/W36p/VHDFgGswwkBtsMzhg8xTVxbzwdL8fb8VFDXcNAQ6VhlWGX4YSRudE8o9VGjUYPjGnGXOMk423GbcajJgYmISZLTepN7ppSTbmmKaY7TDtMx83MzaLN1pk1mz0x1zLnm+eb15vft2BaeFostqi2uGVJsuRaplnutrxuhVo5WaVYVVpds0atna0l1rutu6cRp7lOk06rntZnw7Dxtsm2qbcZsOXYBtuutm22fWFnYhdnt8Wuw+6TvZN9un2N/T0HDYfZDqsdWh1+c7RyFDpWOt6azpzuP33F9JbpL2dYzxDP2DPjthPLKcRpnVOb00dnF2e5c4PziIuJS4LLLpc+Lpsbxt3IveRKdPVxXeF60vWdm7Obwu2o26/uNu5p7ofcn8w0nymeWTNz0MPIQ+BR5dE/C5+VMGvfrH5PQ0+BZ7XnIy9jL5FXrdewt6V3qvdh7xc+9j5yn+M+4zw33jLeWV/MN8C3yLfLT8Nvnl+F30N/I/9k/3r/0QCngCUBZwOJgUGBWwL7+Hp8Ib+OPzrbZfay2e1BjKC5QRVBj4KtguXBrSFoyOyQrSH355jOkc5pDoVQfujW0Adh5mGLw34MJ4WHhVeGP45wiFga0TGXNXfR3ENz30T6RJZE3ptnMU85ry1KNSo+qi5qPNo3ujS6P8YuZlnM1VidWElsSxw5LiquNm5svt/87fOH4p3iC+N7F5gvyF1weaHOwvSFpxapLhIsOpZATIhOOJTwQRAqqBaMJfITdyWOCnnCHcJnIi/RNtGI2ENcKh5O8kgqTXqS7JG8NXkkxTOlLOW5hCepkLxMDUzdmzqeFpp2IG0yPTq9MYOSkZBxQqohTZO2Z+pn5mZ2y6xlhbL+xW6Lty8elQfJa7OQrAVZLQq2QqboVFoo1yoHsmdlV2a/zYnKOZarnivN7cyzytuQN5zvn//tEsIS4ZK2pYZLVy0dWOa9rGo5sjxxedsK4xUFK4ZWBqw8uIq2Km3VT6vtV5eufr0mek1rgV7ByoLBtQFr6wtVCuWFfevc1+1dT1gvWd+1YfqGnRs+FYmKrhTbF5cVf9go3HjlG4dvyr+Z3JS0qavEuWTPZtJm6ebeLZ5bDpaql+aXDm4N2dq0Dd9WtO319kXbL5fNKNu7g7ZDuaO/PLi8ZafJzs07P1SkVPRU+lQ27tLdtWHX+G7R7ht7vPY07NXbW7z3/T7JvttVAVVN1WbVZftJ+7P3P66Jqun4lvttXa1ObXHtxwPSA/0HIw6217nU1R3SPVRSj9Yr60cOxx++/p3vdy0NNg1VjZzG4iNwRHnk6fcJ3/ceDTradox7rOEH0x92HWcdL2pCmvKaRptTmvtbYlu6T8w+0dbq3nr8R9sfD5w0PFl5SvNUyWna6YLTk2fyz4ydlZ19fi753GDborZ752PO32oPb++6EHTh0kX/i+c7vDvOXPK4dPKy2+UTV7hXmq86X23qdOo8/pPTT8e7nLuarrlca7nuer21e2b36RueN87d9L158Rb/1tWeOT3dvfN6b/fF9/XfFt1+cif9zsu72Xcn7q28T7xf9EDtQdlD3YfVP1v+3Njv3H9qwHeg89HcR/cGhYPP/pH1jw9DBY+Zj8uGDYbrnjg+OTniP3L96fynQ89kzyaeF/6i/suuFxYvfvjV69fO0ZjRoZfyl5O/bXyl/erA6xmv28bCxh6+yXgzMV70VvvtwXfcdx3vo98PT+R8IH8o/2j5sfVT0Kf7kxmTk/8EA5jz/GMzLdsAAAAgY0hSTQAAeiUAAICDAAD5/wAAgOkAAHUwAADqYAAAOpgAABdvkl/FRgAACqBJREFUeNp0mMuPJMtVxn/nROSruntmuueObXRBIPEQyCAWyL5C3AWPDbCBBQu8MxuzwHhxkWCHDFswkndXrPwHsEBiwRIMthCPxZXA8oLLcGfuvDSvmq6ursrKjIjDIiKzskem1FVZWRkZeeKc73zfFy2//fM/++EHv/HLv//q5Zo7Z6c8Xa+5d+cOT16tOV91PHlzycXJisdvLrl7esLj9SUXq47Hlxsuuo4nm2vunbY82Ww571qeXm253TS83u+56Dpe7vZcdA3r/YFbTc1633Petbze99xuai77AxerjhfbHe+cdLwq119e77h3ssrjVx2vr3e4pq5+zMNvnjU1q0pZ73pa73hxdU3thOdX13gV1v2IA9b7HhXh9fUep8Kr3R6vyqYfUIHLfqB2ymV/oFLl6jCgKmwPI84J14eAd7A5jFROuRpGvArXY8C5PL52yn4MeKfsxhEVuB5G3Odu3/rok1fr3/qRO7c/e7freHZ5hRhc9j2N9zzbbGm8581+T62Oy/0e75U3u74EMeBUuOx7RIR+GFBRNn2PV8duHFARtn2PV2U/jHjn2B4GaufYDQMG7IcBJ8puGFER+nFERObz/RjQ9x4+GoYY/+Cf7z/g6nBgFyP7MLA5HLjc94yW2A0Dm8PA1aHnahy5HgZ2YaQPgX0Y2R0ChxgZQ2QXA9thYIyJQwjsQyDExJBSHh8jhzASUuIQA30IJEsMyRhiJKTEEMp8MdKPI2MIDDHiAd57+Og7//qjP/ytdQhf/om755zfPqP2NeerFaum5mzVoqrcXa1AhB+6dYYT5XzV4VV5Z7Wi2jjOVw2yUS7ahhfec9411N5zp63xTrnoOurrHRerlsp5bnctTpS7Jx3e9dxpGyrnOO9ausPAnaam9p6LrqXZ9yjH1x/97UffuxxSxCwhgDjox4BFIwHqhMp5SIZ3DswAMDOiJZIZKoJzisg0rSGiWDlTVQSh/OURKX8XkfxGCClhIphZ/g7HYN978Oj186vtH3/3/sMycQLLE6gTvAgqiqogTokxoiJglAcLkB9mxhx4TPkoCIYRU5rWgC/BIXnBlLdheT4zTASniiyDBfjig0d//W8PH3/31dUOK5ds/sivlCxPrPm6ipLMEAEViOmY4el3M8sZRqhUiWYks8UiS9ZFMMDJtLR8r5QQbgQLEFL66t/95/cT5AxUIqSUJ48h5tUmI8aY1yA54PLsGw+dglbNVcmBg1fBqzLERLKUAzrihljOnUhe9DTv28F+8cGjj77/7MU3Pn65zribMyjHiVRQ58AgpEiwhEgOToCEoQUSyYwxphkawRLCER4qgne5OmkRR+4DmzP7A4Mtrz//p/958GwIhqWEaM4UKjcGiWTcOeQIlTIkzc0HZgmYsEtpnozPaEY0UM2ZlILR9FZAZvaDg/3Cg0+3m8PhD7/98f3MAlIAvijVhCMRLYGXBiuBmxmujHfqZraY8VfKKyIooAgxpRlOXjUHOGNf/t/M8oVPPv2bf/nfh3+/3vdzMMkMr0oMkZQSlBJP3a4iiFDeMt9nZjh1M37d9FvBZSoZzqXOjBFTKjRXKA3wv/PV3+Pk9JTnr9Zc3LnN89dr7tw+4+nLNf+R0te+c//Bf/3UZ95pJtyGlECEyjlEC58KOIVoiZgKxqSUe8EI+Vrp8/xX4JLLr6X8qdTIFouY2UCc4pxiFvOF0qVf//Vf/fjpZvv17z17jpaJveqcrQkWKSXGlBARqtIs2JGWrPCfFcpyolDmONIaN95Te+ii/BrT1BtyxF9ZoeQ7/uof/vv+J9chzGwQzQgxFvwZMU3YkzlT04NEZV6Ud1rYkznwSermwEuTpQUFLtUPSYmUMiXFZECiVsWS8ZVf/IVh0x++/NHjZ1iMaCFtRAquHLUvPLsQgpJL1I4UFlIq8zPzKAuZnRpXF0JkC0EqJKpZoCfVmibUXKo/+ZVf+vZpXf27OFfwlCdLZrgCj8yzaS69U8FJzmQoEHGiOD329KRWU2Rpgc9JLCaIWPYmCsUopJhAQdQxlN8MMOHez33uMz8zkbho1nOvSjQIMZWMHMtvlsVh0vXMCIJXmbs9zcQvN0RBAKc6wzMViKggObMCyIIbRbFo5cHyzSRyOlHIGNNcn5gibhINYTYvU4pSeaRXlwOyPMYV7s6Syk0eF1lIN8dgQ2EAry5jtpRUBMQJwPveuy+ZGalku3KKOscY04y3TE1psh9z5ixlQrdlqUUwORJ+tKP0SumFMAlCCV7LosoAQ1CamZqAZE7MPoylsaaHhZiIMeKd4meXKHP5M5cecWkYISRSUScr9JWYHeacvbdNjZnhyBKsioIqZgkjZWoKMdsR4ysJPj/L6QwRwYnLxthsNilxYTqcFrXSrG7OTYi+2ebCTVth5XcnxwVPAqMIWMnSxKMoRONdU/uGk/zAhUcp2ZpoSLLLWrqykuEpU8nSHJIUSbaCx4lvU7nXFo6LhXkxM3SI8agUovNDnehfePVdxozecFlL7yVk+oqFtqbsTE1kdmy6jKyyWGOWWSdyVK8Ju5PAzN5CUae5vMmyoR5jRMW9D+lLZrnrU0xzeYLZrOk54/l+g6xqb1nE5combGYzn46dPzmvybgArpihlNKRGVICCzFLpgqq6kKMH07PUhHUabnRZseki73R5DW9czjVTGVlE5l5MvtVFkHYQrGMpalh2gEW7Bdva4YahjgtImaQ+EDh81K2IblLj7w6FvqSgrHJ+k3jQ0qzYk0wiWnafB4lWUsAUo7TvswKfmVWN2bTrlPzpKzz7yL2Z5PETWVKMWVjoVp2uTIb6Wjx+D3mElPs3URhXqeGm5zbkZvT3C8ye46pMW3BDohkGOSthCOE8JfJrFvuYBRQrzPewmzrZPaYc/Cayz01inEMnpkBdOZbzXRQMl3MTFn4NOfsyABvGM+ePuf+m837n93tfvfTyw3b/sCTzZaL7Y4n2y33+p7Hmy2b/Z6n2x27w4Hn13u2w4HXu579EHhxvaMPI+v9nuvDwOW+Zwgjb/qBMQbe9AMhRtb7nn4YuDwMDCHk83Hkahg5xJHL/YGQIm/6A30IXPY9QwhshhF1CLfatl55962zpuXE15y3HbeahlVdsfKetqo48RUnTctpVXNa1zROaauKla9oak/tldZVtM5zUtc03tFWNbUqrfdlvKfzntp7WueonKPxPr+dUmk+r5ynVkfjHLXmca1zqHeOatV8cKvtfnzVVazahtOTlraquL1qWdUtJ01NU3nO2pq6quiqilVdc1J5nM/BrKqGtlIq52krR1fVtJWjrjxNVdHVNV1V5e9NRV15ujrP1dWe2lecNjW1c3RVXlBXl/F1ReU9OsT4rqvrP721aqjqlrO2wlc1p01N2zSsGs9J3bBqKtqq4qypOKkruspz1jSsKs+q8tROOW1qutrT+Yq6cjTOs6qqnE2n1M7R+pylxuf7Gu84qXIlKueovcvjKk9TjlNl5Nd++ie/uWqar4UU8KqEGHOzpYRXYQiJyjuGEKicK9eVMaXF+OV5vm9MCSda5slHp0KIy2P+XUWJFvNxGlfun87HlPAvr3f/yPWuXf5X7+Z/+YSjzTxK7XIb8rYHfftliw9baP3sM5bXjMU+7TiDAf83AAhAkx3cbvnUAAAAAElFTkSuQmCC);
	background-position:top right;
	height:42px;
	width:43px;
	top:0;
	left:127px
}
.menuPerforationVertical {
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAcAAAADCAYAAABfwxXFAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAAdSURBVHjaYvz//z8DLsCCTXD7islgHYz4dAIEGAC4fAjyXNpkHgAAAABJRU5ErkJggg==);
	background-repeat:repeat-y;
	width:160px
}
.menuPerforationVerticalLeft {
	background-position:0 0
}
.menuPerforationVerticalRight {
	background-position:100% 0;
	padding-left:5px
}
.perforationMenuHorizontal {
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAQAAAABCAYAAAD5PA/NAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAAbSURBVHjaYvz//z/D9hWT/zMAgWdELiNAgAEATn0G/8V7iLIAAAAASUVORK5CYII=);
	background-repeat:repeat-x;
	height:5px;
	width:165px
}
.perforationMenuHorizontalTop {
	background-position:bottom;
	margin-bottom:5px
}
.perforationMenuHorizontalBottom {
	background-position:bottom
}
.menuContainer {
	width:193px;
	position:relative;
	padding-bottom:10px
}
.wrapper_outer {
	width:159px;
	border-radius:5px;
	padding:5px;
	position:relative;
	left:3px
}
.wrapper_inner {
	width:100%;
	border:1px dashed #c2b9a7;
	border-radius:5px;
	padding-bottom:5px
}
.ribbonText {
	color:#EEE;
	font-family:sakura;
	font-size:20px;
	padding-top:25px;
	text-align:right;
	position:relative;
	top:-15px
}
.green,.orange,.red {
	font-size:12px;
	font-weight:700
}
.green {
	color:green
}
.red {
	color:red
}
.orange {
	color:orange
}
.textfield {
	width:80px;
	box-sizing:border-box;
	-webkit-box-sizing:border-box;
	-moz-box-sizing:border-box
}
.travelMap {
	background-image:url(../../images/maps/core3mapV1.jpg);
	background-repeat:no-repeat;
	background-position:bottom right;
	background-size:250px 250px
}
.ribbonLink {
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAAkCAYAAACjQ+sPAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAC4SURBVHjaVJBdCsIwEIRnt/FBbOsPKAiC5/HVQ3gYb+BlPIgPHkFB6A+pTexkqSiB5cvu7Owmcj4eTut8Blf57lJ0b7hHVSPECGUQAG6/WmBT5NDhAhUxiNQQwgiZqkHSJc0I9NE+RIwWBqShG0ozHqUkjchUfnw4ggEsRUtZuzBD5z4E60prJLHNEit9F6NY/l7x7Ro+Aa+mhbYDNPyNyntM/QRut5xjW5Zwz7pNYne93ZPhR4ABAOUfVDx+qWzTAAAAAElFTkSuQmCC);
	background-position:center;
	background-repeat:repeat-x;
	height:36px;
	width:570px
}
.leftDiagonal {
	background-image:url(data:image/jpeg;base64,/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAZAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQAAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQICAgICAgICAgICAwMDAwMDAwMDAwEBAQEBAQECAQECAgIBAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMD/8AAEQgAAwADAwERAAIRAQMRAf/EAaIAAAAGAgMBAAAAAAAAAAAAAAcIBgUECQMKAgEACwEAAAYDAQEBAAAAAAAAAAAABgUEAwcCCAEJAAoLEAACAQMEAQMDAgMDAwIGCXUBAgMEEQUSBiEHEyIACDEUQTIjFQlRQhZhJDMXUnGBGGKRJUOhsfAmNHIKGcHRNSfhUzaC8ZKiRFRzRUY3R2MoVVZXGrLC0uLyZIN0k4Rlo7PD0+MpOGbzdSo5OkhJSlhZWmdoaWp2d3h5eoWGh4iJipSVlpeYmZqkpaanqKmqtLW2t7i5usTFxsfIycrU1dbX2Nna5OXm5+jp6vT19vf4+foRAAIBAwIEBAMFBAQEBgYFbQECAxEEIRIFMQYAIhNBUQcyYRRxCEKBI5EVUqFiFjMJsSTB0UNy8BfhgjQlklMYY0TxorImNRlUNkVkJwpzg5NGdMLS4vJVZXVWN4SFo7PD0+PzKRqUpLTE1OT0laW1xdXl9ShHV2Y4doaWprbG1ub2Z3eHl6e3x9fn90hYaHiImKi4yNjo+DlJWWl5iZmpucnZ6fkqOkpaanqKmqq6ytrq+v/aAAwDAQACEQMRAD8A3bv/AEEfsf7o/wC+/wCzh/2XX+8P++/gvsi/5xaPC/1f0vpdX+rwujH/AHvVr/1fLxqf6tfX/9k=);
	height:auto;
	width:570px
}
.leftDiagonal2 {
	margin-bottom:5px;
	background-image:url(data:image/jpeg;base64,/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAZAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQAAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQICAgICAgICAgICAwMDAwMDAwMDAwEBAQEBAQECAQECAgIBAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMD/8AAEQgAAwADAwERAAIRAQMRAf/EAaIAAAAGAgMBAAAAAAAAAAAAAAcIBgUECQMKAgEACwEAAAYDAQEBAAAAAAAAAAAABgUEAwcCCAEJAAoLEAACAQMEAQMDAgMDAwIGCXUBAgMEEQUSBiEHEyIACDEUQTIjFQlRQhZhJDMXUnGBGGKRJUOhsfAmNHIKGcHRNSfhUzaC8ZKiRFRzRUY3R2MoVVZXGrLC0uLyZIN0k4Rlo7PD0+MpOGbzdSo5OkhJSlhZWmdoaWp2d3h5eoWGh4iJipSVlpeYmZqkpaanqKmqtLW2t7i5usTFxsfIycrU1dbX2Nna5OXm5+jp6vT19vf4+foRAAIBAwIEBAMFBAQEBgYFbQECAxEEIRIFMQYAIhNBUQcyYRRxCEKBI5EVUqFiFjMJsSTB0UNy8BfhgjQlklMYY0TxorImNRlUNkVkJwpzg5NGdMLS4vJVZXVWN4SFo7PD0+PzKRqUpLTE1OT0laW1xdXl9ShHV2Y4doaWprbG1ub2Z3eHl6e3x9fn90hYaHiImKi4yNjo+DlJWWl5iZmpucnZ6fkqOkpaanqKmqq6ytrq+v/aAAwDAQACEQMRAD8A3sv/ADz+D+D/AO+/6uH+jn+If777L2g/3mmn/V8/Cr/q09Kf96rq/wBX+3/1cev/2Q==);
	height:auto;
	padding-top:5px;
	width:540px
}
.perforationVertical1,.perforationVertical2 {
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAcAAAADCAYAAABfwxXFAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAAdSURBVHjaYvz//z8DLsCCTXD7islgHYz4dAIEGAC4fAjyXNpkHgAAAABJRU5ErkJggg==);
	background-repeat:repeat-y;
	height:auto
}
.perforationVertical1 {
	background-position:right;
	margin-right:5px;
	width:565px
}
.perforationVertical2 {
	background-position:left;
	padding-left:5px;
	width:560px;
	padding-bottom:5px
}
.perforationHorizontal {
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAQAAAABCAYAAAD5PA/NAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAAbSURBVHjaYvz//z/D9hWT/zMAgWdELiNAgAEATn0G/8V7iLIAAAAASUVORK5CYII=);
	background-position:bottom;
	background-repeat:repeat-x;
	height:5px;
	width:550px
}
.flipHorizontal {
	-moz-transform:scaleX(-1);
	-webkit-transform:scaleX(-1);
	-o-transform:scaleX(-1);
	transform:scaleX(-1);
	-ms-filter:fliph;
	filter:fliph
}
.tableColumns {
	font-weight:700;
	border-bottom:1px solid #000;
	border-top:1px solid #000;
	background-color:#BFBCAB;
	text-align:center;
	padding:2px
}
.tavernMessageBox,.tdBorder {
	border:1px inset #000
}
.tavernMessageBox ol {
	list-style:upper-roman inside
}
.tavernMessageBox ul {
	list-style:square inside
}
.topPlayersRow {
	text-align:left;
	padding:5px 5px 5px 10px
}
.row1 {
	background-image:url(data:image/jpeg;base64,/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAZAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQAAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQICAgICAgICAgICAwMDAwMDAwMDAwEBAQEBAQECAQECAgIBAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMD/8AAEQgAAwADAwERAAIRAQMRAf/EAaIAAAAGAgMBAAAAAAAAAAAAAAcIBgUECQMKAgEACwEAAAYDAQEBAAAAAAAAAAAABgUEAwcCCAEJAAoLEAACAQMEAQMDAgMDAwIGCXUBAgMEEQUSBiEHEyIACDEUQTIjFQlRQhZhJDMXUnGBGGKRJUOhsfAmNHIKGcHRNSfhUzaC8ZKiRFRzRUY3R2MoVVZXGrLC0uLyZIN0k4Rlo7PD0+MpOGbzdSo5OkhJSlhZWmdoaWp2d3h5eoWGh4iJipSVlpeYmZqkpaanqKmqtLW2t7i5usTFxsfIycrU1dbX2Nna5OXm5+jp6vT19vf4+foRAAIBAwIEBAMFBAQEBgYFbQECAxEEIRIFMQYAIhNBUQcyYRRxCEKBI5EVUqFiFjMJsSTB0UNy8BfhgjQlklMYY0TxorImNRlUNkVkJwpzg5NGdMLS4vJVZXVWN4SFo7PD0+PzKRqUpLTE1OT0laW1xdXl9ShHV2Y4doaWprbG1ub2Z3eHl6e3x9fn90hYaHiImKi4yNjo+DlJWWl5iZmpucnZ6fkqOkpaanqKmqq6ytrq+v/aAAwDAQACEQMRAD8A3sv/ADz+D+D/AO+/6uH+jn+If777L2g/3mmn/V8/Cr/q09Kf96rq/wBX+3/1cev/2Q==)
}
.row2 {
	background-image:url(data:image/jpeg;base64,/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAZAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQAAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQICAgICAgICAgICAwMDAwMDAwMDAwEBAQEBAQECAQECAgIBAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMD/8AAEQgAAwADAwERAAIRAQMRAf/EAaIAAAAGAgMBAAAAAAAAAAAAAAcIBgUECQMKAgEACwEAAAYDAQEBAAAAAAAAAAAABgUEAwcCCAEJAAoLEAACAQMEAQMDAgMDAwIGCXUBAgMEEQUSBiEHEyIACDEUQTIjFQlRQhZhJDMXUnGBGGKRJUOhsfAmNHIKGcHRNSfhUzaC8ZKiRFRzRUY3R2MoVVZXGrLC0uLyZIN0k4Rlo7PD0+MpOGbzdSo5OkhJSlhZWmdoaWp2d3h5eoWGh4iJipSVlpeYmZqkpaanqKmqtLW2t7i5usTFxsfIycrU1dbX2Nna5OXm5+jp6vT19vf4+foRAAIBAwIEBAMFBAQEBgYFbQECAxEEIRIFMQYAIhNBUQcyYRRxCEKBI5EVUqFiFjMJsSTB0UNy8BfhgjQlklMYY0TxorImNRlUNkVkJwpzg5NGdMLS4vJVZXVWN4SFo7PD0+PzKRqUpLTE1OT0laW1xdXl9ShHV2Y4doaWprbG1ub2Z3eHl6e3x9fn90hYaHiImKi4yNjo+DlJWWl5iZmpucnZ6fkqOkpaanqKmqq6ytrq+v/aAAwDAQACEQMRAD8A3bv/AEEfsf7o/wC+/wCzh/2XX+8P++/gvsi/5xaPC/1f0vpdX+rwujH/AHvVr/1fLxqf6tfX/9k=)
}
.newsItem {
	float:center
}
.tdTop {
	background-position:center;
	background-repeat:repeat-x;
	font-weight:700;
	border-bottom:1px solid #000;
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAqCAYAAAByfjF8AAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAABUSURBVHjaYvDw8AhnEhISesZw9+5dT6aTJ078YRDl5VZnEuLm+sRw/cSWBiYGIBhMBIO6hKgkMxMjozTD7Jkz7Rj+//9vy2Rvby/N9OXLl2sAAQYAE0MXY5BfHQoAAAAASUVORK5CYII=);
	text-align:center
}
.ribbonRank,.travelCoordinates,.travelRibbon {
	background-repeat:no-repeat
}
.travelRibbon {
	position:absolute;
	top:0;
	left:2px;
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAHoAAAB8CAYAAACmAKT5AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoTWFjaW50b3NoKSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpDQTIyRTEwMjkwNzIxMUUzQjFEOUYxOEM1OTE2MDBENyIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpDQTIyRTEwMzkwNzIxMUUzQjFEOUYxOEM1OTE2MDBENyI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkNBMjJFMTAwOTA3MjExRTNCMUQ5RjE4QzU5MTYwMEQ3IiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkNBMjJFMTAxOTA3MjExRTNCMUQ5RjE4QzU5MTYwMEQ3Ii8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+yXnNJAAAI59JREFUeNrsnQmU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);
	background-position:left top;
	display:inline;
	height:145px;
	padding-top:0;
	width:202px
}
.ribbonRank {
	background-image:url(data:image/png;base64,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);
	color:#FFF;
	float:left;
	display:inline;
	font-family:sans-serif;
	font-size:10px;
	height:90px;
	padding-top:25px;
	width:77px
}
a:active,a:hover,a:link,a:visited {
	font-family:segoe ui;
	text-decoration:none
}
.travelBackground td,.travelTable td {
	padding:2px
}
.travelTable {
	color:#FFF;
	height:150px;
	width:132px;
	background-size:100% auto
}
.travelCoordinates {
	background-image:url(data:image/png;base64,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);
	background-position:bottom;
	width:132px;
	height:35px
}
a:active,a:link,a:visited {
	color:#111;
	font-weight:700
}
a:hover {
	color:#555
}
.ac-menu,.ac-menu a,.ac-menu li,.ac-menu span,.ac-menu ul {
	border:0;
	outline:0;
	margin:0;
	padding:0
}
.ac-menu li {
	display:block;
	position:relative;
	text-align:center;
	font-family:sakura;
	font-size:12px;
	text-decoration:none;
	border-bottom:1px solid #c2b9a7;
	zoom:1;
	padding:5px
}
.hpcpsp,.stat_num,.widgetText {
	font-family:verdana;
	font-size:8px
}
.ac-menu>li:not(.nohover):hover {
	color:#c2b9a7;
	background:#c2b9a7
}
.listHeader {
	background:#84362a;
	color:#FFF
}
body {
	background:url(images/wallpaper.jpg) center center no-repeat fixed;
	-webkit-background-size:cover;
	-moz-background-size:cover;
	-o-background-size:cover;
	background-size:cover
}
.hpcpsp {
	font-weight:700;
	padding-right:3px;
	text-align:right
}
.widgetText {
	text-align:center
}
.bar {
	border:1px solid #3f3e3c;
	height:10px;
	text-align:left;
	width:100px
}
#popup_title,.stat_num,.subHeader,.topmenulinks {
	text-align:center
}
.stat_num {
	padding-top:0;
	padding-bottom:5px;
	vertical-align:top
}
.topmenulinks {
	font-family:sakura;
	font-size:17px;
	height:36px;
	padding-top:10px;
	width:570px
}
.contentTable,.table {
	border-spacing:0;
	border-collapse:collapse;
	padding:0
}
.topmenulinks a:active,.topmenulinks a:link,.topmenulinks a:visited {
	color:#000;
	font-family:sakura;
	font-size:18px;
	text-decoration:none
}
.subHeader,.subHeader a {
	font-family:sakura;
	font-size:21px
}
.topmenulinks a:hover {
	color:#FFF;
	text-decoration:none
}
.contentTable {
	border:0 solid #580000;
	width:950px
}
.table {
	border:1px solid #580000;
	margin-bottom:15px
}
.subHeader {
	border-bottom:0 solid #000;
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAqCAYAAAByfjF8AAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAABUSURBVHjaYvDw8AhnEhISesZw9+5dT6aTJ078YRDl5VZnEuLm+sRw/cSWBiYGIBhMBIO6hKgkMxMjozTD7Jkz7Rj+//9vy2Rvby/N9OXLl2sAAQYAE0MXY5BfHQoAAAAASUVORK5CYII=);
	background-repeat:repeat-x;
	height:32px;
	color:#612318;
	padding-top:10px
}
#popup_container {
	font-family:Arial,sans-serif;
	font-size:12px;
	min-width:300px;
	max-width:600px;
	color:#000;
	background-color:#fff;
	border:5px solid #999;
	-moz-border-radius:5px;
	-webkit-border-radius:5px;
	border-radius:5px
}
#popup_title {
	font-size:14px;
	font-weight:700;
	line-height:1.75em;
	color:#666;
	background:url(data:image/gif;base64,R0lGODlhBQAXANUAAPHx8fDw8Ozs7NHR0c7Ozs3Nze/v7+3t7dPS0tra2tjY2Ovq6uHh4efn5+Tl5c/Pz+3u7tzc3dzd3dTU1NTU1erq6trb29bW1tfW1uno6Obn5+Tk5d/e3+bm5unp6M/P0N/f3+/u78/Qz+Pj4+Dg4Ojo6N/e3tDQ0NjZ2OHg4d7f393c3N7e3uXl5ebn5tbX1uPi4uPj4tPT0+jp6czNzdXV1dLS0+Li4+Pi4+rr6tnY2OXk5OHh4AAAAAAAAAAAACH5BAAAAAAALAAAAAAFABcAAAZaQIBwSAwYjSGD0gA5NA+CaDRXWSwqs0ym5Ok0NA1Xy+HYbXA3WGzEIPFSDJWJwwJFVhGJxJLgJxQKOgooLxgXFxg1FBOLMggINggDk5MPIh8nDwSbmwU0BaBBADs=) top repeat-x #CCC;
	border:1px solid #FFF;
	border-bottom:solid 1px #999;
	cursor:default;
	margin:0;
	padding:0
}
#popup_content {
	background:url(data:image/gif;base64,R0lGODlhIAAgAPcAABJAuAFd4z1y0iFUtvn6/AgXjP79+KrE6l+S7C1kzwA9ukp93SBbz2mS2qe+5EuA3zZtzxxf1P778zRr1UJCnPn17aPI/2ml/E2H5S5iwiBk0Ymy9Rk6nOXl5Y219O7u7q3R/0uV+wBY3ABOy5q04QBKxIWm3TQ3mVOI4W2FwRJVy4us4pa9+QolkwBAsfn49qHH/i9oybHW/1yK2dLf8aXJ/xFKwkF52kN32xZIvTNy3AA5pHum7ISu8+rv8xI7pwtJure32gcdmfHx+H6p8jF764+9/sXJ5HSi7lRaqvj29Et91nud2+Pp9AA+rgE8qhZm4gYKgwBS0QBCtD6O+mGL1vz9/p3J/wBW2l6P5gBb6VeK4UV51BFTxmWV6QNQzJ7F/Orp6X5+vd3j8QBFw0J3z9HY6w5Qwwsqmv///CJGoi5m05Gv4gxMwJrF/xQ8sBZazlKG2c/S6CcslABV1juD7v/99ilh0put1mp9vYaTyHGX2mWV5oaKw6jM/3OLxA9Ox1mK5FFRpTxx12pqsW2g6ltpsW+d61hkr0dirx1JtEJbq1CD4AY3n1CC3wBEuC935ApKwQ0zrAwnnC9NpA1Dvg9Bp////////qKy2Cdgz36Hwvf39QgoowBU08TE4Zqn0mqX6DuI9fz8/T532GOV4I+39zp33xhj2VaH2DFQpWiW5zZx0Dhu2c/V6pi/+vz48RlWyQxPyTFOpfz8+kB43ABGx0hIoOLi8HOf6gg7o1WN3VmH2FyI1vP3+19xtjpwzNvg7ylbwCdg2DB7/yRo2Spp1AZIvOzr63Si83Cl+Xim8PL5/qHM/36x/yZr43eW0mKe//Pu6LLR9jJ34zd269vq+5O69y1m1IGs7wBX4AU/rVCS9FSe/1qY/zRv3wtUyQpJtQVU0D6D/ydt8LvK5gA7qAA6sR9e4J3D+53D/GBxtoSw9KbC7EyM+kCN90uP85G6+QpMwQxNxNji8wAwqN7k8jtx2E+B14ei1vLz9WCR4j986svU6BFi2/7+/yH5BAAAAAAALAAAAAAgACAAAAj/AC8JHEiwoMGDCBMqXMiwoUOBTTKlSDSL0qI8oII9LDgmhRpLGbjgWyJA2A8O61xtvISHw4BSRq40a+bGiLMLDxS10EOgoZU/jeJcAVEDRrYqvXIRSaYsWqsWhoYs/PdHVyEZICzA4JHPTD9o+w7xQeCNHxpEPRPiaXTVTw0w8ZjQuoRJXxUvWbYwcletxaaE9jjEkeFW3St2Ji6NooWpQaBAjBbgGDeswBGEKQYMrZGOhakNDcqlSeNgCWTJg74Re5NklMEmavbJsKDO84Yey3gx2bNkiyPUExKQQ1dAjsFMli64gWG7BxK8D27ceCD53oQ1miI8m9THYIoM3US9/7p2m88eNivYkCCxpNag65oYtNHA2mAiASG01PHQg8gWE/SkYYAdacwwwQTYxDePC3DkcIJBqpBChRZaFIEED4egwAUNSnzwAi/YrHGHfFO40EUOcxhEiYQBBKANFBisEgopDtjhYSp33BHLMVNM4cSJKRa0CATvtBgAFnSgooMxB0jgIT5nyPNIjy480QUAJ/xTUB7ChBCACGDSIcUItjSJzAtlKDBliU48oYIkghgEyg/cQIEFkp6MWSYsYaCpAJVOmLMNHEKIYVAwHNRSBB105PkFmQfwieY5bD6xAxA2RPHJQb8oAo8/Uow5wghkHFBBB5yUcY4Lbe7gZie3pKxVkBktTFCHOKOOUIIC7ZzKCTD1tLrDGZVEEURCeqBxCiRflOCsJjRI00EFK+TgxA47tAFIAYRoidAohqChAyTgKLCLNb74oC4z04RjzhnbUiCVQkMg0oIm1ESgASsx9OvvGSpUUgAFuDREwCYF/KBJMXB0MU8kkcwjiw2dREHIvA4dkUQBk7yRw8cASCJEFLcEYcVKAsnRRxInzDHHCYKI8cnJKNds8804FxQQADs=) 16px 16px no-repeat;
	margin:0;
	padding:1em 1.75em
}
#popup_content.alert {
	background-color:#fff;
	background-image:url(data:image/gif;base64,R0lGODlhIAAgAPcAABJAuAFd4z1y0iFUtvn6/AgXjP79+KrE6l+S7C1kzwA9ukp93SBbz2mS2qe+5EuA3zZtzxxf1P778zRr1UJCnPn17aPI/2ml/E2H5S5iwiBk0Ymy9Rk6nOXl5Y219O7u7q3R/0uV+wBY3ABOy5q04QBKxIWm3TQ3mVOI4W2FwRJVy4us4pa9+QolkwBAsfn49qHH/i9oybHW/1yK2dLf8aXJ/xFKwkF52kN32xZIvTNy3AA5pHum7ISu8+rv8xI7pwtJure32gcdmfHx+H6p8jF764+9/sXJ5HSi7lRaqvj29Et91nud2+Pp9AA+rgE8qhZm4gYKgwBS0QBCtD6O+mGL1vz9/p3J/wBW2l6P5gBb6VeK4UV51BFTxmWV6QNQzJ7F/Orp6X5+vd3j8QBFw0J3z9HY6w5Qwwsqmv///CJGoi5m05Gv4gxMwJrF/xQ8sBZazlKG2c/S6CcslABV1juD7v/99ilh0put1mp9vYaTyHGX2mWV5oaKw6jM/3OLxA9Ox1mK5FFRpTxx12pqsW2g6ltpsW+d61hkr0dirx1JtEJbq1CD4AY3n1CC3wBEuC935ApKwQ0zrAwnnC9NpA1Dvg9Bp////////qKy2Cdgz36Hwvf39QgoowBU08TE4Zqn0mqX6DuI9fz8/T532GOV4I+39zp33xhj2VaH2DFQpWiW5zZx0Dhu2c/V6pi/+vz48RlWyQxPyTFOpfz8+kB43ABGx0hIoOLi8HOf6gg7o1WN3VmH2FyI1vP3+19xtjpwzNvg7ylbwCdg2DB7/yRo2Spp1AZIvOzr63Si83Cl+Xim8PL5/qHM/36x/yZr43eW0mKe//Pu6LLR9jJ34zd269vq+5O69y1m1IGs7wBX4AU/rVCS9FSe/1qY/zRv3wtUyQpJtQVU0D6D/ydt8LvK5gA7qAA6sR9e4J3D+53D/GBxtoSw9KbC7EyM+kCN90uP85G6+QpMwQxNxNji8wAwqN7k8jtx2E+B14ei1vLz9WCR4j986svU6BFi2/7+/yH5BAAAAAAALAAAAAAgACAAAAj/AC8JHEiwoMGDCBMqXMiwoUOBTTKlSDSL0qI8oII9LDgmhRpLGbjgWyJA2A8O61xtvISHw4BSRq40a+bGiLMLDxS10EOgoZU/jeJcAVEDRrYqvXIRSaYsWqsWhoYs/PdHVyEZICzA4JHPTD9o+w7xQeCNHxpEPRPiaXTVTw0w8ZjQuoRJXxUvWbYwcletxaaE9jjEkeFW3St2Ji6NooWpQaBAjBbgGDeswBGEKQYMrZGOhakNDcqlSeNgCWTJg74Re5NklMEmavbJsKDO84Yey3gx2bNkiyPUExKQQ1dAjsFMli64gWG7BxK8D27ceCD53oQ1miI8m9THYIoM3US9/7p2m88eNivYkCCxpNag65oYtNHA2mAiASG01PHQg8gWE/SkYYAdacwwwQTYxDePC3DkcIJBqpBChRZaFIEED4egwAUNSnzwAi/YrHGHfFO40EUOcxhEiYQBBKANFBisEgopDtjhYSp33BHLMVNM4cSJKRa0CATvtBgAFnSgooMxB0jgIT5nyPNIjy480QUAJ/xTUB7ChBCACGDSIcUItjSJzAtlKDBliU48oYIkghgEyg/cQIEFkp6MWSYsYaCpAJVOmLMNHEKIYVAwHNRSBB105PkFmQfwieY5bD6xAxA2RPHJQb8oAo8/Uow5wghkHFBBB5yUcY4Lbe7gZie3pKxVkBktTFCHOKOOUIIC7ZzKCTD1tLrDGZVEEURCeqBxCiRflOCsJjRI00EFK+TgxA47tAFIAYRoidAohqChAyTgKLCLNb74oC4z04RjzhnbUiCVQkMg0oIm1ESgASsx9OvvGSpUUgAFuDREwCYF/KBJMXB0MU8kkcwjiw2dREHIvA4dkUQBk7yRw8cASCJEFLcEYcVKAsnRRxInzDHHCYKI8cnJKNds8804FxQQADs=)
}
#popup_content.confirm {
	background-color:#fff;
	background-image:url(data:image/gif;base64,R0lGODlhIAAgAPcAAPT09NlzZ87W18TExObm5vz8/Onp6ednVus2Gf9KS7oRC/Xj4/6lpv+qqshDPf81NaSYmPr6+uJ6bbcaF84lF9UrGP/Y2Piin9tcUf+1tf+xscVra6MCANRGPru7u9Sxrf7BvXt7e/1YWMksJcnJyeuLhfz4+Ks6Ov/Cw//T06ITErMkIVwqKuIaAK4hHvV9eN3d3b0uKdNTS4KCgmMqKv+GhscdEWorK7MJBooNC+nw8O/v7+vHxdQ5M0RERKsJBf++vt00HcIDANu4tP/Kyq0eG/X+/tra2vj4+NudneyCfKylpWYGBtMWA7tZVqwVE6QNDJ0LCdwdALMOC4UnJ/78/M+uqs/OzsY5MqamproNBfP6+tbi5P+fnmlDQ38JCOIqC97o6eI8I/+5ueLi4qoBAKMdHZ4FAv97evX6+XJxcVUqKuTu7+Pu78M2MQICArwNC+ZBJv/t7eTj48sVBeUUAMMRBbkrJ/9mZqUXFcEzLsQaERgYGC0tLf////Pz8/7+/vHx8dji4rMwMKoaGP+YmMSem+i/v/7z8++fm38WFuXp6dm1r5JlZfJqVP7//8qpp8IZBuMxLu7t7f39/epDJZGAgO5QNOTBvP9ratxrZNTo6XF7fPuBcZ8PDvYaGb8xLOGrq2dnZ8xIQHEoKOj3+NysrP+Vlv1APnc2Nl5oadXV1fOXkfeyrM/e4Vk/P/fa1f6QjcLAwPXW1v6ztP8qKszJyfL8/rSvr+9AG/+3uPJkS/+5t/jr6/nv7//q6v/s67BnZ+gxDq0sLHlXV2QREbcCAIw/P9zOy9KQkP/k5OPp6vfr69WMjP/Pz8anpe/6+seoqG0iIte2st+9uVlAQPDY2MAVDbRIQ9Gtp96rpfrNzffHx/RrZ+vW0sQ/Ock9N+S3t+S+vr60tJYAAJMLCf/SzZGRkZudnecxJ/Ly8Ojy9O7x8ezs7Mx2dr84NLg7O4JkZIhgYI9qavFbQegfAP/b231JSfzc3O5JLf+iotaMfbBFRZd+frtPT5qKiiH5BAAAAAAALAAAAAAgACAAAAj/AP0IHEiwoMGDCBMqXMgwyYpB0QAxnOjn0BQJGKKMo7iwCjwZwMyNOEaGY8JQU1rZS6HkDIQCJg36GqTJHhEUY7BQORKzYLMJIJwByZChZT+YPf30IhQgBRBaDRh0AUflSlI/7mKAQKFrFrdCp15AmQcgJqAFT0oQ0bDNyBYGNWo4UDSg5wY9IMZoQCQQHxo03VTIC8QREA8oJVA0sLdIzYw0DPBk6vAFF8cq/hwAaaDBD7o3b64AwyNCRJ577SiGe8JKVxcLfrKAXgWoS4IEPb4smWhikIwx+jJU8ePhDR8CfuSIQIWKUCoYCx8leXJBQyFlAmG86UPYj75aDyR9//mHFCGzYTI6xaIl0U8BPj4oCZST4FM6F6R4Jky2wlE9BLA8MlAfohCUiBCR5GZJBAgtYAYGudRxQBrt+eFDCANRog4WxlCwgjS2ILTBHbvUA4Y3WyAhHyUhZOEHIAUgYYQ2OGgxwhfx/GGQNSpggEAdEpSyAwARwBSBRAVEAAA7OjhQhg0uFCOLQU68Q08LQSCzjAE7/FEADI7tEMEfOxjQxgc4/EBBDsRMQpA4nhwgTAv7cAHDHAa4OQNoHkTQjgFzwCCIE2UoMAETuwlUBT/fXNICGLa4wkUYbaxziwCqcNIGNOuwEYYgmxhSBgd75OAFcn6YkscBYEghRQBWZLHzASNDDIEJDzxQM8Q0H1gByTPYcMDBD4e+5McJo1TiqhRNNEsHHXZEq8W0OKT5ww9nCHsGOQqU48UcfpjRQT5xxCGGGEGkW8G6FLRrgw177HHNNQrUOwUOcESxBgl+BPMEFm64oQcoMcRwx8ErJOxCEUUQQkgeeaiggieeRBEFE9XwO0cjN3TcMQ030CAyDSyQzMLJJ6+h8sprvHIOqupcMcDMNNds8801k0BAhVclFBAAOw==)
}
#popup_content.prompt {
	background-color:#fff;
	background-image:url(data:image/gif;base64,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)
}
#popup_message {
	padding-left:48px
}
.tdDiv,td {
	padding:5px;
	text-align:center
}
#popup_panel {
	text-align:center;
	margin:1em 0 0 1em
}
#popup_prompt {
	margin:.5em 0
}
button,input {
	overflow:visible;
	cursor:pointer
}
.ac-menu li>a,.subHeader a:hover {
	color:#000;
	text-decoration:none
}
td {
	font-size:12px
}
.subHeader a:active,.subHeader a:link,.subHeader a:visited {
	color:#612318;
	text-decoration:none
}
.footLinks {
	text-align:center
}
.tooltipster-default {
	border-radius:5px;
	border:2px solid #000;
	background:#4c4c4c;
	color:#fff
}
.tooltipster-default .tooltipster-content {
	font-family:Arial,sans-serif;
	font-size:14px;
	line-height:16px;
	overflow:hidden;
	padding:8px 10px
}
.tooltipster-icon {
	cursor:help;
	margin-left:4px
}
.tooltipster-base {
	font-size:0;
	line-height:0;
	position:absolute;
	left:0;
	top:0;
	z-index:9999999;
	pointer-events:none;
	width:auto;
	overflow:visible;
	padding:0
}
.tooltipster-base .tooltipster-content {
	overflow:hidden
}
.tooltipster-arrow {
	display:block;
	text-align:center;
	width:100%;
	height:100%;
	position:absolute;
	top:0;
	left:0;
	z-index:-1
}
.tooltipster-arrow span,.tooltipster-arrow-border {
	display:block;
	width:0;
	height:0;
	position:absolute
}
.tooltipster-arrow-top span,.tooltipster-arrow-top-left span,.tooltipster-arrow-top-right span {
	border-left:8px solid transparent!important;
	border-right:8px solid transparent!important;
	border-top:8px solid;
	bottom:-7px
}
.tooltipster-arrow-top .tooltipster-arrow-border,.tooltipster-arrow-top-left .tooltipster-arrow-border,.tooltipster-arrow-top-right .tooltipster-arrow-border {
	border-left:9px solid transparent!important;
	border-right:9px solid transparent!important;
	border-top:9px solid;
	bottom:-7px
}
.tooltipster-arrow-bottom span,.tooltipster-arrow-bottom-left span,.tooltipster-arrow-bottom-right span {
	border-left:8px solid transparent!important;
	border-right:8px solid transparent!important;
	border-bottom:8px solid;
	top:-7px
}
.tooltipster-arrow-bottom .tooltipster-arrow-border,.tooltipster-arrow-bottom-left .tooltipster-arrow-border,.tooltipster-arrow-bottom-right .tooltipster-arrow-border {
	border-left:9px solid transparent!important;
	border-right:9px solid transparent!important;
	border-bottom:9px solid;
	top:-7px
}
.tooltipster-arrow-bottom .tooltipster-arrow-border,.tooltipster-arrow-bottom span,.tooltipster-arrow-top .tooltipster-arrow-border,.tooltipster-arrow-top span {
	left:0;
	right:0;
	margin:0 auto
}
.tooltipster-arrow-bottom-left span,.tooltipster-arrow-top-left span {
	left:6px
}
.tooltipster-arrow-bottom-left .tooltipster-arrow-border,.tooltipster-arrow-top-left .tooltipster-arrow-border {
	left:5px
}
.tooltipster-arrow-bottom-right span,.tooltipster-arrow-top-right span {
	right:6px
}
.tooltipster-arrow-bottom-right .tooltipster-arrow-border,.tooltipster-arrow-top-right .tooltipster-arrow-border {
	right:5px
}
.tooltipster-arrow-left .tooltipster-arrow-border,.tooltipster-arrow-left span {
	border-top:8px solid transparent!important;
	border-bottom:8px solid transparent!important;
	border-left:8px solid;
	top:50%;
	margin-top:-7px;
	right:-7px
}
.tooltipster-arrow-left .tooltipster-arrow-border {
	border-top:9px solid transparent!important;
	border-bottom:9px solid transparent!important;
	border-left:9px solid;
	margin-top:-8px
}
.tooltipster-arrow-right .tooltipster-arrow-border,.tooltipster-arrow-right span {
	border-top:8px solid transparent!important;
	border-bottom:8px solid transparent!important;
	border-right:8px solid;
	top:50%;
	margin-top:-7px;
	left:-7px
}
.tooltipster-arrow-right .tooltipster-arrow-border {
	border-top:9px solid transparent!important;
	border-bottom:9px solid transparent!important;
	border-right:9px solid;
	margin-top:-8px
}
.tooltipster-fade {
	opacity:0;
	-webkit-transition-property:opacity;
	-moz-transition-property:opacity;
	-o-transition-property:opacity;
	-ms-transition-property:opacity;
	transition-property:opacity
}
.tooltipster-fade-show {
	opacity:1
}
.tooltipster-grow {
	-webkit-transform:scale(0,0);
	-moz-transform:scale(0,0);
	-o-transform:scale(0,0);
	-ms-transform:scale(0,0);
	transform:scale(0,0);
	-webkit-transition-property:0;
	-moz-transition-property:0;
	-o-transition-property:0;
	-ms-transition-property:0;
	transition-property:transform;
	-webkit-backface-visibility:hidden
}
.tooltipster-grow-show {
	-webkit-transform:scale(1,1);
	-moz-transform:scale(1,1);
	-o-transform:scale(1,1);
	-ms-transform:scale(1,1);
	transform:scale(1,1);
	transition-timing-function:cubic-bezier(.175,.885,.32,1.15)
}
.tooltipster-swing {
	opacity:0;
	-webkit-transform:rotateZ(4deg);
	-moz-transform:rotateZ(4deg);
	-o-transform:rotateZ(4deg);
	-ms-transform:rotateZ(4deg);
	transform:rotateZ(4deg);
	-webkit-transition-property:0 opacity;
	-moz-transition-property:0;
	-o-transition-property:0;
	-ms-transition-property:0;
	transition-property:transform
}
.tooltipster-swing-show {
	opacity:1;
	-webkit-transform:rotateZ(0);
	-moz-transform:rotateZ(0);
	-o-transform:rotateZ(0);
	-ms-transform:rotateZ(0);
	transform:rotateZ(0);
	-webkit-transition-timing-function:cubic-bezier(.23,.635,.495,2.4);
	-moz-transition-timing-function:cubic-bezier(.23,.635,.495,2.4);
	-ms-transition-timing-function:cubic-bezier(.23,.635,.495,2.4);
	-o-transition-timing-function:cubic-bezier(.23,.635,.495,2.4);
	transition-timing-function:cubic-bezier(.23,.635,.495,2.4)
}
.tooltipster-fall {
	top:0;
	-webkit-transition-property:top;
	-moz-transition-property:top;
	-o-transition-property:top;
	-ms-transition-property:top;
	transition-property:top;
	transition-timing-function:cubic-bezier(.175,.885,.32,1.15)
}
.tooltipster-fall.tooltipster-dying {
	-webkit-transition-property:all;
	-moz-transition-property:all;
	-o-transition-property:all;
	-ms-transition-property:all;
	transition-property:all;
	top:0!important;
	opacity:0
}
.tooltipster-slide {
	left:-40px;
	-webkit-transition-property:left;
	-moz-transition-property:left;
	-o-transition-property:left;
	-ms-transition-property:left;
	transition-property:left;
	-webkit-transition-timing-function:cubic-bezier(.175,.885,.32,1.15);
	-moz-transition-timing-function:cubic-bezier(.175,.885,.32,1.15);
	-ms-transition-timing-function:cubic-bezier(.175,.885,.32,1.15);
	-o-transition-timing-function:cubic-bezier(.175,.885,.32,1.15);
	transition-timing-function:cubic-bezier(.175,.885,.32,1.15)
}
.tooltipster-slide.tooltipster-dying {
	-webkit-transition-property:all;
	-moz-transition-property:all;
	-o-transition-property:all;
	-ms-transition-property:all;
	transition-property:all;
	left:0!important;
	opacity:0
}
.tooltipster-content-changing {
	opacity:.5;
	-webkit-transform:scale(1.1,1.1);
	-moz-transform:scale(1.1,1.1);
	-o-transform:scale(1.1,1.1);
	-ms-transform:scale(1.1,1.1);
	transform:scale(1.1,1.1)
}
.tooltipster-noir {
	border-radius:0;
	border:3px solid #2c2c2c;
	background:#fff;
	color:#2c2c2c
}
.tooltipster-noir .tooltipster-content {
	font-family:Georgia,serif;
	font-size:14px;
	line-height:16px;
	padding:8px 10px
}
table.sortable tbody tr:nth-child(2n) td {
	background-image:url(data:image/jpeg;base64,/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAZAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQAAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQICAgICAgICAgICAwMDAwMDAwMDAwEBAQEBAQECAQECAgIBAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMD/8AAEQgAAwADAwERAAIRAQMRAf/EAaIAAAAGAgMBAAAAAAAAAAAAAAcIBgUECQMKAgEACwEAAAYDAQEBAAAAAAAAAAAABgUEAwcCCAEJAAoLEAACAQMEAQMDAgMDAwIGCXUBAgMEEQUSBiEHEyIACDEUQTIjFQlRQhZhJDMXUnGBGGKRJUOhsfAmNHIKGcHRNSfhUzaC8ZKiRFRzRUY3R2MoVVZXGrLC0uLyZIN0k4Rlo7PD0+MpOGbzdSo5OkhJSlhZWmdoaWp2d3h5eoWGh4iJipSVlpeYmZqkpaanqKmqtLW2t7i5usTFxsfIycrU1dbX2Nna5OXm5+jp6vT19vf4+foRAAIBAwIEBAMFBAQEBgYFbQECAxEEIRIFMQYAIhNBUQcyYRRxCEKBI5EVUqFiFjMJsSTB0UNy8BfhgjQlklMYY0TxorImNRlUNkVkJwpzg5NGdMLS4vJVZXVWN4SFo7PD0+PzKRqUpLTE1OT0laW1xdXl9ShHV2Y4doaWprbG1ub2Z3eHl6e3x9fn90hYaHiImKi4yNjo+DlJWWl5iZmpucnZ6fkqOkpaanqKmqq6ytrq+v/aAAwDAQACEQMRAD8A3sv/ADz+D+D/AO+/6uH+jn+If777L2g/3mmn/V8/Cr/q09Kf96rq/wBX+3/1cev/2Q==);
}
table.sortable tbody tr:nth-child(2n+1) td {
	background-image: url(data:image/jpeg;base64,/9j/4AAQSkZJRgABAgAAZABkAAD/7AARRHVja3kAAQAEAAAAZAAA/+4ADkFkb2JlAGTAAAAAAf/bAIQAAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQICAgICAgICAgICAwMDAwMDAwMDAwEBAQEBAQECAQECAgIBAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMD/8AAEQgAAwADAwERAAIRAQMRAf/EAaIAAAAGAgMBAAAAAAAAAAAAAAcIBgUECQMKAgEACwEAAAYDAQEBAAAAAAAAAAAABgUEAwcCCAEJAAoLEAACAQMEAQMDAgMDAwIGCXUBAgMEEQUSBiEHEyIACDEUQTIjFQlRQhZhJDMXUnGBGGKRJUOhsfAmNHIKGcHRNSfhUzaC8ZKiRFRzRUY3R2MoVVZXGrLC0uLyZIN0k4Rlo7PD0+MpOGbzdSo5OkhJSlhZWmdoaWp2d3h5eoWGh4iJipSVlpeYmZqkpaanqKmqtLW2t7i5usTFxsfIycrU1dbX2Nna5OXm5+jp6vT19vf4+foRAAIBAwIEBAMFBAQEBgYFbQECAxEEIRIFMQYAIhNBUQcyYRRxCEKBI5EVUqFiFjMJsSTB0UNy8BfhgjQlklMYY0TxorImNRlUNkVkJwpzg5NGdMLS4vJVZXVWN4SFo7PD0+PzKRqUpLTE1OT0laW1xdXl9ShHV2Y4doaWprbG1ub2Z3eHl6e3x9fn90hYaHiImKi4yNjo+DlJWWl5iZmpucnZ6fkqOkpaanqKmqq6ytrq+v/aAAwDAQACEQMRAD8A3bv/AEEfsf7o/wC+/wCzh/2XX+8P++/gvsi/5xaPC/1f0vpdX+rwujH/AHvVr/1fLxqf6tfX/9k=);
}

</style>
{/literal}

<script>
  var the_id={$the_id};
</script>
{literal}
  <script>
    //function to animate open and close on hover of select boxes
    function SelectSlide(select) {
    if (($(select).attr("size")) == 1 && select[select.selectedIndex].value == 'default') {
    var mi = "inc";
    var s = 1;

    setInterval(function() {

    if (s < select.length) {
        s = s + 1;
        $(select).attr("size", s);
      }

    }, select.length + 2);
  } else if (($(select).attr("size")) == select.length) {
    var mi = "dec";
    var s = select.length;
    setInterval(function() {
      if (s > 1) {
        s--;
        $(select).attr("size", s);
      }
    }, select.length);
  }
}

//global var used for weapon_selects
var previous;

function ShowTarget() {
  var count = 0;
  var filled = 0;
  var selects = $(".select-wrapper").not($('#select_target'));

  for (var i = 0; i < selects.length; i++) {
    if ($(selects[i]).attr('id') != 'target_select') {
      if ($(selects[i]).is(':visible')) {
        count++;

        if (selects[i].value != 'default') {
          filled++;
        }
      }
    }
  }
  if (count == filled) {
    $('#target_select').show();

    $('#target_select option').each(function() {
      var action_mode = $('#action_select option:selected').text();
      var targeting_mode = '';
      if (action_mode == 'Jutsus') {
        targeting_mode = $('#jutsu_select option:selected').attr('class');
      } else if (action_mode == 'Weapons') {
        targeting_mode = $('#weapon_attack_select option:selected').attr('class');
      } else if (action_mode == 'Items') {
        targeting_mode = $('#item_attack_select option:selected').attr('class');
      } else {
        targeting_mode = '';
      }

      targeting_mode = targeting_mode.replace(' row1', '');
      targeting_mode = targeting_mode.replace(' row2', '');

      //self,opponent,ally,other,target,team,all,allOthers,rivalTeams
      if (targeting_mode == 'self') {
        var target_class = $(this).attr('class');
        target_class = target_class.replace(' row1', '');
        target_class = target_class.replace(' row2', '');

        if (target_class == 'self') {
          this.style.visibility = 'visible';
        } else if (target_class == 'ally') {
          this.style.visibility = 'hidden';
        } else if (target_class == 'opponent') {
          this.style.visibility = 'hidden';
        } else {
          this.style.visibility = 'visible';
        }
      } else if (targeting_mode == 'opponent') {
        var target_class = $(this).attr('class');
        target_class = target_class.replace(' row1', '');
        target_class = target_class.replace(' row2', '');

        if (target_class == 'self') {
          this.style.visibility = 'hidden';
        } else if (target_class == 'ally') {
          this.style.visibility = 'hidden';
        } else if (target_class == 'opponent') {
          this.style.visibility = 'visible';
        } else {
          this.style.visibility = 'visible';
        }
      } else if (targeting_mode == 'ally') {
        var target_class = $(this).attr('class');
        target_class = target_class.replace(' row1', '');
        target_class = target_class.replace(' row2', '');

        if (target_class == 'self') {
          this.style.visibility = 'hidden';
        } else if (target_class == 'ally') {
          this.style.visibility = 'visible';
        } else if (target_class == 'opponent') {
          this.style.visibility = 'hidden';
        } else {
          this.style.visibility = 'visible';
        }
      } else if (targeting_mode == 'other') {
        var target_class = $(this).attr('class');
        target_class = target_class.replace(' row1', '');
        target_class = target_class.replace(' row2', '');

        if (target_class == 'self') {
          this.style.visibility = 'hidden';
        } else if (target_class == 'ally') {
          this.style.visibility = 'visible';
        } else if (target_class == 'opponent') {
          this.style.visibility = 'visible';
        } else {
          this.style.visibility = 'visible';
        }
      } else if (targeting_mode == 'target') {
        this.style.visibility = 'visible';
      } else if (targeting_mode == 'team') {
        this.style.visibility = 'visible';
      } else if (targeting_mode == 'all') {
        this.style.visibility = 'visible';
      } else if (targeting_mode == 'allOther') {
        this.style.visibility = 'visible';
      } else if (targeting_mode == 'rivalTeams') {
        this.style.visibility = 'visible';
      } else //default if target is not set make all targets visible
      {
        this.style.visibility = 'visible';
      }
    });

  } else {
    $('#target_select').hide();
    $('#button').hide();
    $('#target_select option[value=default]').prop('selected', true);
  }
}

//un hides the elements marked by previous
function showOldSelection() {

  var previous_elements = getElementsByClassName(previous);

  for (var i = 0; i < previous_elements.length; i++) {
    previous_elements[i].style.visibility = 'visible';
  }
}

//hides all members of a class
function hideOtherCopies(selectElement) {
  var x = $('#' + selectElement.id + ' option:selected').attr('class');
  var elements = getElementsByClassName(x);

  for (var i = 0; i < elements.length; i++) {
    elements[i].style.visibility = 'hidden';
  }

  $(':focus').blur();
}

//gets element array by class name
function getElementsByClassName(className) {
  var matchingItems = [];
  var allElements = document.getElementsByTagName("*");

  for (var i = 0; i < allElements.length; i++) {
    if (allElements[i].className == className) {
      matchingItems.push(allElements[i]);
    }
  }

  return matchingItems;
}

    var timer = setInterval(function()
    { 
      if($('#turn_timer').text() > 0)
      {
        $('#turn_timer').text($('#turn_timer').text()-1);
      }
      else if($('#turn_timer').text() == 0 && $('#allow_refresh').is(':checked') )
      {
        $('#refresh_button').click();
      }
      else
      {
        $('#turn_timer').text('0');
      }
    }, 1000);

    var check_for_end_of_turn = setInterval(function()
    {
      $.get(window.location.protocol + "//" + window.location.host + "/" + "clean_room/combat_backend/?what_to_get=turn_counter&id=" + the_id, function(data, status)
      { 
        if( data + 1 > $('.turn_counter').text() && $('#allow_refresh').is(':checked') )
        {
          $('#refresh_button').click();
        }
      });
    },3000);

    setTimeout( function(){
    $(document).ready(function() {
    $(document).keypress(function(e) {
    if (e.which == 13) {
    if ($('#button').is(':visible')) {
    $('#button').click();
    }
    }
    });}, 1000);

    //hiding all things be default except action_select
    $('#weapon_attack_select').hide();
    $('select[name^="jutsu_weapon_select-').hide();
    $('#item_attack_select').hide();
    $('#action_select').prop('selectedIndex', 1);
    $('#jutsu_select').prop('selectedIndex', $('#jutsu_select option').length - 1);
    $('[class^="round"]').hide();
    $('[class^="details_round"]').hide();
    $('[class^="round' + ($('.turn_counter').text() - 1)+'"]').show();
    $('[class^="details_round' + ($('.turn_counter').text() - 1)+'_'+( $('.owner').attr('value') )+'"]').show();
    var index = 0;
    var set_index = false;
    $('#target_select option').each(function() {

    if (this.className == 'opponent') {
    this.style.visibility = 'visible';

    if (set_index === false) {
    set_index = true;
    $('#target_select').prop('selectedIndex', index);
    }
    } else {
    this.style.visibility = 'hidden';
    }
    index++;
    });
    $('#button').text('Attack!');
    $('#button').val('doJutsu|');

    $('#button').prop('disabled', true);

    setTimeout(function(){ $('#button').prop('disabled', false); }, 750);

    $('select[name^="jutsu_weapon_select"]').each(function(){
    console.log(this.length);
    if(this.length == 1)
    {

    this.disabled = true;
    }
    });

    //setting animation to occur on hover
    $(".select-wrapper").hover(function() {
    SelectSlide(this);
    });

    //closing selects automaticly on change
    $(".select-wrapper").change(function() {
    SelectSlide(this);
    });

    //on change of action select
    $('#action_select').change(function() {
    selected_value = this[this.selectedIndex].text;

    $('.select-wrapper').not(this).hide();

    var temp_index = this.selectedIndex;
    $('.select-wrapper option[value=default]').prop('selected', true);
    this.selectedIndex = temp_index;

    if (selected_value == 'Jutsus') {
    $('#jutsu_select').show();
    } else {
    $('#jutsu_select').hide();
    }

    if (selected_value == 'Weapons') {
    $('#weapon_attack_select').show();
    } else {
    $('#weapon_attack_select').hide();
    }

    if (selected_value == 'Items') {
    $('#item_attack_select').show();
    } else {
    $('#item_attack_select').hide();
    }

    ShowTarget();
    });

    $('#jutsu_select').change(function() {
    $('#target_select option[value=default]').prop('selected', true);
    $('select[name^="jutsu_weapon_select-"] option').css("visibility", "visible");
    $('select[name^="jutsu_weapon_select-"]').hide();
    $('select[name^="jutsu_weapon_select-' + this.value + '"]').show();
    var collection = $('select[name^="jutsu_weapon_select-"]');

    for (var i = 0; i < collection.length; i++) {
      collection[i].options.selectedIndex = 0;
    }
    ShowTarget();
  });

  $('select[name^="jutsu_weapon_select-').focus(function() {
    //handle previous
    previous = $('#' + this.id + ' option:selected').attr('class');
  }).change(function() {
    //handle current
    showOldSelection();
    hideOtherCopies(this);
    ShowTarget();
  });

  $('#weapon_attack_select').change(function() {
    ShowTarget();
  });

  $('#item_attack_select').change(function() {
    ShowTarget();
  });

  $('#target_select').change(function() {
    if ($('#target_select option:selected').val() != 'default') {
      $('#button').show();

      var action_type = $('#action_select option:selected').text();

      if (action_type == 'Jutsus') {
        $('#button').val('doJutsu|');
        $('#button').text('GO!');
      } else if (action_type == 'Weapons') {
        $('#button').val('useWeapon|');
        $('#button').text('GO!');
      } else if (action_type == 'Items') {
        $('#button').val('useItem|');
        $('#button').text('GO!');
      } else {
        $('#button').val('error');
        $('#button').text('error');
      }
    } else {
      $('#button').hide();
      $('#button').val('');
      $('#button').text('');
    }
  });

  //setting every other row stuffs
  $('.select-wrapper').each(function() {
    var flag = 1;
    $('#' + this.id + ' option').each(function() {
      $(this).addClass('row' + flag);

      if (flag == 1) {
        flag = 2;
      } else {
        flag = 1;
      }

    });
  })
  
  $('[class^="header_round"]').click( function()
  { 
    $('[class^="'+'round' + $(this).text().match(/\d+/)+'"]').toggle();
  });
  
  $('[class^="round"]').click( function()
  { 
    if( $(this).attr('class').split(' ')[0].length > 8 )
    {
      $(('[class="details_'+$(this).attr('class').split(' ')[0]+'"]')).toggle();
    }
  });
  
});
  </script>
{/literal}

<div align ="left">
	<form action="" id="form" method="post">
		<input name="battle_id_box" placeholder="battle_id" type="text" value="{$battle_id_box_default}">
		<input type="submit" name="button" value="joinBattle">
		<input type="submit" id="refresh_button" name="button" value="refreshBattle">
		<input type="submit" name="button" value="resetBattle">
		<input type="submit" name="button" value="killBattle">
    <br>
    {if $allow_refresh == 'on'}
      allow timer refresh: <input type="checkbox" name="allow_refresh" id="allow_refresh" checked>
    {else}
      allow timer refresh: <input type="checkbox" name="allow_refresh" id="allow_refresh">
    {/if}
		<br>
		<br>
    syntax: username,team,human/ai|
    <br>
		<textarea rows="4" cols="25" form="form" name="user_username_box" placeholder="username,team,human/ai|" type="text" value="{$user_username_box_default}">{$user_username_box_default}</textarea>
		<input type="submit" name="button" value="addUsers">
		<input type="submit" name="button" value="removeUser">
		<input type="submit" name="button" value="restoreUser">
		<input type="submit" name="button" value="killUser">
		<br>
		<br>
		<input name="data_value_box"    placeholder="value" type="text" value="{$data_value_box_default}">
		<input name="data_target_box"   placeholder="target;more;more" type="text" value="{$data_target_box_default}">
		<input name="data_username_box" placeholder="username" type="text" value="{$data_username_box_default}">
		<input type="submit" name="button" value="setData">
		<br>
		<br>
		<input name="acting_user" placeholder="acting user" type="text" value="{$acting_user}">
		<br>
		<br>

		<table style="border:none;">
			<tr>
				<td>
					<!-- this is the player information box -->
					<div id="player_information" align="left" width="512px">
						<table class="table" align="left" width="512px">
							<tbody>
								<tr><td class="subHeader" colspan="3">Battleground</td></tr>
								<tr>
									<td>
										<!-- here goes the users health display and what not -->
										<table align="center" width="100%" style="border:none;">
											<tr>
												<td style="vertical-align:middle;" width="45%">
													<!-- left side of player_information -->

													<!-- this user -->
													<table align="left" width="45%" style="border:none;">
														<tr>
															<td>
																<img style="border:1px solid #e9cb0c;outline:1px solid #4d2600;" src="{$owner['avatar']}" width="65" height="65">
															</td>
															<td style="text-align:left;">
																<a href="">
																	<b class="owner" value="{$owner['name']}">{ucfirst($owner['name'])}</b>
																</a>
																<br>
																{$owner['display_rank']}, {$owner['team']}
																<br>
																<div style="background-color:#998b08;display:inline-block;border:2px solid #917f08;">
																	<div style="height:5px; width:125px; border:1px solid #4d2600; outline:1px solid #e9cb0c;">
																		<div class="healthBar" style="float:left;height:5px;width:{$owner['health'] / $owner['healthMax'] * 100}%;"></div>
																		<div style="float:right;background-color:lightgray;height:5px;width:{100 - $owner['health'] / $owner['healthMax'] * 100}%;"></div>
																	</div>

																	<div style="height:2px;"></div>
																	<div style="height:5px; width:125px; border:1px solid #4d2600; outline:1px solid #e9cb0c;">
																		<div class="chakraBar" style="float:left;height:5px;width:{$owner['chakra'] / $owner['chakraMax'] * 100}%;"></div>
																		<div style="float:right;background-color:lightgray;height:5px;width:{100 - $owner['chakra'] / $owner['chakraMax'] * 100}%;"></div>
																	</div>

																	<div style="height:2px;"></div>
																	<div style="height:5px; width:125px; border:1px solid #4d2600; outline:1px solid #e9cb0c;">
																		<div class="staminaBar" style="float:left;height:5px;width:{$owner['stamina'] / $owner['staminaMax'] * 100}%;"></div>
																		<div style="float:right;background-color:lightgray;height:5px;width:{100 - $owner['stamina'] / $owner['staminaMax'] * 100}%;"></div>
																	</div>
																</div>
															</td>
														</tr>
													</table>
													<br>

													<!-- teamates -->
													{foreach $users as $username => $userdata}
														{if $userdata['team'] == $owner['team'] && $owner['name'] != $username}
															<br><br><br><br><br>
															<table align="left" width="45%" style="border:none;">
																<tr>
																	<td>
                                    {if $userdata['ai'] == true}
                                      <div style="border:1px solid silver;outline:1px solid #4d2600;width:50px;height:50px;font-size: 40px;">AI</div>
                                    {else}
																		  <img style="border:1px solid silver;outline:1px solid #4d2600;" src="{$userdata['avatar']}" width="50" height="50"></img>
                                    {/if}
																	</td>
																	<td style="text-align:left;">
																		<a href="">
																			<b>
                                        {if $userdata['show_count'] == 'yes'}
                                          {ucfirst($username)}
                                        {else if $userdata['show_count'] == 'no'}
                                          {if strpos($username,'#') !== false}
                                            {ucfirst(substr($username,0,strpos($username,'#') - 1))}
                                          {else}
                                            {ucfirst($username)}
                                          {/if}
                                        {/if}
                                      </b>
																		</a>
																		<br>
																		{$userdata['display_rank']}, {$userdata['team']}
																		<br>
																		<div style="height:5px; width:125px; border:1px solid #4d2600;">
																			<div class="healthBar" style="float:left;height:5px;width:{$userdata['health'] / $userdata['healthMax'] * 100}%;"></div>
																			<div style="float:right;background-color:lightgray;height:5px;width:{100 - $userdata['health'] / $userdata['healthMax'] * 100}%;"></div>
																		</div>
																	</td>
																</tr>
															</table>
														{/if}
													{/foreach}
												</td>
                        
												<td style="vertical-align:middle;font-family: 'Calligraffitti';font-size: 34px;" width="10%">
													<b>
														VS.
													</b>
												</td>

												<td style="vertical-align:middle;" width="45%">
													<!-- right side of player_information -->
													{foreach $users as $username => $userdata}
														{if $userdata['team'] != $owner['team']}
															<table align="right" width="45%" style="border:none;">
																<tr>
																	<td style="text-align:right;">
																		<a href="">
																			<b>
                                        {if $userdata['show_count'] == 'yes'}
                                          {ucfirst($username)}
                                        {else if $userdata['show_count'] == 'no'}
                                          {if strpos($username,'#') !== false}
                                            {ucfirst(substr($username,0,strpos($username,'#') - 1))}
                                          {else}
                                            {ucfirst($username)}
                                          {/if}
                                        {/if}
                                      </b>
																		</a>
																		<br>
																		{$userdata['display_rank']}, {$userdata['team']}
																		<br>
																		<div style="height:5px; width:125px; border:1px solid #4d2600;">
																			<div class="healthBar" style="float:left;height:5px;width:{$userdata['health'] / $userdata['healthMax'] * 100}%;"></div>
																			<div style="float:right;background-color:lightgray;height:5px;width:{100 - $userdata['health'] / $userdata['healthMax'] * 100}%"></div>
																		</div>
																	</td>
																	<td>
                                    {if $userdata['ai'] == true}
                                      <div style="border:1px solid silver;outline:1px solid #4d2600;width:50px;height:50px;font-size: 40px">AI</div>
                                    {else}
                                      <img style="border:1px solid silver;outline:1px solid #4d2600;" src="{$userdata['avatar']}" width="50" height="50"></img>
                                    {/if}
																	</td>
																</tr>
															</table>
															<br><br><br><br><br>
														{/if}
													{/foreach}
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<!-- here goes the battle timer -->
									<td style="font-size:20px;">Round: <b class="turn_counter">{$turn_counter + 1}</b><br>Time Left: <b id="turn_timer">{$turn_timer - time()}</b></td>
								</tr>
								<tr>
									<!-- here goes DSR header -->
									<td class="tableColumns" colspan="3"><b>Damage by Survivability Rating (DSR)</b></td>
								</tr>
								<tr>
									<!-- here goes sf -->
									<td>
										<table style="border:none;width:100%;">
											<tr>
                        <td style="text-align:left;">
                          <b>Your DSR: </b>{base_convert(floor(sqrt($owner['DSR']+$rng+4)), 10, 9)}
                          <br>
													<b>Your Team's DSR: </b>{base_convert(floor(sqrt($friendlyDSR+$rng+4)), 10, 9)}
                        </td>
												<td style="text-align:right;">
													<b>Opponent Team's DSR: </b>{base_convert(floor(sqrt($opponentDSR+$rng+4)), 10, 9)}
                        </td>
											</tr>
										</table>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</td>
			</tr>

			<tr>
				<td>
					<!-- bottom half of page -->
					<div align="left">
						<table class="table" style="border:none;" width="512px">
							<tr align="center" width ="95%">
								<td style="text-align:left;padding:0px;" width="50%" valign="top">
									<!-- actions go here -->
									<table id="available_actions" align="left" width="97.5%">
                    {if $owner['waiting_for_next_turn'] === true}
                      <tr>
                        <td class="subHeader" colspan="3">Waiting for Next Turn</td>
                      </tr>
                    {else}
										  <tr>
										  	<td class="subHeader" colspan="3">Actions</td>
										  </tr>
										  <tr>
										  	<td style="margin:0;padding:0px;border:1px solid black;">
										  		<select  style="width:100%;" class="tableColumns select-wrapper" name="action_select" id="action_select" size="1">
										  			<option selected disabled value="default">Select an Action</option>
										  			<option>Jutsus</option>
										  			<option>Weapons</option>
										  			<option>Items</option>
                            
                            {if true} <!-- if the owner did not initiate the fight -->
                            <option>Flee</option>
                            {/if}
                            
                            {if true} <!-- if the owner did not initiate the fight or if the owner did initiate the fight and the opponent has called for help -->
										  			<option>Call For Help</option>
                            {/if}
										  		</select>
										  	</td>

										  </tr>
										  <tr>
										  	<td style="margin:0;padding:0px;border:1px solid black;">
										  		<select style="width:100%;" class="tableColumns select-wrapper" name="jutsu_select" id="jutsu_select" size="1">
										  			<option selected disabled value="default">Select a Jutsu</option>
										  			{foreach $owner['jutsus'] as $jutsu_id => $jutsu_data}
										  			cooldown status: {$jutsu_data['cooldown_status']}
										  				{if ($jutsu_data['cooldown_status'] == 'off' || $jutsu_data['cooldown_status'] == '') && $jutsu_data['reagent_status'] == true && $jutsu_data['max_uses'] > $jutsu_data['uses']}
										  					<option class="{$jutsu_data['targeting_type']}" title="{if $jutsu_data['max_uses'] - $jutsu_data['uses'] <=5 }(uses left: {$jutsu_data['uses']}/{$jutsu_data['max_uses']}){/if} {$jutsu_data['description']}" value="{$jutsu_id}">
										  						{$jutsu_data['name']}
										  					</option>
										  				{else if $jutsu_data['reagent_status'] == false}
										  					<option title="(out of required reagents) {$jutsu_data['description']}" disabled>
										  						{$jutsu_data['name']} (no more uses)
										  					</option>
										  				{else if $jutsu_data['max_uses'] <= $jutsu_data['uses']}
										  					<option title="(no more uses {$jutsu_data['uses']}/{$jutsu_data['max_uses']}) {$jutsu_data['description']}" disabled>
										  						{$jutsu_data['name']} (no more uses)
										  					</option>
										  				{else}
										  					<option title="(this is on cooldown for {$jutsu_data['cooldown_status']} turn{if $jutsu_data['cooldown_status'] != 1}s{/if}.) {if $jutsu_data['max_uses'] - $jutsu_data['uses'] <=5 }(uses left: {$jutsu_data['uses']}/{$jutsu_data['max_uses']}){/if} {$jutsu_data['description']}" disabled>
										  						{$jutsu_data['name']} ({$jutsu_data['cooldown_status']})
										  					</option>
										  				{/if}
										  			{/foreach}
										  		</select>

										  		<select style="width:100%;" class="tableColumns select-wrapper"  name="weapon_attack_select" id="weapon_attack_select" size="1">
										  			<option selected disabled value="default">Select a Weapon</option>
										  				{foreach $owner['equipment'] as $equipment_id => $equipment_data}
										  					{if $equipment_data['type'] == 'weapon'}
										  						{if $equipment_data['element'] == '' || $equipment_data['element'] == 'none' || $equipment_data['element'] == 'None' || (
                                   in_array($equipment_data['element'], $owner['elements']) && $owner['element_masteries'][ array_search($equipment_data['element'], $owner['elements']) ] > 25)}
                                    {if $owner['equipment_used'][ $equipment_data['iid'] ]['uses'] < $owner['equipment_used'][ $equipment_data['iid'] ]['max_uses']}
										  							  <option title="uses-left: {$owner['equipment_used'][ $equipment_data['iid'] ]['max_uses'] - $owner['equipment_used'][ $equipment_data['iid'] ]['uses']}" class="{$equipment_data['targeting_type']}" value="{$equipment_id}">
										  								  {$equipment_data['name']}
										  							  </option>
                                    {else}
                                      <option disabled title="uses-left: {$owner['equipment_used'][ $equipment_data['iid'] ]['max_uses'] - $owner['equipment_used'][ $equipment_data['iid'] ]['uses']}" class="{$equipment_data['targeting_type']}" value="{$equipment_id}">
										  								  {$equipment_data['name']}
										  							  </option>
                                    {/if}
										  						{/if}
										  					{/if}
										  				{/foreach}
										  		</select>
                           
                           <select style="width:100%;" class="tableColumns select-wrapper"  name="item_attack_select" id="item_attack_select" size="1">
                             <option selected disabled value="default">Select a Item</option>
                             {foreach $owner['items'] as $invin_id => $item}
                               {if $item['stack'] != 0 }
                                 {if $item['max_uses'] - $owner['items_used'][$item['iid']] > 0}
                                   <option title="stack: {$item['stack']} charges-left: {$item['uses'] - $items['times_used']} uses-left: {$item['max_uses'] - $owner['items_used'][$item['iid']]}" class="{$item['targeting_type']}" value="{$invin_id}">{$item['name']}</option>
                                 {else}
                                   <option disabled title="stack: {$item['stack']} charges-left: {$item['uses'] - $items['times_used']} uses-left: {$item['max_uses'] - $owner['items_used'][$item['iid']]}" class="{$item['targeting_type']}" value="{$invin_id}">{$item['name']}</option>
                                 {/if}
                               {/if}
                             {/foreach}
                           </select>
										  	</td>
										  </tr>
										  <tr id="jutsu_weapon_select_tr">
										  	<td style="margin:0;padding:0px;border:1px solid black;">
										  		{$owner['jutsu_weapon_selects']}
										  	</td>
										  </tr>
										  <tr id="target_select_tr">
										  	<td style="margin:0;padding:0px;border:1px solid black;">
										  		<select style="width:100%;" class="tableColumns select-wrapper"  name="target_select" id="target_select" size="1">
										  			<option selected disabled value="default">Select a Target</option>
										  			{foreach $users as $username => $userdata}
										  				<option {if $username == $owner['name']}
										  							class="self"
										  						{else if $userdata['team'] == $owner['team']}
										  							class="ally"
										  						{else}
										  							class="opponent"
										  						{/if}
										  						value="{$username}">
										  					
                                {if $userdata['show_count'] == 'yes'}
                                  {ucfirst($username)}
                                {else if $userdata['show_count'] == 'no'}
                                  {if strpos($username,'#') !== false}
                                    {ucfirst(substr($username,0,strpos($username,'#') - 1))}
                                  {else}
                                    {ucfirst($username)}
                                  {/if}
                                {/if}
                                
										  				</option>
										  			{/foreach}
										  		</select>
										  	</td>
										  </tr>
										  <tr>
										  	<td style="margin:0;padding:0;padding-right:1px;border:1px solid black;">
										  		<button style="width:100%;" class="tableColumns" id="button" type="submit" name="button" value=""> </button>
										  	</td>
										  </tr>
                    {/if}
									</table>
								</td>
								<td style="text-align:left;padding:0px;padding-right:1px;" width="50%" valign="top">
									<table id="battle_log" align="right" width="97.5%">
										<tr>
											<td class="subHeader" colspan="3">Battle Log</td>
										</tr>
										<tr>
											<td style="padding:0px;padding-right:0px;border-right:1px solid black;">
                        <table style="width:100%;border:none;">
                          {foreach array_combine( array_reverse( array_keys( $battle_log )), array_reverse( array_values( $battle_log ) ) ) as $round_number => $round_users}
                            <tr class="header_round{$round_number+1}">
                                <td class="tableColumns" style="border:1px solid black; font-size:16px;">
                                  Round: {$round_number + 1}
                                </td>
                            </tr>
                          
                            <tr>
                              <td class="round{$round_number+1}" style="padding:0px;padding-right:3px;">
                                <table style="width:100%;border:none;">
                                  {foreach $round_users as $username => $userdata}
                                    <tr class="round{$round_number+1}_{$username}"><td style="padding:3px"></td></tr>
                                    <tr class="round{$round_number+1}_{$username}">
                                      <td></td>
                                      <td class="tableColumns" style="height:25px;border:1px solid black; font-size:10px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                                                                                                                         background: -webkit-radial-gradient(circle, #EAE3D9 95%, #{if $username == $owner['name']}00a4a6{else if $owner['team'] == $userdata['team']}171799{else}a64200{/if} 5%);
                                                                                                                                                         background: -o-radial-gradient     (circle, #EAE3D9 95%, #{if $username == $owner['name']}00a4a6{else if $owner['team'] == $userdata['team']}171799{else}a64200{/if} 5%);
                                                                                                                                                         background: -moz-radial-gradient   (circle, #EAE3D9 95%, #{if $username == $owner['name']}00a4a6{else if $owner['team'] == $userdata['team']}171799{else}a64200{/if} 5%);
                                                                                                                                                         background: radial-gradient        (circle, #EAE3D9 95%, #{if $username == $owner['name']}00a4a6{else if $owner['team'] == $userdata['team']}171799{else}a64200{/if} 5%);">
                                        <!--background: repeating-linear-gradient( 0deg,  #{if $username == $owner['name']}00a4a6{else if $owner['team'] == $userdata['team']}171799{else}a64200{/if}, #{if $username == $owner['name']}00a4a6{else if $owner['team'] == $userdata['team']}171799{else}a64200{/if} 0px,  #bfbcac 6px, #bfbcac 22px);">-->

                                        <span style="font-size:11px">{ucfirst($username)}</span>
                                        {if $userdata['name'] == 'Basic Attack'}
                                        
                                          <strong> attacked </strong> <span style="font-size:11;x">{ucfirst($userdata['target'])}</span><strong>!</strong>
                                        
                                        {else if $userdata['type'] == 'jutsu'}
            
                                          <strong> attacked </strong> <span style="font-size:11;x">{ucfirst($userdata['target'])}</span> <strong> with </strong> <br>
                                          <strong> the jutsu {$userdata['name']}!</strong>
                                          
                                        {else if $userdata['type'] == 'weapon'}
                                        
                                          <strong> attacked </strong> <span style="font-size:11;x">{ucfirst($userdata['target'])}</span> <strong> with </strong> <br>
                                          <strong> their {$userdata['name']}!</strong>
                                        
                                        {else if $userdata['type'] == 'item'}
                                        
                                          <strong>used {$userdata['name']}</strong><br>
                                          <strong> on </strong><span style="font-size:11;x">{ucfirst($userdata['target'])}</span><strong>!</strong>
                                        
                                        {else if $userdata['type'] == 'flee' }                                 
                                          <strong>tried to flee from the battle!</strong>
                                        {else}
                                          <strong>is confused. what is: "{$userdata['name']}"</strong>
                                        {/if}
                                      </td>
                                    </tr>
                                    <!-- ///////////////////////////////////////////// extra information \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ -->
                                    <tr class="details_round{$round_number+1}_{$username}"></tr>
                                    <tr class="details_round{$round_number+1}_{$username}">
                                      <td></td>
                                      <td>
                                        
                                        {if isset($userdata['killed']) }
                                         <table style="width:100%;border:none;">
                                           <tr>
                                             <td></td>
                                             <td class="tableColumns" style="border:1px solid black; font-size:10px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                               {if $owner['team'] != $round_users[ $userdata['killed'] ]['team'] }
                                                 background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                 background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                 background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                 background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                               {else}
                                                 background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                 background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                 background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                 background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                               {/if}
                                               ">
                                               <span style="font-size:11px">{ucfirst($username)}</span> defeated <span style="font-size:11px">{ucfirst($userdata['killed'])}!</span>
                                             </td>
                                           </tr>
                                         </table>
                                        {/if}

                                        {if isset($userdata['died']) }
                                          <table style="width:100%;border:none;">
                                            <tr>
                                              <td></td>
                                              <td class="tableColumns" style="border:1px solid black; font-size:10px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                {if $owner['team'] == $round_users[ $userdata['died'] ]['team'] }
                                                  background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                  background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                  background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                  background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                {else}
                                                  background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                  background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                  background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                  background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                {/if}
                                                ">
                                                <span style="font-size:11px">{ucfirst($username)}</span> was defeated at the hands of <span style="font-size:11px">{ucfirst($userdata['died'])}!</span>
                                              </td>
                                            </tr>
                                          </table>
                                        {/if}
                                        
                                        <table style="width:100%;border:none;">
                                          <tr>
                                            <td></td>
                                            <!-- for later these are the colors for good: 179917 bad: a61919 nuetral: 7f7f7f -->
                                            {if is_numeric($userdata['damage_delt']['amount']) }
                                            <td class="tableColumns" style="border:1px solid black; font-size:10px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                  {if $owner['team'] == $round_users[ $username ]['team'] }
                                                    background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                    background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                    background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                    background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                {else}
                                                    background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                    background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                    background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                    background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                {/if}
                                                ">
                                                
                                                {$userdata['damage_delt']['type']} damage dealt: {$userdata['damage_delt']['amount']}
                                              </td>

                                            {else if isset($userdata['fled'])}
                                              {if $userdata['fled'] == true}
                                                <td class="tableColumns" style="border:1px solid black; font-size:10px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                  {if $owner['team'] == $round_users[ $username ]['team'] }
                                                    background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                    background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                    background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                    background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                  {else}
                                                    background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                    background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                    background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                    background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                  {/if}
                                                  ">
                                                  The Attempt was Successful.
                                                </td>
                                              {else}
                                                <td class="tableColumns" style="border:1px solid black; font-size:10px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                  {if $owner['team'] != $round_users[ $username ]['team'] }
                                                    background: -webkit-radial-gradient(circle, #EAE3D9 95%, #179917 5%);
                                                    background: -o-radial-gradient     (circle, #EAE3D9 95%, #179917 5%);
                                                    background: -moz-radial-gradient   (circle, #EAE3D9 95%, #179917 5%);
                                                    background: radial-gradient        (circle, #EAE3D9 95%, #179917 5%);
                                                  {else}
                                                    background: -webkit-radial-gradient(circle, #EAE3D9 95%, #a61919 5%);
                                                    background: -o-radial-gradient     (circle, #EAE3D9 95%, #a61919 5%);
                                                    background: -moz-radial-gradient   (circle, #EAE3D9 95%, #a61919 5%);
                                                    background: radial-gradient        (circle, #EAE3D9 95%, #a61919 5%);
                                                  {/if}
                                                  ">
                                                  The Attempt Failed.
                                                </td>
                                              {/if}
                                              </td>
                                            {else}
                                              <td class="tableColumns" style="border:1px solid black; font-size:10px; text-shadow: 0px 0px 2px #EAE3D9, 0px 0px 1px #EAE3D9; width:100%;background: #EAE3D9;
                                                                                                                                                                                        background: -webkit-radial-gradient(circle, #EAE3D9 95%, #7f7f7f 5%);
                                                                                                                                                                                        background: -o-radial-gradient     (circle, #EAE3D9 95%, #7f7f7f 5%);
                                                                                                                                                                                        background: -moz-radial-gradient   (circle, #EAE3D9 95%, #7f7f7f 5%);
                                                                                                                                                                                        background: radial-gradient        (circle, #EAE3D9 95%, #7f7f7f 5%);">
                                                nothing to see here. sorry.
                                              </td>
                                            {/if}
                                            
                                          </tr>
                                        </table>
                                      </td>
                                    </tr>
                                  {/foreach}
                                </table>
                              </td>
                            </tr>
                          
                          {/foreach}
                        </table>
                      </td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</table>

		<br>
		<br>

		{$this_dump}

		<br>
		<br>

		{$users_dump}

		<br>
		<br>

	</form>
</div>
