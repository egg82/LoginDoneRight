<?php
	require_once(dirname(__FILE__)."/lib/conf.php");
	require_once(dirname(__FILE__)."/lib/database.php");
	require_once(dirname(__FILE__)."/lib/security.php");
	require_once(dirname(__FILE__)."/lib/autologin.php");
	require_once(dirname(__FILE__)."/lib/util.php");
	require_once(dirname(__FILE__)."/dbconf.php");
	
	class Main {
		//vars
		private $warn_https = true;
		
		//constructor
		public function __construct() {
			global $util, $conf, $db_conf, $security;
			
			if (!$conf->https && $this->warn_https) {
				echo("You are not on a secure connection!<br />\n");
			}
			
			new Autologin($db_conf, "users", "user", "email", "pass", "key", "iv");
			
			if ($_SESSION['user_id'] != 0) {
				echo("Already logged in!");
				return;
			}
			
			// CAPTCHA here
			
			$database = new Database($db_conf, "users", "user", "email", "pass", "key", "iv");
			
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
			
			if (isset($_GET['email'])) {
				$email = $database->sanitize($_GET['email']);
			} else if (isset($_POST['email'])) {
				$email = $database->sanitize($_POST['email']);
			} else {
				$email = "";
			}
			
			if ($this->exists_in_file($pass)) {
				echo("Password is in top 10,000 most common!");
				return;
			}
			
			$result = $database->query("SELECT `id` FROM `users` WHERE `user`=".$user.";");
			
			if (count($result) != 0) {
				echo("Username already taken!");
				return;
			}
			
			$key = $security->generate_key();
			$iv = $security->generate_iv();
			$encrypted_pass = $security->encrypt($security->pass_hash($pass), $key, $iv);
			
			if (!$database->execute("INSERT INTO `users` (`user`, `email`, `pass`, `key`, `iv`) VALUES ("
				. $user . ","
				. (($email == "") ? "NULL," : $email . ",")
				. "'".$encrypted_pass . "',"
				. $database->sanitize($key) . ","
				. "'" . base64_encode($iv) . "'"
				. ");"))
			{
				$util->die503();
			}
			
			$security->set_cookie("user", $_GET['username']);
			$security->set_cookie("encrypted_pass", $security->encrypt($pass, $key, $iv));
			new Autologin($db_conf, "users", "user", "email", "pass", "key", "iv");
			
			// Privilege level changed. Regenerate session ID.
			$security->regenerate_session();
			
			echo("Successfully registered!");
		}
		
		//public
		
		//private
		private function exists_in_file($pass) {
			$handle = fopen(dirname(__FILE__)."/passwords.txt", "r");
			$good = false;
			
			while (($buffer = fgets($handle)) !== false) {
				if (strpos($buffer, $pass) !== false) {
					$good = true;
					break;
				}
			}
			
			fclose($handle);
			
			return $good;
		}
	}
	
	new Main();
?>