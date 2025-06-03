<?php
require_once __DIR__ . '/../../back/config/database.php';

class InstallationModel {
  public static function getAll() {
    global $pdo;
    return $pdo->query("SELECT * FROM installation LIMIT 100")->fetchAll();
  }
}
