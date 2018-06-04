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
<h2>Update people</h2>
<form id="proto" name="proto" action="updated_people.php" method="POST">
<textarea cols="85" rows="20" name="people">
<?php
foreach ($PEOPLE as $person) {
	echo ($person);
	echo ("\r\n");
}
?>
</textarea>
<p>
	<label for="submitpw">password:</label> <input type="password" id="submitpw" name="submitpw">
</p>
<p style="text-align:center;">
	<input type="submit" value="save updated people" id="submit" name="submit">
</p>
</div>

</form>
</body>
</html>








