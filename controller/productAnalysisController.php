<?php
require_once __DIR__ . '/../model/OpenFoodFactsClient.php';
require_once __DIR__ . '/../model/InputValidator.php';

header('Content-Type: application/json');

$query = InputValidator::cleanText($_GET['q'] ?? $_POST['q'] ?? '');
$client = new OpenFoodFactsClient();

echo json_encode($client->analyze($query));

?>
