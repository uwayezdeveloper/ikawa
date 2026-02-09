<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\WorkerLoan;
use Exception;

class WorkerLoanController extends Controller
{
    private $workerLoanModel;

    public function __construct()
    {
        parent::__construct();
        $this->workerLoanModel = new WorkerLoan();
    }

    public function create($request, $response)
    {
        return $this->view('finance/loans/create', [
            'user' => $_SESSION['user'] ?? []
        ]);
    }

    public function store($request, $response)
    {
        try {
            $data = $request->getBody();
            
            // Validate required fields
            if (empty($data['request_amount']) || empty($data['description'])) {
                $_SESSION['error'] = 'Request amount and description are required.';
                return $response->redirect(APP_URL . '/finance/loans/create');
            }

            // Validate amount is positive number
            if (!is_numeric($data['request_amount']) || $data['request_amount'] <= 0) {
                $_SESSION['error'] = 'Please enter a valid amount.';
                return $response->redirect(APP_URL . '/finance/loans/create');
            }

            $loanData = [
                'user_id' => $_SESSION['user']['id'],
                'request_amount' => (int)$data['request_amount'],
                'payed_amount' => 0,
                'description' => trim($data['description']),
                'status' => 'pending'
            ];

            if ($this->workerLoanModel->createLoan($loanData)) {
                $_SESSION['success'] = 'Loan request submitted successfully! Your request is now pending review.';
                return $response->redirect(APP_URL . '/finance/loans/create');
            } else {
                $_SESSION['error'] = 'Failed to submit loan request. Please try again.';
                return $response->redirect(APP_URL . '/finance/loans/create');
            }

        } catch (Exception $e) {
            $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
            return $response->redirect(APP_URL . '/finance/loans/create');
        }
    }

    public function myLoans($request, $response)
    {
        $userId = $_SESSION['user']['id'];
        $loans = $this->workerLoanModel->getUserLoans($userId);
        
        return $this->view('finance/loans/my-loans', [
            'loans' => $loans,
            'user' => $_SESSION['user'] ?? []
        ]);
    }

    public function index($request, $response)
    {
        $loans = $this->workerLoanModel->getAllLoans();
        
        return $this->view('finance/loans/index', [
            'loans' => $loans,
            'user' => $_SESSION['user'] ?? []
        ]);
    }

    public function show($request, $response)
    {
        $id = $request->input('id');
        $loan = $this->workerLoanModel->getLoanById($id);
        
        if (!$loan) {
            $_SESSION['error'] = 'Loan not found.';
            return $response->redirect(APP_URL . '/finance/loans');
        }
        
        return $this->view('finance/loans/show', [
            'loan' => $loan,
            'user' => $_SESSION['user'] ?? []
        ]);
    }

    public function updateStatus($request, $response)
    {
        try {
            $id = $request->input('id');
            $data = $request->getBody();
            
            if (empty($data['status'])) {
                $_SESSION['error'] = 'Status is required.';
                return $response->redirect(APP_URL . '/finance/loans');
            }
            
            if ($this->workerLoanModel->updateLoanStatus($id, $data['status'])) {
                $_SESSION['success'] = 'Loan status updated successfully.';
            } else {
                $_SESSION['error'] = 'Failed to update loan status.';
            }
            
            return $response->redirect(APP_URL . '/finance/loans');
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
            return $response->redirect(APP_URL . '/finance/loans');
        }
    }
}