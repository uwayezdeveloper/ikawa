<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Location;
use App\Models\LocationType;

/**
 * Location Controller
 * Handles location management
 */
class LocationController extends Controller
{
    protected Location $locationModel;
    protected LocationType $locationTypeModel;

    public function __construct()
    {
        parent::__construct();
        $this->locationModel = new Location();
        $this->locationTypeModel = new LocationType();
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
     * Show locations list
     */
    public function index(Request $request, Response $response): void
    {
        if (!$this->hasPermission('view-locations')) {
            $_SESSION['flash_error'] = 'You do not have permission to view locations';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }
        
        $locations = $this->locationModel->getAllWithType();
        $locationTypes = $this->locationTypeModel->getActive();
        
        $this->view('locations/index', [
            'locations' => $locations,
            'locationTypes' => $locationTypes,
            'user' => $_SESSION['user'] ?? [],
            'pageTitle' => 'Locations',
            'scripts' => ['js/pages/custom-table.js']
        ], 'main');
    }

    /**
     * Handle location actions (create, update, delete)
     */
    public function handleAction(Request $request, Response $response): void
    {
        $data = $request->getBody();
        $action = $data['action'] ?? '';
        
        // Check permission based on action
        $permissionMap = [
            'create' => 'create-locations',
            'update' => 'edit-locations',
            'delete' => 'delete-locations'
        ];
        
        $requiredPermission = $permissionMap[$action] ?? null;
        if (!$requiredPermission || !$this->hasPermission($requiredPermission)) {
            $_SESSION['flash_error'] = 'You do not have permission to perform this action';
            $response->redirect(APP_URL . '/locations');
            return;
        }
        
        switch ($action) {
            case 'create':
                $this->createLocation($data, $response);
                break;
            case 'update':
                $this->updateLocation($data, $response);
                break;
            case 'delete':
                $this->deleteLocation($data, $response);
                break;
            default:
                $_SESSION['flash_error'] = 'Invalid action';
                $response->redirect(APP_URL . '/locations');
        }
    }

    /**
     * Create location
     */
    protected function createLocation(array $data, Response $response): void
    {
        if (empty($data['name']) || empty($data['location_type_id'])) {
            $_SESSION['flash_error'] = 'Name and Location Type are required';
            $response->redirect(APP_URL . '/locations');
            return;
        }
        
        // Check if name exists
        if ($this->locationModel->nameExists($data['name'])) {
            $_SESSION['flash_error'] = 'Location with this name already exists';
            $response->redirect(APP_URL . '/locations');
            return;
        }
        
        $id = $this->locationModel->create([
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'location_type_id' => (int) $data['location_type_id'],
            'status' => $data['status'] ?? 'active'
        ]);
        
        if ($id) {
            $_SESSION['flash_success'] = 'Location created successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to create location';
        }
        
        $response->redirect(APP_URL . '/locations');
    }

    /**
     * Update location
     */
    protected function updateLocation(array $data, Response $response): void
    {
        if (empty($data['id']) || empty($data['name']) || empty($data['location_type_id'])) {
            $_SESSION['flash_error'] = 'ID, Name and Location Type are required';
            $response->redirect(APP_URL . '/locations');
            return;
        }
        
        $id = (int) $data['id'];
        
        // Check if name exists for other records
        if ($this->locationModel->nameExists($data['name'], $id)) {
            $_SESSION['flash_error'] = 'Location with this name already exists';
            $response->redirect(APP_URL . '/locations');
            return;
        }
        
        $updated = $this->locationModel->update($id, [
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'location_type_id' => (int) $data['location_type_id'],
            'status' => $data['status'] ?? 'active'
        ]);
        
        if ($updated) {
            $_SESSION['flash_success'] = 'Location updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update location';
        }
        
        $response->redirect(APP_URL . '/locations');
    }

    /**
     * Delete location
     */
    protected function deleteLocation(array $data, Response $response): void
    {
        if (empty($data['id'])) {
            $_SESSION['flash_error'] = 'ID is required';
            $response->redirect(APP_URL . '/locations');
            return;
        }
        
        $id = (int) $data['id'];
        $deleted = $this->locationModel->delete($id);
        
        if ($deleted) {
            $_SESSION['flash_success'] = 'Location deleted successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete location';
        }
        
        $response->redirect(APP_URL . '/locations');
    }
}
