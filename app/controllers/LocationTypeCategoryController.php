<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\LocationTypeCategory;
use App\Models\LocationType;
use App\Models\ProductCategory;

/**
 * LocationTypeCategory Controller
 * Handles location type and product category assignments
 */
class LocationTypeCategoryController extends Controller
{
    protected LocationTypeCategory $assignmentModel;
    protected LocationType $locationTypeModel;
    protected ProductCategory $categoryModel;

    public function __construct()
    {
        parent::__construct();
        $this->assignmentModel = new LocationTypeCategory();
        $this->locationTypeModel = new LocationType();
        $this->categoryModel = new ProductCategory();
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
     * Show assignments page
     */
    public function index(Request $request, Response $response): void
    {
        if (!$this->hasPermission('view-location-categories')) {
            $_SESSION['flash_error'] = 'You do not have permission to view location-category assignments';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }
        
        $assignments = $this->assignmentModel->getGroupedByLocationType();
        $locationTypes = $this->locationTypeModel->getActive();
        $productCategories = $this->categoryModel->getActive();
        $locationTypesWithout = $this->assignmentModel->getLocationTypesWithoutCategories();
        $categoriesWithout = $this->assignmentModel->getCategoriesWithoutLocationTypes();
        
        $this->view('settings/location-categories/index', [
            'assignments' => $assignments,
            'locationTypes' => $locationTypes,
            'productCategories' => $productCategories,
            'locationTypesWithout' => $locationTypesWithout,
            'categoriesWithout' => $categoriesWithout,
            'user' => $_SESSION['user'] ?? [],
            'pageTitle' => 'Location Type Categories',
            'scripts' => ['js/pages/custom-table.js']
        ], 'main');
    }

    /**
     * Handle assignment actions
     */
    public function handleAction(Request $request, Response $response): void
    {
        $data = $request->getBody();
        $action = $data['action'] ?? '';
        
        if (!$this->hasPermission('manage-location-categories')) {
            $_SESSION['flash_error'] = 'You do not have permission to manage assignments';
            $response->redirect(APP_URL . '/settings/location-categories');
            return;
        }
        
        switch ($action) {
            case 'assign':
                $this->assignCategories($data, $response);
                break;
            case 'remove':
                $this->removeAssignment($data, $response);
                break;
            case 'bulk_assign':
                $this->bulkAssign($data, $response);
                break;
            default:
                $_SESSION['flash_error'] = 'Invalid action';
                $response->redirect(APP_URL . '/settings/location-categories');
        }
    }

    /**
     * Assign categories to a location type
     */
    protected function assignCategories(array $data, Response $response): void
    {
        $locationTypeId = (int)($data['location_type_id'] ?? 0);
        $categoryIds = $data['category_ids'] ?? [];
        
        if ($locationTypeId <= 0) {
            $_SESSION['flash_error'] = 'Please select a location type';
            $response->redirect(APP_URL . '/settings/location-categories');
            return;
        }
        
        if (empty($categoryIds)) {
            $_SESSION['flash_error'] = 'Please select at least one product category';
            $response->redirect(APP_URL . '/settings/location-categories');
            return;
        }
        
        $this->assignmentModel->bulkAssign($locationTypeId, $categoryIds);
        
        $_SESSION['flash_success'] = 'Categories assigned successfully';
        $response->redirect(APP_URL . '/settings/location-categories');
    }

    /**
     * Remove a category assignment
     */
    protected function removeAssignment(array $data, Response $response): void
    {
        $id = (int)($data['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Invalid assignment ID';
            $response->redirect(APP_URL . '/settings/location-categories');
            return;
        }
        
        if ($this->assignmentModel->removeById($id)) {
            $_SESSION['flash_success'] = 'Assignment removed successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to remove assignment';
        }
        
        $response->redirect(APP_URL . '/settings/location-categories');
    }

    /**
     * Bulk assign (AJAX)
     */
    protected function bulkAssign(array $data, Response $response): void
    {
        header('Content-Type: application/json');
        
        $locationTypeId = (int)($data['location_type_id'] ?? 0);
        $categoryIds = $data['category_ids'] ?? [];
        
        if ($locationTypeId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Please select a location type']);
            exit;
        }
        
        if (empty($categoryIds)) {
            echo json_encode(['success' => false, 'message' => 'Please select at least one category']);
            exit;
        }
        
        $this->assignmentModel->bulkAssign($locationTypeId, $categoryIds);
        
        echo json_encode(['success' => true, 'message' => 'Categories assigned successfully']);
        exit;
    }

    /**
     * Get categories for a location type (AJAX)
     */
    public function getLocationCategories(Request $request, Response $response): void
    {
        if (!$this->hasPermission('view-location-categories')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        $data = $request->getBody();
        $locationTypeId = (int)($data['location_type_id'] ?? 0);
        
        if ($locationTypeId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid location type ID']);
            exit;
        }
        
        $categories = $this->assignmentModel->getCategoriesByLocationType($locationTypeId);
        
        echo json_encode(['success' => true, 'categories' => $categories]);
        exit;
    }
}
