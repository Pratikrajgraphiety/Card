<?php

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'layouts/app'): void
    {
        $viewFile = app_path('Views/' . str_replace('.', '/', $view) . '.php');
        if (!is_file($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if (!$layout) {
            echo $content;
            return;
        }

        $layoutFile = app_path('Views/' . str_replace('.', '/', $layout) . '.php');
        if (!is_file($layoutFile)) {
            throw new \RuntimeException("Layout not found: {$layout}");
        }

        require $layoutFile;
    }

    public static function partial(string $view, array $data = []): void
    {
        $viewFile = app_path('Views/' . str_replace('.', '/', $view) . '.php');
        extract($data, EXTR_SKIP);
        require $viewFile;
    }
}
