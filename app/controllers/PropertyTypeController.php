<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\PropertyType;
/**
 * Property Type Controller
 * Handles property type management
 */
class PropertyTypeController extends Controller
{
    protected PropertyType $propertyTypeModel;

    public function __construct()
    {
        parent::__construct();
        $this->propertyTypeModel = new PropertyType();
    }

    /**
     * Check if user has a specific permission
     */
    protected function hasPermission(string $permission): bool
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) return false;
        
        $permissions = $user['permissions'] ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Show property types list
     */
    public function index(Request $request, Response $response): void
    {
        if (!$this->hasPermission('view-accounts')) {
            $_SESSION['flash_error'] = 'You do not have permission to view property types';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }
        
        $propertyTypes = $this->propertyTypeModel->getAll();
        
        $this->view('properties/types/index', [
            'propertyTypes' => $propertyTypes,
            'user' => $_SESSION['user'] ?? [],
            'pageTitle' => 'Property Types',
            'scripts' => ['js/pages/custom-table.js']
        ], 'main');
    }

    /**
     * Handle property type actions (create, update, delete)
     */
    public function handleAction(Request $request, Response $response): void
    {
        $data = $request->getBody();
        $action = $data['action'] ?? '';
        
        // Check permission based on action
        $permissionMap = [
            'create' => 'create-accounts',
            'update' => 'edit-accounts',
            'delete' => 'delete-accounts'
        ];
        
        $requiredPermission = $permissionMap[$action] ?? null;
        if (!$requiredPermission || !$this->hasPermission($requiredPermission)) {
            $_SESSION['flash_error'] = 'You do not have permission to perform this action';
            $response->redirect(APP_URL . '/properties/types');
            return;
        }
        
        switch ($action) {
            case 'create':
                $this->createPropertyType($data, $response);
                break;
            case 'update':
                $this->updatePropertyType($data, $response);
                break;
            case 'delete':
                $this->deletePropertyType($data, $response);
                break;
            default:
                $_SESSION['flash_error'] = 'Invalid action';
                $response->redirect(APP_URL . '/properties/types');
        }
    }

    /**
     * Create new property type
     */
    private function createPropertyType(array $data, Response $response): void
    {
        try {
            // Validate required fields
            $errors = [];
            
            if (empty($data['type_name'])) {
                $errors[] = 'Property type name is required';
            }
            
            if (empty($data['status'])) {
                $errors[] = 'Status is required';
            }
            
            if (!empty($errors)) {
                $_SESSION['flash_error'] = implode('<br>', $errors);
                $response->redirect(APP_URL . '/properties/types');
                return;
            }
            
            // Create property type data
            $propertyTypeData = [
                'type_name' => trim($data['type_name']),
                'description' => trim($data['description'] ?? ''),
                'status' => (int)$data['status']
            ];
            
            $propertyTypeId = $this->propertyTypeModel->create($propertyTypeData);
            
            if ($propertyTypeId) {
                $_SESSION['flash_success'] = 'Property type created successfully';
            } else {
                $_SESSION['flash_error'] = 'Failed to create property type';
            }
            
            $response->redirect(APP_URL . '/properties/types');
            
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error creating property type: ' . $e->getMessage();
            $response->redirect(APP_URL . '/properties/types');
        }
    }

    /**
     * Update existing property type
     */
    private function updatePropertyType(array $data, Response $response): void
    {
        try {
            $typeId = (int)($data['type_id'] ?? 0);
            
            if (!$typeId) {
                $_SESSION['flash_error'] = 'Invalid property type ID';
                $response->redirect(APP_URL . '/properties/types');
                return;
            }
            
            // Validate required fields
            $errors = [];
            
            if (empty($data['type_name'])) {
                $errors[] = 'Property type name is required';
            }
            
            if (empty($data['status'])) {
                $errors[] = 'Status is required';
            }
            
            if (!empty($errors)) {
                $_SESSION['flash_error'] = implode('<br>', $errors);
                $response->redirect(APP_URL . '/properties/types');
                return;
            }
            
            // Update property type data
            $propertyTypeData = [
                'type_name' => trim($data['type_name']),
                'description' => trim($data['description'] ?? ''),
                'status' => (int)$data['status']
            ];
            
            $updated = $this->propertyTypeModel->update($typeId, $propertyTypeData);
            
            if ($updated) {
                $_SESSION['flash_success'] = 'Property type updated successfully';
            } else {
                $_SESSION['flash_error'] = 'Failed to update property type';
            }
            
            $response->redirect(APP_URL . '/properties/types');
            
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error updating property type: ' . $e->getMessage();
            $response->redirect(APP_URL . '/properties/types');
        }
    }

    /**
     * Delete property type
     */
    private function deletePropertyType(array $data, Response $response): void
    {
        try {
            $typeId = (int)($data['type_id'] ?? 0);
            
            if (!$typeId) {
                $_SESSION['flash_error'] = 'Invalid property type ID';
                $response->redirect(APP_URL . '/properties/types');
                return;
            }
            
            $deleted = $this->propertyTypeModel->delete($typeId);
            
            if ($deleted) {
                $_SESSION['flash_success'] = 'Property type deleted successfully';
            } else {
                $_SESSION['flash_error'] = 'Failed to delete property type';
            }
            
            $response->redirect(APP_URL . '/properties/types');
            
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error deleting property type: ' . $e->getMessage();
            $response->redirect(APP_URL . '/properties/types');
        }
    }

    /**
     * Get active property types for API requests
     */
    public function getActive(Request $request, Response $response): void
    {
        $activeTypes = $this->propertyTypeModel->getActive();
        $response->json(['propertyTypes' => $activeTypes]);
    }
}