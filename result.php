<?php
	/* requirements */
	require_once ("./functions.php");

	/* constants */
	date_default_timezone_set($TIMEZONE);
	$header = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<style>
#header {
	border-bottom:2px gray groove;
	text-align:center;
	padding-bottom:10px;
	margin-bottom:10px;
}

#footer {
	border-top:2px gray groove;
	border-bottom:2px gray groove;
	padding:10px;
}

#header p {
	font-size:x-small;
}
</style>
<meta name="generator" content="Protokoller '.$VERSION.', https://github.com/ZeckoVicath/Protokoller">

<title>'.$NAME.' minutes from the '.date('d.m.Y').'</title>
</head>

<body>
<div id="header">
	<h1>cs minutes '.date('d.m.Y').'</h1>
	<p>minutes of '.$NAME.'</p>
</div>';

	/* init variables */
	/* $pwcorrect = false; */ /* TODO is this still necessary? */
	$present = "";

	/* iterate through form data */
	foreach ($_POST as $key => $value) {
		/* TODO The Case distinction below is suboptimal, it includes a redundant if and possibly some bug */
		if (endsWith($key,"_present")) {
			$pid = substr($key,0,-8);
			$from = $_POST[$pid.'_from']; /* TODO does present always imply from!="" ?*/
			if ($from != "")
				$from = "from ".$from;
			$till = $_POST[$pid.'_till'];
			if ($till != "")
				$till = "till ".$till;
			$fromtill = "";
			if ($from != "" && $till != "") {
				$fromtill = " (".$from.", ".$till.")";
			} else if ($from == "" && $till == "") {
				// $fromtill = ""; 
			} else {
				$fromtill = " (".$from.$till.")";
			}
			$person = str_replace("_"," ",$pid).$fromtill;
			$present .= $person . ', ';
		} /* TODO this else case is not needed anymore, right?
                    else {
			//echo "<p>unknown post data: ($key, $value)</p>";
		}
                */
	}
	$present = substr($present,0,-2); // cut ", " at the end away

	$begintime = $_POST['begin_time'];
	$endtime = $_POST['end_time'];
	$moderator = $_POST['moderator'];
	$protocol = $_POST['protocol'];
	$quorate = $_POST['quorum'] ? 'yes' : 'no';
	$formal = "<table><tbody>
	<tr><td><strong>begin:</strong></td><td>$begintime</td></tr>
	<tr><td><strong>end:</strong></td><td>$endtime</td></tr>
	<tr><td><strong>present:</strong></td><td>$present</td></tr>
	<tr><td><strong>moderator:</strong></td><td>$moderator</td></tr>
	<tr><td><strong>minute taker:</strong></td><td>$protocol</td></tr>
	<tr><td><strong>quorate:</strong></td><td>$quorate</td></tr>
</tbody></table>";

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
		<div><h3>item $i: $thead</h3>
			<blockquote>
				<p>$tbody</p>
			</blockquote>
		</div>
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
