<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\MeasurementUnit;

/**
 * MeasurementUnit Controller
 * Handles measurement unit management with conversion support
 */
class MeasurementUnitController extends Controller
{
    protected MeasurementUnit $unitModel;

    public function __construct()
    {
        parent::__construct();
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
        return in_array($permission, $permissions);
    }

    /**
     * Show measurement units list
     */
    public function index(Request $request, Response $response): void
    {
        if (!$this->hasPermission('view-measurement-units')) {
            $_SESSION['flash_error'] = 'You do not have permission to view measurement units';
            $response->redirect(APP_URL . '/dashboard');
            return;
        }
        
        $units = $this->unitModel->getAll();
        $baseUnits = $this->unitModel->getBaseUnits();
        
        $this->view('products/measurement-units/index', [
            'units' => $units,
            'baseUnits' => $baseUnits,
            'user' => $_SESSION['user'] ?? [],
            'pageTitle' => 'Measurement Units',
            'scripts' => ['js/pages/custom-table.js']
        ], 'main');
    }

    /**
     * Handle measurement unit actions (create, update, delete)
     */
    public function handleAction(Request $request, Response $response): void
    {
        $data = $request->getBody();
        $action = $data['action'] ?? '';
        
        // Check permission based on action
        $permissionMap = [
            'create' => 'create-measurement-units',
            'update' => 'edit-measurement-units',
            'delete' => 'delete-measurement-units',
            'convert' => 'view-measurement-units'
        ];
        
        $requiredPermission = $permissionMap[$action] ?? null;
        if (!$requiredPermission || !$this->hasPermission($requiredPermission)) {
            $_SESSION['flash_error'] = 'You do not have permission to perform this action';
            $response->redirect(APP_URL . '/products/measurement-units');
            return;
        }
        
        switch ($action) {
            case 'create':
                $this->createUnit($data, $response);
                break;
            case 'update':
                $this->updateUnit($data, $response);
                break;
            case 'delete':
                $this->deleteUnit($data, $response);
                break;
            case 'convert':
                $this->convertUnit($data, $response);
                break;
            default:
                $_SESSION['flash_error'] = 'Invalid action';
                $response->redirect(APP_URL . '/products/measurement-units');
        }
    }

    /**
     * Create measurement unit
     */
    protected function createUnit(array $data, Response $response): void
    {
        if (empty($data['name']) || empty($data['symbol'])) {
            $_SESSION['flash_error'] = 'Unit name and symbol are required';
            $response->redirect(APP_URL . '/products/measurement-units');
            return;
        }
        
        // Check if name exists
        if ($this->unitModel->nameExists($data['name'])) {
            $_SESSION['flash_error'] = 'A unit with this name already exists';
            $response->redirect(APP_URL . '/products/measurement-units');
            return;
        }
        
        // Check if symbol exists
        if ($this->unitModel->symbolExists($data['symbol'])) {
            $_SESSION['flash_error'] = 'A unit with this symbol already exists';
            $response->redirect(APP_URL . '/products/measurement-units');
            return;
        }
        
        $id = $this->unitModel->createUnit([
            'name' => trim($data['name']),
            'symbol' => trim($data['symbol']),
            'base_unit_id' => !empty($data['base_unit_id']) ? (int)$data['base_unit_id'] : null,
            'conversion_factor' => !empty($data['conversion_factor']) ? (float)$data['conversion_factor'] : 1,
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?? 'active'
        ]);
        
        if ($id) {
            $_SESSION['flash_success'] = 'Measurement unit created successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to create measurement unit';
        }
        
        $response->redirect(APP_URL . '/products/measurement-units');
    }

    /**
     * Update measurement unit
     */
    protected function updateUnit(array $data, Response $response): void
    {
        if (empty($data['id']) || empty($data['name']) || empty($data['symbol'])) {
            $_SESSION['flash_error'] = 'ID, Unit name and symbol are required';
            $response->redirect(APP_URL . '/products/measurement-units');
            return;
        }
        
        $id = (int) $data['id'];
        
        // Check if name exists for other records
        if ($this->unitModel->nameExists($data['name'], $id)) {
            $_SESSION['flash_error'] = 'A unit with this name already exists';
            $response->redirect(APP_URL . '/products/measurement-units');
            return;
        }
        
        // Check if symbol exists for other records
        if ($this->unitModel->symbolExists($data['symbol'], $id)) {
            $_SESSION['flash_error'] = 'A unit with this symbol already exists';
            $response->redirect(APP_URL . '/products/measurement-units');
            return;
        }
        
        // Prevent setting itself as base unit
        if (!empty($data['base_unit_id']) && (int)$data['base_unit_id'] === $id) {
            $_SESSION['flash_error'] = 'A unit cannot be its own base unit';
            $response->redirect(APP_URL . '/products/measurement-units');
            return;
        }
        
        $updated = $this->unitModel->updateUnit($id, [
            'name' => trim($data['name']),
            'symbol' => trim($data['symbol']),
            'base_unit_id' => !empty($data['base_unit_id']) ? (int)$data['base_unit_id'] : null,
            'conversion_factor' => !empty($data['conversion_factor']) ? (float)$data['conversion_factor'] : 1,
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?? 'active'
        ]);
        
        if ($updated) {
            $_SESSION['flash_success'] = 'Measurement unit updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update measurement unit';
        }
        
        $response->redirect(APP_URL . '/products/measurement-units');
    }

    /**
     * Delete measurement unit
     */
    protected function deleteUnit(array $data, Response $response): void
    {
        if (empty($data['id'])) {
            $_SESSION['flash_error'] = 'ID is required';
            $response->redirect(APP_URL . '/products/measurement-units');
            return;
        }
        
        $id = (int) $data['id'];
        $deleted = $this->unitModel->deleteUnit($id);
        
        if ($deleted) {
            $_SESSION['flash_success'] = 'Measurement unit deleted successfully';
        } else {
            $_SESSION['flash_error'] = 'Cannot delete this unit. It may be used as a base unit for other units.';
        }
        
        $response->redirect(APP_URL . '/products/measurement-units');
    }

    /**
     * Convert unit (AJAX)
     */
    protected function convertUnit(array $data, Response $response): void
    {
        header('Content-Type: application/json');
        
        $value = (float)($data['value'] ?? 0);
        $fromUnitId = (int)($data['from_unit_id'] ?? 0);
        $toUnitId = (int)($data['to_unit_id'] ?? 0);
        
        if ($value <= 0 || $fromUnitId <= 0 || $toUnitId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }
        
        $result = $this->unitModel->convert($value, $fromUnitId, $toUnitId);
        
        if ($result !== null) {
            $toUnit = $this->unitModel->findById($toUnitId);
            echo json_encode([
                'success' => true,
                'result' => $result,
                'formatted' => $this->unitModel->formatValue($result, $toUnit['symbol'])
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Conversion failed']);
        }
        exit;
    }

    /**
     * Get conversion table (AJAX)
     */
    public function conversionTable(Request $request, Response $response): void
    {
        if (!$this->hasPermission('view-measurement-units')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        $data = $request->getBody();
        $value = (float)($data['value'] ?? 0);
        $fromUnitId = (int)($data['from_unit_id'] ?? 0);
        
        if ($value <= 0 || $fromUnitId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }
        
        $conversions = $this->unitModel->getConversionTable($value, $fromUnitId);
        
        echo json_encode([
            'success' => true,
            'conversions' => $conversions
        ]);
        exit;
    }
}
