<?php
require_once __DIR__ . '/../config/config.php';

class Database {
	private static $instance = null;

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new PDO(
				"pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME,
				DB_USER,
				DB_PASS
			);
			self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		return self::$instance;
	}
}
