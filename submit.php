<?php

	/* requirements */
	require_once ("./functions.php");


if ($_POST['submitpw'] == $PASSWORD) {
	date_default_timezone_set($TIMEZONE);
	$pdate = date("Ymd");
	$fname = $PROTO_PATH.$pdate.".html";
	
	/* pull from git repository (optional) */
	if ($USE_GIT) {
            // return status of the git command
            $ret = -1;
            
	    // output --> ignored
	    $gitput = "";
            
	    // assuming that we're currently executed in a git (sub-)directory
	    exec("git pull", $gitput, $ret);
            
	    if ($ret != 0) {
		die ("couldn't pull git repo");
            }
	}
	
	// check if file already exists and if needed, change name
	$dupnumber = -1;
	$fhandle = False;
	while ($fhandle === False) {
		if ($dupnumber > $MAX_PROTOS) {
			die("couldn't open protocol file to write");
		}
		$fhandle = fopen($fname,"x");
		$dupnumber++;
		$fname = $PROTO_PATH.$pdate."-".$dupnumber.".html";
	}
	if (!fputs($fhandle,$_POST['source_view'])) {
		die("couldn't actually write to protocol file (disk full?)");
	}
	fclose($fhandle);
	
	/* push to git repository (optional) */
        if ($USE_GIT) {
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
	}
	// send mail (optional)
        if ($SEND_MAIL) {
            $mailto  = $MAIL_RECIPIENT;
            $subject = $pdate." meeting minutes";
            $message = "see ".$WEBSITE.$pdate.($dupnumber >= 1?"-".($dupnumber-1):"").".html";
            $from    = "From: ".$MAIL_SENDER;
            if (!mail($mailto, $subject, $message, $from)) {
		die("couldn't send mail.");
            }
        }

	echo "<p>protocol written".( ($SEND_MAIL) ? " and mail sent" : "" ).".</p>";
	echo "<p>see <a href=\"".$WEBSITE.$pdate.($dupnumber >= 1?"-".($dupnumber-1):"").".html"."\">here</a>.</p>"; // TODO:  $fname = "./".$pdate."-".$dupnumber.".html"; needs to be worked in
} else {
	echo "<p>wrong password</p>";
}
?>
