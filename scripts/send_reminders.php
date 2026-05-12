<?php

require_once __DIR__ . '/../model/NutritionDashboardService.php';
require_once __DIR__ . '/../model/ReminderMailer.php';
require_once __DIR__ . '/../model/UserModel.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$pdo = new PDO("mysql:host=localhost;dbname=smart_nutrition", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$nutritionService = new NutritionDashboardService($pdo);
$userModel = new UserModel($pdo);
$mailer = new ReminderMailer();

$nutritionService->ensureReminderLogsTable();

$users = $userModel->getRemindableUsers();
$logLines = [];
$runAt = date('Y-m-d H:i:s');

$logLines[] = "[{$runAt}] Reminder run started";

if (empty($users)) {
    $logLines[] = "No users found.";
}

foreach ($users as $user) {
    $userId = (int) ($user['id'] ?? 0);
    $email = trim((string) ($user['email'] ?? ''));
    $nameParts = array_filter([
        trim((string) ($user['prenom'] ?? '')),
        trim((string) ($user['nom'] ?? '')),
    ]);
    $name = !empty($nameParts) ? implode(' ', $nameParts) : 'Utilisateur';

    if ($userId <= 0) {
        $logLines[] = "Skipped invalid user row.";
        continue;
    }

    $decision = $nutritionService->getSmartReminder($userId);

    if (empty($decision['should_send'])) {
        $logLines[] = "Skipped {$email}: " . ($decision['reason'] ?? 'no_reason');
        continue;
    }

    $sent = $mailer->sendReminder([
        'id' => $userId,
        'email' => $email,
        'nom' => $name,
    ]);

    if ($sent) {
        $nutritionService->markReminderSent($userId);
        $logLines[] = "Reminder sent to: {$email} | reason=" . ($decision['reason'] ?? 'unknown') . " | priority=" . ($decision['priority'] ?? 'medium');
    } else {
        $logLines[] = "Reminder failed for: {$email}";
    }
}

$logLines[] = "[{$runAt}] Reminder run finished";

$output = implode(PHP_EOL, $logLines) . PHP_EOL;

echo $output;
file_put_contents(__DIR__ . '/test_log.txt', $output, FILE_APPEND);
