<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\CategoryTypeUnit;
use App\Models\CategoryType;
use App\Models\MeasurementUnit;

/**
 * CategoryTypeUnit Controller
 * Handles category type and measurement unit assignments
 */
class CategoryTypeUnitController extends Controller
{
    protected CategoryTypeUnit $assignmentModel;
    protected CategoryType $typeModel;
    protected MeasurementUnit $unitModel;

    public function __construct()
    {
        parent::__construct();
        $this->assignmentModel = new CategoryTypeUnit();
        $this->typeModel = new CategoryType();
        $this->unitModel = new MeasurementUnit();
    }

    /**
     * Check if user has a specific permission
     */
    protected function hasPermission(string $permission): bool
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) return false;
        
        $permissions = $user['permissions'] ?? [];

        if (is_string($permissions)) {
            $decoded = json_decode($permissions, true);
            if (is_array($decoded)) {
                $permissions = $decoded;
            } else {
                $permissions = array_filter(array_map('trim', explode(',', $permissions)));
            }
        }

        if (!is_array($permissions)) {
            $permissions = [];
        }

        return in_array($permission, $permissions, true);
    }

    /**
     * Show assignments page
     */
    public function index(Request $request, Response $response): void
    {
        if (!$this->hasPermission('view-type-unit-assignments')) {
            $_SESSION['flash_error'] = 'You do not have permission to view type-unit assignments';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }

        try {
            $assignments = [];
            $categoryTypes = [];
            $measurementUnits = [];
            $typesWithoutUnits = [];

            try {
                $assignments = $this->assignmentModel->getGroupedByType();
            } catch (\Throwable $e) {
                $assignments = [];
            }

            try {
                $categoryTypes = $this->typeModel->getActive();
            } catch (\Throwable $e) {
                $categoryTypes = [];
            }

            if (empty($categoryTypes)) {
                try {
                    $categoryTypes = Database::fetchAll(
                        "SELECT ct.type_id AS id, ct.type_name AS name, ct.categ_id AS category_id, pc.categ_name AS category_name
                         FROM tbl_category_types ct
                         JOIN tbl_product_categories pc ON ct.categ_id = pc.categ_id
                         WHERE ct.sts = 1
                         ORDER BY pc.categ_name ASC, ct.type_name ASC"
                    );
                } catch (\Throwable $e) {
                    $categoryTypes = [];
                }
            }

            $categoryTypes = array_map(static function (array $type): array {
                return [
                    'id' => (int) ($type['id'] ?? $type['type_id'] ?? 0),
                    'name' => (string) ($type['name'] ?? $type['type_name'] ?? ''),
                    'category_id' => (int) ($type['category_id'] ?? $type['categ_id'] ?? 0),
                    'category_name' => (string) ($type['category_name'] ?? $type['categ_name'] ?? ''),
                ];
            }, is_array($categoryTypes) ? $categoryTypes : []);

            try {
                $measurementUnits = $this->unitModel->getActive();
            } catch (\Throwable $e) {
                $measurementUnits = [];
            }

            if (empty($measurementUnits)) {
                try {
                    $measurementUnits = Database::fetchAll(
                        "SELECT rec_id AS id, rec_name AS name, '' AS symbol FROM tbl_receipttype WHERE sts = 1 ORDER BY rec_name ASC"
                    );
                } catch (\Throwable $e) {
                    $measurementUnits = [];
                }
            }

            $measurementUnits = array_map(static function (array $unit): array {
                return [
                    'id' => (int) ($unit['id'] ?? $unit['rec_id'] ?? 0),
                    'name' => (string) ($unit['name'] ?? $unit['rec_name'] ?? ''),
                    'symbol' => (string) ($unit['symbol'] ?? ''),
                ];
            }, is_array($measurementUnits) ? $measurementUnits : []);

            try {
                $typesWithoutUnits = $this->assignmentModel->getTypesWithoutUnits();
            } catch (\Throwable $e) {
                $typesWithoutUnits = [];
            }

            $typesWithoutUnits = array_map(static function (array $type): array {
                return [
                    'name' => (string) ($type['name'] ?? $type['type_name'] ?? ''),
                    'category_name' => (string) ($type['category_name'] ?? $type['categ_name'] ?? ''),
                ];
            }, is_array($typesWithoutUnits) ? $typesWithoutUnits : []);
            
            $this->view('products/type-units/index', [
                'assignments' => $assignments,
                'categoryTypes' => $categoryTypes,
                'measurementUnits' => $measurementUnits,
                'typesWithoutUnits' => $typesWithoutUnits,
                'user' => $_SESSION['user'] ?? [],
                'pageTitle' => 'Type Unit Assignments',
                'scripts' => ['js/pages/custom-table.js']
            ], 'main');
        } catch (\Throwable $e) {
            error_log('CategoryTypeUnitController index error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to load type-unit assignments';
            $response->redirect(APP_URL . '/dashboard');
        }
    }

    /**
     * Handle assignment actions
     */
    public function handleAction(Request $request, Response $response): void
    {
        $data = $request->getBody();
        $action = $data['action'] ?? '';
        
        if (!$this->hasPermission('manage-type-unit-assignments')) {
            $_SESSION['flash_error'] = 'You do not have permission to manage assignments';
            $response->redirect(APP_URL . '/products/type-units');
            return;
        }
        
        switch ($action) {
            case 'assign':
                $this->assignUnits($data, $response);
                break;
            case 'remove':
                $this->removeAssignment($data, $response);
                break;
            case 'set_default':
                $this->setDefaultUnit($data, $response);
                break;
            case 'bulk_assign':
                $this->bulkAssign($data, $response);
                break;
            default:
                $_SESSION['flash_error'] = 'Invalid action';
                $response->redirect(APP_URL . '/products/type-units');
        }
    }

    /**
     * Assign units to a category type
     */
    protected function assignUnits(array $data, Response $response): void
    {
        $typeId = (int)($data['category_type_id'] ?? 0);
        $rawUnitIds = $data['unit_ids'] ?? [];
        if (!is_array($rawUnitIds)) {
            $rawUnitIds = [$rawUnitIds];
        }
        $unitIds = array_values(array_unique(array_filter(array_map('intval', $rawUnitIds), static function (int $id): bool {
            return $id > 0;
        })));
        $defaultUnitId = !empty($data['default_unit_id']) ? (int)$data['default_unit_id'] : null;
        
        if ($typeId <= 0) {
            $_SESSION['flash_error'] = 'Please select a category type';
            $response->redirect(APP_URL . '/products/type-units');
            return;
        }
        
        // Clear-all action: valid type but no valid selected units
        if (empty($unitIds)) {
            $this->assignmentModel->bulkAssign($typeId, [], null);
            $_SESSION['flash_success'] = 'All unit assignments cleared successfully';
            $response->redirect(APP_URL . '/products/type-units');
            return;
        }
        
        // Validate default unit is in the selected units
        if ($defaultUnitId && !in_array($defaultUnitId, $unitIds, true)) {
            $defaultUnitId = (int)$unitIds[0]; // Use first unit as default
        }
        
        $this->assignmentModel->bulkAssign($typeId, $unitIds, $defaultUnitId);
        
        $_SESSION['flash_success'] = 'Units assigned successfully';
        $response->redirect(APP_URL . '/products/type-units');
    }

    /**
     * Remove a unit assignment
     */
    protected function removeAssignment(array $data, Response $response): void
    {
        $id = (int)($data['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Invalid assignment ID';
            $response->redirect(APP_URL . '/products/type-units');
            return;
        }
        
        if ($this->assignmentModel->removeById($id)) {
            $_SESSION['flash_success'] = 'Assignment removed successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to remove assignment';
        }
        
        $response->redirect(APP_URL . '/products/type-units');
    }

    /**
     * Set default unit for a category type
     */
    protected function setDefaultUnit(array $data, Response $response): void
    {
        $typeId = (int)($data['category_type_id'] ?? 0);
        $unitId = (int)($data['unit_id'] ?? 0);
        
        if ($typeId <= 0 || $unitId <= 0) {
            $_SESSION['flash_error'] = 'Invalid type or unit';
            $response->redirect(APP_URL . '/products/type-units');
            return;
        }
        
        if ($this->assignmentModel->setDefault($typeId, $unitId)) {
            $_SESSION['flash_success'] = 'Default unit updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update default unit';
        }
        
        $response->redirect(APP_URL . '/products/type-units');
    }

    /**
     * Bulk assign (AJAX)
     */
    protected function bulkAssign(array $data, Response $response): void
    {
        header('Content-Type: application/json');
        
        $typeId = (int)($data['category_type_id'] ?? 0);
        $rawUnitIds = $data['unit_ids'] ?? [];
        if (!is_array($rawUnitIds)) {
            $rawUnitIds = [$rawUnitIds];
        }
        $unitIds = array_values(array_unique(array_filter(array_map('intval', $rawUnitIds), static function (int $id): bool {
            return $id > 0;
        })));
        $defaultUnitId = !empty($data['default_unit_id']) ? (int)$data['default_unit_id'] : null;
        
        if ($typeId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Please select a category type']);
            exit;
        }
        
        if (empty($unitIds)) {
            $this->assignmentModel->bulkAssign($typeId, [], null);
            echo json_encode(['success' => true, 'message' => 'All unit assignments cleared successfully']);
            exit;
        }
        
        $this->assignmentModel->bulkAssign($typeId, $unitIds, $defaultUnitId);
        
        echo json_encode(['success' => true, 'message' => 'Units assigned successfully']);
        exit;
    }

    /**
     * Get units for a type (AJAX)
     */
    public function getTypeUnits(Request $request, Response $response): void
    {
        if (!$this->hasPermission('view-type-unit-assignments')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        $data = $request->getBody();
        $typeId = (int)($data['type_id'] ?? 0);
        
        if ($typeId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid type ID']);
            exit;
        }
        
        $units = $this->assignmentModel->getUnitsByType($typeId);
        
        echo json_encode(['success' => true, 'units' => $units]);
        exit;
    }
}
