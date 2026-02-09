<?php
namespace App\Core;

/**
 * Base Controller Class
 */
abstract class Controller
{
    protected Request $request;
    protected Response $response;

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    /**
     * Render a view
     */
    protected function view(string $view, array $data = [], ?string $layout = null): void
    {
        View::render($view, $data, $layout);
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        $this->response->json($data, $statusCode);
    }

    /**
     * Return success response
     */
    protected function success(array $data = [], string $message = 'Success', int $statusCode = 200): void
    {
        $this->response->success($data, $message, $statusCode);
    }

    /**
     * Return error response
     */
    protected function error(string $message = 'Error', int $statusCode = 400, array $errors = []): void
    {
        $this->response->error($message, $statusCode, $errors);
    }

    /**
     * Redirect to URL
     */
    protected function redirect(string $url): void
    {
        $this->response->redirect($url);
    }

    /**
     * Validate request data
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $ruleString) {
            $ruleList = explode('|', $ruleString);
            $value = $data[$field] ?? null;
            
            foreach ($ruleList as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleParam = $ruleParts[1] ?? null;
                
                $error = $this->validateField($field, $value, $ruleName, $ruleParam, $data);
                
                if ($error) {
                    $errors[$field][] = $error;
                }
            }
        }
        
        return $errors;
    }

    /**
     * Validate a single field
     */
    protected function validateField(string $field, $value, string $rule, ?string $param, array $data): ?string
    {
        $fieldName = ucfirst(str_replace('_', ' ', $field));
        
        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    return "$fieldName is required.";
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "$fieldName must be a valid email address.";
                }
                break;
                
            case 'min':
                if (!empty($value)) {
                    if (is_numeric($value) && (float)$value < (float)$param) {
                        return "$fieldName must be at least $param.";
                    } elseif (strlen($value) < (int)$param) {
                        return "$fieldName must be at least $param characters.";
                    }
                }
                break;
                
            case 'max':
                if (!empty($value) && strlen($value) > (int)$param) {
                    return "$fieldName must not exceed $param characters.";
                }
                break;
                
            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($data[$confirmField] ?? null)) {
                    return "$fieldName confirmation does not match.";
                }
                break;
                
            case 'unique':
                // Format: unique:table,column
                if (!empty($value) && $param) {
                    [$table, $column] = explode(',', $param);
                    $sql = "SELECT COUNT(*) as count FROM $table WHERE $column = :value";
                    $result = Database::fetch($sql, ['value' => $value]);
                    if ($result['count'] > 0) {
                        return "$fieldName already exists.";
                    }
                }
                break;
                
            case 'alpha':
                if (!empty($value) && !ctype_alpha($value)) {
                    return "$fieldName must contain only letters.";
                }
                break;
                
            case 'alpha_num':
                if (!empty($value) && !ctype_alnum($value)) {
                    return "$fieldName must contain only letters and numbers.";
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    return "$fieldName must be a number.";
                }
                break;
                
            case 'integer':
                if (!empty($value) && (!is_numeric($value) || !is_int((float)$value))) {
                    return "$fieldName must be an integer.";
                }
                break;
        }
        
        return null;
    }

    /**
     * Get authenticated user from session
     */
    protected function getAuthUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user']);
    }
}
