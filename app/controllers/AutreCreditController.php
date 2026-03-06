<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\AutreCredit;
use App\Models\AccountTransaction;

class AutreCreditController extends Controller
{
    private AutreCredit $autreCreditModel;
    private AccountTransaction $transactionModel;

    public function __construct()
    {
        parent::__construct();
        $this->autreCreditModel = new AutreCredit();
        $this->transactionModel = new AccountTransaction();
    }

    public function index(Request $request, Response $response)
    {
        return View::render('finance/autre_credit/index', [
            'title' => 'Autre Credit',
            'user' => $_SESSION['user'] ?? [],
            'sources' => $this->autreCreditModel->getActiveSources(),
            'accounts' => $this->autreCreditModel->getActiveAccounts(),
            'credits' => $this->autreCreditModel->getCredits(),
            'history' => $this->autreCreditModel->getHistory(),
        ], 'main');
    }

    public function store(Request $request, Response $response)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->redirect(APP_URL . '/finance/autre-credit');
        }

        $sourceId = (int)($_POST['source_of_money_id'] ?? 0);
        $accountId = (int)($_POST['account_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $dueDate = trim((string)($_POST['due_date'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $user = $_SESSION['user'] ?? [];
        $userId = (int)($user['id'] ?? 0);

        if ($sourceId <= 0 || $accountId <= 0 || $amount <= 0 || $dueDate === '') {
            $_SESSION['error'] = 'Source, account, amount and due date are required.';
            return $response->redirect(APP_URL . '/finance/autre-credit');
        }

        $source = $this->autreCreditModel->getSourceById($sourceId);
        if (!$source) {
            $_SESSION['error'] = 'Invalid source of money selected.';
            return $response->redirect(APP_URL . '/finance/autre-credit');
        }

        try {
            Database::query('START TRANSACTION');

            $creditId = $this->autreCreditModel->createCredit([
                'source_of_money_id' => $sourceId,
                'amount' => $amount,
                'outstanding_amount' => $amount,
                'account_id' => $accountId,
                'due_date' => $dueDate,
                'done_date' => null,
                'status' => 'outstanding',
                'description' => $description !== '' ? $description : null,
                'created_by' => $userId > 0 ? $userId : null,
            ]);

            $this->transactionModel->createCredit(
                $accountId,
                $amount,
                'autre_credit',
                $creditId,
                'Autre credit from source: ' . (string)$source['in_name'],
                $userId > 0 ? $userId : null
            );

            $this->autreCreditModel->addHistory([
                'autre_credit_id' => $creditId,
                'source_of_money_id' => $sourceId,
                'amount' => $amount,
                'account_id' => $accountId,
                'payed_account_id' => null,
                'due_date' => $dueDate,
                'done_date' => null,
                'action_type' => 'created',
                'notes' => $description !== '' ? $description : 'Credit created',
                'created_by' => $userId > 0 ? $userId : null,
            ]);

            Database::query('COMMIT');
            $_SESSION['success'] = 'Autre credit recorded and account credited successfully.';
        } catch (\Throwable $e) {
            Database::query('ROLLBACK');
            $_SESSION['error'] = 'Failed to record autre credit: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/finance/autre-credit');
    }

    public function repay(Request $request, Response $response, array $params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response->redirect(APP_URL . '/finance/autre-credit');
        }

        $creditId = (int)($params['id'] ?? 0);
        $payedAccountId = (int)($_POST['payed_account_id'] ?? 0);
        $amount = (float)($_POST['repay_amount'] ?? 0);
        $notes = trim((string)($_POST['repay_notes'] ?? ''));
        $user = $_SESSION['user'] ?? [];
        $userId = (int)($user['id'] ?? 0);

        $credit = $this->autreCreditModel->getCreditById($creditId);
        if (!$credit) {
            $_SESSION['error'] = 'Credit record not found.';
            return $response->redirect(APP_URL . '/finance/autre-credit');
        }

        if ($payedAccountId <= 0 || $amount <= 0) {
            $_SESSION['error'] = 'Repayment account and amount are required.';
            return $response->redirect(APP_URL . '/finance/autre-credit');
        }

        $outstanding = (float)($credit['outstanding_amount'] ?? 0);
        if ($outstanding <= 0) {
            $_SESSION['error'] = 'This credit is already fully paid.';
            return $response->redirect(APP_URL . '/finance/autre-credit');
        }

        if ($amount > $outstanding) {
            $_SESSION['error'] = 'Repayment amount cannot be greater than outstanding amount.';
            return $response->redirect(APP_URL . '/finance/autre-credit');
        }

        try {
            Database::query('START TRANSACTION');

            $this->transactionModel->createDebit(
                $payedAccountId,
                $amount,
                'autre_credit_repayment',
                $creditId,
                'Repayment for autre credit #' . $creditId,
                $userId > 0 ? $userId : null
            );

            $newOutstanding = max(0, $outstanding - $amount);
            $isPaid = $newOutstanding <= 0;
            $doneDate = $isPaid ? date('Y-m-d H:i:s') : null;

            $this->autreCreditModel->updateCreditAfterRepayment(
                $creditId,
                $newOutstanding,
                $doneDate,
                $isPaid ? 'paid' : 'outstanding'
            );

            $this->autreCreditModel->addHistory([
                'autre_credit_id' => $creditId,
                'source_of_money_id' => (int)$credit['source_of_money_id'],
                'amount' => $amount,
                'account_id' => (int)$credit['account_id'],
                'payed_account_id' => $payedAccountId,
                'due_date' => $credit['due_date'] ?? null,
                'done_date' => date('Y-m-d H:i:s'),
                'action_type' => 'repayment',
                'notes' => $notes !== '' ? $notes : 'Credit repayment',
                'created_by' => $userId > 0 ? $userId : null,
            ]);

            Database::query('COMMIT');
            $_SESSION['success'] = $isPaid
                ? 'Credit repaid successfully and marked as paid.'
                : 'Repayment saved successfully.';
        } catch (\Throwable $e) {
            Database::query('ROLLBACK');
            $_SESSION['error'] = 'Failed to repay credit: ' . $e->getMessage();
        }

        return $response->redirect(APP_URL . '/finance/autre-credit');
    }
}
