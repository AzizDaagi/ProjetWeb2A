<?php
require_once __DIR__ . '/model/Database.php';
try {
    $db = Database::getConnection();
    $email = 'test@user.com';
    $pass = password_hash('password123', PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (nom, prenom, email, password) VALUES ('Test', 'User', :email, :pass)");
    $stmt->execute(['email' => $email, 'pass' => $pass]);
    echo "User created: test@user.com / password123";
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
