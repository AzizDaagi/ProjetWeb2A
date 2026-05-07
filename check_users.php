<?php
require_once __DIR__ . '/model/Database.php';
try {
    $db = Database::getConnection();
    $stmt = $db->query("DESCRIBE users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
