#!/usr/bin/php
<?php

	$counter_image = "A";
	$counter_figure = "5";
	
	$fp = @fopen("counter.dat","r+") or die("カウンターのデータファイルが開けません");
	$count = fgets($fp, 64);
	$count = $count + 1;
	rewind($fp);

	flock($fp, LOCK_EX);
	fwrite($fp, $count);
	fclose($fp);

	$count = sprintf("%0".$counter_figure."d", $count);


	require("gifcat.php");
	if (function_exists("i18n_http_output")) i18n_http_output("pass");
	$gifcat = new gifcat;

	for ($i = 0; $i < strlen($count); $i++){
	
		$number = substr($count, $i, 1);
		$image[$i] = "img/".$number.".gif";
	
	}


	header("Content-Type: image/gif");

	echo @$gifcat->output($image);

	
?>

