<?php
$pdo = new PDO('pgsql:host=localhost;dbname=projetCIR2', 'postgres', 'password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
