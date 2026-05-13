<?php

class ReminderController
{
    private $pdo;

    public function __construct($pdo = null)
    {
        $this->pdo = $pdo;
    }

    public function test()
    {
        require_once __DIR__ . '/../model/ReminderMailer.php';

        $mailer = new ReminderMailer();

        $user = [
            'email' => getenv('MAIL_TO_EMAIL') ?: 'melikkrbb@gmail.com',
            'nom' => 'Malik'
        ];

        try {
            $mailer->sendReminder($user);
            echo "OK";
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function test_reminder()
    {
        $this->test();
    }
}
