<?php
// Don't use a Symbolic Link because the links maybe wrong.
// Just include the original file to redirect the processing.
//include( '/home/gitwitty/public_html/master/installation/index.php');
// Evaluate the original include file to redirect to keep the __FILE__ value.
$filename = '/home/gitwitty/public_html/master/installation/index.php';
$handle = fopen ($filename, "r");
$contents = fread ($handle, filesize ($filename));
fclose ($handle);
unset($handle);
eval("?>" . $contents);
