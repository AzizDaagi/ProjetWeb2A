<?php
require_once __DIR__ . '/model/Database.php';
try {
    $db = Database::getConnection();
    $stmt = $db->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo $row[0] . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
