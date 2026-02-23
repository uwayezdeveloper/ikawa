<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Property;
use App\Models\Location;
use App\Models\PropertyType;

/**
 * Property Controller
 * Handles property management
 */
class PropertyController extends Controller
{
    protected Property $propertyModel;
    protected Location $locationModel;
    protected PropertyType $propertyTypeModel;

    public function __construct()
    {
        parent::__construct();
        $this->propertyModel = new Property();
        $this->locationModel = new Location();
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
     * Show properties list
     */
    public function index(Request $request, Response $response): void
    {
        if (!$this->hasPermission('view-accounts')) {
            $_SESSION['flash_error'] = 'You do not have permission to view properties';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }
        
        $properties = $this->propertyModel->getAllWithLocation();
        $locations = $this->locationModel->getActive();
        $propertyTypes = $this->propertyTypeModel->getActive();
        
        $this->view('properties/index', [
            'properties' => $properties,
            'locations' => $locations,
            'propertyTypes' => $propertyTypes,
            'user' => $_SESSION['user'] ?? [],
            'pageTitle' => 'Properties',
            'scripts' => ['js/pages/custom-table.js']
        ], 'main');
    }

    /**
     * Show create property form
     */
    public function create(Request $request, Response $response): void
    {
        if (!$this->hasPermission('create-accounts')) {
            $_SESSION['flash_error'] = 'You do not have permission to create properties';
            $response->redirect(APP_URL . '/properties');
            return;
        }
        
        $locations = $this->locationModel->getActive();
        $propertyTypes = $this->propertyTypeModel->getActive();
        
        $this->view('properties/create', [
            'locations' => $locations,
            'propertyTypes' => $propertyTypes,
            'user' => $_SESSION['user'] ?? [],
            'pageTitle' => 'Add Property'
        ], 'main');
    }

    /**
     * Handle property actions (create, update, delete)
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
            $response->redirect(APP_URL . '/properties');
            return;
        }
        
        switch ($action) {
            case 'create':
                $this->createProperty($data, $response);
                break;
            case 'update':
                $this->updateProperty($data, $response);
                break;
            case 'delete':
                $this->deleteProperty($data, $response);
                break;
            default:
                $_SESSION['flash_error'] = 'Invalid action';
                $response->redirect(APP_URL . '/properties');
        }
    }

    /**
     * Create new property
     */
    private function createProperty(array $data, Response $response): void
    {
        try {
            // Validate required fields
            $errors = [];
            
            if (empty($data['property_name'])) {
                $errors[] = 'Property name is required';
            }
            
            if (empty($data['location_id'])) {
                $errors[] = 'Location is required';
            }
            
            if (empty($data['type_id'])) {
                $errors[] = 'Property type is required';
            }
            
            if (empty($data['value_amount']) || !is_numeric($data['value_amount'])) {
                $errors[] = 'Valid value amount is required';
            }
            
            if (empty($data['status'])) {
                $errors[] = 'Status is required';
            }
            
            if (!empty($errors)) {
                $_SESSION['flash_error'] = implode('<br>', $errors);
                $response->redirect(APP_URL . '/properties/create');
                return;
            }
            
            // Get current user ID
            $userId = $_SESSION['user']['id'] ?? 0;
            
            // Create property data
            $propertyData = [
                'property_name' => trim($data['property_name']),
                'location_id' => (int)$data['location_id'],
                'type_id' => (int)$data['type_id'],
                'value_amount' => (int)$data['value_amount'],
                'status' => (int)$data['status'],
                'description' => trim($data['description'] ?? ''),
                'created_by' => $userId
            ];
            
            $propertyId = $this->propertyModel->create($propertyData);
            
            if ($propertyId) {
                $_SESSION['flash_success'] = 'Property created successfully';
                $response->redirect(APP_URL . '/properties');
            } else {
                $_SESSION['flash_error'] = 'Failed to create property';
                $response->redirect(APP_URL . '/properties/create');
            }
            
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error creating property: ' . $e->getMessage();
            $response->redirect(APP_URL . '/properties/create');
        }
    }

    /**
     * Update existing property
     */
    private function updateProperty(array $data, Response $response): void
    {
        try {
            $propertyId = (int)($data['property_id'] ?? 0);
            
            if (!$propertyId) {
                $_SESSION['flash_error'] = 'Invalid property ID';
                $response->redirect(APP_URL . '/properties');
                return;
            }
            
            // Validate required fields
            $errors = [];
            
            if (empty($data['property_name'])) {
                $errors[] = 'Property name is required';
            }
            
            if (empty($data['location_id'])) {
                $errors[] = 'Location is required';
            }
            
            if (empty($data['type_id'])) {
                $errors[] = 'Property type is required';
            }
            
            if (empty($data['value_amount']) || !is_numeric($data['value_amount'])) {
                $errors[] = 'Valid value amount is required';
            }
            
            if (empty($data['status'])) {
                $errors[] = 'Status is required';
            }
            
            if (!empty($errors)) {
                $_SESSION['flash_error'] = implode('<br>', $errors);
                $response->redirect(APP_URL . '/properties');
                return;
            }
            
            // Update property data
            $propertyData = [
                'property_name' => trim($data['property_name']),
                'location_id' => (int)$data['location_id'],
                'type_id' => (int)$data['type_id'],
                'value_amount' => (int)$data['value_amount'],
                'status' => (int)$data['status'],
                'description' => trim($data['description'] ?? '')
            ];
            
            $updated = $this->propertyModel->update($propertyId, $propertyData);
            
            if ($updated) {
                $_SESSION['flash_success'] = 'Property updated successfully';
            } else {
                $_SESSION['flash_error'] = 'Failed to update property';
            }
            
            $response->redirect(APP_URL . '/properties');
            
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error updating property: ' . $e->getMessage();
            $response->redirect(APP_URL . '/properties');
        }
    }

    /**
     * Delete property
     */
    private function deleteProperty(array $data, Response $response): void
    {
        try {
            $propertyId = (int)($data['property_id'] ?? 0);
            
            if (!$propertyId) {
                $_SESSION['flash_error'] = 'Invalid property ID';
                $response->redirect(APP_URL . '/properties');
                return;
            }
            
            $deleted = $this->propertyModel->delete($propertyId);
            
            if ($deleted) {
                $_SESSION['flash_success'] = 'Property deleted successfully';
            } else {
                $_SESSION['flash_error'] = 'Failed to delete property';
            }
            
            $response->redirect(APP_URL . '/properties');
            
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error deleting property: ' . $e->getMessage();
            $response->redirect(APP_URL . '/properties');
        }
    }

    /**
     * Get property data for AJAX requests
     */
    public function getProperty(Request $request, Response $response): void
    {
        $propertyId = (int)$request->query('id', 0);
        
        if (!$propertyId) {
            $response->json(['error' => 'Invalid property ID'], 400);
            return;
        }
        
        $property = $this->propertyModel->findById($propertyId);
        
        if (!$property) {
            $response->json(['error' => 'Property not found'], 404);
            return;
        }
        
        $response->json(['property' => $property]);
    }
}