<?php

namespace App\Service;

class Mailer
{
    public function __construct(
        private string $host,
        private int $port,
        private string $username,
        private string $password,
        private string $fromEmail,
        private string $fromName = ''
    ) {
    }

    public function send(string $toEmail, string $subject, string $body, bool $isHtml = true, ?string $plainTextBody = null): bool
    {
        $socket = stream_socket_client("tcp://{$this->host}:{$this->port}", $errno, $errstr, 30);
        if ($socket === false) {
            throw new \RuntimeException("Failed to connect to SMTP server: {$errstr} ({$errno})");
        }

        stream_set_timeout($socket, 30);
        $this->readResponse($socket, [220]);

        $this->writeLine($socket, 'EHLO localhost');
        $this->readResponse($socket, [250]);

        $this->writeLine($socket, 'STARTTLS');
        $this->readResponse($socket, [220]);

        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new \RuntimeException('Failed to enable TLS on SMTP connection');
        }

        $this->writeLine($socket, 'EHLO localhost');
        $this->readResponse($socket, [250]);

        $this->writeLine($socket, 'AUTH LOGIN');
        $this->readResponse($socket, [334]);

        $this->writeLine($socket, base64_encode($this->username));
        $this->readResponse($socket, [334]);

        $this->writeLine($socket, base64_encode($this->password));
        $this->readResponse($socket, [235]);

        $this->writeLine($socket, 'MAIL FROM: <' . $this->fromEmail . '>');
        $this->readResponse($socket, [250]);

        $this->writeLine($socket, 'RCPT TO: <' . $toEmail . '>');
        $this->readResponse($socket, [250, 251]);

        $this->writeLine($socket, 'DATA');
        $this->readResponse($socket, [354]);

        $headers = [
            'From: ' . ($this->fromName !== '' ? $this->fromName . ' <' . $this->fromEmail . '>' : $this->fromEmail),
            'To: ' . $toEmail,
            'Reply-To: ' . $this->fromEmail,
            'Subject: ' . $subject,
            'MIME-Version: 1.0',
            'Date: ' . date('r'),
            'Message-ID: <' . uniqid('', true) . '@' . ($this->host ?: 'localhost') . '>',
        ];

        if ($isHtml) {
            $boundary = 'b1_' . bin2hex(random_bytes(12));
            $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"; charset=UTF-8';

            $textBody = $plainTextBody ?? trim(strip_tags($body));
            $payload = "--{$boundary}\r\n";
            $payload .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $payload .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $payload .= $textBody . "\r\n";
            $payload .= "--{$boundary}\r\n";
            $payload .= "Content-Type: text/html; charset=UTF-8\r\n";
            $payload .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $payload .= $body . "\r\n";
            $payload .= "--{$boundary}--\r\n";
        } else {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            $headers[] = 'Content-Transfer-Encoding: 8bit';
            $payload = $body . "\r\n";
        }

        fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . $payload . ".\r\n");
        $this->readResponse($socket, [250]);

        $this->writeLine($socket, 'QUIT');
        $this->readResponse($socket, [221]);
        fclose($socket);

        return true;
    }

    private function writeLine($socket, string $line): void
    {
        fwrite($socket, $line . "\r\n");
    }

    private function readResponse($socket, array $expectedCodes = []): string
    {
        $response = '';
        while (!feof($socket)) {
            $line = fgets($socket, 515);
            if ($line === false) {
                break;
            }
            $response .= $line;
            if (preg_match('/^\d{3} /', $line)) {
                break;
            }
        }

        if (trim($response) === '') {
            throw new \RuntimeException('No response from SMTP server');
        }

        if ($expectedCodes !== []) {
            $lines = preg_split('/\r?\n/', trim($response));
            $firstLine = $lines[0] ?? '';

            if (!preg_match('/^(\d{3})[- ]/', $firstLine, $matches)) {
                throw new \RuntimeException('Unexpected SMTP response: ' . trim($response));
            }

            $code = (int) $matches[1];
            if (!in_array($code, $expectedCodes, true)) {
                throw new \RuntimeException('SMTP error ' . $code . ': ' . trim($response));
            }
        }

        return $response;
    }
}
