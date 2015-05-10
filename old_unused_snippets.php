<?php
// check if in root or in subdirectory
// this should not be needed anymore
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

/* the merge step of merge sort */
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
	
?>
