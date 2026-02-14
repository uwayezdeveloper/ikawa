<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\WorkerLoanPayment;
use App\Models\WorkerLoan;
use App\Models\Account;

class WorkerLoanPaymentController extends Controller
{
    private WorkerLoanPayment $paymentModel;
    private WorkerLoan $loanModel;
    private Account $accountModel;

    public function __construct()
    {
        parent::__construct();
        $this->paymentModel = new WorkerLoanPayment();
        $this->loanModel = new WorkerLoan();
        $this->accountModel = new Account();
    }

    /**
     * Display all loan payments
     */
    public function index()
    {
        $payments = $this->paymentModel->getAllWithDetails();
        
        $this->view('finance/loan-payments/index', [
            'title' => 'Worker Loan Payments',
            'payments' => $payments
        ]);
    }

    /**
     * Show create payment form
     */
    public function create()
    {
        // Get active loans that still have balance
        $loans = $this->loanModel->getActiveLoansWithBalance();
        // Get all accounts
        $accounts = $this->accountModel->getAll();

        $this->view('finance/loan-payments/create', [
            'title' => 'Pay Worker Loan',
            'loans' => $loans,
            'accounts' => $accounts
        ]);
    }

    /**
     * Store new payment
     */
    public function store()
    {
        try {
            $data = [
                'pay_account' => $this->request->input('pay_account'),
                'loan_id' => $this->request->input('loan_id'),
                'payed_amount' => $this->request->input('payed_amount'),
                'description' => $this->request->input('description'),
                'debited_account' => $this->request->input('debited_account')
            ];

            // Validate required fields
            if (empty($data['pay_account']) || empty($data['loan_id']) || 
                empty($data['payed_amount']) || empty($data['debited_account'])) {
                throw new \Exception('All required fields must be filled');
            }

            // Get loan details to check remaining balance
            $loan = $this->loanModel->getById($data['loan_id']);
            if (!$loan) {
                throw new \Exception('Loan not found');
            }

            // Calculate remaining balance
            $totalPaid = $this->paymentModel->getTotalPaymentsByLoan($data['loan_id']);
            $remainingBalance = $loan['request_amount'] - $totalPaid;

            if ($data['payed_amount'] > $remainingBalance) {
                throw new \Exception('Payment amount cannot exceed remaining loan balance of RWF ' . number_format($remainingBalance));
            }   

            // Start transaction
            Database::beginTransaction();

            // Create payment record
            $paymentId = $this->paymentModel->createPayment($data);

            if (!$paymentId) {
                throw new \Exception('Failed to create payment record');
            }

            // Update the debited account balance (increase)
            $account = $this->accountModel->getById($data['debited_account']);
            if ($account) {
                $newBalance = $account['balance'] + $data['payed_amount'];
                $this->accountModel->updateBalance($data['debited_account'], $newBalance);
            }

            // Update loan paid amount
            $newTotalPaid = $totalPaid + $data['payed_amount'];
            $this->loanModel->updatePaidAmount($data['loan_id'], $newTotalPaid);

            // If loan is fully paid, update status
            if ($newTotalPaid >= $loan['request_amount']) {
                $this->loanModel->updateStatus($data['loan_id'], 'paid');
            }

            Database::commit();

            // Set success message and redirect
            $_SESSION['success'] = 'Payment recorded successfully';
            $this->response->redirect(APP_URL . '/finance/loan-payments');
            return;

        } catch (\Exception $e) {
            Database::rollback();
            $_SESSION['error'] = $e->getMessage();
            $this->response->redirect(APP_URL . '/finance/loan-payments');
            return;
        }
    }

    /**
     * Show payment details
     */
    public function show($request, $response, $params)
    {
        $id = $params['id'] ?? null;
        
        if (!$id) {
            $this->response->redirect(APP_URL . '/finance/loan-payments');
            return;
        }
        
        $payment = $this->paymentModel->getByIdWithDetails($id);
        
        if (!$payment) {
            $this->response->redirect(APP_URL . '/finance/loan-payments');
            return;
        }

        $this->view('finance/loan-payments/show', [
            'title' => 'Payment Details',
            'payment' => $payment
        ]);
    }

    /**
     * Get loan details for AJAX
     */
    public function getLoanDetails($request, $response, $params)
    {
        $loanId = $params['id'] ?? null;
        
        if (!$loanId) {
            $this->response->json([
                'success' => false,
                'message' => 'Loan ID is required'
            ], 400);
            return;
        }
        
        try {
            $loan = $this->loanModel->getById($loanId);
            if (!$loan) {
                throw new \Exception('Loan not found');
            }

            $totalPaid = $this->paymentModel->getTotalPaymentsByLoan($loanId);
            $remainingBalance = $loan['request_amount'] - $totalPaid;

            $this->response->json([
                'success' => true,
                'loan' => $loan,
                'total_paid' => $totalPaid,
                'remaining_balance' => $remainingBalance
            ]);

        } catch (\Exception $e) {
            $this->response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }



    /**
     * Get payments by loan ID for AJAX
     */
    public function getPaymentsByLoan($request, $response, $params)
    {
        $loanId = $params['id'] ?? null;
        
        if (!$loanId) {
            $this->response->json([
                'success' => false,
                'message' => 'Loan ID is required'
            ], 400);
            return;
        }
        
        try {
            $payments = $this->paymentModel->getByLoanId($loanId);
            
            $this->response->json([
                'success' => true,
                'payments' => $payments
            ]);

        } catch (\Exception $e) {
            $this->response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}