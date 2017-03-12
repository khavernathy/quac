<?php
 
	/**
	 * Counts the lines of code in this folder and all sub folders
	 * You may not sell this script or remove these header comments
	 * @author Hamid Alipour, http://blog.code-head.com/
	**/
 
	define('SHOW_DETAILS', true);
 	

	class Folder {
 
		var $name;
		var $path;
		var $folders;
		var $files;
		var $exclude_extensions;
		var $exclude_files;
		var $exclude_folders;
 
 
		function Folder($path) {
			$this -> path 		= $path;
			$this -> name		= array_pop( array_filter( explode(DIRECTORY_SEPARATOR, $path) ) );
			$this -> folders 	= array();
			$this -> files		= array();
			$this -> exclude_extensions = array('gif', 'jpg', 'jpeg', 'png', 'tft', 'bmp', 'rest-of-the-file-extensions-to-exclude');
			$this -> exclude_files 	    = array('quac_backup_2.sql','linecounter.php', 'quac.sql', 'localhost.sql.zip', 'quac_backup_.sql');
			$this -> exclude_folders  = array('images','phpmailer','js','carryover_data','_backup_5-13-2015');
		}
 
		function count_lines() {
			if( defined('SHOW_DETAILS') ) echo "/Folder: {$this -> path}...<br />";
			$total_lines = 0;
			$this -> get_contents();
			foreach($this -> files as $file) {
				if( in_array($file -> ext, $this -> exclude_extensions) || in_array($file -> name, $this -> exclude_files) ) {
					if( defined('SHOW_DETAILS') ) echo "#---Skipping File: {$file -> name};<br />";
					continue;
				}
				$total_lines += $file -> get_num_lines();
			}
			foreach($this -> folders as $folder) {
				if( in_array($folder -> name, $this -> exclude_folders) ) {
					if( defined('SHOW_DETAILS') ) echo "#Skipping Folder: {$folder -> name};<br />";
					continue;
				}
				$total_lines += $folder -> count_lines();
			}
			if( defined('SHOW_DETAILS') ) echo "<br /> Total lines in {$this -> name}: $total_lines;<br /><br />";
			return $total_lines;
		}
 
		function get_contents() {
			$contents = $this -> _get_contents();
			foreach($contents as $key => $value) {
				if( $value['type'] == 'Folder' ) {
					$this -> folders[] = new Folder($value['item']);
				} else {
					$this -> files[]   = new File  ($value['item']);
				}
			}
		}
 
		function _get_contents() {
			$folder = $this -> path;
			if( !is_dir($folder) ) {
				return array();
			}
			$return_array = array();
			$count		  = 0;
			if( $dh = opendir($folder) ) {
				while( ($file = readdir($dh)) !== false ) {
					if( $file == '.' || $file == '..' ) continue;
					$return_array[$count]['item']	= $folder .$file .(is_dir($folder .$file) ? DIRECTORY_SEPARATOR : '');
					$return_array[$count]['type']	= is_dir($folder .$file) ? 'Folder' : 'File';
					$count++;
				}
				closedir($dh);
			}
			return $return_array;
		}
 
	} // Class
 
	class File {
 
		var $name;
		var $path;
		var $ext;
 
 
		function File($path) {
			$this -> path = $path;
			$this -> name = basename($path);
			$this -> ext  = array_pop( explode('.', $this -> name) );
		}
 
		function get_num_lines() {
			$count_lines = count(file($this -> path));
			if( defined('SHOW_DETAILS') ) echo "|---File: {$this -> name}, lines: $count_lines;<br />";
			return $count_lines;
		}
 
	} // Class
 
	$path_to_here = dirname(__FILE__) .DIRECTORY_SEPARATOR;
	$folder 		  = new Folder($path_to_here);
	echo '<b>Total lines of code: ' .$folder -> count_lines() ."</b><br /><br />";
 
?>
