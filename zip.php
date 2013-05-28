<?php
set_time_limit(0);
require('classes/File.php');
$uploadsPath = '/wp-content/uploads';
$uploadsDirectory = $_SERVER['DOCUMENT_ROOT'] . $uploadsPath;

$files = File::getStandardFilesList();
$zipname = 'file.zip';
$zip = new ZipArchive();
$zip->open($zipname, ZipArchive::CREATE);
foreach ($files as $filename) {
	$file = $uploadsDirectory . '/' . $filename;
	if (!file_exists($file)) {
		die('invalid');
	}
	$zip->addFile($file);
}
$zip->close();
?>