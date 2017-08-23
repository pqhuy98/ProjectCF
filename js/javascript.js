var spinning = false;
var a = 0;

function start_spin() {
	show_helper();
	spinning = true;
	one_step();
}
function stop_spin() {
	hide_helper();
	spinning = false;
}

function one_step() {
/* 	if (spinning==false) return;
	a+=Math.random()*10;
	$("#helper").rotate({angle:a});
	setTimeout(function() {one_step()},10); */
}
function show_helper() {
	$("#helper").rotate({angle:0});
	$("#hp1").attr("class","helper_on");
	$("#hp2").attr("class","helper_on");
}
function hide_helper() {
	$("#helper").rotate({angle:180});
	$("#hp1").attr("class","helper_off");
	$("#hp2").attr("class","helper_off");
}

var idx = 0;
var dur = 2000;
var talk = [
	["I'm thinking ! Please wait...<br>", 3000],
	["Too long ? Let me tell you a joke.<br>", 1500],
]

var jokes = [
	"Man and God met somewhere;<br>Both exclaimed, \"My creator!\"",

	"When I lost my rifle, the Army charged me $85.<br>\
	That’s why in the Navy, the captain goes down with the ship.",

	"Every Scooby-Doo episode would literally be two minutes long\
	<br>if the gang went to the mask store first and asked a few questions.",

	"Red sky at night, shepherd’s delight.<br>Blue sky at night, day.",

	"The closest a person ever comes to perfection<br>\
	is when he fills out a job application form.",

	"I hate Russian dolls,<br>they're so full of themselves.",
	"My wife and I were happy for 20 years,<br>then we met.",
	"Whiteboards are remarkable.<br>",

	"The old lady at an ATM asked me to help check her balance.<br>\
	So I pushed her over.",

	"Best two words joke ever: your life.<br>",

	"I bet you I could stop gambling.<br>",

	"There are two rules for success:<br>1) Don't tell all you know.",
	
	"Why was six scared of seven?<br>\
	Because seven \"ate\" nine.",
]

function next_joke() {
	if (jokes.length == 0) return "Did you see my Javascript ?";
	else {
		var idx = randint(0,jokes.length-1);
		var res = jokes[idx];
		jokes.splice(idx, 1);
		return res;
	}
}

function talking() {
	$("#talk").css("color", "#F4FF81");
	$("#talk").css("letter-spacing", "2px");
	if (idx<talk.length) {
		$("#talk").html(talk[idx][0]);
		setTimeout(function(){talking()}, talk[idx][1]);
		idx++;
	} else {
		$("#talk").css("cursor","pointer");
		$("#talk").html(next_joke());
		// $("#talk").on("click", function() {
		// 	x = next_joke();
		// 	$("#talk").html(x);
		// });
	}
}

$("#helper").rotate({angle:180});
var no_more = false;
$("#test").on("click",function() {
	if (no_more) return;
	no_more = true;
	$("#helper").attr("src","loading.gif");
	startEffect(20, 0);
	talking();
});

setTimeout(function(){startEffect(12, 3000)}, 3000);
$("#form").submit(function() {
	if (no_more) return;
	no_more = true;
	$("#helper").attr("src","loading.gif");
	talking();
});
//Helper functions
function gebi(x) {return document.getElementById(x);}
function randint(min,max) {
    return Math.floor(Math.random()*(max-min+1)+min);
}