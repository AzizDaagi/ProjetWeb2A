<?php

namespace App\Controller;

abstract class BaseController
{
    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $viewPath = dirname(__DIR__) . '/View/' . $view . '.php';

        if (!is_file($viewPath)) {
            throw new \RuntimeException('View not found: ' . $view);
        }

        require $viewPath;
    }

    protected function redirect(string $action, array $params = []): void
    {
        header('Location: ' . route_url($action, $params));
        exit;
    }

    protected function requestMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    protected function isPost(): bool
    {
        return $this->requestMethod() === 'POST';
    }

    protected function oldInput(array $fields, array $defaults = []): array
    {
        $old = [];

        foreach ($fields as $field) {
            $default = $defaults[$field] ?? '';
            $old[$field] = trim((string) ($_POST[$field] ?? $default));
        }

        return $old;
    }

    protected function renderWithFormError(string $view, array $data, \Throwable $exception): void
    {
        $data['error'] = $exception->getMessage();
        $this->render($view, $data);
    }
}
