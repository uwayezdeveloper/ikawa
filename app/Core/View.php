<?php
namespace App\Core;

/**
 * View Class
 * Handles view rendering with layouts
 */
class View
{
    protected static ?string $layout = 'main';
    protected static array $sections = [];
    protected static ?string $currentSection = null;

    /**
     * Render a view with optional layout
     */
    public static function render(string $view, array $data = [], ?string $layout = null): void
    {
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include view file
        $viewPath = BASE_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: $view");
        }
        
        include $viewPath;
        
        // Get view content
        $content = ob_get_clean();
        
        // Use specified layout or default
        $layoutName = $layout ?? self::$layout;
        
        // If no layout, output directly
        if ($layoutName === null) {
            echo $content;
            return;
        }
        
        // Include layout
        $layoutPath = BASE_PATH . '/app/Views/layouts/' . $layoutName . '.php';
        
        if (!file_exists($layoutPath)) {
            // No layout found, output content directly
            echo $content;
            return;
        }
        
        include $layoutPath;
    }

    /**
     * Render a view without layout
     */
    public static function renderPartial(string $view, array $data = []): void
    {
        self::render($view, $data, null);
    }

    /**
     * Set the layout
     */
    public static function setLayout(?string $layout): void
    {
        self::$layout = $layout;
    }

    /**
     * Start a section
     */
    public static function startSection(string $name): void
    {
        self::$currentSection = $name;
        ob_start();
    }

    /**
     * End current section
     */
    public static function endSection(): void
    {
        if (self::$currentSection !== null) {
            self::$sections[self::$currentSection] = ob_get_clean();
            self::$currentSection = null;
        }
    }

    /**
     * Get section content
     */
    public static function section(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }

    /**
     * Include a partial view
     */
    public static function include(string $partial, array $data = []): void
    {
        extract($data);
        
        $partialPath = BASE_PATH . '/app/Views/partials/' . str_replace('.', '/', $partial) . '.php';
        
        if (file_exists($partialPath)) {
            include $partialPath;
        }
    }

    /**
     * Generate asset URL
     */
    public static function asset(string $path): string
    {
        return APP_URL . '/assets/' . ltrim($path, '/');
    }

    /**
     * Generate URL
     */
    public static function url(string $path = ''): string
    {
        return APP_URL . '/' . ltrim($path, '/');
    }

    /**
     * Escape HTML
     */
    public static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
