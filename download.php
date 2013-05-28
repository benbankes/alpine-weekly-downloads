<?php
$filename = $_GET['f'];
if(strpos($filename, '..') !== false) {
	die('invalid');
}

$uploadsPath = '/wp-content/uploads';
$uploadsDirectory = $_SERVER['DOCUMENT_ROOT'] . $uploadsPath;
$file = $uploadsDirectory . '/' . $filename;
if (!file_exists($file)) {
	die('invalid');
}

header('Pragma: public');   // required
header('Expires: 0');    // no cache
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Last-Modified: '.gmdate ('D, d M Y H:i:s', filemtime ($file)).' GMT');
header('Cache-Control: private',false);
header('Content-Type: octet/stream');
header('Content-Disposition: attachment; filename="'.basename($file).'"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: '.filesize($file));  // provide file size
header('Connection: close');
readfile($file);    // push it out
exit();
?>