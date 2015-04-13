<?php
if ($_POST['submitpw'] == "password") { // MODIFY: the password to the left
	date_default_timezone_set("Europe/Berlin"); // MODIFY: the timezone to the left
	$pdate = date("Ymd");
	$fname = "./".$pdate.".html";
	
	/* pull from git repository (optional) */
	/*
	// return status of the git command
	$ret = -1;
	// output --> ignored
	$gitput = "";

	// assuming that we're currently executed in a git (sub-)directory
	exec("git pull", $gitput, $ret);

	if ($ret != 0) {
		die ("couldn't pull git repo");
	}
	
	*/
	
	// check if file already exists and if needed, change name
	$dupnumber = 0;
	while (file_exists($fname)) {
		clearstatcache();
		$fname = "./".$pdate."-".$dupnumber.".html";
		$dupnumber++;
	}
	
	$fhandle = fopen ($fname,"w");
	if (!$fhandle) {
		die("couldn't open protocol file to write");
	}
	if (!fputs($fhandle,$_POST['source_view'])) {
		die("couldn't actually write to protocol file (disk full?)");
	}
	fclose($fhandle);
	
	/* push to git repository (optional) */
	/*
	// push the protocol to git repository
	exec("git add ".$fname, $gitput, $ret);
	if ($ret != 0) {
		die ("couldn't add the protocol file. maybe this isn't a git repo?");
	}

	exec('git commit -m "protokoller: '.$fname.'"', $gitput, $ret);
	if ($ret != 0) {
		die("couldn't commit the repo. maybe you committed the same protocol twice?");
	}
	
	exec("git push", $gitput, $ret);
	if ($ret != 0) {
		die("couldn't push the repo ...");
	}
	*/
	
	// TODO line 53-62 need be changed
	// $fname = "./".$pdate."-".$dupnumber.".html"; needs to be worked in
	// send mail
	$mailto  = "news@domain.tld";
	$subject = $pdate." meeting minutes";
	$message = "see https://domain.tld/".$pdate.".html"; // MODIFY: the URL to the left
	$from    = "From: news@domain.tld";

	if (!mail($mailto, $subject, $message, $from)) {
		die("couldn't send mail.");
	}

	echo "<p>protocol written and mail sent.</p>";
	echo "<p>see <a href=\""."https://domain.tld/".$pdate.".html"."\">here</a>.</p>"; // MODIFY: the URL to the left
} else {
	echo "<p>wrong password</p>";
}
?>
