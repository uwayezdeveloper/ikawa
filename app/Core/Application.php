<?php
namespace App\Core;

/**
 * Main Application Class
 * Handles routing and request dispatching
 */
class Application
{
    protected Router $router;
    protected Request $request;
    protected Response $response;
    protected static ?Application $instance = null;

    public function __construct()
    {
        self::$instance = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
    }

    /**
     * Get the application instance
     */
    public static function getInstance(): ?Application
    {
        return self::$instance;
    }

    /**
     * Get the router instance
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Get the request instance
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get the response instance
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        try {
            $this->router->resolve();
        } catch (\Exception $e) {
            if (APP_ENV === 'development') {
                echo '<pre>';
                echo 'Error: ' . $e->getMessage() . "\n";
                echo 'File: ' . $e->getFile() . ':' . $e->getLine() . "\n";
                echo 'Trace: ' . $e->getTraceAsString();
                echo '</pre>';
            } else {
                $this->response->setStatusCode(500);
                View::render('errors/500');
            }
        }
    }
}
