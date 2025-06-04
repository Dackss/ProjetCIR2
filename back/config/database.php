<?php
$pdo = new PDO('pgsql:host=localhost;dbname=projetcir2', 'projetcir2', 'isen');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
