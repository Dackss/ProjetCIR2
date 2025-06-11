<?php
session_start();

if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
	header("Location: index.php?page=back/AdminConnexion");
	exit;
}
?>