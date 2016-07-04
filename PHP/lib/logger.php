<?php
	require_once(dirname(__FILE__)."/conf.php");
	
	class Logger {
		public function __construct() {
			global $conf;
			
			if (substr($conf->log_directory, strlen($conf->log_directory) - 1, 1) == "/") {
				$conf->log_directory = substr($conf->log_directory, 0, strlen($conf->log_directory) - 1);
			}
		}
		
		public function log($type, $level, $string) {
			global $conf;
			
			if (strpos($conf->log_type, "stdout") !== false) {
				echo("[".date("H:i:s")."] [".$level."] (".$type."): ".$string);
			}
			if (strpos($conf->log_type, "log") !== false) {
				if (!file_exists($conf->log_directory."/".date("Y-m-d"))) {
					if (mkdir($conf->log_directory."/".date("Y-m-d"), 0777, true) === false) {
						echo("[".date("H:i:s")."] [ERROR] (LOGGER): Cannot create required directory structure.");
						return;
					}
				}
				if (file_put_contents($conf->log_directory."/".date("Y-m-d")."/".strtolower($type)."_".strtolower($level), "[".date("H:i:s")."] ".$string.PHP_EOL, FILE_APPEND) === false) {
					echo("[".date("H:i:s")."] [ERROR] (LOGGER): Cannot write to log file.");
					return;
				}
			}
		}
	}
	
	$logger = new Logger();
?>
