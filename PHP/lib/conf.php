<?php
	class Conf {
		public $hash_cost = 10; //bcrypt hash cost
		
		public $log_type = "stdout log";
		//public $log_type = "log";
		public $log_directory = "C:/xampp/web_logs";
		//public $log_directory = "/var/log/web_logs";
		
		public $encrypt_algo = "rijndael-256"; //mcrypt algorithm
		public $encrypt_mode = "ofb"; //mcrypt mode
		
		public $rand_chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ`-=[]\\;',./~!@#\$%^&*()_+{}|:\"<>?";
		
		public $session_life = 86400; //1 day
		public $session_regen_expire = 10; //10 second session regeneration - gives ajax a chance to die
		public $session_expire_chance = 5; //5% chance for session expire
		
		public $cookie_life = 604800; //1 week
		
		public $hostname = null;
		public $https = false;
		
		public function __construct() {
			$this->hostname = $_SERVER['HTTP_HOST'];
			if (is_null($this->hostname) || $this->hostname == "") {
				$this->hostname = $_SERVER['SERVER_NAME'];
			}
			$this->https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? true : false;
		}
	}
	class DbConf {
		public $type = "mysql";
		public $host = "127.0.0.1";
		public $user = "root";
		public $pass = "";
		public $name = "web";
		
		public function __construct($type, $host, $user, $pass, $name) {
			$this->type = $type;
			$this->host = $host;
			$this->user = $user;
			$this->pass = $pass;
			$this->name = $name;
		}
	}
	
	$conf = new Conf();
?>
