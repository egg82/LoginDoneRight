<?php
	include_once(dirname(__FILE__)."/logger.php");
	require_once(dirname(__FILE__)."/util.php");
	
	class Database {
		private $connection = null;
		
		public function __construct($conf) {
			global $logger, $util;
			
			try {
				if ($conf->type == "mysql") {
					$this->connection = new \PDO("mysql:host=".$conf->host.";dbname=".$conf->name, $conf->user, $conf->pass,
						array(
							\PDO::ATTR_PERSISTENT => true
						)
					);
				} else if ($conf->type == "mssql" || $conf->type == "sybase") {
					$this->connection = new \PDO($type.":host=".$conf->host.";dbname=".$conf->name.", ".$conf->user.", ".$conf->pass,
						array(
							\PDO::ATTR_PERSISTENT => true
						)
					);
				} else if ($conf->type == "sqlite") {
					$this->connection = new \PDO("sqlite:".$conf->name,
						array(
							\PDO::ATTR_PERSISTENT => true
						)
					);
				} else {
					$logger->log("PDO", "ERROR", "Database type not supported.");
					return;
				}
				
				$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			} catch (\PDOException $ex) {
				$logger->log("PDO", "CRITICAL", $ex);
				$util->die503();
			}

			$this->execute("set character_set_client='utf8';set character_set_results='utf8';set collation_connection='utf8_bin';");
		}
		
		public function query($q) {
			global $logger;
			
			try {
				$statement = $this->connection->prepare($q);
				$statement->setFetchMode(\PDO::FETCH_BOTH);
				$statement->execute();
				
				$retarr = array();
				while($row = $statement->fetch()) {
					array_push($retarr, $row);
				}
				
				return $retarr;
			} catch (\PDOException $ex) {
				$logger->log("PDO", "ERROR", $ex);
				return null;
			}
		}
		public function execute($q) {
			global $logger;
			
			try {
				$statement = $this->connection->prepare($q);
				$statement->setFetchMode(\PDO::FETCH_BOTH);
				return $statement->execute();
			} catch (\PDOException $ex) {
				$logger->log("PDO", "ERROR", $ex);
				return false;
			}
		}
		public function sanitize($string) {
			return $this->connection->quote($string);
		}
		public function getLastInsertId() {
			return $this->connection->lastInsertId();
		}
	}
?>
