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
    }
});
