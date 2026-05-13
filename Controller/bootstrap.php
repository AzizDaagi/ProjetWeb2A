<?php

spl_autoload_register(function (string $className): void {
    $prefix = 'App\\';
    if (strncmp($className, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($className, strlen($prefix));
    $parts = explode('\\', $relativeClass);
    $filePath = dirname(__DIR__) . '/' . implode('/', $parts) . '.php';

    if (is_file($filePath)) {
        require_once $filePath;
        return;
    }

    if (($parts[0] ?? '') === 'Service') {
        $serviceFallback = dirname(__DIR__) . '/Controller/' . implode('/', array_slice($parts, 1)) . '.php';
        if (is_file($serviceFallback)) {
            require_once $serviceFallback;
        }
    }
});
