<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Request;
use App\Core\Response;
use App\Models\WorkerLoan;
use App\Models\User;

class WorkerLoanController extends Controller
{
    protected WorkerLoan $workerLoan;
    protected User $user;

    public function __construct()
    {
        parent::__construct();
        $this->workerLoan = new WorkerLoan();
        $this->user = new User();
    }

    /**
     * Display worker loans list (Admin view)
     */
    public function index(Request $request, Response $response)
    {
        $page = (int) ($_GET['page'] ?? 1);
        $search = $_GET['search'] ?? '';
        $perPage = 20;
        $userId = null; // Admin can see all loans
        
        // Check if user is admin or has proper permissions
        if (!$this->hasPermission('view-worker-loans')) {
            // Regular users can only see their own loans
            $userId = $_SESSION['user']['id'] ?? null;
        }
        
        $result = $this->workerLoan->getPaginatedLoans($page, $perPage, $search, $userId);
        $stats = $this->workerLoan->getLoanStats($userId);
        
        $data = [
            'loans' => $result['data'],
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['totalPages'],
                'per_page' => $result['perPage'],
                'total' => $result['total']
            ],
            'stats' => $stats,
            'search' => $search,
            'title' => 'Worker Loans',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => APP_URL . '/dashboard'],
                ['name' => 'Finance', 'url' => '#'],
                ['name' => 'Worker Loans', 'url' => '']
            ]
        ];

        View::render('finance/worker-loans/index', $data);
    }

    /**
     * Show form to create new loan request
     */
    public function create(Request $request, Response $response)
    {
        $data = [
            'title' => 'Request Loan',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => APP_URL . '/dashboard'],
                ['name' => 'Finance', 'url' => '#'],
                ['name' => 'Worker Loans', 'url' => APP_URL . '/finance/worker-loans'],
                ['name' => 'Request Loan', 'url' => '']
            ]
        ];

        View::render('finance/worker-loans/create', $data);
    }

    /**
     * Store new loan request
     */
    public function store(Request $request, Response $response)
    {
        try {
            $requestAmount = (int) ($request->getBody()['request_amount'] ?? 0);
            $description = trim($request->getBody()['description'] ?? '');
            $userId = $_SESSION['user']['id'] ?? null;

            // Validation
            if (empty($userId)) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'User not logged in'
                ];
                $response->redirect(APP_URL . '/finance/worker-loans/create');
                return;
            }

            if ($requestAmount <= 0) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Request amount must be greater than 0'
                ];
                $response->redirect(APP_URL . '/finance/worker-loans/create');
                return;
            }

            if (empty($description)) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Description is required'
                ];
                $response->redirect(APP_URL . '/finance/worker-loans/create');
                return;
            }

            // Check if user has pending loans
            if ($this->workerLoan->hasPendingLoans($userId)) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'You already have a pending loan request. Please wait for approval.'
                ];
                $response->redirect(APP_URL . '/finance/worker-loans/create');
                return;
            }

            $loanData = [
                'user_id' => $userId,
                'request_amount' => $requestAmount,
                'payed_amount' => 0,
                'description' => $description,
                'status' => 'pending'
            ];

            if ($this->workerLoan->createLoan($loanData)) {
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Loan request submitted successfully'
                ];
                $response->redirect(APP_URL . '/finance/worker-loans');
            } else {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Failed to submit loan request'
                ];
                $response->redirect(APP_URL . '/finance/worker-loans/create');
            }
        } catch (\Exception $e) {
            error_log("Error creating loan request: " . $e->getMessage());
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'An error occurred while processing your request'
            ];
            $response->redirect(APP_URL . '/finance/worker-loans/create');
        }
    }

    /**
     * Show loan details
     */
    public function show(Request $request, Response $response)
    {
        $id = $request->getRouteParam('id');
        $loan = $this->workerLoan->findById($id);

        if (!$loan) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Loan not found'
            ];
            $response->redirect(APP_URL . '/finance/worker-loans');
            return;
        }

        // Check if user can view this loan
        if (!$this->hasPermission('view-worker-loans')) {
            $userId = $_SESSION['user']['id'] ?? null;
            if ($loan['user_id'] != $userId) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Access denied'
                ];
                $response->redirect(APP_URL . '/finance/worker-loans');
                return;
            }
        }

        $data = [
            'loan' => $loan,
            'title' => 'Loan Details',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => APP_URL . '/dashboard'],
                ['name' => 'Finance', 'url' => '#'],
                ['name' => 'Worker Loans', 'url' => APP_URL . '/finance/worker-loans'],
                ['name' => 'Loan Details', 'url' => '']
            ]
        ];

        View::render('finance/worker-loans/show', $data);
    }

    /**
     * Update loan status (Admin only)
     */
    public function updateStatus(Request $request, Response $response)
    {
        if (!$this->hasPermission('manage-worker-loans')) {
            $response->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }

        try {
            $id = $request->getRouteParam('id');
            $requestData = $request->getBody();
            $status = $requestData['status'] ?? '';
            $payedAmount = (int) ($requestData['payed_amount'] ?? 0);

            $loan = $this->workerLoan->findById($id);
            if (!$loan) {
                $response->json(['success' => false, 'message' => 'Loan not found'], 404);
                return;
            }

            // Validation
            if (!in_array($status, ['pending', 'outstanding', 'disbursed'])) {
                $response->json(['success' => false, 'message' => 'Invalid status'], 400);
                return;
            }

            if ($status === 'disbursed' && $payedAmount <= 0) {
                $response->json(['success' => false, 'message' => 'Payed amount must be greater than 0 for disbursed loans'], 400);
                return;
            }

            $updateData = [
                'status' => $status,
                'payed_amount' => $payedAmount
            ];

            if ($this->workerLoan->updateLoan($id, $updateData)) {
                $response->json(['success' => true, 'message' => 'Loan updated successfully']);
            } else {
                $response->json(['success' => false, 'message' => 'Failed to update loan'], 500);
            }
        } catch (\Exception $e) {
            error_log("Error updating loan status: " . $e->getMessage());
            $response->json(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Delete loan (Admin only)
     */
    public function delete(Request $request, Response $response)
    {
        if (!$this->hasPermission('manage-worker-loans')) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Access denied'
            ];
            $response->redirect(APP_URL . '/finance/worker-loans');
            return;
        }

        try {
            $id = $request->getRouteParam('id');
            $loan = $this->workerLoan->findById($id);

            if (!$loan) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Loan not found'
                ];
                $response->redirect(APP_URL . '/finance/worker-loans');
                return;
            }

            if ($this->workerLoan->deleteLoan($id)) {
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Loan deleted successfully'
                ];
            } else {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Failed to delete loan'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error deleting loan: " . $e->getMessage());
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'An error occurred while deleting the loan'
            ];
        }

        $response->redirect(APP_URL . '/finance/worker-loans');
    }

    /**
     * Get user's loan history
     */
    public function myLoans(Request $request, Response $response)
    {
        $userId = $_SESSION['user']['id'] ?? null;
        if (!$userId) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'User not logged in'
            ];
            $response->redirect(APP_URL . '/login');
            return;
        }

        $loans = $this->workerLoan->getUserLoanHistory($userId);
        $stats = $this->workerLoan->getLoanStats($userId);

        $data = [
            'loans' => $loans,
            'stats' => $stats,
            'title' => 'My Loans',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => APP_URL . '/dashboard'],
                ['name' => 'Finance', 'url' => '#'],
                ['name' => 'My Loans', 'url' => '']
            ]
        ];

        View::render('finance/worker-loans/my-loans', $data);
    }

    /**
     * Helper method to check permissions
     */
    private function hasPermission($permission): bool
    {
        $userPermissions = $_SESSION['user']['permissions'] ?? [];
        return in_array($permission, $userPermissions) || in_array('manage-all', $userPermissions);
    }
}