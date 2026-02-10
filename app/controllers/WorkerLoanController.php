<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\WorkerLoan;
use App\Models\LoanDisbursement;
use App\Models\Account;
use Exception;

class WorkerLoanController extends Controller
{
    private $workerLoanModel;
    private $loanDisbursementModel;
    private $accountModel;

    public function __construct()
    {
        parent::__construct();
        $this->workerLoanModel = new WorkerLoan();
        $this->loanDisbursementModel = new LoanDisbursement();
        $this->accountModel = new Account();
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

    public function show($request, $response, $params)
    {
        $id = $params['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = 'Invalid loan ID.';
            return $response->redirect(APP_URL . '/finance/loans');
        }
        
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
            $data = $request->getBody();
            $id = $data['id'] ?? null;
            
            if (empty($id) || empty($data['status'])) {
                $_SESSION['error'] = 'Loan ID and status are required.';
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

    /**
     * Show loans ready for disbursement
     */
    public function disbursementIndex($request, $response)
    {
        $loans = $this->workerLoanModel->getLoansForDisbursement();
        
        return $this->view('finance/loans/disbursement-index', [
            'loans' => $loans,
            'user' => $_SESSION['user'] ?? []
        ]);
    }

    /**
     * Show disbursement form for specific loan
     */
    public function disbursementForm($request, $response, $params)
    {
        $loanId = $params['id'] ?? null;
        
        if (!$loanId) {
            $_SESSION['error'] = 'Invalid loan ID.';
            return $response->redirect(APP_URL . '/finance/loans/disbursement');
        }
        
        // Get loan details
        $loan = $this->workerLoanModel->getLoanById($loanId);
        if (!$loan) {
            $_SESSION['error'] = 'Loan not found.';
            return $response->redirect(APP_URL . '/finance/loans/disbursement');
        }

        // Check if loan is ready for disbursement
        if (!$this->workerLoanModel->isLoanReadyForDisbursement($loanId)) {
            $_SESSION['error'] = 'Loan is not ready for disbursement.';
            return $response->redirect(APP_URL . '/finance/loans/disbursement');
        }

        // Get active accounts with balance
        $accounts = $this->accountModel->getActive();
        
        return $this->view('finance/loans/disbursement-form', [
            'loan' => $loan,
            'accounts' => $accounts,
            'user' => $_SESSION['user'] ?? []
        ]);
    }

    /**
     * Process loan disbursement
     */
    public function processDisbursement($request, $response)
    {
        try {
            $data = $request->getBody();
            $loanId = $data['loan_id'] ?? null;
            
            if (empty($loanId)) {
                $_SESSION['error'] = 'Loan ID is required.';
                return $response->redirect(APP_URL . '/finance/loans/disbursement');
            }

            // Get loan details
            $loan = $this->workerLoanModel->getLoanById($loanId);
            if (!$loan) {
                $_SESSION['error'] = 'Loan not found.';
                return $response->redirect(APP_URL . '/finance/loans/disbursement');
            }

            // Validate that loan is ready for disbursement
            if (!$this->workerLoanModel->isLoanReadyForDisbursement($loanId)) {
                $_SESSION['error'] = 'Loan is not ready for disbursement.';
                return $response->redirect(APP_URL . '/finance/loans/disbursement');
            }

            // Process payment accounts and amounts
            $paymentAccounts = [];
            $totalAmount = 0;
            $charges = (int)($data['charges'] ?? 0);

            // Process multiple accounts
            if (isset($data['account_ids']) && isset($data['amounts'])) {
                for ($i = 0; $i < count($data['account_ids']); $i++) {
                    $accountId = $data['account_ids'][$i];
                    $amount = (int)$data['amounts'][$i];
                    
                    if ($accountId && $amount > 0) {
                        // Get account details
                        $account = $this->accountModel->findById($accountId);
                        if ($account && $account['balance'] >= $amount) {
                            $paymentAccounts[] = [
                                'account_id' => $accountId,
                                'account_name' => $account['account_name'],
                                'amount' => $amount
                            ];
                            $totalAmount += $amount;
                        } else {
                            $_SESSION['error'] = "Insufficient balance in account: {$account['account_name']}";
                            return $response->redirect(APP_URL . '/finance/loans/disbursement/' . $loanId);
                        }
                    }
                }
            }

            // Validate total amount matches requested amount
            if ($totalAmount != $loan['request_amount']) {
                $_SESSION['error'] = 'Total disbursement amount must equal the requested loan amount.';
                return $response->redirect(APP_URL . '/finance/loans/disbursement/' . $loanId);
            }

            // Validate required fields
            if (empty($data['worker_account'])) {
                $_SESSION['error'] = 'Worker account is required.';
                return $response->redirect(APP_URL . '/finance/loans/disbursement/' . $loanId);
            }

            // Create disbursement record
            $disbursementData = [
                'l_id' => $loanId,
                'pay_accounts' => $paymentAccounts,
                'amount' => $totalAmount,
                'charges' => $charges,
                'worker_account' => trim($data['worker_account'])
            ];

            if ($this->loanDisbursementModel->createDisbursement($disbursementData)) {
                // Deduct amounts from accounts
                foreach ($paymentAccounts as $payAccount) {
                    $newBalance = $this->accountModel->findById($payAccount['account_id'])['balance'] - $payAccount['amount'];
                    $this->accountModel->setBalance($payAccount['account_id'], $newBalance);
                }

                // Update loan status to disbursed
                $this->workerLoanModel->markAsDispursed($loanId);

                $_SESSION['success'] = 'Loan disbursed successfully.';
                return $response->redirect(APP_URL . '/finance/loans/disbursement');
            } else {
                $_SESSION['error'] = 'Failed to disburse loan.';
                return $response->redirect(APP_URL . '/finance/loans/disbursement/' . $loanId);
            }

        } catch (Exception $e) {
            $_SESSION['error'] = 'An error occurred: ' . $e->getMessage();
            return $response->redirect(APP_URL . '/finance/loans/disbursement');
        }
    }
}