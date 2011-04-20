<?php
// Don't use a Symbolic Link because that crash the website.
// Just include the original file to redirect the processing.
//include( '/home/gitwitty/public_html/master/index2.php');
// Evaluate the original include file to redirect to keep the __FILE__ value.
$filename = '/home/gitwitty/public_html/master/index2.php';
$handle = fopen ($filename, "r");
$contents = fread ($handle, filesize ($filename));
fclose ($handle);
unset($handle);
eval("?>" . $contents);
