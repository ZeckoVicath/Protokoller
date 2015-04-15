<?php

$VERSION = '0.4';

$NAME = 'The Team Name'; // What is the assembly's name for which you are using the minute taker
$ADMIN_EMAIL = 'admin@domain.tld'; // Who should be contacted if an internal server error occurs (is publicly displayed on error page)
$PROTO_PATH = './'; // the relative path (to the the scripts directory) where the protocol should be saved
$WEBSITE = 'https://domain.tld/protokoller/'; // Homepage URL for footer of the generated meeting minute pages
$TIMEZONE = 'Europe/Berlin'; // In what time zone is the minute taker deployed, important for correct time stamps
$PASSWORD = 'password'; // The submit password against unauthorized usage, choose something cryptographically difficult
$USE_GIT = False; // Should the minute taker push the protocol to a git repository
$SEND_MAIL = False; // Should the minute taker send the protocol by mail
$MAIL_RECIPIENT = 'protocols@domain.tld'; // Who should receive the protocol by mail?
$MAIL_SENDER = 'protocols@domain.tld'; // Who should send the protocol by mail?
$MAX_PROTOS = 100; // max trials to save file and max protocols per day
$PEOPLE = array("Max Mustermann", "Marlene Musterfrau");

// check if in root or in subdirectory
$ppre = '';
{
	$maxdepth = 5;
	while (!file_exists($ppre.'functions.php')) {
		$ppre .= '../';
		if ($maxdepth-- < 0) {
			exit ('INTERNAL SERVER ERROR. Please contact :'.$ADMIN_EMAIL); // MODIFY: the contact address to the left
		}
	}
}

	/* TODO: These functions may be useful to create an API to show meeting minutes gists in an overview list on a website */
	/* ### news functions ### */
	/* reads all files of a directory as xml files and returns the array with their xml root nodes
	   $maxEntries = -1 => no restriction
	   $oldCount: how many entries of the past to show, -1 => no restriction
	*/
	class News {
		public $title, $text, $pubDate, $eventbegin, $eventend, $fileid, $filename, $absfilename, $location, $gallery, $image, $thumbnail;
	}
	function getNewsXML ($absfile) {
		global $lowerLang, $ppre;
		$date = getDateFromFilename($absfile);
		$news = simplexml_load_file($absfile);
		if (!$news) {
			echo "Failed loading XML\n";
			foreach(libxml_get_errors() as $error) {
				echo "\t", $error->message;
			}
			exit ("\n in file $absfile\n");
		}
		$title = (string)($news->title[0]);
		$text = ($news->text[0]->asXML());
		foreach($news->title as $t) {
			if (isset($t["lang"]) && $t["lang"] == $lowerLang)
				$title = (string)$t;
		}
		foreach($news->text as $t) {
			if (isset($t["lang"]) && $t["lang"] == $lowerLang)
				$text = $t->asXML();
		}
		$toadd = new News ();
		$toadd->title = $title;
		$toadd->text = $text;
		$toadd->pubDate = (isset($news->time) ? (string)($news->time) : $date["name"]);
		if (isset($news->eventbegin)) {
			$toadd->eventbegin = (string)$news->eventbegin;
			$toadd->eventend   = (string)$news->eventend;
		}
		if (isset($news->eventlocation)) {
			$toadd->location = (string)($news->eventlocation);
			foreach($news->eventlocation as $l) {
				if (isset($l["lang"]) && $l["lang"] == $lowerLang)
					$toadd->location = (string)$l;
			}
		}
		if (isset($news->gallery)) {
			$toadd->gallery = (string)($news->gallery);
		} else if (file_exists($ppre."galleries/".$date["id"])) {
			$toadd->gallery = $date["id"];
		}
		if (isset($news->image)) {
			$toadd->image = (string)($news->image);
		}
		if (isset($news->thumbnail)) {
			$toadd->thumbnail = (string)($news->thumbnail);
		}
		return $toadd;
	}
	function getNewsProtocol ($absfile) {
		global $lowerLang;
		$date = getDateFromFilename($absfile);
		$toadd = new News ();
		$datestr = $date["name"];
		$toadd->title = (($lowerLang == 'de') ? "Sitzung $datestr" : "meeting $datestr");
		$toadd->text = implode(getTops ($absfile), ', ');
		$tmparr = (getDateFromFilename($absfile));
		$toadd->pubDate = $tmparr["name"]; // TODO: use end time of meeting
		return $toadd;
	}
	function getNews ($directory, $idsuffix, $maxEntries=-1, $oldCount=-1) {
		global $lowerLang;
		$files = scandir($directory, 1);
		$ret = array();
		$now = date_create();
		$nowUnix = date_timestamp_get($now);
		foreach ($files as $file) {
			if ($maxEntries == 0) {
				break;
			}
			if ($file == "." || $file == "..")
				continue;
			$date = getDateFromFilename ($file);
			$absfile = $directory.'/'.$file;
			if (!file_exists($absfile))
				exit ("this cannot happen: $absfile is missing");
			
			if (endsWith($file, '.xml')) {
				$toadd = getNewsXML($absfile);
			} else {
				$toadd = getNewsProtocol($absfile);
			}
			$toadd->fileid = $date["id"].$idsuffix;
			$toadd->filename = $file;
			$toadd->absfilename = $absfile;

			$old = isset($toadd->eventend) && (date_timestamp_get(date_create($toadd->eventend)) < $nowUnix);
			if ($oldCount != 0 || !$old) {
			  	if ($old) {
			  		$oldCount -= 1;
			  	}
				array_push ($ret, $toadd);
				$maxEntries -= 1;
			}
		}
		return $ret;
	}
	function getDateFromFilename ($file) {
		if (strpos($file,"/") !== false) {
			$file = substr($file,strrpos($file,"/")+1);
		}
		$ret = array ();
		$dotpos = strrpos ($file, ".");
		$ret["id"]    = substr($file, 0, $dotpos);
		$ret["year"]  = substr($ret["id"], 0, 4);
		$ret["month"] = substr($ret["id"], 4, 2);
		$ret["day"]   = substr($ret["id"], 6, 2);
		$ret["name"]  = $ret["day"].".".$ret["month"].".".$ret["year"];
		//var_dump($ret);
		return $ret;
	}
	function setEventId ($events, $suffix) {
		array_map (function ($el) use ($suffix) {
			$el->fileid = preg_replace('/\.(.*)$/', $suffix, $el->filename);
		}, $events);
	}
	function eventComparator ($ev1, $ev2) {
		return (-strcmp($ev1->filename,$ev2->filename));
	}
	function eventUpdateComparator ($ev1, $ev2) {
		return filemtime($ev2->absfilename) - filemtime($ev1->absfilename);
	}
	function mergeEvents ($events1, $events2) {
		return merge ('eventComparator', $events1, $events2);
	}
	function getTops ($filename) {
		$i = 1; // the number of the TOP to search for
		$tops = array ();
		foreach (file($filename) as $line) {
			if (strpos($line,"$i") !== false && preg_match("/<h3>top|<h3>item/i", $line)) {
				$line = strip_tags($line);
				$line = preg_replace ("/(top|item)(?U).*$i/i", "", $line, 1);
				$line = trim ($line, ": \t\n\r\0\x0B");
				$i = $i + 1;
				if (!empty($line)) {
					array_push ($tops, $line);
				}
			}
		}
		return $tops;
	}

	/* TODO: Most of this should not be needed */
	/* ### menu functions ### */
	function countMenuEntries ($xmlfile) {
		global $ppre;
		if (!file_exists($xmlfile))
			exit ("menu xml file not found: $xmlfile");
		$xmlObject = simplexml_load_file($xmlfile);
		if (!$xmlObject) {
			echo "Failed loading XML\n";
			foreach(libxml_get_errors() as $error) {
				echo "\t", $error->message;
			}
			exit ("\n in file $xmlfile\n");
		}
		return count($xmlObject->entry);
	}
	function printMenu ($xmlfile) {
		global $lowerLang, $ppre, $page;
		if (!file_exists($xmlfile))
			exit ("menu xml file not found: $xmlfile");
		$xmlObject = simplexml_load_file($xmlfile);
		if (!$xmlObject) {
			echo "Failed loading XML\n";
			foreach(libxml_get_errors() as $error) {
				echo "\t", $error->message;
			}
			exit ("\n in file $xmlfile\n");
		}
		echo "<div class=\"tabcontainer\">";
		$querystr = $_GET;
		$otherlang = ($lowerLang=="de") ? 'en' : 'de';
		$querystr['lang'] = $otherlang;
		echo '<a class="langchooser" href="?', http_build_query($querystr), '"><img src="',$ppre,'images/lang_',$otherlang, '.png" alt="Deutsch/English"></a>';
		echo '<a class="subscribe_rss" href="', $ppre, 'newsfeed.php"><img src="',$ppre,'images/rss.png" alt="RSS"/></a>';
		foreach ($xmlObject->entry as $entry) {
			$name = ($lowerLang === 'de') ? $entry->name_de : $entry->name_en;
			$link = $entry->link;
			if (isset($page) and strcmp($page,$link)==0) { // active page
				echo "<span class=\"tabmenuentry\">$name</span>";
			} else {
				echo "<a class=\"tabmenuentry\" href=\"$ppre$link\">$name</a>";
			}
		}
		echo "</div>";
	} // TODO: unify printPanels and printMenu in their common code
	function printPanels ($xmlfile) {
		global $lowerLang, $ppre;
		if (!file_exists($xmlfile))
			exit ("menu xml file not found: $xmlfile");
		$xmlObject = simplexml_load_file($xmlfile);
		if (!$xmlObject) {
			echo "Failed loading XML\n";
			foreach(libxml_get_errors() as $error) {
				echo "\t", $error->message;
			}
			exit ("\n in file $xmlfile\n");
		}
		$i = 0;
		foreach ($xmlObject->entry as $entry) {
			$name  = ($lowerLang === 'de') ? $entry->name_de : $entry->name_en;
			$descr = ($lowerLang === 'de') ? $entry->descr_de : $entry->descr_en;
			$link = $entry->link;
			echo "<a href=\"$link\" class=\"coloredBox randomBackground$i\"> <h3>$name</h3> $descr</a>";
			$i++;
		}
	}

	/* TODO: Some of this is needed */
	/* ### utility functions ### */
	function merge ($comparator, $arr1, $arr2) {
		$ret = array();
		while (!empty($arr1) and !empty($arr2)) {
			$hd1 = reset($arr1); // php -.-
			$hd2 = reset($arr2);
			$cmp_result = call_user_func($comparator, $hd1, $hd2);
			if ($cmp_result === false) {
				exit ("ERROR: couldn't call $comparator for merge sort.");
			}
			if ($cmp_result < 0) {
				array_push($ret, array_shift($arr1));
			} else {
				array_push($ret, array_shift($arr2));
			}
		}
		while (!empty($arr1)) {
			array_push ($ret, array_shift($arr1));
		}
		while (!empty($arr2)) {
			array_push ($ret, array_shift($arr2));
		}
		return $ret;
	}
	function startsWith($haystack, $needle) {
		return !strncmp($haystack, $needle, strlen($needle));
	}
	function endsWith($haystack, $needle) {
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}
		return (substr($haystack, -$length) === $needle);
	}
	function getIfThere($where,$what) {
		return getElse($where,$what,null);
	}
	function getElse($where,$what,$default) {
		if (empty($where[$what])) {
			return $default;
		} else {
			return $where[$what];
		}
	}

	/* TODO: Most of this should not be needed */
	// this is NOT for security.
	function sanitizeTitleText ($text) {
		return str_replace('"','',
			str_replace("'",'',
				preg_replace('/\s+/', ' ',
					trim (strip_tags ($text))
				)
			)
		);
	}
	function getLanguage() {
		// GET data override
		$overridelang = getElse($_GET, 'lang', 'unset');
		if ($overridelang === "de" or $overridelang === "en") {
			header ("Content-Language: $overridelang");
			setcookie ("lang", $overridelang, strtotime("+1 year"), '/');
			return $overridelang;
		}

		// Cookie override
		$overridelang = getElse($_COOKIE, 'lang', 'unset');
		if ($overridelang === "de" or $overridelang === "en") {
			header ("Content-Language: $overridelang");
			return $overridelang;
		}

		// no GET lang set, so ask browser preference
		// append defaults at the end so index is always > -1
		$httpLang = getElse($_SERVER,'HTTP_ACCEPT_LANGUAGE','').',en,de';
		$gerPriority = strpos($httpLang, 'de');
		$enPriority  = strpos($httpLang, 'en');
		$lang = (($gerPriority < $enPriority) ? 'de' : 'en');
		header ("Content-Language: $lang");
		return $lang;
	}
	function stripNonPrintables($str) {
		$ret = strval($str);
		for ($i=0; $i<strlen($ret); ++$i) {
			$c = $ret{$i};
			if (!(($c >= 'a' && $c <= 'z') || ($c >= 'A' && $c <= 'Z') || ($c >= '0' && $c <= '9')))
				$ret{$i} = '_';
		}
		return $ret;
	}
	function mungeEmail($address, $clicktext = "Please click this text", $linktext = null, $noscripttext = "N/A (please enable javascript)") {
		return mungeLink("mailto:".$address, $clicktext, is_null($linktext) ? $address : $linktext, $noscripttext);
	}
	function mungeLink($address, $clicktext = "Please click this text", $linktext = null, $noscripttext = "N/A (please enable javascript)") {
		$unmixedkey = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.@-_/ #:";
		$inprogresskey = $unmixedkey;
		$mixedkey="";
		$unshuffled = strlen($unmixedkey);
		for ($i = 0; $i <= strlen($unmixedkey); $i++) {
			$ranpos = rand(0,$unshuffled-1);
			$nextchar = @$inprogresskey{$ranpos};
			$mixedkey .= $nextchar;
			$before = substr($inprogresskey,0,$ranpos);
			$after = substr($inprogresskey,$ranpos+1,$unshuffled-($ranpos+1));
			$inprogresskey = $before.''.$after;
			$unshuffled -= 1;
		}
		$cipher = $mixedkey;

		$address = strval($address);
		$shift = strlen($address);
		$codedLink = "";
		for ($j=0; $j<strlen($address); $j++) {
			if (strpos($cipher,$address{$j}) === FALSE) {
				$codedLink .= $address{$j};
			} else {
				$chr = ($shift + strpos($cipher,$address{$j})) % strlen($cipher);
				$codedLink .= $cipher{$chr};
				$shift += $chr;
			}
		}
		if (is_null($linktext)) {
			$codedText = $codedLink;
		} else {
			$linktext = strval($linktext);
			$shift = strlen($linktext);
			$codedText = "";
			for ($j=0; $j<strlen($linktext); $j++) {
				if (strpos($cipher,$linktext{$j}) === FALSE) {
					$codedText .= $linktext{$j};
				} else {
					$chr = ($shift + strpos($cipher,$linktext{$j})) % strlen($cipher);
					$codedText .= $cipher{$chr};
					$shift += $chr;
				}
			}
		}

		global $nextNr;
		$myNr = intval($nextNr++);

		$txt = "<span id=\"decrypt$myNr\" class=\"hidden_mail\" onclick=\"decryptLink(this, '" . $codedLink . "', '" . $codedText . "', '".$cipher."')\">".htmlspecialchars($noscripttext)."</span>\n";

		$txt .= "<script type=\"text/javascript\">\n";
		$txt .= "<!--\n";
		global $mungeMailFunctionWritten;
		if ($mungeMailFunctionWritten !== 1) {
			$mungeMailFunctionWritten = 1;
			$txt .= "// Email obfuscator script 2.1 by Tim Williams, University of Arizona\n".
					"// Random encryption key feature by Andrew Moulden, Site Engineering Ltd\n".
					"// PHP version coded by Ross Killen, Celtic Productions Ltd\n".
					"// Improvements and adaptions by Clemens Hammacher, Saarland University\n".
					"// This code is freeware provided these six comment lines remain intact\n".
					"// A wizard to generate this code is at http://www.jottings.com/obfuscator/\n".
					"// The PHP code may be obtained from http://www.celticproductions.net/\n\n".
					"function decryptText(coded, key) {\n".
					"  shift=coded.length\n".
					"  text=\"\"\n".
					"  for (i=0; i<coded.length; i++) {\n" .
					"    keyindex=key.indexOf(coded.charAt(i))\n" .
					"    if (keyindex==-1) {\n" .
					"      text += coded.charAt(i)\n" .
					"    }\n" .
					"    else {     \n".
					"      ltr = (keyindex - shift+key.length) % key.length\n".
					"      text += (key.charAt(ltr))\n".
					"      shift += keyindex - key.length\n".
					"    }\n".
					"  }\n".
					"  return text\n".
					"}\n".
					"function decryptLink(node, codedLink, codedText, key) {\n".
					"  link=decryptText(codedLink, key)\n".
					"  text=decryptText(codedText, key)\n".
					"  newnode = document.createElement(\"a\")\n".
					"  newnode.href=link\n".
					"  newnode.innerHTML=text\n".
					"  node.parentNode.replaceChild(newnode, node)\n".
					"}\n";
		}

		$txt .= "document.getElementById(\"decrypt$myNr\").innerHTML = \"".htmlspecialchars($clicktext)."\";\n".
			"//-->\n" .
			"</script>";
		return $txt;
	}

?>
