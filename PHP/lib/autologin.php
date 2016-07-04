<?php
	require_once(dirname(__FILE__)."/util.php");
	require_once(dirname(__FILE__)."/database.php");
	require_once(dirname(__FILE__)."/security.php");
	
	class Autologin {
		//vars
		private $prevent_user_enum = true;
		
		//constructor
		public function __construct($db_conf, $table, $user_field, $email_field, $pass_field, $key_field, $iv_field) {
			global $util, $security;
			
			if (!isset($_SESSION["user_id"]) || !isset($_SESSION["username"])) {
				$_SESSION["user_id"] = 0;
				$_SESSION["username"] = "";
			}
			
			if ($_SESSION["user_id"] == 0 && isset($_COOKIE["user"]) && isset($_COOKIE["encrypted_pass"])) {
				$database = new Database($db_conf);
				
				$user = $database->sanitize($_COOKIE["user"]);
				$result = $database->query("SELECT `id`, `". $user_field . "`, `" . $pass_field . "`, `" . $key_field . "`, `" . $iv_field . "` FROM `" . $table . "` WHERE `". $user_field . "`=".$user." OR `". $email_field . "`=".$user.";");
				
				$cookie_user = $_COOKIE["user"];
				$cookie_encrypted_pass = $_COOKIE["encrypted_pass"];
				
				$security->delete_cookie("user");
				$security->delete_cookie("encrypted_pass");
				
				if (count($result) == 0) {
					if ($this->prevent_user_enum) {
						$this->check_pass("testing", "+wDY57krS5nRSY6wbfRk3LpYLHIXkPAaHwxmKGlKIy7V3XAa2EKAdNS/o8Mrv5ub+58rSREPuUSHA7Pc/zHnjYggi2St55DQEYEdypq2jDIbYJydsY0X8g7g46UdxQD2+LfFjNe4vyIzONGdXL/j5INeeZxCjt7sp+DbcdzySJUS2VXfAMVHsHU6fS8XNDWM", "drYB,|h=zS*cVJ6Q7::&m=Y3DYwGI\$TM", "GfDHP5lj88QAH2cr7lw8pEGgJM7stTeGpOUvUBVYPLo=");
					}
					return;
				}
				
				if (!$this->check_pass($cookie_encrypted_pass, $result[0][$pass_field], $result[0][$key_field], $result[0][$iv_field])) {
					return;
				}
				
				$_SESSION["user_id"] = $result[0]["id"];
				$_SESSION["username"] = $result[0][$user_field];
				$_SESSION["html_username"] = $util->sanitize($result[0][$user_field]);
				
				$security->set_cookie("user", $cookie_user);
				$security->set_cookie("encrypted_pass", $cookie_encrypted_pass);
			}
		}
		
		//public
		
		//private
		private function check_pass($encrypted_pass1, $encrypted_pass2, $key, $iv) {
			global $security;
			
			$decoded_iv = base64_decode($iv);
			$decrypted_pass1 = $security->decrypt($encrypted_pass1, $key, $decoded_iv);
			$decrypted_pass2 = $security->decrypt($encrypted_pass2, $key, $decoded_iv);
			return $security->pass_verify($decrypted_pass2, $decrypted_pass1);
		}
	}
?>
