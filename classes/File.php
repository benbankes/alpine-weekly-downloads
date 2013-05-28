<?php
	class File {
		public $name;
		public $path;
		public $modified;
		
		public function getModifiedTime() {
			if($this->path && is_file($this->path)) {
				return filemtime($this->path);
			}
			return false;
		}
		
		public static function getFile($filename, $path) {
			if(!is_file($path . '/' . $filename)) {
				return;
			}
			
			$file = new File();
			$file->name = $filename;
			$file->path = $path . '/' . $filename;
			$file->modified = $file->getModifiedTime();
			return $file;
		}
		
		public static function getStandardFilesList() {
			return array(
				'starter.osz',
				'countdown.wmv',
				'songs.zip',
				'announcements.zip',
				'slides.zip',
				'sermonintro.mp4',
				'sermonfull.mp4'
			);
		}
		
		public static function getStarterFilename() {
			return 'starter.osz';
		}
		
		public static function getStarterFileDependencies() {
			return array(
				'countdown.wmv',
				'songs.zip',
				'announcements.zip',
				'slides.zip'
			);
		}
	}
?>