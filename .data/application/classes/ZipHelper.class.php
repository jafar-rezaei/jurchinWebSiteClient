<?php
$GLOBALS['zipResult'] = 0; 

class ZipHelper {

	/**
	*	extractor Citygram Site engine project
	*	
	*	@param archive 	=> zip or gz file
	*	@param destination 	=> directory
	*/
	
	public function extract($archive, $destination) {
		$ext = pathinfo($archive, PATHINFO_EXTENSION);
		if ($ext === 'zip') {
			$this->extractZipArchive($archive, $destination);
		}
		else {
			if ($ext === 'gz') {
				$this->extractGzipFile($archive, $destination);
			}
		}
	}
	
	
	/**
	* Decompress/extract a zip archive using ZipArchive.
	*
	* @param $archive
	* @param $destination
	*
	*/
	   
	private function extractZipArchive($archive, $destination) {
		// Check if webserver supports unzipping.
		if(!class_exists('ZipArchive')) {
			$GLOBALS['zipResult'] = 'عدم توانایی PHP';
		}
		$zip = new ZipArchive;
		// Check if archive is readable.
		if ($zip->open($archive) === TRUE) {
			// Check if destination is writable
			if(is_writeable($destination . '/')) {
				$zip->extractTo($destination);
				$zip->close();
				$GLOBALS['zipResult'] = '1';
			}
			else {
				$GLOBALS['zipResult'] = 'دایرکتوری غیر قابل نوشتن است .';
			}
		}
		else {
			$GLOBALS['zipResult'] = 'عدم توانایی در خواندن فایل !';
		}
	}
	
	/**
	* Decompress a .gz File.
	*
	* @param $archive
	* @param $destination
	*
	*/
	public function extractGzipFile($archive, $destination) {
		// Check if zlib is enabled
		if(!function_exists('gzopen')) {
			$GLOBALS['zipResult'] = 'عدم توانایی PHP !';
		}
		$filename = pathinfo($archive, PATHINFO_FILENAME);
		$gzipped = gzopen($archive, "rb");
		$file = fopen($filename, "w");
		while ($string = gzread($gzipped, 4096)) {
			fwrite($file, $string, strlen($string));
		}
		gzclose($gzipped);
		fclose($file);
		
		
		// Check if file was extracted.
		if(file_exists($destination . '/' . $filename)) {
			$GLOBALS['zipResult'] = '1';
		}
		else {
			$GLOBALS['zipResult'] = 'خطا در گشایش فایل پوسته !';
		}
	}


	/**
	* create zip from a folder
	*
	* @param $source 		= folder to create zip
	* @param $destination	= ziped file destination
	*
	* @example 				= createZip('/path/to/folder', '/path/to/backup.zip');
	*/
	public function createZip($source, $destination) {
		if (extension_loaded('zip')) {
			if (file_exists($source)) {
				$zip = new ZipArchive();
				if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
					$source = realpath($source);
					if (is_dir($source)) {
						$iterator = new RecursiveDirectoryIterator($source);
						// skip dot files while iterating 
						$iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
						$files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
						foreach ($files as $file) {
							$file = realpath($file);
							if (is_dir($file)) {
								$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
							} else if (is_file($file)) {
								$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
							}
						}
					} else if (is_file($source)) {
						$zip->addFromString(basename($source), file_get_contents($source));
					}
				}
				return $zip->close();
			}
		}
		return false;
	}
}