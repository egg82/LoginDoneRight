<?php
	require_once(dirname(__FILE__)."/conf.php");
	require_once(dirname(__FILE__)."/util.php");
	
	class Security {
		public function __construct() {
			$this->start_session();
		}
		
		public function pass_hash($password) {
			global $conf, $util;
			
			if (extension_loaded("scrypt")) {
				$salt = $util->secure_random_string(16);
				return $salt . scrypt($password, $salt, $conf->hash_cost ^ 2, 8, 1, 64);
			} else {
				return password_hash($password, PASSWORD_BCRYPT,
					[
						'cost' => $conf->hash_cost
					]
				);
			}
		}
		public function pass_verify($hash, $password) {
			global $conf;
			
			if (extension_loaded("scrypt")) {
				if (strlen($hash) <= 16) {
					return false;
				}
				$salt = substr($hash, 0, 16);
				$crypt = substr($hash, 16);
				return (scrypt($password, $salt, $conf->hash_cost ^ 2, 8, 1, 64) == $crypt) ? true : false;
			} else {
				return password_verify($password, $hash);
			}
		}
		
		public function generate_key() {
			global $conf, $util;
			
			$td = mcrypt_module_open($conf->encrypt_algo, '', $conf->encrypt_mode, '');
			$ks = mcrypt_enc_get_key_size($td);
			$key = $util->secure_random_string($ks);
			mcrypt_module_close($td);
			
			return $key;
		}
		public function generate_iv() {
			global $conf;
			
			$td = mcrypt_module_open($conf->encrypt_algo, '', $conf->encrypt_mode, '');
			$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM);
			mcrypt_module_close($td);
			
			return $iv;
		}
		public function encrypt($string, $key, $iv) {
			global $conf;
			
			$td = mcrypt_module_open($conf->encrypt_algo, '', $conf->encrypt_mode, '');
			mcrypt_generic_init($td, $key, $iv);
			$encrypted = mcrypt_generic($td, $string);
			mcrypt_generic_deinit($td);
			mcrypt_module_close($td);
			
			return base64_encode($encrypted);
		}
		public function decrypt($string, $key, $iv) {
			global $conf;
			
			$td = mcrypt_module_open($conf->encrypt_algo, '', $conf->encrypt_mode, '');
			mcrypt_generic_init($td, $key, $iv);
			$decrypted = mdecrypt_generic($td, base64_decode($string));
			mcrypt_generic_deinit($td);
			mcrypt_module_close($td);
			
			return trim($decrypted);
		}
		
		public function set_cookie($key, $value) {
			global $conf;
			$_COOKIE[$key] = $value;
			setcookie($key, $value, time() + $conf->cookie_life, "/", "", $conf->https, true);
		}
		public function delete_cookie($key) {
			unset($_COOKIE[$key]);
			setcookie($key, NULL, -1);
		}
		
		public function delete_session() {
			$_SESSION = array();
			$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
			$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$this->regenerate_session();
		}
		public function regenerate_session() {
			global $conf;
			
			if(isset($_SESSION['OBSOLETE'])) {
				return;
			}
			
			$_SESSION['OBSOLETE'] = true;
			$_SESSION['EXPIRES'] = time() + $conf->session_regen_expire;
			
			session_regenerate_id(false);
			
			$new_session = session_id();
			session_write_close();
			
			session_id($new_session);
			session_start();
			
			unset($_SESSION['OBSOLETE']);
			unset($_SESSION['EXPIRES']);
		}
		private function start_session() {
			global $conf;
			
			session_name("sess");
			session_set_cookie_params(time() + $conf->session_life, "/", "", isset($_SERVER['HTTPS']), true);
			session_start();
			
			if ($this->validate_session()) {
				if (!$this->check_hijacking()) {
					$this->delete_session();
				} elseif (mt_rand(1, 100) <= $conf->session_expire_chance) {
					$this->regenerate_session();
				}
			} else {
				$_SESSION = array();
				session_destroy();
				session_start();
			}
		}
		private function check_hijacking() {
			if (!isset($_SESSION['ip']) || !isset($_SESSION['user_agent'])) {
				return false;
			}
			if ($_SESSION['ip'] != $_SERVER['REMOTE_ADDR']) {
				return false;
			}
			if( $_SESSION['user_agent'] != $_SERVER['HTTP_USER_AGENT']) {
				return false;
			}
			
			return true;
		}
		private function validate_session() {
			if (isset($_SESSION['OBSOLETE']) && !isset($_SESSION['EXPIRES'])) {
				return false;
			}
			if (isset($_SESSION['EXPIRES']) && $_SESSION['EXPIRES'] < time()) {
				return false;
			}
			
			return true;
		}
	}
	
	$security = new Security();
?>
