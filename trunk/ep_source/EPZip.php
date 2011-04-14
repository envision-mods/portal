<?php 
/**************************************************************************************
* EPZip.php                                                                         *
/**************************************************************************************
* EnvisionPortal                                                                      *
* Community Portal Application for SMF                                                *
* =================================================================================== *
* Software by:                  EnvisionPortal (http://envisionportal.net/)           *
* Software for:                 Simple Machines Forum                                 *
* Copyright 2011 by:            EnvisionPortal (http://envisionportal.net/)           *
* Support, News, Updates at:    http://envisionportal.net/                            *
**************************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

/*	This file provides Envision Portal's zip and unzip functions.
	void EPZip()
			- Checks whether or not we'll be using the ZipArchive Class.
	multi Open(String $file_name)
			- Opens a zip archive.
			- Returns true on success. On failure, returns false or an error code.
	bool Extract(String $dir_name)
			- Extracts open zip archive to the given directory.
			- Returns true on success and false on failure.
	bool Close()
			- Closes a zip archive.
			- Returns true on success and false on failure.
*/
class EPZip{
	public $zipModule = false; // Tells whether or not we can use zlib functions.
	private $zipArchive; // ZipArchive() handle.
	private $zipOpen; // zip_open() handle.
	public function EPZip(){
		$this->zipModule = extension_loaded('zlib');
		if($this->zipModule)
			$this->zipArchive = new ZipArchive();
	}
	private function Open($file_name){
		// This means zlib is loaded and working.
		if($this->zipModule){
			$this->zipArchive->open($file_name);
			return $this->zipArchive;
		}
		
		// Great, now we had to do it the hard way...
		$this->zipFile = zip_open($file_name);
		if(!$this->zipFile)
			return false;
		return true;
	}
	private function Extract($dir_name){
		// ZipArchive really is a blessing as opposed to the other...
		if($this->zipModule)
			return $this->zipArchive->extractTo($dir_name);
		
		// Such a nuisance...
		while($zip = zip_read($this->zipFile)){
			$name = zip_entry_name($zip);
			$dir = dirname($name);
			if(!zip_entry_open($this->zipFile, $zip))
				return false;
			if(file_exists($name))
				return false;
			if($name[strlen($name) - 1] == DIRECTORY_SEPARATOR)
				mkdir($name);
			else{
				$handle = fopen($name, 'w');
				if(!$handle)
					return false;
           		$bool = fwrite($fopen, zip_entry_read($zip));
           		if(!$bool)
           			return false;
           		fclose($handle);
			}
			zip_entry_close($zip);
		}
		return true;
	}
	private function Close(){
		if($this->zipModule)
			return $this->zipArchive->close();
		zip_close($this->zipFile);
		return true;
	}
}
?>