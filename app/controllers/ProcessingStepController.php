<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\ProcessingStep;

class ProcessingStepController extends Controller
{
    protected ProcessingStep $stepModel;

    public function __construct()
    {
        parent::__construct();
        $this->stepModel = new ProcessingStep();
    }

    /**
     * Display processing steps page
     */
    public function index($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        $permissions = $user['permissions'] ?? [];
        
        if (!in_array('view-settings', $permissions) && !in_array('manage-settings', $permissions)) {
            return $response->redirect(APP_URL . '/dashboard');
        }

        $steps = $this->stepModel->getAll();

        return View::render('settings/processing-steps', [
            'title' => 'Processing Steps',
            'user' => $user,
            'steps' => $steps
        ], 'main');
    }

    /**
     * Handle processing step actions
     */
    public function handleAction($request, $response)
    {
        $user = $_SESSION['user'] ?? null;
        $permissions = $user['permissions'] ?? [];
        
        if (!in_array('manage-settings', $permissions)) {
            $_SESSION['flash_error'] = 'You do not have permission to manage processing steps';
            return $response->redirect(APP_URL . '/settings/processing-steps');
        }

        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                return $this->create($request, $response);
            case 'update':
                return $this->update($request, $response);
            case 'delete':
                return $this->delete($request, $response);
            case 'toggle_status':
                return $this->toggleStatus($request, $response);
            case 'change_order':
                return $this->changeOrder($request, $response);
            default:
                $_SESSION['flash_error'] = 'Invalid action';
                return $response->redirect(APP_URL . '/settings/processing-steps');
        }
    }

    /**
     * Create new processing step
     */
    private function create($request, $response)
    {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (empty($name)) {
            $_SESSION['flash_error'] = 'Name is required';
            return $response->redirect(APP_URL . '/settings/processing-steps');
        }

        if ($this->stepModel->nameExists($name)) {
            $_SESSION['flash_error'] = 'A processing step with this name already exists';
            return $response->redirect(APP_URL . '/settings/processing-steps');
        }

        $nextOrder = $this->stepModel->getNextOrder();
        
        $id = $this->stepModel->create([
            'name' => $name,
            'description' => $description,
            'step_order' => $nextOrder,
            'status' => $status
        ]);

        if ($id) {
            $_SESSION['flash_success'] = 'Processing step created successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to create processing step';
        }

        return $response->redirect(APP_URL . '/settings/processing-steps');
    }

    /**
     * Update processing step
     */
    private function update($request, $response)
    {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (!$id || empty($name)) {
            $_SESSION['flash_error'] = 'ID and Name are required';
            return $response->redirect(APP_URL . '/settings/processing-steps');
        }

        if ($this->stepModel->nameExists($name, $id)) {
            $_SESSION['flash_error'] = 'A processing step with this name already exists';
            return $response->redirect(APP_URL . '/settings/processing-steps');
        }

        $updated = $this->stepModel->update($id, [
            'name' => $name,
            'description' => $description,
            'status' => $status
        ]);

        if ($updated) {
            $_SESSION['flash_success'] = 'Processing step updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update processing step';
        }

        return $response->redirect(APP_URL . '/settings/processing-steps');
    }

    /**
     * Delete processing step
     */
    private function delete($request, $response)
    {
        $id = intval($_POST['id'] ?? 0);

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid ID';
            return $response->redirect(APP_URL . '/settings/processing-steps');
        }

        // Check if step is in use
        $inUse = \App\Core\Database::fetch(
            "SELECT COUNT(*) as count FROM warehouse_processing WHERE processing_step = (SELECT name FROM processing_steps WHERE id = :id)",
            ['id' => $id]
        );

        if (($inUse['count'] ?? 0) > 0) {
            $_SESSION['flash_error'] = 'Cannot delete: This processing step is in use';
            return $response->redirect(APP_URL . '/settings/processing-steps');
        }

        $deleted = $this->stepModel->delete($id);

        if ($deleted) {
            $_SESSION['flash_success'] = 'Processing step deleted successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete processing step';
        }

        return $response->redirect(APP_URL . '/settings/processing-steps');
    }

    /**
     * Toggle processing step status
     */
    private function toggleStatus($request, $response)
    {
        $id = intval($_POST['id'] ?? 0);

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid ID';
            return $response->redirect(APP_URL . '/settings/processing-steps');
        }

        $toggled = $this->stepModel->toggleStatus($id);

        if ($toggled) {
            $_SESSION['flash_success'] = 'Status updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update status';
        }

        return $response->redirect(APP_URL . '/settings/processing-steps');
    }

    /**
     * Change processing step order (swap with existing)
     */
    private function changeOrder($request, $response)
    {
        $id = intval($_POST['id'] ?? 0);
        $newOrder = intval($_POST['new_order'] ?? 0);
        $maxOrder = $this->stepModel->getCount();

        if (!$id) {
            $_SESSION['flash_error'] = 'Invalid ID';
            return $response->redirect(APP_URL . '/settings/processing-steps');
        }

        if ($newOrder < 1 || $newOrder > $maxOrder) {
            $_SESSION['flash_error'] = "Order must be between 1 and {$maxOrder}";
            return $response->redirect(APP_URL . '/settings/processing-steps');
        }

        $changed = $this->stepModel->changeOrder($id, $newOrder);

        if ($changed) {
            $_SESSION['flash_success'] = 'Order updated successfully';
        } else {
            $_SESSION['flash_error'] = 'Failed to update order';
        }

        return $response->redirect(APP_URL . '/settings/processing-steps');
    }
}
