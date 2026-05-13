<?php
require_once '../controler/config.php';
$pdo = Config::getConnexion();
$types = $pdo->query("SELECT DISTINCT type FROM aliments")->fetchAll(PDO::FETCH_COLUMN);
print_r($types);
?>
