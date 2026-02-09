<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Company;
use App\Models\LocationType;

/**
 * Settings Controller
 * Handles application settings
 */
class SettingsController extends Controller
{
    protected Company $companyModel;
    protected LocationType $locationTypeModel;

    public function __construct()
    {
        parent::__construct();
        $this->companyModel = new Company();
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
     * Show settings index page
     */
    public function index(Request $request, Response $response): void
    {
        if (!$this->hasPermission('view-settings')) {
            $_SESSION['flash_error'] = 'You do not have permission to view settings';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }
        
        $this->view('settings/index', [
            'pageTitle' => 'Settings'
        ], 'main');
    }

    /**
     * Show company settings
     */
    public function company(Request $request, Response $response): void
    {
        if (!$this->hasPermission('view-company')) {
            $_SESSION['flash_error'] = 'You do not have permission to view company settings';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }
        
        $company = $this->companyModel->getCompany();
        
        $this->view('settings/company', [
            'company' => $company,
            'user' => $_SESSION['user'] ?? [],
            'pageTitle' => 'Company Settings'
        ], 'main');
    }

    /**
     * Update company settings
     */
    public function updateCompany(Request $request, Response $response): void
    {
        if (!$this->hasPermission('edit-company')) {
            $_SESSION['flash_error'] = 'You do not have permission to edit company settings';
            $response->redirect(APP_URL . '/settings/company');
            return;
        }
        
        $data = $request->getBody();
        
        // Validate required fields
        if (empty($data['full_name']) || empty($data['email']) || empty($data['phone'])) {
            $_SESSION['flash_error'] = 'Full Name, Email and Phone are required';
            $response->redirect(APP_URL . '/settings/company');
            return;
        }
        
        // Handle logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoResult = $this->uploadLogo($_FILES['logo']);
            if ($logoResult['success']) {
                $this->companyModel->updateLogo($logoResult['filename']);
            } else {
                $_SESSION['flash_error'] = $logoResult['error'];
                $response->redirect(APP_URL . '/settings/company');
                return;
            }
        }
        
        // Update company info
        $updated = $this->companyModel->updateCompany([
            'full_name' => trim($data['full_name']),
            'short_name' => trim($data['short_name'] ?? ''),
            'email' => trim($data['email']),
            'phone' => trim($data['phone']),
            'address' => trim($data['address'] ?? '')
        ]);
        
        if ($updated) {
            $_SESSION['flash_success'] = 'Company information updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update company information';
        }
        
        $response->redirect(APP_URL . '/settings/company');
    }

    /**
     * Upload company logo
     */
    protected function uploadLogo(array $file): array
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP'];
        }
        
        // Validate file size
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'File too large. Maximum size: 2MB'];
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . time() . '.' . $extension;
        
        // Upload directory
        $uploadDir = BASE_PATH . '/assets/images/logos/';
        
        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Delete old logo if exists
        $oldLogo = $this->companyModel->getLogo();
        if ($oldLogo && file_exists($uploadDir . $oldLogo)) {
            unlink($uploadDir . $oldLogo);
        }
        
        // Move file
        $destination = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => true, 'filename' => $filename];
        }
        
        return ['success' => false, 'error' => 'Failed to upload file'];
    }

    /**
     * Show location types
     */
    public function locationTypes(Request $request, Response $response): void
    {
        if (!$this->hasPermission('view-location-types')) {
            $_SESSION['flash_error'] = 'You do not have permission to view location types';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }
        
        $locationTypes = $this->locationTypeModel->getAll();
        
        $this->view('settings/location-types', [
            'locationTypes' => $locationTypes,
            'user' => $_SESSION['user'] ?? [],
            'pageTitle' => 'Location Types',
            'scripts' => ['js/pages/custom-table.js']
        ], 'main');
    }

    /**
     * Handle location type actions (create, update, delete)
     */
    public function handleLocationType(Request $request, Response $response): void
    {
        $data = $request->getBody();
        $action = $data['action'] ?? '';
        
        // Check permission based on action
        $permissionMap = [
            'create' => 'create-location-types',
            'update' => 'edit-location-types',
            'delete' => 'delete-location-types'
        ];
        
        $requiredPermission = $permissionMap[$action] ?? null;
        if (!$requiredPermission || !$this->hasPermission($requiredPermission)) {
            $_SESSION['flash_error'] = 'You do not have permission to perform this action';
            $response->redirect(APP_URL . '/settings/location-types');
            return;
        }
        
        switch ($action) {
            case 'create':
                $this->createLocationType($data, $response);
                break;
            case 'update':
                $this->updateLocationType($data, $response);
                break;
            case 'delete':
                $this->deleteLocationType($data, $response);
                break;
            default:
                $_SESSION['flash_error'] = 'Invalid action';
                $response->redirect(APP_URL . '/settings/location-types');
        }
    }

    /**
     * Create location type
     */
    protected function createLocationType(array $data, Response $response): void
    {
        if (empty($data['name'])) {
            $_SESSION['flash_error'] = 'Name is required';
            $response->redirect(APP_URL . '/settings/location-types');
            return;
        }
        
        // Check if name exists
        if ($this->locationTypeModel->nameExists($data['name'])) {
            $_SESSION['flash_error'] = 'Location type with this name already exists';
            $response->redirect(APP_URL . '/settings/location-types');
            return;
        }
        
        $id = $this->locationTypeModel->create([
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?? 'active'
        ]);
        
        if ($id) {
            $_SESSION['flash_success'] = 'Location type created successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to create location type';
        }
        
        $response->redirect(APP_URL . '/settings/location-types');
    }

    /**
     * Update location type
     */
    protected function updateLocationType(array $data, Response $response): void
    {
        if (empty($data['id']) || empty($data['name'])) {
            $_SESSION['flash_error'] = 'ID and Name are required';
            $response->redirect(APP_URL . '/settings/location-types');
            return;
        }
        
        $id = (int) $data['id'];
        
        // Check if name exists for other records
        if ($this->locationTypeModel->nameExists($data['name'], $id)) {
            $_SESSION['flash_error'] = 'Location type with this name already exists';
            $response->redirect(APP_URL . '/settings/location-types');
            return;
        }
        
        $updated = $this->locationTypeModel->update($id, [
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?? 'active'
        ]);
        
        if ($updated) {
            $_SESSION['flash_success'] = 'Location type updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update location type';
        }
        
        $response->redirect(APP_URL . '/settings/location-types');
    }

    /**
     * Delete location type
     */
    protected function deleteLocationType(array $data, Response $response): void
    {
        if (empty($data['id'])) {
            $_SESSION['flash_error'] = 'ID is required';
            $response->redirect(APP_URL . '/settings/location-types');
            return;
        }
        
        $id = (int) $data['id'];
        $deleted = $this->locationTypeModel->delete($id);
        
        if ($deleted) {
            $_SESSION['flash_success'] = 'Location type deleted successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete location type';
        }
        
        $response->redirect(APP_URL . '/settings/location-types');
    }
}