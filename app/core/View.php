<?php
namespace App\Core;

class View
{
    protected static string $layout = 'default';
    protected static array $sections = [];
    protected static array $stack = [];

    public static function render(string $template, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        $path = self::resolve($template);
        if (!is_file($path)) {
            throw new \RuntimeException("View not found: {$template}");
        }
        include $path;
        $content = ob_get_clean();

        if (self::$layout) {
            $layout = self::$layout;
            self::$layout = 'default';
            $layoutPath = self::resolve('layouts/' . $layout);
            ob_start();
            include $layoutPath;
            return ob_get_clean();
        }
        return $content;
    }

    public static function layout(string $name): void
    {
        self::$layout = $name;
    }

    public static function resolve(string $template): string
    {
        $base = dirname(__DIR__) . '/views/';
        $full = $base . $template . '.php';
        // Theme override
        $theme = current_theme();
        $override = dirname(__DIR__, 2) . '/app/themes/' . $theme . '/' . $template . '.php';
        if (is_file($override)) {
            return $override;
        }
        return $full;
    }

    public static function section(string $name, string $content): void
    {
        self::$sections[$name] = $content;
    }

    public static function yield(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }
}
