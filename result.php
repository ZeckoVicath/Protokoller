<?php

	/* requirements */
	require_once ("./functions.php");

	/* constants */
	date_default_timezone_set($TIMEZONE);
	$header = '<!DOCTYPE HTML>
<html><head>
<meta charset="UTF-8">
<style>
body {
	margin:0;
	padding:1em;
	font-family:serif;
}
h1 {
	font-weight:lighter;
}
#header {
	border-bottom:0.1em solid gray;
	text-align:center;
	padding-bottom:10px;
	margin-bottom:10px;
}
#footer {
	border-top:0.1em solid gray;
	border-bottom:0.1em solid gray;
	padding:10px;
}
#header p {
	font-size:small;
}
</style>
<meta name="generator" content="Protokoller '.$VERSION.', https://github.com/ZeckoVicath/Protokoller">

<title>'.$NAME.' minutes from the '.date('d.m.Y').'</title>
</head>

<body>
<div id="header">
	<h1>'.$NAME.' minutes '.date('d.m.Y').'</h1>
	<p>minutes of '.$NAME.'</p>
</div>';

	/* init variables */
	$present = "";

	/* iterate through form data */
	foreach ($_POST as $key => $value) {
		if (endsWith($key,"_present")) {
			$pid = substr($key,0,-8);
			$from = $_POST[$pid.'_from'];
			if ($from != "") {
				// this happens if somebody was there from the beginning
				$from = "from ".$from;
			}
			$till = $_POST[$pid.'_till'];
			if ($till != "") {
				// the person was there until the end
				$till = "until ".$till;
			}
			$fromtill = "";
			if ($from != "" && $till != "") {
				$fromtill = " (".$from.", ".$till.")";
			} else if ($from == "" && $till == "") {
				// person was there all the time => no comment needed
				// fromtill is already initialized as "".
			} else {
				$fromtill = " (".$from.$till.")";
			}
			$person = str_replace("_"," ",$pid).$fromtill;
			$present .= $person . ', ';
		}
	}
	$present = substr($present,0,-2); // cut ", " at the end away

	$begintime = $_POST['begin_time'];
	$endtime = $_POST['end_time'];
	$moderator = $_POST['moderator'];
	$protocol = $_POST['protocol'];
	$quorate = $_POST['quorum'] ? 'yes' : 'no';
	$formal = "<table>
	<tr><td><b>begin:</b></td><td>$begintime</td></tr>
	<tr><td><b>end:</b></td><td>$endtime</td></tr>
	<tr><td><b>present:</b></td><td>$present</td></tr>
	<tr><td><b>moderator:</b></td><td>$moderator</td></tr>
	<tr><td><b>minute taker:</b></td><td>$protocol</td></tr>
	<tr><td><b>quorate:</b></td><td>$quorate</td></tr>
</table>";

	$agenda = "<h2>agenda</h2>
<ul>";
	{
		$i = 1;
		$ihead = 'top'.$i.'_heading';
		$ibody = 'top'.$i;
		while ($_POST[$ihead] && $_POST[$ibody] && $_POST[$ihead]!="" && $_POST[$ibody]!="") {
			$thead = $_POST[$ihead];
			$tbody = str_replace("\n","<br/>",$_POST[$ibody]);
			$agenda .= "\n	<li>
		<h3>item $i: $thead</h3>
		<p>$tbody</p>
	</li>";
			$i += 1;
			$ihead = 'top'.$i.'_heading';
			$ibody = 'top'.$i;
		}
	}
	$agenda .= "</ul>";

	$footer = '<div id="footer">
back to the <a href="'.$WEBSITE.'">[homepage]</a>
</div>
</body></html>';

	$protocol = $header."\n".$formal."\n".$agenda."\n".$footer; // newlines are for prettier html code

	if ($JUST_DOWNLOAD) {
		header('Content-Type: text/html');
		$dstname = 'meeting_'.date("Ymd").'.html';
		header('Content-Disposition: attachment; filename="'.$dstname.'"');
		echo ($protocol);
		die();
	}
?><!DOCTYPE HTML>
<head>
<title>preview and send minutes</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
</head>
<body>
<form id="submit_proto" name="submit_proto" action="submit.php" method="POST">
<textarea id="source_view" name="source_view" style="width:100%; min-height:300px;"></textarea><br/>
<input type="button" value="update preview" onclick="update_preview()"/><input type="password" name="submitpw"/><input type="submit" value="upload protocol and send mail"/><!-- MODIFY: the value if necessary --><br/>
</form>
<iframe id="preview" style="width:100%; min-height:500px;"></iframe>
<script>
var the_source = <?php echo json_encode($protocol); ?> ;

function update_preview () {
	var ifrm = document.getElementById('preview');
	var ifrm = (ifrm.contentWindow) ? ifrm.contentWindow : (ifrm.contentDocument.document) ? ifrm.contentDocument.document : ifrm.contentDocument;
	ifrm.document.open();
	ifrm.document.write(document.getElementById('source_view').value);
	ifrm.document.close();
}

document.getElementById('source_view').value=the_source;
update_preview();
</script>
</body>
