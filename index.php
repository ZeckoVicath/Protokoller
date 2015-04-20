<?php

/* requirements */
require_once ("./functions.php");

?><!DOCTYPE HTML>
<html><head>
<title>Protokoller <?php echo($VERSION);?></title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<style>
body {
	font-family: sans-serif;
}
h1 {
	text-align: center;
	background: #acf;
}
.container h2 {
	background: #333;
	color: #eee;
	padding-left:1em;
}
.container {
	background: #ccc;
	border: 1px solid black;
	margin: 2em;
	padding: 1em;
}
</style>
<script type="text/javascript" src="functions.js"></script>
<script>
var tops=0;

function prePersons () {
	document.write("<table id=\"attendance_table\" style=\"width:100%;\"><colgroup><col width=\"50%\"><col width=\"25%\"><col width=\"25%\"></colgroup><tr><td>present people:</td><td>from:</td><td>to:</td></tr>"
	);
}
function postPersons () {
	document.write("</table>");
}
function getPid (name) {
	return name.replace(/ /g, "_");
}
function dePid (pid) {
	return pid.replace (/_/g, " ");
}
function generatePersonRow (name) {
	var substname = getPid(name);
	return (
		"<tr>"
		+ "<td><input type=\"checkbox\" id=\"" + substname+"_present" + "\" name=\"" + substname+"_present" + "\" onchange=\"checkCount()\" / >"+name+"</td>"
		+ "<td><input type=\"text\" size=\"10\" id=\"" + substname+"_from" + "\" name=\"" + substname+"_from" + "\" onchange=\"setChecked('"+substname+"')\" / >"
		+ "<input type=\"button\" value=\"now\" onclick=\"setChecked('"+substname+"'); setTimeNow('"+substname+"_from')\" / ></td>"
		+ "<td><input type=\"text\" size=\"10\" id=\"" + substname+"_till" + "\" name=\"" + substname+"_till" + "\" onchange=\"setChecked('"+substname+"')\" / >"
		+ "<input type=\"button\" value=\"now\" onclick=\"setChecked('"+substname+"'); setTimeNow('"+substname+"_till')\" / ></td>"
		+ "</tr>"
	);
}
function printPerson (name) {
	document.write(generatePersonRow(name));
}
function addGuest () {
	var naminp = document.getElementById("newGuestName");
	var guestname = "";
	if (naminp.value == "") {
		guestname=prompt("name of the guest");
	} else {
		guestname=naminp.value;
	}
	document.getElementById("attendance_table").tBodies[0].innerHTML += generatePersonRow(guestname);
	setChecked(getPid(guestname));
	checkCount();
	naminp.value="";
}
function createPersonList () {
	var inps = document.getElementsByTagName("input");
	var list = new Array ();
	for (var i=0; i<inps.length; i++) {
		var el = inps[i];
		if (el.type=="checkbox") {
			if (el.checked && endsWith(el.name,"_present")) {
				list.push(dePid(el.name.substring(0,el.name.length-8)));
			}
		}
	}
	return list;
}
function getPersonList (elem) {
	var list = createPersonList();
	elem.innerHTML = ""; // clear
	for (var i=0; i<list.length; i++) {
		elem.innerHTML += "<option>"+list[i]+"</option>";
	}
}
</script>
</head>
<body>
<noscript><h1 style="color:red">Sorry, I was too lazy to make this also work without JavaScript. So please turn it on or use a different text editor.</h1></noscript>
<h1>Protokoller <?php echo($VERSION);?></h1>
<form id="proto" name="proto" action="result.php" method="POST">
<div class="container">
<h2>attendance</h2>
<script>
prePersons("attendance");
<?php
foreach ($PEOPLE as $person) {
	echo "printPerson(\"$person\");\n";
}
?>
postPersons();
</script>
<input type="text" id="newGuestName"/><input type="button" value="+" onclick="addGuest()"/>
<div>total people count: <span id="people_counter">0</span></div>
</div>

<div class="container">
<h2>organisational</h2>
	<table style="width:95%;">
	<colgroup>
		<col width="30%">
		<col width="15%">
		<col width="15%">
		<col width="15%">
		<col width="15%">
	</colgroup>
	<tr>
		<td><label for="begin_time">begin time:</label></td>
		<td><label for="moderator">moderator:</label></td>
		<td><label for="protocol">minute taker:</label></td>
		<td><label for="quorum">quorate:</label></td>
		<td></td>
	</tr>
	<tr>
		<td>
			<input type="text" id="begin_time" name="begin_time" value=""><input type="button" value="now" onclick="setTimeNow('begin_time')">
		</td>
		<td>
			<select size="1" id="moderator" name="moderator" onfocus="getPersonList(this)">
			</select>
		</td>
		<td>
			<select size="1" id="protocol" name="protocol" onfocus="getPersonList(this)">	
			</select>
		</td>
		<td>
			<input type="checkbox" name="quorum" checked="checked" / >&nbsp;yes
		</td>
	</tr>
	</table>
</div>

<div class="container">
<h2>agenda</h2>
	<div id="tops_div" class="sectioncontent">
		<script>generateNewTops(2); // initial top generation</script>
		<input id="genTopsButton" type="button" value="add agenda item" onclick="generateNewTops(1);">
	</div>
</div>

<div class="container">
<h2>end</h2>
<input type="text" id="end_time" name="end_time"><input type="button" value="now" onclick="setTimeNow('end_time')">
<p style="text-align:center;">
	<input type="submit" value="generate protocol!" id="submit" name="submit">
</p>
</div>

</form>
</body>
</html>








