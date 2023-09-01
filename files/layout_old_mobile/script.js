var topMenuOn = "no";

function displayTopMenu()
	{
	if(topMenuOn == "no")
		{
		topMenuOn = "yes";
		document.getElementById("top_menu").style.display = "block";
		}
	else
		{
		topMenuOn = "no";
		document.getElementById("top_menu").style.display = "none";
		}
	}

function showPage(pageId)
	{
	document.getElementById("log-in_page").style.display = "none";
	document.getElementById("profile").style.display = "none";
	document.getElementById("inbox").style.display = "none";
	document.getElementById("inventory").style.display = "none";
	document.getElementById("jutsu").style.display = "none";
	document.getElementById("marriage").style.display = "none";
	document.getElementById("home").style.display = "none";
	document.getElementById("logbook").style.display = "none";
	document.getElementById(pageId).style.display = "block";
	scroll(0,0);
	}
	
function arrowExtend()
	{
	document.getElementById("char").style.display = "none";
	document.getElementById("com").style.display = "none";
	document.getElementById("vil").style.display = "none";
	document.getElementById("train").style.display = "none";
	document.getElementById("right").style.display = "none";
	document.getElementById("left").style.display = "block";
	document.getElementById("map").style.display = "block";
	document.getElementById("combat").style.display = "block";
	document.getElementById("sup").style.display = "block";
	document.getElementById("gen").style.display = "block";
	}
	
function arrowShrink()
	{
	document.getElementById("char").style.display = "block";
	document.getElementById("com").style.display = "block";
	document.getElementById("vil").style.display = "block";
	document.getElementById("train").style.display = "block";
	document.getElementById("right").style.display = "block";
	document.getElementById("left").style.display = "none";
	document.getElementById("map").style.display = "none";
	document.getElementById("combat").style.display = "none";
	document.getElementById("sup").style.display = "none";
	document.getElementById("gen").style.display = "none";
	}