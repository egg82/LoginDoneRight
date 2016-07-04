<?php
	require_once(dirname(__FILE__)."/lib/conf.php");
	require_once(dirname(__FILE__)."/lib/database.php");
	require_once(dirname(__FILE__)."/lib/security.php");
	require_once(dirname(__FILE__)."/lib/autologin.php");
	require_once(dirname(__FILE__)."/dbconf.php");
	
	class Main {
		//vars
		private $warn_https = true;
		private $prevent_user_enum = true;
		
		//constructor
		public function __construct() {
			global $conf, $db_conf, $security;
			
			if (!$conf->https && $this->warn_https) {
				echo("You are not on a secure connection!<br />\n");
			}
			
			new Autologin($db_conf, "users", "user", "email", "pass", "key", "iv");
			
			if ($_SESSION['user_id'] != 0) {
				echo("Already logged in!");
				return;
			}
			
			// Possible CAPTCHA here
			
			$database = new Database($db_conf);
			
			if (isset($_GET['username'])) {
				$user = $database->sanitize($_GET['username']);
			} else if (isset($_POST['username'])) {
				$user = $database->sanitize($_POST['username']);
			} else {
				echo("Username is not set!");
				return;
			}
			if (isset($_GET['password'])) {
				$pass = $_GET['password'];
			} else if (isset($_POST['password'])) {
				$pass = $_POST['password'];
			} else {
				echo("Password is not set!");
				return;
			}
			
			$result = $database->query("SELECT `id`, `user`, `pass`, `key`, `iv` FROM `users` WHERE `user`=".$user." OR `email`=".$user.";");
			
			if (count($result) == 0) {
				if ($this->prevent_user_enum) {
					// Preventing timing attacks.
					$this->check_pass("testing", "+wDY57krS5nRSY6wbfRk3LpYLHIXkPAaHwxmKGlKIy7V3XAa2EKAdNS/o8Mrv5ub+58rSREPuUSHA7Pc/zHnjYggi2St55DQEYEdypq2jDIbYJydsY0X8g7g46UdxQD2+LfFjNe4vyIzONGdXL/j5INeeZxCjt7sp+DbcdzySJUS2VXfAMVHsHU6fS8XNDWM", "drYB,|h=zS*cVJ6Q7::&m=Y3DYwGI\$TM", "GfDHP5lj88QAH2cr7lw8pEGgJM7stTeGpOUvUBVYPLo=");
					echo("User/E-mail does not exist, or password is invalid!");
					return;
				} else {
					echo("User/E-mail does not exist!");
					return;
				}
			}
			
			if (!$this->check_pass($pass, $result[0]['pass'], $result[0]['key'], $result[0]['iv'])) {
				if ($this->prevent_user_enum) {
					echo("User/E-mail does not exist, or password is invalid!");
					return;
				} else {
					echo("Password is invalid!");
					return;
				}
			}
			
			$security->set_cookie("user", $_GET['username']);
			$security->set_cookie("encrypted_pass", $security->encrypt($pass, $result[0]['key'], base64_decode($result[0]['iv'])));
			new Autologin($db_conf, "users", "user", "email", "pass", "key", "iv");
			
			// Privilege level changed. Regenerate session ID.
			$security->regenerate_session();
			
			echo("Successfully logged in!");
		}
		
		//public
		
		//private
		private function check_pass($pass, $encrypted_pass, $key, $iv) {
			global $security;
			
			$decrypted_pass = $security->decrypt($encrypted_pass, $key, base64_decode($iv));
			return $security->pass_verify($decrypted_pass, $pass);
		}
	}
	
	new Main();
?>