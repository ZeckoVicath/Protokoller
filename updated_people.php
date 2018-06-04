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
</head>
<body>
<noscript><h1 style="color:red">Sorry, I was too lazy to make this also work without JavaScript. So please turn it on or use a different text editor.</h1></noscript>
<h1>Protokoller <?php echo($VERSION);?></h1>
<div class="container">
<?php 
if ($_POST["submitpw"] == $PASSWORD) {
	$people = $_POST["people"];
	if (strlen($people) > 5000) {
		$error = "too many people";
	} else {
		$success = file_put_contents("people.txt", $people);
		if ($success === false) {
			$error = "internal server error: could not write to file.";
		}
	}
	if (isset($error)) {
		echo "Error: ".$error;
	} else {
		echo "Success :-)";
	}
} else {
	echo "Wrong password.";
}
?>
</div>
</form>
</body>
</html>








