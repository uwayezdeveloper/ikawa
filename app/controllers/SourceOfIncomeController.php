<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Request;
use App\Core\Response;
use App\Models\SourceOfIncome;

class SourceOfIncomeController extends Controller
{
    protected SourceOfIncome $sourceOfIncome;

    public function __construct()
    {
        parent::__construct();
        $this->sourceOfIncome = new SourceOfIncome();
    }

    /**
     * Display source of income list
     */
    public function index(Request $request, Response $response)
    {
        $page = (int) ($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $perPage = 20;
        
        $result = $this->sourceOfIncome->getPaginatedSources($page, $perPage, $search);
        
        $data = [
            'sources' => $result['data'],
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['totalPages'],
                'per_page' => $result['perPage'],
                'total' => $result['total']
            ],
            'search' => $search,
            'title' => 'Source of Income',
            'user' => $_SESSION['user'] ?? []
        ];
        
        return View::render('finance/source_of_income/index', $data, 'main');
    }

    /**
     * Show create form
     */
    public function create(Request $request, Response $response)
    {
        $data = [
            'title' => 'Add New Source of Income',
            'user' => $_SESSION['user'] ?? []
        ];
        
        return View::render('finance/source_of_income/create', $data, 'main');
    }

    /**
     * Store new source of income
     */
    public function store(Request $request, Response $response)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->redirect(APP_URL . '/finance/source-of-income');
        }

        $data = [
            'in_name' => trim($_POST['in_name'] ?? ''),
            'in_descr' => trim($_POST['in_descr'] ?? ''),
            'in_status' => (int) ($_POST['in_status'] ?? 1)
        ];

        // Validation
        $errors = [];

        if (empty($data['in_name'])) {
            $errors[] = 'Source name is required';
        } elseif (strlen($data['in_name']) > 50) {
            $errors[] = 'Source name must not exceed 50 characters';
        } elseif ($this->sourceOfIncome->sourceNameExists($data['in_name'])) {
            $errors[] = 'Source name already exists';
        }

        if (!empty($data['in_descr']) && strlen($data['in_descr']) > 100) {
            $errors[] = 'Description must not exceed 100 characters';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $data;
            return $response->redirect(APP_URL . '/finance/source-of-income/create');
        }

        try {
            $success = $this->sourceOfIncome->create($data);
            
            if ($success) {
                $_SESSION['success'] = 'Source of income created successfully';
            } else {
                $_SESSION['error'] = 'Failed to create source of income';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/finance/source-of-income');
    }

    /**
     * Show edit form
     */
    public function edit(Request $request, Response $response, array $params)
    {
        $id = (int) $params['id'];
        $source = $this->sourceOfIncome->findById($id);
        
        if (!$source) {
            $_SESSION['error'] = 'Source of income not found';
            return $response->redirect(APP_URL . '/finance/source-of-income');
        }

        $data = [
            'source' => $source,
            'title' => 'Edit Source of Income',
            'user' => $_SESSION['user'] ?? []
        ];
        
        return View::render('finance/source_of_income/edit', $data, 'main');
    }

    /**
     * Update source of income
     */
    public function update(Request $request, Response $response, array $params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->redirect(APP_URL . '/finance/source-of-income');
        }

        $id = (int) $params['id'];
        $source = $this->sourceOfIncome->findById($id);
        
        if (!$source) {
            $_SESSION['error'] = 'Source of income not found';
            return $response->redirect(APP_URL . '/finance/source-of-income');
        }

        $data = [
            'in_name' => trim($_POST['in_name'] ?? ''),
            'in_descr' => trim($_POST['in_descr'] ?? ''),
            'in_status' => (int) ($_POST['in_status'] ?? 1)
        ];

        // Validation
        $errors = [];

        if (empty($data['in_name'])) {
            $errors[] = 'Source name is required';
        } elseif (strlen($data['in_name']) > 50) {
            $errors[] = 'Source name must not exceed 50 characters';
        } elseif ($this->sourceOfIncome->sourceNameExists($data['in_name'], $id)) {
            $errors[] = 'Source name already exists';
        }

        if (!empty($data['in_descr']) && strlen($data['in_descr']) > 100) {
            $errors[] = 'Description must not exceed 100 characters';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $data;
            return $response->redirect(APP_URL . '/finance/source-of-income/' . $id . '/edit');
        }

        try {
            $success = $this->sourceOfIncome->update($id, $data);
            
            if ($success) {
                $_SESSION['success'] = 'Source of income updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update source of income';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/finance/source-of-income');
    }

    /**
     * Delete source of income
     */
    public function delete(Request $request, Response $response, array $params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $id = (int) $params['id'];
        
        try {
            $success = $this->sourceOfIncome->delete($id);
            
            if ($success) {
                $_SESSION['success'] = 'Source of income deleted successfully';
                return $response->json(['success' => true, 'message' => 'Deleted successfully']);
            } else {
                return $response->json(['success' => false, 'message' => 'Failed to delete'], 500);
            }
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Toggle status
     */
    public function toggleStatus(Request $request, Response $response, array $params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $id = (int) $params['id'];
        
        try {
            $success = $this->sourceOfIncome->toggleStatus($id);
            
            if ($success) {
                $source = $this->sourceOfIncome->findById($id);
                return $response->json([
                    'success' => true, 
                    'status' => $source['in_status'],
                    'message' => 'Status updated successfully'
                ]);
            } else {
                return $response->json(['success' => false, 'message' => 'Failed to update status'], 500);
            }
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get active sources (API endpoint)
     */
    public function getActive(Request $request, Response $response)
    {
        try {
            $sources = $this->sourceOfIncome->getActiveSources();
            return $response->json(['success' => true, 'data' => $sources]);
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
