<?php
require_once __DIR__ . '/model/Database.php';
try {
    $db = Database::getConnection();
    $stmt = $db->query("SELECT password FROM users WHERE email = 'amineshimi90@gmail.com'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Password: " . $row['password'];
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
