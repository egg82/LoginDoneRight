<?php
	require_once(dirname(__FILE__)."/lib/conf.php");
	require_once(dirname(__FILE__)."/lib/security.php");
	
	class Main {
		//vars
		
		//constructor
		public function __construct() {
			global $conf, $security;
			
			if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
				echo("Not logged in!");
				return;
			}
			
			$security->delete_cookie("user");
			$security->delete_cookie("encrypted_pass");
			
			// Privilege level changed. Delete session ID.
			$security->delete_session();
			
			echo("Successfully logged out!");
		}
		
		//public
		
		//private
		
	}
	
	new Main();
?>