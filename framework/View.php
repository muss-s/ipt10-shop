<?php

namespace Core;

/**
 * Renders plain-PHP templates from app/Views. A view may pick a layout;
 * its output is passed to the layout as $content.
 */
class View
{
    public static string $path = '';

    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): string
    {
        $content = self::renderFile($view, $data);
        if ($layout === null) {
            return $content;
        }
        return self::renderFile($layout, [...$data, 'content' => $content]);
    }

    public static function renderFile(string $view, array $data): string
    {
        $file = self::$path . '/' . $view . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View not found: $view");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            require $file;
            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /** Include a partial from inside a template. */
    public static function partial(string $view, array $data = []): string
    {
        return self::renderFile($view, $data);
    }
}
