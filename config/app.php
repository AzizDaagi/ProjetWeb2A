<?php

if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $directory = str_replace('\\', '/', dirname($scriptName));

        if ($directory === '/' || $directory === '\\' || $directory === '.') {
            return '';
        }

        return rtrim($directory, '/');
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        $basePath = app_base_path();
        $normalizedPath = ltrim($path, '/');

        if ($normalizedPath === '') {
            return $basePath !== '' ? $basePath . '/' : '/';
        }

        if ($basePath === '') {
            return '/' . $normalizedPath;
        }

        return $basePath . '/' . $normalizedPath;
    }
}
