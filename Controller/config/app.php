<?php

if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $directory = str_replace('\\', '/', dirname($scriptName));

        if ($directory === '/' || $directory === '\\' || $directory === '.') {
            return '';
        }

        $normalizedDirectory = rtrim($directory, '/');
        $projectDirectory = preg_replace('#/Controller$#', '', $normalizedDirectory) ?? $normalizedDirectory;

        if ($projectDirectory === '' || $projectDirectory === '/' || $projectDirectory === '\\' || $projectDirectory === '.') {
            return '';
        }

        return $projectDirectory;
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

if (!function_exists('asset_url')) {
    function asset_url(string $path = ''): string
    {
        $normalizedPath = ltrim($path, '/');

        return app_url('View/assets' . ($normalizedPath !== '' ? '/' . $normalizedPath : ''));
    }
}

if (!function_exists('controller_url')) {
    function controller_url(string $path = ''): string
    {
        $normalizedPath = ltrim($path, '/');

        return app_url('Controller' . ($normalizedPath !== '' ? '/' . $normalizedPath : ''));
    }
}

if (!function_exists('route_url')) {
    function route_url(string $action, array $params = []): string
    {
        $query = http_build_query(array_merge(['action' => $action], $params));

        return controller_url('index.php' . ($query !== '' ? '?' . $query : ''));
    }
}

if (!function_exists('upload_url')) {
    function upload_url(string $fileName): string
    {
        $normalizedFileName = str_replace('\\', '/', ltrim($fileName, '/\\'));
        $encodedFileName = str_replace('%2F', '/', rawurlencode($normalizedFileName));

        return app_url('View/uploads/' . $encodedFileName);
    }
}
