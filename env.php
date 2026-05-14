<?php

if (!function_exists('load_project_env')) {
    /**
     * Load environment variables from .env.local and .env without overriding
     * variables already provided by the server environment.
     */
    function load_project_env(?string $projectRoot = null): void
    {
        $projectRoot = $projectRoot ?: __DIR__;
        $envFiles = [
            $projectRoot . '/.env.local',
            $projectRoot . '/.env',
        ];

        foreach ($envFiles as $envFile) {
            if (!is_file($envFile) || !is_readable($envFile)) {
                continue;
            }

            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines === false) {
                continue;
            }

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                    continue;
                }

                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                if ($key === '' || getenv($key) !== false || array_key_exists($key, $_ENV) || array_key_exists($key, $_SERVER)) {
                    continue;
                }

                $length = strlen($value);
                if ($length >= 2) {
                    $firstChar = $value[0];
                    $lastChar = $value[$length - 1];
                    if (($firstChar === '"' && $lastChar === '"') || ($firstChar === "'" && $lastChar === "'")) {
                        $value = substr($value, 1, -1);
                    }
                }

                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

load_project_env(__DIR__);
